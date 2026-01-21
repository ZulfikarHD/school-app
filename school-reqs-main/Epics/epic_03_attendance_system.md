# EPIC 3: Attendance System (Sistem Absensi)

**Project:** SD Management System  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Epic Owner:** Development Team  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Epic Overview

### Goal
Sistem dapat mengelola absensi siswa (harian dan per mata pelajaran) serta presensi guru, termasuk workflow pengajuan dan verifikasi izin/sakit, dengan notifikasi otomatis dan reporting yang komprehensif.

### Business Value
- **Digitalisasi Absensi:** Menghilangkan pencatatan manual dengan kertas yang rawan hilang
- **Real-Time Monitoring:** Kepala Sekolah dan TU dapat monitor kehadiran siswa & guru secara real-time
- **Parent Engagement:** Orang tua mendapat notifikasi langsung jika anak tidak hadir
- **Efisiensi Guru:** Input absensi cepat dan mudah via mobile
- **Data-Driven Decision:** Data kehadiran untuk deteksi siswa bermasalah dan perhitungan honor guru
- **Compliance:** Tracking kehadiran siswa untuk requirement naik kelas (minimum 80%)

### Success Metrics
- Absensi input time < 2 menit untuk 1 kelas (30 siswa)
- Parent notification delivery rate > 95% dalam 5 menit setelah input
- Teacher adoption rate > 90% untuk clock in/out
- Zero data loss atau duplicate attendance records
- Attendance report generation time < 5 detik untuk 1 bulan data
- User satisfaction > 4.5/5 untuk kemudahan input absensi

---

## 📊 Epic Summary

| **Attribute** | **Value** |
|---------------|-----------|
| **Total Points** | 30 points |
| **Target Sprint** | Sprint 5 & 6 |
| **Priority** | Critical - P0 |
| **Dependencies** | EPIC 1 (Authentication), EPIC 2 (Student Management) |
| **Target Release** | MVP (Phase 1) |
| **Estimated Duration** | 3-4 minggu (1 developer) |

---

## 🎯 User Stories Included

### Student Attendance (17 points)

#### US-ATT-001: Input Absensi Harian Pagi (Siswa)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Guru/Wali Kelas  
**I want** input absensi harian siswa di pagi hari  
**So that** kehadiran siswa tercatat dengan akurat

**Acceptance Criteria:**

✅ **Given** guru login dan di dashboard  
   **When** guru klik menu "Absensi Harian"  
   **Then** sistem tampilkan list kelas yang diajar guru dan tanggal hari ini

✅ **Given** guru pilih kelas dan tanggal  
   **When** guru klik "Input Absensi"  
   **Then** sistem tampilkan list semua siswa di kelas tersebut dengan status default "Hadir"

✅ **Given** guru ubah status siswa yang sakit menjadi "Sakit"  
   **When** guru klik "Simpan Absensi"  
   **Then** sistem:
   - Simpan data absensi dengan timestamp & user_id
   - Tampilkan notifikasi "Absensi berhasil disimpan"
   - Tampilkan summary: "Hadir: X, Izin: Y, Sakit: Z, Alpha: W"

✅ **Given** guru sudah input absensi untuk hari ini  
   **When** guru coba input lagi  
   **Then** sistem tampilkan data yang sudah diinput (mode edit, bukan create baru)

✅ **Given** ada siswa dengan pengajuan izin yang sudah di-approve untuk hari ini  
   **When** guru buka form absensi  
   **Then** status siswa tersebut auto-set "Izin" atau "Sakit" (tidak bisa diubah manual)

**Technical Notes:**
- Status absensi: Hadir (H), Sakit (S), Izin (I), Alpha (A)
- Default semua siswa: Hadir (untuk mempercepat input)
- Validasi: tidak boleh duplikat untuk kelas & tanggal yang sama
- Quick action: button "Tandai Semua Hadir"
- Mobile-friendly (guru sering input via HP)
- Auto-save draft setiap 30 detik (prevent data loss)

---

#### US-ATT-002: Input Absensi Per Mata Pelajaran (Siswa)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Guru Mata Pelajaran  
**I want** input absensi siswa per mata pelajaran yang saya ajar  
**So that** kehadiran siswa di setiap pelajaran tercatat

**Acceptance Criteria:**

✅ **Given** guru di halaman "Absensi Per Pelajaran"  
   **When** guru pilih kelas, mata pelajaran, dan tanggal  
   **Then** sistem tampilkan list siswa dengan status default "Hadir"

✅ **Given** guru input absensi untuk Matematika kelas 3A jam pelajaran ke-2  
   **When** guru simpan  
   **Then** data absensi tersimpan dengan metadata:
   - Kelas ID
   - Mata pelajaran ID
   - Tanggal & jam pelajaran
   - Teacher ID yang input

✅ **Given** siswa tidak hadir di mata pelajaran tertentu (misal: izin dari pelajaran ke-3 karena sakit)  
   **When** guru ubah status jadi "Izin"  
   **Then** sistem catat absensi khusus untuk mata pelajaran tersebut (tidak affect absensi harian)

✅ **Given** sistem ada jadwal pelajaran  
   **When** guru buka form absensi per pelajaran  
   **Then** sistem auto-suggest mata pelajaran & jam berdasarkan jadwal hari ini

**Technical Notes:**
- Optional untuk SD (bisa hanya pakai absensi harian)
- Berguna untuk strict tracking per mata pelajaran
- Unique constraint: tanggal + kelas + mata_pelajaran + jam_pelajaran
- Integration dengan jadwal (future enhancement)

---

#### US-ATT-003: Ajukan Izin/Surat Sakit (Orang Tua)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Orang Tua  
**I want** mengajukan izin atau upload surat sakit untuk anak saya  
**So that** ketidakhadiran anak tercatat resmi dan tidak dianggap alpha

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua klik "Ajukan Izin/Sakit"  
   **Then** sistem tampilkan form:
   - Pilih Anak (dropdown, jika multiple)
   - Jenis: Izin atau Sakit (radio button)
   - Tanggal Mulai (date picker)
   - Tanggal Selesai (date picker, untuk multiple hari)
   - Alasan (textarea, required, min 10 karakter)
   - Upload Foto Surat (optional, image/pdf, max 5MB)

✅ **Given** orang tua isi form izin untuk besok (anak sakit)  
   **When** orang tua submit  
   **Then** sistem:
   - Simpan pengajuan dengan status "Pending"
   - Kirim notifikasi ke wali kelas & TU
   - Tampilkan success message: "Pengajuan izin telah dikirim dan menunggu persetujuan"

✅ **Given** orang tua upload foto surat dokter  
   **When** file berhasil diupload  
   **Then** file tersimpan dan dapat diakses oleh guru/TU untuk verifikasi

✅ **Given** orang tua ingin lihat status pengajuan  
   **When** orang tua buka halaman "Riwayat Izin"  
   **Then** sistem tampilkan list dengan status: Pending / Disetujui / Ditolak

**Technical Notes:**
- Upload file: JPG, PNG, PDF (max 5MB)
- Tanggal izin tidak boleh past date > 7 hari (fleksibilitas koreksi)
- Bisa ajukan izin untuk future date maksimal 30 hari (advance notice)
- Notifikasi real-time ke wali kelas (via WhatsApp/email/in-app)

---

#### US-ATT-004: Verifikasi Izin/Sakit (Guru/TU)
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Wali Kelas/TU  
**I want** verifikasi pengajuan izin/sakit dari orang tua  
**So that** data absensi akurat dan dapat dipercaya

**Acceptance Criteria:**

✅ **Given** ada pengajuan izin baru  
   **When** guru login  
   **Then** sistem tampilkan badge notifikasi "X izin pending" di menu/icon

✅ **Given** guru di halaman "Verifikasi Izin"  
   **When** guru lihat list pengajuan  
   **Then** sistem tampilkan list dengan columns:
   - Foto siswa
   - Nama siswa & kelas
   - Jenis (Izin/Sakit)
   - Tanggal
   - Alasan
   - Foto surat (thumbnail, click untuk preview)
   - Actions (Setujui/Tolak)

✅ **Given** guru setujui pengajuan izin  
   **When** guru klik "Setujui"  
   **Then** sistem:
   - Update status jadi "Approved"
   - Auto-update absensi siswa untuk tanggal tersebut jadi "Izin/Sakit"
   - Kirim notifikasi ke orang tua: "Pengajuan izin disetujui"
   - Record approval (user_id, timestamp)

