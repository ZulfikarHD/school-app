# Epic TCH-2: Teaching Schedule Management

**Sprint:** Week 2  
**Story Points:** 8 points  
**Priority:** Should Have  
**Status:** 🔵 Not Started  
**Dependencies:** Epic TCH-1 (Foundation)

---

## 📋 Overview

Implementasi fitur jadwal mengajar guru dengan calendar/matrix view dan conflict detection. Termasuk fitur copy jadwal dari semester sebelumnya.

---

## 🎯 Goals

1. Input jadwal mengajar guru per kelas
2. View jadwal dalam matrix (hari x jam)
3. View per guru atau per kelas
4. Deteksi dan cegah konflik jadwal
5. Copy jadwal dari semester sebelumnya

---

## 📖 User Stories

### US-TCH-003: Input Jadwal Mengajar
**Priority:** Should Have | **Points:** 3

**Acceptance Criteria:**
- [ ] TU dapat mengakses halaman "Jadwal Mengajar"
- [ ] TU dapat klik "Tambah Jadwal" dan melihat form: Hari, Jam Mulai, Jam Selesai, Kelas, Mata Pelajaran, Guru
- [ ] Saat simpan, jadwal tampil di calendar/matrix view
- [ ] Jika ada konflik (guru/kelas sudah ada jadwal), sistem tampilkan warning
- [ ] TU dapat edit/hapus jadwal existing

### FR-TCH-002: Schedule View per Guru/Kelas
**Priority:** Should Have | **Points:** 3

**Acceptance Criteria:**
- [ ] View per Guru: Matrix (Hari vs Jam) dengan kelas & mapel di setiap cell
- [ ] View per Kelas: Matrix (Hari vs Jam) dengan guru & mapel di setiap cell
- [ ] Color coding untuk membedakan guru/mapel
- [ ] Filter by academic year dan semester
- [ ] Export jadwal ke PDF

### Copy Schedule Feature
**Priority:** Should Have | **Points:** 2

**Acceptance Criteria:**
- [ ] TU dapat memilih semester sumber dan tujuan
- [ ] Sistem copy semua jadwal ke semester baru
- [ ] Preview sebelum konfirmasi copy
- [ ] Jadwal yang sudah ada tidak tertimpa (append)

---

## 📝 Technical Tasks

### Task 2.1: Database Migrations
**Estimated:** 1-2 hours

```bash
php artisan make:migration create_academic_years_table
php artisan make:migration create_teaching_schedules_table
```

**Schema - academic_years:**
```php
Schema::create('academic_years', function (Blueprint $table) {
    $table->id();
    $table->string('tahun', 20); // e.g., "2025/2026"
    $table->enum('semester', ['ganjil', 'genap']);
    $table->date('tanggal_mulai');
    $table->date('tanggal_selesai');
    $table->boolean('is_active')->default(false);
    $table->timestamps();
    
    $table->unique(['tahun', 'semester']);
});
```

**Schema - teaching_schedules:**
```php
Schema::create('teaching_schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    
    $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
    $table->tinyInteger('jam_pelajaran'); // 1-8
    $table->time('jam_mulai');
    $table->time('jam_selesai');
    
    $table->text('catatan')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    // Unique constraint: 1 guru tidak bisa di 2 kelas pada jam yang sama
    $table->unique(
        ['academic_year_id', 'teacher_id', 'hari', 'jam_pelajaran'],
        'unique_teacher_schedule'
    );
    
    // Unique constraint: 1 kelas tidak bisa ada 2 jadwal pada jam yang sama
    $table->unique(
        ['academic_year_id', 'class_id', 'hari', 'jam_pelajaran'],
        'unique_class_schedule'
    );
});
```

**Checklist:**
- [ ] Create academic_years migration
- [ ] Create teaching_schedules migration
- [ ] Run migrations
- [ ] Verify constraints

---

### Task 2.2: Models & Relationships
**Estimated:** 1-2 hours

```bash
php artisan make:model AcademicYear
php artisan make:model TeachingSchedule
```

