# API Documentation: User Management

## Overview

API untuk mengelola user accounts (CRUD operations), hanya accessible oleh Super Admin dan TU.

**Base URL:** `{APP_URL}/admin`  
**Authentication:** Session-based + Role check (SUPERADMIN, ADMIN)  
**Middleware:** `auth`, `role:SUPERADMIN,ADMIN`

---

## User Management Endpoints

### 1. List Users

Mengambil daftar users dengan pagination, search, dan filter.

**Endpoint:** `GET /admin/users`  
**Route Name:** `admin.users.index`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| search | string | No | - | Search by name, username, email |
| role | string | No | - | Filter by role (SUPERADMIN, ADMIN, PRINCIPAL, TEACHER, PARENT) |
| status | string | No | - | Filter by status (active, inactive) |
| page | integer | No | 1 | Page number |

**Response:** `200 OK` (Inertia render)

Renders: `Admin/Users/Index.vue`

**Props:**
```typescript
{
  users: PaginatedData<User>,
  filters: {
    search: string | null,
    role: string | null,
    status: string | null
  }
}
```

**User Object:**
```typescript
interface User {
  id: number,
  name: string,
  username: string,
  email: string,
  phone_number: string | null,
  role: 'SUPERADMIN' | 'ADMIN' | 'PRINCIPAL' | 'TEACHER' | 'PARENT',
  status: 'active' | 'inactive',
  is_first_login: boolean,
  last_login_at: string | null,
  last_login_ip: string | null,
  created_at: string,
  updated_at: string
}
```

---

### 2. Create User Form

Menampilkan form untuk create user baru.

**Endpoint:** `GET /admin/users/create`  
**Route Name:** `admin.users.create`

**Response:** `200 OK` (Inertia render)

Renders: `Admin/Users/Create.vue`

---

### 3. Store User

Membuat user baru dengan auto-generated password.

**Endpoint:** `POST /admin/users`  
**Route Name:** `admin.users.store`

**Request Body:**

```json
{
  "name": "Ibu Siti Rahmawati",
  "email": "siti.rahmawati@sekolah.sch.id",
  "username": "siti.guru",
  "phone_number": "081234567890",
  "role": "TEACHER",
  "status": "active"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | Max 255 chars |
| email | string | Yes | Valid email, unique |
| username | string | Yes | Min 3, max 50 chars, unique, alphanumeric + dot/underscore only |
| phone_number | string | No | Max 20 chars |
| role | string | Yes | Enum: SUPERADMIN, ADMIN, PRINCIPAL, TEACHER, PARENT |
| status | string | Yes | Enum: active, inactive |

**Success Response:** `302 Redirect`

```
Location: /admin/users
Flash message: "User berhasil ditambahkan. Password telah dikirim ke email."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "email": ["Email sudah digunakan oleh user lain."],
    "username": ["Username sudah digunakan oleh user lain."]
  }
}
```

**Side Effects:**
- Generates default password: `{FirstName}{4-digit-random}` (e.g., "Siti1234")
- Sets `is_first_login = true`
- Sends email via `UserAccountCreated` mailable
- Creates `ActivityLog` record (action: `create_user`)

---

### 4. Show User

Menampilkan detail user (jika diperlukan di UI).

**Endpoint:** `GET /admin/users/{user}`  
**Route Name:** `admin.users.show`

**Response:** `200 OK` (Inertia render)

---

### 5. Edit User Form

Menampilkan form untuk edit user existing.

**Endpoint:** `GET /admin/users/{user}/edit`  
**Route Name:** `admin.users.edit`

**Response:** `200 OK` (Inertia render)

Renders: `Admin/Users/Edit.vue`

**Props:**
```typescript
{
  user: User
}
```

---

### 6. Update User

Update user data existing.

**Endpoint:** `PUT|PATCH /admin/users/{user}`  
**Route Name:** `admin.users.update`

**Request Body:**

```json
{
  "name": "Ibu Siti Rahmawati (Updated)",
  "email": "siti.rahmawati@sekolah.sch.id",
  "username": "siti.guru",
  "phone_number": "081234567890",
  "role": "TEACHER",
  "status": "active"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | Max 255 chars |
| email | string | Yes | Valid email, unique (ignore current user) |
| username | string | Yes | Min 3, max 50 chars, unique (ignore current user) |
| phone_number | string | No | Max 20 chars |
| role | string | Yes | Enum: SUPERADMIN, ADMIN, PRINCIPAL, TEACHER, PARENT |
| status | string | Yes | Enum: active, inactive |

**Success Response:** `302 Redirect`

```
Location: /admin/users
Flash message: "User berhasil diupdate."
// Jika role berubah:
Flash message: "User berhasil diupdate. User akan diminta login ulang untuk perubahan role."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "email": ["Email sudah digunakan oleh user lain."]
  }
}
```

**Side Effects:**
- Updates user data
- Creates `ActivityLog` record (action: `update_user`, includes old_values & new_values)
- If role/status changed: Terminates active sessions (TODO: requires database session driver)

---

### 7. Delete User

Menghapus user dari sistem.

**Endpoint:** `DELETE /admin/users/{user}`  
**Route Name:** `admin.users.destroy`

**Success Response:** `302 Redirect`

```
Location: /admin/users
Flash message: "User berhasil dihapus."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "error": ["Anda tidak dapat menghapus akun Anda sendiri."]
  }
}
```

**Business Rules:**
- ❌ Cannot delete yourself (`auth()->id() === user->id`)
- ❌ Cannot delete last Super Admin (count active SUPERADMIN >= 1)

**Side Effects:**
- Deletes user record (hard delete, not soft)
- Creates `ActivityLog` record (action: `delete_user`)

---

### 8. Reset User Password

Admin manually reset password untuk user.

**Endpoint:** `POST /admin/users/{user}/reset-password`  
**Route Name:** `admin.users.reset-password`

**Success Response:** `302 Redirect Back`

```
Flash message: "Password berhasil direset. Password baru telah dikirim ke email user."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "error": ["Gagal mereset password. Silakan coba lagi."]
  }
}
```

**Side Effects:**
- Generates new default password: `{FirstName}{4-digit-random}`
- Sets `is_first_login = true` (force user to change password)
- Sends email via `UserAccountCreated` mailable
- Creates `ActivityLog` record (action: `reset_user_password`)

---

### 9. Toggle User Status

Toggle status user antara active dan inactive.

**Endpoint:** `PATCH /admin/users/{user}/toggle-status`  
**Route Name:** `admin.users.toggle-status`

**Success Response:** `302 Redirect Back`

```
Flash message: "User berhasil diaktifkan." // or "User berhasil dinonaktifkan."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "error": ["Anda tidak dapat menonaktifkan akun Anda sendiri."]
  }
}
```

**Business Rules:**
- ❌ Cannot deactivate yourself
- ❌ Cannot deactivate last active Super Admin

**Side Effects:**
- Toggles `users.status` between `active` and `inactive`
- Creates `ActivityLog` record (action: `toggle_user_status`)
- If deactivated: Terminates active sessions (TODO)

---

## Audit Log Endpoint

### List Activity Logs

Mengambil activity logs dengan advanced filtering.

**Endpoint:** `GET /admin/audit-logs`  
**Route Name:** `admin.audit-logs.index`  
**Access:** Super Admin, TU (full access), Principal (read-only via `/audit-logs`)

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| date_from | string | No | now()-7days | Start date (YYYY-MM-DD) |
| date_to | string | No | today | End date (YYYY-MM-DD) |
| user_id | integer | No | - | Filter by specific user ID |
| actions | array | No | - | Filter by actions (multiple selection) |
| status | string | No | - | Filter by status (success, failed) |
| search | string | No | - | Search by IP address or identifier |
| page | integer | No | 1 | Page number |

**Response:** `200 OK` (Inertia render)

Renders: `Admin/AuditLogs/Index.vue`

**Props:**
```typescript
{
  logs: PaginatedData<ActivityLog>,
  users: User[],
  availableActions: string[],
  filters: {
    date_from: string,
    date_to: string,
    user_id: number | null,
    actions: string[],
    status: string | null,
    search: string | null
  }
}
```

**ActivityLog Object:**
```typescript
interface ActivityLog {
  id: number,
  user_id: number | null,
  user: {
    id: number,
    name: string,
    username: string,
    role: string
  } | null,
  action: string,
  ip_address: string,
  user_agent: string,
  old_values: object | null,
  new_values: object | null,
  status: 'success' | 'failed',
  created_at: string,
  updated_at: string
}
```

**Available Actions:**
- `login`
- `logout`
- `failed_login`
- `password_reset_requested`
- `password_reset_completed`
- `password_changed`
- `first_login_password_change`
- `create_user`
- `update_user`
- `delete_user`
- `reset_user_password`
- `toggle_user_status`

---

## Profile Endpoint

### View Profile

Menampilkan profile page user yang sedang login.

**Endpoint:** `GET /profile`  
**Route Name:** `profile.show`  
**Access:** All authenticated users

**Response:** `200 OK` (Inertia render)

Renders: `Profile/Show.vue`

**Props:**
```typescript
{
  user: {
    id: number,
    name: string,
    username: string,
    email: string,
    phone_number: string | null,
    role: string,
    status: string,
    last_login_at: string | null,
    created_at: string
  }
}
```

---

## Email Notifications

### 1. User Account Created Email

**Trigger:** Admin creates new user  
**Mailable:** `UserAccountCreated`  
**Template:** `resources/views/emails/user-account-created.blade.php`  
**Queue:** Yes (implements ShouldQueue)

**Subject:** "Akun Anda Telah Dibuat - Sistem Informasi Sekolah"

**Content:**
- Username
- Default password
- Login URL
- Warning: harus ganti password saat first login

---

### 2. Password Reset Email

**Trigger:** User requests forgot password  
**Mailable:** `PasswordResetMail`  
**Template:** `resources/views/emails/password-reset.blade.php`  
**Queue:** Yes (implements ShouldQueue)

**Subject:** "Reset Password - Sistem Informasi Sekolah"

**Content:**
- Reset URL dengan token
- Expiry warning: 1 hour
- Security tips
- Ignore instruction jika tidak request

---

*Last Updated: 2025-12-23*