✅ **Given** guru tolak pengajuan (misal: alasan tidak jelas)  
   **When** guru klik "Tolak" dan input alasan penolakan  
   **Then** sistem:
   - Update status jadi "Rejected"
   - Kirim notifikasi ke orang tua: "Pengajuan izin ditolak. Alasan: {alasan}"
   - Record rejection (user_id, timestamp, reason)

**Technical Notes:**
- Hanya guru kelas atau TU yang bisa approve/reject
- Setelah di-approve, absensi tidak bisa diubah manual
- History approval tersimpan untuk audit
- Auto-approve untuk sakit dengan surat dokter (optional, config)

---

#### US-ATT-005: Rekap Absensi Siswa (Bulanan)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Guru/TU/Kepala Sekolah  
**I want** melihat rekap absensi siswa per bulan  
**So that** saya dapat monitoring kedisiplinan siswa

**Acceptance Criteria:**

✅ **Given** user di halaman "Rekap Absensi Siswa"  
   **When** user set filter: Kelas 3A, Bulan Desember 2025  
   **Then** sistem tampilkan tabel rekap:
   - Columns: Nama Siswa, Total Hadir, Sakit, Izin, Alpha, Persentase Kehadiran
   - Sorted by persentase kehadiran (ascending, siswa bermasalah di atas)

✅ **Given** siswa A punya alpha > 3 kali atau kehadiran < 80%  
   **When** sistem generate rekap  
   **Then** baris siswa A di-highlight merah dengan warning icon

✅ **Given** user ingin lihat detail absensi harian siswa tertentu  
   **When** user klik nama siswa  
   **Then** sistem tampilkan calendar view dengan detail absensi per hari

✅ **Given** user ingin export rekap absensi ke Excel  
   **When** user klik "Export Excel"  
   **Then** sistem generate file `Absensi_{Kelas}_{Bulan}_{Tahun}.xlsx` dengan data rekap

✅ **Given** user ingin lihat chart visualisasi  
   **When** user klik tab "Grafik"  
   **Then** sistem tampilkan:
   - Pie chart: proporsi Hadir/Izin/Sakit/Alpha untuk seluruh kelas
   - Bar chart: trend kehadiran per minggu

**Technical Notes:**
- Persentase kehadiran = (Hadir + Izin + Sakit) / Total Hari Sekolah × 100%
- Exclude weekend dan hari libur nasional
- Filter: per kelas, per siswa, rentang tanggal, jenis absensi (harian/per mapel)
- Access control: Guru hanya siswa di kelas yang diajar, TU & Principal semua siswa

---

#### US-ATT-006: Lihat Absensi Anak (Portal Orang Tua)
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** Orang Tua  
**I want** melihat rekap absensi anak saya  
**So that** saya dapat memantau kedisiplinan anak

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua klik tab "Absensi" pada profil anak  
   **Then** sistem tampilkan rekap absensi bulan ini:
   - Summary cards: Total Hadir, Sakit, Izin, Alpha, Persentase Kehadiran
   - Chart (pie chart): proporsi kehadiran
   - List absensi harian (tanggal, status, keterangan)

✅ **Given** anak alpha 2x atau lebih dalam sebulan  
   **When** orang tua lihat rekap  
   **Then** sistem tampilkan warning banner di bagian atas:
   "⚠️ Anak Anda memiliki {X} ketidakhadiran tanpa keterangan. Persentase kehadiran: {Y}%"

✅ **Given** orang tua ingin lihat detail per hari  
   **When** orang tua scroll ke bawah  
   **Then** sistem tampilkan calendar view interaktif dengan color coding per status

**Technical Notes:**
- Real-time data (sinkron dengan input guru)
- Mobile-friendly (mayoritas orang tua pakai HP)
- Color coding: Hijau=Hadir, Kuning=Izin, Biru=Sakit, Merah=Alpha
- Push notification jika anak alpha (optional)

---

### Teacher Attendance (13 points)

#### US-ATT-007: Input Presensi Guru (Manual Digital)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Guru  
**I want** input presensi kehadiran saya sendiri  
**So that** kehadiran saya tercatat di sistem

**Acceptance Criteria:**

✅ **Given** guru login ke sistem di pagi hari  
   **When** guru klik "Clock In" di dashboard  
   **Then** sistem:
   - Capture timestamp
   - Request location access (optional)
   - Capture GPS coordinates (jika allow)
   - Tampilkan "Anda sudah clock in pada {jam}. Lokasi: {alamat}"
   - Button berubah jadi "Clock Out"

✅ **Given** guru selesai jam mengajar  
   **When** guru klik "Clock Out"  
   **Then** sistem:
   - Capture timestamp keluar
   - Calculate total jam kerja (jam_out - jam_in)
   - Tampilkan "Clock out pada {jam}. Total jam kerja: {X} jam {Y} menit"
   - Save record

✅ **Given** guru lupa clock in di pagi hari  
   **When** guru coba clock in jam 10:00  
   **Then** sistem:
   - Allow clock in dengan flag "Terlambat"
   - Tampilkan warning "Anda terlambat. Jam masuk ideal: 07:00-07:30"

✅ **Given** guru di luar area sekolah (> 100m radius)  
   **When** guru coba clock in  
   **Then** sistem:
   - Tampilkan warning "⚠️ Anda di luar area sekolah"
   - Tetap allow clock in dengan flag "Out of Range"
   - TU dapat review location data

**Technical Notes:**
- Clock in ideal: 07:00-07:30
- Status Hadir: clock in < 07:30
- Status Terlambat: clock in >= 07:30
- Geolocation tracking optional (bisa aktif/nonaktif per config)
- Satu guru hanya bisa clock in 1x per hari (edit mode jika sudah)
- TU bisa edit manual jika guru lupa

---

#### US-ATT-008: Approval Izin/Cuti Guru (Kepala Sekolah/TU)
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah/TU  
**I want** approve atau reject pengajuan izin/cuti guru  
**So that** jadwal mengajar dapat diatur dengan baik

**Acceptance Criteria:**

✅ **Given** guru mengajukan izin untuk tanggal tertentu  
   **When** guru submit form izin dengan keterangan dan upload surat (optional)  
   **Then** sistem:
   - Save leave request dengan status "Pending"
   - Kirim notifikasi ke kepala sekolah/TU
   - Tampilkan success message ke guru

✅ **Given** kepala sekolah menerima notifikasi izin guru  
   **When** kepala sekolah buka halaman "Verifikasi Izin Guru"  
   **Then** sistem tampilkan detail:
   - Nama guru
   - Jenis (Izin/Sakit/Cuti)
   - Tanggal mulai - selesai
   - Keterangan
   - Surat pendukung (jika ada, preview)

✅ **Given** kepala sekolah approve izin  
   **When** kepala sekolah klik "Setujui"  
   **Then** sistem:
   - Update status jadi "Approved"
   - Auto-set presensi guru untuk tanggal tersebut jadi "Izin/Sakit"
   - Kirim notifikasi ke guru: "Izin Anda disetujui"

✅ **Given** kepala sekolah reject izin  
   **When** kepala sekolah klik "Tolak" dengan input alasan  
   **Then** sistem:
   - Update status jadi "Rejected"
   - Kirim notifikasi ke guru dengan alasan penolakan

**Technical Notes:**
- Izin harus diajukan minimal H-1 (kecuali emergency)
- Notifikasi via WhatsApp/email/in-app
- Integration dengan jadwal: suggest guru pengganti (future)

---

#### US-ATT-009: Rekap Presensi Guru (Harian & Bulanan)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah/TU  
**I want** melihat rekap presensi guru  
**So that** saya dapat monitoring kedisiplinan dan menghitung honor/gaji

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Rekap Presensi Guru"  
   **When** kepala sekolah set filter: Bulan Desember 2025  
   **Then** sistem tampilkan tabel rekap:
   - Columns: Nama Guru, Total Hadir, Terlambat, Izin, Sakit, Alpha, Persentase Kehadiran, Total Jam Kerja
   - Summary row: total semua guru

✅ **Given** guru A sering terlambat (> 3x dalam sebulan)  
   **When** sistem generate rekap  
   **Then** baris guru A di-highlight kuning dengan warning icon "Sering Terlambat"

