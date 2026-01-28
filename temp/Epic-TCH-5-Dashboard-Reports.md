# Epic TCH-5: Dashboard & Reports

**Sprint:** Week 5  
**Story Points:** 6 points  
**Priority:** Should Have  
**Status:** 🔵 Not Started  
**Dependencies:** Epic TCH-1, TCH-2, TCH-3, TCH-4

---

## 📋 Overview

Implementasi dashboard summary untuk Teacher Management dan berbagai fitur export data ke Excel/PDF. Epic ini juga mencakup integrasi dan polish dari semua fitur sebelumnya.

---

## 🎯 Goals

1. Dashboard dengan summary statistics
2. Quick overview data guru
3. Export data guru ke Excel
4. Export jadwal ke PDF
5. Export payroll & evaluasi
6. Integration & Polish

---

## 📖 User Stories

### US-TCH-010: Dashboard Teacher Management
**Priority:** Should Have | **Points:** 3

**Acceptance Criteria:**
- [ ] Dashboard menampilkan summary cards: Total Guru Aktif, Guru Tetap, Guru Honorer
- [ ] Alert card untuk guru dengan presensi < 80%
- [ ] Rata-rata evaluasi per kategori
- [ ] Summary honor bulan berjalan
- [ ] Quick links ke fitur utama
- [ ] Jadwal mengajar hari ini (widget)

### US-TCH-009: Export Data Guru
**Priority:** Should Have | **Points:** 2

**Acceptance Criteria:**
- [ ] TU dapat export data guru ke Excel
- [ ] Export mengikuti filter yang aktif (status, aktif/nonaktif)
- [ ] Kolom: NIP, Nama, TTL, No HP, Email, Mapel, Status, Tgl Bergabung
- [ ] Nama file: DataGuru_[Tanggal].xlsx

### Integration & Polish
**Priority:** Should Have | **Points:** 1

**Acceptance Criteria:**
- [ ] Semua navigation terintegrasi
- [ ] Consistent UI/UX across all pages
- [ ] Performance optimization
- [ ] Final bug fixes

---

## 📝 Technical Tasks

### Task 5.1: Statistics Service
**Estimated:** 2 hours

```bash
php artisan make:class Services/TeacherStatisticsService
```

