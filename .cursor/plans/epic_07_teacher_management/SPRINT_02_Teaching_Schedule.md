# Sprint 02: Teaching Schedule Management

**Epic:** 07 - Teacher Management (TCH)
**Sprint Duration:** Week 2
**Story Points:** 8 SP
**Sprint Goal:** Complete teaching schedule CRUD with matrix views and conflict detection

---

## Sprint Overview

Sprint ini berfokus pada pengelolaan jadwal mengajar guru, yaitu: input jadwal per guru/kelas, tampilan matrix view, dan sistem deteksi konflik jadwal. Schedule data menjadi basis perhitungan honor di sprint berikutnya.

---

## Prerequisites

- [ ] Sprint 01 completed (Teacher CRUD functional)
- [ ] Teachers data available in database
- [ ] Subjects data available
- [ ] Classes/Kelas data exists in system

---

## User Stories

### TCH-005: Teaching Schedule Database
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As a** developer
**I want** proper schedule database schema
**So that** teaching schedules can be stored and queried efficiently

**Acceptance Criteria:**
- [ ] Migration `teaching_schedules` table dengan: id, teacher_id, subject_id, class_id, academic_year_id, semester, hari (enum: senin-jumat), jam_mulai, jam_selesai, ruangan, is_active, timestamps
- [ ] Migration `academic_years` table jika belum ada
- [ ] Model `TeachingSchedule` dengan relationships
- [ ] Unique constraint: teacher + hari + jam_mulai (no overlap)
- [ ] Unique constraint: class + hari + jam_mulai (no double booking)
- [ ] Factory dan Seeder untuk sample schedules

**Tasks:**
1. [ ] Create migration `academic_years` (if not exists)
2. [ ] Create migration `teaching_schedules`
3. [ ] Create `TeachingSchedule` model dengan relationships
4. [ ] Create `AcademicYear` model (if not exists)
5. [ ] Add scope untuk conflict detection
6. [ ] Create `TeachingScheduleFactory`
7. [ ] Create `TeachingScheduleSeeder`
8. [ ] Add relationship di Teacher model: hasMany schedules

---

