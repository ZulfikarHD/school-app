<?php

namespace App\Http\Controllers;

use App\Http\Requests\Psb\CheckStatusRequest;
use App\Http\Requests\Psb\StorePsbRegistrationRequest;
use App\Models\PsbRegistration;
use App\Services\PsbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PsbController - Controller untuk halaman publik PSB
 *
 * Controller ini bertujuan untuk mengelola halaman-halaman publik PSB,
 * yaitu: landing page, form pendaftaran, success page, dan tracking status
 */
class PsbController extends Controller
{
    public function __construct(
        protected PsbService $psbService
    ) {}

    /**
     * Landing page PSB dengan informasi periode pendaftaran
     * dan persyaratan dokumen yang diperlukan
     */
    public function landing(): Response
    {
        $data = $this->psbService->getLandingPageData();

        return Inertia::render('Psb/Landing', $data);
    }

    /**
     * Halaman form pendaftaran multi-step untuk calon siswa baru
     * dengan validasi apakah periode pendaftaran masih dibuka
     */
    public function create(): Response|RedirectResponse
    {
        // Redirect ke landing jika pendaftaran tutup
        if (! $this->psbService->isRegistrationOpen()) {
            return redirect()->route('psb.landing')
                ->with('error', 'Maaf, periode pendaftaran sudah ditutup.');
        }

        $settings = $this->psbService->getActiveSettings();

        return Inertia::render('Psb/Register', [
            'settings' => $settings ? [
                'registration_fee' => $settings->registration_fee,
                'formatted_fee' => $settings->formatted_fee,
                'academic_year' => $settings->academicYear->name ?? null,
            ] : null,
        ]);
    }

    /**
     * Simpan data pendaftaran baru dari form multi-step
     * dengan upload dokumen persyaratan
     */
    public function store(StorePsbRegistrationRequest $request): RedirectResponse
    {
        try {
            // Prepare documents array
            $documents = [];
            if ($request->hasFile('birth_certificate')) {
                $documents['birth_certificate'] = $request->file('birth_certificate');
            }
            if ($request->hasFile('family_card')) {
                $documents['family_card'] = $request->file('family_card');
            }
            if ($request->hasFile('parent_id')) {
                $documents['parent_id'] = $request->file('parent_id');
            }
            if ($request->hasFile('photo')) {
                $documents['photo'] = $request->file('photo');
            }

            $registration = $this->psbService->submitRegistration(
                $request->validated(),
                $documents
            );

            // Audit log untuk tracking registrasi baru
            Log::info('PSB Registration Submitted', [
                'registration_number' => $registration->registration_number,
                'student_name' => $registration->student_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('psb.success', $registration->id)
                ->with('success', 'Pendaftaran berhasil disimpan.');
        } catch (\Exception $e) {
            // Log error tanpa expose detail sensitive ke user
            Log::error('PSB Registration Failed', [
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            // Return generic error message untuk prevent information disclosure
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan pendaftaran. Silakan coba lagi atau hubungi administrator.',
            ]);
        }
    }

    /**
     * Halaman sukses setelah berhasil mendaftar
     * dengan informasi nomor pendaftaran untuk tracking
     *
     * Security: Menggunakan signed URL atau verifikasi bahwa registrasi
     * baru saja dibuat (dalam 5 menit terakhir) untuk prevent unauthorized access
     */
    public function success(Request $request, PsbRegistration $registration): Response|RedirectResponse
    {
        // Verifikasi bahwa registrasi dibuat dalam 5 menit terakhir
        // untuk prevent unauthorized access ke registrasi lama
        $isRecent = $registration->created_at->isAfter(now()->subMinutes(5));

        // Jika bukan registrasi baru, redirect ke tracking page
        // dengan message bahwa hanya bisa diakses setelah registrasi baru
        if (! $isRecent) {
            return redirect()->route('psb.tracking')
                ->with('info', 'Halaman sukses hanya dapat diakses setelah pendaftaran baru. Gunakan halaman tracking untuk melihat status.');
        }

        return Inertia::render('Psb/Success', [
            'registration' => [
                'registration_number' => $registration->registration_number,
                'student_name' => $registration->student_name,
                'created_at' => $registration->created_at->format('d F Y, H:i'),
            ],
        ]);
    }

    /**
     * Halaman tracking status pendaftaran
     * dengan form input nomor pendaftaran
     */
    public function tracking(): Response
    {
        return Inertia::render('Psb/Tracking', [
            'registration' => null,
        ]);
    }

    /**
     * Cek status pendaftaran berdasarkan nomor pendaftaran
     * dan return data timeline untuk ditampilkan
     */
    public function checkStatus(CheckStatusRequest $request): Response
    {
        $registrationNumber = $request->validated()['registration_number'];

        // Audit log untuk tracking attempts
        Log::info('PSB Status Check Attempt', [
            'registration_number' => $registrationNumber,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $status = $this->psbService->getRegistrationStatus($registrationNumber);

        if (! $status) {
            // Log failed lookup untuk monitoring brute force attempts
            Log::warning('PSB Status Check Failed - Registration Not Found', [
                'registration_number' => $registrationNumber,
                'ip_address' => $request->ip(),
            ]);

            return Inertia::render('Psb/Tracking', [
                'registration' => null,
                'error' => 'Nomor pendaftaran tidak ditemukan.',
            ]);
        }

        // Log successful lookup
        Log::info('PSB Status Check Success', [
            'registration_number' => $registrationNumber,
            'status' => $status['registration']['status'],
            'ip_address' => $request->ip(),
        ]);

        return Inertia::render('Psb/Tracking', [
            'registration' => $status['registration'],
            'timeline' => $status['timeline'],
            'documents' => $status['documents'],
        ]);
    }
}
