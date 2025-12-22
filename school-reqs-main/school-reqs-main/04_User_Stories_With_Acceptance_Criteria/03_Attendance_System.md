# User Stories: Attendance System

Module ini mencakup fitur absensi siswa dan guru (presensi).

---

## US-ATT-001: Input Absensi Harian Pagi (Siswa)

**As a** Guru/Wali Kelas  
**I want** input absensi harian siswa di pagi hari  
**So that** kehadiran siswa tercatat dengan akurat

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru login dan di dashboard  
   **When** guru klik menu "Absensi Harian"  
   **Then** sistem tampilkan list kelas yang diajar guru dan tanggal hari ini

✅ **Given** guru pilih kelas dan tanggal  
   **When** guru klik "Input Absensi"  
   **Then** sistem tampilkan list semua siswa di kelas tersebut dengan status default "Hadir"

✅ **Given** guru ubah status siswa yang sakit menjadi "Sakit"  
   **When** guru klik "Simpan Absensi"  
   **Then** sistem simpan data absensi dan tampilkan notifikasi "Absensi berhasil disimpan"

✅ **Given** guru sudah input absensi untuk hari ini  
   **When** guru coba input lagi  
   **Then** sistem tampilkan data yang sudah diinput (mode edit, bukan create baru)

**Notes:**
- Status absensi: Hadir, Sakit, Izin, Alpha
- Default semua siswa: Hadir (untuk mempercepat input)
- Mobile-friendly (guru sering input via HP)
- Timestamp otomatis (waktu input absensi)

---

## US-ATT-002: Input Absensi Per Mata Pelajaran (Siswa)

**As a** Guru Mata Pelajaran  
**I want** input absensi siswa per mata pelajaran yang saya ajar  
**So that** kehadiran siswa di setiap pelajaran tercatat

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru di halaman "Absensi Per Pelajaran"  
   **When** guru pilih kelas, mata pelajaran, dan tanggal  
   **Then** sistem tampilkan list siswa dengan status default "Hadir"

✅ **Given** guru input absensi untuk Matematika kelas 3A  
   **When** guru simpan  
   **Then** data absensi tersimpan dengan metadata: kelas, mata pelajaran, tanggal, jam pelajaran

✅ **Given** siswa tidak hadir di mata pelajaran tertentu (misal: izin dari pelajaran ke-3 karena sakit)  
   **When** guru ubah status jadi "Izin"  
   **Then** sistem catat absensi khusus untuk mata pelajaran tersebut (tidak affect absensi harian)

**Notes:**
- Opsional untuk SD (bisa hanya pakai absensi harian)
- Berguna untuk sekolah yang strict tracking per mata pelajaran
- Integrasi dengan jadwal pelajaran (future)

---

## US-ATT-003: Ajukan Izin/Surat Sakit (Orang Tua)

**As a** Orang Tua  
**I want** mengajukan izin atau upload surat sakit untuk anak saya  
**So that** ketidakhadiran anak tercatat resmi dan tidak dianggap alpha

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua klik "Ajukan Izin/Sakit"  
   **Then** sistem tampilkan form: Nama Anak, Tanggal, Keterangan, Upload Dokumen (opsional)

✅ **Given** orang tua isi form izin untuk besok (anak sakit)  
   **When** orang tua submit  
   **Then** sistem simpan pengajuan dengan status "Menunggu Verifikasi" dan kirim notifikasi ke wali kelas

✅ **Given** wali kelas menerima notifikasi izin  
   **When** wali kelas buka notifikasi dan klik "Terima"  
   **Then** status izin berubah "Disetujui" dan otomatis update absensi siswa untuk tanggal tersebut menjadi "Izin/Sakit"

✅ **Given** orang tua upload foto surat dokter  
   **When** file berhasil diupload  
   **Then** file tersimpan dan dapat diakses oleh guru/TU untuk verifikasi

**Notes:**
- Upload file: JPG, PNG, PDF (max 2MB)
- Notifikasi real-time ke wali kelas (via WhatsApp/email)
- Status: Menunggu / Disetujui / Ditolak

