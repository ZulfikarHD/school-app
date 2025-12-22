# EPIC 2: Student Management (Manajemen Siswa)

**Project:** SD Management System  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Epic Owner:** Development Team  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Epic Overview

### Goal
Sistem dapat mengelola data siswa secara lengkap mulai dari pendaftaran, profil, data orang tua, hingga tracking status siswa (aktif, mutasi, lulus, DO), serta menyediakan portal untuk orang tua melihat informasi anak mereka.

### Business Value
- **Centralisasi Data:** Semua data siswa tersimpan dalam satu sistem yang terorganisir
- **Efisiensi TU:** Memudahkan staff TU dalam mengelola data siswa dengan fitur bulk operations
- **Data Integritas:** Auto-generate NIS dan validasi unique identifiers (NIK, NISN)
- **Parent Engagement:** Orang tua dapat memantau informasi anak melalui portal
- **Reporting Ready:** Export data untuk keperluan laporan dan backup
- **Historical Tracking:** Riwayat kelas, status, dan perubahan data tersimpan untuk audit

### Success Metrics
- Data entry time untuk siswa baru < 5 menit
- Search & filter response time < 1 detik
- Export Excel untuk 500 siswa < 10 detik
- Parent account auto-creation success rate 100%
- Zero data loss atau duplicate NIS/NIK/NISN
- User satisfaction > 4.5/5 untuk kemudahan penggunaan

---

## 📊 Epic Summary

| **Attribute** | **Value** |
|---------------|-----------|
| **Total Points** | 32 points |
| **Target Sprint** | Sprint 3 & 4 |
| **Priority** | Critical - P0 |
| **Dependencies** | EPIC 1 (Authentication & Master Data) |
| **Target Release** | MVP (Phase 1) |
| **Estimated Duration** | 3-4 minggu (1 developer) |

---

## 🎯 User Stories Included

### Core Student Management (26 points)

#### US-STD-001: Tambah Data Siswa Baru
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** menambahkan data siswa baru ke sistem  
**So that** data siswa tersimpan dan dapat digunakan untuk operasional sekolah

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Tambah Siswa Baru"  
   **Then** sistem menampilkan form input data siswa dengan sections: Biodata, Data Akademik, Data Orang Tua

✅ **Given** TU mengisi semua field wajib dengan data valid  
   **When** TU klik "Simpan"  
   **Then** sistem:
   - Auto-generate NIS (format: {tahun_masuk}{nomor_urut}, contoh: 20250001)
   - Simpan data siswa
   - Auto-create akun orang tua (username: no HP, password: Ortu{NIS})
   - Tampilkan notifikasi "Data siswa berhasil ditambahkan"

✅ **Given** TU input NIK/NISN yang sudah ada di sistem  
   **When** TU klik "Simpan"  
   **Then** sistem tampilkan error "NIK sudah terdaftar" atau "NISN sudah terdaftar"

✅ **Given** TU input tanggal lahir dengan umur < 5 tahun atau > 15 tahun  
   **When** TU klik "Simpan"  
   **Then** sistem tampilkan error "Umur siswa tidak sesuai untuk jenjang SD"

**Technical Notes:**
- Field wajib: Nama Lengkap, NIK (16 digit), NISN (10 digit), Jenis Kelamin, Tempat Lahir, Tanggal Lahir, Agama, Alamat Lengkap, Kelas Saat Ini, Tahun Ajaran Masuk, Nama Ayah, NIK Ayah, Pekerjaan Ayah, No HP Ayah
- Field optional: Nama Panggilan, Foto Siswa, Email, Nomor HP Siswa, Data Ibu, Data Wali
- Upload foto: max 2MB, format jpg/png
- Auto-generate NIS tidak bisa diubah setelah dibuat

---

#### US-STD-002: Edit Data Siswa
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** mengedit data siswa yang sudah ada  
**So that** data siswa selalu update dan akurat

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Edit" pada siswa tertentu  
   **Then** sistem tampilkan form edit dengan data siswa yang sudah terisi

✅ **Given** TU mengubah data siswa (misal: alamat baru)  
   **When** TU klik "Simpan Perubahan"  
   **Then** sistem:
   - Update data siswa
   - Log perubahan ke audit trail (who, when, what changed, before & after value)
   - Tampilkan notifikasi "Data berhasil diupdate"

✅ **Given** TU mengubah NIK/NISN yang sudah digunakan siswa lain  
   **When** TU klik "Simpan"  
   **Then** sistem tampilkan error validasi "NIK/NISN sudah terdaftar"

**Technical Notes:**
- NIS tidak bisa diedit (read-only)
- NIK dan NISN harus tetap unique (excluding current student)
- Log semua perubahan ke audit trail

---

#### US-STD-003: Hapus/Nonaktifkan Data Siswa
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** menonaktifkan data siswa yang sudah lulus/pindah  
**So that** data historis tetap ada tapi tidak muncul di daftar aktif

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Nonaktifkan" pada siswa yang sudah lulus  
   **Then** sistem tampilkan konfirmasi "Apakah Anda yakin ingin menghapus siswa {nama}?"

✅ **Given** TU konfirmasi nonaktifkan siswa  
   **When** TU klik "Ya"  
   **Then** sistem:
   - Soft delete: set flag `deleted_at` timestamp
   - Status siswa tidak muncul di list default
   - Data berelasi (absensi, nilai, pembayaran) tetap tersimpan

✅ **Given** siswa memiliki data berelasi (absensi, nilai, pembayaran)  
   **When** TU coba hapus  
   **Then** sistem tampilkan warning "Siswa ini memiliki data berelasi. Yakin ingin menonaktifkan?"

✅ **Given** Admin ingin restore siswa yang terhapus  
   **When** Admin klik "Aktifkan Kembali" di filter siswa tidak aktif  
   **Then** status siswa kembali "Aktif" dan muncul di daftar siswa aktif

**Technical Notes:**
- Soft delete, tidak permanent delete dari database
- Admin dapat restore jika terhapus tidak sengaja

---

#### US-STD-004: Lihat Detail Profil Siswa
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Guru/Kepala Sekolah  
**I want** melihat profil lengkap siswa  
**So that** saya dapat melihat informasi detail tentang siswa

**Acceptance Criteria:**

✅ **Given** user di halaman "Data Siswa"  
   **When** user klik nama siswa atau tombol "Lihat Detail"  
   **Then** sistem tampilkan halaman profil siswa dengan tab/sections:
   - Biodata (foto, data pribadi, alamat)
   - Data Orang Tua (Ayah, Ibu, Wali dengan kontak utama highlighted)
   - Data Akademik (NIS, NISN, Kelas, Status, Tanggal Masuk)
   - Riwayat Kelas (Table: Tahun Ajaran, Kelas, Wali Kelas)
   - Riwayat Absensi (Summary: hadir, sakit, izin, alpha)
   - Riwayat Nilai (Summary: rata-rata per mapel)
   - Riwayat Pembayaran (Summary: status SPP, tunggakan) - hanya TU & Principal

✅ **Given** user dengan role "Guru"  
   **When** guru akses profil siswa di kelas yang tidak diajar  
   **Then** sistem tampilkan error "Anda tidak memiliki akses ke siswa ini"

