# Manual QA Test Plan: PSB (Penerimaan Siswa Baru)

Dokumen ini berisi panduan testing manual untuk fitur PSB.

---

## Test Environment

| Item | Detail |
|------|--------|
| URL | `http://localhost:8000` |
| Browser | Chrome (latest), Firefox (latest), Safari |
| Device | Desktop (1920x1080), Tablet (768px), Mobile (375px) |
| Test Account Admin | Role: ADMIN atau SUPERADMIN |

### Pre-requisites

- [ ] Database sudah di-migrate
- [ ] Academic year aktif ada di database
- [ ] PSB Setting dengan periode pendaftaran terbuka
- [ ] Storage link sudah dibuat (`php artisan storage:link`)

---

## Test Suite 1: Public PSB - Landing Page

### TC-1.1: Landing Page Accessible
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka browser, akses `/psb` | Halaman landing PSB tampil |
| 2 | Scroll ke bawah | Semua section tampil: Hero, Timeline, Persyaratan, Keunggulan |
| 3 | Periksa responsive (resize ke 375px) | Layout mobile-friendly, tidak ada horizontal scroll |

### TC-1.2: Landing Page Shows Registration Info
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Pastikan PSB Setting aktif di database | - |
| 2 | Akses `/psb` | Tanggal buka, tutup, pengumuman tampil |
| 3 | Periksa status pendaftaran | Badge "Pendaftaran Dibuka" tampil (jika dalam periode) |

### TC-1.3: Landing Page - Registration Closed
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Set PSB Setting: `registration_close_date` = kemarin | - |
| 2 | Akses `/psb` | Badge "Pendaftaran Ditutup" tampil |
| 3 | Klik "Daftar Sekarang" | Redirect ke landing dengan pesan error |

---

## Test Suite 2: Public PSB - Registration Form

### TC-2.1: Multi-Step Form Navigation
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Akses `/psb/register` | Form Step 1 tampil |
| 2 | Isi semua field Step 1 dengan valid | Tombol "Lanjut" aktif |
| 3 | Klik "Lanjut" | Pindah ke Step 2 dengan animasi |
| 4 | Klik "Kembali" | Kembali ke Step 1, data tetap terisi |
| 5 | Navigate sampai Step 4 | Step indicator update sesuai posisi |

### TC-2.2: Step 1 - Data Siswa Validation
**Priority:** High

| Field | Test Input | Expected |
|-------|------------|----------|
| Nama Lengkap | "" (kosong) | Error: "Nama lengkap wajib diisi" |
| Nama Lengkap | "AB" | Error: "minimal 3 karakter" |
| NIK | "12345" | Error: "NIK harus 16 digit" |
| NIK | "1234567890123456" | Valid ✓ |
| Tempat Lahir | "" | Error: "Tempat lahir wajib diisi" |
| Tanggal Lahir | tanggal besok | Error: "tidak valid" |
| Tanggal Lahir | > 10 tahun lalu | Error: "Usia maksimal 10 tahun" |
| Jenis Kelamin | tidak dipilih | Error: "wajib dipilih" |
| Agama | tidak dipilih | Error: "wajib dipilih" |
| Alamat | "Jl. A" (< 10 char) | Error: "minimal 10 karakter" |
| Anak Ke- | 0 | Error: "minimal 1" |

### TC-2.3: Step 2 - Data Orang Tua Validation
**Priority:** High

| Field | Test Input | Expected |
|-------|------------|----------|
| Nama Ayah | "" | Error: "wajib diisi" |
| NIK Ayah | "123" | Error: "harus 16 digit" |
| Pekerjaan Ayah | "" | Error: "wajib diisi" |
| No HP Ayah | "08123" (< 10 digit) | Error: "minimal 10 digit" |
| Email Ayah | "invalid-email" | Error: "format tidak valid" |
| Nama Ibu | "" | Error: "wajib diisi" |
| NIK Ibu | "123" | Error: "harus 16 digit" |
| Pekerjaan Ibu | "" | Error: "wajib diisi" |

### TC-2.4: Step 3 - Document Upload Validation
**Priority:** High

| Test Case | Action | Expected |
|-----------|--------|----------|
| Upload valid PDF | Upload akte.pdf (1MB) | Preview tampil, checkmark hijau |
| Upload valid JPG | Upload foto.jpg (500KB) | Preview dengan thumbnail |
| File too large | Upload file 6MB | Error: "maksimal 5MB" |
| Wrong format | Upload file.doc | Error: "harus PDF, JPG, atau PNG" |
| Photo too large | Upload foto 3MB | Error: "maksimal 2MB" |
| Remove file | Klik X pada preview | File dihapus, upload button kembali |

