# 📋 ATT - Attendance System (Sistem Absensi)

**Module Code:** `ATT`  
**Status:** ✅ Complete  
**Priority:** P0 - Critical  
**Last Updated:** 2026-01-12

---

## Overview

Sistem Absensi adalah modul untuk mengelola kehadiran siswa dan guru secara digital, yaitu: input absensi harian pagi dan per mata pelajaran untuk siswa, clock in/out untuk presensi guru, manajemen izin/sakit dengan workflow approval, serta reporting dan notifikasi otomatis untuk monitoring kehadiran real-time.

**Dependencies:**
- Authentication System
- Student Management Module
- Teacher Management Module
- Notification Service (WhatsApp/Email)

---

## User Stories

| ID | Story | Role | Status |
|----|-------|------|--------|
| US-ATT-001 | Input absensi harian pagi untuk siswa di kelas | Teacher | ✅ Complete |
| US-ATT-002 | Input absensi per mata pelajaran yang diajar | Teacher | ✅ Complete |
| US-ATT-003 | Submit izin/sakit untuk anak dengan upload dokumen | Parent | ✅ Complete |
| US-ATT-004 | Verifikasi permohonan izin/sakit dari orang tua | Teacher/TU | ✅ Complete |
| US-ATT-005 | Lihat rekap absensi siswa dengan filter dan chart | Teacher/TU/Principal | ✅ Complete |
| US-ATT-006 | Clock in/out untuk presensi pribadi dengan GPS | Teacher | ✅ Complete |
| US-ATT-007 | Lihat rekap presensi guru untuk payroll | TU/Principal | ✅ Complete |
| US-ATT-008 | Submit dan approve cuti/izin guru | Teacher/Principal | ✅ Complete |
| US-ATT-009 | Koreksi manual data absensi dengan audit trail | TU/Admin | ✅ Complete |
| US-ATT-010 | Notifikasi otomatis alpha dan reminder input | System | ✅ Complete |

---

## Business Rules

| Rule ID | Description | Implementation |
|---------|-------------|----------------|
| BR-ATT-001 | Absensi harian input window: 06:00-23:59 | Validation di form & service |
| BR-ATT-002 | Approved leave override status Alpha | `syncLeaveToAttendance()` method |
| BR-ATT-003 | Persentase = (H+I+S) / Total Hari × 100% | `calculateAttendanceStatistics()` |
| BR-ATT-004 | Teacher clock in < 07:30 = Tepat Waktu | `TeacherAttendance::clockIn()` |
| BR-ATT-005 | Leave request max 30 hari advance, 7 hari retroactive | Form validation |
| BR-ATT-006 | Duplicate attendance → Edit mode, not create | `updateOrCreate` in service |
| BR-ATT-007 | Weekend & holidays excluded from calculation | `isWeekend()` check |

---

## Technical Implementation

### Core Components

**Backend:**
- **Controllers:**
  - `Teacher\AttendanceController` - Daily attendance CRUD
  - `Teacher\SubjectAttendanceController` - Subject attendance
  - `Teacher\ClockController` - Clock in/out API
  - `Parent\LeaveRequestController` - Submit leave requests
  - `Teacher\LeaveRequestController` - Verify student leaves
  - `Admin\AttendanceController` - Reports & corrections
  - `Principal\TeacherLeaveController` - Approve teacher leaves

- **Services:**
  - `AttendanceService` - Core business logic (758 lines)
  - `NotificationService` - WhatsApp & email delivery

- **Models:**
  - `StudentAttendance` - Daily attendance records
  - `SubjectAttendance` - Per-subject attendance
  - `TeacherAttendance` - Teacher clock in/out
  - `LeaveRequest` - Student leave requests
  - `TeacherLeave` - Teacher leave/cuti
  - `AttendanceAuditLog` - Correction audit trail
  - `AttendanceNotification` - Notification queue

- **Jobs:**
  - `SendAlphaNotification` - Alert parents when Alpha
  - `SendAttendanceReminder` - Morning reminder to teachers

- **Commands:**
  - `attendance:send-reminders` - Scheduled at 10:00 AM

**Frontend:**
- `Teacher/Attendance/Create.vue` - Daily attendance form (480 lines)
- `Teacher/Attendance/Index.vue` - Attendance history list (295 lines)
- `Teacher/SubjectAttendance/Create.vue` - Subject attendance (369 lines)
- `Parent/LeaveRequest/Create.vue` - Submit leave form (392 lines)
- `Teacher/LeaveRequest/Index.vue` - Verify leaves (295 lines)
- `ClockWidget.vue` - Clock in/out widget (367 lines)
- `Principal/TeacherLeave/Index.vue` - Approve teacher leaves (376 lines)
- `Admin/Attendance/Students/Report.vue` - Student reports
- `Admin/Attendance/Teachers/Report.vue` - Teacher reports

### Database Schema

**Tables:**
- `student_attendances` - Daily attendance (unique: student_id + tanggal)
- `subject_attendances` - Subject attendance (unique: student_id + subject_id + tanggal + jam_ke)
- `teacher_attendances` - Teacher clock records (unique: teacher_id + tanggal)
- `leave_requests` - Student leave requests
- `teacher_leaves` - Teacher leave/cuti
- `attendance_audit_logs` - Correction history
- `attendance_notifications` - Notification queue

**Key Indexes:**
- `(tanggal, status)` on student_attendances
- `(class_id, tanggal)` on student_attendances
- `(teacher_id, tanggal)` on teacher_attendances

---

## Key Routes

