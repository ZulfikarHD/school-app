# User Stories: Student Management

Module ini mencakup semua fitur terkait manajemen data siswa, profil, dan data keluarga.

---

## US-STD-001: Tambah Data Siswa Baru

**As a** TU/Admin  
**I want** menambahkan data siswa baru ke sistem  
**So that** data siswa tersimpan dan dapat digunakan untuk operasional sekolah

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Tambah Siswa Baru"  
   **Then** sistem menampilkan form input data siswa dengan field: NIS, NISN, Nama Lengkap, Tempat Lahir, Tanggal Lahir, Jenis Kelamin, Agama, Alamat, Kelas, Tahun Ajaran

✅ **Given** TU mengisi semua field wajib dengan data valid  
   **When** TU klik "Simpan"  
   **Then** sistem simpan data siswa dan tampilkan notifikasi "Data siswa berhasil ditambahkan"

✅ **Given** TU input NIS/NISN yang sudah ada di sistem  
   **When** TU klik "Simpan"  
   **Then** sistem tampilkan error "NIS/NISN sudah terdaftar"

✅ **Given** TU input tanggal lahir dengan format salah  
   **When** TU klik "Simpan"  
   **Then** sistem tampilkan error "Format tanggal harus DD/MM/YYYY"

**Notes:**
- Field wajib: NIS, Nama, Tanggal Lahir, Jenis Kelamin, Kelas
- Auto-generate NIS jika kosong (format: [Tahun][Nomor Urut], contoh: 2025001)
- Upload foto siswa (opsional)

---

## US-STD-002: Edit Data Siswa

**As a** TU/Admin  
**I want** mengedit data siswa yang sudah ada  
**So that** data siswa selalu update dan akurat

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Edit" pada siswa tertentu  
   **Then** sistem tampilkan form edit dengan data siswa yang sudah terisi

✅ **Given** TU mengubah data siswa (misal: alamat baru)  
   **When** TU klik "Simpan Perubahan"  
   **Then** sistem update data dan tampilkan notifikasi "Data berhasil diupdate"

✅ **Given** TU mengubah data yang tidak valid (misal: email format salah)  
   **When** TU klik "Simpan"  
   **Then** sistem tampilkan error validasi sebelum data tersimpan

**Notes:**
- Log perubahan data (audit trail): who, when, what changed
- Tidak boleh edit NIS/NISN setelah siswa sudah punya transaksi pembayaran

---

## US-STD-003: Hapus/Nonaktifkan Data Siswa

**As a** TU/Admin  
**I want** menonaktifkan data siswa yang sudah lulus/pindah  
**So that** data historis tetap ada tapi tidak muncul di daftar aktif

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Nonaktifkan" pada siswa yang sudah lulus  
   **Then** sistem tampilkan konfirmasi "Yakin ingin menonaktifkan siswa ini?"

✅ **Given** TU konfirmasi nonaktifkan siswa  
   **When** TU klik "Ya"  
   **Then** status siswa berubah menjadi "Tidak Aktif" dan tidak muncul di daftar siswa aktif

✅ **Given** TU ingin melihat data siswa yang tidak aktif  
   **When** TU filter status "Tidak Aktif"  
   **Then** sistem tampilkan list siswa yang sudah lulus/pindah

✅ **Given** TU ingin reaktivasi siswa (misal: siswa kembali setelah pindah)  
   **When** TU klik "Aktifkan Kembali"  
   **Then** status siswa kembali "Aktif" dan muncul di daftar siswa aktif

**Notes:**
- Soft delete (tidak permanent delete dari database)
- Data historis (pembayaran, nilai) tetap tersimpan

---

## US-STD-004: Lihat Detail Profil Siswa

**As a** TU/Guru/Kepala Sekolah  
**I want** melihat profil lengkap siswa  
**So that** saya dapat melihat informasi detail tentang siswa

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user di halaman "Data Siswa"  
   **When** user klik nama siswa atau tombol "Lihat Detail"  
   **Then** sistem tampilkan halaman profil siswa dengan tab: Info Pribadi, Data Orang Tua, Riwayat Absensi, Riwayat Pembayaran, Riwayat Nilai

✅ **Given** user di tab "Info Pribadi"  
   **When** halaman load  
   **Then** sistem tampilkan: foto siswa, NIS, NISN, nama, TTL, jenis kelamin, agama, alamat, kelas saat ini

✅ **Given** user di tab "Data Orang Tua"  
   **When** halaman load  
   **Then** sistem tampilkan: nama ayah/ibu/wali, pekerjaan, no HP, email, alamat (jika berbeda)

✅ **Given** user dengan role "Guru"  
   **When** guru akses profil siswa  
   **Then** guru hanya dapat melihat info yang relevan (tidak ada data pembayaran)

**Notes:**
- Data sensitif (pembayaran) hanya untuk TU/Kepala Sekolah
- Mobile-responsive (guru sering akses via HP)

---

## US-STD-005: Tambah/Edit Data Orang Tua/Wali

