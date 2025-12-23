# API Documentation: Authentication

## Overview

API untuk mengelola authentication flow, yaitu: login, logout, forgot password, reset password, first login, dan profile management.

**Base URL:** `{APP_URL}` (configured in .env)  
**Authentication:** Session-based (Laravel Sanctum SPA)

---

## Authentication Endpoints

### 1. Login

Authenticate user dengan username/email dan password.

**Endpoint:** `POST /login`  
**Middleware:** `guest`, `throttle:5,1` (max 5 attempts per minute)  
**Route Name:** `login.post`

**Request Body:**

```json
{
  "identifier": "admin@sekolah.sch.id",
  "password": "Password123",
  "remember": true
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| identifier | string | Yes | Username atau email |
| password | string | Yes | Min 8 chars |
| remember | boolean | No | "Ingat Saya" checkbox |

**Success Response:** `302 Redirect`

```
Location: /dashboard (atau /first-login jika is_first_login=true)
```

**Error Response:** `302 Redirect Back` dengan errors

```json
{
  "errors": {
    "identifier": ["Username/email atau password salah, atau akun tidak aktif."]
  }
}
```

**Account Lockout Response:**

```json
{
  "errors": {
    "identifier": ["Akun terkunci karena terlalu banyak percobaan login gagal. Silakan coba lagi dalam 15 menit."],
    "locked_until": 1703401200
  }
}
```

**Side Effects:**
- Creates `ActivityLog` record (action: `login`)
- Updates `users.last_login_at`, `users.last_login_ip`
- Deletes `FailedLoginAttempt` record on success
- Creates/updates `FailedLoginAttempt` record on failure

---

### 2. Logout

Log out current user dan invalidate session.

**Endpoint:** `POST /logout`  
**Middleware:** `auth`  
**Route Name:** `logout`

**Success Response:** `302 Redirect`

```
Location: /login
Flash message: "Anda telah keluar dari sistem."
```

**Side Effects:**
- Creates `ActivityLog` record (action: `logout`)
- Invalidates session
- Regenerates CSRF token

---

### 3. Forgot Password - Request Reset Link

Request password reset link via email.

**Endpoint:** `POST /forgot-password`  
**Middleware:** `guest`, `throttle:3,60` (max 3 attempts per hour)  
**Route Name:** `password.email`

**Request Body:**

```json
{
  "email": "user@sekolah.sch.id"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| email | string | Yes | Valid email, exists in users table |

**Success Response:** `302 Redirect Back`

```
Flash message: "Link reset password telah dikirim ke email Anda. Periksa inbox Anda (link valid selama 1 jam)."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "email": ["Anda telah mencapai batas maksimal permintaan reset password (3x per 24 jam)."]
  }
}
```

**Side Effects:**
- Generates SHA-256 hashed token
- Inserts record to `password_reset_tokens` table
- Deletes old tokens for same email
- Sends email via `PasswordResetMail` mailable
- Creates `ActivityLog` record (action: `password_reset_requested`)

**Email Content:**
- Subject: "Reset Password - Sistem Informasi Sekolah"
- Contains: Reset link with token
- Expiry warning: 1 hour

---

### 4. Reset Password - Execute Reset

Reset password menggunakan token dari email.

**Endpoint:** `POST /reset-password`  
**Middleware:** `guest`  
**Route Name:** `password.update`

**Request Body:**

```json
{
  "token": "abc123def456...",
  "email": "user@sekolah.sch.id",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| token | string | Yes | Valid reset token |
| email | string | Yes | Valid email, exists in users |
| password | string | Yes | Min 8 chars, letters, numbers, uncompromised, confirmed |
| password_confirmation | string | Yes | Must match password |

**Success Response:** `302 Redirect`

```
Location: /login
Flash message: "Password berhasil diubah. Silakan login dengan password baru Anda."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "email": ["Link reset password tidak valid atau sudah kadaluarsa."]
  }
}
```

**Side Effects:**
- Updates `users.password`
- Deletes used token from `password_reset_tokens`
- Creates `ActivityLog` record (action: `password_reset_completed`)

---

### 5. Change Password (Authenticated)

Change password untuk user yang sudah login.

**Endpoint:** `POST /profile/password`  
**Middleware:** `auth`  
**Route Name:** `profile.password.update`

**Request Body:**

```json
{
  "current_password": "OldPassword123",
  "password": "NewPassword456",
  "password_confirmation": "NewPassword456"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| current_password | string | Yes | Must match current password |
| password | string | Yes | Min 8 chars, letters, numbers, different from current |
| password_confirmation | string | Yes | Must match password |

**Success Response:** `302 Redirect Back`

```
Flash message: "Password berhasil diubah."
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "current_password": ["Password lama tidak sesuai."],
    "password": ["Password baru harus berbeda dengan password lama."]
  }
}
```

**Side Effects:**
- Updates `users.password`
- Creates `ActivityLog` record (action: `password_changed`)

---

### 6. First Login - Force Password Change

Force change password untuk user yang baru pertama kali login.

**Endpoint:** `POST /first-login`  
**Middleware:** `auth`  
**Route Name:** `auth.first-login.update`

**Request Body:**

```json
{
  "password": "MyNewPassword123",
  "password_confirmation": "MyNewPassword123"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| password | string | Yes | Min 8 chars, letters, numbers, uncompromised |
| password_confirmation | string | Yes | Must match password |

**Success Response:** `302 Redirect`

```
Location: /dashboard (sesuai role)
```

**Error Response:** `302 Redirect Back`

```json
{
  "errors": {
    "password": ["Password minimal 8 karakter.", "Password harus mengandung huruf."]
  }
}
```

**Side Effects:**
- Updates `users.password`
- Sets `users.is_first_login = false`
- Creates `ActivityLog` record (action: `first_login_password_change`)

---

## Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body validation failed |
| UNAUTHENTICATED | 401 | Not logged in atau session expired |
| RATE_LIMIT_EXCEEDED | 429 | Too many requests |
| ACCOUNT_LOCKED | 423 | Account locked due to failed attempts |
| TOKEN_EXPIRED | 410 | Password reset token expired |
| TOKEN_INVALID | 400 | Password reset token tidak valid |

---

## Rate Limiting

| Endpoint | Limit | Window |
|----------|-------|--------|
| `POST /login` | 5 attempts | 1 minute |
| `POST /forgot-password` | 3 attempts | 1 hour |
| Failed login (per identifier + IP) | 5 attempts | 15 minutes lockout |
| Password reset request (per email) | 3 requests | 24 hours |

---

## Session Management

- **Session Driver:** Configured via `SESSION_DRIVER` env
- **Session Lifetime:** Configured via `SESSION_LIFETIME` env (default: 120 minutes)
- **Session Regeneration:** On login dan logout untuk prevent session fixation
- **Remember Me:** Stores remember token untuk 2 weeks

---

*Last Updated: 2025-12-23*
