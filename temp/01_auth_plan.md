# Authentication & Authorization - Cross-Frontend Implementation Strategy

## PHASE 1: FEATURE UNDERSTANDING

### Data Being Managed

- **User accounts** (nama, email, username, password, role, status)
- **Authentication sessions** (login tokens, "Ingat Saya" cookies)
- **Password reset tokens** (unique, 1-hour expiry)
- **Activity logs** (login/logout, failed attempts, sensitive actions)
- **Failed login attempts** (IP tracking, account lockout)
- **2FA settings** (QR codes, backup codes) - Phase 2

### Primary Actors

**Data Owners (Who Creates):**

- **Super Admin/TU**: Creates user accounts, resets passwords, manages roles
- **All Users**: Create their own password changes, generate sessions via login
- **System**: Auto-generates activity logs, failed login records

**Data Consumers (Who Views):**

- **All Users**: View their own profile, session status
- **Super Admin/Kepala Sekolah**: View activity logs, user lists
- **System**: Validates sessions, enforces RBAC

### Primary User Goals

1. **Authentication**: Secure login/logout with minimal friction
2. **Authorization**: Role-based access control (6 roles)
3. **Self-Service**: Users can manage their own passwords
4. **Admin Control**: Admins can manage user accounts
5. **Audit Trail**: Monitor security events

---

## PHASE 2: CROSS-FRONTEND IMPACT MAPPING

| Feature Name | Owner (Who Creates) | Consumer (Who Views) | Data Flow | Status |

|--------------|---------------------|----------------------|-----------|--------|

| **Login** | All Users → Login Page | System → Redirects to Dashboard | Create Session → Store → Validate on Each Request | ✅ DONE |

| **Logout** | All Users → Navbar Menu | System → Redirect to Login | Destroy Session → Clear Storage → Redirect | ✅ DONE |

| **First Login Password Change** | New Users → First Login Page | System → Force Before Dashboard | Update Password → Clear Flag → Allow Access | ✅ DONE |

| **Forgot Password (Request)** | All Users → Login Page Link | Email/WhatsApp → User Inbox | Generate Token → Send Link → Expire After 1hr | ⚠️ MISSING |

| **Reset Password (Execute)** | Users → Email Link | Reset Password Page | Validate Token → Update Password → Redirect to Login | ⚠️ MISSING |

| **Change Password (Authenticated)** | Logged-in Users → Profile Page | Same User → Success Notification | Validate Old → Update New → Optional Logout Other Devices | ⚠️ MISSING |

| **User Management (CRUD)** | Super Admin/TU → Admin Panel | Admin Panel → User List Table | Create/Edit User → Generate Default Password → Send to User | ⚠️ MISSING |

| **Role Assignment** | Super Admin/TU → User Edit Form | Admin Panel → User Role Dropdown | Update Role → Invalidate Session → Require Re-login | ⚠️ MISSING |

| **Account Activation/Deactivation** | Super Admin/TU → User List | System → Block Login for Deactivated | Toggle Status → Terminate Active Sessions | ⚠️ MISSING |

| **Activity Log Viewing** | System Auto-generates | Super Admin/Kepala Sekolah → Audit Log Page | Query Logs → Filter by User/Date → Display Table | ⚠️ MISSING |

| **Failed Login Monitoring** | System Auto-generates | System → Lock Account at 5 Attempts | Track Attempts → Lock for 15min → Display Countdown | ✅ BACKEND DONE, Frontend Countdown Missing |

| **RBAC Enforcement** | System → Middleware | All Pages → Show/Hide Based on Role | Check Permission → Return 403 or Hide UI | ✅ PARTIAL (403 Page Missing) |

| **Session Timeout** | System → Auto-expire | Frontend → Detect & Redirect to Login | Check Idle Time → Logout → Show "Session Expired" | ⚠️ MISSING Frontend Detection |

| **2FA Setup** (Phase 2) | Admin Users → Security Settings | Authenticator App → User Scans QR | Generate Secret → Display QR → Store Encrypted | ⬜ PHASE 2 |