**AcademicYear Model:**
```php
class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun', 'semester', 'tanggal_mulai', 'tanggal_selesai', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFullNameAttribute(): string
    {
        $semesterLabel = $this->semester === 'ganjil' ? 'Ganjil' : 'Genap';
        return "Semester {$semesterLabel} {$this->tahun}";
    }
}
```

**TeachingSchedule Model:**
```php
class TeachingSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id', 'teacher_id', 'class_id', 'subject_id',
        'hari', 'jam_pelajaran', 'jam_mulai', 'jam_selesai', 'catatan'
    ];

    protected function casts(): array
    {
        return [
            'jam_mulai' => 'datetime:H:i',
            'jam_selesai' => 'datetime:H:i',
        ];
    }

    // Relationships
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    // Scopes
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeByDay($query, $hari)
    {
        return $query->where('hari', $hari);
    }
}
```

**Checklist:**
- [ ] Create AcademicYear model
- [ ] Create TeachingSchedule model
- [ ] Define relationships
- [ ] Test relationships di Tinker

---

### Task 2.3: Schedule Service
**Estimated:** 2-3 hours

```bash
php artisan make:class Services/TeachingScheduleService
```

**TeachingScheduleService:**
```php
class TeachingScheduleService
{
    /**
     * Cek apakah ada konflik jadwal guru
     * (guru sudah ada jadwal di jam yang sama)
     */
    public function hasTeacherConflict(
        int $teacherId,
        int $academicYearId,
        string $hari,
        int $jamPelajaran,
        ?int $excludeScheduleId = null
    ): bool {
        return TeachingSchedule::query()
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('hari', $hari)
            ->where('jam_pelajaran', $jamPelajaran)
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->exists();
    }

    /**
     * Cek apakah ada konflik jadwal kelas
     * (kelas sudah ada jadwal di jam yang sama)
     */
    public function hasClassConflict(
        int $classId,
        int $academicYearId,
        string $hari,
        int $jamPelajaran,
        ?int $excludeScheduleId = null
    ): bool {
        return TeachingSchedule::query()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('hari', $hari)
            ->where('jam_pelajaran', $jamPelajaran)
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->exists();
    }

    /**
     * Get jadwal dalam format matrix untuk view
     */
    public function getScheduleMatrix(
        int $academicYearId,
        ?int $teacherId = null,
        ?int $classId = null
    ): array {
        $query = TeachingSchedule::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->where('academic_year_id', $academicYearId)
            ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->get();

        // Transform ke matrix format
        $matrix = [];
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $hours = range(1, 8);

        foreach ($days as $day) {
            $matrix[$day] = [];
            foreach ($hours as $hour) {
                $schedule = $query->first(fn($s) => 
                    $s->hari === $day && $s->jam_pelajaran === $hour
                );
                $matrix[$day][$hour] = $schedule;
            }
        }

        return $matrix;
    }

    /**
     * Hitung total jam mengajar guru per minggu
     */
    public function getWeeklyHours(int $teacherId, int $academicYearId): int
    {
        return TeachingSchedule::query()
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->count();
    }

    /**
     * Copy jadwal dari semester sebelumnya
     */
    public function copyFromPreviousSemester(
        int $fromAcademicYearId,
        int $toAcademicYearId
    ): int {
        $schedules = TeachingSchedule::where('academic_year_id', $fromAcademicYearId)->get();
        $copied = 0;

        foreach ($schedules as $schedule) {
            // Skip jika sudah ada
            $exists = TeachingSchedule::where('academic_year_id', $toAcademicYearId)
                ->where('teacher_id', $schedule->teacher_id)
                ->where('class_id', $schedule->class_id)
                ->where('hari', $schedule->hari)
                ->where('jam_pelajaran', $schedule->jam_pelajaran)
                ->exists();

            if (!$exists) {
                TeachingSchedule::create([
                    'academic_year_id' => $toAcademicYearId,
                    'teacher_id' => $schedule->teacher_id,
                    'class_id' => $schedule->class_id,
                    'subject_id' => $schedule->subject_id,
                    'hari' => $schedule->hari,
                    'jam_pelajaran' => $schedule->jam_pelajaran,
                    'jam_mulai' => $schedule->jam_mulai,
                    'jam_selesai' => $schedule->jam_selesai,
                ]);
                $copied++;
            }
        }

        return $copied;
    }
}
```

