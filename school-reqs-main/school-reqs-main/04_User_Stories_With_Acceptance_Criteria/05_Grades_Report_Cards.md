# User Stories: Grades & Report Cards

Module ini mencakup fitur input nilai, rapor digital, dan rekap akademik sesuai Kurikulum 2013.

---

## US-GRD-001: Input Nilai Siswa (Per Komponen)

**As a** Guru Mata Pelajaran  
**I want** input nilai siswa per komponen (UH, UTS, UAS, dll)  
**So that** nilai tersimpan dan dapat diproses untuk rapor

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru di halaman "Input Nilai"  
   **When** guru pilih kelas, mata pelajaran, dan komponen nilai (misal: UH1)  
   **Then** sistem tampilkan list siswa dengan kolom input nilai

✅ **Given** guru input nilai Matematika UH1 untuk siswa A = 85  
   **When** guru klik "Simpan"  
   **Then** sistem simpan nilai dan tampilkan notifikasi "Nilai berhasil disimpan"

✅ **Given** guru input nilai di luar range (misal: 105)  
   **When** guru coba simpan  
   **Then** sistem tampilkan error "Nilai harus antara 0-100"

✅ **Given** guru sudah input nilai UH1 untuk kelas 3A  
   **When** guru buka halaman input nilai lagi  
   **Then** sistem tampilkan nilai yang sudah diinput (mode edit)

**Notes:**
- Komponen nilai K13: UH (Penilaian Harian), UTS, UAS, Praktik/Portofolio, Nilai Sikap (Spiritual & Sosial)
- Range nilai: 0-100
- Support bulk input (copy-paste dari Excel) - opsional
- Validasi: nilai wajib diisi sebelum generate rapor

---

## US-GRD-002: Set Bobot Komponen Nilai

**As a** Admin/Kepala Sekolah  
**I want** mengatur bobot setiap komponen nilai  
**So that** perhitungan nilai akhir sesuai dengan kurikulum

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Pengaturan Nilai"  
   **When** admin set bobot: UH = 40%, UTS = 30%, UAS = 30%  
   **Then** sistem simpan konfigurasi bobot

✅ **Given** bobot sudah diset  
   **When** sistem hitung nilai akhir siswa A (UH=80, UTS=85, UAS=90)  
   **Then** nilai akhir = (80×0.4) + (85×0.3) + (90×0.3) = 84.5

✅ **Given** admin ubah bobot di tengah semester  
   **When** admin update bobot  
   **Then** sistem tampilkan warning "Perubahan bobot akan affect nilai akhir semua siswa" dan minta konfirmasi

**Notes:**
- Bobot per mata pelajaran (bisa berbeda tiap mapel)
- Default bobot sesuai K13
- History perubahan bobot untuk audit

---

## US-GRD-003: Lihat Rekap Nilai Siswa

**As a** Guru/TU/Kepala Sekolah  
**I want** melihat rekap nilai siswa per mata pelajaran  
**So that** saya dapat monitoring perkembangan akademik siswa

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru di halaman "Rekap Nilai"  
   **When** guru pilih kelas dan semester  
   **Then** sistem tampilkan tabel: nama siswa, nilai per mata pelajaran, rata-rata

✅ **Given** guru klik nama siswa  
   **When** detail nilai load  
   **Then** sistem tampilkan breakdown nilai: per komponen (UH, UTS, UAS), nilai akhir, predikat (A/B/C/D)

✅ **Given** ada siswa dengan nilai rata-rata < 60 (KKM)  
   **When** rekap di-generate  
   **Then** baris siswa tersebut highlight merah sebagai warning

**Notes:**
- Predikat nilai sesuai K13: A (86-100), B (71-85), C (56-70), D (≤55)
- KKM per mata pelajaran (configurable, default 60)
- Export rekap ke Excel

---

## US-GRD-004: Input Nilai Sikap (Spiritual & Sosial)

**As a** Wali Kelas  
**I want** input nilai sikap spiritual dan sosial untuk siswa  
**So that** rapor siswa lengkap sesuai K13

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** wali kelas di halaman "Nilai Sikap"  
   **When** wali kelas pilih kelas dan semester  
   **Then** sistem tampilkan list siswa dengan kolom: Sikap Spiritual, Sikap Sosial

✅ **Given** wali kelas input sikap spiritual siswa A = "Baik Sekali"  
   **When** wali kelas simpan  
   **Then** sistem simpan nilai sikap dengan predikat: SB (Sangat Baik), B (Baik), C (Cukup), K (Kurang)

✅ **Given** wali kelas ingin tambah catatan perilaku  
   **When** wali kelas input keterangan (misal: "Siswa rajin beribadah, sopan terhadap guru")  
   **Then** sistem simpan keterangan dan tampil di rapor

