# EPIC 7: Teacher Management (Manajemen Guru)

**Project:** SD Management System  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Epic Owner:** Development Team  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Epic Overview

### Goal
Mengelola data kepegawaian guru secara terpusat, mengatur jadwal mengajar, menghitung honor berdasarkan jam mengajar dan kehadiran, serta melakukan evaluasi kinerja guru untuk meningkatkan kualitas pengajaran.

### Business Value
- **Efisiensi HR:** Centralisasi data kepegawaian guru dalam satu sistem
- **Akurasi Payroll:** Perhitungan honor otomatis berdasarkan jam mengajar dan kehadiran
- **Transparansi:** Guru dapat melihat jadwal, honor, dan evaluasi mereka sendiri
- **Quality Assurance:** Evaluasi kinerja guru untuk continuous improvement
- **Time Saving:** Otomasi perhitungan honor mengurangi 70% waktu processing payroll
- **Conflict Prevention:** Validasi jadwal mencegah bentrok jadwal mengajar

### Success Metrics
- 100% data guru terdigitalisasi dan lengkap
- Perhitungan honor/gaji akurat dengan error rate < 1%
- Waktu processing payroll reduced dari 2 hari ke 2 jam
- Zero konflik jadwal mengajar
- 90% guru login minimal 1x per minggu untuk cek jadwal
- Evaluasi guru dilakukan 100% tepat waktu (per semester)

---

## 📊 Epic Summary

| **Attribute** | **Value** |
|---------------|-----------|
| **Total Points** | 23 points |
| **Target Sprint** | Sprint 7 & 8 |
| **Priority** | P2 - High (after Attendance) |
| **Dependencies** | Epic 1 (Auth), Epic 3 (Attendance), Master Data |
| **Target Release** | MVP (Phase 1) |
| **Estimated Duration** | 3 minggu (1 developer) |

---

## 🎯 User Stories Included

### Teacher Profile Management (8 points)

#### US-TCH-001: Tambah & Edit Data Guru
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** menambah dan mengedit data guru  
**So that** data guru tersimpan lengkap di sistem

**Acceptance Criteria:**
- ✅ **Given** TU di halaman "Data Guru"  
   **When** TU klik "Tambah Guru Baru"  
   **Then** sistem tampilkan form: NIP/NIK, Nama Lengkap, Gelar, TTL, Jenis Kelamin, Alamat, No HP, Email, Foto, Status (Tetap/Honorer), Mata Pelajaran, Tanggal Bergabung, Pendidikan Terakhir, Gaji Tetap/Honor per Jam

- ✅ **Given** TU mengisi data guru baru dengan lengkap (Pak Budi, NIP: 123456, Status: Guru Tetap, Mapel: Matematika)  
   **When** TU klik "Simpan"  
   **Then** sistem:
   - Simpan data guru dengan status "Aktif"
   - Auto-create akun login (username: NIP, password: auto-generated)
   - Send email/WhatsApp dengan credentials
   - Tampilkan notifikasi sukses dengan link ke profil guru

- ✅ **Given** TU ingin edit data guru (update nomor HP atau gaji)  
   **When** TU klik "Edit" di profil guru, update data, dan simpan  
   **Then** sistem update data, log perubahan (audit trail), dan tampilkan notifikasi "Data guru berhasil diupdate"

- ✅ **Given** TU input NIP yang sudah terdaftar  
   **When** TU submit form  
   **Then** sistem tampilkan error "NIP sudah terdaftar. Gunakan NIP lain atau edit data guru existing"

**Technical Notes:**
- Upload foto dengan preview (max 2MB, auto-resize ke 400x400px)
- Mata pelajaran multi-select dari master data
- Gaji Tetap (Rp) untuk guru tetap, Honor per Jam (Rp) untuk guru honorer
- Auto-generate username & password untuk login guru

---

#### US-TCH-002: Lihat Profil Guru
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Kepala Sekolah  
**I want** melihat profil lengkap guru  
**So that** saya dapat melihat informasi detail guru termasuk jadwal, honor, evaluasi

**Acceptance Criteria:**
- ✅ **Given** user di halaman "Data Guru"  
   **When** user klik nama guru  
   **Then** sistem tampilkan profil guru dengan tab:
   1. Info Pribadi (biodata lengkap)
   2. Jadwal Mengajar (mingguan & bulanan)
   3. Rekap Presensi (per bulan)
   4. Rekap Honor (per bulan)
   5. Evaluasi (riwayat evaluasi)

- ✅ **Given** user di tab "Info Pribadi"  
   **When** halaman load  
   **Then** sistem tampilkan: foto guru (large), NIP, nama + gelar, TTL, alamat, no HP, email, mata pelajaran yang diampu, status (tetap/honorer), tanggal bergabung, pendidikan terakhir

- ✅ **Given** user di tab "Jadwal Mengajar"  
   **When** halaman load  
   **Then** sistem tampilkan jadwal mingguan dalam bentuk table/matrix: Hari (kolom) × Jam (baris), cell berisi Kelas + Mata Pelajaran

- ✅ **Given** kepala sekolah buka profil guru  
   **When** halaman load  
   **Then** sistem tampilkan quick actions: "Edit Data", "Buat Evaluasi", "Lihat Presensi", "Hitung Honor"

**Technical Notes:**
- Tab navigation dengan lazy loading
- Mobile responsive (stack vertical di mobile)
- Quick action buttons sesuai role (TU: Edit, KS: Evaluasi, dll)

---

#### US-TCH-008: Nonaktifkan Guru (Resign/Pensiun)
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** menonaktifkan data guru yang resign/pensiun  
**So that** data historis tetap ada tapi guru tidak muncul di daftar aktif

**Acceptance Criteria:**
- ✅ **Given** TU di halaman profil guru  
   **When** TU klik "Nonaktifkan Guru"  
   **Then** sistem tampilkan konfirmasi modal: "Yakin ingin menonaktifkan guru ini? Akun login akan di-disable. Data historis tetap tersimpan."

- ✅ **Given** TU konfirmasi nonaktifkan guru yang resign  
   **When** TU klik "Ya, Nonaktifkan"  
   **Then** sistem:
   - Update status guru jadi "Tidak Aktif"
   - Disable akun login guru
   - Remove guru dari jadwal mengajar aktif
   - Guru tidak muncul di list default (hanya muncul dengan filter "Tidak Aktif")
   - Log activity dengan alasan (resign/pensiun/lainnya)

- ✅ **Given** TU ingin lihat data guru nonaktif  
   **When** TU apply filter status "Tidak Aktif"  
   **Then** sistem tampilkan list guru yang sudah nonaktif dengan tanggal nonaktif dan alasan

- ✅ **Given** admin ingin reaktivasi guru (guru kembali lagi)  
   **When** admin klik "Aktivasi Kembali" di profil guru nonaktif  
   **Then** sistem reactivate akun dan status jadi "Aktif"

**Technical Notes:**
- Soft delete (tidak permanent delete)
- Data historis (presensi, honor, evaluasi) tetap tersimpan
- Option untuk input alasan nonaktif (resign/pensiun/PHK/lainnya)

---

### Teaching Schedule Management (3 points)

#### US-TCH-003: Input Jadwal Mengajar Guru
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** input jadwal mengajar guru  
**So that** sistem tahu guru mengajar apa, dimana, dan kapan

**Acceptance Criteria:**
- ✅ **Given** TU di halaman "Jadwal Mengajar"  
   **When** TU pilih tahun ajaran & semester, lalu klik "Tambah Jadwal"  
   **Then** sistem tampilkan form: Hari (dropdown: Senin-Sabtu), Jam Mulai, Jam Selesai, Kelas (dropdown), Mata Pelajaran (dropdown), Guru (dropdown dengan filter by mata pelajaran)

- ✅ **Given** TU input jadwal: Senin, 08:00-09:30, Kelas 3A, Matematika, Pak Budi  
   **When** TU klik "Simpan"  
   **Then** sistem:
   - Validate tidak ada konflik jadwal (guru/kelas sudah ada jadwal di jam yang sama)
   - Simpan jadwal
   - Tampilkan di calendar view dengan color-coded per guru
   - Notifikasi ke guru tentang jadwal baru (optional)

- ✅ **Given** ada konflik jadwal (Pak Budi sudah ada jadwal Senin 08:00-09:30 di Kelas 2B)  
   **When** TU coba simpan jadwal baru di jam yang sama  
   **Then** sistem tampilkan error: "Konflik Jadwal! Pak Budi sudah mengajar di Kelas 2B pada Senin 08:00-09:30. Pilih jam lain."

