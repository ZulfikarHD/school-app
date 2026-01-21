# Modul 2: Student Management (Manajemen Siswa)

## 📋 Overview

Modul ini menangani semua data dan proses terkait siswa, mulai dari pendaftaran, profil, mutasi, hingga kelulusan.

**Module Code:** `STD`  
**Priority:** P0 (Critical)  
**Dependencies:** Authentication & Authorization

---

## 🎯 Tujuan

1. Centralisasi data siswa dalam satu sistem
2. Memudahkan TU dalam mengelola data siswa
3. Menyediakan data akurat untuk modul lain (absensi, nilai, pembayaran)
4. Tracking status siswa (aktif, mutasi, lulus, DO)
5. Manajemen data orang tua/wali

---

## 📖 User Stories

### US-STD-001: Tambah Data Siswa Baru
```
Sebagai Staf TU,
Saya ingin menambahkan data siswa baru ke dalam sistem,
Sehingga siswa terdaftar dan dapat mengikuti kegiatan sekolah
```

**Acceptance Criteria:**
- ✅ Form input data siswa lengkap (biodata, alamat, orang tua)
- ✅ Upload foto siswa (optional)
- ✅ Validasi NIK/NISN unik (tidak boleh duplikat)
- ✅ Auto-generate NIS (Nomor Induk Siswa) sekolah
- ✅ Pilih kelas & tahun ajaran
- ✅ Simpan data ke database
- ✅ Success notification setelah berhasil
- ✅ Generate akun untuk orang tua otomatis (username & password default)

---

### US-STD-002: Lihat Daftar Siswa
```
Sebagai Staf TU,
Saya ingin melihat daftar semua siswa,
Sehingga saya dapat memonitor data siswa yang terdaftar
```

**Acceptance Criteria:**
- ✅ Table/list view dengan kolom: NIS, Nama, Kelas, Status, Actions
- ✅ Filter berdasarkan: kelas, status (aktif/mutasi/lulus), tahun ajaran
- ✅ Search by nama/NIS/NISN
- ✅ Pagination (20 siswa per halaman)
- ✅ Export to Excel
- ✅ Click row untuk lihat detail siswa

---

### US-STD-003: Edit Data Siswa
```
Sebagai Staf TU,
Saya ingin mengedit data siswa yang sudah ada,
Sehingga data siswa selalu up-to-date
```

**Acceptance Criteria:**
- ✅ Form edit dengan data existing pre-filled
- ✅ Validasi sama seperti tambah data
- ✅ Simpan perubahan
- ✅ Log history perubahan (audit trail)
- ✅ Success notification

---

### US-STD-004: Pindahkan Siswa Ke Kelas Lain (Naik Kelas)
```
Sebagai Staf TU,
Saya ingin memindahkan siswa ke kelas yang lebih tinggi saat naik kelas,
Sehingga data kelas siswa sesuai dengan tahun ajaran baru
```

**Acceptance Criteria:**
- ✅ Fitur bulk "Naik Kelas" untuk semua siswa kelas tertentu
- ✅ Pilih kelas asal dan kelas tujuan
- ✅ Preview siswa yang akan dipindahkan
- ✅ Konfirmasi sebelum execute
- ✅ History perpindahan kelas tersimpan
- ✅ Success notification dengan jumlah siswa yang dipindahkan

---

### US-STD-005: Update Status Siswa (Mutasi/DO/Lulus)
```
Sebagai Staf TU,
Saya ingin mengubah status siswa menjadi mutasi/DO/lulus,
Sehingga data siswa yang tidak aktif tidak tercampur dengan siswa aktif
```

**Acceptance Criteria:**
- ✅ Dropdown status: Aktif, Mutasi, DO (Drop Out), Lulus
- ✅ Jika mutasi: input tanggal mutasi, alasan, sekolah tujuan
- ✅ Jika DO: input tanggal, alasan
- ✅ Jika lulus: input tanggal kelulusan, tahun ajaran
- ✅ Status non-aktif tidak muncul di list default (hanya jika filter)
- ✅ History status tersimpan

