# Modul 3: Attendance System (Sistem Absensi)

## 📋 Overview

Modul ini menangani dua jenis absensi: absensi siswa (harian pagi dan per mata pelajaran) serta presensi guru. Termasuk manajemen izin/sakit dengan upload dokumen pendukung.

**Module Code:** `ATT`  
**Priority:** P0 (Critical)  
**Dependencies:** Authentication, Student Management

---

## 🎯 Tujuan

1. Digitalisasi pencatatan kehadiran siswa dan guru
2. Memudahkan guru input absensi real-time
3. Monitoring kehadiran untuk deteksi siswa bermasalah
4. Otomasi perhitungan persentase kehadiran
5. Manajemen izin/sakit dengan workflow verifikasi
6. Dasar perhitungan honor guru berdasarkan kehadiran

---

## 📖 User Stories

### US-ATT-001: Input Absensi Harian Pagi
```
Sebagai Guru Kelas,
Saya ingin menginput absensi harian pagi untuk siswa di kelas saya,
Sehingga kehadiran siswa tercatat setiap hari
```

**Acceptance Criteria:**
- ✅ List semua siswa di kelas dengan radio button: Hadir, Izin, Sakit, Alpha
- ✅ Default semua siswa: Hadir
- ✅ Tanggal absensi auto-filled (hari ini), bisa diubah untuk koreksi absensi kemarin
- ✅ Quick action: "Tandai Semua Hadir"
- ✅ Simpan absensi
- ✅ Success notification
- ✅ Tidak bisa input absensi untuk tanggal yang sama 2x (edit mode jika sudah ada)

---

### US-ATT-002: Input Absensi Per Mata Pelajaran
```
Sebagai Guru Mata Pelajaran,
Saya ingin menginput absensi siswa per mata pelajaran yang saya ajar,
Sehingga kehadiran siswa per jam pelajaran tercatat
```

**Acceptance Criteria:**
- ✅ Pilih kelas yang diajar
- ✅ Pilih mata pelajaran
- ✅ Tanggal & jam pelajaran (auto dari jadwal, bisa manual)
- ✅ List siswa dengan checkbox/radio: Hadir, Izin, Sakit, Alpha
- ✅ Simpan absensi
- ✅ History absensi per mata pelajaran tersimpan

---

### US-ATT-003: Orang Tua Submit Izin/Sakit
```
Sebagai Orang Tua,
Saya ingin mengajukan izin atau surat sakit untuk anak saya,
Sehingga guru mengetahui alasan ketidakhadiran anak
```

**Acceptance Criteria:**
- ✅ Form submit izin dengan field: tanggal, alasan, upload foto surat (optional)
- ✅ Submit ke sistem
- ✅ Status: Pending (menunggu verifikasi guru/TU)
- ✅ Notifikasi ke guru kelas
- ✅ Orang tua dapat lihat status izin (Pending/Disetujui/Ditolak)

---

### US-ATT-004: Guru/TU Verifikasi Izin/Sakit
```
Sebagai Guru/TU,
Saya ingin memverifikasi pengajuan izin/sakit dari orang tua,
Sehingga absensi siswa akurat dan ter-approve
```

**Acceptance Criteria:**
- ✅ Notifikasi badge untuk izin pending
- ✅ List izin pending dengan detail (nama siswa, tanggal, alasan, foto surat)
- ✅ Action: Setujui atau Tolak
- ✅ Jika setujui: absensi siswa otomatis update jadi Izin/Sakit
- ✅ Jika tolak: orang tua dapat notifikasi dengan alasan penolakan
- ✅ Status tersimpan

---

### US-ATT-005: Lihat Rekap Absensi Siswa
```
Sebagai Guru/TU/Kepala Sekolah,
Saya ingin melihat rekap absensi siswa per kelas atau per siswa,
Sehingga saya dapat memonitor kehadiran dan mendeteksi masalah
```

