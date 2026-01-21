<?php

namespace App\Services;

use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Service untuk mengelola rekonsiliasi bank
 *
 * Menyediakan fitur upload statement, parsing Excel/CSV,
 * auto-matching, dan verifikasi rekonsiliasi
 */
class BankReconciliationService
{
    /**
     * Toleransi tanggal untuk auto-matching (dalam hari)
     */
    protected int $dateTolerance = 1;

    /**
     * Upload dan parse bank statement file
     *
     * @param  UploadedFile  $file  File yang diupload
     * @param  array  $options  Opsi tambahan (bank_name, statement_date)
     * @return array{success: bool, reconciliation: ?BankReconciliation, message: string}
     */
    public function uploadStatement(UploadedFile $file, array $options = []): array
    {
        DB::beginTransaction();

        try {
            // Store file
            $filename = $this->storeFile($file);

            // Create reconciliation record
            $reconciliation = BankReconciliation::create([
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'bank_name' => $options['bank_name'] ?? null,
                'statement_date' => $options['statement_date'] ?? null,
                'status' => BankReconciliation::STATUS_DRAFT,
                'uploaded_by' => auth()->id(),
            ]);

            // Parse file and create items
            $transactions = $this->parseStatement($file);

            if ($transactions->isEmpty()) {
                throw new \Exception('Tidak ada transaksi yang dapat diparse dari file.');
            }

            $this->createItems($reconciliation, $transactions);

            // Update statistics
            $reconciliation->updateStatistics();

            DB::commit();

            return [
                'success' => true,
                'reconciliation' => $reconciliation->fresh(['items']),
                'message' => "Berhasil memproses {$transactions->count()} transaksi.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload bank statement', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'reconciliation' => null,
                'message' => 'Gagal memproses file: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Parse bank statement file (Excel/CSV)
     *
     * @param  UploadedFile  $file  File yang akan di-parse
     * @return Collection<array> Koleksi transaksi
     */
    protected function parseStatement(UploadedFile $file): Collection
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->parseExcel($file);
        } elseif ($extension === 'csv') {
            return $this->parseCsv($file);
        }

        throw new \Exception("Format file tidak didukung: {$extension}");
    }

    /**
     * Parse Excel file
     */
    protected function parseExcel(UploadedFile $file): Collection
    {
        $rows = Excel::toArray(null, $file)[0] ?? [];

        return $this->normalizeTransactions($rows);
    }

    /**
     * Parse CSV file
     */
    protected function parseCsv(UploadedFile $file): Collection
    {
        $rows = [];
        $handle = fopen($file->getPathname(), 'r');

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $this->normalizeTransactions($rows);
    }

    /**
     * Normalize transactions dari raw rows
     *
     * Mengidentifikasi kolom tanggal, deskripsi, jumlah dari header atau posisi
     */
    protected function normalizeTransactions(array $rows): Collection
    {
        if (count($rows) < 2) {
            return collect();
        }

        // Try to detect columns from header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $columnMap = $this->detectColumns($header);

        $transactions = collect();

        // Skip header row
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $transaction = $this->extractTransaction($row, $columnMap);

            if ($transaction && $transaction['amount'] > 0) {
                $transactions->push($transaction);
            }
        }

        return $transactions;
    }

