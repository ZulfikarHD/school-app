# Sistem Informasi Sekolah

Modern school management system dengan Laravel 12, Vue 3, dan Inertia.js yang bertujuan untuk digitalisasi proses administrasi sekolah, yaitu: student management, payment processing, academic tracking, dan PSB (Penerimaan Siswa Baru) dengan security-first approach dan iOS-inspired user experience.

---

## 🚀 Quick Start

### Prerequisites

- **PHP:** 8.4+
- **Node.js:** 18+ dengan Yarn
- **Database:** PostgreSQL atau MySQL
- **Composer:** Latest version

### Installation

```bash
# 1. Clone repository
git clone <repository-url>
cd school-app

# 2. Install dependencies
composer install
yarn install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate
php artisan db:seed

# 5. Build frontend assets
yarn run build

# 6. Start development server
php artisan serve
# Server running at http://localhost:8000
```

### Development Mode

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server (hot reload)
yarn run dev
```

---

## 🔐 Default Login Credentials

| Role | Username | Email | Password |
|------|----------|-------|----------|
| Admin | bu.siti | siti@sekolah.app | Sekolah123 |
| Principal | kepala.sekolah | kepala@sekolah.app | Sekolah123 |
| Teacher | pak.budi | budi@sekolah.app | Sekolah123 |
| Parent | ibu.ani | ani@parent.com | Sekolah123 |

> **Login URL:** http://localhost:8000/login

---

## 📋 Tech Stack

| Component | Technology | Version |
|-----------|------------|---------|
| Backend | Laravel | 12.x |
| Frontend | Vue.js | 3.x |
| UI Framework | Tailwind CSS | 4.x |
| Frontend Bridge | Inertia.js | 2.x |
| Database | PostgreSQL/MySQL | - |
| Package Manager | Yarn | Latest |
| PHP | - | 8.4+ |

---

## 📁 Project Structure

```
school-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # HTTP request handlers
│   │   │   ├── Auth/           # Authentication controllers
│   │   │   └── Dashboard/      # Dashboard controllers
│   │   └── Middleware/         # Custom middleware
│   └── Models/                 # Eloquent models
│
├── database/
│   ├── migrations/             # Database schema
│   └── seeders/                # Test data seeders
│
├── resources/
│   └── js/
│       ├── components/         # Vue components
│       │   └── layouts/        # Layout components
│       └── pages/              # Inertia pages
│           ├── Auth/           # Login, Register
│           ├── Dashboard/      # Role-specific dashboards
│           └── Errors/         # Error pages
│
├── routes/
│   └── web.php                 # Web routes
│
├── tests/
│   └── Feature/                # Feature tests
│
└── docs/                       # 📚 Full documentation
    ├── README.md               # Documentation index
    ├── features/               # Feature specifications
    ├── api/                    # API documentation
    └── testing/                # Test plans
```

---

## ✅ Implemented Features (Sprint 1-1)

### 🔐 Authentication & Authorization (AUTH-P0)

- ✅ Login dengan username atau email
- ✅ Logout dengan session cleanup
- ✅ Role-Based Access Control (6 roles: SUPERADMIN, PRINCIPAL, ADMIN, TEACHER, PARENT, STUDENT)
- ✅ Brute Force Protection (auto-lock setelah 5 failed attempts)
- ✅ Activity Logging untuk audit trail
- ✅ Show/hide password toggle
- ✅ Remember me functionality
- ✅ Mobile-first responsive design dengan iOS-inspired UI

**Dashboard per Role:**
- ✅ Admin Dashboard - Student, Payment, PSB, User stats
- ✅ Principal Dashboard - Overview sekolah
- ✅ Teacher Dashboard - Class management
- ✅ Parent Dashboard - Child information

> 📚 [Full Feature Documentation](./docs/features/auth/AUTH-P0-authentication.md)

---

## 🧪 Testing

### Run Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# With coverage
php artisan test --coverage
```

### Current Test Results

```
✅ PASS  Tests\Feature\Auth\LoginTest
  ✓ login page can be accessed
  ✓ user can login with valid credentials
  ✓ login fails with invalid credentials
  ✓ inactive user cannot login
  ✓ authenticated user can logout

Tests:    5 passed (21 assertions)
Duration: 0.29s
```

---

## 📡 Available Scripts

