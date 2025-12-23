# Feature: AUTH-P3 - Password Reset Flow

> **Code:** AUTH-P3 | **Priority:** High | **Status:** ✅ Complete
> **Sprint:** 1-2 | **Menu:** Login > Lupa Password

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=password` (4 routes found)
- [x] Vue pages exist: ForgotPassword, ResetPassword
- [x] Component exists: PasswordStrengthMeter
- [x] Wayfinder routes generated: `@/routes/password`
- [x] Following DOCUMENTATION_GUIDE.md template

---

## Overview

Password Reset Flow merupakan fitur security recovery yang bertujuan untuk membantu user mendapatkan kembali akses ke akun, yaitu: request reset link melalui email dengan rate limiting protection, validasi token expiration (1 jam), set password baru dengan strength meter, dan automatic redirect ke login setelah success dengan iOS-like design yang dioptimasi untuk low-end devices.

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| US-03 | All Users | Request reset link untuk mendapatkan akses kembali | • Forgot password page<br>• Email input dengan validation<br>• Rate limiting UI (60s cooldown)<br>• Success state notification | ✅ Complete |
| US-04 | All Users | Set password baru dengan reset token untuk login kembali | • Reset password page dengan token<br>• Password strength meter<br>• Token expiration handling<br>• Auto-redirect ke login setelah success | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-01 | Password reset link valid selama 1 jam | Token expiration check di backend |
| BR-02 | Password harus minimal 8 karakter dengan kombinasi huruf & angka | Frontend validation + backend FormRequest |
| BR-03 | Forgot password dibatasi 3 request per 60 menit per user | Throttle middleware `throttle:3,60` |
| BR-04 | Reset token hanya bisa digunakan 1 kali | Token invalidation setelah success |
| BR-05 | Email harus terdaftar dalam sistem untuk request reset | Backend validation di ForgotPasswordController |

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| Controller | `app/Http/Controllers/Auth/ForgotPasswordController.php` | Handle forgot password request |
| Controller | `app/Http/Controllers/Auth/ResetPasswordController.php` | Handle password reset execution |
| Request | `app/Http/Requests/Auth/ResetPasswordRequest.php` | Validation dengan strong password requirements |
| Model | `app/Models/User.php` | User entity dengan password management |
| Page | `resources/js/pages/Auth/ForgotPassword.vue` | Form request reset link |
| Page | `resources/js/pages/Auth/ResetPassword.vue` | Form set password baru |
| Component | `resources/js/components/ui/PasswordStrengthMeter.vue` | Visual password strength indicator |
| Routes | `resources/js/routes/password/index.ts` | Wayfinder type-safe routes |

### Routes Summary

| Method | URI | Name | Controller Method | Frontend |
|--------|-----|------|------------------|----------|
| GET | `/forgot-password` | `password.request` | `ForgotPasswordController@create` | ForgotPassword.vue |
| POST | `/forgot-password` | `password.email` | `ForgotPasswordController@store` | Form submit |
| GET | `/reset-password/{token}` | `password.reset` | `ResetPasswordController@create` | ResetPassword.vue |
| POST | `/reset-password` | `password.update` | `ResetPasswordController@store` | Form submit |

**Middleware:** 
- `guest` (forgot password page & form)
- `throttle:3,60` (forgot password POST only)
- No middleware for reset display (token validation di controller)

> 📡 Full API documentation: [Authentication API - Password Reset Section](../../api/authentication.md#password-reset)

### Database

> 📌 Menggunakan Laravel's built-in password reset system:
> - Tabel `password_reset_tokens` dengan columns: `email`, `token`, `created_at`
> - Token expired setelah 60 menit (config: `config/auth.php`)

## Data Structures

```typescript
// Forgot Password Request
interface ForgotPasswordRequest {
    email: string;  // Email address (required, must exist)
}

// Reset Password Request
interface ResetPasswordRequest {
    token: string;                // From URL parameter
    email: string;                // From URL query or hidden field
    password: string;             // New password (min 8 chars, mixed)
    password_confirmation: string;  // Must match password
}

// Password Strength Validation
interface PasswordStrength {
    score: 0 | 1 | 2 | 3 | 4;    // Weak, Fair, Good, Strong, Very Strong
    feedback: string;              // Real-time feedback message
    passed: boolean;               // Min 8 chars + complexity
}
```

## UI/UX Specifications

### Forgot Password Page

**Layout:**
- Centered card dengan max-width 400px
- Email input field
- Submit button "Kirim Link Reset"
- "Kembali ke Login" link
- Success state dengan checkmark icon

**Flow:**
1. User input email
2. Click "Kirim Link Reset"
3. Loading state (spinner)
4. Success: Show checkmark + message "Link reset telah dikirim ke email Anda"
5. Show countdown timer untuk rate limit (60 detik)

**Rate Limiting UI:**
- Jika hit limit: Disable button + show countdown "Coba lagi dalam 45 detik"
- Countdown real-time update setiap detik
- Button re-enable setelah countdown selesai

### Reset Password Page

**Layout:**
- Centered card dengan max-width 400px
- Hidden email field (pre-filled dari URL)
- Password input dengan show/hide toggle
- Password confirmation input
- Password strength meter (visual bar)
- Submit button "Reset Password"
- Auto-redirect ke login setelah 3 detik

**Password Strength Meter:**
- Visual progress bar dengan color coding:
  - Red (0-25%): Weak
  - Orange (25-50%): Fair
  - Yellow (50-75%): Good
  - Green (75-90%): Strong
  - Blue (90-100%): Very Strong
- Real-time feedback message
- Requirements checklist (8+ chars, mixed case, numbers, symbols)

**Design:**
- iOS-style inputs dengan rounded corners
- Floating labels
- Eye/EyeOff icon untuk toggle visibility
- Haptic feedback pada button press
- Success toast notification

## Edge Cases & Handling

| Scenario | Detection | Handling | User Feedback |
|----------|-----------|----------|---------------|
| **Token expired** | Backend validation di ResetPasswordController | Redirect ke `/forgot-password` | Toast: "Link reset sudah kadaluarsa. Silakan request ulang." |
| **Email tidak ditemukan** | Backend error response 422 | Show inline error | "Email tidak terdapat dalam sistem" |
| **Rate limit exceeded** | Throttle middleware 429 response | Show cooldown timer | "Terlalu banyak percobaan. Coba lagi dalam 45 detik." |
| **Token invalid** | Backend validation error | Redirect ke forgot password | "Link reset tidak valid. Silakan request ulang." |
| **Password too weak** | Frontend validation | Show inline error | "Password harus minimal 8 karakter dengan kombinasi huruf, angka, dan simbol" |
| **Password mismatch** | Frontend + backend validation | Show inline error | "Konfirmasi password tidak cocok" |
| **Network error** | Inertia error handler | Show error toast | "Koneksi bermasalah. Periksa internet Anda." |
| **CSRF token expired** | 419 error | Reload page | "Session expired. Silakan coba lagi." |

## Wayfinder Integration

```typescript
// Import Wayfinder routes
import { email, request, reset, update } from '@/routes/password';
import { login } from '@/routes';