---

### US-STD-006: Lihat Profil Siswa Lengkap
```
Sebagai Guru,
Saya ingin melihat profil lengkap siswa di kelas saya,
Sehingga saya mengenal siswa dengan baik
```

**Acceptance Criteria:**
- ✅ Halaman detail siswa dengan tab/section:
  - Biodata (foto, nama, NIK, NISN, TTL, alamat)
  - Data Orang Tua (nama, pekerjaan, kontak)
  - Riwayat Kelas
  - Riwayat Absensi (summary)
  - Riwayat Nilai (summary)
  - Riwayat Pembayaran (summary) - hanya TU & Principal
- ✅ Guru hanya bisa lihat siswa di kelas yang diajar
- ✅ TU & Principal bisa lihat semua siswa

---

### US-STD-007: Orang Tua Lihat Profil Anak
```
Sebagai Orang Tua,
Saya ingin melihat profil anak saya,
Sehingga saya mengetahui data yang tercatat di sekolah
```

**Acceptance Criteria:**
- ✅ Orang tua hanya bisa lihat data anak sendiri
- ✅ Tampilan profil anak (read-only)
- ✅ Jika orang tua punya lebih dari 1 anak di sekolah, bisa switch antar anak
- ✅ Mobile-friendly

---

## ⚙️ Functional Requirements

### FR-STD-001: Create Student Profile
**Priority:** Must Have  
**Description:** TU dapat menambahkan data siswa baru ke sistem.

**Details:**
**Input Fields:**
- **Biodata Siswa:**
  - Nama Lengkap (required)
  - Nama Panggilan (optional)
  - NIK (required, 16 digit, unik)
  - NISN (required, 10 digit, unik)
  - NIS (auto-generate, format: `{tahun_masuk}{nomor_urut}`, contoh: 20250001)
  - Jenis Kelamin (required, dropdown: L/P)
  - Tempat Lahir (required)
  - Tanggal Lahir (required, date picker)
  - Agama (required, dropdown: Islam, Kristen, Katolik, Hindu, Buddha, Konghucu)
  - Anak ke- (required, number)
  - Jumlah Saudara (required, number)
  - Status dalam Keluarga (dropdown: Anak Kandung, Anak Tiri, Anak Angkat)
  - Alamat Lengkap (required, textarea)
  - RT/RW (optional)
  - Kelurahan/Desa (required)
  - Kecamatan (required)
  - Kota/Kabupaten (required)
  - Provinsi (required)
  - Kode Pos (optional)
  - Nomor HP Siswa (optional)
  - Email Siswa (optional, untuk fase future)
  - Foto Siswa (optional, upload image, max 2MB, format: jpg/png)
  
- **Data Akademik:**
  - Tahun Ajaran Masuk (required, dropdown)
  - Kelas Saat Ini (required, dropdown: 1A, 1B, 2A, 2B, ... 6A, 6B)
  - Status (default: Aktif)
  - Tanggal Masuk (required, date picker)
  
- **Data Orang Tua/Wali:**
  - **Ayah:**
    - Nama Lengkap (required)
    - NIK (required)
    - Pekerjaan (required)
    - Pendidikan Terakhir (dropdown: SD, SMP, SMA, D3, S1, S2, S3)
    - Penghasilan (dropdown: <1jt, 1-3jt, 3-5jt, >5jt)
    - Nomor HP (required)
    - Email (optional)
  - **Ibu:**
    - Nama Lengkap (required)
    - NIK (required)
    - Pekerjaan (required)
    - Pendidikan Terakhir (dropdown)
    - Penghasilan (dropdown)
    - Nomor HP (optional)
    - Email (optional)
  - **Wali (jika ada):**
    - Nama Lengkap (optional)
    - NIK (optional)
    - Hubungan dengan Siswa (optional)
    - Pekerjaan (optional)
    - Nomor HP (optional)
  - **Kontak Utama:** Radio button pilih (Ayah/Ibu/Wali) → akan digunakan untuk notifikasi

