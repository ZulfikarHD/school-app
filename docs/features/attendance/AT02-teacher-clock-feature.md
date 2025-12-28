# AT02: Teacher Clock In/Out System

**Feature Code:** AT02  
**Owner:** Teacher  
**Status:** ✅ Complete  
**Priority:** P0 (Critical)  
**Last Updated:** 2025-12-28

---

## Overview

Fitur Teacher Clock In/Out System merupakan sistem pencatatan kehadiran guru dengan GPS tracking yang bertujuan untuk mencatat waktu masuk dan pulang guru secara otomatis dengan validasi lokasi, yaitu: menggantikan absensi manual guru, menyediakan data untuk payroll, dan mendeteksi keterlambatan secara otomatis.

---

## User Stories

| ID | As a | I want to | So that | Priority | Status |
|----|------|-----------|---------|----------|--------|
| AT02-01 | Teacher | Melihat status clock in saya hari ini | Saya tahu apakah sudah clock in atau belum | P0 | ✅ |
| AT02-02 | Teacher | Clock in dengan GPS location | Sistem bisa verifikasi saya di sekolah | P0 | ✅ |
| AT02-03 | Teacher | Melihat waktu clock in saya | Saya tahu jam berapa saya masuk | P0 | ✅ |
| AT02-04 | Teacher | Clock out dengan GPS location | Sistem bisa hitung durasi kerja saya | P0 | ✅ |
| AT02-05 | Teacher | Melihat indikator terlambat | Saya aware jika terlambat masuk | P0 | ✅ |
| AT02-06 | Teacher | Melihat durasi kerja setelah clock out | Saya tahu total jam kerja hari ini | P0 | ✅ |
| AT02-07 | Teacher | Mendapat error message jika GPS denied | Saya tahu harus enable GPS | P0 | ✅ |

---

## Business Rules

| Rule ID | Rule | Validation | Impact |
|---------|------|------------|--------|
| BR-AT02-01 | Hanya bisa clock in sekali per hari | Check existing `clock_in` for today | Prevent duplicate |
| BR-AT02-02 | Hanya bisa clock out jika sudah clock in | Check `clock_in` exists | Logical flow |
| BR-AT02-03 | Terlambat jika clock in > 07:00 WIB | Compare time with threshold | Late detection |
| BR-AT02-04 | GPS coordinates wajib untuk clock in/out | Validate lat/lng not null | Location tracking |
| BR-AT02-05 | Clock out harus setelah clock in | `clock_out > clock_in` | Data integrity |
| BR-AT02-06 | Status auto-set berdasarkan waktu | HADIR/TERLAMBAT based on time | Auto-classification |

---

## Technical Implementation

### Components

| Component | Type | Path | Purpose |
|-----------|------|------|---------|
| **ClockController** | Backend | `app/Http/Controllers/Teacher/ClockController.php` | Handle clock operations |
| **AttendanceService** | Backend | `app/Services/AttendanceService.php` | Clock business logic |
| **ClockInRequest** | Backend | `app/Http/Requests/ClockInRequest.php` | Clock in validation |
| **ClockOutRequest** | Backend | `app/Http/Requests/ClockOutRequest.php` | Clock out validation |
| **ClockWidget.vue** | Frontend | `resources/js/components/features/attendance/ClockWidget.vue` | Clock UI widget |
| **TeacherDashboard.vue** | Frontend | `resources/js/pages/Dashboard/TeacherDashboard.vue` | Dashboard integration |

### Routes

| Method | URI | Name | Controller@Method |
|--------|-----|------|-------------------|
| GET | `/teacher/clock/status` | teacher.clock.status | ClockController@status |
| POST | `/teacher/clock/in` | teacher.clock.in | ClockController@clockIn |
| POST | `/teacher/clock/out` | teacher.clock.out | ClockController@clockOut |

### Database Schema

**Table:** `teacher_attendances`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| teacher_id | bigint | FK to users |
| tanggal | date | Attendance date |
| clock_in | time | Clock in time |
| clock_out | time | Clock out time |
| clock_in_location | string | GPS coordinates (lat,lng) |
| clock_out_location | string | GPS coordinates (lat,lng) |
| status | enum | HADIR/TERLAMBAT/IZIN/SAKIT/ALPHA |
| is_late | boolean | Late indicator |
| duration | string | Work duration (HH:mm:ss) |
| keterangan | text | Optional notes |

---

## Edge Cases