| **2FA Login** (Phase 2) | Admin Users → After Password | OTP Input Page → Validate Code | Verify TOTP → Allow Access or Deny | ⬜ PHASE 2 |---

## PHASE 3: MISSING IMPLEMENTATION DETECTION

### ⚠️ US-AUTH-003: Lupa Password (Must Have - Phase 1)

**Owner Side (Data Creation):**

- [ ] ❌ **MISSING**: Page `/forgot-password` - Form input email/no HP
- [ ] ❌ **MISSING**: Controller `ForgotPasswordController@sendResetLink`
- [ ] ❌ **MISSING**: Email template untuk reset link
- [ ] ❌ **MISSING**: WhatsApp integration (optional, bisa Phase 2)
- [ ] ⚠️ **MISSING**: Database migration: `password_reset_tokens` table
- [ ] ✅ Validation rules sudah defined di spec

**Consumer Side (Data Display):**

- [ ] ❌ **MISSING**: Page `/reset-password?token=xxx` - Form input password baru
- [ ] ❌ **MISSING**: Controller `ResetPasswordController@reset`
- [ ] ❌ **MISSING**: Password strength indicator component
- [ ] ❌ **MISSING**: Success notification → redirect to login
- [ ] ❌ **MISSING**: Error handling untuk expired token

**Integration Points:**

- [ ] Laravel's built-in `Illuminate\Auth\Passwords\PasswordBroker` (recommended)
- [ ] Email service (SMTP/SendGrid)
- [ ] Rate limiting: max 3 requests/24 hours per user

---

### ⚠️ US-AUTH-004: Ganti Password (Should Have - Phase 1)

**Owner Side (Data Creation):**

- [ ] ❌ **MISSING**: Page `/profile` atau `/settings` - Link "Ganti Password"
- [ ] ❌ **MISSING**: Modal/Page component `ChangePasswordModal.vue`
- [ ] ❌ **MISSING**: Controller `PasswordController@update`
- [ ] ❌ **MISSING**: Form Request `ChangePasswordRequest`
- [ ] ⚠️ **MISSING**: Validation: password baru ≠ 3 password terakhir (nice to have)

**Consumer Side (Data Display):**

- [ ] ❌ **MISSING**: Navigation menu item "Profil Saya" (all roles)
- [ ] ❌ **MISSING**: Success notification after password change
- [ ] ⚠️ **OPTIONAL**: Option to logout all other devices

**Integration Points:**

- [ ] Hash comparison for old password
- [ ] Password strength meter (frontend)
- [ ] Optional: Broadcast event to force logout other sessions

---

### ⚠️ US-AUTH-006: Manajemen User Account (Must Have - Phase 1)

**Owner Side (Data Creation):**

- [ ] ❌ **MISSING**: Page `/admin/users` - User list with CRUD actions
- [ ] ❌ **MISSING**: Page `/admin/users/create` - Form tambah user baru
- [ ] ❌ **MISSING**: Page `/admin/users/{id}/edit` - Form edit user
- [ ] ❌ **MISSING**: Controller `UserController` with CRUD methods
- [ ] ❌ **MISSING**: Form Request `StoreUserRequest`, `UpdateUserRequest`
- [ ] ❌ **MISSING**: Component `UserTable.vue` (list with filters)
- [ ] ❌ **MISSING**: Component `UserForm.vue` (reusable create/edit)
- [ ] ❌ **MISSING**: Auto-generate default password logic
- [ ] ❌ **MISSING**: Send password via email/WhatsApp

**Consumer Side (Data Display):**

- [ ] ❌ **MISSING**: Navigation menu "Manajemen User" (Super Admin/TU only)
- [ ] ❌ **MISSING**: Search/filter by nama, role, status
- [ ] ❌ **MISSING**: Pagination for user list
- [ ] ❌ **MISSING**: Badge untuk status Aktif/Nonaktif
- [ ] ❌ **MISSING**: Quick actions: Reset Password, Toggle Status
- [ ] ❌ **MISSING**: Empty state "Belum Ada User"

**Integration Points:**