✅ **Given** user dengan role "Guru"  
   **When** guru akses profil siswa di kelas yang diajar  
   **Then** guru dapat melihat semua info kecuali section Pembayaran

✅ **Given** Kepala Sekolah atau TU  
   **When** akses profil siswa manapun  
   **Then** dapat melihat semua info termasuk Riwayat Pembayaran

**Technical Notes:**
- Access control berdasarkan role
- Guru hanya bisa lihat siswa di kelas yang diajar
- TU & Principal bisa lihat semua siswa
- Mobile-responsive dengan card-based layout

---

#### US-STD-005: Tambah/Edit Data Orang Tua/Wali
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** menambah dan mengedit data orang tua/wali siswa  
**So that** sistem dapat menghubungi orang tua untuk keperluan administrasi

**Acceptance Criteria:**

✅ **Given** TU di halaman edit siswa, section "Data Orang Tua"  
   **When** TU input data orang tua (Ayah/Ibu/Wali)  
   **Then** form menampilkan fields: Nama Lengkap, NIK, Pekerjaan, Pendidikan Terakhir, Penghasilan, Nomor HP, Email

✅ **Given** TU mengisi data orang tua dengan nomor HP valid  
   **When** TU simpan data siswa baru  
   **Then** sistem:
   - Simpan data orang tua
   - Auto-create akun orang tua (username: no HP, password: Ortu{NIS})
   - Kirim notifikasi WhatsApp: "Selamat! Anak Anda {nama} telah terdaftar. Username: {username}, Password: {password}"

✅ **Given** TU input nomor HP yang sudah terdaftar (orang tua punya 2 anak di sekolah yang sama)  
   **When** TU simpan data  
   **Then** sistem:
   - Link data siswa ke akun orang tua yang sudah ada (tidak create duplicate account)
   - Support multi-child per parent account

✅ **Given** TU pilih kontak utama (Ayah/Ibu/Wali) menggunakan radio button  
   **When** TU simpan  
   **Then** kontak utama akan digunakan untuk notifikasi dan akun parent

**Technical Notes:**
- No HP wajib untuk kontak utama (untuk WhatsApp notification)
- Email optional
- Support multiple children per parent account
- Auto-create account hanya untuk kontak utama

---

#### US-STD-006: Upload Foto Siswa
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** upload foto siswa  
**So that** data siswa lebih lengkap dan mudah dikenali

**Acceptance Criteria:**

✅ **Given** TU di halaman tambah/edit siswa  
   **When** TU klik area upload foto atau drag & drop file  
   **Then** sistem buka file picker untuk pilih foto (format: JPG, PNG)

✅ **Given** TU pilih foto dengan ukuran > 2MB  
   **When** TU upload foto  
   **Then** sistem tampilkan error "Ukuran file maksimal 2MB"

✅ **Given** TU upload foto yang valid  
   **When** foto berhasil diupload  
   **Then** sistem:
   - Tampilkan preview foto
   - Simpan foto dengan naming: {NIS}_{namasiswa}.jpg
   - Compress otomatis jika > 500KB tanpa menurunkan kualitas signifikan

✅ **Given** TU upload file bukan gambar (misal: PDF, DOC)  
   **When** TU coba upload  
   **Then** sistem tampilkan error "Format file harus JPG atau PNG"

**Technical Notes:**
- Format: jpg, jpeg, png
- Max size: 2MB
- Recommended dimensions: 300x400px (portrait)
- Auto-compress untuk hemat storage
- Fallback: default avatar jika tidak ada foto

---

#### US-STD-007: Filter dan Pencarian Siswa
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Guru/Kepala Sekolah  
**I want** filter dan mencari siswa dengan mudah  
**So that** saya dapat menemukan data siswa dengan cepat

**Acceptance Criteria:**

✅ **Given** user di halaman "Data Siswa"  
   **When** user ketik nama siswa di search box  
   **Then** sistem tampilkan hasil search secara real-time dengan debounce 300ms

✅ **Given** user ingin filter berdasarkan kelas  
   **When** user pilih filter "Kelas 3A"  
   **Then** sistem tampilkan hanya siswa kelas 3A

✅ **Given** user ingin filter berdasarkan status  
   **When** user pilih filter "Status: Aktif"  
   **Then** sistem tampilkan hanya siswa dengan status aktif

✅ **Given** user ingin kombinasi filter  
   **When** user apply: Kelas 4B + Status Aktif + Jenis Kelamin Perempuan  
   **Then** sistem tampilkan hasil yang sesuai dengan semua filter

✅ **Given** user search by NIS atau NISN  
   **When** user input "20250001"  
   **Then** sistem tampilkan siswa dengan NIS atau NISN matching

**Technical Notes:**
- Search by: Nama, NIS, NISN (partial match)
- Real-time search dengan debounce 300ms
- Filter options: Kelas, Status (Aktif/Mutasi/DO/Lulus), Tahun Ajaran, Jenis Kelamin
- Pagination: 20 items per page (configurable)
- Show total siswa count

---

#### US-STD-008: Export Data Siswa ke Excel
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** TU/Kepala Sekolah  
**I want** export data siswa ke Excel  
**So that** saya dapat menggunakan data untuk keperluan laporan atau backup

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Export ke Excel"  
   **Then** sistem generate file Excel (.xlsx) dengan semua data siswa sesuai filter yang aktif

✅ **Given** file Excel berhasil di-generate  
   **When** TU buka file  
   **Then** file berisi kolom:
   - NIS, NISN, NIK
   - Nama Lengkap, Jenis Kelamin, TTL
   - Alamat
   - Kelas, Status
   - Nama Orang Tua (Ayah & Ibu)
   - Kontak Orang Tua

✅ **Given** TU sudah apply filter (misal: hanya kelas 5A status Aktif)  
   **When** TU export  
   **Then** file Excel hanya berisi data siswa sesuai filter

✅ **Given** sistem generate file  
   **When** download selesai  
   **Then** filename: `Data_Siswa_{tanggal}.xlsx` (contoh: Data_Siswa_13-12-2025.xlsx)

**Technical Notes:**
- Format Excel (.xlsx) dengan header row
- Export data yang visible (setelah filter/search) atau all data
- Max 1000 rows per export untuk performa
- Include all relevant fields

---

#### US-STD-009: Import Data Siswa dari Excel
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** import data siswa dari Excel  
**So that** saya tidak perlu input manual satu per satu (bulk insert)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik "Import dari Excel"  
   **Then** sistem tampilkan:
   - Instruksi import
   - Link download template Excel
   - Button upload file

✅ **Given** TU download template dan isi data siswa  
   **When** TU upload file Excel yang valid  
   **Then** sistem:
   - Validasi semua data
   - Tampilkan preview data yang akan diimport
   - Tampilkan jumlah: valid, invalid, duplicate

✅ **Given** ada data yang tidak valid (format tanggal salah, NIS duplicate, NIK invalid)  
   **When** sistem validasi  
   **Then** sistem tampilkan list error dengan nomor baris yang bermasalah

✅ **Given** semua data valid  
   **When** TU klik "Proses Import"  
   **Then** sistem:
   - Insert data ke database
   - Auto-generate NIS untuk yang kosong
   - Auto-create parent accounts
   - Tampilkan notifikasi "X siswa berhasil ditambahkan"

