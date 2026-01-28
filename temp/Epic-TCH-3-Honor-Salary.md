# Epic TCH-3: Honor & Salary Calculation

**Sprint:** Week 3  
**Story Points:** 10 points  
**Priority:** Must Have  
**Status:** 🔵 Not Started  
**Dependencies:** Epic TCH-1 (Foundation), Epic TCH-2 (Schedule), Attendance System

---

## 📋 Overview

Implementasi perhitungan jam mengajar efektif, honor/gaji guru, slip gaji PDF, dan export payroll ke Excel. Termasuk konfigurasi tarif honor per kategori.

---

## 🎯 Goals

1. Konfigurasi tarif honor (default & custom per guru)
2. Perhitungan jam mengajar efektif per bulan
3. Perhitungan gaji guru tetap & honor guru honorer
4. Generate slip gaji PDF
5. Export payroll ke Excel

---

## 📖 User Stories

### US-TCH-005: Set Honor/Tarif Guru
**Priority:** Must Have | **Points:** 2

**Acceptance Criteria:**
- [ ] Admin dapat set tarif default per kategori: Guru Tetap, Guru Honorer, Jam Ekstra
- [ ] Admin dapat set tarif custom untuk guru tertentu (override default)
- [ ] History perubahan tarif tersimpan untuk audit
- [ ] Tarif efektif berdasarkan tanggal

### US-TCH-004: Rekap Jam Mengajar
**Priority:** Must Have | **Points:** 3

**Acceptance Criteria:**
- [ ] TU dapat melihat rekap jam mengajar per guru per bulan
- [ ] Breakdown: Jam Regular, Jam Ekstra, Total Jam
- [ ] Integrasi dengan presensi: hanya hitung jam saat guru hadir
- [ ] Adjust untuk hari libur (exclude dari perhitungan)

### FR-TCH-004: Salary/Honor Calculation
**Priority:** Must Have | **Points:** 3

**Acceptance Criteria:**
- [ ] Perhitungan Guru Tetap: Gaji Tetap + Tunjangan - Potongan
- [ ] Perhitungan Guru Honorer: (Jam Efektif × Tarif) - Potongan
- [ ] Potongan otomatis dari data alpha/telat
- [ ] Preview perhitungan sebelum approve
- [ ] Approval workflow: Draft → Approved → Paid

### Slip Gaji & Export
**Priority:** Must Have | **Points:** 2

**Acceptance Criteria:**
- [ ] Generate slip gaji PDF per guru
- [ ] Slip berisi: breakdown gaji, potongan, total
- [ ] Export rekap payroll ke Excel (semua guru)
- [ ] Format nama file: SlipGaji_[Nama]_[Periode].pdf

---

## 📝 Technical Tasks

### Task 3.1: Database Migrations
**Estimated:** 2-3 hours

```bash
php artisan make:migration create_honor_settings_table
php artisan make:migration create_teacher_custom_rates_table
php artisan make:migration create_salary_calculations_table
php artisan make:migration create_salary_calculation_details_table
```

**Schema - honor_settings:**
```php
Schema::create('honor_settings', function (Blueprint $table) {
    $table->id();
    $table->enum('kategori', ['tetap', 'honorer', 'ekstra']);
    $table->decimal('tarif_per_jam', 10, 0);
    $table->date('effective_from');
    $table->date('effective_until')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->text('catatan')->nullable();
    $table->timestamps();
    
    $table->index(['kategori', 'effective_from']);
});
```

**Schema - teacher_custom_rates:**
```php
Schema::create('teacher_custom_rates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
    $table->decimal('tarif_per_jam', 10, 0);
    $table->date('effective_from');
    $table->date('effective_until')->nullable();
    $table->text('catatan')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
    
    $table->index(['teacher_id', 'effective_from']);
});
```