**As a** TU/Admin  
**I want** menambah dan mengedit data orang tua/wali siswa  
**So that** sistem dapat menghubungi orang tua untuk keperluan administrasi

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman edit siswa, tab "Data Orang Tua"  
   **When** TU klik "Tambah Data Orang Tua"  
   **Then** sistem tampilkan form: Hubungan (Ayah/Ibu/Wali), Nama, NIK, Pekerjaan, No HP, Email, Alamat

✅ **Given** TU mengisi data orang tua dengan nomor HP valid  
   **When** TU klik "Simpan"  
   **Then** sistem simpan data dan otomatis create akun orang tua untuk portal (username: no HP, password auto-generated)

✅ **Given** satu siswa punya 2 orang tua (ayah & ibu)  
   **When** TU tambah data keduanya  
   **Then** sistem allow multiple parent account untuk 1 siswa

✅ **Given** TU input nomor HP yang sudah terdaftar (orang tua punya 2 anak di sekolah yang sama)  
   **When** TU simpan data  
   **Then** sistem link data siswa ke akun orang tua yang sudah ada (tidak create duplicate account)

**Notes:**
- No HP wajib (untuk WhatsApp notification)
- Email opsional
- Auto-create account untuk portal orang tua

---

## US-STD-006: Upload Foto Siswa

**As a** TU/Admin  
**I want** upload foto siswa  
**So that** data siswa lebih lengkap dan mudah dikenali

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman edit siswa  
   **When** TU klik area upload foto  
   **Then** sistem buka file picker untuk pilih foto (format: JPG, PNG)

✅ **Given** TU pilih foto dengan ukuran > 2MB  
   **When** TU upload foto  
   **Then** sistem auto-compress foto menjadi max 500KB tanpa menurunkan kualitas signifikan

✅ **Given** TU upload foto yang valid  
   **When** foto berhasil diupload  
   **Then** sistem tampilkan preview foto dan simpan ke storage

✅ **Given** TU upload file bukan gambar (misal: PDF, DOC)  
   **When** TU coba upload  
   **Then** sistem tampilkan error "Format file harus JPG atau PNG"

**Notes:**
- Foto disimpan dengan naming: NIS_namasiswa.jpg
- Compress otomatis untuk hemat bandwidth
- Fallback: default avatar jika tidak ada foto

---

## US-STD-007: Filter dan Pencarian Siswa

**As a** TU/Guru/Kepala Sekolah  
**I want** filter dan mencari siswa dengan mudah  
**So that** saya dapat menemukan data siswa dengan cepat

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user di halaman "Data Siswa"  
   **When** user ketik nama siswa di search box  
   **Then** sistem tampilkan hasil search secara real-time (autocomplete)

✅ **Given** user ingin filter siswa berdasarkan kelas  
   **When** user pilih filter "Kelas 3A"  
   **Then** sistem tampilkan hanya siswa kelas 3A

✅ **Given** user ingin filter siswa berdasarkan status  
   **When** user pilih filter "Status: Aktif"  
   **Then** sistem tampilkan hanya siswa dengan status aktif

✅ **Given** user ingin kombinasi filter (misal: Kelas 4B + Status Aktif)  
   **When** user apply multiple filters  
   **Then** sistem tampilkan hasil yang sesuai dengan semua filter

**Notes:**
- Filter options: Kelas, Status (Aktif/Tidak Aktif), Jenis Kelamin, Tahun Ajaran
- Search by: Nama, NIS, NISN
- Pagination untuk data besar (> 50 siswa per page)

---

## US-STD-008: Export Data Siswa ke Excel

**As a** TU/Kepala Sekolah  
**I want** export data siswa ke Excel  
**So that** saya dapat menggunakan data untuk keperluan laporan atau backup

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik tombol "Export ke Excel"  
   **Then** sistem generate file Excel (.xlsx) dengan semua data siswa sesuai filter yang aktif

✅ **Given** file Excel berhasil di-generate  
   **When** TU buka file  
   **Then** file berisi kolom: NIS, NISN, Nama, TTL, Jenis Kelamin, Agama, Kelas, Status, Nama Orang Tua, No HP Orang Tua

✅ **Given** TU sudah apply filter (misal: hanya kelas 5)  
   **When** TU export  
   **Then** file Excel hanya berisi data siswa kelas 5 (sesuai filter)

**Notes:**
- Format Excel standar dengan header row
- Nama file: DataSiswa_[TanggalExport].xlsx
- Max 1000 rows per export (untuk performa)

---

## US-STD-009: Import Data Siswa dari Excel

**As a** TU/Admin  
**I want** import data siswa dari Excel  
**So that** saya tidak perlu input manual satu per satu (bulk insert)

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU klik "Import dari Excel"  
   **Then** sistem tampilkan instruksi dan link download template Excel

✅ **Given** TU download template dan isi data siswa  
   **When** TU upload file Excel yang valid  
   **Then** sistem validasi data dan tampilkan preview data yang akan diimport