**Acceptance Criteria:**
- ✅ Filter: kelas, siswa, rentang tanggal, jenis absensi (harian/per mapel)
- ✅ Table rekap dengan kolom: Tanggal, Hadir, Izin, Sakit, Alpha, Persentase
- ✅ Chart visualisasi (pie chart atau bar chart)
- ✅ Export to Excel/PDF
- ✅ Highlight siswa dengan persentase kehadiran < 80% (warning)

---

### US-ATT-006: Guru Input Presensi Pribadi (Clock In/Out)
```
Sebagai Guru,
Saya ingin melakukan presensi saat datang dan pulang,
Sehingga kehadiran saya tercatat untuk perhitungan honor
```

**Acceptance Criteria:**
- ✅ Button "Clock In" saat datang (pagi)
- ✅ Button "Clock Out" saat pulang (sore)
- ✅ Capture timestamp dan lokasi (GPS coordinates) - optional
- ✅ Tampilkan jam masuk & jam keluar hari ini
- ✅ History presensi guru tersimpan
- ✅ TU/Kepala dapat lihat rekap presensi guru

---

### US-ATT-007: TU/Kepala Lihat Rekap Presensi Guru
```
Sebagai TU/Kepala Sekolah,
Saya ingin melihat rekap presensi guru,
Sehingga saya dapat menghitung honor dan memonitor kedisiplinan
```

**Acceptance Criteria:**
- ✅ Filter: guru, rentang tanggal
- ✅ Table rekap: Tanggal, Jam Masuk, Jam Keluar, Total Jam Kerja, Status (Hadir/Izin/Sakit/Alpha)
- ✅ Summary: Total hari hadir, persentase kehadiran, total jam mengajar
- ✅ Export to Excel
- ✅ Highlight guru yang terlambat atau tidak clock out

---

## ⚙️ Functional Requirements

### FR-ATT-001: Student Daily Attendance Input
**Priority:** Must Have  
**Description:** Guru dapat menginput absensi harian pagi untuk siswa di kelasnya.

**Details:**
**Input:**
- Tanggal absensi (default: hari ini, bisa ubah untuk koreksi)
- Kelas (auto-selected berdasarkan guru kelas, bisa pilih jika guru mengajar multiple kelas)
- List siswa dengan status: Hadir (H), Izin (I), Sakit (S), Alpha (A)

**Process:**
1. Guru buka halaman "Absensi Harian"
2. Sistem load siswa di kelas guru (sorted by NIS atau nama)
3. Default semua siswa: Hadir
4. Guru ubah status siswa yang tidak hadir
5. Click "Simpan"
6. Validasi: tidak boleh ada absensi duplikat untuk tanggal & kelas yang sama
7. Jika sudah ada absensi hari ini, masuk edit mode
8. Save ke database dengan timestamp & user_id (guru yang input)
9. Success notification

**Business Logic:**
- Absensi harian hanya 1x per hari per kelas
- Jam input: 06:00 - 12:00 (ideal), tapi bisa input sampai 23:59 untuk fleksibilitas
- Jika ada izin/sakit yang sudah di-approve untuk tanggal tersebut, auto-set status Izin/Sakit
- Weekend (Sabtu-Minggu) dan hari libur nasional: skip (tidak wajib ada absensi)

---

### FR-ATT-002: Student Subject Attendance Input
**Priority:** Must Have  
**Description:** Guru mata pelajaran dapat input absensi per jam pelajaran.

**Details:**
**Input:**
- Tanggal (default: hari ini)
- Kelas
- Mata pelajaran (dropdown, based on jadwal guru)
- Jam pelajaran (dropdown: 1, 2, 3, dst atau range jam: 07:00-08:30)
- List siswa dengan status: Hadir, Izin, Sakit, Alpha

**Process:**
1. Guru buka halaman "Absensi Per Mata Pelajaran"
2. Pilih kelas & mata pelajaran
3. Sistem load siswa di kelas
4. Default semua siswa: Hadir
5. Input absensi
6. Simpan
7. Save ke database: `subject_attendance` table dengan relasi ke mata pelajaran