**TeacherStatisticsService:**
```php
class TeacherStatisticsService
{
    /**
     * Get teacher counts by status
     */
    public function getCountByStatus(): array
    {
        return [
            'total' => Teacher::active()->count(),
            'tetap' => Teacher::active()->where('status', 'tetap')->count(),
            'honorer' => Teacher::active()->where('status', 'honorer')->count(),
            'nonaktif' => Teacher::where('is_active', false)->count(),
        ];
    }

    /**
     * Get teachers with bad attendance (< threshold)
     */
    public function getTeachersWithBadAttendance(float $threshold = 80): Collection
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $workingDays = $this->getWorkingDaysInRange($startOfMonth, $endOfMonth);

        return Teacher::active()
            ->withCount(['attendances as hadir_count' => function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                  ->where('status', 'hadir');
            }])
            ->get()
            ->filter(function ($teacher) use ($workingDays, $threshold) {
                $percentage = $workingDays > 0 
                    ? ($teacher->hadir_count / $workingDays) * 100 
                    : 100;
                return $percentage < $threshold;
            })
            ->map(function ($teacher) use ($workingDays) {
                $teacher->attendance_percentage = $workingDays > 0 
                    ? round(($teacher->hadir_count / $workingDays) * 100, 1)
                    : 100;
                return $teacher;
            });
    }

    /**
     * Get average evaluation scores
     */
    public function getAverageEvaluationScores(): array
    {
        $averages = TeacherEvaluation::published()
            ->selectRaw('
                AVG(score_pedagogik) as avg_pedagogik,
                AVG(score_kepribadian) as avg_kepribadian,
                AVG(score_sosial) as avg_sosial,
                AVG(score_profesional) as avg_profesional,
                AVG(score_overall) as avg_overall
            ')
            ->first();

        return [
            'pedagogik' => round($averages->avg_pedagogik ?? 0, 2),
            'kepribadian' => round($averages->avg_kepribadian ?? 0, 2),
            'sosial' => round($averages->avg_sosial ?? 0, 2),
            'profesional' => round($averages->avg_profesional ?? 0, 2),
            'overall' => round($averages->avg_overall ?? 0, 2),
        ];
    }

    /**
     * Get monthly salary summary
     */
    public function getMonthlySalarySummary(?string $periode = null): array
    {
        $periode = $periode ?? now()->format('Y-m');

        $summary = SalaryCalculation::byPeriode($periode)
            ->selectRaw('
                COUNT(*) as total_guru,
                SUM(total_bruto) as total_bruto,
                SUM(total_potongan) as total_potongan,
                SUM(total_neto) as total_neto,
                SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft_count,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count
            ')
            ->first();

        return [
            'periode' => $periode,
            'total_guru' => $summary->total_guru ?? 0,
            'total_bruto' => $summary->total_bruto ?? 0,
            'total_potongan' => $summary->total_potongan ?? 0,
            'total_neto' => $summary->total_neto ?? 0,
            'draft_count' => $summary->draft_count ?? 0,
            'approved_count' => $summary->approved_count ?? 0,
            'paid_count' => $summary->paid_count ?? 0,
        ];
    }

    /**
     * Get today's teaching schedule
     */
    public function getTodaySchedules(): Collection
    {
        $today = strtolower(now()->translatedFormat('l'));
        $dayMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
        ];
        $hari = $dayMap[$today] ?? 'senin';

        $academicYear = AcademicYear::active()->first();

        return TeachingSchedule::with(['teacher', 'schoolClass', 'subject'])
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->where('hari', $hari)
            ->orderBy('jam_pelajaran')
            ->get();
    }

    /**
     * Get distribution data for charts
     */
    public function getDistributionData(): array
    {
        // Teachers by status
        $byStatus = Teacher::active()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Teachers by subject (top 10)
        $bySubject = DB::table('teacher_subject')
            ->join('subjects', 'teacher_subject.subject_id', '=', 'subjects.id')
            ->join('teachers', 'teacher_subject.teacher_id', '=', 'teachers.id')
            ->where('teachers.is_active', true)
            ->selectRaw('subjects.nama as subject, COUNT(*) as count')
            ->groupBy('subjects.id', 'subjects.nama')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        // Evaluation scores distribution
        $byEvaluation = TeacherEvaluation::published()
            ->selectRaw('
                CASE 
                    WHEN score_overall >= 4.5 THEN "Sangat Baik"
                    WHEN score_overall >= 3.5 THEN "Baik"
                    WHEN score_overall >= 2.5 THEN "Cukup"
                    WHEN score_overall >= 1.5 THEN "Kurang"
                    ELSE "Sangat Kurang"
                END as category,
                COUNT(*) as count
            ')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        return [
            'by_status' => $byStatus,
            'by_subject' => $bySubject,
            'by_evaluation' => $byEvaluation,
        ];
    }

    private function getWorkingDaysInRange($start, $end): int
    {
        // TODO: integrate with holiday calendar
        // For now, count weekdays
        $count = 0;
        $current = $start->copy();
        while ($current <= $end) {
            if (!$current->isWeekend()) {
                $count++;
            }
            $current->addDay();
        }
        return $count;
    }
}
```

**Checklist:**
- [ ] Create TeacherStatisticsService
- [ ] Implement all methods
- [ ] Test statistics calculations
- [ ] Cache expensive queries

---

### Task 5.2: Dashboard Controller
**Estimated:** 1-2 hours

```bash
php artisan make:controller Admin/TeacherDashboardController
```

**TeacherDashboardController:**
```php
class TeacherDashboardController extends Controller
{
    public function __construct(
        private TeacherStatisticsService $statisticsService
    ) {}

    public function index()
    {
        return Inertia::render('Admin/Teachers/Dashboard', [
            // Eager loaded data
            'counts' => $this->statisticsService->getCountByStatus(),
            
            // Deferred props for performance
            'badAttendance' => Inertia::defer(fn() => 
                $this->statisticsService->getTeachersWithBadAttendance()
            ),
            'evaluationAverages' => Inertia::defer(fn() => 
                $this->statisticsService->getAverageEvaluationScores()
            ),
            'salarySummary' => Inertia::defer(fn() => 
                $this->statisticsService->getMonthlySalarySummary()
            ),
            'todaySchedules' => Inertia::defer(fn() => 
                $this->statisticsService->getTodaySchedules()
            ),
            'distribution' => Inertia::defer(fn() => 
                $this->statisticsService->getDistributionData()
            ),
        ]);
    }
}
```

**Checklist:**
- [ ] Create TeacherDashboardController
- [ ] Use deferred props for expensive data
- [ ] Register route

---

### Task 5.3: Export Controllers
**Estimated:** 2-3 hours

