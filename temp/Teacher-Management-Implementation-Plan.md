# Teacher Management - Implementation Plan

**Module Code:** TCH  
**Priority:** P2 (Medium)  
**Total Estimation:** ~4-5 Weeks  
**Dependencies:** Authentication, Attendance System  
**Last Updated:** 29 Januari 2026

---

## 📋 Executive Summary

Modul Teacher Management mencakup fitur-fitur untuk mengelola data kepegawaian guru, jadwal mengajar, perhitungan honor/gaji, dan evaluasi kinerja guru. Implementasi dibagi menjadi **5 Epic/Sprint** yang dijalankan secara weekly.

### Story Points Overview

| Epic | Nama | Story Points | Priority |
|------|------|--------------|----------|
| Epic 1 | Foundation & Teacher CRUD | 8 points | Must Have |
| Epic 2 | Teaching Schedule Management | 8 points | Should Have |
| Epic 3 | Honor & Salary Calculation | 10 points | Must Have |
| Epic 4 | Teacher Evaluation | 6 points | Should Have |
| Epic 5 | Dashboard & Reports | 6 points | Should Have |

**Total:** 38 Story Points

---

## 🗓️ Epic 1: Foundation & Teacher CRUD (Week 1)

### Overview
Setup database schema, models, dan CRUD dasar untuk data guru.

### User Stories
- **US-TCH-001:** Tambah & Edit Data Guru (M - 3 points)
- **US-TCH-002:** Lihat Profil Guru (M - 3 points)
- **US-TCH-008:** Nonaktifkan Guru (S - 2 points)

### Tasks

#### 1.1 Database & Models (Day 1-2)

**Migrations:**
```
- create_teachers_table
  - id, user_id (FK), nip, nik, nama_lengkap, gelar
  - jenis_kelamin, tempat_lahir, tanggal_lahir
  - alamat, no_hp, email, foto
  - status (enum: tetap, honorer)
  - pendidikan_terakhir
  - tanggal_bergabung
  - gaji_tetap (nullable, untuk guru tetap)
  - honor_per_jam (nullable, untuk guru honorer)
  - is_active (boolean, default true)
  - timestamps, soft_deletes

- create_subjects_table (if not exists)
  - id, kode, nama, deskripsi
  - timestamps

- create_teacher_subject_table (pivot)
  - teacher_id, subject_id
  - timestamps
```

**Models:**
```php
- Teacher (with relationships: user, subjects, schedules, evaluations)
- Subject (with relationships: teachers)
```

**Factories & Seeders:**
```php
- TeacherFactory
- SubjectSeeder (mata pelajaran standar SD)
- TeacherSeeder (sample data)
```

#### 1.2 Backend - Controller & Routes (Day 2-3)

**Controller:** `Admin\TeacherController`
```php
- index()      // List dengan filter & pagination
- create()     // Form tambah guru
- store()      // Simpan guru baru + auto create user account
- show($id)    // Detail profil guru
- edit($id)    // Form edit guru
- update($id)  // Update data guru
- destroy($id) // Soft delete (nonaktifkan)
- restore($id) // Reaktivasi guru
```

**Form Request:**
```php
- StoreTeacherRequest
- UpdateTeacherRequest
```

**Routes:**
```php
Route::resource('admin/teachers', TeacherController::class);
Route::post('admin/teachers/{teacher}/restore', 'restore');
```

#### 1.3 Frontend - Vue Pages (Day 3-5)

**Pages:**
```
resources/js/pages/Admin/Teachers/
├── Index.vue       // List guru dengan filter, search, pagination
├── Create.vue      // Form tambah guru
├── Edit.vue        // Form edit guru
└── Show.vue        // Detail profil guru (tabs)
```

**Components:**
```
resources/js/components/Teachers/
├── TeacherForm.vue        // Reusable form component
├── TeacherCard.vue        // Card untuk list view
├── TeacherProfileTab.vue  // Tab info pribadi
└── SubjectBadge.vue       // Badge mata pelajaran
```

**Features:**
- Upload foto guru (max 2MB, jpeg/png)
- Multi-select mata pelajaran
- Filter: status (tetap/honorer), aktif/nonaktif
- Search by nama/NIP
- Pagination
- Status toggle (aktif/nonaktif)

