# Sprint 04: Teacher Evaluation System

**Epic:** 07 - Teacher Management (TCH)
**Sprint Duration:** Week 4
**Story Points:** 8 SP
**Sprint Goal:** Complete Principal evaluation system and Teacher self-service views

---

## Sprint Overview

Sprint ini berfokus pada sistem evaluasi kinerja guru oleh Kepala Sekolah, yaitu: form evaluasi 4 kompetensi (Pedagogik, Kepribadian, Sosial, Profesional), workflow publish, dan halaman self-service untuk guru melihat profil, jadwal, dan hasil evaluasi mereka.

---

## Prerequisites

- [ ] Sprint 01 completed (Teacher CRUD)
- [ ] Principal role exists in system
- [ ] Teacher role exists in system
- [ ] Navigation components ready for updates

---

## User Stories

### TCH-013: Evaluation Database & Models
**Story Points:** 1 SP
**Priority:** P0 - Critical

**As a** developer
**I want** proper evaluation schema
**So that** teacher evaluations can be stored and managed

**Acceptance Criteria:**
- [ ] Migration `teacher_evaluations` table: id, teacher_id, evaluator_id, academic_year_id, semester, tanggal_evaluasi, skor_pedagogik (1-5), catatan_pedagogik, skor_kepribadian, catatan_kepribadian, skor_sosial, catatan_sosial, skor_profesional, catatan_profesional, skor_rata_rata (calculated), rekomendasi (enum), catatan_umum, status (draft/published), published_at, timestamps
- [ ] Model `TeacherEvaluation` dengan relationships dan calculated attributes
- [ ] Scope untuk Principal access (own evaluations)
- [ ] Scope untuk Teacher access (own evaluations, published only)

**Tasks:**
1. [ ] Create migration `teacher_evaluations`
2. [ ] Create `TeacherEvaluation` model
3. [ ] Create `RekomendaiEvaluasi` enum (perpanjang, evaluasi_ulang, tidak_perpanjang)
4. [ ] Add relationships di Teacher dan User models
5. [ ] Create `TeacherEvaluationFactory`
6. [ ] Create seeder dengan sample evaluations

---

### TCH-014: Principal Evaluation Management
**Story Points:** 3 SP
**Priority:** P0 - Critical

**As a** Principal (Kepala Sekolah)
**I want** to create and manage teacher evaluations
**So that** teacher performance is documented

**Acceptance Criteria:**
- [ ] Route: `GET /principal/teachers` → Teacher list for Principal
- [ ] Route: `GET /principal/teachers/evaluations` → Evaluation list
- [ ] Route: `GET /principal/teachers/evaluations/create` → Create form
- [ ] Route: `POST /principal/teachers/evaluations` → Store evaluation
- [ ] Route: `GET /principal/teachers/evaluations/{id}` → View detail
- [ ] Route: `GET /principal/teachers/evaluations/{id}/edit` → Edit draft
- [ ] Route: `PUT /principal/teachers/evaluations/{id}` → Update
- [ ] Route: `POST /principal/teachers/evaluations/{id}/publish` → Publish
- [ ] Evaluation form dengan 4 kompetensi:
  - Pedagogik: kemampuan mengajar, metode, penguasaan materi
  - Kepribadian: kedisiplinan, integritas, sikap
  - Sosial: komunikasi, kerjasama, hubungan dengan siswa/ortu
  - Profesional: pengembangan diri, sertifikasi, publikasi
- [ ] Score slider 1-5 dengan label (Sangat Kurang - Sangat Baik)
- [ ] Auto-calculate rata-rata
- [ ] Rekomendasi dropdown
- [ ] Draft auto-save
- [ ] Publish confirmation dialog
- [ ] Published evaluations cannot be edited

