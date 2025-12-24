# Feature: AD04 - Bulk Promote Students (Naik Kelas Massal)

> **Code:** AD04 | **Priority:** High | **Status:** ✅ Complete
> **Sprint:** 2-3 | **Menu:** Admin > Data Siswa > Naik Kelas

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=admin/students/promote`
- [x] Service methods match Controller calls (bulkPromoteStudents)
- [x] Tested with `php artisan tinker` - ✅ Method exists
- [x] Vue pages exist for Inertia renders (Promote.vue, PromoteWizard.vue)
- [x] Migrations applied (classes, student_class_history)
- [x] Following DOCUMENTATION_GUIDE.md template
- [x] Frontend build success: `yarn run build`
- [x] Linter passed for new files
- [x] **All 21 Feature Tests Passing** (BulkPromoteTest)

---

## Overview

Bulk Promote Students merupakan fitur manajemen akademik untuk naik kelas massal yang bertujuan untuk memfasilitasi proses kenaikan kelas siswa secara efisien, yaitu: wizard 3-step yang intuitive untuk memilih tahun ajaran dan kelas, preview siswa dengan checkbox selection untuk fleksibilitas, automatic wali kelas detection dari kelas tujuan, dan batch processing dengan database transaction untuk data integrity.

---

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| AD04-US01 | Admin/TU | Menaikkan multiple siswa ke kelas berikutnya sekaligus | Proses naik kelas efisien (tidak satu-satu) | ✅ Complete |
| AD04-US02 | Admin/TU | Memilih tahun ajaran asal dan tujuan | Dapat promosi untuk tahun ajaran tertentu | ✅ Complete |
| AD04-US03 | Admin/TU | Memilih kelas asal dan kelas tujuan | Kontrol penuh atas perpindahan tingkat | ✅ Complete |
| AD04-US04 | Admin/TU | Melihat preview daftar siswa sebelum proses | Dapat review dan deselect siswa yang tidak naik | ✅ Complete |
| AD04-US05 | Admin/TU | Uncheck siswa tertentu dari daftar preview | Menangani siswa tinggal kelas tanpa menghapus data | ✅ Complete |
| AD04-US06 | System | Auto-filter kelas tujuan berdasarkan tingkat+1 | Mencegah error promosi ke tingkat yang salah | ✅ Complete |
| AD04-US07 | System | Mencatat wali kelas secara otomatis di history | History lengkap dengan data wali kelas saat promosi | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| AD04-BR01 | Tahun ajaran tujuan harus berbeda dari tahun asal | Frontend validation: disable same year selection |
| AD04-BR02 | Kelas tujuan harus tingkat+1 dari kelas asal | Frontend: filter `tingkat = sourceClass.tingkat + 1` |
| AD04-BR03 | Minimal 1 siswa harus dipilih untuk diproses | Frontend: button disabled jika selection empty |
| AD04-BR04 | Tahun ajaran format `YYYY/YYYY` (e.g., 2025/2026) | Backend validation: `regex:/^\d{4}\/\d{4}$/` |
| AD04-BR05 | Siswa yang sama tidak boleh duplikat dalam 1 batch | Service: use array unique student_ids |
| AD04-BR06 | Semua promosi dalam 1 batch harus atomic | Service: `DB::beginTransaction()` + rollback on error |
| AD04-BR07 | History entry dibuat untuk setiap siswa yang dipromote | Service loop through students, insert history |
| AD04-BR08 | Activity log mencatat jumlah siswa yang dipromote | ActivityLog created with student_count metadata |

---

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| **Controller** | `app/Http/Controllers/Admin/StudentController.php` | Handle HTTP request for promote page & submit |
| **Controller Method** | `showPromotePage()` | Render promote page with classes data |
| **Controller Method** | `promote()` | Process bulk promote request |
| **Form Request** | `app/Http/Requests/Admin/BulkPromoteRequest.php` | Validate bulk promote payload |
| **Service** | `app/Services/StudentService.php` | Business logic: `bulkPromoteStudents()` |
| **Model** | `app/Models/Student.php` | Student entity with `kelas()` relation |
| **Model** | `app/Models/SchoolClass.php` | SchoolClass entity with scopes |
| **Model** | `app/Models/StudentClassHistory.php` | History tracking model |
| **Vue Page** | `resources/js/pages/Admin/Students/Promote.vue` | Main promote page wrapper |
| **Vue Component** | `resources/js/components/features/students/PromoteWizard.vue` | 3-step wizard component |
| **Route** | `routes/web.php` | Define GET & POST routes for promote |
| **Wayfinder** | `resources/js/routes/admin/students/promote/index.ts` | Type-safe route helpers |

### Routes

| Method | URI | Controller@Method | Name | Status |
|--------|-----|-------------------|------|--------|
| GET | `admin/students/promote` | `Admin\StudentController@showPromotePage` | `admin.students.promote.page` | ✅ Active |
| POST | `admin/students/promote` | `Admin\StudentController@promote` | `admin.students.promote` | ✅ Active |

> 📌 Routes verified via `php artisan route:list --path=admin/students/promote`

### Request Payload (POST /admin/students/promote)

```json
{
    "student_ids": [1, 2, 3, 5, 8],
    "kelas_id_baru": 12,
    "tahun_ajaran_baru": "2025/2026",
    "wali_kelas": "Ibu Siti Nurhaliza" // Optional, auto-detected from SchoolClass
}
```

### Response (Success)

```json
{
    "message": "5 siswa berhasil dipindahkan ke kelas baru.",
    "success": true
}
```

### Service Method: `bulkPromoteStudents()`

**Location:** `app/Services/StudentService.php:210-246`

**Signature:**
```php
public function bulkPromoteStudents(
    array $studentIds,
    int $kelasIdBaru,
    string $tahunAjaranBaru,
    ?string $waliKelas = null
): int
```

**Logic Flow:**
1. Begin database transaction
2. Fetch students by IDs with eager loading
3. Loop through each student:
   - Create `StudentClassHistory` entry with new class data
   - Update `students.kelas_id` to new class
   - Increment promoted counter
4. Commit transaction if success, rollback if error
5. Return count of promoted students

**Error Handling:**
- Catches all exceptions
- Performs automatic rollback
- Re-throws exception for controller to handle

---

## User Interface

### 3-Step Wizard Flow

#### Step 1: Pilih Tahun Ajaran
- **Input 1:** Tahun Ajaran Asal (dropdown)
  - Options: All unique `tahun_ajaran` from `classes`
- **Input 2:** Tahun Ajaran Tujuan (dropdown)
  - Options: All unique `tahun_ajaran` from `classes`
- **Validation:** Both fields required
- **Next Button:** Enabled when both selected

#### Step 2: Pilih Kelas
- **Input 1:** Kelas Asal (dropdown)
  - Options: Classes filtered by `tahun_ajaran_asal`
  - Format: "1A", "2B", etc.
- **Input 2:** Kelas Tujuan (dropdown)
  - Options: Classes filtered by `tahun_ajaran_tujuan` AND `tingkat = sourceClass.tingkat + 1`
  - Auto-disabled if no source class selected
- **Validation:** Both fields required
- **Next Button:** Enabled when both selected

#### Step 3: Preview & Konfirmasi
- **Display:** Table of students from source class
- **Features:**
  - "Select All" checkbox at top
  - Individual checkbox per student row
  - Shows: NIS, Nama Lengkap, Jenis Kelamin
  - Real-time counter: "X dari Y siswa dipilih"
- **Loading State:** Skeleton loader while fetching students
- **Empty State:** "Tidak ada siswa di kelas ini" if empty
- **Submit Button:** 
  - Label: "Proses Naik Kelas"
  - Disabled if no students selected
  - Shows spinner during submission

### Navigation Button States

| Step | Back Button | Next/Submit Button |
|------|-------------|-------------------|
| 1 | Hidden | "Lanjut" - Enabled when form valid |
| 2 | Visible | "Lanjut" - Enabled when form valid |
| 3 | Visible | "Proses Naik Kelas" - Enabled when selection > 0 |

---

## Edge Cases & Error Handling

| Scenario | Behavior | Status |
|----------|----------|--------|
| No classes available for target year | Show warning message in Step 2 | ✅ Handled |
| No classes at next tingkat level | Display "Tidak ada kelas tujuan yang tersedia" | ✅ Handled |
| Source class has 0 students | Show empty state in Step 3 | ✅ Handled |
| API fetch students fails | Show error modal: "Gagal memuat data siswa" | ✅ Handled |
| User unchecks all students | Submit button disabled | ✅ Handled |
| Submit fails (server error) | Show error modal, keep form state | ✅ Handled |
| User clicks Back after Step 3 | Return to Step 2, preserve form data | ✅ Handled |
| User refreshes page mid-wizard | Form resets to Step 1 (no state persist) | ⚠️ By Design |
| Duplicate student_ids in request | Service processes unique IDs only | ✅ Handled |
| Database transaction rollback | No partial data saved, error thrown | ✅ Handled |

---

## Security Considerations

| Security Concern | Mitigation |
|------------------|------------|
| **Unauthorized access** | Route protected by `auth` + `role:SUPERADMIN,ADMIN` middleware |
| **Mass assignment** | FormRequest validates `student_ids`, `kelas_id_baru`, `tahun_ajaran_baru` only |
| **SQL injection** | Using Eloquent ORM with parameterized queries |
| **CSRF attacks** | Laravel CSRF token validation on POST requests |
| **Invalid student IDs** | Service fetches by `whereIn()`, non-existent IDs ignored |
| **Activity logging** | All promote actions logged to `activity_logs` table |

---

## Performance Considerations

| Aspect | Implementation | Rationale |
|--------|----------------|-----------|
| **Bulk operations** | Single transaction for all students | Reduces DB round trips |
| **Eager loading** | `Student::with(['kelas'])` in service | Prevents N+1 queries |
| **Frontend pagination** | Fetch with `per_page=1000` for preview | Reasonable limit for class size |
| **Async validation** | No real-time API calls during form fill | Reduces server load |
| **Loading states** | Skeleton UI + spinner during fetch | Perceived performance |

---

## Integration Points

### Upstream Dependencies
- **SchoolClass model:** Requires active classes with proper `tahun_ajaran` & `tingkat`
- **Student model:** Requires valid `kelas_id` foreign key

### Downstream Impact
- **Student Class History:** Creates new entries for audit trail
- **Activity Logs:** Logs bulk promote action with metadata
- **Student Index Page:** Refreshed data after successful promote

---

## Testing

**Automated Tests:** 21 Feature Tests - 100% Passing ✅

**Test File:** `tests/Feature/Student/BulkPromoteTest.php`

**Test Coverage:**
- ✅ Admin dapat view promote page
- ✅ Admin dapat promote multiple students
- ✅ Admin dapat promote single student
- ✅ Validation: student_ids required, must be array, min:1
- ✅ Validation: kelas_id_baru required
- ✅ Validation: tahun_ajaran_baru required dengan format valid
- ✅ Validation: wali_kelas optional
- ✅ Authorization: Non-admin & unauthenticated blocked
- ✅ History records created untuk setiap student
- ✅ Activity log dengan metadata lengkap
- ✅ Large batch handling (50 students)
- ✅ Duplicate student_ids handling
- ✅ Superadmin access granted
- ✅ Parent access blocked

**Run Tests:**
```bash
php artisan test --filter=BulkPromoteTest
```

---

## Related Documentation

- **Feature Documentation (Prerequisite):** [AD03 - Assign Student to Class](./AD03-assign-student-to-class.md)
- **Test Plan:** [AD04 Test Plan](../../testing/AD04-bulk-promote-test-plan.md)
- **User Journeys:** [Student Management User Journeys](../../guides/student-management-user-journeys.md) (Journey 9)
- **API Documentation:** [Students API](../../api/students.md)
- **Student Management Overview:** [Student Management](./STD-student-management.md)

---

## Future Enhancements

| Enhancement | Description | Priority |
|-------------|-------------|----------|
| **Bulk Preview Export** | Export preview list to Excel before processing | Medium |
| **Email Notifications** | Send email to parents after bulk promote | Low |
| **Undo Functionality** | Rollback last bulk promote within 5 minutes | Low |
| **History Comparison** | Show "before vs after" class comparison | Medium |
| **Scheduled Promote** | Schedule promote to run at specific date/time | Low |

---

## Changelog

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0.0 | 2025-12-24 | Initial implementation of bulk promote feature | AI Assistant |

---

**Last Updated:** 2025-12-24
**Maintainer:** Development Team
**Status:** ✅ Production Ready
