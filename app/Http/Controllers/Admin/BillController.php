<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateBillsRequest;
use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\PaymentCategory;
use App\Models\SchoolClass;
use App\Services\BillGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * Controller untuk mengelola tagihan pembayaran siswa
 *
 * Menyediakan fitur generate tagihan bulk, daftar tagihan,
 * dan pembatalan tagihan
 */
class BillController extends Controller
{
    public function __construct(
        protected BillGenerationService $billService
    ) {}

    /**
     * Display list of bills dengan pagination dan filter
     */
    public function index(Request $request)
    {
        $query = Bill::query()
            ->with(['student.kelas', 'paymentCategory', 'creator'])
            ->active();

        // Search by nomor tagihan atau nama siswa
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_tagihan', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by payment category
        if ($categoryId = $request->input('category_id')) {
            $query->where('payment_category_id', $categoryId);
        }

        // Filter by bulan
        if ($bulan = $request->input('bulan')) {
            $query->where('bulan', $bulan);
        }

        // Filter by tahun
        if ($tahun = $request->input('tahun')) {
            $query->where('tahun', $tahun);
        }

        // Filter by kelas
        if ($kelasId = $request->input('kelas_id')) {
            $query->whereHas('student', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        $bills = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get filter options
        $categories = PaymentCategory::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode']);

        $classes = SchoolClass::query()
            ->active()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'tingkat', 'nama']);

        // Summary statistics
        $stats = [
            'total_bills' => Bill::active()->count(),
            'unpaid' => Bill::active()->where('status', 'belum_bayar')->count(),
            'partial' => Bill::active()->where('status', 'sebagian')->count(),
            'paid' => Bill::active()->where('status', 'lunas')->count(),
            'total_nominal' => Bill::active()->sum('nominal'),
            'total_paid' => Bill::active()->sum('nominal_terbayar'),
            'total_outstanding' => Bill::active()->whereIn('status', ['belum_bayar', 'sebagian'])
                ->selectRaw('SUM(nominal - nominal_terbayar) as total')
                ->value('total') ?? 0,
        ];

        return Inertia::render('Admin/Payments/Bills/Index', [
            'bills' => $bills,
            'categories' => $categories,
            'classes' => $classes,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'category_id', 'bulan', 'tahun', 'kelas_id']),
        ]);
    }

    /**
     * Show form untuk generate tagihan
     */
    public function showGenerate()
    {
        $categories = $this->billService->getActiveCategories();
        $classes = $this->billService->getAvailableClasses();

        // Current month and year as defaults
        $currentMonth = (int) now()->format('m');
        $currentYear = (int) now()->format('Y');

        return Inertia::render('Admin/Payments/Bills/Generate', [
            'categories' => $categories,
            'classes' => $classes,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Preview tagihan sebelum generate (AJAX)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'payment_category_id' => 'required|exists:payment_categories,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $preview = $this->billService->preview(
            $request->payment_category_id,
            $request->bulan,
            $request->tahun,
            $request->class_ids
        );

        return response()->json($preview);
    }

    /**
     * Generate tagihan bulk
     */
    public function store(GenerateBillsRequest $request)
    {
        $result = $this->billService->generate(
            $request->payment_category_id,
            $request->bulan,
            $request->tahun,
            $request->class_ids,
            $request->boolean('skip_duplicates', true)
        );

        if ($result['success']) {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'generate_bills',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'payment_category_id' => $request->payment_category_id,
                    'bulan' => $request->bulan,
                    'tahun' => $request->tahun,
                    'generated_count' => $result['generated_count'],
                    'total_nominal' => $result['total_nominal'],
                ],
                'status' => 'success',
            ]);

            $message = "{$result['generated_count']} tagihan berhasil di-generate dengan total {$result['formatted_total']}.";
            if ($result['skipped_count'] > 0) {
                $message .= " ({$result['skipped_count']} tagihan duplikat dilewati)";
            }

            return redirect()->route('admin.payments.bills.index')
                ->with('success', $message);
        }

        return back()->withErrors(['error' => implode(', ', $result['errors'])])->withInput();
    }

    /**
     * Cancel/batalkan tagihan
     */
    public function destroy(Bill $bill)
    {
        // Validate bill can be cancelled
        if ($bill->status === 'lunas') {
            return back()->withErrors(['error' => 'Tagihan yang sudah lunas tidak dapat dibatalkan.']);
        }

        if ($bill->status === 'dibatalkan') {
            return back()->withErrors(['error' => 'Tagihan sudah dibatalkan sebelumnya.']);
        }

        // Check if bill has any payments
        $hasPayments = $bill->payments()->where('status', 'verified')->exists();
        if ($hasPayments) {
            return back()->withErrors(['error' => 'Tagihan yang sudah ada pembayaran tidak dapat dibatalkan.']);
        }

        try {
            $oldValues = [
                'nomor_tagihan' => $bill->nomor_tagihan,
                'student_id' => $bill->student_id,
                'status' => $bill->status,
            ];

            $bill->update(['status' => 'dibatalkan']);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'cancel_bill',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => $oldValues,
                'new_values' => ['status' => 'dibatalkan'],
                'status' => 'success',
            ]);

            return back()->with('success', "Tagihan {$bill->nomor_tagihan} berhasil dibatalkan.");
        } catch (\Exception $e) {
            Log::error('Failed to cancel bill', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal membatalkan tagihan.']);
        }
    }
}
