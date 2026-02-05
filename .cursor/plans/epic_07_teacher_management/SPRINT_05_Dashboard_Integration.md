# Sprint 05: Dashboard & Cross-Frontend Integration

**Epic:** 07 - Teacher Management (TCH)
**Sprint Duration:** Week 5
**Story Points:** 6 SP
**Sprint Goal:** Complete dashboards, Teacher salary view, and cross-frontend integration

---

## Sprint Overview

Sprint ini merupakan sprint terakhir dari Epic Teacher Management, berfokus pada: dashboard widgets untuk Admin dan Principal, halaman slip gaji untuk Teacher, integrasi navigasi semua role, dan polish untuk production readiness.

---

## Prerequisites

- [ ] Sprint 01-04 completed
- [ ] All teacher data, schedules, salaries, evaluations available
- [ ] Navigation structure defined

---

## User Stories

### TCH-017: Admin Teacher Dashboard
**Story Points:** 2 SP
**Priority:** P1 - Important

**As an** Admin/TU
**I want** a teacher management dashboard
**So that** I can see overview and quick actions

**Acceptance Criteria:**
- [ ] Route: `GET /admin/teachers/dashboard`
- [ ] Statistics cards:
  - Total guru aktif
  - Guru tetap vs honorer (pie chart)
  - Guru baru bulan ini
  - Kontrak akan berakhir (alert)
- [ ] Quick actions: Tambah Guru, Hitung Gaji, Lihat Jadwal
- [ ] Recent activity: guru baru, perubahan data
- [ ] Calendar widget: jadwal penting (kontrak berakhir)
- [ ] Attendance summary: % kehadiran guru bulan ini
- [ ] Charts: distribusi guru per mata pelajaran

**Tasks:**
1. [ ] Create `Admin/TeacherDashboardController@index`
2. [ ] Create `TeacherDashboardService` untuk aggregasi data
3. [ ] Create `Admin/Teachers/Dashboard.vue`
4. [ ] Create `StatCard` component
5. [ ] Create `TeacherDistributionChart` component (Chart.js/ApexCharts)
6. [ ] Create `ContractAlertCard` component
7. [ ] Create `RecentActivityList` component
8. [ ] Generate Wayfinder routes

---

### TCH-018: Principal Teacher Stats Widget
**Story Points:** 1 SP
**Priority:** P1 - Important

**As a** Principal
**I want** teacher statistics on my dashboard
**So that** I can monitor teacher performance at a glance

**Acceptance Criteria:**
- [ ] Widget on existing Principal Dashboard
- [ ] Stats shown:
  - Total guru
  - Evaluasi pending (belum publish)
  - Rata-rata skor evaluasi semester ini
  - Kehadiran guru bulan ini
- [ ] Quick links: Data Guru, Buat Evaluasi
- [ ] Mini chart: trend evaluasi (last 6 months)

**Tasks:**
1. [ ] Update `Principal/DashboardController` dengan teacher stats
2. [ ] Create `TeacherStatsWidget` component
3. [ ] Add widget to `Principal/Dashboard.vue`
4. [ ] Create mini trend chart component

---

### TCH-019: Teacher Salary Self-Service
**Story Points:** 2 SP
**Priority:** P1 - Important

**As a** Teacher
**I want** to view my salary history and download slips
**So that** I can manage my financial records

**Acceptance Criteria:**
- [ ] Route: `GET /teacher/salary` → Salary history list
- [ ] Route: `GET /teacher/salary/{id}` → Salary detail
- [ ] Route: `GET /teacher/salary/{id}/pdf` → Download PDF slip
- [ ] List shows: Periode, Total Jam, Total Netto, Status
- [ ] Only show approved/paid calculations
- [ ] Detail shows breakdown sama seperti Admin view
- [ ] Download PDF button
- [ ] Empty state jika belum ada data gaji
- [ ] Notification badge jika ada slip baru

**Tasks:**
1. [ ] Create `Teacher/SalaryController@index`
2. [ ] Create `Teacher/SalaryController@show`
3. [ ] Create `Teacher/SalaryController@downloadPdf`
4. [ ] Create `TeacherSalaryResource` (limited fields)
5. [ ] Create `Teacher/Salary/Index.vue`
6. [ ] Create `Teacher/Salary/Show.vue`
7. [ ] Create `SalaryHistoryCard` component
8. [ ] Create `SalaryBreakdownCard` component
9. [ ] Add salary menu to Teacher navigation
10. [ ] Generate Wayfinder routes

---

### TCH-020: Cross-Frontend Navigation Integration
**Story Points:** 1 SP
**Priority:** P0 - Critical

