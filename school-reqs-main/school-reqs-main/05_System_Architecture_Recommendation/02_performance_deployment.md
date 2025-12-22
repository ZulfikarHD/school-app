# System Architecture Recommendation (Part 2)
## Performance, Deployment & Operational Strategy

---

## 8. Performance & Optimization

### 8.1 Database Optimization

**Query Optimization:**
```php
// ❌ N+1 Problem
$students = Student::all();
foreach ($students as $student) {
    echo $student->class->name; // Additional query per student
}

// ✅ Eager Loading
$students = Student::with('class', 'parentAccount')->get();
foreach ($students as $student) {
    echo $student->class->name; // No additional query
}

// ✅ Selective columns
$students = Student::select('id', 'nis', 'name', 'class_id')
    ->with('class:id,name')
    ->get();

// ✅ Chunking for large datasets
Student::chunk(100, function ($students) {
    foreach ($students as $student) {
        // Process in batches
    }
});
```

**Caching Strategy:**
```php
// Cache school settings (rarely change)
$settings = Cache::remember('school.settings', 3600, function () {
    return SchoolSetting::first();
});

// Cache academic year (change once per year)
$academicYear = Cache::remember('academic_year.active', 86400, function () {
    return AcademicYear::where('is_active', true)->first();
});

// Cache user permissions
$permissions = Cache::remember("user.{$userId}.permissions", 3600, function () use ($userId) {
    return User::find($userId)->getAllPermissions()->pluck('name');
});

// Invalidate cache when data changes
class Student extends Model
{
    protected static function booted()
    {
        static::saved(function ($student) {
            Cache::forget("student.{$student->id}");
            Cache::forget("students.class.{$student->class_id}");
        });
    }
}
```

**Redis Configuration:**
```php
// config/cache.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
    ],
];

// Usage in .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 8.2 Frontend Optimization

**Code Splitting (Vite):**
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['vue', '@inertiajs/vue3', 'pinia'],
                    'charts': ['chart.js', 'vue-chartjs'],
                    'utils': ['date-fns', '@vueuse/core'],
                },
            },
        },
    },
});
```

**Lazy Loading Components:**
```vue
<script setup>
// Eager load (for critical components)
import StudentCard from '@/Components/Student/StudentCard.vue';

// Lazy load (for heavy/conditional components)
const ReportCardGenerator = defineAsyncComponent(() => 
  import('@/Components/Grade/ReportCardGenerator.vue')
);

const PaymentReportChart = defineAsyncComponent(() => 
  import('@/Components/Payment/PaymentReportChart.vue')
);
</script>

<template>
  <StudentCard :student="student" />
  
  <!-- Only load when needed -->
  <Suspense v-if="showReportGenerator">
    <template #default>
      <ReportCardGenerator />
    </template>
    <template #fallback>
      <LoadingSpinner />
    </template>
  </Suspense>
</template>
```

**Image Optimization:**
```php
// Automatic image optimization with Spatie Media Library
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Student extends Model implements HasMedia
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->format('webp')
            ->performOnCollections('student_photos');

        $this->addMediaConversion('medium')
            ->width(500)
            ->height(500)
            ->format('webp')
            ->performOnCollections('student_photos');
    }
}

// Usage
$student->getFirstMediaUrl('student_photos', 'thumb');
```

**Debouncing & Throttling:**
```vue
<script setup>
import { ref } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { router } from '@inertiajs/vue3';

const searchQuery = ref('');

const handleSearch = useDebounceFn(() => {
  router.get('/students', 
    { search: searchQuery.value },
    { preserveState: true, replace: true }
  );
}, 500); // Wait 500ms after user stops typing
</script>

<template>
  <input 
    v-model="searchQuery" 
    @input="handleSearch" 
    placeholder="Cari siswa..."
  />
</template>
```

### 8.3 Queue Configuration

**Queue Jobs:**
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],

'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
    'database' => env('DB_CONNECTION', 'mysql'),
    'table' => 'failed_jobs',
],
```

**Job Priority:**
```php
// High priority queue for critical tasks
class SendPaymentReminder implements ShouldQueue
{
    public $queue = 'notifications';
    public $tries = 3;
    public $timeout = 60;
    public $backoff = [60, 300, 900]; // 1m, 5m, 15m

    public function handle()
    {
        // Send WhatsApp reminder
    }
}

