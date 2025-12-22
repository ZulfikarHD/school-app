# Modul 1: Authentication & Authorization

## 📋 Overview

Modul ini menangani autentikasi user (login/logout) dan otorisasi berbasis role untuk mengontrol akses ke fitur-fitur sistem.

**Module Code:** `AUTH`  
**Priority:** P0 (Critical)  
**Dependencies:** None (foundational module)

---

## 🎯 Tujuan

1. Memastikan hanya user terotorisasi yang dapat mengakses sistem
2. Implementasi role-based access control (RBAC)
3. Keamanan data dengan session management
4. User experience yang sederhana untuk login

---

## 👥 User Roles & Permissions

| Role | Code | Access Level | Description |
|------|------|--------------|-------------|
| **Super Admin** | `SUPERADMIN` | Full access | Developer/IT support |
| **Kepala Sekolah** | `PRINCIPAL` | Read all + Approve | Monitoring & approval |
| **Staf TU** | `ADMIN` | Full CRUD (most modules) | Operasional administrasi |
| **Guru** | `TEACHER` | Limited CRUD | Absensi & nilai untuk kelas sendiri |
| **Orang Tua** | `PARENT` | Read-only + Submit | Lihat info anak & submit izin |
| **Siswa** | `STUDENT` | Read-only (limited) | Fase 2 - lihat pengumuman/tugas |

---

## 📖 User Stories

### US-AUTH-001: Login sebagai Staf TU
```
Sebagai Staf TU,
Saya ingin login dengan username dan password,
Sehingga saya dapat mengakses sistem administrasi sekolah
```

**Acceptance Criteria:**
- ✅ Form login dengan field username/email dan password
- ✅ Button "Masuk" untuk submit
- ✅ Checkbox "Ingat Saya" untuk remember login
- ✅ Link "Lupa Password?"
- ✅ Validasi input tidak boleh kosong
- ✅ Error message jelas jika login gagal
- ✅ Redirect ke dashboard sesuai role setelah login sukses
- ✅ Session tersimpan selama 24 jam (atau logout manual)

---

### US-AUTH-002: Logout dari Sistem
```
Sebagai User yang sudah login,
Saya ingin logout dari sistem,
Sehingga session saya berakhir dan data aman
```

**Acceptance Criteria:**
- ✅ Button/menu "Keluar" tersedia di semua halaman
- ✅ Konfirmasi logout (optional: skip jika user prefer langsung)
- ✅ Session dihapus dari server dan browser
- ✅ Redirect ke halaman login
- ✅ Token/session tidak bisa dipakai lagi

---

### US-AUTH-003: Reset Password
```
Sebagai User yang lupa password,
Saya ingin reset password saya,
Sehingga saya bisa login kembali ke sistem
```

**Acceptance Criteria:**
- ✅ Form "Lupa Password" dengan input email/no HP
- ✅ Sistem kirim link/kode reset via email atau WhatsApp
- ✅ Link reset valid selama 1 jam
- ✅ Form input password baru dengan konfirmasi
- ✅ Password harus memenuhi kriteria keamanan
- ✅ Notifikasi sukses setelah password berhasil diubah
- ✅ User bisa langsung login dengan password baru

---

### US-AUTH-004: Ganti Password (Saat Login)
```
Sebagai User yang sudah login,
Saya ingin mengganti password saya,
Sehingga keamanan akun saya terjaga
```

**Acceptance Criteria:**
- ✅ Menu "Ganti Password" di profil user
- ✅ Form dengan field: password lama, password baru, konfirmasi password baru
- ✅ Validasi password lama harus benar
- ✅ Password baru harus berbeda dengan password lama
- ✅ Notifikasi sukses setelah password berhasil diubah

---

### US-AUTH-005: First Time Login - Force Change Password
```
Sebagai User baru yang dibuat oleh Admin,
Saya ingin diwajibkan mengganti password default saat login pertama kali,
Sehingga keamanan akun saya terjaga
```

**Acceptance Criteria:**
- ✅ Setelah login dengan password default, user dipaksa ganti password
- ✅ Tidak bisa skip atau bypass
- ✅ Form ganti password dengan validasi
- ✅ Setelah berhasil, redirect ke dashboard

---

## ⚙️ Functional Requirements

### FR-AUTH-001: Login Flow
**Priority:** Must Have  
**Description:** User dapat login ke sistem menggunakan username/email dan password.

