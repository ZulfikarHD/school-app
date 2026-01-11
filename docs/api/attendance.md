# 📡 Attendance System API Documentation

**Module:** ATT - Attendance System  
**Base URL:** `/`  
**Authentication:** Required (Session-based)  
**Last Updated:** 2026-01-12

---

## Table of Contents

1. [Teacher Attendance Routes](#teacher-attendance-routes)
2. [Parent Leave Request Routes](#parent-leave-request-routes)
3. [Admin/TU Routes](#admintu-routes)
4. [Principal Routes](#principal-routes)
5. [API Helper Routes](#api-helper-routes)
6. [Request/Response Examples](#requestresponse-examples)

---

## Teacher Attendance Routes

### 1. Daily Attendance Management

#### GET `/teacher/attendance`
**Purpose:** List attendance records dengan filter  
**Controller:** `Teacher\AttendanceController@index`  
**Auth:** Teacher role required

**Query Parameters:**
```
kelas_id    (optional) - Filter by class ID
start_date  (optional) - Filter start date (Y-m-d)
end_date    (optional) - Filter end date (Y-m-d)
```

**Response:**
```json
{
  "title": "Daftar Presensi Siswa",
  "classes": [...],
  "attendances": {
    "data": [...],
    "current_page": 1,
    "per_page": 20
  },
  "summary": {
    "total_records": 150,
    "total_hadir": 120,
    "total_izin": 15,
    "total_sakit": 10,
    "total_alpha": 5,
    "attendance_rate": "80.0"
  }
}
```

---

#### GET `/teacher/attendance/daily`
**Purpose:** Show daily attendance input form  
**Controller:** `Teacher\AttendanceController@create`  
**Auth:** Teacher (wali kelas or teacher of class)

**Query Parameters:**
```
kelas_id  (optional) - Pre-select class (edit mode)
tanggal   (optional) - Pre-select date (edit mode)
```

**Response:** Inertia page with classes list and existing attendance (if edit mode)

---

#### POST `/teacher/attendance/daily`
**Purpose:** Save or update daily attendance  
**Controller:** `Teacher\AttendanceController@store`  
**Auth:** Teacher

**Request Body:**
```json
{
  "class_id": 1,
  "tanggal": "2026-01-12",
  "attendances": [
    {
      "student_id": 101,
      "status": "H",
      "keterangan": null
    },
    {
      "student_id": 102,
      "status": "I",
      "keterangan": "Sakit ringan"
    }
  ]
}
```

**Validation Rules:**
```php
'class_id' => 'required|exists:classes,id'
'tanggal' => 'required|date|before_or_equal:today'
'attendances' => 'required|array|min:1'
'attendances.*.student_id' => 'required|exists:students,id'
'attendances.*.status' => 'required|in:H,I,S,A'
'attendances.*.keterangan' => 'nullable|string|max:255'
```

**Response:** Redirect to index with success message

---

#### GET `/teacher/attendance/check-existing`
**Purpose:** Check if attendance exists for class & date  
**Controller:** `Teacher\AttendanceController@checkExisting`  
**Auth:** Teacher

**Query Parameters:**
```
class_id  (required) - Class ID
tanggal   (required) - Date (Y-m-d)
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "student_id": 101,
      "student_nama": "Ahmad Fauzi",
      "student_nis": "2025070001",
      "status": "H",
      "keterangan": null
    }
  ]
}
```

---

### 2. Subject Attendance

#### GET `/teacher/attendance/subject`
**Purpose:** Show subject attendance form  
**Controller:** `Teacher\SubjectAttendanceController@create`  
**Auth:** Teacher

**Response:** Inertia page with teacher's schedule

---

#### POST `/teacher/attendance/subject`
**Purpose:** Save subject attendance  
**Controller:** `Teacher\SubjectAttendanceController@store`  
**Auth:** Teacher

**Request Body:**
```json
{
  "class_id": 1,
  "subject_id": 5,
  "tanggal": "2026-01-12",
  "jam_ke": 1,
  "attendances": [
    {
      "student_id": 101,
      "status": "H",
      "keterangan": null
    }
  ]
}
```

**Validation Rules:**
```php
'class_id' => 'required|exists:classes,id'
'subject_id' => 'required|exists:subjects,id'
'tanggal' => 'required|date'
'jam_ke' => 'required|integer|min:1|max:12'
'attendances' => 'required|array'
```

---

#### GET `/teacher/attendance/subject/history`
**Purpose:** View subject attendance history  
**Controller:** `Teacher\SubjectAttendanceController@index`  
**Auth:** Teacher

**Query Parameters:**
```
subject_id  (optional) - Filter by subject
class_id    (optional) - Filter by class
date        (optional) - Filter by date
```

---

### 3. Teacher Clock In/Out

#### POST `/teacher/clock/in`
**Purpose:** Clock in dengan GPS coordinates  
**Controller:** `Teacher\ClockController@clockIn`  
**Auth:** Teacher  
**Type:** API (JSON response)

**Request Body:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Validation Rules:**
```php
'latitude' => 'required|numeric|between:-90,90'
'longitude' => 'required|numeric|between:-180,180'
```

**Success Response (200):**
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

**Error Response (400):**
```json
{
  "success": false,
  "message": "Anda sudah clock in hari ini."
}
```

---

#### POST `/teacher/clock/out`
**Purpose:** Clock out dengan GPS coordinates  
**Controller:** `Teacher\ClockController@clockOut`  
**Auth:** Teacher  
**Type:** API (JSON response)

**Request Body:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Berhasil clock out",
  "data": {
    "clock_in": "07:15:30",
    "clock_out": "15:30:00",
    "duration": "8 jam 15 menit"
  }
}
```

---

#### GET `/teacher/clock/status`
**Purpose:** Get current clock status  
**Controller:** `Teacher\ClockController@status`  
**Auth:** Teacher  
**Type:** API (JSON response)

**Response:**
```json
{
  "data": {
    "is_clocked_in": true,
    "is_clocked_out": false,
    "clock_in": "07:15:30",
    "clock_out": null,
    "status": "HADIR",
    "is_late": false,
    "duration": null
  }
}
```

---

#### GET `/teacher/my-attendance`
**Purpose:** View teacher's own attendance history  
**Controller:** `Teacher\ClockController@myAttendance`  
**Auth:** Teacher

**Query Parameters:**
```
month  (optional, default: current) - Month (1-12)
year   (optional, default: current) - Year (YYYY)
```

**Response:** Inertia page with attendance records and summary

---

### 4. Leave Request Verification (Student)

#### GET `/teacher/leave-requests`
**Purpose:** List student leave requests for verification  
**Controller:** `Teacher\LeaveRequestController@index`  
**Auth:** Teacher (wali kelas)

**Query Parameters:**
```
status  (optional) - Filter by status (PENDING/APPROVED/REJECTED)
```

**Response:** Inertia page with leave requests and stats

---

#### POST `/teacher/leave-requests/{leaveRequest}/approve`
**Purpose:** Approve student leave request  
**Controller:** `Teacher\LeaveRequestController@approve`  
**Auth:** Teacher (wali kelas)

**Response:** Redirect back with success message

---

#### POST `/teacher/leave-requests/{leaveRequest}/reject`
**Purpose:** Reject student leave request  
**Controller:** `Teacher\LeaveRequestController@reject`  
**Auth:** Teacher (wali kelas)

**Request Body:**
```json
{
  "rejection_reason": "Surat tidak valid"
}
```

**Response:** Redirect back with success message

---

### 5. Teacher Leave Management (Own)

#### GET `/teacher/teacher-leaves`
**Purpose:** View own leave requests  
**Controller:** `Teacher\TeacherLeaveController@index`  
**Auth:** Teacher

---

#### GET `/teacher/teacher-leaves/create`
**Purpose:** Show leave request form  
**Controller:** `Teacher\TeacherLeaveController@create`  
**Auth:** Teacher

---

#### POST `/teacher/teacher-leaves`
**Purpose:** Submit leave request  
**Controller:** `Teacher\TeacherLeaveController@store`  
**Auth:** Teacher

**Request Body (multipart/form-data):**
```
type: IZIN|SAKIT|CUTI
start_date: 2026-01-15
end_date: 2026-01-17
reason: Keperluan keluarga
attachment: [file]
```

**Validation Rules:**
```php
'type' => 'required|in:IZIN,SAKIT,CUTI'
'start_date' => 'required|date'
'end_date' => 'required|date|after_or_equal:start_date'
'reason' => 'required|string|min:10|max:500'
'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
```

---

## Parent Leave Request Routes

#### GET `/parent/leave-requests`
**Purpose:** List leave requests with history  
**Controller:** `Parent\LeaveRequestController@index`  
**Auth:** Parent

---

#### GET `/parent/leave-requests/create`
**Purpose:** Show leave request form  
**Controller:** `Parent\LeaveRequestController@create`  
**Auth:** Parent

---

#### POST `/parent/leave-requests`
**Purpose:** Submit leave request for child  
**Controller:** `Parent\LeaveRequestController@store`  
**Auth:** Parent

**Request Body (multipart/form-data):**
```
student_id: 101
jenis: IZIN|SAKIT
tanggal_mulai: 2026-01-13
tanggal_selesai: 2026-01-13
alasan: Demam tinggi
attachment: [file]
```

**Validation Rules:**
```php
'student_id' => 'required|exists:students,id'
'jenis' => 'required|in:IZIN,SAKIT'
'tanggal_mulai' => 'required|date'
'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai'
'alasan' => 'required|string|min:10|max:500'
'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
```

**Success Response:** Redirect to index with success message

---

## Admin/TU Routes

### Student Attendance Management

#### GET `/admin/attendance/students`
**Purpose:** List all student attendance  
**Controller:** `Admin\AttendanceController@studentsIndex`  
**Auth:** Admin/TU role

**Query Parameters:**
```
class_id  (optional) - Filter by class
date      (optional) - Filter by date
status    (optional) - Filter by status (H/I/S/A)
```

---

#### GET `/admin/attendance/students/report`
**Purpose:** Generate comprehensive student attendance report  
**Controller:** `Admin\AttendanceController@generateReport`  
**Auth:** Admin/TU/Principal

**Query Parameters:**
```
start_date  (optional) - Start date
end_date    (optional) - End date
class_id    (optional) - Filter by class
status      (optional) - Filter by status
student_id  (optional) - Filter by specific student
```

**Response:** Inertia page with filtered data and statistics

---

#### GET `/admin/attendance/statistics`
**Purpose:** Get attendance statistics for dashboard  
**Controller:** `Admin\AttendanceController@getStatistics`  
**Auth:** Admin/TU  
**Type:** API (JSON response)

**Query Parameters:**
```
start_date  (optional)
end_date    (optional)
class_id    (optional)
```

**Response:**
```json
{
  "total_records": 500,
  "hadir": 400,
  "izin": 50,
  "sakit": 30,
  "alpha": 20
}
```

---

#### PUT `/admin/attendance/{attendance}`
**Purpose:** Update attendance record (manual correction)  
**Controller:** `Admin\AttendanceController@update`  
**Auth:** Admin/TU only

**Request Body:**
```json
{
  "status": "I",
  "keterangan": "Koreksi: Ada surat izin",
  "correction_reason": "Surat izin terlambat diserahkan"
}
```

**Validation Rules:**
```php
'status' => 'required|in:H,I,S,A'
'keterangan' => 'nullable|string|max:255'
'correction_reason' => 'nullable|string|max:500'
```

**Response:** Redirect back with success + audit log created

---

#### DELETE `/admin/attendance/{attendance}`
**Purpose:** Delete attendance record  
**Controller:** `Admin\AttendanceController@destroy`  
**Auth:** Admin/TU only

**Request Body:**
```json
{
  "deletion_reason": "Data duplikat"
}
```

---

### Teacher Attendance Management

#### GET `/admin/attendance/teachers`
**Purpose:** List teacher attendance  
**Controller:** `Admin\TeacherAttendanceController@index`  
**Auth:** Admin/TU/Principal

**Query Parameters:**
```
date      (optional) - Filter by date
status    (optional) - Filter by status
is_late   (optional) - Filter late teachers (true/false)
```

---

#### GET `/admin/attendance/teachers/report`
**Purpose:** Generate teacher attendance report for payroll  
**Controller:** `Admin\TeacherAttendanceController@generateReport`  
**Auth:** Admin/TU/Principal

**Query Parameters:**
```
start_date  (optional)
end_date    (optional)
teacher_id  (optional)
```

**Response:** Inertia page with work hours calculation

---

#### GET `/admin/attendance/teachers/export`
**Purpose:** Export teacher attendance to Excel  
**Controller:** `Admin\TeacherAttendanceController@exportTeachers`  
**Auth:** Admin/TU

**Status:** 📝 Placeholder (Phase 3)

---

## Principal Routes

#### GET `/principal/teacher-leaves`
**Purpose:** List teacher leave requests for approval  
**Controller:** `Principal\TeacherLeaveController@index`  
**Auth:** Principal

**Query Parameters:**
```
status  (optional, default: PENDING)
```

---

#### POST `/principal/teacher-leaves/{leave}/approve`
**Purpose:** Approve teacher leave request  
**Controller:** `Principal\TeacherLeaveController@approve`  
**Auth:** Principal

---

#### POST `/principal/teacher-leaves/{leave}/reject`
**Purpose:** Reject teacher leave request  
**Controller:** `Principal\TeacherLeaveController@reject`  
**Auth:** Principal

**Request Body:**
```json
{
  "rejection_reason": "Alasan penolakan"
}
```

---

## API Helper Routes

### Get Students by Class

#### GET `/api/classes/{class}/students`
**Purpose:** Fetch student list for selected class  
**Auth:** Teacher role  
**Type:** API (JSON response)

**Response:**
```json
{
  "data": [
    {
      "id": 101,
      "nis": "2025070001",
      "nama_lengkap": "Ahmad Fauzi",
      "jenis_kelamin": "L",
      "kelas_id": 1,
      "status": "aktif"
    }
  ]
}
```

---

### Search Students

#### GET `/api/students/search`
**Purpose:** Search students by name or NIS  
**Auth:** Teacher role  
**Type:** API (JSON response)

**Query Parameters:**
```
q  (required) - Search query
```

**Response:**
```json
{
  "data": [
    {
      "id": 101,
      "nama_lengkap": "Ahmad Fauzi",
      "nis": "2025070001",
      "kelas": {
        "id": 1,
        "nama_lengkap": "Kelas 7A"
      }
    }
  ]
}
```

---

### Get Student Attendance

#### GET `/api/students/{student}/attendance`
**Purpose:** Fetch attendance history for specific student  
**Auth:** Teacher role  
**Type:** API (JSON response)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "tanggal": "2026-01-12",
      "status": "H",
      "keterangan": null,
      "recorded_by": {
        "id": 5,
        "name": "Pak Budi"
      }
    }
  ]
}
```

---

## Error Responses

### Common Error Codes

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
  "message": "This action is unauthorized."
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "tanggal": [
      "The tanggal field is required."
    ]
  }
}
```

**500 Server Error:**
```json
{
  "message": "Server Error",
  "exception": "..."
}
```

---

## Rate Limiting

- **Default:** 60 requests per minute
- **Auth Routes:** 5 attempts per minute
- **API Routes:** 60 requests per minute

---

## Notes

- All dates in `Y-m-d` format (2026-01-12)
- All times in `H:i:s` format (07:15:30)
- Timezone: Asia/Jakarta (WIB)
- File uploads max 5MB
- Attendance records use `updateOrCreate` for upsert logic
- GPS coordinates stored as decimal(10,8) and decimal(11,8)

---

## Related Documentation

- **Feature Doc:** [ATT Attendance System](../features/attendance/ATT-attendance-system.md)
- **Test Plan:** [ATT Test Plan](../testing/ATT-test-plan.md)
- **Requirements:** `school-reqs-main/02_Functional_Requirements/03_Attendance_System.md`

---

**Last Updated:** 2026-01-12  
**API Version:** 1.0  
**Status:** ✅ Production Ready
