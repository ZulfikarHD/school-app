# EPIC 1: Foundation & Access Control

**Project:** SD Management System  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Epic Owner:** Development Team  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Epic Overview

### Goal
User dapat login, logout, dan akses sistem sesuai role mereka dengan aman, serta sistem memiliki master data foundational yang diperlukan untuk modul lainnya.

### Business Value
- **Keamanan Sistem:** Proteksi data sensitif dengan authentication & authorization yang robust
- **User Experience:** Login/logout yang cepat dan mudah untuk semua user
- **Foundation:** Master data yang diperlukan untuk semua modul lainnya (kelas, mapel, tahun ajaran, dll)
- **Compliance:** Audit trail untuk monitoring aktivitas user

### Success Metrics
- Login time < 2 detik untuk 95% kasus
- Zero unauthorized access incidents
- 100% user harus ganti password default saat first login
- Semua master data tersedia sebelum Sprint 2

---

## 📊 Epic Summary

| **Attribute** | **Value** |
|---------------|-----------|
| **Total Points** | 40 points |
| **Target Sprint** | Sprint 1 & 2 |
| **Priority** | Critical - Must complete first |
| **Dependencies** | None (foundational) |
| **Target Release** | MVP (Phase 1) |
| **Estimated Duration** | 3-4 minggu (1 developer) |

---

## 🎯 User Stories Included

### Authentication Module (23 points)

#### US-AUTH-001: Login ke Sistem
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** User (semua role)  
**I want** login dengan username dan password  
**So that** saya dapat mengakses sistem sesuai dengan hak akses saya

**Acceptance Criteria:**
- ✅ User membuka halaman login dan memasukkan username & password valid → redirect ke dashboard dalam < 2 detik
- ✅ Username atau password salah → tampilkan error "Username atau password salah"
- ✅ User sudah login dan coba akses halaman login → redirect ke dashboard
- ✅ User idle > 30 menit (15 menit untuk admin) → auto-logout dengan pesan session expired

**Technical Notes:**
- Password hashing: bcrypt/Argon2
- Rate limiting: max 5 failed attempts dalam 15 menit
- JWT token dengan expiry 24 jam (configurable)

---

#### US-AUTH-002: Logout dari Sistem
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** User (semua role)  
**I want** logout dari sistem dengan aman  
**So that** orang lain tidak dapat mengakses akun saya

**Acceptance Criteria:**
- ✅ User klik tombol "Keluar" → logout dan redirect ke halaman login dalam < 1 detik
- ✅ Setelah logout, klik browser back button → tidak menampilkan data, redirect ke login
- ✅ Session data dan token dihapus dari browser & server

**Technical Notes:**
- Clear cookies dan local storage
- Invalidate JWT token di server
- Log logout activity

---

#### US-AUTH-003: Lupa Password
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** User yang lupa password  
**I want** reset password saya  
**So that** saya dapat login kembali ke sistem

**Acceptance Criteria:**
- ✅ User klik "Lupa Password?" → tampil form input email/nomor HP
- ✅ Email terdaftar → sistem kirim email dengan link reset password valid 1 jam
- ✅ Klik link reset (masih valid) → tampil form input password baru
- ✅ Input password baru (min 8 karakter) → update password, redirect ke login dengan notifikasi sukses
- ✅ Link sudah expired → tampil "Link sudah kadaluarsa, silakan request ulang"

**Technical Notes:**
- Unique reset token (UUID)
- Token expiry: 1 jam
- Max 3 reset request dalam 24 jam per user
- Support email & WhatsApp (pilih satu saat request)

---

#### US-AUTH-004: Ganti Password (User Sendiri)
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** User yang sudah login  
**I want** mengganti password saya sendiri  
**So that** saya dapat menjaga keamanan akun saya

**Acceptance Criteria:**
- ✅ Di halaman "Profil Saya", klik "Ganti Password" → tampil form dengan 3 field (password lama, baru, konfirmasi)
- ✅ Semua field benar → update password, tampil notifikasi "Password berhasil diubah"
- ✅ Password lama salah → error "Password lama tidak sesuai"
- ✅ Password baru < 8 karakter → error "Password minimal 8 karakter dengan kombinasi huruf, angka, dan simbol"

