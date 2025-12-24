# Student Management - User Journeys

**Feature:** Student Management  
**Last Updated:** 24 Desember 2025

---

## Overview

Dokumen ini merupakan panduan user journeys untuk Student Management feature yang bertujuan untuk menjelaskan flow lengkap dari perspektif setiap user role, yaitu: TU/Admin yang melakukan CRUD siswa, Guru yang view profil siswa, Kepala Sekolah yang monitoring data, dan Orang Tua yang view profil anak melalui portal dengan step-by-step interaction dan expected system behavior.

---

## Journey 1: TU Tambah Siswa Baru (US-STD-001)

**Actor:** TU/Admin  
**Goal:** Mendaftarkan siswa baru dengan auto-generate NIS dan auto-create parent account  
**Precondition:** User sudah login sebagai TU/Admin

### Happy Path

```
1. TU login → Dashboard Admin
   ↓
2. Klik menu "Data Siswa" di sidebar
   ↓
3. Sistem tampilkan halaman List Siswa (/admin/students)
   - Table siswa dengan kolom: Foto, NIS, Nama, Kelas, Status, Actions
   - Default filter: Status = Aktif
   - Pagination 20 items per page
   ↓
4. Klik button "Tambah Siswa" (hijau, icon Plus)
   ↓
5. Sistem redirect ke /admin/students/create
   - Render StudentForm component dengan 6 sections:
     • Section 1: Biodata Siswa (open by default)
     • Section 2: Alamat (open by default)
     • Section 3: Data Akademik (open by default)
     • Section 4: Data Ayah (open by default)
     • Section 5: Data Ibu (open by default)
     • Section 6: Data Wali (collapsed, optional)
   ↓
6. TU isi Section 1 - Biodata:
   - Upload foto siswa (drag-drop atau click)
     → Preview foto tampil
   - Isi NIK: 1234567890123456 (16 digit)
   - Isi NISN: 1234567890 (10 digit)
   - Isi Nama Lengkap: Ahmad Zaki
   - Isi Nama Panggilan: Zaki
   - Pilih Jenis Kelamin: Laki-laki
   - Isi Tempat Lahir: Jakarta
   - Pilih Tanggal Lahir: 15 Januari 2018 (umur 7 tahun)
   - Pilih Agama: Islam
   - Isi Anak Ke: 1
   - Isi Jumlah Saudara: 2
   - Pilih Status Keluarga: Kandung
   ↓
7. TU isi Section 2 - Alamat:
   - Isi Alamat Lengkap: Jl. Sudirman No. 123
   - Isi RT/RW: 001/002
   - Isi Kelurahan: Menteng
   - Isi Kecamatan: Menteng
   - Isi Kota: Jakarta Pusat
   - Isi Provinsi: DKI Jakarta
   - Isi Kode Pos: 10310
   ↓
8. TU isi Section 3 - Data Akademik:
   - Pilih Kelas: Kelas 1 (dari dropdown)
   - Pilih Tahun Ajaran Masuk: 2025/2026
   - Pilih Tanggal Masuk: 15 Juli 2025
   ↓
9. TU isi Section 4 - Data Ayah:
   - Isi NIK: 3171234567890123
   - Isi Nama Lengkap: Budi Santoso
   - Isi Pekerjaan: Wiraswasta
   - Pilih Pendidikan: S1
   - Isi Penghasilan: 10000000
   - Isi No HP: 081234567890
   - Isi Email: budi@email.com (optional)
   - Check "Jadikan Kontak Utama" ✓
   ↓
10. TU isi Section 5 - Data Ibu:
    - Isi NIK: 3171234567890124
    - Isi Nama Lengkap: Siti Aminah
    - Isi Pekerjaan: Ibu Rumah Tangga
    - Pilih Pendidikan: SMA
    - Isi Penghasilan: 0
    - Isi No HP: 081234567891
    ↓
11. TU skip Section 6 - Data Wali (tidak ada wali)
    ↓
12. TU klik button "Simpan" (hijau, bottom right)
    - Haptic feedback: medium
    - Loading state: button disabled, spinner tampil
    ↓
13. Backend Process:
    - Validate semua input (Form Request)
    - Generate NIS: 20250001 (tahun 2025, urutan 0001)
    - Upload foto ke storage/app/public/students/photos/
    - Create Student record
    - Create/Update Guardian (Ayah) dengan NIK check
    - Create/Update Guardian (Ibu) dengan NIK check
    - Attach guardians ke student via pivot table
    - Auto-create Parent Account:
      • Username: 081234567890 (no HP ayah)
      • Password: Ortu20250001 (Ortu + NIS)
      • Role: PARENT
      • Status: ACTIVE
      • is_first_login: true
    - Link guardian ayah ke user account
    - Insert activity log:
      • action: create_student
      • user_id: TU yang login
      • new_values: { student_id, nis, nama_lengkap }
    - Commit transaction
    ↓
14. Success Response:
    - Redirect ke /admin/students
    - Flash message (hijau): "Siswa berhasil ditambahkan dengan NIS: 20250001"
    - Haptic feedback: heavy
    ↓
15. Sistem tampilkan List Siswa:
    - Siswa baru muncul di top (sorted by created_at DESC)
    - Row highlighted dengan animation fade-in
```