**Business Logic:**
- Bisa ada multiple absensi per hari per kelas (untuk mata pelajaran berbeda)
- Unique constraint: tanggal + kelas + mata_pelajaran + jam_pelajaran
- Jika jadwal ada di sistem, auto-suggest mata pelajaran & jam berdasarkan jadwal hari ini

---

### FR-ATT-003: Leave/Sick Request Submission (Parent)
**Priority:** Must Have  
**Description:** Orang tua dapat mengajukan izin/sakit untuk anak via sistem.

**Details:**
**Input:**
- Pilih anak (jika orang tua punya multiple anak di sekolah)
- Jenis: Izin atau Sakit (radio button)
- Tanggal mulai (date picker, bisa hari ini atau future)
- Tanggal selesai (date picker, untuk izin multiple hari)
- Alasan (textarea, required, min 10 karakter)
- Upload foto surat (optional, image file, max 5MB)

**Process:**
1. Orang tua login, go to "Pengajuan Izin"
2. Fill form
3. Submit
4. Save ke database: `leave_requests` table dengan status: Pending
5. Trigger notifikasi ke guru kelas & TU
6. Success notification untuk orang tua: "Pengajuan izin telah dikirim dan menunggu persetujuan"

**Business Logic:**
- Tanggal izin tidak boleh past date > 7 hari (untuk fleksibilitas koreksi)
- Bisa ajukan izin untuk future date (advance notice)
- Orang tua dapat lihat history izin dengan status

---

### FR-ATT-004: Leave/Sick Request Verification
**Priority:** Must Have  
**Description:** Guru/TU dapat memverifikasi pengajuan izin dari orang tua.

**Details:**
**Input:**
- List pending requests dengan detail
- Action: Approve atau Reject
- Jika reject: alasan penolakan (textarea, required)

**Process:**
1. Guru/TU buka halaman "Verifikasi Izin" atau dapat notifikasi
2. Lihat list pending requests (sorted by tanggal submit, terbaru di atas)
3. Click detail untuk lihat foto surat (jika ada)
4. Click "Setujui" atau "Tolak"
5. Jika Setujui:
   - Update status jadi Approved
   - Auto-update absensi siswa untuk tanggal tersebut jadi Izin/Sakit
   - Notifikasi ke orang tua: "Pengajuan izin disetujui"
6. Jika Tolak:
   - Update status jadi Rejected
   - Input alasan penolakan
   - Notifikasi ke orang tua: "Pengajuan izin ditolak. Alasan: {alasan}"

**Business Logic:**
- Hanya guru kelas atau TU yang bisa approve/reject
- Setelah di-approve, absensi tidak bisa diubah manual (harus via update leave request)
- History approval tersimpan (user_id, timestamp, action)

---

### FR-ATT-005: Attendance Report (Student)
**Priority:** Must Have  
**Description:** User dapat melihat rekap absensi siswa dengan berbagai filter.

**Details:**
**Filters:**
- Kelas (dropdown, all/specific class)
- Siswa (dropdown, all/specific student) - jika filter per siswa
- Rentang tanggal (date range picker, default: bulan ini)
- Jenis absensi (dropdown: Harian/Per Mata Pelajaran/All)

**Output:**
- **Tabel Rekap:**
  - Columns: Tanggal, Status (H/I/S/A), Keterangan (jika ada izin)
  - Summary row: Total Hadir, Izin, Sakit, Alpha, Persentase Kehadiran
  
- **Chart:**
  - Pie chart: proporsi Hadir, Izin, Sakit, Alpha
  - Bar chart: kehadiran per hari/minggu (untuk range tertentu)
  
- **Actions:**
  - Export to Excel
  - Export to PDF
  - Print

**Access Control:**
- Guru: hanya siswa di kelas yang diajar
- TU & Principal: semua siswa
- Orang Tua: hanya anak sendiri