**Tasks:**
1. [ ] Create `Principal/TeacherController` untuk teacher list
2. [ ] Create `Principal/TeacherEvaluationController` dengan full CRUD
3. [ ] Create `StoreTeacherEvaluationRequest`
4. [ ] Create `UpdateTeacherEvaluationRequest`
5. [ ] Create `TeacherEvaluationResource`
6. [ ] Create `Principal/Teachers/Index.vue`
7. [ ] Create `Principal/Teachers/Evaluations/Index.vue`
8. [ ] Create `Principal/Teachers/Evaluations/Create.vue`
9. [ ] Create `Principal/Teachers/Evaluations/Edit.vue`
10. [ ] Create `Principal/Teachers/Evaluations/Show.vue`
11. [ ] Create `EvaluationForm` component dengan score sliders
12. [ ] Create `CompetencyCard` component
13. [ ] Implement publish workflow
14. [ ] Update Principal navigation
15. [ ] Generate Wayfinder routes

---

### TCH-015: Teacher Profile Self-Service
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As a** Teacher
**I want** to view my own profile and data
**So that** I can see my employment information

**Acceptance Criteria:**
- [ ] Route: `GET /teacher/profile` → Own profile page
- [ ] Display sections:
  - Data Pribadi: nama, TTL, alamat, kontak
  - Data Kepegawaian: NIP, status, tanggal mulai
  - Mata Pelajaran: list subjects taught
  - Quick links: jadwal, evaluasi, slip gaji
- [ ] Photo display
- [ ] Read-only (no edit for sensitive data)
- [ ] Optional: edit contact info only (no HP, email)
- [ ] Link to full schedule page
- [ ] Statistics card: total jam mengajar bulan ini

**Tasks:**
1. [ ] Create `Teacher/ProfileController@index`
2. [ ] Create `TeacherProfileResource`
3. [ ] Create `Teacher/Profile/Index.vue`
4. [ ] Create `ProfileCard` component
5. [ ] Create `TeacherStatsCard` component
6. [ ] Add profile menu to Teacher navigation
7. [ ] Generate Wayfinder routes

---

### TCH-016: Teacher View Own Evaluations
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As a** Teacher
**I want** to view my published evaluations
**So that** I understand my performance feedback

**Acceptance Criteria:**
- [ ] Route: `GET /teacher/evaluations` → Evaluation list (published only)
- [ ] Route: `GET /teacher/evaluations/{id}` → Evaluation detail
- [ ] List shows: periode, tanggal, rata-rata skor, rekomendasi
- [ ] Detail shows: all 4 competency scores with catatan
- [ ] Read-only view (teacher cannot edit)
- [ ] Hide evaluator name (privacy)
- [ ] Empty state jika belum ada evaluasi
- [ ] Alert/notification saat ada evaluasi baru

**Tasks:**
1. [ ] Create `Teacher/EvaluationController@index`
2. [ ] Create `Teacher/EvaluationController@show`
3. [ ] Create `TeacherEvaluationResource` (teacher view - limited fields)
4. [ ] Create `Teacher/Evaluations/Index.vue`
5. [ ] Create `Teacher/Evaluations/Show.vue`
6. [ ] Create `EvaluationCard` component (read-only)
7. [ ] Create `ScoreDisplay` component dengan visual indicator
8. [ ] Add notification for new published evaluation
9. [ ] Add evaluation menu to Teacher navigation
10. [ ] Generate Wayfinder routes

---

## Technical Specifications

### Database Schema