### Alternate Path: Validation Error

```
Step 6-11: TU isi form
↓
Step 12: TU klik "Simpan"
↓
Backend Validation Failed:
- NIK hanya 12 digit (seharusnya 16)
- Umur siswa 3 tahun (seharusnya 5-15 tahun)
↓
Response:
- Form tidak submit
- Error messages tampil di bawah field yang error:
  • NIK: "NIK harus 16 digit"
  • Tanggal Lahir: "Umur siswa harus antara 5-15 tahun"
- Field yang error di-highlight merah
- Scroll ke field error pertama
- Haptic feedback: light
↓
TU fix errors → klik "Simpan" lagi → Success
```

---

## Journey 2: TU Edit Data Siswa (US-STD-003)

**Actor:** TU/Admin  
**Goal:** Update data siswa existing  
**Precondition:** Siswa sudah terdaftar di sistem

### Happy Path

```
1. TU di halaman /admin/students
   ↓
2. TU search siswa by nama: "Ahmad Zaki"
   - Real-time search, hasil muncul saat typing
   ↓
3. Sistem tampilkan hasil search (1 siswa)
   ↓
4. TU klik icon Edit (Pencil) di row siswa
   - Haptic feedback: light
   ↓
5. Sistem redirect ke /admin/students/{id}/edit
   - Form pre-filled dengan data siswa current
   - Foto siswa tampil di preview
   ↓
6. TU update beberapa field:
   - Update Alamat: Jl. Sudirman No. 456 (pindah rumah)
   - Update No HP siswa: 081234567899
   - Update Penghasilan Ayah: 12000000 (naik gaji)
   ↓
7. TU klik "Simpan"
   ↓
8. Backend Process:
   - Validate input
   - Store old values untuk activity log
   - Update student record
   - Detach all guardians
   - Re-attach guardians dengan data baru
   - Insert activity log:
      • action: update_student
      • old_values: { alamat: "Jl. Sudirman No. 123", ... }
      • new_values: { alamat: "Jl. Sudirman No. 456", ... }
   ↓
9. Success Response:
   - Redirect ke /admin/students
   - Flash message: "Data siswa berhasil diupdate."
```

---

## Journey 3: TU Update Status Siswa (US-STD-005)

**Actor:** TU/Admin  
**Goal:** Mengubah status siswa menjadi Mutasi  
**Precondition:** Siswa berstatus Aktif

### Happy Path