| Command | Description |
|---------|-------------|
| `php artisan serve` | Start Laravel development server |
| `yarn run dev` | Start Vite dev server dengan hot reload |
| `yarn run build` | Build production assets |
| `yarn run lint` | Run ESLint dan Prettier |
| `vendor/bin/pint` | Format PHP code dengan Laravel Pint |
| `php artisan test` | Run PHPUnit tests |
| `php artisan migrate` | Run database migrations |
| `php artisan db:seed` | Seed database dengan test data |
| `php artisan route:list` | List all registered routes |

---

## 🎨 Design System

### Core Principles

- **iOS-inspired**: Glass effects, smooth animations, spring physics
- **Mobile-first**: Touch-optimized (min 44x44px targets)
- **Responsive**: 375px (mobile) hingga 1920px+ (desktop)
- **Dark Mode**: Full support dengan automatic detection
- **Accessibility**: WCAG 2.1 compliant

### Color Palette

- **Primary**: Blue-Indigo gradient (`from-blue-600 to-indigo-600`)
- **Success**: Green (`text-green-600`)
- **Error**: Red (`text-red-600`)
- **Warning**: Yellow (`text-yellow-600`)

### Typography

- **Heading**: `text-3xl font-bold`
- **Body**: `text-base`
- **Small**: `text-sm`

---

## 📚 Documentation

Dokumentasi lengkap tersedia di folder `docs/`:

- **[Documentation Index](./docs/README.md)** - Overview dokumentasi
- **[Feature Docs](./docs/features/)** - Spesifikasi fitur per module
- **[API Docs](./docs/api/)** - Endpoint documentation
- **[Test Plans](./docs/testing/)** - Manual dan automated testing
- **[Documentation Guide](./docs/DOCUMENTATION_GUIDE.md)** - Panduan membuat dokumentasi

### Quick Links

| Document | Description |
|----------|-------------|
| [AUTH-P0 Feature](./docs/features/auth/AUTH-P0-authentication.md) | Authentication system specification |
| [AUTH-P0 Test Plan](./docs/testing/AUTH-P0-test-plan.md) | QA checklist dan automated tests |
| [Authentication API](./docs/api/authentication.md) | Login/logout API endpoints |

---

## 🗺️ Roadmap

### Sprint 1-2: P1 Features (Next)

- [ ] First Login Flow - Force password change
- [ ] Forgot Password - Email reset
- [ ] Profile Management
- [ ] User Management UI (CRUD)
- [ ] Audit Log Viewer

### Future Modules

- [ ] Student Management
- [ ] Payment Processing
- [ ] PSB (Penerimaan Siswa Baru)
- [ ] Academic Management (Grades, Classes)
- [ ] Attendance System
- [ ] Reports & Analytics

---

## 🐛 Troubleshooting

### Frontend tidak update setelah code change

```bash
# Clear cache
php artisan optimize:clear

# Rebuild assets
yarn run build
```

### Permission errors (Linux/Mac)

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

### Database issues

```bash
# Fresh migration dengan seed
php artisan migrate:fresh --seed
```

### View all routes

```bash
php artisan route:list
```

---

## 🤝 Contributing

### Before Commit

1. Run linting:
   ```bash
   vendor/bin/pint          # PHP
   yarn run lint            # JavaScript/Vue
   ```

2. Run tests:
   ```bash
   php artisan test
   ```

3. Update documentation jika ada perubahan feature/API

4. Follow [Documentation Guide](./docs/DOCUMENTATION_GUIDE.md)

### Git Workflow

- Ikuti konvensi di `docs-v1/guides/GIT_WORKFLOW.md`
- Commit message dalam Bahasa Indonesia yang jelas
- Reference issue/ticket jika ada

---

## 👤 Developer

**Name:** Zulfikar Hidayatullah  
**Contact:** +62 857-1583-8733  
**Timezone:** Asia/Jakarta (WIB)  
**Personality:** INFJ (Professional, detail-oriented)

---

## 📄 License

Private project - All rights reserved.

---

## 🙏 Acknowledgments

- Laravel ecosystem team
- Inertia.js team
- Vue.js core team
- Tailwind CSS team

---

*Last Updated: 2025-12-22*  
*Version: 1.0.0 (Sprint 1-1 Complete)*


