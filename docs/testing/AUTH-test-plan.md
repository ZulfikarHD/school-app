# Test Plan: AUTH - Authentication & Authorization

> **Feature Code:** AUTH | **Last Updated:** 2025-12-23  
> **Status:** ✅ Backend Complete, ⏳ Frontend Pending

---

## Overview

Dokumen ini berisi comprehensive test plan untuk Authentication & Authorization module, mencakup automated tests (Unit & Feature) dan manual QA checklist untuk memastikan semua functionality berjalan sesuai business requirements dan security standards.

---

## Test Coverage Summary

| Layer | Coverage | Status |
|-------|----------|--------|
| **Backend Controllers** | 0% | ⏳ TODO |
| **Form Requests** | 0% | ⏳ TODO |
| **Mailables** | 0% | ⏳ TODO |
| **Frontend Pages** | N/A | ⏳ Not Built Yet |
| **Integration Tests** | 0% | ⏳ TODO |

---

## I. Automated Tests (PHPUnit)

### A. Unit Tests

#### 1. User Model Tests

**File:** `tests/Unit/Models/UserTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| UT-USER-001 | `test_user_has_role_method_returns_true_for_matching_role` | User dengan role TEACHER, call hasRole('TEACHER') | Returns true |
| UT-USER-002 | `test_user_has_role_method_returns_false_for_non_matching_role` | User dengan role TEACHER, call hasRole('ADMIN') | Returns false |
| UT-USER-003 | `test_user_is_active_method_returns_true_for_active` | User dengan status active, call isActive() | Returns true |
| UT-USER-004 | `test_user_is_active_method_returns_false_for_inactive` | User dengan status inactive, call isActive() | Returns false |
| UT-USER-005 | `test_password_is_hashed_automatically` | Create user dengan password plain text | Password stored as hash |
| UT-USER-006 | `test_user_has_activity_logs_relationship` | User exists, call activityLogs() | Returns HasMany relationship |

---

#### 2. Activity Log Model Tests

**File:** `tests/Unit/Models/ActivityLogTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| UT-LOG-001 | `test_activity_log_belongs_to_user` | ActivityLog exists, call user() | Returns BelongsTo relationship |
| UT-LOG-002 | `test_old_values_casts_to_array` | Create log dengan old_values JSON string | Returns PHP array |
| UT-LOG-003 | `test_new_values_casts_to_array` | Create log dengan new_values JSON string | Returns PHP array |

---

#### 3. Failed Login Attempt Model Tests

**File:** `tests/Unit/Models/FailedLoginAttemptTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| UT-LOCK-001 | `test_is_locked_returns_true_when_locked` | FailedLoginAttempt dengan locked_until future | isLocked() returns true |
| UT-LOCK-002 | `test_is_locked_returns_false_when_not_locked` | FailedLoginAttempt dengan locked_until null | isLocked() returns false |
| UT-LOCK-003 | `test_is_locked_returns_false_when_lock_expired` | FailedLoginAttempt dengan locked_until past | isLocked() returns false |

---

### B. Feature Tests

#### 1. Authentication Feature Tests

**File:** `tests/Feature/Auth/LoginTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| FT-AUTH-001 | `test_login_page_displays_correctly` | GET /login | Returns 200, shows login form |
| FT-AUTH-002 | `test_user_can_login_with_valid_credentials` | POST /login dengan credentials valid | Redirect to dashboard, authenticated |
| FT-AUTH-003 | `test_user_cannot_login_with_invalid_password` | POST /login dengan password salah | Redirect back, error message, not authenticated |
| FT-AUTH-004 | `test_user_cannot_login_with_non_existent_email` | POST /login dengan email tidak terdaftar | Redirect back, generic error message |
| FT-AUTH-005 | `test_user_cannot_login_with_inactive_account` | POST /login dengan status inactive | Redirect back, error message |
| FT-AUTH-006 | `test_first_login_user_redirected_to_change_password` | POST /login dengan is_first_login=true | Redirect to /first-login |
| FT-AUTH-007 | `test_remember_me_checkbox_works` | POST /login dengan remember=true | Remember token set |
| FT-AUTH-008 | `test_activity_log_created_on_successful_login` | POST /login success | ActivityLog record created (action: login) |
| FT-AUTH-009 | `test_last_login_info_updated_on_success` | POST /login success | last_login_at & last_login_ip updated |
| FT-AUTH-010 | `test_failed_login_creates_failed_attempt_record` | POST /login dengan password salah | FailedLoginAttempt record created/updated |
| FT-AUTH-011 | `test_account_locked_after_5_failed_attempts` | POST /login 5x dengan password salah | 6th attempt returns lockout error |
| FT-AUTH-012 | `test_locked_account_unlocks_after_15_minutes` | Wait 15 min after lock | Can login again |
| FT-AUTH-013 | `test_login_rate_limiting_works` | POST /login 6x dalam 1 menit | 6th request returns 429 Too Many Requests |
| FT-AUTH-014 | `test_user_can_logout_successfully` | POST /logout | Redirect to login, not authenticated |
| FT-AUTH-015 | `test_activity_log_created_on_logout` | POST /logout | ActivityLog record created (action: logout) |
| FT-AUTH-016 | `test_session_invalidated_on_logout` | POST /logout | Session destroyed, CSRF token regenerated |

