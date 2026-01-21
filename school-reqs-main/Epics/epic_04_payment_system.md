# EPIC 4: Payment System (Sistem Pembayaran)

**Project:** SD Management System  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Epic Owner:** Development Team  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Epic Overview

### Goal
Digitalisasi pengelolaan pembayaran sekolah (SPP, uang gedung, dan pembayaran lainnya) dengan sistem yang memudahkan pencatatan, tracking, reminder otomatis, dan laporan keuangan yang akurat untuk meningkatkan transparansi dan kolektibilitas pembayaran.

### Business Value
- **Transparansi Keuangan:** Orang tua dapat melihat tagihan dan riwayat pembayaran secara real-time
- **Efisiensi Operasional:** Otomasi reminder pembayaran mengurangi beban kerja TU
- **Kolektibilitas Tinggi:** Reminder otomatis meningkatkan tingkat pembayaran tepat waktu
- **Akurasi Data:** Pencatatan digital mengurangi kesalahan manual dan kehilangan data
- **Decision Making:** Laporan keuangan real-time membantu kepala sekolah dalam pengambilan keputusan
- **Rekonsiliasi Mudah:** Verifikasi pembayaran transfer dan rekonsiliasi bank yang sistematis

### Success Metrics
- Tingkat kolektibilitas pembayaran meningkat > 90% (dari baseline)
- Waktu pencatatan pembayaran < 3 menit per transaksi
- Tingkat keterlambatan pembayaran menurun > 30%
- 100% tagihan SPP ter-generate otomatis setiap bulan
- Waktu rekonsiliasi pembayaran < 2 jam per bulan
- Kepuasan orang tua terhadap transparansi pembayaran > 4.5/5

---

## 📊 Epic Summary

| **Attribute** | **Value** |
|---------------|-----------|
| **Total Points** | 33 points |
| **Target Sprint** | Sprint 4 & 5 |
| **Priority** | P0 - Critical |
| **Dependencies** | Epic 1 (Authentication), Epic 2 (Student Management), Epic 6 (Notification) |
| **Target Release** | MVP (Phase 1) |
| **Estimated Duration** | 4-5 minggu (1 developer) |

---

## 🎯 User Stories Included

### Payment Management (16 points)

#### US-PAY-001: Catat Pembayaran SPP (Manual)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** mencatat pembayaran SPP siswa yang dibayar tunai/transfer  
**So that** riwayat pembayaran tersimpan dan dapat dilacak

**Acceptance Criteria:**

✅ **Given** TU di halaman "Pembayaran"  
   **When** TU klik "Catat Pembayaran Baru"  
   **Then** sistem tampilkan form: Pilih Siswa (search autocomplete), Jenis Pembayaran, Bulan/Periode, Jumlah, Metode (Tunai/Transfer), Tanggal Bayar, Nomor Referensi (opsional), Keterangan

✅ **Given** TU pilih siswa dan pilih tagihan SPP Januari 2025 yang belum dibayar  
   **When** TU input jumlah Rp 250.000, pilih metode "Tunai", dan klik "Simpan & Cetak Kwitansi"  
   **Then** sistem:
   - Auto-generate nomor kwitansi (format: KWT/2025/01/0001)
   - Simpan transaksi pembayaran
   - Update status tagihan menjadi "Lunas"
   - Tampilkan notifikasi sukses "Pembayaran berhasil dicatat"
   - Auto-open print dialog kwitansi PDF

✅ **Given** siswa A sudah bayar SPP Januari penuh  
   **When** TU coba catat pembayaran SPP Januari lagi untuk siswa yang sama  
   **Then** sistem tampilkan warning "SPP bulan Januari 2025 sudah lunas"

✅ **Given** TU catat pembayaran via transfer bank  
   **When** TU input jumlah Rp 250.000, pilih metode "Transfer Bank", input nomor referensi  
   **Then** sistem simpan transaksi dengan nomor referensi dan status "Lunas"

✅ **Given** siswa membayar sebagian (partial payment) Rp 150.000 untuk tagihan Rp 250.000  
   **When** TU catat pembayaran partial  
   **Then** sistem:
   - Update status tagihan menjadi "Cicilan" (Partial Paid)
   - Update jumlah terbayar: Rp 150.000
   - Sisa tagihan: Rp 100.000
   - Generate kwitansi dengan keterangan "Cicilan 1 dari tagihan SPP Januari 2025"

**Technical Notes:**
- Support partial payment (cicilan)
- Nomor kwitansi auto-increment per bulan
- Validasi: jumlah bayar harus <= sisa tagihan
- Format Rupiah: tampilkan dengan separator ribuan (Rp 250.000)

---

#### US-PAY-002: Lihat Riwayat Pembayaran Siswa
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Kepala Sekolah  
**I want** melihat riwayat pembayaran siswa  
**So that** saya dapat mengecek status pembayaran dan tunggakan

**Acceptance Criteria:**

✅ **Given** TU di halaman profil siswa, tab "Pembayaran"  
   **When** halaman load  
   **Then** sistem tampilkan:
   - Tabel riwayat pembayaran: Tanggal, Jenis, Periode/Bulan, Jumlah, Metode, Status, Nomor Kwitansi, Action
   - Status badge: Lunas (hijau), Cicilan (kuning), Belum Bayar (merah)
   - Filter: per jenis pembayaran, per periode (bulan/tahun)
   - Sort: default by tanggal descending (terbaru di atas)

✅ **Given** siswa A belum bayar SPP bulan Desember & Januari  
   **When** TU lihat riwayat di tab "Tagihan"  
   **Then** sistem tampilkan:
   - List tagihan unpaid: Desember & Januari dengan status "Belum Bayar" (highlight merah)
   - Total tunggakan: Rp 500.000
   - Due date yang sudah lewat ditandai "Terlambat" dengan ikon warning

✅ **Given** TU ingin lihat detail transaksi pembayaran  
   **When** TU klik salah satu transaksi di riwayat  
   **Then** sistem tampilkan modal detail: 
   - Nomor kwitansi, tanggal bayar, nama siswa, kelas
   - Jenis pembayaran, periode
   - Jumlah dibayar, metode pembayaran
   - Nomor referensi (jika transfer)
   - Petugas yang input (nama TU)
   - Button: "Cetak Ulang Kwitansi", "Batalkan Pembayaran" (jika < 7 hari)

✅ **Given** TU ingin print kwitansi ulang  
   **When** TU klik "Cetak Kwitansi" pada transaksi  
   **Then** sistem generate PDF kwitansi pembayaran dengan format resmi sekolah (logo, nomor kwitansi, detail, QR code)

**Technical Notes:**
- Pagination: 20 items per page
- Filter real-time (tidak perlu reload page)
- Export to Excel untuk riwayat pembayaran siswa

---

#### US-PAY-003: Generate Tagihan Bulanan (SPP)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** generate tagihan SPP bulanan untuk semua siswa otomatis  
**So that** saya tidak perlu input manual setiap bulan

**Acceptance Criteria:**

✅ **Given** TU di halaman "Generate Tagihan SPP"  
   **When** TU pilih bulan "Februari 2025", tahun ajaran "2024/2025", kelas "Semua Kelas", dan klik "Preview"  
   **Then** sistem tampilkan preview:
   - Tabel: Nama Siswa, Kelas, Jumlah SPP, Potongan (jika ada), Total Tagihan
   - Summary: Total siswa aktif: 180, Total tagihan: Rp 54.000.000
   - Button: "Kembali" (secondary), "Generate Tagihan" (primary)

✅ **Given** TU review preview dan konfirmasi generate tagihan untuk bulan Februari  
   **When** TU klik "Generate Tagihan"  
   **Then** sistem:
   - Create record tagihan untuk setiap siswa aktif di tabel `bills`
   - Set due date: tanggal 10 Februari 2025 (configurable)
   - Set status: "Belum Bayar" (Unpaid)
   - Tampilkan success notification: "180 tagihan SPP Februari 2025 berhasil di-generate"
   - Tagihan muncul di portal orang tua

✅ **Given** siswa B punya potongan/beasiswa 50%  
   **When** sistem generate tagihan  
   **Then** jumlah SPP siswa B otomatis dikurangi 50% (contoh: Rp 250.000 → Rp 125.000) sesuai data potongan di master siswa

✅ **Given** tagihan SPP bulan Februari 2025 sudah di-generate sebelumnya  
   **When** TU coba generate lagi untuk periode yang sama  
   **Then** sistem tampilkan warning dialog: "Tagihan SPP untuk periode Februari 2025 sudah pernah di-generate. Apakah Anda ingin generate ulang? (Tagihan lama akan dibatalkan)"

✅ **Given** TU ingin generate tagihan hanya untuk kelas tertentu (misal: kelas 3A)  
   **When** TU pilih "Kelas 3A" di dropdown dan generate  
   **Then** sistem hanya generate tagihan untuk siswa di kelas 3A (misal: 30 siswa)

**Technical Notes:**
- Generate hanya untuk siswa dengan status "Aktif"
- Validasi: cek duplikasi periode sebelum generate
- Support bulk generation (handle 500+ siswa)
- Transaction handling: rollback jika ada error
- Optional: auto-trigger reminder notification ke orang tua setelah generate

---

#### US-PAY-004: Set Nominal SPP per Kelas/Siswa
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** mengatur nominal SPP per kelas atau per siswa  
**So that** tagihan SPP sesuai dengan ketentuan sekolah

**Acceptance Criteria:**

✅ **Given** TU di halaman "Pengaturan SPP"  
   **When** TU set nominal SPP: Kelas 1 = Rp 200.000, Kelas 2-6 = Rp 250.000  
   **Then** sistem simpan konfigurasi dan apply untuk generate tagihan berikutnya

