# System Architecture Recommendation
## Sistem Manajemen Sekolah SD - VILT Stack

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)

---

## 📋 Table of Contents

1. [Technology Stack Overview](#1-technology-stack-overview)
2. [System Architecture](#2-system-architecture)
3. [Database Architecture](#3-database-architecture)
4. [Application Structure](#4-application-structure)
5. [Module Architecture](#5-module-architecture)
6. [Security Architecture](#6-security-architecture)
7. [Integration Architecture](#7-integration-architecture)
8. [Performance & Optimization](#8-performance--optimization)
9. [Deployment Architecture](#9-deployment-architecture)
10. [Development Workflow](#10-development-workflow)
11. [Testing Strategy](#11-testing-strategy)
12. [Monitoring & Maintenance](#12-monitoring--maintenance)

---

## 1. Technology Stack Overview

### 1.1 VILT Stack Components

```
┌─────────────────────────────────────────────────┐
│              FRONTEND LAYER                      │
├─────────────────────────────────────────────────┤
│  Vue.js 3 (Composition API)                     │
│  + Inertia.js (SSR-like SPA)                    │
│  + Tailwind CSS + DaisyUI/Flowbite             │
│  + Pinia (State Management)                     │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│            MIDDLEWARE LAYER                      │
├─────────────────────────────────────────────────┤
│  Inertia.js Adapter                             │
│  + Laravel Sanctum (Auth)                       │
│  + Spatie Laravel Permission (RBAC)             │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│              BACKEND LAYER                       │
├─────────────────────────────────────────────────┤
│  Laravel 10.x (PHP 8.2+)                        │
│  + Eloquent ORM                                 │
│  + Laravel Queue (Jobs/Tasks)                   │
│  + Laravel Breeze (Starter Kit)                 │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│             DATABASE LAYER                       │
├─────────────────────────────────────────────────┤
│  MySQL 8.0+ / PostgreSQL 14+                    │
│  + Redis (Cache & Sessions)                     │
└─────────────────────────────────────────────────┘
```

### 1.2 Key Dependencies & Packages

**Backend (Laravel):**
```json
{
  "laravel/framework": "^12.0",
  "inertiajs/inertia-laravel": "^1.3",
  "laravel/sanctum": "^4.0",
  "spatie/laravel-permission": "^6.9",
  "spatie/laravel-medialibrary": "^11.0",
  "barryvdh/laravel-dompdf": "^3.0",
  "maatwebsite/excel": "^3.1",
  "laravel/horizon": "^5.24",
  "spatie/laravel-activitylog": "^4.8"
}
```

**Frontend (Vue.js):**
```json
{
  "vue": "^3.5",
  "@inertiajs/vue3": "^1.2",
  "pinia": "^2.2",
  "tailwindcss": "^4.0",
  "@vueuse/core": "^11.0",
  "motion": "^11.0",
  "chart.js": "^4.4",
  "vue-chartjs": "^5.3",
  "date-fns": "^3.0"
}
```

**Note:** NO UI component libraries (DaisyUI, Flowbite, etc) - build components dari scratch untuk avoid breaking changes

### 1.3 Why VILT Stack?

**Advantages:**
1. **Laravel 12.x:** Latest stable, modern PHP 8.4 features, improved performance
2. **Inertia.js:** Best of both worlds - SPA experience tanpa API complexity
3. **Vue.js 3:** Reactive, modern, lightweight, easy to learn
4. **Tailwind CSS v4:** Utility-first, native CSS, zero-config
5. **Motion-v:** Modern animation library, buttery smooth transitions
6. **Pragmatic:** Tidak over-engineered, NO UI packages (avoid breaking changes)

**Trade-offs:**
- ❌ Tidak native mobile app (gunakan PWA atau future phase: Flutter/React Native)
- ❌ Inertia.js relatif kurang populer dibanding REST API (tapi dokumentasi bagus)
- ✅ Perfect untuk admin panel & school management use case
- ✅ Fast development, minimal boilerplate
- ✅ **PHP 8.4:** Property hooks, new array functions, asymmetric visibility
- ✅ **Laravel 12:** Improved performance, better DX, native type hints

---

## 2. System Architecture

### 2.1 High-Level Architecture

```
┌────────────────────────────────────────────────────────────┐
│                   CLIENT DEVICES                            │
│  Desktop Browser | Mobile Browser | Tablet                  │
└────────────────────┬───────────────────────────────────────┘
                     │ HTTPS
                     ↓
┌────────────────────────────────────────────────────────────┐
│                   LOAD BALANCER (Optional Phase 2)          │
│                   Nginx / Cloudflare                        │
└────────────────────┬───────────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────────┐
│               WEB SERVER (Nginx + PHP-FPM)                  │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │          LARAVEL APPLICATION                          │  │
│  │                                                        │  │
│  │  ┌─────────────────────────────────────────────┐    │  │
│  │  │  Inertia.js Middleware                       │    │  │
│  │  └─────────────────────────────────────────────┘    │  │
│  │                                                        │  │
│  │  ┌─────────────┐  ┌──────────────┐  ┌──────────┐   │  │
│  │  │ Controllers  │  │  Services    │  │  Models  │   │  │
│  │  │   (HTTP)     │  │  (Business)  │  │(Eloquent)│   │  │
│  │  └─────────────┘  └──────────────┘  └──────────┘   │  │
│  │                                                        │  │
│  │  ┌─────────────┐  ┌──────────────┐  ┌──────────┐   │  │
│  │  │   Jobs/      │  │  Events/     │  │  Mail/   │   │  │
│  │  │   Queues     │  │  Listeners   │  │  Notif   │   │  │
│  │  └─────────────┘  └──────────────┘  └──────────┘   │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │          VUE.JS FRONTEND (SSR via Inertia)            │  │
│  │                                                        │  │
│  │  ┌─────────────┐  ┌──────────────┐  ┌──────────┐   │  │
│  │  │   Pages/     │  │  Components  │  │  Layouts │   │  │
│  │  │   Views      │  │  (Reusable)  │  │          │   │  │
│  │  └─────────────┘  └──────────────┘  └──────────┘   │  │
│  │                                                        │  │
│  │  ┌─────────────┐  ┌──────────────┐                  │  │
│  │  │   Stores     │  │  Composables │                  │  │
│  │  │  (Pinia)     │  │  (VueUse)    │                  │  │
│  │  └─────────────┘  └──────────────┘                  │  │
│  └──────────────────────────────────────────────────────┘  │
└────────────────────┬───────────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────────┐
│                  STORAGE LAYER                              │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │
│  │   MySQL      │  │    Redis     │  │ File Storage │    │
│  │  (Primary)   │  │ (Cache/Queue)│  │  (S3/Local)  │    │
│  └──────────────┘  └──────────────┘  └──────────────┘    │
└────────────────────────────────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────────┐
│              EXTERNAL SERVICES (Integrations)               │
│                                                              │
│  WhatsApp API │ Email SMTP │ Payment Gateway │ Cloud Storage│
└────────────────────────────────────────────────────────────┘
```

### 2.2 Request Flow (Inertia.js Pattern)

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ 1. Navigate to /students
       ↓
┌──────────────────┐
│  Laravel Router  │ routes/web.php
└──────┬───────────┘
       │ 2. Route to StudentController@index
       ↓
┌──────────────────┐
│   Middleware     │ auth, role:admin
└──────┬───────────┘
       │ 3. Authorize & Validate
       ↓
┌──────────────────┐
│   Controller     │ StudentController
└──────┬───────────┘
       │ 4. Fetch data via Service/Model
       ↓
┌──────────────────┐
│   Service Layer  │ StudentService
└──────┬───────────┘
       │ 5. Query database via Eloquent
       ↓
┌──────────────────┐
│   Model (ORM)    │ Student::with('class')->paginate(20)
└──────┬───────────┘
       │ 6. Return data
       ↓
┌──────────────────┐
│   Controller     │ return Inertia::render('Students/Index', [...])
└──────┬───────────┘
       │ 7. Return Inertia response
       ↓
┌──────────────────┐
│  Inertia.js      │ Serialize data, inject to Vue component
└──────┬───────────┘
       │ 8. Render Vue component
       ↓
┌──────────────────┐
│  Vue Component   │ Students/Index.vue
└──────┬───────────┘
       │ 9. Display to user
       ↓
┌─────────────┐
│   Browser   │ Interactive SPA
└─────────────┘
```

### 2.3 Layered Architecture

```
┌────────────────────────────────────────────────────────┐
│              PRESENTATION LAYER                         │
│  Vue.js Pages, Components, Layouts                      │
│  - Handle UI/UX                                         │
│  - User interactions                                    │
│  - Form validation (client-side)                        │
└────────────────────┬───────────────────────────────────┘
                     ↓ Inertia.js
┌────────────────────────────────────────────────────────┐
│              CONTROLLER LAYER                           │
│  Laravel Controllers                                    │
│  - Handle HTTP requests                                 │
│  - Input validation (server-side)                       │
│  - Delegate to Services                                 │
│  - Return Inertia responses                             │
└────────────────────┬───────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────┐
│              SERVICE LAYER (Business Logic)             │
│  Service Classes                                        │
│  - Business rules                                       │
│  - Complex operations                                   │
│  - Orchestrate multiple models                          │
│  - Transaction management                               │
└────────────────────┬───────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────┐
│              REPOSITORY / MODEL LAYER                   │
│  Eloquent Models                                        │
│  - Database queries                                     │
│  - Relationships                                        │
│  - Data access logic                                    │
└────────────────────┬───────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────┐
│              DATABASE LAYER                             │
│  MySQL / PostgreSQL                                     │
└────────────────────────────────────────────────────────┘
```

**Example Implementation:**

```php
// Controller Layer
class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService
    ) {}

    public function index(Request $request)
    {
        $students = $this->studentService->getStudentsList(
            $request->input('filters', [])
        );

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => $request->input('filters'),
        ]);
    }
}

// Service Layer
class StudentService
{
    public function getStudentsList(array $filters): LengthAwarePaginator
    {
        return Student::query()
            ->with(['class', 'parentAccount'])
            ->when($filters['class_id'] ?? null, fn($q, $classId) => 
                $q->where('class_id', $classId)
            )
            ->when($filters['status'] ?? null, fn($q, $status) => 
                $q->where('status', $status)
            )
            ->when($filters['search'] ?? null, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->latest('created_at')
            ->paginate(20);
    }

    public function createStudent(array $data): Student
    {
        DB::transaction(function () use ($data) {
            $student = Student::create($data);
            
            // Auto-create parent account
            $this->createParentAccount($student, $data['parent_data']);
            
            // Send welcome notification
            dispatch(new SendWelcomeNotification($student));
            
            return $student;
        });
    }
}
```

---

## 3. Database Architecture

### 3.1 Database Design Principles

1. **Normalization:** 3NF untuk master data, denormalization untuk reporting
2. **Soft Deletes:** Semua tabel utama menggunakan soft delete (`deleted_at`)
3. **Timestamps:** `created_at`, `updated_at` wajib di semua tabel
4. **Audit Trail:** Gunakan `spatie/laravel-activitylog` untuk critical tables
5. **UUID vs Auto-Increment:** Auto-increment ID untuk simplicity (performance better)
6. **Indexing:** Index untuk foreign keys, search fields, dan filter fields

### 3.2 Database Schema Overview

**Core Tables:**
```
users
├── id, name, email, password, role
├── is_first_login, remember_token
└── timestamps, soft_deletes

students
├── id, nis, nisn, nik, name
├── class_id, status, parent_account_id
├── biodata fields (gender, dob, address, etc)
└── timestamps, soft_deletes

classes
├── id, name, level, homeroom_teacher_id
├── academic_year_id, capacity
└── timestamps

teachers
├── id, user_id, nip, name
├── status (tetap/honorer), salary, hourly_rate
├── subjects (JSON array atau pivot table)
└── timestamps, soft_deletes

attendances
├── id, student_id, class_id, date
├── status (H/I/S/A), created_by
└── timestamps

payments
├── id, student_id, category_id
├── amount, method, receipt_number
├── status, paid_at, verified_at
└── timestamps, soft_deletes

bills
├── id, student_id, category_id
├── amount, due_date, period
├── status (unpaid/partial/paid)
└── timestamps

grades
├── id, student_id, subject_id
├── semester_id, assessment_type
├── score, teacher_id
└── timestamps

report_cards
├── id, student_id, semester_id
├── status (draft/finalized)
├── finalized_at, pdf_path
└── timestamps
```

**Relationship Diagram:**
```
┌──────────┐
│  users   │──┐
└──────────┘  │
              │
┌──────────┐  │  ┌──────────┐
│ students │──┼──│ classes  │
└──────────┘  │  └──────────┘
     │        │
     │        └─── teachers
     │
     ├─── attendances
     ├─── payments
     ├─── bills
     ├─── grades
     └─── report_cards
```

### 3.3 Database Indexes

**Critical Indexes:**
```sql
-- Students
CREATE INDEX idx_students_nis ON students(nis);
CREATE INDEX idx_students_status ON students(status);
CREATE INDEX idx_students_class_id ON students(class_id);
CREATE INDEX idx_students_name ON students(name); -- For search

-- Attendances
CREATE INDEX idx_attendances_date ON attendances(date);
CREATE INDEX idx_attendances_student_class ON attendances(student_id, class_id, date);

-- Payments
CREATE INDEX idx_payments_student_date ON payments(student_id, paid_at);
CREATE INDEX idx_payments_status ON payments(status);

-- Bills
CREATE INDEX idx_bills_due_status ON bills(due_date, status);
CREATE INDEX idx_bills_student_period ON bills(student_id, period);

-- Grades
CREATE INDEX idx_grades_student_semester ON grades(student_id, semester_id);
```

### 3.4 Sample Migration

```php
// database/migrations/2025_01_01_000001_create_students_table.php
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('nis', 20)->unique();
    $table->string('nisn', 10)->unique()->nullable();
    $table->string('nik', 16)->unique();
    $table->string('name', 100);
    $table->string('nickname', 50)->nullable();
    $table->enum('gender', ['L', 'P']);
    $table->string('birth_place', 100);
    $table->date('birth_date');
    $table->enum('religion', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']);
    
    $table->text('address');
    $table->string('phone', 15)->nullable();
    $table->string('email', 100)->nullable();
    $table->string('photo_path')->nullable();
    
    $table->foreignId('class_id')->constrained('classes');
    $table->foreignId('parent_account_id')->nullable()->constrained('users');
    
    $table->enum('status', ['Aktif', 'Mutasi', 'DO', 'Lulus'])->default('Aktif');
    $table->date('enrollment_date');
    $table->foreignId('academic_year_id')->constrained('academic_years');
    
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['nis', 'status']);
    $table->index('name');
});
```

---

## 4. Application Structure

### 4.1 Folder Structure (Laravel)

```
laravel-app/
├── app/
│   ├── Console/
│   │   └── Commands/          # Custom artisan commands
│   │       ├── GenerateMonthlyBills.php
│   │       └── SendPaymentReminders.php
│   │
│   ├── Exports/               # Excel exports
│   │   ├── StudentsExport.php
│   │   └── PaymentsExport.php
│   │
│   ├── Http/
│   │   ├── Controllers/       # Organized by module
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── PasswordResetController.php
│   │   │   │
│   │   │   ├── Student/
│   │   │   │   ├── StudentController.php
│   │   │   │   └── StudentClassPromotionController.php
│   │   │   │
│   │   │   ├── Attendance/
│   │   │   │   ├── StudentAttendanceController.php
│   │   │   │   ├── TeacherAttendanceController.php
│   │   │   │   └── LeaveRequestController.php
│   │   │   │
│   │   │   ├── Payment/
│   │   │   │   ├── PaymentController.php
│   │   │   │   ├── BillController.php
│   │   │   │   └── PaymentReportController.php
│   │   │   │
│   │   │   ├── Grade/
│   │   │   │   ├── GradeController.php
│   │   │   │   └── ReportCardController.php
│   │   │   │
│   │   │   ├── PSB/
│   │   │   │   ├── RegistrationController.php
│   │   │   │   └── VerificationController.php
│   │   │   │
│   │   │   ├── Teacher/
│   │   │   │   ├── TeacherController.php
│   │   │   │   └── ScheduleController.php
│   │   │   │
│   │   │   ├── Dashboard/
│   │   │   │   └── DashboardController.php
│   │   │   │
│   │   │   └── Settings/
│   │   │       ├── SchoolSettingsController.php
│   │   │       └── UserManagementController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── HandleInertiaRequests.php
│   │   │   ├── CheckRole.php
│   │   │   └── ForcePasswordChange.php
│   │   │
│   │   └── Requests/          # Form Requests
│   │       ├── StoreStudentRequest.php
│   │       ├── UpdateStudentRequest.php
│   │       └── StorePaymentRequest.php
│   │
│   ├── Jobs/                  # Background jobs
│   │   ├── SendPaymentReminder.php
│   │   ├── GenerateSPPBills.php
│   │   └── SendAttendanceAlert.php
│   │
│   ├── Mail/                  # Email templates
│   │   ├── PaymentReceipt.php
│   │   └── ReportCardAvailable.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── Teacher.php
│   │   ├── Class.php
│   │   ├── Attendance.php
│   │   ├── Payment.php
│   │   ├── Bill.php
│   │   ├── Grade.php
│   │   └── ReportCard.php
│   │
│   ├── Notifications/         # Notification classes
│   │   ├── PaymentDueReminder.php
│   │   └── AttendanceAlert.php
│   │
│   ├── Policies/              # Authorization policies
│   │   ├── StudentPolicy.php
│   │   └── PaymentPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── AuthServiceProvider.php
│   │
│   └── Services/              # Business logic
│       ├── StudentService.php
│       ├── AttendanceService.php
│       ├── PaymentService.php
│       ├── GradeService.php
│       ├── ReportCardService.php
│       ├── NotificationService.php
│       └── WhatsAppService.php
│
├── bootstrap/
│   └── ssr/                   # SSR build output (if needed)
│
├── config/
│   ├── school.php             # Custom school config
│   └── services.php           # Third-party API configs
│
├── database/
│   ├── factories/
│   ├── migrations/
│   │   ├── 2025_01_01_000001_create_students_table.php
│   │   ├── 2025_01_01_000002_create_attendances_table.php
│   │   └── ...
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolePermissionSeeder.php
│       └── DummyDataSeeder.php
│
├── public/
│   ├── build/                 # Vite build output
│   └── storage/               # Symlink to storage/app/public
│
├── resources/
│   ├── js/
│   │   ├── Components/        # Reusable Vue components
│   │   │   ├── Layout/
│   │   │   │   ├── AppLayout.vue
│   │   │   │   ├── AuthLayout.vue
│   │   │   │   ├── Navbar.vue
│   │   │   │   └── Sidebar.vue
│   │   │   │
│   │   │   ├── UI/            # Custom UI components (NO external packages)
│   │   │   │   ├── Button.vue       # Built from scratch
│   │   │   │   ├── Input.vue        # Built from scratch
│   │   │   │   ├── Modal.vue        # Built from scratch
│   │   │   │   ├── Table.vue        # Built from scratch
│   │   │   │   └── Card.vue         # Built from scratch
│   │   │   │
│   │   │   ├── Student/
│   │   │   │   ├── StudentCard.vue
│   │   │   │   └── StudentForm.vue
│   │   │   │
│   │   │   └── Shared/        # Shared across modules
│   │   │       ├── Pagination.vue
│   │   │       └── SearchFilter.vue
│   │   │
│   │   ├── Layouts/           # Page layouts
│   │   │   ├── AppLayout.vue
│   │   │   └── GuestLayout.vue
│   │   │
│   │   ├── Pages/             # Inertia pages (Routes)
│   │   │   ├── Auth/
│   │   │   │   ├── Login.vue
│   │   │   │   ├── ForgotPassword.vue
│   │   │   │   └── ResetPassword.vue
│   │   │   │
│   │   │   ├── Dashboard/
│   │   │   │   ├── Principal.vue
│   │   │   │   ├── Admin.vue
│   │   │   │   ├── Teacher.vue
│   │   │   │   └── Parent.vue
│   │   │   │
│   │   │   ├── Students/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   ├── Edit.vue
│   │   │   │   └── Show.vue
│   │   │   │
│   │   │   ├── Attendance/
│   │   │   │   ├── DailyInput.vue
│   │   │   │   ├── SubjectInput.vue
│   │   │   │   ├── LeaveRequests.vue
│   │   │   │   └── Reports.vue
│   │   │   │
│   │   │   ├── Payments/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   ├── Bills.vue
│   │   │   │   └── Reports.vue
│   │   │   │
│   │   │   ├── Grades/
│   │   │   │   ├── Input.vue
│   │   │   │   ├── Summary.vue
│   │   │   │   └── ReportCards.vue
│   │   │   │
│   │   │   ├── PSB/
│   │   │   │   ├── Register.vue
│   │   │   │   ├── Verification.vue
│   │   │   │   └── Tracking.vue
│   │   │   │
│   │   │   ├── Teachers/
│   │   │   │   ├── Index.vue
│   │   │   │   └── Schedule.vue
│   │   │   │
│   │   │   └── Settings/
│   │   │       ├── School.vue
│   │   │       ├── Academic.vue
│   │   │       └── Users.vue
│   │   │
│   │   ├── Stores/            # Pinia stores
│   │   │   ├── auth.js
│   │   │   ├── student.js
│   │   │   └── notification.js
│   │   │
│   │   ├── Composables/       # Vue composables
│   │   │   ├── usePermission.js
│   │   │   ├── useNotification.js
│   │   │   └── useDebounce.js
│   │   │
│   │   ├── Utils/             # Helper functions
│   │   │   ├── format.js
│   │   │   ├── validation.js
│   │   │   └── constants.js
│   │   │
│   │   └── app.js             # Main entry point
│   │
│   ├── css/
│   │   └── app.css            # Tailwind imports
│   │
│   └── views/
│       └── app.blade.php      # Root HTML template
│
├── routes/
│   ├── web.php                # Main routes file
│   ├── auth.php               # Auth routes
│   └── api.php                # API routes (minimal, for webhooks)
│
├── storage/
│   ├── app/
│   │   ├── public/            # Public files (student photos, receipts)
│   │   └── private/           # Private files (documents)
│   │
│   └── logs/
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Student/
│   │   └── Payment/
│   │
│   └── Unit/
│       └── Services/
│
├── .env.example
├── artisan
├── composer.json
├── package.json
├── tailwind.config.js
├── vite.config.js
└── README.md
```

### 4.2 Vue.js Component Structure

**Example: Students/Index.vue**
```vue
<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { animate, stagger } from 'motion';
import { onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/Shared/Pagination.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';

const props = defineProps({
  students: Object,
  filters: Object,
});

const searchQuery = ref(props.filters.search || '');

const handleSearch = () => {
  router.get('/students', { search: searchQuery.value }, {
    preserveState: true,
    replace: true,
  });
};

const deleteStudent = (studentId) => {
  if (confirm('Yakin ingin menghapus siswa ini?')) {
    router.delete(`/students/${studentId}`);
  }
};

// Animate list on mount
onMounted(() => {
  animate(
    '.student-row',
    { opacity: [0, 1], y: [20, 0] },
    { duration: 0.3, delay: stagger(0.05) }
  );
});
</script>

<template>
  <AppLayout title="Daftar Siswa">
    <div class="container mx-auto px-4 py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Daftar Siswa</h1>
        <Button
          as="link"
          :href="route('students.create')"
          variant="primary"
        >
          Tambah Siswa
        </Button>
      </div>

      <!-- Search & Filter -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <Input
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Cari nama atau NIS..."
          class="w-full"
        />
      </div>

      <!-- Students Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <Table :data="students.data" :columns="['NIS', 'Nama', 'Kelas', 'Status', 'Aksi']">
          <template #row="{ item }">
            <td class="px-4 py-3">{{ item.nis }}</td>
            <td class="px-4 py-3">{{ item.name }}</td>
            <td class="px-4 py-3">{{ item.class?.name }}</td>
            <td class="px-4 py-3">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  item.status === 'Aktif' ? 'bg-green-100 text-green-800' :
                  item.status === 'Mutasi' ? 'bg-yellow-100 text-yellow-800' :
                  item.status === 'Lulus' ? 'bg-blue-100 text-blue-800' :
                  'bg-red-100 text-red-800'
                ]"
              >
                {{ item.status }}
              </span>
            </td>
            <td class="px-4 py-3 space-x-2">
              <Button
                as="link"
                :href="route('students.show', item.id)"
                variant="ghost"
                size="sm"
              >
                Lihat
              </Button>
              <Button
                as="link"
                :href="route('students.edit', item.id)"
                variant="ghost"
                size="sm"
              >
                Edit
              </Button>
              <Button
                @click="deleteStudent(item.id)"
                variant="danger"
                size="sm"
              >
                Hapus
              </Button>
            </td>
          </template>
        </Table>
      </div>

      <Pagination :data="students" class="mt-4" />
    </div>
  </AppLayout>
</template>
```

---

## 5. Module Architecture

### 5.1 Module Pattern

Setiap modul mengikuti struktur konsisten:

```
Module/
├── Controller (HTTP handling)
├── Service (Business logic)
├── Model (Data access)
├── Request (Validation)
├── Policy (Authorization)
├── Job (Background tasks)
├── Notification (Alerts)
└── Views (Vue pages & components)
```

### 5.2 Module Examples

**Student Management Module:**
```
app/
├── Http/Controllers/Student/
│   ├── StudentController.php
│   └── StudentClassPromotionController.php
│
├── Services/
│   └── StudentService.php
│
├── Models/
│   └── Student.php
│
├── Http/Requests/
│   ├── StoreStudentRequest.php
│   └── UpdateStudentRequest.php
│
├── Policies/
│   └── StudentPolicy.php
│
└── Jobs/
    └── SendWelcomeNotification.php

resources/js/
└── Pages/Students/
    ├── Index.vue
    ├── Create.vue
    ├── Edit.vue
    └── Show.vue
```

**Payment Module:**
```
app/
├── Http/Controllers/Payment/
│   ├── PaymentController.php
│   ├── BillController.php
│   └── PaymentReportController.php
│
├── Services/
│   ├── PaymentService.php
│   └── BillService.php
│
├── Models/
│   ├── Payment.php
│   ├── Bill.php
│   └── PaymentCategory.php
│
├── Jobs/
│   ├── GenerateMonthlyBills.php
│   └── SendPaymentReminder.php
│
└── Exports/
    └── PaymentsExport.php

resources/js/
└── Pages/Payments/
    ├── Index.vue
    ├── Create.vue
    ├── Bills.vue
    └── Reports.vue
```

### 5.3 Service Layer Pattern

**Example: StudentService.php**
```php
<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    public function createStudent(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // 1. Generate NIS
            $nis = $this->generateNIS($data['enrollment_year']);

            // 2. Create student record
            $student = Student::create([
                'nis' => $nis,
                'nisn' => $data['nisn'],
                'nik' => $data['nik'],
                'name' => $data['name'],
                'gender' => $data['gender'],
                'birth_place' => $data['birth_place'],
                'birth_date' => $data['birth_date'],
                'religion' => $data['religion'],
                'address' => $data['address'],
                'class_id' => $data['class_id'],
                'status' => 'Aktif',
                'enrollment_date' => now(),
                'academic_year_id' => $data['academic_year_id'],
            ]);

            // 3. Upload photo if exists
            if (isset($data['photo'])) {
                $student->addMedia($data['photo'])
                    ->toMediaCollection('student_photos');
            }

            // 4. Create parent account
            $parentAccount = $this->createParentAccount($student, $data['parent_data']);
            
            $student->update(['parent_account_id' => $parentAccount->id]);

            // 5. Send welcome notification
            dispatch(new \App\Jobs\SendWelcomeNotification($student, $parentAccount));

            return $student->fresh();
        });
    }

    private function generateNIS(int $year): string
    {
        $latestStudent = Student::whereYear('enrollment_date', $year)
            ->orderBy('nis', 'desc')
            ->first();

        if (!$latestStudent) {
            return $year . '0001';
        }

        $lastNumber = (int) substr($latestStudent->nis, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return $year . $newNumber;
    }

    private function createParentAccount(Student $student, array $parentData): User
    {
        // Check if parent account already exists (same phone number)
        $existingParent = User::where('username', $parentData['phone'])->first();

        if ($existingParent) {
            // Link student to existing parent account (multi-child support)
            return $existingParent;
        }

        // Create new parent account
        return User::create([
            'name' => $parentData['father_name'],
            'username' => $parentData['phone'],
            'email' => $parentData['email'] ?? null,
            'password' => Hash::make('Ortu' . $student->nis),
            'role' => 'parent',
            'is_first_login' => true,
        ]);
    }

    public function promoteClass(int $fromClassId, int $toClassId): array
    {
        $students = Student::where('class_id', $fromClassId)
            ->where('status', 'Aktif')
            ->get();

        $promoted = [];

        DB::transaction(function () use ($students, $toClassId, &$promoted) {
            foreach ($students as $student) {
                $student->update(['class_id' => $toClassId]);
                
                // Log class history
                $student->classHistory()->create([
                    'from_class_id' => $student->class_id,
                    'to_class_id' => $toClassId,
                    'promoted_at' => now(),
                ]);

                $promoted[] = $student->id;
            }
        });

        return [
            'promoted_count' => count($promoted),
            'student_ids' => $promoted,
        ];
    }
}
```

---

## 6. Security Architecture

### 6.1 Authentication Flow

**Laravel Sanctum (Session-based):**
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
    sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    )
)),
```

**Login Controller:**
```php
class LoginController extends Controller
{
    public function store(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Rate limiting check
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            throw ValidationException::withMessages([
                'username' => ['Terlalu banyak percobaan. Coba lagi dalam 15 menit.'],
            ]);
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            RateLimiter::clear($this->throttleKey($request));

            $request->session()->regenerate();

            $user = Auth::user();

            // Log activity
            activity()
                ->causedBy($user)
                ->log('User logged in');

            // Check first login
            if ($user->is_first_login) {
                return redirect()->route('password.force-change');
            }

            return redirect()->intended(
                $this->dashboardRoute($user->role)
            );
        }

        RateLimiter::hit($this->throttleKey($request), 900); // 15 minutes

        throw ValidationException::withMessages([
            'username' => ['Username atau password salah.'],
        ]);
    }

    private function dashboardRoute(string $role): string
    {
        return match($role) {
            'principal' => route('dashboard.principal'),
            'admin' => route('dashboard.admin'),
            'teacher' => route('dashboard.teacher'),
            'parent' => route('dashboard.parent'),
            default => route('dashboard'),
        };
    }
}
```

### 6.2 Authorization (RBAC)

**Setup Spatie Permission:**
```php
// database/seeders/RolePermissionSeeder.php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $superAdmin = Role::create(['name' => 'superadmin']);
        $principal = Role::create(['name' => 'principal']);
        $admin = Role::create(['name' => 'admin']);
        $teacher = Role::create(['name' => 'teacher']);
        $parent = Role::create(['name' => 'parent']);

        // Create permissions
        Permission::create(['name' => 'view students']);
        Permission::create(['name' => 'create students']);
        Permission::create(['name' => 'edit students']);
        Permission::create(['name' => 'delete students']);
        
        Permission::create(['name' => 'view payments']);
        Permission::create(['name' => 'create payments']);
        Permission::create(['name' => 'verify payments']);
        
        // ... more permissions

        // Assign permissions to roles
        $superAdmin->givePermissionTo(Permission::all());
        
        $admin->givePermissionTo([
            'view students', 'create students', 'edit students',
            'view payments', 'create payments', 'verify payments',
            // ...
        ]);

        $teacher->givePermissionTo([
            'view students', // only their class
            'input attendance', 'input grades',
        ]);

        $parent->givePermissionTo([
            'view own child', 'view payments', 'submit leave request',
        ]);
    }
}
```

**Policy Example:**
```php
// app/Policies/StudentPolicy.php
class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'principal', 'admin', 'teacher']);
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['superadmin', 'principal', 'admin'])) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            // Teacher can only view students in their class
            return $student->class->homeroom_teacher_id === $user->teacher->id
                || $user->teacher->teachingSchedules()
                    ->where('class_id', $student->class_id)
                    ->exists();
        }

        if ($user->hasRole('parent')) {
            // Parent can only view their own child
            return $student->parent_account_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('superadmin');
    }
}
```

**Middleware:**
```php
// routes/web.php
Route::middleware(['auth', 'role:admin|superadmin'])->group(function () {
    Route::resource('students', StudentController::class);
    Route::post('students/promote', [StudentController::class, 'promoteClass']);
});

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('attendance/daily', [AttendanceController::class, 'daily']);
    Route::post('attendance/daily', [AttendanceController::class, 'store']);
});

Route::middleware(['auth', 'role:parent'])->group(function () {
    Route::get('my-child', [ParentController::class, 'showChild']);
    Route::get('my-child/grades', [ParentController::class, 'childGrades']);
});
```

### 6.3 Data Security

**Encryption:**
```php
// config/school.php
return [
    'encrypt_fields' => [
        'students.nik',
        'parents.nik',
        'payments.bank_account_number',
    ],
];

// Model with encrypted attributes
use Illuminate\Database\Eloquent\Casts\Attribute;

class Student extends Model
{
    protected function nik(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }
}
```

**File Upload Security:**
```php
// app/Http/Requests/StoreStudentRequest.php
public function rules(): array
{
    return [
        'photo' => [
            'nullable',
            'image',
            'max:2048', // 2MB
            'mimes:jpg,jpeg,png',
        ],
        'documents.*' => [
            'nullable',
            'file',
            'max:5120', // 5MB
            'mimes:pdf,jpg,jpeg,png',
        ],
    ];
}

// Controller
public function store(StoreStudentRequest $request)
{
    $validated = $request->validated();

    if ($request->hasFile('photo')) {
        // Sanitize filename
        $filename = Str::slug(pathinfo($request->file('photo')->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $request->file('photo')->getClientOriginalExtension();
        
        // Store with hashed name
        $path = $request->file('photo')->storeAs(
            'student-photos',
            $filename . '_' . time() . '.' . $extension,
            'private'
        );
    }
}
```

**XSS Protection:**
```php
// Automatic via Laravel's {{ }} blade syntax
// Vue.js also auto-escapes by default

// For HTML content (announcements, etc), use purifier
composer require mews/purifier

// config/purifier.php
return [
    'default' => [
        'HTML.Allowed' => 'p,b,strong,i,em,u,a[href],ul,ol,li,br',
    ],
];

// Usage
$clean = clean($request->input('content'));
```

**CSRF Protection:**
```php
// Automatic in Laravel for POST/PUT/DELETE requests
// Inertia.js handles this automatically

// In forms
@csrf

// In Vue (handled by Inertia)
<form @submit.prevent="submit">
  <!-- Inertia auto-includes CSRF token -->
</form>
```

---

## 7. Integration Architecture

### 7.1 WhatsApp Integration

**Service Class:**
```php
// app/Services/WhatsAppService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url');
        $this->apiKey = config('services.whatsapp.key');
    }

    public function sendMessage(string $phoneNumber, string $message): bool
    {
        try {
            $response = Http::post($this->apiUrl . '/send', [
                'api_key' => $this->apiKey,
                'phone' => $this->formatPhoneNumber($phoneNumber),
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp sent', [
                    'phone' => $phoneNumber,
                    'status' => 'success',
                ]);
                return true;
            }

            Log::error('WhatsApp failed', [
                'phone' => $phoneNumber,
                'error' => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp exception', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendMessageFromTemplate(string $phoneNumber, string $templateName, array $params): bool
    {
        $message = $this->renderTemplate($templateName, $params);
        return $this->sendMessage($phoneNumber, $message);
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xxx to 628xxx
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    private function renderTemplate(string $templateName, array $params): string
    {
        $template = config("messages.templates.{$templateName}");

        foreach ($params as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }

        return $template;
    }
}
```

**Usage in Job:**
```php
// app/Jobs/SendPaymentReminder.php
namespace App\Jobs;

use App\Models\Bill;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentReminder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Bill $bill
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $student = $this->bill->student;
        $parent = $student->parentAccount;

        $whatsapp->sendMessageFromTemplate(
            $parent->phone,
            'payment_reminder',
            [
                'nama_ortu' => $parent->name,
                'nama_siswa' => $student->name,
                'bulan' => $this->bill->period_month,
                'jumlah' => number_format($this->bill->amount, 0, ',', '.'),
                'due_date' => $this->bill->due_date->format('d/m/Y'),
            ]
        );

        // Log notification
        $this->bill->notifications()->create([
            'type' => 'whatsapp',
            'recipient' => $parent->phone,
            'message' => 'Payment reminder sent',
            'sent_at' => now(),
        ]);
    }
}
```

**Message Templates Config:**
```php
// config/messages.php
return [
    'templates' => [
        'payment_reminder' => "Yth. Bapak/Ibu {{nama_ortu}},\n\n" .
            "Tagihan SPP bulan {{bulan}} untuk {{nama_siswa}} sebesar Rp{{jumlah}} " .
            "akan jatuh tempo pada {{due_date}}.\n\n" .
            "Mohon segera melunasi. Terima kasih.\n\n" .
            "{{school_name}}",

        'attendance_alert' => "Yth. Bapak/Ibu {{nama_ortu}},\n\n" .
            "Anak Anda {{nama_siswa}} tidak hadir hari ini ({{tanggal}}).\n" .
            "Status: {{status}}.\n\n" .
            "Jika ada kesalahan, silakan hubungi sekolah atau ajukan izin melalui portal.\n\n" .
            "{{school_name}}",

        'welcome_student' => "Selamat! Anak Anda {{nama_siswa}} telah terdaftar sebagai siswa {{school_name}}.\n\n" .
            "NIS: {{nis}}\n" .
            "Kelas: {{kelas}}\n\n" .
            "Akun Orang Tua:\n" .
            "Username: {{username}}\n" .
            "Password: {{password}}\n\n" .
            "Silakan login di {{app_url}} dan ganti password Anda.\n\n" .
            "Tahun ajaran dimulai {{start_date}}.",
    ],
];
```

### 7.2 Email Integration

**Mailable:**
```php
// app/Mail/PaymentReceipt.php
namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {}

    public function build()
    {
        return $this->subject('Kwitansi Pembayaran - ' . $this->payment->receipt_number)
            ->markdown('emails.payments.receipt')
            ->attach($this->payment->receipt_pdf_path, [
                'as' => 'Kwitansi_' . $this->payment->receipt_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
```

**Email Template:**
```blade
{{-- resources/views/emails/payments/receipt.blade.php --}}
@component('mail::message')
# Pembayaran Berhasil

Terima kasih atas pembayaran Anda.

**Nomor Kwitansi:** {{ $payment->receipt_number }}
**Tanggal:** {{ $payment->paid_at->format('d F Y') }}
**Jumlah:** Rp {{ number_format($payment->amount, 0, ',', '.') }}

@component('mail::button', ['url' => route('payments.receipt', $payment->id)])
Lihat Kwitansi Online
@endcomponent

Kwitansi PDF terlampir di email ini.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
```

### 7.3 PDF Generation

**Receipt Generator:**
```php
// app/Services/ReceiptService.php
namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{
    public function generate(Payment $payment): string
    {
        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'student' => $payment->student,
            'school' => config('school'),
        ]);

        $filename = 'receipts/' . $payment->receipt_number . '.pdf';
        
        Storage::disk('private')->put($filename, $pdf->output());

        return $filename;
    }

    public function generateReportCard($reportCard): string
    {
        $pdf = Pdf::loadView('pdf.report-card', [
            'reportCard' => $reportCard,
            'student' => $reportCard->student,
            'grades' => $reportCard->grades,
            'attendance' => $reportCard->attendanceSummary,
        ])
        ->setPaper('a4', 'portrait');

        $filename = 'report-cards/' . $reportCard->id . '.pdf';
        
        Storage::disk('private')->put($filename, $pdf->output());

        return $filename;
    }
}
```

---

**(Character limit reached - continuing in next message...)**

Would you like me to continue with the remaining sections:
- Performance & Optimization
- Deployment Architecture
- Development Workflow
- Testing Strategy
- Monitoring & Maintenance

?