```bash
php artisan make:controller Admin/TeacherExportController
```

**TeacherExportController:**
```php
class TeacherExportController extends Controller
{
    /**
     * Export teachers data to Excel
     */
    public function teachers(Request $request)
    {
        $filters = $request->only(['status', 'is_active', 'search']);

        return Excel::download(
            new TeachersExport($filters),
            'DataGuru_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export schedules to PDF
     */
    public function schedules(Request $request)
    {
        $type = $request->type ?? 'teacher'; // teacher or class
        $id = $request->id;
        $academicYearId = $request->academic_year_id 
            ?? AcademicYear::active()->value('id');

        $service = app(TeachingScheduleService::class);
        
        if ($type === 'teacher') {
            $teacher = Teacher::findOrFail($id);
            $matrix = $service->getScheduleMatrix($academicYearId, $id);
            $title = "Jadwal Mengajar - {$teacher->nama_lengkap}";
        } else {
            $class = SchoolClass::findOrFail($id);
            $matrix = $service->getScheduleMatrix($academicYearId, null, $id);
            $title = "Jadwal Kelas - {$class->nama}";
        }

        $pdf = PDF::loadView('pdf.jadwal', [
            'matrix' => $matrix,
            'title' => $title,
            'type' => $type,
        ]);

        return $pdf->download("Jadwal_{$type}_{$id}.pdf");
    }

    /**
     * Export evaluations to Excel
     */
    public function evaluations(Request $request)
    {
        $filters = $request->only(['periode', 'teacher_id']);

        return Excel::download(
            new EvaluationsExport($filters),
            'Evaluasi_Guru_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
```

**Checklist:**
- [ ] Create TeacherExportController
- [ ] Implement teachers export
- [ ] Implement schedules PDF export
- [ ] Implement evaluations export
- [ ] Register routes

---

### Task 5.4: Export Classes
**Estimated:** 2-3 hours

**TeachersExport:**
```php
class TeachersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        return Teacher::query()
            ->with('subjects')
            ->when($this->filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when(isset($this->filters['is_active']), fn($q) => 
                $q->where('is_active', $this->filters['is_active']))
            ->when($this->filters['search'] ?? null, fn($q, $s) => 
                $q->where('nama_lengkap', 'like', "%{$s}%"))
            ->orderBy('nama_lengkap');
    }

    public function headings(): array
    {
        return [
            'NIP',
            'NIK',
            'Nama Lengkap',
            'Gelar',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'No HP',
            'Email',
            'Status',
            'Pendidikan Terakhir',
            'Tanggal Bergabung',
            'Mata Pelajaran',
            'Gaji/Honor',
            'Aktif',
        ];
    }

    public function map($teacher): array
    {
        return [
            $teacher->nip ?? '-',
            $teacher->nik,
            $teacher->nama_lengkap,
            $teacher->gelar ?? '-',
            $teacher->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $teacher->tempat_lahir,
            $teacher->tanggal_lahir->format('d/m/Y'),
            $teacher->alamat,
            $teacher->no_hp,
            $teacher->email ?? '-',
            ucfirst($teacher->status),
            $teacher->pendidikan_terakhir ?? '-',
            $teacher->tanggal_bergabung->format('d/m/Y'),
            $teacher->subjects->pluck('nama')->join(', '),
            $teacher->status === 'tetap' 
                ? 'Rp ' . number_format($teacher->gaji_tetap, 0, ',', '.')
                : 'Rp ' . number_format($teacher->honor_per_jam, 0, ',', '.') . '/jam',
            $teacher->is_active ? 'Ya' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
```

**EvaluationsExport:**
```php
class EvaluationsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        return TeacherEvaluation::query()
            ->with('teacher')
            ->when($this->filters['periode'] ?? null, fn($q, $p) => $q->where('periode', $p))
            ->when($this->filters['teacher_id'] ?? null, fn($q, $id) => $q->where('teacher_id', $id))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Periode',
            'NIP',
            'Nama Guru',
            'Pedagogik',
            'Kepribadian',
            'Sosial',
            'Profesional',
            'Overall',
            'Rekomendasi',
            'Status',
            'Tanggal Evaluasi',
        ];
    }

    public function map($evaluation): array
    {
        return [
            $evaluation->periode,
            $evaluation->teacher->nip ?? '-',
            $evaluation->teacher->nama_lengkap,
            $evaluation->score_pedagogik,
            $evaluation->score_kepribadian,
            $evaluation->score_sosial,
            $evaluation->score_profesional,
            $evaluation->score_overall,
            $evaluation->rekomendasi_label,
            $evaluation->is_published ? 'Published' : 'Draft',
            $evaluation->created_at->format('d/m/Y'),
        ];
    }
}
```