✅ **Given** siswa C dapat beasiswa 100% (anak guru/prestasi)  
   **When** TU buka profil siswa C, tab "Potongan SPP", set potongan 100%  
   **Then** SPP siswa C menjadi Rp 0 saat generate tagihan (auto-calculated)

✅ **Given** siswa D dapat potongan 25% (saudara kandung siswa aktif)  
   **When** TU set potongan 25% untuk siswa D  
   **Then** SPP siswa D dihitung otomatis: Rp 250.000 - 25% = Rp 187.500

✅ **Given** TU ingin lihat history perubahan nominal SPP  
   **When** TU klik "History Perubahan" di halaman Pengaturan SPP  
   **Then** sistem tampilkan log: tanggal, nominal lama, nominal baru, user yang ubah

**Technical Notes:**
- Setting default per kelas (di master data kelas)
- Setting custom per siswa (override default kelas)
- Jenis potongan: persentase atau nominal tetap
- History perubahan untuk audit trail

---

#### US-PAY-009: Catat Pembayaran Lain (Non-SPP)
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** mencatat pembayaran selain SPP (uang gedung, seragam, kegiatan)  
**So that** semua transaksi keuangan tercatat

**Acceptance Criteria:**

✅ **Given** TU di halaman "Pembayaran"  
   **When** TU pilih jenis pembayaran "Uang Gedung"  
   **Then** sistem tampilkan form sesuai jenis: Pilih Siswa, Jumlah (auto-filled dari default jenis), Tanggal, Metode, Keterangan

✅ **Given** siswa baru bayar uang gedung Rp 2.000.000  
   **When** TU catat pembayaran  
   **Then** sistem simpan transaksi dan generate invoice/kwitansi dengan jenis "Uang Gedung"

✅ **Given** ada pembayaran kegiatan study tour untuk siswa kelas 6 (Rp 500.000)  
   **When** TU catat pembayaran kegiatan  
   **Then** sistem simpan dengan kategori "Kegiatan" dan keterangan "Study Tour Kelas 6"

✅ **Given** TU ingin create tagihan custom untuk multiple siswa (misal: 30 siswa ikut trip)  
   **When** TU buka "Buat Tagihan Custom", pilih 30 siswa, input detail: "Study Tour Bandung", Rp 500.000, due date 31 Jan 2025  
   **Then** sistem create 30 tagihan custom untuk siswa tersebut, muncul di portal orang tua

**Technical Notes:**
- Support one-time payment dan recurring payment
- Jenis pembayaran customizable (admin dapat tambah jenis baru di Settings)
- Bulk create tagihan untuk kegiatan group

---

#### US-PAY-011: Cetak Kwitansi Pembayaran
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** cetak kwitansi pembayaran untuk diberikan ke orang tua  
**So that** orang tua punya bukti pembayaran resmi

**Acceptance Criteria:**

✅ **Given** TU di halaman riwayat pembayaran siswa  
   **When** TU klik "Cetak Kwitansi" pada transaksi tertentu  
   **Then** sistem generate PDF kwitansi dengan:
   - Header: Logo & nama sekolah, alamat, kontak
   - Nomor kwitansi (prominent)
   - Tanggal pembayaran
   - Nama siswa, kelas, NIS
   - Detail pembayaran: jenis, periode (jika SPP), jumlah, terbilang
   - Metode pembayaran, nomor referensi (jika ada)
   - QR code untuk verifikasi kwitansi
   - Footer: tanda tangan TU digital, stempel sekolah, disclaimer

✅ **Given** kwitansi berhasil di-generate  
   **When** TU buka PDF  
   **Then** format kwitansi siap print (A5 portrait atau thermal printer, configurable di settings)

✅ **Given** TU ingin print kwitansi rangkap (untuk arsip)  
   **When** TU setting "Print Rangkap 2" di halaman settings  
   **Then** PDF generate 2 halaman identik (1 untuk orang tua, 1 untuk arsip sekolah)

**Technical Notes:**
- Template kwitansi customizable (logo, format, footer)
- Nomor kwitansi format: KWT/{tahun}/{bulan}/{nomor_urut} (contoh: KWT/2025/01/0001)
- Terbilang otomatis: Rp 250.000 → "Dua Ratus Lima Puluh Ribu Rupiah"
- QR Code encode: nomor_kwitansi + tanggal + jumlah (untuk verifikasi)
- Support thermal printer (58mm/80mm) dan A4/A5 printer

---

### Payment Reminder & Notification (6 points)

#### US-PAY-005: Reminder Pembayaran (WhatsApp/Email)
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** TU/Orang Tua  
**I want** orang tua menerima reminder pembayaran SPP  
**So that** pembayaran tidak terlambat

**Acceptance Criteria:**

✅ **Given** cronjob reminder berjalan setiap hari jam 06:00 WIB  
   **When** tanggal 5 setiap bulan (H-5 sebelum jatuh tempo tanggal 10)  
   **Then** sistem:
   - Query tagihan dengan status "Belum Bayar" dan due_date = H-5
   - Kirim WhatsApp reminder ke orang tua: "Yth. Bapak/Ibu {nama_ortu}, tagihan SPP bulan {bulan} untuk {nama_siswa} kelas {kelas} sebesar Rp{jumlah} akan jatuh tempo pada tanggal {due_date}. Mohon segera melunasi. Terima kasih. - {Nama Sekolah}"
   - Log notification sent ke tabel `notification_logs`

✅ **Given** orang tua siswa A belum bayar SPP Februari dan hari ini tanggal 10 (due date)  
   **When** reminder H-0 terkirim  
   **Then** orang tua terima pesan: "Yth. Bapak/Ibu {nama}, hari ini adalah jatuh tempo pembayaran SPP bulan Februari untuk {nama_siswa}. Jumlah: Rp 250.000. Mohon segera melunasi untuk menghindari keterlambatan."

✅ **Given** siswa A sudah bayar SPP Februari di tanggal 3  
   **When** reminder H-5 (tanggal 5) berjalan  
   **Then** orang tua siswa A TIDAK menerima reminder (karena tagihan sudah lunas)

✅ **Given** siswa B punya tunggakan 2 bulan (Desember & Januari) dan sekarang sudah tanggal 17 Januari (H+7 setelah due date Januari)  
   **When** reminder H+7 (overdue) berjalan  
   **Then** orang tua terima pesan warning: "Yth. Bapak/Ibu {nama}, pembayaran SPP bulan Januari untuk {nama_siswa} sudah melewati jatuh tempo. Total tunggakan saat ini: 2 bulan (Desember, Januari) = Rp 500.000. Mohon segera melunasi atau hubungi sekolah jika ada kendala. Terima kasih."

✅ **Given** orang tua ingin nonaktifkan reminder otomatis  
   **When** orang tua setting "Reminder Pembayaran: Off" di profil portal  
   **Then** sistem tidak kirim reminder otomatis ke orang tua tersebut (tapi tetap tampil di portal)

**Technical Notes:**
- Reminder schedule: H-5, H-0 (due date), H+7 (overdue) - configurable
- Kirim via WhatsApp (prioritas), fallback email
- Template pesan editable oleh TU di settings
- Reminder hanya untuk tagihan wajib (SPP, Uang Gedung), tidak untuk optional (Donasi, Kegiatan)
- Retry mechanism jika gagal kirim (max 3 retry)

---

#### US-PAY-012: Dashboard Pembayaran (Summary)
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** TU/Kepala Sekolah  
**I want** melihat dashboard summary pembayaran  
**So that** saya dapat quick overview status pembayaran sekolah

**Acceptance Criteria:**

✅ **Given** TU/Kepala Sekolah di halaman dashboard pembayaran  
   **When** halaman load  
   **Then** sistem tampilkan summary cards:
   - Card 1: "Pemasukan Bulan Ini" - Rp 45.000.000 (icon: uang, warna hijau)
   - Card 2: "Total Tunggakan" - Rp 8.500.000 (icon: warning, warna merah, highlight jika > 0)
   - Card 3: "Siswa Belum Bayar" - 15 siswa (icon: user, warna kuning)
   - Card 4: "Tingkat Kolektibilitas" - 92% (icon: chart, progress bar hijau/kuning/merah)

✅ **Given** ada 15 siswa yang belum bayar SPP bulan ini  
   **When** dashboard load  
   **Then** card "Siswa Belum Bayar" tampilkan angka 15 dengan highlight merah dan button "Lihat Detail"

✅ **Given** user klik card "Total Tunggakan"  
   **When** user klik  
   **Then** sistem redirect ke halaman "Laporan Piutang" dengan list siswa tunggakan, sorted by jumlah tunggakan (terbesar ke terkecil)

✅ **Given** dashboard tampilkan grafik tren pembayaran 6 bulan terakhir  
   **When** dashboard load  
   **Then** sistem tampilkan:
   - Line chart: tren pemasukan per bulan (6 bulan terakhir)
   - Bar chart: breakdown pemasukan per jenis pembayaran bulan ini
   - Interactive chart (hover untuk lihat detail)

✅ **Given** TU ingin quick action dari dashboard  
   **When** TU klik button quick action  
   **Then** tersedia action: "Generate Tagihan Bulan Ini", "Catat Pembayaran", "Kirim Reminder Manual", "Lihat Laporan"

**Technical Notes:**
- Real-time data (refresh every 5 minutes atau manual refresh)
- Quick filters: bulan, tahun ajaran
- Responsive: mobile & desktop optimized
- Card dengan drill-down link

---

### Parent Portal (5 points)

