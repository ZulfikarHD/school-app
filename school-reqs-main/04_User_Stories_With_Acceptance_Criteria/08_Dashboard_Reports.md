# User Stories: Dashboard & Reports

Module ini mencakup dashboard untuk semua role dan berbagai laporan untuk monitoring operasional sekolah.

---

## US-DASH-001: Dashboard Kepala Sekolah

**As a** Kepala Sekolah  
**I want** melihat dashboard overview seluruh operasional sekolah  
**So that** saya dapat monitoring kondisi sekolah dengan cepat

**Priority:** Must Have  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah login ke sistem  
   **When** kepala sekolah masuk ke dashboard  
   **Then** sistem tampilkan widget: Total Siswa Aktif, Absensi Hari Ini (siswa & guru), Pemasukan Bulan Ini, Tunggakan SPP, Pending Actions

✅ **Given** ada 15 siswa alpha hari ini  
   **When** dashboard load  
   **Then** widget "Absensi Hari Ini" tampilkan angka dengan highlight merah

✅ **Given** ada 20 pembayaran menunggu verifikasi  
   **When** dashboard load  
   **Then** widget "Pending Actions" tampilkan notifikasi dengan link ke halaman verifikasi

✅ **Given** kepala sekolah ingin lihat detail  
   **When** kepala sekolah klik salah satu widget  
   **Then** sistem redirect ke halaman detail module tersebut

**Notes:**
- Widget summary: siswa, absensi, keuangan, akademik, PSB
- Grafik: tren absensi, pemasukan 6 bulan terakhir, distribusi nilai
- Quick action: Approve Rapor, Verifikasi Pembayaran, Lihat Tunggakan

---

## US-DASH-002: Dashboard TU/Admin

**As a** TU/Admin  
**I want** melihat dashboard fokus pada administrasi & keuangan  
**So that** saya dapat fokus pada tugas-tugas TU

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU login ke sistem  
   **When** TU masuk ke dashboard  
   **Then** sistem tampilkan widget: Siswa Baru Hari Ini, Pembayaran Pending Verifikasi, Tunggakan SPP, Dokumen PSB Pending, Presensi Guru Hari Ini

✅ **Given** ada 10 pembayaran pending  
   **When** dashboard load  
   **Then** widget "Pembayaran Pending" tampilkan angka 10 dengan link ke halaman verifikasi

✅ **Given** TU klik quick action "Catat Pembayaran"  
   **When** TU klik  
   **Then** sistem buka form catat pembayaran baru

**Notes:**
- Fokus pada task TU: pembayaran, siswa, PSB
- Quick action: Catat Pembayaran, Tambah Siswa, Verifikasi Dokumen
- Notifikasi real-time untuk task pending

---

## US-DASH-003: Dashboard Guru

**As a** Guru  
**I want** melihat dashboard fokus pada mengajar & siswa  
**So that** saya dapat fokus pada tugas mengajar

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru login ke sistem  
   **When** guru masuk ke dashboard  
   **Then** sistem tampilkan widget: Jadwal Hari Ini, Siswa di Kelas Saya, Absensi Pending Input, Nilai Pending Input

✅ **Given** guru belum input absensi hari ini  
   **When** dashboard load  
   **Then** widget "Absensi Pending" tampilkan notifikasi "Input Absensi Kelas 3A"

✅ **Given** guru klik quick action "Input Absensi"  
   **When** guru klik  
   **Then** sistem buka halaman input absensi untuk kelas yang diajar

**Notes:**
- Fokus pada task guru: absensi, nilai, jadwal
- Quick action: Input Absensi, Input Nilai, Lihat Jadwal
- Mobile-friendly (guru sering akses via HP)

---

## US-DASH-004: Dashboard Orang Tua

**As a** Orang Tua  
**I want** melihat dashboard info anak saya  
**So that** saya dapat monitoring perkembangan anak

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua masuk ke dashboard  
   **Then** sistem tampilkan card anak (jika > 1 anak, tampilkan semua), widget per anak: Absensi Bulan Ini, Tagihan Pending, Pengumuman Terbaru

✅ **Given** anak A punya tagihan SPP belum bayar  
   **When** dashboard load  
   **Then** card anak A tampilkan notifikasi "Tagihan: 1 bulan belum dibayar"

✅ **Given** ada pengumuman baru dari sekolah  
   **When** dashboard load  
   **Then** widget "Pengumuman" tampilkan notifikasi dengan badge "Baru"

**Notes:**
- Fokus pada info anak: absensi, nilai, pembayaran, pengumuman
- Quick action: Lihat Tagihan, Ajukan Izin, Lihat Rapor
- Mobile-first design