- [ ] Email/WhatsApp service untuk kirim password
- [ ] Activity log untuk setiap CRUD action
- [ ] Soft delete or hard delete? (Spec says deactivate, not delete)

---

### ⚠️ US-AUTH-009: Audit Log - User Activity (Should Have - Phase 1)

**Owner Side (Data Creation):**

- [x] ✅ **DONE**: Backend auto-generates logs (LoginController already logs)
- [x] ✅ **DONE**: Database table `activity_logs` exists (assumed)

**Consumer Side (Data Display):**

- [ ] ❌ **MISSING**: Page `/admin/audit-logs` - Log activity table
- [ ] ❌ **MISSING**: Controller `AuditLogController@index`
- [ ] ❌ **MISSING**: Component `AuditLogTable.vue`
- [ ] ❌ **MISSING**: Filters: date range, user, action type, status
- [ ] ❌ **MISSING**: Export to CSV/Excel (optional, nice to have)
- [ ] ❌ **MISSING**: Color coding: success=green, failed=red
- [ ] ❌ **MISSING**: Show IP address, user agent, timestamp

**Integration Points:**

- [ ] Query optimization untuk log table (akan besar)
- [ ] Pagination (default 50 per page)
- [ ] Cache for common queries
- [ ] Log retention policy (auto-delete after 6 months)

---

### ⚠️ US-AUTH-005: RBAC - 403 Error Page (Must Have - Phase 1)

**Owner Side:** N/A (system-generated)**Consumer Side (Data Display):**

- [ ] ❌ **MISSING**: Page `/errors/403` atau `403.vue`
- [ ] ❌ **MISSING**: Component `AccessDenied.vue`
- [ ] ❌ **MISSING**: Friendly message "Anda tidak memiliki akses"
- [ ] ❌ **MISSING**: Button "Kembali ke Dashboard"
- [ ] ❌ **MISSING**: Illustration/icon untuk error state

**Integration Points:**

- [ ] Register in Inertia's error handling
- [ ] Middleware already returns 403 (check existing)

---

### ⚠️ Frontend Session Timeout Detection (Must Have - Phase 1)

**Owner Side (Data Creation):**

- [x] ✅ **DONE**: Backend expires session after 24 hours (Laravel default)

**Consumer Side (Data Display):**

- [ ] ❌ **MISSING**: Composable `useSessionTimeout.ts`
- [ ] ❌ **MISSING**: Idle detection (30min for users, 15min for admin)
- [ ] ❌ **MISSING**: Warning modal "Session akan berakhir dalam X menit"
- [ ] ❌ **MISSING**: Auto-logout → redirect with message "Session expired"
- [ ] ❌ **MISSING**: "Extend Session" button (optional)

**Integration Points:**

- [ ] Detect Inertia 419 response (token mismatch)
- [ ] Use `window.addEventListener('mousemove')` for idle detection
- [ ] Store last activity timestamp in localStorage

---

### ✅ Already Implemented (No Action Needed)

- **US-AUTH-001**: Login (LoginController + Login.vue) ✅
- **US-AUTH-002**: Logout (LoginController@logout) ✅
- **US-AUTH-007**: First Login Force Change (FirstLoginController + FirstLogin.vue) ✅
- **FR-AUTH-008**: Account Lockout (FailedLoginAttempt model) ✅ Backend
- **FR-AUTH-009**: Activity Logging ✅ Backend (viewing page missing)

---

## PHASE 4: GAP ANALYSIS

### 🚨 CRITICAL GAPS (Breaks Core Functionality)

1. **⚠️ User Cannot Recover Account**

- **Gap**: Users who forget password have NO way to login
- **Missing**: Forgot Password page + Reset Password flow
- **Impact**: P0 - System unusable for users who forget credentials
- **Risk**: Admin burden (manual password resets)

2. **⚠️ Admin Cannot Create Users**

- **Gap**: No UI to create teacher/parent/student accounts
- **Missing**: User Management CRUD interface
- **Impact**: P0 - Cannot onboard new users to system
- **Risk**: Project cannot launch without this

3. **⚠️ No Way to Monitor Security Events**