| Method | Path | Controller@Method | Purpose |
|--------|------|-------------------|---------|
| GET | `/teacher/attendance/daily` | AttendanceController@create | Daily attendance form |
| POST | `/teacher/attendance/daily` | AttendanceController@store | Save daily attendance |
| GET | `/teacher/attendance/subject` | SubjectAttendanceController@create | Subject attendance form |
| POST | `/teacher/clock/in` | ClockController@clockIn | Teacher clock in |
| POST | `/teacher/clock/out` | ClockController@clockOut | Teacher clock out |
| GET | `/teacher/clock/status` | ClockController@status | Get clock status |
| POST | `/parent/leave-requests` | LeaveRequestController@store | Submit leave request |
| POST | `/teacher/leave-requests/{id}/approve` | LeaveRequestController@approve | Approve student leave |
| POST | `/teacher/leave-requests/{id}/reject` | LeaveRequestController@reject | Reject student leave |
| GET | `/admin/attendance/students/report` | AttendanceController@generateReport | Student report |
| GET | `/admin/attendance/teachers/report` | TeacherAttendanceController@generateReport | Teacher report |
| POST | `/principal/teacher-leaves/{id}/approve` | TeacherLeaveController@approve | Approve teacher leave |

**Full API documentation:** See [Attendance API](../../api/attendance.md)

---

## Edge Cases

| Case | Handling | Implementation |
|------|----------|----------------|
| Teacher inputs attendance twice for same date | Auto-load existing data (edit mode) | `checkExisting()` endpoint |
| Leave approved after attendance submitted | Leave status overrides | `syncLeaveToAttendance()` |
| Teacher forgets to clock out | TU can edit manually | Manual correction feature |
| GPS permission denied | User-friendly error message | ClockWidget error handling |
| Student has leave for future date | Auto-set status when date arrives | Check on attendance input |
| Teacher not wali kelas but teaches class | Allow input via teacher_subjects | `canRecordAttendance()` check |
| Clock in after 07:30 | Flag as "Terlambat" but still count | `calculateLateness()` |
| Weekend/holiday attendance | Skip in calculations | `isWeekend()` filter |
| Parent submits leave 8 days ago | Form validation blocks | Max 7 days retroactive |
| Multiple students with same name | Display NIS for identification | UI shows nama + NIS |

---

## Security Considerations

| Security Aspect | Implementation |
|----------------|----------------|
| Authorization | Role-based middleware on routes |
| Teacher can only access own classes | `canRecordAttendance()` check |
| Parent can only submit for own children | Guardian relationship validation |
| GPS coordinates stored securely | Decimal fields in DB |
| File upload validation | Max 5MB, jpg/png/pdf only |
| Audit trail for corrections | AttendanceAuditLog records |
| SQL injection prevention | Eloquent ORM used |
| XSS prevention | Inertia.js auto-escapes |

---

## Pre-Documentation Verification

✅ **Routes Verified:**
```bash
php artisan route:list --path=teacher/attendance
php artisan route:list --path=teacher/clock
php artisan route:list --path=parent/leave-requests
php artisan route:list --path=admin/attendance
```
Result: All routes accessible and registered

✅ **Service Methods:** All controller calls match service methods:
- `AttendanceService::recordDailyAttendance()` ✓
- `AttendanceService::recordSubjectAttendance()` ✓
- `AttendanceService::clockIn()` ✓
- `AttendanceService::clockOut()` ✓
- `AttendanceService::approveLeaveRequest()` ✓
- `AttendanceService::syncLeaveToAttendance()` ✓

✅ **Frontend Pages:** All Inertia renders verified:
- `Teacher/Attendance/Create.vue` ✓
- `Teacher/LeaveRequest/Index.vue` ✓
- `Parent/LeaveRequest/Create.vue` ✓
- `ClockWidget.vue` ✓

✅ **Migrations:** All applied:
- `student_attendances` ✓
- `teacher_attendances` ✓
- `leave_requests` ✓
- `attendance_audit_logs` ✓
- `attendance_notifications` ✓

---

## Manual Testing Checklist

- [x] Daily attendance input with edit mode
- [x] Subject attendance input
- [x] Parent submit leave with file upload
- [x] Teacher approve/reject leave
- [x] Teacher clock in with GPS
- [x] Teacher clock out with duration
- [x] Student attendance report with filters
- [x] Teacher attendance report
- [x] Principal approve teacher leave
- [x] Admin manual correction

**Full test plan:** See [ATT Test Plan](../../testing/ATT-test-plan.md)

---

## Performance Considerations

- Attendance queries use indexes on `(tanggal, class_id)`
- Dashboard summary cached for 5 minutes
- Report queries limited to 1000 records
- Eager loading prevents N+1 queries
- GPS acquisition timeout: 15 seconds

---

## Related Documentation

- **API Documentation:** [Attendance API](../../api/attendance.md)
- **Test Plan:** [ATT Test Plan](../../testing/ATT-test-plan.md)
- **QA Manual Testing:** See root-level QA files
- **Requirements:** `school-reqs-main/02_Functional_Requirements/03_Attendance_System.md`

---

## Known Limitations

- Export to Excel/PDF: Placeholder (planned for Phase 3)
- WhatsApp notifications: Requires Fonnte API key
- GPS: Requires HTTPS and browser permission
- Auto clock out: Not implemented (manual TU intervention)

---

**Developer:** Zulfikar Hidayatullah  
**Module Code:** ATT  
**Lines of Code:** ~5,000+ (Backend + Frontend)
