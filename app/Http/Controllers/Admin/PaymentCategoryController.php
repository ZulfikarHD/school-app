<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentCategoryRequest;
use App\Http\Requests\Admin\UpdatePaymentCategoryRequest;
use App\Models\ActivityLog;
use App\Models\PaymentCategory;
use App\Models\PaymentCategoryClassPrice;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * Controller untuk mengelola kategori pembayaran sekolah
 *
 * Menyediakan CRUD operations untuk kategori pembayaran seperti SPP, Uang Gedung,
 * Seragam, Kegiatan, dan Donasi dengan support harga per kelas
 */
class PaymentCategoryController extends Controller
{
    /**
     * Display list of payment categories dengan pagination dan filter
     */
    public function index(Request $request)
    {
        $query = PaymentCategory::query()->with(['creator', 'classPrices.schoolClass']);

        // Search by nama atau kode
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        // Filter by tipe
        if ($tipe = $request->input('tipe')) {
            $query->where('tipe', $tipe);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by tahun ajaran
        if ($tahunAjaran = $request->input('tahun_ajaran')) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }

        $categories = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Payments/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'tipe', 'is_active', 'tahun_ajaran']),
        ]);
    }

    /**
     * Show form untuk create kategori baru
     */
    public function create()
    {
        $classes = SchoolClass::query()
            ->active()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'tingkat', 'nama', 'tahun_ajaran']);

        return Inertia::render('Admin/Payments/Categories/Create', [
            'classes' => $classes,
        ]);
    }

    /**
     * Store kategori pembayaran baru dengan optional class-specific pricing
     */
    public function store(StorePaymentCategoryRequest $request)
    {
        DB::beginTransaction();

        try {
            // Create payment category
            $category = PaymentCategory::create([
                'nama' => $request->nama,
                'kode' => strtoupper($request->kode),
                'deskripsi' => $request->deskripsi,
                'tipe' => $request->tipe,
                'nominal_default' => $request->nominal_default,
                'is_active' => $request->boolean('is_active', true),
                'is_mandatory' => $request->boolean('is_mandatory', true),
                'due_day' => $request->due_day,
                'tahun_ajaran' => $request->tahun_ajaran,
            ]);

            // Save class-specific prices if provided
            if ($request->has('class_prices') && is_array($request->class_prices)) {
                foreach ($request->class_prices as $classPrice) {
                    if (! empty($classPrice['class_id']) && isset($classPrice['nominal'])) {
                        PaymentCategoryClassPrice::create([
                            'payment_category_id' => $category->id,
                            'class_id' => $classPrice['class_id'],
                            'nominal' => $classPrice['nominal'],
                        ]);
                    }
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_payment_category',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'category_id' => $category->id,
                    'nama' => $category->nama,
                    'kode' => $category->kode,
                    'tipe' => $category->tipe,
                    'nominal_default' => $category->nominal_default,
                ],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()->route('admin.payment-categories.index')
                ->with('success', "Kategori pembayaran '{$category->nama}' berhasil ditambahkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create payment category', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal menambahkan kategori pembayaran. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Display detail kategori pembayaran
     */
    public function show(PaymentCategory $paymentCategory)
    {
        $paymentCategory->load(['creator', 'updater', 'classPrices.schoolClass']);

        // Get usage statistics
        $stats = [
            'total_bills' => $paymentCategory->bills()->count(),
            'total_nominal' => $paymentCategory->bills()->sum('nominal'),
            'total_paid' => $paymentCategory->bills()->where('status', 'lunas')->count(),
        ];

        return Inertia::render('Admin/Payments/Categories/Show', [
            'category' => $paymentCategory,
            'stats' => $stats,
        ]);
    }

    /**
     * Show form untuk edit kategori
     */
    public function edit(PaymentCategory $paymentCategory)
    {
        $paymentCategory->load('classPrices');

        $classes = SchoolClass::query()
            ->active()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'tingkat', 'nama', 'tahun_ajaran']);

        return Inertia::render('Admin/Payments/Categories/Edit', [
            'category' => $paymentCategory,
            'classes' => $classes,
        ]);
    }

    /**
     * Update kategori pembayaran
     */
    public function update(UpdatePaymentCategoryRequest $request, PaymentCategory $paymentCategory)
    {
        DB::beginTransaction();

        try {
            $oldValues = [
                'nama' => $paymentCategory->nama,
                'kode' => $paymentCategory->kode,
                'nominal_default' => $paymentCategory->nominal_default,
                'is_active' => $paymentCategory->is_active,
            ];

            // Update payment category
            $paymentCategory->update([
                'nama' => $request->nama,
                'kode' => strtoupper($request->kode),
                'deskripsi' => $request->deskripsi,
                'tipe' => $request->tipe,
                'nominal_default' => $request->nominal_default,
                'is_active' => $request->boolean('is_active', true),
                'is_mandatory' => $request->boolean('is_mandatory', true),
                'due_day' => $request->due_day,
                'tahun_ajaran' => $request->tahun_ajaran,
            ]);

            // Update class-specific prices
            if ($request->has('class_prices')) {
                // Delete existing prices and recreate
                $paymentCategory->classPrices()->delete();

                if (is_array($request->class_prices)) {
                    foreach ($request->class_prices as $classPrice) {
                        if (! empty($classPrice['class_id']) && isset($classPrice['nominal'])) {
                            PaymentCategoryClassPrice::create([
                                'payment_category_id' => $paymentCategory->id,
                                'class_id' => $classPrice['class_id'],
                                'nominal' => $classPrice['nominal'],
                            ]);
                        }
                    }
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_payment_category',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => [
                    'category_id' => $paymentCategory->id,
                    'nama' => $paymentCategory->nama,
                    'kode' => $paymentCategory->kode,
                    'nominal_default' => $paymentCategory->nominal_default,
                    'is_active' => $paymentCategory->is_active,
                ],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()->route('admin.payment-categories.index')
                ->with('success', "Kategori pembayaran '{$paymentCategory->nama}' berhasil diupdate.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update payment category', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal mengupdate kategori pembayaran. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Soft delete kategori pembayaran
     */
    public function destroy(PaymentCategory $paymentCategory)
    {
        DB::beginTransaction();

        try {
            // Check if category has bills
            $billCount = $paymentCategory->bills()->count();
            if ($billCount > 0) {
                return back()->withErrors([
                    'error' => "Kategori ini memiliki {$billCount} tagihan terkait. Nonaktifkan kategori sebagai gantinya.",
                ]);
            }

            // Log activity before delete
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_payment_category',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => [
                    'category_id' => $paymentCategory->id,
                    'nama' => $paymentCategory->nama,
                    'kode' => $paymentCategory->kode,
                ],
                'status' => 'success',
            ]);

            // Soft delete
            $paymentCategory->delete();

            DB::commit();

            return redirect()->route('admin.payment-categories.index')
                ->with('success', "Kategori pembayaran '{$paymentCategory->nama}' berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete payment category', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal menghapus kategori pembayaran.']);
        }
    }

    /**
     * Toggle status aktif kategori
     */
    public function toggleStatus(PaymentCategory $paymentCategory)
    {
        try {
            $paymentCategory->is_active = ! $paymentCategory->is_active;
            $paymentCategory->save();

            $status = $paymentCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'toggle_payment_category_status',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'new_values' => [
                    'category_id' => $paymentCategory->id,
                    'is_active' => $paymentCategory->is_active,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', "Kategori pembayaran '{$paymentCategory->nama}' berhasil {$status}.");
        } catch (\Exception $e) {
            Log::error('Failed to toggle payment category status', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal mengubah status kategori pembayaran.']);
        }
    }
}