**As a** user of any role
**I want** consistent navigation with new teacher features
**So that** I can easily access all teacher-related pages

**Acceptance Criteria:**
- [ ] Admin navigation updated with Guru section
- [ ] Principal navigation updated with Guru section
- [ ] Teacher navigation updated with self-service items
- [ ] Breadcrumbs consistent across all teacher pages
- [ ] Active state correct pada menu items
- [ ] Mobile navigation responsive

**Tasks:**
1. [ ] Update `AdminLayout.vue` navigation config
2. [ ] Update `PrincipalLayout.vue` navigation config
3. [ ] Update `TeacherLayout.vue` navigation config
4. [ ] Create/update breadcrumb components
5. [ ] Test navigation on all viewport sizes
6. [ ] Verify deep linking works correctly

---

## Technical Specifications

### Dashboard Data Aggregation

```php
// TeacherDashboardService.php
class TeacherDashboardService
{
    public function getAdminDashboardData(): array
    {
        return [
            'statistics' => $this->getStatistics(),
            'distribution' => $this->getSubjectDistribution(),
            'contractAlerts' => $this->getContractAlerts(),
            'recentActivity' => $this->getRecentActivity(),
            'attendanceSummary' => $this->getAttendanceSummary(),
        ];
    }
    
    private function getStatistics(): array
    {
        return [
            'total_aktif' => Teacher::where('is_active', true)->count(),
            'guru_tetap' => Teacher::where('status_kepegawaian', 'tetap')->where('is_active', true)->count(),
            'guru_honorer' => Teacher::where('status_kepegawaian', 'honorer')->where('is_active', true)->count(),
            'guru_kontrak' => Teacher::where('status_kepegawaian', 'kontrak')->where('is_active', true)->count(),
            'baru_bulan_ini' => Teacher::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }
    
    private function getContractAlerts(): Collection
    {
        // Kontrak akan berakhir dalam 30 hari
        return Teacher::where('status_kepegawaian', 'kontrak')
            ->where('is_active', true)
            ->whereBetween('tanggal_berakhir_kontrak', [now(), now()->addDays(30)])
            ->orderBy('tanggal_berakhir_kontrak')
            ->get(['id', 'nama_lengkap', 'tanggal_berakhir_kontrak']);
    }
    
    private function getSubjectDistribution(): array
    {
        return DB::table('teacher_subjects')
            ->join('subjects', 'teacher_subjects.subject_id', '=', 'subjects.id')
            ->join('teachers', 'teacher_subjects.teacher_id', '=', 'teachers.id')
            ->where('teachers.is_active', true)
            ->select('subjects.nama', DB::raw('count(*) as total'))
            ->groupBy('subjects.id', 'subjects.nama')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }
    
    private function getAttendanceSummary(): array
    {
        $month = now()->month;
        $year = now()->year;
        
        $totalDays = /* working days in month */;
        $avgAttendance = TeacherAttendance::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('status', 'hadir')
            ->count() / (Teacher::where('is_active', true)->count() ?: 1) / $totalDays * 100;
        
        return [
            'percentage' => round($avgAttendance, 1),
            'month' => now()->translatedFormat('F Y'),
        ];
    }
}
```

### API Endpoints

**Admin Routes:**

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/admin/teachers/dashboard` | TeacherDashboardController@index | Dashboard |

**Teacher Routes:**

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/teacher/salary` | Teacher\SalaryController@index | Salary history |
| GET | `/teacher/salary/{id}` | Teacher\SalaryController@show | Salary detail |
| GET | `/teacher/salary/{id}/pdf` | Teacher\SalaryController@downloadPdf | Download PDF |

### File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── TeacherDashboardController.php
│   │   └── Teacher/
│   │       └── SalaryController.php
│   └── Resources/
│       └── TeacherSalaryResource.php
└── Services/
    └── TeacherDashboardService.php

resources/js/Pages/
├── Admin/Teachers/
│   ├── Dashboard.vue
│   └── Components/
│       ├── StatCard.vue
│       ├── TeacherDistributionChart.vue
│       ├── ContractAlertCard.vue
│       └── RecentActivityList.vue
├── Principal/
│   └── Components/
│       └── TeacherStatsWidget.vue
└── Teacher/
    └── Salary/
        ├── Index.vue
        ├── Show.vue
        └── Components/
            ├── SalaryHistoryCard.vue
            └── SalaryBreakdownCard.vue