**Checklist:**
- [ ] Create TeachingScheduleService
- [ ] Implement conflict detection
- [ ] Implement matrix generation
- [ ] Implement copy schedule
- [ ] Write unit tests

---

### Task 2.4: Form Request & Validation
**Estimated:** 1 hour

```bash
php artisan make:request Schedule/StoreScheduleRequest
php artisan make:request Schedule/UpdateScheduleRequest
```

**StoreScheduleRequest:**
```php
class StoreScheduleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'jam_pelajaran' => ['required', 'integer', 'between:1,8'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $service = app(TeachingScheduleService::class);
            
            // Check teacher conflict
            if ($service->hasTeacherConflict(
                $this->teacher_id,
                $this->academic_year_id,
                $this->hari,
                $this->jam_pelajaran
            )) {
                $validator->errors()->add('teacher_id', 
                    'Guru sudah memiliki jadwal di hari dan jam yang sama');
            }

            // Check class conflict
            if ($service->hasClassConflict(
                $this->class_id,
                $this->academic_year_id,
                $this->hari,
                $this->jam_pelajaran
            )) {
                $validator->errors()->add('class_id', 
                    'Kelas sudah memiliki jadwal di hari dan jam yang sama');
            }

            // Check teacher teaches this subject
            $teacher = Teacher::find($this->teacher_id);
            if ($teacher && !$teacher->subjects->contains($this->subject_id)) {
                $validator->errors()->add('subject_id',
                    'Guru tidak mengampu mata pelajaran ini');
            }
        });
    }
}
```

**Checklist:**
- [ ] Create StoreScheduleRequest
- [ ] Create UpdateScheduleRequest
- [ ] Implement conflict validation
- [ ] Implement subject validation
- [ ] Test validation rules

---

### Task 2.5: Controller Implementation
**Estimated:** 3-4 hours

```bash
php artisan make:controller Admin/TeachingScheduleController
```

**Controller:**
```php
class TeachingScheduleController extends Controller
{
    public function __construct(
        private TeachingScheduleService $scheduleService
    ) {}

    public function index(Request $request)
    {
        $academicYear = $request->academic_year_id
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::active()->first();

        $schedules = TeachingSchedule::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->get();

        return Inertia::render('Admin/Teachers/Schedules/Index', [
            'schedules' => $schedules,
            'academicYears' => AcademicYear::orderByDesc('tahun')->get(),
            'currentAcademicYear' => $academicYear,
        ]);
    }

    public function byTeacher(Request $request, Teacher $teacher)
    {
        $academicYear = $request->academic_year_id
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::active()->first();

        $matrix = $this->scheduleService->getScheduleMatrix(
            $academicYear?->id ?? 0,
            $teacher->id
        );

        $weeklyHours = $this->scheduleService->getWeeklyHours(
            $teacher->id,
            $academicYear?->id ?? 0
        );

        return Inertia::render('Admin/Teachers/Schedules/ByTeacher', [
            'teacher' => $teacher->load('subjects'),
            'matrix' => $matrix,
            'weeklyHours' => $weeklyHours,
            'academicYears' => AcademicYear::orderByDesc('tahun')->get(),
            'currentAcademicYear' => $academicYear,
        ]);
    }

    public function byClass(Request $request, SchoolClass $class)
    {
        $academicYear = $request->academic_year_id
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::active()->first();

        $matrix = $this->scheduleService->getScheduleMatrix(
            $academicYear?->id ?? 0,
            null,
            $class->id
        );

        return Inertia::render('Admin/Teachers/Schedules/ByClass', [
            'class' => $class,
            'matrix' => $matrix,
            'academicYears' => AcademicYear::orderByDesc('tahun')->get(),
            'currentAcademicYear' => $academicYear,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Teachers/Schedules/Create', [
            'academicYears' => AcademicYear::orderByDesc('tahun')->get(),
            'teachers' => Teacher::active()->with('subjects')->get(),
            'classes' => SchoolClass::where('is_active', true)->get(),
            'subjects' => Subject::where('is_active', true)->get(),
            'days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
            'hours' => collect(range(1, 8))->map(fn($h) => [
                'value' => $h,
                'label' => "Jam ke-{$h}",
            ]),
        ]);
    }

    public function store(StoreScheduleRequest $request)
    {
        TeachingSchedule::create($request->validated());

        return redirect()->route('admin.teachers.schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function edit(TeachingSchedule $schedule)
    {
        return Inertia::render('Admin/Teachers/Schedules/Edit', [
            'schedule' => $schedule->load(['teacher', 'schoolClass', 'subject']),
            'academicYears' => AcademicYear::orderByDesc('tahun')->get(),
            'teachers' => Teacher::active()->with('subjects')->get(),
            'classes' => SchoolClass::where('is_active', true)->get(),
            'subjects' => Subject::where('is_active', true)->get(),
            'days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
            'hours' => collect(range(1, 8))->map(fn($h) => [
                'value' => $h,
                'label' => "Jam ke-{$h}",
            ]),
        ]);
    }

    public function update(UpdateScheduleRequest $request, TeachingSchedule $schedule)
    {
        $schedule->update($request->validated());

        return redirect()->route('admin.teachers.schedules.index')
            ->with('success', 'Jadwal berhasil diupdate');
    }

    public function destroy(TeachingSchedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('admin.teachers.schedules.index')
            ->with('success', 'Jadwal berhasil dihapus');
    }

    public function copyFromPrevious(Request $request)
    {
        $request->validate([
            'from_academic_year_id' => ['required', 'exists:academic_years,id'],
            'to_academic_year_id' => ['required', 'exists:academic_years,id', 'different:from_academic_year_id'],
        ]);

        $copied = $this->scheduleService->copyFromPreviousSemester(
            $request->from_academic_year_id,
            $request->to_academic_year_id
        );

        return redirect()->route('admin.teachers.schedules.index', [
            'academic_year_id' => $request->to_academic_year_id
        ])->with('success', "{$copied} jadwal berhasil disalin");
    }
}
```

