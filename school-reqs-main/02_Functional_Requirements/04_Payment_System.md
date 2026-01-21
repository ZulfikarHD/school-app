# Modul 4: Payment System (Sistem Pembayaran)

## 📋 Overview

Modul ini menangani semua jenis pembayaran sekolah (SPP, uang gedung, seragam, kegiatan, dll), pencatatan transaksi, reminder otomatis, dan laporan keuangan.

**Module Code:** `PAY`  
**Priority:** P0 (Critical)  
**Dependencies:** Authentication, Student Management, Notification System

---

## 🎯 Tujuan

1. Digitalisasi pencatatan pembayaran sekolah
2. Otomasi reminder pembayaran ke orang tua
3. Tracking status pembayaran real-time
4. Rekonsiliasi keuangan yang akurat
5. Transparansi informasi pembayaran untuk orang tua
6. Laporan keuangan untuk kepala sekolah

---

## 📖 User Stories

### US-PAY-001: Input Pembayaran Manual (TU)
```
Sebagai Staf TU,
Saya ingin mencatat pembayaran siswa yang membayar tunai/transfer,
Sehingga data pembayaran tercatat di sistem
```

**Acceptance Criteria:**
- ✅ Form input pembayaran dengan pilih siswa, jenis pembayaran, jumlah, metode
- ✅ Auto-generate nomor kwitansi
- ✅ Print kwitansi setelah save
- ✅ Update status pembayaran otomatis
- ✅ Success notification

---

### US-PAY-002: Lihat Tagihan SPP (Orang Tua)
```
Sebagai Orang Tua,
Saya ingin melihat status tagihan SPP anak saya,
Sehingga saya tahu berapa yang harus dibayar dan apakah ada tunggakan
```

**Acceptance Criteria:**
- ✅ Dashboard menampilkan status SPP: Lunas/Menunggak
- ✅ Detail tagihan per bulan dengan status (Lunas/Belum Bayar)
- ✅ Total tunggakan (jika ada) ditampilkan jelas
- ✅ History pembayaran (tanggal, jumlah, nomor kwitansi)
- ✅ Download kwitansi (PDF) untuk pembayaran yang sudah lunas

---

### US-PAY-003: Generate Tagihan SPP Bulanan (TU)
```
Sebagai Staf TU,
Saya ingin generate tagihan SPP untuk semua siswa setiap bulan,
Sehingga tagihan otomatis tercatat dan orang tua bisa lihat
```

**Acceptance Criteria:**
- ✅ Pilih bulan & tahun ajaran
- ✅ Pilih kelas atau semua siswa
- ✅ Preview jumlah siswa & total tagihan
- ✅ Click "Generate" → sistem create tagihan untuk semua siswa
- ✅ Success notification dengan summary
- ✅ Tagihan muncul di dashboard orang tua

---

### US-PAY-004: Reminder Pembayaran Otomatis
```
Sebagai Sistem,
Saya ingin mengirim reminder otomatis ke orang tua yang belum bayar SPP,
Sehingga tingkat kolektibilitas pembayaran meningkat
```

**Acceptance Criteria:**
- ✅ Setiap tanggal 5 (configurable), sistem cek tagihan yang belum dibayar
- ✅ Kirim WhatsApp reminder ke orang tua dengan detail tagihan
- ✅ Format: "Yth. Bapak/Ibu {nama}, tagihan SPP {bulan} untuk {nama_siswa} sebesar Rp{jumlah} belum dibayar. Mohon segera melunasi."
- ✅ Log reminder sent
- ✅ Reminder tidak dikirim jika sudah lunas

---

### US-PAY-005: Laporan Keuangan (Principal)
```
Sebagai Kepala Sekolah,
Saya ingin melihat laporan keuangan bulanan,
Sehingga saya dapat memonitor pemasukan sekolah
```

**Acceptance Criteria:**
- ✅ Filter: bulan, tahun, jenis pembayaran
- ✅ Summary: Total Pemasukan, Total Piutang, Tingkat Kolektibilitas
- ✅ Breakdown per jenis pembayaran (SPP, uang gedung, dll)
- ✅ Chart: pemasukan per bulan, per kelas
- ✅ Export to Excel & PDF

---

### US-PAY-006: Rekonsiliasi Pembayaran (TU)
```
Sebagai Staf TU,
Saya ingin melakukan rekonsiliasi pembayaran dengan mutasi rekening bank,
Sehingga data pembayaran akurat dan sesuai dengan saldo bank
```

**Acceptance Criteria:**
- ✅ Upload mutasi bank (Excel/CSV)
- ✅ Sistem matching otomatis berdasarkan jumlah & tanggal
- ✅ Tampilkan list matched & unmatched transactions
- ✅ Manual matching untuk transaksi yang tidak ketemu otomatis
- ✅ Mark transaksi sebagai "Terverifikasi"
- ✅ Export hasil rekonsiliasi

---

