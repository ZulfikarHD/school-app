# User Stories: Settings & Configuration

Module ini mencakup master data dan konfigurasi sistem yang dikelola oleh Admin/TU.

---

## US-SET-001: Master Data Kelas

**As a** Admin/TU  
**I want** mengelola data kelas (tambah, edit, nonaktifkan)  
**So that** sistem punya master data kelas yang akurat

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Master Data - Kelas"  
   **When** admin klik "Tambah Kelas"  
   **Then** sistem tampilkan form: Nama Kelas (misal: "1A"), Tingkat (1-6), Wali Kelas, Kapasitas Maksimal

✅ **Given** admin input kelas baru "3C"  
   **When** admin simpan  
   **Then** kelas tersimpan dan muncul di list kelas, dapat digunakan untuk assign siswa

✅ **Given** admin ingin nonaktifkan kelas lama (misal: kelas 6A setelah siswa lulus)  
   **When** admin ubah status jadi "Nonaktif"  
   **Then** kelas tidak muncul di dropdown pilihan tapi data historis tetap ada

**Notes:**
- Field: Nama Kelas, Tingkat, Wali Kelas (dropdown guru), Kapasitas, Ruang Kelas, Status
- Soft delete (nonaktif, tidak permanent delete)
- Validasi: nama kelas unique per tahun ajaran

---

## US-SET-002: Master Data Mata Pelajaran

**As a** Admin/TU  
**I want** mengelola data mata pelajaran  
**So that** guru dapat input nilai dan jadwal sesuai mata pelajaran yang diajar

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Master Data - Mata Pelajaran"  
   **When** admin klik "Tambah Mata Pelajaran"  
   **Then** sistem tampilkan form: Nama Mata Pelajaran, Kode (misal: MAT), KKM, Kelompok (Wajib/Muatan Lokal)

✅ **Given** admin input mata pelajaran "Bahasa Jawa" dengan KKM 65  
   **When** admin simpan  
   **Then** mata pelajaran tersimpan dan dapat digunakan untuk input nilai & jadwal

✅ **Given** admin ingin edit KKM Matematika dari 60 menjadi 65  
   **When** admin update KKM  
   **Then** sistem update KKM dan apply untuk input nilai berikutnya

**Notes:**
- Field: Nama, Kode, KKM (Kriteria Ketuntasan Minimal), Kelompok, Status
- Default mata pelajaran sesuai kurikulum K13
- KKM per mata pelajaran (bisa berbeda)

---

## US-SET-003: Master Data Tahun Ajaran & Semester

**As a** Admin/TU  
**I want** mengelola data tahun ajaran dan semester  
**So that** sistem tahu periode aktif dan dapat filter data per periode

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Master Data - Tahun Ajaran"  
   **When** admin klik "Tambah Tahun Ajaran"  
   **Then** sistem tampilkan form: Nama (misal: "2024/2025"), Tanggal Mulai, Tanggal Selesai, Status (Aktif/Nonaktif)

✅ **Given** admin set tahun ajaran "2024/2025" sebagai aktif  
   **When** admin save  
   **Then** sistem otomatis nonaktifkan tahun ajaran lainnya (hanya 1 tahun ajaran aktif)

✅ **Given** admin ingin tambah semester baru  
   **When** admin input Semester 1 (Juli-Desember 2024), Semester 2 (Januari-Juni 2025)  
   **Then** sistem simpan dan dapat digunakan untuk filter laporan/nilai

**Notes:**
- Field: Nama Tahun Ajaran, Start Date, End Date, Status, Semester (1 & 2)
- Hanya 1 tahun ajaran aktif dalam satu waktu
- Auto-switch tahun ajaran (opsional: cron job)

---

## US-SET-004: Pengaturan Umum Sekolah

**As a** Admin  
**I want** mengatur data umum sekolah (nama, alamat, logo, dll)  
**So that** informasi sekolah tampil di seluruh sistem

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Umum"  
   **When** admin update data: Nama Sekolah, Alamat, Nomor Telepon, Email, Website, Logo  
   **Then** sistem simpan dan tampilkan data tersebut di header, footer, kwitansi, rapor, dll

✅ **Given** admin upload logo sekolah baru  
   **When** admin simpan  
   **Then** logo otomatis ganti di seluruh sistem (header, kwitansi, rapor, website)

✅ **Given** admin update nomor telepon sekolah  
   **When** admin simpan  
   **Then** nomor telepon update di halaman kontak website dan footer

**Notes:**
- Field: Nama Sekolah, NPSN, Alamat, Kota, Provinsi, Kode Pos, Telepon, Email, Website, Logo
- Logo: auto-resize dan compress
- Data digunakan di: kwitansi, rapor, website, email template

---

