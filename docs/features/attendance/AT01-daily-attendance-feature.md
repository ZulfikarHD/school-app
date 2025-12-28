# AT01: Daily Attendance Management (Teacher)

**Feature Code:** AT01  
**Owner:** Teacher  
**Status:** ✅ Complete  
**Priority:** P0 (Critical)  
**Last Updated:** 2025-12-28

---

## Overview

Fitur Daily Attendance Management merupakan sistem pencatatan presensi harian siswa yang bertujuan untuk memungkinkan guru mencatat kehadiran siswa secara digital dengan status H (Hadir), I (Izin), S (Sakit), atau A (Alpha), yaitu: menggantikan absensi manual, menyediakan data real-time untuk monitoring, dan memfasilitasi auto-sync dengan permohonan izin yang disetujui.

---

## User Stories

| ID | As a | I want to | So that | Priority | Status |
|----|------|-----------|---------|----------|--------|
| AT01-01 | Teacher | Melihat daftar siswa di kelas yang saya ampu | Saya bisa input presensi untuk kelas tersebut | P0 | ✅ |
| AT01-02 | Teacher | Memilih tanggal untuk input presensi | Saya bisa mencatat presensi untuk hari tertentu | P0 | ✅ |
| AT01-03 | Teacher | Mengubah status presensi setiap siswa (H/I/S/A) | Saya bisa mencatat kehadiran dengan akurat | P0 | ✅ |
| AT01-04 | Teacher | Menambahkan keterangan untuk siswa tidak hadir | Saya bisa memberikan detail alasan ketidakhadiran | P0 | ✅ |
| AT01-05 | Teacher | Melihat summary real-time (Hadir: X, Alpha: Y) | Saya bisa memvalidasi data sebelum submit | P0 | ✅ |
| AT01-06 | Teacher | Mencegah duplikasi presensi untuk tanggal sama | Data presensi tetap akurat dan tidak double | P0 | ✅ |
| AT01-07 | Teacher | Melihat riwayat presensi yang sudah diinput | Saya bisa review data presensi sebelumnya | P1 | 🔄 |

---

## Business Rules

| Rule ID | Rule | Validation | Impact |
|---------|------|------------|--------|
| BR-AT01-01 | Hanya wali kelas atau guru pengajar yang bisa input presensi | `AttendanceService::canRecordAttendance()` | Authorization |
| BR-AT01-02 | Tidak boleh input presensi untuk tanggal masa depan | `tanggal <= today` | Data integrity |
| BR-AT01-03 | Tidak boleh duplikasi presensi untuk siswa & tanggal sama | Check existing record | Prevent double entry |
| BR-AT01-04 | Default status adalah "Hadir" (H) | Frontend initialization | UX optimization |
| BR-AT01-05 | Keterangan wajib untuk status selain Hadir | Frontend conditional | Data completeness |
| BR-AT01-06 | Semua siswa di kelas harus diabsen | `attendances.min:1` | Completeness |

---

## Technical Implementation

### Components

| Component | Type | Path | Purpose |
|-----------|------|------|---------|
| **AttendanceController** | Backend | `app/Http/Controllers/Teacher/AttendanceController.php` | Handle attendance CRUD |
| **AttendanceService** | Backend | `app/Services/AttendanceService.php` | Business logic & validation |
| **StoreStudentAttendanceRequest** | Backend | `app/Http/Requests/StoreStudentAttendanceRequest.php` | Input validation |
| **Create.vue** | Frontend | `resources/js/pages/Teacher/Attendance/Create.vue` | Input form UI |
| **Index.vue** | Frontend | `resources/js/pages/Teacher/Attendance/Index.vue` | History view (placeholder) |
| **AttendanceStatusBadge.vue** | Frontend | `resources/js/components/features/attendance/AttendanceStatusBadge.vue` | Status display |

### Routes

| Method | URI | Name | Controller@Method |
|--------|-----|------|-------------------|
| GET | `/teacher/attendance` | teacher.attendance.index | AttendanceController@index |
| GET | `/teacher/attendance/daily` | teacher.attendance.daily.create | AttendanceController@create |
| POST | `/teacher/attendance/daily` | teacher.attendance.daily.store | AttendanceController@store |
| GET | `/teacher/api/classes/{class}/students` | teacher.api.classes.students | Closure (fetch students) |