    /**
     * Detect column mapping dari header
     */
    protected function detectColumns(array $header): array
    {
        $map = [
            'date' => null,
            'description' => null,
            'amount' => null,
            'reference' => null,
            'type' => null,
        ];

        $dateKeywords = ['tanggal', 'date', 'tgl', 'posting date', 'transaction date'];
        $descKeywords = ['deskripsi', 'description', 'keterangan', 'berita', 'uraian', 'remarks'];
        $amountKeywords = ['jumlah', 'amount', 'nominal', 'kredit', 'credit', 'debit', 'mutasi'];
        $refKeywords = ['referensi', 'reference', 'ref', 'no ref', 'nomor referensi'];

        foreach ($header as $index => $col) {
            $col = strtolower(trim($col));

            foreach ($dateKeywords as $keyword) {
                if (str_contains($col, $keyword)) {
                    $map['date'] = $index;
                    break;
                }
            }

            foreach ($descKeywords as $keyword) {
                if (str_contains($col, $keyword)) {
                    $map['description'] = $index;
                    break;
                }
            }

            foreach ($amountKeywords as $keyword) {
                if (str_contains($col, $keyword)) {
                    $map['amount'] = $index;
                    break;
                }
            }

            foreach ($refKeywords as $keyword) {
                if (str_contains($col, $keyword)) {
                    $map['reference'] = $index;
                    break;
                }
            }
        }

        // Fallback to positional mapping if detection fails
        if ($map['date'] === null) {
            $map['date'] = 0;
        }
        if ($map['description'] === null) {
            $map['description'] = 1;
        }
        if ($map['amount'] === null) {
            $map['amount'] = 2;
        }

        return $map;
    }

    /**
     * Extract transaction dari row berdasarkan column map
     */
    protected function extractTransaction(array $row, array $columnMap): ?array
    {
        try {
            $dateValue = $row[$columnMap['date']] ?? null;
            $descValue = $row[$columnMap['description']] ?? '';
            $amountValue = $row[$columnMap['amount']] ?? 0;
            $refValue = $columnMap['reference'] !== null ? ($row[$columnMap['reference']] ?? null) : null;

            // Parse date
            $date = $this->parseDate($dateValue);
            if (! $date) {
                return null;
            }

            // Parse amount (remove formatting)
            $amount = $this->parseAmount($amountValue);

            return [
                'transaction_date' => $date,
                'description' => trim($descValue),
                'amount' => abs($amount),
                'transaction_type' => $amount >= 0 ? 'credit' : 'debit',
                'reference' => $refValue ? trim($refValue) : null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse date dari berbagai format
     */
    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If numeric (Excel serial date)
        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                // Continue to other parsing methods
            }
        }

        // Try common date formats
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y', 'd F Y', 'd/m/y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's natural parsing
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse amount dari string (remove currency formatting)
     */
    protected function parseAmount($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove common formatting
        $value = str_replace(['Rp', 'IDR', '.', ',', ' '], ['', '', '', '.', ''], $value);
        $value = trim($value);

        return (float) $value;
    }

    /**
     * Create reconciliation items dari transactions
     */
    protected function createItems(BankReconciliation $reconciliation, Collection $transactions): void
    {
        foreach ($transactions as $transaction) {
            BankReconciliationItem::create([
                'reconciliation_id' => $reconciliation->id,
                'transaction_date' => $transaction['transaction_date'],
                'description' => $transaction['description'],
                'amount' => $transaction['amount'],
                'transaction_type' => $transaction['transaction_type'],
                'reference' => $transaction['reference'],
                'match_type' => BankReconciliationItem::MATCH_UNMATCHED,
            ]);
        }
    }

    /**
     * Store uploaded file
     */
    protected function storeFile(UploadedFile $file): string
    {
        $filename = 'reconciliation_'.now()->format('YmdHis').'_'.uniqid().'.'.$file->getClientOriginalExtension();

        $file->storeAs('reconciliations', $filename, 'local');

        return $filename;
    }

    /**
     * Run auto-matching untuk reconciliation
     *
     * @param  BankReconciliation  $reconciliation  Rekonsiliasi yang akan di-match
     * @return array{matched: int, total: int}
     */
    public function runAutoMatch(BankReconciliation $reconciliation): array
    {
        $items = $reconciliation->items()->unmatched()->credits()->get();
        $matchedCount = 0;

        foreach ($items as $item) {
            $match = $this->findMatchingPayment($item);

            if ($match) {
                $item->autoMatchWithPayment($match['payment'], $match['confidence']);
                $matchedCount++;
            }
        }

        // Update statistics
        $reconciliation->updateStatistics();

        return [
            'matched' => $matchedCount,
            'total' => $items->count(),
        ];
    }

    /**
     * Find matching payment untuk item
     *
     * @return array{payment: Payment, confidence: float}|null
     */
    protected function findMatchingPayment(BankReconciliationItem $item): ?array
    {
        $startDate = Carbon::parse($item->transaction_date)->subDays($this->dateTolerance);
        $endDate = Carbon::parse($item->transaction_date)->addDays($this->dateTolerance);

        // Find payments with matching amount within date tolerance
        $payments = Payment::query()
            ->where('metode_pembayaran', 'transfer')
            ->whereIn('status', ['pending', 'verified'])
            ->whereBetween('tanggal_bayar', [$startDate, $endDate])
            ->where('nominal', $item->amount)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('bank_reconciliation_items')
                    ->whereColumn('bank_reconciliation_items.payment_id', 'payments.id');
            })
            ->get();