## US-SET-005: Pengaturan Jenis Pembayaran

**As a** Admin/TU  
**I want** mengatur jenis-jenis pembayaran  
**So that** sistem support berbagai jenis pembayaran sesuai kebutuhan

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Jenis Pembayaran"  
   **When** admin klik "Tambah Jenis Pembayaran"  
   **Then** sistem tampilkan form: Nama Jenis (misal: "Uang Seragam"), Kode, Deskripsi, Tipe (One-Time/Recurring)

✅ **Given** admin input jenis baru "Uang Study Tour"  
   **When** admin simpan  
   **Then** jenis pembayaran tersimpan dan muncul di dropdown saat TU catat pembayaran

✅ **Given** admin ingin nonaktifkan jenis pembayaran lama  
   **When** admin ubah status jadi "Nonaktif"  
   **Then** jenis tidak muncul di dropdown tapi data historis tetap ada

**Notes:**
- Field: Nama, Kode, Deskripsi, Tipe (One-Time/Recurring), Nominal Default (opsional), Status
- Default: SPP, Uang Gedung, Uang Buku, Uang Seragam
- Allow admin tambah custom jenis

---

## US-SET-006: Pengaturan Jam Sekolah & Hari Libur

**As a** Admin/TU  
**I want** mengatur jam sekolah dan hari libur  
**So that** sistem tahu jadwal operasional dan dapat hitung kehadiran dengan akurat

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Jam Sekolah"  
   **When** admin set jam masuk 07:00, jam pulang 13:00, hari sekolah Senin-Sabtu  
   **Then** sistem simpan konfigurasi

✅ **Given** admin input hari libur (misal: 17 Agustus 2025 - Hari Kemerdekaan)  
   **When** admin simpan  
   **Then** sistem tandai tanggal tersebut sebagai libur dan tidak hitung dalam rekap absensi

✅ **Given** guru coba input absensi di hari libur  
   **When** guru buka halaman absensi  
   **Then** sistem tampilkan notifikasi "Hari ini libur nasional"

**Notes:**
- Jam Sekolah: Jam Masuk, Jam Pulang, Hari Operasional
- Hari Libur: input manual atau import dari API hari libur nasional
- Use case: perhitungan total hari sekolah untuk rekap absensi

---

## US-SET-007: Pengaturan Template Notifikasi

**As a** Admin  
**I want** mengatur template pesan notifikasi (WhatsApp/Email)  
**So that** notifikasi dapat disesuaikan dengan gaya komunikasi sekolah

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Template Notifikasi"  
   **When** admin pilih template "Notifikasi Absensi Alpha"  
   **Then** sistem tampilkan template dengan variable: {nama_siswa}, {tanggal}, {nama_sekolah}

✅ **Given** admin edit template menjadi: "Yth. Bapak/Ibu, kami informasikan {nama_siswa} tidak hadir hari ini ({tanggal}). Mohon konfirmasi. Salam, {nama_sekolah}."  
   **When** admin simpan  
   **Then** sistem gunakan template baru untuk notifikasi berikutnya

✅ **Given** sistem kirim notifikasi dengan template baru  
   **When** notifikasi dikirim  
   **Then** variable diganti dengan data real: {nama_siswa} → "Ahmad", {tanggal} → "13 Desember 2025"

**Notes:**
- Template untuk: Absensi, Pembayaran, Pengumuman, Rapor
- Support variable/placeholder
- Preview template sebelum save

---

## US-SET-008: Pengaturan Role & Permission (RBAC)

**As a** Admin  
**I want** mengatur hak akses setiap role  
**So that** user hanya dapat akses fitur sesuai role mereka

**Priority:** Should Have  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Role & Permission"  
   **When** admin pilih role "Guru"  
   **Then** sistem tampilkan checklist permission: Lihat Data Siswa, Input Absensi, Input Nilai, Lihat Jadwal, dll

✅ **Given** admin ingin guru tidak bisa akses modul pembayaran  
   **When** admin uncheck permission "Akses Modul Pembayaran" untuk role Guru  
   **Then** guru tidak dapat melihat menu pembayaran

✅ **Given** admin buat role custom "Wakil Kepala Sekolah"  
   **When** admin set permission: akses dashboard, lihat laporan, tidak bisa edit data  
   **Then** user dengan role tersebut hanya punya akses read-only

**Notes:**
- Default role: Kepala Sekolah, TU, Guru, Wali Kelas, Orang Tua
- Permission granular: per module, per action (create/read/update/delete)
- Allow custom role (opsional, fase 2)

---

## US-SET-009: Backup & Restore Data

**As a** Admin  
**I want** backup data sistem secara manual atau otomatis  
**So that** data aman dan dapat di-restore jika terjadi masalah

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Backup"  
   **When** admin klik "Backup Sekarang"  
   **Then** sistem generate file backup database (SQL dump) dan download otomatis