- ✅ **Given** TU ingin view jadwal  
   **When** TU pilih filter "Per Guru" atau "Per Kelas"  
   **Then** sistem tampilkan matrix:
   - Per Guru: Hari (kolom) × Jam (baris), cell berisi Kelas + Mapel
   - Per Kelas: Hari (kolom) × Jam (baris), cell berisi Guru + Mapel

- ✅ **Given** TU ingin copy jadwal dari semester sebelumnya  
   **When** TU klik "Copy Jadwal" dan pilih semester sumber  
   **Then** sistem copy semua jadwal ke semester aktif dengan konfirmasi changes

**Technical Notes:**
- Calendar view dengan drag & drop (advanced)
- Color-coded per guru atau per mata pelajaran
- Conflict detection real-time
- Export jadwal ke PDF untuk print
- Bulk import dari Excel (optional)

---

### Salary & Honor Calculation (5 points)

#### US-TCH-004: Rekap Jam Mengajar (Untuk Hitung Honor)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** melihat rekap jam mengajar guru per bulan  
**So that** saya dapat menghitung honor guru

**Acceptance Criteria:**
- ✅ **Given** TU di halaman "Rekap Jam Mengajar"  
   **When** TU pilih bulan (Desember 2025) dan guru (Pak Budi)  
   **Then** sistem tampilkan rekap:
   - Total Jam Terjadwal (dari teaching schedule × minggu efektif)
   - Total Jam Tidak Hadir (dari attendance: alpha/izin tanpa penggantian)
   - Total Jam Mengajar Efektif = Terjadwal - Tidak Hadir
   - Breakdown per mata pelajaran
   - Jam Ekstra (jika ada: ekskul, pengganti guru lain, dll)

- ✅ **Given** Pak Budi (guru honorer) dengan jadwal:
   - Senin: 2 jam (Matematika Kelas 1A)
   - Selasa: 2 jam (Matematika Kelas 1B)
   - Kamis: 1 jam (Matematika Kelas 2A)
   - Total per minggu: 5 jam
   - Desember: 4 minggu efektif
   **When** sistem hitung  
   **Then** sistem tampilkan:
   - Jam Terjadwal: 5 × 4 = 20 jam
   - Jam Tidak Hadir: 1 hari alpha (Senin tanggal 16) = 2 jam
   - Jam Efektif: 20 - 2 = 18 jam
   - Honor per Jam: Rp 50,000
   - Total Honor: 18 × Rp 50,000 = Rp 900,000

- ✅ **Given** TU ingin export rekap untuk payroll  
   **When** TU klik "Export Payroll"  
   **Then** sistem generate Excel dengan kolom: NIP, Nama Guru, Status, Jam Terjadwal, Jam Tidak Hadir, Jam Efektif, Honor per Jam, Total Honor, Gaji Tetap, Tunjangan, Potongan, Total Gaji

**Technical Notes:**
- Integration dengan Teaching Schedule (ambil jam mengajar)
- Integration dengan Attendance (ambil presensi guru)
- Minggu efektif exclude hari libur nasional & libur sekolah
- Support jam ekstra (overtime teaching)

---

#### US-TCH-005: Set Honor Guru
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Admin/TU  
**I want** mengatur tarif honor guru per jam  
**So that** perhitungan honor otomatis dan akurat

**Acceptance Criteria:**
- ✅ **Given** admin di halaman "Pengaturan Honor"  
   **When** admin set tarif default:
   - Guru Tetap: Rp 50,000/jam (untuk jam tambahan)
   - Guru Honorer: Rp 60,000/jam
   - Jam Ekstra/Overtime: Rp 75,000/jam
   **Then** sistem simpan konfigurasi dan apply untuk semua guru sesuai status

- ✅ **Given** Pak Budi adalah guru tetap dengan gaji tetap Rp 5,000,000/bulan  
   **When** sistem hitung gaji Desember (dengan 5 jam overtime)  
   **Then** sistem hitung:
   - Gaji Tetap: Rp 5,000,000
   - Honor Overtime: 5 × Rp 75,000 = Rp 375,000
   - Potongan (jika ada): Rp 0
   - Total Gaji: Rp 5,375,000

- ✅ **Given** ada guru dengan tarif custom (Bu Siti, guru senior, tarif Rp 80,000/jam)  
   **When** admin set tarif custom untuk Bu Siti  
   **Then** sistem:
   - Simpan tarif custom di profil Bu Siti
   - Pakai tarif custom untuk Bu Siti, override tarif default
   - Log perubahan tarif dengan timestamp

- ✅ **Given** admin ingin lihat history perubahan tarif  
   **When** admin klik "History Tarif"  
   **Then** sistem tampilkan log: tanggal, user yang ubah, tarif lama, tarif baru

**Technical Notes:**
- Tarif per kategori (default) dan per guru (custom)
- History perubahan tarif untuk audit
- Support tunjangan & potongan (transport, makan, alpha, terlambat)

---

### Teacher Evaluation (6 points)

#### US-TCH-006: Evaluasi Guru (Oleh Kepala Sekolah)
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah  
**I want** memberikan evaluasi untuk guru  
**So that** performa guru dapat dinilai dan di-improve

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah di halaman profil guru  
   **When** kepala sekolah klik "Buat Evaluasi"  
   **Then** sistem tampilkan form evaluasi:
   - Periode Evaluasi (dropdown: Semester 1/2, Tahun Ajaran)
   - 4 Aspek Penilaian (sesuai 4 Kompetensi Guru):
     1. **Pedagogik:** kemampuan mengajar, metode, pengelolaan kelas (rating 1-5, textarea komentar)
     2. **Kepribadian:** sikap, disiplin, teladan (rating 1-5, textarea)
     3. **Sosial:** komunikasi, kerjasama (rating 1-5, textarea)
     4. **Profesional:** penguasaan materi, pengembangan diri (rating 1-5, textarea)
   - Overall Score: auto-calculated (rata-rata 4 aspek)
   - Rekomendasi: dropdown (Lanjutkan/Perlu Bimbingan/Perlu Pelatihan)
   - Catatan Umum: textarea

- ✅ **Given** kepala sekolah isi evaluasi untuk Pak Budi:
   - Pedagogik: 4, "Metode mengajar menarik"
   - Kepribadian: 5, "Disiplin dan menjadi teladan"
   - Sosial: 4, "Komunikatif dengan rekan"
   - Profesional: 4, "Menguasai materi dengan baik"
   **When** kepala sekolah klik "Simpan Evaluasi"  
   **Then** sistem:
   - Calculate Overall Score: (4+5+4+4)/4 = 4.25
   - Simpan evaluasi dengan timestamp & evaluator
   - Tampilkan notifikasi sukses
   - (Optional) Send notification ke guru: "Evaluasi Anda tersedia"

- ✅ **Given** guru (Pak Budi) ingin melihat evaluasi dirinya  
   **When** guru login dan akses tab "Evaluasi" di profil sendiri  
   **Then** sistem tampilkan riwayat evaluasi (read-only):
   - Periode evaluasi
   - 4 aspek dengan rating dan komentar
   - Overall score
   - Rekomendasi
   - Tanggal evaluasi (nama evaluator hidden atau "Kepala Sekolah")

- ✅ **Given** TU ingin lihat semua evaluasi untuk HR record  
   **When** TU akses "Laporan Evaluasi Guru"  
   **Then** sistem tampilkan table: Nama Guru, Periode, Overall Score, Rekomendasi, dengan filter & export Excel

**Technical Notes:**
- Rating 1-5 dengan star icon atau slider
- Evaluasi confidential (hanya kepala sekolah, TU, dan guru yang dievaluasi)
- Archive evaluasi (tidak bisa delete, hanya edit dalam grace period 24 jam)
- Reminder ke kepala sekolah untuk evaluasi rutin (akhir semester)

---

#### US-TCH-010: Dashboard Teacher Management
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah  
**I want** melihat dashboard guru  
**So that** saya dapat quick overview data guru

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah di halaman "Dashboard Guru"  
   **When** halaman load  
   **Then** sistem tampilkan summary cards:
   1. Total Guru Aktif (dengan icon)
   2. Guru Tetap vs Honorer (breakdown pie chart atau number)
   3. Rata-rata Kehadiran Guru Bulan Ini (%)
   4. Rata-rata Evaluasi (overall score, format: 4.2/5.0)
   5. Guru dengan Presensi Buruk (< 80%, badge merah)
   6. Guru Belum Dievaluasi Semester Ini (count dengan link)

