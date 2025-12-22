# User Stories: Authentication & Authorization

Module ini mencakup semua fitur terkait login, logout, manajemen akses, dan keamanan sistem.

---

## US-AUTH-001: Login ke Sistem

**As a** User (semua role)  
**I want** login dengan username dan password  
**So that** saya dapat mengakses sistem sesuai dengan hak akses saya

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user membuka halaman login  
   **When** user memasukkan username dan password yang valid  
   **Then** sistem redirect ke dashboard sesuai role user dalam < 2 detik

✅ **Given** user memasukkan username atau password yang salah  
   **When** user klik tombol "Masuk"  
   **Then** sistem menampilkan pesan error "Username atau password salah" dalam Bahasa Indonesia

✅ **Given** user sudah login  
   **When** user coba akses halaman login lagi  
   **Then** sistem redirect ke dashboard (tidak perlu login ulang)

✅ **Given** user idle > 30 menit (15 menit untuk admin)  
   **When** user coba akses halaman lain  
   **Then** sistem auto-logout dan redirect ke halaman login dengan pesan session expired

**Notes:**
- Password harus di-hash menggunakan bcrypt/Argon2
- Implementasi rate limiting: max 5 failed attempts dalam 15 menit
- Support "Remember Me" (opsional, fase 2)

---

## US-AUTH-002: Logout dari Sistem

**As a** User (semua role)  
**I want** logout dari sistem dengan aman  
**So that** orang lain tidak dapat mengakses akun saya

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** user sudah login  
   **When** user klik tombol "Keluar" di menu  
   **Then** sistem logout user dan redirect ke halaman login dalam < 1 detik

✅ **Given** user sudah logout  
   **When** user klik tombol "Back" di browser  
   **Then** sistem tidak menampilkan data dan redirect ke login (session sudah clear)

✅ **Given** user logout  
   **When** sistem selesai proses logout  
   **Then** semua session data dan token dihapus dari browser & server

**Notes:**
- Clear all cookies dan local storage
- Invalidate JWT token di server

---

## US-AUTH-003: Lupa Password

**As a** User yang lupa password  
**I want** reset password saya  
**So that** saya dapat login kembali ke sistem

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user di halaman login  
   **When** user klik link "Lupa Password?"  
   **Then** sistem menampilkan form input email/nomor HP

✅ **Given** user memasukkan email yang terdaftar  
   **When** user klik "Kirim Link Reset"  
   **Then** sistem kirim email dengan link reset password yang valid selama 1 jam

✅ **Given** user klik link reset password dari email  
   **When** link masih valid (< 1 jam)  
   **Then** sistem tampilkan form input password baru

✅ **Given** user input password baru (min 8 karakter)  
   **When** user klik "Simpan Password Baru"  
   **Then** sistem update password dan redirect ke login dengan notifikasi sukses

✅ **Given** user coba akses link reset yang sudah expired  
   **When** user buka link tersebut  
   **Then** sistem tampilkan pesan "Link sudah kadaluarsa, silakan request ulang"

**Notes:**
- Token reset harus unique dan expire dalam 1 jam
- Password baru harus memenuhi policy (min 8 char, kombinasi huruf/angka/simbol)
- Alternative: reset via WhatsApp (fase 2)

---

## US-AUTH-004: Ganti Password (User Sendiri)

**As a** User yang sudah login  
**I want** mengganti password saya sendiri  
**So that** saya dapat menjaga keamanan akun saya

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** user sudah login dan di halaman "Profil Saya"  
   **When** user klik "Ganti Password"  
   **Then** sistem menampilkan form dengan field: Password Lama, Password Baru, Konfirmasi Password Baru

✅ **Given** user mengisi semua field dengan benar  
   **When** user klik "Simpan"  
   **Then** sistem update password dan tampilkan notifikasi "Password berhasil diubah"

✅ **Given** user input password lama yang salah  
   **When** user klik "Simpan"  
   **Then** sistem tampilkan error "Password lama tidak sesuai"

✅ **Given** password baru tidak memenuhi requirement (< 8 karakter)  
   **When** user klik "Simpan"  
   **Then** sistem tampilkan error "Password minimal 8 karakter dengan kombinasi huruf, angka, dan simbol"

**Notes:**
- Validasi password strength di frontend & backend
- Logout user setelah ganti password (opsional, untuk keamanan)

---

## US-AUTH-005: Role-Based Access Control (RBAC)

**As a** System Admin  
**I want** setiap user hanya dapat akses fitur sesuai role mereka  
**So that** data sensitif terlindungi dan tidak ada unauthorized access

**Priority:** Must Have  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** user dengan role "Guru"  
   **When** user coba akses menu "Laporan Keuangan" (hak akses Kepala Sekolah/TU)  
   **Then** sistem tampilkan error 403 "Anda tidak memiliki akses ke halaman ini"

✅ **Given** user dengan role "Orang Tua"  
   **When** user coba langsung akses URL admin (misal: /admin/students/edit/123)  
   **Then** sistem redirect ke halaman "Akses Ditolak"

✅ **Given** user dengan role "TU"  
   **When** user login ke sistem  
   **Then** menu yang tampil hanya sesuai hak akses TU (Student Management, Payment, tidak ada Teacher Evaluation)

✅ **Given** user dengan role "Kepala Sekolah"  
   **When** user login ke sistem  
   **Then** user dapat akses semua modul (read-only untuk beberapa, full access untuk dashboard & reports)

**Notes:**
- Implementasi middleware RBAC di backend
- Permission matrix:
  - **Kepala Sekolah:** Full read access + approval
  - **TU:** CRUD students, payments, PSB
  - **Guru:** Input absensi, nilai, read student info
  - **Orang Tua:** Read-only (info anak sendiri)
  - **Siswa:** Read-only (opsional, fase 2)

