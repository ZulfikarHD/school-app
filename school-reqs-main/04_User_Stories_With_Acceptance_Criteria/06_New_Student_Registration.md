# User Stories: New Student Registration (PSB)

Module ini mencakup fitur pendaftaran siswa baru online (PSB = Penerimaan Siswa Baru).

---

## US-PSB-001: Formulir Pendaftaran Online (Calon Siswa/Orang Tua)

**As a** Orang Tua calon siswa  
**I want** mengisi formulir pendaftaran online  
**So that** anak saya dapat mendaftar tanpa datang ke sekolah

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua akses website sekolah  
   **When** orang tua klik "Daftar Siswa Baru"  
   **Then** sistem tampilkan form pendaftaran: Data Calon Siswa (nama, TTL, alamat), Data Orang Tua (nama, NIK, pekerjaan, no HP, email)

✅ **Given** orang tua mengisi form dengan data valid  
   **When** orang tua klik "Submit Pendaftaran"  
   **Then** sistem simpan data dan generate nomor pendaftaran, tampilkan notifikasi "Pendaftaran berhasil! Nomor pendaftaran Anda: PSB2025001"

✅ **Given** orang tua input nomor HP yang tidak valid  
   **When** orang tua submit  
   **Then** sistem tampilkan error "Format nomor HP tidak valid"

✅ **Given** pendaftaran berhasil  
   **When** sistem simpan data  
   **Then** sistem kirim email/WhatsApp konfirmasi dengan nomor pendaftaran dan langkah selanjutnya

**Notes:**
- Field wajib: nama, TTL, nama orang tua, no HP
- Auto-generate nomor pendaftaran (format: PSB[Tahun][Nomor Urut])
- Status awal: "Menunggu Verifikasi Dokumen"

---

## US-PSB-002: Upload Dokumen Pendaftaran

**As a** Orang Tua calon siswa  
**I want** upload dokumen pendukung (akte, KK, rapor)  
**So that** pendaftaran saya lengkap dan dapat diverifikasi

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua sudah submit formulir dan dapat nomor pendaftaran  
   **When** orang tua login ke portal PSB dengan nomor pendaftaran  
   **Then** sistem tampilkan halaman upload dokumen: Akte Lahir, Kartu Keluarga, KTP Orang Tua, Pas Foto, Rapor/Sertifikat (jika pindahan)

✅ **Given** orang tua upload akte lahir (file JPG, 1.5MB)  
   **When** upload selesai  
   **Then** sistem simpan file dan tampilkan preview thumbnail

✅ **Given** orang tua upload file PDF > 2MB  
   **When** sistem validasi  
   **Then** sistem tampilkan error "Ukuran file maksimal 2MB"

✅ **Given** semua dokumen wajib sudah diupload  
   **When** orang tua klik "Selesai"  
   **Then** status pendaftaran berubah "Menunggu Verifikasi Admin" dan admin dapat notifikasi

**Notes:**
- Dokumen wajib: Akte, KK, KTP Orang Tua, Pas Foto
- Dokumen opsional: Rapor (jika pindahan), Surat Pindah
- Format file: JPG, PNG, PDF (max 2MB per file)

---

## US-PSB-003: Verifikasi Dokumen Pendaftaran (Admin/TU)

**As a** TU/Admin  
**I want** verifikasi dokumen pendaftaran calon siswa  
**So that** hanya pendaftar dengan dokumen lengkap & valid yang lanjut ke tahap berikutnya

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Verifikasi PSB"  
   **When** TU lihat list pendaftar  
   **Then** sistem tampilkan list dengan status: Menunggu Verifikasi, Disetujui, Ditolak

✅ **Given** TU klik salah satu pendaftar  
   **When** TU lihat detail  
   **Then** sistem tampilkan: data pendaftar, dokumen yang diupload (dengan preview)

✅ **Given** dokumen lengkap dan valid  
   **When** TU klik "Setujui Dokumen"  
   **Then** status berubah "Dokumen Disetujui" dan pendaftar dapat notifikasi untuk lanjut ke tahap berikutnya (opsional: tes/seleksi)