**Business Logic:**
- Persentase kehadiran = (Hadir + Izin + Sakit) / Total Hari Sekolah * 100%
  - Note: Izin & Sakit dihitung sebagai "kehadiran valid"
  - Alpha tidak dihitung
- Highlight siswa dengan persentase < 80% (warning: risiko tidak naik kelas)
- Exclude weekend dan hari libur dari perhitungan

---

### FR-ATT-006: Teacher Clock In/Out (Attendance)
**Priority:** Must Have  
**Description:** Guru dapat melakukan presensi clock in/out untuk kehadiran harian.

**Details:**
**Clock In:**
- Button "Clock In" tersedia di dashboard guru (pagi)
- Click button → capture timestamp dan lokasi (GPS optional)
- Save ke database: `teacher_attendance` table
- Tampilkan "Anda sudah clock in pada {jam}"

**Clock Out:**
- Button "Clock Out" tersedia setelah clock in
- Click button → capture timestamp
- Calculate total jam kerja (jam_out - jam_in)
- Update record
- Tampilkan "Anda sudah clock out pada {jam}. Total jam kerja: {X} jam"

**Business Logic:**
- Clock in: 06:00 - 10:00 (ideal), tapi flexible sampai 23:59
- Jika clock in > 07:30, flag sebagai "Terlambat"
- Clock out: anytime setelah clock in
- Jika lupa clock out, TU bisa edit manual
- Satu guru hanya bisa clock in 1x per hari (edit mode jika sudah clock in)

**Additional Features (Optional MVP):**
- GPS location capture (untuk validasi guru berada di sekolah)
- Photo capture saat clock in (selfie untuk validasi)
- Auto clock out pada jam 16:00 jika lupa

---

### FR-ATT-007: Teacher Attendance Report
**Priority:** Must Have  
**Description:** TU/Principal dapat melihat rekap presensi guru.

**Details:**
**Filters:**
- Guru (dropdown, all/specific)
- Rentang tanggal (date range picker)

**Output:**
- **Tabel Rekap:**
  - Columns: Tanggal, Jam Masuk, Jam Keluar, Total Jam, Status, Keterangan
  - Status: Hadir, Terlambat, Izin, Sakit, Alpha
  - Highlight row untuk status Terlambat/Alpha (color coding)
  
- **Summary:**
  - Total Hari Hadir
  - Total Hari Terlambat
  - Total Jam Kerja (untuk perhitungan honor)
  - Persentase Kehadiran
  
- **Actions:**
  - Export to Excel (untuk payroll)
  - Export to PDF
  - Print

**Business Logic:**
- Status Hadir: clock in < 07:30
- Status Terlambat: clock in >= 07:30
- Status Alpha: tidak ada clock in untuk hari kerja (weekday)
- Izin/Sakit: input manual oleh TU (separate flow)

---

### FR-ATT-008: Teacher Leave Management
**Priority:** Should Have  
**Description:** Guru dapat mengajukan izin/cuti, TU/Principal dapat approve/reject.

**Details:**
**Guru Submit Leave:**
- Form: jenis (Izin/Sakit/Cuti), tanggal mulai, tanggal selesai, alasan, upload surat
- Submit → status Pending
- Notifikasi ke TU/Principal

**TU/Principal Approval:**
- List pending leave requests
- Approve/Reject dengan alasan (jika reject)
- Update status
- Notifikasi ke guru

**Integration:**
- Setelah approved, auto-set status guru di `teacher_attendance` jadi Izin/Sakit untuk tanggal tersebut
- Tidak dihitung sebagai Alpha

---

### FR-ATT-009: Manual Attendance Correction
**Priority:** Should Have  
**Description:** TU dapat mengedit absensi siswa/guru jika ada kesalahan input.

**Details:**
- TU buka halaman absensi
- Pilih tanggal & kelas/guru
- Edit status absensi
- Input alasan koreksi (audit trail)
- Save
- Log perubahan (user_id, timestamp, old_value, new_value, reason)

