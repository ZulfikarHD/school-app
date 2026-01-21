# User Stories: Payment System

Module ini mencakup semua fitur terkait pembayaran SPP, uang gedung, dan tagihan lainnya.

---

## US-PAY-001: Catat Pembayaran SPP (Manual)

**As a** TU/Admin  
**I want** mencatat pembayaran SPP siswa yang dibayar tunai/transfer  
**So that** riwayat pembayaran tersimpan dan dapat dilacak

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Pembayaran"  
   **When** TU klik "Catat Pembayaran Baru"  
   **Then** sistem tampilkan form: Pilih Siswa, Jenis Pembayaran (SPP), Bulan, Jumlah, Metode (Tunai/Transfer), Tanggal Bayar

✅ **Given** TU pilih siswa dan bulan pembayaran (misal: SPP Januari 2025)  
   **When** TU input jumlah Rp 250.000 dan klik "Simpan"  
   **Then** sistem simpan transaksi dan tampilkan notifikasi "Pembayaran berhasil dicatat"

✅ **Given** siswa A sudah bayar SPP Januari  
   **When** TU coba catat pembayaran SPP Januari lagi untuk siswa yang sama  
   **Then** sistem tampilkan warning "SPP bulan ini sudah dibayar"

✅ **Given** TU catat pembayaran via transfer  
   **When** TU upload bukti transfer (foto/PDF)  
   **Then** sistem simpan bukti transfer dan attach ke transaksi

**Notes:**
- Jenis pembayaran: SPP, Uang Gedung, Seragam, Kegiatan, Lain-lain
- Metode: Tunai, Transfer Bank, (Payment Gateway - fase 2)
- Generate nomor invoice otomatis (format: INV/YYYY/MM/XXXX)
- Print kwitansi pembayaran

---

## US-PAY-002: Lihat Riwayat Pembayaran Siswa

**As a** TU/Kepala Sekolah  
**I want** melihat riwayat pembayaran siswa  
**So that** saya dapat mengecek status pembayaran dan tunggakan

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman profil siswa, tab "Pembayaran"  
   **When** halaman load  
   **Then** sistem tampilkan tabel riwayat pembayaran: tanggal, jenis, bulan, jumlah, metode, status (Lunas/Belum)

✅ **Given** siswa A belum bayar SPP bulan Desember & Januari  
   **When** TU lihat riwayat  
   **Then** sistem tampilkan bulan Desember & Januari dengan status "Belum Bayar" (highlight merah)

✅ **Given** TU ingin lihat detail transaksi  
   **When** TU klik salah satu transaksi  
   **Then** sistem tampilkan detail: invoice, tanggal bayar, jumlah, metode, petugas yang input, bukti transfer (jika ada)

✅ **Given** TU ingin print kwitansi  
   **When** TU klik "Print Kwitansi" pada transaksi  
   **Then** sistem generate PDF kwitansi pembayaran dengan format resmi sekolah

**Notes:**
- Status: Lunas, Belum Bayar, Sebagian (jika ada cicilan)
- Highlight tunggakan > 2 bulan dengan warna merah
- Filter: per jenis pembayaran, per periode

---

## US-PAY-003: Generate Tagihan Bulanan (SPP)

**As a** TU/Admin  
**I want** generate tagihan SPP bulanan untuk semua siswa otomatis  
**So that** saya tidak perlu input manual setiap bulan

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Generate Tagihan"  
   **When** TU pilih bulan dan tahun ajaran  
   **Then** sistem tampilkan preview: list siswa aktif, jumlah SPP per siswa, total tagihan

✅ **Given** TU konfirmasi generate tagihan untuk bulan Februari  
   **When** TU klik "Generate"  
   **Then** sistem create invoice untuk semua siswa aktif dengan status "Belum Bayar"

✅ **Given** siswa B punya potongan/beasiswa 50%  
   **When** sistem generate tagihan  
   **Then** jumlah SPP siswa B otomatis dikurangi 50% sesuai data potongan

✅ **Given** tagihan bulan Februari sudah di-generate  
   **When** TU coba generate lagi  
   **Then** sistem tampilkan warning "Tagihan bulan ini sudah pernah di-generate"

**Notes:**
- Generate tagihan setiap tanggal 1 atau awal bulan (bisa otomatis dengan cron job)
- Support potongan/beasiswa per siswa
- Tagihan hanya untuk siswa dengan status "Aktif"