#### US-PAY-006: Lihat Tagihan & Bayar (Portal Orang Tua)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Orang Tua  
**I want** melihat tagihan anak saya dan status pembayaran  
**So that** saya tahu kapan harus bayar dan berapa jumlahnya

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua klik tab "Pembayaran" pada profil anak  
   **Then** sistem tampilkan:
   - Summary cards (top): 
     - Status SPP Bulan Ini: Lunas/Belum Bayar dengan icon
     - Total Tunggakan: Rp amount (red highlight jika > 0)
     - Tagihan Terdekat: due date & jumlah
   - Tab navigation: "Tagihan Aktif" | "Riwayat Pembayaran"

✅ **Given** ada tagihan SPP Februari yang belum dibayar (Rp 250.000, due date 10 Feb)  
   **When** orang tua di tab "Tagihan Aktif"  
   **Then** sistem tampilkan card/table tagihan:
   - Jenis: SPP, Periode: Februari 2025, Jumlah: Rp 250.000
   - Due Date: 10 Februari 2025
   - Status badge: "Belum Bayar" (kuning) atau "Terlambat" (merah, jika sudah lewat due date)
   - Action button: "Lihat Detail"

✅ **Given** orang tua klik "Lihat Detail" pada tagihan  
   **When** modal detail muncul  
   **Then** sistem tampilkan:
   - Detail lengkap: jenis, periode, jumlah, due date, status
   - Instruksi pembayaran: transfer ke rekening bank (nomor rekening, atas nama)
   - Info: "Setelah transfer, silakan upload bukti transfer di bawah ini"
   - Upload form: file input (foto/PDF), tanggal bayar
   - Button: "Konfirmasi Pembayaran"

✅ **Given** orang tua sudah transfer Rp 250.000 via bank  
   **When** orang tua upload bukti transfer (foto) dan klik "Konfirmasi Pembayaran"  
   **Then** sistem:
   - Simpan bukti transfer
   - Update status tagihan menjadi "Menunggu Verifikasi" (status badge kuning)
   - Kirim notifikasi ke TU: "Pembayaran baru perlu diverifikasi dari {nama_ortu} untuk {nama_siswa}"
   - Tampilkan notifikasi sukses: "Bukti pembayaran berhasil diupload. Menunggu verifikasi dari TU."

✅ **Given** orang tua di tab "Riwayat Pembayaran"  
   **When** tab load  
   **Then** sistem tampilkan table riwayat:
   - Tanggal, Jenis, Periode, Jumlah, Metode, Nomor Kwitansi
   - Action: "Download Kwitansi" (PDF)
   - Filter: Bulan, Tahun
   - Pagination

✅ **Given** orang tua klik "Download Kwitansi" pada pembayaran yang sudah lunas  
   **When** klik button  
   **Then** sistem langsung download file PDF kwitansi (no popup, direct download)

**Technical Notes:**
- Read-only untuk riwayat pembayaran (tidak bisa edit/delete)
- Hanya tampilkan data anak sendiri (filtered by parent_id)
- Empty state friendly: "Belum ada tagihan" / "Belum ada riwayat pembayaran"
- Skeleton loading saat fetch data
- Mobile-optimized: card-based layout, swipeable tabs

---

#### US-PAY-007: Verifikasi Pembayaran Transfer (TU)
**Priority:** Must Have | **Estimation:** S (2 points)

**As a** TU/Admin  
**I want** verifikasi pembayaran transfer dari orang tua  
**So that** pembayaran yang sudah ditransfer dapat dikonfirmasi dan dicatat

**Acceptance Criteria:**

✅ **Given** TU di halaman "Verifikasi Pembayaran"  
   **When** halaman load  
   **Then** sistem tampilkan:
   - Badge count: "5 Menunggu Verifikasi" (di sidebar menu)
   - Table list pembayaran dengan status "Menunggu Verifikasi"
   - Kolom: Tanggal Upload, Nama Siswa, Kelas, Jenis Pembayaran, Periode, Jumlah, Action

✅ **Given** TU klik salah satu pembayaran untuk verifikasi  
   **When** TU klik "Lihat Detail"  
   **Then** sistem tampilkan modal:
   - Nama siswa, kelas, NIS
   - Jenis pembayaran, periode, jumlah
   - Tanggal bayar (input orang tua)
   - Bukti transfer (preview gambar/PDF, bisa zoom)
   - Button: "Konfirmasi" (hijau), "Tolak" (merah), "Batal"

✅ **Given** TU cek rekening dan transfer sudah masuk sesuai  
   **When** TU klik "Konfirmasi Pembayaran"  
   **Then** sistem:
   - Generate nomor kwitansi otomatis
   - Save payment record ke tabel `payments`
   - Update bill status → "Lunas" (Paid)
   - Kirim notifikasi ke orang tua: "Pembayaran SPP Februari 2025 untuk {nama_siswa} telah dikonfirmasi. Nomor kwitansi: KWT/2025/02/0012. Terima kasih."
   - Tampilkan success toast: "Pembayaran berhasil diverifikasi"

✅ **Given** bukti transfer tidak sesuai (jumlah salah Rp 200.000, seharusnya Rp 250.000)  
   **When** TU klik "Tolak" dan input keterangan "Jumlah transfer tidak sesuai (Rp 200.000, seharusnya Rp 250.000)"  
   **Then** sistem:
   - Update status tagihan kembali "Belum Bayar"
   - Hapus bukti transfer yang diupload
   - Kirim notifikasi ke orang tua: "Pembayaran Anda ditolak. Alasan: Jumlah transfer tidak sesuai (Rp 200.000, seharusnya Rp 250.000). Silakan upload ulang bukti transfer yang benar."
   - Tampilkan success toast: "Pembayaran ditolak dengan alasan"

✅ **Given** TU ingin batch verification (verifikasi banyak sekaligus)  
   **When** TU centang 5 pembayaran yang sudah sesuai dan klik "Verifikasi Semua"  
   **Then** sistem verifikasi 5 pembayaran tersebut secara batch

**Technical Notes:**
- Real-time notification ke TU saat ada upload bukti baru (WebSocket atau polling)
- Preview bukti transfer: support image (jpg, png) dan PDF
- Log rejection dengan alasan (audit trail)
- Optional: integrate dengan rekening bank untuk auto-reconciliation (Phase 2)

---

### Financial Reports (6 points)

#### US-PAY-008: Laporan Keuangan (Pemasukan)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah/TU  
**I want** melihat laporan keuangan pemasukan  
**So that** saya dapat monitoring cash flow sekolah

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Laporan Keuangan"  
   **When** kepala sekolah pilih periode "Desember 2025", jenis pembayaran "Semua", dan klik "Tampilkan"  
   **Then** sistem tampilkan:
   - Summary cards:
     - Total Pemasukan: Rp 52.000.000 (icon uang, hijau)
     - Total Piutang: Rp 8.500.000 (icon warning, merah)
     - Tingkat Kolektibilitas: 86% (progress bar)
     - Jumlah Siswa Menunggak: 18 siswa (icon user, merah)
   - Breakdown per jenis pembayaran (table):
     - Jenis: SPP, Uang Gedung, Seragam, Kegiatan
     - Jumlah Tagihan, Jumlah Terbayar, Piutang, Persentase
   - Chart section:
     - Bar Chart: Pemasukan per jenis pembayaran
     - Line Chart: Tren pemasukan per bulan (6 bulan terakhir)

✅ **Given** kepala sekolah ingin lihat detail transaksi SPP  
   **When** kepala sekolah klik kategori "SPP" di breakdown table  
   **Then** sistem tampilkan list detail transaksi SPP untuk periode Desember 2025:
   - Tanggal, Nama Siswa, Kelas, Periode, Jumlah, Metode, Status, Nomor Kwitansi

✅ **Given** kepala sekolah ingin export laporan untuk akuntan  
   **When** kepala sekolah klik "Export ke Excel"  
   **Then** sistem generate file Excel (.xlsx) dengan sheet:
   - Sheet 1: Summary (total pemasukan, piutang, kolektibilitas)
   - Sheet 2: Detail Transaksi (tanggal, invoice, siswa, jenis, jumlah, metode, petugas)
   - Sheet 3: Breakdown per Jenis

✅ **Given** ada siswa yang belum bayar (tunggakan)  
   **When** laporan di-generate  
   **Then** sistem tampilkan section "Tunggakan":
   - Table: NIS, Nama Siswa, Kelas, Total Tunggakan, Lama Tunggakan (hari), Jumlah Bulan
   - Sort by: total tunggakan (descending)
   - Highlight siswa dengan tunggakan > 3 bulan (high risk, warna merah)
   - Action: "Kirim Reminder Manual" (button per siswa)

✅ **Given** kepala sekolah ingin print laporan  
   **When** kepala sekolah klik "Export ke PDF"  
   **Then** sistem generate PDF formatted report dengan:
   - Header: logo, nama sekolah, periode laporan
   - Summary cards dengan visual
   - Chart (bar & line chart included)
   - Table breakdown
   - Footer: tanggal cetak, nama user yang print

**Technical Notes:**
- Filter: per periode (bulan/tahun), per jenis pembayaran, per kelas
- Chart interactive: hover untuk lihat detail
- Color coding: hijau (lunas), merah (tunggakan), kuning (partial)
- Export format: Excel (.xlsx) dan PDF
- Performance: optimize query untuk large data (1000+ transaksi)

---

### Optional Features (Phase 2 - Not MVP)

#### US-PAY-010: Integrasi Payment Gateway (Phase 2)
**Priority:** Could Have (Phase 2) | **Estimation:** L (5 points)

**As a** Orang Tua  
**I want** bayar SPP langsung via payment gateway (VA/QRIS)  
**So that** saya tidak perlu transfer manual dan menunggu verifikasi