**Access Control:**
- Hanya TU & Super Admin yang bisa edit
- Guru tidak bisa edit absensi yang sudah disimpan (harus minta TU)

---

### FR-ATT-010: Attendance Notification & Alerts
**Priority:** Should Have  
**Description:** Sistem mengirim notifikasi otomatis terkait absensi.

**Notifications:**
1. **Ke Orang Tua (end of day):**
   - "Anak Anda {nama} tidak hadir hari ini. Status: {Alpha}. Jika ada kesalahan, silakan ajukan izin."
   - Hanya jika status Alpha (tidak hadir tanpa keterangan)
   
2. **Ke Guru Kelas (morning reminder):**
   - "Jangan lupa input absensi harian untuk kelas {kelas} hari ini."
   - Jika belum input absensi hingga jam 10:00
   
3. **Ke TU (weekly summary):**
   - "Ada {X} siswa dengan kehadiran < 80% minggu ini."
   - Setiap hari Jumat sore
   
4. **Ke Principal (monthly report):**
   - "Laporan absensi bulan {bulan}: Rata-rata kehadiran siswa {X}%, guru {Y}%."
   - Setiap awal bulan

**Delivery:**
- WhatsApp (prioritas untuk orang tua)
- Email (untuk guru/TU/principal)
- In-app notification (bell icon)

---

## 📏 Business Rules

### BR-ATT-001: Daily Attendance Window
- Absensi harian bisa diinput dari jam 06:00 hingga 23:59
- Ideal input: 07:00 - 09:00 (setelah bel masuk)
- TU bisa input/edit absensi untuk hari sebelumnya (koreksi)

### BR-ATT-002: Attendance Status Priority
- Jika ada izin/sakit yang di-approve, status Izin/Sakit override status Alpha
- Sequence: Approved Leave > Manual Input > Default (Alpha)

### BR-ATT-003: Attendance Calculation
- Persentase kehadiran = (Hadir + Izin + Sakit) / Total Hari Sekolah * 100%
- Minimum kehadiran untuk naik kelas: 80% (configurable)
- Weekend dan hari libur tidak dihitung dalam total hari sekolah

### BR-ATT-004: Teacher Attendance
- Guru clock in jam < 07:30 = Hadir
- Guru clock in jam >= 07:30 = Terlambat (tetap dihitung Hadir untuk payroll, tapi ada flag)
- Tidak clock in di hari kerja = Alpha (potong gaji)

### BR-ATT-005: Leave Request
- Orang tua bisa ajukan izin maksimal 30 hari ke depan (advance notice)
- Orang tua bisa ajukan izin untuk past date maksimal 7 hari (fleksibilitas)
- Leave request yang approved tidak bisa di-reject lagi (harus cancel)

---

## ✅ Validation Rules

### VR-ATT-001: Daily Attendance Form
**Tanggal:**
- Required
- Tidak boleh future date > 1 hari (bisa input untuk besok untuk advance planning)
- Error: "Tanggal wajib dipilih", "Tidak dapat input absensi untuk tanggal tersebut"

**Status:**
- Required untuk setiap siswa (default: Hadir)
- Value: H, I, S, A

**Duplikasi:**
- Tidak boleh ada 2 absensi untuk kelas & tanggal yang sama
- Error: "Absensi untuk kelas {kelas} tanggal {tanggal} sudah ada. Silakan edit."

---

### VR-ATT-002: Leave Request Form
**Jenis:**
- Required
- Value: Izin atau Sakit

**Tanggal Mulai:**
- Required
- Tidak boleh past date > 7 hari
- Error: "Tanggal mulai tidak boleh lebih dari 7 hari yang lalu"

**Tanggal Selesai:**
- Required
- Harus >= tanggal mulai
- Error: "Tanggal selesai tidak boleh lebih awal dari tanggal mulai"

**Alasan:**
- Required
- Min 10 karakter
- Error: "Alasan wajib diisi minimal 10 karakter"

