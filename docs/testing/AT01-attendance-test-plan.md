# Test Plan: AT01 - Attendance Foundation

> **Feature:** AT01 | **Sprint:** 1 | **Status:** Partial (8/33 tests complete)

---

## Test Summary

| Category | Total | Passed | Failed | Pending |
|----------|-------|--------|--------|---------|
| Student Attendance | 8 | 8 ✅ | 0 | 0 |
| Leave Requests | 10 | 0 | 0 | 10 📝 |
| Teacher Clock | 6 | 0 | 0 | 6 📝 |
| Subject Attendance | 5 | 0 | 0 | 5 📝 |
| Teacher Leave | 4 | 0 | 0 | 4 📝 |
| **TOTAL** | **33** | **8** | **0** | **25** |

---

## Automated Tests

### 1. Student Attendance Tests (`StudentAttendanceTest.php`)

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| SAT-001 | Teacher dapat record daily attendance | 3 records created, status correct | ✅ Pass |
| SAT-002 | Teacher tidak bisa input kelas lain | 403 Forbidden, 0 records | ✅ Pass |
| SAT-003 | Duplicate attendance dicegah | Skip duplicate, 1 record only | ✅ Pass |
| SAT-004 | Tidak bisa input tanggal masa depan | 422 validation error | ✅ Pass |
| SAT-005 | Validasi status H/I/S/A only | 422 error untuk status invalid | ✅ Pass |
| SAT-006 | Parent tidak bisa record attendance | 403 Forbidden | ✅ Pass |
| SAT-007 | Guest tidak bisa akses routes | 401 Unauthorized | ✅ Pass |
| SAT-008 | Minimal 1 student required | 422 validation error | ✅ Pass |

**Run Command:**
```bash
php artisan test --filter=StudentAttendanceTest
```

---

### 2. Leave Request Tests (`LeaveRequestTest.php`) - 📝 TO BE CREATED

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| LRT-001 | Parent submit izin untuk anak | Record created, status PENDING | 📝 Pending |
| LRT-002 | Parent tidak bisa submit untuk anak lain | 403 Forbidden | 📝 Pending |
| LRT-003 | Teacher approve izin | Status = APPROVED, attendance created | 📝 Pending |
| LRT-004 | Teacher reject izin | Status = REJECTED, no attendance | 📝 Pending |
| LRT-005 | Approved leave auto-update attendance | StudentAttendance records dengan status I/S | 📝 Pending |
| LRT-006 | Date range creates multiple records | 5 hari = 5 attendance (skip weekend) | 📝 Pending |
| LRT-007 | Attachment upload works | File tersimpan di storage | 📝 Pending |
| LRT-008 | Rejection reason required | 422 if reject without reason | 📝 Pending |
| LRT-009 | Admin dapat approve semua | Admin bisa approve any class | 📝 Pending |
| LRT-010 | Validation date range | tanggal_selesai >= tanggal_mulai | 📝 Pending |

---

### 3. Teacher Clock Tests (`TeacherClockTest.php`) - 📝 TO BE CREATED

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| TCT-001 | Clock in dengan GPS works | Record created, coordinates saved | 📝 Pending |
| TCT-002 | Tidak bisa clock in 2x | 400 error "sudah clock in" | 📝 Pending |
| TCT-003 | Tidak bisa clock out sebelum in | 404 error "belum clock in" | 📝 Pending |
| TCT-004 | Lateness detected correctly | is_late = true if > 07:30 | 📝 Pending |
| TCT-005 | Duration calculated | clock_out - clock_in in hours/minutes | 📝 Pending |
| TCT-006 | GPS validation works | 422 if lat/lng out of range | 📝 Pending |

---

### 4. Subject Attendance Tests (`SubjectAttendanceTest.php`) - 📝 TO BE CREATED

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| SUBT-001 | Record subject attendance | Record created dengan subject_id | 📝 Pending |
| SUBT-002 | Teacher must teach subject | 403 if not teaching | 📝 Pending |
| SUBT-003 | Duplicate prevention | Skip jika sudah ada | 📝 Pending |
| SUBT-004 | Jam_ke validation | 422 if < 1 or > 10 | 📝 Pending |
| SUBT-005 | Get teacher schedule | Return subjects taught | 📝 Pending |

---

### 5. Teacher Leave Tests (`TeacherLeaveTest.php`) - 📝 TO BE CREATED

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| TLT-001 | Teacher submit leave | Record created PENDING | 📝 Pending |
| TLT-002 | Admin approve leave | Status = APPROVED | 📝 Pending |
| TLT-003 | Principal approve leave | Status = APPROVED | 📝 Pending |
| TLT-004 | Teacher cannot approve others | 403 Forbidden | 📝 Pending |

---

## Manual QA Checklist