- **Gap**: Activity logs generated but not viewable
- **Missing**: Audit Log viewing page
- **Impact**: P1 - Security incidents undetectable
- **Risk**: Compliance issues, no accountability

### ⚠️ MODERATE GAPS (Incomplete Features)

4. **⚠️ Users Cannot Change Their Own Password**

- **Gap**: Once logged in, users stuck with initial password
- **Missing**: Profile page + Change Password flow
- **Impact**: P1 - Poor UX, security concern
- **Workaround**: Admin must reset password manually

5. **⚠️ Session Timeout Not User-Friendly**

- **Gap**: Backend expires session, but frontend shows generic error
- **Missing**: Idle detection + friendly "Session Expired" message
- **Impact**: P2 - Confusing UX when session ends
- **Workaround**: Users will see Inertia 419 error

6. **⚠️ No Access Denied Page**

- **Gap**: 403 errors show generic Laravel error
- **Missing**: Custom 403.vue page with branding
- **Impact**: P2 - Unprofessional error handling
- **Workaround**: Browser shows default error page

### ✅ NO GAPS (Properly Implemented)

- Login/Logout flow ✅
- First Login password change ✅
- Rate limiting + account lockout ✅ (backend)
- RBAC middleware ✅ (routes protected)
- Activity logging ✅ (backend only)

---

## PHASE 5: IMPLEMENTATION SEQUENCING

### P0 (Critical - Must Build First)

**Cannot ship without these:**

1. **User Management CRUD** (US-AUTH-006) - 3 days

- **Why First**: Admin needs to create accounts before anyone can use system
- **Dependencies**: None
- **Blocks**: All other features (no users = no testing)
- **Build Order**:

    1. Backend: `UserController` + Form Requests
    2. Frontend: `/admin/users` list page
    3. Frontend: Create/Edit forms
    4. Integration: Email service for default password
1. **Forgot Password Flow** (US-AUTH-003) - 2 days

- **Why First**: Critical recovery mechanism
- **Dependencies**: Email service setup
- **Blocks**: None (parallel with User Management)
- **Build Order**:

    1. Backend: `password_reset_tokens` migration
    2. Backend: `ForgotPasswordController` + `ResetPasswordController`
    3. Frontend: `/forgot-password` page
    4. Frontend: `/reset-password` page
    5. Email template

---

### P1 (Important - Complete Feature Set)

**Feature is incomplete without these:**

3. **Change Password (Authenticated)** (US-AUTH-004) - 1 day

- **Why**: Users need self-service password management
- **Dependencies**: User Management (for testing)
- **Can Build in Parallel**: Yes
- **Build Order**:

    1. Backend: `PasswordController@update`
    2. Frontend: Profile page skeleton
    3. Frontend: `ChangePasswordModal.vue`
    4. Integration: Optional logout other sessions
1. **Audit Log Viewing** (US-AUTH-009) - 1.5 days

- **Why**: Security monitoring for admin
- **Dependencies**: Existing logs need to be queryable
- **Can Build in Parallel**: Yes
- **Build Order**:

    1. Backend: `AuditLogController@index` with filters
    2. Frontend: `/admin/audit-logs` page
    3. Frontend: `AuditLogTable.vue` with pagination
    4. Optimization: Index on `activity_logs.created_at`
1. **403 Access Denied Page** - 0.5 day

- **Why**: Professional error handling
- **Dependencies**: None
- **Can Build in Parallel**: Yes
- **Build Order**:

    1. Frontend: `resources/js/pages/Errors/403.vue`
    2. Register in Inertia error handling

---

### P2 (Enhancement - Can Ship Later)

**Nice to have, but not blocking:**

6. **Session Timeout Detection (Frontend)** - 1 day

- **Why**: Better UX, but backend already handles expiry
- **Dependencies**: None
- **Can Build in Parallel**: Yes
- **Build Order**:

    1. Composable: `useSessionTimeout.ts`
    2. Component: `SessionTimeoutModal.vue`
    3. Integration: Add to AppLayout.vue
1. **Failed Login Countdown Timer** - 0.5 day