```
1. TU di halaman /admin/students/{id} (Detail Siswa)
   ↓
2. TU klik button "Update Status" (kuning, icon AlertCircle)
   ↓
3. Sistem tampilkan modal "Update Status Siswa":
   - Dropdown Status: [Aktif, Mutasi, DO, Lulus]
   - Date Picker: Tanggal
   - Textarea: Alasan (required)
   - Textarea: Keterangan (optional)
   - Input: Sekolah Tujuan (conditional, muncul jika status = Mutasi)
   ↓
4. TU pilih Status: Mutasi
   - Field "Sekolah Tujuan" muncul (required)
   ↓
5. TU isi form:
   - Tanggal: 20 Desember 2025
   - Alasan: Orang tua pindah tugas ke Surabaya
   - Sekolah Tujuan: SD Negeri 1 Surabaya
   ↓
6. TU klik "Simpan"
   ↓
7. Backend Process:
   - Validate input
   - Call StudentService::updateStudentStatus()
   - Insert student_status_history:
      • status_lama: aktif
      • status_baru: mutasi
      • tanggal: 2025-12-20
      • alasan: ...
      • sekolah_tujuan: SD Negeri 1 Surabaya
      • changed_by: TU user_id
   - Update student.status = mutasi
   - Insert activity log
   ↓
8. Success Response:
   - Modal close
   - Flash message: "Status siswa berhasil diupdate."
   - Tab "Riwayat Status" auto-refresh, tampil entry baru
   - Badge status berubah dari "Aktif" (hijau) ke "Mutasi" (kuning)
```

---

## Journey 4: Orang Tua View Profil Anak (US-STD-008)

**Actor:** Orang Tua  
**Goal:** Melihat profil anak di portal parent  
**Precondition:** Parent account sudah dibuat otomatis saat siswa didaftarkan

### Happy Path

```
1. Orang Tua buka browser → navigate ke https://sekolah.id/login
   ↓
2. Orang Tua isi form login:
   - Username: 081234567890 (no HP yang dijadikan primary contact)
   - Password: Ortu20250001 (diberitahu oleh TU via phone/WA)
   ↓
3. Klik "Masuk"
   ↓
4. Backend verify credentials:
   - Check User dengan username = 081234567890
   - Verify password hash
   - Check role = PARENT
   ↓
5. Success → Redirect ke /parent/children (Dashboard Parent)
   ↓
6. Sistem tampilkan card anak:
   - Foto siswa (rounded-full, 80x80)
   - Nama: Ahmad Zaki
   - NIS: 20250001
   - Kelas: Kelas 1
   - Status: Aktif (badge hijau)
   ↓
7. Orang Tua klik card anak
   - Haptic feedback: light
   ↓
8. Sistem redirect ke /parent/children/{id}
   ↓
9. Backend Authorization Check:
   - Get guardian record by user_id
   - Check if student is linked to guardian via pivot table
   - If not linked → 403 Forbidden
   ↓
10. Sistem tampilkan Detail Profil Anak (read-only):
    - Header: Foto, Nama, NIS, Kelas
    - Tab 1: Info Pribadi
      • Biodata lengkap (TTL, agama, alamat, dll)
    - Tab 2: Orang Tua
      • Data Ayah, Ibu (tanpa NIK untuk privacy)
    - Tab 3: Riwayat Kelas
      • History perpindahan kelas per tahun ajaran
    - Tab 4: Absensi (future integration)
    - Tab 5: Pembayaran (future integration)
    ↓
11. Orang Tua browse tabs, view informasi
    - Tidak ada button Edit/Delete (read-only)
```

### Alternate Path: Multiple Children

```
Step 5: Dashboard Parent tampilkan 2 cards:
- Card 1: Ahmad Zaki (Kelas 1)
- Card 2: Siti Zahra (Kelas 3)
↓
Orang Tua bisa klik salah satu card untuk view detail
↓
Sistem verify authorization untuk child yang diklik
```

---

## Journey 5: TU Bulk Naik Kelas (US-STD-004)

**Actor:** TU/Admin  
**Goal:** Memindahkan multiple siswa ke kelas yang lebih tinggi  
**Precondition:** Akhir tahun ajaran, siswa siap naik kelas

**Note:** UI page belum ada, ini adalah planned journey

### Planned Flow