- ✅ **Given** ada 2 guru dengan presensi < 80% bulan ini (Pak Ahmad: 75%, Bu Rina: 70%)  
   **When** dashboard load  
   **Then** card "Presensi Buruk" tampilkan angka 2 dengan badge merah, click untuk detail list

- ✅ **Given** kepala sekolah ingin lihat detail performa guru  
   **When** kepala sekolah scroll ke bawah dashboard  
   **Then** sistem tampilkan:
   - Table Top Performers (5 guru dengan evaluasi tertinggi)
   - Chart: Distribusi evaluasi guru (berapa guru score 4-5, 3-4, 2-3, 1-2)
   - Timeline: trend kehadiran guru (line chart per bulan)

- ✅ **Given** kepala sekolah klik salah satu card summary  
   **When** click  
   **Then** sistem redirect ke halaman detail yang relevan (e.g., click "Presensi Buruk" → list guru dengan presensi rendah)

**Technical Notes:**
- Real-time atau cache 1 jam untuk performance
- Grafik interactive (hover untuk detail)
- Quick actions: "Tambah Guru", "Buat Evaluasi", "Lihat Jadwal"
- Mobile responsive dengan card vertical stack

---

### Export & Reporting (3 points)

#### US-TCH-009: Export Data Guru
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** TU/Kepala Sekolah  
**I want** export data guru ke Excel  
**So that** data dapat digunakan untuk laporan atau backup

**Acceptance Criteria:**
- ✅ **Given** TU di halaman "Data Guru"  
   **When** TU klik "Export ke Excel"  
   **Then** sistem generate file Excel dengan sheet:
   - Sheet 1 "Data Guru": NIP, Nama, Gelar, TTL, Jenis Kelamin, Alamat, No HP, Email, Mata Pelajaran, Status, Tanggal Bergabung, Pendidikan
   - Sheet 2 "Rekap Gaji" (optional): NIP, Nama, Gaji Tetap, Honor per Jam, Total Gaji Bulan Ini

- ✅ **Given** TU sudah apply filter (status "Aktif" dan mata pelajaran "Matematika")  
   **When** TU export  
   **Then** file Excel hanya berisi guru aktif yang mengajar Matematika

- ✅ **Given** kepala sekolah ingin export evaluasi guru untuk laporan  
   **When** kepala sekolah di halaman "Laporan Evaluasi" klik "Export"  
   **Then** sistem generate Excel: NIP, Nama, Periode, Pedagogik, Kepribadian, Sosial, Profesional, Overall Score, Rekomendasi

**Technical Notes:**
- Format Excel standar dengan header yang jelas
- Nama file: DataGuru_[Tanggal].xlsx, EvaluasiGuru_[Periode].xlsx
- Auto-adjust column width untuk readability

---

## 🏗️ Technical Architecture

### Database Schema Requirements

#### Teachers Table
```
- id (PK)
- nip (unique, nullable untuk guru honorer tanpa NIP)
- nik (unique, 16 digit)
- full_name (required)
- title (S.Pd, M.Pd, etc)
- gender (L/P)
- birth_place
- birth_date
- address (text)
- phone (required)
- email
- photo_path (storage path)
- status (tetap/honorer, required)
- education_level (S1, S2, S3)
- employment_date (tanggal bergabung)
- base_salary (Rp, untuk guru tetap, decimal)
- hourly_rate (Rp, untuk guru honorer, decimal)
- custom_hourly_rate (Rp, override default, nullable)
- is_active (boolean, default true)
- deactivation_reason (resign/pensiun/PHK/lainnya)
- deactivated_at
- user_id (FK to users, akun login guru)
- created_at
- updated_at
- deleted_at (soft delete)
```

#### Teacher_Subjects Table (Many-to-Many)
```
- id (PK)
- teacher_id (FK)
- subject_id (FK to subjects)
- created_at
- updated_at
```

#### Teaching_Schedules Table
```
- id (PK)
- academic_year_id (FK)
- semester_id (FK)
- teacher_id (FK)
- class_id (FK)
- subject_id (FK)
- day_of_week (1=Senin, 2=Selasa, ..., 6=Sabtu)
- start_time (time)
- end_time (time)
- room (optional, varchar)
- is_active (boolean)
- created_at
- updated_at
- deleted_at
```

#### Teaching_Hours_Log Table
```
- id (PK)
- teacher_id (FK)
- month (date, format: YYYY-MM-01)
- scheduled_hours (decimal, jam terjadwal dari schedule)
- absent_hours (decimal, jam tidak hadir dari attendance)
- effective_hours (scheduled_hours - absent_hours)
- extra_hours (decimal, jam ekstra/overtime)
- total_hours (effective_hours + extra_hours)
- calculated_at (timestamp)
- created_at
- updated_at
```

#### Teacher_Salaries Table
```
- id (PK)
- teacher_id (FK)
- month (date, format: YYYY-MM-01)
- base_salary (decimal, gaji tetap)
- teaching_hours (decimal)
- hourly_rate (decimal)
- teaching_pay (hours × rate)
- allowances (decimal, tunjangan: transport, makan, dll)
- deductions (decimal, potongan: alpha, terlambat, dll)
- total_salary (base + teaching_pay + allowances - deductions)
- status (draft/confirmed/paid)
- confirmed_by (FK to users)
- confirmed_at
- notes (text)
- created_at
- updated_at
```

#### Teacher_Evaluations Table
```
- id (PK)
- teacher_id (FK)
- academic_year_id (FK)
- semester_id (FK)
- evaluated_by (FK to users, kepala sekolah)
- pedagogic_score (1-5, decimal)
- pedagogic_comment (text)
- personality_score (1-5)
- personality_comment (text)
- social_score (1-5)
- social_comment (text)
- professional_score (1-5)
- professional_comment (text)
- overall_score (rata-rata 4 aspek, auto-calculated)
- recommendation (lanjutkan/bimbingan/pelatihan)
- general_notes (text)
- evaluation_date (date)
- created_at
- updated_at
```

#### Salary_Settings Table
```
- id (PK)
- setting_type (default_hourly_rate_tetap/default_hourly_rate_honorer/overtime_rate)
- amount (decimal)
- effective_from (date)
- is_active (boolean)
- created_by (FK to users)
- created_at
- updated_at
```

#### Salary_Adjustments Table (Tunjangan & Potongan)
```
- id (PK)
- salary_id (FK to teacher_salaries)
- type (allowance/deduction)
- category (transport/makan/alpha/terlambat/lainnya)
- amount (decimal)
- description (text)
- created_at
- updated_at
```

---

### API Endpoints

#### Teacher Management
- `GET /api/teachers` - List all teachers (paginated, with filters: status, subject)
- `POST /api/teachers` - Create new teacher (TU)
- `GET /api/teachers/:id` - Get teacher detail with tabs (profile, schedule, attendance, salary, evaluation)
- `PUT /api/teachers/:id` - Update teacher (TU)
- `DELETE /api/teachers/:id` - Soft delete/deactivate teacher (TU)
- `POST /api/teachers/:id/reactivate` - Reactivate inactive teacher (Admin)
- `POST /api/teachers/:id/upload-photo` - Upload teacher photo
- `GET /api/teachers/:id/subjects` - Get subjects taught by teacher
- `POST /api/teachers/:id/subjects` - Assign subjects to teacher
- `GET /api/teachers/export` - Export teachers to Excel

#### Teaching Schedule
- `GET /api/teaching-schedules` - List schedules (filters: teacher, class, day)
- `POST /api/teaching-schedules` - Create schedule (TU)
- `GET /api/teaching-schedules/:id` - Get schedule detail
- `PUT /api/teaching-schedules/:id` - Update schedule (TU)
- `DELETE /api/teaching-schedules/:id` - Delete schedule (TU)
- `POST /api/teaching-schedules/validate-conflict` - Check schedule conflict before save
- `GET /api/teaching-schedules/by-teacher/:teacher_id` - Get schedule matrix per teacher
- `GET /api/teaching-schedules/by-class/:class_id` - Get schedule matrix per class
- `POST /api/teaching-schedules/copy-from-semester` - Copy schedule from previous semester
- `GET /api/teaching-schedules/export-pdf` - Export schedule to PDF