**Process:**
1. TU mengisi form
2. Validasi input
3. Auto-generate NIS
4. Save data siswa
5. Auto-create akun orang tua:
   - Username: nomor HP kontak utama
   - Password: `Ortu{NIS}` (contoh: Ortu20250001)
   - Role: PARENT
   - Link ke student_id
6. Success notification + option "Tambah Siswa Lagi" atau "Lihat Detail"

---

### FR-STD-002: Read Student List
**Priority:** Must Have  
**Description:** User dapat melihat daftar siswa dengan filter dan search.

**Details:**
- **Table Columns:**
  - NIS
  - Foto (thumbnail)
  - Nama Lengkap
  - Kelas
  - Status (badge: Aktif/Mutasi/DO/Lulus)
  - Actions (icon: view, edit, delete)
  
- **Filters:**
  - Kelas (dropdown, all/1A/1B/.../6B)
  - Status (dropdown, all/Aktif/Mutasi/DO/Lulus)
  - Tahun Ajaran (dropdown)
  - Jenis Kelamin (dropdown, all/L/P)
  
- **Search:**
  - By Nama, NIS, atau NISN
  - Real-time search (debounce 300ms)
  
- **Actions:**
  - Export to Excel (semua data atau filtered data)
  - Bulk actions: Naik Kelas, Print ID Card
  
- **Pagination:**
  - 20 items per page (configurable)
  - Show total siswa count

**Access Control:**
- Super Admin, Principal, Admin: view all students
- Teacher: view only students in their class
- Parent: redirect to own child profile

---

### FR-STD-003: Update Student Profile
**Priority:** Must Have  
**Description:** TU dapat mengupdate data siswa yang sudah ada.

**Details:**
- Form sama seperti Create, pre-filled dengan data existing
- Tidak bisa edit NIS (read-only)
- NIK dan NISN harus tetap unik (excluding current student)
- Save changes
- Log perubahan ke audit trail:
  - User who edited
  - Timestamp
  - Fields yang diubah (before & after value)
- Success notification

---

### FR-STD-004: Delete Student (Soft Delete)
**Priority:** Should Have  
**Description:** TU dapat menghapus data siswa (soft delete, tidak permanent).

**Details:**
- Soft delete: set flag `deleted_at` timestamp
- Data tidak muncul di list default
- Admin dapat restore jika terhapus tidak sengaja
- Konfirmasi sebelum delete: "Apakah Anda yakin ingin menghapus siswa {nama}?"
- Jika siswa punya data berelasi (absensi, nilai, pembayaran), tampilkan warning

---

### FR-STD-005: Bulk Class Promotion (Naik Kelas)
**Priority:** Must Have  
**Description:** TU dapat memindahkan siswa naik kelas secara massal.

**Details:**
- **Flow:**
  1. Pilih tahun ajaran lama & baru
  2. Pilih kelas asal (contoh: Kelas 1A)
  3. Pilih kelas tujuan (contoh: Kelas 2A)
  4. Preview list siswa yang akan dipindahkan (table dengan checkbox)
  5. Uncheck jika ada siswa yang tidak naik/tinggal kelas
  6. Konfirmasi
  7. Execute bulk update
  8. Success notification: "{X} siswa berhasil dipindahkan ke {kelas_tujuan}"
  
- **Business Logic:**
  - Kelas tujuan harus lebih tinggi dari kelas asal (kecuali tinggal kelas)
  - Update field `kelas_saat_ini` dan `tahun_ajaran`
  - Insert record ke `student_class_history` table
  - Option: auto-promote semua kelas sekaligus (1→2, 2→3, dst) untuk efisiensi

---

### FR-STD-006: Update Student Status
**Priority:** Must Have  
**Description:** TU dapat mengubah status siswa (Aktif/Mutasi/DO/Lulus).