### Acceptance Criteria
- [ ] TU dapat menambah guru baru dengan semua field wajib
- [ ] Sistem auto-create akun login untuk guru (username: NIP)
- [ ] TU dapat edit data guru existing
- [ ] TU dapat nonaktifkan guru (soft delete)
- [ ] TU dapat filter guru by status dan aktif/nonaktif
- [ ] Foto guru dapat diupload dan ditampilkan
- [ ] Validasi NIP/NIK unik

### Technical Notes
- Gunakan pattern yang sama dengan `Admin\StudentController`
- Upload foto menggunakan storage disk `public`
- Generate password random saat create akun guru
- Kirim notifikasi email ke guru dengan credentials (optional)

---

## 🗓️ Epic 2: Teaching Schedule Management (Week 2)

### Overview
Implementasi fitur jadwal mengajar guru dengan calendar view dan conflict detection.

### User Stories
- **US-TCH-003:** Input Jadwal Mengajar (M - 3 points)
- **FR-TCH-002:** Schedule per guru/kelas view (M - 3 points)
- Copy schedule dari semester sebelumnya (S - 2 points)

### Tasks

#### 2.1 Database & Models (Day 1)

**Migrations:**
```
- create_teaching_schedules_table
  - id, teacher_id (FK), class_id (FK), subject_id (FK)
  - academic_year_id (FK)
  - hari (enum: senin-jumat, optional: sabtu)
  - jam_mulai (time), jam_selesai (time)
  - jam_pelajaran (integer: 1-8)
  - timestamps, soft_deletes

- create_academic_years_table (if not exists)
  - id, tahun (e.g., "2025/2026")
  - semester (enum: ganjil, genap)
  - tanggal_mulai, tanggal_selesai
  - is_active (boolean)
  - timestamps
```

**Models:**
```php
- TeachingSchedule (with relationships)
- AcademicYear
```

#### 2.2 Backend - Schedule Management (Day 2-3)

**Controller:** `Admin\TeachingScheduleController`
```php
- index()           // List semua jadwal
- byTeacher($id)    // Jadwal per guru (matrix view)
- byClass($id)      // Jadwal per kelas (matrix view)
- create()          // Form tambah jadwal
- store()           // Simpan dengan conflict validation
- edit($id)         // Form edit
- update($id)       // Update dengan conflict validation
- destroy($id)      // Hapus jadwal
- copyFromPrevious() // Copy dari semester sebelumnya
```

**Service:** `TeachingScheduleService`
```php
- checkConflict($teacherId, $hari, $jamMulai, $jamSelesai)
- checkClassConflict($classId, $hari, $jamMulai, $jamSelesai)
- copySchedule($fromAcademicYearId, $toAcademicYearId)
- getWeeklyHours($teacherId, $academicYearId)
```

**Validation Rules:**
```php
- Guru tidak bisa di 2 kelas pada jam yang sama
- Kelas tidak bisa ada 2 guru pada jam yang sama
- Guru hanya bisa mengajar mapel yang dia ampu
```

#### 2.3 Frontend - Schedule Views (Day 3-5)

**Pages:**
```
resources/js/pages/Admin/Teachers/
├── Schedules/
│   ├── Index.vue       // List semua jadwal
│   ├── ByTeacher.vue   // Matrix view per guru
│   ├── ByClass.vue     // Matrix view per kelas
│   └── Create.vue      // Form tambah jadwal
```

**Components:**
```
resources/js/components/Schedule/
├── ScheduleMatrix.vue     // Matrix grid (hari x jam)
├── ScheduleCell.vue       // Cell dengan info guru/kelas/mapel
├── ScheduleForm.vue       // Form input jadwal
├── ConflictWarning.vue    // Warning modal untuk konflik
└── ScheduleExport.vue     // Export to PDF/print
```

**Features:**
- Matrix view dengan color coding per guru/mapel
- Drag & drop untuk reschedule (nice to have)
- Conflict detection real-time
- Filter by academic year, semester
- Export jadwal ke PDF
- Copy schedule dari semester sebelumnya

### Acceptance Criteria
- [ ] TU dapat input jadwal: Hari, Jam, Kelas, Mapel, Guru
- [ ] Sistem menampilkan jadwal dalam matrix view (hari vs jam)
- [ ] View per guru: menampilkan kelas & mapel di setiap slot
- [ ] View per kelas: menampilkan guru & mapel di setiap slot
- [ ] Sistem mendeteksi dan mencegah konflik jadwal
- [ ] TU dapat copy jadwal dari semester sebelumnya
- [ ] Jadwal dapat di-export ke PDF