### US-PAY-007: Setting Harga & Kategori Pembayaran
```
Sebagai Admin/TU,
Saya ingin mengatur jenis dan harga pembayaran,
Sehingga sistem dapat generate tagihan sesuai dengan kebijakan sekolah
```

**Acceptance Criteria:**
- ✅ CRUD kategori pembayaran (nama, harga, deskripsi, frekuensi)
- ✅ Contoh kategori: SPP, Uang Gedung, Seragam, Kegiatan, Donasi
- ✅ Setting harga berbeda per kelas (opsional)
- ✅ Aktif/non-aktif kategori
- ✅ History perubahan harga

---

## ⚙️ Functional Requirements

### FR-PAY-001: Payment Categories Management
**Priority:** Must Have  
**Description:** Admin dapat mengelola kategori dan harga pembayaran.

**Details:**
**Payment Categories:**
- **SPP (Sekolah Pengajian Per Bulan):**
  - Frekuensi: Bulanan
  - Harga: Rp 300,000/bulan (contoh, configurable)
  - Wajib: Ya
  - Applicable to: Semua siswa
  
- **Uang Gedung:**
  - Frekuensi: Sekali (saat masuk)
  - Harga: Rp 5,000,000
  - Wajib: Ya
  - Applicable to: Siswa baru saja
  
- **Seragam:**
  - Frekuensi: Tahunan atau as-needed
  - Harga: Rp 500,000 (paket lengkap)
  - Wajib: Tidak (optional)
  - Applicable to: Per siswa yang beli
  
- **Kegiatan (Trip, Ekskul, dll):**
  - Frekuensi: Ad-hoc
  - Harga: Variable (input manual per kegiatan)
  - Wajib: Tidak
  - Applicable to: Siswa yang ikut kegiatan
  
- **Donasi/Sumbangan:**
  - Frekuensi: Voluntary
  - Harga: Variable (input manual)
  - Wajib: Tidak
  - Applicable to: Per siswa

**CRUD Operations:**
- Create: tambah kategori baru dengan field (nama, kode, harga_default, frekuensi, wajib, deskripsi)
- Read: list semua kategori dengan status aktif/non-aktif
- Update: edit kategori existing (simpan history perubahan harga)
- Delete: soft delete (non-aktifkan kategori)

**Business Logic:**
- Jika harga kategori diubah, hanya berlaku untuk tagihan yang belum di-generate
- Kategori SPP tidak bisa dihapus (core category)
- Harga bisa berbeda per kelas (advanced feature, opsional MVP)

---

### FR-PAY-002: Generate Monthly SPP Bills
**Priority:** Must Have  
**Description:** TU dapat generate tagihan SPP untuk semua siswa setiap bulan.

**Details:**
**Input:**
- Bulan & Tahun Ajaran (dropdown)
- Kelas (dropdown: All/Specific class)
- Preview: jumlah siswa, total tagihan

**Process:**
1. TU buka halaman "Generate Tagihan SPP"
2. Pilih bulan, tahun, kelas (all or specific)
3. Click "Preview"
4. Sistem query siswa aktif di kelas tersebut
5. Calculate total tagihan: jumlah_siswa × harga_SPP_per_kelas
6. Tampilkan preview (table: nama siswa, kelas, jumlah tagihan)
7. TU review, click "Generate"
8. Sistem create record di `bills` table:
   - student_id
   - category_id (SPP)
   - amount
   - due_date (tanggal jatuh tempo: tanggal 10 bulan tersebut, configurable)
   - status: Unpaid
   - period: bulan-tahun (e.g., "2025-01" untuk Januari 2025)
9. Success notification: "{X} tagihan SPP berhasil di-generate"
10. (Optional) Auto-trigger reminder notification ke orang tua

**Business Logic:**
- Tagihan hanya di-generate untuk siswa dengan status Aktif
- Tidak bisa generate tagihan duplikat untuk periode yang sama (validasi: cek apakah sudah ada tagihan SPP untuk siswa & periode tersebut)
- Tanggal jatuh tempo: tanggal 10 setiap bulan (configurable di settings)
- Jika generate di tengah bulan, due_date tetap tanggal 10 bulan tersebut

---

### FR-PAY-003: Manual Payment Recording
**Priority:** Must Have  
**Description:** TU dapat mencatat pembayaran yang dilakukan siswa (tunai/transfer).

**Details:**
**Input:**
- Pilih siswa (search by nama/NIS)
- Pilih tagihan yang dibayar (dropdown: list unpaid bills untuk siswa tersebut)
  - Atau: input manual jika pembayaran non-tagihan (e.g., donasi)
- Jenis pembayaran (dropdown: kategori)
- Jumlah dibayar (number input, auto-filled dari tagihan)
- Metode pembayaran (dropdown: Tunai, Transfer Bank, EDC/Debit)
- Tanggal bayar (date picker, default: hari ini)
- Nomor referensi (optional, untuk transfer: nomor transaksi bank)
- Keterangan (optional, textarea)

