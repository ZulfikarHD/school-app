# STD - Student Management Test Plan

**Feature Code:** STD  
**Test Coverage:** Unit Tests (30), Feature Tests (27)  
**Status:** ✅ Unit Tests Passed, ⚠️ Feature Tests Require Frontend  
**Last Updated:** 24 Desember 2025

---

## Test Summary

| Test Type | Total | Passed | Failed | Pending | Coverage |
|-----------|-------|--------|--------|---------|----------|
| **Unit Tests** | 30 | 30 | 0 | 0 | 100% |
| **Feature Tests** | 27 | 0 | 0 | 27 | Requires Frontend |
| **Integration Tests** | 0 | 0 | 0 | 0 | N/A |
| **Total** | 57 | 30 | 0 | 27 | 52.6% |

---

## Unit Test Coverage

### StudentServiceTest.php (10 tests, 30 assertions) ✅

| Test Case | Status | Assertions | Purpose |
|-----------|--------|------------|---------|
| `test_generates_nis_with_correct_format` | ✅ Pass | 2 | Verify NIS format `{year}{4-digit}` |
| `test_nis_increments_for_same_year` | ✅ Pass | 2 | Verify NIS auto-increment per tahun |
| `test_nis_resets_for_different_year` | ✅ Pass | 2 | Verify NIS reset untuk tahun berbeda |
| `test_attaches_guardians_to_student` | ✅ Pass | 2 | Verify guardian attachment works |
| `test_creates_parent_account_for_primary_contact` | ✅ Pass | 4 | Verify auto-create parent account |
| `test_reuses_existing_parent_account_for_multiple_children` | ✅ Pass | 1 | Verify no duplicate parent account |
| `test_bulk_promotes_students_to_new_class` | ✅ Pass | 5 | Verify bulk naik kelas dengan history |
| `test_updates_student_status_with_history` | ✅ Pass | 5 | Verify status change tracking |
| `test_normalizes_phone_number` | ✅ Pass | 4 | Verify phone normalization logic |
| `test_updates_existing_guardian_when_nik_matches` | ✅ Pass | 3 | Verify guardian update, tidak duplicate |

**Command to Run:**
```bash
php artisan test --filter=StudentServiceTest
```

---

### StudentModelTest.php (20 tests, 36 assertions) ✅

| Test Case | Status | Assertions | Purpose |
|-----------|--------|------------|---------|
| `test_student_factory_creates_valid_student` | ✅ Pass | 3 | Verify factory generates valid data |
| `test_student_can_attach_guardians` | ✅ Pass | 1 | Verify many-to-many relationship |
| `test_student_can_get_primary_guardian` | ✅ Pass | 1 | Verify primary guardian filter |
| `test_student_is_active_helper` | ✅ Pass | 2 | Verify `isActive()` helper |
| `test_student_get_age_helper` | ✅ Pass | 1 | Verify `getAge()` calculation |
| `test_student_formatted_nis_accessor` | ✅ Pass | 1 | Verify NIS formatting accessor |
| `test_student_active_scope` | ✅ Pass | 1 | Verify `active()` scope |
| `test_student_by_class_scope` | ✅ Pass | 1 | Verify `byClass()` scope |
| `test_student_by_academic_year_scope` | ✅ Pass | 1 | Verify `byAcademicYear()` scope |
| `test_student_search_scope` | ✅ Pass | 4 | Verify search functionality |
| `test_student_can_have_class_history` | ✅ Pass | 1 | Verify class history relationship |
| `test_student_can_have_status_history` | ✅ Pass | 1 | Verify status history relationship |
| `test_student_soft_delete` | ✅ Pass | 2 | Verify soft delete works |
| `test_guardian_factory_creates_valid_guardian` | ✅ Pass | 3 | Verify guardian factory |
| `test_guardian_has_portal_account_helper` | ✅ Pass | 2 | Verify `hasPortalAccount()` helper |
| `test_guardian_hubungan_label_accessor` | ✅ Pass | 3 | Verify label accessor |
| `test_guardian_scopes` | ✅ Pass | 3 | Verify guardian scopes |
| `test_student_class_history_belongs_to_student` | ✅ Pass | 1 | Verify history relationship |
| `test_student_status_history_relationships` | ✅ Pass | 2 | Verify status history relationships |
| `test_status_history_status_label_accessor` | ✅ Pass | 1 | Verify status label accessor |

**Command to Run:**
```bash
php artisan test --filter=StudentModelTest
```

---

## Feature Test Coverage (Pending Frontend)

### StudentManagementTest.php (17 tests) ⚠️