### Technical Notes
- Matrix view menggunakan CSS Grid atau Table
- Color coding menggunakan hash dari teacher_id untuk konsistensi
- Validasi conflict di backend (FormRequest) dan frontend (real-time)

---

## 🗓️ Epic 3: Honor & Salary Calculation (Week 3)

### Overview
Implementasi perhitungan jam mengajar, honor/gaji guru, dan slip gaji.

### User Stories
- **US-TCH-004:** Rekap Jam Mengajar (M - 3 points)
- **US-TCH-005:** Set Honor/Tarif (S - 2 points)
- **FR-TCH-004:** Salary Calculation (M - 3 points)
- Slip Gaji PDF (S - 2 points)

### Tasks

#### 3.1 Database & Configuration (Day 1)

**Migrations:**
```
- create_honor_settings_table
  - id, kategori (enum: tetap, honorer, ekstra)
  - tarif_per_jam (decimal)
  - effective_from (date)
  - created_by (FK user_id)
  - timestamps

- create_teacher_custom_rates_table
  - id, teacher_id (FK)
  - tarif_per_jam (decimal)
  - effective_from (date)
  - catatan (text, nullable)
  - timestamps

- create_salary_calculations_table
  - id, teacher_id (FK), periode (year-month)
  - total_jam_regular, total_jam_ekstra
  - gaji_tetap, honor_jam, tunjangan
  - potongan_alpha, potongan_telat, potongan_lain
  - total_gaji
  - status (enum: draft, approved, paid)
  - approved_by, approved_at
  - timestamps

- create_salary_calculation_details_table
  - id, salary_calculation_id (FK)
  - tanggal, jenis (regular/ekstra/potongan)
  - jam, tarif, subtotal
  - catatan
  - timestamps
```

#### 3.2 Backend - Calculation Engine (Day 2-3)

**Service:** `SalaryCalculationService`
```php
- calculateMonthlyHours($teacherId, $yearMonth)
  // Hitung jam dari schedule - jam tidak hadir
  
- calculateSalary($teacherId, $yearMonth)
  // Guru Tetap: Gaji Tetap + Tunjangan - Potongan
  // Guru Honorer: (Jam Efektif × Tarif) - Potongan
  
- generateSlipGaji($calculationId)
  // Generate PDF slip gaji
  
- exportPayroll($yearMonth)
  // Export semua guru ke Excel
```

**Controller:** `Admin\SalaryController`
```php
- index()              // List rekap per bulan
- calculate($month)    // Trigger perhitungan
- show($id)            // Detail perhitungan guru
- approve($id)         // Approve perhitungan
- generateSlip($id)    // Generate PDF
- exportPayroll($month) // Export Excel
```

**Controller:** `Admin\HonorSettingController`
```php
- index()    // List tarif
- store()    // Tambah/update tarif
- history()  // History perubahan tarif
```

#### 3.3 Frontend - Salary Management (Day 3-5)

**Pages:**
```
resources/js/pages/Admin/Teachers/
├── Salary/
│   ├── Index.vue          // Dashboard rekap per bulan
│   ├── Calculate.vue      // Trigger & review perhitungan
│   ├── Detail.vue         // Detail per guru
│   └── Settings.vue       // Pengaturan tarif
```

**Components:**
```
resources/js/components/Salary/
├── SalaryTable.vue        // Table rekap semua guru
├── SalaryCard.vue         // Summary card
├── CalculationBreakdown.vue // Detail breakdown
├── TarifForm.vue          // Form setting tarif
└── SlipGajiPreview.vue    // Preview slip gaji
```

**Features:**
- Dashboard rekap per bulan
- Breakdown perhitungan per guru
- Setting tarif (default + custom per guru)
- Generate slip gaji PDF
- Export payroll ke Excel
- Approval workflow

### Acceptance Criteria
- [ ] Admin dapat set tarif honor per kategori (tetap/honorer/ekstra)
- [ ] Admin dapat set tarif custom untuk guru tertentu
- [ ] Sistem menghitung jam mengajar efektif per bulan
- [ ] Perhitungan guru tetap: Gaji + Tunjangan - Potongan
- [ ] Perhitungan guru honorer: Jam × Tarif - Potongan
- [ ] Potongan alpha/telat dari data presensi
- [ ] Generate slip gaji PDF per guru
- [ ] Export rekap payroll ke Excel

