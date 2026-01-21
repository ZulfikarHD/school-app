# Grades & Report Cards API

## Overview

API documentation untuk module Grades & Report Cards yang mencakup endpoints untuk input nilai, nilai sikap, konfigurasi bobot, generate rapor, dan approval flow.

---

## Teacher Routes

### Grades CRUD

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/teacher/grades` | teacher.grades.index | List penilaian teacher |
| GET | `/teacher/grades/create` | teacher.grades.create | Form input nilai baru |
| POST | `/teacher/grades` | teacher.grades.store | Simpan nilai baru |
| GET | `/teacher/grades/{grade}/edit` | teacher.grades.edit | Form edit nilai |
| PUT | `/teacher/grades/{grade}` | teacher.grades.update | Update nilai |
| DELETE | `/teacher/grades/{grade}` | teacher.grades.destroy | Hapus penilaian |

### Grades API Helper

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/teacher/grades/classes/{class}/students` | teacher.grades.classes.students | Get students by class |
| GET | `/teacher/grades/classes/{class}/subjects` | teacher.grades.classes.subjects | Get subjects by class |

### Attitude Grades (Wali Kelas)

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/teacher/attitude-grades` | teacher.attitude-grades.index | List nilai sikap |
| GET | `/teacher/attitude-grades/create` | teacher.attitude-grades.create | Form input nilai sikap |
| POST | `/teacher/attitude-grades` | teacher.attitude-grades.store | Simpan nilai sikap |

### Teacher Report Cards

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/teacher/report-cards` | teacher.report-cards.index | List rapor kelas |
| GET | `/teacher/report-cards/{reportCard}` | teacher.report-cards.show | Preview rapor |

---

## Admin Routes

### Grades Management

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/admin/grades` | admin.grades.index | Rekap semua nilai |
| GET | `/admin/grades/summary` | admin.grades.summary | Summary per kelas |
| GET | `/admin/grades/export` | admin.grades.export | Export ke CSV |

### Grade Weight Configuration

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/admin/settings/grade-weights` | admin.settings.grade-weights.index | Config bobot |
| PUT | `/admin/settings/grade-weights` | admin.settings.grade-weights.update | Update bobot |

### Report Cards Management

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/admin/report-cards` | admin.report-cards.index | List semua rapor |
| GET | `/admin/report-cards/generate` | admin.report-cards.generate | Wizard generate |
| POST | `/admin/report-cards/generate` | admin.report-cards.process-generate | Process generate bulk |
| POST | `/admin/report-cards/validate` | admin.report-cards.validate | Validasi kelengkapan |
| GET | `/admin/report-cards/{reportCard}` | admin.report-cards.show | Preview rapor |
| GET | `/admin/report-cards/{reportCard}/download` | admin.report-cards.download | Download PDF |
| POST | `/admin/report-cards/{reportCard}/unlock` | admin.report-cards.unlock | Unlock untuk koreksi |
| POST | `/admin/report-cards/{reportCard}/regenerate` | admin.report-cards.regenerate | Regenerate PDF |
| GET | `/admin/report-cards/download-zip` | admin.report-cards.download-zip | Download bulk ZIP |

---

## Principal Routes

### Academic Dashboard

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/principal/academic/dashboard` | principal.academic.dashboard | Dashboard akademik |
| GET | `/principal/academic/grades` | principal.academic.grades | Rekap nilai semua kelas |