### TCH-006: Admin Schedule List & Matrix View
**Story Points:** 3 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to view all schedules in list and matrix format
**So that** I can visualize and manage teaching schedules

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/schedules` → Index page
- [ ] Route: `GET /admin/teachers/schedules/by-teacher/{teacher}` → Matrix per teacher
- [ ] Route: `GET /admin/teachers/schedules/by-class/{class}` → Matrix per class
- [ ] List view dengan filters: teacher, class, day, semester
- [ ] Matrix view (time slots x days) dengan color coding per subject
- [ ] Empty slot indication untuk available time
- [ ] Conflict indication dengan warning color
- [ ] Mobile: List view only (matrix too complex)
- [ ] Export schedule ke PDF (per teacher/per class)

**Tasks:**
1. [ ] Create `TeachingScheduleController@index`
2. [ ] Create `TeachingScheduleController@byTeacher`
3. [ ] Create `TeachingScheduleController@byClass`
4. [ ] Create `TeachingScheduleResource`
5. [ ] Create `Admin/Teachers/Schedules/Index.vue`
6. [ ] Create `Admin/Teachers/Schedules/ByTeacher.vue` (matrix)
7. [ ] Create `Admin/Teachers/Schedules/ByClass.vue` (matrix)
8. [ ] Create `ScheduleMatrix` reusable component
9. [ ] Create `ScheduleCard` component untuk mobile
10. [ ] Implement PDF export dengan DomPDF
11. [ ] Generate Wayfinder routes

---

### TCH-007: Admin Create Schedule with Conflict Detection
**Story Points:** 2 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to add teaching schedules with automatic conflict detection
**So that** no scheduling conflicts occur

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/schedules/create` → Create form
- [ ] Route: `POST /admin/teachers/schedules` → Store with validation
- [ ] Form fields: teacher (select), subject (select based on teacher's subjects), class, semester, hari, jam_mulai, jam_selesai, ruangan
- [ ] Real-time conflict check saat input:
  - Teacher conflict: guru sudah mengajar di waktu tersebut
  - Class conflict: kelas sudah ada pelajaran di waktu tersebut
  - Room conflict: ruangan sudah dipakai (optional)
- [ ] Validation: jam_selesai > jam_mulai
- [ ] Validation: time within school hours (07:00 - 16:00)
- [ ] Success/error notification

**Tasks:**
1. [ ] Create `StoreTeachingScheduleRequest` dengan custom validation
2. [ ] Create `TeachingScheduleController@create`
3. [ ] Create `TeachingScheduleController@store`
4. [ ] Create `ScheduleConflictService` untuk conflict detection
5. [ ] Create API endpoint untuk real-time conflict check
6. [ ] Create `Admin/Teachers/Schedules/Create.vue`
7. [ ] Create `ScheduleForm` component
8. [ ] Implement conflict warning UI
9. [ ] Add cascade subject dropdown based on teacher
10. [ ] Generate Wayfinder routes

---

### TCH-008: Admin Edit & Delete Schedule
**Story Points:** 1 SP
**Priority:** P0 - Critical

**As an** Admin/TU
**I want** to edit and delete schedules
**So that** I can adjust schedules as needed

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/schedules/{schedule}/edit`
- [ ] Route: `PUT /admin/teachers/schedules/{schedule}`
- [ ] Route: `DELETE /admin/teachers/schedules/{schedule}`
- [ ] Edit dengan conflict check sama seperti create
- [ ] Soft delete dengan confirmation dialog
- [ ] Bulk delete selected schedules
- [ ] Copy schedule from previous semester feature

**Tasks:**
1. [ ] Create `UpdateTeachingScheduleRequest`
2. [ ] Create `TeachingScheduleController@edit`
3. [ ] Create `TeachingScheduleController@update`
4. [ ] Create `TeachingScheduleController@destroy`
5. [ ] Create `Admin/Teachers/Schedules/Edit.vue`
6. [ ] Implement bulk delete
7. [ ] Implement copy from previous semester
8. [ ] Add confirmation dialog component
9. [ ] Generate Wayfinder routes

---

## Technical Specifications

### Database Schema

```sql
-- academic_years table (if not exists)
CREATE TABLE academic_years (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tahun_ajaran VARCHAR(20) NOT NULL, -- e.g., "2025/2026"
    semester ENUM('ganjil', 'genap') NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    is_active BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_year_semester (tahun_ajaran, semester)
);

-- teaching_schedules table
CREATE TABLE teaching_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    hari ENUM('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruangan VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES school_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
);

-- Index for conflict detection queries
CREATE INDEX idx_schedule_teacher_day_time ON teaching_schedules(teacher_id, hari, jam_mulai, jam_selesai);
CREATE INDEX idx_schedule_class_day_time ON teaching_schedules(class_id, hari, jam_mulai, jam_selesai);
```

### Conflict Detection Logic

```php
// ScheduleConflictService.php
public function checkConflicts(array $data, ?int $excludeId = null): array
{
    $conflicts = [];
    
    // Teacher conflict: guru sudah mengajar di waktu overlap
    $teacherConflict = TeachingSchedule::query()
        ->where('teacher_id', $data['teacher_id'])
        ->where('hari', $data['hari'])
        ->where('academic_year_id', $data['academic_year_id'])
        ->where(function ($q) use ($data) {
            $q->whereBetween('jam_mulai', [$data['jam_mulai'], $data['jam_selesai']])
              ->orWhereBetween('jam_selesai', [$data['jam_mulai'], $data['jam_selesai']])
              ->orWhere(function ($q2) use ($data) {
                  $q2->where('jam_mulai', '<=', $data['jam_mulai'])
                     ->where('jam_selesai', '>=', $data['jam_selesai']);
              });
        })
        ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
        ->exists();
    
    if ($teacherConflict) {
        $conflicts['teacher'] = 'Guru sudah memiliki jadwal di waktu tersebut';
    }
    
    // Class conflict: kelas sudah ada jadwal di waktu tersebut
    $classConflict = TeachingSchedule::query()
        ->where('class_id', $data['class_id'])
        ->where('hari', $data['hari'])
        ->where('academic_year_id', $data['academic_year_id'])
        ->where(function ($q) use ($data) {
            // same overlap logic
        })
        ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
        ->exists();
    
    if ($classConflict) {
        $conflicts['class'] = 'Kelas sudah memiliki jadwal di waktu tersebut';
    }
    
    return $conflicts;
}
```

### API Endpoints

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/admin/teachers/schedules` | TeachingScheduleController@index | List schedules |
| GET | `/admin/teachers/schedules/create` | TeachingScheduleController@create | Create form |
| POST | `/admin/teachers/schedules` | TeachingScheduleController@store | Store schedule |
| GET | `/admin/teachers/schedules/{id}/edit` | TeachingScheduleController@edit | Edit form |
| PUT | `/admin/teachers/schedules/{id}` | TeachingScheduleController@update | Update |
| DELETE | `/admin/teachers/schedules/{id}` | TeachingScheduleController@destroy | Delete |
| GET | `/admin/teachers/schedules/by-teacher/{teacher}` | TeachingScheduleController@byTeacher | Matrix view |
| GET | `/admin/teachers/schedules/by-class/{class}` | TeachingScheduleController@byClass | Matrix view |
| POST | `/admin/teachers/schedules/check-conflict` | TeachingScheduleController@checkConflict | Real-time check |
| POST | `/admin/teachers/schedules/copy-semester` | TeachingScheduleController@copySemester | Copy schedules |

### File Structure

```
app/
├── Http/
│   ├── Controllers/Admin/
│   │   └── TeachingScheduleController.php
│   ├── Requests/Admin/
│   │   ├── StoreTeachingScheduleRequest.php
│   │   └── UpdateTeachingScheduleRequest.php
│   └── Resources/
│       └── TeachingScheduleResource.php
├── Models/
│   ├── TeachingSchedule.php
│   └── AcademicYear.php
└── Services/
    └── ScheduleConflictService.php

resources/js/Pages/Admin/Teachers/Schedules/
├── Index.vue
├── Create.vue
├── Edit.vue
├── ByTeacher.vue
├── ByClass.vue
└── Components/
    ├── ScheduleForm.vue
    ├── ScheduleMatrix.vue
    ├── ScheduleCard.vue
    └── ConflictWarning.vue
```

---

## Definition of Done

- [ ] All schedule CRUD operations functional
- [ ] Matrix views render correctly
- [ ] Conflict detection works real-time
- [ ] No double-booking possible
- [ ] Mobile responsive (list view)
- [ ] PDF export works
- [ ] Copy semester feature works
- [ ] Feature tests passing
- [ ] No lint errors

---

## Dependencies

**Blocked By:**
- Sprint 01: Teacher CRUD (teachers data required)

**Blocks:**
- Sprint 03: Honor/Salary (schedule hours needed for calculation)
- Sprint 05: Dashboard (schedule data for widgets)

---

## Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Complex matrix UI | Medium | Fallback to list view, prioritize functionality |
| Performance with many schedules | Medium | Add indexes, pagination, lazy loading |
| Time overlap edge cases | High | Comprehensive conflict detection, unit tests |

---

## Notes

- Time slots biasanya 45 menit per jam pelajaran
- Istirahat tidak perlu di-schedule (gap otomatis)
- Sabtu optional tergantung kebijakan sekolah
- Matrix view sebaiknya 07:00-16:00 dengan increment 30 menit
