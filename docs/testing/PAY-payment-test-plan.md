# Manual QA Test Plan: Payment System (PAY)

## Test Environment

- **URL:** `http://localhost:8000` atau environment testing
- **Browser:** Chrome, Firefox, Safari, Edge
- **Device:** Desktop (1920x1080, 1366x768) & Mobile (375x812, 414x896)
- **Accounts Required:**
  - Admin/TU: `admin@sekolah.sch.id`
  - Principal: `principal@sekolah.sch.id`
  - Parent: `parent@sekolah.sch.id` (dengan anak terdaftar)

## Pre-Conditions

- [ ] Database sudah termigrasi dengan payment tables
- [ ] Minimal 1 kategori pembayaran aktif
- [ ] Minimal 10 siswa aktif dengan kelas
- [ ] Seeder data sudah dijalankan (jika ada)

---

# SECTION 1: ADMIN PAYMENT CATEGORIES

## TC-PAY-001: Akses Menu Kategori Pembayaran

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Login sebagai Admin | Dashboard Admin tampil | ☐ |
| 2 | Klik menu "Pembayaran" di sidebar | Submenu expand | ☐ |
| 3 | Klik "Kategori Pembayaran" | Halaman Index tampil | ☐ |
| 4 | Verifikasi URL | `/admin/payment-categories` | ☐ |
| 5 | Verifikasi breadcrumb | Admin > Pembayaran > Kategori Pembayaran | ☐ |

**Mobile Test:**
- [ ] Menu hamburger berfungsi
- [ ] Sidebar slide-in benar
- [ ] Touch target minimal 44px

---

## TC-PAY-002: Tambah Kategori Pembayaran

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik tombol "Tambah Kategori" | Form Create tampil | ☐ |
| 2 | Isi nama: "SPP Bulanan" | Input terisi | ☐ |
| 3 | Isi kode: "SPP" | Input terisi | ☐ |
| 4 | Pilih tipe: "Bulanan" | Dropdown selected | ☐ |
| 5 | Isi nominal: "300000" | Format Rupiah otomatis | ☐ |
| 6 | Toggle "Aktif" ON | Switch green | ☐ |
| 7 | Toggle "Wajib" ON | Switch green | ☐ |
| 8 | Isi tanggal jatuh tempo: "10" | Input terisi | ☐ |
| 9 | Klik "Simpan" | Redirect ke Index + flash success | ☐ |
| 10 | Verifikasi data di tabel | Kategori baru muncul | ☐ |

**Validasi Error Test:**
- [ ] Submit form kosong → Error "Nama harus diisi"
- [ ] Kode duplikat → Error "Kode sudah digunakan"
- [ ] Nominal negatif → Error "Nominal minimal 0"
- [ ] Due day > 28 → Error validation

---

## TC-PAY-003: Edit Kategori Pembayaran

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik menu dropdown (⋮) pada row | Menu muncul | ☐ |
| 2 | Klik "Edit" | Form Edit tampil dengan data | ☐ |
| 3 | Ubah nama menjadi "SPP Bulanan 2026" | Input berubah | ☐ |
| 4 | Klik "Simpan Perubahan" | Redirect + flash success | ☐ |
| 5 | Verifikasi perubahan di tabel | Nama terupdate | ☐ |

---

## TC-PAY-004: Toggle Status Kategori

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik toggle "Aktif" pada kategori aktif | Confirmation dialog | ☐ |
| 2 | Konfirmasi | Status berubah ke Nonaktif | ☐ |
| 3 | Badge berubah menjadi abu-abu | Visual feedback | ☐ |
| 4 | Toggle kembali | Status Aktif lagi | ☐ |

---

# SECTION 2: ADMIN GENERATE TAGIHAN

## TC-PAY-010: Akses Generate Tagihan

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik menu "Generate Tagihan" | Halaman Generate tampil | ☐ |
| 2 | Verifikasi form filters | Dropdown kategori, bulan, tahun, kelas | ☐ |

---

## TC-PAY-011: Preview Tagihan

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Pilih kategori: "SPP Bulanan" | Dropdown selected | ☐ |
| 2 | Pilih bulan: "Februari" | Dropdown selected | ☐ |
| 3 | Pilih tahun: "2026" | Dropdown selected | ☐ |
| 4 | Pilih kelas: "Semua Kelas" | Dropdown selected | ☐ |
| 5 | Klik "Preview" | Loading indicator | ☐ |
| 6 | Tabel preview muncul | List siswa dengan nominal | ☐ |
| 7 | Summary card tampil | Total siswa, total nominal | ☐ |
| 8 | Duplikat ditandai badge kuning | Jika ada duplikat | ☐ |

---

