# Epic 07: Teacher Management - Sprint Planning

## Overview

Epic ini mencakup seluruh implementasi modul Teacher Management (TCH) dengan priority P2, yang terdiri dari 5 sprint dengan total **38 Story Points** dalam estimasi **5 minggu** pengerjaan.

## Sprint Summary

| Sprint | Focus Area | Story Points | Duration |
|--------|------------|--------------|----------|
| [Sprint 01](./SPRINT_01_Foundation_Teacher_CRUD.md) | Foundation & Teacher CRUD | 8 SP | Week 1 |
| [Sprint 02](./SPRINT_02_Teaching_Schedule.md) | Teaching Schedule Management | 8 SP | Week 2 |
| [Sprint 03](./SPRINT_03_Honor_Salary.md) | Honor/Salary Calculation | 8 SP | Week 3 |
| [Sprint 04](./SPRINT_04_Teacher_Evaluation.md) | Teacher Evaluation System | 8 SP | Week 4 |
| [Sprint 05](./SPRINT_05_Dashboard_Integration.md) | Dashboard & Integration | 6 SP | Week 5 |

## Sprint Dependencies

```
Sprint 01 (Foundation)
    │
    ├──► Sprint 02 (Schedule) ──► Sprint 03 (Salary) ──► Sprint 05 (Dashboard)
    │                                                         ▲
    └──► Sprint 04 (Evaluation) ─────────────────────────────┘
```

## User Stories by Sprint

### Sprint 01: Foundation & Teacher CRUD (8 SP)
- TCH-001: Database & Model Foundation (3 SP) - P0
- TCH-002: Admin Teacher List Page (2 SP) - P0
- TCH-003: Admin Create Teacher Page (2 SP) - P0
- TCH-004: Admin Edit & Update Teacher (1 SP) - P0

### Sprint 02: Teaching Schedule Management (8 SP)
- TCH-005: Teaching Schedule Database (2 SP) - P0
- TCH-006: Admin Schedule List & Matrix View (3 SP) - P0
- TCH-007: Admin Create Schedule with Conflict Detection (2 SP) - P0
- TCH-008: Admin Edit & Delete Schedule (1 SP) - P0

### Sprint 03: Honor/Salary Calculation (8 SP)
- TCH-009: Salary Configuration Database (2 SP) - P0
- TCH-010: Admin Honor Rate Configuration (1 SP) - P0
- TCH-011: Admin Salary Calculation (3 SP) - P0
- TCH-012: Salary Detail & PDF Slip (2 SP) - P0

### Sprint 04: Teacher Evaluation (8 SP)
- TCH-013: Evaluation Database & Models (1 SP) - P0
- TCH-014: Principal Evaluation Management (3 SP) - P0
- TCH-015: Teacher Profile Self-Service (2 SP) - P0
- TCH-016: Teacher View Own Evaluations (2 SP) - P0

### Sprint 05: Dashboard & Integration (6 SP)
- TCH-017: Admin Teacher Dashboard (2 SP) - P1
- TCH-018: Principal Teacher Stats Widget (1 SP) - P1
- TCH-019: Teacher Salary Self-Service (2 SP) - P1
- TCH-020: Cross-Frontend Navigation Integration (1 SP) - P0

## Role Coverage Matrix

| Feature | Admin/TU | Principal | Teacher | Parent |
|---------|----------|-----------|---------|--------|
| Teacher CRUD | ✅ Full | 👁️ View | ❌ | ❌ |
| Schedule Management | ✅ Full | 👁️ View | 👁️ Own | ❌ |
| Salary Calculation | ✅ Full | 👁️ Summary | 👁️ Own | ❌ |
| Evaluation | 👁️ View | ✅ Full | 👁️ Own | ❌ |
| Dashboard | ✅ Full | ✅ Widget | 📊 Stats | ❌ |

Legend: ✅ Full CRUD, 👁️ View Only, 📊 Limited Stats, ❌ No Access

## Technical Stack

- **Backend:** Laravel 12 (Controllers, Services, Form Requests, Resources)
- **Frontend:** Vue 3 + Inertia v2 + TypeScript
- **Styling:** Tailwind CSS v4
- **Routing:** Laravel Wayfinder
- **PDF:** Laravel DomPDF
- **Excel:** Laravel Excel
- **Charts:** Chart.js / ApexCharts

## Database Tables

| Table | Sprint | Description |
|-------|--------|-------------|
| `subjects` | 01 | Mata pelajaran |
| `teachers` | 01 | Data guru |
| `teacher_subjects` | 01 | Pivot guru-mapel |
| `academic_years` | 02 | Tahun ajaran |
| `teaching_schedules` | 02 | Jadwal mengajar |
| `honor_rates` | 03 | Tarif honor |
| `salary_calculations` | 03 | Perhitungan gaji |
| `salary_calculation_components` | 03 | Komponen gaji |
| `teacher_evaluations` | 04 | Evaluasi guru |

## Key Files to Create

```
app/
├── Http/Controllers/
│   ├── Admin/
│   │   ├── TeacherController.php
│   │   ├── TeachingScheduleController.php
│   │   ├── SalaryCalculationController.php
│   │   ├── HonorRateController.php
│   │   └── TeacherDashboardController.php
│   ├── Principal/
│   │   ├── TeacherController.php
│   │   └── TeacherEvaluationController.php
│   └── Teacher/
│       ├── ProfileController.php
│       ├── EvaluationController.php
│       └── SalaryController.php
├── Models/
│   ├── Teacher.php
│   ├── Subject.php
│   ├── TeachingSchedule.php
│   ├── HonorRate.php
│   ├── SalaryCalculation.php
│   └── TeacherEvaluation.php
└── Services/
    ├── TeacherService.php
    ├── ScheduleConflictService.php
    ├── SalaryCalculationService.php
    └── TeacherDashboardService.php

resources/js/Pages/
├── Admin/Teachers/
├── Principal/Teachers/
└── Teacher/
    ├── Profile/
    ├── Evaluations/
    └── Salary/
```

## Definition of Done (Epic Level)

- [ ] All 20 user stories completed
- [ ] All migrations run successfully
- [ ] All feature tests passing
- [ ] No TypeScript/ESLint errors
- [ ] Pint formatting applied
- [ ] Mobile responsive on all pages
- [ ] Navigation updated for all roles
- [ ] Documentation updated
- [ ] Performance acceptable (< 2s page load)

## Risk Register

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Existing attendance data format mismatch | High | Medium | Verify schema early in Sprint 03 |
| Schedule matrix complexity | Medium | Medium | Fallback to list view, prioritize functionality |
| Principal role not configured | High | Low | Verify role seeder before Sprint 04 |
| PDF generation memory issues | Medium | Low | Batch processing, queue for bulk |

## Notes

- NIP bisa kosong untuk guru honorer (nullable)
- Periode gaji format: YYYY-MM (e.g., "2025-01")
- Evaluasi per semester (2x setahun)
- Teacher tidak bisa melihat siapa evaluator (privacy)
- All currency in Rupiah (Rp)
- Timezone: Asia/Jakarta (WIB)