#### Teaching Hours & Salary
- `GET /api/teaching-hours/:teacher_id/:month` - Get teaching hours recap for a teacher in specific month
- `POST /api/teaching-hours/calculate` - Calculate teaching hours for all teachers in a month
- `GET /api/salaries` - List salaries (filters: teacher, month, status)
- `POST /api/salaries/calculate` - Calculate salary for specific teacher & month
- `PUT /api/salaries/:id/confirm` - Confirm salary (TU)
- `GET /api/salaries/:id/slip` - Generate salary slip PDF
- `GET /api/salaries/export-payroll` - Export payroll to Excel
- `GET /api/salary-settings` - Get salary settings (hourly rates, etc)
- `PUT /api/salary-settings` - Update salary settings (Admin)
- `GET /api/salary-settings/history` - Get salary settings history

#### Teacher Evaluation
- `GET /api/teacher-evaluations` - List evaluations (filters: teacher, semester, year)
- `POST /api/teacher-evaluations` - Create evaluation (Kepala Sekolah)
- `GET /api/teacher-evaluations/:id` - Get evaluation detail
- `PUT /api/teacher-evaluations/:id` - Update evaluation (within 24h grace period)
- `GET /api/teacher-evaluations/by-teacher/:teacher_id` - Get evaluation history for a teacher
- `GET /api/teacher-evaluations/export` - Export evaluations to Excel

#### Dashboard
- `GET /api/teachers/dashboard/summary` - Get summary stats (total, status breakdown, avg attendance, avg evaluation)
- `GET /api/teachers/dashboard/low-attendance` - Get teachers with low attendance
- `GET /api/teachers/dashboard/top-performers` - Get top performing teachers (by evaluation)
- `GET /api/teachers/dashboard/pending-evaluations` - Get teachers without evaluation this semester

---

### Integration Points

#### INT-TCH-001: User Management (Authentication)
**Description:** Auto-create user account untuk guru baru
**Data Flow:**
1. Teacher created → Create User account
2. Generate username (NIP atau email) & random password
3. Assign role "TEACHER"
4. Send credentials via email/WhatsApp
5. Force change password on first login

**Technical:**
- API call: `POST /api/users/create-teacher-account`
- Payload: teacher_id, email, phone
- Response: user_id, username, temporary_password

---

#### INT-TCH-002: Attendance System
**Description:** Get teacher attendance untuk calculate teaching hours & salary deductions
**Data Flow:**
1. Calculate Teaching Hours → Query teacher attendance for the month
2. Get absent days & hours
3. Adjust scheduled hours dengan actual attendance
4. Calculate deductions untuk alpha/terlambat

**Technical:**
- API call: `GET /api/attendance/teacher/:teacher_id/month/:month`
- Response: total_present, total_absent, absent_hours, late_count

---

#### INT-TCH-003: Master Data (Subjects, Classes)
**Description:** Teaching schedule menggunakan master data subjects & classes
**Data Flow:**
1. Create Schedule → Dropdown subjects & classes dari master data
2. Filter subjects by teacher's assigned subjects
3. Validate class availability

**Technical:**
- API call: `GET /api/master/subjects`, `GET /api/master/classes`

---

#### INT-TCH-004: Notification Module
**Description:** Send notifications untuk teacher-related events
**Events:**
- Account created → Credentials email/WhatsApp
- Schedule updated → Notification dengan link jadwal
- Evaluation created → Notification "Evaluasi Anda tersedia"
- Salary confirmed → Notification "Slip gaji bulan X ready"

**Technical:**
- Queue-based notification
- Template per event type

---

## 🎨 UI/UX Design Requirements

### Teacher List Page

**Layout:**
- Header: "Manajemen Guru" dengan total count dan button "Tambah Guru Baru"
- Filter bar: Status (Aktif/Tidak Aktif), Mata Pelajaran, Search (nama/NIP)
- View toggle: Grid view (cards) atau Table view
- Pagination

**Grid View:**
- Card per guru dengan:
  - Foto guru (circular, 80x80px)
  - Nama + Gelar
  - Status badge (Tetap: hijau, Honorer: biru)
  - Mata pelajaran (chips, max 3 tampil, +X lainnya)
  - Quick stats: Kehadiran bulan ini (%), Evaluasi (score/5.0)
  - Action buttons: "View Profile", "Edit", "..."

**Table View:**
- Columns: Foto, NIP, Nama, Status, Mata Pelajaran, Kehadiran, Evaluasi, Actions
- Sortable columns
- Bulk actions: Export Selected

**Mobile:**
- List view dengan foto thumbnail, nama, status, collapse untuk detail

---

### Teacher Profile Page

**Layout:**
- Header card: Foto large, Nama + Gelar, Status badge, NIP/NIK, Quick actions (Edit, Nonaktifkan, dll)
- Tab navigation:
  1. Info Pribadi
  2. Jadwal Mengajar
  3. Rekap Presensi
  4. Rekap Honor/Gaji
  5. Evaluasi

**Tab 1: Info Pribadi**
- Section layout dengan icon:
  - Personal Info: TTL, Gender, Alamat, No HP, Email
  - Employment: Tanggal Bergabung, Status, Pendidikan
  - Subjects: Mata pelajaran yang diampu (chips)
  - Salary: Gaji Tetap atau Honor per Jam (hanya visible untuk TU/Admin)
- Edit button (inline edit atau modal)

**Tab 2: Jadwal Mengajar**
- Calendar widget dengan month selector
- Matrix view: Hari (kolom) × Jam (baris)
- Cell berisi: Kelas + Mapel dengan color-coded
- Total jam per minggu (summary di bawah)
- Export PDF button

**Tab 3: Rekap Presensi**
- Month selector
- Summary cards: Total Hadir, Alpha, Izin, Sakit, Percentage (gauge chart)
- Calendar view dengan color-coded per status
- Detail list: tanggal, status, keterangan

**Tab 4: Rekap Honor/Gaji**
- Month selector
- Summary card: Total Gaji bulan ini (large number)
- Breakdown table:
  - Gaji Tetap (jika ada)
  - Jam Mengajar × Honor per Jam
  - Jam Ekstra × Rate
  - Tunjangan (+)
  - Potongan (-)
  - **Total Gaji (bold)**
- Button "Download Slip Gaji" (PDF)
- History: last 6 months bar chart

**Tab 5: Evaluasi**
- List evaluasi per semester (card)
- Per evaluasi:
  - Periode (Semester 1 2024/2025)
  - 4 Kompetensi dengan score (star rating) dan komentar
  - Overall Score (large, dengan gauge chart)
  - Rekomendasi (badge)
  - Tanggal evaluasi
- Button "Buat Evaluasi Baru" (hanya untuk Kepala Sekolah)

---

### Teaching Schedule Page

**Layout:**
- Header: "Jadwal Mengajar" dengan academic year & semester selector
- View toggle: "Per Guru" atau "Per Kelas"
- Filter: Guru (dropdown), Kelas (dropdown), Hari (checkbox multi-select)
- Actions: "Tambah Jadwal", "Copy dari Semester Lalu", "Export PDF"

**Matrix View:**
- Grid layout (table style)
- Horizontal: Hari (Senin - Sabtu)
- Vertical: Jam Pelajaran (Jam 1 - 8 atau time range)
- Cell:
  - Background color-coded per guru (jika per kelas) atau per kelas (jika per guru)
  - Content: Guru + Mapel atau Kelas + Mapel
  - Click untuk edit/delete
  - Hover untuk detail tooltip

**Add Schedule Modal:**
- Form fields:
  - Guru (searchable dropdown dengan filter by subject)
  - Kelas (dropdown)
  - Mata Pelajaran (dropdown)
  - Hari (dropdown)
  - Jam Mulai - Jam Selesai (time picker)
- Real-time conflict validation dengan warning banner
- Preview: "Jadwal ini akan menambah X jam per minggu untuk [Guru]"
- Save & Add Another button

**Mobile:**
- List view per hari dengan accordion
- Filter by guru atau kelas
- Add schedule floating action button

---

### Salary Calculation Page

**Layout:**
- Header: "Perhitungan Gaji Guru" dengan month selector
- Filter: Status (Tetap/Honorer), Search nama
- Summary cards:
  - Total Payroll (semua guru)
  - Guru Tetap: X orang, Total: Rp Y
  - Guru Honorer: X orang, Total: Rp Y

**Table:**
- Columns:
  - Guru (nama + status badge)
  - Jam Terjadwal
  - Jam Tidak Hadir
  - Jam Efektif
  - Honor/Gaji
  - Tunjangan
  - Potongan
  - **Total** (bold)
  - Status (Draft/Confirmed)
  - Actions (View Detail, Confirm, Download Slip)