**Acceptance Criteria:**
- Orang tua klik "Bayar Sekarang" → redirect ke payment gateway (Midtrans/Xendit)
- Pilih metode: Virtual Account, QRIS, E-wallet (GoPay, OVO, Dana)
- Payment sukses → sistem auto-update status pembayaran jadi "Lunas"
- No manual verification needed dari TU

**Technical Notes:**
- Payment Gateway: Midtrans / Xendit / Doku
- Webhook untuk payment confirmation
- Auto-reconciliation
- Biaya admin: configurable (ditanggung sekolah atau orang tua)

---

## 🏗️ Technical Architecture

### Database Schema Requirements

#### Bills Table (Tagihan)
```sql
CREATE TABLE bills (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  student_id UUID NOT NULL REFERENCES students(id),
  payment_category_id UUID NOT NULL REFERENCES payment_categories(id),
  academic_year_id UUID NOT NULL REFERENCES academic_years(id),
  period VARCHAR(7), -- Format: YYYY-MM untuk SPP bulanan (e.g., "2025-01")
  amount DECIMAL(15,2) NOT NULL,
  discount_amount DECIMAL(15,2) DEFAULT 0,
  total_amount DECIMAL(15,2) GENERATED ALWAYS AS (amount - discount_amount) STORED,
  paid_amount DECIMAL(15,2) DEFAULT 0,
  remaining_amount DECIMAL(15,2) GENERATED ALWAYS AS (total_amount - paid_amount) STORED,
  status VARCHAR(20) NOT NULL DEFAULT 'unpaid', -- unpaid, partial_paid, paid, cancelled
  due_date DATE NOT NULL,
  description TEXT,
  created_by UUID REFERENCES users(id),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_student_period (student_id, period),
  INDEX idx_status (status),
  INDEX idx_due_date (due_date)
);
```

#### Payments Table (Pembayaran)
```sql
CREATE TABLE payments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  bill_id UUID NOT NULL REFERENCES bills(id),
  receipt_number VARCHAR(50) UNIQUE NOT NULL, -- KWT/2025/01/0001
  student_id UUID NOT NULL REFERENCES students(id),
  amount DECIMAL(15,2) NOT NULL,
  payment_method VARCHAR(20) NOT NULL, -- cash, bank_transfer, edc, payment_gateway
  payment_date DATE NOT NULL,
  reference_number VARCHAR(100), -- untuk transfer bank
  proof_file_url TEXT, -- bukti transfer
  notes TEXT,
  status VARCHAR(20) DEFAULT 'confirmed', -- pending_verification, confirmed, cancelled
  verified_at TIMESTAMP,
  verified_by UUID REFERENCES users(id),
  cancelled_at TIMESTAMP,
  cancelled_by UUID REFERENCES users(id),
  cancellation_reason TEXT,
  created_by UUID NOT NULL REFERENCES users(id),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_receipt_number (receipt_number),
  INDEX idx_student_payment_date (student_id, payment_date),
  INDEX idx_status (status)
);
```