**Process:**
1. TU buka halaman "Input Pembayaran"
2. Search & pilih siswa
3. Sistem load list tagihan unpaid untuk siswa tersebut
4. TU pilih tagihan atau input manual
5. Fill form
6. Click "Simpan & Cetak Kwitansi"
7. Sistem:
   - Auto-generate nomor kwitansi (format: `KWT/{tahun}/{bulan}/{nomor_urut}`, e.g., KWT/2025/01/0001)
   - Save payment record ke `payments` table
   - Update bill status jadi Paid (jika full payment) atau Partial (jika partial)
   - Update bill paid_amount dan remaining_amount
8. Success notification
9. Auto-open print dialog untuk kwitansi (PDF)

**Partial Payment:**
- Jika jumlah dibayar < jumlah tagihan, bill status = Partial Paid
- Remaining amount = tagihan - jumlah dibayar
- Bisa bayar cicilan multiple kali sampai lunas

---

### FR-PAY-004: Payment Receipt (Kwitansi) Generation
**Priority:** Must Have  
**Description:** Sistem generate kwitansi PDF setelah pembayaran dicatat.

**Details:**
**Kwitansi Content:**
- Header: Logo & Nama Sekolah, Alamat, Kontak
- Nomor Kwitansi (prominent)
- Tanggal Pembayaran
- Nama Siswa, Kelas, NIS
- Detail Pembayaran:
  - Jenis Pembayaran
  - Periode (jika SPP)
  - Jumlah Dibayar (Rp format dengan terbilang)
  - Metode Pembayaran
- Footer: Tanda tangan TU, Stamp/Stempel sekolah (digital)
- Note: "Harap simpan kwitansi ini sebagai bukti pembayaran yang sah"

**Format:**
- PDF, A5 landscape atau portrait (disesuaikan dengan thermal printer)
- Printable (optimize untuk printer kasir/thermal)
- QR Code untuk verifikasi kwitansi (encode: nomor_kwitansi + tanggal + jumlah)

**Business Logic:**
- Kwitansi tidak bisa diedit setelah di-generate (immutable)
- Jika ada kesalahan, batalkan payment & generate ulang
- Orang tua dapat download kwitansi dari portal (PDF)

---

### FR-PAY-005: Student Bills & Payment Status (Parent View)
**Priority:** Must Have  
**Description:** Orang tua dapat melihat tagihan dan status pembayaran anak.

**Details:**
**Dashboard Summary:**
- Card: Status SPP bulan ini (Lunas/Belum Bayar/Menunggak)
- Total Tunggakan (jika ada, highlight merah)
- Tagihan Terdekat (due date paling dekat)

**Detail Page:**
- **Tab 1: Tagihan Aktif**
  - Table: Jenis, Periode, Jumlah, Status, Due Date, Actions
  - Status badge: Lunas (hijau), Belum Bayar (kuning), Terlambat (merah)
  - Action: "Lihat Detail" (untuk tagihan Partial Paid, show remaining amount)
  
- **Tab 2: Riwayat Pembayaran**
  - Table: Tanggal, Jenis, Jumlah, Metode, Nomor Kwitansi, Actions
  - Action: "Download Kwitansi" (PDF)
  - Filter: bulan, tahun, jenis pembayaran

**Business Logic:**
- Hanya tampilkan tagihan & payment untuk anak sendiri
- Status "Terlambat" jika due_date sudah lewat dan status masih Unpaid
- Download kwitansi: generate PDF dari payment data

**Access Control:**
- Orang tua: read-only, hanya anak sendiri
- Guru: tidak ada akses ke payment info (privacy)
- TU & Principal: full access semua siswa

---

### FR-PAY-006: Payment Reminder Notification
**Priority:** Must Have  
**Description:** Sistem auto-send reminder ke orang tua untuk tagihan yang belum dibayar.

**Details:**
**Reminder Schedule:**
1. **H-5 sebelum due date:** Reminder pertama
   - "Yth. Bapak/Ibu {nama_ortu}, tagihan SPP bulan {bulan} untuk {nama_siswa} sebesar Rp{jumlah} akan jatuh tempo pada tanggal {due_date}. Mohon segera melunasi. Terima kasih."
   
2. **Pada due date (tanggal 10):** Reminder kedua
   - "Yth. Bapak/Ibu {nama_ortu}, hari ini adalah jatuh tempo pembayaran SPP bulan {bulan} untuk {nama_siswa} sebesar Rp{jumlah}. Mohon segera melunasi untuk menghindari keterlambatan."
   
3. **H+7 setelah due date:** Reminder ketiga (warning)
   - "Yth. Bapak/Ibu {nama_ortu}, pembayaran SPP bulan {bulan} untuk {nama_siswa} sebesar Rp{jumlah} sudah melewati jatuh tempo. Total tunggakan saat ini: Rp{total_tunggakan}. Mohon segera melunasi atau hubungi sekolah jika ada kendala."