- Footer: Total payroll (sum semua)

**Bulk Actions:**
- Calculate All (untuk semua guru yang belum calculated)
- Confirm All (untuk approve payroll)
- Export Payroll Excel

**Detail View (Modal):**
- Guru info (foto, nama, NIP)
- Breakdown lengkap gaji dengan editable fields (untuk adjustment manual)
- Input tunjangan & potongan tambahan
- Notes textarea
- Button: "Confirm Gaji" atau "Save Draft"

**Salary Slip PDF:**
- Header: Logo sekolah, nama sekolah
- Guru info
- Breakdown gaji dalam table
- Signature area: TU, Kepala Sekolah, Guru
- Footer: tanggal generate

---

### Evaluation Form

**Layout:**
- Header: "Evaluasi Guru - [Nama Guru]"
- Periode selector: Semester & Tahun Ajaran

**Form Sections (Accordion atau Tabs):**

**Section 1: Pedagogik**
- Rating: 5-star atau slider 1-5 dengan label (Kurang - Sangat Baik)
- Komentar: textarea (min 20 karakter)
- Prompt: "Penilaian kemampuan mengajar, metode pembelajaran, dan pengelolaan kelas"

**Section 2: Kepribadian**
- Rating + Komentar
- Prompt: "Penilaian sikap, disiplin, dan menjadi teladan"

**Section 3: Sosial**
- Rating + Komentar
- Prompt: "Penilaian komunikasi dan kerjasama dengan rekan"

**Section 4: Profesional**
- Rating + Komentar
- Prompt: "Penilaian penguasaan materi dan pengembangan diri"

**Overall:**
- Auto-calculated Overall Score (large number dengan gauge chart)
- Rekomendasi: dropdown (Lanjutkan/Perlu Bimbingan/Perlu Pelatihan)
- Catatan Umum: textarea

**Footer:**
- Preview button (modal dengan preview format final)
- Save Draft button
- Submit Evaluasi button (konfirmasi: "Evaluasi tidak dapat diedit setelah 24 jam")

**UX:**
- Auto-save draft setiap 30 detik
- Smooth scroll antar section
- Visual feedback untuk rating (star animation, color change)
- Character counter untuk textarea

---

### Dashboard Teacher Management

**Layout:**
- Grid layout dengan responsive cards

**Row 1: Summary Cards**
1. Total Guru Aktif (icon: users)
2. Guru Tetap (icon: user-check, hijau)
3. Guru Honorer (icon: user-plus, biru)
4. Guru Tidak Aktif (icon: user-x, abu-abu)

**Row 2: Performance Cards**
5. Rata-rata Kehadiran (gauge chart, %)
6. Rata-rata Evaluasi (gauge chart, X/5.0)
7. Guru Presensi Buruk (badge merah, click untuk detail)
8. Guru Belum Dievaluasi (badge kuning, click untuk detail)

**Row 3: Charts**
- **Left:** Pie chart - Status Guru (Tetap vs Honorer)
- **Right:** Bar chart - Distribusi Evaluasi (berapa guru di range 4-5, 3-4, 2-3, 1-2)

**Row 4: Tables**
- **Left:** Top Performers (table: Nama, Evaluasi, Kehadiran)
- **Right:** Low Performers / Needs Attention (table: Nama, Issue, Action)

**Row 5: Timeline**
- Line chart: Trend kehadiran guru per bulan (6 bulan terakhir)

**Quick Actions (Floating or Top Right):**
- "Tambah Guru Baru"
- "Lihat Semua Jadwal"
- "Hitung Gaji Bulan Ini"
- "Buat Evaluasi"

**Mobile:**
- Cards stack vertical
- Charts resize responsive
- Tables scroll horizontal

---

## ✅ Definition of Done

### Code Level
- [ ] Unit test coverage minimal 70% untuk business logic
- [ ] Integration test untuk critical flow (create teacher, calculate salary, create evaluation)
- [ ] Code review approved
- [ ] No critical linter errors
- [ ] All API endpoints documented (Swagger/Postman)

### Functionality
- [ ] All acceptance criteria met untuk semua 10 user stories
- [ ] CRUD teacher working dengan photo upload
- [ ] Teaching schedule dengan conflict validation working
- [ ] Salary calculation accurate dengan integration ke attendance
- [ ] Salary slip PDF generation working dengan proper format
- [ ] Teacher evaluation working dengan all 4 kompetensi
- [ ] Dashboard dengan all charts & summary working
- [ ] Export to Excel working untuk data guru, payroll, dan evaluasi

### UI/UX
- [ ] Responsive di mobile dan desktop
- [ ] Loading state untuk semua async actions (calculate salary, generate PDF)
- [ ] Error handling dengan user-friendly message dalam Bahasa Indonesia
- [ ] Success feedback (toast/notification) untuk setiap action
- [ ] Teaching schedule matrix view working dengan color-coded
- [ ] Salary breakdown dengan clear calculation steps
- [ ] Evaluation form dengan smooth UX (auto-save, rating animation)

### Security
- [ ] Salary data hanya accessible oleh TU/Admin/Kepala Sekolah
- [ ] Evaluation hanya accessible oleh Kepala Sekolah (create) dan guru yang dievaluasi (read)
- [ ] Teacher can only view own profile, schedule, and evaluation
- [ ] Audit log untuk perubahan gaji dan evaluasi

### Performance
- [ ] Teacher list load < 2 detik dengan 100+ records
- [ ] Salary calculation untuk all teachers < 10 detik
- [ ] PDF generation < 3 detik
- [ ] Dashboard load < 3 detik dengan all charts
- [ ] Database queries optimized dengan proper indexes

### Integration
- [ ] Integration dengan User Management tested (auto-create account)
- [ ] Integration dengan Attendance tested (get teacher attendance)
- [ ] Integration dengan Master Data tested (subjects, classes)
- [ ] Integration dengan Notification tested (email/WhatsApp)

### Documentation
- [ ] API documentation complete dengan example requests/responses
- [ ] Database schema documented dengan relationships
- [ ] Salary calculation formula documented dengan examples
- [ ] User manual untuk TU (cara manage guru, hitung gaji)
- [ ] User manual untuk Kepala Sekolah (cara evaluasi)
- [ ] User manual untuk Guru (cara lihat jadwal, gaji, evaluasi)

---

## 🔗 Dependencies

### External Dependencies
- **PDF Library:** Library untuk generate salary slip PDF (e.g., wkhtmltopdf, Puppeteer)
- **Excel Library:** Library untuk export data (e.g., PHPSpreadsheet, ExcelJS)
- **Email/WhatsApp Service:** Untuk send credentials dan notifications
- **Chart Library:** Library untuk dashboard charts (e.g., Chart.js, Recharts)

### Internal Dependencies (Must Complete First)
- **Epic 1:** Authentication & Access Control (user management, RBAC)
- **Epic 3:** Attendance System (untuk ambil teacher attendance data)
- **Master Data:** Subjects, Classes, Academic Years, Semesters

### Blocking For
Epic 7 tidak blocking epic lain. Namun data teacher digunakan oleh:
- **Epic 3:** Attendance (untuk teacher attendance)
- **Epic 5:** Grades (untuk teacher yang input nilai)
- Modul lain yang memerlukan data guru

---

## 🧪 Testing Strategy

### Unit Testing
- Service layer: salary calculation logic, teaching hours calculation
- Validation: schedule conflict detection, NIP uniqueness
- Formula: test dengan various scenarios (guru tetap, honorer, dengan/tanpa overtime)
- Target coverage: 70%

### Integration Testing
- Teacher CRUD: create → auto-create user account → login working
- Schedule CRUD: create schedule → conflict validation → save
- Salary calculation: get teaching hours → get attendance → calculate salary → generate PDF
- Evaluation: create evaluation → calculate overall score → guru can view
- Export: teacher list → Excel file dengan proper format

### E2E Testing (Critical Paths)
1. **Add New Teacher:**
   - TU add guru baru → input all data → save → account created → credentials sent
2. **Create Teaching Schedule:**
   - TU create schedule → validate no conflict → save → guru dapat view jadwal di dashboard
3. **Calculate Salary:**
   - TU calculate salary untuk bulan Desember → sistem get teaching hours & attendance → calculate dengan formula → show breakdown → confirm → generate slip PDF
4. **Create Evaluation:**
   - Kepala Sekolah open guru profile → create evaluation → rate 4 kompetensi → save → guru dapat view evaluation di profile
