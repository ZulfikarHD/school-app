# User Stories: Teacher Management

Module ini mencakup fitur manajemen data guru, jadwal mengajar, honor, dan evaluasi guru.

---

## US-TCH-001: Tambah & Edit Data Guru

**As a** TU/Admin  
**I want** menambah dan mengedit data guru  
**So that** data guru tersimpan lengkap di sistem

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Guru"  
   **When** TU klik "Tambah Guru Baru"  
   **Then** sistem tampilkan form: NIP/NIK, Nama, TTL, Jenis Kelamin, Alamat, No HP, Email, Status (Tetap/Honorer), Mata Pelajaran yang diajar

✅ **Given** TU mengisi data guru baru dengan lengkap  
   **When** TU klik "Simpan"  
   **Then** sistem simpan data dan otomatis create akun login untuk guru (username: NIP, password auto-generated)

✅ **Given** TU ingin edit data guru (misal: update nomor HP)  
   **When** TU klik "Edit" dan update data  
   **Then** sistem update data dan tampilkan notifikasi "Data guru berhasil diupdate"

**Notes:**
- Field wajib: NIP/NIK, Nama, No HP, Status
- Status: Guru Tetap, Guru Honorer
- Auto-create akun login untuk guru
- Upload foto guru (opsional)

---

## US-TCH-002: Lihat Profil Guru

**As a** TU/Kepala Sekolah  
**I want** melihat profil lengkap guru  
**So that** saya dapat melihat informasi detail guru termasuk jadwal, honor, evaluasi

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user di halaman "Data Guru"  
   **When** user klik nama guru  
   **Then** sistem tampilkan profil guru dengan tab: Info Pribadi, Jadwal Mengajar, Rekap Presensi, Rekap Honor, Evaluasi

✅ **Given** user di tab "Info Pribadi"  
   **When** halaman load  
   **Then** sistem tampilkan: foto, NIP, nama, TTL, alamat, no HP, email, mata pelajaran, status

✅ **Given** user di tab "Jadwal Mengajar"  
   **When** halaman load  
   **Then** sistem tampilkan jadwal mingguan guru (hari, jam, kelas, mata pelajaran)

**Notes:**
- Mobile-friendly
- Quick action: Edit Data, Lihat Presensi, Hitung Honor

---

## US-TCH-003: Input Jadwal Mengajar Guru

**As a** TU/Admin  
**I want** input jadwal mengajar guru  
**So that** sistem tahu guru mengajar apa, dimana, dan kapan

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Jadwal Mengajar"  
   **When** TU pilih guru dan klik "Tambah Jadwal"  
   **Then** sistem tampilkan form: Hari, Jam Mulai, Jam Selesai, Kelas, Mata Pelajaran

✅ **Given** TU input jadwal: Senin, 08:00-09:30, Kelas 3A, Matematika  
   **When** TU simpan  
   **Then** sistem simpan jadwal dan tampilkan di calendar view

✅ **Given** ada konflik jadwal (guru sudah ada jadwal di jam yang sama)  
   **When** TU coba simpan  
   **Then** sistem tampilkan warning "Guru sudah ada jadwal di jam ini"

**Notes:**
- Calendar view untuk visualisasi jadwal
- Deteksi konflik jadwal (bentrok)
- Export jadwal ke PDF untuk dicetak

---

## US-TCH-004: Rekap Jam Mengajar (Untuk Hitung Honor)

**As a** TU/Admin  
**I want** melihat rekap jam mengajar guru per bulan  
**So that** saya dapat menghitung honor guru

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Rekap Jam Mengajar"  
   **When** TU pilih bulan dan guru  
   **Then** sistem tampilkan rekap: total jam mengajar, breakdown per mata pelajaran, total jam ekstra (ekskul/pengganti)

✅ **Given** guru A mengajar 80 jam di bulan Januari (60 jam regular + 20 jam ekstra)  
   **When** sistem hitung  
   **Then** sistem tampilkan: Total Jam = 80, Honor = (60 × Rp 50.000) + (20 × Rp 75.000) = Rp 4.500.000

✅ **Given** TU ingin export rekap untuk payroll  
   **When** TU klik "Export Payroll"  
   **Then** sistem generate Excel dengan kolom: nama guru, total jam, total honor

**Notes:**
- Honor per jam configurable (berbeda untuk tetap/honorer, regular/ekstra)
- Integrasi dengan presensi guru (hanya hitung jam saat guru hadir)
- Export untuk akuntan/payroll

---

## US-TCH-005: Set Honor Guru

**As a** Admin/TU  
**I want** mengatur tarif honor guru per jam  
**So that** perhitungan honor otomatis dan akurat

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan Honor"  
   **When** admin set tarif: Guru Tetap = Rp 50.000/jam, Guru Honorer = Rp 60.000/jam, Jam Ekstra = Rp 75.000/jam  
   **Then** sistem simpan konfigurasi

✅ **Given** guru A adalah guru tetap  
   **When** sistem hitung honor  
   **Then** sistem pakai tarif Rp 50.000/jam

✅ **Given** ada guru dengan tarif khusus (misal: guru senior)  
   **When** admin set tarif custom untuk guru tersebut  
   **Then** sistem pakai tarif custom, override tarif default

**Notes:**
- Tarif bisa per kategori (tetap/honorer) atau per guru (custom)
- History perubahan tarif untuk audit

---

## US-TCH-006: Evaluasi Guru (Oleh Kepala Sekolah)

