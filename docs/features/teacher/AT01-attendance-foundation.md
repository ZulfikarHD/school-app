# Feature: AT01 - Attendance Foundation

> **Code:** AT01 | **Priority:** Critical | **Status:** ✅ Complete (Backend)
> **Sprint:** 1 | **Menu:** Teacher Dashboard > Presensi, Parent Dashboard > Izin

---

## Overview

Attendance Foundation merupakan sistem presensi yang bertujuan untuk mengelola kehadiran siswa dan guru secara digital, yaitu: tracking kehadiran harian siswa, absensi per mata pelajaran, pengelolaan izin siswa dengan approval workflow, serta clock in/out guru dengan GPS tracking untuk akurasi dan transparansi data kehadiran.

---

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| AT01-01 | Teacher | Input presensi harian siswa | Data kehadiran tercatat per hari | ✅ Complete |
| AT01-02 | Teacher | Input presensi per mata pelajaran | Track kehadiran per sesi mengajar | ✅ Complete |
| AT01-03 | Teacher | Melihat riwayat presensi kelas | Monitor pola kehadiran siswa | 🔄 Backend Only |
| AT01-04 | Parent | Mengajukan izin/sakit untuk anak | Ketidakhadiran tercatat resmi | ✅ Complete |
| AT01-05 | Parent | Melihat status permohonan izin | Tahu apakah disetujui/ditolak | 🔄 Backend Only |
| AT01-06 | Teacher | Menyetujui/menolak izin siswa | Verify alasan ketidakhadiran | ✅ Complete |
| AT01-07 | Teacher | Clock in/out dengan GPS | Presensi guru tercatat akurat | ✅ Complete |
| AT01-08 | Teacher | Melihat status clock in hari ini | Tahu sudah absen atau belum | ✅ Complete |
| AT01-09 | Admin | Melihat rekap presensi siswa | Monitoring dan reporting | 🔄 Backend Only |
| AT01-10 | Admin | Melihat rekap presensi guru | Evaluasi kedisiplinan | 🔄 Backend Only |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-AT-01 | Presensi harian hanya bisa diinput untuk hari ini atau sebelumnya | Validation: `tanggal <= today` |
| BR-AT-02 | Satu siswa hanya bisa punya 1 presensi harian per tanggal | Unique constraint `[student_id, tanggal]` |
| BR-AT-03 | Teacher hanya bisa input presensi untuk kelas yang diajar | Authorization check via `canRecordAttendance()` |
| BR-AT-04 | Izin yang disetujui otomatis membuat record presensi | Service method `syncLeaveToAttendance()` |
| BR-AT-05 | Teacher hanya bisa clock in 1x per hari | Unique constraint `[teacher_id, tanggal]` |
| BR-AT-06 | Terlambat jika clock in > 07:30 WIB | Logic di `TeacherAttendance::clockIn()` |
| BR-AT-07 | Parent hanya bisa ajukan izin untuk anaknya sendiri | Authorization check via guardian relationship |
| BR-AT-08 | Izin tidak termasuk weekend (Sabtu-Minggu) | Business days calculation di service |

---

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| **Models** | `app/Models/Subject.php` | Mata pelajaran dengan relationships |
| | `app/Models/StudentAttendance.php` | Presensi harian siswa |
| | `app/Models/SubjectAttendance.php` | Presensi per mata pelajaran |
| | `app/Models/LeaveRequest.php` | Permohonan izin dengan approval |
| | `app/Models/TeacherAttendance.php` | Clock in/out guru dengan GPS |
| | `app/Models/TeacherLeave.php` | Cuti guru |
| **Service** | `app/Services/AttendanceService.php` | Business logic (20+ methods) |
| **Controllers** | `app/Http/Controllers/Teacher/AttendanceController.php` | Daily attendance endpoints |
| | `app/Http/Controllers/Teacher/SubjectAttendanceController.php` | Subject attendance endpoints |
| | `app/Http/Controllers/Teacher/ClockController.php` | Clock in/out API |
| | `app/Http/Controllers/Teacher/LeaveRequestController.php` | Leave verification |
| | `app/Http/Controllers/Parent/LeaveRequestController.php` | Leave submission |
| | `app/Http/Controllers/Admin/AttendanceController.php` | Attendance reports |
| | `app/Http/Controllers/Admin/TeacherAttendanceController.php` | Teacher attendance reports |
| **Form Requests** | `app/Http/Requests/StoreStudentAttendanceRequest.php` | Validation + custom rules |
| | `app/Http/Requests/StoreSubjectAttendanceRequest.php` | Subject attendance validation |
| | `app/Http/Requests/StoreLeaveRequestRequest.php` | Leave request validation |
| | `app/Http/Requests/ApproveLeaveRequestRequest.php` | Approval validation |
| | `app/Http/Requests/ClockInRequest.php` | GPS validation |
| | `app/Http/Requests/StoreTeacherLeaveRequest.php` | Teacher leave validation |

### Routes Summary

| Group | Count | Prefix | Status |
|-------|-------|--------|--------|
| Student Attendance | 3 | `teacher/attendance` | ✅ Registered |
| Subject Attendance | 2 | `teacher/attendance/subject` | ✅ Registered |
| Teacher Clock | 3 | `teacher/clock` | ✅ Registered |
| Leave Verification | 2 | `teacher/leave-requests` | ✅ Registered |
| Leave Submission | 3 | `parent/leave-requests` | ✅ Registered |
| Admin Reports | 3 | `admin/attendance` | ✅ Registered |