## TC-PAY-012: Generate Tagihan Bulk

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Setelah preview, klik "Generate" | Confirmation dialog | ☐ |
| 2 | Konfirmasi | Loading state | ☐ |
| 3 | Success notification | "X tagihan berhasil di-generate" | ☐ |
| 4 | Redirect ke Daftar Tagihan | Bills list tampil | ☐ |
| 5 | Filter by bulan Februari | Tagihan baru muncul | ☐ |

**Edge Case Test:**
- [ ] Generate untuk bulan yang sudah ada → Skip duplicates
- [ ] Kategori nonaktif → Error tidak bisa generate
- [ ] Tidak ada siswa aktif di kelas → Warning message

---

# SECTION 3: ADMIN CATAT PEMBAYARAN

## TC-PAY-020: Akses Form Catat Pembayaran

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik menu "Catat Pembayaran" | Form Create tampil | ☐ |
| 2 | Verifikasi komponen | Search siswa, tagihan, form pembayaran | ☐ |

---

## TC-PAY-021: Search & Select Siswa

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Ketik nama siswa di search | Autocomplete dropdown | ☐ |
| 2 | Minimal 2 karakter | Hasil pencarian muncul | ☐ |
| 3 | Klik salah satu hasil | Siswa terpilih | ☐ |
| 4 | Card siswa tampil | Nama, NIS, Kelas, Total tunggakan | ☐ |
| 5 | Tabel tagihan belum bayar muncul | List bills unpaid | ☐ |

---

## TC-PAY-022: Record Pembayaran Tunai

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Pilih tagihan dari tabel | Row highlighted | ☐ |
| 2 | Nominal auto-fill sisa tagihan | Input terisi | ☐ |
| 3 | Pilih metode: "Tunai" | Button selected green | ☐ |
| 4 | Tanggal bayar default hari ini | Date picker | ☐ |
| 5 | Isi keterangan (opsional) | Input terisi | ☐ |
| 6 | Klik "Simpan Pembayaran" | Confirmation dialog | ☐ |
| 7 | Konfirmasi | Loading state | ☐ |
| 8 | Redirect ke Show payment | Detail pembayaran | ☐ |
| 9 | Status = "Terverifikasi" | Badge hijau (auto-verify tunai) | ☐ |
| 10 | Nomor kwitansi muncul | Format KWT/YYYY/MM/XXXXX | ☐ |

---

## TC-PAY-023: Record Pembayaran Transfer

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Pilih tagihan, pilih metode "Transfer" | Button selected | ☐ |
| 2 | Simpan pembayaran | Redirect ke Show | ☐ |
| 3 | Status = "Menunggu Verifikasi" | Badge kuning | ☐ |
| 4 | Tombol "Verifikasi" tersedia | Button enabled | ☐ |

---

## TC-PAY-024: Partial Payment

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Pilih tagihan Rp 300.000 | Nominal auto-fill | ☐ |
| 2 | Ubah nominal menjadi Rp 150.000 | Input berubah | ☐ |
| 3 | Simpan pembayaran | Success | ☐ |
| 4 | Status tagihan = "Sebagian" | Badge biru | ☐ |
| 5 | Sisa tagihan = Rp 150.000 | Terhitung benar | ☐ |

**Validasi Error Test:**
- [ ] Nominal > sisa tagihan → Error "Melebihi sisa tagihan"
- [ ] Nominal 0 atau negatif → Error validation
- [ ] Tanggal > hari ini → Error "Tidak boleh lebih dari hari ini"

---

## TC-PAY-025: Download & Print Kwitansi

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Di halaman Show payment, klik "Download Kwitansi" | PDF terdownload | ☐ |
| 2 | Buka PDF | Layout kwitansi benar | ☐ |
| 3 | Verifikasi isi | Nama sekolah, siswa, nominal, tanggal | ☐ |
| 4 | Klik "Print" | Print dialog browser | ☐ |

---

# SECTION 4: ADMIN VERIFIKASI PEMBAYARAN

## TC-PAY-030: Akses Queue Verifikasi

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik menu "Riwayat Pembayaran" | Index tampil | ☐ |
| 2 | Filter status: "Menunggu Verifikasi" | List pending payments | ☐ |
| 3 | Badge count di sidebar match | Jumlah pending benar | ☐ |

---

## TC-PAY-031: Verifikasi Pembayaran

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik pembayaran pending | Show detail | ☐ |
| 2 | Review detail pembayaran | Data lengkap | ☐ |
| 3 | Klik "Verifikasi" | Confirmation dialog | ☐ |
| 4 | Konfirmasi | Status berubah "Terverifikasi" | ☐ |
| 5 | Badge hijau | Visual update | ☐ |
| 6 | Bill status update | Lunas/Sebagian sesuai | ☐ |