✅ **Given** dokumen tidak lengkap atau tidak jelas  
   **When** TU klik "Tolak" dengan keterangan  
   **Then** status berubah "Dokumen Ditolak" dan orang tua dapat notifikasi untuk perbaiki dokumen

**Notes:**
- Notifikasi via WhatsApp/email
- TU dapat download dokumen untuk arsip
- Batch verification (verifikasi banyak sekaligus)

---

## US-PSB-004: Jadwal & Hasil Tes/Seleksi (Opsional)

**As a** TU/Admin  
**I want** mengatur jadwal tes dan input hasil tes calon siswa  
**So that** proses seleksi dapat dilakukan dengan teratur

**Priority:** Could Have (opsional untuk SD)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Jadwal Tes PSB"  
   **When** TU set tanggal tes (misal: 15 Januari 2025, pukul 08:00-10:00)  
   **Then** sistem kirim notifikasi ke semua pendaftar yang sudah disetujui dokumennya

✅ **Given** tes sudah dilaksanakan  
   **When** TU input hasil tes (Lulus/Tidak Lulus) untuk setiap pendaftar  
   **Then** sistem simpan hasil dan update status pendaftar

✅ **Given** pendaftar lulus tes  
   **When** TU save hasil  
   **Then** pendaftar dapat notifikasi "Selamat! Anda lulus seleksi" dan instruksi daftar ulang

**Notes:**
- Opsional untuk SD (biasanya tidak ada tes)
- Jika tidak ada tes, langsung ke daftar ulang setelah verifikasi dokumen

---

## US-PSB-005: Pengumuman Hasil Seleksi

**As a** Orang Tua calon siswa  
**I want** melihat hasil seleksi/pengumuman penerimaan  
**So that** saya tahu apakah anak saya diterima

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin sudah release pengumuman  
   **When** orang tua login ke portal PSB  
   **Then** sistem tampilkan status: "Selamat! Anda diterima" atau "Mohon maaf, Anda belum berhasil kali ini"

✅ **Given** pendaftar diterima  
   **When** orang tua lihat pengumuman  
   **Then** sistem tampilkan instruksi daftar ulang: tanggal, biaya, dokumen yang dibawa

✅ **Given** pengumuman belum dirilis  
   **When** orang tua cek status  
   **Then** sistem tampilkan "Pengumuman akan dirilis pada [Tanggal]"

**Notes:**
- Pengumuman bisa dilihat dengan nomor pendaftaran (tanpa login)
- Notifikasi via WhatsApp/email saat pengumuman dirilis

---

## US-PSB-006: Pembayaran Formulir Pendaftaran

**As a** Orang Tua calon siswa  
**I want** membayar biaya formulir pendaftaran  
**So that** pendaftaran saya diproses

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua sudah submit formulir  
   **When** sistem generate nomor pendaftaran  
   **Then** sistem tampilkan tagihan biaya formulir (misal: Rp 50.000) dan instruksi pembayaran

✅ **Given** orang tua bayar via transfer bank  
   **When** orang tua upload bukti transfer  
   **Then** status pembayaran berubah "Menunggu Verifikasi" dan TU dapat notifikasi

✅ **Given** TU verifikasi pembayaran  
   **When** TU konfirmasi pembayaran  
   **Then** status berubah "Lunas" dan pendaftaran diproses ke tahap berikutnya

**Notes:**
- Fase 1: manual (transfer + upload bukti)
- Fase 2: integrasi payment gateway (VA/QRIS)
- Biaya formulir configurable per tahun ajaran

---

## US-PSB-007: Daftar Ulang (Pendaftar Diterima)

**As a** Orang Tua yang anaknya diterima  
**I want** melakukan daftar ulang dan bayar uang gedung  
**So that** anak saya resmi terdaftar sebagai siswa

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua anaknya diterima  
   **When** orang tua login ke portal PSB  
   **Then** sistem tampilkan form daftar ulang: konfirmasi data, pilih kelas, upload dokumen tambahan (jika ada)

✅ **Given** orang tua submit daftar ulang  
   **When** orang tua klik "Konfirmasi Daftar Ulang"  
   **Then** sistem generate tagihan uang gedung dan instruksi pembayaran

