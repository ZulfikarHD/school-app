# Modul 7: Teacher Management (Manajemen Guru)

## 📋 Overview

Modul ini menangani data kepegawaian guru, jadwal mengajar, perhitungan honor/gaji, dan evaluasi kinerja guru.

**Module Code:** `TCH`  
**Priority:** P2 (Medium)  
**Dependencies:** Authentication, Attendance System

---

## 🎯 Tujuan

1. Centralisasi data kepegawaian guru
2. Manajemen jadwal mengajar
3. Otomasi perhitungan honor berdasarkan jam mengajar
4. Tracking evaluasi kinerja guru
5. Rekap presensi untuk payroll

---

## 📖 User Stories

### US-TCH-001: Kelola Data Guru (TU)
```
Sebagai Staf TU,
Saya ingin mengelola data kepegawaian guru,
Sehingga data guru terorganisir dan lengkap
```

**Acceptance Criteria:**
- ✅ CRUD data guru (biodata, kontak, status, mata pelajaran)
- ✅ Upload foto guru
- ✅ Set status: Tetap/Honorer
- ✅ Assign mata pelajaran yang diampu

### US-TCH-002: Kelola Jadwal Mengajar (TU)
```
Sebagai Staf TU,
Saya ingin mengatur jadwal mengajar guru per kelas,
Sehingga guru tahu jadwal dan dapat input absensi/nilai sesuai kelas yang diajar
```

**Acceptance Criteria:**
- ✅ Input jadwal: Hari, Jam, Kelas, Mata Pelajaran, Guru
- ✅ View jadwal per guru atau per kelas
- ✅ Validasi: tidak ada konflik jadwal (1 guru tidak bisa ngajar 2 kelas di jam yang sama)

### US-TCH-003: Lihat Jadwal Mengajar (Guru)
```
Sebagai Guru,
Saya ingin melihat jadwal mengajar saya,
Sehingga saya tahu jam dan kelas yang harus saya ajar
```

**Acceptance Criteria:**
- ✅ Dashboard: jadwal hari ini & minggu ini
- ✅ Kalender view: jadwal per bulan
- ✅ Notifikasi reminder sebelum jam mengajar (optional)

### US-TCH-004: Hitung Honor Guru (TU)
```
Sebagai Staf TU,
Saya ingin menghitung honor guru berdasarkan jam mengajar dan kehadiran,
Sehingga payroll akurat
```

**Acceptance Criteria:**
- ✅ Input honor per jam untuk guru honorer
- ✅ Rekap jam mengajar per bulan (dari jadwal)
- ✅ Rekap kehadiran (dari presensi guru)
- ✅ Calculate: Total Honor = (Jam Mengajar × Honor per Jam) + Gaji Tetap (jika ada) - Potongan (jika alpha)
- ✅ Generate slip gaji (PDF)
- ✅ Export to Excel untuk payroll

### US-TCH-005: Evaluasi Guru (Kepala Sekolah)
```
Sebagai Kepala Sekolah,
Saya ingin mengevaluasi kinerja guru,
Sehingga ada record evaluasi untuk pengembangan guru
```

**Acceptance Criteria:**
- ✅ Input evaluasi per guru per semester
- ✅ Kategori evaluasi: Pedagogik, Kepribadian, Sosial, Profesional (standar kompetensi guru)
- ✅ Score (1-5) dan komentar naratif
- ✅ History evaluasi tersimpan
- ✅ Guru dapat view evaluasi sendiri

---

## ⚙️ Functional Requirements

### FR-TCH-001: Teacher Profile Management
**Priority:** Must Have  
**Description:** TU dapat mengelola data guru.

**Details:**
**Data Fields:**
- Nama Lengkap, Gelar (S.Pd, M.Pd, dll)
- NIP (Nomor Induk Pegawai) atau NIK
- Jenis Kelamin, Tempat/Tanggal Lahir
- Alamat, Nomor HP, Email
- Foto Guru (upload, max 2MB)
- Status: Tetap atau Honorer
- Mata Pelajaran yang Diampu (multi-select)
- Tanggal Bergabung
- Pendidikan Terakhir
- Gaji Tetap (untuk guru tetap, Rp amount)
- Honor per Jam (untuk guru honorer, Rp amount)

**CRUD:** Create, Read, Update, Soft Delete

### FR-TCH-002: Teaching Schedule Management
**Priority:** Must Have  
**Description:** TU dapat mengatur jadwal mengajar guru.

**Details:**
**Input:**
- Hari (dropdown: Senin-Jumat, atau Sabtu jika ada)
- Jam Pelajaran (dropdown: Jam 1, 2, ..., 8 atau time range: 07:00-08:30, 08:30-10:00, dst)
- Kelas (dropdown)
- Mata Pelajaran (dropdown)
- Guru (dropdown)

**View Options:**
1. **Per Guru:** Matrix/table (Hari vs Jam) dengan kelas & mapel di cell
2. **Per Kelas:** Matrix (Hari vs Jam) dengan guru & mapel di cell

**Validation:**
- Tidak boleh konflik: 1 guru tidak bisa di 2 kelas di jam yang sama
- 1 kelas tidak bisa ada 2 guru di jam yang sama

