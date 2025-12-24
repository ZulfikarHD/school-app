# API Documentation: Attendance Management

## Overview

API untuk mengelola attendance siswa dan guru yang mencakup daily attendance, subject attendance, leave requests, dan teacher clock in/out dengan GPS tracking untuk transparansi dan akurasi data kehadiran.

Base URL: `{APP_URL}`

---

## Authentication

Semua endpoint memerlukan authentication Laravel Sanctum/Session:
```
Cookie: laravel_session=...
X-CSRF-TOKEN: ...
```

---

## Endpoints

### Teacher: Student Attendance

#### 1. View Attendance List

Menampilkan daftar attendance records untuk monitoring.

**Endpoint:** `GET /teacher/attendance`

**Authorization:** `role:TEACHER`

**Response:** Inertia page (`Teacher/Attendance/Index`)

---

#### 2. Show Daily Attendance Form

Form untuk input attendance harian.

**Endpoint:** `GET /teacher/attendance/daily`

**Authorization:** `role:TEACHER`

**Response:** Inertia page (`Teacher/Attendance/Create`)

---

#### 3. Store Daily Attendance

Menyimpan attendance harian untuk multiple siswa.

**Endpoint:** `POST /teacher/attendance/daily`

**Authorization:** `role:TEACHER` + must be wali kelas or teach in class

**Request Body:**
```json
{
  "class_id": 1,
  "tanggal": "2025-12-24",
  "attendances": [
    {
      "student_id": 10,
      "status": "H",
      "keterangan": null
    },
    {
      "student_id": 11,
      "status": "I",
      "keterangan": "Izin sakit"
    },
    {
      "student_id": 12,
      "status": "A",
      "keterangan": null
    }
  ]
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| class_id | integer | Yes | exists:classes,id |
| tanggal | string | Yes | date, before_or_equal:today |
| attendances | array | Yes | min:1 |
| attendances.*.student_id | integer | Yes | exists:students,id |
| attendances.*.status | string | Yes | in:H,I,S,A |
| attendances.*.keterangan | string | No | max:500 |

**Response:** `302 Redirect`
```
Redirect to: teacher.attendance.index
Flash: "Berhasil menyimpan presensi untuk {count} siswa."
```

**Error Response:** `422 Unprocessable Entity`
```json
{
  "message": "The attendances.0.status field must be one of: H, I, S, A.",
  "errors": {
    "attendances.0.status": [
      "Status hanya boleh H (Hadir), I (Izin), S (Sakit), atau A (Alpha)."
    ]
  }
}
```

---

### Teacher: Subject Attendance

#### 4. Show Subject Attendance Form

Form untuk input attendance per mata pelajaran.

**Endpoint:** `GET /teacher/attendance/subject`

**Authorization:** `role:TEACHER`

**Response:** Inertia page with teacher schedule

---

#### 5. Store Subject Attendance

Menyimpan attendance per mata pelajaran untuk satu sesi.

**Endpoint:** `POST /teacher/attendance/subject`

**Authorization:** `role:TEACHER` + must teach subject in class

**Request Body:**
```json
{
  "class_id": 1,
  "subject_id": 3,
  "tanggal": "2025-12-24",
  "jam_ke": 2,
  "attendances": [
    {
      "student_id": 10,
      "status": "H",
      "keterangan": null
    }
  ]
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| class_id | integer | Yes | exists:classes,id |
| subject_id | integer | Yes | exists:subjects,id |
| tanggal | string | Yes | date, before_or_equal:today |
| jam_ke | integer | Yes | min:1, max:10 |
| attendances | array | Yes | min:1 |
| attendances.*.student_id | integer | Yes | exists:students,id |
| attendances.*.status | string | Yes | in:H,I,S,A |

**Response:** `302 Redirect` with success message

---

### Teacher: Clock In/Out

#### 6. Clock In

Teacher clock in dengan GPS coordinates.

**Endpoint:** `POST /teacher/clock/in`

**Authorization:** `role:TEACHER`

**Content-Type:** `application/json`

**Request Body:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| latitude | numeric | Yes | between:-90,90 |
| longitude | numeric | Yes | between:-180,180 |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Berhasil clock in",
  "data": {
    "clock_in": "07:15:30",
    "status": "HADIR",
    "is_late": false
  }
}
```

**Error Response:** `400 Bad Request`
```json
{
  "success": false,
  "message": "Anda sudah clock in hari ini."
}
```

---

#### 7. Clock Out

Teacher clock out dengan GPS coordinates.

**Endpoint:** `POST /teacher/clock/out`

**Authorization:** `role:TEACHER`

**Request Body:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Berhasil clock out",
  "data": {
    "clock_in": "07:15:30",
    "clock_out": "14:30:00",
    "duration": "7 jam 14 menit"
  }
}
```

---

#### 8. Check Clock Status

Mendapatkan status clock in hari ini untuk widget.

**Endpoint:** `GET /teacher/clock/status`

**Authorization:** `role:TEACHER`