**Process:**
- Cronjob running daily (06:00 AM)
- Query bills dengan status Unpaid dan due_date sesuai schedule
- Send notification via WhatsApp (primary) atau Email (secondary)
- Log notification sent ke `notification_logs` table
- Jika bill sudah dibayar sebelum reminder, skip notification

**Configuration:**
- Reminder schedule configurable di settings (H-5, H-0, H+7)
- Enable/disable reminder per kategori
- Template message editable oleh TU

---

### FR-PAY-007: Financial Reports
**Priority:** Must Have  
**Description:** Kepala sekolah & TU dapat melihat laporan keuangan.

**Details:**
**Report Types:**

1. **Laporan Pemasukan Bulanan:**
   - Filter: bulan, tahun, jenis pembayaran
   - Summary:
     - Total Pemasukan (sum paid payments)
     - Total Piutang (sum unpaid bills)
     - Tingkat Kolektibilitas (paid / (paid + unpaid) × 100%)
   - Breakdown per jenis pembayaran:
     - Table: Jenis, Jumlah Tagihan, Jumlah Terbayar, Piutang, %
   - Chart: Bar chart pemasukan per jenis, Pie chart proporsi pemasukan

2. **Laporan Piutang:**
   - List siswa dengan status Menunggak (unpaid bills)
   - Table: NIS, Nama Siswa, Kelas, Total Tunggakan, Lama Tunggakan (hari)
   - Sort by: total tunggakan (desc) atau lama tunggakan (desc)
   - Highlight siswa dengan tunggakan > 3 bulan (high risk)
   - Action: "Kirim Reminder Manual" (button per siswa)

3. **Laporan Pemasukan Per Kelas:**
   - Summary pemasukan per kelas
   - Table: Kelas, Jumlah Siswa, Total Tagihan, Total Terbayar, Piutang, %
   - Chart: Bar chart comparison per kelas

4. **Laporan Harian (Daily Cash Flow):**
   - List semua pembayaran hari ini
   - Table: Waktu, Nama Siswa, Kelas, Jenis, Jumlah, Metode, Nomor Kwitansi
   - Summary: Total Pemasukan Tunai, Total Transfer, Grand Total
   - Export to Excel untuk serah terima kasir

**Export:**
- Excel (.xlsx): full data table
- PDF: formatted report dengan chart (untuk presentasi/arsip)

**Access Control:**
- Kepala Sekolah: view all reports
- TU: view all reports + download
- Guru: no access

---

### FR-PAY-008: Bank Reconciliation (Optional MVP)
**Priority:** Should Have  
**Description:** TU dapat melakukan rekonsiliasi pembayaran dengan mutasi bank.

**Details:**
**Input:**
- Upload file mutasi bank (Excel atau CSV format)
- Expected columns: Tanggal, Deskripsi/Berita, Jumlah, Nomor Referensi

**Process:**
1. TU upload file mutasi bank
2. Sistem parse file
3. Auto-matching:
   - Match berdasarkan jumlah & tanggal (toleransi ±1 hari)
   - Jika ada payment dengan jumlah & tanggal sama, mark as "Matched"
4. Tampilkan result:
   - **Matched Transactions:** list payment yang cocok dengan mutasi bank (check icon)
   - **Unmatched Transactions (Payment):** payment di sistem tapi tidak ada di bank (warning)
   - **Unmatched Transactions (Bank):** transaksi di bank tapi tidak ada di sistem (error)
5. Manual Matching:
   - TU bisa manual link unmatched payment dengan unmatched bank transaction
   - Drag & drop atau click to match
6. Mark as Verified:
   - Setelah matching selesai, TU mark sebagai "Terverifikasi"
   - Update payment record dengan flag `is_verified: true` dan `verified_at: timestamp`
7. Export hasil rekonsiliasi (Excel)

**Business Logic:**
- Bank reconciliation untuk payment dengan metode "Transfer Bank" saja
- Payment yang terverifikasi tidak bisa diedit lagi (immutable, harus cancel & create new)
- Rekonsiliasi sebaiknya dilakukan weekly atau monthly

---

### FR-PAY-009: Payment History & Audit Trail
**Priority:** Should Have  
**Description:** Sistem menyimpan audit trail untuk semua transaksi pembayaran.

**Details:**
**Audit Log:**
- Setiap payment record memiliki audit trail:
  - created_by (user_id TU yang input)
  - created_at (timestamp)
  - updated_by (jika ada koreksi)
  - updated_at
  - cancelled_by (jika payment dibatalkan)
  - cancelled_at
  - cancellation_reason

**Payment Cancellation:**
- TU dapat membatalkan payment jika ada kesalahan
- Input alasan pembatalan (required)
- Payment status → Cancelled
- Bill status kembali ke Unpaid (atau Partial jika ada payment lain)
- Generate "Nota Pembatalan" (PDF) untuk dokumentasi
- Log cancellation ke audit trail

**View Audit Log:**
- Super Admin & Principal dapat view audit log per payment
- Tampilkan history perubahan (timeline view)

---