**As a** Kepala Sekolah  
**I want** memberikan evaluasi untuk guru  
**So that** performa guru dapat dinilai dan di-improve

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman profil guru  
   **When** kepala sekolah klik "Buat Evaluasi"  
   **Then** sistem tampilkan form evaluasi: Periode, Aspek Penilaian (Pedagogik, Kepribadian, Sosial, Profesional), Nilai (1-5), Catatan

✅ **Given** kepala sekolah isi evaluasi untuk guru A  
   **When** kepala sekolah simpan  
   **Then** evaluasi tersimpan dan dapat dilihat di profil guru (read-only untuk guru)

✅ **Given** guru ingin melihat evaluasi dirinya  
   **When** guru login dan akses profil sendiri  
   **Then** sistem tampilkan riwayat evaluasi (tanpa nama penilai, hanya hasil & catatan)

**Notes:**
- Aspek penilaian sesuai standar guru (4 kompetensi)
- Skala nilai: 1-5 atau A-E
- Confidential: hanya kepala sekolah & admin yang bisa lihat semua evaluasi

---

## US-TCH-007: Survei Kepuasan (Orang Tua/Siswa ke Guru)

**As a** Orang Tua/Siswa  
**I want** memberikan feedback untuk guru  
**So that** sekolah tahu bagaimana kualitas pengajaran

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** ada survei kepuasan guru (misal: akhir semester)  
   **Then** sistem tampilkan form survei: pilih guru, rating (1-5 bintang), komentar opsional

✅ **Given** orang tua isi survei untuk guru Matematika (rating 4)  
   **When** orang tua submit  
   **Then** survei tersimpan (anonim) dan masuk ke aggregate data

✅ **Given** kepala sekolah ingin lihat hasil survei  
   **When** kepala sekolah buka laporan survei  
   **Then** sistem tampilkan rata-rata rating per guru dan feedback summary

**Notes:**
- Survei anonim (nama penilai tidak ditampilkan ke guru)
- Periode survei: akhir semester atau sesuai kebutuhan
- Opsional untuk fase 2

---

## US-TCH-008: Nonaktifkan Guru (Resign/Pensiun)

**As a** TU/Admin  
**I want** menonaktifkan data guru yang resign/pensiun  
**So that** data historis tetap ada tapi guru tidak muncul di daftar aktif

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman profil guru  
   **When** TU klik "Nonaktifkan Guru"  
   **Then** sistem tampilkan konfirmasi "Yakin ingin menonaktifkan guru ini?"

✅ **Given** TU konfirmasi  
   **When** TU klik "Ya"  
   **Then** status guru berubah "Tidak Aktif", akun login di-disable, guru tidak muncul di daftar aktif

✅ **Given** TU ingin lihat data guru nonaktif  
   **When** TU filter status "Tidak Aktif"  
   **Then** sistem tampilkan list guru yang sudah resign/pensiun

**Notes:**
- Soft delete (tidak permanent delete)
- Data historis (presensi, honor, evaluasi) tetap tersimpan
- Akun login otomatis nonaktif

---

## US-TCH-009: Export Data Guru

**As a** TU/Kepala Sekolah  
**I want** export data guru ke Excel  
**So that** data dapat digunakan untuk laporan atau backup

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Guru"  
   **When** TU klik "Export ke Excel"  
   **Then** sistem generate file Excel dengan kolom: NIP, nama, TTL, no HP, email, mata pelajaran, status, tanggal bergabung

✅ **Given** TU sudah apply filter (misal: status "Aktif")  
   **When** TU export  
   **Then** file Excel hanya berisi guru aktif

**Notes:**
- Format Excel standar
- Nama file: DataGuru_[Tanggal].xlsx

---

## US-TCH-010: Dashboard Teacher Management

**As a** Kepala Sekolah  
**I want** melihat dashboard guru  
**So that** saya dapat quick overview data guru

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Dashboard Guru"  
   **When** halaman load  
   **Then** sistem tampilkan card summary: total guru aktif, guru tetap, guru honorer, rata-rata evaluasi, guru dengan presensi buruk

✅ **Given** ada 2 guru dengan presensi < 80% bulan ini  
   **When** dashboard load  
   **Then** card "Presensi Buruk" tampilkan angka 2 dengan highlight merah

✅ **Given** kepala sekolah ingin lihat detail  
   **When** kepala sekolah klik salah satu card  
   **Then** sistem redirect ke halaman detail

**Notes:**
- Summary: total guru, breakdown status, presensi rata-rata, evaluasi rata-rata
- Quick link: Tambah Guru, Jadwal, Rekap Honor

---

## Summary: Teacher Management

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-TCH-001 | CRUD Data Guru | Must Have | M | 1 |
| US-TCH-002 | Profil Guru | Must Have | M | 1 |
| US-TCH-003 | Jadwal Mengajar | Should Have | M | 1 |
| US-TCH-004 | Rekap Jam & Honor | Must Have | M | 1 |
| US-TCH-005 | Set Honor | Must Have | S | 1 |
| US-TCH-006 | Evaluasi Guru | Should Have | M | 1 |
| US-TCH-007 | Survei Kepuasan | Could Have | M | 2 |
| US-TCH-008 | Nonaktifkan Guru | Must Have | S | 1 |
| US-TCH-009 | Export Data | Should Have | S | 1 |
| US-TCH-010 | Dashboard Guru | Should Have | M | 1 |

**Total Estimation Phase 1:** 23 points (~3-4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