// Low priority for bulk operations
class GenerateMonthlyReport implements ShouldQueue
{
    public $queue = 'reports';
    public $tries = 1;
    public $timeout = 300;
}
```

**Queue Workers:**
```bash
# Supervisor configuration
[program:laravel-worker-default]
command=php /var/www/html/artisan queue:work redis --queue=default --sleep=3 --tries=3 --max-time=3600
process_name=%(program_name)s_%(process_num)02d
numprocs=2
autostart=true
autorestart=true

[program:laravel-worker-notifications]
command=php /var/www/html/artisan queue:work redis --queue=notifications --sleep=1 --tries=3
process_name=%(program_name)s_%(process_num)02d
numprocs=1
autostart=true
autorestart=true
```

---

## 9. Deployment Architecture

### 9.1 Server Requirements

**Minimum (MVP - 200 Users):**
```
CPU: 2 cores (vCPU)
RAM: 4GB
Storage: 40GB SSD
Bandwidth: 100GB/month
Database: MySQL 8.0+ or PostgreSQL 14+
PHP: 8.2+
Node.js: 18+ (for build process)
Redis: 6+
```

**Recommended (Production):**
```
Web Server: 
  - CPU: 4 cores
  - RAM: 8GB
  - Storage: 80GB SSD

Database Server (separate):
  - CPU: 2 cores
  - RAM: 4GB
  - Storage: 50GB SSD (with auto-scaling)

Redis Cache:
  - RAM: 1GB
  - Managed service recommended (Redis Cloud, AWS ElastiCache)
```

### 9.2 Deployment Options

**Option 1: Shared Hosting (Budget-Friendly)**
```
Platform: cPanel / Plesk
Pros:
  - Low cost (~$5-10/month)
  - Easy setup
  - Pre-configured PHP, MySQL

Cons:
  - Limited control
  - No SSH access (some hosts)
  - Cannot run queue workers easily
  - Limited scalability

Recommended for: Testing/Demo phase only
```

**Option 2: VPS/Cloud (Recommended for MVP)**
```
Platforms:
  - DigitalOcean Droplet ($12-24/month)
  - Vultr ($12-24/month)
  - Linode ($12-24/month)
  - AWS Lightsail ($20-40/month)
  - IDCloudHost (Indonesia) (~Rp 150k-300k/month)

Pros:
  - Full control (root access)
  - Can run queue workers
  - SSH access
  - Good performance

Cons:
  - Need to manage server (updates, security)
  - Requires DevOps knowledge

Recommended for: MVP & Production (Phase 1)
```

**Option 3: Platform-as-a-Service (Easy Deploy)**
```
Platforms:
  - Laravel Forge + DigitalOcean (~$15 + $12/month)
  - Ploi.io + VPS (~$11 + $12/month)
  - Heroku (free tier limited, paid $7+/month)
  - Railway.app ($5-20/month)
  - Vercel (frontend) + PlanetScale (database)

Pros:
  - Zero-downtime deployment
  - Automatic SSL
  - Easy scaling
  - One-click rollback

Cons:
  - Additional cost for management layer
  - Vendor lock-in (some platforms)

Recommended for: Production (Phase 2) or if budget allows
```

**Option 4: Containerized (Advanced)**
```
Platform: Docker + Kubernetes (AWS ECS, Google Cloud Run)

Pros:
  - Highly scalable
  - Isolated environments
  - Easy replication

Cons:
  - Overkill for small school
  - Complex setup
  - Higher cost

Recommended for: Future (Phase 3+) if school grows significantly
```

### 9.3 Deployment Flow

**Development → Staging → Production**

```
┌─────────────────┐
│  Local Dev      │  Developer machine
│  (Vite HMR)     │  → yarn dev
└────────┬────────┘
         │ git push to 'develop' branch
         ↓
┌─────────────────┐
│  Staging Server │  Testing environment
│  (staging.app)  │  → git pull, composer install, npm build
└────────┬────────┘
         │ git merge to 'main' branch
         ↓
┌─────────────────┐
│ Production      │  Live environment
│ (sekolah.app)   │  → Automated deploy via CI/CD
└─────────────────┘
```

**Deployment Script (Zero-Downtime):**
```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting deployment..."

# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Run migrations
php artisan migrate --force

# 4. Clear & cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart queue workers
php artisan queue:restart

# 6. Reload PHP-FPM (if using)
sudo systemctl reload php8.2-fpm

# 7. Clear application cache
php artisan cache:clear