### FR-PAY-010: Custom Payment Creation (Non-SPP)
**Priority:** Must Have  
**Description:** TU dapat create tagihan custom untuk pembayaran non-rutin.

**Details:**
**Use Case:**
- Siswa beli seragam tambahan
- Siswa ikut trip/study tour
- Pembayaran kegiatan ekskul
- Donasi khusus
- Denda (e.g., buku rusak)

**Process:**
1. TU buka "Buat Tagihan Custom"
2. Input:
   - Pilih siswa atau multiple siswa (jika kegiatan group)
   - Pilih kategori atau "Custom"
   - Nama tagihan (e.g., "Study Tour Bandung")
   - Jumlah tagihan
   - Due date
   - Deskripsi (optional)
3. Click "Buat Tagihan"
4. Sistem create bill record
5. Tagihan muncul di portal orang tua
6. (Optional) Send notification ke orang tua

**Business Logic:**
- Custom payment bisa dibayar cicilan (partial payment allowed)
- Bisa bulk create untuk multiple siswa dengan jumlah sama

---

## 📏 Business Rules

### BR-PAY-001: SPP Billing Cycle
- SPP di-generate setiap awal bulan (tanggal 1, atau configurable)
- Due date: tanggal 10 setiap bulan (configurable)
- Status Terlambat jika belum dibayar setelah due date
- Tunggakan dihitung dari due date

### BR-PAY-002: Payment Allocation
- Jika siswa punya multiple tagihan dan bayar partial, payment dialokasikan ke tagihan tertua terlebih dahulu (FIFO)
- TU bisa override allocation (manual pilih tagihan mana yang dibayar)

### BR-PAY-003: Receipt Number
- Format: `KWT/{tahun}/{bulan}/{nomor_urut}`
- Nomor urut increment per bulan (reset setiap bulan baru)
- Nomor kwitansi tidak bisa duplikat
- Kwitansi yang dibatalkan tetap tercatat (nomor tidak di-reuse)

### BR-PAY-004: Payment Cancellation
- Hanya TU & Super Admin yang bisa cancel payment
- Cancel hanya boleh dalam 7 hari setelah payment created (configurable)
- Harus ada alasan pembatalan (audit trail)
- Payment yang sudah di-rekonsiliasi (verified) tidak bisa di-cancel

### BR-PAY-005: Reminder Rules
- Reminder hanya dikirim untuk tagihan wajib (SPP, Uang Gedung)
- Reminder tidak dikirim untuk tagihan optional (Donasi, Kegiatan) kecuali ada flag khusus
- Reminder stop setelah 3x kirim atau setelah dibayar
- Orang tua bisa opt-out dari reminder (setting preference)

### BR-PAY-006: Discount & Subsidy (Future Phase)
- Siswa bisa dapat diskon (e.g., saudara kandung, prestasi, beasiswa)
- Diskon dihitung saat generate tagihan
- Diskon harus di-approve oleh Kepala Sekolah

---

## ✅ Validation Rules

### VR-PAY-001: Payment Recording Form

**Siswa:**
- Required
- Must be valid student_id
- Error: "Siswa wajib dipilih"

**Jumlah Dibayar:**
- Required
- Number, min: 1000 (Rp 1,000), max: 100,000,000 (reasonable limit)
- Error: "Jumlah pembayaran wajib diisi", "Jumlah minimal Rp 1,000"

**Metode Pembayaran:**
- Required
- Value: Tunai, Transfer Bank, EDC/Debit
- Error: "Metode pembayaran wajib dipilih"

**Tanggal Bayar:**
- Required
- Valid date
- Tidak boleh future date
- Error: "Tanggal pembayaran tidak boleh di masa depan"

**Nomor Referensi:**
- Optional (wajib jika metode = Transfer Bank)
- Min 5 karakter
- Error: "Nomor referensi wajib diisi untuk pembayaran transfer"

---

### VR-PAY-002: Generate SPP Bills Form

**Bulan & Tahun:**
- Required
- Error: "Bulan dan tahun wajib dipilih"

**Duplikasi:**
- Tidak boleh generate tagihan untuk periode yang sudah ada
- Error: "Tagihan SPP untuk periode {bulan-tahun} sudah di-generate. Silakan cek di daftar tagihan."

---

### VR-PAY-003: Payment Category Form

**Nama Kategori:**
- Required
- Min 3 karakter, max 50 karakter
- Unik
- Error: "Nama kategori wajib diisi", "Nama kategori sudah ada"

**Harga Default:**
- Required (kecuali untuk kategori variable)
- Number, min: 0
- Error: "Harga wajib diisi", "Harga tidak valid"

**Kode Kategori:**
- Required
- 3-10 karakter, uppercase, alphanumeric only
- Unik
- Error: "Kode kategori wajib diisi", "Kode sudah digunakan"

---

## 🎨 UI/UX Requirements

### Payment Recording Page (TU)