**Upload Surat:**
- Optional
- File type: jpg, jpeg, png, pdf
- Max size: 5MB
- Error: "Format file harus jpg/png/pdf", "Ukuran file maksimal 5MB"

---

### VR-ATT-003: Teacher Clock In/Out
**Clock In:**
- Hanya bisa 1x per hari
- Error: "Anda sudah clock in hari ini pada {jam}"

**Clock Out:**
- Hanya bisa setelah clock in
- Error: "Anda belum clock in hari ini"

---

## 🎨 UI/UX Requirements

### Daily Attendance Input Page (Guru)

**Layout:**
- Header: Kelas, Tanggal (date picker), button "Tandai Semua Hadir"
- List siswa (table atau card)
- Per siswa: Foto, Nama, NIS, Radio button (H/I/S/A)
- Footer: Button "Simpan" (primary) dan "Batal"

**UX:**
- Default semua siswa: Hadir (H selected)
- Radio button besar dan touch-friendly (mobile)
- Color coding: H=hijau, I=kuning, S=biru, A=merah
- Jika ada izin pending, tampilkan badge di siswa tersebut dengan tooltip "Ada pengajuan izin pending"
- Quick count: "Hadir: {X}, Izin: {Y}, Sakit: {Z}, Alpha: {W}"
- Auto-save setiap 30 detik (draft, untuk prevent data loss)
- Success notification dengan summary: "Absensi kelas {kelas} tanggal {tanggal} berhasil disimpan. Hadir: {X}, Alpha: {Y}"

**Mobile:**
- Card-based layout per siswa
- Swipe left/right untuk ubah status (optional advanced UX)
- Sticky header dengan count & save button

---

### Leave Request Form (Orang Tua)

**Layout:**
- Title: "Pengajuan Izin/Sakit"
- Form fields:
  - Pilih anak (dropdown, jika multiple)
  - Jenis (radio: Izin/Sakit)
  - Tanggal mulai & selesai (date range picker)
  - Alasan (textarea)
  - Upload surat (drag & drop atau click)
- Button "Kirim" dan "Batal"

**UX:**
- Visual preview foto surat setelah upload
- Character counter untuk alasan (show: 10/100 karakter)
- Date range picker dengan highlight weekend/holiday
- Konfirmasi sebelum submit: "Anda yakin ingin mengirim pengajuan izin?"
- Success message: "Pengajuan berhasil dikirim. Anda akan menerima notifikasi setelah diverifikasi."

**Mobile:**
- Full-screen form
- Native date picker
- Camera option untuk upload surat (take photo langsung)
- Touch-friendly buttons

---

### Leave Verification Page (Guru/TU)

**Layout:**
- Header: "Verifikasi Izin/Sakit" dengan badge count pending
- List pending requests (table atau card)
- Per request: Foto siswa, Nama, Kelas, Jenis, Tanggal, Alasan, Foto surat (thumbnail)
- Actions: Button "Setujui" (hijau) dan "Tolak" (merah)

**UX:**
- Click thumbnail foto surat → open modal preview (lightbox)
- Click "Tolak" → modal input alasan penolakan (required)
- After action, request hilang dari list (atau move ke tab "Riwayat")
- Tab: Pending | Disetujui | Ditolak (untuk lihat history)
- Filter: kelas, tanggal, status

**Mobile:**
- Card-based layout
- Swipe actions (swipe right = approve, swipe left = reject) - advanced UX

---

### Attendance Report Page

**Layout:**
- Header: "Rekap Absensi Siswa" dengan filters (kelas, siswa, tanggal, jenis)
- Button "Export Excel" dan "Export PDF"
- Content area:
  - Tab: Tabel | Grafik
  - **Tab Tabel:** 
    - Table dengan columns: Tanggal, Status, Keterangan
    - Summary row di atas: Total H/I/S/A, Persentase
    - Pagination jika data banyak
  - **Tab Grafik:**
    - Pie chart: proporsi H/I/S/A
    - Bar chart: kehadiran per minggu/bulan
    - Summary cards: Total Hadir, Persentase Kehadiran, Warning (jika < 80%)

