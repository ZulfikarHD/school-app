# Modul 6: New Student Registration / PSB Online (Penerimaan Siswa Baru)

## 📋 Overview

Modul ini menangani proses penerimaan siswa baru secara online, dari pendaftaran, upload dokumen, verifikasi, hingga daftar ulang dan pembayaran formulir.

**Module Code:** `PSB`  
**Priority:** P2 (Medium)  
**Dependencies:** Authentication, Student Management, Payment System, Notification System

---

## 🎯 Tujuan

1. Digitalisasi proses PSB dari manual ke online
2. Memperluas jangkauan calon siswa (tidak harus datang ke sekolah)
3. Efisiensi proses verifikasi dokumen
4. Tracking status pendaftaran real-time
5. Otomasi komunikasi dengan calon orang tua
6. Database calon siswa untuk follow-up

---

## 📖 User Stories

### US-PSB-001: Pendaftaran Online (Calon Orang Tua)
```
Sebagai Calon Orang Tua,
Saya ingin mendaftarkan anak saya secara online,
Sehingga tidak perlu datang ke sekolah untuk pendaftaran awal
```

**Acceptance Criteria:**
- ✅ Form pendaftaran online accessible tanpa login (public)
- ✅ Input data calon siswa & orang tua
- ✅ Upload dokumen persyaratan (foto/PDF)
- ✅ Submit form
- ✅ Dapatkan nomor pendaftaran & email konfirmasi
- ✅ Dapat track status pendaftaran via nomor pendaftaran

---

### US-PSB-002: Verifikasi Pendaftaran (TU/Admin)
```
Sebagai Staf TU,
Saya ingin memverifikasi dokumen calon siswa,
Sehingga hanya pendaftar yang lengkap dokumentasinya yang lanjut ke tahap berikutnya
```

**Acceptance Criteria:**
- ✅ List pendaftaran baru (status: Pending)
- ✅ View detail pendaftaran & dokumen yang diupload
- ✅ Action: Approve atau Reject
- ✅ Jika reject: input alasan (akan dikirim ke orang tua)
- ✅ Jika approve: status berubah, notifikasi ke orang tua

---

### US-PSB-003: Pengumuman Hasil Seleksi
```
Sebagai TU/Admin,
Saya ingin mengumumkan hasil seleksi PSB,
Sehingga calon siswa yang diterima dapat melanjutkan daftar ulang
```

**Acceptance Criteria:**
- ✅ Bulk action: set status pendaftar jadi "Diterima" atau "Tidak Diterima"
- ✅ Notifikasi otomatis ke orang tua via WhatsApp/Email
- ✅ Orang tua dapat cek status via portal atau tracking page

---

### US-PSB-004: Daftar Ulang & Pembayaran Formulir
```
Sebagai Calon Orang Tua yang Diterima,
Saya ingin melakukan daftar ulang dan pembayaran formulir,
Sehingga anak saya resmi terdaftar sebagai siswa
```

**Acceptance Criteria:**
- ✅ Setelah diterima, orang tua dapatkan instruksi daftar ulang
- ✅ Form daftar ulang (konfirmasi data, upload dokumen tambahan jika ada)
- ✅ Pembayaran formulir (manual: transfer & konfirmasi atau online: payment gateway)
- ✅ Setelah pembayaran dikonfirmasi, status jadi "Terdaftar"
- ✅ Data siswa otomatis masuk ke Student Management Module

---

### US-PSB-005: Track Status Pendaftaran (Public)
```
Sebagai Calon Orang Tua,
Saya ingin mengecek status pendaftaran anak saya,
Sehingga saya tahu sampai tahap mana proses pendaftaran
```

**Acceptance Criteria:**
- ✅ Halaman public "Cek Status Pendaftaran"
- ✅ Input nomor pendaftaran
- ✅ Tampilkan status: Pending Verifikasi → Diverifikasi → Diterima/Tidak Diterima → Daftar Ulang → Terdaftar
- ✅ Timeline visual dengan status saat ini
- ✅ Keterangan per tahap

---

## ⚙️ Functional Requirements

### FR-PSB-001: Online Registration Form (Public)
**Priority:** Must Have  
**Description:** Calon orang tua dapat mendaftar secara online tanpa perlu login.