---

**File:** `tests/Feature/Auth/ForgotPasswordTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| FT-FORGOT-001 | `test_forgot_password_page_displays` | GET /forgot-password | Returns 200, shows form |
| FT-FORGOT-002 | `test_user_can_request_password_reset` | POST /forgot-password dengan email valid | Redirect back, success message, email sent |
| FT-FORGOT-003 | `test_password_reset_token_created_in_database` | POST /forgot-password | Token record in password_reset_tokens table |
| FT-FORGOT-004 | `test_password_reset_email_sent` | POST /forgot-password | Email queued/sent via PasswordResetMail |
| FT-FORGOT-005 | `test_old_token_deleted_when_new_request` | POST /forgot-password 2x untuk same email | Old token deleted, new token created |
| FT-FORGOT-006 | `test_activity_log_created_on_reset_request` | POST /forgot-password | ActivityLog record created |
| FT-FORGOT-007 | `test_cannot_request_reset_with_non_existent_email` | POST /forgot-password dengan email tidak terdaftar | Validation error |
| FT-FORGOT-008 | `test_cannot_request_reset_for_inactive_user` | POST /forgot-password dengan status inactive | Error message |
| FT-FORGOT-009 | `test_rate_limiting_3_requests_per_24_hours` | POST /forgot-password 4x dalam 24 jam | 4th request returns error |
| FT-FORGOT-010 | `test_rate_limiting_applies_per_email` | POST untuk email A 3x, email B still works | Email B not affected |

---

**File:** `tests/Feature/Auth/ResetPasswordTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| FT-RESET-001 | `test_reset_password_page_displays_with_valid_token` | GET /reset-password/{token}?email=x | Returns 200, shows form |
| FT-RESET-002 | `test_reset_password_page_redirects_with_invalid_token` | GET /reset-password/invalid | Redirect to login, error message |
| FT-RESET-003 | `test_reset_password_page_redirects_with_expired_token` | GET /reset-password/{token} older than 1 hour | Redirect to login, error message |
| FT-RESET-004 | `test_user_can_reset_password_with_valid_token` | POST /reset-password dengan token valid | Redirect to login, success message |
| FT-RESET-005 | `test_password_updated_in_database` | POST /reset-password | users.password updated |
| FT-RESET-006 | `test_token_deleted_after_successful_reset` | POST /reset-password | Token removed from password_reset_tokens |
| FT-RESET-007 | `test_activity_log_created_on_reset` | POST /reset-password | ActivityLog record created |
| FT-RESET-008 | `test_cannot_reset_with_weak_password` | POST /reset-password dengan password 5 chars | Validation error |
| FT-RESET-009 | `test_cannot_reset_with_unconfirmed_password` | POST /reset-password, password ≠ confirmation | Validation error |
| FT-RESET-010 | `test_cannot_reuse_token_after_reset` | POST /reset-password 2x dengan same token | 2nd attempt fails |

---