✅ **Given** kepala sekolah ingin lihat detail presensi harian guru  
   **When** kepala sekolah klik nama guru  
   **Then** sistem tampilkan detail per hari:
   - Tanggal, Jam Masuk, Jam Keluar, Total Jam Kerja, Status, Lokasi (jika ada), Keterangan

✅ **Given** kepala sekolah ingin export untuk payroll  
   **When** kepala sekolah klik "Export untuk Payroll"  
   **Then** sistem generate Excel dengan columns:
   - Nama Guru, NIP, Total Hari Kerja, Total Hari Terlambat, Total Jam Kerja, Potongan (jika ada)

**Technical Notes:**
- Perhitungan otomatis untuk payroll
- Filter: per guru, per bulan, per tahun ajaran
- Access control: hanya TU & Principal
- Status Alpha: tidak ada clock in untuk hari kerja (weekday)

---

#### US-ATT-010: Notifikasi Otomatis Absensi (WhatsApp/Email)
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Orang Tua  
**I want** menerima notifikasi otomatis jika anak saya tidak hadir  
**So that** saya segera mengetahui jika anak tidak ke sekolah

**Acceptance Criteria:**

✅ **Given** guru input absensi pagi dan siswa A statusnya "Alpha"  
   **When** guru simpan absensi  
   **Then** sistem:
   - Queue notification untuk dikirim end of day (jam 15:00)
   - Kirim WhatsApp ke orang tua: "Anak Anda {nama} tidak hadir di sekolah hari ini tanpa keterangan. Status: Alpha. Jika ada kesalahan, silakan ajukan izin melalui portal."

✅ **Given** siswa B statusnya "Sakit" dengan izin yang sudah disetujui  
   **When** guru simpan absensi  
   **Then** sistem TIDAK kirim notifikasi alpha (karena sudah ada izin)

✅ **Given** orang tua set preferensi notifikasi "WhatsApp Only"  
   **When** sistem kirim notifikasi  
   **Then** notifikasi hanya dikirim via WhatsApp, tidak via email

✅ **Given** guru input absensi jam 08:00 WIB  
   **When** sistem process notification (jam 15:00)  
   **Then** orang tua menerima notifikasi WhatsApp

✅ **Given** guru kelas belum input absensi hingga jam 10:00  
   **When** sistem cek status input absensi  
   **Then** kirim reminder ke guru: "Jangan lupa input absensi harian untuk kelas {kelas} hari ini."

**Technical Notes:**
- Notifikasi alpha dikirim end of day (jam 15:00) untuk menghindari spam
- Notifikasi via WhatsApp (prioritas), fallback ke email jika gagal
- Template notifikasi dalam Bahasa Indonesia
- Orang tua dapat set preferensi notifikasi di settings
- Additional notifications:
  - Weekly summary ke TU (setiap Jumat): siswa dengan kehadiran < 80%
  - Monthly report ke Principal (setiap awal bulan): rata-rata kehadiran siswa & guru

---

#### US-ATT-011: Dashboard Absensi Real-Time
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah  
**I want** melihat dashboard absensi real-time semua kelas  
**So that** saya dapat monitoring kehadiran siswa & guru secara cepat

**Acceptance Criteria:**

✅ **Given** kepala sekolah login di pagi hari  
   **When** kepala sekolah buka dashboard  
   **Then** sistem tampilkan summary cards:
   - Total siswa hadir hari ini (number & percentage)
   - Total siswa alpha hari ini (highlight merah jika > 10)
   - Total guru hadir hari ini
   - Status input absensi per kelas (sudah/belum)

✅ **Given** ada kelas yang belum input absensi (jam sudah > 09:00)  
   **When** dashboard refresh  
   **Then** kelas tersebut ditandai merah dengan label "⚠️ Belum Input Absensi"

✅ **Given** kepala sekolah ingin lihat detail per kelas  
   **When** kepala sekolah klik salah satu kelas  
   **Then** sistem tampilkan list siswa yang alpha/izin/sakit hari ini dengan detail

✅ **Given** ada data baru (guru input absensi)  
   **When** sistem detect perubahan  
   **Then** dashboard auto-refresh dan update angka (websocket atau polling)

**Technical Notes:**
- Real-time update (websocket atau polling setiap 1-2 menit)
- Visual: card dengan icon & color coding
- Chart: trend kehadiran siswa & guru (7 hari terakhir)
- Mobile-responsive

---

## 🏗️ Technical Architecture

### Database Schema Requirements

#### Daily Attendance Table (Absensi Harian Siswa)
```sql
CREATE TABLE daily_attendance (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  date DATE NOT NULL,
  class_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  status ENUM('H', 'I', 'S', 'A') NOT NULL DEFAULT 'H',
  notes TEXT,
  leave_request_id BIGINT, -- link to leave request if approved
  
  -- Metadata
  input_by BIGINT NOT NULL, -- teacher who input
  input_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_by BIGINT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (input_by) REFERENCES users(id),
  FOREIGN KEY (leave_request_id) REFERENCES leave_requests(id),
  
  UNIQUE KEY unique_daily_attendance (date, class_id, student_id),
  INDEX idx_date (date),
  INDEX idx_class (class_id),
  INDEX idx_student (student_id),
  INDEX idx_status (status)
);
```

#### Subject Attendance Table (Absensi Per Mata Pelajaran)
```sql
CREATE TABLE subject_attendance (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  date DATE NOT NULL,
  class_id BIGINT NOT NULL,
  subject_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  lesson_period INT, -- jam pelajaran ke-
  lesson_time VARCHAR(20), -- contoh: "07:00-08:30"
  status ENUM('H', 'I', 'S', 'A') NOT NULL DEFAULT 'H',
  notes TEXT,
  
  -- Metadata
  input_by BIGINT NOT NULL,
  input_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (input_by) REFERENCES users(id),
  
  UNIQUE KEY unique_subject_attendance (date, class_id, subject_id, lesson_period, student_id),
  INDEX idx_date (date),
  INDEX idx_class (class_id),
  INDEX idx_subject (subject_id),
  INDEX idx_student (student_id)
);
```

#### Leave Requests Table (Pengajuan Izin)
```sql
CREATE TABLE leave_requests (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT NOT NULL,
  type ENUM('Izin', 'Sakit') NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason TEXT NOT NULL,
  document_url VARCHAR(255), -- foto surat
  
  -- Status & Approval
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  approved_by BIGINT,
  approved_at TIMESTAMP NULL,
  rejection_reason TEXT,
  
  -- Metadata
  submitted_by BIGINT NOT NULL, -- parent user_id
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (submitted_by) REFERENCES users(id),
  FOREIGN KEY (approved_by) REFERENCES users(id),
  
  INDEX idx_student (student_id),
  INDEX idx_status (status),
  INDEX idx_start_date (start_date),
  INDEX idx_submitted_at (submitted_at)
);
```

#### Teacher Attendance Table (Presensi Guru)
```sql
CREATE TABLE teacher_attendance (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  teacher_id BIGINT NOT NULL,
  date DATE NOT NULL,
  clock_in_time TIMESTAMP NULL,
  clock_out_time TIMESTAMP NULL,
  
  -- Location tracking (optional)
  clock_in_location VARCHAR(255), -- GPS coordinates
  clock_in_address TEXT, -- reverse geocoding address
  clock_out_location VARCHAR(255),
  
  -- Status
  status ENUM('Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha') NOT NULL,
  is_out_of_range BOOLEAN DEFAULT FALSE,
  total_work_hours DECIMAL(5,2), -- calculated: clock_out - clock_in
  notes TEXT,
  
  -- Leave reference
  leave_request_id BIGINT, -- if teacher on approved leave
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (leave_request_id) REFERENCES teacher_leave_requests(id),
  
  UNIQUE KEY unique_teacher_date (teacher_id, date),
  INDEX idx_date (date),
  INDEX idx_teacher (teacher_id),
  INDEX idx_status (status)
);
```

#### Teacher Leave Requests Table
```sql
CREATE TABLE teacher_leave_requests (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  teacher_id BIGINT NOT NULL,
  type ENUM('Izin', 'Sakit', 'Cuti') NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason TEXT NOT NULL,
  document_url VARCHAR(255),
  
  -- Status & Approval
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  approved_by BIGINT,
  approved_at TIMESTAMP NULL,
  rejection_reason TEXT,
  
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (approved_by) REFERENCES users(id),
  
  INDEX idx_teacher (teacher_id),
  INDEX idx_status (status),
  INDEX idx_start_date (start_date)
);
```

