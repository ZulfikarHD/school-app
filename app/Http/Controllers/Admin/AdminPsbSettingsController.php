<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psb\StorePsbSettingsRequest;
use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Services\PsbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AdminPsbSettingsController - Controller untuk mengelola pengaturan PSB
 *
 * Controller ini bertujuan untuk menyediakan fungsi admin dalam mengelola
 * konfigurasi PSB per tahun ajaran, yaitu: periode pendaftaran, pengumuman,
 * biaya, dan kuota penerimaan siswa baru
 */
class AdminPsbSettingsController extends Controller
{
    /**
     * Constructor untuk inject PsbService dependency
     * yang digunakan untuk business logic PSB
     */
    public function __construct(
        protected PsbService $psbService
    ) {}

    /**
     * Display settings page dengan current settings dan history
     * untuk overview konfigurasi PSB per tahun ajaran
     */
    public function index(): Response
    {
        // Get all academic years untuk dropdown
        $academicYears = AcademicYear::orderBy('name', 'desc')
            ->get(['id', 'name', 'is_active']);

        // Get active settings
        $activeSettings = $this->psbService->getActiveSettings();

        // Get all settings dengan academic year untuk history
        $settings = PsbSetting::with('academicYear')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($setting) {
                // Get registration count untuk setiap periode
                $registrationCount = PsbRegistration::where('academic_year_id', $setting->academic_year_id)
                    ->count();

                return [
                    'id' => $setting->id,
                    'academic_year_id' => $setting->academic_year_id,
                    'academic_year' => $setting->academicYear?->name,
                    'is_active' => $setting->academicYear?->is_active ?? false,
                    'registration_open_date' => $setting->registration_open_date->format('Y-m-d'),
                    'registration_close_date' => $setting->registration_close_date->format('Y-m-d'),
                    'announcement_date' => $setting->announcement_date->format('Y-m-d'),
                    're_registration_deadline_days' => $setting->re_registration_deadline_days,
                    'registration_fee' => $setting->registration_fee,
                    'formatted_fee' => $setting->getFormattedRegistrationFee(),
                    'quota_per_class' => $setting->quota_per_class,
                    'waiting_list_enabled' => $setting->waiting_list_enabled,
                    'is_registration_open' => $setting->isRegistrationOpen(),
                    'registration_count' => $registrationCount,
                    're_registration_deadline' => $setting->getReRegistrationDeadline()->format('Y-m-d'),
                    'created_at' => $setting->created_at->format('d M Y H:i'),
                ];
            });

        // Format active settings untuk frontend
        $activeSettingsData = null;
        if ($activeSettings) {
            $activeSettingsData = [
                'id' => $activeSettings->id,
                'academic_year_id' => $activeSettings->academic_year_id,
                'academic_year' => $activeSettings->academicYear?->name,
                'registration_open_date' => $activeSettings->registration_open_date->format('Y-m-d'),
                'registration_close_date' => $activeSettings->registration_close_date->format('Y-m-d'),
                'announcement_date' => $activeSettings->announcement_date->format('Y-m-d'),
                're_registration_deadline_days' => $activeSettings->re_registration_deadline_days,
                'registration_fee' => $activeSettings->registration_fee,
                'formatted_fee' => $activeSettings->getFormattedRegistrationFee(),
                'quota_per_class' => $activeSettings->quota_per_class,
                'waiting_list_enabled' => $activeSettings->waiting_list_enabled,
                'is_registration_open' => $activeSettings->isRegistrationOpen(),
                're_registration_deadline' => $activeSettings->getReRegistrationDeadline()->format('Y-m-d'),
            ];
        }

        return Inertia::render('Admin/Psb/Settings/Index', [
            'title' => 'Pengaturan PSB',
            'academicYears' => $academicYears,
            'activeSettings' => $activeSettingsData,
            'settings' => $settings,
        ]);
    }

    /**
     * Store new PSB settings untuk tahun ajaran yang dipilih
     * dengan validasi agar hanya ada satu setting per tahun ajaran
     */
    public function store(StorePsbSettingsRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Check apakah sudah ada setting untuk tahun ajaran ini
            $existingSetting = PsbSetting::where('academic_year_id', $validated['academic_year_id'])
                ->first();

            if ($existingSetting) {
                return back()->with('error', 'Pengaturan untuk tahun ajaran ini sudah ada. Silakan edit pengaturan yang sudah ada.');
            }

            // Create new setting
            PsbSetting::create([
                'academic_year_id' => $validated['academic_year_id'],
                'registration_open_date' => $validated['registration_open_date'],
                'registration_close_date' => $validated['registration_close_date'],
                'announcement_date' => $validated['announcement_date'],
                're_registration_deadline_days' => $validated['re_registration_deadline_days'],
                'registration_fee' => $validated['registration_fee'],
                'quota_per_class' => $validated['quota_per_class'],
                'waiting_list_enabled' => $validated['waiting_list_enabled'] ?? false,
            ]);

            return redirect()
                ->route('admin.psb.settings.index')
                ->with('success', 'Pengaturan PSB berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('Failed to create PSB settings', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menyimpan pengaturan. Silakan coba lagi.');
        }
    }

    /**
     * Update existing PSB settings
     * dengan validasi tanggal yang logical
     */
    public function update(StorePsbSettingsRequest $request, PsbSetting $setting): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Check apakah mengubah ke tahun ajaran yang sudah ada setting-nya
            if ($validated['academic_year_id'] !== $setting->academic_year_id) {
                $existingSetting = PsbSetting::where('academic_year_id', $validated['academic_year_id'])
                    ->where('id', '!=', $setting->id)
                    ->first();

                if ($existingSetting) {
                    return back()->with('error', 'Pengaturan untuk tahun ajaran ini sudah ada.');
                }
            }

            // Update setting
            $setting->update([
                'academic_year_id' => $validated['academic_year_id'],
                'registration_open_date' => $validated['registration_open_date'],
                'registration_close_date' => $validated['registration_close_date'],
                'announcement_date' => $validated['announcement_date'],
                're_registration_deadline_days' => $validated['re_registration_deadline_days'],
                'registration_fee' => $validated['registration_fee'],
                'quota_per_class' => $validated['quota_per_class'],
                'waiting_list_enabled' => $validated['waiting_list_enabled'] ?? false,
            ]);

            return redirect()
                ->route('admin.psb.settings.index')
                ->with('success', 'Pengaturan PSB berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Failed to update PSB settings', [
                'setting_id' => $setting->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal memperbarui pengaturan. Silakan coba lagi.');
        }
    }
}