**Details:**
- **Status Options:**
  - **Aktif:** Siswa aktif bersekolah (default)
  - **Mutasi:** Siswa pindah ke sekolah lain
    - Input required: tanggal_mutasi, alasan, sekolah_tujuan
  - **DO (Drop Out):** Siswa keluar tanpa pindah
    - Input required: tanggal_keluar, alasan
  - **Lulus:** Siswa lulus (hanya untuk kelas 6)
    - Input required: tanggal_kelulusan, tahun_ajaran_lulus
    
- **Business Logic:**
  - Status Mutasi/DO/Lulus → siswa tidak muncul di list default (hanya jika filter aktif)
  - Status Lulus hanya bisa untuk siswa kelas 6
  - Save history ke `student_status_history` table
  - Notify orang tua via WhatsApp (opsional)

---

### FR-STD-007: View Student Detail Profile
**Priority:** Must Have  
**Description:** User dapat melihat profil lengkap siswa.

**Details:**
- **Layout:** Tab atau accordion dengan sections:
  1. **Biodata:** 
     - Foto siswa
     - Data pribadi lengkap
     - Alamat
  2. **Data Orang Tua:**
     - Info Ayah, Ibu, Wali
     - Kontak utama (highlight)
  3. **Data Akademik:**
     - NIS, NISN
     - Kelas saat ini
     - Status
     - Tanggal masuk
  4. **Riwayat Kelas:**
     - Table: Tahun Ajaran, Kelas, Wali Kelas
  5. **Riwayat Absensi (Summary):**
     - Chart: Hadir, Izin, Sakit, Alpha (bulan ini/semester ini)
     - Link ke detail absensi
  6. **Riwayat Nilai (Summary):**
     - Table: Mata Pelajaran, Rata-rata Nilai (semester terakhir)
     - Link ke detail rapor
  7. **Riwayat Pembayaran (Summary):** (hanya untuk TU & Principal)
     - Status SPP: Lunas/Menunggak
     - Total tunggakan (jika ada)
     - Link ke detail pembayaran
     
- **Actions:**
  - Edit Data (button) - hanya TU & Principal
  - Print Profil (PDF)
  - Reset Password Orang Tua (button) - hanya TU
  - Change Status (button) - hanya TU

**Access Control:**
- TU & Principal: full access semua siswa
- Teacher: hanya siswa di kelas yang diajar
- Parent: hanya anak sendiri, dan hide section Pembayaran (summary tetap ada di menu lain)

---

### FR-STD-008: Parent Account Creation (Auto)
**Priority:** Must Have  
**Description:** Saat siswa baru ditambahkan, akun orang tua otomatis dibuat.

**Details:**
- Trigger: saat save data siswa baru
- Auto-create user account:
  - Role: PARENT
  - Username: nomor HP kontak utama (remove spaces/dash)
  - Password: `Ortu{NIS}` (harus diganti saat first login)
  - Email: email kontak utama (jika ada)
  - `is_first_login`: true
  - Link ke `student_id`
- Jika orang tua sudah punya akun (punya anak lain di sekolah):
  - Link student baru ke akun existing
  - Support multi-child per parent account
- Send notifikasi ke orang tua via WhatsApp:
  - "Selamat! Anak Anda {nama_siswa} telah terdaftar di {nama_sekolah}. Username: {username}, Password: {password}. Silakan login di {url} dan ganti password Anda."

---

### FR-STD-009: Parent - View Own Child Profile
**Priority:** Must Have  
**Description:** Orang tua dapat melihat profil anak sendiri.

**Details:**
- Dashboard orang tua tampilkan card/list anak (jika lebih dari 1)
- Click card anak → ke halaman profil anak
- Layout profil sama seperti FR-STD-007, tapi read-only
- Hide section Riwayat Pembayaran (ada di menu Payment tersendiri)
- Mobile-friendly, card-based layout

---

### FR-STD-010: Export Student Data to Excel
**Priority:** Should Have  
**Description:** TU dapat export data siswa ke Excel untuk reporting/backup.

**Details:**
- Button "Export Excel" di halaman list siswa
- Export data yang terlihat (setelah filter/search), atau all data
- Columns:
  - NIS, NISN, NIK
  - Nama Lengkap
  - Jenis Kelamin, TTL
  - Alamat
  - Kelas, Status
  - Nama Orang Tua (Ayah & Ibu)
  - Kontak Orang Tua