**Layout:**
- Header: "Input Pembayaran"
- Form (2 columns di desktop, 1 column di mobile):
  - Left: Pilih siswa (search autocomplete), List tagihan unpaid
  - Right: Form pembayaran (jenis, jumlah, metode, tanggal, dll)
- Footer: Button "Simpan & Cetak" (primary) dan "Batal"

**UX:**
- **Student Search:** 
  - Autocomplete dengan avatar & info (Nama, Kelas, NIS)
  - Search by nama atau NIS
  - Real-time search (debounce 300ms)
- **Unpaid Bills:**
  - List dengan checkbox (multi-select untuk batch payment)
  - Per item: Jenis, Periode, Jumlah, Due Date, Status (badge)
  - Total tagihan dipilih ditampilkan di bawah
- **Jumlah Dibayar:**
  - Auto-filled dengan total tagihan dipilih
  - Editable (untuk partial payment atau custom amount)
  - Format Rupiah real-time (e.g., Rp 300.000)
  - Show terbilang di bawah (e.g., "Tiga Ratus Ribu Rupiah")
- **After Save:**
  - Success notification dengan nomor kwitansi
  - Auto-open print dialog (kwitansi PDF)
  - Option: "Cetak Ulang" atau "Input Pembayaran Baru"

**Mobile:**
- Single column layout
- Sticky footer dengan button
- Touch-friendly form fields
- Native number keyboard untuk jumlah

---

### Parent Payment Dashboard

**Layout:**
- **Summary Cards (Top):**
  - Card 1: Status SPP Bulan Ini (Lunas/Belum Bayar dengan icon)
  - Card 2: Total Tunggakan (Rp amount, red highlight jika > 0)
  - Card 3: Tagihan Terdekat (due date & jumlah)
  
- **Tab Navigation:**
  - Tab 1: Tagihan Aktif (unpaid/partial)
  - Tab 2: Riwayat Pembayaran (history)
  
- **Tab 1: Tagihan Aktif**
  - List/Table tagihan dengan status
  - Per item: Jenis, Periode, Jumlah, Status badge, Due date, Action ("Lihat Detail")
  - Color coding: Belum Bayar (kuning), Terlambat (merah)
  - Sort: due date (ascending, terdekat di atas)
  
- **Tab 2: Riwayat Pembayaran**
  - List/Table payments
  - Per item: Tanggal, Jenis, Jumlah, Metode, Nomor Kwitansi, Action ("Download Kwitansi")
  - Filter: Bulan, Tahun
  - Pagination

**UX:**
- Summary cards dengan icon yang eye-catching
- Total tunggakan ditampilkan prominent (large text, red color)
- Status badge dengan color coding yang jelas
- Button "Download Kwitansi" langsung download PDF (no popup)
- Empty state yang friendly jika tidak ada tagihan/riwayat
- Skeleton loading saat fetch data

**Mobile:**
- Card-based layout (stack vertically)
- Swipeable tabs
- Collapsible card untuk lihat detail tagihan
- FAB (Floating Action Button) untuk quick action: "Hubungi Sekolah" (WhatsApp)

---

### Generate SPP Bills Page (TU)

**Layout:**
- Header: "Generate Tagihan SPP"
- **Step 1: Input** (form)
  - Dropdown: Bulan
  - Dropdown: Tahun Ajaran
  - Dropdown: Kelas (All/Specific)
  - Button "Preview" (secondary)
  
- **Step 2: Preview** (table)
  - Table: Nama Siswa, Kelas, Jumlah Tagihan
  - Summary: Total Siswa, Total Tagihan
  - Button "Kembali" dan "Generate" (primary)
  
- **Step 3: Success**
  - Success message dengan icon
  - Summary: "{X} tagihan berhasil di-generate. Total: Rp{Y}"
  - Button "Selesai" atau "Generate Lagi"

**UX:**
- Preview sebelum generate untuk validasi
- Jumlah tagihan per siswa bisa berbeda (jika ada diskon/subsidy) - tampilkan note
- Loading indicator saat generate (jika siswa banyak, bisa 2-3 detik)
- Success animation

**Mobile:**
- Wizard-style form (step by step)
- Full-width buttons
- Preview table scroll horizontal jika columns banyak

---

### Financial Report Dashboard (Principal/TU)

**Layout:**
- **Summary Cards (Top Row):**
  - Card 1: Total Pemasukan Bulan Ini (Rp amount, icon uang)
  - Card 2: Total Piutang (Rp amount, icon warning)
  - Card 3: Tingkat Kolektibilitas (% dengan progress bar)
  - Card 4: Jumlah Siswa Menunggak (number, icon alert)
  
- **Filters:**
  - Dropdown: Bulan, Tahun, Jenis Pembayaran
  - Button: "Tampilkan" dan "Export" (Excel/PDF dropdown)
  
- **Charts Section:**
  - Left: Bar Chart - Pemasukan per Jenis Pembayaran
  - Right: Line Chart - Tren Pemasukan per Bulan (last 6 months)
  