**Notes:**
- Predikat sikap K13: SB, B, C, K (bukan angka)
- Keterangan opsional (deskripsi perilaku)
- Wali kelas yang input (bukan guru mata pelajaran)

---

## US-GRD-005: Generate Rapor Digital (PDF)

**As a** TU/Wali Kelas  
**I want** generate rapor siswa dalam format PDF  
**So that** rapor dapat dicetak atau dilihat online oleh orang tua

**Priority:** Must Have  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** wali kelas di halaman "Rapor"  
   **When** wali kelas pilih kelas, semester, dan klik "Generate Rapor"  
   **Then** sistem generate PDF rapor untuk semua siswa di kelas tersebut

✅ **Given** rapor siswa A di-generate  
   **When** wali kelas buka PDF  
   **Then** PDF berisi: identitas siswa, nilai per mata pelajaran (breakdown komponen + nilai akhir + predikat), nilai sikap, catatan wali kelas, ttd kepala sekolah

✅ **Given** ada siswa yang nilai belum lengkap (UTS belum diinput)  
   **When** sistem coba generate rapor  
   **Then** sistem tampilkan error "Nilai belum lengkap untuk siswa X, Y, Z. Harap lengkapi nilai terlebih dahulu"

✅ **Given** rapor berhasil di-generate  
   **When** rapor di-print  
   **Then** format rapor sesuai standar Dinas Pendidikan (template customizable per sekolah)

**Notes:**
- Template rapor sesuai format K13 dan Dinas Pendidikan
- Generate bulk (semua siswa) atau individual
- Watermark "Rapor Resmi" (opsional)
- Digital signature kepala sekolah (opsional, fase 2)

---

## US-GRD-006: Lihat Rapor Online (Portal Orang Tua)

**As a** Orang Tua  
**I want** melihat rapor anak saya secara online  
**So that** saya dapat memantau perkembangan akademik anak

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua klik tab "Rapor" pada profil anak  
   **Then** sistem tampilkan list rapor per semester (Semester 1, Semester 2)

✅ **Given** orang tua klik "Semester 1"  
   **When** rapor load  
   **Then** sistem tampilkan nilai per mata pelajaran, nilai sikap, catatan wali kelas

✅ **Given** orang tua ingin download rapor  
   **When** orang tua klik "Download PDF"  
   **Then** sistem generate PDF rapor dan auto-download

✅ **Given** rapor belum di-release (masih draft)  
   **When** orang tua coba akses  
   **Then** sistem tampilkan pesan "Rapor belum tersedia, akan dirilis oleh sekolah"

**Notes:**
- Read-only untuk orang tua
- Rapor hanya tampil setelah wali kelas/kepala sekolah "release"
- Notifikasi ke orang tua saat rapor dirilis

---

## US-GRD-007: Approval & Release Rapor (Kepala Sekolah)

**As a** Kepala Sekolah  
**I want** review dan approve rapor sebelum dirilis ke orang tua  
**So that** rapor yang dirilis sudah final dan akurat

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Approval Rapor"  
   **When** halaman load  
   **Then** sistem tampilkan list rapor yang menunggu approval (per kelas, per semester)

✅ **Given** kepala sekolah review rapor kelas 3A  
   **When** kepala sekolah klik "Preview"  
   **Then** sistem tampilkan sample rapor 2-3 siswa untuk review

✅ **Given** kepala sekolah setujui rapor kelas 3A  
   **When** kepala sekolah klik "Approve & Release"  
   **Then** status rapor berubah "Released" dan orang tua siswa kelas 3A dapat akses rapor online + notifikasi

✅ **Given** kepala sekolah temukan kesalahan  
   **When** kepala sekolah klik "Reject" dengan keterangan  
   **Then** rapor kembali ke wali kelas untuk perbaikan dan wali kelas dapat notifikasi

**Notes:**
- Workflow: Input Nilai → Generate Rapor → Approval → Release
- Notifikasi ke orang tua setelah release
- Lock nilai setelah rapor di-release (tidak bisa edit lagi)

---

## US-GRD-008: Catatan Wali Kelas di Rapor

**As a** Wali Kelas  
**I want** menambahkan catatan untuk setiap siswa di rapor  
**So that** orang tua dapat feedback tentang perkembangan anak

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** wali kelas di halaman "Catatan Rapor"  
   **When** wali kelas pilih siswa  
   **Then** sistem tampilkan textarea untuk input catatan

✅ **Given** wali kelas input catatan untuk siswa A: "Siswa rajin dan disiplin, perlu tingkatkan kemampuan Matematika"  
   **When** wali kelas simpan  
   **Then** catatan tersimpan dan tampil di rapor siswa A