        if ($payments->isEmpty()) {
            return null;
        }

        // If only one match, return with high confidence
        if ($payments->count() === 1) {
            $payment = $payments->first();
            $confidence = $this->calculateConfidence($item, $payment);

            if ($confidence >= 70) {
                return ['payment' => $payment, 'confidence' => $confidence];
            }
        }

        // If multiple matches, try to find best match by date
        $exactDateMatch = $payments->first(function ($payment) use ($item) {
            return $payment->tanggal_bayar->format('Y-m-d') === $item->transaction_date->format('Y-m-d');
        });

        if ($exactDateMatch) {
            return ['payment' => $exactDateMatch, 'confidence' => 95];
        }

        return null;
    }

    /**
     * Calculate confidence score untuk match
     */
    protected function calculateConfidence(BankReconciliationItem $item, Payment $payment): float
    {
        $score = 0;

        // Exact amount match = 50 points
        if ((float) $item->amount === (float) $payment->nominal) {
            $score += 50;
        }

        // Date matching
        $daysDiff = abs(Carbon::parse($item->transaction_date)->diffInDays($payment->tanggal_bayar));
        if ($daysDiff === 0) {
            $score += 40;
        } elseif ($daysDiff === 1) {
            $score += 30;
        } else {
            $score += 20;
        }

        // Reference/description contains student name or receipt number
        if ($item->description || $item->reference) {
            $searchText = strtolower($item->description.' '.$item->reference);

            if (str_contains($searchText, strtolower($payment->student->nama_lengkap ?? ''))) {
                $score += 10;
            }

            if (str_contains($searchText, strtolower($payment->nomor_kwitansi ?? ''))) {
                $score += 10;
            }
        }

        return min($score, 100);
    }

    /**
     * Manual match item dengan payment
     */
    public function manualMatch(BankReconciliationItem $item, Payment $payment): array
    {
        if ($item->isMatched()) {
            return [
                'success' => false,
                'message' => 'Item sudah di-match dengan pembayaran lain.',
            ];
        }

        // Check if payment already matched
        $existingMatch = BankReconciliationItem::where('payment_id', $payment->id)->first();
        if ($existingMatch) {
            return [
                'success' => false,
                'message' => 'Pembayaran sudah di-match dengan transaksi lain.',
            ];
        }

        $item->matchWithPayment($payment);

        return [
            'success' => true,
            'message' => 'Berhasil melakukan matching.',
        ];
    }

    /**
     * Unmatch item
     */
    public function unmatchItem(BankReconciliationItem $item): array
    {
        if (! $item->isMatched()) {
            return [
                'success' => false,
                'message' => 'Item belum di-match.',
            ];
        }

        $item->unmatch();

        return [
            'success' => true,
            'message' => 'Berhasil membatalkan matching.',
        ];
    }

    /**
     * Verify reconciliation dan update status pembayaran yang di-match
     */
    public function verify(BankReconciliation $reconciliation): array
    {
        if (! $reconciliation->canBeVerified()) {
            return [
                'success' => false,
                'message' => 'Rekonsiliasi belum dapat diverifikasi. Pastikan semua transaksi sudah di-match.',
            ];
        }

        DB::beginTransaction();

        try {
            // Verify pembayaran yang di-match (jika masih pending)
            $matchedItems = $reconciliation->matchedItems()->with('payment')->get();

            foreach ($matchedItems as $item) {
                if ($item->payment && $item->payment->status === 'pending') {
                    $item->payment->verify();
                }
            }

            // Verify reconciliation
            $reconciliation->verify();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Rekonsiliasi berhasil diverifikasi.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to verify reconciliation', [
                'reconciliation_id' => $reconciliation->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memverifikasi rekonsiliasi.',
            ];
        }
    }

    /**
     * Get unmatched payments untuk manual matching
     *
     * Mengembalikan semua pembayaran transfer yang belum di-match
     * dengan rekonsiliasi manapun dalam rentang tanggal yang ditentukan
     * atau 90 hari terakhir jika tidak ada rentang yang ditentukan
     */
    public function getUnmatchedPayments(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = Payment::query()
            ->where('metode_pembayaran', 'transfer')
            ->whereIn('status', ['pending', 'verified'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('bank_reconciliation_items')
                    ->whereColumn('bank_reconciliation_items.payment_id', 'payments.id');
            })
            ->with(['student.kelas', 'bill.paymentCategory']);

        // Jika tidak ada rentang tanggal, ambil 90 hari terakhir
        if (! $startDate && ! $endDate) {
            $query->whereDate('tanggal_bayar', '>=', now()->subDays(90));
        } else {
            if ($startDate) {
                $query->whereDate('tanggal_bayar', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('tanggal_bayar', '<=', $endDate);
            }
        }

        return $query->orderBy('tanggal_bayar', 'desc')->limit(100)->get();
    }

    /**
     * Format reconciliation untuk response
     */
    public function formatReconciliationForResponse(BankReconciliation $reconciliation): array
    {
        return [
            'id' => $reconciliation->id,
            'filename' => $reconciliation->original_filename,
            'bank_name' => $reconciliation->bank_name,
            'statement_date' => $reconciliation->statement_date?->format('d M Y'),
            'total_transactions' => $reconciliation->total_transactions,
            'total_amount' => (float) $reconciliation->total_amount,
            'formatted_total_amount' => $reconciliation->formatted_total_amount,
            'matched_count' => $reconciliation->matched_count,
            'matched_amount' => (float) $reconciliation->matched_amount,
            'formatted_matched_amount' => $reconciliation->formatted_matched_amount,
            'unmatched_count' => $reconciliation->unmatched_count,
            'match_rate' => $reconciliation->match_rate,
            'status' => $reconciliation->status,
            'status_label' => $reconciliation->status_label,
            'uploader' => $reconciliation->uploader ? [
                'id' => $reconciliation->uploader->id,
                'name' => $reconciliation->uploader->name,
            ] : null,
            'verifier' => $reconciliation->verifier ? [
                'id' => $reconciliation->verifier->id,
                'name' => $reconciliation->verifier->name,
            ] : null,
            'verified_at' => $reconciliation->verified_at?->format('d M Y H:i'),
            'created_at' => $reconciliation->created_at?->format('d M Y H:i'),
        ];
    }

    /**
     * Format item untuk response
     */
    public function formatItemForResponse(BankReconciliationItem $item): array
    {
        return [
            'id' => $item->id,
            'transaction_date' => $item->transaction_date?->format('d M Y'),
            'description' => $item->description,
            'amount' => (float) $item->amount,
            'formatted_amount' => $item->formatted_amount,
            'transaction_type' => $item->transaction_type,
            'transaction_type_label' => $item->transaction_type_label,
            'reference' => $item->reference,
            'match_type' => $item->match_type,
            'match_type_label' => $item->match_type_label,
            'match_confidence' => $item->match_confidence,
            'matched_at' => $item->matched_at?->format('d M Y H:i'),
            'payment' => $item->payment ? [
                'id' => $item->payment->id,
                'nomor_kwitansi' => $item->payment->nomor_kwitansi,
                'student_name' => $item->payment->student->nama_lengkap ?? '-',
                'nominal' => (float) $item->payment->nominal,
                'formatted_nominal' => $item->payment->formatted_nominal,
                'status' => $item->payment->status,
            ] : null,
        ];
    }
}