**Technical Notes:**
- Validasi password strength (frontend & backend)
- Optional: logout user setelah ganti password

---

#### US-AUTH-005: Role-Based Access Control (RBAC)
**Priority:** Must Have | **Estimation:** L (5 points)

**As a** System Admin  
**I want** setiap user hanya dapat akses fitur sesuai role mereka  
**So that** data sensitif terlindungi dan tidak ada unauthorized access

**Acceptance Criteria:**
- ✅ Guru coba akses "Laporan Keuangan" (hak akses Kepala Sekolah/TU) → error 403 "Anda tidak memiliki akses"
- ✅ Orang Tua akses URL admin langsung (misal: /admin/students/edit/123) → redirect ke "Akses Ditolak"
- ✅ TU login → menu sesuai hak akses TU (Student Management, Payment, tidak ada Teacher Evaluation)
- ✅ Kepala Sekolah login → akses semua modul (read-only untuk beberapa, full access untuk dashboard & reports)

**Permission Matrix:**
- **Kepala Sekolah:** Full read access + approval
- **TU:** CRUD students, payments, PSB
- **Guru:** Input absensi, nilai, read student info (kelas sendiri)
- **Orang Tua:** Read-only (info anak sendiri)
- **Siswa:** Read-only (opsional, fase 2)

**Technical Notes:**
- Middleware RBAC di backend
- UI hide/show menu berdasarkan role
- Permission check pada setiap endpoint

---

#### US-AUTH-006: Manajemen User Account (Admin)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** System Admin/TU  
**I want** membuat, mengedit, dan menonaktifkan akun user  
**So that** setiap user memiliki akses yang sesuai

**Acceptance Criteria:**
- ✅ Di halaman "Manajemen User", klik "Tambah User Baru" → form: Nama, Email, Username, Role, Status
- ✅ Data lengkap → sistem generate password default dan kirim ke email/WhatsApp user
- ✅ Admin nonaktifkan akun guru resign → user tidak dapat login, session aktif terminate
- ✅ Admin klik "Reset Password" user tertentu → generate password baru, kirim ke email/WhatsApp

**Technical Notes:**
- Password default: kombinasi nama + 4 digit random (contoh: Budi1234)
- User wajib ganti password saat first login
- Log semua aktivitas admin (audit trail)

---

#### US-AUTH-007: First Time Login - Force Change Password
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** User baru  
**I want** diminta mengganti password default saat pertama kali login  
**So that** akun saya lebih aman

**Acceptance Criteria:**
- ✅ User baru login dengan password default → redirect ke halaman "Ganti Password Wajib"
- ✅ User coba skip/back → sistem tidak allow, tetap di halaman ganti password
- ✅ Password baru memenuhi requirement → update password, tandai akun "activated", redirect ke dashboard

**Technical Notes:**
- Flag `is_first_login` di database user
- Password baru harus berbeda dari password default

---

#### US-AUTH-009: Audit Log - User Activity
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** System Admin/Kepala Sekolah  
**I want** melihat log aktivitas user (login, logout, akses halaman sensitif)  
**So that** saya dapat monitor keamanan sistem

**Acceptance Criteria:**
- ✅ Di halaman "Audit Log", filter by user/tanggal → tampil list: timestamp, user, IP, action, status
- ✅ Failed login > 5x dari satu IP → sistem auto-block IP 15 menit dan log incident
- ✅ User ubah data critical (pembayaran, nilai) → log: who, what, when, old value, new value

**Technical Notes:**
- Log minimal: login/logout, failed login, data critical (payment, grades)
- Retention: 6 bulan
- Optional: integrate dengan Sentry/LogRocket

---

### Settings & Configuration Module (17 points)

#### US-SET-001: Master Data Kelas
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Admin/TU  
**I want** mengelola data kelas (tambah, edit, nonaktifkan)  
**So that** sistem punya master data kelas yang akurat