**Schema - salary_calculations:**
```php
Schema::create('salary_calculations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
    $table->string('periode', 7); // Format: 2025-01
    
    // Jam Mengajar
    $table->integer('total_jam_jadwal')->default(0);
    $table->integer('total_jam_hadir')->default(0);
    $table->integer('total_jam_tidak_hadir')->default(0);
    $table->integer('total_jam_ekstra')->default(0);
    
    // Komponen Gaji
    $table->decimal('gaji_pokok', 12, 0)->default(0);
    $table->decimal('honor_jam_regular', 12, 0)->default(0);
    $table->decimal('honor_jam_ekstra', 12, 0)->default(0);
    $table->decimal('tunjangan', 12, 0)->default(0);
    
    // Potongan
    $table->decimal('potongan_alpha', 12, 0)->default(0);
    $table->decimal('potongan_telat', 12, 0)->default(0);
    $table->decimal('potongan_lain', 12, 0)->default(0);
    
    // Total
    $table->decimal('total_bruto', 12, 0)->default(0);
    $table->decimal('total_potongan', 12, 0)->default(0);
    $table->decimal('total_neto', 12, 0)->default(0);
    
    // Status & Approval
    $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamp('approved_at')->nullable();
    $table->foreignId('paid_by')->nullable()->constrained('users');
    $table->timestamp('paid_at')->nullable();
    
    $table->text('catatan')->nullable();
    $table->timestamps();
    
    $table->unique(['teacher_id', 'periode']);
});
```

**Schema - salary_calculation_details:**
```php
Schema::create('salary_calculation_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('salary_calculation_id')->constrained()->cascadeOnDelete();
    
    $table->date('tanggal');
    $table->enum('jenis', ['regular', 'ekstra', 'alpha', 'telat', 'izin', 'sakit', 'potongan_lain']);
    $table->integer('jam')->default(0);
    $table->decimal('tarif', 10, 0)->default(0);
    $table->decimal('subtotal', 12, 0)->default(0);
    $table->text('keterangan')->nullable();
    
    $table->timestamps();
});
```

**Checklist:**
- [ ] Create honor_settings migration
- [ ] Create teacher_custom_rates migration
- [ ] Create salary_calculations migration
- [ ] Create salary_calculation_details migration
- [ ] Run migrations

---

### Task 3.2: Models & Relationships
**Estimated:** 2 hours

```bash
php artisan make:model HonorSetting
php artisan make:model TeacherCustomRate
php artisan make:model SalaryCalculation
php artisan make:model SalaryCalculationDetail
```

**HonorSetting Model:**
```php
class HonorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori', 'tarif_per_jam', 'effective_from', 
        'effective_until', 'created_by', 'catatan'
    ];

    protected function casts(): array
    {
        return [
            'tarif_per_jam' => 'decimal:0',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get tarif efektif untuk kategori pada tanggal tertentu
     */
    public static function getTarif(string $kategori, ?Carbon $date = null): ?float
    {
        $date = $date ?? now();
        
        return static::where('kategori', $kategori)
            ->where('effective_from', '<=', $date)
            ->where(fn($q) => $q->whereNull('effective_until')
                ->orWhere('effective_until', '>=', $date))
            ->orderByDesc('effective_from')
            ->value('tarif_per_jam');
    }
}
```

**SalaryCalculation Model:**
```php
class SalaryCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'periode',
        'total_jam_jadwal', 'total_jam_hadir', 'total_jam_tidak_hadir', 'total_jam_ekstra',
        'gaji_pokok', 'honor_jam_regular', 'honor_jam_ekstra', 'tunjangan',
        'potongan_alpha', 'potongan_telat', 'potongan_lain',
        'total_bruto', 'total_potongan', 'total_neto',
        'status', 'approved_by', 'approved_at', 'paid_by', 'paid_at', 'catatan'
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:0',
            'honor_jam_regular' => 'decimal:0',
            'honor_jam_ekstra' => 'decimal:0',
            'tunjangan' => 'decimal:0',
            'potongan_alpha' => 'decimal:0',
            'potongan_telat' => 'decimal:0',
            'potongan_lain' => 'decimal:0',
            'total_bruto' => 'decimal:0',
            'total_potongan' => 'decimal:0',
            'total_neto' => 'decimal:0',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalaryCalculationDetail::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeByPeriode($query, string $periode)
    {
        return $query->where('periode', $periode);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
```

**Checklist:**
- [ ] Create HonorSetting model
- [ ] Create TeacherCustomRate model
- [ ] Create SalaryCalculation model
- [ ] Create SalaryCalculationDetail model
- [ ] Test relationships

---

### Task 3.3: Salary Calculation Service
**Estimated:** 4-5 hours

```bash
php artisan make:class Services/SalaryCalculationService
```