---

## US-ATT-004: Verifikasi Izin/Sakit (Guru/TU)

**As a** Wali Kelas/TU  
**I want** verifikasi pengajuan izin/sakit dari orang tua  
**So that** data absensi akurat dan dapat dipercaya

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** guru di halaman "Pengajuan Izin/Sakit"  
   **When** guru lihat list pengajuan  
   **Then** sistem tampilkan list dengan status: Menunggu, Disetujui, Ditolak

✅ **Given** guru klik salah satu pengajuan  
   **When** guru lihat detail  
   **Then** sistem tampilkan: nama siswa, tanggal, keterangan, dokumen pendukung (jika ada)

✅ **Given** guru setujui pengajuan izin  
   **When** guru klik "Setujui"  
   **Then** status berubah "Disetujui" dan absensi siswa untuk tanggal tersebut otomatis update jadi "Izin/Sakit"

✅ **Given** guru tolak pengajuan (misal: alasan tidak jelas)  
   **When** guru klik "Tolak" dan isi alasan  
   **Then** status berubah "Ditolak" dan orang tua dapat notifikasi dengan alasan penolakan

**Notes:**
- Auto-approve untuk sakit dengan surat dokter (opsional)
- Notifikasi ke orang tua setelah approval/rejection

---

## US-ATT-005: Lihat Rekap Absensi Siswa (Bulanan)

**As a** Guru/TU/Kepala Sekolah  
**I want** melihat rekap absensi siswa per bulan  
**So that** saya dapat monitoring kedisiplinan siswa

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user di halaman "Rekap Absensi"  
   **When** user pilih bulan dan kelas  
   **Then** sistem tampilkan tabel rekap: nama siswa, total hadir, sakit, izin, alpha, persentase kehadiran

✅ **Given** siswa A punya alpha > 3 kali dalam sebulan  
   **When** sistem generate rekap  
   **Then** baris siswa A ditandai merah (highlight) sebagai warning

✅ **Given** user ingin lihat detail absensi harian siswa tertentu  
   **When** user klik nama siswa  
   **Then** sistem tampilkan calendar view dengan detail absensi per hari

✅ **Given** user ingin export rekap absensi ke Excel  
   **When** user klik "Export"  
   **Then** sistem generate file Excel dengan data rekap absensi

**Notes:**
- Persentase kehadiran: (Total Hadir / Total Hari Sekolah) × 100%
- Warning untuk siswa dengan alpha > 3x atau kehadiran < 80%
- Filter: per kelas, per bulan, per tahun ajaran

---

## US-ATT-006: Lihat Absensi Anak (Portal Orang Tua)

**As a** Orang Tua  
**I want** melihat rekap absensi anak saya  
**So that** saya dapat memantau kedisiplinan anak

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua klik tab "Absensi" pada profil anak  
   **Then** sistem tampilkan rekap absensi bulan ini: total hadir, sakit, izin, alpha, persentase kehadiran

✅ **Given** orang tua ingin lihat detail per hari  
   **When** orang tua scroll ke bawah  
   **Then** sistem tampilkan list absensi harian (tanggal, status, keterangan jika ada)

✅ **Given** anak alpha 2x dalam sebulan  
   **When** orang tua lihat rekap  
   **Then** sistem tampilkan notifikasi warning di bagian atas: "Anak Anda memiliki 2x ketidakhadiran tanpa keterangan"

**Notes:**
- Real-time data (sinkron dengan input guru)
- Mobile-friendly (mayoritas orang tua pakai HP)
- Push notification jika anak alpha (opsional)

---

## US-ATT-007: Input Presensi Guru (Manual Digital)

**As a** Guru  
**I want** input presensi kehadiran saya sendiri  
**So that** kehadiran saya tercatat di sistem

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru login ke sistem di pagi hari  
   **When** guru klik "Presensi Masuk"  
   **Then** sistem catat timestamp dan lokasi (jika allow location access) sebagai waktu masuk

✅ **Given** guru selesai jam mengajar  
   **When** guru klik "Presensi Pulang"  
   **Then** sistem catat timestamp sebagai waktu pulang dan hitung total jam kerja