**Details:**
- Input: username atau email, password
- Validasi kredensial terhadap database
- Generate JWT token atau session ID
- Set session duration: 24 jam (configurable)
- Log aktivitas login (timestamp, IP address, device)
- Rate limiting: max 5 percobaan login gagal dalam 15 menit → lock account sementara (15 menit)

---

### FR-AUTH-002: Logout Flow
**Priority:** Must Have  
**Description:** User dapat logout dan mengakhiri session.

**Details:**
- Button logout accessible dari semua halaman (navbar/menu)
- Hapus session/token dari server
- Clear local storage/cookies di browser
- Redirect ke login page
- Log aktivitas logout

---

### FR-AUTH-003: Role-Based Access Control (RBAC)
**Priority:** Must Have  
**Description:** Sistem mengontrol akses fitur berdasarkan role user.

**Details:**
- Setiap user memiliki 1 role
- Role menentukan permission ke modul/fitur
- Middleware check permission sebelum akses endpoint
- UI hide/show fitur berdasarkan role
- Unauthorized access return error 403 (Forbidden)

**Permission Matrix:**

| Module/Feature | Super Admin | Principal | Admin (TU) | Teacher | Parent |
|----------------|-------------|-----------|------------|---------|--------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |
| Student Management (CRUD) | ✅ | ❌ | ✅ | ❌ | ❌ |
| Attendance Input | ✅ | ❌ | ✅ | ✅ (kelas sendiri) | ❌ |
| Attendance View | ✅ | ✅ (all) | ✅ (all) | ✅ (kelas sendiri) | ✅ (anak sendiri) |
| Payment CRUD | ✅ | ❌ | ✅ | ❌ | ❌ |
| Payment View | ✅ | ✅ (all) | ✅ (all) | ❌ | ✅ (anak sendiri) |
| Grades Input | ✅ | ❌ | ❌ | ✅ (kelas sendiri) | ❌ |
| Grades View | ✅ | ✅ (all) | ✅ (all) | ✅ (kelas sendiri) | ✅ (anak sendiri) |
| Report Cards Generate | ✅ | ✅ | ✅ | ✅ (kelas sendiri) | ❌ |
| Report Cards View | ✅ | ✅ (all) | ✅ (all) | ✅ (kelas sendiri) | ✅ (anak sendiri) |
| PSB Verification | ✅ | ✅ | ✅ | ❌ | ❌ |
| Teacher Management | ✅ | ✅ | ✅ | ❌ | ❌ |
| Financial Reports | ✅ | ✅ | ✅ | ❌ | ❌ |
| System Settings | ✅ | ❌ | ❌ | ❌ | ❌ |

---

### FR-AUTH-004: Password Reset via Email/WhatsApp
**Priority:** Should Have (MVP)  
**Description:** User dapat reset password jika lupa.

**Details:**
- Form input email atau nomor HP
- Generate unique reset token (UUID)
- Token valid selama 1 jam
- Kirim link reset via email atau WhatsApp (pilih salah satu saat request)
- Link format: `https://sekolah.app/reset-password?token=xxx`
- Setelah reset sukses, token expired
- Max 3 request reset dalam 24 jam per user

---

### FR-AUTH-005: Change Password (Authenticated User)
**Priority:** Must Have  
**Description:** User yang sudah login dapat mengganti password.

**Details:**
- Requires current password verification
- Password baru harus memenuhi kriteria:
  - Minimal 8 karakter
  - Mengandung huruf dan angka
  - (Optional) 1 karakter spesial
- Password baru tidak boleh sama dengan 3 password terakhir
- Setelah ganti password, logout dari semua device lain (optional)

---

### FR-AUTH-006: First Login - Force Password Change
**Priority:** Should Have  
**Description:** User baru harus ganti password default saat login pertama kali.

**Details:**
- Flag `is_first_login` atau `password_changed_at` di database
- Setelah login sukses, cek flag
- Jika first login, redirect ke halaman ganti password
- User tidak bisa akses fitur lain sebelum ganti password
- Setelah berhasil, update flag dan redirect ke dashboard

---

### FR-AUTH-007: Session Management
**Priority:** Must Have  
**Description:** Sistem mengelola session user untuk keamanan.

**Details:**
- Session duration: 24 jam (default)
- "Ingat Saya" option: extend session to 30 hari
- Auto logout jika session expired
- Token refresh mechanism untuk UX yang smooth
- Concurrent session: max 3 device per user (configurable)
- Admin dapat force logout user tertentu

