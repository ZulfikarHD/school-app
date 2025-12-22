# EPIC 6: New Student Registration (PSB Online)

**Project:** SD Management System  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Epic Owner:** Development Team  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Epic Overview

### Goal
Digitalisasi proses penerimaan siswa baru (PSB) dari manual menjadi online, memungkinkan calon orang tua mendaftar tanpa harus datang ke sekolah, dengan proses verifikasi dokumen, pengumuman hasil seleksi, hingga daftar ulang dan pembayaran formulir yang terkelola dengan baik.

### Business Value
- **Efisiensi Operasional:** Mengurangi beban kerja TU dalam mengelola pendaftaran manual
- **Jangkauan Lebih Luas:** Calon siswa dari luar kota dapat mendaftar tanpa datang ke sekolah
- **Transparansi:** Status pendaftaran dapat ditrack secara real-time oleh orang tua
- **Data Terstruktur:** Database calon siswa untuk analisis dan follow-up
- **Profesionalisme:** Meningkatkan citra sekolah dengan sistem PSB modern
- **Otomasi:** Komunikasi otomatis ke orang tua untuk setiap tahap proses

### Success Metrics
- 80% pendaftar menggunakan sistem online (target tahun pertama)
- Waktu proses verifikasi < 3 hari kerja
- 90% conversion rate dari verifikasi approved ke daftar ulang
- Pengurangan 50% waktu admin untuk processing pendaftaran
- Zero kehilangan dokumen fisik

---

## 📊 Epic Summary

| **Attribute** | **Value** |
|---------------|-----------|
| **Total Points** | 24 points |
| **Target Sprint** | Sprint 5 & 6 |
| **Priority** | P2 - High (setelah Student Management) |
| **Dependencies** | Epic 1 (Auth), Epic 2 (Student), Epic 4 (Payment), Notification |
| **Target Release** | MVP (Phase 1) |
| **Estimated Duration** | 3 minggu (1 developer) |

---

## 🎯 User Stories Included

### Public Registration Flow (10 points)

#### US-PSB-001: Formulir Pendaftaran Online (Calon Siswa/Orang Tua)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Orang Tua calon siswa  
**I want** mengisi formulir pendaftaran online  
**So that** anak saya dapat mendaftar tanpa datang ke sekolah

**Acceptance Criteria:**
- ✅ **Given** orang tua akses website sekolah  
   **When** orang tua klik "Daftar Siswa Baru"  
   **Then** sistem tampilkan form pendaftaran multi-step: Data Calon Siswa, Data Orang Tua, Upload Dokumen, Review & Submit

- ✅ **Given** orang tua mengisi form dengan data valid dan lengkap  
   **When** orang tua klik "Submit Pendaftaran"  
   **Then** sistem generate nomor pendaftaran (format: PSB/2025/0001), simpan data dengan status "Pending", tampilkan success page dengan nomor pendaftaran

- ✅ **Given** orang tua input NIK yang sudah terdaftar  
   **When** orang tua submit  
   **Then** sistem tampilkan error "NIK sudah terdaftar. Hubungi sekolah jika ada masalah"

- ✅ **Given** pendaftaran berhasil  
   **When** sistem simpan data  
   **Then** sistem kirim email & WhatsApp konfirmasi dengan nomor pendaftaran dan link tracking status

**Technical Notes:**
- Multi-step form dengan progress indicator
- Auto-save draft di localStorage (prevent data loss)
- Real-time validation untuk NIK (16 digit), email, nomor HP
- Mobile-first design dengan camera option untuk upload dokumen

---

#### US-PSB-002: Upload Dokumen Pendaftaran
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Orang Tua calon siswa  
**I want** upload dokumen pendukung (akte, KK, rapor)  
**So that** pendaftaran saya lengkap dan dapat diverifikasi

**Acceptance Criteria:**
- ✅ **Given** orang tua di step "Upload Dokumen"  
   **When** halaman load  
   **Then** sistem tampilkan list dokumen wajib: Akte Lahir, Kartu Keluarga, KTP Orang Tua, Pas Foto (dan opsional: Rapor/Surat Pindah jika pindahan)

- ✅ **Given** orang tua upload akte lahir (file JPG, 1.5MB)  
   **When** upload selesai  
   **Then** sistem simpan file, tampilkan preview thumbnail, show progress bar

- ✅ **Given** orang tua upload file PDF > 5MB  
   **When** sistem validasi  
   **Then** sistem tampilkan error "Ukuran file maksimal 5MB. Silakan kompres file Anda"

- ✅ **Given** semua dokumen wajib sudah diupload  
   **When** orang tua klik "Lanjut"  
   **Then** sistem enable button "Submit" di step review

**Technical Notes:**
- Drag & drop upload dengan preview
- Support format: JPG, PNG, PDF (max 5MB per file)
- Mobile: native camera integration untuk capture dokumen langsung
- Image compression otomatis di client-side

---

#### US-PSB-005: Pengumuman Hasil Seleksi
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Orang Tua calon siswa  
**I want** melihat hasil seleksi/pengumuman penerimaan  
**So that** saya tahu apakah anak saya diterima

**Acceptance Criteria:**
- ✅ **Given** admin sudah release pengumuman  
   **When** orang tua akses halaman tracking dengan nomor pendaftaran  
   **Then** sistem tampilkan status: "Selamat! Anda diterima" atau "Mohon maaf, Anda belum berhasil kali ini"

- ✅ **Given** pendaftar diterima  
   **When** orang tua lihat pengumuman  
   **Then** sistem tampilkan instruksi daftar ulang dengan deadline, biaya formulir, dan link untuk daftar ulang

- ✅ **Given** pengumuman belum dirilis  
   **When** orang tua cek status  
   **Then** sistem tampilkan "Pengumuman akan dirilis pada [Tanggal]" dengan countdown timer

**Technical Notes:**
- Real-time notification via WhatsApp & email saat pengumuman dirilis
- Public tracking page (no login required)

---

#### US-PSB-009: Konfigurasi Periode PSB
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Admin/TU  
**I want** mengatur periode pendaftaran (tanggal buka & tutup)  
**So that** pendaftaran hanya bisa dilakukan pada periode yang ditentukan

**Acceptance Criteria:**
- ✅ **Given** admin di halaman "Pengaturan PSB"  
   **When** admin set periode: Tanggal Buka = 1 Januari 2025, Tanggal Tutup = 31 Januari 2025, Tanggal Pengumuman = 15 Februari 2025  
   **Then** sistem simpan konfigurasi dan enforce periode tersebut

- ✅ **Given** periode PSB sudah ditutup (setelah 31 Januari)  
   **When** orang tua coba akses form pendaftaran  
   **Then** sistem tampilkan pesan "Pendaftaran sudah ditutup. Periode pendaftaran: 1-31 Januari 2025. Terima kasih atas minat Anda"

- ✅ **Given** periode PSB belum dibuka (sebelum 1 Januari)  
   **When** orang tua coba akses  
   **Then** sistem tampilkan "Pendaftaran akan dibuka pada 1 Januari 2025" dengan countdown timer