✅ **Given** guru lupa presensi masuk di pagi hari  
   **When** guru coba presensi siang hari  
   **Then** sistem tampilkan warning "Anda belum presensi masuk" dan allow presensi dengan keterangan terlambat

✅ **Given** guru di luar area sekolah (> 100m radius)  
   **When** guru coba presensi  
   **Then** sistem tampilkan warning "Anda di luar area sekolah" tapi tetap allow presensi dengan flag "Out of Range"

**Notes:**
- Geolocation tracking (opsional, bisa diaktifkan/nonaktifkan oleh admin)
- Status presensi: Hadir, Terlambat, Izin, Sakit, Alpha
- Untuk fase 1: manual digital (PIN/login)
- Fase 2: upgrade ke biometric (fingerprint/face recognition)

---

## US-ATT-008: Approval Izin/Cuti Guru (Kepala Sekolah/TU)

**As a** Kepala Sekolah/TU  
**I want** approve atau reject pengajuan izin/cuti guru  
**So that** jadwal mengajar dapat diatur dengan baik

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru mengajukan izin untuk tanggal tertentu  
   **When** guru submit form izin dengan keterangan  
   **Then** sistem kirim notifikasi ke kepala sekolah untuk approval

✅ **Given** kepala sekolah menerima notifikasi izin guru  
   **When** kepala sekolah buka detail izin  
   **Then** sistem tampilkan: nama guru, tanggal, keterangan, surat pendukung (jika ada)

✅ **Given** kepala sekolah approve izin  
   **When** kepala sekolah klik "Setujui"  
   **Then** status izin berubah "Disetujui" dan guru dapat notifikasi, presensi guru untuk tanggal tersebut otomatis "Izin"

✅ **Given** kepala sekolah reject izin  
   **When** kepala sekolah klik "Tolak" dengan alasan  
   **Then** guru dapat notifikasi rejection beserta alasan

**Notes:**
- Izin harus diajukan minimal H-1 (kecuali emergency)
- Notifikasi via WhatsApp/email
- Integrasi dengan jadwal (opsional): jika guru izin, sistem suggest pengganti

---

## US-ATT-009: Rekap Presensi Guru (Harian & Bulanan)

**As a** Kepala Sekolah/TU  
**I want** melihat rekap presensi guru  
**So that** saya dapat monitoring kedisiplinan dan menghitung honor/gaji

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Rekap Presensi Guru"  
   **When** kepala sekolah pilih bulan  
   **Then** sistem tampilkan tabel rekap: nama guru, total hadir, terlambat, izin, sakit, alpha, persentase kehadiran

✅ **Given** guru A sering terlambat (> 3x dalam sebulan)  
   **When** sistem generate rekap  
   **Then** baris guru A ditandai dengan warning (highlight kuning/merah)

✅ **Given** kepala sekolah ingin lihat detail presensi harian guru  
   **When** kepala sekolah klik nama guru  
   **Then** sistem tampilkan detail: tanggal, jam masuk, jam pulang, total jam kerja, keterangan

✅ **Given** kepala sekolah ingin export untuk payroll  
   **When** kepala sekolah klik "Export untuk Payroll"  
   **Then** sistem generate Excel dengan kolom: nama guru, total hari kerja, total jam mengajar (untuk hitung honor)

**Notes:**
- Perhitungan otomatis untuk payroll (integrasi dengan Teacher Management)
- Filter: per guru, per bulan, per tahun ajaran
- Export ke Excel untuk akuntan/payroll

---

## US-ATT-010: Notifikasi Otomatis Absensi (WhatsApp/Email)

**As a** Orang Tua  
**I want** menerima notifikasi otomatis jika anak saya tidak hadir  
**So that** saya segera mengetahui jika anak tidak ke sekolah

**Priority:** Should Have (Critical untuk engagement)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru input absensi pagi dan siswa A statusnya "Alpha"  
   **When** guru simpan absensi  
   **Then** sistem otomatis kirim notifikasi WhatsApp/email ke orang tua siswa A: "Anak Anda tidak hadir di sekolah hari ini tanpa keterangan"

