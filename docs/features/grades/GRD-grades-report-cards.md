# GRD - Grades & Report Cards (Nilai & Rapor)

## Overview

Module Grades & Report Cards merupakan sistem penilaian akademik yang bertujuan untuk mengelola input nilai siswa (UH, UTS, UAS, Praktik), perhitungan nilai akhir, dan generate rapor digital sesuai Kurikulum 2013 (K13), yaitu: mendukung predikat A/B/C/D, bobot nilai yang dapat dikonfigurasi, approval flow rapor oleh Principal, serta akses rapor digital untuk orang tua.

**Status:** ✅ Complete  
**Last Updated:** 2026-01-21

---

## User Stories Summary

| ID | Story | Role | Status |
|----|-------|------|--------|
| US-GRD-001 | Input nilai siswa (UH/UTS/UAS/Praktik) | Teacher | ✅ Done |
| US-GRD-002 | Set bobot komponen nilai (K13) | Admin | ✅ Done |
| US-GRD-003 | Lihat rekap nilai kelas | Teacher, Admin, Principal | ✅ Done |
| US-GRD-004 | Input nilai sikap (spiritual/sosial) | Teacher (Wali Kelas) | ✅ Done |
| US-GRD-005 | Generate rapor PDF | Admin | ✅ Done |
| US-GRD-006 | Lihat rapor online | Parent | ✅ Done |
| US-GRD-007 | Approval & release rapor | Principal | ✅ Done |
| US-GRD-008 | Catatan wali kelas untuk rapor | Teacher (Wali Kelas) | ✅ Done |
| US-GRD-009 | Download rapor PDF | Parent | ✅ Done |
| US-GRD-010 | Dashboard akademik | Principal | ✅ Done |
| US-GRD-011 | Export rekap nilai Excel | Admin | ✅ Done |

---

## Business Rules

| Rule | Description |
|------|-------------|
| BR-GRD-001 | Nilai harus dalam range 0-100 |
| BR-GRD-002 | Predikat K13: A (90-100), B (80-89), C (70-79), D (<70) |
| BR-GRD-003 | Total bobot harus = 100% (UH + UTS + UAS + Praktik) |
| BR-GRD-004 | Hanya wali kelas yang dapat input nilai sikap |
| BR-GRD-005 | Nilai di-lock setelah rapor di-generate |
| BR-GRD-006 | Rapor harus di-approve Principal sebelum dirilis ke parent |
| BR-GRD-007 | Parent hanya bisa melihat rapor dengan status RELEASED |
| BR-GRD-008 | Nilai sikap: A/B/C/D dengan template deskripsi K13 |
| BR-GRD-009 | Satu siswa hanya punya 1 rapor per semester |
| BR-GRD-010 | Ranking dihitung berdasarkan rata-rata semua mapel |

---

## Technical Implementation

### Database Schema

| Table | Purpose |
|-------|---------|
| `grades` | Nilai siswa per assessment (UH, UTS, UAS, Praktik) |
| `attitude_grades` | Nilai sikap spiritual & sosial + catatan wali kelas |
| `report_cards` | Data rapor dengan approval flow |
| `grade_weight_configs` | Konfigurasi bobot nilai per tahun ajaran |

### Services

| Service | Purpose |
|---------|---------|
| `GradeCalculationService` | Formula K13, ranking, summary per siswa/kelas |
| `ReportCardService` | Generate PDF, validasi, lock/unlock nilai |

### Key Components

| Component | Location | Purpose |
|-----------|----------|---------|
| Grade Model | `app/Models/Grade.php` | Assessment types, predikat, scopes |
| AttitudeGrade Model | `app/Models/AttitudeGrade.php` | Spiritual/social grades, templates |
| ReportCard Model | `app/Models/ReportCard.php` | Approval flow helpers |
| PDF Template | `resources/views/pdf/report-card.blade.php` | K13 format rapor |

---

## Routes Summary

### Teacher Routes (8)
- `/teacher/grades/*` - CRUD nilai
- `/teacher/attitude-grades/*` - Nilai sikap (wali kelas)
- `/teacher/report-cards/*` - Preview rapor kelas

### Admin Routes (12)
- `/admin/grades/*` - Rekap & export nilai
- `/admin/settings/grade-weights` - Konfigurasi bobot
- `/admin/report-cards/*` - Generate & manage rapor

### Principal Routes (7)
- `/principal/academic/*` - Dashboard & rekap nilai
- `/principal/report-cards/*` - Approval flow

### Parent Routes (4)
- `/parent/children/{student}/grades` - Rekap nilai anak
- `/parent/children/{student}/report-cards/*` - View/download rapor

**Full API documentation:** [Grades API](../../api/grades.md)

---

## Frontend Pages

| Role | Page | Purpose |
|------|------|---------|
| Teacher | Grades/Index | List penilaian yang sudah diinput |
| Teacher | Grades/Create | Wizard input nilai baru |
| Teacher | Grades/Edit | Edit nilai |
| Teacher | AttitudeGrades/Index | List nilai sikap |
| Teacher | AttitudeGrades/Create | Input nilai sikap |
| Teacher | ReportCards/Index | Preview rapor kelas |
| Admin | Grades/Index | Rekap semua nilai |
| Admin | Grades/Summary | Summary per kelas/siswa |
| Admin | Settings/GradeWeights | Konfigurasi bobot |
| Admin | ReportCards/Generate | Wizard generate rapor |
| Principal | Academic/Dashboard | Dashboard akademik |
| Principal | Academic/Grades | Rekap nilai semua kelas |
| Principal | ReportCards/Index | List pending approval |
| Principal | ReportCards/Show | Preview & approve |
| Parent | Children/Grades | Rekap nilai anak |
| Parent | Children/ReportCards | List rapor anak |

---

## Edge Cases

| Case | Handling |
|------|----------|
| Nilai incomplete saat generate rapor | Validasi menampilkan list missing values |
| Guru edit nilai yang sudah di-lock | Ditolak dengan error message |
| Parent akses rapor belum RELEASED | Redirect dengan pesan |
| Bobot tidak = 100% | Validasi mencegah save |
| Duplicate assessment | Validasi by title + date |
| Siswa tanpa nilai sikap | Warning saat generate rapor |
| Wali kelas tidak diset | Menu nilai sikap tidak muncul |

---

## Security Considerations

| Concern | Implementation |
|---------|----------------|
| Teacher hanya edit nilai sendiri | `teacher_id` check di controller |
| Parent hanya lihat anak sendiri | Guardian-Student relationship check |
| Wali kelas only untuk sikap | SchoolClass `wali_kelas_id` validation |
| Lock mechanism | `is_locked` flag prevent edit |
| Role-based access | Middleware per route group |

---

## Related Documentation

- **API Documentation:** [Grades API](../../api/grades.md)
- **Test Plan:** [GRD Test Plan](../../testing/GRD-test-plan.md)
- **User Journeys:** [Grades User Journeys](../../guides/grades-user-journeys.md)

---

## Changelog

| Date | Change |
|------|--------|
| 2026-01-21 | Initial implementation complete |