### Pre-Testing Setup
- [ ] Database migrated: `php artisan migrate`
- [ ] Subjects seeded: 11 mata pelajaran exist
- [ ] Test users created: TEACHER, PARENT, ADMIN roles
- [ ] Test students assigned to classes
- [ ] Teacher assigned to subjects via `teacher_subjects`

### Student Attendance Flow
- [ ] Teacher dapat akses `/teacher/attendance`
- [ ] Form daily attendance muncul
- [ ] Select kelas menampilkan siswa correct
- [ ] Submit attendance berhasil
- [ ] Duplicate attendance di-prevent
- [ ] Validation error messages dalam Bahasa Indonesia
- [ ] Success message muncul

### Leave Request Flow (Parent)
- [ ] Parent dapat akses `/parent/leave-requests`
- [ ] Form create muncul dengan list anak
- [ ] Upload attachment works (PDF/JPG max 2MB)
- [ ] Submit berhasil, status = PENDING
- [ ] Parent hanya lihat leave requests anaknya sendiri

### Leave Verification Flow (Teacher)
- [ ] Teacher dapat akses `/teacher/leave-requests`
- [ ] List hanya untuk siswa di kelasnya
- [ ] Approve button works
- [ ] Reject dengan reason works
- [ ] Auto-create attendance setelah approve

### Clock In/Out Flow
- [ ] Teacher dapat clock in dengan GPS
- [ ] Cannot clock in 2x same day
- [ ] Clock status widget shows current state
- [ ] Clock out works after clock in
- [ ] Duration calculated correctly
- [ ] Late detection works (after 07:30)

### Admin Reports
- [ ] Admin dapat akses `/admin/attendance/students`
- [ ] Filter by class works
- [ ] Filter by date works
- [ ] Filter by status works
- [ ] Pagination works
- [ ] Teacher attendance report accessible

---

## Performance Testing

| Test | Metric | Target | Status |
|------|--------|--------|--------|
| Daily attendance 30 students | Response time | < 1s | ⏳ To test |
| Leave request list | DB queries | < 10 queries | ⏳ To test |
| Admin report with filters | Response time | < 2s | ⏳ To test |
| Clock in/out API | Response time | < 500ms | ⏳ To test |

---

## Security Testing

| Test | Scenario | Expected Result | Status |
|------|----------|-----------------|--------|
| Role Authorization | Parent access teacher routes | 403 Forbidden | ⏳ To test |
| CSRF Protection | POST without token | 419 Page Expired | ⏳ To test |
| SQL Injection | Malicious input in search | Sanitized, no injection | ⏳ To test |
| File Upload | Upload .php file | Rejected (MIME validation) | ⏳ To test |
| GPS Spoofing | Coordinates di-store untuk audit | Coordinates saved | ⏳ To test |

---

## Edge Case Testing

| Test | Scenario | Expected Result | Status |
|------|----------|-----------------|--------|
| Weekend Leave | Izin Sabtu-Minggu 2 hari | 0 attendance records (skip weekend) | ⏳ To test |
| Past Date Leave | Approve izin tanggal kemarin | Backfill attendance record | ⏳ To test |
| Concurrent Clock In | 2 teachers clock in same time | Both successful, no conflict | ⏳ To test |
| Empty Class | Input attendance kelas kosong | Validation error or skip | ⏳ To test |
| Long Leave Period | Izin 30 hari | 21 attendance records (Mon-Fri only) | ⏳ To test |

---

## Regression Testing Checklist

Run after ANY code changes:

- [ ] `php artisan test` - All tests pass
- [ ] `vendor/bin/pint` - Code style clean
- [ ] `php artisan route:list` - No route conflicts
- [ ] Database rollback + migrate works
- [ ] Seeder runs without errors

---

## Bug Tracking

| Bug ID | Description | Severity | Status | Fixed In |
|--------|-------------|----------|--------|----------|
| - | No known bugs | - | - | - |

---

## Test Environment

| Component | Version | Status |
|-----------|---------|--------|
| PHP | 8.4.1 | ✅ |
| Laravel | 12.x | ✅ |
| Database | MySQL/PostgreSQL | ✅ |
| PHPUnit | 11.x | ✅ |

---

## Next Steps

1. **Create Remaining Test Files:**
   - `LeaveRequestTest.php` (10 tests)
   - `TeacherClockTest.php` (6 tests)
   - `SubjectAttendanceTest.php` (5 tests)
   - `TeacherLeaveTest.php` (4 tests)

2. **Manual Testing:**
   - UI flow testing (requires Sprint 2 UI)
   - Cross-browser testing
   - Mobile responsiveness

3. **Performance Optimization:**
   - Add query caching where appropriate
   - Optimize N+1 queries
   - Load testing with 100+ concurrent users

---

*Last Updated: 2025-12-24*
*Sprint 1 Status: 8/33 automated tests complete*