#### Attendance Notifications Queue Table
```sql
CREATE TABLE attendance_notifications_queue (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  type ENUM('alpha_alert', 'reminder', 'weekly_summary', 'monthly_report') NOT NULL,
  recipient_user_id BIGINT NOT NULL,
  recipient_phone VARCHAR(15),
  recipient_email VARCHAR(100),
  
  -- Content
  subject VARCHAR(255),
  message TEXT NOT NULL,
  
  -- Status
  status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
  delivery_method ENUM('whatsapp', 'email', 'in_app') NOT NULL,
  sent_at TIMESTAMP NULL,
  failed_reason TEXT,
  retry_count INT DEFAULT 0,
  
  -- Reference
  reference_type VARCHAR(50), -- 'daily_attendance', 'leave_request', etc
  reference_id BIGINT,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (recipient_user_id) REFERENCES users(id),
  
  INDEX idx_status (status),
  INDEX idx_created_at (created_at),
  INDEX idx_recipient (recipient_user_id)
);
```

---

### API Endpoints

#### Student Daily Attendance
- `GET /api/attendance/daily` - List daily attendance (with filters)
  - Query params: `class_id`, `date`, `student_id`
- `POST /api/attendance/daily` - Create daily attendance
- `GET /api/attendance/daily/:id` - Get attendance detail
- `PUT /api/attendance/daily/:id` - Update attendance
- `POST /api/attendance/daily/bulk` - Bulk create/update untuk 1 kelas
  - Body: `{ date, class_id, attendance: [{student_id, status, notes}] }`

#### Student Subject Attendance
- `GET /api/attendance/subject` - List subject attendance
- `POST /api/attendance/subject` - Create subject attendance
- `POST /api/attendance/subject/bulk` - Bulk create untuk 1 kelas 1 pelajaran

#### Leave Requests (Student)
- `GET /api/leave-requests/students` - List leave requests (with filters)
  - Query params: `student_id`, `status`, `start_date`, `end_date`
- `POST /api/leave-requests/students` - Submit leave request
- `GET /api/leave-requests/students/:id` - Get request detail
- `PUT /api/leave-requests/students/:id/approve` - Approve request
- `PUT /api/leave-requests/students/:id/reject` - Reject request
  - Body: `{ rejection_reason }`
- `GET /api/leave-requests/students/pending-count` - Get pending count for badge

#### Teacher Attendance
- `GET /api/attendance/teachers` - List teacher attendance
- `POST /api/attendance/teachers/clock-in` - Teacher clock in
  - Body: `{ latitude, longitude }` (optional)
- `PUT /api/attendance/teachers/clock-out` - Teacher clock out
- `GET /api/attendance/teachers/today` - Get today's attendance for logged-in teacher
- `PUT /api/attendance/teachers/:id` - TU manual edit teacher attendance

#### Teacher Leave Requests
- `GET /api/leave-requests/teachers` - List teacher leave requests
- `POST /api/leave-requests/teachers` - Submit teacher leave request
- `GET /api/leave-requests/teachers/:id` - Get request detail
- `PUT /api/leave-requests/teachers/:id/approve` - Approve request
- `PUT /api/leave-requests/teachers/:id/reject` - Reject request

#### Attendance Reports
- `GET /api/reports/attendance/student-summary` - Get student attendance summary
  - Query params: `class_id`, `student_id`, `start_date`, `end_date`, `type` (daily/subject)
- `GET /api/reports/attendance/class-summary` - Get class attendance summary
- `GET /api/reports/attendance/teacher-summary` - Get teacher attendance summary
- `GET /api/reports/attendance/export` - Export attendance to Excel/PDF
  - Query params: `type`, `format`, filters...

#### Dashboard
- `GET /api/dashboard/attendance/today` - Get today's attendance overview
- `GET /api/dashboard/attendance/realtime` - Real-time attendance dashboard data
- `GET /api/dashboard/attendance/trends` - Get attendance trends (7/30 days)

---

### Business Logic Implementation

#### Daily Attendance Input with Leave Request Check
```javascript
async function createDailyAttendance({ date, class_id, attendanceData, user_id }) {
  const transaction = await db.transaction();
  
  try {
    // Check if attendance already exists
    const existing = await DailyAttendance.findOne({
      where: { date, class_id },
      transaction
    });
    
    if (existing) {
      throw new Error('Absensi untuk kelas dan tanggal ini sudah ada. Silakan edit.');
    }
    
    // Get approved leave requests for this date
    const approvedLeaves = await LeaveRequest.findAll({
      where: {
        status: 'Approved',
        start_date: { lte: date },
        end_date: { gte: date }
      },
      transaction
    });
    
    const leaveMap = {};
    approvedLeaves.forEach(leave => {
      leaveMap[leave.student_id] = {
        type: leave.type,
        leave_request_id: leave.id
      };
    });
    
    // Process attendance data
    const attendanceRecords = attendanceData.map(data => {
      // Override status if student has approved leave
      if (leaveMap[data.student_id]) {
        const leave = leaveMap[data.student_id];
        return {
          date,
          class_id,
          student_id: data.student_id,
          status: leave.type === 'Sakit' ? 'S' : 'I',
          leave_request_id: leave.leave_request_id,
          input_by: user_id,
          notes: `Auto-set dari izin yang disetujui`
        };
      }
      
      return {
        date,
        class_id,
        student_id: data.student_id,
        status: data.status || 'H',
        notes: data.notes,
        input_by: user_id
      };
    });
    
    // Bulk insert
    await DailyAttendance.bulkCreate(attendanceRecords, { transaction });
    
    // Queue notifications for alpha students
    const alphaStudents = attendanceRecords.filter(r => r.status === 'A');
    if (alphaStudents.length > 0) {
      await queueAlphaNotifications(alphaStudents, date);
    }
    
    await transaction.commit();
    
    return {
      success: true,
      count: attendanceRecords.length,
      summary: calculateSummary(attendanceRecords)
    };
  } catch (error) {
    await transaction.rollback();
    throw error;
  }
}

function calculateSummary(records) {
  return {
    hadir: records.filter(r => r.status === 'H').length,
    izin: records.filter(r => r.status === 'I').length,
    sakit: records.filter(r => r.status === 'S').length,
    alpha: records.filter(r => r.status === 'A').length
  };
}
```

#### Leave Request Approval with Auto-Update Attendance
```javascript
async function approveLeaveRequest(requestId, userId) {
  const transaction = await db.transaction();
  
  try {
    const leaveRequest = await LeaveRequest.findByPk(requestId, { transaction });
    
    if (!leaveRequest) {
      throw new Error('Leave request tidak ditemukan');
    }
    
    if (leaveRequest.status !== 'Pending') {
      throw new Error('Leave request sudah diproses');
    }
    
    // Update leave request status
    await leaveRequest.update({
      status: 'Approved',
      approved_by: userId,
      approved_at: new Date()
    }, { transaction });
    
    // Auto-update attendance for the date range
    const dates = getDateRange(leaveRequest.start_date, leaveRequest.end_date);
    const status = leaveRequest.type === 'Sakit' ? 'S' : 'I';
    
    for (const date of dates) {
      // Check if attendance exists
      const attendance = await DailyAttendance.findOne({
        where: {
          date,
          student_id: leaveRequest.student_id
        },
        transaction
      });
      
      if (attendance) {
        // Update existing
        await attendance.update({
          status,
          leave_request_id: requestId,
          notes: 'Auto-update dari izin yang disetujui',
          updated_by: userId
        }, { transaction });
      } else {
        // Create new (if attendance not yet input)
        await DailyAttendance.create({
          date,
          student_id: leaveRequest.student_id,
          class_id: leaveRequest.student.current_class_id,
          status,
          leave_request_id: requestId,
          notes: 'Auto-create dari izin yang disetujui',
          input_by: userId
        }, { transaction });
      }
    }
    
    // Send notification to parent
    await sendNotification({
      user_id: leaveRequest.submitted_by,
      type: 'leave_approved',
      message: `Pengajuan izin untuk ${leaveRequest.student.full_name} telah disetujui.`
    });
    
    await transaction.commit();
    return { success: true };
  } catch (error) {
    await transaction.rollback();
    throw error;
  }
}

function getDateRange(startDate, endDate) {
  const dates = [];
  let currentDate = new Date(startDate);
  const end = new Date(endDate);
  
  while (currentDate <= end) {
    // Skip weekends
    const dayOfWeek = currentDate.getDay();
    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
      dates.push(new Date(currentDate));
    }
    currentDate.setDate(currentDate.getDate() + 1);
  }
  
  return dates;
}
```

