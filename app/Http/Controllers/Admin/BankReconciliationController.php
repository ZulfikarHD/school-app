<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\Payment;
use App\Services\BankReconciliationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controller untuk mengelola rekonsiliasi bank (Admin/TU)
 *
 * Menyediakan fitur upload statement, auto-matching,
 * manual matching, dan verifikasi rekonsiliasi
 */
class BankReconciliationController extends Controller
{
    public function __construct(
        protected BankReconciliationService $reconciliationService
    ) {}

    /**
     * Display list of reconciliations
     */
    public function index(Request $request)
    {
        $query = BankReconciliation::query()
            ->with(['uploader', 'verifier'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $reconciliations = $query->paginate(20)->withQueryString();

        // Transform data
        $reconciliations->getCollection()->transform(function ($reconciliation) {
            return $this->reconciliationService->formatReconciliationForResponse($reconciliation);
        });

        return Inertia::render('Admin/Payments/Reconciliation/Index', [
            'reconciliations' => $reconciliations,
            'filters' => $request->only(['status', 'start_date', 'end_date']),
            'statusOptions' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'processing', 'label' => 'Sedang Diproses'],
                ['value' => 'completed', 'label' => 'Selesai'],
                ['value' => 'verified', 'label' => 'Terverifikasi'],
            ],
        ]);
    }

    /**
     * Upload bank statement file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'bank_name' => 'nullable|string|max:100',
            'statement_date' => 'nullable|date',
        ], [
            'file.required' => 'File statement harus diupload.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $result = $this->reconciliationService->uploadStatement(
            $request->file('file'),
            [
                'bank_name' => $request->input('bank_name'),
                'statement_date' => $request->input('statement_date'),
            ]
        );

        if ($result['success']) {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'upload_bank_statement',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'reconciliation_id' => $result['reconciliation']->id,
                    'filename' => $result['reconciliation']->original_filename,
                    'total_transactions' => $result['reconciliation']->total_transactions,
                ],
                'status' => 'success',
            ]);

            return redirect()->route('admin.payments.reconciliation.match', $result['reconciliation'])
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Show matching page for reconciliation
     */
    public function showMatch(BankReconciliation $reconciliation)
    {
        $reconciliation->load(['items.payment.student', 'uploader']);

        $items = $reconciliation->items()
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn ($item) => $this->reconciliationService->formatItemForResponse($item));

        // Get unmatched payments untuk manual matching
        $unmatchedPayments = $this->reconciliationService->getUnmatchedPayments(
            $reconciliation->statement_start_date,
            $reconciliation->statement_end_date
        )->map(fn ($payment) => [
            'id' => $payment->id,
            'nomor_kwitansi' => $payment->nomor_kwitansi,
            'student_name' => $payment->student->nama_lengkap ?? '-',
            'student_nis' => $payment->student->nis ?? '-',
            'kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
            'category' => $payment->bill->paymentCategory->nama ?? '-',
            'periode' => $payment->bill->nama_bulan.' '.$payment->bill->tahun,
            'nominal' => (float) $payment->nominal,
            'formatted_nominal' => $payment->formatted_nominal,
            'tanggal_bayar' => $payment->tanggal_bayar->format('d M Y'),
            'status' => $payment->status,
        ]);