✅ **Given** ada data yang tidak valid (misal: format tanggal salah, NIS duplicate)  
   **When** sistem validasi  
   **Then** sistem tampilkan list error dengan nomor baris yang bermasalah

✅ **Given** semua data valid  
   **When** TU klik "Proses Import"  
   **Then** sistem insert data ke database dan tampilkan notifikasi "X siswa berhasil ditambahkan"

**Notes:**
- Template Excel dengan contoh data dan format
- Validasi di frontend & backend
- Max 100 rows per import (untuk performa)

---

## US-STD-010: Pindah Kelas Siswa (Naik Kelas)

**As a** TU/Admin  
**I want** memindahkan siswa ke kelas lain (naik kelas atau mutasi)  
**So that** data kelas siswa selalu update setiap tahun ajaran

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Siswa"  
   **When** TU pilih multiple siswa dan klik "Pindah Kelas"  
   **Then** sistem tampilkan form: Kelas Tujuan, Tahun Ajaran Baru

✅ **Given** TU pilih siswa kelas 1A (10 siswa) untuk naik ke kelas 2A  
   **When** TU submit  
   **Then** sistem update kelas semua siswa menjadi 2A untuk tahun ajaran baru

✅ **Given** TU ingin bulk naik kelas semua siswa di akhir tahun ajaran  
   **When** TU klik "Naik Kelas Otomatis"  
   **Then** sistem tampilkan konfirmasi dan daftar perubahan (Kelas 1 → Kelas 2, dst)

✅ **Given** TU konfirmasi naik kelas otomatis  
   **When** TU klik "Ya, Proses"  
   **Then** sistem update semua siswa aktif ke kelas berikutnya dan create data tahun ajaran baru

**Notes:**
- Histori kelas disimpan (untuk laporan historis)
- Siswa kelas 6 yang naik kelas → status otomatis "Lulus"
- Fitur bulk update untuk efisiensi

---

## US-STD-011: Lihat Data Siswa (Portal Orang Tua)

**As a** Orang Tua  
**I want** melihat data anak saya yang terdaftar  
**So that** saya dapat memantau informasi anak saya di sekolah

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua masuk ke dashboard  
   **Then** sistem tampilkan card/list anak yang terdaftar (jika punya > 1 anak di sekolah yang sama)

✅ **Given** orang tua klik salah satu anak  
   **When** halaman profil anak load  
   **Then** sistem tampilkan: foto, nama, NIS, kelas, wali kelas, info umum

✅ **Given** orang tua ingin lihat riwayat absensi anak  
   **When** orang tua klik tab "Absensi"  
   **Then** sistem tampilkan rekap absensi bulanan: hadir, sakit, izin, alpha

✅ **Given** orang tua ingin lihat riwayat pembayaran  
   **When** orang tua klik tab "Pembayaran"  
   **Then** sistem tampilkan status pembayaran SPP dan tagihan lain

**Notes:**
- Read-only untuk orang tua (tidak bisa edit)
- Mobile-friendly (mayoritas orang tua pakai HP)
- Data real-time (tidak delay)

---

## US-STD-012: Generate Kartu Siswa/ID Card

**As a** TU/Admin  
**I want** generate dan print kartu siswa  
**So that** setiap siswa memiliki ID card sekolah

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman profil siswa  
   **When** TU klik "Generate Kartu Siswa"  
   **Then** sistem generate PDF kartu siswa dengan: foto, nama, NIS, kelas, alamat, barcode/QR code

✅ **Given** TU ingin print kartu untuk semua siswa kelas 1  
   **When** TU pilih multiple siswa dan klik "Print Kartu"  
   **Then** sistem generate PDF dengan multiple kartu (format A4, 2 kartu per halaman)

✅ **Given** kartu siswa berhasil di-generate  
   **When** TU scan barcode/QR code di kartu  
   **Then** sistem redirect ke profil siswa (untuk absensi cepat, dll)

**Notes:**
- Template desain kartu (customizable per sekolah)
- QR code berisi: NIS, Nama, Kelas
- Opsional untuk fase 2

---

## Summary: Student Management

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-STD-001 | Tambah Data Siswa | Must Have | M | 1 |
| US-STD-002 | Edit Data Siswa | Must Have | S | 1 |
| US-STD-003 | Hapus/Nonaktifkan Siswa | Must Have | S | 1 |
| US-STD-004 | Lihat Detail Profil | Must Have | M | 1 |
| US-STD-005 | Data Orang Tua | Must Have | M | 1 |
| US-STD-006 | Upload Foto Siswa | Should Have | S | 1 |
| US-STD-007 | Filter & Search | Must Have | M | 1 |
| US-STD-008 | Export ke Excel | Should Have | S | 1 |
| US-STD-009 | Import dari Excel | Should Have | M | 1 |
| US-STD-010 | Pindah Kelas/Naik Kelas | Must Have | M | 1 |
| US-STD-011 | Portal Orang Tua | Must Have | M | 1 |
| US-STD-012 | Generate Kartu Siswa | Could Have | M | 2 |

**Total Estimation Phase 1:** 26 points (~4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