5. **Schedule Conflict Prevention:**
   - TU create schedule untuk Pak Budi Senin 08:00 di Kelas 1A → save
   - TU coba create schedule untuk Pak Budi Senin 08:00 di Kelas 2B → error "Konflik Jadwal"

### Performance Testing
- Load test: 100 teachers list load time
- Salary calculation untuk 50 teachers simultaneous
- PDF generation untuk 20 salary slips
- Target:
  - List load < 2 detik
  - Salary calculation < 10 detik untuk all teachers
  - PDF generation < 3 detik per file

### Security Testing
- Access control: Guru coba akses salary data guru lain (should be blocked)
- Access control: TU coba create evaluation (should be blocked, hanya Kepala Sekolah)
- Data leak: Check evaluation tidak expose evaluator name ke guru
- Audit log: Verify semua perubahan gaji & evaluasi logged

### UAT (User Acceptance Testing)
- Test dengan TU untuk CRUD guru, schedule, dan salary calculation
- Test dengan Kepala Sekolah untuk evaluation flow
- Test dengan sample guru untuk view jadwal, gaji, evaluasi
- Collect feedback dan adjust before production

---

## 📅 Sprint Planning

### Sprint 7 (2 minggu) - 11 points
**Focus:** Teacher Profile, Schedule, & Salary Calculation

**Stories:**
- US-TCH-001: Tambah & Edit Data Guru (3 pts) - **Day 1-3**
  - CRUD teacher dengan photo upload
  - Auto-create user account
  - Send credentials
- US-TCH-002: Lihat Profil Guru (3 pts) - **Day 4-5**
  - Profile page dengan 5 tabs
  - Info pribadi, jadwal, presensi, honor, evaluasi
- US-TCH-003: Input Jadwal Mengajar (3 pts) - **Day 6-8**
  - Schedule CRUD dengan matrix view
  - Conflict validation
  - Copy dari semester sebelumnya
  - Export PDF
- US-TCH-008: Nonaktifkan Guru (2 pts) - **Day 9**
  - Soft delete dengan alasan
  - Disable user account
  - Filter inactive teachers

**Deliverables:**
- TU dapat manage data guru lengkap
- Teaching schedule management dengan conflict prevention
- Teacher dapat view profil dan jadwal sendiri
- Export schedule to PDF

**Sprint Goal:** "TU dapat manage guru dan jadwal mengajar dengan lengkap"

---

### Sprint 8 (2 minggu) - 12 points
**Focus:** Salary Calculation, Evaluation, Dashboard, & Export

**Stories:**
- US-TCH-004: Rekap Jam Mengajar (3 pts) - **Day 1-3**
  - Calculate teaching hours dari schedule & attendance
  - Rekap per bulan dengan breakdown
  - Export payroll Excel
- US-TCH-005: Set Honor Guru (2 pts) - **Day 4**
  - Settings untuk tarif default & custom
  - History perubahan tarif
- US-TCH-006: Evaluasi Guru (3 pts) - **Day 5-7**
  - Evaluation form dengan 4 kompetensi
  - Auto-calculate overall score
  - Guru dapat view evaluation sendiri
- US-TCH-009: Export Data Guru (2 pts) - **Day 8**
  - Export guru to Excel dengan filters
  - Export evaluasi to Excel
- US-TCH-010: Dashboard Teacher Management (3 pts) - **Day 9-10**
  - Summary cards
  - Charts: status, evaluasi, kehadiran
  - Top performers & low performers

**Deliverables:**
- Salary calculation working dengan accurate formula
- Salary slip PDF generation
- Teacher evaluation complete
- Dashboard dengan analytics
- Export functionality working

**Sprint Goal:** "Complete end-to-end teacher management dengan salary calculation dan evaluation"

---

## 🎯 Acceptance Criteria (Epic Level)

### Functional
- [ ] TU dapat CRUD data guru dengan lengkap (biodata, foto, status, mata pelajaran)
- [ ] Auto-create user account untuk guru baru dengan credentials sent
- [ ] TU dapat create teaching schedule dengan conflict validation
- [ ] Schedule matrix view working per guru dan per kelas
- [ ] Copy schedule dari semester sebelumnya working
- [ ] Teaching hours calculation accurate berdasarkan schedule & attendance
- [ ] Salary calculation working dengan formula yang benar (guru tetap & honorer)
- [ ] Salary slip PDF generation dengan format proper
- [ ] Export payroll to Excel working dengan all data
- [ ] Tarif honor configurable (default & custom per guru)
- [ ] Kepala Sekolah dapat create evaluation dengan 4 kompetensi
- [ ] Overall evaluation score auto-calculated dengan benar
- [ ] Guru dapat view evaluation sendiri (read-only)
- [ ] Dashboard menampilkan summary stats, charts, dan top/low performers
- [ ] Export data guru & evaluasi to Excel working
- [ ] Soft delete guru dengan disable user account working

### Non-Functional
- [ ] Mobile-responsive (tested di iOS & Android)
- [ ] Teacher list load < 2 detik dengan 100+ records
- [ ] Salary calculation untuk all teachers < 10 detik
- [ ] PDF generation < 3 detik per file
- [ ] User-friendly error messages dalam Bahasa Indonesia
- [ ] Success feedback untuk setiap action
- [ ] Loading indicator untuk calculate salary & generate PDF

### Integration
- [ ] Integration dengan User Management: auto-create account working
- [ ] Integration dengan Attendance: get teacher attendance working
- [ ] Integration dengan Master Data: subjects & classes working
- [ ] Integration dengan Notification: send credentials & evaluasi notification

### Technical
- [ ] API documentation complete (Swagger)
- [ ] Database schema implemented dengan proper indexes
- [ ] Unit test coverage 70%
- [ ] Integration test untuk critical flows
- [ ] Salary calculation formula documented
- [ ] Audit log untuk gaji & evaluasi changes

---

## 🚧 Risks & Mitigation

### Risk 1: Salary Calculation Accuracy
**Impact:** Critical - Kesalahan perhitungan gaji dapat cause serious issue  
**Probability:** Medium  
**Mitigation:**
- Comprehensive unit test untuk semua scenarios (guru tetap, honorer, overtime, potongan)
- Manual verification dengan sample data sebelum production
- Dry run dengan TU: hitung manual vs sistem (compare results)
- Audit trail untuk setiap calculation (log formula & inputs)
- Grace period untuk correction (TU dapat edit dalam 3 hari sebelum confirm)
- Clear breakdown dalam slip gaji untuk transparency

---

### Risk 2: Schedule Conflict Issues
**Impact:** High - Double booking guru atau kelas dapat cause chaos  
**Probability:** Medium  
**Mitigation:**
- Real-time conflict validation (frontend & backend)
- Clear error message dengan detail conflict (guru X sudah di kelas Y jam Z)
- Validation sebelum copy schedule dari semester lalu
- Weekly auto-check untuk detect any conflict (send report ke TU)
- Allow override dengan approval (untuk emergency substitution)

---

### Risk 3: Teaching Hours Miscalculation
**Impact:** High - Salah hitung jam mengajar = salah gaji  
**Probability:** Medium  
**Mitigation:**
- Clear definition of "minggu efektif" (exclude libur)
- Integration test dengan various attendance scenarios
- Manual review mandatory untuk first 3 months
- Comparison report: sistem vs manual calculation
- Allow manual adjustment dengan audit log
- Dashboard alert jika ada anomaly (e.g., jam efektif > jam terjadwal)

---

### Risk 4: PDF Generation Performance
**Impact:** Medium - Slow PDF generation dapat frustrate users  
**Probability:** Low  
**Mitigation:**
- Async PDF generation dengan queue
- Cache PDF untuk 24 jam (regenerate jika data berubah)
- Optimize PDF template (simpler design, less images)
- Use efficient PDF library (benchmark sebelum choose)
- Loading indicator dengan estimated time
- Allow download dari email (jika generation lama)

---

### Risk 5: Evaluation Subjectivity & Fairness
**Impact:** Medium - Evaluasi tidak objektif dapat cause guru dissatisfaction  
**Probability:** High  
**Mitigation:**
- Clear rubric untuk setiap kompetensi (dokumentasi guidelines)
- Training untuk Kepala Sekolah tentang cara evaluasi yang fair
- Anonymous feedback dari siswa/orang tua (optional, untuk reference)
- Evaluasi dilakukan rutin (konsisten setiap semester)
- Guru dapat add self-reflection (optional textarea)
- Evaluation result discussed dalam 1-on-1 meeting (not just online)

---