**Acceptance Criteria:**
- ✅ Di halaman "Master Data - Kelas", klik "Tambah Kelas" → form: Nama Kelas, Tingkat (1-6), Wali Kelas, Kapasitas
- ✅ Input kelas baru "3C" → tersimpan dan muncul di list, dapat assign siswa
- ✅ Nonaktifkan kelas lama (6A setelah lulus) → tidak muncul di dropdown tapi data historis tetap ada

**Technical Notes:**
- Field: Nama Kelas, Tingkat, Wali Kelas (dropdown guru), Kapasitas, Ruang Kelas, Status
- Soft delete (nonaktif, tidak permanent delete)
- Validasi: nama kelas unique per tahun ajaran

---

#### US-SET-002: Master Data Mata Pelajaran
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Admin/TU  
**I want** mengelola data mata pelajaran  
**So that** guru dapat input nilai dan jadwal sesuai mata pelajaran yang diajar

**Acceptance Criteria:**
- ✅ Di halaman "Master Data - Mata Pelajaran", klik "Tambah Mata Pelajaran" → form: Nama, Kode, KKM, Kelompok
- ✅ Input "Bahasa Jawa" dengan KKM 65 → tersimpan, dapat digunakan untuk input nilai & jadwal
- ✅ Edit KKM Matematika 60 → 65 → update dan apply untuk input nilai berikutnya

**Technical Notes:**
- Field: Nama, Kode, KKM (Kriteria Ketuntasan Minimal), Kelompok, Status
- Default mata pelajaran sesuai kurikulum K13
- KKM per mata pelajaran (bisa berbeda)

---

#### US-SET-003: Master Data Tahun Ajaran & Semester
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Admin/TU  
**I want** mengelola data tahun ajaran dan semester  
**So that** sistem tahu periode aktif dan dapat filter data per periode

**Acceptance Criteria:**
- ✅ Di halaman "Master Data - Tahun Ajaran", klik "Tambah Tahun Ajaran" → form: Nama ("2024/2025"), Tanggal Mulai/Selesai, Status
- ✅ Set tahun ajaran "2024/2025" aktif → sistem auto-nonaktifkan tahun ajaran lainnya (hanya 1 aktif)
- ✅ Tambah semester baru (Semester 1 & 2) → dapat digunakan untuk filter laporan/nilai

**Technical Notes:**
- Field: Nama Tahun Ajaran, Start Date, End Date, Status, Semester (1 & 2)
- Hanya 1 tahun ajaran aktif dalam satu waktu
- Auto-switch tahun ajaran (opsional: cron job)

---

#### US-SET-004: Pengaturan Umum Sekolah
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Admin  
**I want** mengatur data umum sekolah (nama, alamat, logo, dll)  
**So that** informasi sekolah tampil di seluruh sistem

**Acceptance Criteria:**
- ✅ Di halaman "Pengaturan - Umum", update data: Nama Sekolah, Alamat, Telepon, Email, Website, Logo → tampil di header, footer, kwitansi, rapor
- ✅ Upload logo baru → otomatis ganti di seluruh sistem (header, kwitansi, rapor, website)
- ✅ Update nomor telepon → update di halaman kontak website dan footer

**Technical Notes:**
- Field: Nama Sekolah, NPSN, Alamat, Kota, Provinsi, Kode Pos, Telepon, Email, Website, Logo
- Logo: auto-resize dan compress
- Data digunakan di: kwitansi, rapor, website, email template

---

#### US-SET-005: Pengaturan Jenis Pembayaran
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Admin/TU  
**I want** mengatur jenis-jenis pembayaran  
**So that** sistem support berbagai jenis pembayaran sesuai kebutuhan

**Acceptance Criteria:**
- ✅ Di halaman "Pengaturan - Jenis Pembayaran", klik "Tambah Jenis Pembayaran" → form: Nama, Kode, Deskripsi, Tipe
- ✅ Input jenis baru "Uang Study Tour" → tersimpan, muncul di dropdown saat TU catat pembayaran
- ✅ Nonaktifkan jenis lama → tidak muncul di dropdown tapi data historis tetap ada