---

### FR-AUTH-008: Account Lockout (Brute Force Protection)
**Priority:** Must Have  
**Description:** Proteksi dari brute force attack dengan lock account sementara.

**Details:**
- Track failed login attempts per username/email
- Max 5 percobaan gagal dalam 15 menit
- Setelah 5x gagal, lock account selama 15 menit
- Tampilkan countdown timer untuk user
- Admin dapat unlock account secara manual
- Log semua failed attempts untuk security audit

---

### FR-AUTH-009: Activity Log
**Priority:** Should Have  
**Description:** Sistem mencatat semua aktivitas autentikasi untuk audit trail.

**Details:**
- Log setiap login (success/failed)
- Data yang dicatat:
  - User ID & username
  - Timestamp
  - IP address
  - Device/browser info
  - Action (login, logout, password change, etc)
- Log retention: 6 bulan
- Super Admin dapat view activity log

---

## 📏 Business Rules

### BR-AUTH-001: Username Unik
Setiap username harus unik dalam sistem. Tidak boleh ada duplikasi.

### BR-AUTH-002: Role Assignment
- Setiap user harus memiliki minimal 1 role
- User hanya boleh memiliki 1 primary role (tidak support multi-role untuk simplicity)
- Role hanya dapat diubah oleh Admin atau Super Admin

### BR-AUTH-003: Password Policy
- Minimal 8 karakter
- Harus mengandung huruf dan angka
- Password tidak boleh sama dengan username
- Password default untuk user baru: `Sekolah123` (harus diganti saat first login)

### BR-AUTH-004: Session Expiry
- Session normal: 24 jam
- Session "Ingat Saya": 30 hari
- Session expired → auto logout → redirect ke login

### BR-AUTH-005: Account Deactivation
- Admin dapat deactivate account (tidak delete)
- User yang deactivated tidak dapat login
- Data user tetap tersimpan
- Account dapat diaktifkan kembali

---

## ✅ Validation Rules

### VR-AUTH-001: Login Form
- **Username/Email:**
  - Required
  - Min 3 karakter
  - Valid email format jika menggunakan email
- **Password:**
  - Required
  - Min 8 karakter

**Error Messages:**
- "Username atau email wajib diisi"
- "Password wajib diisi"
- "Username atau password salah" (generic message untuk security)
- "Akun Anda terkunci. Silakan coba lagi dalam {X} menit"
- "Akun Anda telah dinonaktifkan. Hubungi administrator"

---

### VR-AUTH-002: Reset Password Form
- **Email/No HP:**
  - Required
  - Valid format
- **Password Baru:**
  - Required
  - Min 8 karakter
  - Mengandung huruf dan angka
- **Konfirmasi Password:**
  - Required
  - Harus sama dengan password baru

**Error Messages:**
- "Email atau nomor HP wajib diisi"
- "Format email tidak valid"
- "Password minimal 8 karakter"
- "Password harus mengandung huruf dan angka"
- "Konfirmasi password tidak cocok"
- "Link reset password sudah kadaluarsa"

---

### VR-AUTH-003: Change Password Form
- **Password Lama:**
  - Required
  - Harus match dengan password saat ini di database
- **Password Baru:**
  - Required
  - Min 8 karakter
  - Mengandung huruf dan angka
  - Tidak boleh sama dengan password lama
- **Konfirmasi Password Baru:**
  - Required
  - Harus sama dengan password baru

**Error Messages:**
- "Password lama salah"
- "Password baru tidak boleh sama dengan password lama"
- "Password minimal 8 karakter dan mengandung huruf dan angka"
- "Konfirmasi password tidak cocok"

---

## 🎨 UI/UX Requirements

### Login Page
**Layout:**
- Center aligned form
- Logo sekolah di atas form
- Nama sekolah/sistem
- Card/container dengan shadow untuk form
- Responsive: mobile & desktop

**Components:**
- Input field username/email (dengan icon user)
- Input field password (dengan icon lock dan toggle show/hide password)
- Checkbox "Ingat Saya"
- Button "Masuk" (full width, primary color)
- Link "Lupa Password?" (di bawah button)
- Footer dengan copyright/kontak

**Mobile-Specific:**
- Touch-friendly input (min height 48px)
- Auto-focus pada field username saat page load
- Keyboard type sesuai input (email keyboard untuk email)

---