**SalaryCalculationService:**
```php
class SalaryCalculationService
{
    /**
     * Hitung jam mengajar dari jadwal untuk bulan tertentu
     */
    public function getScheduledHours(int $teacherId, string $periode): int
    {
        [$year, $month] = explode('-', $periode);
        
        // Get active academic year
        $academicYear = AcademicYear::active()->first();
        if (!$academicYear) return 0;

        // Count schedules per week
        $weeklyHours = TeachingSchedule::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYear->id)
            ->count();

        // Calculate effective weeks in month (exclude holidays)
        $effectiveWeeks = $this->getEffectiveWeeks($year, $month);

        return $weeklyHours * $effectiveWeeks;
    }

    /**
     * Get attendance data untuk bulan tertentu
     */
    public function getAttendanceData(int $teacherId, string $periode): array
    {
        [$year, $month] = explode('-', $periode);
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        return [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'telat' => $attendances->where('is_late', true)->count(),
        ];
    }

    /**
     * Get tarif untuk guru (custom atau default)
     */
    public function getTarifForTeacher(Teacher $teacher, ?Carbon $date = null): float
    {
        $date = $date ?? now();

        // Check custom rate first
        $customRate = TeacherCustomRate::where('teacher_id', $teacher->id)
            ->where('effective_from', '<=', $date)
            ->where(fn($q) => $q->whereNull('effective_until')
                ->orWhere('effective_until', '>=', $date))
            ->orderByDesc('effective_from')
            ->value('tarif_per_jam');

        if ($customRate) {
            return $customRate;
        }

        // Fallback to teacher's own rate
        if ($teacher->honor_per_jam) {
            return $teacher->honor_per_jam;
        }

        // Fallback to default rate by status
        return HonorSetting::getTarif($teacher->status, $date) ?? 0;
    }

    /**
     * Calculate salary for a teacher for a specific period
     */
    public function calculateSalary(int $teacherId, string $periode): SalaryCalculation
    {
        $teacher = Teacher::findOrFail($teacherId);
        
        // Get scheduled hours
        $scheduledHours = $this->getScheduledHours($teacherId, $periode);
        
        // Get attendance data
        $attendance = $this->getAttendanceData($teacherId, $periode);
        
        // Calculate effective hours
        $hoursNotAttended = $attendance['alpha'] * $this->getHoursPerDay($teacherId);
        $effectiveHours = max(0, $scheduledHours - $hoursNotAttended);
        
        // Get tarif
        $tarif = $this->getTarifForTeacher($teacher);
        $tarifEkstra = HonorSetting::getTarif('ekstra') ?? ($tarif * 1.5);
        
        // Calculate components based on status
        if ($teacher->status === 'tetap') {
            $gajiPokok = $teacher->gaji_tetap ?? 0;
            $honorRegular = 0;
        } else {
            $gajiPokok = 0;
            $honorRegular = $effectiveHours * $tarif;
        }
        
        // Potongan
        $potonganAlpha = $attendance['alpha'] * $this->getPotonganPerAlpha();
        $potonganTelat = $attendance['telat'] * $this->getPotonganPerTelat();
        
        // Totals
        $totalBruto = $gajiPokok + $honorRegular;
        $totalPotongan = $potonganAlpha + $potonganTelat;
        $totalNeto = max(0, $totalBruto - $totalPotongan);

        // Create or update calculation
        return SalaryCalculation::updateOrCreate(
            ['teacher_id' => $teacherId, 'periode' => $periode],
            [
                'total_jam_jadwal' => $scheduledHours,
                'total_jam_hadir' => $effectiveHours,
                'total_jam_tidak_hadir' => $hoursNotAttended,
                'total_jam_ekstra' => 0, // TODO: implement extra hours
                'gaji_pokok' => $gajiPokok,
                'honor_jam_regular' => $honorRegular,
                'honor_jam_ekstra' => 0,
                'tunjangan' => 0,
                'potongan_alpha' => $potonganAlpha,
                'potongan_telat' => $potonganTelat,
                'potongan_lain' => 0,
                'total_bruto' => $totalBruto,
                'total_potongan' => $totalPotongan,
                'total_neto' => $totalNeto,
                'status' => 'draft',
            ]
        );
    }

    /**
     * Calculate salary for all teachers for a period
     */
    public function calculateAllSalaries(string $periode): array
    {
        $teachers = Teacher::active()->get();
        $results = [];

        foreach ($teachers as $teacher) {
            $results[] = $this->calculateSalary($teacher->id, $periode);
        }

        return $results;
    }

    /**
     * Approve salary calculation
     */
    public function approve(SalaryCalculation $calculation, int $userId): void
    {
        $calculation->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(SalaryCalculation $calculation, int $userId): void
    {
        $calculation->update([
            'status' => 'paid',
            'paid_by' => $userId,
            'paid_at' => now(),
        ]);
    }

    // Helper methods
    private function getEffectiveWeeks(int $year, int $month): int
    {
        // TODO: integrate with holiday calendar
        // For now, assume 4 weeks
        return 4;
    }

    private function getHoursPerDay(int $teacherId): int
    {
        // Calculate average hours per day from schedule
        $academicYear = AcademicYear::active()->first();
        if (!$academicYear) return 4;

        $totalHours = TeachingSchedule::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYear->id)
            ->count();

        $daysPerWeek = TeachingSchedule::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYear->id)
            ->distinct('hari')
            ->count('hari');

        return $daysPerWeek > 0 ? ceil($totalHours / $daysPerWeek) : 4;
    }

    private function getPotonganPerAlpha(): float
    {
        // TODO: make configurable
        return 50000;
    }

    private function getPotonganPerTelat(): float
    {
        // TODO: make configurable
        return 10000;
    }
}
```

