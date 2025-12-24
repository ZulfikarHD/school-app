# Sprint 1: Attendance Foundation Strategy

## Executive Summary

Sprint 1 adalah **pure backend foundation** - zero UI. Fokus membangun robust data layer untuk mendukung 5 fitur utama: Student Daily Attendance, Subject Attendance, Leave Requests, Teacher Clock In/Out, dan Teacher Leave Management.

**Total Estimation:** 45 jam (~7 hari kerja)

---

## Current State Analysis

### ✅ Yang Sudah Ada (Reusable)

- Users table (role: TEACHER, PARENT, ADMIN, PRINCIPAL)
- Students table + Guardian relationships  
- Classes table + wali kelas
- Service Layer pattern (StudentService)
- Form Request validation pattern
- Proper PHPDoc Indonesian comments

### ❌ Yang Harus Dibangun

**Database (6 tables):**
- subjects + teacher_subjects pivot
- student_attendances
- subject_attendances  
- leave_requests
- teacher_attendances
- teacher_leaves

**Models (6 new + 3 updated):**
- Subject, StudentAttendance, SubjectAttendance, LeaveRequest, TeacherAttendance, TeacherLeave
- Update: Student, User, SchoolClass (add relationships)

**Service Layer:**
- AttendanceService dengan 20+ core methods

**Controllers (7 controllers):**
- Teacher: AttendanceController, SubjectAttendanceController, ClockController, LeaveRequestController
- Parent: LeaveRequestController
- Admin: AttendanceController, TeacherAttendanceController

**Validation (6 Form Requests):**
- StoreStudentAttendanceRequest
- StoreSubjectAttendanceRequest
- StoreLeaveRequestRequest
- ApproveLeaveRequestRequest
- ClockInRequest
- StoreTeacherLeaveRequest

---

## Critical Gaps Identified

### Gap 1: Missing Subjects Table ⚠️

`subject_attendances` memerlukan `subject_id` tapi table `subjects` belum ada.

**Solution:** Include di Sprint 1:
- subjects table (kode_mapel, nama_mapel, tingkat, kategori)
- teacher_subjects pivot table
- SubjectSeeder untuk mata pelajaran standar SD

### Gap 2: Duplicate Prevention Logic

Teacher bisa submit attendance 2x untuk tanggal sama.

**Solution:**
- Unique constraint: `[student_id, tanggal]`
- Service validation sebelum insert
- Form Request custom rules

### Gap 3: Leave-to-Attendance Sync

Izin di-approve tapi `student_attendances` tidak auto-update.

**Solution:**
- `LeaveRequest::approve()` method memanggil service
- Service creates/updates attendance records untuk date range
- DB transaction untuk consistency

---

## Implementation Sequence

### Phase 0: Prerequisites (2h)

**Files:**
- [`database/migrations/xxxx_create_subjects_table.php`](database/migrations/)
- [`database/migrations/xxxx_create_teacher_subjects_table.php`](database/migrations/)
- [`database/seeders/SubjectSeeder.php`](database/seeders/)

**Deliverable:** Subjects table dengan 9 mata pelajaran SD ter-seed.

---

### Phase 1: Database Schema (5h)

**5 Migration Files:**

1. **student_attendances:**
```sql
- student_id, class_id, tanggal, status (H/I/S/A), keterangan
- recorded_by, recorded_at
- UNIQUE(student_id, tanggal)
- INDEX(tanggal, status), INDEX(class_id, tanggal)
```

2. **subject_attendances:**
```sql
- student_id, class_id, subject_id, teacher_id
- tanggal, jam_ke, status, keterangan
- UNIQUE(student_id, subject_id, tanggal, jam_ke)
```

3. **leave_requests:**
```sql
- student_id, jenis (izin/sakit), tanggal_mulai, tanggal_selesai
- alasan, attachment_path
- status (pending/approved/rejected), submitted_by, reviewed_by
- reviewed_at, rejection_reason
```

4. **teacher_attendances:**
```sql
- teacher_id, tanggal
- clock_in, clock_out, latitude_in/out, longitude_in/out
- is_late, status (hadir/terlambat/izin/sakit/alpha)
- UNIQUE(teacher_id, tanggal)
```

5. **teacher_leaves:**
```sql
- teacher_id, jenis, tanggal_mulai, tanggal_selesai, jumlah_hari
- alasan, attachment_path, status
- reviewed_by, reviewed_at, rejection_reason
```