- Filename: `Data_Siswa_{tanggal}.xlsx`
- Format: Excel (.xlsx), dengan header row

---

## 📏 Business Rules

### BR-STD-001: NIS Auto-Generation
- Format: `{tahun_masuk}{nomor_urut}`
- Contoh: 20250001 (siswa ke-1 yang masuk tahun 2025)
- Nomor urut increment otomatis per tahun masuk
- NIS tidak bisa diubah setelah dibuat

### BR-STD-002: Unique Identifiers
- NIK harus unik (16 digit)
- NISN harus unik (10 digit)
- NIS harus unik (auto-generated)
- Nomor HP kontak utama harus unik untuk parent account (1 HP = 1 akun, support multi-child)

### BR-STD-003: Class Assignment
- Siswa harus memiliki 1 kelas aktif setiap saat
- Siswa bisa pindah kelas (manual atau bulk naik kelas)
- History perpindahan kelas harus disimpan

### BR-STD-004: Status Transition
- Aktif → Mutasi/DO/Lulus (allowed)
- Mutasi/DO/Lulus → Aktif (allowed, jika ada kesalahan input, tapi perlu confirmation)
- Status change harus dicatat di history

### BR-STD-005: Soft Delete
- Delete siswa = soft delete (set `deleted_at`)
- Data siswa tidak benar-benar dihapus dari database
- Admin dapat restore siswa yang terhapus

### BR-STD-006: Parent Account
- 1 nomor HP = 1 parent account
- 1 parent account dapat link ke multiple children (jika orang tua punya beberapa anak di sekolah)
- Parent account auto-created saat siswa baru ditambahkan
- Username parent = nomor HP kontak utama

---

## ✅ Validation Rules

### VR-STD-001: Create/Update Student Form

**Nama Lengkap:**
- Required
- Min 3 karakter, max 100 karakter
- Hanya huruf dan spasi
- Error: "Nama lengkap wajib diisi", "Nama minimal 3 karakter"

**NIK:**
- Required
- Exactly 16 digit angka
- Unik (tidak boleh duplikat)
- Error: "NIK wajib diisi", "NIK harus 16 digit", "NIK sudah terdaftar"

**NISN:**
- Required
- Exactly 10 digit angka
- Unik
- Error: "NISN wajib diisi", "NISN harus 10 digit", "NISN sudah terdaftar"

**Tanggal Lahir:**
- Required
- Valid date
- Umur minimal 5 tahun, maksimal 15 tahun (untuk SD)
- Error: "Tanggal lahir wajib diisi", "Umur siswa tidak sesuai untuk jenjang SD"

**Nomor HP:**
- Required (untuk kontak utama orang tua)
- Format Indonesia: 08xxxxxxxxxx atau +628xxxxxxxxxx
- Min 10 digit, max 15 digit
- Angka saja (remove spaces/dash otomatis)
- Error: "Nomor HP wajib diisi", "Format nomor HP tidak valid"

**Email:**
- Optional
- Valid email format
- Error: "Format email tidak valid"

**Foto Siswa:**
- Optional
- File type: jpg, jpeg, png
- Max size: 2MB
- Dimensions: recommended 300x400px (portrait)
- Error: "Format file harus jpg/png", "Ukuran file maksimal 2MB"

**Kelas:**
- Required
- Must be valid class from dropdown
- Error: "Kelas wajib dipilih"

---

### VR-STD-002: Status Change Form

**Status Mutasi:**
- Tanggal Mutasi: required, date, tidak boleh future date
- Alasan: required, min 10 karakter
- Sekolah Tujuan: required, min 5 karakter
- Error: "Tanggal mutasi wajib diisi", "Alasan minimal 10 karakter"

**Status DO:**
- Tanggal Keluar: required, date, tidak boleh future date
- Alasan: required, min 10 karakter
- Error: "Tanggal keluar wajib diisi", "Alasan minimal 10 karakter"