#### Payment Categories Table (Jenis Pembayaran)
```sql
CREATE TABLE payment_categories (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(100) NOT NULL,
  code VARCHAR(50) UNIQUE NOT NULL, -- SPP, UANG_GEDUNG, SERAGAM, dll
  description TEXT,
  type VARCHAR(20) NOT NULL, -- one_time, monthly, yearly, ad_hoc
  default_amount DECIMAL(15,2),
  is_mandatory BOOLEAN DEFAULT false,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Payment Settings Table (Konfigurasi SPP per Kelas)
```sql
CREATE TABLE payment_settings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  class_id UUID REFERENCES classes(id),
  payment_category_id UUID NOT NULL REFERENCES payment_categories(id),
  amount DECIMAL(15,2) NOT NULL,
  academic_year_id UUID NOT NULL REFERENCES academic_years(id),
  effective_from DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(class_id, payment_category_id, academic_year_id)
);
```

#### Student Discounts Table (Potongan per Siswa)
```sql
CREATE TABLE student_discounts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  student_id UUID NOT NULL REFERENCES students(id),
  payment_category_id UUID NOT NULL REFERENCES payment_categories(id),
  discount_type VARCHAR(20) NOT NULL, -- percentage, fixed_amount
  discount_value DECIMAL(15,2) NOT NULL,
  reason VARCHAR(255), -- beasiswa, anak guru, saudara kandung, dll
  effective_from DATE NOT NULL,
  effective_until DATE,
  is_active BOOLEAN DEFAULT true,
  created_by UUID REFERENCES users(id),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_student_category (student_id, payment_category_id)
);
```

#### Payment Reminders Log Table
```sql
CREATE TABLE payment_reminder_logs (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  bill_id UUID NOT NULL REFERENCES bills(id),
  student_id UUID NOT NULL REFERENCES students(id),
  parent_id UUID NOT NULL REFERENCES users(id),
  reminder_type VARCHAR(20) NOT NULL, -- h_minus_5, due_date, h_plus_7
  message TEXT NOT NULL,
  channel VARCHAR(20) NOT NULL, -- whatsapp, email
  status VARCHAR(20) NOT NULL, -- sent, failed, pending
  sent_at TIMESTAMP,
  error_message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Bank Reconciliation Table (Opsional - MVP Should Have)
```sql
CREATE TABLE bank_reconciliations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  payment_id UUID REFERENCES payments(id),
  bank_transaction_date DATE NOT NULL,
  bank_amount DECIMAL(15,2) NOT NULL,
  bank_reference VARCHAR(100),
  bank_description TEXT,
  matched_status VARCHAR(20) DEFAULT 'unmatched', -- matched, unmatched, manual_matched
  matched_by UUID REFERENCES users(id),
  matched_at TIMESTAMP,
  verified BOOLEAN DEFAULT false,
  verified_by UUID REFERENCES users(id),
  verified_at TIMESTAMP,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### API Endpoints

#### Bills Management
- `GET /api/bills` - List all bills (dengan filter: student_id, status, period, class_id)
- `POST /api/bills/generate-monthly-spp` - Generate tagihan SPP bulanan
- `POST /api/bills/create-custom` - Create tagihan custom (non-SPP)
- `GET /api/bills/:id` - Get bill detail
- `PUT /api/bills/:id` - Update bill
- `DELETE /api/bills/:id` - Cancel bill
- `GET /api/bills/student/:student_id` - Get bills untuk siswa tertentu
- `GET /api/bills/overdue` - Get list tagihan yang sudah lewat due date

#### Payments Management
- `GET /api/payments` - List all payments (paginated, dengan filter)
- `POST /api/payments` - Record manual payment
- `GET /api/payments/:id` - Get payment detail
- `PUT /api/payments/:id` - Update payment
- `DELETE /api/payments/:id/cancel` - Cancel payment (with reason)
- `GET /api/payments/receipt/:receipt_number` - Get payment by receipt number
- `POST /api/payments/verify/:id` - Verify payment (TU)
- `POST /api/payments/reject/:id` - Reject payment verification
- `GET /api/payments/pending-verification` - Get list payments pending verification
- `GET /api/payments/receipt/:id/pdf` - Generate kwitansi PDF

#### Payment Categories & Settings
- `GET /api/payment-categories` - List all payment categories
- `POST /api/payment-categories` - Create payment category
- `PUT /api/payment-categories/:id` - Update payment category
- `DELETE /api/payment-categories/:id` - Deactivate category

#### SPP Settings
- `GET /api/payment-settings/spp` - Get SPP settings per kelas
- `PUT /api/payment-settings/spp` - Update SPP amount per kelas
- `GET /api/student-discounts/:student_id` - Get discount untuk siswa
- `POST /api/student-discounts` - Create/update student discount
- `DELETE /api/student-discounts/:id` - Remove discount

#### Financial Reports
- `GET /api/reports/income` - Laporan pemasukan (filter: period, payment_type, class)
- `GET /api/reports/outstanding` - Laporan piutang/tunggakan
- `GET /api/reports/daily-cashflow` - Laporan harian kasir
- `GET /api/reports/by-class` - Laporan per kelas
- `POST /api/reports/export` - Export laporan (Excel/PDF)

#### Payment Reminders
- `POST /api/reminders/send-manual` - Send reminder manual ke siswa tertentu
- `GET /api/reminders/logs` - Get reminder logs
- `POST /api/reminders/schedule` - Configure reminder schedule

#### Bank Reconciliation (Optional MVP)
- `POST /api/reconciliation/upload` - Upload mutasi bank (Excel/CSV)
- `GET /api/reconciliation/unmatched` - Get unmatched transactions
- `POST /api/reconciliation/match` - Manual match transaction
- `POST /api/reconciliation/verify` - Verify reconciliation result
- `GET /api/reconciliation/history` - Reconciliation history

#### Parent Portal
- `GET /api/parent/bills` - Get tagihan anak orang tua login
- `GET /api/parent/payments` - Get riwayat pembayaran anak
- `POST /api/parent/upload-proof` - Upload bukti transfer
- `GET /api/parent/receipt/:payment_id/download` - Download kwitansi

---

### Business Logic Implementation

#### Generate Monthly SPP Bills
```
Input: month, year, academic_year_id, class_id (optional, null = all classes)

Process:
1. Validate: cek apakah tagihan untuk periode tersebut sudah ada
   - Query: SELECT COUNT(*) FROM bills WHERE period = '{year-month}' AND payment_category_id = 'SPP'
   - Jika > 0: prompt "Tagihan sudah ada, generate ulang?"

2. Get active students (filter by class_id if specified)
   - Query: SELECT * FROM students WHERE status = 'active' AND (class_id = X OR X IS NULL)

3. For each student:
   a. Get SPP amount:
      - Cek payment_settings untuk kelas siswa & tahun ajaran
      - Fallback: default amount dari payment_categories
   
   b. Get discount (jika ada):
      - Query student_discounts WHERE student_id = X AND payment_category_id = 'SPP' AND is_active = true
      - Calculate: discount_amount = (discount_type == 'percentage') ? amount * (discount_value/100) : discount_value
   
   c. Calculate total:
      - total_amount = amount - discount_amount
   
   d. Create bill:
      - INSERT INTO bills (student_id, payment_category_id, period, amount, discount_amount, due_date, status)
      - due_date = first day of month + 9 days (tanggal 10, configurable)

4. Return summary: total siswa, total tagihan created, total amount

5. (Optional) Trigger notification ke orang tua
```

#### Record Manual Payment
```
Input: bill_id, amount, payment_method, payment_date, reference_number, notes

Process:
1. Validate:
   - Cek bill exists dan status != 'cancelled'
   - Cek amount <= remaining_amount (tidak boleh overpayment)

2. Generate receipt number:
   - Format: KWT/{YYYY}/{MM}/{NNNN}
   - NNNN = auto-increment per bulan (get last receipt number for current month + 1)

3. Create payment record:
   - INSERT INTO payments (bill_id, student_id, receipt_number, amount, payment_method, payment_date, ...)
   - status = 'confirmed' (untuk payment manual)

4. Update bill:
   - paid_amount = paid_amount + amount
   - remaining_amount = total_amount - paid_amount (auto-calculated)
   - status = (remaining_amount == 0) ? 'paid' : 'partial_paid'

5. Generate kwitansi PDF:
   - Call PDF generator service
   - Return PDF URL untuk print

6. Log activity:
   - INSERT INTO activity_logs (user_id, action='create_payment', module='payments', ...)

7. Return: payment_id, receipt_number, pdf_url
```

#### Payment Reminder Cronjob
```
Schedule: Daily at 06:00 WIB

Process:
1. Get current date
2. Calculate reminder dates:
   - h_minus_5 = today + 5 days
   - due_date = today
   - h_plus_7 = today - 7 days

3. For each reminder type:
   a. Query unpaid bills:
      - H-5: due_date = h_minus_5 AND status IN ('unpaid', 'partial_paid')
      - Due: due_date = today AND status IN ('unpaid', 'partial_paid')
      - H+7: due_date = h_plus_7 AND status IN ('unpaid', 'partial_paid')
   
   b. For each bill:
      - Check: sudah pernah kirim reminder type ini untuk bill ini? (check payment_reminder_logs)
      - Skip jika sudah pernah kirim
      
      - Get parent contact (phone/email)
      - Build message dari template (replace {nama_ortu}, {nama_siswa}, {jumlah}, {due_date}, dll)
      
      - Send via WhatsApp API (primary) atau Email (fallback)
      
      - Log: INSERT INTO payment_reminder_logs (bill_id, reminder_type, message, channel, status, sent_at)

4. Send summary notification ke TU:
   - "{X} reminder berhasil dikirim, {Y} gagal"
```

---

### Security Implementation

#### Payment Validation
- Validasi amount: tidak boleh negatif, tidak boleh overpayment
- Validasi bill status: tidak boleh bayar bill yang sudah cancelled
- Validasi permission: hanya TU/Admin yang bisa record payment
- Validasi date: payment_date tidak boleh future date

#### Receipt Number Generation
- Format fixed: KWT/{YYYY}/{MM}/{NNNN}
- Auto-increment per bulan (reset setiap bulan baru)
- Unique constraint di database
- Transaction lock untuk prevent race condition

#### File Upload (Bukti Transfer)
- Allowed file types: JPG, PNG, PDF (max 5MB)
- Virus scan sebelum save
- Store di secure storage (S3/Cloud Storage)
- Generate signed URL untuk akses (expired 1 jam)

#### RBAC Payment Module
- **Kepala Sekolah:** Read-only (laporan, dashboard)
- **TU/Admin:** Full access (CRUD payments, verify, reports)
- **Guru:** No access (kecuali melihat pembayaran anak sendiri jika anak guru)
- **Orang Tua:** Read-only anak sendiri + upload bukti transfer

---

## 🎨 UI/UX Design Requirements

### Payment Recording Page (TU)

**Layout:**
- Header: "Catat Pembayaran Baru"
- 2 Column Layout (desktop), 1 column (mobile):
  - **Left Column:**
    - Student search autocomplete dengan avatar & info (Nama, Kelas, NIS)
    - List unpaid bills dengan checkbox (multi-select untuk batch payment)
    - Per bill card: Jenis, Periode, Jumlah, Due Date, Status badge
    - Total tagihan dipilih (bold, large)
  
  - **Right Column:**
    - Form pembayaran:
      - Jumlah Dibayar (auto-filled dari total tagihan dipilih, editable)
      - Terbilang (auto-generate, contoh: "Dua Ratus Lima Puluh Ribu Rupiah")
      - Metode Pembayaran (dropdown: Tunai, Transfer Bank, EDC)
      - Nomor Referensi (required jika Transfer Bank)
      - Tanggal Bayar (date picker, default: hari ini)
      - Keterangan (optional, textarea)
    - Footer: Button "Batal" (secondary) | "Simpan & Cetak Kwitansi" (primary, large)

**UX Features:**
- **Student Search:**
  - Real-time autocomplete (debounce 300ms)
  - Search by: nama siswa, NIS
  - Tampilkan avatar, nama, kelas, NIS di dropdown
  
- **Unpaid Bills:**
  - Checkbox multi-select
  - Sort by: due date (ascending, terdekat di atas)
  - Color coding: Belum Bayar (kuning), Terlambat (merah)
  
- **Jumlah Input:**
  - Format Rupiah real-time (separator ribuan)
  - Auto-calculate terbilang di bawah input
  - Warning jika partial payment: "Jumlah < total tagihan. Status akan menjadi 'Cicilan'"
  
- **After Save:**
  - Success toast dengan nomor kwitansi
  - Auto-open print dialog (kwitansi PDF)
  - Option button: "Cetak Ulang" | "Input Pembayaran Baru" | "Selesai"

**Mobile:**
- Single column layout
- Sticky footer dengan button
- Touch-friendly form (min height 48px)
- Native keyboard untuk input angka
- Native date picker

---

### Generate SPP Bills Page (TU)

**Layout - Wizard Style (3 Steps):**

**Step 1: Input**
- Header: "Generate Tagihan SPP Bulanan"
- Form fields:
  - Dropdown: Bulan (Jan - Des)
  - Dropdown: Tahun Ajaran (2024/2025)
  - Dropdown: Kelas (All / Kelas 1A, 1B, dst)
- Button: "Lanjut ke Preview" (primary)

**Step 2: Preview**
- Header: "Preview Tagihan SPP - {Bulan} {Tahun}"
- Summary Card:
  - Total Siswa: 180
  - Total Tagihan: Rp 54.000.000
- Table: Nama Siswa | Kelas | SPP Normal | Potongan | Total Tagihan
  - Highlight siswa dengan potongan (badge "Beasiswa" / "Potongan 25%")
  - Max 50 rows dengan pagination
- Footer: "Kembali" (secondary) | "Generate Tagihan" (primary, bold)

**Step 3: Success**
- Success animation (checkmark icon)
- Message: "180 tagihan SPP {bulan} {tahun} berhasil di-generate!"
- Summary:
  - Total Siswa: 180
  - Total Tagihan: Rp 54.000.000
  - Tagihan dengan Potongan: 12 siswa
- Button: "Lihat Daftar Tagihan" | "Generate Bulan Lain" | "Selesai"

**UX Features:**
- Progress indicator (Step 1/3, 2/3, 3/3)
- Loading indicator saat generate (jika banyak siswa)
- Validation: warning jika periode sudah pernah di-generate
- Konfirmasi dialog sebelum generate

---

### Parent Payment Dashboard

**Layout:**

**Summary Cards (Top)**
- Card 1: **Status SPP Bulan Ini**
  - Icon: checklist (hijau) atau warning (merah)
  - Text: "Lunas" / "Belum Bayar" / "Terlambat"
  - Sub-text: Periode, Jumlah
  
- Card 2: **Total Tunggakan**
  - Large number: Rp 0 (hijau) atau Rp 500.000 (merah, bold)
  - Sub-text: "0 bulan" / "2 bulan (Des, Jan)"
  
- Card 3: **Tagihan Terdekat**
  - Text: "SPP Februari 2025"
  - Sub-text: "Jatuh tempo: 10 Februari 2025"
  - Amount: Rp 250.000

**Tab Navigation:**
- Tab 1: **Tagihan Aktif** (badge count: 2)
- Tab 2: **Riwayat Pembayaran**

**Tab 1: Tagihan Aktif**
- Card/Table per tagihan:
  - Header: Jenis Pembayaran (bold)
  - Periode: Februari 2025
  - Jumlah: Rp 250.000 (large)
  - Due Date: 10 Februari 2025
  - Status badge: Belum Bayar (kuning) / Terlambat (merah) / Cicilan (kuning)
  - Jika partial: Progress bar (Rp 150.000 / Rp 250.000 terbayar)
  - Button: "Bayar Sekarang" (primary) | "Lihat Detail"
  
- Empty state: "Tidak ada tagihan aktif. Semua pembayaran sudah lunas 🎉"

**Tab 2: Riwayat Pembayaran**
- Table/List:
  - Tanggal | Jenis | Periode | Jumlah | Metode | Nomor Kwitansi | Action
  - Action: Icon download (download kwitansi PDF)
- Filter: Bulan, Tahun (dropdown)
- Pagination
- Empty state: "Belum ada riwayat pembayaran"

**UX Features:**
- **Card "Bayar Sekarang":**
  - Click → Modal muncul dengan instruksi transfer:
    - Bank: BCA
    - No Rekening: 1234567890
    - Atas Nama: SD Negeri 1 Jakarta
    - Jumlah: Rp 250.000
    - Copy button untuk nomor rekening
  - Form upload bukti transfer (drag & drop atau browse)
  - Input tanggal bayar
  - Button: "Konfirmasi Pembayaran"
  
- **Download Kwitansi:**
  - Direct download PDF (no modal)
  - Loading indicator saat generate PDF
  
- **Notification:**
  - Badge notification jika ada tagihan yang belum dibayar (due date - 3 hari)

**Mobile:**
- Summary cards stack 2x2 atau 1x3
- Swipeable tabs
- Card-based layout untuk tagihan (tidak table)
- FAB (Floating Action Button): "Hubungi Sekolah" (WhatsApp)

---

### Financial Report Dashboard (Principal/TU)

**Layout:**

**Summary Cards (Top Row):**
- Card 1: **Total Pemasukan Bulan Ini**
  - Large number: Rp 52.000.000
  - Icon: trending up (hijau)
  - Sub-text: "+12% dari bulan lalu"
  
- Card 2: **Total Piutang**
  - Large number: Rp 8.500.000
  - Icon: alert circle (merah)
  - Sub-text: "18 siswa menunggak"
  
- Card 3: **Tingkat Kolektibilitas**
  - Large number: 86%
  - Progress bar (hijau/kuning/merah based on %)
  - Icon: target
  
- Card 4: **Siswa Menunggak**
  - Large number: 18
  - Icon: users (merah)
  - Sub-text: "3 siswa > 3 bulan"

**Filters Section:**
- Dropdown: Bulan (Jan - Des)
- Dropdown: Tahun (2023, 2024, 2025)
- Dropdown: Jenis Pembayaran (Semua / SPP / Uang Gedung / dll)
- Button: "Tampilkan" (primary) | "Export" (dropdown: Excel, PDF)

**Charts Section (2 Columns):**
- **Left:** Bar Chart - Pemasukan per Jenis Pembayaran
  - X-axis: Jenis (SPP, Uang Gedung, Seragam, dll)
  - Y-axis: Jumlah (Rupiah)
  - Interactive: hover untuk detail
  
- **Right:** Line Chart - Tren Pemasukan 6 Bulan Terakhir
  - X-axis: Bulan (Jul - Des)
  - Y-axis: Jumlah (Rupiah)
  - Multiple lines: Pemasukan, Piutang

**Table Section:**
- **Tab Navigation:** Pemasukan | Piutang | Per Kelas | Daily Cashflow
  
- **Tab Pemasukan:** Breakdown per jenis
  - Table: Jenis | Jumlah Tagihan | Terbayar | Piutang | Persentase
  
- **Tab Piutang:** List siswa menunggak
  - Table: NIS | Nama Siswa | Kelas | Total Tunggakan | Lama (hari) | Bulan | Action
  - Action: "Kirim Reminder" button
  - Highlight: siswa dengan tunggakan > 3 bulan (background merah muda)
  
- **Tab Per Kelas:**
  - Table: Kelas | Jumlah Siswa | Total Tagihan | Terbayar | Piutang | %
  
- **Tab Daily Cashflow:** List pembayaran hari ini
  - Table: Waktu | Nama Siswa | Kelas | Jenis | Jumlah | Metode | Nomor Kwitansi
  - Summary footer: Total Tunai | Total Transfer | Grand Total

**UX Features:**
- **Interactive Charts:**
  - Hover untuk lihat detail angka
  - Click legend untuk hide/show series
  
- **Color Coding:**
  - Hijau: pemasukan, lunas, kolektibilitas > 90%
  - Kuning: partial, kolektibilitas 70-90%
  - Merah: piutang, tunggakan, kolektibilitas < 70%
  
- **Export:**
  - Excel: full data dengan multiple sheets
  - PDF: formatted report dengan chart images
  
- **Filter:**
  - Auto-update chart & table saat filter berubah (no reload)
  
- **Drill-down:**
  - Click card → navigate ke detail page
  - Click bar chart → filter table by jenis

**Mobile:**
- Summary cards stack 1x4
- Charts full-width, swipeable (swipe left/right untuk lihat chart lain)
- Table collapse jadi card list (vertical)
- Sticky filter bar at top

---

### Receipt (Kwitansi) Template

**Format:** A5 Portrait (atau configurable: Thermal 58mm/80mm)

**Layout:**
```
+--------------------------------------------+
|  [LOGO]        NAMA SEKOLAH                |
|         Alamat Lengkap Sekolah             |
|         Telp: (021) 1234567                |
+--------------------------------------------+
|                                            |
|       KWITANSI PEMBAYARAN                  |
|                                            |
|  No: KWT/2025/01/0012                      |
|  Tanggal: 12 Desember 2025                 |
+--------------------------------------------+
|                                            |
|  Telah terima dari:                        |
|  Nama Siswa  : Ahmad Rizki Hidayat         |
|  Kelas       : 3A                          |
|  NIS         : 20250123                    |
|                                            |
|  Untuk pembayaran:                         |
|  SPP Bulan Januari 2025                    |
|                                            |
|  Jumlah      : Rp 250.000                  |
|  Terbilang   : Dua Ratus Lima Puluh        |
|                Ribu Rupiah                 |
|                                            |
|  Metode      : Transfer Bank BCA           |
|  Ref         : 1234567890123               |
+--------------------------------------------+
|                                            |
|           [QR CODE]                        |
|                                            |
|  Petugas: Siti Nurhaliza (TU)              |
|  [Tanda Tangan Digital]                    |
|  [Stempel Sekolah]                         |
+--------------------------------------------+
|  Harap simpan kwitansi ini sebagai         |
|  bukti pembayaran yang sah.                |
+--------------------------------------------+
```

**Design Elements:**
- **Watermark:** "LUNAS" (semi-transparent, diagonal background)
- **QR Code:** Encode `{receipt_number}|{date}|{amount}` untuk verifikasi
- **Font:** Professional (Arial/Helvetica), size readable
- **Border:** Simple box border, tidak terlalu tebal
- **Logo:** School logo di header (auto-resize, max height 60px)

**Technical:**
- PDF generation: library Puppeteer atau wkhtmltopdf
- Support print ke: Thermal printer (58mm/80mm) atau A4/A5 office printer
- Template customizable via settings (logo, footer text)

---

## ✅ Definition of Done

### Code Level
- [ ] Unit test coverage minimal 80%
- [ ] Integration test untuk payment flow (create, verify, cancel)
- [ ] Code review approved
- [ ] No critical linter errors
- [ ] API endpoints documented (Swagger)
- [ ] Database migration tested (up & down)

### Functionality
- [ ] All acceptance criteria met dan tested untuk 12 user stories
- [ ] Payment recording (manual) working dengan kwitansi generation
- [ ] Generate monthly SPP bills working untuk 500+ siswa
- [ ] Parent portal dapat lihat tagihan & upload bukti transfer
- [ ] TU dapat verifikasi pembayaran transfer
- [ ] Financial reports working dengan export Excel & PDF
- [ ] Payment reminder cronjob tested dan running
- [ ] RBAC implemented: TU full access, Parent read-only anak sendiri, Principal read-only reports

### UI/UX
- [ ] Responsive di mobile dan desktop (tested iOS & Android)
- [ ] Loading state untuk semua async actions (payment recording, generate bills, upload)
- [ ] Error handling dengan user-friendly message (Bahasa Indonesia)
- [ ] Success feedback (toast notification)
- [ ] Empty state untuk list kosong (tagihan, riwayat pembayaran)
- [ ] Skeleton loading saat fetch data
- [ ] Print dialog kwitansi working di semua browser

### Data Integrity
- [ ] Payment transaction atomic (rollback jika error)
- [ ] No duplicate receipt number (tested dengan concurrent requests)
- [ ] Bill status auto-update setelah payment (unpaid → partial_paid → paid)
- [ ] Partial payment calculation correct (paid_amount, remaining_amount)
- [ ] Discount calculation correct (percentage & fixed amount)

### Performance
- [ ] Payment recording time < 3 detik (95th percentile)
- [ ] Generate 500 bills < 10 detik
- [ ] Financial report load time < 5 detik
- [ ] Kwitansi PDF generation < 2 detik
- [ ] Database queries optimized (use indexes, no N+1 query)

### Security
- [ ] Payment amount validation (tidak boleh negatif, overpayment)
- [ ] Receipt number unique constraint enforced
- [ ] File upload validation (type, size, virus scan)
- [ ] RBAC: Parent hanya akses data anak sendiri
- [ ] Activity log untuk payment create, verify, cancel

### Documentation
- [ ] API documentation complete (Swagger/Postman)
- [ ] Database schema documented dengan ERD
- [ ] User manual untuk TU (Bahasa Indonesia): cara catat pembayaran, generate tagihan, verifikasi transfer
- [ ] User manual untuk Orang Tua: cara lihat tagihan, upload bukti transfer, download kwitansi
- [ ] Cronjob documentation (reminder schedule, how to run)

---

## 🔗 Dependencies

### External Dependencies
- **Email Service:** SMTP (Gmail/SendGrid) untuk reminder email (fallback dari WhatsApp)
- **WhatsApp API:** Fonnte/Wablas untuk reminder notifikasi (primary channel)
- **PDF Generator:** Puppeteer atau wkhtmltopdf untuk kwitansi generation
- **File Storage:** AWS S3 atau Cloud Storage untuk bukti transfer
- **Cronjob:** Cron scheduler untuk payment reminder daily job

### Internal Dependencies (Must Complete First)
- **Epic 1 (Foundation):** Authentication, RBAC, Settings (payment categories, school settings)
- **Epic 2 (Student Management):** Student data, class data, parent data (untuk tagihan & reminder)
- **Epic 6 (Notification - Partial):** WhatsApp/Email notification service (untuk reminder)

### Blocking For
- **Epic 7 (Reports):** Financial data dari payment module untuk annual reports
- **Epic 8 (PSB):** Pembayaran uang pendaftaran untuk calon siswa baru

---

## 🧪 Testing Strategy

### Unit Testing
- **Service Layer:**
  - Generate monthly SPP bills logic
  - Payment calculation (partial, discount)
  - Receipt number generation
  - Terbilang conversion (number to words in Indonesian)
- **Utility Functions:**
  - Rupiah formatting
  - Date validation
  - Amount validation
- **Target Coverage:** 80%

### Integration Testing
- **Payment Flow:**
  - Create bill → record payment → update bill status → generate kwitansi
  - Partial payment flow: cicilan 1, cicilan 2, lunas
  - Payment cancellation: cancel → bill status revert → audit log
- **Generate Bills:**
  - Generate untuk all classes (500+ siswa)
  - Generate untuk specific class (30 siswa)
  - Prevent duplicate generation untuk periode yang sama
  - Discount calculation: percentage & fixed amount
- **Verification Flow:**
  - Parent upload bukti transfer → TU verify → payment confirmed → notification sent
  - Parent upload bukti → TU reject → notification sent → status revert
- **Financial Reports:**
  - Query income per period, per payment type
  - Calculate outstanding bills (piutang)
  - Export to Excel & PDF

### E2E Testing (Critical Paths)

1. **Happy Path - Manual Payment Recording:**
   - TU login → Navigate to "Catat Pembayaran"
   - Search siswa "Ahmad Rizki"
   - Select tagihan SPP Januari (Rp 250.000)
   - Input amount, pilih metode "Tunai"
   - Click "Simpan & Cetak Kwitansi"
   - **Expected:** Success toast, kwitansi PDF open, bill status → "Lunas"

2. **Generate Monthly SPP Bills:**
   - TU login → Navigate to "Generate Tagihan SPP"
   - Pilih bulan "Februari 2025", kelas "Semua"
   - Click "Preview" → verify total siswa & amount
   - Click "Generate"
   - **Expected:** Success message, 180 tagihan created, tagihan muncul di parent portal

3. **Parent Upload Bukti Transfer & TU Verify:**
   - Orang tua login → Navigate to "Pembayaran"
   - Lihat tagihan SPP Februari (Rp 250.000, status "Belum Bayar")
   - Click "Bayar Sekarang" → upload bukti transfer (JPG)
   - Submit
   - **Expected:** Status → "Menunggu Verifikasi", notification sent to TU
   - TU login → Navigate to "Verifikasi Pembayaran"
   - Lihat bukti transfer → Click "Konfirmasi"
   - **Expected:** Payment confirmed, bill status → "Lunas", notification sent to parent

4. **Payment Reminder Cronjob:**
   - Setup: 1 siswa dengan tagihan SPP due date = 10 Januari, status "Belum Bayar"
   - Run cronjob at tanggal 5 Januari (H-5)
   - **Expected:** WhatsApp reminder sent ke orang tua, log created di payment_reminder_logs

5. **Financial Report & Export:**
   - Kepala Sekolah login → Navigate to "Laporan Keuangan"
   - Filter: Desember 2025, Jenis: Semua
   - Click "Tampilkan"
   - **Expected:** Summary cards, charts, breakdown table loaded
   - Click "Export ke Excel"
   - **Expected:** Excel file downloaded dengan multiple sheets

### Performance Testing
- **Load Test:** 100 TU concurrent record payment
- **Stress Test:** Generate bills untuk 1000 siswa
- **Bulk Operations:** Batch verify 50 pembayaran sekaligus
- **Target:**
  - Payment recording < 3 detik (95th percentile)
  - Generate 500 bills < 10 detik
  - Financial report query < 5 detik

### Security Testing
- **Authorization Test:**
  - Orang tua coba akses tagihan siswa lain → 403 Forbidden
  - Guru coba akses payment module → 403 Forbidden
- **Input Validation:**
  - Input amount negatif → rejected
  - Input amount > remaining_amount (overpayment) → rejected
  - Upload file non-image (exe, zip) → rejected
- **Receipt Number Uniqueness:**
  - Concurrent payment recordings → no duplicate receipt number

---

## 📅 Sprint Planning

### Sprint 4 (2 minggu) - 16 points
**Focus:** Payment Core - Recording, Bills, Receipt

**Stories:**
- US-PAY-001: Catat Pembayaran Manual (3 pts) - **Day 1-3**
- US-PAY-002: Riwayat Pembayaran Siswa (3 pts) - **Day 4-5**
- US-PAY-003: Generate Tagihan Bulanan (3 pts) - **Day 6-8**
- US-PAY-004: Set Nominal SPP (2 pts) - **Day 9**
- US-PAY-009: Catat Pembayaran Non-SPP (2 pts) - **Day 10**
- US-PAY-011: Cetak Kwitansi (2 pts) - **Day 10**

**Deliverables:**
- TU dapat catat pembayaran tunai/transfer dengan kwitansi
- Generate tagihan SPP bulanan untuk semua siswa
- Set nominal SPP per kelas dengan potongan per siswa
- Kwitansi PDF generation working

**Sprint Goal:** "TU dapat mengelola pembayaran dan generate tagihan SPP dengan mudah dan akurat"

---

### Sprint 5 (2 minggu) - 17 points
**Focus:** Parent Portal, Verification, Reports, Reminders

**Stories:**
- US-PAY-006: Portal Orang Tua - Lihat Tagihan (3 pts) - **Day 1-3**
- US-PAY-007: Verifikasi Transfer (2 pts) - **Day 4**
- US-PAY-008: Laporan Keuangan (3 pts) - **Day 5-7**
- US-PAY-005: Reminder Pembayaran (3 pts) - **Day 8-9**
- US-PAY-012: Dashboard Pembayaran (3 pts) - **Day 10-12**

**Deliverables:**
- Orang tua dapat lihat tagihan & upload bukti transfer
- TU dapat verifikasi pembayaran transfer
- Laporan keuangan complete dengan export Excel & PDF
- Payment reminder cronjob active
- Dashboard pembayaran dengan summary & charts

**Sprint Goal:** "Orang tua dapat monitoring pembayaran secara transparent dan sistem dapat auto-remind pembayaran yang belum dibayar"

---

## 🎯 Acceptance Criteria (Epic Level)

### Functional
- [ ] TU dapat catat pembayaran tunai/transfer < 3 menit per transaksi
- [ ] Generate tagihan SPP bulanan untuk 500+ siswa < 10 detik
- [ ] Kwitansi PDF auto-generate dan ready to print
- [ ] Support partial payment (cicilan) dengan tracking sisa tagihan
- [ ] Orang tua dapat lihat tagihan & riwayat pembayaran anak sendiri
- [ ] Orang tua dapat upload bukti transfer dan TU dapat verifikasi
- [ ] Payment reminder auto-send sesuai schedule (H-5, H-0, H+7)
- [ ] Financial reports complete: income, outstanding, daily cashflow, per class
- [ ] Export reports to Excel & PDF working
- [ ] Dashboard pembayaran dengan real-time summary

### Non-Functional
- [ ] Payment recording performance < 3 detik (95th percentile)
- [ ] Generate bills performance < 10 detik untuk 500+ siswa
- [ ] Kwitansi PDF generation < 2 detik
- [ ] Financial report query < 5 detik
- [ ] Mobile-responsive (tested di iOS & Android)
- [ ] Data integrity: no duplicate receipt number, no overpayment
- [ ] Security: RBAC implemented, file upload validation

### Business
- [ ] Tingkat kolektibilitas pembayaran meningkat > 10% (dari baseline)
- [ ] Tingkat keterlambatan pembayaran menurun > 20%
- [ ] 100% tagihan SPP ter-generate otomatis setiap bulan
- [ ] Waktu rekonsiliasi pembayaran < 2 jam per bulan (jika implemented)
- [ ] Kepuasan orang tua terhadap transparansi > 4/5

### Technical
- [ ] Database schema implemented dengan proper indexes
- [ ] API documentation complete (Swagger)
- [ ] Unit test coverage 80%
- [ ] Integration test untuk critical flows
- [ ] Cronjob untuk reminder running stable
- [ ] Monitoring: error tracking (Sentry) dan performance monitoring

---

## 🚧 Risks & Mitigation

### Risk 1: WhatsApp API Delivery Issues
**Impact:** High - Reminder tidak sampai ke orang tua  
**Probability:** Medium  
**Mitigation:**
- Use reliable WhatsApp API provider (Fonnte/Wablas dengan SLA)
- Implement fallback: Email notification jika WhatsApp gagal
- Retry mechanism (max 3 retry)
- Monitor delivery rate daily
- Alert admin jika delivery rate < 90%

### Risk 2: Duplicate Receipt Number (Race Condition)
**Impact:** High - Data integrity issue  
**Probability:** Low  
**Mitigation:**
- Database unique constraint pada receipt_number
- Transaction lock saat generate receipt number
- Use database sequence atau atomic increment
- Comprehensive testing dengan concurrent requests
- Monitor untuk duplicate errors

### Risk 3: Payment Fraud (Fake Bukti Transfer)
**Impact:** High - Financial loss  
**Probability:** Medium  
**Mitigation:**
- Manual verification by TU (human check)
- Optional: Bank reconciliation untuk cross-check dengan mutasi bank
- Watermark bukti transfer yang diupload (auto-add timestamp)
- Log semua verifikasi (who, when, decision)
- Monthly audit oleh kepala sekolah

### Risk 4: Performance Degradation (Large Data Volume)
**Impact:** Medium - Slow response time  
**Probability:** Medium (setelah 2-3 tahun beroperasi)  
**Mitigation:**
- Database indexing pada kolom frequently queried (student_id, period, status, due_date)
- Pagination untuk large list (20-50 items per page)
- Archive old data (> 3 tahun) ke cold storage
- Query optimization: avoid N+1 query, use JOIN efficiently
- Caching untuk financial reports (cache 1 hour)

### Risk 5: Kwitansi PDF Generation Slow/Fail
**Impact:** Medium - TU frustrasi, delay print  
**Probability:** Low  
**Mitigation:**
- Use lightweight PDF library (Puppeteer optimized atau wkhtmltopdf)
- Template simple (tidak terlalu banyak image/font)
- Async generation dengan queue (jika slow)
- Fallback: generate HTML printable version
- Retry mechanism jika generation failed

---

## 📊 Success Metrics & KPIs

### Sprint 4
- [ ] 100% user stories completed (6/6)
- [ ] Payment recording success rate > 99%
- [ ] Generate bills tested dengan 500+ siswa < 10 detik
- [ ] Kwitansi PDF generation success rate > 99%
- [ ] Zero critical bugs in staging

### Sprint 5
- [ ] 100% user stories completed (5/5)
- [ ] Parent portal tested dengan 50+ orang tua (UAT)
- [ ] Payment verification tested: verify & reject flow
- [ ] Financial reports tested dengan 1000+ transaksi
- [ ] Payment reminder cronjob tested: H-5, H-0, H+7 scenarios

### Epic Level (After Sprint 5)
- [ ] Total 33 points delivered (12 user stories)
- [ ] Unit test coverage 80%
- [ ] Zero security vulnerabilities (OWASP Top 10)
- [ ] User acceptance: TU satisfaction > 4/5, Parent satisfaction > 4/5
- [ ] Performance: all critical operations meet target (< 3s, < 10s, < 5s)

### Business Impact (After 3 Months Production)
- [ ] Tingkat kolektibilitas pembayaran > 90% (target: meningkat dari baseline)
- [ ] Tingkat keterlambatan pembayaran menurun > 20%
- [ ] 100% tagihan SPP ter-generate otomatis setiap bulan (no manual)
- [ ] Waktu pencatatan pembayaran < 3 menit per transaksi (vs 10 menit manual)
- [ ] Kepuasan orang tua terhadap transparansi pembayaran > 4.5/5

---

## 📝 Notes & Assumptions

### Assumptions
1. WhatsApp API account sudah disiapkan dan balance cukup untuk reminder
2. Email server properly configured untuk fallback notification
3. Storage (S3/Cloud Storage) tersedia untuk bukti transfer
4. TU terlatih menggunakan system untuk catat pembayaran dan verifikasi
5. Orang tua familiar dengan web/mobile app (untuk upload bukti transfer)
6. School bank account tersedia untuk instruksi pembayaran
7. SPP amount per kelas sudah ditentukan oleh sekolah

### Business Rules
1. **SPP Billing Cycle:**
   - Generate setiap tanggal 1 (awal bulan)
   - Due date: tanggal 10 (configurable)
   - Status "Terlambat" jika belum dibayar setelah due date

2. **Payment Allocation (Multiple Bills):**
   - Jika siswa punya multiple tagihan dan bayar partial, payment dialokasikan ke tagihan tertua (FIFO)
   - TU bisa override allocation (manual pilih tagihan mana yang dibayar)

3. **Receipt Number Format:**
   - KWT/{YYYY}/{MM}/{NNNN}
   - Nomor urut reset setiap bulan baru
   - Kwitansi yang dibatalkan tetap tercatat (nomor tidak di-reuse)

4. **Payment Cancellation:**
   - Hanya TU & Super Admin yang bisa cancel payment
   - Cancel hanya boleh dalam 7 hari setelah payment created (configurable)
   - Harus ada alasan pembatalan (audit trail)
   - Payment yang sudah di-rekonsiliasi tidak bisa di-cancel

5. **Reminder Rules:**
   - Reminder hanya untuk tagihan wajib (SPP, Uang Gedung)
   - Reminder tidak untuk optional (Donasi, Kegiatan) kecuali ada flag khusus
   - Reminder stop setelah 3x kirim atau setelah dibayar
   - Orang tua bisa opt-out dari reminder (setting preference)

### Out of Scope (Epic 4 MVP)
- ❌ Payment Gateway integration (VA, QRIS, E-wallet) - **Phase 2**
- ❌ Bank reconciliation auto-matching via API - **Phase 2**
- ❌ Advanced discount rules (promo, voucher) - **Phase 2**
- ❌ Subscription management (auto-recurring) - **Phase 2**
- ❌ Payment link generation (unique link per siswa) - **Phase 2**
- ❌ Multi-payment method per transaction (kombinasi tunai + transfer) - **Phase 2**
- ❌ Receipt email auto-send - **Phase 2**
- ❌ Integration dengan accounting software (Accurate, Jurnal) - **Phase 2**

### Nice to Have (Not Required for MVP)
- Real-time notification ke TU saat orang tua upload bukti transfer (WebSocket)
- Payment installment plan builder (atur cicilan otomatis)
- Payment analytics & prediction (prediksi payment trend)
- Bulk import pembayaran dari Excel (untuk migrasi data)
- Multiple bank accounts untuk instruksi pembayaran

---

## 🔄 Review & Refinement

### Sprint 4 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner, TU (Key User)

**Review Checklist:**
- [ ] Demo payment recording flow (tunai & transfer)
- [ ] Demo generate monthly SPP bills (untuk 180 siswa)
- [ ] Demo kwitansi PDF generation & print
- [ ] Get feedback dari TU: ease of use, speed, missing features
- [ ] Identify improvement areas
- [ ] Adjust Sprint 5 backlog if needed

### Sprint 5 Review
**Date:** TBD  
**Attendees:** Development Team, Product Owner, TU, Kepala Sekolah, Sample Orang Tua

**Review Checklist:**
- [ ] Demo complete payment system flow (end-to-end)
- [ ] Demo parent portal: lihat tagihan, upload bukti, download kwitansi
- [ ] Demo TU verification flow
- [ ] Demo financial reports & export
- [ ] Demo payment reminder (show WhatsApp message received)
- [ ] User acceptance testing (UAT) with actual users
- [ ] Security review
- [ ] Performance review
- [ ] Collect feedback for post-MVP improvements

---

## ✅ Epic Checklist (Before Moving to Next Epic)

### Development
- [ ] All 12 user stories implemented dan tested
- [ ] Code merged to main branch
- [ ] Database migration successful (up & down tested)
- [ ] API documentation published (Swagger)
- [ ] Cronjob configured dan tested

### Testing
- [ ] Unit test pass (coverage 80%)
- [ ] Integration test pass (payment flow, verification flow, reports)
- [ ] E2E test pass untuk critical paths (5 scenarios)
- [ ] Performance test pass (payment < 3s, generate bills < 10s, reports < 5s)
- [ ] Security test pass (authorization, input validation, file upload)

### Deployment
- [ ] Deployed to staging environment
- [ ] UAT approved by TU & Kepala Sekolah
- [ ] Sample orang tua tested parent portal
- [ ] Cronjob deployed dan scheduled (daily 06:00 WIB)
- [ ] Deployed to production
- [ ] Monitoring & logging active (Sentry, application logs)

### Data & Configuration
- [ ] Payment categories seeded (SPP, Uang Gedung, Seragam, dll)
- [ ] SPP amount per kelas configured
- [ ] School bank account info configured (untuk instruksi pembayaran)
- [ ] WhatsApp API configured dan tested (send test message)
- [ ] Email SMTP configured (fallback)
- [ ] Kwitansi template configured (logo, footer)
- [ ] Reminder schedule configured (H-5, H-0, H+7)

### Documentation
- [ ] Technical documentation complete (architecture, database schema, API)
- [ ] User manual TU (Bahasa Indonesia): catat pembayaran, generate tagihan, verifikasi transfer, laporan
- [ ] User manual Orang Tua: lihat tagihan, upload bukti transfer, download kwitansi
- [ ] Admin guide: configure payment settings, reminder schedule
- [ ] Troubleshooting guide: common issues & solutions

### Training & Handover
- [ ] Training session untuk TU (2 jam): demo system, hands-on practice
- [ ] Training session untuk Kepala Sekolah (1 jam): dashboard, reports
- [ ] Tutorial video untuk Orang Tua (5 menit): cara lihat tagihan & bayar
- [ ] Q&A session dengan stakeholders
- [ ] Support contact established (WhatsApp group, email)
- [ ] Feedback channel setup (form feedback di portal)

---

## 📞 Contact & Support

**Epic Owner:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733  
**Timezone:** Asia/Jakarta (WIB)

**For Technical Issues:**
- WhatsApp: +62 857-1583-8733
- Email: dev-support@sekolah.app

**For Product Questions:**
- Contact Product Owner
- Email: product@sekolah.app

**For User Support (TU/Orang Tua):**
- WhatsApp Support: [Phone Number]
- Email: support@sekolah.app
- Working Hours: 08:00 - 16:00 WIB (Senin - Jumat)

---

**Document Status:** ✅ Ready for Sprint Planning  
**Last Review:** 13 Desember 2025  
**Next Review:** After Sprint 4 completion

---

*Catatan: Dokumen ini adalah living document dan akan diupdate seiring progress sprint. Semua perubahan akan dicatat di section "Change Log" di bawah.*

## 📋 Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 13 Des 2025 | 1.0 | Initial creation of EPIC 4 Payment System document | Zulfikar Hidayatullah |

---

**End of EPIC 4 Document**