**Technical Notes:**
- Konfigurasi: Tanggal Buka, Tanggal Tutup, Tanggal Pengumuman, Deadline Daftar Ulang, Kuota, Biaya Formulir
- Landing page dengan countdown timer
- Form pendaftaran disabled di luar periode

---

### Admin Verification Flow (6 points)

#### US-PSB-003: Verifikasi Dokumen Pendaftaran (Admin/TU)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** verifikasi dokumen pendaftaran calon siswa  
**So that** hanya pendaftar dengan dokumen lengkap & valid yang lanjut ke tahap berikutnya

**Acceptance Criteria:**
- ✅ **Given** TU di halaman "Verifikasi PSB"  
   **When** TU lihat list pendaftar  
   **Then** sistem tampilkan list dengan filter status: Pending, Approved, Rejected, Revisi; badge count untuk pending verifications

- ✅ **Given** TU klik salah satu pendaftar  
   **When** TU lihat detail  
   **Then** sistem tampilkan: biodata pendaftar, dokumen dengan preview thumbnail, action buttons (Approve, Reject, Request Revisi)

- ✅ **Given** dokumen lengkap dan valid  
   **When** TU klik "Setujui Dokumen" dan konfirmasi  
   **Then** status berubah "Approved", pendaftar dapat notifikasi "Dokumen Anda telah diverifikasi. Menunggu pengumuman hasil seleksi"

- ✅ **Given** dokumen tidak lengkap atau tidak jelas  
   **When** TU klik "Tolak" dengan input alasan (required)  
   **Then** status berubah "Rejected" dan orang tua dapat notifikasi dengan alasan penolakan

- ✅ **Given** dokumen perlu diperbaiki  
   **When** TU klik "Request Revisi", pilih dokumen mana yang perlu direvisi, dan input alasan  
   **Then** status berubah "Revisi", orang tua dapat notifikasi dengan link untuk upload ulang dokumen tertentu

**Technical Notes:**
- Lightbox preview untuk view dokumen full-size
- Download button untuk arsip offline
- Keyboard shortcut untuk efficiency: A=Approve, R=Reject
- Batch verification (select multiple untuk approve sekaligus)

---

#### US-PSB-004: Jadwal & Hasil Tes/Seleksi (Opsional)
**Priority:** Could Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** mengatur jadwal tes dan input hasil tes calon siswa  
**So that** proses seleksi dapat dilakukan dengan teratur

**Acceptance Criteria:**
- ✅ **Given** TU di halaman "Jadwal Tes PSB"  
   **When** TU set tanggal tes (misal: 15 Januari 2025, pukul 08:00-10:00)  
   **Then** sistem kirim notifikasi ke semua pendaftar yang sudah status Approved dengan instruksi tes

- ✅ **Given** tes sudah dilaksanakan  
   **When** TU input hasil tes (Lulus/Tidak Lulus) untuk setiap pendaftar  
   **Then** sistem simpan hasil dan update status pendaftar

- ✅ **Given** pendaftar lulus tes  
   **When** TU save hasil  
   **Then** pendaftar dapat notifikasi "Selamat! Anda lulus seleksi" dan instruksi menunggu pengumuman resmi

**Technical Notes:**
- Optional feature untuk SD (biasanya tidak ada tes)
- Jika disabled, langsung dari Approved ke Accepted setelah announcement
- Support untuk scoring jika ada tes tertulis

---

### Payment & Re-Registration Flow (5 points)

#### US-PSB-006: Pembayaran Formulir Pendaftaran
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Orang Tua calon siswa  
**I want** membayar biaya formulir pendaftaran  
**So that** pendaftaran saya diproses

**Acceptance Criteria:**
- ✅ **Given** orang tua sudah submit formulir lengkap  
   **When** sistem generate nomor pendaftaran  
   **Then** sistem tampilkan tagihan biaya formulir (misal: Rp 50.000) dengan instruksi pembayaran (rekening sekolah, kode unik)

- ✅ **Given** orang tua bayar via transfer bank  
   **When** orang tua upload bukti transfer di portal tracking  
   **Then** status pembayaran berubah "Menunggu Verifikasi" dan TU dapat notifikasi

- ✅ **Given** TU verifikasi pembayaran di dashboard  
   **When** TU konfirmasi pembayaran  
   **Then** status berubah "Lunas", create payment record, dan pendaftaran lanjut ke proses verifikasi dokumen

**Technical Notes:**
- Fase 1: Manual transfer + upload bukti (MVP)
- Fase 2: Payment gateway integration (VA/QRIS)
- Biaya formulir configurable per tahun ajaran di settings PSB

---

#### US-PSB-007: Daftar Ulang (Pendaftar Diterima)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Orang Tua yang anaknya diterima  
**I want** melakukan daftar ulang dan bayar uang gedung  
**So that** anak saya resmi terdaftar sebagai siswa

**Acceptance Criteria:**
- ✅ **Given** orang tua anaknya diterima (status Accepted)  
   **When** orang tua akses link daftar ulang  
   **Then** sistem tampilkan form daftar ulang: konfirmasi data calon siswa (pre-filled, editable), upload dokumen tambahan jika ada, checkbox "Setuju dengan peraturan sekolah"

- ✅ **Given** orang tua submit daftar ulang  
   **When** orang tua klik "Konfirmasi Daftar Ulang"  
   **Then** sistem generate tagihan uang gedung, tampilkan instruksi pembayaran dengan deadline

- ✅ **Given** orang tua sudah bayar uang gedung dan upload bukti  
   **When** TU verifikasi pembayaran  
   **Then** status berubah "Registered", sistem otomatis:
   - Create student record di Student Management Module
   - Generate NIS otomatis
   - Create parent account dengan credentials
   - Send welcome email/WhatsApp dengan info: NIS, username & password orang tua, info tahun ajaran

- ✅ **Given** deadline daftar ulang terlewati tanpa submit  
   **When** sistem check deadline via cron job  
   **Then** status berubah "Expired", quota released untuk waiting list

**Technical Notes:**
- Deadline daftar ulang configurable (e.g., 7 hari setelah pengumuman)
- Auto-generate NIS dengan format sesuai school settings
- Integration dengan Student Management untuk create student & parent account
- Payment integration untuk record pembayaran uang gedung

---

### Dashboard & Reporting (3 points)

#### US-PSB-008: Dashboard PSB (Admin/Kepala Sekolah)
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah/TU  
**I want** melihat dashboard PSB  
**So that** saya dapat monitoring proses penerimaan siswa baru

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah di halaman "Dashboard PSB"  
   **When** halaman load  
   **Then** sistem tampilkan summary cards:
   - Total Pendaftar
   - Pending Verifikasi (dengan badge highlight)
   - Approved
   - Rejected
   - Accepted
   - Registered (daftar ulang selesai)
   - Conversion Rate (Registered / Total Pendaftar)

- ✅ **Given** ada 15 pendaftar yang dokumen menunggu verifikasi  
   **When** dashboard load  
   **Then** card "Menunggu Verifikasi" tampilkan angka 15 dengan badge merah dan highlight animation

- ✅ **Given** kepala sekolah ingin lihat detail per status  
   **When** kepala sekolah klik salah satu summary card  
   **Then** sistem redirect ke list pendaftar dengan filter status tersebut