resources/js/Layouts/
├── AdminLayout.vue (update navigation)
├── PrincipalLayout.vue (update navigation)
└── TeacherLayout.vue (update navigation)
```

---

## Navigation Configuration

### Admin Navigation Config

```typescript
// adminNavigation.ts
export const teacherSection = {
  title: 'Guru',
  icon: 'UserGroupIcon',
  items: [
    { name: 'Dashboard Guru', href: adminTeacherDashboard(), icon: 'ChartBarIcon' },
    { name: 'Data Guru', href: adminTeachersIndex(), icon: 'UsersIcon' },
    { name: 'Jadwal Mengajar', href: adminTeacherSchedulesIndex(), icon: 'CalendarIcon' },
    { name: 'Rekap Honor', href: adminTeacherSalaryIndex(), icon: 'BanknotesIcon' },
  ],
};
```

### Principal Navigation Config

```typescript
// principalNavigation.ts
export const teacherSection = {
  title: 'Guru',
  icon: 'UserGroupIcon',
  items: [
    { name: 'Data Guru', href: principalTeachersIndex(), icon: 'UsersIcon' },
    { name: 'Evaluasi Guru', href: principalTeacherEvaluationsIndex(), icon: 'ClipboardDocumentCheckIcon' },
  ],
};
```

### Teacher Navigation Config

```typescript
// teacherNavigation.ts
export const selfServiceSection = {
  title: 'Akun Saya',
  icon: 'UserCircleIcon',
  items: [
    { name: 'Profil Saya', href: teacherProfile(), icon: 'UserIcon' },
    { name: 'Jadwal Saya', href: teacherSchedule(), icon: 'CalendarIcon' },
    { name: 'Evaluasi Saya', href: teacherEvaluationsIndex(), icon: 'ClipboardDocumentCheckIcon' },
    { name: 'Slip Gaji', href: teacherSalaryIndex(), icon: 'BanknotesIcon' },
  ],
};
```

---

## Charts Configuration

### Subject Distribution Pie Chart

```vue
<!-- TeacherDistributionChart.vue -->
<script setup lang="ts">
import { computed } from 'vue';
import { Pie } from 'vue-chartjs';

const props = defineProps<{
  data: Array<{ nama: string; total: number }>;
}>();

const chartData = computed(() => ({
  labels: props.data.map(d => d.nama),
  datasets: [{
    data: props.data.map(d => d.total),
    backgroundColor: [
      '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
      '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1',
    ],
  }],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right' as const,
    },
  },
};
</script>

<template>
  <div class="h-64">
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>
```

---

## Definition of Done

- [ ] Admin dashboard shows all statistics correctly
- [ ] Principal widget integrated with existing dashboard
- [ ] Teacher can view and download salary slips
- [ ] All navigation updated and consistent
- [ ] Charts render correctly
- [ ] Mobile responsive
- [ ] All feature tests passing
- [ ] No lint errors
- [ ] Performance acceptable (< 2s page load)

---

## Dependencies

**Blocked By:**
- Sprint 01-04: All teacher features

**Blocks:**
- None (this is the final sprint)

---

## Post-Sprint Activities

### Documentation
- [ ] Update user manual untuk fitur Teacher Management
- [ ] Create API documentation
- [ ] Update README dengan feature overview

### Testing
- [ ] End-to-end testing untuk semua user journeys
- [ ] Performance testing
- [ ] Cross-browser testing
- [ ] Mobile device testing

### Deployment Checklist
- [ ] Run all migrations
- [ ] Run seeders untuk default data (honor rates, subjects)
- [ ] Clear caches
- [ ] Regenerate Wayfinder routes
- [ ] Build frontend assets

---

## Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Chart library compatibility | Low | Use well-tested library (Chart.js/ApexCharts) |
| Dashboard performance | Medium | Eager loading, caching, pagination |
| Navigation regression | Medium | Test all existing navigation paths |

---

## Notes

- Dashboard data bisa di-cache untuk performance
- Chart data bisa di-lazy load setelah initial page render
- Notification badge untuk slip gaji baru menggunakan database flag atau timestamp comparison
- Consider adding "Export All" feature untuk batch download salary slips

---

## Epic Summary

Setelah Sprint 05 selesai, Epic 07 Teacher Management akan memiliki:

| Feature | Admin | Principal | Teacher |
|---------|-------|-----------|---------|
| Teacher CRUD | ✅ Full CRUD | ✅ View only | ❌ |
| Schedule Management | ✅ Full CRUD | ✅ View | ✅ View own |
| Salary Calculation | ✅ Full + Approve | ✅ View summary | ✅ View own + PDF |
| Evaluation | ✅ View all | ✅ Full CRUD | ✅ View own |
| Dashboard | ✅ Full stats | ✅ Widget | ✅ Profile stats |

**Total Story Points:** 38 SP across 5 sprints
**Estimated Duration:** 5 weeks