**Technical Notes:**
- Template Excel dengan contoh data dan format
- Validasi di frontend & backend
- Max 100 rows per import untuk performa
- Support auto-generate NIS
- Auto-create parent accounts

---

#### US-STD-010: Pindah Kelas Siswa (Naik Kelas)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** memindahkan siswa ke kelas lain (naik kelas atau mutasi)  
**So that** data kelas siswa selalu update setiap tahun ajaran

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa", menu "Naik Kelas"  
   **When** TU pilih:
   - Tahun Ajaran Lama & Baru
   - Kelas Asal (contoh: Kelas 1A)
   - Kelas Tujuan (contoh: Kelas 2A)  
   **Then** sistem tampilkan preview list siswa yang akan dipindahkan

✅ **Given** TU review preview siswa  
   **When** TU uncheck siswa yang tidak naik kelas  
   **Then** siswa yang uncheck tidak akan dipindahkan

✅ **Given** TU konfirmasi perpindahan  
   **When** TU klik "Proses Naik Kelas"  
   **Then** sistem:
   - Update field `kelas_saat_ini` dan `tahun_ajaran`
   - Insert record ke `student_class_history` table
   - Tampilkan success notification: "{X} siswa berhasil dipindahkan ke {kelas_tujuan}"

✅ **Given** TU ingin bulk naik kelas semua siswa (akhir tahun ajaran)  
   **When** TU klik "Naik Kelas Otomatis"  
   **Then** sistem:
   - Tampilkan konfirmasi dan daftar perubahan (Kelas 1→2, 2→3, dst)
   - Kelas 6 yang naik → status otomatis "Lulus"

**Technical Notes:**
- Kelas tujuan harus lebih tinggi dari kelas asal (kecuali tinggal kelas)
- History perpindahan kelas disimpan
- Option: auto-promote semua kelas sekaligus
- Siswa kelas 6 yang naik kelas → status "Lulus"

---

#### US-STD-011: Update Status Siswa (Mutasi/DO/Lulus)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** mengubah status siswa menjadi mutasi/DO/lulus  
**So that** data siswa yang tidak aktif tidak tercampur dengan siswa aktif

**Acceptance Criteria:**

✅ **Given** TU di halaman detail siswa  
   **When** TU klik "Change Status"  
   **Then** sistem tampilkan dropdown status: Aktif, Mutasi, DO, Lulus

✅ **Given** TU pilih status "Mutasi"  
   **When** TU submit  
   **Then** sistem tampilkan form:
   - Tanggal Mutasi (required, tidak boleh future date)
   - Alasan (required, min 10 karakter)
   - Sekolah Tujuan (required, min 5 karakter)

✅ **Given** TU pilih status "DO (Drop Out)"  
   **When** TU submit  
   **Then** sistem tampilkan form:
   - Tanggal Keluar (required, tidak boleh future date)
   - Alasan (required, min 10 karakter)

✅ **Given** TU pilih status "Lulus"  
   **When** TU submit  
   **Then** sistem:
   - Validasi siswa harus kelas 6
   - Jika bukan kelas 6 → error "Hanya siswa kelas 6 yang bisa lulus"
   - Jika kelas 6 → tampilkan form: Tanggal Kelulusan, Tahun Ajaran Lulus

✅ **Given** status berubah menjadi non-aktif (Mutasi/DO/Lulus)  
   **When** user filter list siswa  
   **Then** siswa tidak muncul di list default (hanya jika filter status aktif)

**Technical Notes:**
- Status: Aktif, Mutasi, DO (Drop Out), Lulus
- Save history ke `student_status_history` table
- Notify orang tua via WhatsApp (optional)
- Status Lulus hanya untuk siswa kelas 6

---

### Parent Portal (6 points)

#### US-STD-012: Lihat Data Siswa (Portal Orang Tua)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Orang Tua  
**I want** melihat data anak saya yang terdaftar  
**So that** saya dapat memantau informasi anak saya di sekolah

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua masuk ke dashboard  
   **Then** sistem tampilkan card/list anak yang terdaftar (jika punya > 1 anak di sekolah yang sama)

✅ **Given** orang tua klik salah satu anak  
   **When** halaman profil anak load  
   **Then** sistem tampilkan (read-only):
   - Foto, Nama, NIS, Kelas, Wali Kelas
   - Info umum (TTL, Alamat, Agama)
   - Tab: Absensi, Nilai, Pembayaran

✅ **Given** orang tua ingin lihat riwayat absensi anak  
   **When** orang tua klik tab "Absensi"  
   **Then** sistem tampilkan rekap absensi bulanan: hadir, sakit, izin, alpha dengan chart

✅ **Given** orang tua ingin lihat riwayat nilai  
   **When** orang tua klik tab "Nilai"  
   **Then** sistem tampilkan nilai per mata pelajaran (semester terakhir)

✅ **Given** orang tua ingin lihat status pembayaran  
   **When** orang tua klik tab "Pembayaran"  
   **Then** sistem tampilkan status pembayaran SPP dan tagihan lain

**Technical Notes:**
- Read-only untuk orang tua (tidak bisa edit)
- Mobile-friendly (mayoritas orang tua pakai HP)
- Data real-time
- Support multi-child per parent account

---

#### US-STD-013: Parent Account Auto-Creation
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** System  
**I want** otomatis membuat akun orang tua saat siswa baru ditambahkan  
**So that** orang tua dapat langsung akses portal tanpa proses registrasi manual

**Acceptance Criteria:**

✅ **Given** TU menambahkan siswa baru dengan data orang tua lengkap  
   **When** TU klik "Simpan"  
   **Then** sistem:
   - Auto-create user account dengan:
     - Role: PARENT
     - Username: nomor HP kontak utama (remove spaces/dash)
     - Password: `Ortu{NIS}` (contoh: Ortu20250001)
     - Email: email kontak utama (jika ada)
     - `is_first_login`: true
     - Link ke `student_id`

✅ **Given** orang tua sudah punya akun (punya anak lain di sekolah)  
   **When** TU tambah siswa baru dengan nomor HP sama  
   **Then** sistem:
   - Link student baru ke akun existing
   - Support multi-child per parent account
   - Tidak create duplicate account

✅ **Given** akun orang tua berhasil dibuat  
   **When** sistem selesai save  
   **Then** sistem kirim notifikasi WhatsApp ke orang tua:
   "Selamat! Anak Anda {nama_siswa} telah terdaftar di {nama_sekolah}. Username: {username}, Password: {password}. Silakan login di {url} dan ganti password Anda."

✅ **Given** orang tua login pertama kali  
   **When** orang tua input username & password default  
   **Then** sistem:
   - Redirect ke halaman "Ganti Password Wajib"
   - Force user untuk ganti password sebelum akses dashboard

**Technical Notes:**
- Trigger: saat save data siswa baru
- Username: nomor HP kontak utama
- Password default: `Ortu{NIS}`
- Support multi-child per parent account
- Send WhatsApp notification (integrate dengan WhatsApp API)

---

## 🏗️ Technical Architecture

### Database Schema Requirements