- ✅ **Given** kepala sekolah di dashboard  
   **When** halaman load  
   **Then** sistem tampilkan grafik:
   - Funnel conversion (Submit → Verified → Accepted → Registered)
   - Timeline: pendaftar per hari/minggu (line chart)
   - Demographics: by gender, agama, asal sekolah, alamat

**Technical Notes:**
- Real-time count dengan auto-refresh setiap 30 detik
- Export data ke Excel dengan semua detail pendaftar
- Filter by date range untuk historical analysis

---

#### US-PSB-010: Export Data Pendaftar
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** TU/Kepala Sekolah  
**I want** export data pendaftar ke Excel  
**So that** data dapat digunakan untuk analisis atau laporan

**Acceptance Criteria:**
- ✅ **Given** TU di halaman "Data Pendaftar"  
   **When** TU klik "Export ke Excel"  
   **Then** sistem generate file Excel dengan kolom: Nomor Pendaftaran, Nama Calon Siswa, NIK, TTL, Nama Orang Tua, No HP, Email, Status, Tanggal Daftar

- ✅ **Given** TU sudah apply filter (misal: status "Diterima")  
   **When** TU export  
   **Then** file Excel hanya berisi pendaftar dengan status Diterima

- ✅ **Given** kepala sekolah export untuk laporan  
   **When** export selesai  
   **Then** file include summary sheet dengan statistik: total pendaftar, conversion rate, demographics breakdown

**Technical Notes:**
- Format Excel standar dengan proper column headers
- Nama file: DataPendaftar_PSB2025_[Tanggal].xlsx
- Include dokumen uploaded sebagai hyperlink (optional)

---

## 🏗️ Technical Architecture

### Database Schema Requirements

#### PSB Registrations Table
```
- id (PK)
- registration_number (unique, format: PSB/2025/0001)
- academic_year_id (FK)
- status (pending/approved/rejected/revision/accepted/not_accepted/re_registered/registered/expired)
- payment_status (pending/paid/verified)

- student_name (required)
- student_nickname
- student_nik (required, unique, 16 digit)
- student_nisn (10 digit, optional)
- student_gender (L/P)
- student_birth_place
- student_birth_date
- student_religion
- student_child_number (anak ke-)
- student_address (textarea)
- student_previous_school

- father_name (required)
- father_nik (required)
- father_occupation
- father_phone (required)
- father_email (required)

- mother_name (required)
- mother_nik (required)
- mother_occupation
- mother_phone
- mother_email

- verification_notes (jika rejected/revision)
- verified_by (FK to users, TU yang verifikasi)
- verified_at
- announced_at (tanggal pengumuman)
- re_registration_deadline
- re_registered_at

- created_at
- updated_at
- deleted_at (soft delete)
```

#### PSB Documents Table
```
- id (PK)
- registration_id (FK)
- document_type (akte_lahir/kartu_keluarga/ktp/pas_foto/rapor/surat_pindah/other)
- file_name
- file_path (storage path)
- file_size (bytes)
- mime_type
- is_required (boolean)
- status (uploaded/missing/rejected/approved)
- rejection_reason (jika status rejected)
- uploaded_at
- created_at
- updated_at
```

#### PSB Settings Table
```
- id (PK)
- academic_year_id (FK)
- registration_open_date
- registration_close_date
- announcement_date
- re_registration_deadline_days (X hari setelah pengumuman)
- quota_per_class (JSON: {kelas_1: 60, kelas_2: 0, ...})
- registration_fee (biaya formulir)
- required_documents (JSON array)
- minimum_age (default: 6 untuk kelas 1)
- is_active (boolean)
- created_at
- updated_at
```

#### PSB Payments Table
```
- id (PK)
- registration_id (FK)
- payment_type (registration_fee/re_registration_fee)
- amount
- payment_method (transfer/cash/va/qris)
- payment_proof (file path, jika manual transfer)
- status (pending/verified/rejected)
- paid_at
- verified_by (FK to users)
- verified_at
- notes
- created_at
- updated_at
```

#### PSB Test Results Table (Optional)
```
- id (PK)
- registration_id (FK)
- test_date
- test_type (wawancara/tertulis/praktik)
- score
- result (lulus/tidak_lulus)
- notes
- evaluated_by (FK to users)
- created_at
- updated_at
```

---

### API Endpoints

#### Public Registration
- `GET /api/psb/config` - Get PSB configuration (periode, biaya, etc) [Public]
- `POST /api/psb/register` - Submit registration form [Public]
- `GET /api/psb/track/:registration_number` - Track registration status [Public, with birth_date validation]
- `POST /api/psb/documents/upload` - Upload document [Public, with registration_number]
- `POST /api/psb/documents/:id/reupload` - Re-upload document untuk revision [Public]
- `POST /api/psb/payment/proof` - Upload payment proof [Public]

#### Admin Verification
- `GET /api/psb/registrations` - List all registrations [Admin, filters: status, date]
- `GET /api/psb/registrations/:id` - Get registration detail [Admin]
- `PUT /api/psb/registrations/:id/approve` - Approve registration [Admin]
- `PUT /api/psb/registrations/:id/reject` - Reject registration with reason [Admin]
- `PUT /api/psb/registrations/:id/request-revision` - Request document revision [Admin]
- `POST /api/psb/registrations/bulk-announce` - Bulk announcement (accept/not accept) [Admin]
- `GET /api/psb/documents/:id/download` - Download document [Admin]

#### Payment Verification
- `GET /api/psb/payments` - List payments pending verification [Admin]
- `PUT /api/psb/payments/:id/verify` - Verify payment [Admin]
- `PUT /api/psb/payments/:id/reject` - Reject payment [Admin]

#### Re-Registration
- `GET /api/psb/re-registration/:registration_number` - Get re-registration form [Public]
- `POST /api/psb/re-registration/:registration_number/submit` - Submit re-registration [Public]
- `POST /api/psb/re-registration/:registration_number/payment` - Submit payment for re-registration [Public]

#### Test/Selection (Optional)
- `POST /api/psb/test-schedule` - Set test schedule [Admin]
- `PUT /api/psb/test-results/:registration_id` - Input test result [Admin]

#### Dashboard & Reports
- `GET /api/psb/dashboard/summary` - Get summary statistics [Admin, Principal]
- `GET /api/psb/dashboard/funnel` - Get conversion funnel [Admin, Principal]
- `GET /api/psb/dashboard/demographics` - Get demographics data [Admin, Principal]
- `GET /api/psb/reports/export` - Export registrations to Excel [Admin, Principal]

#### Settings
- `GET /api/psb/settings` - Get PSB settings [Admin]
- `PUT /api/psb/settings` - Update PSB settings [Admin]

---

### Integration Points

#### INT-PSB-001: Student Management Module
**Description:** Setelah daftar ulang sukses dan payment verified, auto-create student record
**Data Flow:**
1. PSB Registration (status: Registered) → Create Student Record
2. Transfer data: nama, NIK, TTL, alamat, orang tua
3. Auto-generate NIS
4. Link PSB registration_id ke student record

**Technical:**
- API call: `POST /api/students/create-from-psb`
- Payload: PSB registration data
- Response: Student ID, NIS

