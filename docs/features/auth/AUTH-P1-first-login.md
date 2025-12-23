# Feature: AUTH-P1 - First Login Password Change

> **Code:** AUTH-P1 | **Priority:** High | **Status:** ✅ Complete
> **Sprint:** 1-2 | **Menu:** First Login (Auth Flow)

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=first-login` (2 routes found)
- [x] Service methods match Controller calls
- [x] Tested with automated tests (8 tests passed, 51 assertions)
- [x] Vue pages exist for Inertia renders (`FirstLogin.vue`)
- [x] Migrations applied (`add_auth_fields_to_users_table.php` includes `is_first_login`)
- [x] Following DOCUMENTATION_GUIDE.md template

---

## Overview

First Login Password Change merupakan fitur security mandatory yang bertujuan untuk memaksa user mengubah password default saat pertama kali login, yaitu: validasi password strength dengan requirements ketat, automatic redirect ke dashboard setelah berhasil, activity logging untuk compliance audit, dan flag `is_first_login` otomatis diubah menjadi `false` setelah password diganti.

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| FIRST-01 | New User | Dipaksa ubah password default saat first login | Password saya aman dan unik | ✅ Complete |
| FIRST-02 | New User | Lihat password requirements yang jelas | Saya tahu kriteria password yang valid | ✅ Complete |
| FIRST-03 | New User | Toggle show/hide password | Saya bisa verify password yang saya ketik | ✅ Complete |
| FIRST-04 | New User | Tidak bisa skip first login | System security terjaga | ✅ Complete |
| FIRST-05 | System | Log password change activity | Audit trail compliance | ✅ Complete |
| FIRST-06 | System | Redirect user ke dashboard sesuai role | User experience smooth setelah password change | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-01 | User dengan `is_first_login = true` WAJIB ubah password sebelum akses sistem | FirstLoginRequest authorization check |
| BR-02 | Password minimal 8 karakter dengan mixed case, numbers, dan symbols | Password::min(8)->mixedCase()->numbers()->symbols() |
| BR-03 | Password tidak boleh ada di leaked password database | Password::uncompromised() |
| BR-04 | Password confirmation harus cocok | 'confirmed' validation rule |
| BR-05 | Setelah berhasil, `is_first_login` flag diubah ke `false` | FirstLoginController@update |
| BR-06 | Activity log tercatat dengan action `first_login_password_change` | ActivityLog::create() |
| BR-07 | User di-redirect ke dashboard sesuai role setelah success | Role-based redirect logic |
| BR-08 | User yang sudah pernah login tidak bisa akses `/first-login` | Authorization return forbidden |

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| Controller | `app/Http/Controllers/Auth/FirstLoginController.php` | Handle show form & update password |
| Request | `app/Http/Requests/Auth/FirstLoginRequest.php` | Validation dengan strong password requirements |
| Model | `app/Models/User.php` | User entity dengan `is_first_login` flag |
| Model | `app/Models/ActivityLog.php` | Log password change untuk audit |
| Routes | `resources/js/routes/auth/first-login/index.ts` | Wayfinder routes (auto-generated) |
| Page | `resources/js/pages/Auth/FirstLogin.vue` | Form ubah password dengan iOS design |
| Migration | `2025_12_22_085442_add_auth_fields_to_users_table.php` | Includes `is_first_login` boolean field |

### Routes Summary

| Method | URI | Name | Middleware | Controller Method |
|--------|-----|------|------------|------------------|
| GET | `/first-login` | `auth.first-login` | `auth` | `FirstLoginController@show` |
| POST | `/first-login` | `auth.first-login.update` | `auth` | `FirstLoginController@update` |

**Total Routes:** 2 routes (protected dengan `auth` middleware)

**Routing System:** Laravel Wayfinder - Type-safe auto-generated routes

> 📡 Full API documentation: [Authentication API](../../api/authentication.md#first-login)

### Database

> 📌 Lihat migrasi lengkap:
> - `2025_12_22_085442_add_auth_fields_to_users_table.php` (column `is_first_login`)

#### Key Fields:
- **users.is_first_login**: Boolean flag untuk track first login status (default: `false`)
- **activity_logs**: Tercatat dengan action `first_login_password_change`

## Data Structures

```typescript
// First Login Request
interface FirstLoginRequest {
    password: string;               // New password (min 8 chars, mixed case, numbers, symbols)
    password_confirmation: string;  // Must match password
}

// User State Check
interface User {
    id: number;
    is_first_login: boolean;  // true = harus ubah password, false = sudah pernah login
    role: string;             // Untuk determine dashboard redirect
}

// Activity Log Entry
interface ActivityLog {
    user_id: number;
    action: 'first_login_password_change';
    ip_address: string;
    user_agent: string;
    status: 'success';
    description: string;
    created_at: DateTime;
}
```

## UI/UX Specifications

### First Login Page

**Layout:** Full-screen centered card dengan gradient background

**Components:**
1. **Header Section**
   - Icon: Key icon dalam circle dengan blur background
   - Title: "Login Pertama"
   - Subtitle: "Silakan ubah password Anda"

2. **Welcome Message**
   - Background: Blue 50 dengan border
   - Text: "Selamat datang, **[Nama User]**!"
   - Info: "Untuk keamanan akun, harap ubah password default Anda sebelum melanjutkan."

3. **Password Requirements Box**
   - Background: Amber 50 (warning color)
   - Title: "Persyaratan Password:"
   - Checklist with icons:
     - ✓ Minimal 8 karakter
     - ✓ Mengandung huruf besar dan kecil
     - ✓ Mengandung angka dan simbol

4. **Form Fields**
   - **Password Baru** (required)
     - Type: password (dengan toggle show/hide)
     - Placeholder: "Masukkan password baru"
     - Icon: Eye/EyeOff toggle button
   
   - **Konfirmasi Password** (required)
     - Type: password (dengan toggle show/hide)
     - Placeholder: "Masukkan ulang password baru"
     - Icon: Eye/EyeOff toggle button

5. **Submit Button**
   - Text: "Simpan & Lanjutkan" (idle), "Menyimpan..." (loading)
   - Style: Blue gradient dengan spring animation & press feedback
   - Loading state: Spinner icon saat processing

6. **Footer**
   - Text: "Password Anda akan di-enkripsi dan disimpan dengan aman"
   - Style: Gray text, small size

**Animations:**
- Page entrance: Spring animation (scale 0.95 → 1, opacity 0 → 1)
- Button press: Scale 0.97 dengan spring
- Haptic feedback: Medium saat submit, light saat toggle password visibility

**Responsive:**
- Mobile: Full width dengan padding 1rem
- Desktop: Max width 28rem (448px)

## Edge Cases & Error Handling

| Scenario | Expected Behavior | Status |
|----------|-------------------|--------|
| User sudah pernah login (`is_first_login = false`) | Redirect ke dashboard, forbidden jika paksa akses endpoint | ✅ Handled |
| Guest user akses `/first-login` | Redirect ke login page | ✅ Handled |
| Password < 8 karakter | Error: "Password minimal harus 8 karakter." | ✅ Handled |
| Password tanpa uppercase | Error dari Laravel Password validator | ✅ Handled |
| Password tanpa lowercase | Error dari Laravel Password validator | ✅ Handled |
| Password tanpa angka | Error dari Laravel Password validator | ✅ Handled |
| Password tanpa simbol | Error dari Laravel Password validator | ✅ Handled |
| Password in leaked database | Error dari Laravel Password::uncompromised() | ✅ Handled |
| Password confirmation tidak cocok | Error: "Konfirmasi password tidak cocok." | ✅ Handled |
| User logout di tengah first login | Session clear, redirect ke login | ✅ Handled |
| Network error saat submit | Inertia error handling, retry allowed | ✅ Handled |

## Security Considerations

1. **Authorization Check**
   - Only users with `is_first_login = true` can access endpoint
   - Verified in `FirstLoginRequest::authorize()` method

2. **Password Strength**
   - Minimum 8 characters
   - Must contain uppercase and lowercase letters
   - Must contain at least one number
   - Must contain at least one symbol
   - Must not appear in leaked password database (haveibeenpwned.com)

3. **Activity Logging**
   - Every password change is logged with user_id, IP, user agent, timestamp
   - Action type: `first_login_password_change`
   - Status: `success` (only logged on success)

4. **Password Hashing**
   - Bcrypt hashing with Laravel's `Hash::make()`
   - Cost factor: default Laravel config (10-12 rounds)

5. **Session Security**
   - Session continues after password change (no re-login required)
   - User automatically proceeds to their dashboard

## Testing Coverage

> 📋 Full test plan: [AUTH-P1 Test Plan](../../testing/AUTH-P1-first-login-test-plan.md)

### Automated Tests (8 tests, 51 assertions)

| Test Case | Status | Assertions |
|-----------|--------|-----------|
| First login user can access page | ✅ Pass | 6 |
| Non-first login user redirects to dashboard | ✅ Pass | 1 |
| Guest cannot access page | ✅ Pass | 1 |
| User can change password with valid data | ✅ Pass | 5 |
| Password must meet all requirements | ✅ Pass | 5 |
| Password confirmation must match | ✅ Pass | 1 |
| Non-first login user cannot update password | ✅ Pass | 1 |
| Redirects to correct dashboard based on role | ✅ Pass | 31 |

**Test File:** `tests/Feature/Auth/FirstLoginTest.php`

**Coverage:** 100% of happy paths + critical edge cases

## Integration Points

### Called By:
- `LoginController@login` (line 96-97): Redirects to first-login jika `is_first_login = true`

### Calls To:
- `User::update()`: Update password dan set `is_first_login = false`
- `ActivityLog::create()`: Log password change activity
- Dashboard routes: Role-based redirect setelah success

### Data Flow:
```
LoginController (detect is_first_login = true)
    ↓
Redirect to /first-login
    ↓
FirstLoginController@show (render form)
    ↓
User submit new password
    ↓
FirstLoginRequest (validate password strength)
    ↓
FirstLoginController@update:
    - Hash password dengan bcrypt
    - Update user.password
    - Set user.is_first_login = false
    - Log to activity_logs
    ↓
Redirect to dashboard (role-based)
```

## Known Limitations

1. **STUDENT Role Disabled**
   - Student dashboard currently not implemented
   - First login akan redirect ke `/login` untuk STUDENT role
   - See: `STUDENT_FEATURES_DISABLED.md`

2. **No Password History Check**
   - Password history table exists but not yet implemented for first login
   - User bisa set password yang sama dengan default (not recommended)
   - Planned for future sprint

3. **No Email Notification**
   - No email sent when password is changed
   - Planned for future sprint

## Future Enhancements

- [ ] Email notification saat password berhasil diubah
- [ ] Password strength indicator (weak/medium/strong) dengan visual
- [ ] Password history check (prevent reuse of last N passwords)
- [ ] OTP verification untuk extra security layer
- [ ] Custom password requirements per role (e.g., admin more strict)

## Related Documentation

- **API Documentation:** [Authentication API - First Login Section](../../api/authentication.md#first-login)
- **Test Plan:** [AUTH-P1 Test Plan](../../testing/AUTH-P1-first-login-test-plan.md)
- **Parent Feature:** [AUTH-P0 Authentication](./AUTH-P0-authentication.md)

---

**Last Updated:** 2025-12-23
**Author:** Development Team
**Verified:** ✅ All tests passing, production-ready