**Technical Notes:**
- Field: Nama, Kode, Deskripsi, Tipe (One-Time/Recurring), Nominal Default, Status
- Default: SPP, Uang Gedung, Uang Buku, Uang Seragam
- Allow admin tambah custom jenis

---

#### US-SET-008: Pengaturan Role & Permission (RBAC)
**Priority:** Should Have | **Estimation:** L (5 points)

**As a** Admin  
**I want** mengatur hak akses setiap role  
**So that** user hanya dapat akses fitur sesuai role mereka

**Acceptance Criteria:**
- ✅ Di halaman "Pengaturan - Role & Permission", pilih role "Guru" → tampil checklist permission: Lihat Data Siswa, Input Absensi, Input Nilai, dll
- ✅ Uncheck "Akses Modul Pembayaran" untuk role Guru → guru tidak dapat melihat menu pembayaran
- ✅ Buat role custom "Wakil Kepala Sekolah" → set permission: akses dashboard, lihat laporan, tidak bisa edit data → user dengan role tersebut hanya read-only

**Technical Notes:**
- Default role: Kepala Sekolah, TU, Guru, Wali Kelas, Orang Tua
- Permission granular: per module, per action (create/read/update/delete)
- Allow custom role (opsional, fase 2)

---

## 🏗️ Technical Architecture

### Database Schema Requirements

#### Users Table
```
- id (PK)
- username (unique)
- email (unique)
- password_hash
- role_id (FK)
- status (active/inactive)
- is_first_login (boolean)
- last_login_at
- created_at
- updated_at
```

#### Roles Table
```
- id (PK)
- name (Kepala Sekolah, TU, Guru, Orang Tua)
- code (PRINCIPAL, ADMIN, TEACHER, PARENT)
- permissions (JSON)
- created_at
- updated_at
```

#### Password Reset Tokens Table
```
- id (PK)
- user_id (FK)
- token (unique)
- expires_at
- used_at
- created_at
```

#### Activity Logs Table
```
- id (PK)
- user_id (FK)
- action (login, logout, create, update, delete)
- module (auth, students, payments, etc)
- ip_address
- user_agent
- old_value (JSON)
- new_value (JSON)
- created_at
```

#### Classes Table (Master Data)
```
- id (PK)
- name (1A, 2B, etc)
- level (1-6)
- homeroom_teacher_id (FK to users)
- capacity
- room
- academic_year_id (FK)
- status (active/inactive)
- created_at
- updated_at
```

#### Subjects Table (Master Data)
```
- id (PK)
- name (Matematika, IPA, etc)
- code (MAT, IPA, etc)
- kkm (Kriteria Ketuntasan Minimal)
- group (Wajib/Muatan Lokal)
- status (active/inactive)
- created_at
- updated_at
```

#### Academic Years Table
```
- id (PK)
- name (2024/2025)
- start_date
- end_date
- is_active (boolean, only one can be true)
- created_at
- updated_at
```

#### Semesters Table
```
- id (PK)
- academic_year_id (FK)
- semester (1 or 2)
- start_date
- end_date
- is_active (boolean)
- created_at
- updated_at
```

#### School Settings Table
```
- id (PK)
- key (school_name, address, phone, etc)
- value (text)
- type (string, number, file, json)
- created_at
- updated_at
```

#### Payment Types Table
```
- id (PK)
- name (SPP, Uang Gedung, etc)
- code (SPP, UANG_GEDUNG)
- description
- type (one_time/recurring)
- default_amount
- status (active/inactive)
- created_at
- updated_at
```

---

### API Endpoints