- **Why**: User-friendly lockout message
- **Dependencies**: Backend already tracks attempts
- **Can Build in Parallel**: Yes
- **Build Order**:

    1. Backend: Return `locked_until` in error response
    2. Frontend: Display countdown in Login.vue

---

### ⬜ PHASE 2 (Future - Not MVP)

- **US-AUTH-008**: Multiple Device Session Management
- **US-AUTH-010**: 2FA Setup + Login
- **"Remember Me"** functionality (mentioned but not critical)
- **WhatsApp integration** for password reset

---

## PHASE 6: DETAILED RECOMMENDATIONS

### A. New Pages/Routes Needed

#### Backend Routes (routes/auth.php)

```php
// Password Reset
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->middleware('throttle:3,60') // Max 3 requests per hour
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])
        ->name('password.update');
});

// Authenticated
Route::middleware('auth')->group(function () {
    // Profile & Password Change
    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');
    Route::post('/profile/password', [PasswordController::class, 'update'])
        ->name('profile.password.update');
});
```



#### Backend Routes (routes/web.php)

```php
// Admin Routes
Route::middleware(['auth', 'role:SUPERADMIN,ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    
    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index');
});

// Kepala Sekolah can also view audit logs
Route::middleware(['auth', 'role:PRINCIPAL'])->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index');
});
```



#### Frontend Pages

| Path | Component | Priority | Purpose |

|------|-----------|----------|---------|

| `/forgot-password` | `Auth/ForgotPassword.vue` | P0 | Request reset link |

| `/reset-password?token=xxx` | `Auth/ResetPassword.vue` | P0 | Execute password reset |

| `/profile` | `Profile/Show.vue` | P1 | User profile + change password |

| `/admin/users` | `Admin/Users/Index.vue` | P0 | User list (Super Admin/TU) |

| `/admin/users/create` | `Admin/Users/Create.vue` | P0 | Add new user |

| `/admin/users/{id}/edit` | `Admin/Users/Edit.vue` | P0 | Edit user |

| `/admin/audit-logs` | `Admin/AuditLogs/Index.vue` | P1 | Security logs (Admin/Principal) |

| `/errors/403` | `Errors/403.vue` | P1 | Access denied page |---

### B. Updates to Existing Pages

#### [resources/js/pages/Auth/Login.vue](resources/js/pages/Auth/Login.vue)

- **Add**: Link to `/forgot-password` (already has placeholder at line 284)
  ```vue
    <a href="/forgot-password" class="...">Lupa password?</a>
  ```

- **Add**: Display lockout countdown if `errors.identifier` contains "terkunci"
  ```vue
    <p v-if="lockoutRemaining > 0">
      Akun terkunci. Coba lagi dalam {{ lockoutRemaining }} menit.
    </p>
  ```

- **Priority**: P0 (blocking password recovery)

#### [resources/js/components/layouts/AppLayout.vue](resources/js/components/layouts/AppLayout.vue)

- **Add**: Navigation menu item "Manajemen User" for Super Admin/TU
  ```vue
    <NavItem v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'"
             :href="route('admin.users.index')"
             icon="Users">
      Manajemen User
    </NavItem>
  ```

- **Add**: Navigation menu item "Audit Log" for Super Admin/TU/Principal
- **Add**: User dropdown with "Profil Saya" link
- **Add**: Session timeout detector (integrate `useSessionTimeout`)
- **Priority**: P0 (navigation needed for new pages)

#### Existing Dashboard Pages

- **No Changes Needed**: Dashboards don't directly interact with auth data

---

### C. Navigation/Menu Changes

#### Admin (Super Admin + TU)

**Add to Sidebar/Navbar:**

```javascript
📊 Dashboard
👥 Manajemen User [NEW] → /admin/users
📚 Data Siswa
💰 Keuangan
📋 Audit Log [NEW] → /admin/audit-logs
⚙️ Pengaturan
```



#### Principal (Kepala Sekolah)

**Add to Sidebar/Navbar:**

```javascript
📊 Dashboard
📋 Audit Log [NEW] → /audit-logs (read-only)
📈 Laporan
```