---

## TC-PAY-032: Batalkan Pembayaran

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik pembayaran verified | Show detail | ☐ |
| 2 | Klik "Batalkan" | Modal alasan muncul | ☐ |
| 3 | Isi alasan: "Kesalahan input" | Input terisi | ☐ |
| 4 | Konfirmasi | Status berubah "Dibatalkan" | ☐ |
| 5 | Badge merah | Visual update | ☐ |
| 6 | Bill status rollback | Belum bayar/Sebagian | ☐ |

---

# SECTION 5: ADMIN LAPORAN KEUANGAN

## TC-PAY-040: Akses Laporan Keuangan

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik menu "Laporan Keuangan" | Reports page tampil | ☐ |
| 2 | Default filter: bulan & tahun ini | Filters pre-filled | ☐ |

---

## TC-PAY-041: View Summary Statistics

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Verifikasi card "Total Pemasukan" | Nominal formatted Rupiah | ☐ |
| 2 | Verifikasi card "Total Piutang" | Nominal formatted | ☐ |
| 3 | Verifikasi card "Kolektibilitas" | Persentase % | ☐ |
| 4 | Verifikasi breakdown by method | Tunai, Transfer, QRIS | ☐ |

---

## TC-PAY-042: Filter Reports

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Ubah filter bulan ke Januari | Data update | ☐ |
| 2 | Ubah filter kategori ke "SPP" | Data filtered | ☐ |
| 3 | Klik "Tampilkan" | Charts & tables update | ☐ |

---

## TC-PAY-043: View Charts

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Verifikasi trend chart | Line/Bar chart 6 bulan | ☐ |
| 2 | Hover data point | Tooltip dengan nominal | ☐ |
| 3 | Verifikasi category breakdown | Pie/Bar chart | ☐ |

---

## TC-PAY-044: Export Report

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik "Export Excel" | File terdownload | ☐ |
| 2 | Buka file CSV/Excel | Data lengkap dan benar | ☐ |
| 3 | Verifikasi format angka | Nominal numeric | ☐ |

---

## TC-PAY-045: View Delinquent Students

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik "Siswa Menunggak" | Delinquents page | ☐ |
| 2 | Verifikasi tabel | Nama, NIS, Kelas, Total tunggakan | ☐ |
| 3 | Sort by total tunggakan | Descending default | ☐ |
| 4 | Expand row detail | List tagihan unpaid | ☐ |
| 5 | Badge overdue merah | Jika jatuh tempo lewat | ☐ |

---

# SECTION 6: ADMIN REKONSILIASI BANK

## TC-PAY-050: Upload Bank Statement

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik menu "Rekonsiliasi Bank" | Index page tampil | ☐ |
| 2 | Klik "Upload Mutasi" | Upload dialog | ☐ |
| 3 | Pilih file Excel/CSV | File selected | ☐ |
| 4 | Klik "Upload" | Processing indicator | ☐ |
| 5 | Success | New reconciliation created | ☐ |
| 6 | List items muncul | Transaksi dari bank statement | ☐ |

---

## TC-PAY-051: Auto Match

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik reconciliation row | Detail page | ☐ |
| 2 | Klik "Auto Match" | Processing | ☐ |
| 3 | Items matched ditandai hijau | Badge matched | ☐ |
| 4 | Unmatched tetap kuning | Badge unmatched | ☐ |

---

## TC-PAY-052: Manual Match

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik item unmatched | Match page | ☐ |
| 2 | Search payment | Autocomplete | ☐ |
| 3 | Select payment to match | Payment selected | ☐ |
| 4 | Klik "Match" | Item matched | ☐ |

---

## TC-PAY-053: Verify Reconciliation

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Semua items matched | Verify button enabled | ☐ |
| 2 | Klik "Verify" | Confirmation | ☐ |
| 3 | Status: "Verified" | Badge hijau | ☐ |

---

# SECTION 7: PARENT PAYMENTS

## TC-PAY-060: Parent View Bills

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Login sebagai Parent | Dashboard Parent | ☐ |
| 2 | Klik menu "Pembayaran" | Payment page | ☐ |
| 3 | Pilih anak (jika multiple) | Child selector | ☐ |
| 4 | Verifikasi summary cards | Total tunggakan, status SPP | ☐ |
| 5 | Tab "Tagihan Aktif" | List bills unpaid | ☐ |

---

## TC-PAY-061: Parent Bill Details

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik bill card | Detail muncul | ☐ |
| 2 | Verifikasi informasi | Kategori, periode, nominal, due date | ☐ |
| 3 | Badge status | Belum Bayar/Sebagian | ☐ |
| 4 | Badge overdue merah | Jika lewat jatuh tempo | ☐ |