**Deliverable:** `php artisan migrate` runs without errors, all tables created.

---

### Phase 2: Eloquent Models (10h)

**6 New Models + Factories:**

**1. Subject Model**
- Relationships: `teachers()`, `subjectAttendances()`, `classes()`
- Scopes: `active()`, `byLevel()`, `byCategory()`

**2. StudentAttendance Model**
- Relationships: `student()`, `class()`, `recordedBy()`
- Scopes: `byDate()`, `byDateRange()`, `byStatus()`, `hadir()`, `alpha()`
- Accessors: `getFormattedStatusAttribute()` → "Hadir", "Izin", "Sakit", "Alpha"

**3. SubjectAttendance Model**
- Relationships: `student()`, `class()`, `subject()`, `teacher()`
- Scopes: `bySubject()`, `byTeacher()`, `byDate()`

**4. LeaveRequest Model**
- Relationships: `student()`, `submittedBy()`, `reviewedBy()`
- Methods: 
  - `approve(User $reviewer)` - Update status + sync attendance
  - `reject(User $reviewer, string $reason)`
- Scopes: `pending()`, `approved()`, `rejected()`

**5. TeacherAttendance Model**
- Relationship: `teacher()`
- Methods:
  - `clockIn($lat, $lng)` - Set clock_in + check lateness
  - `clockOut($lat, $lng)` - Set clock_out + calculate duration
- Accessors: `getDurationAttribute()` → "8 jam 15 menit"

**6. TeacherLeave Model**
- Relationships: `teacher()`, `reviewedBy()`
- Methods: `approve()`, `reject()`

**Update Existing Models:**

[`app/Models/Student.php`](app/Models/Student.php) - Add:
```php
dailyAttendances(), subjectAttendances(), leaveRequests()
getAttendanceSummary($startDate, $endDate)
```

[`app/Models/User.php`](app/Models/User.php) - Add:
```php
teacherAttendances(), teacherLeaves(), subjects()
scopeTeachers($query)
```

[`app/Models/SchoolClass.php`](app/Models/SchoolClass.php) - Add:
```php
studentAttendances(), subjects()
```

**Deliverable:** All models can be instantiated, relationships work via tinker.

---

### Phase 3: Service Layer (12h)

**File:** [`app/Services/AttendanceService.php`](app/Services/)

**Core Methods (20+ methods):**

**Student Daily Attendance:**
- `recordDailyAttendance(array $data, User $teacher): Collection`
- `updateAttendance(StudentAttendance $att, array $data): StudentAttendance`
- `canRecordAttendance(User $teacher, int $classId, string $date): bool`
- `isDuplicateAttendance(int $studentId, string $date): bool`

**Subject Attendance:**
- `recordSubjectAttendance(array $data, User $teacher): SubjectAttendance`
- `getTeacherSchedule(User $teacher, string $date): Collection`

**Leave Management:**
- `submitLeaveRequest(array $data, User $parent): LeaveRequest`
- `approveLeaveRequest(LeaveRequest $req, User $reviewer): void`
- `rejectLeaveRequest(LeaveRequest $req, User $reviewer, string $reason): void`
- `syncLeaveToAttendance(LeaveRequest $req): void` // Auto-update attendance

**Teacher Attendance:**
- `clockIn(User $teacher, float $lat, float $lng): TeacherAttendance`
- `clockOut(User $teacher, float $lat, float $lng): TeacherAttendance`
- `isAlreadyClockedIn(User $teacher, string $date): bool`
- `calculateLateness(Carbon $clockIn): array`

**Teacher Leave:**
- `submitTeacherLeave(array $data, User $teacher): TeacherLeave`
- `approveTeacherLeave(TeacherLeave $leave, User $reviewer): void`

**Reporting:**
- `getClassAttendanceSummary(int $classId, string $date): array`
- `getStudentAttendanceReport(int $studentId, string $start, string $end): array`

**Business Logic:**
1. Duplicate prevention dengan DB query check
2. Authorization: Teacher hanya untuk kelas yang diajar
3. Leave approval auto-creates attendance records (date range loop)
4. Clock lateness calculation: Compare dengan jam masuk sekolah
5. DB transactions untuk multi-record operations