```
1. TU di halaman /admin/students
   ↓
2. TU klik button "Naik Kelas" (biru, icon ArrowUpCircle)
   ↓
3. Sistem redirect ke /admin/students/promote (Wizard 3-step)
   ↓
4. Step 1: Pilih Tahun Ajaran & Kelas Asal
   - Dropdown: Tahun Ajaran Lama → 2024/2025
   - Dropdown: Tahun Ajaran Baru → 2025/2026
   - Dropdown: Kelas Asal → Kelas 1
   - Dropdown: Kelas Tujuan → Kelas 2
   - Button "Lanjut"
   ↓
5. TU klik "Lanjut"
   ↓
6. Step 2: Preview & Select Students
   - Sistem fetch siswa di Kelas 1 dengan status Aktif
   - Table dengan checkbox:
     ☑ Ahmad Zaki (NIS: 20250001)
     ☑ Budi Santoso (NIS: 20250002)
     ☐ Siti Aminah (NIS: 20250003) ← TU uncheck (tidak naik)
     ☑ ... (25 siswa lainnya)
   - Summary: "25 dari 28 siswa akan dipindahkan ke Kelas 2"
   - Button "Kembali" | Button "Proses Naik Kelas"
   ↓
7. TU review list, uncheck siswa yang tidak naik
   ↓
8. TU klik "Proses Naik Kelas"
   ↓
9. Sistem tampilkan modal konfirmasi:
   "Yakin memindahkan 25 siswa ke Kelas 2?"
   ↓
10. TU klik "Ya, Proses"
    ↓
11. Backend Process:
    - Call StudentService::bulkPromoteStudents()
    - Loop through selected student_ids:
      • Insert student_class_history record
      • Update student.kelas_id = kelas_tujuan
    - Insert activity log dengan promoted count
    - Commit transaction
    ↓
12. Success Response:
    - Redirect ke /admin/students?kelas_id=2
    - Flash message: "25 siswa berhasil dipindahkan ke kelas 2A."
    - List tampilkan siswa di kelas baru
```

---

## Journey 6: Guru View Profil Siswa di Kelas (US-STD-006)

**Actor:** Guru  
**Goal:** Melihat profil siswa di kelas yang diajar  
**Precondition:** Guru sudah assigned ke kelas tertentu

### Happy Path

```
1. Guru login → Dashboard Guru
   ↓
2. Guru klik menu "Data Siswa"
   ↓
3. Sistem tampilkan /admin/students dengan filter otomatis:
   - Filter by kelas_id = kelas yang diajar guru (e.g., Kelas 1)
   - Guru hanya bisa lihat siswa di kelas tersebut
   ↓
4. Guru search siswa by nama
   ↓
5. Guru klik nama siswa → View detail profil
   ↓
6. Sistem tampilkan detail siswa (read-only):
   - Guru bisa view biodata, orang tua, riwayat
   - Tidak ada button Edit/Delete
```

---

## Journey 7: TU Assign Single Student to Class (US-AD03-001)

**Actor:** TU/Admin  
**Goal:** Memindahkan satu siswa ke kelas baru dengan riwayat lengkap  
**Precondition:** User sudah login, siswa sudah terdaftar, kelas tujuan tersedia

### Happy Path

```
1. TU login → Navigate to /admin/students
   ↓
2. TU klik nama siswa → Detail page (/admin/students/{id})
   ↓
3. Sistem tampilkan StudentDetailTabs:
   - Tab Biodata active
   - Tab Riwayat
   - Tab Orang Tua
   - Button "Pindah Kelas" visible (emerald, icon ArrowRightLeft)
   ↓
4. TU klik button "Pindah Kelas"
   ↓
5. Sistem buka AssignClassModal:
   - Title: "Pindah Kelas Siswa"
   - Kelas Saat Ini: 1A (read-only, gray badge)
   - Dropdown Kelas Tujuan: [1B, 1C, 2A, 2B, ...] (all active classes)
   - Textarea Catatan: (optional, max 255 chars)
   - Button "Batal" (secondary)
   - Button "Simpan" (primary, emerald)
   ↓
6. TU pilih Kelas Tujuan: 2A
   ↓
7. TU isi Catatan: "Naik kelas berdasarkan nilai"
   ↓
8. TU klik "Simpan"
   ↓
9. Sistem proses:
   - Validate: kelas_id exists, not same as current
   - Begin transaction
   - Insert student_class_history:
     • student_id: 1
     • kelas_id: 12 (2A)
     • tahun_ajaran: 2024/2025
     • wali_kelas: "Ibu Siti" (auto from SchoolClass.waliKelas)
     • notes: "Naik kelas berdasarkan nilai"
   - Update students.kelas_id = 12
   - Create activity_log
   - Commit transaction
   ↓
10. Sistem response:
    - Haptic feedback (medium vibration)
    - Success toast: "Siswa berhasil dipindahkan ke kelas 2A"
    - Modal close
    - Page reload dengan data terbaru
    ↓
11. TU verify:
    - Kelas Saat Ini: 2A (updated)
    - Tab Riwayat → New entry:
      "2024/2025 - Kelas 2A - Ibu Siti - Naik kelas berdasarkan nilai"
```