### Database Schema

**Table:** `student_attendances`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| student_id | bigint | FK to students |
| class_id | bigint | FK to classes |
| tanggal | date | Attendance date |
| status | enum('H','I','S','A') | Attendance status |
| keterangan | text | Optional notes |
| recorded_by | bigint | FK to users (teacher) |
| recorded_at | timestamp | Recording timestamp |

---

## Edge Cases

| Case | Handling | Status |
|------|----------|--------|
| Teacher bukan wali kelas/pengajar | Return 403 Forbidden via `canRecordAttendance()` | ✅ |
| Duplikasi presensi tanggal sama | Skip duplicate in transaction loop | ✅ |
| Siswa pindah kelas di tengah hari | Validasi `kelas_id` saat submit | ✅ |
| Tanggal masa depan | Frontend & backend validation | ✅ |
| Kelas tidak memiliki siswa | Show empty state dengan message | ✅ |
| Network error saat submit | Form shows error, data tidak hilang | ✅ |

---

## Security Considerations

| Security Aspect | Implementation | Status |
|-----------------|----------------|--------|
| **Authorization** | `role:TEACHER` middleware + `canRecordAttendance()` check | ✅ |
| **CSRF Protection** | Laravel CSRF token pada form POST | ✅ |
| **Input Validation** | FormRequest dengan rules & custom validator | ✅ |
| **SQL Injection** | Eloquent ORM dengan parameter binding | ✅ |
| **XSS Prevention** | Vue automatic escaping + sanitized output | ✅ |

---

## User Experience Features

### Input Form Features
- ✅ Auto-load teacher's classes (wali kelas + kelas yang diajar)
- ✅ Default semua siswa ke status "Hadir" (H)
- ✅ Radio button selection untuk H/I/S/A per siswa
- ✅ Conditional keterangan field (hanya muncul jika bukan Hadir)
- ✅ Real-time summary counter: "Hadir: 28, Izin: 0, Sakit: 0, Alpha: 2"
- ✅ Date picker dengan max date = today
- ✅ Mobile-responsive table layout
- ✅ Haptic feedback pada selection
- ✅ Loading skeleton saat fetch students

### Design Standards
- ✅ Emerald accent untuk primary actions
- ✅ Slate-50/80 background untuk form fields
- ✅ Rounded-xl borders
- ✅ iOS-like spring animations (Motion-v)
- ✅ Press feedback (scale: 0.97) pada buttons

---

## Performance Considerations

| Aspect | Implementation | Impact |
|--------|----------------|--------|
| **Batch Insert** | Transaction untuk multiple students | Fast bulk insert |
| **Lazy Loading** | Students fetched on class selection | Reduced initial load |
| **Optimistic UI** | Summary updates instantly | Snappy UX |
| **Debounced Search** | (Future) Search students | Reduced API calls |

---

## Future Enhancements (P2)

| Enhancement | Description | Priority |
|-------------|-------------|----------|
| **Edit Attendance** | Modify existing attendance records | P1 |
| **Bulk Actions** | Mark all as Hadir/Alpha | P2 |
| **Attendance History** | Full history table with filters | P1 |
| **Export to Excel** | Download attendance report | P2 |
| **QR Code Scan** | Auto-mark present via QR scan | P3 |

---

## Related Documentation

- **API Documentation:** [Attendance API](../../api/attendance.md)
- **Test Plan:** [AT01 Test Plan](../../testing/AT01-attendance-test-plan.md)
- **User Journeys:** [Attendance User Journeys](../../guides/attendance-user-journeys.md)

---

## Verification Evidence

```bash
# Routes verified
$ php artisan route:list --path=teacher/attendance
✓ teacher.attendance.index (GET)
✓ teacher.attendance.daily.create (GET)
✓ teacher.attendance.daily.store (POST)

# Migrations verified
$ php artisan migrate:status | findstr attendance
✓ 2025_12_24_091405_create_student_attendances_table [Ran]

# Service methods verified
✓ AttendanceService::recordDailyAttendance() - exists
✓ AttendanceService::canRecordAttendance() - exists
✓ AttendanceService::getTeacherClasses() - exists
```

---

**Last Verified:** 2025-12-28 21:45 WIB