**File:** `tests/Feature/Auth/FirstLoginTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| FT-FIRST-001 | `test_first_login_page_displays` | GET /first-login (authenticated, is_first_login=true) | Returns 200, shows form |
| FT-FIRST-002 | `test_first_login_redirects_if_not_first_login` | GET /first-login (is_first_login=false) | Redirect to dashboard |
| FT-FIRST-003 | `test_user_can_change_password_on_first_login` | POST /first-login dengan password valid | Redirect to dashboard, password updated |
| FT-FIRST-004 | `test_is_first_login_flag_cleared_after_change` | POST /first-login | users.is_first_login = false |
| FT-FIRST-005 | `test_activity_log_created_on_first_login_change` | POST /first-login | ActivityLog record created |
| FT-FIRST-006 | `test_cannot_change_to_weak_password` | POST /first-login dengan password weak | Validation error |

---

#### 2. User Management Feature Tests

**File:** `tests/Feature/Admin/UserManagementTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| FT-USER-001 | `test_admin_can_view_user_list` | GET /admin/users (auth as ADMIN) | Returns 200, shows user list |
| FT-USER-002 | `test_non_admin_cannot_view_user_list` | GET /admin/users (auth as TEACHER) | Returns 403 |
| FT-USER-003 | `test_user_list_search_works` | GET /admin/users?search=john | Returns filtered users |
| FT-USER-004 | `test_user_list_role_filter_works` | GET /admin/users?role=TEACHER | Returns only teachers |
| FT-USER-005 | `test_user_list_status_filter_works` | GET /admin/users?status=active | Returns only active users |
| FT-USER-006 | `test_user_list_pagination_works` | GET /admin/users?page=2 | Returns page 2 results |
| FT-USER-007 | `test_admin_can_create_user` | POST /admin/users dengan data valid | User created, redirect, success message |
| FT-USER-008 | `test_default_password_generated_on_create` | POST /admin/users | Password format: FirstName + 4 digits |
| FT-USER-009 | `test_is_first_login_set_to_true_on_create` | POST /admin/users | users.is_first_login = true |
| FT-USER-010 | `test_email_sent_to_new_user` | POST /admin/users | Email queued via UserAccountCreated |
| FT-USER-011 | `test_activity_log_created_on_user_create` | POST /admin/users | ActivityLog record created |
| FT-USER-012 | `test_cannot_create_user_with_duplicate_email` | POST /admin/users dengan email exists | Validation error |
| FT-USER-013 | `test_cannot_create_user_with_duplicate_username` | POST /admin/users dengan username exists | Validation error |
| FT-USER-014 | `test_cannot_create_user_with_invalid_role` | POST /admin/users dengan role=INVALID | Validation error |
| FT-USER-015 | `test_admin_can_update_user` | PATCH /admin/users/{user} | User updated, redirect, success message |
| FT-USER-016 | `test_activity_log_created_on_user_update` | PATCH /admin/users/{user} | ActivityLog with old_values & new_values |
| FT-USER-017 | `test_admin_can_delete_user` | DELETE /admin/users/{user} | User deleted, redirect, success message |
| FT-USER-018 | `test_cannot_delete_yourself` | DELETE /admin/users/{auth()->id()} | Error message, not deleted |
| FT-USER-019 | `test_cannot_delete_last_superadmin` | DELETE last SUPERADMIN | Error message, not deleted |
| FT-USER-020 | `test_activity_log_created_on_user_delete` | DELETE /admin/users/{user} | ActivityLog record created |
| FT-USER-021 | `test_admin_can_reset_user_password` | POST /admin/users/{user}/reset-password | Password reset, email sent |
| FT-USER-022 | `test_is_first_login_set_on_admin_reset` | POST /admin/users/{user}/reset-password | users.is_first_login = true |
| FT-USER-023 | `test_admin_can_toggle_user_status` | PATCH /admin/users/{user}/toggle-status | Status toggled |
| FT-USER-024 | `test_cannot_deactivate_yourself` | PATCH /admin/users/{auth()->id()}/toggle-status | Error message |
| FT-USER-025 | `test_cannot_deactivate_last_active_superadmin` | PATCH toggle last active SUPERADMIN | Error message |

---

#### 3. Profile & Password Change Tests

