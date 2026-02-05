# Sprint 01: Foundation & Teacher CRUD

**Epic:** 07 - Teacher Management (TCH)
**Sprint Duration:** Week 1
**Story Points:** 8 SP
**Sprint Goal:** Establish database foundation and complete Admin Teacher CRUD functionality

---

## Sprint Overview

Sprint ini berfokus pada pembangunan fondasi sistem Teacher Management, yaitu: database schema, models dengan relationships, serta Admin CRUD pages untuk pengelolaan data guru. Fondasi ini menjadi dependency untuk seluruh feature di sprint berikutnya.

---

## User Stories

### TCH-001: Database & Model Foundation
**Story Points:** 3 SP
**Priority:** P0 - Critical

**As a** developer
**I want** proper database schema and Eloquent models
**So that** all teacher-related features have a solid foundation

**Acceptance Criteria:**
- [ ] Migration `teachers` table dengan kolom: id, user_id (FK), nip, nik, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_hp, email, foto, status_kepegawaian (enum: tetap, honorer, kontrak), tanggal_mulai_kerja, tanggal_berakhir_kontrak, mata_pelajaran_utama, kualifikasi_pendidikan, is_active, timestamps, soft_deletes
- [ ] Migration `teacher_subjects` pivot table (teacher_id, subject_id, is_primary)
- [ ] Migration `subjects` table jika belum ada (id, nama, kode, deskripsi, tingkat, timestamps)
- [ ] Model `Teacher` dengan relationships: belongsTo User, belongsToMany Subject, hasMany TeachingSchedule, hasMany TeacherEvaluation, hasMany SalaryCalculation
- [ ] Model `Subject` dengan relationships: belongsToMany Teacher
- [ ] Factory `TeacherFactory` untuk testing
- [ ] Seeder `TeacherSeeder` dengan sample data (10 teachers)

**Tasks:**
1. [ ] Create migration untuk `subjects` table
2. [ ] Create migration untuk `teachers` table
3. [ ] Create migration untuk `teacher_subjects` pivot
4. [ ] Create `Subject` model dengan fillable dan relationships
5. [ ] Create `Teacher` model dengan fillable, casts, dan relationships
6. [ ] Create `TeacherFactory` dengan realistic data
7. [ ] Create `SubjectFactory`
8. [ ] Create `TeacherSeeder` dan `SubjectSeeder`
9. [ ] Update `DatabaseSeeder` untuk include new seeders
10. [ ] Run migrations dan verify schema

---

### TCH-002: Admin Teacher List Page
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to view all teachers in a filterable list
**So that** I can manage teacher data efficiently

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers` → `Admin/Teachers/Index.vue`
- [ ] Display table dengan kolom: Foto, NIP, Nama, Status, Mata Pelajaran, No HP, Aksi
- [ ] Filter by: status_kepegawaian, mata_pelajaran, is_active
- [ ] Search by: nama, nip, nik
- [ ] Sorting by: nama (asc/desc), tanggal_mulai_kerja
- [ ] Pagination (15 items per page)
- [ ] Action buttons: View, Edit, Toggle Status
- [ ] Empty state dengan ilustrasi jika tidak ada data
- [ ] Loading skeleton saat fetch data
- [ ] Mobile responsive (card view pada mobile)

**Tasks:**
1. [ ] Create `TeacherController@index` dengan filtering, search, pagination
2. [ ] Create `TeacherResource` untuk API response
3. [ ] Define route di `routes/web.php` (admin group)
4. [ ] Create `Admin/Teachers/Index.vue` page
5. [ ] Create reusable `TeacherFilters` component
6. [ ] Create `TeacherTable` component dengan sorting
7. [ ] Implement mobile card view
8. [ ] Add empty state dan loading skeleton
9. [ ] Generate Wayfinder routes

---

### TCH-003: Admin Create Teacher Page
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to add new teacher data
**So that** new teachers are registered in the system

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/create` → `Admin/Teachers/Create.vue`
- [ ] Route: `POST /admin/teachers` → store action
- [ ] Form fields sesuai migration (grouped by section):
  - Data Pribadi: NIK, nama, TTL, jenis kelamin, alamat, no HP, email, foto
  - Data Kepegawaian: NIP, status, tanggal mulai, tanggal berakhir (jika kontrak)
  - Akademik: mata pelajaran (multi-select), kualifikasi pendidikan
- [ ] Client-side validation dengan error messages
- [ ] Server-side validation dengan `StoreTeacherRequest`
- [ ] Auto-create User account dengan generated password
- [ ] Success notification dengan redirect ke list
- [ ] Cancel button kembali ke list
- [ ] Photo upload dengan preview dan crop

