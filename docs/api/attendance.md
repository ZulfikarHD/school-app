# Attendance API Documentation

**Last Updated:** 2025-12-28  
**Version:** 1.0

---

## Overview

API documentation untuk sistem attendance management yang mencakup daily attendance input, teacher clock in/out, dan leave request management.

---

## Authentication

Semua endpoint memerlukan authentication via Laravel session dan CSRF token.

```http
Headers:
  X-CSRF-TOKEN: {csrf_token}
  Cookie: laravel_session={session_id}
```

---

## Teacher Attendance Endpoints

### 1. Get Attendance Form

**Endpoint:** `GET /teacher/attendance/daily`  
**Name:** `teacher.attendance.daily.create`  
**Middleware:** `auth`, `role:TEACHER`

**Response:**
```json
{
  "title": "Input Presensi Harian",
  "classes": [
    {
      "id": 1,
      "tingkat": 4,
      "nama": "A",
      "nama_lengkap": "Kelas 4A",
      "tahun_ajaran": "2024/2025",
      "jumlah_siswa": 30
    }
  ]
}
```

---

### 2. Store Daily Attendance

**Endpoint:** `POST /teacher/attendance/daily`  
**Name:** `teacher.attendance.daily.store`  
**Middleware:** `auth`, `role:TEACHER`

**Request Body:**
```json
{
  "class_id": 1,
  "tanggal": "2025-12-28",
  "attendances": [
    {
      "student_id": 1,
      "status": "H",
      "keterangan": null
    },
    {
      "student_id": 2,
      "status": "A",
      "keterangan": "Tidak ada kabar"
    }
  ]
}
```

**Validation Rules:**
- `class_id`: required, exists:classes,id
- `tanggal`: required, date, before_or_equal:today
- `attendances`: required, array, min:1
- `attendances.*.student_id`: required, exists:students,id
- `attendances.*.status`: required, in:H,I,S,A
- `attendances.*.keterangan`: nullable, string, max:500

**Success Response (302):**
```
Redirect to: /teacher/attendance
Flash Message: "Berhasil menyimpan presensi untuk 30 siswa."
```

**Error Response (403):**
```json
{
  "message": "Anda tidak memiliki akses untuk input attendance kelas ini."
}
```

---

### 3. Get Students by Class