**UX:**
- Real-time update chart saat ubah filter
- Persentase ditampilkan dengan progress bar dan color coding:
  - >= 90%: hijau
  - 80-89%: kuning
  - < 80%: merah (warning)
- Warning message jika siswa < 80%: "Perhatian: Kehadiran di bawah minimum 80%. Siswa berisiko tidak naik kelas."

**Mobile:**
- Stack filters vertically
- Tab swipe navigation
- Chart responsive (resize untuk mobile)
- Export button di floating action button (FAB)

---

### Teacher Clock In/Out Widget (Dashboard)

**Layout:**
- Card/widget di dashboard guru
- Title: "Presensi Hari Ini"
- Content:
  - Tanggal & hari
  - Status: "Belum Clock In" atau "Sudah Clock In pada {jam}"
  - Button "Clock In" (jika belum) atau "Clock Out" (jika sudah clock in)
  - Jika sudah clock out: "Clock Out pada {jam}. Total jam kerja: {X} jam"

**UX:**
- Button besar dan prominent (primary color)
- Clock In button: icon jam + text "Masuk"
- Clock Out button: icon jam + text "Pulang"
- Loading indicator saat click (capture GPS)
- Success animation setelah clock in/out
- Daily summary di bawah: "Anda hadir {X} hari bulan ini. Terlambat: {Y} kali."

**Mobile:**
- Full-width button
- Haptic feedback saat click (vibrate)
- Native GPS permission prompt

---

## 🔗 Integration Points

### INT-ATT-001: Student Management Module
- Fetch student list per class untuk input absensi
- Fetch student info untuk display di attendance pages

### INT-ATT-002: Teacher Management Module
- Fetch teacher schedule untuk suggest mata pelajaran & jam
- Fetch teacher list untuk report

### INT-ATT-003: Notification Module
- Send WhatsApp notification ke orang tua (Alpha alert)
- Send email notification ke guru (reminder input absensi)
- Send in-app notification (leave request approval)

### INT-ATT-004: Report Module
- Provide attendance data untuk dashboard & analytics
- Provide attendance data untuk report card (rapor)

### INT-ATT-005: Payment Module (Future)
- Kehadiran < 80% bisa trigger warning di payment (risiko tidak naik kelas, refund consideration)

---

## 🧪 Test Scenarios

### TS-ATT-001: Input Daily Attendance
1. Login sebagai Guru Kelas
2. Go to "Absensi Harian"
3. List siswa kelas 1A muncul, default semua Hadir
4. Ubah 2 siswa jadi Alpha
5. Click "Simpan"
6. **Expected:** Success message, absensi tersimpan, muncul di rekap

### TS-ATT-002: Duplicate Attendance Prevention
1. Input absensi harian untuk kelas 1A tanggal 12 Des 2025
2. Coba input lagi untuk kelas & tanggal yang sama
3. **Expected:** Masuk edit mode (load data existing), tidak bisa create baru

### TS-ATT-003: Parent Submit Leave Request
1. Login sebagai Orang Tua
2. Go to "Pengajuan Izin"
3. Fill form: Jenis=Izin, Tanggal=besok, Alasan="Acara keluarga", Upload foto
4. Submit
5. **Expected:** Success message, status Pending, notifikasi ke guru

### TS-ATT-004: Teacher Approve Leave Request
1. Login sebagai Guru Kelas
2. Notifikasi badge "1 izin pending"
3. Go to "Verifikasi Izin"
4. Lihat detail request, foto surat
5. Click "Setujui"
6. **Expected:** Status jadi Approved, absensi siswa auto-update jadi Izin, notifikasi ke orang tua

### TS-ATT-005: Teacher Reject Leave Request
1. Verifikasi izin
2. Click "Tolak"
3. Input alasan: "Surat tidak valid"
4. Submit
5. **Expected:** Status jadi Rejected, notifikasi ke orang tua dengan alasan

