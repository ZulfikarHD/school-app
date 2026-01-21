# User Journeys: Payment System

## Overview

Dokumen ini menjelaskan alur user (user journeys) untuk fitur Payment System dari perspektif setiap role, yaitu: Admin/TU, Principal, dan Parent.

---

## Journey 1: Admin Records Manual Payment (Pembayaran Tunai)

### Konteks
Admin/TU menerima pembayaran tunai dari orang tua siswa di kantor TU.

### Flow Diagram

```
📍 START: Admin Dashboard
    │
    ├─▶ Navigate: Sidebar → Pembayaran → Catat Pembayaran
    │   └─ URL: /admin/payments/records/create
    │
    ├─▶ Step 1: Search Siswa
    │   ├─ Ketik nama/NIS di search box
    │   ├─ Autocomplete dropdown muncul (min 2 karakter)
    │   └─ Klik siswa yang dicari
    │
    ├─▶ Step 2: Review Tagihan
    │   ├─ Card siswa tampil (Nama, NIS, Kelas, Total Tunggakan)
    │   ├─ Tabel tagihan belum bayar muncul
    │   └─ Klik/pilih tagihan yang akan dibayar
    │
    ├─▶ Step 3: Isi Detail Pembayaran
    │   ├─ Nominal auto-fill (sisa tagihan)
    │   ├─ Pilih metode: "Tunai" (selected)
    │   ├─ Tanggal bayar: hari ini (default)
    │   └─ Keterangan (opsional)
    │
    ├─▶ Step 4: Konfirmasi & Simpan
    │   ├─ Klik "Simpan Pembayaran"
    │   ├─ Modal konfirmasi muncul
    │   └─ Klik "Ya, Simpan"
    │
    ├─▶ Step 5: Cetak Kwitansi
    │   ├─ Redirect ke halaman detail pembayaran
    │   ├─ Status: "Terverifikasi" (auto untuk tunai)
    │   ├─ Klik "Download Kwitansi" atau "Print"
    │   └─ Serahkan kwitansi ke orang tua
    │
    └─▶ 🏁 END: Pembayaran tercatat, kwitansi diberikan
```

### Timeline
- Total waktu: ~2-3 menit
- Search siswa: 10-15 detik
- Isi form: 30-60 detik
- Generate PDF: 5-10 detik

---

## Journey 2: Admin Generate Monthly SPP Bills

### Konteks
Setiap awal bulan, Admin generate tagihan SPP untuk semua siswa.

### Flow Diagram

```
📍 START: Admin Dashboard (Awal bulan)
    │
    ├─▶ Navigate: Sidebar → Pembayaran → Generate Tagihan
    │   └─ URL: /admin/payments/bills/generate
    │
    ├─▶ Step 1: Pilih Parameter
    │   ├─ Kategori: "SPP Bulanan"
    │   ├─ Bulan: Februari
    │   ├─ Tahun: 2026
    │   └─ Kelas: "Semua Kelas" (atau pilih spesifik)
    │
    ├─▶ Step 2: Preview
    │   ├─ Klik "Preview"
    │   ├─ Loading spinner
    │   ├─ Tabel preview muncul:
    │   │   └─ Daftar siswa, kelas, nominal masing-masing
    │   ├─ Summary card:
    │   │   ├─ Total siswa: 180
    │   │   ├─ Total nominal: Rp 54.000.000
    │   │   └─ Duplikat: 0 (jika ada, ditandai kuning)
    │   └─ Review daftar
    │
    ├─▶ Step 3: Generate
    │   ├─ Klik "Generate Tagihan"
    │   ├─ Modal konfirmasi
    │   ├─ Loading indicator (2-5 detik)
    │   └─ Success notification: "180 tagihan berhasil di-generate"
    │
    ├─▶ Step 4: Verifikasi
    │   ├─ Redirect ke Daftar Tagihan
    │   ├─ Filter bulan Februari
    │   └─ Verifikasi tagihan sudah ada
    │
    └─▶ 🏁 END: Tagihan bulan ini ter-generate untuk semua siswa
```

### Catatan
- Jika ada duplikat (tagihan bulan sama sudah ada), sistem akan skip otomatis
- Nominal bisa berbeda per kelas jika ada konfigurasi class prices