**Checklist:**
- [ ] Create SalaryCalculationService
- [ ] Implement getScheduledHours
- [ ] Implement getAttendanceData
- [ ] Implement getTarifForTeacher
- [ ] Implement calculateSalary
- [ ] Implement calculateAllSalaries
- [ ] Implement approve/markAsPaid
- [ ] Write unit tests

---

### Task 3.4: Controllers
**Estimated:** 3-4 hours

```bash
php artisan make:controller Admin/HonorSettingController
php artisan make:controller Admin/SalaryController
```

**HonorSettingController:**
```php
class HonorSettingController extends Controller
{
    public function index()
    {
        $settings = HonorSetting::with('creator')
            ->orderByDesc('effective_from')
            ->get()
            ->groupBy('kategori');

        return Inertia::render('Admin/Teachers/Salary/Settings', [
            'settings' => $settings,
            'categories' => ['tetap', 'honorer', 'ekstra'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => ['required', 'in:tetap,honorer,ekstra'],
            'tarif_per_jam' => ['required', 'numeric', 'min:10000'],
            'effective_from' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        HonorSetting::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Tarif berhasil disimpan');
    }
}
```

**SalaryController:**
```php
class SalaryController extends Controller
{
    public function __construct(
        private SalaryCalculationService $salaryService
    ) {}

    public function index(Request $request)
    {
        $periode = $request->periode ?? now()->format('Y-m');

        $calculations = SalaryCalculation::with('teacher')
            ->byPeriode($periode)
            ->orderBy('teacher_id')
            ->get();

        $summary = [
            'total_guru' => $calculations->count(),
            'total_bruto' => $calculations->sum('total_bruto'),
            'total_potongan' => $calculations->sum('total_potongan'),
            'total_neto' => $calculations->sum('total_neto'),
            'draft' => $calculations->where('status', 'draft')->count(),
            'approved' => $calculations->where('status', 'approved')->count(),
            'paid' => $calculations->where('status', 'paid')->count(),
        ];

        return Inertia::render('Admin/Teachers/Salary/Index', [
            'calculations' => $calculations,
            'summary' => $summary,
            'periode' => $periode,
        ]);
    }

    public function calculate(Request $request)
    {
        $periode = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
        ])['periode'];

        $this->salaryService->calculateAllSalaries($periode);

        return redirect()->route('admin.teachers.salary.index', ['periode' => $periode])
            ->with('success', 'Perhitungan gaji berhasil');
    }

    public function show(SalaryCalculation $calculation)
    {
        $calculation->load(['teacher', 'details', 'approver']);

        return Inertia::render('Admin/Teachers/Salary/Detail', [
            'calculation' => $calculation,
        ]);
    }

    public function approve(SalaryCalculation $calculation)
    {
        $this->salaryService->approve($calculation, auth()->id());

        return redirect()->back()->with('success', 'Perhitungan disetujui');
    }

    public function generateSlip(SalaryCalculation $calculation)
    {
        $pdf = PDF::loadView('pdf.slip-gaji', [
            'calculation' => $calculation->load('teacher'),
        ]);

        $filename = "SlipGaji_{$calculation->teacher->nama_lengkap}_{$calculation->periode}.pdf";

        return $pdf->download($filename);
    }

    public function exportPayroll(Request $request)
    {
        $periode = $request->periode ?? now()->format('Y-m');

        return Excel::download(
            new PayrollExport($periode),
            "Payroll_{$periode}.xlsx"
        );
    }
}
```