### Alternative Flow 1: Same Class Selected

```
6. TU pilih Kelas Tujuan: 1A (same as current)
   ↓
7. TU klik "Simpan"
   ↓
8. Sistem skip history insert (BR-AD03-02)
   - Show info toast: "Siswa sudah berada di kelas tersebut"
   - Modal tetap open untuk re-select
```

### Alternative Flow 2: Validation Error

```
8. Sistem validate → kelas_id invalid
   ↓
9. Sistem response:
   - Error toast: "Kelas tujuan tidak ditemukan"
   - Form tetap open, highlight field dengan error
   - TU dapat retry
```

---

## Journey 8: TU Bulk Assign Students to Class (US-AD03-002)

**Actor:** TU/Admin  
**Goal:** Memindahkan multiple siswa sekaligus ke kelas yang sama  
**Precondition:** User sudah login, ada siswa yang perlu dipindahkan

### Happy Path

```
1. TU login → Navigate to /admin/students
   ↓
2. Sistem tampilkan StudentTable dengan checkbox column
   - Checkbox di header (Select All)
   - Checkbox per row siswa
   ↓
3. TU check 5 siswa:
   - Ahmad Zaki (1A)
   - Siti Aisyah (1A)
   - Budi Santoso (1B)
   - Dewi Lestari (1B)
   - Eko Prasetyo (1C)
   ↓
4. Sistem tampilkan selection bar (sticky header):
   - "5 Siswa Dipilih"
   - Button "Batal Memilih" (X icon)
   - Button "Pindah Kelas" (emerald, ArrowRightLeft icon)
   ↓
5. TU klik "Pindah Kelas"
   ↓
6. Sistem buka AssignClassModal:
   - Title: "Pindah Kelas (Bulk)"
   - Count: "5 siswa dipilih"
   - Dropdown Kelas Tujuan: [all active classes]
   - Textarea Catatan: (optional)
   ↓
7. TU pilih Kelas Tujuan: 2A
   ↓
8. TU isi Catatan: "Reorganisasi kelas berdasarkan kapasitas"
   ↓
9. TU klik "Pindahkan"
   ↓
10. Sistem proses (loop through 5 students):
    - Begin transaction
    - For each student:
      • Skip if kelas_id sama dengan kelas tujuan
      • Insert student_class_history
      • Update students.kelas_id
    - Create activity_log dengan student_count: 5
    - Commit transaction
    ↓
11. Sistem response:
    - Haptic feedback (heavy vibration)
    - Success toast: "5 siswa berhasil dipindahkan ke kelas 2A"
    - Modal close
    - Clear selection
    - Page reload
    ↓
12. TU verify di table:
    - All 5 siswa now show "2A" di kolom Kelas
```

---

## Journey 9: TU Bulk Promote Students via Wizard (US-AD04-001)

**Actor:** TU/Admin  
**Goal:** Menaikkan siswa ke kelas berikutnya secara massal (naik kelas tahunan)  
**Precondition:** User sudah login, ada siswa yang siap naik kelas, kelas tujuan tersedia

### Happy Path - Full Wizard Flow