#### All Users

**Add to User Dropdown (top-right):**

```javascript
[User Avatar]
  └─ Profil Saya [NEW] → /profile
  └─ Ganti Password [NEW] → /profile (modal)
  └─ Keluar (existing)
```

---

### D. Component Library Additions

#### Priority P0 (Build Immediately)

1. **`UserTable.vue`** - Used By: `Admin/Users/Index.vue`

- Props: `users`, `loading`, `onEdit`, `onDelete`, `onResetPassword`, `onToggleStatus`
- Features: Search, filter by role/status, pagination, quick actions
- Mobile: Responsive cards instead of table

2. **`UserForm.vue`** - Used By: `Admin/Users/Create.vue`, `Admin/Users/Edit.vue`

- Props: `user` (optional, for edit mode), `roles`, `onSubmit`
- Fields: Nama, Email, Username, Role, Status
- Validation: Real-time, Indonesian error messages

3. **`PasswordStrengthMeter.vue`** - Used By: Password-related forms

- Props: `password`, `minLength`
- Display: Progress bar (red → yellow → green)
- Labels: "Lemah", "Sedang", "Kuat"

#### Priority P1 (Build After P0)

4. **`AuditLogTable.vue`** - Used By: `Admin/AuditLogs/Index.vue`

- Props: `logs`, `loading`
- Features: Date range filter, user filter, action filter, export CSV
- Display: Color-coded status (success=green, failed=red)

5. **`ChangePasswordModal.vue`** - Used By: `Profile/Show.vue`

- Props: `show`, `onClose`
- Fields: Password Lama, Password Baru, Konfirmasi
- Features: Password strength indicator, toggle visibility

6. **`SessionTimeoutModal.vue`** - Used By: `AppLayout.vue`

- Props: `remainingSeconds`, `onExtend`, `onLogout`
- Display: "Session akan berakhir dalam X menit"
- Actions: "Perpanjang Session", "Keluar"

#### Priority P2 (Nice to Have)

7. **`AccessDeniedIllustration.vue`** - Used By: `Errors/403.vue`

- Props: None
- Display: SVG illustration of locked door or shield
- Style: Match iOS design system

---

## PHASE 7: EXAMPLE USER JOURNEYS

### TOP 1: User Management (CRITICAL - P0)

#### Owner Journey (Admin Creates New Teacher Account)

1. **User navigates to**: `/admin/dashboard`
2. **User clicks**: Sidebar menu "Manajemen User"
3. **System displays**: `/admin/users` - Table with existing users (Super Admin, self)
4. **User clicks**: Button "Tambah User Baru" (top-right, blue primary button)
5. **System displays**: `/admin/users/create` - Form with fields:

- Nama Lengkap (required, text)
- Email (required, email format)
- Username (required, unique, min 3 chars)
- Role (required, dropdown: Super Admin/Kepala Sekolah/TU/Guru/Orang Tua)
- Status (toggle: Aktif/Nonaktif, default Aktif)

6. **User fills**:

- Nama: "Ibu Siti Rahmawati"
- Email: "siti.rahmawati@sekolah.sch.id"
- Username: "siti.guru"
- Role: "Guru"
- Status: "Aktif"

7. **User clicks**: Button "Simpan" (bottom-right)
8. **System does**:

- Validate input (backend)
- Generate default password: "Siti1234" (Nama + 4 random digits)
- Hash password with bcrypt
- Set `is_first_login = true`
- Insert into `users` table
- Log activity: `action='create_user', user_id=admin_id, new_values=siti.guru`
- Send email to siti.rahmawati@sekolah.sch.id:
     ```javascript
          Subject: Akun Anda Telah Dibuat
          
          Halo Ibu Siti Rahmawati,
          
          Akun Anda di Sistem Informasi Sekolah telah dibuat:
          Username: siti.guru
          Password: Siti1234
          
          Silakan login dan ganti password Anda.
     ```


9. **User sees**:

- Success toast: "User berhasil ditambahkan. Password telah dikirim ke email."
- Redirect to `/admin/users` with Ibu Siti in table
- Haptic feedback: `haptics.success()`