**Checklist:**
- [ ] Create HonorSettingController
- [ ] Create SalaryController
- [ ] Implement all methods
- [ ] Register routes

---

### Task 3.5: PDF & Export
**Estimated:** 3-4 hours

**Install Packages:**
```bash
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
```

**Slip Gaji View:** `resources/views/pdf/slip-gaji.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .amount { text-align: right; }
        .total { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SLIP GAJI</h2>
        <p>Periode: {{ $calculation->periode }}</p>
    </div>

    <div class="info">
        <table>
            <tr><td>Nama</td><td>: {{ $calculation->teacher->nama_lengkap }}</td></tr>
            <tr><td>NIP</td><td>: {{ $calculation->teacher->nip ?? '-' }}</td></tr>
            <tr><td>Status</td><td>: {{ ucfirst($calculation->teacher->status) }}</td></tr>
        </table>
    </div>

    <h4>Penghasilan</h4>
    <table>
        @if($calculation->gaji_pokok > 0)
        <tr><td>Gaji Pokok</td><td class="amount">Rp {{ number_format($calculation->gaji_pokok, 0, ',', '.') }}</td></tr>
        @endif
        @if($calculation->honor_jam_regular > 0)
        <tr><td>Honor Mengajar ({{ $calculation->total_jam_hadir }} jam)</td><td class="amount">Rp {{ number_format($calculation->honor_jam_regular, 0, ',', '.') }}</td></tr>
        @endif
        @if($calculation->tunjangan > 0)
        <tr><td>Tunjangan</td><td class="amount">Rp {{ number_format($calculation->tunjangan, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="total"><td>Total Bruto</td><td class="amount">Rp {{ number_format($calculation->total_bruto, 0, ',', '.') }}</td></tr>
    </table>

    <h4>Potongan</h4>
    <table>
        @if($calculation->potongan_alpha > 0)
        <tr><td>Potongan Alpha</td><td class="amount">Rp {{ number_format($calculation->potongan_alpha, 0, ',', '.') }}</td></tr>
        @endif
        @if($calculation->potongan_telat > 0)
        <tr><td>Potongan Keterlambatan</td><td class="amount">Rp {{ number_format($calculation->potongan_telat, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="total"><td>Total Potongan</td><td class="amount">Rp {{ number_format($calculation->total_potongan, 0, ',', '.') }}</td></tr>
    </table>

    <h4>Total Diterima</h4>
    <table>
        <tr class="total"><td>TOTAL NETO</td><td class="amount">Rp {{ number_format($calculation->total_neto, 0, ',', '.') }}</td></tr>
    </table>

    <div style="margin-top: 40px;">
        <p>Dikeluarkan: {{ now()->format('d F Y') }}</p>
    </div>
</body>
</html>
```

**PayrollExport:**
```php
class PayrollExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private string $periode) {}

    public function collection()
    {
        return SalaryCalculation::with('teacher')
            ->byPeriode($this->periode)
            ->get();
    }

    public function headings(): array
    {
        return [
            'NIP', 'Nama', 'Status', 'Total Jam', 
            'Gaji Pokok', 'Honor', 'Tunjangan',
            'Pot. Alpha', 'Pot. Telat', 'Total Potongan',
            'Total Neto', 'Status'
        ];
    }

    public function map($row): array
    {
        return [
            $row->teacher->nip ?? '-',
            $row->teacher->nama_lengkap,
            ucfirst($row->teacher->status),
            $row->total_jam_hadir,
            $row->gaji_pokok,
            $row->honor_jam_regular,
            $row->tunjangan,
            $row->potongan_alpha,
            $row->potongan_telat,
            $row->total_potongan,
            $row->total_neto,
            ucfirst($row->status),
        ];
    }
}
```

**Checklist:**
- [ ] Install DomPDF package
- [ ] Install Laravel Excel package
- [ ] Create slip gaji blade view
- [ ] Create PayrollExport class
- [ ] Test PDF generation
- [ ] Test Excel export

---

### Task 3.6: Vue Pages
**Estimated:** 4-5 hours

