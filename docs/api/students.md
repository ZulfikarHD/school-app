# API Documentation: Students

> **Resource:** Students Management
> **Base Path:** `/admin/students`
> **Authentication:** Required (Admin/Superadmin only)
> **Last Updated:** 2025-12-24

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Endpoints](#endpoints)
4. [Data Structures](#data-structures)
5. [Error Codes](#error-codes)

---

## Overview

Student API merupakan endpoint collection untuk mengelola data siswa yang mencakup CRUD operations, class assignment, status management, dan import/export functionality dengan role-based access control untuk Admin dan Superadmin.

**Available Operations:**
- List students dengan pagination & filtering
- View student detail dengan relations
- Create/Update student data
- Delete student (soft delete)
- Assign students to class (single/bulk)
- Update student status
- Import/Export students via Excel

---

## Authentication

Semua endpoint memerlukan authentication token dan role validation.

**Required Headers:**
```http
Cookie: laravel_session={session_token}
X-CSRF-TOKEN: {csrf_token}
X-Requested-With: XMLHttpRequest
```

**Authorized Roles:**
- `SUPERADMIN` - Full access
- `ADMIN` - Full access
- `TEACHER` - Read-only access (limited)

---

## Endpoints

### 1. List Students

Mengambil daftar siswa dengan pagination, filtering, dan searching.

**Endpoint:** `GET /admin/students`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Nomor halaman |
| per_page | integer | No | 15 | Items per page |
| search | string | No | - | Search by nama/NIS/NISN |
| status | string | No | - | Filter: ACTIVE, INACTIVE, GRADUATED, DROPPED_OUT |
| kelas_id | integer | No | - | Filter by class ID |
| tingkat | integer | No | - | Filter by grade level (1-6) |
| tahun_ajaran | string | No | - | Filter by academic year |

**Success Response:** `200 OK`

```json
{
    "students": {
        "data": [
            {
                "id": 1,
                "nis": "2024001",
                "nisn": "1234567890",
                "nama_lengkap": "Ahmad Rizki Maulana",
                "nama_panggilan": "Rizki",
                "tempat_lahir": "Jakarta",
                "tanggal_lahir": "2010-05-15",
                "jenis_kelamin": "L",
                "agama": "ISLAM",
                "kewarganegaraan": "WNI",
                "anak_ke": 2,
                "jumlah_saudara": 3,
                "status": "ACTIVE",
                "kelas_id": 5,
                "kelas": {
                    "id": 5,
                    "nama_lengkap": "1A",
                    "tahun_ajaran": "2024/2025"
                },
                "guardians": [
                    {
                        "id": 1,
                        "nama": "Budi Maulana",
                        "hubungan": "AYAH",
                        "is_primary_contact": true
                    }
                ],
                "primary_guardian": {
                    "id": 1,
                    "nama": "Budi Maulana",
                    "no_hp": "081234567890"
                },
                "foto": "students/photo123.jpg",
                "created_at": "2024-12-01T10:30:00.000000Z",
                "updated_at": "2024-12-24T08:15:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150
    },
    "filters": {
        "search": null,
        "status": null,
        "kelas_id": null
    },
    "classes": [
        {
            "id": 1,
            "nama_lengkap": "1A",
            "tahun_ajaran": "2024/2025"
        }
    ]
}
```

---

### 2. Show Student Detail

Mengambil detail lengkap siswa beserta guardians, class history, dan status history.

**Endpoint:** `GET /admin/students/{id}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Student ID |

**Success Response:** `200 OK`

```json
{
    "student": {
        "id": 1,
        "nis": "2024001",
        "nisn": "1234567890",
        "nama_lengkap": "Ahmad Rizki Maulana",
        "kelas_id": 5,
        "kelas": {
            "id": 5,
            "nama_lengkap": "1A",
            "tahun_ajaran": "2024/2025",
            "wali_kelas": {
                "id": 10,
                "name": "Siti Nurhaliza, S.Pd"
            }
        },
        "guardians": [
            {
                "id": 1,
                "nama": "Budi Maulana",
                "hubungan": "AYAH",
                "no_hp": "081234567890",
                "email": "budi@example.com",
                "pekerjaan": "Wiraswasta",
                "is_primary_contact": true
            }
        ],
        "class_history": [
            {
                "id": 1,
                "kelas_id": 5,
                "kelas": {
                    "nama_lengkap": "1A"
                },
                "tahun_ajaran": "2024/2025",
                "wali_kelas": "Siti Nurhaliza, S.Pd",
                "notes": "Naik kelas dari TK",
                "created_at": "2024-07-15T08:00:00.000000Z"
            }
        ],
        "status_history": [
            {
                "id": 1,
                "old_status": null,
                "new_status": "ACTIVE",
                "reason": "Pendaftaran baru",
                "changed_by": {
                    "id": 2,
                    "name": "Admin User"
                },
                "created_at": "2024-07-15T08:00:00.000000Z"
            }
        ]
    },
    "classes": [...]
}
```

**Error Response:** `404 Not Found`

```json
{
    "message": "Student not found"
}
```

---

### 3. Create Student

Membuat siswa baru dengan data lengkap.

**Endpoint:** `POST /admin/students`

**Request Body:**

```json
{
    "nis": "2024050",
    "nisn": "9876543210",
    "nama_lengkap": "Siti Aisyah",
    "nama_panggilan": "Aisyah",
    "tempat_lahir": "Bandung",
    "tanggal_lahir": "2011-03-20",
    "jenis_kelamin": "P",
    "agama": "ISLAM",
    "kewarganegaraan": "WNI",
    "anak_ke": 1,
    "jumlah_saudara": 2,
    "alamat": "Jl. Merdeka No. 123",
    "rt": "001",
    "rw": "005",
    "kelurahan": "Kebon Jeruk",
    "kecamatan": "Kebon Jeruk",
    "kota": "Jakarta Barat",
    "provinsi": "DKI Jakarta",
    "kode_pos": "11530",
    "no_hp": "081234567890",
    "email": "aisyah@example.com",
    "tahun_ajaran_masuk": "2024/2025",
    "foto": "(file upload)",
    "status": "ACTIVE"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| nis | string | Yes | unique, max:20 |
| nisn | string | No | unique, max:20 |
| nama_lengkap | string | Yes | max:100 |
| tanggal_lahir | date | Yes | before:today |
| jenis_kelamin | string | Yes | enum: L, P |
| agama | string | Yes | enum: ISLAM, KRISTEN, KATOLIK, HINDU, BUDDHA, KONGHUCU |
| no_hp | string | No | numeric, min:10, max:15 |
| email | string | No | email, unique |
| foto | file | No | image, max:2MB |

**Success Response:** `201 Created`

```json
{
    "message": "Siswa berhasil ditambahkan",
    "student": {
        "id": 51,
        "nis": "2024050",
        "nama_lengkap": "Siti Aisyah",
        ...
    }
}
```

**Error Response:** `422 Unprocessable Entity`

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "nis": ["NIS sudah digunakan"],
        "email": ["Format email tidak valid"]
    }
}
```

---

### 4. Update Student

Mengupdate data siswa existing.

**Endpoint:** `PUT /admin/students/{id}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Student ID |

**Request Body:**
(Same structure as Create, semua field optional)

**Success Response:** `200 OK`

```json
{
    "message": "Data siswa berhasil diperbarui",
    "student": {...}
}
```

---

### 5. Delete Student

Menghapus siswa (soft delete).

**Endpoint:** `DELETE /admin/students/{id}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Student ID |

**Success Response:** `200 OK`

```json
{
    "message": "Siswa berhasil dihapus"
}
```

**Error Response:** `404 Not Found`

```json
{
    "message": "Student not found"
}
```

---

### 6. Assign Students to Class ⭐

Memindahkan satu atau multiple siswa ke kelas baru dengan riwayat.

**Endpoint:** `POST /admin/students/assign-class`

**Request Body:**

```json
{
    "student_ids": [1, 2, 3, 15],
    "kelas_id": 8,
    "tahun_ajaran": "2024/2025",
    "notes": "Promosi kelas berdasarkan nilai"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| student_ids | array | Yes | min:1, each must exist in students table |
| kelas_id | integer | Yes | must exist in classes table |
| tahun_ajaran | string | No | regex: `^\d{4}/\d{4}$` (default: 2024/2025) |
| notes | string | No | max:255 |

**Success Response:** `302 Redirect` (back with success message)

```json
{
    "message": "4 siswa berhasil dipindahkan ke kelas."
}
```

**Error Response:** `422 Unprocessable Entity`

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "student_ids": ["Pilih minimal 1 siswa untuk dipindahkan."],
        "kelas_id": ["Kelas tujuan tidak ditemukan."]
    }
}
```

**Business Logic:**
- Hanya siswa yang `kelas_id`-nya berbeda yang dicatat di history
- Wali kelas otomatis tercatat dari relasi `SchoolClass->waliKelas`
- Semua operasi dalam database transaction
- Activity log dicatat dengan `student_count` dan `kelas_id`

---

### 7. Bulk Promote Students (Naik Kelas Massal) ⭐

Menaikkan multiple siswa ke kelas berikutnya secara bersamaan dengan wizard.

**Endpoint:** `POST /admin/students/promote`

**Request Body:**

```json
{
    "student_ids": [1, 2, 3, 5, 8, 10],
    "kelas_id_baru": 12,
    "tahun_ajaran_baru": "2025/2026",
    "wali_kelas": "Ibu Siti Nurhaliza" // Optional, auto-detected
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| student_ids | array | Yes | min:1, each must exist in students table |
| kelas_id_baru | integer | Yes | must exist in classes table |
| tahun_ajaran_baru | string | Yes | regex: `^\d{4}/\d{4}$` |
| wali_kelas | string | No | max:100, auto-filled from SchoolClass relation |

**Success Response:** `302 Redirect` (back with success message)

```json
{
    "message": "6 siswa berhasil dipindahkan ke kelas baru."
}
```

**Error Response:** `422 Unprocessable Entity`

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "student_ids": ["Pilih minimal 1 siswa untuk dipromote."],
        "tahun_ajaran_baru": ["Format tahun ajaran harus YYYY/YYYY"]
    }
}
```

**Business Logic:**
- Batch processing untuk efisiensi (multiple students dalam 1 transaction)
- Wali kelas otomatis tercatat dari relasi `SchoolClass->waliKelas` jika tidak diisi
- Student class history dibuat untuk setiap siswa yang dipromote
- `students.kelas_id` diupdate ke kelas baru
- Semua operasi atomic (rollback jika error)
- Activity log dicatat dengan `student_count` dan metadata promosi

**Wizard Page:** `GET /admin/students/promote`
- Returns Inertia page dengan data classes untuk wizard

---

### 8. Update Student Status

Mengubah status siswa dengan alasan.

**Endpoint:** `POST /admin/students/{id}/update-status`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Student ID |

**Request Body:**

```json
{
    "status": "INACTIVE",
    "reason": "Sakit berkepanjangan, mengambil cuti"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| status | string | Yes | enum: ACTIVE, INACTIVE, GRADUATED, DROPPED_OUT |
| reason | string | Yes | min:10, max:500 |

**Success Response:** `200 OK`

```json
{
    "message": "Status siswa berhasil diperbarui"
}
```

---

### 9. Export Students

Export data siswa ke Excel.

**Endpoint:** `GET /admin/students/export`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| kelas_id | integer | No | Filter by class |
| status | string | No | Filter by status |
| format | string | No | excel (default) |

**Success Response:** `200 OK` (File Download)

**Content-Type:** `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`

**Filename:** `students_YYYY-MM-DD_HH-mm-ss.xlsx`

---

### 10. Import Students Preview

Preview import data sebelum confirm.

**Endpoint:** `POST /admin/students/import/preview`

**Request Body (multipart/form-data):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| file | file | Yes | Excel file (.xlsx, .xls) |

**Success Response:** `200 OK`

```json
{
    "preview": [
        {
            "row": 2,
            "nis": "2024100",
            "nama_lengkap": "John Doe",
            "status": "valid"
        },
        {
            "row": 3,
            "nis": "2024101",
            "nama_lengkap": "Jane Smith",
            "status": "error",
            "errors": ["NIS sudah digunakan"]
        }
    ],
    "summary": {
        "total": 50,
        "valid": 48,
        "errors": 2
    }
}
```

---

### 11. Import Students Confirm

Konfirmasi dan simpan import data.

**Endpoint:** `POST /admin/students/import`

**Request Body (multipart/form-data):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| file | file | Yes | Excel file (.xlsx, .xls) |
| skip_errors | boolean | No | Skip rows dengan error |

**Success Response:** `200 OK`

```json
{
    "message": "48 siswa berhasil diimport",
    "imported": 48,
    "skipped": 2
}
```

---

## Data Structures

### Student Object

```typescript
interface Student {
    id: number;
    nis: string;
    nisn: string | null;
    nama_lengkap: string;
    nama_panggilan: string | null;
    tempat_lahir: string;
    tanggal_lahir: string; // YYYY-MM-DD
    jenis_kelamin: 'L' | 'P';
    agama: 'ISLAM' | 'KRISTEN' | 'KATOLIK' | 'HINDU' | 'BUDDHA' | 'KONGHUCU';
    kewarganegaraan: 'WNI' | 'WNA';
    anak_ke: number;
    jumlah_saudara: number;
    alamat: string;
    rt: string;
    rw: string;
    kelurahan: string;
    kecamatan: string;
    kota: string;
    provinsi: string;
    kode_pos: string;
    no_hp: string | null;
    email: string | null;
    foto: string | null;
    status: 'ACTIVE' | 'INACTIVE' | 'GRADUATED' | 'DROPPED_OUT';
    kelas_id: number | null;
    tahun_ajaran_masuk: string; // YYYY/YYYY
    kelas?: SchoolClass;
    guardians?: Guardian[];
    primary_guardian?: Guardian;
    class_history?: StudentClassHistory[];
    status_history?: StudentStatusHistory[];
    created_at: string;
    updated_at: string;
}
```

### SchoolClass Object

```typescript
interface SchoolClass {
    id: number;
    tingkat: number; // 1-6
    nama: string; // A, B, C, D
    nama_lengkap: string; // e.g., "1A", "6D"
    wali_kelas_id: number | null;
    wali_kelas?: User;
    kapasitas: number;
    tahun_ajaran: string; // YYYY/YYYY
    is_active: boolean;
    created_at: string;
    updated_at: string;
}
```

### StudentClassHistory Object

```typescript
interface StudentClassHistory {
    id: number;
    student_id: number;
    kelas_id: number;
    kelas?: SchoolClass;
    tahun_ajaran: string;
    wali_kelas: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}
```

---

## Error Codes

| Code | HTTP Status | Description | Solution |
|------|-------------|-------------|----------|
| `VALIDATION_ERROR` | 422 | Request body tidak valid | Check validation rules |
| `UNAUTHORIZED` | 401 | Token tidak valid atau expired | Re-login |
| `FORBIDDEN` | 403 | Tidak punya akses ke resource | Check user role |
| `RESOURCE_NOT_FOUND` | 404 | Student/Class tidak ditemukan | Verify ID exists |
| `CONFLICT` | 409 | NIS/NISN sudah digunakan | Use unique identifier |
| `INTERNAL_ERROR` | 500 | Server error | Check logs, contact admin |

---

## Rate Limiting

| Endpoint | Rate Limit | Window |
|----------|------------|--------|
| All endpoints | 60 requests | 1 minute |
| Import/Export | 5 requests | 1 minute |

---

## Changelog

| Date | Version | Changes |
|------|---------|---------|
| 2024-12-24 | 1.0.0 | Initial API documentation |
| 2024-12-24 | 1.1.0 | Added assign-class endpoint |
| 2024-12-24 | 1.2.0 | Added bulk promote endpoint (AD04) |

---

**Last Updated:** 2025-12-24
**Maintained By:** Development Team
**API Version:** v1