**Details:**
**Form Fields:**
- **Data Calon Siswa:**
  - Nama Lengkap (required)
  - Nama Panggilan (optional)
  - NIK (required, 16 digit)
  - NISN (optional, 10 digit - jika pindahan)
  - Jenis Kelamin (required, radio: L/P)
  - Tempat Lahir (required)
  - Tanggal Lahir (required, date picker)
  - Agama (required, dropdown)
  - Anak ke- (required, number)
  - Alamat Lengkap (required, textarea)
  - Asal Sekolah (optional, untuk siswa pindahan/dari TK)
  
- **Data Orang Tua (Ayah):**
  - Nama Lengkap (required)
  - NIK (required)
  - Pekerjaan (required)
  - Nomor HP (required)
  - Email (required)
  
- **Data Orang Tua (Ibu):**
  - Similar dengan Ayah
  
- **Upload Dokumen:**
  - Akte Lahir/Akta Kelahiran (required, image/PDF, max 5MB)
  - Kartu Keluarga (required, image/PDF)
  - Fotokopi KTP Orang Tua (required, image/PDF)
  - Rapor/Ijazah (optional, jika pindahan)
  - Pas Foto (required, image, max 2MB)
  - Surat Pindah (optional, jika pindahan)

**Process:**
1. Calon orang tua akses halaman PSB dari website sekolah
2. Fill form pendaftaran (multi-step atau long form)
3. Upload dokumen (drag & drop atau file picker)
4. Review data sebelum submit
5. Submit
6. Sistem:
   - Validasi data
   - Generate nomor pendaftaran (format: `PSB/{tahun}/{nomor_urut}`, e.g., PSB/2025/0001)
   - Save data ke `psb_registrations` table dengan status: Pending
   - Send email konfirmasi dengan nomor pendaftaran & link tracking
   - Send WhatsApp notification (optional)
7. Success page: "Pendaftaran berhasil. Nomor pendaftaran Anda: {nomor}. Simpan nomor ini untuk cek status."

**Business Logic:**
- Pendaftaran dibuka pada periode tertentu (configurable di settings: tanggal buka & tutup PSB)
- Di luar periode, form tidak accessible (tampilkan message "PSB belum/sudah ditutup")
- NIK calon siswa harus unik (tidak boleh duplikat pendaftaran)
- Semua dokumen wajib diupload kecuali yang optional

---

### FR-PSB-002: Registration Verification (TU)
**Priority:** Must Have  
**Description:** TU dapat memverifikasi pendaftaran dan dokumen calon siswa.

**Details:**
**List View:**
- Table pendaftaran dengan columns: Nomor, Nama Calon Siswa, Tanggal Daftar, Status, Actions
- Filter: Status (Pending/Approved/Rejected), Tanggal (range)
- Search: by nama atau nomor pendaftaran
- Badge count: "X pendaftaran pending"

**Detail View:**
- Biodata calon siswa & orang tua (read-only)
- Section dokumen dengan preview (thumbnail, click untuk view full)
- Per dokumen: preview image/PDF, status (uploaded/missing), download button
- Action buttons:
  - "Approve" (hijau)
  - "Reject" (merah)
  - "Request Revisi" (kuning) - minta orang tua upload ulang dokumen tertentu

**Approval Process:**
1. TU buka detail pendaftaran
2. Review data & dokumen (click thumbnail untuk lihat full)
3. Jika ok → Click "Approve"
   - Konfirmasi
   - Update status jadi "Approved"
   - Notifikasi ke orang tua: "Pendaftaran Anda telah diverifikasi. Menunggu pengumuman hasil seleksi."
4. Jika tidak ok → Click "Reject"
   - Modal input alasan (required)
   - Update status jadi "Rejected"
   - Notifikasi ke orang tua: "Pendaftaran ditolak. Alasan: {alasan}"
5. Jika dokumen kurang/salah → Click "Request Revisi"
   - Input dokumen mana yang perlu direvisi & alasan
   - Status jadi "Revisi"
   - Notifikasi ke orang tua dengan link untuk upload ulang

**Business Logic:**
- Hanya TU & Super Admin yang bisa verifikasi
- Log semua approval/rejection (audit trail)
- Orang tua dapat resubmit jika status Rejected (buat pendaftaran baru atau edit existing - configurable)

---

### FR-PSB-003: Selection Announcement
**Priority:** Must Have  
**Description:** TU dapat mengumumkan hasil seleksi PSB secara bulk.