✅ **Given** admin set backup otomatis harian pukul 23:00 WIB  
   **When** admin save setting  
   **Then** sistem otomatis backup setiap pukul 23:00 dan simpan ke cloud storage atau local

✅ **Given** admin ingin restore data dari backup  
   **When** admin upload file backup dan klik "Restore"  
   **Then** sistem restore data dan tampilkan notifikasi "Data berhasil di-restore. Silakan login kembali"

**Notes:**
- Backup format: SQL dump (database) + files (foto, dokumen)
- Auto backup: harian (incremental), mingguan (full)
- Storage: cloud (Google Drive, Dropbox) atau local server
- Retention: 30 hari backup history

---

## US-SET-010: Audit Log Sistem (Activity Log)

**As a** Admin/Kepala Sekolah  
**I want** melihat log aktivitas user di sistem  
**So that** saya dapat monitoring dan audit perubahan data penting

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Audit Log"  
   **When** admin pilih filter (user, tanggal, action)  
   **Then** sistem tampilkan list log: timestamp, user, IP address, action (create/update/delete), module, old value, new value

✅ **Given** TU ubah nilai SPP siswa A dari Rp 200.000 menjadi Rp 250.000  
   **When** sistem log aktivitas  
   **Then** log mencatat: user=TU, action=update, module=payment_setting, old_value=200000, new_value=250000

✅ **Given** admin ingin export log untuk audit  
   **When** admin klik "Export"  
   **Then** sistem generate Excel dengan data log

**Notes:**
- Log untuk: login/logout, perubahan data critical (nilai, pembayaran, siswa), approval/rejection
- Retention: 6 bulan
- Search & filter: by user, by action, by module, by date range

---

## US-SET-011: Pengaturan Email & SMTP

**As a** Admin  
**I want** konfigurasi email setting (SMTP)  
**So that** sistem dapat kirim email notifikasi

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Email"  
   **When** admin input: SMTP Host, Port, Username, Password, From Email, From Name  
   **Then** sistem simpan konfigurasi

✅ **Given** admin ingin test email setting  
   **When** admin klik "Kirim Test Email"  
   **Then** sistem kirim email ke alamat admin dan tampilkan notifikasi "Email test berhasil dikirim"

✅ **Given** email setting salah (password salah)  
   **When** sistem coba kirim email  
   **Then** tampilkan error "Gagal kirim email. Periksa konfigurasi SMTP"

**Notes:**
- Support: Gmail SMTP, Custom SMTP, Transactional Email (SendGrid, Mailgun)
- Test email untuk validasi setting
- Secure: password di-encrypt

---

## US-SET-012: Konfigurasi Payment Gateway (Phase 2)

**As a** Admin  
**I want** konfigurasi payment gateway (Midtrans/Xendit)  
**So that** orang tua dapat bayar SPP online

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan - Payment Gateway"  
   **When** admin pilih provider (Midtrans/Xendit), input API Key, Merchant ID  
   **Then** sistem simpan konfigurasi

✅ **Given** admin aktifkan payment gateway  
   **When** admin toggle "Aktifkan Payment Gateway" ON  
   **Then** orang tua dapat opsi "Bayar Online" di portal

✅ **Given** admin ingin test payment gateway  
   **When** admin klik "Test Transaksi"  
   **Then** sistem buat transaksi dummy dan validasi koneksi dengan payment gateway

**Notes:**
- Provider: Midtrans, Xendit, Doku (pilih yang cost-effective)
- Mode: Sandbox (test) dan Production
- Fee setting: ditanggung sekolah atau orang tua (configurable)

---

## Summary: Settings & Configuration

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-SET-001 | Master Data Kelas | Must Have | S | 1 |
| US-SET-002 | Master Data Mapel | Must Have | S | 1 |
| US-SET-003 | Tahun Ajaran & Semester | Must Have | M | 1 |
| US-SET-004 | Pengaturan Umum Sekolah | Must Have | M | 1 |
| US-SET-005 | Jenis Pembayaran | Must Have | S | 1 |
| US-SET-006 | Jam Sekolah & Libur | Should Have | M | 1 |
| US-SET-007 | Template Notifikasi | Could Have | M | 2 |
| US-SET-008 | Role & Permission | Should Have | L | 1 |
| US-SET-009 | Backup & Restore | Should Have | M | 1 |
| US-SET-010 | Audit Log | Should Have | M | 1 |
| US-SET-011 | Email & SMTP | Should Have | S | 1 |
| US-SET-012 | Payment Gateway Config | Could Have | M | 2 |

**Total Estimation Phase 1:** 23 points (~3-4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