**File:** `tests/Feature/Profile/ProfileTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| FT-PROF-001 | `test_user_can_view_profile_page` | GET /profile (authenticated) | Returns 200, shows profile data |
| FT-PROF-002 | `test_guest_cannot_view_profile` | GET /profile (guest) | Redirect to login |
| FT-PROF-003 | `test_user_can_change_password` | POST /profile/password dengan valid data | Success message, password updated |
| FT-PROF-004 | `test_old_password_must_be_correct` | POST /profile/password dengan wrong current_password | Validation error |
| FT-PROF-005 | `test_new_password_must_be_different` | POST /profile/password, new = old | Validation error |
| FT-PROF-006 | `test_new_password_must_be_confirmed` | POST /profile/password, password ≠ confirmation | Validation error |
| FT-PROF-007 | `test_activity_log_created_on_password_change` | POST /profile/password | ActivityLog record created |

---

#### 4. Audit Log Tests

**File:** `tests/Feature/Admin/AuditLogTest.php`

| Test ID | Test Name | Scenario | Expected |
|---------|-----------|----------|----------|
| FT-AUDIT-001 | `test_admin_can_view_audit_logs` | GET /admin/audit-logs (auth as ADMIN) | Returns 200, shows logs |
| FT-AUDIT-002 | `test_principal_can_view_audit_logs` | GET /audit-logs (auth as PRINCIPAL) | Returns 200, shows logs |
| FT-AUDIT-003 | `test_teacher_cannot_view_audit_logs` | GET /admin/audit-logs (auth as TEACHER) | Returns 403 |
| FT-AUDIT-004 | `test_audit_log_date_filter_works` | GET /admin/audit-logs?date_from=X&date_to=Y | Returns filtered logs |
| FT-AUDIT-005 | `test_audit_log_user_filter_works` | GET /admin/audit-logs?user_id=X | Returns logs for specific user |
| FT-AUDIT-006 | `test_audit_log_action_filter_works` | GET /admin/audit-logs?actions[]=login | Returns only login actions |
| FT-AUDIT-007 | `test_audit_log_status_filter_works` | GET /admin/audit-logs?status=failed | Returns only failed attempts |
| FT-AUDIT-008 | `test_audit_log_search_by_ip_works` | GET /admin/audit-logs?search=192.168.1.1 | Returns logs from that IP |
| FT-AUDIT-009 | `test_audit_log_pagination_works` | GET /admin/audit-logs?page=2 | Returns page 2 (50 per page) |
| FT-AUDIT-010 | `test_audit_log_eager_loads_user` | GET /admin/audit-logs | No N+1 queries |

---

## II. Manual QA Checklist

### Pre-Test Environment Setup

- [ ] SMTP configured (or use Mailtrap for testing)
- [ ] Queue worker running (`php artisan queue:work`)
- [ ] Database fresh migrated + seeded dengan test data
- [ ] Browser dev tools open (check console errors)
- [ ] Test pada 3 browsers: Chrome, Firefox, Safari/Edge

---

### A. Authentication Flow

#### Login Page

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-AUTH-001 | Display login page | Navigate to `/login` | Form displayed: identifier, password, remember checkbox, forgot password link | ☐ |
| QA-AUTH-002 | Login success | Enter valid credentials, submit | Redirect to dashboard sesuai role | ☐ |
| QA-AUTH-003 | Login with username | Enter username (bukan email) | Login berhasil | ☐ |
| QA-AUTH-004 | Login with email | Enter email (bukan username) | Login berhasil | ☐ |
| QA-AUTH-005 | Login wrong password | Enter valid identifier, wrong password | Error message generic, no hint what's wrong | ☐ |
| QA-AUTH-006 | Login non-existent user | Enter email tidak terdaftar | Error message generic (timing attack mitigation) | ☐ |
| QA-AUTH-007 | Login inactive account | Login dengan status inactive user | Error message "akun tidak aktif" | ☐ |
| QA-AUTH-008 | First login redirect | Login dengan is_first_login=true user | Redirect to /first-login | ☐ |
| QA-AUTH-009 | Remember me checkbox | Check "Ingat Saya", login | Session persists after browser close | ☐ |
| QA-AUTH-010 | Account lockout | 5 failed login attempts | 6th attempt shows lockout message with countdown | ☐ |
| QA-AUTH-011 | Lockout countdown display | Account locked | Countdown timer displayed (if frontend implemented) | ☐ |
| QA-AUTH-012 | Rate limiting | 6 login attempts dalam 1 menit | 6th request shows "too many requests" | ☐ |

---

#### Logout Flow

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-AUTH-020 | Logout success | Click logout button | Redirect to login, session cleared | ☐ |
| QA-AUTH-021 | Cannot access protected routes after logout | Logout, try access /dashboard | Redirect to login | ☐ |
| QA-AUTH-022 | Activity log created | Logout | Log recorded dalam audit logs | ☐ |

---

#### Forgot Password Flow

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-FORGOT-001 | Display forgot password page | Click "Lupa password?" di login | Form displayed: email input | ☐ |
| QA-FORGOT-002 | Request reset link | Enter valid email, submit | Success message, email sent | ☐ |
| QA-FORGOT-003 | Email received | Check inbox | Email contains reset link, 1-hour warning | ☐ |
| QA-FORGOT-004 | Reset link works | Click link dari email | Opens /reset-password page dengan token | ☐ |
| QA-FORGOT-005 | Non-existent email | Enter email tidak terdaftar | Validation error | ☐ |
| QA-FORGOT-006 | Inactive user | Enter email dari inactive user | Error message | ☐ |
| QA-FORGOT-007 | Rate limiting 3/24h | Request 4x untuk same email | 4th request blocked dengan error | ☐ |
| QA-FORGOT-008 | Old token invalidated | Request 2x untuk same email | Old link tidak work, only new link | ☐ |

---

#### Reset Password Flow

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-RESET-001 | Display reset page | Open link dari email | Form displayed: email (readonly), password, confirmation | ☐ |
| QA-RESET-002 | Reset password success | Enter password valid, submit | Redirect to login, success message | ☐ |
| QA-RESET-003 | Can login with new password | Login dengan new password | Login berhasil | ☐ |
| QA-RESET-004 | Old password tidak work | Login dengan old password | Login gagal | ☐ |
| QA-RESET-005 | Expired token | Open link older than 1 hour | Redirect to login, error message | ☐ |
| QA-RESET-006 | Invalid token | Modify token di URL | Redirect to login, error message | ☐ |
| QA-RESET-007 | Weak password | Enter password 5 chars | Validation error | ☐ |
| QA-RESET-008 | Password no confirmation | password ≠ password_confirmation | Validation error | ☐ |
| QA-RESET-009 | Token reuse prevented | Use same link 2x | 2nd attempt fails | ☐ |
| QA-RESET-010 | Password strength indicator | Type password | Visual feedback (weak/medium/strong) | ☐ |

---

#### First Login Flow

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-FIRST-001 | Forced to change password | Login dengan new user account | Redirect to /first-login, cannot skip | ☐ |
| QA-FIRST-002 | Change password success | Enter new password valid, submit | Redirect to dashboard, is_first_login=false | ☐ |
| QA-FIRST-003 | Can login with new password | Logout, login dengan new password | Login berhasil, direct to dashboard (no force change) | ☐ |
| QA-FIRST-004 | Weak password rejected | Enter password weak | Validation error | ☐ |

---

### B. User Management (Admin)

#### User List

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-USER-001 | Display user list | Navigate to /admin/users (as ADMIN) | Table displayed dengan pagination | ☐ |
| QA-USER-002 | Search by name | Enter name di search box | Filtered results | ☐ |
| QA-USER-003 | Search by username | Enter username di search box | Filtered results | ☐ |
| QA-USER-004 | Search by email | Enter email di search box | Filtered results | ☐ |
| QA-USER-005 | Filter by role | Select role dropdown | Filtered results | ☐ |
| QA-USER-006 | Filter by status | Select status dropdown | Filtered results | ☐ |
| QA-USER-007 | Pagination works | Click page 2 | Shows next 15 users | ☐ |
| QA-USER-008 | Mobile responsive | Resize to mobile | Cards instead of table | ☐ |

---

#### Create User

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-USER-010 | Display create form | Click "Tambah User" | Form displayed: name, email, username, phone, role, status | ☐ |
| QA-USER-011 | Create user success | Fill valid data, submit | Redirect to list, success message | ☐ |
| QA-USER-012 | Email sent to new user | Check inbox | Email dengan credentials received | ☐ |
| QA-USER-013 | New user di list | Check user list | New user appears | ☐ |
| QA-USER-014 | New user forced first login | Login as new user | Forced to /first-login | ☐ |
| QA-USER-015 | Duplicate email blocked | Create dengan email exists | Validation error | ☐ |
| QA-USER-016 | Duplicate username blocked | Create dengan username exists | Validation error | ☐ |
| QA-USER-017 | Invalid role blocked | Select invalid role | Validation error (frontend should prevent) | ☐ |
| QA-USER-018 | Validation errors display | Submit invalid data | Errors shown di Indonesian | ☐ |

---

#### Update User

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-USER-020 | Display edit form | Click edit icon | Form displayed with current data | ☐ |
| QA-USER-021 | Update user success | Change name, submit | Redirect to list, success message | ☐ |
| QA-USER-022 | Role change detection | Change role, submit | Success message mentions "login ulang" | ☐ |
| QA-USER-023 | Can update own email | Change email to another | Success, email unique check ignores self | ☐ |
| QA-USER-024 | Activity log records changes | Update user | Audit log shows old_values & new_values | ☐ |

---

#### Delete User

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-USER-030 | Delete user success | Click delete, confirm | User deleted, redirect, success message | ☐ |
| QA-USER-031 | Cannot delete yourself | Try delete own account | Error message, not deleted | ☐ |
| QA-USER-032 | Cannot delete last admin | Try delete last SUPERADMIN | Error message, not deleted | ☐ |
| QA-USER-033 | Confirmation modal | Click delete | Confirmation prompt shown | ☐ |

---

#### Reset Password (Admin)

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-USER-040 | Reset user password | Click "Reset Password" | Success message, email sent | ☐ |
| QA-USER-041 | Email received | Check user inbox | Email dengan new password | ☐ |
| QA-USER-042 | User forced first login | User login dengan new password | Forced to /first-login | ☐ |

---

#### Toggle Status

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-USER-050 | Activate user | Toggle inactive user | Status changes to active | ☐ |
| QA-USER-051 | Deactivate user | Toggle active user | Status changes to inactive | ☐ |
| QA-USER-052 | Deactivated user cannot login | Try login as inactive | Error message | ☐ |
| QA-USER-053 | Cannot deactivate yourself | Try deactivate own account | Error message | ☐ |
| QA-USER-054 | Cannot deactivate last admin | Try deactivate last active SUPERADMIN | Error message | ☐ |

---

### C. Profile & Change Password

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-PROF-001 | Display profile page | Navigate to /profile | Profile info displayed | ☐ |
| QA-PROF-002 | Profile shows correct data | Check displayed data | Name, username, email, role, last login correct | ☐ |
| QA-PROF-003 | Open change password modal | Click "Ganti Password" | Modal displayed: old, new, confirm fields | ☐ |
| QA-PROF-004 | Change password success | Enter valid data, submit | Success message, password updated | ☐ |
| QA-PROF-005 | Can login with new password | Logout, login dengan new password | Login berhasil | ☐ |
| QA-PROF-006 | Old password required | Submit without old password | Validation error | ☐ |
| QA-PROF-007 | Wrong old password | Enter wrong old password | Validation error | ☐ |
| QA-PROF-008 | New must be different | New password = old password | Validation error | ☐ |
| QA-PROF-009 | Password strength indicator | Type new password | Visual feedback displayed | ☐ |
| QA-PROF-010 | Activity log created | Change password | Log recorded | ☐ |

---

### D. Audit Logs

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-AUDIT-001 | Display audit logs (Admin) | Navigate to /admin/audit-logs (as ADMIN) | Table displayed dengan logs | ☐ |
| QA-AUDIT-002 | Display audit logs (Principal) | Navigate to /audit-logs (as PRINCIPAL) | Table displayed (read-only) | ☐ |
| QA-AUDIT-003 | Teacher cannot access | Navigate to /admin/audit-logs (as TEACHER) | 403 error page | ☐ |
| QA-AUDIT-004 | Date range filter | Select date range, apply | Filtered logs | ☐ |
| QA-AUDIT-005 | User filter | Select specific user | Shows only that user's logs | ☐ |
| QA-AUDIT-006 | Action filter | Select "login" action | Shows only login logs | ☐ |
| QA-AUDIT-007 | Multiple action filter | Select multiple actions | Shows all selected actions | ☐ |
| QA-AUDIT-008 | Status filter | Select "failed" | Shows only failed attempts | ☐ |
| QA-AUDIT-009 | Search by IP | Enter IP address | Filtered results | ☐ |
| QA-AUDIT-010 | Pagination works | Navigate pages | 50 logs per page | ☐ |
| QA-AUDIT-011 | Logs display correct info | Check log entries | User, action, status, IP, timestamp correct | ☐ |
| QA-AUDIT-012 | Success logs green | Check success logs | Badge displayed green | ☐ |
| QA-AUDIT-013 | Failed logs red | Check failed logs | Badge displayed red | ☐ |
| QA-AUDIT-014 | Expand log details | Click log row | Shows old_values & new_values JSON | ☐ |
| QA-AUDIT-015 | Mobile responsive | Resize to mobile | Compact view, essential info only | ☐ |

---

### E. Security Checks

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-SEC-001 | CSRF protection | Disable CSRF token, submit form | Request blocked | ☐ |
| QA-SEC-002 | Password hashed | Check database users table | Password stored as hash, not plain text | ☐ |
| QA-SEC-003 | Token hashed | Check password_reset_tokens | Token stored as hash | ☐ |
| QA-SEC-004 | Session regeneration | Login, check session ID, logout, login again | Different session IDs | ☐ |
| QA-SEC-005 | RBAC enforcement | Access admin route as TEACHER | 403 error | ☐ |
| QA-SEC-006 | SQL injection attempt | Enter `' OR 1=1 --` di login | No SQL error, safe handling | ☐ |
| QA-SEC-007 | XSS attempt | Create user dengan name `<script>alert(1)</script>` | Displayed escaped, no alert | ☐ |
| QA-SEC-008 | Sensitive data not logged | Check activity logs | Passwords NOT present di logs | ☐ |
| QA-SEC-009 | Rate limiting enforced | Rapid requests | Throttled appropriately | ☐ |
| QA-SEC-010 | Email spamming prevented | Request forgot password 10x | Blocked after 3 requests | ☐ |

