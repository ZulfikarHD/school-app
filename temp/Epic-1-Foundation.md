# Epic 1: Foundation & Public Registration

## Sprint 1 - Week 1

**Goal:** Membangun fondasi database dan fitur pendaftaran publik untuk calon siswa baru.

---

## Backend Developer Tasks

### Task 1.1: Database Migrations (P0)
**Story Points:** 5

Buat migration untuk semua tabel PSB:

```sql
-- psb_settings
id, academic_year_id, registration_open_date, registration_close_date,
announcement_date, re_registration_deadline_days, registration_fee,
quota_per_class, waiting_list_enabled, created_at, updated_at

-- psb_registrations  
id, registration_number, academic_year_id, status, student_name, student_nik,
birth_place, birth_date, gender, religion, address, child_order,
origin_school, father_name, father_nik, father_occupation, father_phone,
father_email, mother_name, mother_nik, mother_occupation, mother_phone,
mother_email, notes, rejection_reason, verified_by, verified_at,
announced_at, created_at, updated_at

-- psb_documents
id, psb_registration_id, document_type, file_path, original_name,
status, revision_note, created_at, updated_at

-- psb_payments
id, psb_registration_id, payment_type, amount, payment_method,
proof_file_path, status, verified_by, verified_at, notes, created_at, updated_at
```

**Acceptance Criteria:**
- [ ] Migration untuk `psb_settings` dengan relasi ke `academic_years`
- [ ] Migration untuk `psb_registrations` dengan status enum
- [ ] Migration untuk `psb_documents` dengan document_type enum
- [ ] Migration untuk `psb_payments` dengan payment status enum
- [ ] Semua foreign key constraints terpasang dengan benar
- [ ] Jalankan `php artisan migrate` tanpa error

**Commands:**
```bash
php artisan make:migration create_psb_settings_table
php artisan make:migration create_psb_registrations_table
php artisan make:migration create_psb_documents_table
php artisan make:migration create_psb_payments_table
```

---

### Task 1.2: Eloquent Models (P0)
**Story Points:** 5

Buat models dengan relationships:

**PsbSetting Model:**
- Relasi: belongsTo AcademicYear
- Casts: dates, boolean untuk waiting_list_enabled

**PsbRegistration Model:**
- Relasi: hasMany PsbDocument, hasMany PsbPayment, belongsTo User (verified_by)
- Casts: dates untuk birth_date, verified_at, announced_at
- Status enum: pending, document_review, approved, rejected, waiting_list, re_registration, completed

**PsbDocument Model:**
- Relasi: belongsTo PsbRegistration
- Document type enum: birth_certificate, family_card, parent_id, photo, other

**PsbPayment Model:**
- Relasi: belongsTo PsbRegistration, belongsTo User (verified_by)

**Acceptance Criteria:**
- [ ] Semua model memiliki `$fillable` yang sesuai
- [ ] Semua relasi terdefinisi dengan return type
- [ ] Factory dibuat untuk testing
- [ ] Model memiliki casts method yang proper

**Commands:**
```bash
php artisan make:model PsbSetting -f
php artisan make:model PsbRegistration -f
php artisan make:model PsbDocument -f
php artisan make:model PsbPayment -f
```

---

### Task 1.3: PsbService (P0)
**Story Points:** 5

Buat service class untuk business logic:

```php
class PsbService
{
    // Generate nomor pendaftaran: PSB/2025/0001
    public function generateRegistrationNumber(): string;
    
    // Submit registrasi baru
    public function submitRegistration(array $data): PsbRegistration;
    
    // Upload dokumen
    public function uploadDocument(PsbRegistration $registration, UploadedFile $file, string $type): PsbDocument;
    
    // Get registration status untuk tracking
    public function getRegistrationStatus(string $registrationNumber): ?array;
    
    // Check apakah periode pendaftaran aktif
    public function isRegistrationOpen(): bool;
    
    // Get active PSB settings
    public function getActiveSettings(): ?PsbSetting;
}
```

**Acceptance Criteria:**
- [ ] Registration number unique dan sequential per tahun
- [ ] Upload document ke storage dengan proper naming
- [ ] Status tracking return timeline data
- [ ] Registration period check based on dates

---

### Task 1.4: Public Registration Controller & Routes (P0)
**Story Points:** 5

**Routes (routes/web.php):**
```php
Route::prefix('psb')->name('psb.')->group(function () {
    Route::get('/', [PsbController::class, 'landing'])->name('landing');
    Route::get('/register', [PsbController::class, 'create'])->name('register');
    Route::post('/register', [PsbController::class, 'store'])->name('store');
    Route::get('/success/{registration}', [PsbController::class, 'success'])->name('success');
    Route::get('/tracking', [PsbController::class, 'tracking'])->name('tracking');
    Route::post('/tracking', [PsbController::class, 'checkStatus'])->name('check-status');
});
```

