# Test Plan: AD04 - Bulk Promote Students

> **Feature Code:** AD04 | **Feature:** Bulk Promote Students (Naik Kelas Massal)
> **Test Type:** Manual + Automated | **Priority:** High
> **Related Doc:** [AD04 Feature Doc](../features/admin/AD04-bulk-promote-students.md)

---

## Test Environment Setup

### Prerequisites
- [x] Database seeded with sample classes (tingkat 1-6, multiple tahun_ajaran)
- [x] Database seeded with sample students assigned to classes
- [x] Test user with ADMIN or SUPERADMIN role
- [x] Frontend build completed: `yarn run build`
- [x] Backend server running
- [x] Browser DevTools open for debugging

### Test Data Requirements

```sql
-- Verify test data exists
SELECT * FROM classes WHERE tahun_ajaran IN ('2024/2025', '2025/2026') AND is_active = 1;
SELECT * FROM students WHERE kelas_id IS NOT NULL LIMIT 10;
SELECT * FROM student_class_history ORDER BY created_at DESC LIMIT 5;
```

---

## Manual Test Cases

### Test Suite 1: Wizard Navigation

#### TC-AD04-001: Access Promote Page
**Priority:** Critical | **Type:** Smoke Test

**Steps:**
1. Login as ADMIN user
2. Navigate to `/admin/students`
3. Click "Naik Kelas" button (sky blue, graduation cap icon)

**Expected Result:**
- ✅ Redirected to `/admin/students/promote`
- ✅ Page title: "Naik Kelas Siswa"
- ✅ Info card visible with panduan
- ✅ Wizard Step 1 active (highlighted)
- ✅ Steps 2 & 3 grayed out

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-002: Step 1 - Form Validation
**Priority:** High | **Type:** Functional

**Steps:**
1. Open promote page
2. Do NOT select any tahun ajaran
3. Try to click "Lanjut" button

**Expected Result:**
- ✅ Button disabled (opacity 50%, cursor not-allowed)
- ✅ No step transition occurs

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-003: Step 1 to Step 2 - Valid Navigation
**Priority:** Critical | **Type:** Functional

**Steps:**
1. Select "Tahun Ajaran Asal": 2024/2025
2. Select "Tahun Ajaran Tujuan": 2025/2026
3. Click "Lanjut" button

**Expected Result:**
- ✅ Haptic feedback triggered (light vibration)
- ✅ Step 2 becomes active (emerald border)
- ✅ Step 1 shows checkmark icon
- ✅ "Kelas Asal" dropdown populated with classes from 2024/2025
- ✅ "Kelas Tujuan" dropdown disabled until Kelas Asal selected

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-004: Step 2 - Kelas Tujuan Auto-Filter
**Priority:** High | **Type:** Functional

**Steps:**
1. Navigate to Step 2
2. Select "Kelas Asal": 1A (tingkat 1)
3. Observe "Kelas Tujuan" dropdown options

**Expected Result:**
- ✅ Dropdown enabled
- ✅ Only shows classes with `tingkat = 2` (2A, 2B, 2C, etc.)
- ✅ No classes with tingkat 1, 3, 4, 5, 6 visible

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-005: Step 2 - No Target Class Available
**Priority:** Medium | **Type:** Edge Case

**Steps:**
1. Navigate to Step 2
2. Select "Kelas Asal": 6A (tingkat 6, highest level)
3. Observe "Kelas Tujuan" dropdown

**Expected Result:**
- ✅ Dropdown shows "Pilih kelas tujuan" placeholder
- ✅ Warning message displayed: "Tidak ada kelas tujuan yang tersedia untuk tingkat berikutnya pada tahun ajaran tujuan."
- ✅ "Lanjut" button disabled

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-006: Step 2 to Step 3 - Fetch Students
**Priority:** Critical | **Type:** Functional

**Steps:**
1. Navigate to Step 2
2. Select "Kelas Asal": 1A
3. Select "Kelas Tujuan": 2A
4. Click "Lanjut" button
5. Observe Step 3 content

**Expected Result:**
- ✅ Loading spinner visible (Loader2 icon, animate-spin)
- ✅ After ~500ms, student list appears
- ✅ "Select All" checkbox at top
- ✅ Each student row shows: NIS, Nama Lengkap, Jenis Kelamin
- ✅ All students checked by default
- ✅ Counter shows: "X dari X siswa" (all selected)

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

### Test Suite 2: Student Selection

#### TC-AD04-007: Select All Functionality
**Priority:** High | **Type:** Functional

**Steps:**
1. Navigate to Step 3 with students loaded
2. Uncheck "Pilih Semua Siswa" checkbox
3. Check it again