---

## Journey 3: Admin Verifies Transfer Payment

### Konteks
Orang tua transfer via bank, Admin menerima notifikasi untuk verifikasi.

### Flow Diagram

```
📍 START: Admin menerima info transfer dari orang tua
    │
    ├─▶ Navigate: Sidebar → Pembayaran → Riwayat Pembayaran
    │   └─ URL: /admin/payments/records
    │
    ├─▶ Step 1: Filter Pending
    │   ├─ Filter status: "Menunggu Verifikasi"
    │   └─ List pembayaran pending muncul
    │
    ├─▶ Step 2: Review Detail
    │   ├─ Klik pembayaran yang akan diverifikasi
    │   ├─ Review:
    │   │   ├─ Nama siswa
    │   │   ├─ Nominal
    │   │   ├─ Tanggal bayar
    │   │   └─ Bukti transfer (jika ada)
    │   └─ Cocokkan dengan mutasi bank
    │
    ├─▶ Step 3: Verifikasi
    │   ├─ Klik "Verifikasi"
    │   ├─ Konfirmasi
    │   └─ Status berubah: "Terverifikasi"
    │
    ├─▶ [Optional] Cetak Kwitansi
    │   └─ Download PDF untuk dikirim ke orang tua via WhatsApp
    │
    └─▶ 🏁 END: Pembayaran terverifikasi, bill status update
```

---

## Journey 4: Parent Views Bills & History

### Konteks
Orang tua ingin cek status pembayaran anaknya via portal.

### Flow Diagram

```
📍 START: Parent Dashboard
    │
    ├─▶ Navigate: Sidebar → Pembayaran
    │   └─ URL: /parent/payments
    │
    ├─▶ [If multiple children] Pilih Anak
    │   └─ Dropdown child selector
    │
    ├─▶ View Summary Cards
    │   ├─ Status SPP: "Lunas" / "Belum Bayar"
    │   ├─ Total Tunggakan: Rp X.XXX.XXX
    │   └─ Jatuh Tempo Terdekat: 10 Feb 2026
    │
    ├─▶ Tab: Tagihan Aktif
    │   ├─ List tagihan belum bayar
    │   ├─ Setiap card:
    │   │   ├─ Kategori (SPP, Uang Gedung, dll)
    │   │   ├─ Periode (Februari 2026)
    │   │   ├─ Nominal
    │   │   ├─ Status badge
    │   │   └─ Due date (merah jika overdue)
    │   └─ Klik card untuk detail
    │
    ├─▶ Tab: Riwayat Pembayaran
    │   ├─ List pembayaran yang sudah tercatat
    │   └─ Setiap row:
    │       ├─ Tanggal bayar
    │       ├─ Kategori & periode
    │       ├─ Nominal
    │       ├─ No. Kwitansi
    │       └─ Status badge
    │
    ├─▶ [Optional] Download Kwitansi
    │   ├─ Klik pembayaran dari history
    │   └─ Klik "Download Kwitansi" → PDF
    │
    └─▶ 🏁 END: Parent informed tentang status pembayaran
```

### Mobile Experience
- Summary cards stack vertikal
- Bill cards full width
- Tab navigation swipeable
- Touch-friendly buttons

---

## Journey 5: Principal Reviews Financial Report

### Konteks
Kepala Sekolah ingin review kesehatan finansial bulanan.

### Flow Diagram

```
📍 START: Principal Dashboard
    │
    ├─▶ Navigate: Sidebar → Keuangan → Laporan Keuangan
    │   └─ URL: /principal/financial/reports
    │
    ├─▶ View Summary Cards (Default: Bulan ini)
    │   ├─ Total Pemasukan: Rp 45.000.000
    │   ├─ Total Piutang: Rp 9.000.000
    │   ├─ Kolektibilitas: 83.3%
    │   └─ Breakdown by Method (Tunai, Transfer, QRIS)
    │
    ├─▶ View Charts
    │   ├─ Trend 6 bulan terakhir (Line chart)
    │   └─ Breakdown per kategori (Pie/Bar chart)
    │
    ├─▶ [Optional] Filter by Period
    │   ├─ Ubah bulan/tahun
    │   ├─ Pilih kategori spesifik
    │   └─ Klik "Tampilkan"
    │
    ├─▶ [Optional] View Delinquents
    │   ├─ Klik "Siswa Menunggak"
    │   └─ List siswa dengan total tunggakan
    │       └─ Sorted by tunggakan terbesar
    │
    ├─▶ [Optional] Export Report
    │   ├─ Klik "Export Excel"
    │   └─ Download file untuk arsip/rapat
    │
    └─▶ 🏁 END: Principal has financial overview
```