#### Teacher Clock In with Location Tracking
```javascript
async function teacherClockIn({ teacher_id, latitude, longitude }) {
  const today = new Date().toISOString().split('T')[0];
  
  // Check if already clocked in today
  const existing = await TeacherAttendance.findOne({
    where: { teacher_id, date: today }
  });
  
  if (existing && existing.clock_in_time) {
    throw new Error(`Anda sudah clock in hari ini pada ${existing.clock_in_time}`);
  }
  
  const clockInTime = new Date();
  let status = 'Hadir';
  let isOutOfRange = false;
  let address = null;
  
  // Check if late (after 07:30)
  const hour = clockInTime.getHours();
  const minute = clockInTime.getMinutes();
  if (hour > 7 || (hour === 7 && minute >= 30)) {
    status = 'Terlambat';
  }
  
  // Location tracking (optional)
  if (latitude && longitude) {
    const schoolLocation = await getSchoolLocation(); // from settings
    const distance = calculateDistance(
      { lat: latitude, lon: longitude },
      schoolLocation
    );
    
    if (distance > 100) { // > 100 meters
      isOutOfRange = true;
    }
    
    // Reverse geocoding to get address
    address = await reverseGeocode(latitude, longitude);
  }
  
  // Create or update attendance
  const attendance = await TeacherAttendance.upsert({
    teacher_id,
    date: today,
    clock_in_time: clockInTime,
    clock_in_location: latitude && longitude ? `${latitude},${longitude}` : null,
    clock_in_address: address,
    status,
    is_out_of_range: isOutOfRange
  });
  
  return {
    success: true,
    clock_in_time: clockInTime,
    status,
    is_out_of_range: isOutOfRange,
    address
  };
}

function calculateDistance(point1, point2) {
  // Haversine formula to calculate distance between two GPS points
  const R = 6371e3; // Earth radius in meters
  const φ1 = point1.lat * Math.PI / 180;
  const φ2 = point2.lat * Math.PI / 180;
  const Δφ = (point2.lat - point1.lat) * Math.PI / 180;
  const Δλ = (point2.lon - point1.lon) * Math.PI / 180;
  
  const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ/2) * Math.sin(Δλ/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  
  return R * c; // Distance in meters
}
```

#### Attendance Report with Percentage Calculation
```javascript
async function getStudentAttendanceSummary({
  student_id,
  class_id,
  start_date,
  end_date,
  type = 'daily'
}) {
  const table = type === 'daily' ? DailyAttendance : SubjectAttendance;
  
  // Get attendance records
  const records = await table.findAll({
    where: {
      ...(student_id && { student_id }),
      ...(class_id && { class_id }),
      date: {
        gte: start_date,
        lte: end_date
      }
    },
    order: [['date', 'ASC']]
  });
  
  // Calculate total school days (exclude weekends & holidays)
  const totalSchoolDays = await calculateSchoolDays(start_date, end_date);
  
  // Calculate summary
  const summary = {
    hadir: records.filter(r => r.status === 'H').length,
    izin: records.filter(r => r.status === 'I').length,
    sakit: records.filter(r => r.status === 'S').length,
    alpha: records.filter(r => r.status === 'A').length,
    total_school_days: totalSchoolDays
  };
  
  // Calculate percentage (Hadir + Izin + Sakit) / Total School Days
  summary.percentage = totalSchoolDays > 0
    ? ((summary.hadir + summary.izin + summary.sakit) / totalSchoolDays * 100).toFixed(2)
    : 0;
  
  // Warning flag
  summary.has_warning = summary.percentage < 80 || summary.alpha > 3;
  
  return {
    summary,
    records,
    chart_data: generateChartData(summary)
  };
}

async function calculateSchoolDays(startDate, endDate) {
  let count = 0;
  let currentDate = new Date(startDate);
  const end = new Date(endDate);
  
  // Get holidays from database
  const holidays = await getHolidays(startDate, endDate);
  const holidayDates = holidays.map(h => h.date.toISOString().split('T')[0]);
  
  while (currentDate <= end) {
    const dayOfWeek = currentDate.getDay();
    const dateStr = currentDate.toISOString().split('T')[0];
    
    // Count if not weekend and not holiday
    if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidayDates.includes(dateStr)) {
      count++;
    }
    
    currentDate.setDate(currentDate.getDate() + 1);
  }
  
  return count;
}
```

---

## 🎨 UI/UX Design Requirements

### Daily Attendance Input Page (Guru)

**Layout:**
- **Header:**
  - Title: "Absensi Harian"
  - Date picker (default: hari ini)
  - Dropdown: Pilih Kelas (jika guru mengajar multiple kelas)
  - Button "Tandai Semua Hadir" (quick action)

- **Student List:**
  - Table atau card-based (mobile)
  - Per row/card:
    - Foto siswa (40x40px, rounded)
    - Nama lengkap (bold)
    - NIS (gray, small)
    - Radio buttons: H | I | S | A (color-coded)
    - Badge "Ada Izin" (jika ada pending leave request)

- **Footer:**
  - Live count: "Hadir: X, Izin: Y, Sakit: Z, Alpha: W"
  - Button "Simpan Absensi" (primary, full-width di mobile)
  - Button "Batal"

**UX:**
- Default semua siswa: Hadir (H selected)
- Radio button besar dan touch-friendly (min 48px tap target di mobile)
- Color coding:
  - H (Hadir) = hijau
  - I (Izin) = kuning
  - S (Sakit) = biru
  - A (Alpha) = merah
- Badge "Ada Izin Pending" di siswa yang punya pending leave request (tooltip: detail izin)
- Auto-save draft setiap 30 detik ke local storage (prevent data loss)
- Success notification dengan confetti animation dan summary
- Loading skeleton saat load data
- Search/filter siswa (untuk kelas besar > 30 siswa)

**Mobile Optimization:**
- Card-based layout (lebih touch-friendly)
- Swipe left/right untuk ubah status (advanced UX, optional)
- Sticky header dengan date, class selector, dan count
- Bottom fixed button untuk Save

---

### Leave Request Form (Orang Tua)

**Layout:**
- Title: "Pengajuan Izin/Sakit"
- Form fields:
  - Pilih Anak (dropdown dengan foto, jika multiple children)
  - Jenis (radio button dengan icon):
    - 📋 Izin
    - 🏥 Sakit
  - Tanggal Mulai (date picker)
  - Tanggal Selesai (date picker, dengan helper text "Kosongkan jika 1 hari saja")
  - Alasan (textarea, 3 rows, character counter)
  - Upload Surat (drag & drop area atau click to upload)
    - Preview image/PDF setelah upload
    - Button "Hapus" untuk cancel upload
- Buttons:
  - "Batal" (secondary)
  - "Kirim Pengajuan" (primary)

**UX:**
- Date picker dengan highlight:
  - Weekend = gray (disabled)
  - Holiday = gray (disabled)
  - Past date > 7 hari = disabled
  - Future date > 30 hari = disabled
- Character counter untuk alasan: "10/200 karakter" (min 10)
- Image preview dengan zoom (click untuk enlarge)
- Konfirmasi sebelum submit: "Yakin ingin mengirim pengajuan izin untuk {anak} tanggal {date}?"
- Success message dengan animation: "✅ Pengajuan berhasil dikirim! Anda akan menerima notifikasi setelah diverifikasi oleh wali kelas."
- Redirect ke halaman "Riwayat Izin" setelah success

**Mobile:**
- Native date picker
- Camera option untuk upload (take photo langsung)
- Bottom sheet untuk konfirmasi
- Haptic feedback saat submit

---

### Leave Verification Page (Guru/TU)

**Layout:**
- Header:
  - Title: "Verifikasi Izin/Sakit"
  - Badge count: "X pending" (red badge)
  - Tab navigation: Pending | Disetujui | Ditolak

- **Tab: Pending**
  - Card list (per request):
    - Left: Foto siswa (60x60px)
    - Center:
      - Nama siswa (bold) + Kelas (badge)
      - Jenis icon + text: "🏥 Sakit" atau "📋 Izin"
      - Tanggal: "13-15 Des 2025 (3 hari)"
      - Alasan: first 100 characters... (click "Lihat Selengkapnya")
      - Surat: thumbnail foto (click untuk preview)
    - Right:
      - Button "✓ Setujui" (green)
      - Button "✗ Tolak" (red)