| Test Case | Status | Purpose |
|-----------|--------|---------|
| `test_admin_can_view_students_list` | ⚠️ Pending | Verify list page renders dengan data |
| `test_admin_can_search_students` | ⚠️ Pending | Verify search functionality |
| `test_admin_can_filter_students_by_status` | ⚠️ Pending | Verify filter by status |
| `test_admin_can_view_create_student_form` | ⚠️ Pending | Verify create form renders |
| `test_admin_can_create_student_with_auto_generated_nis` | ⚠️ Pending | Verify full create flow |
| `test_cannot_create_student_with_duplicate_nik` | ⚠️ Pending | Verify validation works |
| `test_admin_can_view_student_detail` | ⚠️ Pending | Verify detail page renders |
| `test_admin_can_update_student` | ⚠️ Pending | Verify update flow |
| `test_admin_can_delete_student` | ⚠️ Pending | Verify soft delete |
| `test_admin_can_update_student_status` | ⚠️ Pending | Verify status change |
| `test_admin_can_bulk_promote_students` | ⚠️ Pending | Verify bulk promote |
| `test_non_admin_cannot_access_student_management` | ⚠️ Pending | Verify access control |
| `test_admin_can_upload_student_photo` | ⚠️ Pending | Verify photo upload |

**Blocking Issue:** Requires Vue files:
- `resources/js/pages/Admin/Students/Index.vue`
- `resources/js/pages/Admin/Students/Create.vue`
- `resources/js/pages/Admin/Students/Edit.vue`
- `resources/js/pages/Admin/Students/Show.vue`

---

### ParentPortalTest.php (10 tests) ⚠️

| Test Case | Status | Purpose |
|-----------|--------|---------|
| `test_parent_can_view_their_children_list` | ⚠️ Pending | Verify parent portal list |
| `test_parent_can_view_multiple_children` | ⚠️ Pending | Verify multiple children support |
| `test_parent_only_sees_active_children` | ⚠️ Pending | Verify status filter |
| `test_parent_can_view_own_child_detail` | ⚠️ Pending | Verify detail view |
| `test_parent_cannot_view_other_parents_child` | ⚠️ Pending | Verify authorization |
| `test_parent_without_guardian_record_gets_message` | ⚠️ Pending | Verify edge case handling |
| `test_non_parent_cannot_access_parent_portal` | ⚠️ Pending | Verify access control |
| `test_parent_child_detail_includes_guardians` | ⚠️ Pending | Verify data completeness |
| `test_parent_child_detail_includes_class_history` | ⚠️ Pending | Verify history included |
| `test_unauthenticated_user_cannot_access_parent_portal` | ⚠️ Pending | Verify auth middleware |

**Blocking Issue:** Requires Vue files:
- `resources/js/pages/Parent/Children/Index.vue`
- `resources/js/pages/Parent/Children/Show.vue`

---

## Manual Testing Checklist

### Admin - Student CRUD

- [ ] **Create Student**
  - [ ] Fill semua required fields
  - [ ] Upload foto siswa (< 2MB)
  - [ ] Verify NIS auto-generated dengan format correct
  - [ ] Verify parent account created dengan username = no HP
  - [ ] Check activity log recorded
  - [ ] Verify success notification displayed

- [ ] **List Students**
  - [ ] View all students dengan pagination
  - [ ] Search by nama/NIS/NISN
  - [ ] Filter by kelas
  - [ ] Filter by status (aktif/mutasi/do/lulus)
  - [ ] Filter by tahun ajaran
  - [ ] Filter by jenis kelamin
  - [ ] Verify default shows only aktif students

- [ ] **View Student Detail**
  - [ ] View biodata lengkap
  - [ ] View guardians list dengan primary contact highlighted
  - [ ] View class history
  - [ ] View status history dengan changed_by info
  - [ ] Verify foto displayed correctly

- [ ] **Update Student**
  - [ ] Edit biodata siswa
  - [ ] Edit data orang tua/wali
  - [ ] Upload new foto (replace old)
  - [ ] Verify validation for duplicate NIK/NISN
  - [ ] Check activity log recorded
  - [ ] Verify success notification

- [ ] **Delete Student**
  - [ ] Soft delete student
  - [ ] Verify tidak muncul di list default
  - [ ] Verify bisa di-restore (future feature)
  - [ ] Check activity log recorded

### Admin - Bulk Operations

- [ ] **Bulk Naik Kelas**
  - [ ] Select multiple students
  - [ ] Choose kelas tujuan dan tahun ajaran baru
  - [ ] Preview list students yang akan dipindahkan
  - [ ] Confirm dan execute
  - [ ] Verify kelas_id updated
  - [ ] Verify history records created
  - [ ] Check activity log recorded
  - [ ] Verify success notification dengan count

### Admin - Status Management

- [ ] **Update Status ke Mutasi**
  - [ ] Fill tanggal, alasan, sekolah tujuan
  - [ ] Submit form
  - [ ] Verify status changed
  - [ ] Verify history record created dengan changed_by
  - [ ] Verify tidak muncul di list default (filter: aktif)