**Tasks:**
1. [ ] Create `StoreTeacherRequest` dengan validation rules
2. [ ] Create `TeacherController@create` untuk form data (subjects list)
3. [ ] Create `TeacherController@store` dengan User creation logic
4. [ ] Create `TeacherService` untuk business logic (create teacher + user)
5. [ ] Create `Admin/Teachers/Create.vue` page
6. [ ] Create `TeacherForm` reusable component
7. [ ] Implement photo upload dengan preview
8. [ ] Add multi-select untuk mata pelajaran
9. [ ] Implement form validation dan error handling
10. [ ] Generate Wayfinder routes

---

### TCH-004: Admin Edit & Update Teacher
**Story Points:** 1 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to edit existing teacher data
**So that** I can keep teacher information up to date

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/{teacher}/edit` → `Admin/Teachers/Edit.vue`
- [ ] Route: `PUT /admin/teachers/{teacher}` → update action
- [ ] Pre-filled form dengan existing data
- [ ] Reuse `TeacherForm` component dari Create
- [ ] Validation rules sama dengan create (kecuali unique fields)
- [ ] Success notification dengan redirect
- [ ] Track perubahan (audit log)

**Tasks:**
1. [ ] Create `UpdateTeacherRequest` dengan validation rules
2. [ ] Create `TeacherController@edit`
3. [ ] Create `TeacherController@update`
4. [ ] Create `Admin/Teachers/Edit.vue` page (reuse TeacherForm)
5. [ ] Handle photo update logic (delete old, upload new)
6. [ ] Implement audit logging untuk perubahan data
7. [ ] Generate Wayfinder routes

---

## Technical Specifications

### Database Schema

```sql
-- subjects table
CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL,
    tingkat ENUM('sd', 'smp', 'sma', 'all') DEFAULT 'all',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- teachers table
CREATE TABLE teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    nip VARCHAR(30) UNIQUE NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    tempat_lahir VARCHAR(100) NULL,
    tanggal_lahir DATE NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    alamat TEXT NULL,
    no_hp VARCHAR(20) NULL,
    email VARCHAR(100) UNIQUE NULL,
    foto VARCHAR(255) NULL,
    status_kepegawaian ENUM('tetap', 'honorer', 'kontrak') DEFAULT 'honorer',
    tanggal_mulai_kerja DATE NULL,
    tanggal_berakhir_kontrak DATE NULL,
    kualifikasi_pendidikan VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- teacher_subjects pivot
CREATE TABLE teacher_subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id)
);
```

### API Endpoints

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/admin/teachers` | TeacherController@index | List teachers with filters |
| GET | `/admin/teachers/create` | TeacherController@create | Show create form |
| POST | `/admin/teachers` | TeacherController@store | Store new teacher |
| GET | `/admin/teachers/{teacher}/edit` | TeacherController@edit | Show edit form |
| PUT | `/admin/teachers/{teacher}` | TeacherController@update | Update teacher |

### File Structure

```
app/
├── Http/
│   ├── Controllers/Admin/
│   │   └── TeacherController.php
│   ├── Requests/Admin/
│   │   ├── StoreTeacherRequest.php
│   │   └── UpdateTeacherRequest.php
│   └── Resources/
│       └── TeacherResource.php
├── Models/
│   ├── Teacher.php
│   └── Subject.php
├── Services/
│   └── TeacherService.php
└── Enums/
    └── StatusKepegawaian.php

database/
├── migrations/
│   ├── xxxx_create_subjects_table.php
│   ├── xxxx_create_teachers_table.php
│   └── xxxx_create_teacher_subjects_table.php
├── factories/
│   ├── TeacherFactory.php
│   └── SubjectFactory.php
└── seeders/
    ├── TeacherSeeder.php
    └── SubjectSeeder.php

resources/js/Pages/Admin/Teachers/
├── Index.vue
├── Create.vue
├── Edit.vue
└── Components/
    ├── TeacherForm.vue
    ├── TeacherTable.vue
    └── TeacherFilters.vue
```

---

## Definition of Done

- [ ] All migrations run successfully
- [ ] Models have proper relationships and work correctly
- [ ] All CRUD operations functional
- [ ] Form validations work (client & server side)
- [ ] Photo upload works with preview
- [ ] Mobile responsive design
- [ ] No TypeScript/ESLint errors
- [ ] Pint formatting applied
- [ ] Feature tests written and passing
- [ ] Wayfinder routes generated

---

## Dependencies

**Blocked By:** None (this is foundation)

**Blocks:**
- Sprint 02: Teaching Schedule Management
- Sprint 03: Honor/Salary Calculation
- Sprint 04: Teacher Evaluation

---

## Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Existing `subjects` table conflict | Medium | Check existing schema, migrate carefully |
| User creation failure | High | Transaction wrapper, proper error handling |
| Photo upload size | Low | Validate max size, compress images |

---

## Notes

- Pastikan User yang dibuat untuk guru memiliki role 'guru' yang sudah ada di sistem
- NIP bisa kosong untuk guru honorer (nullable)
- Email harus unique untuk login credentials
- Photo disimpan di `storage/app/public/teachers/`