**Controller Methods:**
- `landing()` - Halaman info PSB
- `create()` - Form pendaftaran multi-step
- `store()` - Simpan pendaftaran
- `success()` - Halaman sukses dengan nomor registrasi
- `tracking()` - Halaman cek status
- `checkStatus()` - API cek status

**Form Request:**
- `StorePsbRegistrationRequest` dengan validasi lengkap

**Acceptance Criteria:**
- [ ] Semua routes terdaftar dan bisa diakses
- [ ] Form validation lengkap dengan error messages
- [ ] File upload untuk dokumen berfungsi
- [ ] Redirect ke success page setelah submit
- [ ] Tracking bisa cek status dengan nomor registrasi

---

## Frontend Developer Tasks

### Task 1.5: PSB Landing Page (P0)
**Story Points:** 3

**File:** `resources/js/pages/Psb/Landing.vue`

**Requirements:**
- Hero section dengan info PSB
- Timeline pendaftaran (buka, tutup, pengumuman)
- Syarat & ketentuan
- Dokumen yang diperlukan
- CTA button "Daftar Sekarang"
- Responsive design (mobile-first)

**Props dari Controller:**
```typescript
interface Props {
    settings: {
        registration_open_date: string;
        registration_close_date: string;
        announcement_date: string;
        registration_fee: number;
        quota_per_class: number;
    } | null;
    isOpen: boolean;
}
```

**Acceptance Criteria:**
- [ ] Tampilan info periode pendaftaran
- [ ] List dokumen yang diperlukan
- [ ] Button disabled jika pendaftaran ditutup
- [ ] Mobile responsive
- [ ] iOS-like design dengan Motion-v animations

---

### Task 1.6: Multi-Step Registration Form (P0)
**Story Points:** 8

**File:** `resources/js/pages/Psb/Register.vue`

**Steps:**
1. **Step 1: Data Calon Siswa**
   - Nama lengkap, NIK, tempat lahir, tanggal lahir
   - Jenis kelamin, agama, alamat
   - Anak ke-, asal sekolah

2. **Step 2: Data Orang Tua**
   - Data Ayah: nama, NIK, pekerjaan, telepon, email
   - Data Ibu: nama, NIK, pekerjaan, telepon, email

3. **Step 3: Upload Dokumen**
   - Akte kelahiran (required)
   - Kartu keluarga (required)
   - KTP orang tua (required)
   - Foto 3x4 (required)

4. **Step 4: Review & Submit**
   - Summary semua data
   - Checkbox persetujuan
   - Submit button

**Components Needed:**
- `PsbMultiStepForm.vue` - Form wrapper dengan progress indicator
- `PsbStepIndicator.vue` - Progress stepper

**Acceptance Criteria:**
- [ ] Step navigation (next/prev) berfungsi
- [ ] Validation per step sebelum next
- [ ] File upload dengan preview
- [ ] Review semua data sebelum submit
- [ ] Loading state saat submit
- [ ] Error handling dengan toast

---

### Task 1.7: Success Page (P0)
**Story Points:** 2

**File:** `resources/js/pages/Psb/Success.vue`

**Requirements:**
- Tampilkan nomor pendaftaran dengan style yang prominent
- "Copy to clipboard" untuk nomor registrasi
- Next steps information
- Link ke halaman tracking
- Print/Save option

**Props:**
```typescript
interface Props {
    registration: {
        registration_number: string;
        student_name: string;
        created_at: string;
    };
}
```

**Acceptance Criteria:**
- [ ] Nomor registrasi tampil jelas
- [ ] Copy button berfungsi
- [ ] Link ke tracking page
- [ ] Konfirmasi animasi (checkmark)

---

### Task 1.8: Status Tracking Page (P0)
**Story Points:** 5

**File:** `resources/js/pages/Psb/Tracking.vue`

**Requirements:**
- Form input nomor pendaftaran
- Timeline UI menampilkan progress:
  - Pendaftaran diterima
  - Verifikasi dokumen
  - Pengumuman
  - Daftar ulang
  - Selesai
- Status badges (pending, approved, rejected, etc)
- Notes/rejection reason jika ada

**Components:**
- `PsbTimeline.vue` - Timeline stepper component

**Acceptance Criteria:**
- [ ] Search by registration number
- [ ] Timeline menampilkan status correctly
- [ ] Different states (pending, approved, rejected) dengan warna berbeda
- [ ] Rejection reason tampil jika ditolak
- [ ] Mobile responsive

---

## Sprint 1 Summary

| Role | Tasks | Total Story Points |
|------|-------|-------------------|
| Backend | 1.1, 1.2, 1.3, 1.4 | 20 |
| Frontend | 1.5, 1.6, 1.7, 1.8 | 18 |

**Definition of Done:**
- [ ] Semua migrations berjalan
- [ ] Models dengan factory bisa generate data
- [ ] Public bisa submit registration
- [ ] Public bisa track status
- [ ] Unit tests untuk PsbService
- [ ] Feature tests untuk registration flow
