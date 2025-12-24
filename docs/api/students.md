# Student Management API Documentation

**Resource:** Students & Guardians  
**Base Path:** `/admin/students`, `/parent/children`  
**Authentication:** Required  
**Last Updated:** 24 Desember 2025

---

## Admin Endpoints

### List Students

**Endpoint:** `GET /admin/students`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Retrieve paginated list of students dengan filter dan search capabilities

**Query Parameters:**

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search by nama, NIS, atau NISN | `?search=Budi` |
| `kelas_id` | integer | No | Filter by class ID | `?kelas_id=1` |
| `status` | string | No | Filter by status (aktif/mutasi/do/lulus) | `?status=aktif` |
| `tahun_ajaran` | string | No | Filter by academic year | `?tahun_ajaran=2024/2025` |
| `jenis_kelamin` | string | No | Filter by gender (L/P) | `?jenis_kelamin=L` |
| `page` | integer | No | Page number untuk pagination | `?page=2` |

**Response:** `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "nis": "20240001",
      "nisn": "1234567890",
      "nik": "1234567890123456",
      "nama_lengkap": "Budi Santoso",
      "nama_panggilan": "Budi",
      "jenis_kelamin": "L",
      "tempat_lahir": "Jakarta",
      "tanggal_lahir": "2015-01-15",
      "agama": "Islam",
      "kelas_id": 1,
      "status": "aktif",
      "foto": "students/photos/abc123.jpg",
      "guardians": [
        {
          "id": 1,
          "nama_lengkap": "Pak Budi",
          "hubungan": "ayah",
          "no_hp": "081234567890",
          "pivot": {
            "is_primary_contact": true
          }
        }
      ],
      "created_at": "2024-12-24T10:00:00.000000Z",
      "updated_at": "2024-12-24T10:00:00.000000Z"
    }
  ],
  "links": { ... },
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150
  }
}
```

---

### Create Student

**Endpoint:** `POST /admin/students`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Create new student dengan auto-generate NIS dan auto-create parent account

**Request Body:**

```json
{
  "nisn": "1234567890",
  "nik": "1234567890123456",
  "nama_lengkap": "Budi Santoso",
  "nama_panggilan": "Budi",
  "jenis_kelamin": "L",
  "tempat_lahir": "Jakarta",
  "tanggal_lahir": "2015-01-15",
  "agama": "Islam",
  "anak_ke": 1,
  "jumlah_saudara": 2,
  "status_keluarga": "Anak Kandung",
  "alamat": "Jl. Sudirman No. 123",
  "rt_rw": "001/002",
  "kelurahan": "Kebayoran Baru",
  "kecamatan": "Kebayoran Baru",
  "kota": "Jakarta Selatan",
  "provinsi": "DKI Jakarta",
  "kode_pos": "12345",
  "no_hp": "081234567890",
  "email": "budi@example.com",
  "foto": "base64_or_file_upload",
  "kelas_id": 1,
  "tahun_ajaran_masuk": "2024/2025",
  "tanggal_masuk": "2024-07-01",
  "ayah": {
    "nik": "1234567890123457",
    "nama_lengkap": "Pak Budi Senior",
    "pekerjaan": "PNS",
    "pendidikan": "S1",
    "penghasilan": "3-5jt",
    "no_hp": "081234567890",
    "email": "pakbudi@example.com",
    "alamat": "Jl. Sudirman No. 123",
    "is_primary_contact": true
  },
  "ibu": {
    "nik": "1234567890123458",
    "nama_lengkap": "Bu Siti",
    "pekerjaan": "Ibu Rumah Tangga",
    "pendidikan": "SMA",
    "penghasilan": "<1jt",
    "no_hp": "081234567891",
    "email": "busiti@example.com"
  },
  "wali": {
    "nik": "1234567890123459",
    "nama_lengkap": "Pak Wali",
    "pekerjaan": "Wiraswasta",
    "pendidikan": "S1",
    "penghasilan": "3-5jt",
    "no_hp": "081234567892"
  }
}
```

**Response:** `302 Redirect` to `/admin/students`

**Success Flash Message:**
```
"Siswa berhasil ditambahkan dengan NIS: 20240001"
```

