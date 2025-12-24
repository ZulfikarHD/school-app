# Feature: AUTH-P4 - Profile Management

> **Code:** AUTH-P4 | **Priority:** Medium | **Status:** ✅ Complete
> **Sprint:** 1-2 | **Menu:** Navbar > Profile Icon

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=profile` (2 routes found)
- [x] Vue page exists: Profile/Show
- [x] Component exists: ChangePasswordModal, PasswordStrengthMeter
- [x] Wayfinder routes generated: `@/routes/profile`
- [x] Following DOCUMENTATION_GUIDE.md template

---

## Overview

Profile Management merupakan fitur user self-service yang bertujuan untuk memungkinkan user melihat informasi profil dan mengganti password sendiri, yaitu: display read-only user info (name, email, username, role), change password modal dengan validasi current password, password strength meter untuk new password, dan success notification dengan iOS-like design yang dioptimasi untuk low-end devices.

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| US-05 | Authenticated User | Mengganti password sendiri untuk keamanan akun | • Profile page dengan button "Ganti Password"<br>• Modal dengan 3 fields (old, new, confirm)<br>• Password strength meter<br>• Current password validation | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-01 | Profile hanya accessible oleh authenticated user | Middleware: `auth` |
| BR-02 | Password lama harus valid untuk ganti password | Backend validation di PasswordController |
| BR-03 | Password baru harus minimal 8 karakter dengan kombinasi huruf & angka | Frontend + backend validation |
| BR-04 | Password baru tidak boleh sama dengan password lama | Backend validation (optional enforcement) |
| BR-05 | Activity log tercatat untuk change password | ActivityLog::create() di PasswordController |

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| Controller | `app/Http/Controllers/Profile/ProfileController.php` | Handle show profile |
| Controller | `app/Http/Controllers/Profile/PasswordController.php` | Handle change password |
| Request | `app/Http/Requests/Profile/UpdatePasswordRequest.php` | Validation dengan strong password requirements |
| Model | `app/Models/User.php` | User entity dengan password management |
| Page | `resources/js/pages/Profile/Show.vue` | Display profile dengan change password button |
| Component | `resources/js/components/ui/ChangePasswordModal.vue` | Modal form ganti password |
| Component | `resources/js/components/ui/PasswordStrengthMeter.vue` | Visual password strength indicator |
| Routes | `resources/js/routes/profile/index.ts` | Wayfinder type-safe routes |

### Routes Summary

| Method | URI | Name | Controller Method | Frontend |
|--------|-----|------|------------------|----------|
| GET | `/profile` | `profile.show` | `ProfileController@show` | Profile/Show.vue |
| POST | `/profile/password` | `profile.password.update` | `PasswordController@update` | ChangePasswordModal |

**Middleware:** `auth`

> 📡 Full API documentation: [Authentication API - Profile Section](../../api/authentication.md#profile)

### Database

> 📌 Menggunakan tabel `users` existing
> - Read: `id`, `name`, `email`, `username`, `role`, `status`, `created_at`
> - Update: `password` (hashed dengan bcrypt)

## Data Structures

```typescript
// Profile Display
interface UserProfile {
    id: number;
    name: string;
    email: string;
    username: string;
    role: string;
    status: string;
    created_at: string;
    last_login_at: string | null;
}

// Change Password Request
interface ChangePasswordRequest {
    current_password: string;     // Old password (required, must be correct)
    password: string;             // New password (min 8 chars, mixed)
    password_confirmation: string;  // Must match password
}

// Change Password Response
interface ChangePasswordResponse {
    success: boolean;
    message: string;              // "Password berhasil diubah"
}
```

## UI/UX Specifications

### Profile Show Page

**Layout:**
- Centered card dengan max-width 600px
- Avatar section dengan initial letter (large circular badge)
- Info display (read-only):
  - Full Name
  - Email
  - Username
  - Role badge (colored)
  - Status badge (Active/Inactive)
  - Member since (formatted date)
- Button "Ganti Password" (primary action)

**Design:**
- iOS-style card dengan soft shadow
- Avatar dengan gradient background based on role
- Info fields dengan label + value layout
- Role badge dengan color coding:
  - SUPERADMIN: Purple
  - PRINCIPAL: Blue
  - ADMIN: Indigo
  - TEACHER: Green
  - PARENT: Orange

### Change Password Modal

**Layout:**
- Modal overlay (centered, max-width 400px)
- Header: "Ganti Password" dengan close button
- Form fields:
  1. Current Password (password input dengan show/hide)
  2. New Password (password input dengan show/hide + strength meter)
  3. Confirm New Password (password input dengan show/hide)
- Footer: Cancel button + Submit button

**Validation:**
- Real-time validation dengan inline error messages
- Password strength meter updates on input
- Submit disabled jika validation failed

**Flow:**
1. User clicks "Ganti Password" button
2. Modal opens dengan fade + scale animation
3. User fills form
4. Click "Ubah Password"
5. Loading state (spinner di button)
6. Success: Toast notification + modal closes
7. Error: Show inline error message

**Design:**
- iOS-style modal dengan rounded corners
- Backdrop blur effect (`bg-black/20`)
- Floating labels
- Eye/EyeOff icon untuk toggle visibility
- Password strength meter (visual progress bar)
- Haptic feedback pada button press

## Edge Cases & Handling

| Scenario | Detection | Handling | User Feedback |
|----------|-----------|----------|---------------|
| **Current password salah** | Backend validation error 422 | Show inline error | "Password saat ini tidak sesuai" |
| **New password sama dengan old** | Backend validation (optional) | Show warning | "Password baru tidak boleh sama dengan password lama" |
| **Password too weak** | Frontend validation | Show inline error + strength meter | "Password harus minimal 8 karakter dengan kombinasi huruf, angka, dan simbol" |
| **Password mismatch** | Frontend + backend validation | Show inline error | "Konfirmasi password tidak cocok" |
| **Network error** | Inertia error handler | Show error toast | "Koneksi bermasalah. Periksa internet Anda." |
| **Session expired** | 401 response | Redirect to login | "Session Anda telah berakhir. Silakan login kembali." |

## Wayfinder Integration

```typescript
// Import Wayfinder routes
import { show } from '@/routes/profile';
import { update as passwordUpdate } from '@/routes/profile/password';