- **Tab: Disetujui/Ditolak**
  - Same card layout
  - Show: approved/rejected by, timestamp
  - No action buttons

**UX:**
- Click thumbnail foto → open modal lightbox dengan zoom & download
- Click "Tolak" → modal input alasan penolakan (required)
- After approve/reject → card animate out (slide & fade)
- Empty state (no pending): illustration + text "Tidak ada pengajuan pending"
- Filter: kelas, tanggal submit, jenis (izin/sakit)
- Pull to refresh (mobile)

**Interactions:**
- Swipe right = approve (dengan konfirmasi)
- Swipe left = reject (dengan modal alasan)
- Batch actions: checkbox + bulk approve (advanced, optional)

---

### Attendance Report Page

**Layout:**
- **Header:**
  - Title: "Rekap Absensi Siswa"
  - Filters (horizontal row, collapsible di mobile):
    - Dropdown: Kelas (all/specific)
    - Dropdown: Siswa (all/specific, searchable)
    - Date range picker: Tanggal
    - Dropdown: Jenis (Harian/Per Mapel/Semua)
  - Buttons:
    - "Export Excel" (icon 📊)
    - "Export PDF" (icon 📄)

- **Content Area:**
  - Tab navigation: Tabel | Grafik

  - **Tab: Tabel**
    - Summary cards (top):
      - Total Hadir (green)
      - Total Izin (yellow)
      - Total Sakit (blue)
      - Total Alpha (red)
      - Persentase Kehadiran (dengan progress bar)
    
    - Table:
      - Columns: Nama Siswa | Hadir | Izin | Sakit | Alpha | Persentase | Status
      - Sorted by: Persentase (ascending, siswa bermasalah di atas)
      - Row dengan persentase < 80% = highlight merah + warning icon
      - Click row → expand detail (calendar view per hari)
    
    - Pagination (jika data banyak)

  - **Tab: Grafik**
    - Pie chart: proporsi H/I/S/A (dengan legend & percentage)
    - Bar chart: trend kehadiran per minggu (line chart untuk 30 hari)
    - Heatmap calendar (optional): color-coded per day

**UX:**
- Real-time chart update saat ubah filter
- Persentase dengan color coding:
  - >= 90% = hijau (excellent)
  - 80-89% = kuning (good)
  - < 80% = merah (warning, risiko tidak naik kelas)
- Warning banner jika ada siswa < 80%:
  "⚠️ Ada {X} siswa dengan kehadiran di bawah 80%. Klik untuk lihat detail."
- Chart responsive (resize untuk mobile)
- Export loading state (progress bar)
- Empty state jika no data

**Mobile:**
- Filters collapse jadi accordion
- Tab swipe navigation
- Summary cards stack vertically
- Table scroll horizontal atau transform jadi card

---

### Teacher Clock In/Out Widget (Dashboard)

**Layout:**
- Card widget di dashboard guru
- **Header:** "Presensi Hari Ini"
- **Content:**
  - Icon jam besar (center)
  - Tanggal & hari (text-center)
  - Status:
    - **Belum Clock In:**
      - Text: "Anda belum presensi hari ini"
      - Button "Clock In" (large, primary, full-width)
    - **Sudah Clock In:**
      - Text: "Clock In pada {jam}"
      - Lokasi (jika ada): "📍 {alamat}"
      - Button "Clock Out" (large, secondary, full-width)
    - **Sudah Clock Out:**
      - Text: "Clock In: {jam} | Clock Out: {jam}"
      - Total jam kerja: "{X} jam {Y} menit" (large, bold)
      - Icon ✅ "Presensi lengkap hari ini"

- **Footer:**
  - Daily summary: "Bulan ini: Hadir {X} hari, Terlambat {Y} kali"

**UX:**
- Button besar dan prominent (min 56px height)
- Icon: 🕐 untuk Clock In, 🏠 untuk Clock Out
- Loading indicator saat capture GPS (spinner di button)
- Success animation setelah clock in/out:
  - Confetti animation
  - Haptic feedback (vibrate)
  - Success sound (optional)
- Warning jika terlambat: "⚠️ Anda terlambat hari ini. Jam masuk ideal: 07:00-07:30"
- Warning jika out of range: "⚠️ Anda berada di luar area sekolah"
- Permission request untuk GPS (friendly message)

**Mobile:**
- Full-width card
- Touch-friendly buttons (min 48px)
- Native GPS permission prompt
- Offline indicator jika no connection (data akan sync saat online)

---

### Dashboard Real-Time (Kepala Sekolah)

**Layout:**
- **Header:** "Dashboard Absensi - Hari Ini"
- **Summary Cards (4 columns, stack di mobile):**
  1. Total Siswa Hadir
     - Big number + percentage
     - Icon: 👥
     - Trend indicator (↑↓ vs kemarin)
  2. Total Siswa Alpha
     - Big number (highlight merah jika > 10)
     - Icon: ⚠️
  3. Total Guru Hadir
     - Big number + percentage
     - Icon: 👨‍🏫
  4. Status Input Absensi
     - "{X} dari {Y} kelas sudah input"
     - Progress bar

- **Class Attendance Status (Grid/List):**
  - Per kelas card:
    - Kelas name (bold)
    - Wali kelas
    - Status:
      - ✅ "Sudah Input Absensi" (green) + jam input
      - ⚠️ "Belum Input Absensi" (red) jika > 09:00
      - ⏳ "Menunggu" (gray) jika < 09:00
    - Quick stats: H: X, I: Y, S: Z, A: W
    - Click → expand detail (list siswa yang tidak hadir)

- **Charts:**
  - Line chart: Trend kehadiran siswa 7 hari terakhir
  - Bar chart: Kehadiran per kelas hari ini

- **Recent Alerts:**
  - List notifikasi/alert:
    - "Kelas 3A belum input absensi (09:30)"
    - "5 siswa alpha hari ini"
    - "Guru X belum clock in"

**UX:**
- Auto-refresh every 1-2 menit (websocket atau polling)
- Loading skeleton saat fetch data
- Pull to refresh (mobile)
- Click card → drill-down ke detail
- Color coding consistent (green/yellow/red)
- Responsive grid (4 cols → 2 cols → 1 col)

---

## ✅ Definition of Done

### Code Level
- [ ] Unit test coverage minimal 80%
- [ ] Integration test untuk critical flow (input absensi, approve leave, clock in/out)
- [ ] Code review approved
- [ ] No critical linter errors
- [ ] All API endpoints documented (Swagger)

### Functionality
- [ ] All acceptance criteria met dan tested
- [ ] Guru dapat input absensi harian < 2 menit untuk 1 kelas
- [ ] Orang tua dapat submit leave request dengan upload dokumen
- [ ] Guru/TU dapat verify leave request dan auto-update absensi
- [ ] Attendance report accurate dengan correct percentage calculation
- [ ] Teacher clock in/out working dengan GPS tracking (optional)
- [ ] WhatsApp notification delivery > 95% success rate
- [ ] Dashboard real-time update working

### UI/UX
- [ ] Responsive di mobile dan desktop (tested di iOS & Android)
- [ ] Loading state untuk semua async actions
- [ ] Error handling dengan user-friendly message (Bahasa Indonesia)
- [ ] Success feedback (toast/animation)
- [ ] Form validation real-time dengan clear error messages
- [ ] Accessibility: keyboard navigation, screen reader support
- [ ] Mobile-optimized (touch-friendly, camera upload)

### Data Integrity
- [ ] No duplicate attendance records untuk same date & class
- [ ] Leave request approval correctly updates attendance
- [ ] Percentage calculation accurate (exclude weekends/holidays)
- [ ] GPS location tracking accurate (< 50m error)
- [ ] Notification queue processing reliable (no message loss)

### Performance
- [ ] Attendance input load time < 2 detik untuk 1 kelas
- [ ] Report generation < 5 detik untuk 1 bulan data
- [ ] Dashboard real-time refresh < 1 detik
- [ ] WhatsApp notification delivery < 5 menit after input
- [ ] Database queries optimized (use indexes)
- [ ] No N+1 query problem