---

## US-DASH-005: Laporan Keuangan (Bulanan)

**As a** Kepala Sekolah/TU  
**I want** generate laporan keuangan bulanan  
**So that** saya dapat monitoring cash flow dan tunggakan

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Laporan Keuangan"  
   **When** kepala sekolah pilih bulan (misal: Januari 2025)  
   **Then** sistem tampilkan summary: Total Pemasukan, Breakdown per Jenis (SPP, Uang Gedung, dll), Total Tunggakan, Persentase Kolektibilitas

✅ **Given** laporan Januari menunjukkan pemasukan Rp 45.000.000  
   **When** laporan di-generate  
   **Then** sistem tampilkan grafik breakdown: SPP (Rp 40jt), Uang Gedung (Rp 3jt), Lain-lain (Rp 2jt)

✅ **Given** kepala sekolah ingin export laporan  
   **When** kepala sekolah klik "Export PDF"  
   **Then** sistem generate PDF siap print dengan logo sekolah, summary, grafik, detail transaksi

**Notes:**
- Export: PDF (untuk presentasi) dan Excel (untuk akuntan)
- Grafik: pie chart, bar chart, line chart (tren)
- Filter: per periode, per jenis pembayaran

---

## US-DASH-006: Laporan Akademik (Per Semester)

**As a** Kepala Sekolah  
**I want** generate laporan akademik per semester  
**So that** saya dapat evaluasi kualitas pendidikan

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Laporan Akademik"  
   **When** kepala sekolah pilih semester  
   **Then** sistem tampilkan: Rata-rata Nilai per Mata Pelajaran, Distribusi Predikat (A/B/C/D), Top 10 Siswa, Siswa Perlu Perhatian (nilai < KKM)

✅ **Given** mata pelajaran IPA rata-ratanya 75  
   **When** laporan di-generate  
   **Then** sistem tampilkan bar chart dengan IPA di posisi sesuai rata-rata

✅ **Given** kepala sekolah ingin export  
   **When** kepala sekolah klik "Export PDF"  
   **Then** sistem generate PDF dengan grafik, summary, rekomendasi

**Notes:**
- Analisis per kelas, per mata pelajaran
- Identifikasi mata pelajaran yang perlu improvement
- Ranking siswa (opsional, bisa di-hide jika tidak diinginkan)

---

## US-DASH-007: Laporan Absensi (Bulanan)

**As a** Kepala Sekolah/TU  
**I want** generate laporan absensi bulanan (siswa & guru)  
**So that** saya dapat monitoring kedisiplinan

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Laporan Absensi"  
   **When** kepala sekolah pilih bulan dan kategori (Siswa/Guru)  
   **Then** sistem tampilkan rekap: Total Hadir, Sakit, Izin, Alpha, Persentase Kehadiran per Kelas/per Guru

✅ **Given** kelas 3A kehadiran 92%, kelas 3B kehadiran 85%  
   **When** laporan di-generate  
   **Then** sistem highlight kelas 3B sebagai "Need Attention"

✅ **Given** kepala sekolah ingin lihat detail siswa dengan alpha > 3x  
   **When** kepala sekolah klik "Siswa Bermasalah"  
   **Then** sistem tampilkan list siswa dengan alpha tertinggi dan contact orang tua

**Notes:**
- Grafik: tren kehadiran per kelas, per bulan
- Export: Excel dan PDF
- Filter: per kelas, per periode

---

## US-DASH-008: Laporan PSB (Recruitment)

**As a** Kepala Sekolah  
**I want** generate laporan PSB (penerimaan siswa baru)  
**So that** saya dapat evaluasi proses recruitment

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Laporan PSB"  
   **When** kepala sekolah pilih tahun ajaran  
   **Then** sistem tampilkan: Total Pendaftar, Diterima, Ditolak, Daftar Ulang Selesai, Funnel Conversion

✅ **Given** total pendaftar 120, diterima 100, daftar ulang selesai 95  
   **When** laporan di-generate  
   **Then** sistem tampilkan funnel chart: 120 → 100 → 95 (conversion rate 79%)

✅ **Given** kepala sekolah ingin export  
   **When** kepala sekolah klik "Export"  
   **Then** sistem generate PDF/Excel dengan detail per status

**Notes:**
- Funnel analysis: pendaftar → verifikasi → diterima → daftar ulang
- Grafik: tren pendaftar per hari/minggu
- Comparison dengan tahun sebelumnya

---

## US-DASH-009: Notifikasi & Alert Dashboard