// Usage in components
<Link :href="show()">Profile</Link>

// Form submission (need .url)
form.post(passwordUpdate().url);

// Navigation
router.get(show().url);
```

## iOS-like Design Implementation

### Design Standards Applied

| Standard | Implementation | Example |
|----------|----------------|---------|
| **Spring Physics** | `stiffness: 300, damping: 30` | Modal open/close animation |
| **Press Feedback** | `:whileTap="{ scale: 0.97 }"` | All buttons |
| **Fake Glass** | `bg-white/95` (no blur) | Card background |
| **Crisp Borders** | `border border-gray-200 shadow-sm` | Card, modal, inputs |
| **Haptic Feedback** | `haptics.medium()` on success | Password changed |
| **Touch Targets** | Min 44x44px | Mobile buttons |

### Animation Patterns

```vue
<!-- Modal Entrance -->
<Motion 
    :initial="{ opacity: 0, scale: 0.95 }"
    :animate="{ opacity: 1, scale: 1 }"
    :exit="{ opacity: 0, scale: 0.95 }"
    :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
>
    <div class="modal-content">...</div>
</Motion>

<!-- Success Feedback -->
<Motion 
    :animate="{ scale: [1, 1.1, 1] }"
    :transition="{ duration: 0.3 }"
>
    <CheckIcon class="text-green-500" />
</Motion>
```

## Security Considerations

| Area | Implementation | Protection Against |
|------|----------------|-------------------|
| **Current Password Validation** | Backend check dengan Hash::check() | Unauthorized password change |
| **Password Strength** | Real-time validation (8+ chars, mixed) | Weak passwords |
| **CSRF Protection** | Laravel Sanctum (automatic) | Cross-site request forgery |
| **XSS Prevention** | Vue automatic escaping | Script injection |
| **Activity Logging** | Log password change dengan timestamp | Audit trail |
| **Session Validation** | Middleware `auth` | Unauthorized access |

## Performance Optimizations

| Technique | Implementation | Benefit |
|-----------|----------------|---------|
| **Lazy Modal** | ChangePasswordModal loaded on demand | Faster initial page load |
| **Debounced Validation** | Password strength check debounced 200ms | Reduced CPU usage |
| **Optimistic UI** | Modal close immediately on success | Snappy UX |
| **No Heavy Blur** | `bg-white/95` instead of `backdrop-blur` | 60fps on low-end devices |

## Testing

### Quick Verification

- [ ] Profile page displays correct user info
- [ ] Avatar shows correct initial letter
- [ ] Role badge shows correct color
- [ ] "Ganti Password" button opens modal
- [ ] Current password validation works
- [ ] Password strength meter updates real-time
- [ ] Password mismatch shows error
- [ ] Success shows toast notification
- [ ] Activity log recorded
- [ ] Modal closes after success
- [ ] Mobile responsive
- [ ] Haptic feedback works

> 📋 Full test plan: [AUTH-P4 Test Plan](../../testing/AUTH-P4-profile-management-test-plan.md)

## Related Documentation

- **API Documentation:** [Authentication API - Profile](../../api/authentication.md#profile)
- **Test Plan:** [AUTH-P4 Test Plan](../../testing/AUTH-P4-profile-management-test-plan.md)
- **User Journeys:** [Authentication User Journeys - Profile Section](../../guides/auth-user-journeys.md#profile-management)

## Future Enhancements

| Enhancement | Priority | Complexity | Estimated Effort |
|-------------|----------|------------|------------------|
| Edit profile info (name, email) | Medium | Low | 4 hours |
| Upload profile photo | Nice to have | Medium | 6 hours |
| Two-factor authentication setup | High | High | 16 hours |
| Password history validation | Medium | Medium | 4 hours |

## Update Triggers

Update dokumentasi ini ketika:
- [ ] Password requirements berubah
- [ ] Profile fields berubah (new fields, editable fields)
- [ ] UI/UX flow berubah
- [ ] New edge cases ditemukan

---

*Last Updated: 2025-12-23*
*Documentation Status: ✅ Complete*
*Implementation Status: ✅ Tested dan Production-Ready*




