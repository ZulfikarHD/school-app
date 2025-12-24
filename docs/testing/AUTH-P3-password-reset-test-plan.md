# Test Plan: AUTH-P3 - Password Reset Flow

> **Feature:** AUTH-P3 | **Version:** 1.0 | **Last Updated:** 2025-12-23

---

## Overview

Test plan untuk Password Reset Flow yang mencakup forgot password request, reset link validation, password strength testing, dan security testing dengan focus pada rate limiting dan token expiration.

---

## Test Environment

| Component | Requirement |
|-----------|-------------|
| **Browser** | Chrome 120+, Safari 17+, Firefox 120+ |
| **Device** | Desktop (1920x1080), Mobile (375x667) |
| **Network** | Fast 3G, 4G, WiFi |
| **Email** | Valid email testing (Mailtrap/MailHog) |

---

## Manual QA Test Checklist

### 1. Forgot Password Page

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| FP-001 | Navigate ke `/forgot-password` | Page loads dengan email input | ☐ |
| FP-002 | Submit empty form | Validation error displayed | ☐ |
| FP-003 | Submit invalid email format | Error: "Email tidak valid" | ☐ |
| FP-004 | Submit valid registered email | Success: "Link reset telah dikirim" | ☐ |
| FP-005 | Submit unregistered email | Generic message (no info disclosure) | ☐ |
| FP-006 | Verify countdown timer (60s) | Countdown displayed, button disabled | ☐ |
| FP-007 | Verify email sent | Email received dengan reset link | ☐ |
| FP-008 | Click "Kembali ke Login" | Navigates to login page | ☐ |
| FP-009 | Mobile responsive | Centered card, readable text | ☐ |

### 2. Rate Limiting

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| RL-001 | Submit 3 requests rapidly | 3rd request succeeds | ☐ |
| RL-002 | Submit 4th request | Error: rate limit message + countdown | ☐ |
| RL-003 | Countdown updates real-time | Timer decreases every second | ☐ |
| RL-004 | Wait for countdown to finish | Button re-enabled after 60s | ☐ |
| RL-005 | Submit after cooldown | Request succeeds | ☐ |

### 3. Reset Password Page

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| RP-001 | Click link from email | Navigates to reset password page | ☐ |
| RP-002 | Verify token in URL | Token parameter present | ☐ |
| RP-003 | Verify email pre-filled | Email field contains correct email | ☐ |
| RP-004 | Submit empty form | Validation errors displayed | ☐ |
| RP-005 | Enter weak password (< 8 chars) | Error + strength meter shows weak | ☐ |
| RP-006 | Enter password without numbers | Error: "Password harus mixed" | ☐ |
| RP-007 | Password mismatch | Error: "Konfirmasi tidak cocok" | ☐ |
| RP-008 | Enter strong password | Strength meter shows strong (green) | ☐ |
| RP-009 | Submit valid form | Success, redirects to login (3s) | ☐ |

### 4. Password Strength Meter

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| PS-001 | Type "abc" | Meter: Red (Weak 0-25%) | ☐ |
| PS-002 | Type "abcdefgh" | Meter: Orange (Fair 25-50%) | ☐ |
| PS-003 | Type "Abcdefgh" | Meter: Yellow (Good 50-75%) | ☐ |
| PS-004 | Type "Abcdefgh1" | Meter: Green (Strong 75-90%) | ☐ |
| PS-005 | Type "Abcdefgh1!" | Meter: Blue (Very Strong 90-100%) | ☐ |
| PS-006 | Real-time update | Meter updates on every keystroke | ☐ |
| PS-007 | Feedback message | Clear message displayed below meter | ☐ |

### 5. Token Validation

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| TV-001 | Use expired token (> 1 hour) | Redirect to forgot password + error | ☐ |
| TV-002 | Use invalid token | Redirect to forgot password + error | ☐ |
| TV-003 | Use token twice | 2nd attempt fails (token invalidated) | ☐ |
| TV-004 | Manually edit token in URL | Error: invalid token | ☐ |
| TV-005 | Use valid token | Form displays successfully | ☐ |

### 6. Toggle Password Visibility

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| TV-001 | Click eye icon (show) | Password visible as plain text | ☐ |
| TV-002 | Click eye icon (hide) | Password hidden as dots | ☐ |
| TV-003 | Icon changes | Eye → EyeOff on toggle | ☐ |
| TV-004 | Works on both fields | New password & confirmation | ☐ |

### 7. UX & Design Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UX-001 | Success checkmark animation | Spring animation (scale 0 → 1) | ☐ |
| UX-002 | Countdown animation | Pulsing effect on timer | ☐ |
| UX-003 | Button press feedback | Scale to 0.97 on tap | ☐ |
| UX-004 | Haptic feedback works | Vibration on button tap (mobile) | ☐ |
| UX-005 | Loading state | Spinner in button during submit | ☐ |
| UX-006 | Auto-redirect countdown | "Redirecting in 3... 2... 1..." | ☐ |
| UX-007 | Mobile responsive (375px) | Centered card, readable text | ☐ |
| UX-008 | Toast notifications | Success/error toasts displayed | ☐ |

### 8. Security Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| SE-001 | Rate limiting enforced | Max 3 requests per 60 minutes | ☐ |
| SE-002 | Token expiration enforced | Tokens expire after 1 hour | ☐ |
| SE-003 | Token single-use enforced | Cannot reuse same token | ☐ |
| SE-004 | CSRF protection | Request fails without CSRF token | ☐ |
| SE-005 | XSS attempt in email field | Input sanitized | ☐ |
| SE-006 | Information disclosure | No "user not found" message | ☐ |

### 9. Email Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| EM-001 | Email received | Email arrives in inbox | ☐ |
| EM-002 | Email content correct | Contains reset link + instructions | ☐ |
| EM-003 | Link format correct | URL contains token + email | ☐ |
| EM-004 | Link clickable | Opens reset password page | ☐ |
| EM-005 | Branded template | Email uses app branding | ☐ |

---

## Automated Test Coverage

### Feature Tests (PHPUnit)

```bash
php artisan test tests/Feature/Auth/PasswordResetTest.php
```

**Expected Tests:**
- [x] `test_forgot_password_page_displays`
- [x] `test_forgot_password_sends_email`
- [x] `test_forgot_password_rate_limiting`
- [x] `test_reset_password_page_displays`
- [x] `test_reset_password_with_valid_token`
- [x] `test_reset_password_with_expired_token`
- [x] `test_reset_password_with_invalid_token`
- [x] `test_password_strength_validation`
- [x] `test_token_invalidated_after_use`

---

## Test Summary

| Category | Total | Passed | Failed | Pass Rate |
|----------|-------|--------|--------|-----------|
| Functional | 26 | - | - | -% |
| UX/Design | 8 | - | - | -% |
| Security | 6 | - | - | -% |
| Email | 5 | - | - | -% |
| **TOTAL** | **45** | **-** | **-** | **-%** |

---

## Sign-Off

- [ ] All functional tests passed
- [ ] All security tests passed (rate limiting, expiration)
- [ ] Email delivery working
- [ ] No critical bugs found
- [ ] Ready for production

**Tester:** ________________  
**Date:** ________________  
**Reviewer:** ________________  
**Date:** ________________

---

*Last Updated: 2025-12-23*