- **Table Section:**
  - Tab: Pemasukan | Piutang | Per Kelas
  - Table dengan data sesuai tab
  - Pagination
  
- **Actions:**
  - Export Excel (full data)
  - Export PDF (formatted report dengan chart)
  - Print

**UX:**
- Real-time update saat ubah filter
- Interactive chart (hover untuk lihat detail)
- Color coding:
  - Hijau: pemasukan, status lunas
  - Merah: piutang, tunggakan
  - Kuning: partial payment
- Tooltip untuk info tambahan
- Responsive chart (mobile: stack charts vertically)

**Mobile:**
- Summary cards stack 2x2 atau 1x4
- Charts full-width, swipeable
- Table collapse jadi card list
- Sticky filter bar

---

### Receipt (Kwitansi) Template

**Design:**
- **Format:** A5 atau thermal (configurable)
- **Layout:**
  ```
  +----------------------------------+
  | [Logo]    NAMA SEKOLAH           |
  |           Alamat, Telp           |
  +----------------------------------+
  | KWITANSI PEMBAYARAN              |
  | No: KWT/2025/01/0001             |
  | Tanggal: 12 Desember 2025        |
  +----------------------------------+
  | Telah terima dari:               |
  | Nama Siswa : Ahmad Rizki         |
  | Kelas      : 1A                  |
  | NIS        : 20250001            |
  |                                  |
  | Untuk pembayaran:                |
  | - SPP Bulan Desember 2025        |
  |                                  |
  | Jumlah : Rp 300.000              |
  | Terbilang: Tiga Ratus Ribu Rupiah|
  |                                  |
  | Metode : Tunai                   |
  +----------------------------------+
  |           [QR Code]              |
  |                                  |
  | Petugas: [Nama TU]               |
  | [Tanda Tangan Digital/Stempel]   |
  +----------------------------------+
  | Simpan kwitansi sebagai bukti    |
  +----------------------------------+
  ```

**Features:**
- QR Code untuk verifikasi (scan untuk cek validitas kwitansi)
- Watermark "LUNAS" (semi-transparent background)
- Printable (optimize untuk printer thermal kasir atau A4/A5 office printer)
- Footer dengan disclaimer

---

## 🔗 Integration Points

### INT-PAY-001: Student Management Module
- Fetch student data untuk payment recording
- Fetch active students untuk generate bills

### INT-PAY-002: Notification Module
- Send WhatsApp reminder untuk payment due
- Send email notification untuk payment received
- Send in-app notification untuk new bill

### INT-PAY-003: Dashboard Module
- Provide payment summary untuk dashboard (semua roles)
- Provide financial data untuk charts & analytics

### INT-PAY-004: Report Module
- Export payment data untuk financial reports
- Provide data untuk end-of-year report

### INT-PAY-005: Payment Gateway (Phase 2)
- Integration dengan Midtrans/Xendit/Doku untuk online payment
- Virtual Account untuk auto-reconciliation
- Webhook untuk payment confirmation

---

## 🧪 Test Scenarios

### TS-PAY-001: Record Manual Payment (Full)
1. Login sebagai TU
2. Go to "Input Pembayaran"
3. Search & pilih siswa "Ahmad Rizki"
4. List tagihan muncul: SPP Desember (Rp 300,000, Unpaid)
5. Select tagihan, jumlah auto-filled
6. Pilih metode: Tunai
7. Click "Simpan & Cetak"
8. **Expected:** 
   - Success notification dengan nomor kwitansi
   - Bill status → Paid
   - Print dialog open dengan kwitansi PDF
   - Payment record tersimpan

### TS-PAY-002: Record Partial Payment
1. Input pembayaran untuk siswa dengan tagihan Rp 300,000
2. Input jumlah: Rp 150,000 (partial)
3. Simpan
4. **Expected:**
   - Bill status → Partial Paid
   - Remaining amount: Rp 150,000
   - Payment record tersimpan dengan amount Rp 150,000

### TS-PAY-003: Generate Monthly SPP Bills
1. Login sebagai TU
2. Go to "Generate Tagihan SPP"
3. Pilih: Bulan Januari, Tahun 2025, Kelas All
4. Click "Preview"
5. **Expected:** Table muncul dengan 180 siswa, total Rp 54,000,000
6. Click "Generate"
7. **Expected:** 
   - Success message "180 tagihan berhasil di-generate"
   - Tagihan muncul di list bills (status Unpaid)
   - Orang tua bisa lihat tagihan di portal

### TS-PAY-004: Duplicate Bill Prevention
1. Generate tagihan SPP Januari 2025 untuk semua siswa
2. Coba generate lagi untuk periode yang sama
3. **Expected:** Error "Tagihan SPP untuk periode Januari 2025 sudah di-generate"

### TS-PAY-005: Parent View Bills
1. Login sebagai Orang Tua (Ahmad Rizki)
2. Go to "Pembayaran" / Dashboard
3. **Expected:**
   - Summary card: SPP Bulan Ini - Belum Bayar
   - Tab Tagihan Aktif: SPP Desember Rp 300,000 (status: Belum Bayar, due 10 Des)
   - Tab Riwayat: (empty jika belum ada payment)