#### Students Table
```sql
CREATE TABLE students (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  nis VARCHAR(20) UNIQUE NOT NULL,
  nisn VARCHAR(10) UNIQUE NOT NULL,
  nik VARCHAR(16) UNIQUE NOT NULL,
  
  -- Biodata
  full_name VARCHAR(100) NOT NULL,
  nickname VARCHAR(50),
  gender ENUM('L', 'P') NOT NULL,
  birth_place VARCHAR(100) NOT NULL,
  birth_date DATE NOT NULL,
  religion ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu') NOT NULL,
  child_position INT,
  siblings_count INT,
  family_status ENUM('Anak Kandung', 'Anak Tiri', 'Anak Angkat'),
  
  -- Alamat
  address TEXT NOT NULL,
  rt VARCHAR(5),
  rw VARCHAR(5),
  kelurahan VARCHAR(100) NOT NULL,
  kecamatan VARCHAR(100) NOT NULL,
  kota VARCHAR(100) NOT NULL,
  provinsi VARCHAR(100) NOT NULL,
  postal_code VARCHAR(10),
  
  -- Kontak
  phone VARCHAR(15),
  email VARCHAR(100),
  photo_url VARCHAR(255),
  
  -- Data Akademik
  academic_year_id BIGINT NOT NULL,
  current_class_id BIGINT NOT NULL,
  admission_date DATE NOT NULL,
  status ENUM('Aktif', 'Mutasi', 'DO', 'Lulus') DEFAULT 'Aktif',
  
  -- Status metadata
  status_date DATE,
  status_reason TEXT,
  status_destination VARCHAR(255), -- untuk mutasi
  
  -- Timestamps
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by BIGINT,
  updated_by BIGINT,
  
  FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
  FOREIGN KEY (current_class_id) REFERENCES classes(id),
  FOREIGN KEY (created_by) REFERENCES users(id),
  FOREIGN KEY (updated_by) REFERENCES users(id),
  
  INDEX idx_nis (nis),
  INDEX idx_nisn (nisn),
  INDEX idx_nik (nik),
  INDEX idx_full_name (full_name),
  INDEX idx_current_class (current_class_id),
  INDEX idx_status (status),
  INDEX idx_deleted_at (deleted_at)
);
```

#### Parents Table
```sql
CREATE TABLE parents (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT NOT NULL,
  
  -- Relationship
  relation_type ENUM('Ayah', 'Ibu', 'Wali') NOT NULL,
  is_primary_contact BOOLEAN DEFAULT FALSE,
  
  -- Biodata
  full_name VARCHAR(100) NOT NULL,
  nik VARCHAR(16) NOT NULL,
  occupation VARCHAR(100),
  education ENUM('SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'),
  income_range ENUM('<1jt', '1-3jt', '3-5jt', '>5jt'),
  
  -- Kontak
  phone VARCHAR(15),
  email VARCHAR(100),
  address TEXT,
  
  -- Account link
  user_id BIGINT, -- link to users table
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  
  INDEX idx_student_id (student_id),
  INDEX idx_user_id (user_id),
  INDEX idx_phone (phone),
  INDEX idx_is_primary (is_primary_contact)
);
```

#### Student Class History Table
```sql
CREATE TABLE student_class_history (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT NOT NULL,
  academic_year_id BIGINT NOT NULL,
  class_id BIGINT NOT NULL,
  homeroom_teacher_id BIGINT,
  start_date DATE NOT NULL,
  end_date DATE,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (homeroom_teacher_id) REFERENCES users(id),
  
  INDEX idx_student_id (student_id),
  INDEX idx_academic_year (academic_year_id),
  INDEX idx_class_id (class_id)
);
```

#### Student Status History Table
```sql
CREATE TABLE student_status_history (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT NOT NULL,
  status_from ENUM('Aktif', 'Mutasi', 'DO', 'Lulus') NOT NULL,
  status_to ENUM('Aktif', 'Mutasi', 'DO', 'Lulus') NOT NULL,
  change_date DATE NOT NULL,
  reason TEXT,
  destination VARCHAR(255), -- untuk mutasi
  
  changed_by BIGINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES users(id),
  
  INDEX idx_student_id (student_id),
  INDEX idx_change_date (change_date)
);
```

#### Student Audit Log Table
```sql
CREATE TABLE student_audit_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT NOT NULL,
  action ENUM('create', 'update', 'delete', 'restore') NOT NULL,
  field_name VARCHAR(100),
  old_value TEXT,
  new_value TEXT,
  
  user_id BIGINT NOT NULL,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  
  INDEX idx_student_id (student_id),
  INDEX idx_action (action),
  INDEX idx_created_at (created_at)
);
```

---

### API Endpoints

#### Student Management
- `GET /api/students` - List students (paginated, with filters)
  - Query params: `page`, `limit`, `class_id`, `status`, `academic_year_id`, `gender`, `search`
- `POST /api/students` - Create new student
- `GET /api/students/:id` - Get student detail
- `PUT /api/students/:id` - Update student
- `DELETE /api/students/:id` - Soft delete student
- `POST /api/students/:id/restore` - Restore deleted student
- `GET /api/students/:id/history` - Get student history (class, status)

#### Student Status Management
- `POST /api/students/:id/change-status` - Change student status
  - Body: `{ status, date, reason, destination }`
- `GET /api/students/:id/status-history` - Get status history

#### Bulk Operations
- `POST /api/students/bulk-promote` - Bulk class promotion
  - Body: `{ from_class_id, to_class_id, academic_year_id, student_ids[] }`
- `POST /api/students/auto-promote` - Auto promote all classes
  - Body: `{ academic_year_id }`

#### Parent Management
- `GET /api/students/:id/parents` - Get student's parents
- `POST /api/students/:id/parents` - Add parent to student
- `PUT /api/parents/:id` - Update parent info
- `DELETE /api/parents/:id` - Remove parent
- `POST /api/parents/:id/reset-password` - Admin reset parent password

#### Import/Export
- `POST /api/students/import` - Import students from Excel
  - Multipart form data with Excel file
- `GET /api/students/export` - Export students to Excel
  - Query params: same as list (for filtering)
- `GET /api/students/import-template` - Download import template

#### Parent Portal
- `GET /api/parent/children` - Get parent's children list
- `GET /api/parent/children/:id` - Get child detail
- `GET /api/parent/children/:id/attendance` - Get child attendance summary
- `GET /api/parent/children/:id/grades` - Get child grades summary
- `GET /api/parent/children/:id/payments` - Get child payment summary

---

### Business Logic Implementation

#### NIS Auto-Generation
```javascript
function generateNIS(admissionYear) {
  // Format: {tahun_masuk}{nomor_urut}
  // Example: 20250001 (siswa pertama tahun 2025)
  
  const year = admissionYear.toString();
  const lastStudent = await Student.findOne({
    where: { nis: { startsWith: year } },
    order: [['nis', 'DESC']]
  });
  
  let sequence = 1;
  if (lastStudent) {
    const lastSequence = parseInt(lastStudent.nis.substring(4));
    sequence = lastSequence + 1;
  }
  
  const nis = year + sequence.toString().padStart(4, '0');
  return nis;
}
```