---

## US-PAY-004: Set Nominal SPP per Kelas/Siswa

**As a** TU/Admin  
**I want** mengatur nominal SPP per kelas atau per siswa  
**So that** tagihan SPP sesuai dengan ketentuan sekolah

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Pengaturan SPP"  
   **When** TU set nominal SPP untuk kelas 1 = Rp 200.000, kelas 2-6 = Rp 250.000  
   **Then** sistem simpan konfigurasi dan apply untuk generate tagihan berikutnya

✅ **Given** siswa C dapat beasiswa (potongan 100%)  
   **When** TU set potongan khusus untuk siswa C  
   **Then** SPP siswa C menjadi Rp 0 saat generate tagihan

✅ **Given** siswa D dapat potongan 25% (anak guru)  
   **When** TU set potongan 25% untuk siswa D  
   **Then** SPP siswa D dihitung otomatis: Rp 250.000 - 25% = Rp 187.500

**Notes:**
- Setting per kelas (default) atau per siswa (custom)
- Jenis potongan: persentase atau nominal tetap
- History perubahan nominal SPP untuk audit

---

## US-PAY-005: Reminder Pembayaran (WhatsApp/Email)

**As a** TU/Orang Tua  
**I want** orang tua menerima reminder pembayaran SPP  
**So that** pembayaran tidak terlambat

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** tanggal 5 setiap bulan (H-5 sebelum jatuh tempo)  
   **When** sistem cron job reminder berjalan  
   **Then** sistem kirim WhatsApp/email ke orang tua yang belum bayar SPP bulan ini

✅ **Given** orang tua siswa A belum bayar SPP Februari  
   **When** reminder terkirim  
   **Then** orang tua terima pesan: "Reminder: SPP bulan Februari untuk [Nama Anak] belum dibayar. Jatuh tempo: 10 Februari 2025. Jumlah: Rp 250.000"

✅ **Given** siswa A sudah bayar SPP Februari di tanggal 3  
   **When** reminder H-5 berjalan tanggal 5  
   **Then** orang tua siswa A TIDAK menerima reminder (karena sudah lunas)

✅ **Given** siswa B punya tunggakan 2 bulan (Desember & Januari)  
   **When** reminder berjalan  
   **Then** orang tua terima pesan dengan total tunggakan: "Anda memiliki tunggakan 2 bulan (Desember, Januari). Total: Rp 500.000"

**Notes:**
- Schedule reminder: H-5, H-3, H (jatuh tempo), H+3, H+7 (overdue)
- Template pesan dalam Bahasa Indonesia
- Kirim via WhatsApp (prioritas), fallback email
- Orang tua dapat set preferensi reminder (on/off)

---

## US-PAY-006: Lihat Tagihan & Bayar (Portal Orang Tua)

**As a** Orang Tua  
**I want** melihat tagihan anak saya dan status pembayaran  
**So that** saya tahu kapan harus bayar dan berapa jumlahnya

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua login ke portal  
   **When** orang tua klik tab "Pembayaran" pada profil anak  
   **Then** sistem tampilkan: tagihan yang belum dibayar, riwayat pembayaran, total tunggakan (jika ada)

✅ **Given** ada tagihan SPP Februari yang belum dibayar  
   **When** orang tua lihat tagihan  
   **Then** sistem tampilkan card tagihan dengan: jenis, bulan, jumlah, jatuh tempo, status "Belum Bayar"

✅ **Given** orang tua sudah bayar via transfer bank  
   **When** orang tua klik "Konfirmasi Pembayaran"  
   **Then** sistem tampilkan form upload bukti transfer dan input tanggal bayar

✅ **Given** orang tua upload bukti transfer  
   **When** orang tua submit  
   **Then** sistem simpan bukti dan kirim notifikasi ke TU untuk verifikasi, status tagihan berubah "Menunggu Verifikasi"

**Notes:**
- Read-only untuk riwayat pembayaran
- Upload bukti transfer untuk verifikasi TU
- Notifikasi setelah TU verifikasi pembayaran
- Fase 2: integrasi payment gateway untuk bayar langsung

---

## US-PAY-007: Verifikasi Pembayaran Transfer (TU)

**As a** TU/Admin  
**I want** verifikasi pembayaran transfer dari orang tua  
**So that** pembayaran yang sudah ditransfer dapat dikonfirmasi dan dicatat

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Verifikasi Pembayaran"  
   **When** halaman load  
   **Then** sistem tampilkan list pembayaran dengan status "Menunggu Verifikasi"