---

### F. Email Templates

| ID | Test Case | Steps | Expected Result | Pass/Fail |
|----|-----------|-------|-----------------|-----------|
| QA-EMAIL-001 | User created email renders | Create user, check email | HTML renders correctly, credentials displayed | ☐ |
| QA-EMAIL-002 | Password reset email renders | Request forgot password, check email | HTML renders correctly, link works | ☐ |
| QA-EMAIL-003 | Email subject correct | Check email subject | Indonesian, professional | ☐ |
| QA-EMAIL-004 | Email sender correct | Check from address | Matches MAIL_FROM_ADDRESS | ☐ |
| QA-EMAIL-005 | Email mobile responsive | Open on mobile device | Renders correctly on small screens | ☐ |
| QA-EMAIL-006 | Email links work | Click links di email | Opens correct pages | ☐ |

---

## III. Performance Testing

| ID | Test Case | Target | Actual | Pass/Fail |
|----|-----------|--------|--------|-----------|
| PERF-001 | Login response time | < 500ms | - | ☐ |
| PERF-002 | User list load (100 users) | < 1s | - | ☐ |
| PERF-003 | Audit log load (1000 logs) | < 2s | - | ☐ |
| PERF-004 | No N+1 queries di audit logs | 0 extra queries | - | ☐ |
| PERF-005 | Email queue processing | Immediate queue, not blocking | - | ☐ |

