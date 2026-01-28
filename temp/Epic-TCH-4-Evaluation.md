# Epic TCH-4: Teacher Evaluation

**Sprint:** Week 4  
**Story Points:** 6 points  
**Priority:** Should Have  
**Status:** 🔵 Not Started  
**Dependencies:** Epic TCH-1 (Foundation)

---

## 📋 Overview

Implementasi sistem evaluasi guru oleh Kepala Sekolah berdasarkan 4 kompetensi standar guru Indonesia (Pedagogik, Kepribadian, Sosial, Profesional).

---

## 🎯 Goals

1. Kepala Sekolah dapat input evaluasi guru
2. Evaluasi berdasarkan 4 kompetensi standar
3. Score 1-5 dengan catatan per kompetensi
4. Guru dapat melihat evaluasi sendiri (read-only)
5. History evaluasi tersimpan per semester

---

## 📖 User Stories

### US-TCH-006: Evaluasi Guru oleh Kepala Sekolah
**Priority:** Should Have | **Points:** 3

**Acceptance Criteria:**
- [ ] Kepala Sekolah dapat mengakses profil guru dan klik "Buat Evaluasi"
- [ ] Form evaluasi menampilkan 4 aspek: Pedagogik, Kepribadian, Sosial, Profesional
- [ ] Setiap aspek memiliki input: Score (1-5), Catatan
- [ ] Sistem menghitung overall score (rata-rata 4 aspek)
- [ ] Kepala Sekolah dapat memilih rekomendasi: Lanjutkan, Perlu Bimbingan, Perlu Pelatihan
- [ ] Evaluasi tersimpan dan dapat dilihat di profil guru

### View Evaluasi oleh Guru
**Priority:** Should Have | **Points:** 2

**Acceptance Criteria:**
- [ ] Guru dapat login dan akses profil sendiri
- [ ] Tab "Evaluasi" menampilkan riwayat evaluasi
- [ ] Guru dapat melihat detail evaluasi (read-only)
- [ ] Nama evaluator tidak ditampilkan ke guru
- [ ] Hanya evaluasi yang sudah di-publish yang terlihat

### History Evaluasi
**Priority:** Should Have | **Points:** 1

**Acceptance Criteria:**
- [ ] Evaluasi tersimpan per semester/periode
- [ ] Evaluasi tidak bisa dihapus (archive only)
- [ ] TU/Admin dapat melihat semua evaluasi untuk HR record

---

## 📝 Technical Tasks

### Task 4.1: Database Migration
**Estimated:** 1 hour

```bash
php artisan make:migration create_teacher_evaluations_table
```

**Schema:**
```php
Schema::create('teacher_evaluations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
    $table->foreignId('evaluator_id')->constrained('users');
    $table->foreignId('academic_year_id')->nullable()->constrained();
    
    // Periode
    $table->string('periode', 50); // e.g., "Semester Ganjil 2025/2026"
    
    // Kompetensi Pedagogik
    $table->tinyInteger('score_pedagogik')->unsigned(); // 1-5
    $table->text('catatan_pedagogik')->nullable();
    
    // Kompetensi Kepribadian
    $table->tinyInteger('score_kepribadian')->unsigned(); // 1-5
    $table->text('catatan_kepribadian')->nullable();
    
    // Kompetensi Sosial
    $table->tinyInteger('score_sosial')->unsigned(); // 1-5
    $table->text('catatan_sosial')->nullable();
    
    // Kompetensi Profesional
    $table->tinyInteger('score_profesional')->unsigned(); // 1-5
    $table->text('catatan_profesional')->nullable();
    
    // Overall
    $table->decimal('score_overall', 3, 2); // e.g., 4.25
    $table->enum('rekomendasi', ['lanjutkan', 'perlu_bimbingan', 'perlu_pelatihan']);
    $table->text('catatan_umum')->nullable();
    
    // Status
    $table->boolean('is_published')->default(false);
    $table->timestamp('published_at')->nullable();
    
    $table->timestamps();
    
    // One evaluation per teacher per period
    $table->unique(['teacher_id', 'periode']);
});
```

**Checklist:**
- [ ] Create migration
- [ ] Run migration
- [ ] Verify table structure

---

### Task 4.2: Model & Relationships
**Estimated:** 1 hour

```bash
php artisan make:model TeacherEvaluation
```

**TeacherEvaluation Model:**
```php
class TeacherEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'evaluator_id', 'academic_year_id', 'periode',
        'score_pedagogik', 'catatan_pedagogik',
        'score_kepribadian', 'catatan_kepribadian',
        'score_sosial', 'catatan_sosial',
        'score_profesional', 'catatan_profesional',
        'score_overall', 'rekomendasi', 'catatan_umum',
        'is_published', 'published_at'
    ];

    protected function casts(): array
    {
        return [
            'score_overall' => 'decimal:2',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($evaluation) {
            // Auto-calculate overall score
            $scores = [
                $evaluation->score_pedagogik,
                $evaluation->score_kepribadian,
                $evaluation->score_sosial,
                $evaluation->score_profesional,
            ];
            $evaluation->score_overall = array_sum($scores) / count($scores);
        });
    }

    // Relationships
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByPeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    // Accessors
    public function getRekomendasiLabelAttribute(): string
    {
        return match($this->rekomendasi) {
            'lanjutkan' => 'Lanjutkan',
            'perlu_bimbingan' => 'Perlu Bimbingan',
            'perlu_pelatihan' => 'Perlu Pelatihan',
            default => '-'
        };
    }

    public function getScoreOverallLabelAttribute(): string
    {
        return match(true) {
            $this->score_overall >= 4.5 => 'Sangat Baik',
            $this->score_overall >= 3.5 => 'Baik',
            $this->score_overall >= 2.5 => 'Cukup',
            $this->score_overall >= 1.5 => 'Kurang',
            default => 'Sangat Kurang'
        };
    }
}
```

**Update Teacher Model:**
```php
// Add to Teacher model
public function evaluations(): HasMany
{
    return $this->hasMany(TeacherEvaluation::class);
}

public function latestEvaluation(): HasOne
{
    return $this->hasOne(TeacherEvaluation::class)->latestOfMany();
}
```

**Checklist:**
- [ ] Create TeacherEvaluation model
- [ ] Auto-calculate overall score
- [ ] Add relationships to Teacher model
- [ ] Test in Tinker

---

### Task 4.3: Policy & Authorization
**Estimated:** 1 hour

```bash
php artisan make:policy TeacherEvaluationPolicy
```

**TeacherEvaluationPolicy:**
```php
class TeacherEvaluationPolicy
{
    /**
     * Only Kepala Sekolah can create evaluations
     */
    public function create(User $user): bool
    {
        return $user->hasRole('kepala_sekolah');
    }

    /**
     * View: Kepala Sekolah, Admin, TU, or the teacher being evaluated
     */
    public function view(User $user, TeacherEvaluation $evaluation): bool
    {
        // Admin, TU, Kepala Sekolah can view all
        if ($user->hasAnyRole(['admin', 'tu', 'kepala_sekolah'])) {
            return true;
        }

        // Teacher can view own evaluation (if published)
        if ($user->hasRole('guru')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            return $teacher && 
                   $evaluation->teacher_id === $teacher->id && 
                   $evaluation->is_published;
        }

        return false;
    }

    /**
     * Only Kepala Sekolah can update (before publish)
     */
    public function update(User $user, TeacherEvaluation $evaluation): bool
    {
        return $user->hasRole('kepala_sekolah') && !$evaluation->is_published;
    }

    /**
     * Only Kepala Sekolah can publish
     */
    public function publish(User $user, TeacherEvaluation $evaluation): bool
    {
        return $user->hasRole('kepala_sekolah') && !$evaluation->is_published;
    }

    /**
     * Evaluations cannot be deleted
     */
    public function delete(User $user, TeacherEvaluation $evaluation): bool
    {
        return false;
    }
}
```

**Register Policy:**
```php
// In AuthServiceProvider or bootstrap
Gate::policy(TeacherEvaluation::class, TeacherEvaluationPolicy::class);
```

**Checklist:**
- [ ] Create TeacherEvaluationPolicy
- [ ] Register policy
- [ ] Test authorization

---

### Task 4.4: Form Request & Validation
**Estimated:** 30 minutes

```bash
php artisan make:request Evaluation/StoreEvaluationRequest
php artisan make:request Evaluation/UpdateEvaluationRequest
```

**StoreEvaluationRequest:**
```php
class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('kepala_sekolah');
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'periode' => ['required', 'string', 'max:50'],
            
            // Scores must be 1-5
            'score_pedagogik' => ['required', 'integer', 'between:1,5'],
            'catatan_pedagogik' => ['nullable', 'string', 'max:1000'],
            
            'score_kepribadian' => ['required', 'integer', 'between:1,5'],
            'catatan_kepribadian' => ['nullable', 'string', 'max:1000'],
            
            'score_sosial' => ['required', 'integer', 'between:1,5'],
            'catatan_sosial' => ['nullable', 'string', 'max:1000'],
            
            'score_profesional' => ['required', 'integer', 'between:1,5'],
            'catatan_profesional' => ['nullable', 'string', 'max:1000'],
            
            'rekomendasi' => ['required', 'in:lanjutkan,perlu_bimbingan,perlu_pelatihan'],
            'catatan_umum' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'score_*.between' => 'Score harus antara 1-5',
            'catatan_*.max' => 'Catatan maksimal 1000 karakter',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if evaluation already exists for this teacher and period
            $exists = TeacherEvaluation::where('teacher_id', $this->teacher_id)
                ->where('periode', $this->periode)
                ->exists();

            if ($exists) {
                $validator->errors()->add('periode', 
                    'Evaluasi untuk guru ini pada periode yang sama sudah ada');
            }
        });
    }
}
```

**Checklist:**
- [ ] Create StoreEvaluationRequest
- [ ] Create UpdateEvaluationRequest
- [ ] Add validation messages
- [ ] Test validation

---

### Task 4.5: Controllers
**Estimated:** 2-3 hours

```bash
php artisan make:controller Admin/TeacherEvaluationController
php artisan make:controller Teacher/EvaluationController
```

**Admin/TeacherEvaluationController:**
```php
class TeacherEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $evaluations = TeacherEvaluation::with(['teacher', 'evaluator'])
            ->when($request->teacher_id, fn($q, $id) => $q->where('teacher_id', $id))
            ->when($request->periode, fn($q, $p) => $q->where('periode', $p))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Teachers/Evaluations/Index', [
            'evaluations' => $evaluations,
            'filters' => $request->only(['teacher_id', 'periode']),
            'teachers' => Teacher::active()->get(['id', 'nama_lengkap']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', TeacherEvaluation::class);

        $teacher = $request->teacher_id 
            ? Teacher::findOrFail($request->teacher_id)
            : null;

        $academicYear = AcademicYear::active()->first();

        return Inertia::render('Admin/Teachers/Evaluations/Create', [
            'teacher' => $teacher,
            'teachers' => Teacher::active()->get(['id', 'nama_lengkap']),
            'academicYear' => $academicYear,
            'periodeOptions' => $this->getPeriodeOptions(),
            'rekomendasiOptions' => [
                ['value' => 'lanjutkan', 'label' => 'Lanjutkan'],
                ['value' => 'perlu_bimbingan', 'label' => 'Perlu Bimbingan'],
                ['value' => 'perlu_pelatihan', 'label' => 'Perlu Pelatihan'],
            ],
        ]);
    }

    public function store(StoreEvaluationRequest $request)
    {
        $evaluation = TeacherEvaluation::create([
            ...$request->validated(),
            'evaluator_id' => auth()->id(),
        ]);

        return redirect()->route('admin.teachers.evaluations.show', $evaluation)
            ->with('success', 'Evaluasi berhasil disimpan');
    }

    public function show(TeacherEvaluation $evaluation)
    {
        $this->authorize('view', $evaluation);

        $evaluation->load(['teacher', 'evaluator', 'academicYear']);

        return Inertia::render('Admin/Teachers/Evaluations/Show', [
            'evaluation' => $evaluation,
        ]);
    }

    public function edit(TeacherEvaluation $evaluation)
    {
        $this->authorize('update', $evaluation);

        return Inertia::render('Admin/Teachers/Evaluations/Edit', [
            'evaluation' => $evaluation->load('teacher'),
            'rekomendasiOptions' => [
                ['value' => 'lanjutkan', 'label' => 'Lanjutkan'],
                ['value' => 'perlu_bimbingan', 'label' => 'Perlu Bimbingan'],
                ['value' => 'perlu_pelatihan', 'label' => 'Perlu Pelatihan'],
            ],
        ]);
    }

    public function update(UpdateEvaluationRequest $request, TeacherEvaluation $evaluation)
    {
        $this->authorize('update', $evaluation);

        $evaluation->update($request->validated());

        return redirect()->route('admin.teachers.evaluations.show', $evaluation)
            ->with('success', 'Evaluasi berhasil diupdate');
    }

    public function publish(TeacherEvaluation $evaluation)
    {
        $this->authorize('publish', $evaluation);

        $evaluation->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Evaluasi berhasil dipublikasikan');
    }

    private function getPeriodeOptions(): array
    {
        $academicYears = AcademicYear::orderByDesc('tahun')->get();
        $options = [];

        foreach ($academicYears as $year) {
            $options[] = [
                'value' => "Semester {$year->semester} {$year->tahun}",
                'label' => "Semester {$year->semester} {$year->tahun}",
            ];
        }

        return $options;
    }
}
```