✅ **Given** TU klik salah satu pembayaran  
   **When** TU lihat detail  
   **Then** sistem tampilkan: nama siswa, jenis pembayaran, jumlah, tanggal bayar, bukti transfer

✅ **Given** TU cek rekening dan transfer sudah masuk  
   **When** TU klik "Konfirmasi Pembayaran"  
   **Then** status pembayaran berubah "Lunas" dan orang tua dapat notifikasi "Pembayaran Anda telah dikonfirmasi"

✅ **Given** bukti transfer tidak sesuai (jumlah salah/rekening salah)  
   **When** TU klik "Tolak" dengan keterangan  
   **Then** status kembali "Belum Bayar" dan orang tua dapat notifikasi rejection beserta keterangan

**Notes:**
- Notifikasi real-time ke TU saat ada upload bukti baru
- Batch verification untuk efisiensi (verifikasi banyak sekaligus)
- Integrasi dengan rekening bank (auto-reconciliation) - fase 2

---

## US-PAY-008: Laporan Keuangan (Pemasukan)

**As a** Kepala Sekolah/TU  
**I want** melihat laporan keuangan pemasukan  
**So that** saya dapat monitoring cash flow sekolah

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah di halaman "Laporan Keuangan"  
   **When** kepala sekolah pilih periode (bulan/tahun)  
   **Then** sistem tampilkan summary: total pemasukan, breakdown per jenis pembayaran (SPP, Uang Gedung, dll), grafik trend

✅ **Given** kepala sekolah ingin lihat detail transaksi  
   **When** kepala sekolah klik salah satu kategori (misal: SPP)  
   **Then** sistem tampilkan list transaksi SPP untuk periode tersebut

✅ **Given** kepala sekolah ingin export laporan untuk akuntan  
   **When** kepala sekolah klik "Export ke Excel"  
   **Then** sistem generate file Excel dengan detail: tanggal, invoice, siswa, jenis, jumlah, metode, petugas

✅ **Given** ada siswa yang belum bayar (tunggakan)  
   **When** laporan di-generate  
   **Then** sistem tampilkan section "Tunggakan": list siswa dengan total tunggakan dan jumlah bulan tertunggak

**Notes:**
- Summary: total pemasukan, total tunggakan, persentase kolektibilitas
- Grafik: tren pemasukan per bulan, breakdown per jenis pembayaran (pie chart)
- Export: Excel dan PDF
- Filter: per periode, per jenis pembayaran, per kelas

---

## US-PAY-009: Catat Pembayaran Lain (Non-SPP)

**As a** TU/Admin  
**I want** mencatat pembayaran selain SPP (uang gedung, seragam, kegiatan)  
**So that** semua transaksi keuangan tercatat

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman "Pembayaran"  
   **When** TU pilih jenis pembayaran "Uang Gedung"  
   **Then** sistem tampilkan form sesuai jenis: siswa, jumlah, tanggal, metode

✅ **Given** siswa baru bayar uang gedung Rp 2.000.000  
   **When** TU catat pembayaran  
   **Then** sistem simpan transaksi dan generate invoice/kwitansi

✅ **Given** ada pembayaran kegiatan study tour untuk siswa kelas 6 (Rp 500.000)  
   **When** TU catat pembayaran  
   **Then** sistem simpan dengan kategori "Kegiatan" dan keterangan "Study Tour Kelas 6"

**Notes:**
- Jenis pembayaran customizable (admin dapat tambah jenis baru)
- Support one-time payment dan recurring payment
- Kwitansi untuk setiap jenis pembayaran

---

## US-PAY-010: Integrasi Payment Gateway (Phase 2)

**As a** Orang Tua  
**I want** bayar SPP langsung via payment gateway (VA/QRIS)  
**So that** saya tidak perlu transfer manual dan menunggu verifikasi

**Priority:** Could Have (Phase 2)  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** orang tua di portal, tab "Pembayaran"  
   **When** orang tua klik "Bayar Sekarang" pada tagihan SPP  
   **Then** sistem redirect ke halaman payment gateway (Midtrans/Xendit) dengan detail tagihan

✅ **Given** orang tua pilih metode pembayaran (Virtual Account BCA)  
   **When** orang tua confirm  
   **Then** sistem generate VA number dan tampilkan instruksi pembayaran