✅ **Given** rapor sudah di-generate  
   **When** rapor dicetak  
   **Then** catatan wali kelas tampil di bagian bawah rapor

**Notes:**
- Catatan per siswa per semester
- Max 500 karakter
- Template catatan (opsional, untuk mempercepat input)

---

## US-GRD-009: Export Nilai ke Excel

**As a** TU/Kepala Sekolah  
**I want** export data nilai ke Excel  
**So that** data dapat digunakan untuk analisis atau backup

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Rekap Nilai"  
   **When** TU klik "Export ke Excel"  
   **Then** sistem generate file Excel dengan kolom: nama siswa, NIS, kelas, per mata pelajaran (UH, UTS, UAS, nilai akhir, predikat)

✅ **Given** TU sudah apply filter (misal: kelas 4B, Semester 1)  
   **When** TU export  
   **Then** file Excel hanya berisi data sesuai filter

**Notes:**
- Format Excel standar dengan header
- Nama file: RekapNilai_[Kelas]_[Semester]_[Tanggal].xlsx

---

## US-GRD-010: Import Nilai dari Excel

**As a** Guru  
**I want** import nilai dari Excel (bulk insert)  
**So that** saya tidak perlu input manual satu per satu

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru di halaman "Input Nilai"  
   **When** guru klik "Import dari Excel"  
   **Then** sistem tampilkan instruksi dan template Excel

✅ **Given** guru download template dan isi nilai  
   **When** guru upload file Excel  
   **Then** sistem validasi data dan tampilkan preview

✅ **Given** semua data valid  
   **When** guru klik "Proses Import"  
   **Then** sistem insert nilai ke database dan tampilkan notifikasi "X nilai berhasil diimport"

**Notes:**
- Template Excel dengan format yang jelas
- Validasi: range nilai, duplikasi, dll
- Opsional untuk fase 2

---

## US-GRD-011: Analisis Nilai (Dashboard Akademik)

**As a** Kepala Sekolah  
**I want** melihat analisis nilai secara keseluruhan  
**So that** saya dapat monitoring kualitas pendidikan

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Dashboard Akademik"  
   **When** kepala sekolah pilih semester  
   **Then** sistem tampilkan: rata-rata nilai per mata pelajaran, distribusi predikat (A/B/C/D), grafik tren nilai

✅ **Given** mata pelajaran Matematika rata-ratanya rendah (< 70)  
   **When** dashboard load  
   **Then** sistem highlight Matematika sebagai "Need Attention"

✅ **Given** kepala sekolah ingin lihat detail per kelas  
   **When** kepala sekolah klik salah satu kelas  
   **Then** sistem tampilkan breakdown nilai kelas tersebut

**Notes:**
- Chart: bar chart, pie chart, line chart (tren)
- Filter: per kelas, per mata pelajaran, per semester
- Export analisis ke PDF untuk presentasi

---

## US-GRD-012: Remedial & Pengayaan Tracking

**As a** Guru  
**I want** catat siswa yang ikut remedial atau pengayaan  
**So that** nilai perbaikan tercatat

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** siswa A nilai UTS Matematika < KKM (60)  
   **When** sistem generate list remedial  
   **Then** siswa A masuk list "Wajib Remedial"

✅ **Given** guru input nilai remedial siswa A = 75  
   **When** guru simpan  
   **Then** sistem replace nilai UTS dengan nilai remedial (max KKM+1, misal 61)

✅ **Given** siswa B nilai tinggi, ikut pengayaan  
   **When** guru catat nilai pengayaan  
   **Then** nilai pengayaan tersimpan (tidak replace nilai asli, hanya catatan)

**Notes:**
- Remedial: untuk siswa < KKM
- Pengayaan: untuk siswa berprestasi
- Opsional untuk fase 2

---

## Summary: Grades & Report Cards

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-GRD-001 | Input Nilai | Must Have | M | 1 |
| US-GRD-002 | Set Bobot Nilai | Must Have | S | 1 |
| US-GRD-003 | Rekap Nilai | Must Have | M | 1 |
| US-GRD-004 | Nilai Sikap | Must Have | S | 1 |
| US-GRD-005 | Generate Rapor PDF | Must Have | L | 1 |
| US-GRD-006 | Portal Orang Tua | Must Have | M | 1 |
| US-GRD-007 | Approval Rapor | Should Have | M | 1 |
| US-GRD-008 | Catatan Wali Kelas | Should Have | S | 1 |
| US-GRD-009 | Export Excel | Should Have | S | 1 |
| US-GRD-010 | Import Excel | Could Have | M | 2 |
| US-GRD-011 | Dashboard Akademik | Should Have | M | 1 |
| US-GRD-012 | Remedial Tracking | Could Have | M | 2 |

**Total Estimation Phase 1:** 25 points (~4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