**Endpoint:** `GET /teacher/api/classes/{classId}/students`  
**Name:** `teacher.api.classes.students`  
**Middleware:** `auth`, `role:TEACHER`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "nis": "2024001",
      "nama_lengkap": "Ahmad Fauzi",
      "jenis_kelamin": "L",
      "kelas_id": 1,
      "status": "aktif"
    }
  ]
}
```

---

## Teacher Clock Endpoints

### 1. Get Clock Status

**Endpoint:** `GET /teacher/clock/status`  
**Name:** `teacher.clock.status`  
**Middleware:** `auth`, `role:TEACHER`

**Response:**
```json
{
  "data": {
    "is_clocked_in": true,
    "is_clocked_out": false,
    "clock_in": "07:15:34",
    "clock_out": null,
    "status": "TERLAMBAT",
    "is_late": true,
    "duration": null
  }
}
```

---

### 2. Clock In

**Endpoint:** `POST /teacher/clock/in`  
**Name:** `teacher.clock.in`  
**Middleware:** `auth`, `role:TEACHER`

**Request Body:**
```json
{
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Validation Rules:**
- `latitude`: required, numeric, between:-90,90
- `longitude`: required, numeric, between:-180,180

**Success Response (200):**
```json
{
  "success": true,
  "message": "Berhasil clock in",
  "data": {
    "clock_in": "07:15:34",
    "status": "TERLAMBAT",
    "is_late": true
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Anda sudah clock in hari ini"
}
```

---

### 3. Clock Out

**Endpoint:** `POST /teacher/clock/out`  
**Name:** `teacher.clock.out`  
**Middleware:** `auth`, `role:TEACHER`

**Request Body:**
```json
{
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Berhasil clock out",
  "data": {
    "clock_out": "15:30:00",
    "duration": "08:14:26"
  }
}
```

---

## Parent Leave Request Endpoints

### 1. Get Leave Requests

**Endpoint:** `GET /parent/leave-requests`  
**Name:** `parent.leave-requests.index`  
**Middleware:** `auth`, `role:PARENT`

**Response:**
```json
{
  "title": "Riwayat Permohonan Izin",
  "leaveRequests": [
    {
      "id": 1,
      "student_id": 15,
      "jenis": "SAKIT",
      "tanggal_mulai": "2025-12-28",
      "tanggal_selesai": "2025-12-29",
      "alasan": "Demam tinggi 39°C",
      "attachment_path": "leave-attachments/abc123.jpg",
      "status": "PENDING",
      "created_at": "2025-12-28T06:30:00.000000Z",
      "student": {
        "id": 15,
        "nama_lengkap": "Ahmad Fauzi",
        "kelas": {
          "nama_lengkap": "Kelas 4A"
        }
      }
    }
  ]
}
```

---

### 2. Create Leave Request Form

**Endpoint:** `GET /parent/leave-requests/create`  
**Name:** `parent.leave-requests.create`  
**Middleware:** `auth`, `role:PARENT`

**Response:**
```json
{
  "title": "Ajukan Permohonan Izin",
  "children": [
    {
      "id": 15,
      "nama_lengkap": "Ahmad Fauzi",
      "kelas": {
        "nama_lengkap": "Kelas 4A"
      }
    }
  ]
}
```

---

### 3. Store Leave Request

**Endpoint:** `POST /parent/leave-requests`  
**Name:** `parent.leave-requests.store`  
**Middleware:** `auth`, `role:PARENT`

**Request Body (multipart/form-data):**
```
student_id: 15
jenis: SAKIT
tanggal_mulai: 2025-12-28
tanggal_selesai: 2025-12-29
alasan: Demam tinggi 39°C
attachment: [File]
```

**Validation Rules:**
- `student_id`: required, exists:students,id
- `jenis`: required, in:IZIN,SAKIT
- `tanggal_mulai`: required, date, after_or_equal:today
- `tanggal_selesai`: required, date, after_or_equal:tanggal_mulai
- `alasan`: required, string, max:1000
- `attachment`: nullable, file, max:2048, mimes:jpg,jpeg,png,pdf

**Success Response (302):**
```
Redirect to: /parent/leave-requests
Flash Message: "Permohonan izin berhasil diajukan dan menunggu persetujuan."
```

---

## Teacher Leave Verification Endpoints

### 1. Get Pending Leave Requests

**Endpoint:** `GET /teacher/leave-requests`  
**Name:** `teacher.leave-requests.index`  
**Middleware:** `auth`, `role:TEACHER`

**Response:**
```json
{
  "title": "Verifikasi Permohonan Izin",
  "leaveRequests": [
    {
      "id": 1,
      "student": {
        "nama_lengkap": "Ahmad Fauzi",
        "kelas": {
          "nama_lengkap": "Kelas 4A"
        }
      },
      "jenis": "SAKIT",
      "tanggal_mulai": "2025-12-28",
      "tanggal_selesai": "2025-12-29",
      "alasan": "Demam tinggi 39°C",
      "attachment_path": "leave-attachments/abc123.jpg",
      "status": "PENDING",
      "submitted_by_user": {
        "name": "Ibu Siti"
      }
    }
  ]
}
```

---

### 2. Approve/Reject Leave Request

**Endpoint:** `POST /teacher/leave-requests/{id}/approve`  
**Name:** `teacher.leave-requests.approve`  
**Middleware:** `auth`, `role:TEACHER`

**Request Body (Approve):**
```json
{
  "action": "approve"
}
```

**Request Body (Reject):**
```json
{
  "action": "reject",
  "rejection_reason": "Dokumen tidak lengkap, mohon upload surat dokter"
}
```

**Validation Rules:**
- `action`: required, in:approve,reject
- `rejection_reason`: required_if:action,reject, string, max:1000

**Success Response (302):**
```
Redirect to: /teacher/leave-requests
Flash Message: "Permohonan izin berhasil disetujui." / "Permohonan izin berhasil ditolak."
```

**Auto-Sync Behavior (on approve):**
- Creates `StudentAttendance` records for each date in range
- Status set to 'I' (Izin) or 'S' (Sakit) based on `jenis`
- Keterangan: "Auto-generated from leave request: {alasan}"

---

## Error Responses

### 400 Bad Request
```json
{
  "message": "Validation failed",
  "errors": {
    "tanggal": ["Tanggal tidak boleh lebih dari hari ini."],
    "attendances.0.status": ["Status hanya boleh H, I, S, atau A."]
  }
}
```

### 403 Forbidden
```json
{
  "message": "Anda tidak memiliki akses untuk resource ini."
}
```

### 404 Not Found
```json
{
  "message": "Resource tidak ditemukan."
}
```

### 422 Unprocessable Entity
```json
{
  "message": "Terdapat siswa yang duplikat dalam daftar attendance."
}
```

---

## Rate Limiting

| Endpoint Group | Limit | Window |
|----------------|-------|--------|
| Clock endpoints | 10 requests | 1 minute |
| Attendance endpoints | 60 requests | 1 minute |
| Leave request endpoints | 30 requests | 1 minute |

---

## File Storage

**Leave Request Attachments:**
- Path: `storage/app/public/leave-attachments/`
- Public URL: `/storage/leave-attachments/{filename}`
- Max Size: 2MB
- Allowed Types: JPG, JPEG, PNG, PDF

---

**Last Updated:** 2025-12-28 21:45 WIB