### Report Card Approval

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/principal/report-cards` | principal.report-cards.index | List pending approval |
| GET | `/principal/report-cards/{reportCard}` | principal.report-cards.show | Preview rapor |
| POST | `/principal/report-cards/{reportCard}/approve` | principal.report-cards.approve | Approve & release |
| POST | `/principal/report-cards/{reportCard}/reject` | principal.report-cards.reject | Reject dengan notes |
| POST | `/principal/report-cards/bulk-approve` | principal.report-cards.bulk-approve | Bulk approve per kelas |

---

## Parent Routes

### Child Grades & Report Cards

| Method | Endpoint | Name | Description |
|--------|----------|------|-------------|
| GET | `/parent/children/{student}/grades` | parent.children.grades | Rekap nilai anak |
| GET | `/parent/children/{student}/report-cards` | parent.children.report-cards.index | List rapor |
| GET | `/parent/children/{student}/report-cards/{reportCard}` | parent.children.report-cards.show | View rapor |
| GET | `/parent/children/{student}/report-cards/{reportCard}/download` | parent.children.report-cards.download | Download PDF |

---

## Request/Response Examples

### Store Grade (POST /teacher/grades)

**Request:**
```json
{
  "class_id": 1,
  "subject_id": 2,
  "tahun_ajaran": "2025/2026",
  "semester": "1",
  "assessment_type": "UH",
  "assessment_number": 1,
  "title": "UH 1: Perkalian",
  "assessment_date": "2026-01-15",
  "grades": [
    { "student_id": 1, "score": 85, "notes": null },
    { "student_id": 2, "score": 90, "notes": "Sangat baik" }
  ]
}
```

**Response:** Redirect to index with success message

### Store Attitude Grade (POST /teacher/attitude-grades)

**Request:**
```json
{
  "class_id": 1,
  "tahun_ajaran": "2025/2026",
  "semester": "1",
  "grades": [
    {
      "student_id": 1,
      "spiritual_grade": "A",
      "spiritual_description": "Siswa selalu...",
      "social_grade": "B",
      "social_description": "Siswa menunjukkan...",
      "homeroom_notes": "Catatan untuk rapor"
    }
  ]
}
```

### Update Grade Weight (PUT /admin/settings/grade-weights)

**Request:**
```json
{
  "tahun_ajaran": "2025/2026",
  "uh_weight": 30,
  "uts_weight": 25,
  "uas_weight": 30,
  "praktik_weight": 15
}
```

**Validation:** Total must equal 100%

### Generate Report Cards (POST /admin/report-cards/generate)

**Request:**
```json
{
  "class_ids": [1, 2, 3],
  "tahun_ajaran": "2025/2026",
  "semester": "1"
}
```

**Response:**
```json
{
  "success": true,
  "message": "30 rapor berhasil di-generate",
  "total_generated": 30,
  "total_failed": 0,
  "results": [
    { "class_id": 1, "class_name": "1A", "generated_count": 10, "failed_count": 0 }
  ]
}
```

### Validate Completeness (POST /admin/report-cards/validate)

**Request:**
```json
{
  "class_id": 1,
  "tahun_ajaran": "2025/2026",
  "semester": "1"
}
```

**Response:**
```json
{
  "is_complete": false,
  "students": [
    {
      "student_id": 1,
      "student_name": "Ahmad",
      "nis": "12345",
      "is_complete": false,
      "missing": ["UH Matematika belum diinput", "Nilai sikap belum diinput"]
    }
  ],
  "missing_count": 1,
  "complete_count": 9,
  "total_students": 10
}
```

### Approve Report Card (POST /principal/report-cards/{id}/approve)

**Request:**
```json
{
  "notes": "Approved" 
}
```

**Response:** Redirect with success message, triggers notification to parent

### Reject Report Card (POST /principal/report-cards/{id}/reject)

**Request:**
```json
{
  "notes": "Nilai matematika perlu dicek ulang"
}
```

**Validation:** Notes required (max 500 chars)

---

## Enum Values

### Assessment Types
- `UH` - Ulangan Harian
- `UTS` - Ujian Tengah Semester
- `UAS` - Ujian Akhir Semester
- `PRAKTIK` - Praktik
- `PROYEK` - Proyek

### Attitude Grades
- `A` - Sangat Baik
- `B` - Baik
- `C` - Cukup
- `D` - Kurang

### Report Card Status
- `DRAFT` - Draft
- `PENDING_APPROVAL` - Menunggu Persetujuan
- `APPROVED` - Disetujui
- `RELEASED` - Dirilis

### Predikat (Score-based)
- `A` - 90-100 (Sangat Baik)
- `B` - 80-89 (Baik)
- `C` - 70-79 (Cukup)
- `D` - <70 (Kurang)

---

## Error Responses

| Code | Message | Cause |
|------|---------|-------|
| 403 | Anda tidak memiliki akses | Teacher bukan pengajar mapel/kelas |
| 403 | Hanya wali kelas yang dapat mengakses | Non-wali kelas access attitude grades |
| 400 | Nilai sudah dikunci | Edit locked grade |
| 400 | Total bobot harus 100% | Invalid weight config |
| 400 | Rapor tidak dapat di-approve | Wrong status |