#### Consumer Journey (Teacher First Login)

1. **User (Ibu Siti) navigates to**: `/login`
2. **User fills**: Username "siti.guru", Password "Siti1234"
3. **System validates**: Credentials correct, but `is_first_login = true`
4. **System redirects**: `/first-login` (force change password page)
5. **User sees**: Form with fields:

- Password Baru (required, min 8 chars, with strength meter)
- Konfirmasi Password (required, must match)

6. **User fills**: Password "SitiGuru2025!", Konfirmasi "SitiGuru2025!"
7. **System does**:

- Validate: min 8 chars ✅, huruf + angka ✅, tidak sama dengan default ✅
- Update password
- Set `is_first_login = false`
- Log activity: `action='first_login_password_change', user_id=siti_id`

8. **User sees**: Redirect to `/teacher/dashboard` with welcome message
9. **User achieves**: Can now access Teacher Dashboard with secure password

---

### TOP 2: Forgot Password Flow (CRITICAL - P0)

#### Owner Journey (User Requests Password Reset)

1. **User navigates to**: `/login`
2. **User clicks**: Link "Lupa password?" (below login button)
3. **System displays**: `/forgot-password` - Form with:

- Email or No HP (input field)
- Button "Kirim Link Reset"
- Info text: "Link reset akan dikirim ke email Anda dan valid selama 1 jam"

4. **User fills**: Email "siti.rahmawati@sekolah.sch.id"
5. **User clicks**: "Kirim Link Reset"
6. **System does**:

- Check email exists in `users` table ✅
- Check throttling: max 3 requests/24 hours per email ✅
- Generate unique token: `$token = Str::random(64)`
- Insert into `password_reset_tokens`:
     ```javascript
          email: siti.rahmawati@sekolah.sch.id
          token: hashed($token)
          created_at: now()
     ```

- Send email:
     ```javascript
          Subject: Reset Password Anda
          
          Klik link berikut untuk reset password (valid 1 jam):
          https://sekolah.app/reset-password?token=abc123def456
          
          Jika Anda tidak meminta reset, abaikan email ini.
     ```

- Log activity: `action='password_reset_requested', user_id=siti_id`

7. **User sees**:

- Success message: "Link reset password telah dikirim ke email Anda. Periksa inbox Anda."
- Disable button for 60 seconds (prevent spam)
- Haptic feedback: `haptics.success()`

#### Consumer Journey (User Resets Password)

1. **User opens email**, clicks link
2. **System validates**:

- Token exists ✅
- Token not expired (< 1 hour) ✅
- Email matches token ✅

3. **System displays**: `/reset-password?token=abc123def456` - Form with:

- Email (pre-filled, read-only)
- Password Baru (input with strength meter)
- Konfirmasi Password (input)
- Button "Ubah Password"

4. **User fills**: Password "SitiGuru2025!", Konfirmasi "SitiGuru2025!"
5. **User clicks**: "Ubah Password"
6. **System does**:

- Validate: min 8 chars ✅, huruf + angka ✅
- Update password in `users` table
- Delete token from `password_reset_tokens`
- Log activity: `action='password_reset_completed', user_id=siti_id`
- Optional: Logout all active sessions for security

7. **User sees**:

- Success message: "Password berhasil diubah. Silakan login dengan password baru."
- Redirect to `/login` after 2 seconds
- Haptic feedback: `haptics.success()`

8. **User can**: Login dengan password baru "SitiGuru2025!"
9. **User achieves**: Regained access to account without admin help

---

### TOP 3: Audit Log Monitoring (IMPORTANT - P1)

#### Owner Journey (System Auto-Generates Logs)

**No manual action needed - system automatically logs:**

- Every login (success/failed)
- Every logout
- User CRUD operations by admin
- Password changes
- Sensitive data access (future: payment edits, grade changes)

#### Consumer Journey (Admin Views Security Events)

1. **User (Super Admin) navigates to**: `/admin/dashboard`
2. **User clicks**: Sidebar menu "Audit Log"
3. **System displays**: `/admin/audit-logs` - Table with columns:

- Timestamp (sortable, default desc)
- User (link to user profile)
- Action (badge: login/logout/create_user/failed_login)
- Status (badge: success=green, failed=red)
- IP Address
- User Agent (truncated, full on hover)
- Details (expandable JSON)

4. **User sees**: Latest 50 logs, paginated
5. **User can**: Filter by:

- Date Range: "Last 7 days" (default), Custom range picker
- User: Dropdown (all users)
- Action: Multi-select (login, logout, failed_login, etc.)
- Status: Success/Failed

6. **Admin notices**: Multiple "failed_login" from same IP

- IP: 192.168.1.100
- User: "siti.guru"
- Attempts: 5 (status = failed)
- Last attempt: 2025-12-23 14:35 WIB

7. **Admin clicks**: Row to expand details:
   ```json
      {
        "identifier": "siti.guru",
        "attempts": 5,
        "locked_until": "2025-12-23 14:50 WIB"
      }
   ```

8. **Admin can**: (Optional) Button "Unlock Account" to reset failed attempts
9. **System does**: Delete `FailedLoginAttempt` record
10. **Admin achieves**: Identified security incident, helped locked-out user

---

## CRITICAL REMINDERS

### 1. Data Creation WITHOUT Display = Broken Feature

- ✅ Activity logs are generated (backend), but **NO PAGE** to view them → Audit feature incomplete
- ✅ Failed login attempts are tracked, but **NO COUNTDOWN** shown to user → Poor UX

### 2. Mobile-First Check

- ALL new pages must work on mobile (min width 320px)
- User Management table → Use cards on mobile, not horizontal scroll
- Audit Log table → Collapse columns, show essentials only

### 3. Navigation Must Be Discoverable

- Users CANNOT GUESS URLs like `/admin/users`
- **MUST ADD** sidebar menu items:
- "Manajemen User" for Admin/TU
- "Audit Log" for Admin/TU/Principal
- "Profil Saya" in user dropdown (all roles)

### 4. Empty States Are Critical

- `/admin/users` with no users → Show "Belum ada user. Klik 'Tambah User' untuk memulai."
- `/admin/audit-logs` with no logs → Show "Belum ada aktivitas tercatat."

### 5. Search/Filter for Scalability

- User list will grow → Need search by name/username/email
- Audit logs will be massive → Date range filter is MANDATORY

### 6. Indonesian Language Consistency

- All error messages, labels, buttons in Indonesian
- Examples:
- "Simpan" not "Save"
- "Batal" not "Cancel"
- "Password lama salah" not "Old password is incorrect"

---

## ESTIMATED TIMELINE (1 Developer)

| Priority | Feature | Effort | Cumulative |

|----------|---------|--------|------------|

| P0 | User Management CRUD | 3 days | 3 days |

| P0 | Forgot + Reset Password | 2 days | 5 days |

| P1 | Change Password (Auth) | 1 day | 6 days |

| P1 | Audit Log Viewing | 1.5 days | 7.5 days |

| P1 | 403 Error Page | 0.5 day | 8 days |

| P2 | Session Timeout Frontend | 1 day | 9 days |

| P2 | Lockout Countdown Timer | 0.5 day | 9.5 days |**Total Phase 1:** ~9.5 days (2 work weeks)**Phase 2 (Future):**

- MFA Setup + Login: 5 days
- Multi-Device Session Management: 3 days
- WhatsApp Integration: 2 days

---

## NEXT STEPS

1. **Confirm Priorities**: Review P0/P1/P2 classification with stakeholders
2. **Setup Email Service**: Configure SMTP/SendGrid for password reset emails
3. **Database Review**: Confirm `password_reset_tokens` table exists or create migration
4. **Start with P0**: Begin User Management CRUD (highest business value)
5. **Parallel Development**: Forgot Password can be built simultaneously by different developer
6. **Testing**: Each feature needs test cases from spec (TS-AUTH-001 through TS-AUTH-008)

---**Document Version:** 1.0

**Analysis Date:** December 23, 2025