**Business Logic:**
- Jadwal per semester/tahun ajaran
- Copy jadwal dari semester sebelumnya (untuk efisiensi)

### FR-TCH-003: Teaching Hours Calculation
**Priority:** Must Have  
**Description:** Sistem menghitung total jam mengajar guru per bulan.

**Formula:**
```
Total Jam Mengajar = SUM(jadwal per minggu) × Jumlah Minggu Efektif

Contoh:
Guru A mengajar:
- Senin jam 1-2 (2 jam) di Kelas 1A
- Selasa jam 3-4 (2 jam) di Kelas 1B
- Kamis jam 1 (1 jam) di Kelas 2A
Total per minggu = 5 jam
Bulan Desember: 4 minggu efektif (exclude libur)
Total Jam Mengajar Desember = 5 × 4 = 20 jam
```

**Integration:**
- Jam mengajar dari Teaching Schedule
- Adjust jika ada hari libur (exclude dari perhitungan)
- Adjust jika guru tidak hadir (dari Attendance/Presensi Guru)

### FR-TCH-004: Salary/Honor Calculation
**Priority:** Must Have  
**Description:** TU dapat menghitung gaji/honor guru per bulan.

**Formula:**

**Guru Tetap:**
```
Gaji = Gaji Tetap + Tunjangan - Potongan

Potongan:
- Alpha: Rp X per hari (configurable)
- Terlambat: Rp Y per kejadian (configurable)
```

**Guru Honorer:**
```
Honor = (Jam Mengajar Efektif × Honor per Jam) - Potongan

Jam Mengajar Efektif = Jam dari Jadwal - Jam Tidak Hadir

Contoh:
Honor per Jam: Rp 50,000
Jam Mengajar Desember: 20 jam
Jam Tidak Hadir (alpha 1 hari = 5 jam): 5 jam
Jam Efektif: 20 - 5 = 15 jam
Total Honor: 15 × 50,000 = Rp 750,000
```

**Output:**
- Slip Gaji (PDF) per guru
- Table rekap: Nama Guru, Gaji Tetap, Honor, Tunjangan, Potongan, Total Gaji
- Export to Excel

### FR-TCH-005: Teacher Evaluation
**Priority:** Should Have  
**Description:** Kepala sekolah dapat mengevaluasi guru.

**Evaluation Categories (4 Kompetensi Guru):**
1. **Pedagogik:** kemampuan mengajar, metode, pengelolaan kelas
2. **Kepribadian:** sikap, disiplin, teladan
3. **Sosial:** komunikasi, kerjasama
4. **Profesional:** penguasaan materi, pengembangan diri

**Input:**
- Per kategori: Score (1-5), Deskripsi/Komentar (textarea)
- Overall Score: rata-rata 4 kategori
- Rekomendasi: Lanjutkan/Perlu Bimbingan/Perlu Pelatihan

**Access:**
- Kepala Sekolah: input evaluasi
- Guru: view evaluasi sendiri (read-only)
- TU: view all evaluations (untuk HR record)

---

## 📏 Business Rules

### BR-TCH-001: Teaching Assignment
- Guru hanya bisa mengajar mata pelajaran yang tercantum di profil
- Guru kelas (wali kelas) mengajar semua/banyak mapel untuk kelasnya
- Guru mata pelajaran tertentu (e.g., Olahraga, Agama) mengajar lintas kelas

### BR-TCH-002: Honor Calculation
- Honor dihitung per bulan berdasarkan jam mengajar efektif
- Jika tidak hadir (alpha) tanpa izin, jam tersebut tidak dibayar
- Jika izin/sakit (dengan approval), jam tetap dibayar (configurable)

### BR-TCH-003: Evaluation Period
- Evaluasi dilakukan minimal 1x per semester
- Evaluasi tersimpan sebagai archive (tidak bisa dihapus)

---

## ✅ Validation Rules

**Nama:** Required, min 3 karakter  
**NIP/NIK:** Required, unik  
**Status:** Required (Tetap/Honorer)  
**Honor per Jam:** Required jika status Honorer, min Rp 10,000  

---

## 🎨 UI/UX Requirements

**Teacher List:** Table dengan foto, nama, status, mata pelajaran, actions  
**Teaching Schedule:** Matrix view (grid calendar style), color-coded per guru atau per kelas  
**Salary Report:** Table dengan summary, export Excel & PDF button  
**Evaluation Form:** Multi-section form dengan rating (star or slider 1-5)

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ CRUD teacher profile
- ✅ Teaching schedule management (input, view per guru/kelas)
- ✅ Teaching hours calculation
- ✅ Salary/honor calculation dengan slip gaji PDF
- ✅ Export salary report to Excel

### Should Have (MVP):
- ✅ Teacher evaluation (Kepala Sekolah input, guru view)
- ✅ Copy schedule dari semester sebelumnya
- ✅ Schedule conflict validation

### Could Have:
- ⬜ Leave/cuti management untuk guru
- ⬜ Sertifikasi & pelatihan tracking
- ⬜ Replacement teacher (guru pengganti jika ada yang izin)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft

