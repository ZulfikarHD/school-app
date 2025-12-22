# 📚 Sistem Informasi Sekolah - Documentation

## Overview

Dokumentasi lengkap untuk Sistem Informasi Sekolah yang bertujuan untuk memudahkan development, maintenance, dan onboarding developer baru, yaitu: architecture documentation, feature specifications, API references, dan testing guidelines.

---

## 📂 Documentation Structure

```
docs/
├── README.md                     # Dokumen ini
├── DOCUMENTATION_GUIDE.md        # Panduan membuat dokumentasi
│
├── features/                     # Feature-specific documentation
│   └── auth/
│       └── AUTH-P0-authentication.md
│
├── api/                          # API endpoint documentation
│   └── authentication.md
│
└── testing/                      # Test plans dan QA checklists
    └── AUTH-P0-test-plan.md
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.4+
- Node.js 18+ dengan Yarn
- PostgreSQL atau MySQL
- Composer

### Setup Development Environment

```bash
# 1. Clone dan install dependencies
composer install
yarn install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Database setup
php artisan migrate
php artisan db:seed

# 4. Build frontend
yarn run build
# atau untuk development
yarn run dev

# 5. Start server
php artisan serve
```

---

## 📋 Implemented Features

### ✅ P0 - Critical Features (Sprint 1-1)

| Feature Code | Feature Name | Status | Documentation |
|--------------|--------------|--------|---------------|
| AUTH-P0 | Authentication & Authorization | ✅ Complete | [Feature Doc](./features/auth/AUTH-P0-authentication.md) |

**Included in AUTH-P0:**
- ✅ Login dengan username/email + password
- ✅ Logout dengan session cleanup
- ✅ Role-Based Access Control (6 roles)
- ✅ Brute Force Protection (5 attempts → 15 min lock)
- ✅ Activity Logging untuk audit trail
- ✅ iOS-inspired UI dengan glass effects
- ✅ Mobile-first responsive design
- ✅ Show/hide password toggle
- ✅ Remember me functionality

---

## 📡 API Documentation

| Resource | Endpoints | Documentation |
|----------|-----------|---------------|
| Authentication | Login, Logout, Dashboards | [API Doc](./api/authentication.md) |

### Available Routes

```bash
# View all routes
php artisan route:list

# Filter by prefix
php artisan route:list --path=login
php artisan route:list --path=admin
```

---

## 🧪 Testing

### Run Automated Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run with coverage
php artisan test --coverage
```

### Test Documentation

| Feature | Test Plan | Status |
|---------|-----------|--------|
| Authentication | [AUTH-P0 Test Plan](./testing/AUTH-P0-test-plan.md) | ✅ 5/5 passing |

---

## 🔐 Test Accounts

Default accounts available setelah seeding:

| Role | Username | Email | Password |
|------|----------|-------|----------|
| SUPERADMIN | superadmin | superadmin@sekolah.app | Sekolah123 |
| PRINCIPAL | kepala.sekolah | kepala@sekolah.app | Sekolah123 |
| ADMIN | bu.siti | siti@sekolah.app | Sekolah123 |
| TEACHER | pak.budi | budi@sekolah.app | Sekolah123 |
| PARENT | ibu.ani | ani@parent.com | Sekolah123 |
| STUDENT | raka.pratama | raka@student.com | Sekolah123 |

---

## 🏗️ Architecture

### Tech Stack

| Component | Technology | Version |
|-----------|------------|---------|
| Backend Framework | Laravel | 12.x |
| Frontend Framework | Vue.js | 3.x |
| Frontend Integration | Inertia.js | 2.x |
| CSS Framework | Tailwind CSS | 4.x |
| Database | PostgreSQL/MySQL | - |
| Package Manager | Yarn | Latest |
| PHP Version | PHP | 8.4+ |

### Application Structure