> 📡 Full API documentation: [Attendance API](../../api/attendance.md)

### Database Schema

| Table | Purpose | Key Constraints |
|-------|---------|-----------------|
| `subjects` | Mata pelajaran SD | Unique `kode_mapel` |
| `teacher_subjects` | Pivot guru-mapel | Unique `[teacher_id, subject_id, class_id, tahun_ajaran]` |
| `student_attendances` | Presensi harian | Unique `[student_id, tanggal]` |
| `subject_attendances` | Presensi per mapel | Unique `[student_id, subject_id, tanggal, jam_ke]` |
| `leave_requests` | Izin siswa | Index `[student_id, status]` |
| `teacher_attendances` | Clock guru | Unique `[teacher_id, tanggal]` + GPS |
| `teacher_leaves` | Cuti guru | Index `[teacher_id, status]` |

> 📌 Lihat DATABASE.md untuk schema lengkap.

---

## Edge Cases & Handling

| Scenario | Expected Behavior | Implementation |
|----------|-------------------|----------------|
| Teacher input presensi 2x untuk siswa sama | Skip duplicate, success response | Service `isDuplicateAttendance()` check |
| Parent ajukan izin untuk anak orang lain | 403 Forbidden | Form Request authorization |
| Teacher clock in tanpa GPS | 422 Validation Error | `ClockInRequest` validation |
| Izin disetujui tapi tanggal sudah lewat | Tetap buat attendance record | `syncLeaveToAttendance()` backfill |
| Clock in > 07:30 | Auto-set `is_late = true`, `status = TERLAMBAT` | `TeacherAttendance::clockIn()` logic |
| Izin range 5 hari | Buat 5 attendance records (skip weekend) | Service loop dengan `isWeekend()` check |
| Teacher belum clock in tapi mau clock out | 404 Not Found error | Service `isAlreadyClockedIn()` check |
| Input presensi untuk tanggal masa depan | 422 Validation Error | Form Request rule `before_or_equal:today` |

---

## Security Considerations

| Concern | Mitigation | Implementation |
|---------|-----------|----------------|
| **Authorization Bypass** | Role-based middleware + Form Request checks | `role:TEACHER` middleware + custom `authorize()` |
| **GPS Spoofing** | Store coordinates untuk audit | Latitude/longitude columns di DB |
| **Duplicate Prevention** | Database unique constraints | Composite unique keys |
| **SQL Injection** | Eloquent ORM + prepared statements | Never raw queries |
| **File Upload Abuse** | MIME type + size validation | `mimes:pdf,jpg,jpeg,png` + `max:2048` |
| **CSRF** | Laravel default protection | `@csrf` tokens |

---

## Performance Considerations

| Concern | Solution | Implementation |
|---------|----------|----------------|
| **N+1 Queries** | Eager loading relationships | `with(['student', 'class'])` |
| **Bulk Insert** | DB transactions | `DB::transaction()` wrapping |
| **Index Optimization** | Strategic indexes | `index(['tanggal', 'status'])` |
| **Pagination** | Limit query results | `paginate(50)` default |

---

## Testing

### Automated Tests

| Test File | Scenarios Covered | Status |
|-----------|------------------|--------|
| `StudentAttendanceTest.php` | 8 test cases | ✅ All Passing |
| `LeaveRequestTest.php` | Planned 10 test cases | 📝 To be created |
| `TeacherClockTest.php` | Planned 6 test cases | 📝 To be created |
| `SubjectAttendanceTest.php` | Planned 5 test cases | 📝 To be created |
| `TeacherLeaveTest.php` | Planned 4 test cases | 📝 To be created |

### Quick Verification

- [x] Routes accessible via `route:list`
- [x] Migrations applied successfully
- [x] Service methods exist and callable
- [x] Form Request validation works
- [x] Authorization logic prevents unauthorized access
- [x] Duplicate prevention works
- [ ] UI pages created (Sprint 2)
- [ ] End-to-end flow tested (Sprint 2)

> 📋 Full test plan: [AT01 Test Plan](../../testing/AT01-attendance-test-plan.md)

---

## Configuration

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `SCHOOL_START_TIME` | time | `07:30` | Jam masuk sekolah untuk late detection |
| `TIMEZONE` | string | `Asia/Jakarta` | Timezone WIB untuk timestamps |
| `UPLOAD_MAX_SIZE` | int | `2048` | Max file size (KB) untuk attachment |

---

## Related Documentation

- **API Documentation:** [Attendance API](../../api/attendance.md)
- **Test Plan:** [AT01 Test Plan](../../testing/AT01-attendance-test-plan.md)
- **Database Schema:** [DATABASE.md](../../architecture/DATABASE.md)

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-12-24 | Initial implementation - Backend foundation complete |

---

## Update Triggers

Update dokumentasi ini ketika:
- [ ] Business rules berubah (misal: jam masuk berubah)
- [ ] API contract berubah (request/response format)
- [ ] New edge cases ditemukan saat testing
- [ ] Database schema modified
- [ ] New routes ditambahkan

---

*Last Updated: 2025-12-24*
*Sprint 1 Status: Backend Complete ✅ | UI Pending (Sprint 2) 🔄*
