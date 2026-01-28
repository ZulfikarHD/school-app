<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Services\PsbService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PrincipalPsbController - Controller untuk PSB Dashboard Kepala Sekolah
 *
 * Controller ini bertujuan untuk menyediakan dashboard read-only
 * bagi kepala sekolah untuk memantau progress pendaftaran siswa baru
 */
class PrincipalPsbController extends Controller
{
    /**
     * Constructor untuk inject PsbService dependency
     * yang digunakan untuk mengambil data statistik PSB
     */
    public function __construct(
        protected PsbService $psbService
    ) {}

    /**
     * Display PSB Dashboard dengan summary stats dan chart data
     * untuk monitoring progress pendaftaran siswa baru
     */
    public function dashboard(): Response
    {
        // Get summary statistics
        $summary = $this->psbService->getRegistrationStats();

        // Get chart data
        $dailyRegistrations = $this->psbService->getDailyRegistrations(30);
        $genderDistribution = $this->psbService->getGenderDistribution();
        $statusDistribution = $this->psbService->getStatusDistribution();

        // Get active settings for period info
        $settings = $this->psbService->getActiveSettings();

        return Inertia::render('Principal/Psb/Dashboard', [
            'title' => 'Dashboard PSB',
            'summary' => $summary,
            'dailyRegistrations' => $dailyRegistrations,
            'genderDistribution' => $genderDistribution,
            'statusDistribution' => $statusDistribution,
            'periodInfo' => $settings ? [
                'academic_year' => $settings->academicYear?->name,
                'registration_open_date' => $settings->registration_open_date->format('d M Y'),
                'registration_close_date' => $settings->registration_close_date->format('d M Y'),
                'announcement_date' => $settings->announcement_date->format('d M Y'),
                'is_registration_open' => $settings->isRegistrationOpen(),
                'quota_per_class' => $settings->quota_per_class,
            ] : null,
        ]);
    }
}