---

#### INT-PSB-002: Payment Module
**Description:** Pembayaran formulir & uang gedung recorded di Payment Module
**Data Flow:**
1. PSB Payment created → Create Payment Record
2. Link payment_id ke PSB registration
3. Payment status sync between modules

**Technical:**
- API call: `POST /api/payments/create-from-psb`
- Webhook untuk payment status update (jika payment gateway)

---

#### INT-PSB-003: Notification Module
**Description:** Send email/WhatsApp untuk setiap status change
**Events:**
- Registration submitted → Confirmation email with tracking link
- Document approved → Notification "Dokumen diverifikasi"
- Document rejected/revision → Notification with reason
- Announcement → Notification "Diterima" or "Tidak Diterima"
- Re-registration reminder → Notification X hari sebelum deadline
- Re-registration completed → Welcome email with credentials

**Technical:**
- Queue-based notification (async)
- Template management untuk setiap event
- Support email & WhatsApp

---

#### INT-PSB-004: User Management (Create Parent Account)
**Description:** Auto-create parent account setelah daftar ulang selesai
**Data Flow:**
1. Registration status: Registered → Create parent user account
2. Username: father's phone or email
3. Password: auto-generated
4. Send credentials via email/WhatsApp

**Technical:**
- API call: `POST /api/users/create-parent-from-psb`
- Link parent user_id ke student record

---

## 🎨 UI/UX Design Requirements

### Public Registration Form (Multi-Step)

**Landing Page:**
- Hero section dengan visual menarik
- Info periode PSB (dengan countdown timer)
- CTA button "Daftar Sekarang" (large, prominent)
- Syarat & ketentuan pendaftaran
- FAQ section
- Kontak sekolah

**Form Design:**
- Multi-step wizard dengan 4 steps:
  1. Data Calon Siswa
  2. Data Orang Tua
  3. Upload Dokumen
  4. Review & Submit
- Progress bar di atas (visual step indicator dengan icon)
- Navigation: "Sebelumnya" dan "Lanjut" button
- Auto-save draft ke localStorage setiap 30 detik

**Mobile-First Considerations:**
- Single column layout
- Large input fields (min 48px height) untuk touch-friendly
- Native date picker untuk tanggal lahir
- Native camera integration untuk upload dokumen
- Sticky footer dengan navigation buttons
- Field validation real-time dengan clear error messages
- Character counter untuk textarea

**Upload Documents UX:**
- Drag & drop area dengan visual feedback
- Preview thumbnail untuk setiap dokumen
- Progress bar untuk upload
- Edit/delete option untuk dokumen sudah upload
- Camera button untuk mobile (ambil foto langsung)
- Compress image otomatis di client-side

**Review Step:**
- Display semua data dengan section collapsible
- Edit button untuk setiap section (back to specific step)
- Checklist: "Data sudah benar"
- Submit button dengan confirmation modal

**Success Page:**
- Konfetti animation atau celebratory visual
- Display nomor pendaftaran (large, prominent)
- Instruksi: simpan nomor pendaftaran, cek email
- Button "Cek Status Pendaftaran"
- Button "Download PDF Bukti Pendaftaran"

---

### Tracking Page (Public)

**Design:**
- Clean, minimalist design
- Form di center: Input nomor pendaftaran & tanggal lahir anak
- Large input fields dengan icon
- Submit button

**Result Page:**
- Header: Nomor Pendaftaran, Nama Calon Siswa, Foto (jika ada)
- Timeline stepper (vertical di mobile, horizontal di desktop):
  1. ✅ Pendaftaran Diterima (tanggal)
  2. 🔄 Verifikasi Dokumen (status: Pending/Approved/Rejected/Revisi)
  3. ⏳ Pengumuman Hasil Seleksi (tanggal atau "Menunggu")
  4. ⏳ Daftar Ulang (deadline)
  5. ⏳ Terdaftar
- Color coding: Done=hijau, Current=biru blink, Pending=abu-abu
- Keterangan per step dengan icon
- Action button jika ada: "Upload Ulang Dokumen", "Bayar Formulir", "Daftar Ulang"
- Share button: copy link tracking

---

### Admin Verification Dashboard

**List View:**
- Header: "Verifikasi Pendaftaran PSB" dengan badge count pending
- Quick filters: "Semua", "Pending" (highlight), "Approved", "Rejected", "Revisi"
- Search bar: by nama atau nomor pendaftaran
- Table/Card view toggle
- Columns: 
  - Nomor Pendaftaran
  - Foto calon siswa (thumbnail)
  - Nama Lengkap
  - Tanggal Daftar
  - Status (badge dengan color coding)
  - Actions: "View Detail"
- Pagination dengan showing "X-Y of Z entries"
- Bulk action: Select multiple untuk approve sekaligus (advanced feature)

**Detail View (Modal or Full Page):**
- Left Sidebar (atau top section di mobile):
  - Biodata calon siswa (semua field, read-only)
  - Biodata orang tua
- Right Content (atau bottom section di mobile):
  - Section "Dokumen" dengan grid layout
  - Per dokumen: thumbnail, nama dokumen, status badge, download icon
  - Click thumbnail → lightbox preview (full image/PDF viewer)
  - Zoom in/out untuk preview
- Bottom Action Bar (sticky):
  - Button "Setujui" (hijau, icon: check)
  - Button "Tolak" (merah, icon: x) → open modal input alasan
  - Button "Minta Revisi" (kuning, icon: refresh) → open modal pilih dokumen & alasan
  - Button "Kembali"

**Keyboard Shortcuts (for efficiency):**
- `A` = Approve
- `R` = Reject
- `V` = Request Revision
- `Esc` = Close modal
- Arrow keys untuk navigate list

---

### Dashboard PSB

**Layout:**
- Grid layout dengan cards untuk summary metrics
- Each card:
  - Icon (relevant)
  - Number (large)
  - Label
  - Trend indicator (up/down vs periode sebelumnya)
  - Click to drill down

**Summary Cards:**
1. Total Pendaftar (icon: users)
2. Pending Verifikasi (icon: clock, badge merah jika > 0)
3. Approved (icon: check-circle, hijau)
4. Diterima (icon: user-check)
5. Terdaftar (icon: user-plus, hijau terang)
6. Conversion Rate (icon: trending-up, format: XX%)

**Charts:**
- Funnel Chart: Submit → Approved → Accepted → Registered (visual conversion)
- Line Chart: Pendaftar per hari/minggu (date range selector)
- Pie Chart: Demographics (gender, agama, asal sekolah)
- Bar Chart: Pendaftar by kelurahan/kecamatan (untuk analisa geografis)

**Actions:**
- Export to Excel (top right)
- Date range filter
- Print report

---

### PSB Settings Page

**Layout:**
- Tab navigation:
  1. Periode & Quota
  2. Biaya
  3. Dokumen Persyaratan
  4. Template Notifikasi

**Tab 1: Periode & Quota**
- Form fields:
  - Tahun Ajaran (dropdown, auto-select active year)
  - Tanggal Buka Pendaftaran (date picker)
  - Tanggal Tutup Pendaftaran (date picker)
  - Tanggal Pengumuman (date picker)
  - Deadline Daftar Ulang (number input: X hari setelah pengumuman)
  - Quota per Kelas (table: Kelas 1 = 60, Kelas 2 = 0, ...)
  - Usia Minimal (number: default 6 tahun untuk kelas 1)