### TC-2.5: Step 4 - Review & Submit
**Priority:** Critical

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Isi semua data valid sampai Step 4 | Review page menampilkan semua data |
| 2 | Periksa data siswa | Data sesuai dengan yang diinput |
| 3 | Periksa data orang tua | Data sesuai dengan yang diinput |
| 4 | Periksa daftar dokumen | 4 dokumen tampil dengan nama file |
| 5 | Klik "Kirim Pendaftaran" | Loading state tampil |
| 6 | Tunggu submit selesai | Redirect ke `/psb/success/{id}` |

### TC-2.6: Submit Success
**Priority:** Critical

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Setelah submit berhasil | Halaman sukses tampil |
| 2 | Periksa nomor pendaftaran | Format: PSB/2026/XXXX |
| 3 | Periksa database | Record `psb_registrations` ada |
| 4 | Periksa storage | Files tersimpan di `storage/app/public/psb/documents/` |

---

## Test Suite 3: Public PSB - Tracking

### TC-3.1: Tracking Page Accessible
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Akses `/psb/tracking` | Halaman tracking tampil |
| 2 | Periksa form input | Input nomor pendaftaran visible |

### TC-3.2: Check Status - Valid Number
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Input nomor valid (PSB/2026/0001) | - |
| 2 | Klik "Cek Status" | Timeline status tampil |
| 3 | Periksa timeline | Step yang sesuai status ter-highlight |
| 4 | Periksa data | Nama siswa dan nomor pendaftaran tampil |

### TC-3.3: Check Status - Invalid Number
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Input nomor tidak valid (PSB/2026/9999) | - |
| 2 | Klik "Cek Status" | Pesan error "tidak ditemukan" tampil |

### TC-3.4: Check Status - Rate Limit
**Priority:** Low

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Submit check status 11x dalam 1 menit | - |
| 2 | Pada request ke-11 | Error 429 Too Many Requests |

---

## Test Suite 4: Admin PSB - Dashboard

### TC-4.1: Dashboard Accessible (Admin)
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login sebagai ADMIN | Dashboard admin tampil |
| 2 | Klik menu "PSB" di sidebar | Submenu expand |
| 3 | Klik "Dashboard" | `/admin/psb` tampil |
| 4 | Periksa statistik | Card: Total, Pending, Approved, Rejected tampil |

### TC-4.2: Dashboard - Non-Admin Access
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login sebagai TEACHER | - |
| 2 | Akses `/admin/psb` langsung | Response 403 Forbidden |

### TC-4.3: Dashboard - Guest Access
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Logout | - |
| 2 | Akses `/admin/psb` langsung | Redirect ke `/login` |

---

## Test Suite 5: Admin PSB - Registrations List

### TC-5.1: List Page Accessible
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login sebagai ADMIN | - |
| 2 | Akses `/admin/psb/registrations` | Tabel pendaftaran tampil |
| 3 | Periksa kolom | No. Registrasi, Nama, Tanggal, Status, Actions |

### TC-5.2: Filter by Status
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Klik tab "Pending" | Hanya status pending yang tampil |
| 2 | Klik tab "Approved" | Hanya status approved yang tampil |
| 3 | Klik tab "Semua" | Semua status tampil |

### TC-5.3: Search Functionality
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Input nama "Ahmad" di search | Tabel filter berdasarkan nama |
| 2 | Input nomor "PSB/2026/0001" | Tabel filter berdasarkan nomor |
| 3 | Clear search | Semua data tampil kembali |

### TC-5.4: Pagination
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buat > 15 pendaftaran | - |
| 2 | Akses list | Pagination tampil |
| 3 | Klik page 2 | Data page 2 tampil |

---

## Test Suite 6: Admin PSB - Registration Detail

### TC-6.1: Detail Page Content
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Dari list, klik "Lihat Detail" | `/admin/psb/registrations/{id}` tampil |
| 2 | Periksa Data Siswa section | Semua field tampil dengan nilai |
| 3 | Periksa Data Orang Tua section | Data Ayah dan Ibu tampil |
| 4 | Periksa Dokumen section | 4 dokumen dengan thumbnail/icon |
| 5 | Periksa Timeline section | Timeline status tampil |

### TC-6.2: Document Preview
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Klik thumbnail dokumen | Lightbox/modal preview tampil |
| 2 | Periksa gambar | Gambar tampil full-size |
| 3 | Close preview | Modal tertutup |

---

## Test Suite 7: Admin PSB - Approval Flow

### TC-7.1: Approve Registration
**Priority:** Critical

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka detail pendaftaran status "pending" | - |
| 2 | Klik tombol "Setujui" | Modal konfirmasi tampil |
| 3 | (Opsional) Input catatan | - |
| 4 | Klik "Ya, Setujui" | Loading state |
| 5 | Tunggu selesai | Redirect ke list dengan success message |
| 6 | Periksa database | Status = "approved", verified_by terisi |
| 7 | Periksa email (jika configured) | Email notifikasi terkirim |