**Status Lulus:**
- Hanya untuk siswa kelas 6
- Tanggal Kelulusan: required, date
- Tahun Ajaran: required
- Error: "Hanya siswa kelas 6 yang bisa lulus", "Tanggal kelulusan wajib diisi"

---

## 🎨 UI/UX Requirements

### Student List Page

**Layout:**
- Header dengan title "Data Siswa" dan button "Tambah Siswa"
- Filter bar (horizontal) dengan 4 dropdowns + search box
- Table responsive (scroll horizontal di mobile)
- Pagination di bawah table
- Export Excel button di atas table (kanan)

**Table Design:**
- Row hover effect
- Badge untuk status (Aktif: hijau, Mutasi: kuning, DO: merah, Lulus: biru)
- Foto thumbnail 40x40px, rounded
- Actions: icon view (eye), edit (pencil), delete (trash) dengan tooltip

**Mobile:**
- Table collapse jadi card list
- Card per siswa dengan foto, nama, kelas, status
- Tap card untuk expand/lihat detail

---

### Student Form (Create/Edit)

**Layout:**
- Multi-step form atau long form dengan sections
- Section 1: Biodata Siswa
- Section 2: Data Akademik
- Section 3: Data Orang Tua (Ayah)
- Section 4: Data Orang Tua (Ibu)
- Section 5: Data Wali (jika ada, collapsible)
- Button "Simpan" dan "Batal" di bawah form

**UX:**
- Upload foto: drag & drop atau click to upload, preview image
- Date picker untuk tanggal lahir (calendar UI)
- Dropdown dengan search untuk provinsi/kota (banyak data)
- Auto-format nomor HP (remove spaces/dash)
- Real-time validation dengan error message di bawah field
- Required field ditandai dengan asterisk (*)
- Save button disabled sampai semua required field valid

**Mobile:**
- Stack form fields vertically
- Touch-friendly dropdowns
- Native date picker
- Auto-capitalize nama (first letter per word)

---

### Student Detail Page

**Layout:**
- Header: foto siswa (besar, 200x200px), nama, kelas, NIS (prominent)
- Tab navigation: Biodata | Orang Tua | Akademik | Riwayat
- Content area per tab
- Action buttons (top right): Edit, Print, Reset Password, Change Status

**Tab: Biodata**
- 2-column layout (label: value)
- Grouped by kategori (Data Pribadi, Alamat, Kontak)

**Tab: Orang Tua**
- 3 cards: Ayah, Ibu, Wali
- Per card: foto (optional), nama, pekerjaan, kontak
- Highlight kontak utama dengan badge

**Tab: Akademik**
- Info kelas, status, tanggal masuk
- Riwayat kelas (table kecil)

**Tab: Riwayat**
- Sub-tab: Absensi, Nilai, Pembayaran
- Chart/summary per sub-tab
- Link "Lihat Detail" ke modul terkait

**Mobile:**
- Tab jadi dropdown select
- Single column layout
- Card-based design

---

### Bulk Naik Kelas Flow

**Step 1: Pilih Kelas**
- Dropdown: Tahun Ajaran Asal & Tujuan
- Dropdown: Kelas Asal & Kelas Tujuan
- Button "Lanjut"

**Step 2: Preview & Konfirmasi**
- Table list siswa di kelas asal dengan checkbox (all selected by default)
- User dapat uncheck siswa yang tidak naik
- Show count: "{X} dari {Y} siswa akan dipindahkan"
- Button "Kembali" dan "Proses Naik Kelas"

**Step 3: Success**
- Success message dengan count siswa
- Button "Selesai" (kembali ke list) atau "Naik Kelas Lagi" (untuk kelas lain)

---

## 🔗 Integration Points

### INT-STD-001: Attendance Module
- Provide student list per class untuk input absensi
- Provide student ID untuk link attendance records

### INT-STD-002: Payment Module
- Provide student info untuk generate tagihan SPP
- Provide parent contact untuk reminder pembayaran

