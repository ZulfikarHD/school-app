# Feature: AD03 - Assign Student to Class

> **Code:** AD03 | **Priority:** High | **Status:** ✅ Complete
> **Sprint:** 0-1 | **Menu:** Admin > Data Siswa > Pindah Kelas

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=admin/students`
- [x] Service methods match Controller calls (assignStudentsToClass)
- [x] Tested with `php artisan tinker` - ✅ Method exists
- [x] Vue pages exist for Inertia renders (AssignClassModal.vue)
- [x] Migrations applied (classes, student_class_history)
- [x] Following DOCUMENTATION_GUIDE.md template
- [x] All 18 tests passing (AssignClassTest)

---

## Overview

Assign Student to Class merupakan fitur manajemen akademik yang bertujuan untuk memindahkan siswa ke kelas baru dengan riwayat lengkap, yaitu: mendukung perpindahan single/bulk student, mencatat wali kelas otomatis, menyimpan history untuk audit trail, dan memastikan data integrity dengan database transactions.

---

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| AD03-US01 | Admin | Memindahkan satu siswa ke kelas lain | Dapat mengelola perpindahan individual | ✅ Complete |
| AD03-US02 | Admin | Memindahkan multiple siswa sekaligus (bulk) | Efisien saat promosi kelas atau reorganisasi | ✅ Complete |
| AD03-US03 | Admin | Melihat riwayat perpindahan kelas siswa | Dapat tracking perjalanan akademik siswa | ✅ Complete |
| AD03-US04 | Admin | Menambahkan catatan saat memindahkan | Mendokumentasikan alasan perpindahan | ✅ Complete |
| AD03-US05 | Admin | Melihat nama wali kelas di history | Mengetahui siapa wali kelas saat perpindahan | ✅ Complete |
| AD03-US06 | System | Auto-record tahun ajaran saat perpindahan | History mencerminkan waktu perpindahan | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| AD03-BR01 | Siswa dapat dipindahkan ke kelas manapun tanpa batasan tingkat | Service tidak validasi tingkat kelas |
| AD03-BR02 | Perpindahan yang sama (kelas lama = kelas baru) tidak dicatat di history | Service skip jika `kelas_id` sama |
| AD03-BR03 | Wali kelas otomatis tercatat dari SchoolClass relation | Service fetch `waliKelas->name` saat create history |
| AD03-BR04 | Tahun ajaran wajib format `YYYY/YYYY` (e.g., 2024/2025) | Validation: `regex:/^\d{4}\/\d{4}$/` |
| AD03-BR05 | Minimal 1 siswa wajib dipilih untuk bulk assignment | Validation: `student_ids.min:1` |
| AD03-BR06 | Semua operasi dalam database transaction untuk data integrity | Service: `DB::beginTransaction()` + rollback on error |
| AD03-BR07 | Activity log dicatat untuk audit trail | ActivityLog created with student_count & kelas_id |

---

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| **Migration** | `database/migrations/2025_12_24_070315_create_classes_table.php` | Define `classes` table schema |
| **Migration** | `database/migrations/2025_12_24_012253_create_student_class_history_table.php` | Define history tracking table |
| **Model** | `app/Models/SchoolClass.php` | SchoolClass entity with relations & scopes |
| **Model** | `app/Models/Student.php` | Added `kelas()` belongsTo relation |
| **Model** | `app/Models/StudentClassHistory.php` | Added `kelas()` belongsTo relation |
| **Controller** | `app/Http/Controllers/Admin/StudentController.php` | Handle HTTP request for assign-class |
| **Form Request** | `app/Http/Requests/Admin/AssignClassRequest.php` | Validate assign-class payload |
| **Service** | `app/Services/StudentService.php` | Business logic: `assignStudentsToClass()` |
| **Factory** | `database/factories/SchoolClassFactory.php` | Generate fake SchoolClass for testing |
| **Seeder** | `database/seeders/SchoolClassSeeder.php` | Populate 24 classes (1A-6D) |
| **Seeder** | `database/seeders/StudentSeeder.php` | Generate 50 students with classes & guardians |
| **Vue Component** | `resources/js/components/features/students/AssignClassModal.vue` | Modal UI for assign class |
| **Vue Component** | `resources/js/components/features/students/StudentTable.vue` | Table with checkbox selection |
| **Vue Page** | `resources/js/pages/Admin/Students/Index.vue` | Index page with bulk action |
| **Vue Page** | `resources/js/pages/Admin/Students/Show.vue` | Detail page with single action |
| **Form Component** | `resources/js/components/ui/Form/FormSelect.vue` | Enhanced select dropdown |
| **Test** | `tests/Feature/Student/AssignClassTest.php` | 18 test cases (100% passing) |

### Routes

| Method | URI | Controller@Method | Name | Status |
|--------|-----|-------------------|------|--------|
| POST | `admin/students/assign-class` | `Admin\StudentController@assignClass` | `admin.students.assign-class` | ✅ Active |

> 📌 Route verified via `php artisan route:list --path=admin/students`

### Database Schema

#### Table: `classes`

```sql
CREATE TABLE classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tingkat TINYINT UNSIGNED NOT NULL COMMENT '1-6 for SD',
    nama VARCHAR(10) NOT NULL COMMENT 'A, B, C, D',
    nama_lengkap VARCHAR(20) GENERATED ALWAYS AS (CONCAT(tingkat, nama)),
    wali_kelas_id BIGINT UNSIGNED NULL,
    kapasitas INT NOT NULL DEFAULT 40,
    tahun_ajaran VARCHAR(9) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_tingkat_tahun_active (tingkat, tahun_ajaran, is_active),
    FOREIGN KEY (wali_kelas_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### Table: `student_class_history`

```sql
CREATE TABLE student_class_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL,
    wali_kelas VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES classes(id) ON DELETE CASCADE
);
```

> 📌 Lihat `docs/DATABASE.md` untuk schema lengkap (jika tersedia)

---

## Data Structures

### Request Payload

```typescript
interface AssignClassRequest {
    student_ids: number[];           // Array of student IDs
    kelas_id: number;                // Target class ID
    tahun_ajaran?: string;           // Optional, defaults to '2024/2025'
    notes?: string;                  // Optional notes (max 255 chars)
}
```

### SchoolClass Interface

```typescript
interface SchoolClass {
    id: number;
    tingkat: number;                 // 1-6
    nama: string;                    // A, B, C, D
    nama_lengkap: string;            // e.g., "1A", "6D"
    wali_kelas_id: number | null;
    kapasitas: number;
    tahun_ajaran: string;            // YYYY/YYYY format
    is_active: boolean;
    created_at: string;
    updated_at: string;
}
```

### StudentClassHistory Interface

```typescript
interface StudentClassHistory {
    id: number;
    student_id: number;
    kelas_id: number;
    tahun_ajaran: string;
    wali_kelas: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}
```

---

## UI/UX Specifications

### Index Page (Bulk Assignment)

**Location:** `resources/js/pages/Admin/Students/Index.vue`

**Features:**
- ✅ Checkbox selection on table rows (desktop & mobile)
- ✅ Dynamic header showing "X Siswa Dipilih" when selection active
- ✅ "Pindah Kelas" button appears when ≥1 student selected
- ✅ Emerald accent for primary actions
- ✅ Haptic feedback on interactions

**Mobile Optimization:**
- Student card dengan checkbox di kiri atas
- Selected cards diberi border emerald dan ring effect
- Touch target minimal 44x44px

### Show Page (Single Assignment)

**Location:** `resources/js/pages/Admin/Students/Show.vue`

**Features:**
- ✅ "Pindah Kelas" button pada action bar
- ✅ Emerald accent dengan border style
- ✅ Modal menampilkan nama siswa di summary info

### AssignClassModal Component

**Location:** `resources/js/components/features/students/AssignClassModal.vue`

**Layout:**
1. **Summary Info Section**
   - Single: "Siswa: [Nama Lengkap]"
   - Bulk: "Memindahkan X siswa terpilih"
   - Background: `slate-50` dengan border

2. **Form Section**
   - FormSelect: Pilih Kelas Tujuan (required)
   - FormTextarea: Catatan Opsional (max 255 chars)
   - Emerald focus state

3. **Action Buttons**
   - "Batal" (secondary)
   - "Simpan Perubahan" (primary, emerald)
   - Loading state: "Menyimpan..."

**Behavior:**
- ✅ Form reset on modal close
- ✅ Watch `show` prop to reset `kelas_id` and notes
- ✅ Validation: kelas_id wajib dipilih
- ✅ Success: reload page, show success modal
- ✅ Error: haptic heavy, display error

---

## Edge Cases & Handling

| Scenario | Expected Behavior | Implementation | Status |
|----------|-------------------|----------------|--------|
| Pindah ke kelas yang sama | Skip, tidak create history | Service: `if ($student->kelas_id !== $kelasId)` | ✅ Tested |
| Kelas tujuan tidak ditemukan | Validation error 422 | `Rule::exists('classes', 'id')` | ✅ Tested |
| Student ID tidak valid | Validation error 422 | `Rule::exists('students', 'id')` | ✅ Tested |
| Tahun ajaran format salah | Validation error 422 | `regex:/^\d{4}\/\d{4}$/` | ✅ Tested |
| Array kosong student_ids | Validation error 422 | `student_ids.min:1` | ✅ Tested |
| Database error saat insert | Rollback transaction, 500 error | `DB::rollBack()` + exception throw | ✅ Tested |
| Wali kelas tidak ada di kelas | `wali_kelas` field = null | `$targetClass?->waliKelas?->name` | ✅ Tested |
| User unauthenticated | Redirect to login | Auth middleware | ✅ Tested |
| User bukan admin/superadmin | 403 Forbidden | `AssignClassRequest::authorize()` | ✅ Tested |
| Classes prop tidak ada/null | Empty option di select | Defensive check: `classOptions` computed | ✅ Fixed |
| Select value undefined | FormSelect returns null | Enhanced handleChange logic | ✅ Fixed |

---

## Configuration

### Default Values

```php
// app/Http/Controllers/Admin/StudentController.php
'tahun_ajaran' => $request->tahun_ajaran ?? '2024/2025'  // Fallback value
```

### Factory States

```php
// database/factories/SchoolClassFactory.php
SchoolClass::factory()->tingkat(1)->nama('A')->tahunAjaran('2024/2025')->create();
```

---

## Security Considerations

| Concern | Mitigation | Implementation |
|---------|-----------|----------------|
| Unauthorized access | Role-based authorization | `authorize()` checks ADMIN/SUPERADMIN role |
| SQL Injection | Eloquent ORM + parameterized queries | Laravel's query builder |
| Mass assignment | Fillable whitelist | `$fillable` in models |
| Invalid input | Form Request validation | `AssignClassRequest` rules |
| Race condition | Database transaction | `DB::beginTransaction()` + `commit()/rollBack()` |
| Audit trail missing | Activity logging | `ActivityLog::create()` after success |

---

## Performance Considerations

- **Bulk Operations:** Uses single transaction for all students
- **N+1 Query Prevention:** Eager load `waliKelas` via `SchoolClass::find()->waliKelas`
- **Index Usage:** `idx_tingkat_tahun_active` on classes table
- **Transaction Overhead:** Minimal, only during assignment operation

---

## Testing Coverage

### Test Suite: `AssignClassTest`

**Total Tests:** 18 | **Status:** ✅ 18 Passed (100%)

**Test Categories:**
1. **Core Functionality (2 tests)**
   - ✅ Admin can assign single student to class
   - ✅ Admin can assign multiple students to class

2. **Validation (9 tests)**
   - ✅ Requires student_ids
   - ✅ student_ids must be array
   - ✅ Requires at least one student
   - ✅ Requires kelas_id
   - ✅ kelas_id must exist
   - ✅ student_ids must exist
   - ✅ tahun_ajaran must have valid format
   - ✅ tahun_ajaran is optional
   - ✅ notes is optional

3. **Authorization (2 tests)**
   - ✅ Non-admin cannot assign students
   - ✅ Unauthenticated user cannot assign

4. **Business Logic (3 tests)**
   - ✅ Includes wali kelas name in history
   - ✅ Creates history for all students
   - ✅ Rolls back on error

5. **Integration (2 tests)**
   - ✅ Students index shows classes data
   - ✅ Student show page includes classes

**Run Command:**
```bash
php artisan test --filter=AssignClassTest
```

> 📌 Full test plan: [AD03 Test Plan](../../testing/AD03-assign-class-test-plan.md)

---

## Manual Testing Checklist

- [x] Single assignment dari detail page siswa
- [x] Bulk assignment dari index page (≥2 siswa)
- [x] Validation error ditampilkan dengan benar
- [x] Success message muncul setelah assign
- [x] History tercatat di tab "Riwayat Kelas"
- [x] Wali kelas tercatat di history
- [x] Modal reset setelah close
- [x] Select dropdown tidak menampilkan "undefined"
- [x] Mobile view: checkbox dan select berfungsi
- [x] Haptic feedback terasa pada mobile

---

## Known Issues & Limitations

| Issue | Impact | Workaround | Fix Status |
|-------|--------|------------|------------|
| ~~Select option shows "undefined"~~ | User cannot select class | ~~Refresh page~~ | ✅ Fixed (defensive checks) |
| Kapasitas kelas tidak di-check | Bisa overfill class | Manual check | ⚠️ Future enhancement |
| Tidak ada confirmation modal | Langsung save | Add confirmation | ⚠️ Future enhancement |
| History tidak sortable | Hard to find recent | Add sorting | ⚠️ Future enhancement |

---

## Future Enhancements

- [ ] Check kapasitas kelas sebelum assign
- [ ] Confirmation modal untuk bulk assignment
- [ ] Filter classes by tingkat di modal
- [ ] Show current class capacity in select label
- [ ] Undo last assignment action
- [ ] Bulk promote (naik tingkat)

---

## Related Documentation

- **API Documentation:** [Student API](../../api/students.md)
- **Test Plan:** [AD03 Test Plan](../../testing/AD03-assign-class-test-plan.md)
- **Database Schema:** [DATABASE.md](../../DATABASE.md)
- **User Journeys:** [Student Management User Journeys](../../guides/student-management-user-journeys.md)

---

## Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2025-12-24 | 1.0.0 | Initial feature implementation | System |
| 2025-12-24 | 1.0.1 | Fix undefined select option | System |

---

**Last Updated:** 2025-12-24
**Maintained By:** Development Team
**Review Cycle:** Every sprint or when feature changes
