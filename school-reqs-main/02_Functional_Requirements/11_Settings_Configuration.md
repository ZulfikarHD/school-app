# Modul 11: Settings & Configuration (Pengaturan & Konfigurasi)

## 📋 Overview

Modul pengaturan sistem untuk konfigurasi sekolah, tahun ajaran, user management, dan system settings.

**Module Code:** `SET`  
**Priority:** P3 (Low - Support Module)  
**Dependencies:** Authentication

---

## 🎯 Tujuan

1. Centralisasi konfigurasi sistem
2. Fleksibilitas pengaturan tanpa hardcode
3. User management (CRUD users, roles, permissions)
4. Master data management (kelas, mata pelajaran, tahun ajaran)
5. System preferences & customization

---

## 📖 Settings Categories

### 1. School Information Settings

**FR-SET-001: School Profile**
**Priority:** Must Have  
**Description:** Admin dapat mengatur informasi dasar sekolah.

**Fields:**
- Nama Sekolah (text)
- Alamat Lengkap (textarea)
- No. Telp, Email, Website
- Logo Sekolah (upload image, max 2MB, transparent PNG recommended)
- Akreditasi (dropdown: A, B, C, Belum Terakreditasi)
- NPSN (Nomor Pokok Sekolah Nasional)
- NSS (Nomor Statistik Sekolah)
- Nama Kepala Sekolah & NIP
- Tahun Berdiri
- Visi & Misi (textarea, WYSIWYG editor optional)

**Usage:**
- Ditampilkan di website, kwitansi, rapor, slip gaji, semua dokumen resmi
- Edit hanya oleh Admin/Super Admin
- History perubahan tersimpan

---

### 2. Academic Settings

**FR-SET-002: Academic Year Management**
**Priority:** Must Have  
**Description:** Admin dapat mengelola tahun ajaran.

**Fields:**
- Tahun Ajaran (text, format: "2024/2025")
- Semester (dropdown: 1 atau 2)
- Tanggal Mulai & Tanggal Selesai (date range)
- Status (radio: Aktif atau Tidak Aktif)

**Business Logic:**
- Hanya 1 tahun ajaran & semester yang bisa aktif
- Semua transaksi (absensi, nilai, pembayaran) mengacu ke tahun ajaran aktif
- Saat ganti tahun ajaran baru, data tahun lama tetap tersimpan (archive)

**CRUD:** Create, Read, Update (tidak ada Delete, hanya set Tidak Aktif)

---

**FR-SET-003: Class Management**
**Priority:** Must Have  
**Description:** Admin dapat mengelola master data kelas.

**Fields:**
- Tingkat (dropdown: 1, 2, 3, 4, 5, 6)
- Nama Kelas (text, e.g., "A", "B") → auto-generate full name: "1A", "1B"
- Wali Kelas (dropdown: pilih guru)
- Kapasitas (number, max siswa per kelas, e.g., 30)
- Tahun Ajaran (link ke tahun ajaran aktif)

**CRUD:** Create, Read, Update, Soft Delete

**Usage:**
- Digunakan untuk assign siswa ke kelas
- Digunakan untuk jadwal mengajar
- Tampil di dropdown di berbagai modul

---

**FR-SET-004: Subject Management**
**Priority:** Must Have  
**Description:** Admin dapat mengelola master data mata pelajaran.

**Fields:**
- Kode Mata Pelajaran (text, e.g., "MTK", "IPA")
- Nama Mata Pelajaran (text, e.g., "Matematika", "IPA")
- Kategori (dropdown: Wajib, Muatan Lokal, Ekstra)
- KKM (Kriteria Ketuntasan Minimal, number 0-100, default: 70)
- Tingkat (multi-select: kelas berapa saja yang ada mapel ini)

**CRUD:** Create, Read, Update, Soft Delete

**Usage:**
- Assign ke guru (guru mengajar mapel apa)
- Input nilai (pilih mapel)
- Jadwal mengajar
- Rapor (list semua mapel)

---

### 3. Financial Settings

**FR-SET-005: Payment Configuration**
**Priority:** Must Have  
**Description:** Admin dapat mengatur konfigurasi pembayaran.

**Fields:**
- **SPP Settings:**
  - Harga SPP per Kelas (table: kelas → harga, bisa sama atau beda)
  - Tanggal Jatuh Tempo (number, e.g., tanggal 10 setiap bulan)
  - Denda Keterlambatan (checkbox: aktif/tidak, jumlah denda per hari jika aktif)
  
- **Payment Reminder Schedule:**
  - H-5 Reminder: aktif/tidak aktif
  - Due Date Reminder: aktif/tidak aktif
  - H+7 Warning: aktif/tidak aktif
  
- **Bank Account Info (untuk tampil di portal pembayaran):**
  - Nama Bank, Nomor Rekening, Atas Nama
  
- **Payment Gateway (Phase 2):**
  - Enable/Disable
  - Provider (Midtrans, Xendit, dll)
  - API Keys (encrypted)

**Access:** Admin/Super Admin only (sensitive data)

---

### 4. User Management

**FR-SET-006: User CRUD**
**Priority:** Must Have  
**Description:** Admin dapat mengelola user accounts.

**Features:**
- **List Users:** Table dengan columns (Username, Email, Role, Status, Actions)
- **Create User:**
  - Username, Email, Password (auto-generate atau manual)
  - Role (dropdown: Super Admin, Principal, Admin, Teacher, Parent)
  - Link to Guru/Siswa (jika role Teacher/Parent)
  - Status (Aktif/Non-aktif)
- **Update User:**
  - Edit info (kecuali username)
  - Reset password (generate new password & send via email/WA)
  - Deactivate/Activate account
- **Delete User:** Soft delete (set deleted_at)