- Save button

**Tab 2: Biaya**
- Biaya Formulir (currency input: Rp)
- Metode Pembayaran (checkbox: Transfer Manual, Payment Gateway)
- Rekening Sekolah (jika manual): Bank, No Rek, Atas Nama
- Save button

**Tab 3: Dokumen Persyaratan**
- List dokumen dengan checkbox (wajib/opsional):
  - Akte Lahir (wajib, checked, disabled)
  - Kartu Keluarga (wajib)
  - KTP Orang Tua (wajib)
  - Pas Foto (wajib)
  - Rapor (opsional)
  - Surat Pindah (opsional)
  - [+ Tambah Dokumen Custom]
- Drag to reorder
- Save button

**Tab 4: Template Notifikasi**
- Dropdown: Select notification type (Registration Success, Approved, Rejected, etc)
- Rich text editor untuk email template
- Text area untuk WhatsApp template (with variable placeholders: {nama}, {nomor_pendaftaran}, etc)
- Preview button
- Save button

---

## ✅ Definition of Done

### Code Level
- [ ] Unit test coverage minimal 70% untuk business logic
- [ ] Integration test untuk critical flow (register, verify, announce, re-register)
- [ ] Code review approved
- [ ] No critical linter errors
- [ ] All API endpoints documented (Swagger/Postman)

### Functionality
- [ ] All acceptance criteria met dan tested untuk semua 10 user stories
- [ ] Public registration form tested di mobile & desktop
- [ ] Document upload working dengan validation (file type, size)
- [ ] Admin verification flow tested (approve, reject, request revision)
- [ ] Announcement flow tested (bulk accept/reject)
- [ ] Re-registration & payment flow tested
- [ ] Auto-create student record working after re-registration
- [ ] Tracking page working tanpa login
- [ ] All notification events triggered correctly

### UI/UX
- [ ] Responsive di mobile dan desktop (tested di iOS & Android)
- [ ] Loading state untuk semua async actions (upload, submit)
- [ ] Error handling dengan user-friendly message dalam Bahasa Indonesia
- [ ] Success feedback (toast/notification) untuk setiap action
- [ ] Multi-step form dengan auto-save working
- [ ] Document preview (lightbox) working untuk semua file types

### Security
- [ ] Public endpoints rate limited (prevent spam submission)
- [ ] Tracking page validation: nomor pendaftaran + tanggal lahir
- [ ] File upload validation (file type whitelist, max size)
- [ ] Secure file storage (tidak public accessible)
- [ ] NIK uniqueness validation (no duplicate registration)

### Performance
- [ ] Form submission < 3 detik (termasuk file upload)
- [ ] Document upload with progress indicator (chunk upload untuk large files)
- [ ] Tracking page load < 2 detik
- [ ] Dashboard load < 3 detik dengan all charts
- [ ] Database queries optimized untuk list view dengan pagination

### Integration
- [ ] Integration dengan Student Management tested (auto-create student)
- [ ] Integration dengan Payment Module tested (record payments)
- [ ] Integration dengan Notification Module tested (email & WhatsApp)
- [ ] Integration dengan User Management tested (auto-create parent account)

### Documentation
- [ ] API documentation complete dengan example requests/responses
- [ ] Database schema documented dengan relationships
- [ ] User manual untuk Admin/TU (cara verifikasi, announcement, settings)
- [ ] User manual untuk Orang Tua (cara daftar, upload dokumen, track status)
- [ ] Technical documentation untuk integration points

---

## 🔗 Dependencies

### External Dependencies
- **Email Service:** SMTP atau transactional email service (SendGrid, Mailgun)
- **WhatsApp API:** Fonnte, Wablas, atau Twilio WhatsApp API
- **File Storage:** Local storage atau cloud storage (AWS S3, Google Cloud Storage)
- **PDF Generator:** Library untuk generate PDF bukti pendaftaran
- **Image Processing:** Library untuk compress & resize image (client-side & server-side)
- **Payment Gateway (Phase 2):** Xendit, Midtrans, atau Doku untuk VA/QRIS

### Internal Dependencies (Must Complete First)
- **Epic 1:** Authentication & Access Control (user roles, RBAC)
- **Epic 2:** Student Management (untuk create student record after re-registration)
- **Epic 4:** Payment System (untuk record payment formulir & uang gedung)
- **Notification Module:** Email & WhatsApp notification system

### Blocking For
Epic 6 is independent dan tidak blocking epic lain. Namun disarankan develop setelah Epic 2 (Student Management) selesai karena integration dependency.

---

## 🧪 Testing Strategy

### Unit Testing
- Service layer: registration logic, validation, status transition
- Utility functions: NIK validation, file upload validation, date validation
- Target coverage: 70%

### Integration Testing
- Registration flow: submit form → upload documents → verify → approve/reject
- Payment flow: upload proof → verify payment → update status
- Re-registration flow: submit → payment → auto-create student
- Announcement flow: bulk accept/reject → notification sent
- Tracking flow: input nomor pendaftaran → validate → display status

### E2E Testing (Critical Paths)
1. **Happy Path Registration:**
   - Orang tua buka form → isi semua data → upload dokumen → submit → dapat nomor pendaftaran → receive email
2. **Verification Flow:**
   - TU login → lihat pending registrations → view detail → approve → orang tua dapat notifikasi
3. **Rejection & Revision:**
   - TU reject dengan alasan → orang tua dapat notifikasi → orang tua upload ulang → TU verify ulang
4. **Announcement:**
   - TU bulk select pendaftar → announce (accept/reject) → all pendaftar dapat notifikasi → tracking page updated
5. **Re-Registration:**
   - Orang tua (diterima) submit daftar ulang → upload payment proof → TU verify → student record created → parent account created → welcome email sent
6. **Tracking Public:**
   - Public user input nomor pendaftaran + tanggal lahir → view timeline with current status

### Performance Testing
- Load test: 50 concurrent form submissions
- Stress test: 100 document uploads simultaneous
- Target: 
  - Form submission < 3 detik
  - Document upload < 5 detik per file
  - Tracking page < 2 detik

### Security Testing
- Rate limiting test: verify max submissions per IP
- File upload security: test upload executable files (should be blocked)
- Tracking validation: test access dengan wrong birth date (should be blocked)
- NIK uniqueness: test duplicate submission (should error)

### UAT (User Acceptance Testing)
- Test dengan actual orang tua (3-5 orang) untuk registration flow
- Collect feedback pada UX (apakah mudah dipahami, ada yang membingungkan?)
- Test dengan TU untuk verification flow
- Adjust based on feedback before production release

---

## 📅 Sprint Planning

### Sprint 5 (2 minggu) - 12 points
**Focus:** Public Registration & Document Upload

**Stories:**
- US-PSB-001: Formulir Pendaftaran Online (3 pts) - **Day 1-3**
  - Multi-step form dengan validation
  - Auto-save draft
  - Generate nomor pendaftaran
  - Send confirmation email