echo "✅ Deployment complete!"
```

**GitHub Actions CI/CD:**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
    
    - name: Install Dependencies
      run: composer install --no-dev --optimize-autoloader
    
    - name: Run Tests
      run: php artisan test
    
    - name: Build Frontend
      run: |
        npm install
        npm run build
    
    - name: Deploy to Server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SERVER_SSH_KEY }}
        script: |
          cd /var/www/html
          ./deploy.sh
```

### 9.4 Environment Configuration

**.env.example (Template for Production):**
```env
APP_NAME="Sistem Manajemen Sekolah"
APP_ENV=production
APP_KEY=base64:xxxxx
APP_DEBUG=false
APP_URL=https://sekolah.app

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_db
DB_USERNAME=school_user
DB_PASSWORD=strong_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=sekolah@example.com
MAIL_PASSWORD=email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=sekolah@example.com
MAIL_FROM_NAME="${APP_NAME}"

# WhatsApp API
WHATSAPP_API_URL=https://api.fonnte.com
WHATSAPP_API_KEY=your_fonnte_key

# Payment Gateway (Phase 2)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

# School-specific
SCHOOL_NAME="SD Example"
SCHOOL_TIMEZONE=Asia/Jakarta
SCHOOL_ACADEMIC_YEAR_ACTIVE=2024/2025
```

---

## 10. Development Workflow

### 10.1 Git Branching Strategy

**Git Flow (Simplified):**
```
main (production)
  ├── staging (testing)
  │   ├── develop (integration)
  │   │   ├── feature/student-management
  │   │   ├── feature/payment-system
  │   │   ├── feature/attendance
  │   │   └── hotfix/bug-fix-nis-generation
```

**Branch Naming Convention:**
```
feature/module-name       → New feature
bugfix/issue-description  → Bug fix
hotfix/critical-fix       → Production hotfix
refactor/improvement      → Code refactoring
```

**Commit Message Convention:**
```
feat(students): add bulk class promotion
fix(payments): correct SPP calculation
refactor(attendance): optimize query performance
docs(readme): update deployment instructions
style(ui): improve mobile responsiveness
test(grades): add unit tests for grade calculation
```

### 10.2 Code Review Checklist

**Before Pull Request:**
- [ ] Code follows PSR-12 standards (run `composer lint`)
- [ ] No `dd()`, `var_dump()`, or console.log() left
- [ ] All tests pass (`php artisan test`)
- [ ] No N+1 queries (check with Laravel Debugbar)
- [ ] Proper validation in Form Requests
- [ ] Authorization via Policies
- [ ] Error handling with try-catch where needed
- [ ] Comments for complex logic
- [ ] Mobile-responsive UI (test on 360px width)

**Pull Request Template:**
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] New feature
- [ ] Bug fix
- [ ] Refactoring
- [ ] Documentation

## Testing
- [ ] Manual testing done
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated

## Screenshots (if UI changes)
[Add screenshots]

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review done
- [ ] No console errors
- [ ] Mobile-responsive
```

### 10.3 Local Development Setup

**Prerequisites:**
```bash
# Install tools
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+ or PostgreSQL
- Redis (optional for local, recommended)
```

**Setup Steps:**
```bash
# 1. Clone repository
git clone https://github.com/school/sistem-sekolah.git
cd sistem-sekolah

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Run migrations & seeders
php artisan migrate:fresh --seed

# 6. Create storage link
php artisan storage:link

# 7. Start development server
php artisan serve &
npm run dev

# Open: http://localhost:8000
# Default login: admin@example.com / password
```

**Database Seeding:**
```php
// database/seeders/DemoDataSeeder.php
class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin TU',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create teacher
        $teacher = User::create([
            'name' => 'Guru Matematika',
            'email' => 'guru@example.com',
            'username' => 'guru',
            'password' => Hash::make('password'),
        ]);
        $teacher->assignRole('teacher');

        // Create academic year
        $academicYear = AcademicYear::create([
            'name' => '2024/2025',
            'semester' => 1,
            'start_date' => '2024-07-15',
            'end_date' => '2024-12-20',
            'is_active' => true,
        ]);

        // Create classes
        $class1A = SchoolClass::create([
            'name' => '1A',
            'level' => 1,
            'academic_year_id' => $academicYear->id,
            'capacity' => 30,
        ]);

        // Create 30 students with factory
        Student::factory(30)->create([
            'class_id' => $class1A->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Create payments, grades, etc.
    }
}
```

---

## 11. Testing Strategy

### 11.1 Testing Pyramid

```
           ┌──────────────┐
           │   E2E Tests  │  (10%)
           │  (Dusk/Pest) │
           └──────────────┘
         ┌──────────────────┐
         │ Feature Tests    │  (30%)
         │ (HTTP/Inertia)   │
         └──────────────────┘
      ┌────────────────────────┐
      │    Unit Tests          │  (60%)
      │  (Service/Model)       │
      └────────────────────────┘