### Technical Notes
- Integrasi dengan `teacher_attendances` table untuk data kehadiran
- PDF generation menggunakan Laravel DomPDF atau Snappy
- Excel export menggunakan Laravel Excel (Maatwebsite)
- Perhitungan minggu efektif exclude hari libur

### Formula Reference
```
Guru Tetap:
Total = Gaji_Tetap + Tunjangan - (Alpha × Potongan_Per_Hari) - (Telat × Potongan_Per_Telat)

Guru Honorer:
Jam_Efektif = Jam_Jadwal - Jam_Tidak_Hadir
Total = (Jam_Efektif × Tarif_Per_Jam) - Potongan
```

---

## 🗓️ Epic 4: Teacher Evaluation (Week 4)

### Overview
Implementasi sistem evaluasi guru oleh Kepala Sekolah berdasarkan 4 kompetensi standar.

### User Stories
- **US-TCH-006:** Evaluasi Guru oleh Kepala Sekolah (M - 3 points)
- View evaluasi oleh guru (S - 2 points)
- History evaluasi (S - 1 point)

### Tasks

#### 4.1 Database & Models (Day 1)

**Migrations:**
```
- create_teacher_evaluations_table
  - id, teacher_id (FK), evaluator_id (FK user_id)
  - academic_year_id (FK), semester
  - periode (e.g., "Semester 1 2025/2026")
  
  // 4 Kompetensi
  - score_pedagogik (tinyint 1-5)
  - catatan_pedagogik (text)
  - score_kepribadian (tinyint 1-5)
  - catatan_kepribadian (text)
  - score_sosial (tinyint 1-5)
  - catatan_sosial (text)
  - score_profesional (tinyint 1-5)
  - catatan_profesional (text)
  
  - score_overall (decimal, computed average)
  - rekomendasi (enum: lanjutkan, perlu_bimbingan, perlu_pelatihan)
  - catatan_umum (text)
  - is_published (boolean, default false)
  - timestamps
```

**Model:**
```php
- TeacherEvaluation
  - Relationships: teacher, evaluator, academicYear
  - Computed: overall_score
  - Scope: byPeriod, byTeacher
```

#### 4.2 Backend - Evaluation System (Day 2-3)

**Controller:** `Admin\TeacherEvaluationController`
```php
- index()         // List evaluasi (filter by teacher, period)
- create($teacherId) // Form evaluasi untuk guru
- store()         // Simpan evaluasi
- show($id)       // Detail evaluasi
- edit($id)       // Edit evaluasi (sebelum publish)
- update($id)     // Update evaluasi
- publish($id)    // Publish agar guru bisa lihat
```

**Controller (Guru):** `Teacher\EvaluationController`
```php
- index()  // List evaluasi sendiri
- show($id) // Detail evaluasi (read-only)
```

**Policy:**
```php
- TeacherEvaluationPolicy
  - create: only Kepala Sekolah
  - view: Kepala Sekolah, Admin, atau guru yang bersangkutan
  - update: Kepala Sekolah (sebelum publish)
```

#### 4.3 Frontend - Evaluation Interface (Day 3-5)

**Pages:**
```
resources/js/pages/Admin/Teachers/
├── Evaluations/
│   ├── Index.vue      // List semua evaluasi
│   ├── Create.vue     // Form input evaluasi
│   └── Show.vue       // Detail evaluasi

resources/js/pages/Teacher/
├── Evaluations/
│   ├── Index.vue      // List evaluasi sendiri
│   └── Show.vue       // Detail (read-only)
```

**Components:**
```
resources/js/components/Evaluation/
├── EvaluationForm.vue       // Form dengan 4 section kompetensi
├── RatingInput.vue          // Star rating atau slider 1-5
├── CompetencyCard.vue       // Card per kompetensi
├── EvaluationSummary.vue    // Summary overall score
└── RecommendationBadge.vue  // Badge rekomendasi
```

**Features:**
- Form multi-section (4 kompetensi)
- Star rating atau slider untuk score 1-5
- Auto-calculate overall score
- Dropdown rekomendasi
- Preview sebelum publish
- History evaluasi per guru
- Guru view (read-only, tanpa nama evaluator)