- US-PSB-002: Upload Dokumen (3 pts) - **Day 4-6**
  - Drag & drop upload
  - Preview thumbnail
  - File validation
  - Mobile camera integration
- US-PSB-009: Konfigurasi Periode PSB (2 pts) - **Day 7**
  - Settings page
  - Periode enforcement
  - Countdown timer
- US-PSB-005: Pengumuman Hasil Seleksi (2 pts) - **Day 8**
  - Public tracking page
  - Status timeline UI
- US-PSB-010: Export Data (2 pts) - **Day 9**
  - Excel export
  - Filter by status

**Deliverables:**
- Public dapat akses form PSB dan submit registration
- Upload dokumen working dengan preview
- Tracking page untuk cek status
- Admin dapat configure periode PSB
- Export data pendaftar

**Sprint Goal:** "Orang tua dapat mendaftar online dengan lengkap dan tracking status"

---

### Sprint 6 (2 minggu) - 12 points
**Focus:** Admin Verification, Announcement, & Re-Registration

**Stories:**
- US-PSB-003: Verifikasi Dokumen (3 pts) - **Day 1-3**
  - Verification dashboard
  - Document preview (lightbox)
  - Approve/reject/request revision
  - Notification integration
- US-PSB-006: Pembayaran Formulir (3 pts) - **Day 4-5**
  - Payment proof upload
  - Payment verification by TU
  - Payment status tracking
- US-PSB-007: Daftar Ulang (3 pts) - **Day 6-8**
  - Re-registration form
  - Payment uang gedung
  - Auto-create student record
  - Auto-create parent account
- US-PSB-008: Dashboard PSB (3 pts) - **Day 9-10**
  - Summary cards
  - Funnel chart
  - Demographics charts
  - Export reports
- US-PSB-004: Jadwal & Hasil Tes (Optional, jika ada waktu) - **Bonus**

**Deliverables:**
- TU dapat verifikasi dokumen pendaftar
- TU dapat announce hasil seleksi (bulk accept/reject)
- Orang tua dapat daftar ulang dan bayar
- Auto-create student & parent account setelah re-registration complete
- Dashboard PSB dengan analytics

**Sprint Goal:** "Complete end-to-end PSB flow dari pendaftaran hingga terdaftar sebagai siswa"

---

## 🎯 Acceptance Criteria (Epic Level)

### Functional
- [ ] Orang tua dapat mendaftar online tanpa login dari website sekolah
- [ ] Multi-step form dengan auto-save working (no data loss)
- [ ] Upload dokumen (akte, KK, KTP, foto) working dengan validation
- [ ] Sistem generate nomor pendaftaran dengan format PSB/2025/XXXX
- [ ] Email & WhatsApp confirmation sent setelah submit registration
- [ ] Public tracking page working (no login, validated dengan tanggal lahir)
- [ ] TU dapat view list registrations dengan filter status
- [ ] TU dapat approve/reject/request revision dokumen dengan notifikasi ke orang tua
- [ ] TU dapat bulk announce hasil seleksi (accept/not accept)
- [ ] Orang tua yang diterima dapat daftar ulang dengan deadline enforcement
- [ ] Auto-create student record di Student Management setelah re-registration verified
- [ ] Auto-create parent user account dengan credentials sent via email
- [ ] Payment formulir & uang gedung recorded di Payment Module
- [ ] Dashboard PSB menampilkan summary, funnel, dan demographics
- [ ] Export data pendaftar ke Excel working dengan filter
- [ ] Admin dapat configure PSB settings (periode, quota, biaya, dokumen)
- [ ] Periode PSB enforcement (form only accessible dalam periode)
- [ ] NIK uniqueness validation (no duplicate registration)

### Non-Functional
- [ ] Mobile-responsive (tested di iOS Safari & Android Chrome)
- [ ] Form submission performance < 3 detik
- [ ] Document upload dengan progress indicator < 5 detik per file
- [ ] Tracking page load < 2 detik
- [ ] User-friendly error messages dalam Bahasa Indonesia
- [ ] Success feedback untuk setiap action
- [ ] File storage secure (not publicly accessible)
- [ ] Rate limiting untuk prevent spam submission
- [ ] Image compression working (reduce file size tanpa quality loss signifikan)

### Integration
- [ ] Integration dengan Student Management: auto-create student working
- [ ] Integration dengan Payment Module: payment records created
- [ ] Integration dengan Notification: email & WhatsApp sent untuk all events
- [ ] Integration dengan User Management: parent account created dengan proper role

### Technical
- [ ] API documentation complete (Swagger)
- [ ] Database schema implemented dengan proper indexes
- [ ] Unit test coverage 70%
- [ ] Integration test untuk critical flows
- [ ] Secure file upload (whitelist file types, max size validation)
- [ ] Error tracking & logging untuk troubleshooting

---

## 🚧 Risks & Mitigation

### Risk 1: File Storage Capacity
**Impact:** High - Banyak dokumen uploaded dapat habiskan storage  
**Probability:** Medium  
**Mitigation:**
- Image compression otomatis (reduce 50-70% size tanpa quality loss)
- Set max file size per dokumen (5MB)
- Use cloud storage dengan scalable capacity (AWS S3, GCS)
- Archive dokumen pendaftar tidak diterima setelah 6 bulan
- Monitor storage usage dengan alert

---

### Risk 2: Email/WhatsApp Delivery Failure
**Impact:** High - Orang tua tidak dapat notifikasi penting  
**Probability:** Medium  
**Mitigation:**
- Use reliable transactional email service (SendGrid, Mailgun)
- Setup proper DNS (SPF, DKIM, DMARC) untuk email deliverability
- Fallback: jika email gagal, try WhatsApp, vice versa
- Notification retry mechanism (max 3x retry)
- Show notification history di tracking page (orang tua dapat lihat walau email gagal)
- Monitor delivery rate dengan alert

---

### Risk 3: High Traffic During Registration Period
**Impact:** High - System slow atau down saat peak registration  
**Probability:** High (especially hari pertama & terakhir periode)  
**Mitigation:**
- Load test sebelum periode PSB
- Scale server capacity (horizontal scaling)
- Use CDN untuk static assets
- Optimize database queries dengan proper indexes
- Implement caching untuk settings & config
- Queue-based processing untuk notification (async)
- Monitor server load dengan auto-scaling

---

### Risk 4: Duplicate Registration (Multiple Attempts)
**Impact:** Medium - Orang tua coba daftar berkali-kali karena bingung  
**Probability:** Medium  
**Mitigation:**
- NIK uniqueness validation (prevent duplicate)
- Clear success page dengan prominent nomor pendaftaran
- Email confirmation immediate dengan tracking link
- Warning modal jika coba submit lagi: "Anda sudah terdaftar dengan nomor PSB/2025/XXXX"
- Admin tool untuk merge duplicate registrations (jika terjadi)

---