### TC-7.2: Approve - Already Approved
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka detail pendaftaran status "approved" | - |
| 2 | Tombol "Setujui" | Disabled atau tidak tampil |

---

## Test Suite 8: Admin PSB - Rejection Flow

### TC-8.1: Reject Registration
**Priority:** Critical

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka detail pendaftaran status "pending" | - |
| 2 | Klik tombol "Tolak" | Modal dengan textarea tampil |
| 3 | Klik "Tolak" tanpa alasan | Error: "Alasan wajib diisi" |
| 4 | Input alasan penolakan | - |
| 5 | Klik "Ya, Tolak" | Loading state |
| 6 | Tunggu selesai | Redirect ke list dengan success message |
| 7 | Periksa database | Status = "rejected", rejection_reason terisi |

---

## Test Suite 9: Admin PSB - Revision Flow

### TC-9.1: Request Document Revision
**Priority:** Critical

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka detail pendaftaran status "pending" | - |
| 2 | Klik tombol "Minta Revisi" | Modal dengan checklist dokumen tampil |
| 3 | Pilih dokumen yang perlu revisi | Checkbox tercentang |
| 4 | Input catatan revisi untuk setiap dokumen | - |
| 5 | Klik "Kirim Permintaan" | Loading state |
| 6 | Tunggu selesai | Redirect ke list dengan success message |
| 7 | Periksa database | Status = "document_review" |
| 8 | Periksa psb_documents | Status = "rejected", revision_note terisi |

### TC-9.2: Revision - No Document Selected
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka modal revisi | - |
| 2 | Tidak pilih dokumen | - |
| 3 | Klik "Kirim Permintaan" | Error: "Pilih minimal satu dokumen" |

### TC-9.3: Revision - Note Required
**Priority:** Medium

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Pilih dokumen | - |
| 2 | Tidak isi catatan revisi | - |
| 3 | Klik "Kirim Permintaan" | Error: "Catatan revisi wajib diisi" |

---

## Test Suite 10: Mobile Responsive

### TC-10.1: Public PSB Mobile
**Priority:** High

| Page | Test (375px viewport) | Expected |
|------|----------------------|----------|
| Landing | Layout | Single column, no horizontal scroll |
| Landing | Buttons | Min 44px tap target |
| Register | Form | Full-width inputs |
| Register | Step indicator | Compact/horizontal scroll |
| Tracking | Form | Full-width, easy to tap |

### TC-10.2: Admin PSB Mobile
**Priority:** Medium

| Page | Test (375px viewport) | Expected |
|------|----------------------|----------|
| Dashboard | Stats cards | Stack vertically |
| List | Table | Card view atau horizontal scroll |
| Detail | Sections | Stack vertically |
| Actions | Modal | Full-screen atau bottom sheet |

---

## Test Suite 11: Security

### TC-11.1: Rate Limiting - Registration
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Submit form 6x dalam 1 jam (same IP) | - |
| 2 | Pada request ke-6 | Error 429 Too Many Requests |

### TC-11.2: CSRF Protection
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Submit form tanpa CSRF token | Error 419 atau redirect |

### TC-11.3: File Upload Security
**Priority:** High

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Upload file dengan extension .php.jpg | File rejected atau sanitized |
| 2 | Upload file dengan malicious content | File rejected |

---

## Defect Report Template

```markdown
## Bug Report

**Title:** [Deskripsi singkat]

**Severity:** Critical / High / Medium / Low

**Environment:**
- Browser: 
- Device: 
- URL: 

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Result:**


**Actual Result:**


**Screenshot:**
[Attach screenshot]

**Additional Notes:**

```

---

## Test Execution Checklist

### Sprint Completion

- [ ] TC-1.x: Landing Page (3 test cases)
- [ ] TC-2.x: Registration Form (6 test cases)
- [ ] TC-3.x: Tracking (4 test cases)
- [ ] TC-4.x: Admin Dashboard (3 test cases)
- [ ] TC-5.x: Admin List (4 test cases)
- [ ] TC-6.x: Admin Detail (2 test cases)
- [ ] TC-7.x: Approval Flow (2 test cases)
- [ ] TC-8.x: Rejection Flow (1 test case)
- [ ] TC-9.x: Revision Flow (3 test cases)
- [ ] TC-10.x: Mobile Responsive (2 test cases)
- [ ] TC-11.x: Security (3 test cases)

**Total: 33 test cases**

---

## Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| QA Lead | | | |
| Developer | | | |
| Product Owner | | | |
