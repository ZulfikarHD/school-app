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

## Related Documentation

- **Feature Documentation:** [STD Student Management](../features/admin/STD-student-management.md)
- **API Documentation:** [Students API](../api/students.md)
- **Test Plan:** [STD Test Plan](../testing/STD-test-plan.md)