### Risk 5: Parent Tidak Tech-Savvy
**Impact:** Medium - Orang tua kesulitan pakai system, call TU untuk help  
**Probability:** High (especially di area rural)  
**Mitigation:**
- Simple, intuitive UI dengan Bahasa Indonesia yang jelas
- Tooltip & help text untuk field yang mungkin membingungkan
- Video tutorial cara daftar (embed di landing page)
- FAQ section di website
- WhatsApp support number untuk assistance
- Fallback: TU dapat bantu input data via admin panel (jika orang tua datang ke sekolah)
- Conduct UAT dengan actual parents sebelum launch

---

### Risk 6: Document Quality Issues
**Impact:** Medium - Foto dokumen blur/tidak jelas, susah verifikasi  
**Probability:** High  
**Mitigation:**
- Preview before upload dengan option retake (mobile camera)
- Client-side validation: min resolution, file size
- Tips di upload page: "Pastikan foto jelas, tidak blur, semua teks dapat dibaca"
- TU dapat request revision dengan specific feedback
- Option untuk orang tua crop/rotate image sebelum upload

---

## 📊 Success Metrics & KPIs

### Sprint 5 (Public Registration)
- [ ] 100% user stories completed (5/5)
- [ ] Zero critical bugs in production
- [ ] Form submission success rate > 95%
- [ ] Average form completion time < 10 menit
- [ ] Mobile users (expected 70%) dapat complete form tanpa issue

### Sprint 6 (Admin Flow & Integration)
- [ ] 100% user stories completed (5/5)
- [ ] Verification process time average < 5 menit per registration
- [ ] Auto-create student success rate 100%
- [ ] Notification delivery rate > 98%
- [ ] Zero data loss during re-registration process

### Epic Level (Post-Launch, First PSB Cycle)
- [ ] Total 24 points delivered
- [ ] 80% pendaftar use online system (vs manual)
- [ ] Conversion rate dari submit ke registered > 70%
- [ ] Admin time reduced 50% vs manual process
- [ ] Parent satisfaction score > 4/5
- [ ] Zero major incidents during PSB period
- [ ] Zero dokumen fisik hilang (vs previous years)

### Business Metrics (First Year)
- [ ] Total pendaftar increase 20% (karena lebih mudah akses)
- [ ] Registered siswa baru sesuai quota (100% filled)
- [ ] Cost saving untuk paper & printing 70%
- [ ] Processing time dari pendaftaran ke registered: < 14 hari (vs 30 hari manual)

---

## 📝 Notes & Assumptions

### Assumptions
1. Email & WhatsApp API sudah disiapkan dan configured
2. File storage (local atau cloud) tersedia dengan capacity cukup
3. Payment gateway (untuk fase 2) akan setup kemudian, MVP pakai manual transfer
4. NIK data dari pendaftar assumed valid (tidak ada verification ke Dukcapil untuk MVP)
5. Orang tua memiliki smartphone atau akses ke komputer untuk pendaftaran
6. Internet connection tersedia untuk orang tua (mobile data atau WiFi)

### Out of Scope (Epic 6 MVP)
- ❌ Payment gateway integration (VA/QRIS) - Phase 2
- ❌ Online test/assessment (CBT) - Phase 2
- ❌ Video interview - Phase 2
- ❌ Integration dengan Dapodik - Phase 2
- ❌ AI document verification (OCR) - Phase 2
- ❌ Chatbot untuk FAQ - Phase 2
- ❌ Referral program - Phase 2
- ❌ SMS notification (WhatsApp & email sufficient untuk MVP)
- ❌ Parent portal login untuk edit data setelah submit (must contact TU)

### Nice to Have (Not Required for MVP)
- Interview scheduling (online booking slot tes/wawancara)
- QR code untuk kwitansi daftar ulang
- Batch import pendaftar from Excel (jika ada data offline)
- Multiple language support (Bahasa Indonesia sufficient)
- Dark mode

### Technical Decisions
- File storage: Start dengan local storage, migrate to S3 jika needed (based on volume)
- Image compression: Client-side compression first (reduce upload time), server-side validation
- Notification: Async queue-based (prevent slow form submission)
- Database: Use PostgreSQL JSONB untuk flexible data storage (dokumen requirements dapat berubah per tahun)

---

## 🔄 Review & Refinement

### Sprint 5 Review
**Date:** TBD (end of sprint 5)  
**Attendees:** Development Team, Product Owner, TU Staff, Sample Parents

**Review Checklist:**
- [ ] Demo registration flow dari mobile & desktop
- [ ] Get feedback dari TU tentang data yang di-capture (apakah sudah lengkap?)
- [ ] Get feedback dari sample parents tentang UX (mudah atau susah?)
- [ ] Test tracking page dengan actual registered data
- [ ] Identify improvement areas untuk Sprint 6
- [ ] Adjust Sprint 6 backlog if needed based on feedback

---

### Sprint 6 Review
**Date:** TBD (end of sprint 6)  
**Attendees:** Development Team, Product Owner, Kepala Sekolah, TU Staff

**Review Checklist:**
- [ ] Demo complete end-to-end PSB flow
- [ ] Demo admin verification flow
- [ ] Demo announcement & re-registration flow
- [ ] Demo dashboard & reports
- [ ] User acceptance testing (UAT) dengan TU
- [ ] Performance review (load test results)
- [ ] Security review (penetration test results)
- [ ] Documentation complete check
- [ ] Go/No-Go decision untuk production launch

---

### Epic Review (Post-Launch, After First PSB Cycle)
**Date:** TBD (setelah PSB cycle selesai)  
**Attendees:** All stakeholders

**Retrospective Questions:**
1. What went well?
   - What features were most appreciated by parents?
   - What features saved most time for TU?
2. What didn't go well?
   - What issues/bugs occurred during PSB period?
   - What complaints from parents?
3. What can we improve for next year?
   - What features to add?
   - What UX to improve?
4. Metrics review:
   - Did we achieve success metrics?
   - Where did we fall short?

**Action Items for Phase 2:**
- List features untuk next PSB cycle
- Prioritize based on impact & effort
- Plan timeline untuk development

---

## ✅ Epic Checklist (Before Production Launch)

### Development
- [ ] All 10 user stories implemented dan tested
- [ ] Code merged to main branch
- [ ] Database migration successful di staging & production
- [ ] API documentation published dan reviewed
- [ ] File storage configured dengan proper permissions

### Testing
- [ ] Unit test pass (coverage 70%)
- [ ] Integration test pass untuk all critical flows
- [ ] E2E test pass (6 critical paths)
- [ ] Security test pass (file upload, tracking validation)
- [ ] Performance test pass (form submission < 3s, upload < 5s)
- [ ] Cross-browser test pass (Chrome, Safari, Firefox di desktop & mobile)
- [ ] UAT approved by TU & sample parents

### Deployment
- [ ] Deployed to staging environment
- [ ] Staging tested dengan production-like data
- [ ] Production database backup ready
- [ ] Rollback plan documented
- [ ] Deployed to production
- [ ] Monitoring & logging active (error tracking, performance monitoring)
- [ ] Alert configured untuk critical issues

### Documentation
- [ ] Technical documentation complete (architecture, database, APIs)
- [ ] User manual untuk Admin/TU (Bahasa Indonesia, dengan screenshots)
- [ ] User manual untuk Orang Tua (Bahasa Indonesia, dengan screenshots)
- [ ] Video tutorial cara daftar (max 5 menit, Bahasa Indonesia)
- [ ] FAQ document ready
- [ ] API documentation complete (Swagger/Postman)