### TS-ATT-006: Teacher Clock In
1. Login sebagai Guru
2. Dashboard tampilkan "Presensi Hari Ini: Belum Clock In"
3. Click "Clock In" (jam 07:15)
4. **Expected:** Success, tampilan update "Sudah Clock In pada 07:15", button berubah jadi "Clock Out"

### TS-ATT-007: Teacher Clock In Late
1. Clock in pada jam 08:00
2. **Expected:** Success, tapi ada flag "Terlambat" di record, tampilkan warning message "Anda terlambat hari ini"

### TS-ATT-008: View Attendance Report (Guru)
1. Login sebagai Guru
2. Go to "Rekap Absensi"
3. Pilih kelas 1A, tanggal: 1-31 Des 2025
4. Click "Lihat Rekap"
5. **Expected:** Table rekap muncul dengan data, summary total H/I/S/A, chart, persentase kehadiran per siswa

### TS-ATT-009: Export Attendance Report to Excel
1. Dari halaman rekap absensi
2. Set filter: kelas 1A, bulan Desember
3. Click "Export Excel"
4. **Expected:** Download file `Absensi_1A_Desember_2025.xlsx` dengan data sesuai filter

### TS-ATT-010: Parent View Child Attendance
1. Login sebagai Orang Tua
2. Go to dashboard, pilih anak
3. Go to menu "Absensi"
4. **Expected:** Tampil rekap absensi anak bulan ini, summary H/I/S/A, persentase, chart

### TS-ATT-011: Alert for Low Attendance
1. Siswa A kehadiran 75% (di bawah 80%)
2. Guru/TU buka rekap absensi
3. **Expected:** Row siswa A di-highlight merah, tampil warning icon, message "Kehadiran di bawah minimum"

### TS-ATT-012: Automatic Alpha Alert to Parent
1. Siswa tidak hadir (status Alpha) hari ini
2. Jam 15:00 (end of day), sistem cek absensi
3. **Expected:** Auto-send WhatsApp ke orang tua: "Anak Anda {nama} tidak hadir hari ini tanpa keterangan. Status: Alpha."

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Input absensi harian pagi (per kelas)
- ✅ Input absensi per mata pelajaran
- ✅ Orang tua submit izin/sakit dengan upload foto
- ✅ Guru/TU verifikasi izin (approve/reject)
- ✅ Auto-update absensi setelah izin di-approve
- ✅ Rekap absensi siswa (table, chart, summary)
- ✅ Guru clock in/out untuk presensi
- ✅ Rekap presensi guru untuk TU/Principal
- ✅ Export attendance report to Excel
- ✅ Persentase kehadiran calculation
- ✅ Alert untuk kehadiran < 80%

### Should Have (MVP):
- ✅ WhatsApp notification ke orang tua (Alpha alert)
- ✅ Reminder ke guru untuk input absensi
- ✅ Teacher leave management (submit & approval)
- ✅ Manual attendance correction oleh TU
- ✅ GPS location capture saat clock in (optional)
- ✅ History leave request dengan status
- ✅ Weekly/monthly attendance summary notification

### Could Have (Nice to Have):
- ⬜ Bulk attendance input (upload Excel)
- ⬜ QR code attendance (scan QR untuk clock in)
- ⬜ Face recognition untuk presensi
- ⬜ Auto clock out jika lupa
- ⬜ Integration dengan smart card/RFID
- ⬜ Predictive alert (siswa yang sering alpha, predict risk)
- ⬜ Parent excuse letter template (generate surat izin otomatis)

### Won't Have (Phase 2):
- ⬜ Real-time GPS tracking guru
- ⬜ Geofencing (auto clock in saat masuk area sekolah)
- ⬜ Integration dengan biometric device (fingerprint reader)
- ⬜ Live attendance dashboard (real-time update saat guru input)
- ⬜ AI anomaly detection (deteksi pola absensi mencurigakan)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft - Ready for Review