---

## TC-PAY-062: Parent Payment History

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik tab "Riwayat Pembayaran" | History list | ☐ |
| 2 | Verifikasi data | Tanggal, nominal, kwitansi | ☐ |
| 3 | Badge status verified | Hijau | ☐ |

---

## TC-PAY-063: Parent Download Receipt

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik payment dari history | Detail muncul | ☐ |
| 2 | Klik "Download Kwitansi" | PDF terdownload | ☐ |
| 3 | Verifikasi isi kwitansi | Data anak sendiri | ☐ |

**Security Test:**
- [ ] Akses kwitansi anak orang lain → 403 Forbidden

---

# SECTION 8: PRINCIPAL FINANCIAL

## TC-PAY-070: Principal View Reports

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Login sebagai Principal | Dashboard Principal | ☐ |
| 2 | Klik menu "Keuangan" → "Laporan" | Reports page | ☐ |
| 3 | Verifikasi data sama dengan Admin | Summary, charts | ☐ |
| 4 | TIDAK ada tombol edit/tambah | Read-only | ☐ |

---

## TC-PAY-071: Principal View Delinquents

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik "Siswa Menunggak" | Delinquents page | ☐ |
| 2 | Data sama dengan Admin view | Consistent | ☐ |

---

## TC-PAY-072: Principal Export

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Klik "Export" | File terdownload | ☐ |
| 2 | Verifikasi data | Lengkap dan benar | ☐ |

---

# SECTION 9: MOBILE RESPONSIVENESS

## TC-PAY-080: Mobile Admin Payment

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Viewport 375px width | No horizontal scroll | ☐ |
| 2 | Tables responsive | Scroll atau card view | ☐ |
| 3 | Forms full width | Usable | ☐ |
| 4 | Buttons min 44px height | Touch target | ☐ |
| 5 | Modal tidak overflow | Visible | ☐ |

---

## TC-PAY-081: Mobile Parent Payment

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Bill cards full width | Readable | ☐ |
| 2 | Summary cards stack vertical | Mobile layout | ☐ |
| 3 | Download button accessible | Touch target | ☐ |

---

# SECTION 10: EDGE CASES & ERROR HANDLING

## TC-PAY-090: Empty States

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Parent tanpa tagihan | Empty state "Tidak ada tagihan" | ☐ |
| 2 | Report bulan tanpa transaksi | Empty state + "0" di summary | ☐ |
| 3 | Search tidak ditemukan | "Tidak ada hasil" | ☐ |

---

## TC-PAY-091: Concurrent Modification

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | User A buka form payment | Form tampil | ☐ |
| 2 | User B bayar tagihan yang sama | Success B | ☐ |
| 3 | User A submit | Error "Tagihan sudah lunas" | ☐ |

---

## TC-PAY-092: Network Error Handling

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Disconnect network | - | ☐ |
| 2 | Submit form | Error toast + retry option | ☐ |
| 3 | Reconnect + retry | Success | ☐ |

---

# DEFECT REPORTING TEMPLATE

```
## Defect Report

**ID:** PAY-DEF-XXX
**Title:** [Brief description]
**Severity:** Critical / High / Medium / Low
**Priority:** P1 / P2 / P3 / P4
**Environment:** Browser, OS, Viewport

### Steps to Reproduce
1. ...
2. ...
3. ...

### Expected Result
[What should happen]

### Actual Result
[What actually happened]

### Evidence
- Screenshot: [attached]
- Console errors: [if any]
- Network response: [if relevant]

### Additional Notes
[Any other relevant information]
```

---

# TEST SUMMARY

| Section | Total TCs | Passed | Failed | Blocked |
|---------|-----------|--------|--------|---------|
| Payment Categories | 4 | ☐ | ☐ | ☐ |
| Generate Bills | 3 | ☐ | ☐ | ☐ |
| Record Payment | 6 | ☐ | ☐ | ☐ |
| Verification | 3 | ☐ | ☐ | ☐ |
| Reports | 6 | ☐ | ☐ | ☐ |
| Reconciliation | 4 | ☐ | ☐ | ☐ |
| Parent | 4 | ☐ | ☐ | ☐ |
| Principal | 3 | ☐ | ☐ | ☐ |
| Mobile | 2 | ☐ | ☐ | ☐ |
| Edge Cases | 3 | ☐ | ☐ | ☐ |
| **TOTAL** | **38** | ☐ | ☐ | ☐ |

---

**Tested By:** _________________  
**Date:** _________________  
**Sign-off:** _________________

---

*Last Updated: 2026-01-21*