**Details:**
**Selection Process:**
1. TU buka halaman "Pengumuman PSB"
2. List semua pendaftaran yang sudah Approved
3. Filter/search untuk identifikasi siapa yang diterima (based on criteria: nilai, kuota, dll)
4. Bulk action: select multiple pendaftaran
5. Action: "Terima" atau "Tidak Terima"
6. Konfirmasi: "{X} pendaftar akan diumumkan sebagai Diterima. Lanjutkan?"
7. Click "Ya"
8. Sistem:
   - Update status pendaftaran jadi "Accepted" atau "Not Accepted"
   - Send notifikasi ke semua orang tua:
     - **Diterima:** "Selamat! Anak Anda {nama} diterima sebagai siswa baru. Silakan lakukan daftar ulang paling lambat {tanggal}. Link: {url}"
     - **Tidak Diterima:** "Mohon maaf, anak Anda {nama} belum dapat kami terima pada periode ini. Terima kasih atas partisipasinya."

**Business Logic:**
- Announcement bisa dilakukan multiple batch (tidak harus semua sekaligus)
- Deadline daftar ulang configurable (e.g., 7 hari setelah announcement)
- Jika melewati deadline tanpa daftar ulang, status jadi "Expired" (quota bisa ke waiting list)

---

### FR-PSB-004: Re-Registration & Payment
**Priority:** Must Have  
**Description:** Calon orang tua yang diterima dapat melakukan daftar ulang dan pembayaran.

**Details:**
**Process:**
1. Orang tua klik link dari notifikasi atau login ke portal PSB (auto-create account)
2. Halaman "Daftar Ulang" dengan info:
   - Selamat, Anda diterima!
   - Deadline daftar ulang: {tanggal}
   - Biaya formulir: Rp {jumlah}
3. Form daftar ulang:
   - Konfirmasi data calon siswa (pre-filled, editable jika ada koreksi)
   - Upload dokumen tambahan (jika ada, e.g., surat keterangan sehat)
   - Checkbox: "Saya setuju dengan peraturan sekolah"
4. Pembayaran formulir:
   - **Option 1 (MVP): Manual**
     - Tampilkan rekening sekolah
     - Upload bukti transfer
     - Submit
     - Status: Menunggu konfirmasi TU
   - **Option 2 (Phase 2): Payment Gateway**
     - Generate virtual account atau payment link
     - Redirect ke payment page
     - Auto-confirm setelah payment berhasil
5. Setelah submit daftar ulang:
   - Status jadi "Re-Registered - Waiting Payment Confirmation"
6. Setelah TU konfirmasi pembayaran:
   - Status jadi "Registered" (resmi terdaftar)
   - Auto-create student record di Student Management Module
   - Auto-generate NIS
   - Auto-create parent account (jika belum ada)
   - Send welcome notification: "Selamat! {Nama} resmi terdaftar sebagai siswa. NIS: {NIS}, Username Orang Tua: {username}, Password: {password}. Tahun ajaran dimulai {tanggal}."

**Business Logic:**
- Daftar ulang hanya bisa dilakukan jika status "Accepted"
- Deadline enforcement: jika lewat deadline, status jadi "Expired", quota released
- Payment confirmation oleh TU dalam 1x24 jam (max)
- Setelah terdaftar, data tidak bisa diubah (harus minta TU)

---

### FR-PSB-005: Registration Status Tracking (Public)
**Priority:** Must Have  
**Description:** Calon orang tua dapat mengecek status pendaftaran via portal public.

**Details:**
**Tracking Page:**
- URL: `/psb/tracking` atau `/psb/cek-status`
- Form input: Nomor Pendaftaran, Tanggal Lahir Anak (untuk validasi)
- Submit
- Jika valid, tampilkan status page:
  - Header: Nomor Pendaftaran, Nama Calon Siswa
  - Timeline (stepper UI):
    1. ✅ Pendaftaran Diterima (tanggal)
    2. 🔄 Verifikasi Dokumen (status: Pending/Approved/Rejected/Revisi)
    3. ⏳ Pengumuman Hasil Seleksi (tanggal pengumuman: {date} atau "Menunggu")
    4. ⏳ Daftar Ulang (deadline: {date})
    5. ⏳ Terdaftar
  - Keterangan per step (e.g., "Dokumen Anda sedang diverifikasi oleh TU. Mohon tunggu 1-3 hari kerja.")
  - Action button (jika ada): e.g., "Upload Ulang Dokumen" (jika status Revisi), "Daftar Ulang" (jika Accepted)