```

### 11.2 Unit Tests

**Example: StudentService Test**
```php
// tests/Unit/Services/StudentServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StudentService;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StudentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StudentService::class);
    }

    /** @test */
    public function it_generates_unique_nis()
    {
        $nis1 = $this->service->generateNIS(2024);
        $nis2 = $this->service->generateNIS(2024);

        $this->assertEquals('20240001', $nis1);
        $this->assertEquals('20240002', $nis2);
    }

    /** @test */
    public function it_creates_student_with_parent_account()
    {
        $data = [
            'name' => 'Test Student',
            'nik' => '1234567890123456',
            'nisn' => '1234567890',
            'gender' => 'L',
            'birth_place' => 'Jakarta',
            'birth_date' => '2015-05-15',
            'religion' => 'Islam',
            'address' => 'Jl. Test',
            'class_id' => SchoolClass::factory()->create()->id,
            'academic_year_id' => AcademicYear::factory()->create()->id,
            'enrollment_year' => 2024,
            'parent_data' => [
                'father_name' => 'Bapak Test',
                'phone' => '081234567890',
                'email' => 'parent@test.com',
            ],
        ];

        $student = $this->service->createStudent($data);

        $this->assertDatabaseHas('students', [
            'name' => 'Test Student',
            'nik' => '1234567890123456',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => '081234567890',
            'role' => 'parent',
        ]);

        $this->assertNotNull($student->parent_account_id);
    }
}
```

### 11.3 Feature Tests

**Example: Student Management**
```php
// tests/Feature/StudentManagementTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_student_list()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Student::factory(5)->create();

        $response = $this->actingAs($admin)
            ->get('/students');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Students/Index')
                ->has('students.data', 5)
        );
    }

    /** @test */
    public function admin_can_create_student()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->post('/students', [
                'name' => 'New Student',
                'nik' => '1234567890123456',
                'nisn' => '1234567890',
                // ... other fields
            ]);

        $response->assertRedirect('/students');
        $this->assertDatabaseHas('students', ['name' => 'New Student']);
    }

    /** @test */
    public function teacher_cannot_create_student()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $response = $this->actingAs($teacher)
            ->post('/students', ['name' => 'Test']);

        $response->assertStatus(403);
    }

    /** @test */
    public function parent_can_only_view_own_child()
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        $ownChild = Student::factory()->create([
            'parent_account_id' => $parent->id,
        ]);

        $otherChild = Student::factory()->create();

        // Can view own child
        $response = $this->actingAs($parent)
            ->get("/students/{$ownChild->id}");
        $response->assertStatus(200);

        // Cannot view other child
        $response = $this->actingAs($parent)
            ->get("/students/{$otherChild->id}");
        $response->assertStatus(403);
    }
}
```

### 11.4 Browser Tests (Optional)

**Example: Login Flow**
```php
// tests/Browser/LoginTest.php
namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /** @test */
    public function user_can_login_successfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('username', 'admin')
                ->type('password', 'password')
                ->press('Masuk')
                ->assertPathIs('/dashboard')
                ->assertSee('Dashboard');
        });
    }

    /** @test */
    public function user_sees_error_with_wrong_credentials()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('username', 'admin')
                ->type('password', 'wrong')
                ->press('Masuk')
                ->assertPathIs('/login')
                ->assertSee('Username atau password salah');
        });
    }
}
```

---

## 12. Monitoring & Maintenance

### 12.1 Logging Strategy

**Laravel Logging Channels:**
```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],

    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'level' => 'critical',
    ],

    'payment' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payment.log'),
        'level' => 'info',
        'days' => 90,
    ],
],
```

**Custom Logging:**
```php
// Log payment transactions
Log::channel('payment')->info('Payment recorded', [
    'payment_id' => $payment->id,
    'student_id' => $payment->student_id,
    'amount' => $payment->amount,
    'user_id' => auth()->id(),
]);

