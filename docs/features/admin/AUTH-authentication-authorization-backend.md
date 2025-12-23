# Feature: AUTH - Authentication & Authorization (Backend)

> **Code:** AUTH | **Priority:** Critical | **Status:** ✅ Backend Complete (Frontend Pending)  
> **Sprint:** 1 | **Last Updated:** 2025-12-23

---

## Overview

Authentication & Authorization merupakan sistem keamanan inti yang bertujuan untuk mengelola identitas, akses, dan aktivitas user dalam sistem, yaitu: validasi credentials dengan rate limiting dan account lockout, role-based access control (RBAC) untuk 6 roles berbeda, self-service password management (forgot/reset/change), admin-managed user CRUD operations, dan comprehensive audit logging untuk security monitoring dan compliance.

---

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| US-AUTH-001 | All Users | Login dengan username/email & password | Dapat mengakses sistem sesuai role | ✅ Done |
| US-AUTH-002 | All Users | Logout dari sistem | Session saya berakhir dengan aman | ✅ Done |
| US-AUTH-003 | All Users | Request reset password via email | Dapat login kembali jika lupa password | ✅ Backend Done |
| US-AUTH-004 | All Users | Change password saat sudah login | Dapat update password secara mandiri | ✅ Backend Done |
| US-AUTH-005 | System | Enforce RBAC dengan 6 roles | Setiap user hanya akses fitur sesuai permission | ✅ Done |
| US-AUTH-006 | Super Admin/TU | CRUD user accounts | Dapat mengelola user dalam sistem | ✅ Backend Done |
| US-AUTH-007 | New Users | Forced change password saat first login | Password default tidak digunakan permanent | ✅ Done |
| US-AUTH-008 | System | Lock account after 5 failed login | Mencegah brute force attacks | ✅ Done |
| US-AUTH-009 | Admin/Principal | View activity logs | Monitoring security events | ✅ Backend Done |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-AUTH-001 | Password minimal 8 karakter, harus ada huruf & angka | Laravel Password validation rules |
| BR-AUTH-002 | Account locked 15 menit setelah 5 failed login attempts | FailedLoginAttempt model + LoginController |
| BR-AUTH-003 | Password reset token valid 1 jam saja | Token expiry check di ResetPasswordController |
| BR-AUTH-004 | Max 3 forgot password requests per 24 jam per email | Rate limiting di ForgotPasswordController |
| BR-AUTH-005 | Login rate limiting: 5 attempts per minute | Throttle middleware di routes/auth.php |
| BR-AUTH-006 | New user password: FirstName + 4 random digits | UserController::store() |
| BR-AUTH-007 | User tidak bisa delete diri sendiri atau last admin | Safety checks di UserController::destroy() |
| BR-AUTH-008 | Role change requires re-login | Session termination (TODO) di UserController::update() |
| BR-AUTH-009 | Semua sensitive actions di-log untuk audit | ActivityLog::create() di setiap controller |

---

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| **Controllers** | `app/Http/Controllers/Admin/UserController.php` | User CRUD + reset password + toggle status |
| | `app/Http/Controllers/Auth/LoginController.php` | Login + logout + lockout handling |
| | `app/Http/Controllers/Auth/ForgotPasswordController.php` | Request password reset link |
| | `app/Http/Controllers/Auth/ResetPasswordController.php` | Execute password reset via token |
| | `app/Http/Controllers/Auth/FirstLoginController.php` | Force password change first login |
| | `app/Http/Controllers/Profile/ProfileController.php` | Display user profile |
| | `app/Http/Controllers/Profile/PasswordController.php` | Change password authenticated users |
| | `app/Http/Controllers/Admin/AuditLogController.php` | View activity logs dengan filters |
| **Form Requests** | `app/Http/Requests/Admin/StoreUserRequest.php` | Validation create user |
| | `app/Http/Requests/Admin/UpdateUserRequest.php` | Validation update user |
| | `app/Http/Requests/Auth/ForgotPasswordRequest.php` | Validation forgot password |
| | `app/Http/Requests/Auth/ResetPasswordRequest.php` | Validation reset password |
| | `app/Http/Requests/Profile/ChangePasswordRequest.php` | Validation change password |
| **Mailables** | `app/Mail/UserAccountCreated.php` | Email credentials ke user baru |
| | `app/Mail/PasswordResetMail.php` | Email reset password link |
| **Models** | `app/Models/User.php` | User entity dengan relationships |
| | `app/Models/ActivityLog.php` | Security audit trail |
| | `app/Models/FailedLoginAttempt.php` | Track failed logins untuk lockout |
| **Migrations** | `database/migrations/*_create_password_reset_tokens_table.php` | Password reset tokens storage |
| **Email Templates** | `resources/views/emails/user-account-created.blade.php` | HTML email untuk credentials |
| | `resources/views/emails/password-reset.blade.php` | HTML email untuk reset link |

### Routes Summary

| Group | Count | Prefix | Description |
|-------|-------|--------|-------------|
| User Management | 9 | `admin/users` | CRUD + reset password + toggle status |
| Password Reset | 4 | `forgot-password`, `reset-password` | Forgot/reset flow untuk guest |
| Profile & Change Password | 2 | `profile` | Profile view + change password |
| Audit Logs | 2 | `admin/audit-logs`, `audit-logs` | Admin/Principal view logs |
| Authentication | 3 | `login`, `logout`, `first-login` | Login/logout + first login flow |

> 📡 **Full API Documentation:** [Authentication API](../../api/authentication.md), [Users API](../../api/users.md)

