# Feature: AUTH-P0 - Authentication & Authorization System

> **Code:** AUTH-P0 | **Priority:** Critical | **Status:** ✅ Complete
> **Sprint:** 1-1 | **Menu:** Login, Dashboard

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=login`
- [x] Service methods match Controller calls
- [x] Tested with automated tests (5 tests passed, 21 assertions)
- [x] Vue pages exist for Inertia renders
- [x] Migrations applied (4 migrations ran)
- [x] Following DOCUMENTATION_GUIDE.md template

---

## Overview

Authentication & Authorization System merupakan fitur security fundamental yang bertujuan untuk mengelola akses user ke aplikasi, yaitu: validasi credentials, role-based access control (RBAC), activity logging untuk audit trail, dan brute force protection dengan automatic account locking setelah 5 percobaan login gagal.

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| AUTH-01 | User | Login menggunakan username atau email | Saya bisa akses sistem dengan fleksibel | ✅ Complete |
| AUTH-02 | User | See password yang saya ketik | Saya bisa verify sebelum submit | ✅ Complete |
| AUTH-03 | User | Diingatkan dengan "Remember Me" | Saya tidak perlu login ulang | ✅ Complete |
| AUTH-04 | User | Logout dengan aman | Session saya terhapus dan aman | ✅ Complete |
| AUTH-05 | Admin | Lihat dashboard sesuai role | Saya hanya lihat menu yang relevan | ✅ Complete |
| AUTH-06 | System | Block user setelah 5x failed login | Mencegah brute force attack | ✅ Complete |
| AUTH-07 | System | Log semua aktivitas user | Audit trail untuk compliance | ✅ Complete |
| AUTH-08 | System | Track last login time dan IP | Monitoring keamanan | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-01 | User hanya bisa login dengan status `active` | CheckRole middleware |
| BR-02 | Setelah 5x failed login, account locked selama 15 menit | FailedLoginAttempt model |
| BR-03 | Password harus di-hash dengan bcrypt | Hash::make() di User model |
| BR-04 | Session harus di-regenerate setelah login | $request->session()->regenerate() |
| BR-05 | Activity logging untuk POST/PUT/PATCH/DELETE | LogActivity middleware |
| BR-06 | Role menentukan dashboard redirect destination | getDashboardRoute() method |

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| Controller | `app/Http/Controllers/Auth/LoginController.php` | Handle login/logout dengan rate limiting |
| Middleware | `app/Http/Middleware/CheckRole.php` | Role-based access control |
| Middleware | `app/Http/Middleware/LogActivity.php` | Activity logging untuk audit trail |
| Model | `app/Models/User.php` | User entity dengan relationships |
| Model | `app/Models/ActivityLog.php` | Activity tracking |
| Model | `app/Models/FailedLoginAttempt.php` | Failed login tracking |
| Model | `app/Models/PasswordHistory.php` | Password history untuk reuse prevention |
| Routes | `resources/js/routes/index.ts` | Auto-generated Wayfinder routes (type-safe) |
| Routes | `resources/js/routes/login/index.ts` | Login route helpers |
| Page | `resources/js/pages/Auth/Login.vue` | Login form dengan iOS design |
| Layout | `resources/js/components/layouts/AppLayout.vue` | Main app layout dengan navigation |
| Dashboard | `resources/js/pages/Dashboard/*.vue` | Role-specific dashboards |

### Routes Summary

| Group | Count | Prefix | Authentication Required |
|-------|-------|--------|------------------------|
| Authentication | 3 | `/login`, `/logout` | Guest (login), Auth (logout) |
| Universal Redirect | 1 | `/dashboard` | All authenticated users |
| Admin Dashboard | 1 | `/admin/dashboard` | SUPERADMIN, ADMIN |
| Principal Dashboard | 1 | `/principal/dashboard` | PRINCIPAL |
| Teacher Dashboard | 1 | `/teacher/dashboard` | TEACHER |
| Parent Dashboard | 1 | `/parent/dashboard` | PARENT |
| Student Dashboard | 1 | `/student/dashboard` | STUDENT |

**Total Auth Routes:** 9 routes (excluding public routes, health check, dan internal routes)

**Routing System:** Laravel Wayfinder - Type-safe auto-generated routes di `resources/js/routes/`

> 📡 Full API documentation: [Authentication API](../../api/authentication.md)

### Database

> 📌 Lihat migrasi lengkap:
> - `2025_12_22_085442_add_auth_fields_to_users_table.php`
> - `2025_12_22_085443_create_activity_logs_table.php`
> - `2025_12_22_085445_create_failed_login_attempts_table.php`
> - `2025_12_22_085446_create_password_histories_table.php`

#### Key Tables:
- **users**: Core user data dengan role, status, last_login tracking
- **activity_logs**: Audit trail untuk semua aktivitas
- **failed_login_attempts**: Brute force protection
- **password_histories**: Password reuse prevention (ready for P1)

## Data Structures

```typescript
// Login Request
interface LoginRequest {
    identifier: string;  // username atau email
    password: string;
    remember: boolean;
}

// User Authentication State
interface AuthUser {
    id: number;
    name: string;
    username: string;
    email: string;
    role: 'SUPERADMIN' | 'PRINCIPAL' | 'ADMIN' | 'TEACHER' | 'PARENT' | 'STUDENT';
    status: 'active' | 'inactive';
    is_first_login: boolean;
    last_login_at: string | null;
    last_login_ip: string | null;
    phone_number: string | null;
}

// Dashboard Stats (berbeda per role)
interface DashboardStats {
    // Admin/TU
    total_students?: number;
    total_payments?: number;
    pending_psb?: number;
    total_users?: number;
    
    // Principal
    total_teachers?: number;
    total_classes?: number;
    attendance_rate?: number;
    
    // Teacher
    my_classes?: number;
    pending_grades?: number;
    today_schedule?: any[];
    
    // Parent
    children?: any[];
    pending_payments?: number;
    recent_grades?: any[];
    attendance_summary?: any[];
}
```

## UI/UX Specifications

### Login Page
- **Layout**: Centered card dengan gradient background
- **Glass Effect**: `bg-white/80 backdrop-blur-xl` untuk modern iOS-like feel
- **Form Fields**: 
  - Identifier (username/email) dengan validation
  - Password dengan show/hide toggle
  - Remember me checkbox
- **Loading State**: Spinner dengan disabled button
- **Error Display**: Inline error messages dalam Bahasa Indonesia
- **Responsive**: Mobile-first design (375px - desktop)

### Dashboard Layout
- **Navigation**: Sticky top bar dengan glass effect
- **Menu**: Dynamic menu berdasarkan user role
- **Mobile Menu**: Slide-in menu dengan backdrop overlay
- **User Info**: Display name dan role di top-right
- **Logout**: Confirmation dialog sebelum logout

### Design System
- **Colors**: Blue-Indigo gradient untuk primary actions
- **Typography**: System fonts dengan scale 3xl (title) - base (body)
- **Spacing**: Consistent padding 6-8, gap 4-6
- **Shadows**: 2xl untuk cards, lg untuk hover states
- **Transitions**: Smooth 200ms duration untuk all interactions

## Edge Cases & Handling

| Scenario | Expected Behavior | Implementation |
|----------|------------------|----------------|
| Login dengan inactive user | Error: "Akun telah dinonaktifkan" | `isActive()` check di LoginController |
| 5x failed login | Account locked 15 menit | FailedLoginAttempt::isLocked() |
| Login saat sudah locked | Error dengan remaining minutes | Countdown display |
| Login dengan email | Berhasil login | `orWhere('email', $identifier)` |
| Login dengan username | Berhasil login | `where('username', $identifier)` |
| Logout tanpa confirmation | Logout langsung | router.post('/logout') |
| Access unauthorized page | Redirect ke 403 error | CheckRole middleware abort(403) |
| First login flag true | Redirect ke first-login page (P1) | is_first_login check |
| Session expired | Redirect ke login | Auth middleware |
| Invalid credentials | Error: "Username/email atau password salah" | Hash::check() validation |

## Testing

### Automated Tests

| Test ID | Scenario | Type | File |
|---------|----------|------|------|
| AUTH-T01 | Login page accessibility | Feature | `tests/Feature/Auth/LoginTest.php` |
| AUTH-T02 | Valid credentials login | Feature | `tests/Feature/Auth/LoginTest.php` |
| AUTH-T03 | Invalid credentials rejection | Feature | `tests/Feature/Auth/LoginTest.php` |
| AUTH-T04 | Inactive user prevention | Feature | `tests/Feature/Auth/LoginTest.php` |
| AUTH-T05 | Logout functionality | Feature | `tests/Feature/Auth/LoginTest.php` |

**Test Results**: ✅ 5 tests passed, 21 assertions, 0.29s duration

### Quick Verification

- [x] Desktop responsive (1920px, 1366px, 1024px)
- [x] Mobile responsive (375px, 414px)
- [x] Loading states shown
- [x] Error handling untuk invalid credentials
- [x] Error handling untuk inactive user
- [x] Error handling untuk account locked
- [x] Show/hide password toggle
- [x] Remember me functionality
- [x] Logout confirmation (implemented in AppLayout)
- [x] Role-based redirect ke dashboard

> 📋 Full test plan: [AUTH-P0 Test Plan](../../testing/AUTH-P0-test-plan.md)

## Security Considerations

| Concern | Mitigation | Implementation |
|---------|-----------|----------------|
| Brute Force Attack | Rate limiting dengan lock setelah 5 attempts | FailedLoginAttempt model |
| Password Exposure | Hash dengan bcrypt, hidden di response | Hash::make(), $hidden property |
| Session Hijacking | Session regeneration setelah login | $request->session()->regenerate() |
| SQL Injection | Eloquent ORM dengan parameterized queries | Eloquent Model |
| XSS Attack | Input validation, Vue automatic escaping | Form validation, v-model |
| CSRF Attack | CSRF token di semua forms | Laravel default protection |
| Unauthorized Access | Role-based middleware | CheckRole middleware |
| Activity Tracking | Log semua critical actions | LogActivity middleware |

## Performance Considerations

| Concern | Solution | Implementation |
|---------|----------|----------------|
| Failed login query overhead | Index pada identifier + ip_address | Migration unique index |
| Activity log table growth | Archive policy (future P2) | Indexed created_at column |
| Dashboard stats query | Eager loading untuk relationships | With(['relation']) |
| Session storage | Database session driver dengan cleanup | `config/session.php` |

## Default Test Accounts

| Role | Username | Email | Password |
|------|----------|-------|----------|
| SUPERADMIN | superadmin | superadmin@sekolah.app | Sekolah123 |
| PRINCIPAL | kepala.sekolah | kepala@sekolah.app | Sekolah123 |
| ADMIN | bu.siti | siti@sekolah.app | Sekolah123 |
| TEACHER | pak.budi | budi@sekolah.app | Sekolah123 |
| PARENT | ibu.ani | ani@parent.com | Sekolah123 |
| STUDENT | raka.pratama | raka@student.com | Sekolah123 |

## Related Documentation

- **API Documentation:** [Authentication API](../../api/authentication.md)
- **Test Plan:** [AUTH-P0 Test Plan](../../testing/AUTH-P0-test-plan.md)
- **User Journeys:** [Authentication User Journeys](../../guides/auth-user-journeys.md)
- **Database Schema:** Migration files in `database/migrations/`

## Next Phase (P1 - Important)

- [ ] First Login Flow - Force password change
- [ ] Forgot Password - Email reset link
- [ ] Profile Management - Update user info
- [ ] Password History Validation - Prevent 3 last passwords reuse
- [ ] User Management UI - CRUD untuk admin

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2025-12-22 | Initial P0 implementation - Login, Logout, RBAC, Activity Logging |
| 1.0.1 | 2025-12-23 | Route structure updated - Universal /dashboard redirect, Wayfinder migration |

## Update Triggers

Update dokumentasi ini ketika:
- [ ] Business rules berubah
- [ ] API contract berubah (endpoints, request/response)
- [ ] New edge cases ditemukan
- [ ] Security requirements berubah
- [ ] Dashboard routes berubah

---

*Last Updated: 2025-12-23*
*Documentation Status: ✅ Complete dengan verification*
*Implementation Status: ✅ Tested dan Production-Ready*