✅ **Given** orang tua sudah bayar uang gedung dan upload bukti  
   **When** TU verifikasi pembayaran  
   **Then** status berubah "Daftar Ulang Selesai" dan data calon siswa otomatis masuk ke database siswa aktif

**Notes:**
- Generate NIS otomatis setelah daftar ulang selesai
- Kirim welcome email/WhatsApp dengan info sekolah, jadwal, dll

---

## US-PSB-008: Dashboard PSB (Admin/Kepala Sekolah)

**As a** Kepala Sekolah/TU  
**I want** melihat dashboard PSB  
**So that** saya dapat monitoring proses penerimaan siswa baru

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Dashboard PSB"  
   **When** halaman load  
   **Then** sistem tampilkan summary: total pendaftar, verifikasi pending, diterima, ditolak, daftar ulang selesai

✅ **Given** ada 15 pendaftar yang dokumen menunggu verifikasi  
   **When** dashboard load  
   **Then** card "Menunggu Verifikasi" tampilkan angka 15 dengan highlight

✅ **Given** kepala sekolah ingin lihat detail per status  
   **When** kepala sekolah klik salah satu card  
   **Then** sistem redirect ke list pendaftar dengan status tersebut

**Notes:**
- Summary: total pendaftar, funnel conversion (submit → verifikasi → diterima → daftar ulang)
- Grafik: tren pendaftar per hari/minggu
- Export data pendaftar ke Excel

---

## US-PSB-009: Konfigurasi Periode PSB

**As a** Admin/TU  
**I want** mengatur periode pendaftaran (tanggal buka & tutup)  
**So that** pendaftaran hanya bisa dilakukan pada periode yang ditentukan

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan PSB"  
   **When** admin set periode: Tanggal Buka = 1 Januari 2025, Tanggal Tutup = 31 Januari 2025  
   **Then** sistem simpan konfigurasi

✅ **Given** periode PSB sudah ditutup (setelah 31 Januari)  
   **When** orang tua coba akses form pendaftaran  
   **Then** sistem tampilkan pesan "Pendaftaran sudah ditutup. Periode pendaftaran: 1-31 Januari 2025"

✅ **Given** periode PSB belum dibuka (sebelum 1 Januari)  
   **When** orang tua coba akses  
   **Then** sistem tampilkan "Pendaftaran akan dibuka pada 1 Januari 2025"

**Notes:**
- Konfigurasi per tahun ajaran
- Set kuota pendaftar (max siswa diterima) - opsional
- Countdown timer di website

---

## US-PSB-010: Export Data Pendaftar

**As a** TU/Kepala Sekolah  
**I want** export data pendaftar ke Excel  
**So that** data dapat digunakan untuk analisis atau laporan

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Data Pendaftar"  
   **When** TU klik "Export ke Excel"  
   **Then** sistem generate file Excel dengan kolom: nomor pendaftaran, nama, TTL, nama orang tua, no HP, status, tanggal daftar

✅ **Given** TU sudah apply filter (misal: status "Diterima")  
   **When** TU export  
   **Then** file Excel hanya berisi pendaftar yang diterima

**Notes:**
- Format Excel standar
- Nama file: DataPendaftar_PSB2025_[Tanggal].xlsx

---

## Summary: New Student Registration (PSB)

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-PSB-001 | Formulir Pendaftaran | Must Have | M | 1 |
| US-PSB-002 | Upload Dokumen | Must Have | M | 1 |
| US-PSB-003 | Verifikasi Dokumen | Must Have | M | 1 |
| US-PSB-004 | Jadwal & Hasil Tes | Could Have | M | 2 |
| US-PSB-005 | Pengumuman Hasil | Must Have | S | 1 |
| US-PSB-006 | Pembayaran Formulir | Should Have | M | 1 |
| US-PSB-007 | Daftar Ulang | Must Have | M | 1 |
| US-PSB-008 | Dashboard PSB | Should Have | M | 1 |
| US-PSB-009 | Konfigurasi Periode | Must Have | S | 1 |
| US-PSB-010 | Export Data | Should Have | S | 1 |

**Total Estimation Phase 1:** 22 points (~3-4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