**Expected Result:**
- ✅ First uncheck: All student checkboxes unchecked
- ✅ Counter shows: "0 dari X siswa"
- ✅ Second check: All student checkboxes checked again
- ✅ Counter shows: "X dari X siswa"
- ✅ Haptic feedback on each toggle

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-008: Individual Student Selection
**Priority:** High | **Type:** Functional

**Steps:**
1. Navigate to Step 3 with 10 students
2. Uncheck student #3
3. Uncheck student #7
4. Check student #3 again

**Expected Result:**
- ✅ After step 2: Counter shows "9 dari 10 siswa"
- ✅ After step 3: Counter shows "8 dari 10 siswa"
- ✅ After step 4: Counter shows "9 dari 10 siswa"
- ✅ Row highlight (emerald bg) reflects checked state

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-009: Submit with Zero Selection
**Priority:** High | **Type:** Validation

**Steps:**
1. Navigate to Step 3
2. Uncheck all students (or click "Pilih Semua" to deselect)
3. Try to click "Proses Naik Kelas" button

**Expected Result:**
- ✅ Button disabled (opacity 50%)
- ✅ No API call triggered
- ✅ No confirmation modal appears

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

### Test Suite 3: Submit & Confirmation

#### TC-AD04-010: Confirmation Dialog
**Priority:** Critical | **Type:** Functional

**Steps:**
1. Navigate to Step 3 with 5 students selected
2. Click "Proses Naik Kelas" button
3. Observe confirmation dialog

**Expected Result:**
- ✅ Modal appears with title: "Konfirmasi Naik Kelas"
- ✅ Message shows: "Yakin ingin menaikkan **5 siswa** dari kelas **1A** ke kelas **2A**?"
- ✅ "Ya, Proses" button (primary)
- ✅ "Batal" button (secondary)

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-011: Cancel Confirmation
**Priority:** Medium | **Type:** Functional

**Steps:**
1. Trigger confirmation dialog (TC-AD04-010)
2. Click "Batal" button

**Expected Result:**
- ✅ Modal closes
- ✅ No API call made (check Network tab)
- ✅ Step 3 form still intact with selections
- ✅ Can resubmit again

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-012: Successful Bulk Promote
**Priority:** Critical | **Type:** Integration

**Steps:**
1. Trigger confirmation dialog with 5 students
2. Click "Ya, Proses"
3. Observe behavior

**Expected Result:**
- ✅ "Proses Naik Kelas" button shows spinner
- ✅ POST request to `/admin/students/promote` with payload:
  ```json
  {
    "student_ids": [1,2,3,4,5],
    "kelas_id_baru": 12,
    "tahun_ajaran_baru": "2025/2026"
  }
  ```
- ✅ Success response (200 OK)
- ✅ Haptic feedback (heavy vibration)
- ✅ Success toast/modal: "Berhasil menaikkan 5 siswa ke kelas 2A!"
- ✅ Wizard resets to Step 1 (or redirects to student list)

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-013: Submit Failure Handling
**Priority:** High | **Type:** Error Handling

**Steps:**
1. Simulate server error (disconnect backend or cause validation error)
2. Try to submit bulk promote
3. Observe error handling

**Expected Result:**
- ✅ Error modal appears: "Gagal memproses naik kelas" or specific error message
- ✅ Form data preserved (students still selected)
- ✅ User can retry without re-selecting
- ✅ No partial data saved in database

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

### Test Suite 4: Mobile Responsiveness

#### TC-AD04-014: Mobile Layout (320px - 768px)
**Priority:** High | **Type:** UI/UX

**Steps:**
1. Open DevTools, set viewport to iPhone SE (375x667)
2. Navigate through all 3 wizard steps

**Expected Result:**
- ✅ Progress steps stack properly (not horizontal overflow)
- ✅ Form inputs full width with proper spacing
- ✅ Buttons touch-friendly (min 44px height)
- ✅ Student list scrollable with max-height
- ✅ "Naik Kelas" button in Index page shows icon only
- ✅ Text remains legible, no truncation

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-015: Tablet Layout (768px - 1024px)
**Priority:** Medium | **Type:** UI/UX

**Steps:**
1. Set viewport to iPad (768x1024)
2. Navigate through wizard

**Expected Result:**
- ✅ Progress steps horizontal (3 columns)
- ✅ Form inputs have proper max-width
- ✅ Student list shows 2-column if space allows
- ✅ Navigation buttons proper spacing

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

### Test Suite 5: Data Integrity

#### TC-AD04-016: Database Verification - student_class_history
**Priority:** Critical | **Type:** Integration