**Response:** `200 OK`
```json
{
  "is_clocked_in": true,
  "attendance": {
    "id": 45,
    "teacher_id": 5,
    "tanggal": "2025-12-24",
    "clock_in": "07:15:30",
    "clock_out": null,
    "status": "HADIR",
    "is_late": false
  }
}
```

---

### Teacher: Leave Verification

#### 9. View Leave Requests

List permohonan izin untuk siswa di kelas yang diampu.

**Endpoint:** `GET /teacher/leave-requests`

**Authorization:** `role:TEACHER`

**Response:** Inertia page dengan pending leave requests

---

#### 10. Approve/Reject Leave Request

Approve atau reject permohonan izin.

**Endpoint:** `POST /teacher/leave-requests/{leaveRequest}/approve`

**Authorization:** `role:TEACHER` (wali kelas) or `role:ADMIN,PRINCIPAL`

**Request Body:**
```json
{
  "action": "approve",
  "rejection_reason": null
}
```

OR

```json
{
  "action": "reject",
  "rejection_reason": "Tidak ada surat keterangan dokter"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| action | string | Yes | in:approve,reject |
| rejection_reason | string | Conditional | required_if:action,reject, min:10, max:500 |

**Response:** `302 Redirect`
```
Flash: "Permohonan izin berhasil disetujui." / "ditolak."
```

**Side Effect:** If approved, creates `StudentAttendance` records for date range with status I/S.

---

### Parent: Leave Requests

#### 11. View My Leave Requests

List permohonan izin yang sudah diajukan.

**Endpoint:** `GET /parent/leave-requests`

**Authorization:** `role:PARENT`

**Response:** Inertia page dengan leave requests history

---

#### 12. Show Create Leave Form

Form untuk ajukan permohonan izin baru.

**Endpoint:** `GET /parent/leave-requests/create`

**Authorization:** `role:PARENT`

**Response:** Inertia page dengan list anak

---

#### 13. Submit Leave Request

Mengajukan permohonan izin/sakit untuk anak.

**Endpoint:** `POST /parent/leave-requests`

**Authorization:** `role:PARENT` + must be parent of student

**Content-Type:** `multipart/form-data`

**Request Body:**
```
student_id=10
jenis=SAKIT
tanggal_mulai=2025-12-25
tanggal_selesai=2025-12-27
alasan=Demam tinggi sejak semalam
attachment=[FILE]
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| student_id | integer | Yes | exists:students,id |
| jenis | string | Yes | in:IZIN,SAKIT |
| tanggal_mulai | string | Yes | date, after_or_equal:today |
| tanggal_selesai | string | Yes | date, after_or_equal:tanggal_mulai |
| alasan | string | Yes | min:10, max:1000 |
| attachment | file | No | mimes:pdf,jpg,jpeg,png, max:2048 |

**Response:** `302 Redirect`
```
Redirect to: parent.leave-requests.index
Flash: "Permohonan izin berhasil diajukan dan menunggu persetujuan."
```

---

### Admin: Reports

#### 14. Student Attendance Report

Rekap presensi siswa dengan filter.

**Endpoint:** `GET /admin/attendance/students`

**Authorization:** `role:SUPERADMIN,ADMIN`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| class_id | integer | No | - | Filter by class |
| date | string | No | - | Filter by date (Y-m-d) |
| status | string | No | - | Filter by status (H/I/S/A) |
| page | integer | No | 1 | Pagination |

**Response:** Inertia page dengan paginated attendances

---

#### 15. Teacher Attendance Report

Rekap presensi guru.

**Endpoint:** `GET /admin/attendance/teachers`

**Authorization:** `role:SUPERADMIN,ADMIN`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| date | string | No | today | Filter by date |
| status | string | No | - | Filter by status |
| is_late | boolean | No | - | Filter by lateness |
| page | integer | No | 1 | Pagination |

**Response:** Inertia page dengan summary statistics

---

## Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 302 | Redirect - Form submitted, redirecting |
| 401 | Unauthorized - Not authenticated |
| 403 | Forbidden - No permission |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error |

---

## Error Response Format

```json
{
  "message": "The attendances.0.status field is required.",
  "errors": {
    "attendances.0.status": [
      "Status attendance wajib diisi."
    ]
  }
}
```

---

## Attendance Status Values

| Code | Label | Description |
|------|-------|-------------|
| H | Hadir | Student present |
| I | Izin | Excused absence with permission |
| S | Sakit | Sick leave |
| A | Alpha | Unexcused absence |

---

## Leave Request Status Values

| Status | Description |
|--------|-------------|
| PENDING | Waiting for approval |
| APPROVED | Approved by teacher/admin |
| REJECTED | Rejected with reason |

---

## Teacher Attendance Status Values

| Status | Description |
|--------|-------------|
| HADIR | Present, on time |
| TERLAMBAT | Present, but late (> 07:30) |
| IZIN | Excused absence |
| SAKIT | Sick leave |
| ALPHA | Unexcused absence |

---

*Last Updated: 2025-12-24*
*API Version: 1.0*