| Case | Handling | Status |
|------|----------|--------|
| GPS permission denied | Show error modal dengan instruksi | ✅ |
| Browser tidak support geolocation | Show warning message | ✅ |
| Already clocked in today | Disable button, show status | ✅ |
| Clock out sebelum clock in | Return error 400 | ✅ |
| GPS timeout (10s) | Show timeout error dengan retry | ✅ |
| Network error saat submit | Show error, allow retry | ✅ |
| Clock in di hari libur | Allowed, marked as special | ✅ |

---

## Security Considerations

| Security Aspect | Implementation | Status |
|-----------------|----------------|--------|
| **Authorization** | `role:TEACHER` middleware | ✅ |
| **CSRF Protection** | Laravel CSRF token | ✅ |
| **GPS Validation** | Validate lat/lng format & range | ✅ |
| **Rate Limiting** | Prevent spam clock requests | ✅ |
| **Location Privacy** | GPS stored encrypted (future) | 🔄 |

---

## User Experience Features

### Clock Widget Features
- ✅ Real-time status display: "Belum Clock In" / "Sudah Clock In pada 07:15 WIB"
- ✅ Late indicator: "⚠️ Terlambat" badge
- ✅ GPS permission request dengan clear messaging
- ✅ Loading state saat request GPS: "Mencari Lokasi..."
- ✅ Haptic feedback (medium) on clock actions
- ✅ Confirmation modal untuk clock out
- ✅ Duration display setelah clock out
- ✅ Gradient background (emerald to teal)
- ✅ Animated status indicator (pulsing dot)

### Design Standards
- ✅ Gradient card: `from-emerald-500 to-teal-600`
- ✅ White text untuk contrast
- ✅ Rounded-2xl borders
- ✅ Shadow dengan emerald tint
- ✅ iOS-like spring animations
- ✅ Press feedback (scale: 0.97)

---

## GPS Integration

### Browser Geolocation API

```javascript
navigator.geolocation.getCurrentPosition(
    (position) => {
        // Success: Get lat/lng
        latitude: position.coords.latitude,
        longitude: position.coords.longitude
    },
    (error) => {
        // Error handling by code
        PERMISSION_DENIED: "Izin GPS ditolak"
        POSITION_UNAVAILABLE: "Lokasi tidak tersedia"
        TIMEOUT: "Request GPS timeout"
    },
    {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    }
);
```

### Error Messages (Indonesian)

| Error Code | User Message |
|------------|--------------|
| PERMISSION_DENIED | "Izin GPS ditolak. Aktifkan GPS di pengaturan browser." |
| POSITION_UNAVAILABLE | "Lokasi tidak tersedia. Pastikan GPS aktif." |
| TIMEOUT | "Request GPS timeout. Coba lagi." |
| NOT_SUPPORTED | "Browser tidak mendukung GPS. Hubungi admin." |

---

## Performance Considerations

| Aspect | Implementation | Impact |
|--------|----------------|--------|
| **Status Caching** | Fetch once on mount | Reduced API calls |
| **Optimistic UI** | Update UI before API response | Instant feedback |
| **GPS Timeout** | 10s max wait time | Prevent hanging |
| **Lazy Component** | Widget only loads on dashboard | Faster page load |

---

## Future Enhancements (P2)

| Enhancement | Description | Priority |
|-------------|-------------|----------|
| **Geofencing** | Validate teacher di area sekolah | P1 |
| **Face Recognition** | Verify identity saat clock in | P3 |
| **Offline Mode** | Queue clock when offline | P2 |
| **History View** | Teacher view own clock history | P1 |
| **Push Notification** | Remind clock out | P2 |

---

## Related Documentation

- **API Documentation:** [Clock API](../../api/attendance.md#teacher-clock)
- **Test Plan:** [AT02 Test Plan](../../testing/AT02-clock-test-plan.md)
- **User Journeys:** [Clock User Journeys](../../guides/attendance-user-journeys.md#teacher-clock)

---

## Verification Evidence

```bash
# Routes verified
$ php artisan route:list --path=teacher/clock
✓ teacher.clock.status (GET)
✓ teacher.clock.in (POST)
✓ teacher.clock.out (POST)

# Migrations verified
$ php artisan migrate:status | findstr attendance
✓ 2025_12_24_091410_create_teacher_attendances_table [Ran]

# Service methods verified
✓ AttendanceService::clockIn() - exists
✓ AttendanceService::clockOut() - exists
✓ AttendanceService::getClockStatus() - exists
```

---

**Last Verified:** 2025-12-28 21:45 WIB