✅ **Given** orang tua transfer ke VA number  
   **When** payment gateway terima notifikasi payment success  
   **Then** sistem otomatis update status pembayaran jadi "Lunas" dan kirim notifikasi ke orang tua

✅ **Given** orang tua bayar via QRIS  
   **When** orang tua scan QRIS dan bayar  
   **Then** status pembayaran otomatis update real-time tanpa verifikasi manual TU

**Notes:**
- Payment gateway: Midtrans / Xendit / Doku (pilih yang paling cost-effective)
- Metode: Virtual Account (semua bank), QRIS, Gopay, OVO, dll
- Auto-reconciliation (tidak perlu verifikasi manual TU)
- Biaya admin payment gateway: ditanggung sekolah atau orang tua (configurable)
- Fase 2 (prioritas setelah MVP stabil)

---

## US-PAY-011: Cetak Kwitansi Pembayaran

**As a** TU/Admin  
**I want** cetak kwitansi pembayaran untuk diberikan ke orang tua  
**So that** orang tua punya bukti pembayaran resmi

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** TU di halaman riwayat pembayaran siswa  
   **When** TU klik "Cetak Kwitansi" pada transaksi tertentu  
   **Then** sistem generate PDF kwitansi dengan: logo sekolah, nama sekolah, nomor kwitansi, tanggal, nama siswa, kelas, jenis pembayaran, jumlah, terbilang, tanda tangan & stempel

✅ **Given** kwitansi berhasil di-generate  
   **When** TU buka PDF  
   **Then** format kwitansi siap print (A5 atau custom size sesuai setting sekolah)

✅ **Given** TU ingin print kwitansi rangkap (untuk arsip)  
   **When** TU setting "Print Rangkap 2"  
   **Then** PDF generate 2 halaman (1 untuk orang tua, 1 untuk arsip sekolah)

**Notes:**
- Template kwitansi customizable (logo, format, dll)
- Nomor kwitansi auto-increment (format: KWT/YYYY/MM/XXXX)
- Terbilang otomatis (Rp 250.000 → "Dua Ratus Lima Puluh Ribu Rupiah")
- Digital signature (opsional, fase 2)

---

## US-PAY-012: Dashboard Pembayaran (Summary)

**As a** TU/Kepala Sekolah  
**I want** melihat dashboard summary pembayaran  
**So that** saya dapat quick overview status pembayaran sekolah

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user di halaman dashboard pembayaran  
   **When** halaman load  
   **Then** sistem tampilkan card summary: total pemasukan bulan ini, total tunggakan, jumlah siswa belum bayar, persentase kolektibilitas

✅ **Given** ada 15 siswa yang belum bayar SPP bulan ini  
   **When** dashboard load  
   **Then** card "Siswa Belum Bayar" tampilkan angka 15 dengan highlight merah

✅ **Given** user klik card "Tunggakan"  
   **When** user klik  
   **Then** sistem redirect ke halaman list siswa dengan tunggakan, sorted by jumlah tunggakan (terbesar ke terkecil)

**Notes:**
- Card summary: pemasukan, tunggakan, kolektibilitas, pending verification
- Grafik: tren pembayaran 6 bulan terakhir
- Quick action: "Generate Tagihan Bulan Ini", "Kirim Reminder", "Verifikasi Pembayaran"

---

## Summary: Payment System

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-PAY-001 | Catat Pembayaran Manual | Must Have | M | 1 |
| US-PAY-002 | Riwayat Pembayaran | Must Have | M | 1 |
| US-PAY-003 | Generate Tagihan | Must Have | M | 1 |
| US-PAY-004 | Set Nominal SPP | Must Have | S | 1 |
| US-PAY-005 | Reminder Pembayaran | Should Have | M | 1 |
| US-PAY-006 | Portal Orang Tua | Must Have | M | 1 |
| US-PAY-007 | Verifikasi Transfer | Must Have | S | 1 |
| US-PAY-008 | Laporan Keuangan | Must Have | M | 1 |
| US-PAY-009 | Pembayaran Non-SPP | Must Have | S | 1 |
| US-PAY-010 | Payment Gateway | Could Have | L | 2 |
| US-PAY-011 | Cetak Kwitansi | Must Have | S | 1 |
| US-PAY-012 | Dashboard Pembayaran | Should Have | M | 1 |

**Total Estimation Phase 1:** 26 points (~4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