### Acceptance Criteria
- [ ] Kepala Sekolah dapat membuat evaluasi untuk guru
- [ ] Evaluasi mencakup 4 kompetensi dengan score 1-5
- [ ] Setiap kompetensi memiliki field catatan
- [ ] Sistem menghitung overall score (rata-rata)
- [ ] Kepala Sekolah dapat memilih rekomendasi
- [ ] Guru dapat melihat evaluasi sendiri (setelah publish)
- [ ] History evaluasi tersimpan per semester
- [ ] Evaluasi tidak bisa dihapus (archive only)

### Technical Notes
- 4 Kompetensi standar guru Indonesia:
  1. Pedagogik: kemampuan mengajar, metode, pengelolaan kelas
  2. Kepribadian: sikap, disiplin, teladan
  3. Sosial: komunikasi, kerjasama
  4. Profesional: penguasaan materi, pengembangan diri
- Evaluasi bersifat confidential sampai di-publish
- Nama evaluator tidak ditampilkan ke guru

---

## 🗓️ Epic 5: Dashboard & Reports (Week 5)

### Overview
Implementasi dashboard summary dan export reports untuk teacher management.

### User Stories
- **US-TCH-010:** Dashboard Teacher Management (M - 3 points)
- **US-TCH-009:** Export Data Guru (S - 2 points)
- Integration & Polish (S - 1 point)

### Tasks

#### 5.1 Dashboard Backend (Day 1-2)

**Controller:** `Admin\TeacherDashboardController`
```php
- index()
  // Return aggregated data:
  // - Total guru aktif, tetap, honorer
  // - Rata-rata evaluasi
  // - Guru dengan presensi buruk
  // - Summary honor bulan ini
  // - Upcoming schedules today
```

**Service:** `TeacherStatisticsService`
```php
- getTotalByStatus()
- getAverageEvaluation()
- getTeachersWithBadAttendance($threshold)
- getMonthlyHonorSummary()
- getUpcomingSchedules()
```

#### 5.2 Export Features (Day 2-3)

**Controller:** `Admin\TeacherExportController`
```php
- exportTeachers($filters)     // Export data guru ke Excel
- exportSchedules($filters)    // Export jadwal ke Excel/PDF
- exportPayroll($month)        // Export payroll ke Excel
- exportEvaluations($period)   // Export evaluasi ke Excel
```

**Export Classes (Laravel Excel):**
```php
- TeachersExport
- SchedulesExport
- PayrollExport
- EvaluationsExport
```

#### 5.3 Frontend - Dashboard (Day 3-5)

**Pages:**
```
resources/js/pages/Admin/Teachers/
├── Dashboard.vue      // Main dashboard
```

**Components:**
```
resources/js/components/Dashboard/
├── StatCard.vue           // Summary stat card
├── AttendanceAlert.vue    // Alert guru presensi buruk
├── HonorSummary.vue       // Summary honor bulan ini
├── TodaySchedule.vue      // Jadwal hari ini
└── QuickActions.vue       // Quick action buttons
```

**Dashboard Features:**
- Summary cards: total guru, tetap, honorer
- Alert card: guru dengan presensi < 80%
- Chart: distribusi guru per status
- Rata-rata evaluasi per kategori
- Quick links: Tambah Guru, Jadwal, Rekap Honor
- Today's schedule widget

### Acceptance Criteria
- [ ] Dashboard menampilkan total guru aktif/tetap/honorer
- [ ] Dashboard menampilkan guru dengan presensi buruk
- [ ] Dashboard menampilkan rata-rata evaluasi
- [ ] Quick links ke fitur utama
- [ ] Export data guru ke Excel dengan filter
- [ ] Export jadwal ke PDF
- [ ] Export payroll ke Excel

### Technical Notes
- Dashboard menggunakan deferred props untuk performa
- Charts menggunakan Chart.js atau ApexCharts
- Export menggunakan Laravel Excel package
- PDF menggunakan Laravel DomPDF

---