### Marketing & Communication
- [ ] Landing page PSB di website sekolah updated
- [ ] Announcement via social media (Instagram, Facebook, WhatsApp grup)
- [ ] Flyer/poster tentang PSB online printed & distributed
- [ ] Info session untuk parents (online/offline) scheduled
- [ ] WhatsApp broadcast message prepared

### Training & Support
- [ ] Training session untuk TU staff completed (cara verifikasi, announcement, settings)
- [ ] Training session untuk Kepala Sekolah (cara lihat dashboard, reports)
- [ ] Support hotline/WhatsApp number prepared (untuk assist parents)
- [ ] Internal troubleshooting guide untuk common issues
- [ ] Escalation process documented (jika ada critical issue during PSB period)

### Monitoring & Backup
- [ ] Daily database backup configured
- [ ] Server monitoring dengan auto-alert (CPU, memory, disk)
- [ ] Application error monitoring (Sentry, LogRocket)
- [ ] File storage backup configured
- [ ] On-call rotation scheduled untuk support during PSB period

---

## 📞 Contact & Support

**Epic Owner:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733  
**Email:** [Your Email]

**For Technical Issues During PSB Period:**
- WhatsApp: +62 857-1583-8733 (Developer on-call)
- Email: dev-support@sekolah.app
- Response time: < 2 jam (during business hours), < 4 jam (outside)

**For User Support (Parents):**
- WhatsApp: [School Support Number]
- Email: psb@sekolah.app
- Available: Senin-Jumat 08:00-16:00, Sabtu 08:00-12:00

**For Product Questions:**
- Contact Product Owner: [Name]
- Email: product@sekolah.app

**For Emergency (System Down):**
- Call Developer: +62 857-1583-8733
- Escalate to: [IT Manager/CTO]

---

**Document Status:** ✅ Ready for Sprint Planning  
**Last Review:** 13 Desember 2025  
**Next Review:** After Sprint 5 completion

---

*Catatan: Dokumen ini adalah living document dan akan diupdate seiring progress sprint. Semua perubahan akan dicatat di section "Change Log" di bawah.*

## 📋 Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 13 Des 2025 | 1.0 | Initial creation of EPIC 6 document | Zulfikar Hidayatullah |

---

## 🎨 Appendix: Wireframes & Mockups

### A. Registration Form (Mobile View)
```
┌─────────────────────┐
│ [< Back]  PSB 2025  │
│                     │
│ ┌─────────────────┐ │
│ │ ●───○───○───○   │ │  ← Progress: Step 1/4
│ └─────────────────┘ │
│                     │
│ Data Calon Siswa    │
│ ─────────────────── │
│                     │
│ Nama Lengkap *      │
│ ┌─────────────────┐ │
│ │                 │ │
│ └─────────────────┘ │
│                     │
│ NIK (16 digit) *    │
│ ┌─────────────────┐ │
│ │                 │ │
│ └─────────────────┘ │
│ ✓ NIK valid         │
│                     │
│ [... more fields]   │
│                     │
│ ┌─────────────────┐ │
│ │   LANJUT  →     │ │  ← Sticky bottom button
│ └─────────────────┘ │
└─────────────────────┘
```

### B. Document Upload (Mobile View)
```
┌─────────────────────┐
│ Upload Dokumen      │
│                     │
│ ✅ Akte Lahir       │
│ ┌───────────────┐   │
│ │   [Image]     │   │  ← Thumbnail dengan X untuk delete
│ │   [X]         │   │
│ └───────────────┘   │
│                     │
│ ⏸ Kartu Keluarga *  │
│ ┌─────────────────┐ │
│ │  📷 Ambil Foto  │ │
│ │  📁 Pilih File  │ │
│ └─────────────────┘ │
│                     │
│ ⏸ KTP Orang Tua *   │
│ [Upload Area]       │
│                     │
│ ✅ Pas Foto         │
│ [Thumbnail]         │
│                     │
│ ⏸ Rapor (opsional)  │
│ [Upload Area]       │
│                     │
│ ┌─────────────────┐ │
│ │   LANJUT  →     │ │
│ └─────────────────┘ │
└─────────────────────┘
```

### C. Tracking Timeline (Mobile View)
```
┌─────────────────────┐
│ Status Pendaftaran  │
│                     │
│ PSB/2025/0001       │
│ Muhammad Ahmad      │
│                     │
│ ┌─────────────────┐ │
│ │ ✅ Pendaftaran  │ │
│ │    Diterima     │ │
│ │    12 Des 2025  │ │
│ │       ↓         │ │
│ │ ✅ Verifikasi   │ │
│ │    Dokumen      │ │
│ │    13 Des 2025  │ │
│ │       ↓         │ │
│ │ 🔄 Pengumuman   │ │  ← Current step (animated)
│ │    Hasil        │ │
│ │    Menunggu...  │ │
│ │    Est: 20 Des  │ │
│ │       ↓         │ │
│ │ ⏳ Daftar Ulang │ │
│ │       ↓         │ │
│ │ ⏳ Terdaftar    │ │
│ └─────────────────┘ │
│                     │
│ Keterangan:         │
│ Dokumen Anda telah  │
│ diverifikasi. Hasil │
│ seleksi akan        │
│ diumumkan pada      │
│ 20 Desember 2025.   │
│                     │
│ ┌─────────────────┐ │
│ │ 🔔 Subscribe    │ │  ← Subscribe notifikasi
│ │   Notifikasi    │ │
│ └─────────────────┘ │
└─────────────────────┘
```

### D. Admin Verification Detail (Desktop View)
```
┌───────────────────────────────────────────────────┐
│ ← Kembali  │  Verifikasi Pendaftaran              │
│                                                    │
│ ┌─────────────────────┬───────────────────────┐   │
│ │ BIODATA            │ DOKUMEN               │   │
│ │                     │                       │   │
│ │ Nomor:              │ ┌────┬────┬────┐     │   │
│ │ PSB/2025/0001       │ │[📄]│[📄]│[📄]│     │   │ ← Thumbnails
│ │                     │ │Akte│ KK │KTP │     │   │   clickable
│ │ Nama:               │ └────┴────┴────┘     │   │
│ │ Muhammad Ahmad      │                       │   │
│ │                     │ ┌────┬────┐           │   │
│ │ NIK:                │ │[📷]│[📄]│           │   │
│ │ 3273XXXXXXXXXX      │ │Foto│Rprt│           │   │
│ │                     │ └────┴────┘           │   │
│ │ TTL:                │                       │   │
│ │ Bandung, 10-05-2019 │ [Click untuk preview] │   │
│ │                     │                       │   │
│ │ [... more fields]   │                       │   │
│ │                     │                       │   │
│ └─────────────────────┴───────────────────────┘   │
│                                                    │
│ ┌────────────────────────────────────────────┐    │
│ │ Actions:                                   │    │
│ │ [✅ Setujui] [❌ Tolak] [🔄 Minta Revisi] │    │
│ └────────────────────────────────────────────┘    │
└───────────────────────────────────────────────────┘
```

---

**End of EPIC 6 Document**