### Database Schema

> 📌 **Lihat:** [DATABASE.md](../../architecture/DATABASE.md) untuk schema lengkap.

**Primary Tables:**
- `users` - User accounts dengan role, status, login info
- `activity_logs` - Audit trail untuk semua sensitive actions
- `failed_login_attempts` - Track failed logins untuk lockout
- `password_reset_tokens` - Tokens untuk forgot password flow
- `password_histories` - Riwayat password (TODO: prevent reuse)

---

## Edge Cases & Handling

| Scenario | Expected Behavior | Implementation |
|----------|-------------------|----------------|
| **User lupa password saat account locked** | Reset password tetap bisa dilakukan via email | Token bypass lockout check |
| **Admin reset password user yang sedang login** | User harus login ulang dengan password baru | Set `is_first_login = true` |
| **Forgot password request saat token masih valid** | Old token dihapus, generate new token | Delete old tokens before insert |
| **Reset password dengan expired token** | Show error, redirect to forgot password | Token expiry check di show() |
| **Change password dengan password yang sama** | Validation error | `different:current_password` rule |
| **Delete last Super Admin** | Prevented dengan error message | Count active admins di destroy() |
| **Admin delete diri sendiri** | Prevented dengan error message | Check `auth()->id() !== user->id` |
| **Role change saat user sedang login** | User session should be terminated | TODO: implement session termination |
| **Multiple failed logins dari IP berbeda** | Each IP tracked separately | `identifier + ip_address` composite |
| **Email send failure saat create user** | User tetap created, log error | Try-catch around Mail::send() |

---

## Security Considerations

| Concern | Mitigation | Implementation |
|---------|-----------|----------------|
| **Brute Force Attacks** | Rate limiting + account lockout | Throttle middleware + FailedLoginAttempt |
| **Timing Attacks** | Constant-time password check | Hash::check() dengan dummy hash |
| **Password Reuse** | Strong password validation | Laravel Password rules (min 8, letters, numbers, uncompromised) |
| **Token Hijacking** | SHA-256 hashing + 1-hour expiry | hash('sha256', $token) di ForgotPasswordController |
| **Session Fixation** | Regenerate session on login | $request->session()->regenerate() |
| **CSRF** | Laravel CSRF protection | @csrf directive di form |
| **SQL Injection** | Eloquent ORM + prepared statements | No raw queries |
| **XSS** | Blade escaping output | {{ }} escapes by default |
| **Mass Assignment** | Fillable whitelist di models | $fillable property |
| **Sensitive Data Exposure** | Activity log filters | Don't log passwords |

---

## Performance Considerations

| Concern | Solution | Implementation |
|---------|----------|----------------|
| **N+1 Query di Audit Logs** | Eager loading | `->with('user:id,name,username,role')` |
| **Large activity_logs table** | Database indexes | Indexes on user_id, action, created_at |
| **Email sending blocking request** | Queue emails | implements ShouldQueue |
| **Heavy dashboard queries** | Pagination | `->paginate(50)` |
| **Repeated user lookups** | Cache user data | Session stores auth user |

---

## Configuration

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `MAIL_MAILER` | string | smtp | Email driver untuk password reset |
| `MAIL_FROM_ADDRESS` | string | - | Sender email address |
| `QUEUE_CONNECTION` | string | sync | Queue driver (recommend: database) |
| `SESSION_LIFETIME` | integer | 120 | Session timeout dalam menit (24 hours = 1440) |
| `SESSION_DRIVER` | string | file | Session storage (database recommended for multi-server) |

---

## Testing

### Automated Tests Status
⚠️ **TODO:** Unit & Feature tests belum dibuat. Prioritas setelah frontend selesai.

### Manual Testing Checklist (Backend Only)

- [x] Routes terdaftar dengan benar (verified via artisan route:list)
- [x] Controllers loadable tanpa error
- [x] Form Requests validation rules correct
- [x] Mailables instantiable
- [x] Database tables exists
- [x] Tinker dapat query models successfully
- [ ] Email templates render correctly (need SMTP config)
- [ ] Rate limiting works as expected
- [ ] Account lockout mechanism works
- [ ] Activity logs created for all actions

> 📋 **Full Test Plan:** [AUTH Test Plan](../../testing/AUTH-test-plan.md)

---

## Related Documentation

- **API Documentation:** [Authentication API](../../api/authentication.md), [Users API](../../api/users.md)
- **Test Plan:** [AUTH Test Plan](../../testing/AUTH-test-plan.md)
- **Implementation Plan:** [Auth Implementation Strategy](.cursor/plans/auth_module_implementation_strategy_5c671339.plan.md)
- **Backend Summary:** [Backend Implementation Summary](../backend-implementation-summary.md)

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2025-12-23 | ✅ Backend implementation complete (P0, P1, P2 features) |
| - | - | 📝 Frontend implementation pending |

---

## Next Steps (Frontend Implementation Required)

### Priority P0 - Critical (Cannot Ship Without):
1. `/admin/users` - User list + create + edit pages
2. `/forgot-password` - Request reset link form
3. `/reset-password` - Reset password with token form

### Priority P1 - Important:
4. `/profile` - User profile page dengan change password modal
5. `/admin/audit-logs` - Activity log viewer dengan filters
6. `/errors/403` - Access denied error page

### Priority P2 - Enhancement:
7. Session timeout detection + warning modal di AppLayout
8. Lockout countdown timer di login page

---

*Last Updated: 2025-12-23 | Status: ✅ Backend Complete | Frontend: Pending*