---

## Journey 6: Admin Bank Reconciliation

### Konteks
Setiap minggu/bulan, Admin mencocokkan pembayaran dengan mutasi bank.

### Flow Diagram

```
📍 START: Admin has bank statement file
    │
    ├─▶ Navigate: Sidebar → Pembayaran → Rekonsiliasi Bank
    │   └─ URL: /admin/payments/reconciliation
    │
    ├─▶ Step 1: Upload Bank Statement
    │   ├─ Klik "Upload Mutasi"
    │   ├─ Pilih file Excel/CSV dari bank
    │   ├─ Klik "Upload"
    │   └─ Processing... → Success
    │
    ├─▶ Step 2: View Uploaded Items
    │   ├─ Klik reconciliation yang baru dibuat
    │   └─ List items dari bank statement:
    │       ├─ Tanggal
    │       ├─ Nominal
    │       ├─ Keterangan
    │       └─ Status (Unmatched/Matched)
    │
    ├─▶ Step 3: Auto Match
    │   ├─ Klik "Auto Match"
    │   ├─ System mencoba match berdasarkan:
    │   │   ├─ Nominal exact match
    │   │   └─ Tanggal range ±3 hari
    │   └─ Matched items ditandai hijau
    │
    ├─▶ Step 4: Manual Match (for unmatched)
    │   ├─ Klik item unmatched
    │   ├─ Search payment manual
    │   ├─ Select payment
    │   └─ Klik "Match"
    │
    ├─▶ Step 5: Verify All
    │   ├─ Semua items matched
    │   ├─ Klik "Verify"
    │   └─ Status: "Verified"
    │
    └─▶ 🏁 END: Bank reconciliation complete
```

---

## Summary: Role Access Matrix

| Feature | Admin/TU | Principal | Parent |
|---------|:--------:|:---------:|:------:|
| Kategori Pembayaran CRUD | ✅ Full | ❌ | ❌ |
| Generate Tagihan | ✅ Full | ❌ | ❌ |
| Catat Pembayaran | ✅ Full | ❌ | ❌ |
| Verifikasi Transfer | ✅ Full | ❌ | ❌ |
| Batalkan Pembayaran | ✅ Full | ❌ | ❌ |
| Rekonsiliasi Bank | ✅ Full | ❌ | ❌ |
| Laporan Keuangan | ✅ Full | ✅ Read | ❌ |
| Siswa Menunggak | ✅ Full | ✅ Read | ❌ |
| View Tagihan | ❌ | ❌ | ✅ Own children |
| Download Kwitansi | ✅ All | ❌ | ✅ Own children |
| Export Report | ✅ Full | ✅ Full | ❌ |

---

## Common User Questions

### Q: Bagaimana jika orang tua bayar sebagian?
**A:** Admin input nominal partial, status tagihan berubah "Sebagian", sisa tagihan terupdate otomatis.

### Q: Bagaimana jika salah input pembayaran?
**A:** Admin bisa "Batalkan" pembayaran dengan isi alasan. Status tagihan rollback.

### Q: Bagaimana parent tahu ada tagihan baru?
**A:** Parent melihat summary card di dashboard dan tab Tagihan Aktif di halaman Pembayaran. (Notifikasi WhatsApp: planned feature)

### Q: Bagaimana jika kategori tidak aktif?
**A:** Tidak bisa generate tagihan dari kategori nonaktif. Tagihan existing tetap bisa dibayar.

### Q: Bagaimana nominal berbeda per kelas?
**A:** Di kategori pembayaran, set "Harga per Kelas". Sistem auto-calculate saat generate.

---

*Last Updated: 2026-01-21*