**Steps:**
1. Note current count: `SELECT COUNT(*) FROM student_class_history;`
2. Perform bulk promote of 5 students (TC-AD04-012)
3. Re-query: `SELECT * FROM student_class_history ORDER BY created_at DESC LIMIT 5;`

**Expected Result:**
- ✅ 5 new rows inserted
- ✅ Each row has: `student_id`, `kelas_id` (new), `tahun_ajaran` (new), `wali_kelas` (auto-detected)
- ✅ `created_at` matches current timestamp

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-017: Database Verification - students.kelas_id
**Priority:** Critical | **Type:** Integration

**Steps:**
1. Before promote: Note `kelas_id` of student with ID=1
   ```sql
   SELECT id, nama_lengkap, kelas_id FROM students WHERE id = 1;
   ```
2. Perform bulk promote including student ID=1 to kelas_id=12
3. Re-query same student

**Expected Result:**
- ✅ `kelas_id` updated from old value to 12
- ✅ Other fields (nama_lengkap, nis) unchanged

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-018: Activity Log Verification
**Priority:** High | **Type:** Audit

**Steps:**
1. Perform bulk promote of 5 students
2. Query: `SELECT * FROM activity_logs WHERE action = 'bulk_promote_students' ORDER BY created_at DESC LIMIT 1;`

**Expected Result:**
- ✅ 1 new row created
- ✅ `user_id` = logged in admin ID
- ✅ `action` = 'bulk_promote_students'
- ✅ `new_values` JSON contains:
  ```json
  {
    "student_count": 5,
    "kelas_id_baru": 12,
    "tahun_ajaran_baru": "2025/2026"
  }
  ```
- ✅ `status` = 'success'

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

### Test Suite 6: Edge Cases & Security

#### TC-AD04-019: Duplicate Student IDs Handling
**Priority:** Medium | **Type:** Edge Case

**Steps:**
1. Open browser console
2. Manually send POST request with duplicate IDs:
   ```javascript
   fetch('/admin/students/promote', {
     method: 'POST',
     headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
     body: JSON.stringify({
       student_ids: [1, 1, 2, 2, 3],
       kelas_id_baru: 12,
       tahun_ajaran_baru: "2025/2026"
     })
   })
   ```

**Expected Result:**
- ✅ Service processes unique IDs only (1, 2, 3)
- ✅ Success response: "3 siswa berhasil dipindahkan"
- ✅ No duplicate history entries

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-020: Unauthorized Access
**Priority:** Critical | **Type:** Security

**Steps:**
1. Login as PARENT or TEACHER role
2. Manually navigate to `/admin/students/promote`

**Expected Result:**
- ✅ Redirected to 403 Forbidden page OR dashboard
- ✅ No promote wizard visible
- ✅ Direct POST request returns 403 error

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

#### TC-AD04-021: CSRF Token Validation
**Priority:** Critical | **Type:** Security

**Steps:**
1. Open browser console
2. Send POST request without CSRF token:
   ```javascript
   fetch('/admin/students/promote', {
     method: 'POST',
     headers: {'Content-Type': 'application/json'},
     body: JSON.stringify({
       student_ids: [1,2,3],
       kelas_id_baru: 12,
       tahun_ajaran_baru: "2025/2026"
     })
   })
   ```

**Expected Result:**
- ✅ Response: 419 Page Expired (CSRF token mismatch)
- ✅ No data modified in database

**Actual Result:** ___________
**Status:** [ ] Pass [ ] Fail

---

## Automated Test Cases (PHPUnit)

### Test File: `tests/Feature/Student/BulkPromoteTest.php`