        return Inertia::render('Admin/Payments/Reconciliation/Match', [
            'reconciliation' => $this->reconciliationService->formatReconciliationForResponse($reconciliation),
            'items' => $items,
            'unmatchedPayments' => $unmatchedPayments,
            'canVerify' => $reconciliation->canBeVerified(),
        ]);
    }

    /**
     * Run auto-matching
     */
    public function autoMatch(BankReconciliation $reconciliation, Request $request)
    {
        $result = $this->reconciliationService->runAutoMatch($reconciliation);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'auto_match_reconciliation',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'reconciliation_id' => $reconciliation->id,
                'matched' => $result['matched'],
                'total' => $result['total'],
            ],
            'status' => 'success',
        ]);

        return back()->with('success', "Auto-matching selesai. {$result['matched']} dari {$result['total']} transaksi berhasil di-match.");
    }

    /**
     * Manual match item dengan payment
     */
    public function storeMatch(Request $request, BankReconciliation $reconciliation)
    {
        $request->validate([
            'item_id' => 'required|exists:bank_reconciliation_items,id',
            'payment_id' => 'required|exists:payments,id',
        ]);

        $item = BankReconciliationItem::findOrFail($request->item_id);
        $payment = Payment::findOrFail($request->payment_id);

        // Verify item belongs to this reconciliation
        if ($item->reconciliation_id !== $reconciliation->id) {
            return back()->withErrors(['error' => 'Item tidak valid untuk rekonsiliasi ini.']);
        }

        $result = $this->reconciliationService->manualMatch($item, $payment);

        if ($result['success']) {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'manual_match_reconciliation',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'reconciliation_id' => $reconciliation->id,
                    'item_id' => $item->id,
                    'payment_id' => $payment->id,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Unmatch item
     */
    public function unmatch(Request $request, BankReconciliation $reconciliation, BankReconciliationItem $item)
    {
        // Verify item belongs to this reconciliation
        if ($item->reconciliation_id !== $reconciliation->id) {
            return back()->withErrors(['error' => 'Item tidak valid untuk rekonsiliasi ini.']);
        }

        $result = $this->reconciliationService->unmatchItem($item);

        if ($result['success']) {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'unmatch_reconciliation_item',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => [
                    'reconciliation_id' => $reconciliation->id,
                    'item_id' => $item->id,
                    'payment_id' => $item->payment_id,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Verify reconciliation
     */
    public function verify(BankReconciliation $reconciliation, Request $request)
    {
        $result = $this->reconciliationService->verify($reconciliation);

        if ($result['success']) {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'verify_reconciliation',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'reconciliation_id' => $reconciliation->id,
                    'matched_count' => $reconciliation->matched_count,
                    'matched_amount' => $reconciliation->matched_amount,
                ],
                'status' => 'success',
            ]);

            return redirect()->route('admin.payments.reconciliation.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Export reconciliation results
     */
    public function export(BankReconciliation $reconciliation)
    {
        $reconciliation->load(['items.payment.student', 'uploader', 'verifier']);

        $exportData = $reconciliation->items->map(function ($item) {
            return [
                'Tanggal Transaksi' => $item->transaction_date?->format('d/m/Y'),
                'Deskripsi' => $item->description,
                'Referensi' => $item->reference,
                'Nominal' => $item->amount,
                'Tipe' => $item->transaction_type_label,
                'Status Match' => $item->match_type_label,
                'No Kwitansi' => $item->payment?->nomor_kwitansi ?? '-',
                'Nama Siswa' => $item->payment?->student?->nama_lengkap ?? '-',
                'Confidence' => $item->match_confidence ? $item->match_confidence.'%' : '-',
            ];
        });

        $filename = "Rekonsiliasi-{$reconciliation->id}-{$reconciliation->created_at->format('Ymd')}.csv";

        return response()->streamDownload(function () use ($exportData) {
            $output = fopen('php://output', 'w');
            if ($exportData->isNotEmpty()) {
                fputcsv($output, array_keys($exportData->first()));
                foreach ($exportData as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Delete reconciliation (only draft status)
     */
    public function destroy(BankReconciliation $reconciliation, Request $request)
    {
        if ($reconciliation->status !== BankReconciliation::STATUS_DRAFT) {
            return back()->withErrors(['error' => 'Hanya rekonsiliasi draft yang dapat dihapus.']);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_reconciliation',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => [
                'reconciliation_id' => $reconciliation->id,
                'filename' => $reconciliation->original_filename,
            ],
            'status' => 'success',
        ]);

        $reconciliation->delete();

        return redirect()->route('admin.payments.reconciliation.index')
            ->with('success', 'Rekonsiliasi berhasil dihapus.');
    }
}