**Business Logic:**
- Tracking public (no login required)
- Validasi: nomor pendaftaran + tanggal lahir must match
- Real-time status (updated setelah TU approval/action)

---

### FR-PSB-006: PSB Settings & Configuration
**Priority:** Should Have  
**Description:** Admin dapat mengatur periode PSB dan konfigurasi terkait.

**Details:**
**Settings:**
- Periode PSB:
  - Tanggal Buka Pendaftaran (date)
  - Tanggal Tutup Pendaftaran (date)
  - Tanggal Pengumuman (date)
  - Deadline Daftar Ulang (X hari setelah pengumuman)
- Quota:
  - Quota per kelas (e.g., Kelas 1: 60 siswa = 2 kelas × 30)
  - Waiting list (enable/disable)
- Biaya:
  - Biaya Formulir (Rp amount)
- Persyaratan:
  - List dokumen wajib (editable)
  - Usia minimal (e.g., 6 tahun untuk kelas 1)
- Notification:
  - Email template (customize)
  - WhatsApp message template
  
**Business Logic:**
- Settings hanya bisa diubah oleh Admin/Super Admin
- Perubahan periode tidak berlaku retroactive (tidak affect pendaftaran existing)

---

### FR-PSB-007: PSB Reports & Analytics
**Priority:** Should Have  
**Description:** TU & Principal dapat melihat laporan PSB.

**Details:**
**Reports:**
1. **Summary:**
   - Total Pendaftar
   - Pending Verifikasi
   - Approved
   - Rejected
   - Accepted
   - Registered
   - Conversion Rate (Registered / Total Pendaftar)

2. **Demographics:**
   - Chart: Pendaftar by Gender (L/P)
   - Chart: Pendaftar by Asal Sekolah (TK A, TK B, Pindahan, dll)
   - Chart: Pendaftar by Agama
   - Table: Pendaftar by Alamat (Kelurahan/Kecamatan) - untuk analisa lokasi

3. **Timeline:**
   - Chart: Pendaftar per hari/minggu (line chart)
   - Peak days (hari dengan pendaftar terbanyak)

**Export:**
- Excel: full data pendaftar
- PDF: summary report untuk presentasi

---

## 📏 Business Rules

### BR-PSB-001: Registration Period
- Pendaftaran hanya bisa dilakukan dalam periode yang ditentukan (tanggal buka - tutup)
- Di luar periode, form tidak accessible

### BR-PSB-002: Registration Number
- Format: `PSB/{tahun}/{nomor_urut}` (e.g., PSB/2025/0001)
- Nomor urut increment per tahun ajaran
- Nomor tidak bisa duplikat

### BR-PSB-003: Document Requirements
- Semua dokumen wajib harus diupload sebelum submit
- Format dokumen: image (jpg/png) atau PDF
- Max file size: 5MB per file

### BR-PSB-004: Age Requirement
- Calon siswa kelas 1 minimal 6 tahun (berdasarkan tanggal lahir vs tanggal masuk)
- Validasi otomatis saat submit form

### BR-PSB-005: Duplicate Prevention
- NIK calon siswa tidak boleh duplikat (cek di database PSB dan Student Management)
- Jika duplikat, tampilkan error: "NIK sudah terdaftar. Hubungi sekolah jika ada masalah."

### BR-PSB-006: Quota Management
- Jika quota penuh, pendaftaran tetap bisa (masuk waiting list) atau tutup (configurable)
- Prioritas: first-come-first-served atau selection-based (configurable)

---

## ✅ Validation Rules

### VR-PSB-001: Registration Form

**Nama Lengkap:**
- Required
- Min 3 karakter
- Error: "Nama lengkap wajib diisi"

**NIK:**
- Required
- Exactly 16 digit
- Unik (cek duplikat)
- Error: "NIK wajib diisi 16 digit", "NIK sudah terdaftar"

**Tanggal Lahir:**
- Required
- Valid date
- Usia minimal 6 tahun (untuk kelas 1)
- Error: "Tanggal lahir wajib diisi", "Usia minimal 6 tahun"

**Nomor HP:**
- Required
- Format Indonesia
- Error: "Nomor HP wajib diisi", "Format nomor HP tidak valid"