```php
<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\StudentClassHistory;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BulkPromoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SchoolClass $sourceClass;
    protected SchoolClass $targetClass;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        
        $this->sourceClass = SchoolClass::factory()->create([
            'tingkat' => 1,
            'nama' => 'A',
            'tahun_ajaran' => '2024/2025',
        ]);
        
        $this->targetClass = SchoolClass::factory()->create([
            'tingkat' => 2,
            'nama' => 'A',
            'tahun_ajaran' => '2025/2026',
        ]);
    }

    /** @test */
    public function admin_can_access_promote_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.promote.page'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Students/Promote')
                ->has('classes')
        );
    }

    /** @test */
    public function non_admin_cannot_access_promote_page()
    {
        $parent = User::factory()->parent()->create();

        $response = $this->actingAs($parent)
            ->get(route('admin.students.promote.page'));

        $response->assertForbidden();
    }

    /** @test */
    public function can_promote_multiple_students_successfully()
    {
        $students = Student::factory()->count(5)->create([
            'kelas_id' => $this->sourceClass->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.promote'), [
                'student_ids' => $students->pluck('id')->toArray(),
                'kelas_id_baru' => $this->targetClass->id,
                'tahun_ajaran_baru' => '2025/2026',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify students updated
        foreach ($students as $student) {
            $this->assertEquals($this->targetClass->id, $student->fresh()->kelas_id);
        }

        // Verify history created
        $this->assertDatabaseCount('student_class_history', 5);
    }

    /** @test */
    public function validation_fails_if_student_ids_empty()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.promote'), [
                'student_ids' => [],
                'kelas_id_baru' => $this->targetClass->id,
                'tahun_ajaran_baru' => '2025/2026',
            ]);

        $response->assertSessionHasErrors(['student_ids']);
    }

    /** @test */
    public function validation_fails_if_tahun_ajaran_invalid_format()
    {
        $students = Student::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.promote'), [
                'student_ids' => $students->pluck('id')->toArray(),
                'kelas_id_baru' => $this->targetClass->id,
                'tahun_ajaran_baru' => 'invalid-format',
            ]);

        $response->assertSessionHasErrors(['tahun_ajaran_baru']);
    }

    /** @test */
    public function activity_log_created_on_successful_promote()
    {
        $students = Student::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->post(route('admin.students.promote'), [
                'student_ids' => $students->pluck('id')->toArray(),
                'kelas_id_baru' => $this->targetClass->id,
                'tahun_ajaran_baru' => '2025/2026',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'bulk_promote_students',
            'status' => 'success',
        ]);

        $log = ActivityLog::latest()->first();
        $this->assertEquals(3, $log->new_values['student_count']);
    }
}
```

---

## Performance Test Cases

### PT-AD04-001: Load Time - Step 3 Student Fetch
**Target:** ≤ 2 seconds for 50 students

**Steps:**
1. Create class with 50 students
2. Navigate to Step 3
3. Measure time from "Lanjut" click to students render

**Acceptance Criteria:**
- ✅ < 1s: Excellent
- ✅ 1-2s: Good
- ⚠️ 2-3s: Acceptable
- ❌ > 3s: Fail (optimize query)

---

### PT-AD04-002: Submit Processing Time
**Target:** ≤ 5 seconds for 100 students

**Steps:**
1. Select 100 students in Step 3
2. Click submit
3. Measure time until success message

**Acceptance Criteria:**
- ✅ < 3s: Excellent
- ✅ 3-5s: Good
- ⚠️ 5-7s: Acceptable
- ❌ > 7s: Fail (optimize transaction)

---

## Test Summary

### Manual Test Checklist

- [ ] TC-AD04-001: Access Promote Page
- [ ] TC-AD04-002: Step 1 - Form Validation
- [ ] TC-AD04-003: Step 1 to Step 2 Navigation
- [ ] TC-AD04-004: Step 2 - Kelas Tujuan Auto-Filter
- [ ] TC-AD04-005: Step 2 - No Target Class Available
- [ ] TC-AD04-006: Step 2 to Step 3 - Fetch Students
- [ ] TC-AD04-007: Select All Functionality
- [ ] TC-AD04-008: Individual Student Selection
- [ ] TC-AD04-009: Submit with Zero Selection
- [ ] TC-AD04-010: Confirmation Dialog
- [ ] TC-AD04-011: Cancel Confirmation
- [ ] TC-AD04-012: Successful Bulk Promote
- [ ] TC-AD04-013: Submit Failure Handling
- [ ] TC-AD04-014: Mobile Layout (320px - 768px)
- [ ] TC-AD04-015: Tablet Layout (768px - 1024px)
- [ ] TC-AD04-016: Database Verification - History
- [ ] TC-AD04-017: Database Verification - Students
- [ ] TC-AD04-018: Activity Log Verification
- [ ] TC-AD04-019: Duplicate Student IDs Handling
- [ ] TC-AD04-020: Unauthorized Access
- [ ] TC-AD04-021: CSRF Token Validation

### Automated Test Status
- [ ] PHPUnit tests written (7 tests)
- [ ] All tests passing
- [ ] Code coverage ≥ 80%

---

## Known Issues & Limitations

| Issue ID | Description | Workaround | Priority |
|----------|-------------|------------|----------|
| - | No state persistence on page refresh | User must restart wizard | Low |
| - | No preview export to Excel | Manual copy-paste | Low |

---

## Test Sign-Off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| **QA Engineer** | _____________ | _____________ | ______ |
| **Developer** | _____________ | _____________ | ______ |
| **Product Owner** | _____________ | _____________ | ______ |

---

**Last Updated:** 2025-12-24
**Test Plan Version:** 1.0.0