#### Parent Account Auto-Creation
```javascript
async function createParentAccount(student, primaryParent) {
  // Check if account already exists
  const existingUser = await User.findOne({
    where: { username: primaryParent.phone }
  });
  
  if (existingUser) {
    // Link to existing parent account
    await Parent.update(
      { user_id: existingUser.id },
      { where: { id: primaryParent.id } }
    );
    return existingUser;
  }
  
  // Create new parent account
  const password = `Ortu${student.nis}`;
  const hashedPassword = await bcrypt.hash(password, 12);
  
  const user = await User.create({
    username: primaryParent.phone,
    password: hashedPassword,
    role_id: PARENT_ROLE_ID,
    email: primaryParent.email,
    is_first_login: true,
    status: 'active'
  });
  
  // Link to parent record
  await Parent.update(
    { user_id: user.id },
    { where: { id: primaryParent.id } }
  );
  
  // Send WhatsApp notification
  await sendWhatsAppNotification(
    primaryParent.phone,
    `Selamat! Anak Anda ${student.full_name} telah terdaftar di ${SCHOOL_NAME}. Username: ${user.username}, Password: ${password}. Silakan login di ${APP_URL} dan ganti password Anda.`
  );
  
  return user;
}
```

#### Bulk Class Promotion
```javascript
async function bulkClassPromotion({
  fromClassId,
  toClassId,
  academicYearId,
  studentIds
}) {
  // Start transaction
  const transaction = await db.transaction();
  
  try {
    // Update students' class
    await Student.update(
      {
        current_class_id: toClassId,
        academic_year_id: academicYearId
      },
      {
        where: { id: { in: studentIds } },
        transaction
      }
    );
    
    // Insert to class history
    const historyRecords = studentIds.map(studentId => ({
      student_id: studentId,
      academic_year_id: academicYearId,
      class_id: toClassId,
      start_date: new Date()
    }));
    
    await StudentClassHistory.bulkCreate(historyRecords, { transaction });
    
    // Check if promoted to grade 7 (after grade 6)
    const toClass = await Class.findByPk(toClassId);
    if (toClass.level === 7) {
      // Auto-graduate students
      await Student.update(
        { status: 'Lulus', status_date: new Date() },
        { where: { id: { in: studentIds } }, transaction }
      );
    }
    
    await transaction.commit();
    return { success: true, count: studentIds.length };
  } catch (error) {
    await transaction.rollback();
    throw error;
  }
}
```

---

## 🎨 UI/UX Design Requirements

### Student List Page

**Layout:**
- Header dengan title "Data Siswa" dan button "Tambah Siswa" (primary, kanan atas)
- Filter bar (horizontal) dengan 4 dropdowns:
  - Kelas (all/1A/1B/.../6B)
  - Status (all/Aktif/Mutasi/DO/Lulus)
  - Tahun Ajaran
  - Jenis Kelamin (all/L/P)
- Search box (search icon, placeholder: "Cari nama, NIS, atau NISN")
- Button "Export Excel" dan "Import Excel" (kanan, setelah filter)
- Table responsive dengan columns:
  - Foto (thumbnail 40x40px, rounded)
  - NIS
  - Nama Lengkap
  - Kelas
  - Status (badge dengan color: Aktif=hijau, Mutasi=kuning, DO=merah, Lulus=biru)
  - Actions (icon: view 👁, edit ✏, delete 🗑 dengan tooltip)
- Pagination di bawah table (show: "Menampilkan 1-20 dari 245 siswa")

**Mobile:**
- Filter collapse jadi dropdown/accordion
- Table collapse jadi card list
- Card per siswa: foto, nama, kelas, status
- Tap card untuk expand/lihat detail
- Action buttons dalam card (horizontal layout)

**Interactions:**
- Row hover effect (background abu-abu terang)
- Search debounce 300ms
- Filter auto-apply onChange
- Loading skeleton saat fetch data

---

### Student Form (Create/Edit)

**Layout:**
- Multi-section form dengan accordion atau long scroll
- **Section 1: Biodata Siswa**
  - Upload foto (drag & drop area, 200x200px preview)
  - Grid 2 kolom: Nama Lengkap*, Nama Panggilan
  - Grid 3 kolom: NIK*, NISN*, NIS (read-only untuk edit)
  - Grid 2 kolom: Jenis Kelamin*, Agama*
  - Grid 2 kolom: Tempat Lahir*, Tanggal Lahir* (date picker)
  - Grid 3 kolom: Anak ke-*, Jumlah Saudara*, Status dalam Keluarga
  - Alamat Lengkap* (textarea, 4 rows)
  - Grid 4 kolom: RT, RW, Kelurahan*, Kecamatan*
  - Grid 3 kolom: Kota/Kabupaten*, Provinsi*, Kode Pos
  - Grid 2 kolom: Nomor HP, Email

- **Section 2: Data Akademik**
  - Grid 2 kolom: Tahun Ajaran Masuk*, Kelas Saat Ini*
  - Grid 2 kolom: Tanggal Masuk*, Status (default: Aktif)

- **Section 3: Data Orang Tua - Ayah**
  - Grid 2 kolom: Nama Lengkap*, NIK*
  - Grid 2 kolom: Pekerjaan*, Pendidikan Terakhir
  - Grid 2 kolom: Penghasilan, Nomor HP*
  - Email

- **Section 4: Data Orang Tua - Ibu**
  - (sama seperti Ayah)

- **Section 5: Data Wali (opsional, collapsible)**
  - Checkbox "Ada Wali"
  - (fields sama seperti Ayah/Ibu)

- **Section 6: Kontak Utama**
  - Radio button: ⚫ Ayah ⚪ Ibu ⚪ Wali
  - Helper text: "Kontak utama akan digunakan untuk notifikasi dan akun portal orang tua"

- Footer dengan button:
  - "Batal" (secondary, kiri)
  - "Simpan" (primary, kanan)

**UX:**
- Upload foto: drag & drop atau click to upload, preview image
- Date picker untuk tanggal lahir (calendar UI, tahun dropdown)
- Dropdown dengan search untuk provinsi/kota (banyak data)
- Auto-format nomor HP (remove spaces/dash, validasi format)
- Real-time validation dengan error message di bawah field (warna merah)
- Required field ditandai dengan asterisk (*) warna merah
- Save button disabled sampai semua required field valid
- Auto-capitalize nama (first letter per word)
- Success notification: toast dengan confetti animation (untuk create)

**Mobile:**
- Stack form fields vertically (1 kolom)
- Touch-friendly dropdowns dan date picker (native)
- Floating label untuk better UX
- Bottom fixed button bar untuk Save/Cancel

---

### Student Detail Page

**Layout:**
- **Header:**
  - Foto siswa (besar, 200x200px, rounded)
  - Nama lengkap (H2, bold)
  - NIS (badge, warna primary)
  - Kelas saat ini (badge, warna secondary)
  - Status (badge dengan color)
  
- **Action Buttons (top right):**
  - Edit (✏, hanya TU & Principal)
  - Print Profil (🖨)
  - Reset Password Orang Tua (🔑, hanya TU)
  - Change Status (🔄, hanya TU)

- **Tab Navigation:**
  - Biodata
  - Orang Tua
  - Akademik
  - Riwayat

**Tab: Biodata**
- 2-column layout (label: value)
- Grouped by kategori:
  - **Data Pribadi:** Nama Lengkap, Nama Panggilan, NIK, NISN, NIS, Jenis Kelamin, TTL, Agama, Anak ke-, Jumlah Saudara
  - **Alamat:** Alamat lengkap, RT/RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos
  - **Kontak:** Nomor HP, Email