**Email:**
- Required
- Valid email format
- Error: "Email wajib diisi", "Format email tidak valid"

**Upload Dokumen:**
- Required (untuk dokumen wajib)
- File type: jpg, png, pdf
- Max size: 5MB
- Error: "Dokumen wajib diupload", "Format file tidak valid", "Ukuran file maksimal 5MB"

---

## 🎨 UI/UX Requirements

### PSB Registration Form (Public)

**Layout:**
- Landing: Hero section dengan CTA "Daftar Sekarang"
- Multi-step form:
  - Step 1: Data Calon Siswa
  - Step 2: Data Orang Tua
  - Step 3: Upload Dokumen
  - Step 4: Review & Submit
- Progress bar di atas (visual step indicator)
- Navigation: "Sebelumnya" dan "Lanjut" button

**UX:**
- Mobile-first design (banyak orang tua pakai HP)
- Auto-save draft (localStorage) untuk prevent data loss
- Field validation real-time
- Upload drag & drop dengan preview
- Character counter untuk textarea
- Tooltip untuk field yang mungkin kurang jelas
- Success page dengan konfetti animation

**Mobile:**
- Single column layout
- Large input field (min 48px height)
- Native date picker & dropdown
- Camera option untuk upload dokumen (take photo langsung)
- Sticky footer dengan button "Lanjut"

---

### Verification Dashboard (TU)

**Layout:**
- Header: "Verifikasi Pendaftaran PSB" dengan badge count pending
- Filter & search bar
- Table/Card list pendaftaran
- Per row/card: Nomor, Foto calon siswa, Nama, Tanggal Daftar, Status badge, Actions (View)
- Pagination

**Detail View:**
- Modal atau full page
- Left side: Biodata (scroll vertical)
- Right side: Dokumen (thumbnail grid, click untuk lightbox preview)
- Bottom: Action buttons (Approve, Reject, Request Revisi)

**UX:**
- Quick filter: "Pending", "Approved", "Rejected"
- Bulk action: select multiple untuk approval sekaligus (advanced)
- Keyboard shortcut: A=Approve, R=Reject (for efficiency)
- Swipe actions di mobile (swipe right=approve, left=reject)

---

### Tracking Page (Public)

**Layout:**
- Clean, simple design
- Form di tengah: Input nomor pendaftaran & tanggal lahir
- Submit
- Result: Timeline stepper dengan status saat ini di-highlight
- Color coding: Done=hijau, Current=biru, Pending=abu

**UX:**
- Large input field dengan icon
- Loading indicator saat submit
- Timeline animated (step-by-step reveal)
- Share button: copy link tracking (jika public)
- Mobile-friendly timeline (vertical stepper)

---

## 🔗 Integration Points

### INT-PSB-001: Student Management Module
- Setelah daftar ulang sukses, create student record otomatis
- Transfer data from PSB registration ke student profile

### INT-PSB-002: Payment Module
- Payment formulir recorded di Payment Module
- Link payment record ke PSB registration

### INT-PSB-003: Notification Module
- Send email/WhatsApp notification untuk setiap status change
- Template notification configurable

### INT-PSB-004: School Website
- PSB form embedded atau linked dari website
- Landing page PSB dengan informasi lengkap

### INT-PSB-005: Analytics
- Data PSB untuk dashboard & reporting
- Demographics untuk strategi marketing tahun depan

---

## 🧪 Test Scenarios

### TS-PSB-001: Submit Registration
1. Akses halaman PSB dari website sekolah
2. Fill form step by step (data siswa, orang tua, upload dokumen)
3. Review data
4. Submit
5. **Expected:**
   - Success page dengan nomor pendaftaran: PSB/2025/0001
   - Email konfirmasi dikirim
   - Data tersimpan dengan status Pending

### TS-PSB-002: Duplicate NIK Prevention
1. Submit pendaftaran dengan NIK: 1234567890123456
2. Coba submit lagi dengan NIK yang sama
3. **Expected:** Error "NIK sudah terdaftar"

### TS-PSB-003: TU Approve Registration
1. Login sebagai TU
2. Go to "Verifikasi PSB"
3. List pending: 1 pendaftaran
4. Click "View" detail
5. Review dokumen (semua lengkap & valid)
6. Click "Approve"
7. Konfirmasi
8. **Expected:**
   - Status jadi Approved
   - Notifikasi email/WhatsApp ke orang tua
   - Pendaftaran hilang dari list pending