---

## US-AUTH-006: Manajemen User Account (Admin)

**As a** System Admin/TU  
**I want** membuat, mengedit, dan menonaktifkan akun user  
**So that** setiap user memiliki akses yang sesuai

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Manajemen User"  
   **When** admin klik "Tambah User Baru"  
   **Then** sistem tampilkan form: Nama, Email, Username, Role, Status (Aktif/Nonaktif)

✅ **Given** admin mengisi data user baru dengan lengkap  
   **When** admin klik "Simpan"  
   **Then** sistem generate password default dan kirim ke email/WhatsApp user

✅ **Given** admin ingin menonaktifkan akun guru yang resign  
   **When** admin ubah status user menjadi "Nonaktif"  
   **Then** user tersebut tidak dapat login dan session aktif langsung terminate

✅ **Given** admin ingin reset password user yang lupa  
   **When** admin klik "Reset Password" pada user tertentu  
   **Then** sistem generate password baru dan kirim ke email/WhatsApp user

**Notes:**
- Password default: kombinasi nama + 4 digit random (contoh: Budi1234)
- User wajib ganti password saat first login
- Log semua aktivitas admin (audit trail)

---

## US-AUTH-007: First Time Login - Force Change Password

**As a** User baru  
**I want** diminta mengganti password default saat pertama kali login  
**So that** akun saya lebih aman

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** user baru login pertama kali dengan password default  
   **When** user berhasil login  
   **Then** sistem langsung redirect ke halaman "Ganti Password Wajib"

✅ **Given** user di halaman "Ganti Password Wajib"  
   **When** user coba skip/back  
   **Then** sistem tidak allow dan tetap di halaman ganti password

✅ **Given** user sudah ganti password baru  
   **When** password memenuhi requirement  
   **Then** sistem update password, tandai akun sebagai "activated", dan redirect ke dashboard

**Notes:**
- Flag `is_first_login` di database user
- Password baru harus berbeda dari password default

---

## US-AUTH-008: Session Management - Multiple Device

**As a** System Admin  
**I want** user dapat login dari 1 device pada satu waktu (opsional: multiple device)  
**So that** tidak ada concurrent login yang tidak sah

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user sudah login di device A (laptop)  
   **When** user login lagi di device B (HP)  
   **Then** session di device A otomatis logout (atau tampil notifikasi "Akun Anda login di tempat lain")

✅ **Given** admin ingin allow multiple device untuk role tertentu  
   **When** admin set config "Allow Multiple Session" untuk role Guru  
   **Then** guru dapat login di laptop dan HP secara bersamaan

**Notes:**
- Session tracking dengan device fingerprint atau IP
- Opsional untuk fase 2, fase 1 bisa single session saja

---

## US-AUTH-009: Audit Log - User Activity

**As a** System Admin/Kepala Sekolah  
**I want** melihat log aktivitas user (login, logout, akses halaman sensitif)  
**So that** saya dapat monitor keamanan sistem

**Priority:** Should Have (Critical untuk data sensitif)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Audit Log"  
   **When** admin filter log berdasarkan user/tanggal  
   **Then** sistem tampilkan list aktivitas: timestamp, user, IP address, action, status (success/failed)

✅ **Given** ada failed login > 5x dari satu IP  
   **When** sistem detect anomali  
   **Then** sistem otomatis block IP tersebut selama 15 menit dan log incident

✅ **Given** user akses halaman sensitif (edit pembayaran, edit nilai)  
   **When** user melakukan perubahan data  
   **Then** sistem log aktivitas: who, what, when, old value, new value

**Notes:**
- Log minimal: login/logout, failed login, data critical (payment, grades)
- Retention: simpan log minimal 6 bulan
- Integrasi dengan monitoring tools (Sentry, LogRocket) - opsional

---

## US-AUTH-010: Multi-Factor Authentication (MFA) untuk Admin

**As a** System Admin  
**I want** aktifkan 2FA untuk akun admin  
**So that** keamanan akun admin lebih tinggi

**Priority:** Could Have (Phase 2)  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** admin sudah login dan di halaman "Keamanan"  
   **When** admin aktifkan 2FA  
   **Then** sistem tampilkan QR code untuk scan dengan authenticator app (Google Authenticator, Authy)

✅ **Given** admin sudah enable 2FA  
   **When** admin login dengan username & password  
   **Then** sistem minta kode OTP 6 digit sebelum akses dashboard

✅ **Given** admin input kode OTP yang benar  
   **When** admin submit kode  
   **Then** sistem allow akses ke dashboard

✅ **Given** admin kehilangan akses ke authenticator app  
   **When** admin klik "Gunakan Backup Code"  
   **Then** sistem allow login dengan salah satu backup code yang diberikan saat setup 2FA

**Notes:**
- Wajib untuk role: System Admin, TU (yang handle payment)
- Opsional untuk role lain
- Generate 10 backup codes saat setup 2FA

---

## Summary: Authentication & Authorization

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-AUTH-001 | Login ke Sistem | Must Have | M | 1 |
| US-AUTH-002 | Logout | Must Have | S | 1 |
| US-AUTH-003 | Lupa Password | Must Have | M | 1 |
| US-AUTH-004 | Ganti Password | Should Have | S | 1 |
| US-AUTH-005 | RBAC | Must Have | L | 1 |
| US-AUTH-006 | Manajemen User | Must Have | M | 1 |
| US-AUTH-007 | First Login Force Change | Should Have | S | 1 |
| US-AUTH-008 | Session Management | Could Have | M | 2 |
| US-AUTH-009 | Audit Log | Should Have | M | 1 |
| US-AUTH-010 | MFA untuk Admin | Could Have | L | 2 |

**Total Estimation Phase 1:** 21 points (~3-4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
