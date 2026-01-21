<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGradeWeightRequest;
use App\Models\ActivityLog;
use App\Models\GradeWeightConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * GradeWeightController - Controller untuk mengelola konfigurasi bobot nilai
 * yang mencakup pengaturan bobot UH, UTS, UAS, dan Praktik sesuai K13
 */
class GradeWeightController extends Controller
{
    /**
     * Menampilkan halaman konfigurasi bobot nilai dengan data
     * konfigurasi aktif dan history perubahan
     */
    public function index(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->getCurrentTahunAjaran());

        // Ambil atau buat default config untuk tahun ajaran
        $config = GradeWeightConfig::getOrCreateDefault($tahunAjaran);

        // Ambil list tahun ajaran yang tersedia
        $availableTahunAjaran = GradeWeightConfig::query()
            ->select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran')
            ->toArray();

        // Tambahkan tahun ajaran saat ini jika belum ada
        if (! in_array($tahunAjaran, $availableTahunAjaran)) {
            array_unshift($availableTahunAjaran, $tahunAjaran);
        }

        // Ambil history perubahan (dari activity log)
        $history = ActivityLog::query()
            ->where('model_type', GradeWeightConfig::class)
            ->whereIn('action', ['created', 'updated'])
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'user_name' => $log->user?->name ?? 'System',
                    'changes' => $log->changes,
                    'created_at' => $log->created_at->format('d M Y H:i'),
                ];
            });

        return Inertia::render('Admin/Settings/GradeWeights', [
            'config' => [
                'id' => $config->id,
                'tahun_ajaran' => $config->tahun_ajaran,
                'uh_weight' => $config->uh_weight,
                'uts_weight' => $config->uts_weight,
                'uas_weight' => $config->uas_weight,
                'praktik_weight' => $config->praktik_weight,
                'total_weight' => $config->total_weight,
                'is_valid' => $config->is_valid,
                'is_default' => $config->is_default,
                'updated_at' => $config->updated_at?->format('d M Y H:i'),
            ],
            'availableTahunAjaran' => $availableTahunAjaran,
            'currentTahunAjaran' => $tahunAjaran,
            'history' => $history,
            'defaultWeights' => [
                'uh' => GradeWeightConfig::DEFAULT_UH_WEIGHT,
                'uts' => GradeWeightConfig::DEFAULT_UTS_WEIGHT,
                'uas' => GradeWeightConfig::DEFAULT_UAS_WEIGHT,
                'praktik' => GradeWeightConfig::DEFAULT_PRAKTIK_WEIGHT,
            ],
        ]);
    }

    /**
     * Mengupdate konfigurasi bobot nilai dengan validasi total = 100%
     * dan mencatat perubahan ke activity log
     */
    public function update(UpdateGradeWeightRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Ambil atau buat config untuk tahun ajaran
            $config = GradeWeightConfig::firstOrNew([
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'is_default' => true,
            ]);

            $isNew = ! $config->exists;
            $oldValues = $config->only(['uh_weight', 'uts_weight', 'uas_weight', 'praktik_weight']);

            $config->fill([
                'uh_weight' => $validated['uh_weight'],
                'uts_weight' => $validated['uts_weight'],
                'uas_weight' => $validated['uas_weight'],
                'praktik_weight' => $validated['praktik_weight'],
                'subject_id' => null,
            ]);

            // Validasi total weight
            $config->validateTotalWeight();

            $config->save();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $isNew ? 'created' : 'updated',
                'model_type' => GradeWeightConfig::class,
                'model_id' => $config->id,
                'changes' => [
                    'old' => $isNew ? null : $oldValues,
                    'new' => [
                        'uh_weight' => $config->uh_weight,
                        'uts_weight' => $config->uts_weight,
                        'uas_weight' => $config->uas_weight,
                        'praktik_weight' => $config->praktik_weight,
                    ],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.settings.grade-weights.index', [
                    'tahun_ajaran' => $validated['tahun_ajaran'],
                ])
                ->with('success', 'Konfigurasi bobot nilai berhasil disimpan.');
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['total_weight' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Gagal menyimpan konfigurasi: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Helper: Mendapatkan tahun ajaran saat ini berdasarkan bulan
     * Juli-Desember = tahun/tahun+1, Januari-Juni = tahun-1/tahun
     */
    protected function getCurrentTahunAjaran(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        if ($month >= 7) {
            return $year.'/'.($year + 1);
        }

        return ($year - 1).'/'.$year;
    }
}