**Checklist:**
- [ ] Create TeachersExport class
- [ ] Create EvaluationsExport class
- [ ] Style Excel output
- [ ] Test exports

---

### Task 5.5: PDF Views
**Estimated:** 1-2 hours

**resources/views/pdf/jadwal.blade.php:**
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { 
            border: 1px solid #333; 
            padding: 5px; 
            text-align: center;
            vertical-align: middle;
        }
        th { background: #f0f0f0; }
        .day { font-weight: bold; width: 80px; }
        .empty { background: #fafafa; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    
    <table>
        <thead>
            <tr>
                <th class="day">Hari</th>
                @for($i = 1; $i <= 8; $i++)
                <th>Jam {{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $day)
            <tr>
                <td class="day">{{ ucfirst($day) }}</td>
                @for($i = 1; $i <= 8; $i++)
                    @php $schedule = $matrix[$day][$i] ?? null; @endphp
                    @if($schedule)
                    <td>
                        @if($type === 'teacher')
                            {{ $schedule->schoolClass->nama }}<br>
                            <small>{{ $schedule->subject->nama }}</small>
                        @else
                            {{ $schedule->teacher->nama_lengkap }}<br>
                            <small>{{ $schedule->subject->nama }}</small>
                        @endif
                    </td>
                    @else
                    <td class="empty">-</td>
                    @endif
                @endfor
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 9px;">
        Dicetak: {{ now()->format('d F Y H:i') }}
    </div>
</body>
</html>
```

**Checklist:**
- [ ] Create jadwal PDF view
- [ ] Style for print
- [ ] Test PDF generation

---

### Task 5.6: Vue Dashboard Page
**Estimated:** 4-5 hours

**File:** `resources/js/pages/Admin/Teachers/Dashboard.vue`

**Sections:**
1. **Summary Cards**
   - Total Guru Aktif
   - Guru Tetap
   - Guru Honorer
   - Guru Nonaktif

2. **Alert Card**
   - Guru dengan presensi buruk (< 80%)
   - Click to see details

3. **Evaluation Summary**
   - Average scores per kompetensi
   - Overall average
   - Bar chart

4. **Salary Summary (Current Month)**
   - Total to be paid
   - Draft / Approved / Paid breakdown

5. **Today's Schedule Widget**
   - List of classes today
   - Grouped by jam pelajaran

6. **Quick Actions**
   - Tambah Guru
   - Jadwal Mengajar
   - Rekap Honor
   - Evaluasi

7. **Charts (Optional)**
   - Teacher distribution by status (Pie)
   - Evaluation score distribution (Bar)

**Components:**
```
resources/js/components/Dashboard/
├── StatCard.vue           // Summary stat card with icon
├── AlertCard.vue          // Warning/alert card
├── EvaluationChart.vue    // Bar chart for evaluation
├── SalarySummary.vue      // Salary overview
├── TodaySchedule.vue      // Today's schedule list
├── QuickActions.vue       // Action buttons
└── DistributionChart.vue  // Pie/bar charts
```

**Checklist:**
- [ ] Create Dashboard.vue
- [ ] Create StatCard component
- [ ] Create AlertCard component
- [ ] Create TodaySchedule component
- [ ] Create QuickActions component
- [ ] Implement deferred props loading
- [ ] Add skeleton loading states
- [ ] Mobile responsive grid

---

### Task 5.7: Routes & Navigation
**Estimated:** 1 hour

**Routes:**
```php
Route::middleware(['auth', 'role:admin,tu,kepala_sekolah'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('teachers/dashboard', [TeacherDashboardController::class, 'index'])
        ->name('teachers.dashboard');

    // Exports
    Route::prefix('teachers/export')->name('teachers.export.')->group(function () {
        Route::get('teachers', [TeacherExportController::class, 'teachers'])->name('teachers');
        Route::get('schedules', [TeacherExportController::class, 'schedules'])->name('schedules');
        Route::get('evaluations', [TeacherExportController::class, 'evaluations'])->name('evaluations');
    });
});
```

**Update Navigation:**
```typescript
// In useNavigation.ts
{
  name: 'Guru',
  icon: 'users',
  children: [
    { name: 'Dashboard', href: '/admin/teachers/dashboard' },
    { name: 'Data Guru', href: '/admin/teachers' },
    { name: 'Jadwal Mengajar', href: '/admin/teachers/schedules' },
    { name: 'Rekap Honor', href: '/admin/teachers/salary' },
    { name: 'Evaluasi', href: '/admin/teachers/evaluations' },
  ]
}
```

**Checklist:**
- [ ] Add dashboard route
- [ ] Add export routes
- [ ] Update navigation menu
- [ ] Generate Wayfinder routes
- [ ] Test all navigation

---

### Task 5.8: Integration & Polish
**Estimated:** 3-4 hours

**Tasks:**
1. **Cross-link all pages**
   - Teacher profile → Schedules, Salary, Evaluations
   - Dashboard → All detail pages
   - Lists → Profile pages

2. **Consistent UI/UX**
   - Same button styles
   - Same card styles
   - Same table styles
   - Same filter patterns

3. **Performance**
   - Lazy load heavy components
   - Cache statistics (Redis)
   - Optimize queries

4. **Mobile**
   - Test all pages on mobile
   - Fix any layout issues
   - Touch-friendly buttons

5. **Final Bug Fixes**
   - Test all CRUD operations
   - Test all exports
   - Test all validations

**Checklist:**
- [ ] All pages cross-linked
- [ ] UI consistency check
- [ ] Performance optimization
- [ ] Mobile testing
- [ ] Bug fixes
- [ ] Final QA

---

## ✅ Definition of Done

- [ ] Dashboard dengan semua summary cards
- [ ] Alert card untuk presensi buruk
- [ ] Today's schedule widget
- [ ] Quick actions functional
- [ ] Export teachers to Excel
- [ ] Export schedules to PDF
- [ ] Export evaluations to Excel
- [ ] Export payroll to Excel (from Epic 3)
- [ ] Navigation terintegrasi
- [ ] UI consistent across all pages
- [ ] Mobile responsive
- [ ] Performance acceptable
- [ ] No linter errors
- [ ] All tests pass

---

## 🧪 Test Cases

### Feature Tests
```php
public function test_dashboard_loads_with_all_data()
public function test_teacher_export_generates_excel()
public function test_schedule_export_generates_pdf()
public function test_evaluation_export_generates_excel()
public function test_export_respects_filters()
```

### Unit Tests
```php
public function test_get_count_by_status()
public function test_get_teachers_with_bad_attendance()
public function test_get_average_evaluation_scores()
public function test_get_monthly_salary_summary()
public function test_get_today_schedules()
```

---

## 📊 Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────┐
│                    TEACHER MANAGEMENT                        │
├─────────┬─────────┬─────────┬─────────┬─────────────────────┤
│ Total   │ Tetap   │ Honorer │Nonaktif │   Quick Actions     │
│   45    │   20    │   23    │    2    │ [+Guru] [Jadwal]    │
│         │         │         │         │ [Honor] [Evaluasi]  │
├─────────┴─────────┴─────────┴─────────┴─────────────────────┤
│ ⚠️ Alert: 3 guru dengan presensi < 80%                       │
│    - Bu Ani (75%), Pak Budi (72%), Bu Citra (78%)          │
├─────────────────────────────────┬───────────────────────────┤
│ Evaluasi Rata-rata              │ Honor Bulan Ini           │
│ ████████░░ Pedagogik  4.2       │ Total: Rp 125.000.000    │
│ ███████░░░ Kepribadian 3.8      │ Draft: 5 | Approved: 35  │
│ █████████░ Sosial     4.5       │ Paid: 5                   │
│ ████████░░ Profesional 4.0      │                           │
├─────────────────────────────────┴───────────────────────────┤
│ Jadwal Hari Ini (Rabu)                                       │
│ Jam 1: Bu Ani → 1A (MTK), Pak Budi → 2B (IPA)               │
│ Jam 2: Bu Citra → 1B (BIN), Pak Dani → 3A (IPS)             │
│ ...                                                          │
└─────────────────────────────────────────────────────────────┘
```

---

## 📝 Notes

### Caching Strategy
```php
// Cache statistics for 5 minutes
Cache::remember('teacher_stats', 300, fn() => $this->statisticsService->getCountByStatus());
```

### Performance Tips
- Use Inertia deferred props for heavy data
- Paginate large lists
- Lazy load charts
- Cache dashboard statistics

### Mobile Layout
- Stack cards vertically on mobile
- Horizontal scroll for schedule matrix
- Collapsible sections

---

**Epic Status:** 🔵 Not Started  
**Last Updated:** 29 Januari 2026