**Pages:**
```
resources/js/pages/Admin/Teachers/Salary/
├── Index.vue       // Dashboard dengan summary & list
├── Detail.vue      // Detail perhitungan per guru
├── Settings.vue    // Pengaturan tarif
└── Calculate.vue   // Form trigger perhitungan
```

**Index.vue Features:**
- Summary cards: Total Guru, Total Bruto, Total Potongan, Total Neto
- Status breakdown: Draft, Approved, Paid
- Table: NIP, Nama, Status, Total Jam, Total Gaji, Actions
- Filter by periode (month picker)
- Batch actions: Calculate All, Approve All
- Export button

**Detail.vue Features:**
- Teacher info card
- Breakdown calculation
- Approve button
- Generate slip PDF button

**Settings.vue Features:**
- Current tarif per kategori
- History perubahan
- Form tambah tarif baru

**Checklist:**
- [ ] Create Index.vue
- [ ] Create Detail.vue
- [ ] Create Settings.vue
- [ ] Summary cards dengan proper formatting
- [ ] Month picker component
- [ ] Export & PDF buttons
- [ ] Mobile responsive

---

### Task 3.7: Routes Registration
**Estimated:** 30 minutes

```php
Route::middleware(['auth', 'role:admin,tu'])->prefix('admin')->name('admin.')->group(function () {
    // Honor Settings
    Route::get('teachers/salary/settings', [HonorSettingController::class, 'index'])
        ->name('teachers.salary.settings');
    Route::post('teachers/salary/settings', [HonorSettingController::class, 'store'])
        ->name('teachers.salary.settings.store');

    // Salary Calculation
    Route::prefix('teachers/salary')->name('teachers.salary.')->group(function () {
        Route::get('/', [SalaryController::class, 'index'])->name('index');
        Route::post('/calculate', [SalaryController::class, 'calculate'])->name('calculate');
        Route::get('/{calculation}', [SalaryController::class, 'show'])->name('show');
        Route::post('/{calculation}/approve', [SalaryController::class, 'approve'])->name('approve');
        Route::get('/{calculation}/slip', [SalaryController::class, 'generateSlip'])->name('slip');
        Route::get('/export/payroll', [SalaryController::class, 'exportPayroll'])->name('export');
    });
});
```

**Checklist:**
- [ ] Add routes to web.php
- [ ] Generate Wayfinder routes
- [ ] Verify routes

---

## ✅ Definition of Done

- [ ] Tarif honor dapat dikonfigurasi (default & custom)
- [ ] Perhitungan jam mengajar dari jadwal berfungsi
- [ ] Potongan dari data presensi berfungsi
- [ ] Perhitungan gaji tetap & honorer berfungsi
- [ ] Approval workflow berfungsi
- [ ] Slip gaji PDF dapat di-generate
- [ ] Export payroll Excel berfungsi
- [ ] Mobile responsive
- [ ] No linter errors
- [ ] Unit tests pass

---

## 🧪 Test Cases

### Feature Tests
```php
public function test_admin_can_set_honor_tarif()
public function test_admin_can_calculate_salary_for_period()
public function test_salary_calculation_uses_custom_rate_when_available()
public function test_salary_calculation_deducts_for_alpha()
public function test_admin_can_approve_salary()
public function test_slip_gaji_pdf_can_be_generated()
public function test_payroll_can_be_exported_to_excel()
```

### Unit Tests
```php
public function test_get_scheduled_hours_calculates_correctly()
public function test_get_tarif_for_teacher_returns_custom_rate_first()
public function test_calculate_salary_for_guru_tetap()
public function test_calculate_salary_for_guru_honorer()
```

---

## 📝 Formula Reference

### Guru Tetap
```
Total Bruto = Gaji Pokok + Tunjangan
Total Potongan = (Jumlah Alpha × Potongan per Alpha) + (Jumlah Telat × Potongan per Telat)
Total Neto = Total Bruto - Total Potongan
```

### Guru Honorer
```
Jam Efektif = Jam dari Jadwal - (Jumlah Alpha × Jam per Hari)
Total Bruto = Jam Efektif × Tarif per Jam
Total Potongan = (Jumlah Telat × Potongan per Telat)
Total Neto = Total Bruto - Total Potongan
```

### Default Values (Configurable)
- Potongan per Alpha: Rp 50.000
- Potongan per Telat: Rp 10.000
- Minggu Efektif per Bulan: 4

---

**Epic Status:** 🔵 Not Started  
**Last Updated:** 29 Januari 2026