**Checklist:**
- [ ] Create TeachingScheduleController
- [ ] Implement index
- [ ] Implement byTeacher
- [ ] Implement byClass
- [ ] Implement create & store
- [ ] Implement edit & update
- [ ] Implement destroy
- [ ] Implement copyFromPrevious
- [ ] Register routes

---

### Task 2.6: Routes Registration
**Estimated:** 30 minutes

```php
Route::middleware(['auth', 'role:admin,tu'])->prefix('admin')->name('admin.')->group(function () {
    // Teaching Schedules
    Route::prefix('teachers/schedules')->name('teachers.schedules.')->group(function () {
        Route::get('/', [TeachingScheduleController::class, 'index'])->name('index');
        Route::get('/create', [TeachingScheduleController::class, 'create'])->name('create');
        Route::post('/', [TeachingScheduleController::class, 'store'])->name('store');
        Route::get('/by-teacher/{teacher}', [TeachingScheduleController::class, 'byTeacher'])->name('by-teacher');
        Route::get('/by-class/{class}', [TeachingScheduleController::class, 'byClass'])->name('by-class');
        Route::get('/{schedule}/edit', [TeachingScheduleController::class, 'edit'])->name('edit');
        Route::put('/{schedule}', [TeachingScheduleController::class, 'update'])->name('update');
        Route::delete('/{schedule}', [TeachingScheduleController::class, 'destroy'])->name('destroy');
        Route::post('/copy', [TeachingScheduleController::class, 'copyFromPrevious'])->name('copy');
    });
});
```

**Checklist:**
- [ ] Add routes to web.php
- [ ] Generate Wayfinder routes
- [ ] Verify routes

---

### Task 2.7: Vue Pages - Index & List
**Estimated:** 2-3 hours

**File:** `resources/js/pages/Admin/Teachers/Schedules/Index.vue`

**Features:**
- List semua jadwal dengan filter academic year
- Table: Hari, Jam, Kelas, Mapel, Guru
- Quick links: View by Teacher, View by Class
- Actions: Edit, Delete

**Checklist:**
- [ ] Create Index.vue
- [ ] Academic year filter
- [ ] Sortable table
- [ ] Row actions
- [ ] Mobile responsive

