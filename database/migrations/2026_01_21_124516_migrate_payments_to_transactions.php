<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Data Migration untuk memindahkan data dari `payments` table
 * ke `payment_transactions` dan `payment_items` tables.
 *
 * Strategy: Group payments by bukti_transfer + tanggal_bayar + created_by
 * untuk membuat satu PaymentTransaction untuk multiple payments yang
 * di-submit bersamaan.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Memproses data existing payments dan memigrasikannya ke
     * payment_transactions dan payment_items dengan menjaga
     * integritas data dan backward compatibility.
     */
    public function up(): void
    {
        // Skip if no payments exist
        if (DB::table('payments')->count() === 0) {
            Log::info('No payments to migrate.');

            return;
        }

        Log::info('Starting payment data migration...');

        // Get all payments grouped by unique combination of
        // bukti_transfer + tanggal_bayar + created_by
        $paymentGroups = DB::table('payments')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function ($payment) {
                // Group payments that were submitted together
                // Same proof file, same date, same creator = same transaction
                $proofKey = $payment->bukti_transfer ?? 'no_proof_'.uniqid();
                $dateKey = $payment->tanggal_bayar ?? 'no_date';
                $creatorKey = $payment->created_by ?? 0;

                return "{$proofKey}|{$dateKey}|{$creatorKey}";
            });

        $migratedCount = 0;
        $transactionCount = 0;

        DB::beginTransaction();

        try {
            foreach ($paymentGroups as $groupKey => $payments) {
                // Get first payment for transaction-level data
                $firstPayment = $payments->first();

                // Calculate total amount from all payments in group
                $totalAmount = $payments->sum('nominal');

                // Determine guardian_id from payment creator if they are parent
                $guardianId = $this->getGuardianId($firstPayment);

                // Generate transaction number
                $transactionNumber = $this->generateTransactionNumber($firstPayment->created_at);

                // Create PaymentTransaction
                $transactionId = DB::table('payment_transactions')->insertGetId([
                    'transaction_number' => $transactionNumber,
                    'guardian_id' => $guardianId,
                    'total_amount' => $totalAmount,
                    'payment_method' => $firstPayment->metode_pembayaran,
                    'payment_date' => $firstPayment->tanggal_bayar,
                    'payment_time' => $firstPayment->waktu_bayar,
                    'proof_file' => $firstPayment->bukti_transfer,
                    'notes' => $firstPayment->keterangan ?? 'Migrated from legacy payments',
                    'status' => $firstPayment->status,
                    'verified_by' => $firstPayment->verified_by,
                    'verified_at' => $firstPayment->verified_at,
                    'cancelled_by' => $firstPayment->cancelled_by,
                    'cancelled_at' => $firstPayment->cancelled_at,
                    'cancellation_reason' => $firstPayment->cancellation_reason,
                    'created_by' => $firstPayment->created_by,
                    'updated_by' => $firstPayment->updated_by,
                    'created_at' => $firstPayment->created_at,
                    'updated_at' => $firstPayment->updated_at,
                ]);

                $transactionCount++;

                // Create PaymentItems for each payment in the group
                foreach ($payments as $payment) {
                    DB::table('payment_items')->insert([
                        'payment_transaction_id' => $transactionId,
                        'bill_id' => $payment->bill_id,
                        'student_id' => $payment->student_id,
                        'amount' => $payment->nominal,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ]);

                    $migratedCount++;
                }
            }

            DB::commit();

            Log::info('Payment migration completed successfully.', [
                'transactions_created' => $transactionCount,
                'payments_migrated' => $migratedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment migration failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * Menghapus data yang telah di-migrate dari payment_transactions
     * dan payment_items. Data asli di payments table tetap utuh.
     */
    public function down(): void
    {
        Log::info('Rolling back payment data migration...');

        // Delete all payment_items
        DB::table('payment_items')->truncate();

        // Delete all payment_transactions
        DB::table('payment_transactions')->truncate();

        Log::info('Payment data migration rolled back.');
    }

    /**
     * Generate transaction number based on creation date
     */
    protected function generateTransactionNumber(?string $createdAt): string
    {
        static $sequenceCache = [];

        $date = $createdAt ? \Carbon\Carbon::parse($createdAt) : now();
        $year = $date->format('Y');
        $month = $date->format('m');

        $cacheKey = "{$year}-{$month}";

        if (! isset($sequenceCache[$cacheKey])) {
            // Get max sequence for this month
            $lastTransaction = DB::table('payment_transactions')
                ->where('transaction_number', 'like', "TRX/{$year}/{$month}/%")
                ->orderBy('transaction_number', 'desc')
                ->first();

            if ($lastTransaction) {
                $lastSequence = (int) substr($lastTransaction->transaction_number, -5);
                $sequenceCache[$cacheKey] = $lastSequence;
            } else {
                $sequenceCache[$cacheKey] = 0;
            }
        }

        $sequenceCache[$cacheKey]++;

        return sprintf('TRX/%s/%s/%05d', $year, $month, $sequenceCache[$cacheKey]);
    }

    /**
     * Get guardian_id from payment creator if they are a parent
     */
    protected function getGuardianId($payment): ?int
    {
        if (! $payment->created_by) {
            return null;
        }

        // Check if the creator is a parent user with associated guardian
        $user = DB::table('users')
            ->where('id', $payment->created_by)
            ->where('role', 'PARENT')
            ->first();

        if (! $user) {
            return null;
        }

        // Get guardian associated with this user
        $guardian = DB::table('guardians')
            ->where('user_id', $user->id)
            ->first();

        return $guardian?->id;
    }
};