### TS-PSB-004: TU Reject Registration
1. View detail pendaftaran dengan dokumen tidak valid
2. Click "Reject"
3. Input alasan: "Foto akte lahir tidak jelas"
4. Submit
5. **Expected:**
   - Status jadi Rejected
   - Notifikasi ke orang tua dengan alasan
   - Orang tua dapat submit ulang (jika allowed)

### TS-PSB-005: Bulk Announcement
1. Login sebagai TU
2. Go to "Pengumuman PSB"
3. List 50 pendaftaran Approved
4. Select 40 pendaftaran (diterima), 10 tidak
5. Bulk action: "Terima" untuk 40 pendaftaran
6. Konfirmasi
7. **Expected:**
   - 40 pendaftaran status jadi Accepted
   - Notifikasi "Diterima" ke 40 orang tua
   - 10 pendaftaran tetap Approved (belum diumumkan)

### TS-PSB-006: Re-Registration & Payment
1. Login sebagai Orang Tua (status Accepted)
2. Dashboard: "Selamat diterima! Lakukan daftar ulang"
3. Click "Daftar Ulang"
4. Konfirmasi data, checkbox setuju
5. Upload bukti transfer pembayaran formulir
6. Submit
7. **Expected:**
   - Status jadi Re-Registered - Waiting Payment Confirmation
   - TU dapat konfirmasi payment
8. TU konfirmasi payment
9. **Expected:**
   - Status jadi Registered
   - Data siswa otomatis masuk Student Management
   - NIS auto-generated
   - Welcome email dengan credentials

### TS-PSB-007: Track Status
1. Akses halaman Tracking (public, no login)
2. Input nomor pendaftaran: PSB/2025/0001
3. Input tanggal lahir: 10 Mei 2019
4. Submit
5. **Expected:**
   - Timeline muncul dengan status saat ini:
     - ✅ Pendaftaran Diterima (12 Des 2025)
     - ✅ Verifikasi Dokumen (13 Des 2025 - Approved)
     - 🔄 Pengumuman Hasil Seleksi (Menunggu, estimasi 20 Des 2025)
     - ⏳ Daftar Ulang
     - ⏳ Terdaftar

### TS-PSB-008: Registration Outside Period
1. PSB period: 1 Jan - 31 Jan 2025
2. Akses form PSB pada 15 Feb 2025 (setelah tutup)
3. **Expected:**
   - Form tidak accessible
   - Message: "Pendaftaran siswa baru sudah ditutup. Tunggu informasi periode berikutnya."

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Online registration form (public, multi-step)
- ✅ Upload dokumen (akte, KK, KTP, foto, dll)
- ✅ Auto-generate nomor pendaftaran
- ✅ TU verification dashboard (approve/reject)
- ✅ Selection announcement (bulk accept/reject)
- ✅ Re-registration & payment confirmation (manual transfer)
- ✅ Auto-create student record setelah registered
- ✅ Status tracking (public page)
- ✅ Email/WhatsApp notification per status change
- ✅ PSB settings (periode, quota, biaya)

### Should Have (MVP):
- ✅ Request revisi dokumen (TU minta orang tua upload ulang)
- ✅ PSB reports & analytics (summary, demographics, timeline)
- ✅ Export data pendaftar to Excel
- ✅ Waiting list management (jika quota penuh)
- ✅ Deadline enforcement untuk daftar ulang

### Could Have (Nice to Have):
- ⬜ Payment gateway integration untuk formulir (VA/QRIS)
- ⬜ Interview scheduling (online booking slot tes/wawancara)
- ⬜ Test score input (jika ada tes masuk)
- ⬜ Auto-ranking based on test score
- ⬜ Batch import pendaftar from Excel (jika ada data offline)
- ⬜ QR code untuk kwitansi daftar ulang
- ⬜ Parent portal login untuk edit data (setelah submit)

### Won't Have (Phase 2):
- ⬜ Online test/assessment (CBT untuk tes masuk)
- ⬜ Video interview
- ⬜ Integration dengan Dapodik (sync data siswa baru)
- ⬜ AI document verification (OCR untuk validasi KTP/akte)
- ⬜ Chatbot untuk FAQ PSB
- ⬜ Referral program (diskon jika refer teman)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft - Ready for Review