**Deliverable:** Service methods work, tested via feature tests.

---

### Phase 4: Form Requests (6h)

**6 Form Request Files:**

**1. StoreStudentAttendanceRequest**
- Rules: `tanggal`, `class_id`, `attendances` array dengan validation
- Authorization: User is TEACHER + can access class
- Custom: Check duplicate, validate students belong to class

**2. StoreSubjectAttendanceRequest**
- Rules: `tanggal`, `subject_id`, `class_id`, `jam_ke`, `attendances`
- Authorization: Teacher teaches subject in class

**3. StoreLeaveRequestRequest**
- Rules: `student_id`, `jenis`, `tanggal_mulai/selesai`, `alasan`, `attachment`
- Authorization: Parent can only submit for their child
- Custom: Validate student_id is child of authenticated parent

**4. ApproveLeaveRequestRequest**
- Rules: `action` (approve/reject), `rejection_reason` (required if reject)
- Authorization: Teacher (wali kelas) OR Admin/Principal

**5. ClockInRequest**
- Rules: `latitude`, `longitude` dengan range validation
- Custom: Check teacher belum clock in today

**6. StoreTeacherLeaveRequest**
- Rules: `jenis`, `tanggal_mulai/selesai`, `alasan`, `attachment`
- Authorization: User is TEACHER

**Deliverable:** Invalid data returns 422 dengan proper error messages.

---

### Phase 5: Controllers (8h)

**7 Controller Files:**

**Teacher Namespace:**

1. **AttendanceController:**
   - `index()` - List attendance records
   - `create()` - Form untuk input harian
   - `store(StoreStudentAttendanceRequest $request)` - Save via service

2. **SubjectAttendanceController:**
   - `create()`, `store()` - Input absensi per mapel

3. **ClockController:**
   - `clockIn(ClockInRequest $request)` - POST /teacher/clock/in
   - `clockOut()` - POST /teacher/clock/out
   - `status()` - GET current clock status (for widget)

4. **LeaveRequestController (Teacher):**
   - `index()` - List leave requests untuk verification
   - `approve()`, `reject()` - POST actions

**Parent Namespace:**

5. **LeaveRequestController (Parent):**
   - `index()` - List pengajuan izin
   - `create()`, `store()` - Submit izin baru

**Admin Namespace:**

6. **AttendanceController:**
   - `studentsIndex()` - Rekap absensi siswa
   - `correction()` - Form koreksi data

7. **TeacherAttendanceController:**
   - `index()` - Rekap presensi guru

**Common Pattern:**
- Constructor injection untuk AttendanceService
- ActivityLog integration untuk audit
- Return JSON untuk API endpoints (clock in/out)
- Return Inertia::render() stub untuk pages (Sprint 2 akan implement UI)

**Deliverable:** All endpoints accessible, return expected responses.

---

### Phase 6: Routes Registration (1h)

**File:** [`routes/web.php`](routes/web.php)

**Route Groups:**

```php
// Teacher Routes
Route::middleware(['auth', 'role:TEACHER'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/daily', [AttendanceController::class, 'create'])->name('attendance.daily.create');
    Route::post('attendance/daily', [AttendanceController::class, 'store'])->name('attendance.daily.store');
    
    Route::get('attendance/subject', [SubjectAttendanceController::class, 'create'])->name('attendance.subject.create');
    Route::post('attendance/subject', [SubjectAttendanceController::class, 'store'])->name('attendance.subject.store');
    
    Route::post('clock/in', [ClockController::class, 'clockIn'])->name('clock.in');
    Route::post('clock/out', [ClockController::class, 'clockOut'])->name('clock.out');
    Route::get('clock/status', [ClockController::class, 'status'])->name('clock.status');
    
    Route::get('leave-requests', [TeacherLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('leave-requests/{leaveRequest}/approve', [...], 'approve')->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject', [...], 'reject')->name('leave-requests.reject');
});

// Parent Routes
Route::middleware(['auth', 'role:PARENT'])->prefix('parent')->name('parent.')->group(function () {
    Route::resource('leave-requests', ParentLeaveRequestController::class)->only(['index', 'create', 'store']);
    Route::get('children/{student}/attendance', [ChildController::class, 'attendance'])->name('children.attendance');
});

// Admin Routes  
Route::middleware(['auth', 'role:SUPERADMIN,ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('attendance/students', [AdminAttendanceController::class, 'studentsIndex'])->name('attendance.students.index');
    Route::get('attendance/teachers', [TeacherAttendanceController::class, 'index'])->name('attendance.teachers.index');
});
```