```
┌─────────────────────────────────────────────────────────────┐
│ STEP 1: PILIH TAHUN AJARAN                                  │
└─────────────────────────────────────────────────────────────┘

1. TU navigate to /admin/students
   ↓
2. TU klik button "Naik Kelas" (sky blue, GraduationCap icon)
   - Button position: Sebelah "Export" dan "Tambah Siswa"
   ↓
3. Sistem redirect ke /admin/students/promote
   ↓
4. Sistem render Promote page:
   - Header: "Naik Kelas Siswa"
   - Info card (sky blue) dengan panduan:
     • "Pilih tahun ajaran asal dan tujuan"
     • "Pilih kelas asal dan tujuan"
     • "Preview dan pilih siswa"
   - PromoteWizard component loaded
   ↓
5. Wizard Step 1 active:
   - Progress indicator: Step 1 (emerald), Step 2-3 (gray)
   - Form fields:
     • Tahun Ajaran Asal: Dropdown
       Options: [2023/2024, 2024/2025, 2025/2026]
     • Tahun Ajaran Tujuan: Dropdown
       Options: [2023/2024, 2024/2025, 2025/2026]
   - Button "Lanjut" (disabled)
   ↓
6. TU select Tahun Ajaran Asal: 2024/2025
   - Dropdown closes dengan animation
   - Field shows selected value
   ↓
7. TU select Tahun Ajaran Tujuan: 2025/2026
   - Form validation passes
   - Button "Lanjut" enabled (emerald, no opacity)
   ↓
8. TU klik "Lanjut"
   - Haptic feedback (light vibration)
   - Step 1 shows checkmark icon
   - Step 2 becomes active (emerald border)

┌─────────────────────────────────────────────────────────────┐
│ STEP 2: PILIH KELAS                                         │
└─────────────────────────────────────────────────────────────┘

9. Wizard Step 2 active:
   - Progress: Step 1 (✓), Step 2 (emerald), Step 3 (gray)
   - Form fields:
     • Kelas Asal: Dropdown
       Options: Filtered by tahun_ajaran = 2024/2025
       [1A, 1B, 1C, 2A, 2B, 2C, ..., 6A, 6B]
     • Kelas Tujuan: Dropdown (disabled initially)
   - Button "Kembali" (visible, left)
   - Button "Lanjut" (disabled, right)
   ↓
10. TU select Kelas Asal: 1A
    - System fetch tingkat = 1 from SchoolClass
    - Kelas Tujuan dropdown enabled
    - Auto-filter: tingkat = 2 AND tahun_ajaran = 2025/2026
    - Options: [2A, 2B, 2C]
    ↓
11. TU select Kelas Tujuan: 2A
    - Form validation passes
    - Button "Lanjut" enabled
    ↓
12. TU klik "Lanjut"
    - Haptic feedback
    - Step 2 shows checkmark
    - Step 3 becomes active
    - Loading spinner visible

┌─────────────────────────────────────────────────────────────┐
│ STEP 3: PREVIEW & KONFIRMASI                                │
└─────────────────────────────────────────────────────────────┘

13. System fetch students:
    - API call: GET /admin/students?kelas_id=5&per_page=1000
    - Response: 28 students from kelas 1A
    ↓
14. Wizard Step 3 active:
    - Progress: All steps emerald
    - Header info:
      • "28 dari 28 siswa"
      • "1A → 2A"
    - "Pilih Semua Siswa" checkbox (checked by default)
    - Student list (scrollable, max-height 400px):
      Row 1: [✓] Ahmad Zaki - NIS: 2024001 - L
      Row 2: [✓] Siti Aisyah - NIS: 2024002 - P
      Row 3: [✓] Budi Santoso - NIS: 2024003 - L
      ... (25 more)
    - Button "Kembali" (left)
    - Button "Proses Naik Kelas" (emerald, Check icon)
    ↓
15. TU review list → Notice 3 siswa tidak naik kelas:
    - TU uncheck Row 5: Dewi Lestari (tinggal kelas)
    - TU uncheck Row 12: Eko Prasetyo (pindah sekolah)
    - TU uncheck Row 20: Fitri Handayani (sakit berkepanjangan)
    ↓
16. Counter update: "25 dari 28 siswa"
    - Visual feedback: Unchecked rows bg-gray
    - Button tetap enabled (selection > 0)
    ↓
17. TU klik "Proses Naik Kelas"
    ↓
18. System show confirmation modal:
    - Type: warning
    - Icon: question mark
    - Title: "Konfirmasi Naik Kelas"
    - Message: "Yakin ingin menaikkan **25 siswa** dari kelas **1A** ke kelas **2A**?"
    - Button "Batal" (secondary)
    - Button "Ya, Proses" (primary, emerald)
    ↓
19. TU klik "Ya, Proses"
    ↓
20. System process (backend):
    - POST /admin/students/promote
    - Payload:
      {
        "student_ids": [1,2,3,4,6,7,8,9,10,11,13,...,28],
        "kelas_id_baru": 12,
        "tahun_ajaran_baru": "2025/2026"
      }
    - Service: bulkPromoteStudents()
      • Begin transaction
      • Loop 25 students:
        - Insert student_class_history (kelas_id: 12, tahun: 2025/2026, wali: "Ibu Siti")
        - Update students.kelas_id = 12
      • Create activity_log (student_count: 25)
      • Commit transaction
    ↓
21. System response:
    - Haptic feedback (heavy vibration)
    - Success modal: "Berhasil menaikkan 25 siswa ke kelas 2A!"
    - Wizard reset to Step 1 (atau redirect ke /admin/students)
    ↓
22. TU verify di /admin/students:
    - Filter Kelas: 2A
    - 25 siswa baru muncul di kelas 2A
    - 3 siswa tetap di kelas 1A (yang di-uncheck)
```