```sql
-- teacher_evaluations table
CREATE TABLE teacher_evaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id BIGINT UNSIGNED NOT NULL,
    evaluator_id BIGINT UNSIGNED NOT NULL, -- Principal user_id
    academic_year_id BIGINT UNSIGNED NOT NULL,
    semester ENUM('ganjil', 'genap') NOT NULL,
    tanggal_evaluasi DATE NOT NULL,
    
    -- Kompetensi Pedagogik (mengajar, metode, penguasaan materi)
    skor_pedagogik TINYINT UNSIGNED NOT NULL CHECK (skor_pedagogik BETWEEN 1 AND 5),
    catatan_pedagogik TEXT NULL,
    
    -- Kompetensi Kepribadian (disiplin, integritas, sikap)
    skor_kepribadian TINYINT UNSIGNED NOT NULL CHECK (skor_kepribadian BETWEEN 1 AND 5),
    catatan_kepribadian TEXT NULL,
    
    -- Kompetensi Sosial (komunikasi, kerjasama)
    skor_sosial TINYINT UNSIGNED NOT NULL CHECK (skor_sosial BETWEEN 1 AND 5),
    catatan_sosial TEXT NULL,
    
    -- Kompetensi Profesional (pengembangan diri)
    skor_profesional TINYINT UNSIGNED NOT NULL CHECK (skor_profesional BETWEEN 1 AND 5),
    catatan_profesional TEXT NULL,
    
    -- Summary
    skor_rata_rata DECIMAL(3,2) GENERATED ALWAYS AS (
        (skor_pedagogik + skor_kepribadian + skor_sosial + skor_profesional) / 4
    ) STORED,
    
    rekomendasi ENUM('perpanjang', 'evaluasi_ulang', 'tidak_perpanjang') NOT NULL,
    catatan_umum TEXT NULL,
    
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_teacher_semester (teacher_id, academic_year_id, semester)
);
```

### Score Labels

```php
// config/evaluation.php
return [
    'scores' => [
        1 => 'Sangat Kurang',
        2 => 'Kurang',
        3 => 'Cukup',
        4 => 'Baik',
        5 => 'Sangat Baik',
    ],
    'competencies' => [
        'pedagogik' => [
            'label' => 'Kompetensi Pedagogik',
            'description' => 'Kemampuan mengajar, penguasaan metode pembelajaran, dan penguasaan materi',
            'indicators' => [
                'Menyusun RPP dengan baik',
                'Menggunakan metode pembelajaran yang variatif',
                'Menguasai materi pelajaran',
                'Melaksanakan evaluasi pembelajaran',
            ],
        ],
        'kepribadian' => [
            'label' => 'Kompetensi Kepribadian',
            'description' => 'Kedisiplinan, integritas, dan sikap profesional',
            'indicators' => [
                'Hadir tepat waktu',
                'Berpakaian rapi dan sopan',
                'Menunjukkan integritas',
                'Menjadi teladan bagi siswa',
            ],
        ],
        'sosial' => [
            'label' => 'Kompetensi Sosial',
            'description' => 'Komunikasi, kerjasama, dan hubungan dengan stakeholder',
            'indicators' => [
                'Berkomunikasi efektif dengan siswa',
                'Bekerja sama dengan rekan guru',
                'Berinteraksi baik dengan orang tua',
                'Aktif dalam kegiatan sekolah',
            ],
        ],
        'profesional' => [
            'label' => 'Kompetensi Profesional',
            'description' => 'Pengembangan diri dan profesionalisme',
            'indicators' => [
                'Mengikuti pelatihan/workshop',
                'Melakukan penelitian/PTK',
                'Mengembangkan bahan ajar',
                'Memiliki sertifikasi',
            ],
        ],
    ],
];
```

### API Endpoints

**Principal Routes:**

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/principal/teachers` | Principal\TeacherController@index | List teachers |
| GET | `/principal/teachers/evaluations` | Principal\EvaluationController@index | List evaluations |
| GET | `/principal/teachers/evaluations/create` | Principal\EvaluationController@create | Create form |
| POST | `/principal/teachers/evaluations` | Principal\EvaluationController@store | Store |
| GET | `/principal/teachers/evaluations/{id}` | Principal\EvaluationController@show | View detail |
| GET | `/principal/teachers/evaluations/{id}/edit` | Principal\EvaluationController@edit | Edit form |
| PUT | `/principal/teachers/evaluations/{id}` | Principal\EvaluationController@update | Update |
| POST | `/principal/teachers/evaluations/{id}/publish` | Principal\EvaluationController@publish | Publish |

**Teacher Routes:**

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/teacher/profile` | Teacher\ProfileController@index | Own profile |
| GET | `/teacher/evaluations` | Teacher\EvaluationController@index | Own evaluations |
| GET | `/teacher/evaluations/{id}` | Teacher\EvaluationController@show | Evaluation detail |