// Usage in components
<Link :href="request()">Lupa Password</Link>
<Link :href="login()">Kembali ke Login</Link>

// Form submission (need .url)
form.post(email().url);         // Forgot password submit
form.post(update().url);        // Reset password submit

// Navigation
router.get(reset(token).url);   // Navigate to reset page
```

> 📊 **Flow Diagram:** Lihat [Authentication User Journeys - Password Reset Section](../../guides/auth-user-journeys.md#password-reset) untuk detailed flow diagram.

## iOS-like Design Implementation

### Design Standards Applied

| Standard | Implementation | Example |
|----------|----------------|---------|
| **Spring Physics** | `stiffness: 300, damping: 30` | Page entrance, success checkmark |
| **Press Feedback** | `:whileTap="{ scale: 0.97 }"` | Submit buttons |
| **Fake Glass** | `bg-white/95` (no blur) | Card background |
| **Crisp Borders** | `border border-gray-200 shadow-sm` | Input fields, cards |
| **Haptic Feedback** | `haptics.light()` on tap | Button press |
| **Typography** | System sans, clear hierarchy | Headings, labels |

### Animation Patterns

```vue
<!-- Success Checkmark Animation -->
<Motion 
    :initial="{ scale: 0, opacity: 0 }"
    :animate="{ scale: 1, opacity: 1 }"
    :transition="{ type: 'spring', stiffness: 300, damping: 20 }"
>
    <CheckCircleIcon class="text-green-500 w-16 h-16" />
</Motion>

<!-- Countdown Timer -->
<Motion 
    :animate="{ opacity: [1, 0.5, 1] }"
    :transition="{ duration: 1, repeat: Infinity }"
>
    <span>Coba lagi dalam {{ countdown }} detik</span>
</Motion>
```

## Security Considerations

| Area | Implementation | Protection Against |
|------|----------------|-------------------|
| **Rate Limiting** | Throttle middleware 3 req/60min | Brute force attacks |
| **Token Expiration** | 1 hour validity | Token replay attacks |
| **Single-Use Token** | Invalidate after successful reset | Multiple password changes |
| **CSRF Protection** | Laravel Sanctum (automatic) | Cross-site request forgery |
| **Password Strength** | Real-time validation (8+ chars, mixed) | Weak passwords |
| **XSS Prevention** | Vue automatic escaping | Script injection |
| **Email Validation** | Backend check if email exists | Information disclosure minimized |

## Performance Optimizations

| Technique | Implementation | Benefit |
|-----------|----------------|---------|
| **No Heavy Blur** | `bg-white/95` instead of `backdrop-blur` | 60fps on low-end devices |
| **Optimistic UI** | Show success immediately | Snappy UX |
| **Lazy Component** | PasswordStrengthMeter loaded on demand | Faster initial load |
| **Debounced Validation** | Password strength check debounced 200ms | Reduced CPU usage |

## Testing

### Quick Verification

- [ ] Request reset link dengan email valid
- [ ] Email tidak ditemukan shows error
- [ ] Rate limiting works (3 req/60min)
- [ ] Countdown timer updates every second
- [ ] Reset link di email valid
- [ ] Token expired shows error + redirect
- [ ] Password strength meter works (weak → strong)
- [ ] Password mismatch shows error
- [ ] Success redirects to login (3s countdown)
- [ ] Mobile responsive
- [ ] Haptic feedback works

> 📋 Full test plan: [AUTH-P3 Test Plan](../../testing/AUTH-P3-password-reset-test-plan.md)

## Related Documentation

- **API Documentation:** [Authentication API - Password Reset](../../api/authentication.md#password-reset)
- **Test Plan:** [AUTH-P3 Test Plan](../../testing/AUTH-P3-password-reset-test-plan.md)
- **User Journeys:** [Authentication User Journeys - Password Reset Section](../../guides/auth-user-journeys.md#password-reset)

## Update Triggers

Update dokumentasi ini ketika:
- [ ] Token expiration time berubah
- [ ] Rate limiting rules berubah
- [ ] Password requirements berubah
- [ ] Email template berubah
- [ ] New edge cases ditemukan

---

*Last Updated: 2025-12-23*
*Documentation Status: ✅ Complete*
*Implementation Status: ✅ Tested dan Production-Ready*