### Alternative Flow 1: No Target Class Available

```
10. TU select Kelas Asal: 6A (tingkat 6)
    ↓
11. System auto-filter: tingkat = 7 (tidak ada)
    - Kelas Tujuan dropdown empty
    - Warning message: "Tidak ada kelas tujuan yang tersedia untuk tingkat berikutnya"
    - Button "Lanjut" disabled
    ↓
12. TU harus klik "Kembali" atau pilih kelas lain
```

### Alternative Flow 2: Empty Class

```
13. System fetch students dari kelas 5B
    - Response: 0 students (empty array)
    ↓
14. Step 3 shows empty state:
    - Icon: Users (gray)
    - Message: "Tidak ada siswa di kelas ini"
    - Button "Proses Naik Kelas" disabled
    ↓
15. TU must go back and select different class
```

### Alternative Flow 3: All Students Unchecked

```
15. TU uncheck "Pilih Semua Siswa"
    - All 28 checkboxes unchecked
    - Counter: "0 dari 28 siswa"
    - Button "Proses Naik Kelas" disabled (opacity 50%)
    ↓
16. TU cannot proceed until at least 1 student checked
```

### Alternative Flow 4: Submit Error

```
20. System process → Server error (500)
    ↓
21. System response:
    - Error modal: "Gagal memproses naik kelas"
    - Transaction rollback (no partial data)
    - Form state preserved (selections intact)
    - TU can retry with same selections
```

### Mobile UX Optimizations

```
- Progress steps: Stack vertically on mobile (< 768px)
- Form inputs: Full width dengan proper touch targets (44px min)
- Student list: Optimized scrolling dengan virtual scroll (if > 100 items)
- Buttons: Touch-friendly spacing (16px gap minimum)
- "Naik Kelas" button di Index page: Icon only dengan tooltip
```

---

## Journey 10: Principal Monitor Bulk Promote Activity

**Actor:** Kepala Sekolah  
**Goal:** Monitoring aktivitas naik kelas massal untuk audit  
**Precondition:** TU sudah melakukan bulk promote

### Happy Path

```
1. Principal login → Dashboard
   ↓
2. Principal navigate to Audit Logs
   ↓
3. Principal filter by action: "bulk_promote_students"
   ↓
4. Sistem tampilkan activity logs:
   Row 1: 
   - User: TU Siti (Admin)
   - Action: Bulk Promote Students
   - Time: 24 Des 2025, 10:30 WIB
   - Details: "25 siswa dipindahkan ke kelas 2A"
   - Metadata: {student_count: 25, kelas_id_baru: 12, tahun_ajaran: "2025/2026"}
   - Status: Success
   ↓
5. Principal klik "View Details" → Modal dengan:
   - List student IDs yang dipromote
   - Kelas asal dan tujuan
   - Timestamp detail
```

---

## Related Documentation

- **Feature Documentation:** 
  - [STD Student Management](../features/admin/STD-student-management.md)
  - [AD03 Assign Student to Class](../features/admin/AD03-assign-student-to-class.md)
  - [AD04 Bulk Promote Students](../features/admin/AD04-bulk-promote-students.md)
- **API Documentation:** [Students API](../api/students.md)
- **Test Plans:**
  - [STD Test Plan](../testing/STD-test-plan.md)
  - [AD03 Test Plan](../testing/AD03-assign-class-test-plan.md)
  - [AD04 Test Plan](../testing/AD04-bulk-promote-test-plan.md)