### INT-STD-003: Grades Module
- Provide student list per class untuk input nilai
- Provide student ID untuk link grades records

### INT-STD-004: PSB Module
- Import data siswa dari PSB setelah approval
- Auto-populate form dengan data dari pendaftaran

### INT-STD-005: WhatsApp API
- Send welcome message & credentials ke orang tua saat siswa baru ditambahkan
- Send notification saat ada perubahan status siswa

---

## 🧪 Test Scenarios

### TS-STD-001: Create New Student
1. Login sebagai TU
2. Go to Student Management
3. Click "Tambah Siswa"
4. Fill all required fields
5. Upload foto siswa
6. Click "Simpan"
7. **Expected:** Success message, siswa muncul di list, akun orang tua auto-created

### TS-STD-002: Duplicate NIK/NISN Validation
1. Tambah siswa dengan NIK/NISN yang sudah ada
2. Submit form
3. **Expected:** Error message "NIK sudah terdaftar" atau "NISN sudah terdaftar"

### TS-STD-003: Edit Student Data
1. Buka detail siswa
2. Click "Edit"
3. Ubah beberapa field (nama, alamat, dll)
4. Save
5. **Expected:** Data terupdate, success notification, perubahan tercatat di audit log

### TS-STD-004: Bulk Class Promotion
1. Go to Student Management
2. Click "Naik Kelas"
3. Pilih Kelas 1A (asal) → Kelas 2A (tujuan)
4. Preview list siswa
5. Uncheck 1 siswa (tidak naik kelas)
6. Konfirmasi
7. **Expected:** X-1 siswa pindah ke kelas 2A, 1 siswa tetap di kelas 1A

### TS-STD-005: Change Student Status to Mutasi
1. Buka detail siswa
2. Click "Change Status"
3. Pilih "Mutasi"
4. Isi tanggal, alasan, sekolah tujuan
5. Save
6. **Expected:** Status berubah jadi Mutasi, siswa tidak muncul di list default (filter: Aktif)

### TS-STD-006: Parent View Child Profile
1. Login sebagai Orang Tua
2. Dashboard menampilkan card anak
3. Click card
4. **Expected:** Redirect ke profil anak, tampil semua info, read-only

### TS-STD-007: Export Student Data to Excel
1. Go to Student Management
2. Set filter: Kelas 1A, Status Aktif
3. Click "Export Excel"
4. **Expected:** Download file Excel dengan data siswa kelas 1A yang aktif

### TS-STD-008: Search Student by Name
1. Go to Student Management
2. Ketik nama siswa di search box (partial match)
3. **Expected:** Table filter real-time, hanya siswa dengan nama matching yang muncul

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Create student profile (form lengkap dengan biodata, orang tua, foto)
- ✅ Read student list (table dengan filter, search, pagination)
- ✅ Update student profile
- ✅ Soft delete student
- ✅ View student detail profile (semua data + summary riwayat)
- ✅ Bulk class promotion (naik kelas)
- ✅ Update student status (Aktif/Mutasi/DO/Lulus)
- ✅ Auto-create parent account
- ✅ Parent view own child profile
- ✅ Export student data to Excel
- ✅ Auto-generate NIS

### Should Have (MVP):
- ✅ Audit trail untuk perubahan data siswa
- ✅ Print student profile (PDF)
- ✅ Reset parent password dari admin panel
- ✅ Student class history tracking
- ✅ Status history tracking
- ✅ WhatsApp notification untuk orang tua (welcome message)

### Could Have (Nice to Have):
- ⬜ Print ID Card siswa
- ⬜ Barcode/QR Code untuk NIS
- ⬜ Import student data from Excel
- ⬜ Advanced search (multiple filters kombinasi)
- ⬜ Student photo gallery
- ⬜ Document storage per siswa (e.g., ijazah TK, sertifikat)

### Won't Have (Phase 2):
- ⬜ Student self-registration
- ⬜ Integration dengan Dapodik
- ⬜ Face recognition untuk foto siswa
- ⬜ E-signature untuk dokumen

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft - Ready for Review