**Tab: Orang Tua**
- 3 cards: Ayah, Ibu, Wali
- Per card:
  - Label hubungan (H4)
  - Badge "Kontak Utama" (jika applicable, warna hijau)
  - Nama lengkap (bold)
  - NIK
  - Pekerjaan
  - Pendidikan Terakhir
  - Penghasilan
  - Kontak: No HP (dengan icon WhatsApp clickable), Email

**Tab: Akademik**
- **Info Kelas:**
  - Kelas Saat Ini
  - Wali Kelas
  - Tahun Ajaran
  - Status
  - Tanggal Masuk

- **Riwayat Kelas (Table):**
  - Columns: Tahun Ajaran | Kelas | Wali Kelas
  - Sorted by tahun ajaran (newest first)

**Tab: Riwayat**
- **Sub-tab: Absensi**
  - Chart: Hadir, Izin, Sakit, Alpha (bulan ini/semester ini)
  - Summary statistics
  - Link "Lihat Detail Absensi" → redirect ke modul Attendance

- **Sub-tab: Nilai**
  - Table: Mata Pelajaran | Rata-rata Nilai (semester terakhir)
  - Link "Lihat Detail Rapor" → redirect ke modul Grades

- **Sub-tab: Pembayaran** (hanya TU & Principal)
  - Status SPP: Lunas/Menunggak (badge)
  - Total tunggakan (jika ada, highlight merah)
  - Link "Lihat Detail Pembayaran" → redirect ke modul Payment

**Mobile:**
- Header vertical layout
- Tab jadi dropdown select (lebih hemat space)
- Single column layout untuk content
- Card-based design
- Actions dalam floating action button (FAB) dengan menu

---

### Bulk Class Promotion Flow

**Step 1: Pilih Kelas (Modal/Page)**
- Form dengan 2 sections:
  - **Tahun Ajaran:**
    - Dropdown: Tahun Ajaran Asal
    - Dropdown: Tahun Ajaran Tujuan
  - **Kelas:**
    - Dropdown: Kelas Asal (contoh: 1A)
    - Dropdown: Kelas Tujuan (contoh: 2A)
  - Helper text: "Kelas tujuan harus lebih tinggi dari kelas asal"
- Button "Lanjut" (primary, kanan)

**Step 2: Preview & Konfirmasi**
- Table list siswa di kelas asal:
  - Checkbox (all selected by default)
  - Nama siswa
  - NIS
  - Status
- Info box (highlight):
  - "X dari Y siswa akan dipindahkan dari Kelas 1A ke Kelas 2A untuk Tahun Ajaran 2025/2026"
- User dapat uncheck siswa yang tidak naik
- Button "Kembali" (secondary) dan "Proses Naik Kelas" (primary, hijau)

**Step 3: Success**
- Success illustration (icon ✅ besar)
- Success message: "Berhasil! X siswa telah dipindahkan ke Kelas 2A"
- Button "Selesai" (kembali ke list) atau "Naik Kelas Lagi" (untuk kelas lain)

**UX:**
- Progress indicator (Step 1/3, 2/3, 3/3)
- Loading state saat proses (progress bar)
- Confirmation dialog sebelum proses
- Toast notification success

---

### Import Excel Flow

**Step 1: Upload (Modal/Page)**
- **Instruksi:**
  - Icon 📄
  - "Import data siswa dari Excel"
  - List requirements:
    1. Download template Excel terlebih dahulu
    2. Isi data sesuai format
    3. Maksimal 100 siswa per import
- Button "Download Template" (secondary)
- Drag & drop area untuk upload file
- Button "Proses" (primary, disabled sampai file uploaded)

**Step 2: Validasi & Preview**
- **Summary Box:**
  - ✅ Valid: X siswa
  - ❌ Invalid: Y siswa
  - ⚠️ Duplicate: Z siswa
- **Table Preview:**
  - Show semua data dengan status indicator
  - Row dengan error highlight merah dengan tooltip error message
- Button "Kembali" dan "Lanjutkan Import" (disabled jika ada invalid)

**Step 3: Process & Success**
- Loading state dengan progress bar
- Success: "X siswa berhasil ditambahkan"
- List siswa yang berhasil (collapsible)
- Button "Selesai" atau "Import Lagi"

**Error Handling:**
- Jelas error message per row
- Nomor baris yang error
- Allow download error report (Excel dengan kolom error message)

---

### Parent Portal - Child Profile

**Layout:**
- **Dashboard:**
  - Greeting: "Selamat datang, {Nama Orang Tua}"
  - Card list anak (jika > 1 anak):
    - Foto anak
    - Nama
    - Kelas
    - Wali Kelas
    - Status
  - Tap card → detail anak

- **Child Detail:**
  - Header sama seperti TU view (tapi read-only)
  - Tab:
    - **Info Anak:** Biodata lengkap
    - **Absensi:** Chart & summary
    - **Nilai:** Table nilai per mapel
    - **Pembayaran:** Status SPP, tunggakan, history

**Mobile-First Design:**
- Card-based layout
- Large touch targets (min 48px)
- Bottom tab navigation
- Pull to refresh
- Native-like transitions

**UX:**
- Simple & clean (tidak overwhelming)
- Visual feedback (loading, success)
- Offline mode (cache data, show last sync time)
- Push notification untuk update penting

---

## ✅ Definition of Done

### Code Level
- [ ] Unit test coverage minimal 80%
- [ ] Integration test untuk critical flow (create student, parent account creation, bulk promotion)
- [ ] Code review approved
- [ ] No critical linter errors
- [ ] All API endpoints documented (Swagger)

### Functionality
- [ ] All acceptance criteria met dan tested
- [ ] CRUD operations working untuk students & parents
- [ ] Bulk class promotion tested dengan 100+ students
- [ ] Parent account auto-creation working 100%
- [ ] Import/Export Excel tested dengan berbagai scenarios
- [ ] Access control tested (Guru hanya lihat siswa di kelas sendiri)
- [ ] Audit log recording semua perubahan data siswa

### UI/UX
- [ ] Responsive di mobile dan desktop (tested di iOS & Android)
- [ ] Loading state untuk semua async actions
- [ ] Error handling dengan user-friendly message (Bahasa Indonesia)
- [ ] Success feedback (toast/notification)
- [ ] Form validation real-time dengan clear error messages
- [ ] Accessibility: keyboard navigation, screen reader support

### Data Integrity
- [ ] NIS auto-generation working, no duplicate
- [ ] NIK & NISN validation, no duplicate
- [ ] Soft delete working, data historis tetap ada
- [ ] Parent account multi-child support working
- [ ] Class history tracking accurate

### Performance
- [ ] Student list load time < 2 detik untuk 500 students
- [ ] Search response time < 1 detik
- [ ] Export Excel < 10 detik untuk 500 students
- [ ] Import Excel < 30 detik untuk 100 students
- [ ] Database queries optimized (use indexes)
- [ ] No N+1 query problem

### Documentation
- [ ] API documentation complete
- [ ] Database schema documented
- [ ] User manual untuk TU (Bahasa Indonesia)
- [ ] User manual untuk Parent Portal (Bahasa Indonesia)
- [ ] Technical documentation untuk developer