```
school-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/          # Authentication controllers
│   │   │   └── Dashboard/     # Dashboard controllers per role
│   │   └── Middleware/        # Custom middleware (CheckRole, LogActivity)
│   ├── Models/                # Eloquent models
│   └── Services/              # Business logic (future)
│
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
│
├── resources/
│   └── js/
│       ├── components/
│       │   └── layouts/       # Layout components
│       └── pages/             # Inertia pages
│           ├── Auth/          # Authentication pages
│           ├── Dashboard/     # Dashboard pages
│           └── Errors/        # Error pages (403, 404, etc)
│
├── routes/
│   └── web.php                # Web routes (Inertia)
│
├── tests/
│   └── Feature/
│       └── Auth/              # Authentication tests
│
└── docs/                      # Documentation (this folder)
```

### Design System

- **iOS-inspired Design**: Spring animations, glass effects, smooth transitions
- **Mobile-first**: Touch-optimized dengan min 44x44px touch targets
- **Dark Mode**: Full support dengan `dark:` Tailwind classes
- **Responsive**: 375px (mobile) - 1920px+ (desktop)
- **Color Palette**: Blue-Indigo gradients untuk primary actions
- **Typography**: System fonts dengan consistent scale

---

## 📝 Contributing

### Documentation Standards

Sebelum commit feature baru, **WAJIB** membuat dokumentasi:

1. **Feature Documentation** di `docs/features/{role}/{CODE}-feature.md`
2. **Test Plan** di `docs/testing/{CODE}-test-plan.md`
3. **API Documentation** di `docs/api/{resource}.md` (jika ada API)

> 📚 Baca [DOCUMENTATION_GUIDE.md](./DOCUMENTATION_GUIDE.md) untuk detail lengkap.

### Pre-Commit Checklist

- [ ] Code linting: `vendor/bin/pint` (PHP) dan `yarn run lint` (JS)
- [ ] Tests passing: `php artisan test`
- [ ] Frontend build: `yarn run build`
- [ ] Documentation updated
- [ ] Git commit message descriptive

---

## 🔍 Troubleshooting

### Common Issues

#### 1. Frontend Not Updating

```bash
# Clear cache dan rebuild
php artisan optimize:clear
yarn run build
```

#### 2. Session Issues

```bash
# Clear sessions
php artisan session:flush
```

#### 3. Migration Issues

```bash
# Fresh migration dengan seed
php artisan migrate:fresh --seed
```

#### 4. Permission Issues (Linux/Mac)

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

---

## 📚 Additional Resources

### Laravel Boost Tools

Project ini menggunakan Laravel Boost MCP server dengan tools:

- `list-routes` - View all routes
- `database-schema` - View database structure
- `read-log-entries` - Read application logs
- `tinker` - Execute PHP code in Laravel context
- `search-docs` - Search Laravel documentation
- `list-artisan-commands` - View available artisan commands

### External Documentation

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue 3 Documentation](https://vuejs.org/)
- [Tailwind CSS 4 Documentation](https://tailwindcss.com/docs)

---

## 📅 Roadmap

### Sprint 1-2: P1 Features (Important)

- [ ] First Login Flow - Force password change
- [ ] Forgot Password - Email reset link
- [ ] Profile Management - Update user info
- [ ] Password History Validation - Prevent reuse
- [ ] User Management UI - CRUD untuk admin
- [ ] Audit Log Viewer - View activity logs

### Sprint 2: P2 Features (Enhancement)

- [ ] Two-Factor Authentication (2FA)
- [ ] Session Management - Multi-device tracking
- [ ] Email Templates - Branded notifications
- [ ] Password Strength Meter
- [ ] Account Lockout Notifications

### Future Modules

- [ ] Student Management
- [ ] Payment Management
- [ ] PSB (Penerimaan Siswa Baru)
- [ ] Academic Management (Grades, Classes)
- [ ] Attendance System
- [ ] Reporting & Analytics

---

## 👤 Author

**Developer:** Zulfikar Hidayatullah  
**Contact:** +62 857-1583-8733  
**Timezone:** Asia/Jakarta (WIB)  
**Currency:** Rupiah (Rp)

---

## 📄 License

Private project - All rights reserved.

---

*Last Updated: 2025-12-22*  
*Documentation Version: 1.0*