## 📁 File Structure Summary

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── TeacherController.php
│   │   │   ├── TeachingScheduleController.php
│   │   │   ├── SalaryController.php
│   │   │   ├── HonorSettingController.php
│   │   │   ├── TeacherEvaluationController.php
│   │   │   ├── TeacherDashboardController.php
│   │   │   └── TeacherExportController.php
│   │   └── Teacher/
│   │       └── EvaluationController.php
│   └── Requests/
│       ├── Teacher/
│       │   ├── StoreTeacherRequest.php
│       │   └── UpdateTeacherRequest.php
│       ├── Schedule/
│       │   └── StoreScheduleRequest.php
│       └── Evaluation/
│           └── StoreEvaluationRequest.php
├── Models/
│   ├── Teacher.php
│   ├── Subject.php
│   ├── TeachingSchedule.php
│   ├── AcademicYear.php
│   ├── HonorSetting.php
│   ├── TeacherCustomRate.php
│   ├── SalaryCalculation.php
│   └── TeacherEvaluation.php
├── Services/
│   ├── TeachingScheduleService.php
│   ├── SalaryCalculationService.php
│   └── TeacherStatisticsService.php
├── Exports/
│   ├── TeachersExport.php
│   ├── SchedulesExport.php
│   ├── PayrollExport.php
│   └── EvaluationsExport.php
└── Policies/
    ├── TeacherPolicy.php
    └── TeacherEvaluationPolicy.php

resources/js/
├── pages/
│   ├── Admin/
│   │   └── Teachers/
│   │       ├── Index.vue
│   │       ├── Create.vue
│   │       ├── Edit.vue
│   │       ├── Show.vue
│   │       ├── Dashboard.vue
│   │       ├── Schedules/
│   │       │   ├── Index.vue
│   │       │   ├── ByTeacher.vue
│   │       │   ├── ByClass.vue
│   │       │   └── Create.vue
│   │       ├── Salary/
│   │       │   ├── Index.vue
│   │       │   ├── Calculate.vue
│   │       │   ├── Detail.vue
│   │       │   └── Settings.vue
│   │       └── Evaluations/
│   │           ├── Index.vue
│   │           ├── Create.vue
│   │           └── Show.vue
│   └── Teacher/
│       └── Evaluations/
│           ├── Index.vue
│           └── Show.vue
└── components/
    ├── Teachers/
    ├── Schedule/
    ├── Salary/
    ├── Evaluation/
    └── Dashboard/

database/
├── migrations/
│   ├── xxxx_create_teachers_table.php
│   ├── xxxx_create_subjects_table.php
│   ├── xxxx_create_teacher_subject_table.php
│   ├── xxxx_create_teaching_schedules_table.php
│   ├── xxxx_create_academic_years_table.php
│   ├── xxxx_create_honor_settings_table.php
│   ├── xxxx_create_teacher_custom_rates_table.php
│   ├── xxxx_create_salary_calculations_table.php
│   ├── xxxx_create_salary_calculation_details_table.php
│   └── xxxx_create_teacher_evaluations_table.php
├── factories/
│   └── TeacherFactory.php
└── seeders/
    ├── SubjectSeeder.php
    └── TeacherSeeder.php
```

---

## 🔗 Dependencies & Integrations

### Internal Dependencies
- **Authentication Module:** User accounts untuk guru
- **Attendance System:** Data presensi guru untuk perhitungan honor
- **Class Management:** Data kelas untuk jadwal
- **Academic Year:** Periode semester

### External Packages
- `maatwebsite/excel` - Export Excel
- `barryvdh/laravel-dompdf` - Generate PDF slip gaji

### API Integrations
- Teacher attendance data
- Holiday/leave data
- Class & student data

---

## ⚠️ Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Attendance module belum ready | Perhitungan honor tidak akurat | Provide manual input untuk jam tidak hadir |
| Konflik jadwal kompleks | Bug di validation | Thorough testing dengan edge cases |
| PDF generation slow | UX buruk | Background job + queue |
| Large payroll export | Timeout | Chunked export + queue |

---

## 📝 Notes

### Out of Scope (Phase 2)
- Leave/cuti management untuk guru
- Sertifikasi & pelatihan tracking
- Replacement teacher (guru pengganti)
- Survei kepuasan dari orang tua/siswa

### Testing Strategy
- Unit tests untuk calculation services
- Feature tests untuk CRUD operations
- Browser tests untuk critical flows (Dusk)

### Performance Considerations
- Lazy load jadwal matrix
- Paginate teacher list
- Queue PDF generation
- Cache dashboard statistics

---

**Document Version:** 1.0  
**Author:** AI Assistant  
**Status:** Ready for Review