---

## 🔗 Dependencies

### External Dependencies
- **WhatsApp API:** Fonnte/Wablas untuk kirim credential ke orang tua
- **Image Processing:** Sharp atau ImageMagick untuk compress foto siswa
- **Excel Library:** ExcelJS atau Apache POI untuk import/export
- **Frontend Framework:** React/Next.js
- **Backend Framework:** Node.js/Express atau Laravel
- **Database:** PostgreSQL atau MySQL

### Internal Dependencies
- **EPIC 1 (MUST COMPLETE FIRST):**
  - Authentication & Authorization (untuk RBAC)
  - Master Data Kelas (untuk assign siswa ke kelas)
  - Master Data Tahun Ajaran (untuk data akademik siswa)
  - User Management (untuk create parent accounts)

### Blocking For
EPIC 2 harus selesai sebelum epic berikut dapat dimulai:
- **EPIC 3 (Attendance):** butuh student list per class
- **EPIC 4 (Payment):** butuh student info dan parent contact
- **EPIC 5 (Grades):** butuh student list per class
- **EPIC 6 (PSB):** akan import data dari PSB ke Student Management

---

## 🧪 Testing Strategy

### Unit Testing
- Service layer:
  - NIS auto-generation logic
  - Parent account creation logic
  - Data validation (NIK, NISN, phone, email)
  - Bulk promotion logic
- Utility functions:
  - Phone number formatting
  - Name capitalization
  - Date validation
- Target coverage: 80%

### Integration Testing
- **Student CRUD:**
  - Create student dengan data valid → success, NIS auto-generated
  - Create student dengan NIK duplicate → error
  - Update student data → success, audit log created
  - Soft delete student → success, tidak muncul di list
  - Restore deleted student → success, muncul di list

- **Parent Account:**
  - Create student → parent account auto-created
  - Create student dengan phone existing → link ke account existing
  - Parent login → forced change password
  - Parent view child profile → hanya anak sendiri

- **Bulk Operations:**
  - Bulk class promotion → all selected students promoted
  - Import Excel valid data → all imported
  - Import Excel invalid data → error list shown
  - Export Excel dengan filter → only filtered data exported

### E2E Testing (Critical Paths)
1. **Happy Path - Create Student:**
   - TU login → Tambah Siswa → isi semua data → Upload foto → Simpan → Success + Parent account created → WhatsApp sent

2. **Search & Filter:**
   - TU login → Data Siswa → Search "Budi" → result filtered
   - Apply filter Kelas 3A + Status Aktif → result filtered
   - Clear filter → all students shown

3. **Bulk Class Promotion:**
   - TU login → Naik Kelas → Pilih Kelas 1A → 2A → Preview → Uncheck 1 siswa → Proses → Success

4. **Parent Portal:**
   - Parent login → Forced change password → Dashboard → Pilih anak → View profile → View absensi, nilai, pembayaran

5. **Import Excel:**
   - TU login → Import → Download template → Upload file → Validasi → Proses → Success

### Performance Testing
- Load test: 100 concurrent users akses student list
- Stress test: Bulk import 100 students bersamaan
- Search performance: 1000 students dengan real-time search
- Export performance: 500 students ke Excel
- Target: 95th percentile response time < 2 detik

### Security Testing
- RBAC: Guru coba akses edit student → blocked
- RBAC: Parent coba akses student lain → blocked
- SQL injection test pada search & filter
- File upload security: upload PHP file sebagai foto → blocked
- XSS test pada nama siswa dengan script tag

---

## 📅 Sprint Planning

### Sprint 3 (2 minggu) - 18 points
**Focus:** Core Student CRUD & Parent Account

**Stories:**
- US-STD-001: Tambah Data Siswa Baru (3 pts) - **Day 1-3**
- US-STD-002: Edit Data Siswa (2 pts) - **Day 4**
- US-STD-003: Hapus/Nonaktifkan Siswa (2 pts) - **Day 5**
- US-STD-004: Lihat Detail Profil Siswa (3 pts) - **Day 6-7**
- US-STD-005: Tambah/Edit Data Orang Tua (3 pts) - **Day 8-9**
- US-STD-006: Upload Foto Siswa (2 pts) - **Day 10**
- US-STD-013: Parent Account Auto-Creation (3 pts) - **Day 10-12**

**Deliverables:**
- TU dapat create, read, update, delete students
- Auto-generate NIS working
- Parent account auto-creation working
- Upload foto siswa working
- Audit trail recording changes

**Sprint Goal:** "TU dapat mengelola data siswa dan sistem auto-create parent account"

---

### Sprint 4 (2 minggu) - 14 points
**Focus:** Search, Filter, Bulk Operations, Parent Portal

**Stories:**
- US-STD-007: Filter dan Pencarian Siswa (3 pts) - **Day 1-2**
- US-STD-008: Export Data Siswa ke Excel (2 pts) - **Day 3**
- US-STD-009: Import Data Siswa dari Excel (3 pts) - **Day 4-5**
- US-STD-010: Pindah Kelas Siswa (Naik Kelas) (3 pts) - **Day 6-7**
- US-STD-011: Update Status Siswa (3 pts) - **Day 8-9**
- US-STD-012: Portal Orang Tua (3 pts) - **Day 10-12**

**Deliverables:**
- Search & filter working dengan performa baik
- Import/Export Excel working
- Bulk class promotion working
- Status management (Mutasi/DO/Lulus) working
- Parent portal working (view child profile)

**Sprint Goal:** "Sistem memiliki fitur lengkap untuk student management dan parent portal"

---

## 🎯 Acceptance Criteria (Epic Level)

### Functional
- [ ] TU dapat create, read, update, delete student dengan mudah
- [ ] NIS auto-generated, tidak ada duplicate
- [ ] NIK & NISN validation working, tidak ada duplicate
- [ ] Parent account auto-created saat siswa baru ditambahkan
- [ ] Support multi-child per parent account
- [ ] WhatsApp notification sent ke orang tua saat account created
- [ ] Upload foto siswa working (max 2MB, auto-compress)
- [ ] Search & filter working dengan performa baik (< 1 detik)
- [ ] Bulk class promotion working untuk 100+ students
- [ ] Status management (Aktif/Mutasi/DO/Lulus) working
- [ ] Import/Export Excel working dengan validation
- [ ] Guru hanya dapat lihat siswa di kelas yang diajar
- [ ] Parent dapat view child profile di portal (read-only)
- [ ] Audit log recording semua perubahan data siswa

### Non-Functional
- [ ] Student list load time < 2 detik untuk 500 students
- [ ] Search response time < 1 detik
- [ ] Export Excel < 10 detik untuk 500 students
- [ ] Import Excel < 30 detik untuk 100 students
- [ ] Mobile-responsive (tested di iOS & Android)
- [ ] User-friendly error messages dalam Bahasa Indonesia
- [ ] Soft delete working, data historis tetap ada

### Technical
- [ ] API documentation complete (Swagger)
- [ ] Database schema implemented dengan proper indexes
- [ ] Unit test coverage 80%
- [ ] Integration test untuk critical flows
- [ ] RBAC implemented dan tested
- [ ] Image upload security (validate file type & size)
- [ ] WhatsApp API integration working