### TS-PAY-006: Parent Download Receipt
1. Setelah pembayaran dicatat oleh TU
2. Orang tua login, go to Riwayat Pembayaran
3. List muncul: 12 Des 2025, SPP Desember, Rp 300,000, KWT/2025/12/0001
4. Click "Download Kwitansi"
5. **Expected:** Download file PDF kwitansi dengan data lengkap

### TS-PAY-007: Auto Reminder - Before Due Date
1. Ada tagihan SPP dengan due date 10 Januari 2025
2. Tanggal 5 Januari, cronjob running (06:00 AM)
3. **Expected:** 
   - System send WhatsApp reminder ke orang tua
   - Log notification sent
   - Reminder tidak dikirim jika tagihan sudah dibayar

### TS-PAY-008: Auto Reminder - After Due Date (Overdue)
1. Tagihan SPP Desember due date 10 Des, belum dibayar
2. Tanggal 17 Des (H+7), cronjob running
3. **Expected:**
   - Send warning reminder ke orang tua dengan total tunggakan
   - Flag bill as "Overdue"

### TS-PAY-009: Financial Report - Monthly Income
1. Login sebagai Principal
2. Go to "Laporan Keuangan"
3. Filter: Desember 2025, Jenis: Semua
4. Click "Tampilkan"
5. **Expected:**
   - Summary cards: Total Pemasukan, Piutang, Kolektibilitas
   - Chart: breakdown per jenis pembayaran
   - Table: detail pemasukan
   - Export Excel & PDF button available

### TS-PAY-010: Bank Reconciliation
1. Login sebagai TU
2. Go to "Rekonsiliasi Bank"
3. Upload mutasi bank (Excel) dengan 10 transaksi
4. **Expected:**
   - System auto-match 8 transaksi (hijau)
   - 2 transaksi unmatched (kuning)
5. Manual match 2 transaksi
6. Mark as Verified
7. **Expected:**
   - Semua transaksi matched
   - Payment record updated dengan flag verified
   - Export hasil rekonsiliasi

### TS-PAY-011: Create Custom Payment (Study Tour)
1. Login sebagai TU
2. Go to "Buat Tagihan Custom"
3. Pilih: 20 siswa kelas 6A (multi-select)
4. Kategori: Kegiatan, Nama: "Study Tour Bandung", Jumlah: Rp 500,000, Due: 31 Jan 2025
5. Click "Buat Tagihan"
6. **Expected:**
   - 20 tagihan custom dibuat untuk 20 siswa
   - Tagihan muncul di portal orang tua dengan deskripsi jelas

### TS-PAY-012: Payment Cancellation
1. TU salah input pembayaran (siswa salah)
2. Go to detail payment
3. Click "Batalkan Pembayaran"
4. Input alasan: "Salah input siswa"
5. Konfirmasi
6. **Expected:**
   - Payment status → Cancelled
   - Bill status kembali Unpaid
   - Generate "Nota Pembatalan" (PDF)
   - Audit log tersimpan

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Payment categories management (CRUD)
- ✅ Generate monthly SPP bills (bulk untuk semua siswa)
- ✅ Manual payment recording (tunai & transfer)
- ✅ Print receipt (kwitansi PDF)
- ✅ Parent view bills & payment history
- ✅ Parent download receipt
- ✅ Payment reminder notification (WhatsApp)
- ✅ Financial reports (pemasukan, piutang, per kelas)
- ✅ Export reports to Excel & PDF
- ✅ Payment status tracking (Unpaid/Partial/Paid)
- ✅ Custom payment creation
- ✅ Partial payment support

### Should Have (MVP):
- ✅ Bank reconciliation (upload & matching)
- ✅ Payment cancellation dengan audit trail
- ✅ Receipt QR Code untuk verifikasi
- ✅ Auto reminder schedule (H-5, H-0, H+7)
- ✅ Overdue bill flagging
- ✅ Discount/subsidy support (basic)
- ✅ Daily cash flow report untuk kasir

### Could Have (Nice to Have):
- ⬜ Payment via mobile app (in-app payment)
- ⬜ Multi-payment method selection (kombinasi tunai + transfer)
- ⬜ Payment installment plan (recurring cicilan)
- ⬜ Receipt email auto-send
- ⬜ Payment analytics & prediction
- ⬜ Integration dengan accounting software (Accurate, Jurnal)

### Won't Have (Phase 2):
- ⬜ Payment Gateway integration (VA, QRIS, Credit Card)
- ⬜ Auto-reconciliation via API
- ⬜ E-wallet integration (GoPay, OVO, Dana)
- ⬜ Payment link generation (unique link per siswa)
- ⬜ Subscription management (auto-recurring)
- ⬜ Advanced discount rules (promo, voucher)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft - Ready for Review