**Access Control:**
- Super Admin: full access semua user
- Admin: tidak bisa edit Super Admin
- Principal: read-only

**Business Logic:**
- Username unik
- Email unik
- Password auto-generate (atau set manual): min 8 karakter
- Setelah create, send credentials via email/WA

---

### 5. Notification Settings

**FR-SET-007: Notification Configuration**
**Priority:** Should Have  
**Description:** Admin dapat mengatur konfigurasi notifikasi.

**Fields:**
- **WhatsApp API:**
  - Provider (dropdown: Fonnte, Wablas, WA Business API, Custom)
  - API URL & API Key (text, encrypted)
  - Test Connection (button → send test message)
  
- **Email SMTP:**
  - SMTP Host, Port, Username, Password
  - From Name, From Email
  - Test Connection (button → send test email)
  
- **Notification Schedule:**
  - Enable/Disable per jenis notification (checkboxes)
  - Customize schedule (e.g., Reminder H-5 ganti jadi H-7)

**Access:** Super Admin only (API keys sensitive)

---

### 6. System Preferences

**FR-SET-008: General System Settings**
**Priority:** Should Have  
**Description:** Admin dapat mengatur preferensi sistem.

**Fields:**
- **Locale:**
  - Timezone (dropdown: Asia/Jakarta)
  - Date Format (dropdown: DD/MM/YYYY, MM/DD/YYYY, YYYY-MM-DD)
  - Currency (dropdown: IDR - Rupiah)
  
- **Features Toggle:**
  - Enable/Disable Ranking (checkbox: tampilkan ranking di rapor atau tidak)
  - Enable/Disable PSB (checkbox: buka/tutup pendaftaran)
  - Enable/Disable Payment Gateway (checkbox)
  
- **Pagination:**
  - Items per Page (number, default: 20)
  
- **Session:**
  - Session Duration (number, in hours, default: 24)
  - Max Concurrent Sessions per User (number, default: 3)

**Access:** Super Admin only

---

### 7. Backup & Restore (Future Phase)

**FR-SET-009: Database Backup**
**Priority:** Could Have (Nice to Have)  
**Description:** Admin dapat backup database secara manual atau auto.

**Features:**
- **Manual Backup:** Button "Backup Now" → generate SQL dump atau full backup
- **Auto Backup Schedule:** Daily/Weekly (configurable)
- **Backup Storage:** Local server atau Cloud (S3, Google Drive)
- **Restore:** Upload backup file untuk restore (caution: overwrite existing data)

**Access:** Super Admin only

---

## 📏 Business Rules

### BR-SET-001: Active Academic Year
- Hanya 1 tahun ajaran & semester yang boleh aktif
- Sebelum activate tahun ajaran baru, harus deactivate yang lama
- Semua modul (absensi, nilai, pembayaran) otomatis menggunakan tahun ajaran aktif

### BR-SET-002: Master Data Immutability
- Master data yang sudah digunakan (e.g., kelas yang sudah ada siswanya) tidak bisa di-delete
- Hanya bisa soft delete (non-aktifkan)

### BR-SET-003: Sensitive Settings
- API keys, passwords disimpan encrypted di database
- Hanya Super Admin yang bisa view/edit

---

## ✅ Validation Rules

**School Name:** Required, min 5 karakter  
**Logo:** Optional, image file (jpg/png), max 2MB  
**NPSN:** Required, 8 digit angka  
**Academic Year:** Required, format "YYYY/YYYY" (e.g., "2024/2025")  
**SPP Amount:** Required, number, min Rp 10,000  
**Email SMTP:** Required jika enable email notification, valid email format  
**API Key:** Required jika enable WhatsApp notification, min 10 karakter  

---

## 🎨 UI/UX Requirements

**Layout:**
- Settings menu di sidebar atau navbar (icon: gear)
- Tab navigation untuk categories:
  - Sekolah (School Info)
  - Akademik (Academic: tahun ajaran, kelas, mapel)
  - Keuangan (Financial)
  - Pengguna (User Management)
  - Notifikasi (Notification)
  - Sistem (System Preferences)
  - Backup (Backup & Restore)
- Per tab: form fields dengan sections
- Save button (per section atau global "Simpan Perubahan")
- Success notification setelah save

**Access Control Indicator:**
- Di UI, tampilkan badge "Super Admin Only" untuk settings yang sensitive
- Non-Super Admin tidak bisa view halaman settings tersebut (redirect atau hide menu)

**Mobile:**
- Tab jadi dropdown select
- Form fields stack vertically
- Large save button (sticky footer)

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ School Information (nama, alamat, logo, visi/misi, kepala sekolah)
- ✅ Academic Year Management (CRUD tahun ajaran, set aktif)
- ✅ Class Management (CRUD kelas, assign wali kelas)
- ✅ Subject Management (CRUD mata pelajaran, KKM)
- ✅ Payment Configuration (harga SPP, due date, rekening bank)
- ✅ User Management (CRUD user, reset password, activate/deactivate)
- ✅ Notification Configuration (WhatsApp API, Email SMTP, test connection)

### Should Have (MVP):
- ✅ General System Settings (timezone, date format, features toggle)
- ✅ Notification schedule customization
- ✅ Settings audit log (track who changed what)

### Could Have (Nice to Have):
- ⬜ Backup & Restore (manual & auto backup)
- ⬜ Theme customization (school branding colors)
- ⬜ Email template editor (WYSIWYG untuk customize template)
- ⬜ Import master data from Excel (kelas, mapel, siswa bulk import)

### Won't Have (Phase 2):
- ⬜ Multi-school support (1 sistem untuk multiple sekolah)
- ⬜ Plugin system (extend functionality via plugins)
- ⬜ API management (untuk third-party integration)
- ⬜ Advanced permission management (role-based permission granular)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft

