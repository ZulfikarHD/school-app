# Epic TCH-1: Foundation & Teacher CRUD

**Sprint:** Week 1  
**Story Points:** 8 points  
**Priority:** Must Have  
**Status:** 🔵 Not Started

---

## 📋 Overview

Setup database schema, models, dan CRUD dasar untuk data guru. Epic ini merupakan fondasi untuk semua fitur Teacher Management.

---

## 🎯 Goals

1. Database schema untuk teacher management
2. CRUD lengkap untuk data guru
3. Auto-create user account untuk guru baru
4. Profile view dengan tabs
5. Soft delete (nonaktifkan) guru

---

## 📖 User Stories

### US-TCH-001: Tambah & Edit Data Guru
**Priority:** Must Have | **Points:** 3

**Acceptance Criteria:**
- [ ] TU dapat mengakses halaman "Data Guru" dari menu admin
- [ ] TU dapat klik "Tambah Guru Baru" dan melihat form lengkap
- [ ] Form fields: NIP/NIK, Nama, Gelar, TTL, Jenis Kelamin, Alamat, No HP, Email, Status (Tetap/Honorer), Mata Pelajaran
- [ ] TU dapat upload foto guru (max 2MB, jpeg/png)
- [ ] Saat simpan, sistem auto-create akun login (username: NIP, password: auto-generated)
- [ ] TU dapat edit data guru existing
- [ ] Notifikasi sukses setelah simpan/update

### US-TCH-002: Lihat Profil Guru
**Priority:** Must Have | **Points:** 3

**Acceptance Criteria:**
- [ ] Klik nama guru menampilkan halaman profil
- [ ] Profil memiliki tabs: Info Pribadi, Jadwal Mengajar, Rekap Presensi, Rekap Honor, Evaluasi
- [ ] Tab Info Pribadi: foto, NIP, nama, TTL, alamat, no HP, email, mata pelajaran, status
- [ ] Mobile-friendly layout
- [ ] Quick actions: Edit Data, Nonaktifkan

### US-TCH-008: Nonaktifkan Guru
**Priority:** Must Have | **Points:** 2

**Acceptance Criteria:**
- [ ] TU dapat klik "Nonaktifkan Guru" di profil
- [ ] Konfirmasi dialog sebelum nonaktifkan
- [ ] Status berubah "Tidak Aktif", akun login di-disable
- [ ] Guru tidak muncul di list aktif (default filter)
- [ ] TU dapat filter "Tidak Aktif" untuk melihat guru nonaktif
- [ ] Data historis tetap tersimpan

---

## 📝 Technical Tasks

### Task 1.1: Database Migrations
**Estimated:** 2-3 hours

```bash
php artisan make:migration create_teachers_table
php artisan make:migration create_subjects_table
php artisan make:migration create_teacher_subject_table
```

**Schema - teachers:**
```php
Schema::create('teachers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    
    // Identitas
    $table->string('nip', 30)->unique()->nullable();
    $table->string('nik', 20)->unique();
    $table->string('nama_lengkap', 100);
    $table->string('gelar', 50)->nullable(); // S.Pd, M.Pd, dll
    
    // Data Pribadi
    $table->enum('jenis_kelamin', ['L', 'P']);
    $table->string('tempat_lahir', 50);
    $table->date('tanggal_lahir');
    $table->text('alamat');
    $table->string('no_hp', 20);
    $table->string('email', 100)->nullable();
    $table->string('foto')->nullable();
    
    // Status Kepegawaian
    $table->enum('status', ['tetap', 'honorer'])->default('honorer');
    $table->string('pendidikan_terakhir', 50)->nullable();
    $table->date('tanggal_bergabung');
    
    // Gaji/Honor
    $table->decimal('gaji_tetap', 12, 0)->nullable(); // Untuk guru tetap
    $table->decimal('honor_per_jam', 10, 0)->nullable(); // Untuk guru honorer
    
    // Status Aktif
    $table->boolean('is_active')->default(true);
    
    $table->timestamps();
    $table->softDeletes();
});
```

**Schema - subjects:**
```php
Schema::create('subjects', function (Blueprint $table) {
    $table->id();
    $table->string('kode', 10)->unique();
    $table->string('nama', 100);
    $table->text('deskripsi')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**Schema - teacher_subject (pivot):**
```php
Schema::create('teacher_subject', function (Blueprint $table) {
    $table->id();
    $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    
    $table->unique(['teacher_id', 'subject_id']);
});
```

**Checklist:**
- [ ] Create teachers migration
- [ ] Create subjects migration
- [ ] Create pivot table migration
- [ ] Run migrations
- [ ] Verify tables created

---

### Task 1.2: Models & Relationships
**Estimated:** 1-2 hours

```bash
php artisan make:model Teacher
php artisan make:model Subject
```

**Teacher Model:**
```php
class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'nip', 'nik', 'nama_lengkap', 'gelar',
        'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'alamat', 'no_hp', 'email', 'foto',
        'status', 'pendidikan_terakhir', 'tanggal_bergabung',
        'gaji_tetap', 'honor_per_jam', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_bergabung' => 'date',
            'gaji_tetap' => 'decimal:0',
            'honor_per_jam' => 'decimal:0',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TeacherEvaluation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessors
    public function getNamaLengkapGelarAttribute(): string
    {
        return $this->gelar 
            ? "{$this->nama_lengkap}, {$this->gelar}"
            : $this->nama_lengkap;
    }
}
```

**Subject Model:**
```php
class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'deskripsi', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class)->withTimestamps();
    }
}
```

**Checklist:**
- [ ] Create Teacher model
- [ ] Create Subject model
- [ ] Define relationships
- [ ] Add scopes dan accessors
- [ ] Test relationships di Tinker

---

### Task 1.3: Factories & Seeders
**Estimated:** 1-2 hours

```bash
php artisan make:factory TeacherFactory
php artisan make:seeder SubjectSeeder
php artisan make:seeder TeacherSeeder
```

**SubjectSeeder:**
```php
// Mata pelajaran standar SD
$subjects = [
    ['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam'],
    ['kode' => 'PKN', 'nama' => 'Pendidikan Kewarganegaraan'],
    ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia'],
    ['kode' => 'MTK', 'nama' => 'Matematika'],
    ['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam'],
    ['kode' => 'IPS', 'nama' => 'Ilmu Pengetahuan Sosial'],
    ['kode' => 'SBK', 'nama' => 'Seni Budaya dan Keterampilan'],
    ['kode' => 'PJK', 'nama' => 'Pendidikan Jasmani dan Kesehatan'],
    ['kode' => 'BIG', 'nama' => 'Bahasa Inggris'],
    ['kode' => 'TIK', 'nama' => 'Teknologi Informasi dan Komunikasi'],
    ['kode' => 'MUL', 'nama' => 'Muatan Lokal'],
];
```

**Checklist:**
- [ ] Create TeacherFactory
- [ ] Create SubjectSeeder dengan mata pelajaran SD
- [ ] Create TeacherSeeder dengan sample data
- [ ] Run seeders
- [ ] Verify data created

---

### Task 1.4: Form Requests & Validation
**Estimated:** 1 hour

```bash
php artisan make:request Teacher/StoreTeacherRequest
php artisan make:request Teacher/UpdateTeacherRequest
```

**Validation Rules:**
```php
// StoreTeacherRequest
public function rules(): array
{
    return [
        'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip'],
        'nik' => ['required', 'string', 'size:16', 'unique:teachers,nik'],
        'nama_lengkap' => ['required', 'string', 'min:3', 'max:100'],
        'gelar' => ['nullable', 'string', 'max:50'],
        'jenis_kelamin' => ['required', 'in:L,P'],
        'tempat_lahir' => ['required', 'string', 'max:50'],
        'tanggal_lahir' => ['required', 'date', 'before:today'],
        'alamat' => ['required', 'string'],
        'no_hp' => ['required', 'string', 'max:20'],
        'email' => ['nullable', 'email', 'max:100'],
        'foto' => ['nullable', 'image', 'mimes:jpeg,png', 'max:2048'],
        'status' => ['required', 'in:tetap,honorer'],
        'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
        'tanggal_bergabung' => ['required', 'date'],
        'gaji_tetap' => ['nullable', 'required_if:status,tetap', 'numeric', 'min:0'],
        'honor_per_jam' => ['nullable', 'required_if:status,honorer', 'numeric', 'min:10000'],
        'subject_ids' => ['required', 'array', 'min:1'],
        'subject_ids.*' => ['exists:subjects,id'],
    ];
}
```

**Checklist:**
- [ ] Create StoreTeacherRequest
- [ ] Create UpdateTeacherRequest
- [ ] Add validation messages in Indonesian
- [ ] Test validation rules

---

### Task 1.5: Controller Implementation
**Estimated:** 3-4 hours

```bash
php artisan make:controller Admin/TeacherController --resource
```

**Controller Methods:**

```php
class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::query()
            ->with(['subjects', 'user'])
            ->when($request->search, fn($q, $s) => $q->where('nama_lengkap', 'like', "%{$s}%")
                ->orWhere('nip', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->is_active === null, fn($q) => $q->where('is_active', true))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'filters' => $request->only(['search', 'status', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Teachers/Create', [
            'subjects' => Subject::where('is_active', true)->get(),
            'statusOptions' => ['tetap', 'honorer'],
        ]);
    }

    public function store(StoreTeacherRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Create user account
            $password = Str::random(10);
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email ?? $request->nip . '@sekolah.local',
                'password' => Hash::make($password),
                'role' => 'guru',
            ]);

            // Create teacher
            $teacher = Teacher::create([
                ...$request->validated(),
                'user_id' => $user->id,
            ]);

            // Attach subjects
            $teacher->subjects()->attach($request->subject_ids);

            // TODO: Send email with credentials
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil disimpan');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['subjects', 'user', 'schedules', 'evaluations']);

        return Inertia::render('Admin/Teachers/Show', [
            'teacher' => $teacher,
        ]);
    }

    public function edit(Teacher $teacher)
    {
        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher->load('subjects'),
            'subjects' => Subject::where('is_active', true)->get(),
            'statusOptions' => ['tetap', 'honorer'],
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        DB::transaction(function () use ($request, $teacher) {
            $teacher->update($request->validated());
            $teacher->subjects()->sync($request->subject_ids);
        });

        return redirect()->route('admin.teachers.show', $teacher)
            ->with('success', 'Data guru berhasil diupdate');
    }

    public function destroy(Teacher $teacher)
    {
        DB::transaction(function () use ($teacher) {
            // Disable user account
            if ($teacher->user) {
                $teacher->user->update(['is_active' => false]);
            }
            
            // Soft delete teacher
            $teacher->update(['is_active' => false]);
            $teacher->delete();
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Guru berhasil dinonaktifkan');
    }

    public function restore(Teacher $teacher)
    {
        DB::transaction(function () use ($teacher) {
            $teacher->restore();
            $teacher->update(['is_active' => true]);
            
            if ($teacher->user) {
                $teacher->user->update(['is_active' => true]);
            }
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Guru berhasil diaktifkan kembali');
    }
}
```

**Checklist:**
- [ ] Create TeacherController
- [ ] Implement index with filters & pagination
- [ ] Implement create & store
- [ ] Implement show (profile)
- [ ] Implement edit & update
- [ ] Implement destroy (soft delete)
- [ ] Implement restore
- [ ] Register routes
- [ ] Test all endpoints

---

### Task 1.6: Routes Registration
**Estimated:** 30 minutes

**web.php:**
```php
Route::middleware(['auth', 'role:admin,tu'])->prefix('admin')->name('admin.')->group(function () {
    // Teachers
    Route::resource('teachers', TeacherController::class);
    Route::post('teachers/{teacher}/restore', [TeacherController::class, 'restore'])
        ->name('teachers.restore')
        ->withTrashed();
});
```

**Checklist:**
- [ ] Add routes to web.php
- [ ] Generate Wayfinder routes
- [ ] Verify routes with `php artisan route:list`

---

### Task 1.7: Vue Pages - Index
**Estimated:** 3-4 hours

**File:** `resources/js/pages/Admin/Teachers/Index.vue`

**Features:**
- Table dengan kolom: Foto, NIP, Nama, Status, Mata Pelajaran, Actions
- Search by nama/NIP
- Filter by status (tetap/honorer)
- Filter by aktif/nonaktif
- Pagination
- Row actions: View, Edit, Nonaktifkan

**Checklist:**
- [ ] Create Index.vue
- [ ] Implement search functionality
- [ ] Implement filters
- [ ] Implement pagination
- [ ] Implement row actions
- [ ] Mobile responsive

---

### Task 1.8: Vue Pages - Create/Edit
**Estimated:** 3-4 hours

**File:** `resources/js/pages/Admin/Teachers/Create.vue`
**File:** `resources/js/pages/Admin/Teachers/Edit.vue`

**Form Sections:**
1. **Identitas:** NIP, NIK, Nama, Gelar
2. **Data Pribadi:** Jenis Kelamin, Tempat/Tanggal Lahir, Alamat
3. **Kontak:** No HP, Email
4. **Kepegawaian:** Status, Pendidikan, Tanggal Bergabung
5. **Gaji:** Gaji Tetap / Honor per Jam
6. **Mata Pelajaran:** Multi-select checkboxes
7. **Foto:** Upload dengan preview

**Checklist:**
- [ ] Create Create.vue
- [ ] Create Edit.vue (or reusable form)
- [ ] Implement file upload with preview
- [ ] Implement multi-select subjects
- [ ] Conditional fields (gaji tetap vs honor)
- [ ] Form validation
- [ ] Mobile responsive

---

### Task 1.9: Vue Pages - Show (Profile)
**Estimated:** 2-3 hours

**File:** `resources/js/pages/Admin/Teachers/Show.vue`

**Tabs:**
1. **Info Pribadi:** Semua data guru
2. **Jadwal Mengajar:** (placeholder, Epic 2)
3. **Rekap Presensi:** (placeholder, integrasi)
4. **Rekap Honor:** (placeholder, Epic 3)
5. **Evaluasi:** (placeholder, Epic 4)

**Checklist:**
- [ ] Create Show.vue
- [ ] Implement tabs navigation
- [ ] Profile header dengan foto
- [ ] Info Pribadi tab
- [ ] Placeholder tabs untuk fitur lain
- [ ] Quick actions (Edit, Nonaktifkan)
- [ ] Mobile responsive

---

### Task 1.10: Navigation & Menu
**Estimated:** 30 minutes

**Update:** `useNavigation.ts`

```typescript
// Add to admin menu
{
  name: 'Guru',
  href: '/admin/teachers',
  icon: 'users',
  children: [
    { name: 'Data Guru', href: '/admin/teachers' },
    { name: 'Jadwal Mengajar', href: '/admin/teachers/schedules' },
    { name: 'Rekap Honor', href: '/admin/teachers/salary' },
    { name: 'Evaluasi', href: '/admin/teachers/evaluations' },
  ]
}
```

**Checklist:**
- [ ] Add Teacher menu to navigation
- [ ] Add submenu items
- [ ] Set correct icons
- [ ] Test navigation

---

## ✅ Definition of Done

- [ ] Semua migrations sudah dijalankan
- [ ] Models dengan relationships berfungsi
- [ ] Factory & Seeder tersedia
- [ ] CRUD lengkap (Create, Read, Update, Soft Delete)
- [ ] Auto-create user account saat tambah guru
- [ ] Filter & search berfungsi
- [ ] Upload foto berfungsi
- [ ] Multi-select mata pelajaran berfungsi
- [ ] Profile view dengan tabs
- [ ] Nonaktifkan/aktifkan guru berfungsi
- [ ] Mobile responsive
- [ ] No linter errors
- [ ] Unit tests pass

---

## 🧪 Test Cases

### Feature Tests
```php
public function test_admin_can_view_teachers_list()
public function test_admin_can_create_teacher()
public function test_admin_can_update_teacher()
public function test_admin_can_deactivate_teacher()
public function test_admin_can_reactivate_teacher()
public function test_teacher_creation_creates_user_account()
public function test_validation_requires_unique_nip()
public function test_validation_requires_unique_nik()
public function test_filter_by_status_works()
public function test_search_by_name_works()
```

---

## 📝 Notes

- Pastikan upload foto menggunakan `storage:link`
- Password auto-generated perlu dicatat/dikirim ke guru
- Pertimbangkan email notification untuk credentials
- NIK harus 16 digit (validasi)
- NIP format sesuai standar PNS (jika ada)

---

**Epic Status:** 🔵 Not Started  
**Last Updated:** 29 Januari 2026