**Teacher/EvaluationController (for teacher to view own):**
```php
class EvaluationController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $evaluations = TeacherEvaluation::byTeacher($teacher->id)
            ->published()
            ->latest()
            ->get();

        return Inertia::render('Teacher/Evaluations/Index', [
            'evaluations' => $evaluations,
            'teacher' => $teacher,
        ]);
    }

    public function show(TeacherEvaluation $evaluation)
    {
        $this->authorize('view', $evaluation);

        // Hide evaluator info for teacher
        return Inertia::render('Teacher/Evaluations/Show', [
            'evaluation' => $evaluation->makeHidden(['evaluator_id']),
        ]);
    }
}
```

**Checklist:**
- [ ] Create Admin/TeacherEvaluationController
- [ ] Create Teacher/EvaluationController
- [ ] Implement all methods
- [ ] Apply authorization
- [ ] Register routes

---

### Task 4.6: Routes Registration
**Estimated:** 30 minutes

```php
// Admin routes
Route::middleware(['auth', 'role:admin,kepala_sekolah'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('teachers/evaluations', TeacherEvaluationController::class)
        ->except(['destroy']);
    Route::post('teachers/evaluations/{evaluation}/publish', [TeacherEvaluationController::class, 'publish'])
        ->name('teachers.evaluations.publish');
});

// Teacher routes
Route::middleware(['auth', 'role:guru'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('evaluations', [Teacher\EvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('evaluations/{evaluation}', [Teacher\EvaluationController::class, 'show'])->name('evaluations.show');
});
```

**Checklist:**
- [ ] Add admin routes
- [ ] Add teacher routes
- [ ] Generate Wayfinder routes
- [ ] Verify routes

---

### Task 4.7: Vue Pages - Admin
**Estimated:** 4-5 hours

**Pages:**
```
resources/js/pages/Admin/Teachers/Evaluations/
├── Index.vue      // List evaluasi dengan filter
├── Create.vue     // Form input evaluasi
├── Show.vue       // Detail evaluasi
└── Edit.vue       // Edit evaluasi (sebelum publish)
```

**Create.vue Features:**
- Teacher selector (jika tidak dari profil)
- Periode selector
- 4 kompetensi sections dengan:
  - Score input (star rating atau slider 1-5)
  - Catatan textarea
- Auto-calculate overall score
- Rekomendasi dropdown
- Catatan umum textarea
- Save as draft / Publish buttons

**Components:**
```
resources/js/components/Evaluation/
├── EvaluationForm.vue       // Main form
├── CompetencySection.vue    // Section per kompetensi
├── RatingInput.vue          // Star rating 1-5
├── ScoreSummary.vue         // Overall score display
└── RecommendationBadge.vue  // Badge with color
```

**Show.vue Features:**
- Teacher info card
- 4 kompetensi cards dengan score & catatan
- Overall score dengan visual
- Rekomendasi badge
- Publish button (jika belum publish)