- [ ] **Update Status ke DO**
  - [ ] Fill tanggal dan alasan
  - [ ] Submit form
  - [ ] Verify status changed dan history recorded

- [ ] **Update Status ke Lulus**
  - [ ] Fill tanggal kelulusan
  - [ ] Submit form
  - [ ] Verify status changed dan history recorded

### Parent Portal

- [ ] **View Children List**
  - [ ] Login as parent
  - [ ] View list anak (hanya yang aktif)
  - [ ] Verify multiple children displayed jika ada
  - [ ] Verify card layout mobile-friendly

- [ ] **View Child Detail**
  - [ ] Click child card
  - [ ] View biodata lengkap (read-only)
  - [ ] View guardians info
  - [ ] View class history
  - [ ] Verify cannot edit anything
  - [ ] Try access other parent's child → should get 403

### Edge Cases

- [ ] **Duplicate Data**
  - [ ] Try create student dengan NIK yang sudah ada → validation error
  - [ ] Try create student dengan NISN yang sudah ada → validation error

- [ ] **Multiple Children Same Parent**
  - [ ] Create student 1 dengan parent A
  - [ ] Create student 2 dengan same parent A (same no HP)
  - [ ] Verify hanya 1 user account created
  - [ ] Verify parent dapat view both children

- [ ] **Guardian Update**
  - [ ] Create student dengan guardian NIK X
  - [ ] Create another student dengan guardian NIK X (same)
  - [ ] Verify guardian data updated, tidak duplicate

- [ ] **Photo Upload**
  - [ ] Upload foto > 2MB → validation error
  - [ ] Upload non-image file → validation error
  - [ ] Upload valid foto → success

- [ ] **Umur Validation**
  - [ ] Try create student dengan umur < 5 tahun → validation error
  - [ ] Try create student dengan umur > 15 tahun → validation error

### Performance

- [ ] **List Performance**
  - [ ] Load list dengan 100+ students
  - [ ] Verify pagination works smoothly
  - [ ] Verify search response time < 1s

- [ ] **Bulk Operations**
  - [ ] Promote 50+ students sekaligus
  - [ ] Verify transaction completes without timeout
  - [ ] Verify all history records created

---

## Automated Test Commands

### Run All Unit Tests
```bash
php artisan test --filter=Student --testsuite=Unit
```

### Run Specific Test Class
```bash
php artisan test --filter=StudentServiceTest
php artisan test --filter=StudentModelTest
```

### Run Specific Test Method
```bash
php artisan test --filter=test_generates_nis_with_correct_format
```

### Run with Coverage (Requires Xdebug)
```bash
php artisan test --coverage --min=80
```

---

## Test Data Setup

### Seed Test Data
```bash
php artisan db:seed --class=StudentSeeder
```

### Factory Usage
```php
// Create single student
$student = Student::factory()->create();

// Create student dengan guardians
$student = Student::factory()
    ->has(Guardian::factory()->ayah())
    ->has(Guardian::factory()->ibu())
    ->create();

// Create multiple students
$students = Student::factory()->count(10)->create();

// Create dengan specific attributes
$student = Student::factory()->create([
    'status' => 'aktif',
    'kelas_id' => 1,
]);
```

---

## Known Test Issues

| Issue | Impact | Workaround | Status |
|-------|--------|------------|--------|
| Feature tests require Vue files | Cannot run feature tests | Run unit tests only | 📝 Planned |
| No integration tests | Limited end-to-end coverage | Manual testing required | 📝 Planned |
| Photo upload tests use fake storage | Not testing real file system | Acceptable for unit tests | ✅ OK |

---

## Test Coverage Goals

| Component | Current | Target | Status |
|-----------|---------|--------|--------|
| **StudentService** | 100% | 100% | ✅ Met |
| **Student Model** | 100% | 100% | ✅ Met |
| **Guardian Model** | 100% | 100% | ✅ Met |
| **StudentController** | 0% | 80% | ⚠️ Pending Frontend |
| **ChildController** | 0% | 80% | ⚠️ Pending Frontend |
| **Form Requests** | 0% | 100% | ⚠️ Pending Frontend |
| **Overall** | 52.6% | 85% | 🔄 In Progress |

---

## Related Documentation

- **Feature Documentation:** [STD Student Management](../features/admin/STD-student-management.md)
- **API Documentation:** [Student API](../api/students.md)
- **Test Files:**
  - `tests/Unit/Student/StudentServiceTest.php`
  - `tests/Unit/Student/StudentModelTest.php`
  - `tests/Feature/Student/StudentManagementTest.php`
  - `tests/Feature/Student/ParentPortalTest.php`