### File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Principal/
│   │   │   ├── TeacherController.php
│   │   │   └── TeacherEvaluationController.php
│   │   └── Teacher/
│   │       ├── ProfileController.php
│   │       └── EvaluationController.php
│   ├── Requests/Principal/
│   │   ├── StoreTeacherEvaluationRequest.php
│   │   └── UpdateTeacherEvaluationRequest.php
│   └── Resources/
│       ├── TeacherEvaluationResource.php
│       └── TeacherProfileResource.php
├── Models/
│   └── TeacherEvaluation.php
└── Enums/
    └── RekomendasiEvaluasi.php

resources/js/Pages/
├── Principal/
│   └── Teachers/
│       ├── Index.vue
│       └── Evaluations/
│           ├── Index.vue
│           ├── Create.vue
│           ├── Edit.vue
│           ├── Show.vue
│           └── Components/
│               ├── EvaluationForm.vue
│               ├── CompetencyCard.vue
│               └── ScoreSlider.vue
└── Teacher/
    ├── Profile/
    │   ├── Index.vue
    │   └── Components/
    │       ├── ProfileCard.vue
    │       └── TeacherStatsCard.vue
    └── Evaluations/
        ├── Index.vue
        ├── Show.vue
        └── Components/
            ├── EvaluationCard.vue
            └── ScoreDisplay.vue
```

---

## UI Components

### Score Slider Component

```vue
<!-- ScoreSlider.vue -->
<template>
  <div class="space-y-2">
    <div class="flex justify-between text-sm">
      <span class="font-medium">{{ label }}</span>
      <span :class="scoreColorClass">{{ scoreLabel }}</span>
    </div>
    <input
      type="range"
      :value="modelValue"
      @input="$emit('update:modelValue', Number($event.target.value))"
      min="1"
      max="5"
      step="1"
      class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
    />
    <div class="flex justify-between text-xs text-gray-500">
      <span>Sangat Kurang</span>
      <span>Sangat Baik</span>
    </div>
  </div>
</template>
```

### Score Display Component (Read-only)

```vue
<!-- ScoreDisplay.vue -->
<template>
  <div class="flex items-center gap-3">
    <div class="flex gap-1">
      <template v-for="i in 5" :key="i">
        <div
          :class="[
            'w-3 h-3 rounded-full',
            i <= score ? scoreColors[score] : 'bg-gray-200'
          ]"
        />
      </template>
    </div>
    <span class="text-sm font-medium">{{ scoreLabels[score] }}</span>
  </div>
</template>
```

---

## Definition of Done

- [ ] Principal can create/edit/publish evaluations
- [ ] Teacher can view own published evaluations
- [ ] Teacher can view own profile
- [ ] Score calculation works correctly
- [ ] Publish workflow functional
- [ ] Navigation updated for both roles
- [ ] Mobile responsive
- [ ] Feature tests passing
- [ ] No lint errors

---

## Dependencies

**Blocked By:**
- Sprint 01: Teacher CRUD

**Blocks:**
- Sprint 05: Dashboard (evaluation summary widget)

---

## Navigation Updates

### Principal Menu

```
Principal Menu:
├── Dashboard
├── Data Siswa
├── Guru (NEW)
│   ├── Data Guru
│   └── Evaluasi Guru
├── Kehadiran
├── Akademik
...
```

### Teacher Menu

```
Teacher Menu:
├── Dashboard
├── Data Siswa
├── Profil Saya (NEW)
├── Jadwal Saya
├── Presensi
├── Penilaian
├── Perizinan
├── Evaluasi Saya (NEW)
└── Slip Gaji (Sprint 05)
```

---

## Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Principal role not exists | High | Verify role seeder, create if needed |
| Privacy concerns on evaluator | Medium | Hide evaluator name in teacher view |
| Draft auto-save conflicts | Low | Debounce, last-write-wins |

---

## Notes

- Evaluasi per semester (2x setahun)
- Satu guru hanya bisa dievaluasi sekali per semester
- Published evaluation tidak bisa di-edit (create new if needed)
- Skor rata-rata di-calculate oleh database (GENERATED column)
- Teacher tidak bisa melihat siapa yang mengevaluasi (privacy)