### Documentation
- [ ] API documentation complete
- [ ] Database schema documented
- [ ] User manual untuk Guru (input absensi)
- [ ] User manual untuk Orang Tua (submit izin)
- [ ] Technical documentation untuk developer

---

## 🔗 Dependencies

### External Dependencies
- **WhatsApp API:** Fonnte/Wablas untuk notifikasi ke orang tua
- **Geolocation API:** Browser Geolocation API untuk GPS tracking
- **Reverse Geocoding API:** Google Maps API atau OpenStreetMap untuk alamat
- **Chart Library:** Chart.js atau Recharts untuk visualisasi
- **Date Library:** date-fns atau moment.js untuk date manipulation
- **File Storage:** Cloud storage (AWS S3/Google Cloud Storage) untuk foto surat

### Internal Dependencies
- **EPIC 1 (MUST COMPLETE FIRST):**
  - Authentication & Authorization (untuk RBAC)
  - Master Data Kelas (untuk assign absensi per kelas)
  - Master Data Mata Pelajaran (untuk absensi per mapel)
  - Master Data Tahun Ajaran (untuk filter laporan)

- **EPIC 2 (MUST COMPLETE FIRST):**
  - Student Management (untuk fetch student list)
  - Parent Account (untuk link leave request ke parent)
  - Student-Parent relationship (untuk notifikasi)

### Blocking For
EPIC 3 harus selesai sebelum epic berikut dapat optimal:
- **EPIC 5 (Grades):** data kehadiran digunakan untuk eligibility naik kelas
- **EPIC 6 (Report Card):** attendance summary included in rapor
- **EPIC 7 (Teacher Management):** attendance data untuk payroll calculation

---

## 🧪 Testing Strategy

### Unit Testing
- Service layer:
  - Attendance input logic dengan leave request check
  - Percentage calculation logic
  - Date range calculation (school days)
  - GPS distance calculation
  - Notification queue processing
- Utility functions:
  - Date manipulation (getDateRange, calculateSchoolDays)
  - Status determination (Hadir/Terlambat)
  - Chart data generation
- Target coverage: 80%

### Integration Testing
- **Attendance CRUD:**
  - Create daily attendance → success
  - Create duplicate attendance → error
  - Update attendance → success, audit log created
  - Bulk create attendance → all students saved

- **Leave Request Flow:**
  - Parent submit leave → success, notification sent
  - Guru approve leave → attendance auto-updated
  - Guru reject leave → parent notified

- **Teacher Clock In/Out:**
  - Clock in → success, status calculated
  - Clock in late → status "Terlambat"
  - Clock out → total hours calculated
  - Clock out without clock in → error

- **Notifications:**
  - Alpha student → notification queued
  - Notification delivered via WhatsApp → success
  - Notification failed → retry mechanism triggered

### E2E Testing (Critical Paths)
1. **Happy Path - Daily Attendance:**
   - Guru login → Input Absensi → Pilih kelas → Ubah status 2 siswa → Simpan → Success notification

2. **Leave Request Flow:**
   - Parent login → Submit izin → Guru notified → Guru approve → Attendance auto-updated → Parent notified

3. **Teacher Clock In Late:**
   - Guru login jam 08:00 → Clock In → Status "Terlambat" + warning message → Clock Out → Total hours calculated

4. **Attendance Report:**
   - TU login → Rekap Absensi → Filter kelas 3A bulan Des → View report → Export Excel → File downloaded

5. **Real-Time Dashboard:**
   - Principal login → Dashboard → View summary → Click kelas → View detail siswa alpha → Refresh → Data updated

### Performance Testing
- Load test: 50 guru input absensi simultaneously
- Stress test: Generate report untuk 1000 siswa 1 tahun
- Notification queue: Process 500 notifications concurrently
- Real-time dashboard: 20 concurrent users dengan auto-refresh
- Target: 95th percentile response time < 2 detik

### Security Testing
- RBAC: Guru coba akses attendance kelas lain → blocked
- RBAC: Parent coba lihat attendance anak orang lain → blocked
- SQL injection test pada search/filter
- File upload security: upload script file sebagai surat → blocked
- GPS spoofing detection (optional advanced)

---

## 📅 Sprint Planning

### Sprint 5 (2 minggu) - 17 points
**Focus:** Student Attendance Core & Leave Request

**Stories:**
- US-ATT-001: Input Absensi Harian Pagi (3 pts) - **Day 1-3**
- US-ATT-002: Input Absensi Per Mata Pelajaran (3 pts) - **Day 4-6**
- US-ATT-003: Submit Leave Request (3 pts) - **Day 7-8**
- US-ATT-004: Verify Leave Request (2 pts) - **Day 9**
- US-ATT-005: Rekap Absensi Siswa (3 pts) - **Day 10-11**
- US-ATT-006: Portal Orang Tua Absensi (2 pts) - **Day 12**

**Deliverables:**
- Guru dapat input absensi harian dan per mapel
- Orang tua dapat submit dan track leave request
- Guru/TU dapat verify leave request dengan auto-update attendance
- Attendance report working dengan chart & export

**Sprint Goal:** "Guru dapat input absensi dan orang tua dapat submit izin dengan workflow approval"

---

### Sprint 6 (2 minggu) - 13 points
**Focus:** Teacher Attendance, Notifications, Dashboard

**Stories:**
- US-ATT-007: Teacher Clock In/Out (3 pts) - **Day 1-3**
- US-ATT-008: Teacher Leave Approval (3 pts) - **Day 4-5**
- US-ATT-009: Rekap Presensi Guru (3 pts) - **Day 6-7**
- US-ATT-010: Notifikasi Otomatis (3 pts) - **Day 8-10**
- US-ATT-011: Dashboard Real-Time (3 pts) - **Day 11-12**

**Deliverables:**
- Teacher clock in/out working dengan GPS tracking
- Teacher leave management dengan approval workflow
- Attendance report untuk guru (payroll-ready)
- WhatsApp notification automated untuk alpha students
- Real-time dashboard untuk monitoring

**Sprint Goal:** "Sistem absensi complete dengan teacher attendance, notifications, dan real-time monitoring"

---

## 🎯 Acceptance Criteria (Epic Level)

### Functional
- [ ] Guru dapat input absensi harian < 2 menit untuk 1 kelas (30 siswa)
- [ ] Orang tua dapat submit leave request dengan upload foto surat
- [ ] Guru/TU dapat verify leave request dan attendance auto-updated
- [ ] Attendance report accurate dengan correct percentage calculation
- [ ] Parent dapat view child attendance di portal (real-time)
- [ ] Teacher dapat clock in/out dengan GPS tracking (optional)
- [ ] Teacher leave request approval workflow working
- [ ] WhatsApp notification automated untuk alpha students
- [ ] Dashboard real-time update untuk Principal monitoring
- [ ] Export attendance report to Excel/PDF working
- [ ] Weekend & holiday auto-excluded dari calculation

### Non-Functional
- [ ] Attendance input time < 2 menit untuk 1 kelas
- [ ] Report generation < 5 detik untuk 1 bulan data
- [ ] WhatsApp notification delivery > 95% dalam 5 menit
- [ ] Dashboard refresh time < 1 detik
- [ ] Mobile-responsive (tested di iOS & Android)
- [ ] GPS accuracy < 50m error margin
- [ ] User-friendly error messages dalam Bahasa Indonesia

### Technical
- [ ] API documentation complete (Swagger)
- [ ] Database schema implemented dengan proper indexes
- [ ] Unit test coverage 80%
- [ ] Integration test untuk critical flows
- [ ] RBAC implemented dan tested
- [ ] File upload security (validate file type & size)
- [ ] WhatsApp API integration working
- [ ] Notification queue with retry mechanism
- [ ] No duplicate attendance records (unique constraints)

---

## 🚧 Risks & Mitigation

### Risk 1: WhatsApp API Delivery Failures
**Impact:** High - Parent tidak terima notifikasi alpha  
**Probability:** Medium  
**Mitigation:**
- Gunakan reliable WhatsApp gateway (Fonnte/Wablas dengan SLA)
- Implement retry mechanism (max 3 attempts dengan exponential backoff)
- Fallback ke email jika WhatsApp gagal after retries
- Log semua notifikasi sent/failed untuk monitoring
- Admin dashboard untuk resend failed notifications manual
- Daily monitoring & alert jika failure rate > 10%