---

## 🚧 Risks & Mitigation

### Risk 1: WhatsApp API Delivery Issues
**Impact:** High - Parent tidak terima credential  
**Probability:** Medium  
**Mitigation:**
- Gunakan reliable WhatsApp gateway (Fonnte/Wablas)
- Implement retry mechanism (max 3 attempts)
- Log semua notifikasi sent/failed
- Alternative: display credential di UI untuk TU print/copy manual
- Admin can resend credential manually dari UI

### Risk 2: Performance Degradation (Large Student List)
**Impact:** Medium - Slow load time untuk list  
**Probability:** Medium  
**Mitigation:**
- Implement pagination (20 items per page)
- Optimize database queries dengan proper indexes
- Implement caching untuk frequently accessed data
- Virtual scrolling untuk large lists di frontend
- Load balancing jika concurrent users tinggi

### Risk 3: Duplicate NIS Generation (Race Condition)
**Impact:** High - Data integrity issue  
**Probability:** Low  
**Mitigation:**
- Database unique constraint pada NIS column
- Use transaction untuk NIS generation
- Implement database lock saat generate NIS
- Retry mechanism jika duplicate detected

### Risk 4: Import Excel Validation Complexity
**Impact:** Medium - Bad data masuk sistem  
**Probability:** Medium  
**Mitigation:**
- Comprehensive validation di backend (frontend bisa di-bypass)
- Clear error messages dengan nomor baris
- Provide template Excel dengan contoh data
- Limit import ke 100 rows untuk performa
- Preview & confirmation sebelum final import

### Risk 5: Photo Upload Security
**Impact:** High - Potential malware upload  
**Probability:** Low  
**Mitigation:**
- Validate file type (check magic bytes, bukan hanya extension)
- Max file size 2MB
- Scan uploaded files dengan antivirus (optional)
- Store files outside web root
- Generate unique filename (prevent overwrite)
- Use CDN/Object Storage untuk production

### Risk 6: Access Control Bypass
**Impact:** High - Unauthorized access ke data siswa  
**Probability:** Low  
**Mitigation:**
- RBAC middleware di backend untuk semua endpoints
- Double-check permission di service layer
- Comprehensive testing untuk semua role
- Regular security audit
- Log semua access attempts

---

## 📊 Success Metrics & KPIs

### Sprint 3
- [ ] 100% user stories completed (7/7)
- [ ] Zero critical bugs in production
- [ ] NIS auto-generation success rate 100%
- [ ] Parent account creation success rate 100%
- [ ] Average data entry time < 5 menit per student

### Sprint 4
- [ ] 100% user stories completed (6/6)
- [ ] Search performance < 1 detik (95th percentile)
- [ ] Import success rate > 95%
- [ ] Export performance < 10 detik untuk 500 students
- [ ] Bulk promotion success rate 100%

### Epic Level
- [ ] Total 32 points delivered
- [ ] Code coverage 80%
- [ ] Zero data integrity issues (no duplicate NIS/NIK/NISN)
- [ ] User satisfaction score > 4.5/5 (dari UAT dengan TU & Parent)
- [ ] Parent adoption rate > 80% (orang tua login ke portal)

---

## 📝 Notes & Assumptions

### Assumptions
1. WhatsApp API account sudah disiapkan dan properly configured
2. Storage untuk foto siswa tersedia (min 10GB untuk 500 siswa)
3. TU sudah familiar dengan Excel untuk import/export
4. Orang tua memiliki smartphone untuk akses portal
5. Internet connection stable untuk WhatsApp notification
6. EPIC 1 (Authentication & Master Data) sudah 100% selesai

### Out of Scope (Epic 2)
- ❌ Student self-registration - Phase 2
- ❌ Integration dengan Dapodik - Phase 2
- ❌ Face recognition untuk foto siswa - Phase 2
- ❌ E-signature untuk dokumen - Phase 2
- ❌ Print ID Card siswa - Phase 2 (US-STD-012)
- ❌ Barcode/QR Code untuk NIS - Phase 2
- ❌ Document storage per siswa (ijazah TK, sertifikat) - Phase 2
- ❌ Advanced search dengan multiple filters kombinasi - Phase 2

### Nice to Have (Not Required for MVP)
- Email notification (selain WhatsApp)
- Bulk edit students (update multiple students at once)
- Student photo gallery
- Print student profile (PDF)
- Advanced analytics dashboard (student demographics, trends)
- Import from CSV (selain Excel)

---

## 🔄 Review & Refinement

### Sprint 3 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner, TU Staff

**Review Checklist:**
- [ ] Demo CRUD students
- [ ] Demo parent account auto-creation
- [ ] Demo upload foto
- [ ] Get feedback dari TU staff tentang UX form
- [ ] Identify improvement areas
- [ ] Adjust Sprint 4 if needed

### Sprint 4 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner, TU Staff, Sample Parents

**Review Checklist:**
- [ ] Demo complete Epic 2 functionality
- [ ] Demo parent portal ke orang tua
- [ ] User acceptance testing (UAT) dengan TU
- [ ] Test bulk operations dengan real data
- [ ] Performance review (load time, search, export)
- [ ] Security review (access control)
- [ ] Documentation complete check

---

## ✅ Epic Checklist (Before Moving to Epic 3)

### Development
- [ ] All 13 user stories implemented dan tested
- [ ] Code merged to main branch
- [ ] Database migration successful
- [ ] API documentation published
- [ ] Sample data (seed) untuk testing tersedia

### Testing
- [ ] Unit test pass (coverage 80%)
- [ ] Integration test pass
- [ ] E2E test pass untuk critical paths
- [ ] Security test pass (no critical issues)
- [ ] Performance test pass (< 2s load time)
- [ ] UAT approved by TU & sample parents

### Data Integrity
- [ ] No duplicate NIS generated
- [ ] No duplicate NIK/NISN
- [ ] Soft delete working properly
- [ ] Audit trail recording accurately
- [ ] Parent multi-child support working

### Deployment
- [ ] Deployed to staging environment
- [ ] Data migration plan ready (jika ada data existing)
- [ ] Deployed to production
- [ ] Monitoring & logging active
- [ ] WhatsApp API configured dan tested

### Documentation
- [ ] Technical documentation complete
- [ ] User manual untuk TU (Bahasa Indonesia) ready
- [ ] User manual untuk Parent Portal ready
- [ ] API documentation complete
- [ ] Database schema documented
- [ ] FAQ untuk common issues

### Handover
- [ ] Demo to TU staff
- [ ] Demo to sample parents (parent portal)
- [ ] Training session untuk TU completed
- [ ] Training video untuk parent portal (opsional)
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

**For Parent Support:**
- WhatsApp Support: [Number]
- Email: support@sekolah.app

---

**Document Status:** ✅ Ready for Sprint Planning  
**Last Review:** 13 Desember 2025  
**Next Review:** After Sprint 3 completion

---

*Catatan: Dokumen ini adalah living document dan akan diupdate seiring progress sprint. Semua perubahan akan dicatat di section "Change Log" di bawah.*

## 📋 Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 13 Des 2025 | 1.0 | Initial creation of EPIC 2 document | Zulfikar Hidayatullah |
