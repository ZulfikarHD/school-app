<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\PsbDocument;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * PsbService - Service class untuk business logic PSB
 *
 * Service ini bertujuan untuk mengelola seluruh proses pendaftaran siswa baru,
 * yaitu: generate nomor pendaftaran, submit registrasi, upload dokumen,
 * tracking status, dan validasi periode pendaftaran
 */
class PsbService
{
    /**
     * Generate nomor pendaftaran dengan format PSB/{tahun}/{nomor_urut}
     * dimana nomor urut adalah 4 digit yang increment per tahun
     *
     * @return string Format: PSB/2025/0001
     */
    public function generateRegistrationNumber(): string
    {
        $year = now()->year;
        $prefix = "PSB/{$year}/";

        // Get last registration number untuk tahun ini
        $lastRegistration = PsbRegistration::where('registration_number', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(registration_number, -4) AS UNSIGNED) DESC')
            ->first();

        if ($lastRegistration) {
            // Extract nomor urut dan increment
            $lastNumber = (int) substr($lastRegistration->registration_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Submit registrasi baru dengan data lengkap siswa dan orang tua
     * serta upload dokumen-dokumen persyaratan
     *
     * @param  array  $data  Data pendaftaran dari form
     * @param  array<string, UploadedFile>  $documents  File dokumen yang diupload
     * @return PsbRegistration Registrasi yang berhasil dibuat
     *
     * @throws \Exception Jika periode pendaftaran tidak aktif
     */
    public function submitRegistration(array $data, array $documents = []): PsbRegistration
    {
        // Validasi periode pendaftaran
        if (! $this->isRegistrationOpen()) {
            throw new \Exception('Periode pendaftaran tidak aktif.');
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            throw new \Exception('Tahun ajaran aktif tidak ditemukan.');
        }

        return DB::transaction(function () use ($data, $documents, $activeYear) {
            // Generate nomor pendaftaran
            $registrationNumber = $this->generateRegistrationNumber();

            // Create registrasi
            $registration = PsbRegistration::create([
                'registration_number' => $registrationNumber,
                'academic_year_id' => $activeYear->id,
                'status' => PsbRegistration::STATUS_PENDING,
                // Data Siswa
                'student_name' => $data['student_name'],
                'student_nik' => $data['student_nik'],
                'birth_place' => $data['birth_place'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'religion' => $data['religion'],
                'address' => $data['address'],
                'child_order' => $data['child_order'],
                'origin_school' => $data['origin_school'] ?? null,
                // Data Ayah
                'father_name' => $data['father_name'],
                'father_nik' => $data['father_nik'],
                'father_occupation' => $data['father_occupation'],
                'father_phone' => $data['father_phone'],
                'father_email' => $data['father_email'] ?? null,
                // Data Ibu
                'mother_name' => $data['mother_name'],
                'mother_nik' => $data['mother_nik'],
                'mother_occupation' => $data['mother_occupation'],
                'mother_phone' => $data['mother_phone'] ?? null,
                'mother_email' => $data['mother_email'] ?? null,
                // Lainnya
                'notes' => $data['notes'] ?? null,
            ]);

            // Upload dokumen-dokumen
            foreach ($documents as $type => $file) {
                if ($file instanceof UploadedFile) {
                    $this->uploadDocument($registration, $file, $type);
                }
            }

            return $registration->load(['documents', 'academicYear']);
        });
    }

    /**
     * Upload dokumen persyaratan ke storage dengan naming convention
     * yang terstruktur untuk memudahkan pengelolaan
     *
     * @param  PsbRegistration  $registration  Registrasi terkait
     * @param  UploadedFile  $file  File yang diupload
     * @param  string  $type  Tipe dokumen (birth_certificate, family_card, etc)
     * @return PsbDocument Dokumen yang berhasil disimpan
     */
    public function uploadDocument(PsbRegistration $registration, UploadedFile $file, string $type): PsbDocument
    {
        // Generate path dengan struktur: psb/documents/{year}/{registration_id}/{type}_{timestamp}.{ext}
        $year = now()->year;
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension();
        $filename = "{$type}_{$timestamp}.{$extension}";
        $path = "psb/documents/{$year}/{$registration->id}";

        // Store file
        $filePath = $file->storeAs($path, $filename, 'public');

        // Create document record
        return PsbDocument::create([
            'psb_registration_id' => $registration->id,
            'document_type' => $type,
            'file_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'status' => PsbDocument::STATUS_PENDING,
        ]);
    }

    /**
     * Get registration status untuk tracking berdasarkan nomor pendaftaran
     * dengan informasi timeline dan dokumen terkait
     *
     * @param  string  $registrationNumber  Nomor pendaftaran
     * @return array|null Data status registration dengan timeline
     */
    public function getRegistrationStatus(string $registrationNumber): ?array
    {
        $registration = PsbRegistration::with(['documents', 'academicYear'])
            ->where('registration_number', $registrationNumber)
            ->first();

        if (! $registration) {
            return null;
        }

        return [
            'registration' => [
                'registration_number' => $registration->registration_number,
                'student_name' => $registration->student_name,
                'status' => $registration->status,
                'status_label' => $registration->getStatusLabel(),
                'rejection_reason' => $registration->rejection_reason,
                'created_at' => $registration->created_at->format('d M Y H:i'),
                'verified_at' => $registration->verified_at?->format('d M Y H:i'),
                'announced_at' => $registration->announced_at?->format('d M Y H:i'),
            ],
            'timeline' => $this->buildTimeline($registration),
            'documents' => $registration->documents->map(fn ($doc) => [
                'type' => $doc->document_type,
                'type_label' => $doc->type_label,
                'status' => $doc->status,
                'revision_note' => $doc->revision_note,
            ])->toArray(),
        ];
    }

    /**
     * Build timeline status pendaftaran untuk tracking visual
     *
     * @param  PsbRegistration  $registration  Data pendaftaran
     * @return array<int, array{step: string, label: string, completed: bool, current: bool, date: ?string}>
     */
    protected function buildTimeline(PsbRegistration $registration): array
    {
        $statuses = [
            PsbRegistration::STATUS_PENDING,
            PsbRegistration::STATUS_DOCUMENT_REVIEW,
            PsbRegistration::STATUS_APPROVED,
            PsbRegistration::STATUS_RE_REGISTRATION,
            PsbRegistration::STATUS_COMPLETED,
        ];

        $currentIndex = array_search($registration->status, $statuses);
        if ($currentIndex === false) {
            // Handle rejected atau waiting list
            if ($registration->status === PsbRegistration::STATUS_REJECTED) {
                $currentIndex = 2; // Stop di pengumuman
            } elseif ($registration->status === PsbRegistration::STATUS_WAITING_LIST) {
                $currentIndex = 2; // Stop di pengumuman
            }
        }

        $timeline = [
            [
                'step' => 'registered',
                'label' => 'Pendaftaran Diterima',
                'completed' => true,
                'current' => $registration->status === PsbRegistration::STATUS_PENDING,
                'date' => $registration->created_at?->format('d M Y'),
            ],
            [
                'step' => 'document_review',
                'label' => 'Verifikasi Dokumen',
                'completed' => $currentIndex >= 1,
                'current' => $registration->status === PsbRegistration::STATUS_DOCUMENT_REVIEW,
                'date' => null,
            ],
            [
                'step' => 'announcement',
                'label' => 'Pengumuman',
                'completed' => $currentIndex >= 2 || in_array($registration->status, [
                    PsbRegistration::STATUS_REJECTED,
                    PsbRegistration::STATUS_WAITING_LIST,
                ]),
                'current' => in_array($registration->status, [
                    PsbRegistration::STATUS_APPROVED,
                    PsbRegistration::STATUS_REJECTED,
                    PsbRegistration::STATUS_WAITING_LIST,
                ]),
                'date' => $registration->announced_at?->format('d M Y'),
            ],
            [
                'step' => 're_registration',
                'label' => 'Daftar Ulang',
                'completed' => $currentIndex >= 3,
                'current' => $registration->status === PsbRegistration::STATUS_RE_REGISTRATION,
                'date' => null,
            ],
            [
                'step' => 'completed',
                'label' => 'Selesai',
                'completed' => $registration->status === PsbRegistration::STATUS_COMPLETED,
                'current' => $registration->status === PsbRegistration::STATUS_COMPLETED,
                'date' => null,
            ],
        ];

        return $timeline;
    }

    /**
     * Check apakah periode pendaftaran sedang dibuka
     * berdasarkan tanggal saat ini dan konfigurasi PSB
     *
     * @return bool True jika pendaftaran sedang dibuka
     */
    public function isRegistrationOpen(): bool
    {
        $settings = $this->getActiveSettings();

        if (! $settings) {
            return false;
        }

        return $settings->isRegistrationOpen();
    }

    /**
     * Get konfigurasi PSB aktif untuk tahun ajaran yang sedang berjalan
     *
     * @return PsbSetting|null Konfigurasi PSB atau null jika tidak ada
     */
    public function getActiveSettings(): ?PsbSetting
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            return null;
        }

        return PsbSetting::where('academic_year_id', $activeYear->id)->first();
    }

    /**
     * Get data landing page PSB dengan informasi periode dan kuota
     *
     * @return array Data untuk landing page
     */
    public function getLandingPageData(): array
    {
        $settings = $this->getActiveSettings();
        $isOpen = $this->isRegistrationOpen();

        return [
            'settings' => $settings ? [
                'registration_open_date' => $settings->registration_open_date->format('d F Y'),
                'registration_close_date' => $settings->registration_close_date->format('d F Y'),
                'announcement_date' => $settings->announcement_date->format('d F Y'),
                'registration_fee' => $settings->registration_fee,
                'formatted_fee' => $settings->formatted_fee,
                'quota_per_class' => $settings->quota_per_class,
                'academic_year' => $settings->academicYear->name ?? null,
            ] : null,
            'isOpen' => $isOpen,
            'requiredDocuments' => PsbDocument::getDocumentTypes(),
        ];
    }

    /**
     * Get statistik pendaftaran untuk tahun ajaran aktif
     *
     * @return array Statistik jumlah pendaftar per status
     */
    public function getRegistrationStats(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'completed' => 0,
            ];
        }

        $stats = PsbRegistration::where('academic_year_id', $activeYear->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => array_sum($stats),
            'pending' => $stats[PsbRegistration::STATUS_PENDING] ?? 0,
            'document_review' => $stats[PsbRegistration::STATUS_DOCUMENT_REVIEW] ?? 0,
            'approved' => $stats[PsbRegistration::STATUS_APPROVED] ?? 0,
            'rejected' => $stats[PsbRegistration::STATUS_REJECTED] ?? 0,
            'waiting_list' => $stats[PsbRegistration::STATUS_WAITING_LIST] ?? 0,
            're_registration' => $stats[PsbRegistration::STATUS_RE_REGISTRATION] ?? 0,
            'completed' => $stats[PsbRegistration::STATUS_COMPLETED] ?? 0,
        ];
    }
}