**Checklist:**
- [ ] Create Index.vue
- [ ] Create Create.vue
- [ ] Create Show.vue
- [ ] Create Edit.vue
- [ ] Create reusable components
- [ ] Star rating component
- [ ] Mobile responsive

---

### Task 4.8: Vue Pages - Teacher View
**Estimated:** 2 hours

**Pages:**
```
resources/js/pages/Teacher/Evaluations/
├── Index.vue      // List evaluasi sendiri
└── Show.vue       // Detail (read-only, no evaluator)
```

**Features:**
- List evaluasi dengan periode dan score
- Detail view (read-only)
- No evaluator name visible
- Mobile friendly

**Checklist:**
- [ ] Create Teacher/Evaluations/Index.vue
- [ ] Create Teacher/Evaluations/Show.vue
- [ ] Hide evaluator information
- [ ] Mobile responsive

---

### Task 4.9: Integration with Teacher Profile
**Estimated:** 1 hour

**Update Show.vue (Teacher Profile):**
- Add "Evaluasi" tab
- Show evaluation history
- Link to create new evaluation (for Kepala Sekolah)
- Show latest evaluation summary

**Checklist:**
- [ ] Add Evaluasi tab to teacher profile
- [ ] Show evaluation history
- [ ] Quick action: Buat Evaluasi
- [ ] Latest evaluation summary card

---

## ✅ Definition of Done

- [ ] Kepala Sekolah dapat membuat evaluasi
- [ ] 4 kompetensi dengan score 1-5 berfungsi
- [ ] Overall score auto-calculated
- [ ] Rekomendasi dapat dipilih
- [ ] Draft & Publish workflow berfungsi
- [ ] Guru dapat melihat evaluasi sendiri (published only)
- [ ] Evaluator name hidden from guru
- [ ] History evaluasi tersimpan
- [ ] Authorization berfungsi dengan benar
- [ ] Mobile responsive
- [ ] No linter errors

---

## 🧪 Test Cases

### Feature Tests
```php
public function test_kepala_sekolah_can_create_evaluation()
public function test_evaluation_calculates_overall_score()
public function test_kepala_sekolah_can_publish_evaluation()
public function test_teacher_can_view_own_published_evaluation()
public function test_teacher_cannot_view_unpublished_evaluation()
public function test_teacher_cannot_see_evaluator_name()
public function test_non_kepala_sekolah_cannot_create_evaluation()
public function test_evaluation_cannot_be_deleted()
public function test_duplicate_evaluation_for_same_period_rejected()
```

---

## 📝 4 Kompetensi Guru Indonesia

### 1. Pedagogik
Kemampuan mengelola pembelajaran peserta didik yang meliputi:
- Pemahaman terhadap peserta didik
- Perancangan dan pelaksanaan pembelajaran
- Evaluasi hasil belajar
- Pengembangan peserta didik

### 2. Kepribadian
Kemampuan kepribadian yang mantap, stabil, dewasa, arif, dan berwibawa:
- Bertindak sesuai norma
- Menampilkan kemandirian
- Etos kerja yang tinggi
- Teladan bagi peserta didik

### 3. Sosial
Kemampuan berkomunikasi dan berinteraksi secara efektif:
- Berkomunikasi dengan peserta didik
- Berkomunikasi dengan orang tua
- Berkomunikasi dengan sesama pendidik
- Bergaul secara santun

### 4. Profesional
Penguasaan materi pembelajaran secara luas dan mendalam:
- Menguasai materi pelajaran
- Menguasai struktur dan metode keilmuan
- Mengembangkan keprofesionalan berkelanjutan
- Memanfaatkan teknologi

---

## 📊 Score Interpretation

| Score | Label | Color |
|-------|-------|-------|
| 4.5 - 5.0 | Sangat Baik | Green |
| 3.5 - 4.4 | Baik | Blue |
| 2.5 - 3.4 | Cukup | Yellow |
| 1.5 - 2.4 | Kurang | Orange |
| 1.0 - 1.4 | Sangat Kurang | Red |

---

**Epic Status:** 🔵 Not Started  
**Last Updated:** 29 Januari 2026