**Deliverable:** `php artisan route:list | grep attendance` shows all routes.

---

### Phase 7: Testing (8h)

**Feature Test Files:**

1. **StudentAttendanceTest** (8 tests)
   - Teacher can record daily attendance
   - Cannot record for other's class
   - Duplicate detection works
   - Cannot record future date
   - Status validation (H/I/S/A only)

2. **LeaveRequestTest** (10 tests)
   - Parent can submit for child
   - Cannot submit for other's child
   - Teacher can approve/reject
   - Approved leave auto-updates attendance
   - Date range creates multiple records
   - Attachment upload works

3. **TeacherClockTest** (6 tests)
   - Can clock in with GPS
   - Cannot clock in twice
   - Cannot clock out before in
   - Lateness detected correctly
   - Duration calculated
   - GPS validation works

4. **SubjectAttendanceTest** (5 tests)
   - Teacher can record subject attendance
   - Must teach subject in class
   - Duplicate prevention
   - Jam_ke validation

5. **TeacherLeaveTest** (4 tests)
   - Teacher can submit leave
   - Admin can approve
   - Principal can approve
   - Teacher cannot approve others

**Run Tests:**
```bash
php artisan test --filter=Attendance
php artisan test tests/Feature/Attendance/
```

**Deliverable:** 100% test pass, no errors.

---

## Success Criteria

Sprint 1 DONE jika:

✅ **Database:**
- [ ] 6 tables migrated successfully
- [ ] Subjects seeded (minimal 9 mapel SD)
- [ ] Foreign keys + constraints work
- [ ] Can rollback migrations

✅ **Models:**
- [ ] 6 models + factories exist
- [ ] Relationships return correct data (test via tinker)
- [ ] Scopes filter correctly
- [ ] Helper methods work

✅ **Service:**
- [ ] AttendanceService has all core methods
- [ ] Duplicate prevention works
- [ ] Leave-to-attendance sync works
- [ ] Clock lateness detection works
- [ ] Transactions rollback on error

✅ **Controllers:**
- [ ] 7 controllers created
- [ ] Routes accessible via `route:list`
- [ ] Form validation rejects invalid data
- [ ] Authorization prevents unauthorized access

✅ **Testing:**
- [ ] 33+ feature tests pass
- [ ] `php artisan test` = 100% green
- [ ] No N+1 queries (check with Debugbar)

✅ **Code Quality:**
- [ ] `vendor/bin/pint` = clean
- [ ] All methods have PHPDoc (Indonesian)
- [ ] Consistent naming
- [ ] No unresolved TODO comments

---

## Critical Questions (MUST ANSWER)

1. **Subjects Scope:** Confirm Sprint 1 = seed only, NO CRUD UI subjects?
2. **Mata Pelajaran:** Mata pelajaran apa saja yang perlu di-seed? (SD kelas 1-6?)
3. **Academic Year:** Apakah attendance filtered by tahun_ajaran?
4. **Teacher Authorization:** Wali kelas bisa input semua mapel atau hanya guru mapel?
5. **Leave Approval:** Single approval (teacher) atau dual (teacher → principal)?
6. **Clock Rules:** Jam masuk sekolah jam berapa? GPS radius validation di Sprint 1?

---

## Risk Mitigation

**Risk 1: Subjects Table Scope Creep**
→ Sprint 1 = seeder only, CRUD UI di Sprint 3

**Risk 2: Notification System**
→ OUT OF SCOPE Sprint 1, add TODO hooks di service

**Risk 3: GPS Validation Complexity**
→ Store coordinates only, validation logic Sprint 2

**Risk 4: Performance di Large Dataset**
→ Add proper indexes, eager loading, caching di Sprint 2

---

## Next Steps After Sprint 1

Sprint 2 (Week 3-4) akan build UI:
- Daily Attendance Input page (Teacher)
- Clock Widget (Teacher Dashboard)
- Leave Request Form (Parent)
- Leave Verification (Teacher/Admin)
- Navigation updates
- Wayfinder integration

Sprint 1 provides COMPLETE backend API untuk Sprint 2 consume.