#### Authentication
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/reset-password` - Reset password with token
- `POST /api/auth/change-password` - Change password (authenticated)
- `GET /api/auth/me` - Get current user info
- `POST /api/auth/refresh-token` - Refresh JWT token

#### User Management
- `GET /api/users` - List all users (paginated, with filters)
- `POST /api/users` - Create new user
- `GET /api/users/:id` - Get user detail
- `PUT /api/users/:id` - Update user
- `DELETE /api/users/:id` - Soft delete/deactivate user
- `POST /api/users/:id/reset-password` - Admin reset user password
- `GET /api/activity-logs` - Get activity logs (with filters)

#### Master Data - Classes
- `GET /api/master/classes` - List all classes
- `POST /api/master/classes` - Create class
- `GET /api/master/classes/:id` - Get class detail
- `PUT /api/master/classes/:id` - Update class
- `DELETE /api/master/classes/:id` - Deactivate class

#### Master Data - Subjects
- `GET /api/master/subjects` - List all subjects
- `POST /api/master/subjects` - Create subject
- `GET /api/master/subjects/:id` - Get subject detail
- `PUT /api/master/subjects/:id` - Update subject
- `DELETE /api/master/subjects/:id` - Deactivate subject

#### Master Data - Academic Years
- `GET /api/master/academic-years` - List all academic years
- `POST /api/master/academic-years` - Create academic year
- `GET /api/master/academic-years/:id` - Get academic year detail
- `PUT /api/master/academic-years/:id` - Update academic year
- `POST /api/master/academic-years/:id/activate` - Set as active year
- `GET /api/master/academic-years/active` - Get current active year

#### Master Data - Payment Types
- `GET /api/master/payment-types` - List all payment types
- `POST /api/master/payment-types` - Create payment type
- `GET /api/master/payment-types/:id` - Get payment type detail
- `PUT /api/master/payment-types/:id` - Update payment type
- `DELETE /api/master/payment-types/:id` - Deactivate payment type

#### Settings
- `GET /api/settings` - Get all settings
- `PUT /api/settings` - Update settings (bulk)
- `GET /api/settings/:key` - Get specific setting
- `PUT /api/settings/:key` - Update specific setting

#### Roles & Permissions
- `GET /api/roles` - List all roles
- `POST /api/roles` - Create custom role
- `GET /api/roles/:id` - Get role detail with permissions
- `PUT /api/roles/:id` - Update role permissions
- `GET /api/permissions` - List all available permissions

---

### Security Implementation

#### Password Policy
- Minimal 8 karakter
- Harus mengandung huruf dan angka
- Tidak boleh sama dengan username
- Hash menggunakan bcrypt (cost factor: 12)

#### Rate Limiting
- Login endpoint: max 5 requests per 15 menit per IP
- Password reset: max 3 requests per 24 jam per user
- API endpoints: max 100 requests per menit per user

#### Session Management
- JWT token dengan expiry 24 jam
- Refresh token dengan expiry 30 hari
- Session timeout: 30 menit idle untuk user biasa, 15 menit untuk admin
- Token blacklist untuk logout

#### RBAC Implementation
- Middleware untuk check permission setiap request
- Permission format: `module.action` (e.g., `students.create`, `payments.read`)
- Role hierarchy: Super Admin > Principal > Admin (TU) > Teacher > Parent

---

## 🎨 UI/UX Design Requirements

### Login Page
**Layout:**
- Center-aligned form card dengan shadow
- Logo sekolah di atas form
- Responsive: mobile & desktop optimized

**Components:**
- Input username/email (icon: user)
- Input password (icon: lock, toggle show/hide)
- Checkbox "Ingat Saya" (extend session 30 hari)
- Button "Masuk" (full width, primary color)
- Link "Lupa Password?"
- Footer dengan copyright

**Mobile-Specific:**
- Touch-friendly input (min height 48px)
- Auto-focus username field
- Keyboard type sesuai input (email keyboard untuk email)

---

### Dashboard (Role-Based)
**Kepala Sekolah:**
- Overview statistics (total siswa, guru, pendapatan bulan ini)
- Pending approvals (izin guru, rapor)
- Grafik kehadiran & performa akademik

**TU/Admin:**
- Quick actions (Tambah Siswa, Catat Pembayaran)
- Reminder pembayaran tertunggak
- Pending verifikasi (PSB, pembayaran transfer)

**Guru:**
- Jadwal mengajar hari ini
- Quick access: Input Absensi, Input Nilai
- Reminder: nilai yang belum diinput

**Orang Tua:**
- Info anak (foto, kelas, wali kelas)
- Status pembayaran bulan ini
- Kehadiran minggu ini
- Nilai terakhir

---

### Master Data Pages
**Design Pattern:**
- Table dengan search, filter, dan pagination
- Button "Tambah" di kanan atas
- Action column dengan icon: Edit (pencil), Delete/Deactivate (trash)
- Modal/slide-over untuk form (tidak full page)
- Success/error toast notification

**Validation:**
- Real-time validation (frontend)
- Error message dalam Bahasa Indonesia
- Field yang error highlighted dengan warna merah

---

## ✅ Definition of Done

### Code Level
- [ ] Unit test coverage minimal 80%
- [ ] Integration test untuk critical flow (login, RBAC)
- [ ] Code review approved
- [ ] No critical linter errors
- [ ] All API endpoints documented (Swagger/Postman)

### Functionality
- [ ] All acceptance criteria met dan tested
- [ ] RBAC tested untuk semua role
- [ ] Password reset flow tested (email & WhatsApp)
- [ ] Audit log recording semua critical actions
- [ ] Rate limiting implemented dan tested

### UI/UX
- [ ] Responsive di mobile dan desktop
- [ ] Loading state untuk semua async actions
- [ ] Error handling dengan user-friendly message
- [ ] Success feedback (toast/notification)
- [ ] Accessibility: keyboard navigation, screen reader support

### Security
- [ ] Password hashing dengan bcrypt
- [ ] JWT token dengan proper expiry
- [ ] Rate limiting active
- [ ] HTTPS only (production)
- [ ] No sensitive data in logs

### Performance
- [ ] Login time < 2 detik (95th percentile)
- [ ] API response time < 500ms (95th percentile)
- [ ] Database queries optimized (use indexes)
- [ ] No N+1 query problem

### Documentation
- [ ] API documentation complete
- [ ] Database schema documented
- [ ] Deployment guide ready
- [ ] User manual (Bahasa Indonesia)

---

## 🔗 Dependencies

### External Dependencies
- **Email Service:** SMTP (Gmail/SendGrid) untuk password reset
- **WhatsApp API:** Fonnte/Wablas untuk notifikasi (opsional untuk Epic 1)
- **Frontend Framework:** React/Next.js (atau sesuai tech stack)
- **Backend Framework:** Node.js/Express atau Laravel
- **Database:** PostgreSQL atau MySQL

### Internal Dependencies
- **None** - Epic ini adalah foundational, tidak ada dependency ke epic lain

### Blocking For
Epic 1 harus selesai 100% sebelum epic lain dapat dimulai, karena:
- Epic 2 (Student Management) butuh authentication & master data kelas
- Epic 3 (Attendance) butuh user roles & master data
- Epic 4 (Payment) butuh master data jenis pembayaran
- Epic 5 (Grades) butuh master data mata pelajaran

---

## 🧪 Testing Strategy

### Unit Testing
- Service layer: authentication logic, password hashing, token generation
- Utility functions: validation, permission check
- Target coverage: 80%

### Integration Testing
- Login flow: success, failed, account lockout
- Password reset: request, validate token, update password
- RBAC: permission check untuk berbagai role dan endpoint
- Master data CRUD: create, read, update, deactivate

### E2E Testing (Critical Paths)
1. **Happy Path Login:**
   - User buka login → input credential → redirect dashboard sesuai role
2. **First Login Force Change Password:**
   - User baru login → forced ganti password → redirect dashboard
3. **Password Reset:**
   - Request reset → receive email → click link → change password → login success
4. **RBAC:**
   - Login as Guru → try access payment module → blocked (403)
5. **Master Data:**
   - Create kelas baru → assign wali kelas → deactivate kelas lama

### Performance Testing
- Load test: 100 concurrent users login
- Stress test: 1000 requests per second ke API
- Target: 95th percentile response time < 500ms

### Security Testing
- Penetration test: SQL injection, XSS, CSRF
- Brute force test: verify rate limiting works
- Session management: verify token invalidation after logout
- RBAC bypass attempt: verify middleware blocks unauthorized access

---

## 📅 Sprint Planning

### Sprint 1 (2 minggu) - 20 points
**Focus:** Authentication Core & Basic RBAC

**Stories:**
- US-AUTH-001: Login ke Sistem (3 pts) - **Day 1-2**
- US-AUTH-002: Logout (2 pts) - **Day 3**
- US-AUTH-005: RBAC (5 pts) - **Day 4-6**
- US-AUTH-006: Manajemen User Account (3 pts) - **Day 7-8**
- US-AUTH-007: First Login Force Change (2 pts) - **Day 9**
- US-SET-001: Master Data Kelas (2 pts) - **Day 10**
- US-SET-002: Master Data Mapel (2 pts) - **Day 10**

**Deliverables:**
- User dapat login/logout
- RBAC active untuk semua role
- Admin dapat manage user accounts
- Master data kelas & mapel tersedia

**Sprint Goal:** "User dapat login dengan aman dan sistem memiliki RBAC yang berfungsi"

---

### Sprint 2 (2 minggu) - 20 points
**Focus:** Password Management, Master Data, & Configuration

**Stories:**
- US-AUTH-003: Lupa Password (3 pts) - **Day 1-3**
- US-AUTH-004: Ganti Password (2 pts) - **Day 4**
- US-AUTH-009: Audit Log (3 pts) - **Day 5-6**
- US-SET-003: Tahun Ajaran & Semester (3 pts) - **Day 7**
- US-SET-004: Pengaturan Umum Sekolah (3 pts) - **Day 8**
- US-SET-005: Jenis Pembayaran (2 pts) - **Day 9**
- US-SET-008: Role & Permission (5 pts) - **Day 10-12**

**Deliverables:**
- Password reset via email/WhatsApp working
- Activity log recording critical actions
- Semua master data complete
- Role & permission management tersedia

**Sprint Goal:** "Sistem memiliki security lengkap dan semua master data ready untuk modul lain"

---

## 🎯 Acceptance Criteria (Epic Level)

### Functional
- [ ] User dari semua role dapat login/logout tanpa masalah
- [ ] Password reset via email berfungsi dengan baik
- [ ] RBAC implemented: setiap role hanya akses fitur sesuai permission
- [ ] Admin dapat manage user accounts (create, edit, deactivate)
- [ ] First login force change password working
- [ ] Activity log recording login/logout dan perubahan data critical
- [ ] Semua master data tersedia: Kelas, Mapel, Tahun Ajaran, Jenis Pembayaran
- [ ] School settings dapat diatur dan tampil di seluruh sistem

### Non-Functional
- [ ] Login performance < 2 detik
- [ ] Zero security vulnerabilities (OWASP Top 10)
- [ ] Mobile-responsive (tested di iOS & Android)
- [ ] User-friendly error messages dalam Bahasa Indonesia
- [ ] Audit log retention 6 bulan

### Technical
- [ ] API documentation complete (Swagger)
- [ ] Database schema implemented dengan proper indexes
- [ ] Unit test coverage 80%
- [ ] Integration test untuk critical flows
- [ ] Security: bcrypt hashing, JWT token, rate limiting
- [ ] Monitoring: error tracking (Sentry) dan performance monitoring

---

## 🚧 Risks & Mitigation

### Risk 1: Email Delivery Issues
**Impact:** High - User tidak dapat reset password  
**Probability:** Medium  
**Mitigation:**
- Setup SMTP dengan proper DNS (SPF, DKIM, DMARC)
- Gunakan transactional email service (SendGrid/Mailgun)
- Backup: SMS/WhatsApp untuk password reset
- Monitor email delivery rate

### Risk 2: RBAC Complexity
**Impact:** High - Potential security breach jika salah implementasi  
**Probability:** Medium  
**Mitigation:**
- Start dengan simple role-based (tidak attribute-based)
- Comprehensive testing untuk setiap role dan endpoint
- Code review untuk semua RBAC logic
- Use middleware pattern untuk consistency

### Risk 3: Performance Degradation (Activity Log)
**Impact:** Medium - Logging semua activity dapat slow down system  
**Probability:** Low  
**Mitigation:**
- Async logging (queue-based)
- Index database table untuk query performance
- Log retention policy (6 bulan)
- Archive old logs to cold storage

### Risk 4: Forgot Password Abuse
**Impact:** Medium - Spam email/WhatsApp  
**Probability:** Medium  
**Mitigation:**
- Rate limiting: max 3 requests per 24 jam
- CAPTCHA untuk forgot password form
- Monitor untuk unusual activity patterns

---

## 📊 Success Metrics & KPIs

### Sprint 1
- [ ] 100% user stories completed (7/7)
- [ ] Zero critical bugs in production
- [ ] Login success rate > 99%
- [ ] Average login time < 2 detik

### Sprint 2
- [ ] 100% user stories completed (7/7)
- [ ] Password reset success rate > 95%
- [ ] Zero unauthorized access incidents
- [ ] Activity log coverage 100% untuk critical actions

### Epic Level
- [ ] Total 40 points delivered
- [ ] Code coverage 80%
- [ ] Zero security vulnerabilities
- [ ] User satisfaction score > 4/5 (dari testing dengan actual users)

---

## 📝 Notes & Assumptions

### Assumptions
1. Email server tersedia dan properly configured
2. WhatsApp API account sudah disiapkan (untuk fase berikutnya)
3. SSL certificate tersedia untuk HTTPS
4. Database dapat handle 1000+ users concurrent
5. User sudah familiar dengan web application (tidak perlu onboarding ekstensif)

### Out of Scope (Epic 1)
- ❌ Multi-factor Authentication (2FA) - Phase 2
- ❌ Social login (Google, Facebook) - Phase 2
- ❌ Biometric login - Phase 2
- ❌ Single Sign-On (SSO) - Phase 2
- ❌ Advanced activity log analytics - Phase 2
- ❌ IP whitelist - Phase 2
- ❌ Session management multi-device - Phase 2

### Nice to Have (Not Required for MVP)
- Real-time notification untuk activity log
- Password strength meter dengan visual feedback
- Login history untuk user (last 10 logins)
- Device management (user dapat see & revoke devices)

---

## 🔄 Review & Refinement

### Sprint 1 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner

**Review Checklist:**
- [ ] Demo all completed user stories
- [ ] Get feedback dari stakeholder (Kepala Sekolah, TU)
- [ ] Identify improvement areas
- [ ] Adjust Sprint 2 if needed

### Sprint 2 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner, Key Users

**Review Checklist:**
- [ ] Demo complete Epic 1 functionality
- [ ] User acceptance testing (UAT)
- [ ] Security review
- [ ] Performance review
- [ ] Documentation complete check

---

## ✅ Epic Checklist (Before Moving to Epic 2)

### Development
- [ ] All 14 user stories implemented dan tested
- [ ] Code merged to main branch
- [ ] Database migration successful
- [ ] API documentation published

### Testing
- [ ] Unit test pass (coverage 80%)
- [ ] Integration test pass
- [ ] E2E test pass untuk critical paths
- [ ] Security test pass (no critical issues)
- [ ] Performance test pass (login < 2s)

### Deployment
- [ ] Deployed to staging environment
- [ ] UAT approved by stakeholders
- [ ] Deployed to production
- [ ] Monitoring & logging active

### Documentation
- [ ] Technical documentation complete
- [ ] User manual (Bahasa Indonesia) ready
- [ ] API documentation complete
- [ ] Database schema documented

### Handover
- [ ] Demo to all user roles
- [ ] Training session completed
- [ ] Support contact established
- [ ] Feedback channel setup

---

## 📞 Contact & Support

**Epic Owner:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733  
**Email:** [Your Email]

**For Technical Issues:**
- Slack: #dev-sd-management
- Email: dev-support@sekolah.app

**For Product Questions:**
- Contact Product Owner
- Email: product@sekolah.app

---

**Document Status:** ✅ Ready for Sprint Planning  
**Last Review:** 13 Desember 2025  
**Next Review:** After Sprint 1 completion

---

*Catatan: Dokumen ini adalah living document dan akan diupdate seiring progress sprint. Semua perubahan akan dicatat di section "Change Log" di bawah.*

## 📋 Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 13 Des 2025 | 1.0 | Initial creation of EPIC 1 document | Zulfikar Hidayatullah |