✅ **Given** siswa B statusnya "Sakit" dengan izin yang sudah disetujui  
   **When** guru simpan absensi  
   **Then** sistem TIDAK kirim notifikasi (karena sudah ada izin)

✅ **Given** orang tua set preferensi notifikasi "WhatsApp Only"  
   **When** sistem kirim notifikasi  
   **Then** notifikasi hanya dikirim via WhatsApp, tidak via email

✅ **Given** guru input absensi jam 08:00 WIB  
   **When** sistem kirim notifikasi  
   **Then** orang tua menerima notifikasi maksimal 5 menit setelah absensi disimpan

**Notes:**
- Notifikasi via WhatsApp (prioritas), fallback ke email jika WhatsApp gagal
- Template notifikasi dalam Bahasa Indonesia
- Orang tua dapat reply untuk klarifikasi (future: two-way chat)

---

## US-ATT-011: Dashboard Absensi Real-Time

**As a** Kepala Sekolah  
**I want** melihat dashboard absensi real-time semua kelas  
**So that** saya dapat monitoring kehadiran siswa & guru secara cepat

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah login di pagi hari  
   **When** kepala sekolah buka dashboard  
   **Then** sistem tampilkan summary: total siswa hadir, total alpha hari ini, persentase kehadiran, status input absensi per kelas

✅ **Given** ada kelas yang belum input absensi (jam sudah > 09:00 WIB)  
   **When** dashboard refresh  
   **Then** kelas tersebut ditandai merah dengan label "Belum Input Absensi"

✅ **Given** kepala sekolah ingin lihat detail per kelas  
   **When** kepala sekolah klik salah satu kelas  
   **Then** sistem tampilkan list siswa yang alpha/izin/sakit hari ini

**Notes:**
- Real-time update (websocket atau polling setiap 1 menit)
- Visual: card/chart untuk mudah dibaca
- Mobile-responsive

---

## US-ATT-012: Offline Mode untuk Input Absensi (Phase 2)

**As a** Guru  
**I want** input absensi meskipun internet tidak stabil  
**So that** saya tetap dapat input absensi tanpa terganggu koneksi

**Priority:** Could Have (Phase 2)  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** guru di area dengan internet tidak stabil  
   **When** guru buka halaman absensi  
   **Then** sistem load data siswa dari cache (offline mode)

✅ **Given** guru input absensi dalam offline mode  
   **When** guru simpan  
   **Then** data disimpan di local storage browser

✅ **Given** koneksi internet kembali  
   **When** sistem detect koneksi  
   **Then** data absensi otomatis sync ke server dan tampilkan notifikasi "Absensi berhasil disinkronkan"

**Notes:**
- Menggunakan Service Worker (PWA)
- Opsional untuk fase 2 (prioritas rendah untuk MVP)
- Fallback: guru bisa input via app mobile (future)

---

## Summary: Attendance System

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-ATT-001 | Absensi Harian Siswa | Must Have | M | 1 |
| US-ATT-002 | Absensi Per Pelajaran | Must Have | M | 1 |
| US-ATT-003 | Ajukan Izin (Orang Tua) | Must Have | M | 1 |
| US-ATT-004 | Verifikasi Izin | Must Have | S | 1 |
| US-ATT-005 | Rekap Absensi Siswa | Must Have | M | 1 |
| US-ATT-006 | Portal Orang Tua | Must Have | S | 1 |
| US-ATT-007 | Presensi Guru | Must Have | M | 1 |
| US-ATT-008 | Approval Izin Guru | Should Have | M | 1 |
| US-ATT-009 | Rekap Presensi Guru | Must Have | M | 1 |
| US-ATT-010 | Notifikasi Otomatis | Should Have | M | 1 |
| US-ATT-011 | Dashboard Real-Time | Should Have | M | 1 |
| US-ATT-012 | Offline Mode | Could Have | L | 2 |

**Total Estimation Phase 1:** 30 points (~5 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