**As a** User (semua role)  
**I want** menerima notifikasi penting di dashboard  
**So that** saya tidak miss task/informasi penting

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU belum verifikasi 10 pembayaran  
   **When** TU login  
   **Then** dashboard tampilkan notifikasi "10 pembayaran menunggu verifikasi" dengan badge merah

✅ **Given** guru belum input absensi hari ini (jam > 10:00)  
   **When** guru buka dashboard  
   **Then** sistem tampilkan alert "Reminder: Input absensi kelas 3A"

✅ **Given** orang tua punya tunggakan SPP 2 bulan  
   **When** orang tua login  
   **Then** dashboard tampilkan warning "Anda memiliki tunggakan 2 bulan. Mohon segera dilunasi"

**Notes:**
- Notifikasi real-time atau near real-time
- Badge counter untuk pending actions
- Color coding: merah (urgent), kuning (warning), hijau (info)

---

## US-DASH-010: Export Laporan Multi-Format

**As a** Kepala Sekolah/TU  
**I want** export laporan dalam berbagai format (PDF, Excel, CSV)  
**So that** saya dapat gunakan sesuai kebutuhan

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** user di halaman laporan apapun  
   **When** user klik "Export"  
   **Then** sistem tampilkan pilihan format: PDF, Excel (.xlsx), CSV

✅ **Given** user pilih PDF  
   **When** user klik "Download PDF"  
   **Then** sistem generate PDF dengan format professional (logo sekolah, header, footer, page number)

✅ **Given** user pilih Excel  
   **When** user klik "Download Excel"  
   **Then** sistem generate Excel dengan data detail dan formatting (header bold, auto-width column)

**Notes:**
- PDF: untuk presentasi dan print
- Excel: untuk analisis lebih lanjut
- CSV: untuk import ke software lain (misal: accounting software)

---

## US-DASH-011: Custom Date Range untuk Laporan

**As a** Kepala Sekolah/TU  
**I want** memilih custom date range untuk laporan  
**So that** saya dapat generate laporan untuk periode spesifik

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** user di halaman laporan keuangan  
   **When** user pilih "Custom Range" dan set 1 Januari - 15 Januari 2025  
   **Then** sistem generate laporan hanya untuk tanggal tersebut

✅ **Given** user pilih preset "Minggu Ini" atau "Bulan Ini"  
   **When** user klik preset  
   **Then** date range otomatis terisi sesuai preset

**Notes:**
- Preset: Hari Ini, Minggu Ini, Bulan Ini, 3 Bulan Terakhir, Tahun Ini
- Custom: date picker untuk start & end date
- Validasi: end date tidak boleh < start date

---

## US-DASH-012: Grafik & Visualisasi Data

**As a** Kepala Sekolah  
**I want** melihat data dalam bentuk grafik/chart  
**So that** informasi lebih mudah dipahami

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user di dashboard atau laporan  
   **When** data di-load  
   **Then** sistem tampilkan grafik yang sesuai: line chart (tren), bar chart (perbandingan), pie chart (distribusi)

✅ **Given** data pemasukan 6 bulan terakhir  
   **When** sistem generate chart  
   **Then** tampilkan line chart dengan sumbu X (bulan), sumbu Y (nominal), dan tooltip saat hover

✅ **Given** data distribusi nilai (A=30%, B=40%, C=25%, D=5%)  
   **When** sistem generate chart  
   **Then** tampilkan pie chart dengan legend dan persentase

**Notes:**
- Library chart: Chart.js atau ApexCharts
- Interactive: hover untuk detail, klik untuk drill-down
- Responsive: chart menyesuaikan ukuran layar

---

## Summary: Dashboard & Reports

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-DASH-001 | Dashboard Kepala Sekolah | Must Have | L | 1 |
| US-DASH-002 | Dashboard TU | Must Have | M | 1 |
| US-DASH-003 | Dashboard Guru | Must Have | M | 1 |
| US-DASH-004 | Dashboard Orang Tua | Must Have | M | 1 |
| US-DASH-005 | Laporan Keuangan | Must Have | M | 1 |
| US-DASH-006 | Laporan Akademik | Should Have | M | 1 |
| US-DASH-007 | Laporan Absensi | Must Have | M | 1 |
| US-DASH-008 | Laporan PSB | Should Have | S | 1 |
| US-DASH-009 | Notifikasi & Alert | Should Have | M | 1 |
| US-DASH-010 | Export Multi-Format | Should Have | S | 1 |
| US-DASH-011 | Custom Date Range | Should Have | S | 1 |
| US-DASH-012 | Grafik & Visualisasi | Should Have | M | 1 |

**Total Estimation Phase 1:** 33 points (~5 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