**Validation Errors:** `422 Unprocessable Entity`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "nik": ["NIK sudah terdaftar."],
    "nisn": ["NISN harus 10 digit."],
    "tanggal_lahir": ["Umur siswa tidak sesuai untuk jenjang SD."]
  }
}
```

---

### Show Student

**Endpoint:** `GET /admin/students/{student}`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Get detailed student profile dengan guardians dan history

**Response:** `200 OK`

```json
{
  "id": 1,
  "nis": "20240001",
  "nama_lengkap": "Budi Santoso",
  "guardians": [...],
  "primary_guardian": {...},
  "class_history": [
    {
      "id": 1,
      "kelas_id": 1,
      "tahun_ajaran": "2024/2025",
      "wali_kelas": "Pak Guru",
      "created_at": "2024-07-01T00:00:00.000000Z"
    }
  ],
  "status_history": [
    {
      "id": 1,
      "status_lama": "aktif",
      "status_baru": "mutasi",
      "tanggal": "2024-12-01",
      "alasan": "Pindah tugas orang tua",
      "changed_by": {
        "id": 1,
        "name": "Admin TU"
      }
    }
  ]
}
```

---

### Update Student

**Endpoint:** `PUT/PATCH /admin/students/{student}`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Update student data dan guardians

**Request Body:** Same as Create Student (without `tahun_ajaran_masuk`, `tanggal_masuk`)

**Response:** `302 Redirect` to `/admin/students`

**Success Flash Message:**
```
"Data siswa berhasil diupdate."
```

---

### Delete Student

**Endpoint:** `DELETE /admin/students/{student}`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Soft delete student

**Response:** `302 Redirect` to `/admin/students`

**Success Flash Message:**
```
"Siswa berhasil dihapus."
```

---

### Update Student Status

**Endpoint:** `POST /admin/students/{student}/update-status`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Change student status dengan history tracking

**Request Body:**

```json
{
  "status": "mutasi",
  "tanggal": "2024-12-24",
  "alasan": "Pindah ke luar kota karena orang tua pindah tugas",
  "keterangan": "Siswa berprestasi, aktif di ekstrakurikuler",
  "sekolah_tujuan": "SDN 01 Jakarta"
}
```

**Validation Rules:**

| Field | Required | Rules |
|-------|----------|-------|
| `status` | Yes | `in:aktif,mutasi,do,lulus` |
| `tanggal` | Yes | `date`, `before_or_equal:today` |
| `alasan` | Yes | `string`, `min:10` |
| `keterangan` | No | `string` |
| `sekolah_tujuan` | Conditional | Required jika status = `mutasi` |

**Response:** `302 Redirect` back

**Success Flash Message:**
```
"Status siswa berhasil diupdate."
```

---

### Bulk Promote Students

**Endpoint:** `POST /admin/students/promote`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Bulk naik kelas untuk multiple students

**Request Body:**

```json
{
  "student_ids": [1, 2, 3, 4, 5],
  "kelas_id_baru": 2,
  "tahun_ajaran_baru": "2025/2026",
  "wali_kelas": "Pak Budi"
}
```

**Response:** `302 Redirect` back

**Success Flash Message:**
```
"5 siswa berhasil dipindahkan ke kelas baru."
```

---

### Export Students (Placeholder)

**Endpoint:** `GET /admin/students/export`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Export student data to Excel

**Status:** 📝 Planned (Placeholder method exists)

**Response:** `302 Redirect` back

**Info Flash Message:**
```
"Fitur export akan segera tersedia."
```

---

### Import Students Preview (Placeholder)

**Endpoint:** `POST /admin/students/import/preview`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Preview import data dari Excel

**Status:** 📝 Planned (Placeholder method exists)

---

### Import Students (Placeholder)

**Endpoint:** `POST /admin/students/import`  
**Permission:** `SUPERADMIN`, `ADMIN`  
**Purpose:** Import student data dari Excel

**Status:** 📝 Planned (Placeholder method exists)

---

## Parent Portal Endpoints

### List Children

**Endpoint:** `GET /parent/children`  
**Permission:** `PARENT`  
**Purpose:** Get list of children untuk logged-in parent

**Response:** `200 OK`

```json
{
  "children": [
    {
      "id": 1,
      "nis": "20240001",
      "nama_lengkap": "Budi Santoso",
      "nama_panggilan": "Budi",
      "kelas_id": 1,
      "status": "aktif",
      "foto": "students/photos/abc123.jpg",
      "guardians": [...],
      "primary_guardian": {...}
    }
  ]
}
```

**Note:** Hanya menampilkan children dengan status `aktif`

---

### Show Child Detail

**Endpoint:** `GET /parent/children/{student}`  
**Permission:** `PARENT` (own children only)  
**Purpose:** Get detailed profile untuk specific child

**Authorization:** System verify bahwa student adalah anak dari logged-in parent

**Response:** `200 OK` (same structure as Admin Show Student)

**Error:** `403 Forbidden` jika bukan anak sendiri

```json
{
  "message": "Anda tidak memiliki akses ke data siswa ini."
}
```

---

## Common Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\Student] {id}"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message here"]
  }
}
```

### 500 Server Error
```json
{
  "message": "Server Error"
}
```

---

## Rate Limiting

All endpoints inherit Laravel's default rate limiting:
- **Web routes:** 60 requests per minute per IP
- **API routes:** Not applicable (using web routes)

---

## Pagination

List endpoints return paginated results:
- **Default per page:** 20 items
- **Query parameter:** `?page=2`
- **Response includes:** `links` (prev/next) dan `meta` (total, current_page, per_page)

---

## File Uploads

### Student Photo

- **Field name:** `foto`
- **Accepted formats:** `jpg`, `jpeg`, `png`
- **Max size:** 2MB
- **Storage path:** `storage/app/public/students/photos/`
- **Access URL:** `/storage/students/photos/{filename}`

---

## Testing Examples

### cURL Examples

**List Students:**
```bash
curl -X GET "http://localhost/admin/students?search=Budi&status=aktif" \
  -H "Cookie: laravel_session=..." \
  -H "Accept: application/json"
```

**Create Student:**
```bash
curl -X POST "http://localhost/admin/students" \
  -H "Cookie: laravel_session=..." \
  -H "Content-Type: application/json" \
  -d @student-data.json
```

**Bulk Promote:**
```bash
curl -X POST "http://localhost/admin/students/promote" \
  -H "Cookie: laravel_session=..." \
  -H "Content-Type: application/json" \
  -d '{
    "student_ids": [1,2,3],
    "kelas_id_baru": 2,
    "tahun_ajaran_baru": "2025/2026",
    "wali_kelas": "Pak Budi"
  }'
```

---

## Related Documentation

- **Feature Documentation:** [STD Student Management](../features/admin/STD-student-management.md)
- **Test Plan:** [STD Test Plan](../testing/STD-test-plan.md)
- **Database Schema:** [Database Documentation](../architecture/DATABASE.md#student-management-tables)