---

## IV. Browser Compatibility

| Browser | Version | Login | CRUD | Audit Logs | Pass/Fail |
|---------|---------|-------|------|------------|-----------|
| Chrome | Latest | ☐ | ☐ | ☐ | ☐ |
| Firefox | Latest | ☐ | ☐ | ☐ | ☐ |
| Safari | Latest | ☐ | ☐ | ☐ | ☐ |
| Edge | Latest | ☐ | ☐ | ☐ | ☐ |
| Mobile Safari | iOS 15+ | ☐ | ☐ | ☐ | ☐ |
| Mobile Chrome | Android 12+ | ☐ | ☐ | ☐ | ☐ |

---

## V. Accessibility Testing

| ID | Test Case | Steps | Expected | Pass/Fail |
|----|-----------|-------|----------|-----------|
| A11Y-001 | Keyboard navigation | Tab through login form | Focus order logical, visible focus indicators | ☐ |
| A11Y-002 | Screen reader compatibility | Use NVDA/JAWS | Form labels announced correctly | ☐ |
| A11Y-003 | Error messages accessible | Submit invalid form | Errors announced by screen reader | ☐ |
| A11Y-004 | Color contrast sufficient | Check via WebAIM | WCAG AA compliance | ☐ |

---

## Test Execution Summary

**Tested By:** ________________  
**Date:** ________________  
**Environment:** ☐ Local  ☐ Staging  ☐ Production

**Results:**
- Total Tests: _____
- Passed: _____
- Failed: _____
- Blocked: _____

**Critical Issues Found:**
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

**Sign-off:** ________________  
**Date:** ________________

---

*Last Updated: 2025-12-23*