---

### Task 2.8: Vue Pages - Matrix View
**Estimated:** 4-5 hours

**File:** `resources/js/pages/Admin/Teachers/Schedules/ByTeacher.vue`
**File:** `resources/js/pages/Admin/Teachers/Schedules/ByClass.vue`

**Features:**
- Matrix grid (Hari x Jam)
- Color-coded cells
- Hover untuk detail
- Click cell untuk edit/tambah
- Weekly hours summary (for teacher view)

**Components:**
```
resources/js/components/Schedule/
├── ScheduleMatrix.vue     // Grid component
├── ScheduleCell.vue       // Individual cell
└── ScheduleLegend.vue     // Color legend
```

**Checklist:**
- [ ] Create ByTeacher.vue
- [ ] Create ByClass.vue
- [ ] Create ScheduleMatrix component
- [ ] Color coding implementation
- [ ] Interactive cells
- [ ] Mobile scroll horizontal
- [ ] Export to PDF button

---

### Task 2.9: Vue Pages - Create/Edit Form
**Estimated:** 2-3 hours

**File:** `resources/js/pages/Admin/Teachers/Schedules/Create.vue`

**Features:**
- Dropdown: Academic Year, Hari, Jam, Kelas, Mapel, Guru
- Dynamic subject options based on selected teacher
- Real-time conflict check
- Time picker for jam_mulai/jam_selesai

**Checklist:**
- [ ] Create Create.vue
- [ ] Dynamic dropdowns
- [ ] Real-time conflict validation
- [ ] Time picker component
- [ ] Form validation
- [ ] Mobile responsive

---

### Task 2.10: Copy Schedule Feature
**Estimated:** 1-2 hours

**File:** Modal component atau separate page

**Features:**
- Select source semester
- Select target semester
- Preview count of schedules to copy
- Confirm button
- Progress feedback

**Checklist:**
- [ ] Copy schedule modal/form
- [ ] Preview before copy
- [ ] Success feedback
- [ ] Error handling

---

## ✅ Definition of Done

- [ ] Jadwal dapat diinput dengan validasi konflik
- [ ] Matrix view berfungsi untuk teacher dan class
- [ ] Deteksi konflik guru dan kelas berfungsi
- [ ] Copy jadwal dari semester sebelumnya berfungsi
- [ ] Color coding konsisten
- [ ] Export PDF berfungsi
- [ ] Mobile responsive (horizontal scroll untuk matrix)
- [ ] No linter errors
- [ ] Unit tests pass

---

## 🧪 Test Cases

### Feature Tests
```php
public function test_admin_can_view_schedule_list()
public function test_admin_can_create_schedule()
public function test_schedule_creation_validates_teacher_conflict()
public function test_schedule_creation_validates_class_conflict()
public function test_schedule_creation_validates_teacher_subject()
public function test_admin_can_view_schedule_by_teacher()
public function test_admin_can_view_schedule_by_class()
public function test_admin_can_copy_schedule_to_new_semester()
public function test_copy_schedule_skips_existing()
```

### Unit Tests
```php
public function test_has_teacher_conflict_returns_true_when_conflict()
public function test_has_class_conflict_returns_true_when_conflict()
public function test_get_schedule_matrix_returns_correct_format()
public function test_get_weekly_hours_calculates_correctly()
```

---

## 📝 Notes

### Jam Pelajaran Default
```
Jam 1: 07:00 - 07:35
Jam 2: 07:35 - 08:10
Jam 3: 08:10 - 08:45
Istirahat: 08:45 - 09:00
Jam 4: 09:00 - 09:35
Jam 5: 09:35 - 10:10
Jam 6: 10:10 - 10:45
Istirahat: 10:45 - 11:00
Jam 7: 11:00 - 11:35
Jam 8: 11:35 - 12:10
```

### Color Coding Strategy
- Generate color dari hash teacher_id untuk konsistensi
- Atau predefined palette dengan cycle

### Mobile Considerations
- Matrix perlu horizontal scroll
- Alternative list view untuk mobile

---

**Epic Status:** 🔵 Not Started  
**Last Updated:** 29 Januari 2026