### Reset Password Flow
**Step 1: Request Reset**
- Form input email/no HP
- Button "Kirim Link Reset"
- Success message: "Link reset password telah dikirim ke email/WhatsApp Anda"

**Step 2: Reset Form (dari link)**
- Form input password baru & konfirmasi
- Password strength indicator (weak/medium/strong)
- Button "Ubah Password"
- Success message: "Password berhasil diubah. Silakan login"

---

### Change Password (In-App)
**Layout:**
- Modal atau dedicated page
- Form dengan 3 fields (old, new, confirm)
- Password strength indicator untuk password baru
- Button "Simpan" dan "Batal"
- Success notification setelah berhasil

---

## 🔗 Integration Points

### INT-AUTH-001: Email Service
- Untuk kirim reset password link
- Service: SMTP atau SendGrid/MailGun
- Template email dengan branding sekolah

### INT-AUTH-002: WhatsApp API
- Untuk kirim reset password link via WhatsApp
- WhatsApp Business API atau third-party (Fonnte, Wablas, dll)
- Message template approved

### INT-AUTH-003: Logging Service
- Activity log disimpan ke database atau log file
- Optional: integrate dengan Sentry untuk error tracking

---

## 🧪 Test Scenarios

### TS-AUTH-001: Successful Login
1. Buka halaman login
2. Input username valid dan password valid
3. Click "Masuk"
4. **Expected:** Redirect ke dashboard sesuai role

### TS-AUTH-002: Failed Login - Wrong Password
1. Buka halaman login
2. Input username valid dan password salah
3. Click "Masuk"
4. **Expected:** Error message "Username atau password salah"
5. User tetap di halaman login

### TS-AUTH-003: Account Lockout
1. Login dengan password salah 5 kali berturut-turut
2. **Expected:** 
   - Attempt ke-6, muncul message "Akun terkunci selama 15 menit"
   - User tidak bisa login meskipun password benar
   - Setelah 15 menit, bisa login lagi

### TS-AUTH-004: Reset Password Flow
1. Click "Lupa Password"
2. Input email terdaftar
3. Submit
4. **Expected:** Success message
5. Cek email, ada link reset
6. Click link
7. Input password baru
8. Submit
9. **Expected:** Success message, redirect ke login
10. Login dengan password baru
11. **Expected:** Berhasil login

### TS-AUTH-005: First Login Force Change Password
1. Login dengan akun baru (password default)
2. **Expected:** Redirect ke halaman ganti password
3. Coba akses URL dashboard langsung
4. **Expected:** Redirect kembali ke halaman ganti password
5. Ganti password
6. **Expected:** Redirect ke dashboard

### TS-AUTH-006: Role-Based Access
1. Login sebagai Guru
2. Coba akses halaman Student Management (CRUD)
3. **Expected:** 
   - UI tidak menampilkan menu tersebut
   - Jika akses langsung via URL, error 403 Forbidden

### TS-AUTH-007: Session Expiry
1. Login tanpa "Ingat Saya"
2. Tunggu 24 jam (atau ubah session duration untuk testing)
3. Coba akses halaman dalam sistem
4. **Expected:** Auto logout, redirect ke login

### TS-AUTH-008: Logout
1. Login sebagai user
2. Click menu "Keluar"
3. **Expected:** Redirect ke login page
4. Browser back button
5. **Expected:** Tetap di login page, tidak bisa kembali ke dashboard

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Login dengan username/password
- ✅ Logout functionality
- ✅ Role-based access control (5 roles: Super Admin, Principal, Admin, Teacher, Parent)
- ✅ Change password untuk user yang sudah login
- ✅ Session management (24 jam)
- ✅ Account lockout protection
- ✅ Password validation (min 8 char, huruf & angka)
- ✅ Activity logging (basic)

### Should Have (MVP):
- ✅ Reset password via email atau WhatsApp
- ✅ First login force change password
- ✅ "Ingat Saya" functionality
- ✅ Detailed activity log untuk Admin

### Could Have (Nice to Have):
- ⬜ Multi-factor authentication (2FA)
- ⬜ Social login (Google)
- ⬜ Biometric login (fingerprint/face) untuk mobile
- ⬜ IP whitelist untuk Admin

### Won't Have (Phase 2):
- ⬜ SSO (Single Sign-On)
- ⬜ OAuth provider untuk third-party apps
- ⬜ Advanced security features (CAPTCHA, anomaly detection)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft - Ready for Review