// Log errors with context
try {
    $this->generateReportCard($student);
} catch (\Exception $e) {
    Log::error('Report card generation failed', [
        'student_id' => $student->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    throw $e;
}
```

### 12.2 Error Tracking

**Sentry Integration:**
```bash
composer require sentry/sentry-laravel
```

```php
// config/sentry.php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),
    'environment' => env('APP_ENV', 'production'),
];

// .env
SENTRY_LARAVEL_DSN=https://xxxxx@sentry.io/xxxxx
```

### 12.3 Performance Monitoring

**Laravel Telescope (Development):**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Laravel Horizon (Queue Monitoring):**
```bash
composer require laravel/horizon
php artisan horizon:install
```

Access at: `/horizon`

### 12.4 Backup Strategy

**Automated Database Backup:**
```bash
# Install package
composer require spatie/laravel-backup

# config/backup.php
'backup' => [
    'name' => 'school_backup',
    'source' => [
        'files' => [
            'include' => [
                storage_path('app/private'),
                storage_path('app/public'),
            ],
        ],
        'databases' => ['mysql'],
    ],
    'destination' => [
        'disks' => ['local', 's3'], // Store locally & cloud
    ],
],

# Schedule in Kernel
protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:run')->daily()->at('02:00');
    $schedule->command('backup:clean')->daily()->at('03:00');
}
```

### 12.5 Health Checks

**Health Check Endpoint:**
```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'failed',
        'cache' => Cache::has('health_check') ? 'working' : 'failed',
        'queue' => Queue::size() !== false ? 'working' : 'failed',
    ]);
});
```

**Uptime Monitoring:**
- Use UptimeRobot (free) or Pingdom
- Monitor `/health` endpoint every 5 minutes
- Send alert if down

---

## 13. Security Checklist

**Pre-Deployment:**
- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secure
- [ ] HTTPS enabled (SSL certificate)
- [ ] CORS configured properly
- [ ] Rate limiting enabled
- [ ] File upload validation
- [ ] XSS protection (auto in Laravel/Vue)
- [ ] CSRF protection enabled
- [ ] SQL injection prevention (Eloquent ORM)
- [ ] Sensitive data encrypted
- [ ] Error pages don't expose stack traces
- [ ] Regular security updates (composer update, npm update)

---

## 14. Cost Estimation

**Monthly Operational Cost (MVP):**
```
VPS/Cloud Server: $12-24/month
Domain (.id): ~Rp 200k/year (~Rp 17k/month)
SSL Certificate: Free (Let's Encrypt)
WhatsApp API: ~Rp 50k-150k/month (depends on volume)
Email Service: Free tier (Gmail SMTP) or ~$10/month (SendGrid)
Backups: Free (local) or ~$5/month (S3)
Monitoring: Free (UptimeRobot, Sentry free tier)

Total Estimate: $15-40/month (~Rp 240k-640k/month)
```

**Phase 2 Additional Costs:**
```
Payment Gateway: 1-2% per transaction + ~Rp 2000/transaction
Redis Managed Service: $10-15/month (optional)
CDN (Cloudflare): Free or $20/month for pro
Database managed service: $15-30/month (optional)
```

---

## 15. Conclusion & Next Steps

### Recommended Tech Stack Summary:
✅ **Laravel 10** - Robust, mature, excellent documentation  
✅ **Inertia.js** - Perfect for admin panel, no API overhead  
✅ **Vue.js 3** - Modern, reactive, easy to learn  
✅ **Tailwind CSS + DaisyUI** - Rapid UI development, maintainable  
✅ **MySQL** - Reliable, widely supported  
✅ **Redis** - Essential for caching & queues  

### Implementation Timeline:
**Sprint 1-2 (Weeks 1-6):** Auth, Student Management, Settings  
**Sprint 3-4 (Weeks 7-12):** Attendance, Payment System  
**Sprint 5-6 (Weeks 13-18):** Grades, Report Cards, Dashboard  
**Sprint 7-8 (Weeks 19-23):** PSB, Teacher Management, Notifications  

### Success Criteria:
- All MVP features functional
- Performance: Page load < 3s
- Mobile-responsive (tested on Chrome Mobile)
- Security audit passed
- User training completed
- Data migration from manual system (if applicable)

---

**Document Prepared By:** Zulfikar Hidayatullah  
**Contact:** +62 857-1583-8733  
**Date:** 13 Desember 2025  
**Version:** 1.0 - Ready for Development