### Risk 6: Data Privacy (Salary & Evaluation)
**Impact:** High - Salary & evaluation data sangat sensitif  
**Probability:** Low  
**Mitigation:**
- Strict RBAC: hanya TU/Admin/KS yang dapat akses salary semua guru
- Guru hanya dapat view salary & evaluation sendiri
- Evaluation creator name hidden dari guru (hanya show "Kepala Sekolah")
- Audit log untuk setiap access ke salary & evaluation pages
- Data encryption untuk salary & evaluation tables
- Regular security audit

---

## 📊 Success Metrics & KPIs

### Sprint 7 (Teacher Profile & Schedule)
- [ ] 100% user stories completed (4/4)
- [ ] Zero critical bugs in production
- [ ] Teacher account creation success rate 100%
- [ ] Schedule conflict detection accuracy 100%
- [ ] Average schedule creation time < 2 menit per entry

### Sprint 8 (Salary, Evaluation, Dashboard)
- [ ] 100% user stories completed (5/5)
- [ ] Salary calculation accuracy 100% (vs manual verification)
- [ ] PDF generation success rate > 98%
- [ ] Evaluation completion rate 100% (all guru dievaluasi per semester)
- [ ] Dashboard load time < 3 detik

### Epic Level (Post-Launch, First Semester)
- [ ] Total 23 points delivered
- [ ] 100% guru data terdigitalisasi
- [ ] Payroll processing time reduced dari 2 hari ke 2 jam (90% improvement)
- [ ] Zero salary calculation errors reported
- [ ] Zero schedule conflicts in production
- [ ] 90% guru login minimal 1x per minggu untuk cek jadwal
- [ ] 100% guru dievaluasi tepat waktu (end of semester)
- [ ] TU satisfaction score > 4/5 untuk salary calculation feature
- [ ] Guru satisfaction score > 3.5/5 untuk transparency (dapat view gaji & evaluasi)

### Business Metrics (First Year)
- [ ] HR administrative time reduced 50%
- [ ] Payroll error rate < 1% (vs 10-15% manual process)
- [ ] Evaluation completion rate 100% (vs 60-70% manual process)
- [ ] Cost saving dari paper-based payroll: Rp 500K/month
- [ ] Teacher turnover rate maintained atau improved (indicator: good HR management)

---

## 📝 Notes & Assumptions

### Assumptions
1. Teaching hours dalam satuan jam (1 jam = 45 menit atau 1 jam penuh, configurable)
2. Salary calculation dilakukan monthly (end of month atau awal bulan berikutnya)
3. Evaluation dilakukan minimal 1x per semester (target: 100% completion)
4. Guru tetap mendapat gaji tetap + honor untuk jam tambahan (overtime)
5. Guru honorer hanya dapat honor berdasarkan jam mengajar efektif
6. Potongan untuk alpha/terlambat configurable oleh admin
7. Bank transfer untuk salary payment (tidak include payment gateway untuk MVP)

### Out of Scope (Epic 7 MVP)
- ❌ Leave/cuti management untuk guru - Phase 2
- ❌ Sertifikasi & pelatihan tracking - Phase 2
- ❌ Replacement teacher (guru pengganti) - Phase 2
- ❌ Survei kepuasan dari orang tua/siswa ke guru - Phase 2
- ❌ Teacher performance ranking/leaderboard - Phase 2
- ❌ Integration dengan sistem kepegawaian nasional - Phase 2
- ❌ Automatic payroll transfer to bank - Phase 2
- ❌ Multi-school support (untuk yayasan dengan banyak sekolah) - Phase 2

### Nice to Have (Not Required for MVP)
- Drag & drop untuk teaching schedule (saat ini: form-based input)
- Mobile app untuk guru (saat ini: web responsive)
- Push notification untuk schedule changes
- Integration dengan Google Calendar
- Teacher skill matrix & competency tracking
- Career development planning

### Technical Decisions
- PDF generation: Use server-side library untuk consistency
- Teaching hours calculation: Cron job monthly + manual trigger option
- Salary settings: Single source of truth, versioned untuk history
- Evaluation: Grace period 24 jam untuk edit (after that, locked)
- Schedule conflict: Hard validation (tidak allow override) untuk MVP

---

## 🔄 Review & Refinement

### Sprint 7 Review
**Date:** TBD (end of sprint 7)  
**Attendees:** Development Team, Product Owner, TU Staff

**Review Checklist:**
- [ ] Demo teacher CRUD dengan photo upload
- [ ] Demo teaching schedule dengan conflict validation
- [ ] Get feedback dari TU tentang schedule UI (mudah digunakan?)
- [ ] Test schedule conflict detection dengan various scenarios
- [ ] Identify improvement areas untuk Sprint 8
- [ ] Verify integration dengan User Management working

---

### Sprint 8 Review
**Date:** TBD (end of sprint 8)  
**Attendees:** Development Team, Product Owner, TU, Kepala Sekolah, Sample Teachers

**Review Checklist:**
- [ ] Demo complete teacher management flow
- [ ] Demo salary calculation dengan various scenarios
- [ ] Verify salary calculation accuracy dengan manual calculation
- [ ] Demo evaluation flow (KS create → guru view)
- [ ] Demo dashboard dengan all charts
- [ ] Get feedback dari TU tentang salary calculation (accurate? easy?)
- [ ] Get feedback dari Kepala Sekolah tentang evaluation form
- [ ] Get feedback dari guru tentang profile page (informative?)
- [ ] User acceptance testing (UAT) dengan actual data
- [ ] Performance review (calculation speed, PDF generation)
- [ ] Security review (data privacy, RBAC)
- [ ] Documentation complete check
- [ ] Go/No-Go decision untuk production launch

---

### Epic Review (Post-Launch, After First Semester)
**Date:** TBD (setelah 1 semester usage)  
**Attendees:** All stakeholders

**Retrospective Questions:**
1. **What went well?**
   - Which features saved most time for TU?
   - Which features teachers appreciate most?
   - Salary calculation accuracy?
2. **What didn't go well?**
   - Any salary calculation errors?
   - Any schedule conflicts in production?
   - Any bugs/issues during payroll period?
3. **What can we improve?**
   - UI/UX improvements based on usage?
   - Additional features needed?
   - Performance optimization needed?
4. **Metrics review:**
   - Did we achieve success metrics?
   - Payroll processing time reduced?
   - Evaluation completion rate?

**Action Items for Phase 2:**
- List features untuk next release (leave management, teacher survey, dll)
- Prioritize based on impact & effort
- Plan timeline untuk development

---

## ✅ Epic Checklist (Before Production Launch)

### Development
- [ ] All 10 user stories implemented dan tested
- [ ] Code merged to main branch
- [ ] Database migration successful di staging & production
- [ ] API documentation published dan reviewed
- [ ] Salary calculation formula documented dengan examples

### Testing
- [ ] Unit test pass (coverage 70%)
- [ ] Integration test pass untuk all critical flows
- [ ] E2E test pass (5 critical paths)
- [ ] Security test pass (RBAC, data privacy)
- [ ] Performance test pass (list < 2s, calculation < 10s, PDF < 3s)
- [ ] Salary calculation verified dengan manual calculation (sample data)
- [ ] UAT approved by TU, Kepala Sekolah, dan sample teachers

### Deployment
- [ ] Deployed to staging environment
- [ ] Staging tested dengan production-like data (at least 50 teachers)
- [ ] Production database backup ready
- [ ] Rollback plan documented
- [ ] Deployed to production
- [ ] Monitoring & logging active (error tracking, performance monitoring)
- [ ] Alert configured untuk critical issues (salary calculation errors)

### Documentation
- [ ] Technical documentation complete (architecture, database, APIs, formulas)
- [ ] User manual untuk TU (manage guru, jadwal, hitung gaji) - Bahasa Indonesia
- [ ] User manual untuk Kepala Sekolah (evaluasi guru) - Bahasa Indonesia
- [ ] User manual untuk Guru (view jadwal, gaji, evaluasi) - Bahasa Indonesia
- [ ] Salary calculation guide dengan examples
- [ ] Evaluation guidelines (rubric untuk 4 kompetensi)
- [ ] API documentation complete (Swagger/Postman)
- [ ] FAQ document ready

### Training & Support
- [ ] Training session untuk TU (manage guru, schedule, salary calculation)
- [ ] Training session untuk Kepala Sekolah (evaluation process & guidelines)
- [ ] Training session untuk Guru (cara login, view jadwal/gaji/evaluasi)
- [ ] Demo video untuk common tasks (max 5 menit each)
- [ ] Internal troubleshooting guide untuk common issues
- [ ] Support hotline/WhatsApp prepared