### Risk 2: GPS Spoofing (Teacher Clock In)
**Impact:** Medium - Guru bisa fake location  
**Probability:** Low  
**Mitigation:**
- GPS tracking optional (bisa disabled per config)
- Multiple verification: GPS + IP address + device fingerprint
- Flag "Out of Range" untuk TU review manual
- Random spot-check verification
- Education & trust: most teachers won't cheat
- Phase 2: biometric (fingerprint/face recognition)

### Risk 3: Concurrent Attendance Input Conflicts
**Impact:** Medium - Data inconsistency jika 2 guru input simultaneously  
**Probability:** Low  
**Mitigation:**
- Database unique constraint (date + class_id + student_id)
- Transaction-based insert dengan proper locking
- UI: show warning "Kelas ini sedang diinput oleh {guru_name}"
- Optimistic locking: check updated_at before save
- Error message clear: "Absensi sudah diinput oleh guru lain. Silakan refresh."

### Risk 4: Large File Upload (Surat Dokter)
**Impact:** Medium - Slow upload, storage cost  
**Probability:** Medium  
**Mitigation:**
- Client-side compression before upload (reduce size to max 1MB)
- Max file size validation (5MB)
- Use cloud storage (S3/GCS) dengan CDN untuk faster access
- Image optimization: convert to WebP, resize to max 1024px
- Cleanup old files: auto-delete setelah 1 tahun
- Monitor storage usage & cost

### Risk 5: School Days Calculation Accuracy
**Impact:** High - Wrong percentage calculation → wrong decisions  
**Probability:** Medium  
**Mitigation:**
- Accurate holiday calendar database (manual input by TU per year)
- Weekend auto-excluded (configurable for sekolah with Saturday class)
- Validation: cross-check with Kalender Pendidikan from Disdik
- TU can manual adjust total school days per month (override)
- Display total school days prominently di report untuk transparency
- Unit test extensively untuk date calculation logic

### Risk 6: Teacher Adoption Rate for Clock In/Out
**Impact:** High - Jika adoption < 50%, data tidak reliable  
**Probability:** Medium  
**Mitigation:**
- Simple & fast UX (< 10 seconds untuk clock in)
- Prominent widget di dashboard (can't miss)
- Daily reminder notification di pagi hari
- Gamification: badge/reward untuk perfect attendance
- Management support: Kepala Sekolah emphasize importance
- Training session untuk semua guru
- Phase 1: voluntary with incentive, Phase 2: mandatory

---

## 📊 Success Metrics & KPIs

### Sprint 5
- [ ] 100% user stories completed (6/6)
- [ ] Zero critical bugs in production
- [ ] Attendance input time < 2 menit (tested with real guru)
- [ ] Leave request approval working 100%
- [ ] Attendance report accurate (cross-check manual calculation)

### Sprint 6
- [ ] 100% user stories completed (5/5)
- [ ] Teacher adoption rate > 70% for clock in/out (week 1)
- [ ] WhatsApp notification delivery > 95%
- [ ] Dashboard load time < 2 detik
- [ ] Zero attendance data loss atau duplicate

### Epic Level
- [ ] Total 30 points delivered
- [ ] Code coverage 80%
- [ ] Zero data integrity issues
- [ ] User satisfaction > 4.5/5 (dari UAT dengan Guru & Parent)
- [ ] Teacher adoption rate > 90% (after 1 month)
- [ ] Parent engagement rate > 80% (check attendance report in portal)

---

## 📝 Notes & Assumptions

### Assumptions
1. WhatsApp API account sudah disiapkan dan properly configured
2. Storage untuk foto surat tersedia (min 5GB)
3. Guru memiliki smartphone untuk input absensi mobile
4. Internet connection stable di sekolah (untuk real-time sync)
5. Orang tua memiliki WhatsApp untuk notifikasi
6. EPIC 1 (Authentication) & EPIC 2 (Student Management) sudah 100% selesai
7. School holiday calendar diinput manual oleh TU setiap tahun
8. Weekend = Saturday & Sunday (jika sekolah ada kelas Sabtu, configurable)

### Out of Scope (Epic 3)
- ❌ QR Code attendance (scan QR untuk clock in) - Phase 2
- ❌ Face recognition untuk presensi - Phase 2
- ❌ Integration dengan biometric device (fingerprint reader) - Phase 2
- ❌ Real-time GPS tracking guru - Phase 2
- ❌ Geofencing (auto clock in saat masuk area sekolah) - Phase 2
- ❌ Offline mode untuk input absensi - Phase 2 (US-ATT-012)
- ❌ AI anomaly detection (deteksi pola absensi mencurigakan) - Phase 2
- ❌ Live attendance dashboard dengan websocket - Phase 2
- ❌ Parent excuse letter template generator - Phase 2
- ❌ Auto clock out jika lupa - Phase 2

### Nice to Have (Not Required for MVP)
- SMS notification (selain WhatsApp & email)
- Photo capture saat clock in (selfie verification)
- Integration dengan smart card/RFID attendance device
- Bulk attendance input via Excel upload
- Predictive alert (siswa yang sering alpha, predict risk)
- Advanced analytics: correlation between attendance & grades
- Parent reply capability via WhatsApp (two-way chat)

---

## 🔄 Review & Refinement

### Sprint 5 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner, Sample Guru & TU

**Review Checklist:**
- [ ] Demo attendance input flow (daily & subject)
- [ ] Demo leave request submission & approval
- [ ] Demo attendance report dengan chart
- [ ] Get feedback dari guru tentang input speed & UX
- [ ] Test notification delivery dengan real WhatsApp
- [ ] Identify improvement areas
- [ ] Adjust Sprint 6 if needed

### Sprint 6 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner, Guru, TU, Principal, Sample Parents

**Review Checklist:**
- [ ] Demo complete Epic 3 functionality
- [ ] Demo teacher clock in/out dengan GPS
- [ ] Demo dashboard real-time ke Principal
- [ ] User acceptance testing (UAT) dengan actual users
- [ ] Test WhatsApp notification delivery rate
- [ ] Performance review (input speed, report generation)
- [ ] Security review (RBAC, file upload)
- [ ] Documentation complete check

---

## ✅ Epic Checklist (Before Moving to Epic 4)

### Development
- [ ] All 11 user stories implemented dan tested
- [ ] Code merged to main branch
- [ ] Database migration successful
- [ ] API documentation published
- [ ] Sample data (seed) untuk testing tersedia

### Testing
- [ ] Unit test pass (coverage 80%)
- [ ] Integration test pass
- [ ] E2E test pass untuk critical paths
- [ ] Security test pass (no critical issues)
- [ ] Performance test pass (< 2s input time, < 5s report)
- [ ] UAT approved by Guru, TU, Principal, & sample Parents

### Data Integrity
- [ ] No duplicate attendance records
- [ ] Percentage calculation accurate (cross-checked)
- [ ] Leave approval correctly updates attendance
- [ ] GPS location tracking accurate (tested)
- [ ] Notification queue processing reliable

### Deployment
- [ ] Deployed to staging environment
- [ ] Holiday calendar seeded untuk current year
- [ ] WhatsApp API configured dan tested
- [ ] Cloud storage configured untuk foto surat
- [ ] Deployed to production
- [ ] Monitoring & logging active
- [ ] Notification queue worker running

### Documentation
- [ ] Technical documentation complete
- [ ] User manual untuk Guru (input absensi) ready
- [ ] User manual untuk Orang Tua (submit izin) ready
- [ ] User manual untuk TU (verify izin, manual correction) ready
- [ ] API documentation complete
- [ ] Database schema documented
- [ ] FAQ untuk common issues

### Handover
- [ ] Demo to all guru (training session)
- [ ] Demo to sample parents (parent portal)
- [ ] Demo to TU & Principal (dashboard & reports)
- [ ] Training session completed
- [ ] Training video available (optional)
- [ ] Support contact established
- [ ] Feedback channel setup
- [ ] Monitor adoption rate & engagement

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

**For User Support (Guru & Orang Tua):**
- WhatsApp Support: [Number]
- Email: support@sekolah.app

---

**Document Status:** ✅ Ready for Sprint Planning  
**Last Review:** 13 Desember 2025  
**Next Review:** After Sprint 5 completion

---

*Catatan: Dokumen ini adalah living document dan akan diupdate seiring progress sprint. Semua perubahan akan dicatat di section "Change Log" di bawah.*

## 📋 Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 13 Des 2025 | 1.0 | Initial creation of EPIC 3 document | Zulfikar Hidayatullah |