### Data Migration (if applicable)
- [ ] Existing teacher data migrated to new system
- [ ] Historical salary data migrated (optional, jika ada)
- [ ] User accounts created untuk all existing teachers
- [ ] Data validation post-migration (100% teachers should be accessible)

### Communication
- [ ] Announcement ke all teachers tentang sistem baru
- [ ] Send credentials ke all teachers via email/WhatsApp
- [ ] Info session scheduled (optional, jika banyak yang perlu bantuan)
- [ ] Feedback channel setup (WhatsApp group atau email)

---

## 📞 Contact & Support

**Epic Owner:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733  
**Email:** [Your Email]

**For Technical Issues:**
- WhatsApp: +62 857-1583-8733 (Developer)
- Email: dev-support@sekolah.app
- Response time: < 4 jam (during business hours)

**For Payroll/Salary Questions:**
- Contact TU: [TU Name & Phone]
- Email: tu@sekolah.app

**For Evaluation Questions:**
- Contact Kepala Sekolah: [Name & Phone]

**For Emergency (System Down during Payroll Period):**
- Call Developer: +62 857-1583-8733
- Escalate to: [IT Manager/CTO]
- Fallback: Manual payroll process (documented)

---

**Document Status:** ✅ Ready for Sprint Planning  
**Last Review:** 13 Desember 2025  
**Next Review:** After Sprint 7 completion

---

*Catatan: Dokumen ini adalah living document dan akan diupdate seiring progress sprint. Semua perubahan akan dicatat di section "Change Log" di bawah.*

## 📋 Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 13 Des 2025 | 1.0 | Initial creation of EPIC 7 document | Zulfikar Hidayatullah |

---

## 🎨 Appendix: Wireframes & Mockups

### A. Teacher List (Table View - Desktop)
```
┌───────────────────────────────────────────────────────────────┐
│ Manajemen Guru                     Total: 45 guru              │
│ ┌──────────────────────────────────────┐ [+ Tambah Guru Baru] │
│ │ 🔍 Cari nama/NIP...                  │                       │
│ └──────────────────────────────────────┘                       │
│ Filter: [Status ▼] [Mata Pelajaran ▼]    View: [Grid] [Table]│
│ ─────────────────────────────────────────────────────────────│
│ Foto  NIP      Nama           Status    Mapel      Kehadiran │
│ ─────────────────────────────────────────────────────────────│
│ [👤] 123456  Pak Budi, S.Pd  [Tetap]   Mat, IPA    95%  4.2  │
│ [👤] 123457  Bu Siti, M.Pd   [Honorer] B.Ind       88%  4.5  │
│ [👤] 123458  Pak Ahmad        [Tetap]   Penjaskes   92%  4.0  │
│ ...                                                            │
│ ─────────────────────────────────────────────────────────────│
│ Showing 1-10 of 45                        < 1 2 3 4 5 >       │
└───────────────────────────────────────────────────────────────┘
```

### B. Teaching Schedule Matrix (Per Guru)
```
┌────────────────────────────────────────────────────────┐
│ Jadwal Mengajar - Pak Budi, S.Pd                      │
│ Semester 1, 2024/2025          [Copy] [Export PDF]    │
│ ────────────────────────────────────────────────────  │
│        Senin    Selasa   Rabu     Kamis    Jumat      │
│ ────────────────────────────────────────────────────  │
│ 1  │  1A      │  1B      │        │  2A    │          │
│ 07 │  Mat     │  Mat     │        │  Mat   │          │
│ ────────────────────────────────────────────────────  │
│ 2  │  1A      │  1B      │  1C    │        │  2B      │
│ 08 │  Mat     │  Mat     │  Mat   │        │  Mat     │
│ ────────────────────────────────────────────────────  │
│ 3  │          │          │  1C    │        │  2B      │
│ 09 │          │          │  Mat   │        │  Mat     │
│ ────────────────────────────────────────────────────  │
│ ...                                                    │
│ ────────────────────────────────────────────────────  │
│ Total: 18 jam/minggu                                   │
└────────────────────────────────────────────────────────┘
```

### C. Salary Calculation Detail (Desktop)
```
┌─────────────────────────────────────────────────────┐
│ Perhitungan Gaji - Pak Budi, S.Pd                  │
│ Bulan: Desember 2025                                │
│ ─────────────────────────────────────────────────  │
│                                                     │
│ RINCIAN JAM MENGAJAR                                │
│ ┌─────────────────────────────────┐                │
│ │ Jam Terjadwal:        20 jam    │                │
│ │ Jam Tidak Hadir:       2 jam    │ (1 hari alpha) │
│ │ Jam Efektif:          18 jam    │                │
│ │ Jam Ekstra/Overtime:   5 jam    │                │
│ └─────────────────────────────────┘                │
│                                                     │
│ PERHITUNGAN GAJI                                    │
│ ┌─────────────────────────────────────────┐        │
│ │ Gaji Tetap:                Rp 5,000,000 │        │
│ │ Honor Mengajar (18×50K):     Rp 900,000 │        │
│ │ Honor Overtime (5×75K):      Rp 375,000 │        │
│ │                            ────────────  │        │
│ │ Subtotal:                  Rp 6,275,000 │        │
│ │                                          │        │
│ │ Tunjangan Transport:         Rp 200,000 │        │
│ │ Potongan Alpha (1 hari):    -Rp 100,000 │        │
│ │                            ────────────  │        │
│ │ TOTAL GAJI:                Rp 6,375,000 │  ✅    │
│ └─────────────────────────────────────────┘        │
│                                                     │
│ Catatan: ___________________________________        │
│                                                     │
│ [Simpan Draft]    [Konfirmasi Gaji]  [Download]   │
└─────────────────────────────────────────────────────┘
```

### D. Evaluation Form (Desktop)
```
┌───────────────────────────────────────────────────────┐
│ Evaluasi Guru - Pak Budi, S.Pd                       │
│ Periode: Semester 1, 2024/2025                        │
│ ───────────────────────────────────────────────────  │
│                                                       │
│ 1. PEDAGOGIK                                          │
│    Penilaian kemampuan mengajar & pengelolaan kelas  │
│    Rating: ⭐⭐⭐⭐⭐ (5/5)                             │
│    ┌────────────────────────────────────────┐        │
│    │ Komentar:                              │        │
│    │ Metode mengajar sangat menarik dan     │        │
│    │ inovatif. Siswa aktif di kelas.        │        │
│    └────────────────────────────────────────┘        │
│                                                       │
│ 2. KEPRIBADIAN                                        │
│    Penilaian sikap, disiplin, dan teladan            │
│    Rating: ⭐⭐⭐⭐⭐ (5/5)                             │
│    ┌────────────────────────────────────────┐        │
│    │ Komentar:                              │        │
│    │ Sangat disiplin, menjadi teladan bagi  │        │
│    │ guru lain.                              │        │
│    └────────────────────────────────────────┘        │
│                                                       │
│ 3. SOSIAL (Rating: ⭐⭐⭐⭐ - 4/5)                     │
│ 4. PROFESIONAL (Rating: ⭐⭐⭐⭐ - 4/5)                 │
│                                                       │
│ ───────────────────────────────────────────────────  │
│ Overall Score: 4.5 / 5.0   [████████░░] 90%         │
│                                                       │
│ Rekomendasi: [Lanjutkan ▼]                           │
│                                                       │
│ Catatan Umum: ______________________________         │
│                                                       │
│ [Preview]  [Simpan Draft]  [Submit Evaluasi]        │
└───────────────────────────────────────────────────────┘
```

### E. Teacher Profile - Mobile View
```
┌─────────────────────┐
│ ← Profil Guru       │
│                     │
│     [👤 Foto]       │
│                     │
│ Pak Budi, S.Pd      │
│ NIP: 123456         │
│ [Guru Tetap]        │
│                     │
│ ┌─────────────────┐ │
│ │ ➤ Info Pribadi  │ │
│ ├─────────────────┤ │
│ │ ➤ Jadwal        │ │
│ ├─────────────────┤ │
│ │ ➤ Presensi      │ │
│ ├─────────────────┤ │
│ │ ➤ Gaji          │ │
│ ├─────────────────┤ │
│ │ ➤ Evaluasi      │ │
│ └─────────────────┘ │
│                     │
│ [Edit]  [Nonaktif]  │
└─────────────────────┘
```

---

**End of EPIC 7 Document**
