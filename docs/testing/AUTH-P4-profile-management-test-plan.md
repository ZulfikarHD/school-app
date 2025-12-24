# Test Plan: AUTH-P4 - Profile Management

> **Feature:** AUTH-P4 | **Version:** 1.0 | **Last Updated:** 2025-12-23

---

## Overview

Test plan untuk Profile Management yang mencakup profile viewing, change password modal, password strength validation, dan security testing dengan focus pada current password validation dan activity logging.

---

## Test Environment

| Component | Requirement |
|-----------|-------------|
| **Browser** | Chrome 120+, Safari 17+, Firefox 120+ |
| **Device** | Desktop (1920x1080), Mobile (375x667) |
| **Test Users** | All roles (SUPERADMIN, ADMIN, TEACHER, PARENT) |

---

## Manual QA Test Checklist

### 1. Profile Display

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| PD-001 | Navigate ke `/profile` | Page loads dengan profile info | ☐ |
| PD-002 | Verify name displayed | Correct user name shown | ☐ |
| PD-003 | Verify email displayed | Correct email shown | ☐ |
| PD-004 | Verify username displayed | Correct username shown | ☐ |
| PD-005 | Verify role badge | Correct role with proper color | ☐ |
| PD-006 | Verify status badge | Correct status (Active/Inactive) | ☐ |
| PD-007 | Verify avatar initial | First letter of name in circle | ☐ |
| PD-008 | Verify member since date | Formatted date displayed | ☐ |
| PD-009 | Mobile responsive | Card centered, readable text | ☐ |

### 2. Change Password Modal

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| CP-001 | Click "Ganti Password" button | Modal opens dengan animation | ☐ |
| CP-002 | Verify modal has 3 fields | Current, New, Confirm password | ☐ |
| CP-003 | Submit empty form | Validation errors displayed | ☐ |
| CP-004 | Enter wrong current password | Error: "Password saat ini tidak sesuai" | ☐ |
| CP-005 | Enter weak new password | Error + strength meter shows weak | ☐ |
| CP-006 | New password same as current | Warning (optional check) | ☐ |
| CP-007 | Password mismatch | Error: "Konfirmasi tidak cocok" | ☐ |
| CP-008 | Enter valid passwords | Success, modal closes, toast shown | ☐ |
| CP-009 | Click cancel button | Modal closes without saving | ☐ |
| CP-010 | Click outside modal | Modal closes (if enabled) | ☐ |

### 3. Password Strength Meter

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| PS-001 | Type "abc" | Meter: Red (Weak 0-25%) | ☐ |
| PS-002 | Type "abcdefgh" | Meter: Orange (Fair 25-50%) | ☐ |
| PS-003 | Type "Abcdefgh" | Meter: Yellow (Good 50-75%) | ☐ |
| PS-004 | Type "Abcdefgh1" | Meter: Green (Strong 75-90%) | ☐ |
| PS-005 | Type "Abcdefgh1!" | Meter: Blue (Very Strong 90-100%) | ☐ |
| PS-006 | Real-time update | Meter updates on keystroke | ☐ |

### 4. Toggle Password Visibility

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| TV-001 | Click eye icon (current) | Password visible as plain text | ☐ |
| TV-002 | Click eye icon (new) | Password visible as plain text | ☐ |
| TV-003 | Click eye icon (confirm) | Password visible as plain text | ☐ |
| TV-004 | Icon changes | Eye → EyeOff on toggle | ☐ |
| TV-005 | Independent toggles | Each field toggles independently | ☐ |

### 5. Success Flow

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| SF-001 | Submit valid form | Loading spinner in button | ☐ |
| SF-002 | Verify success toast | Green toast: "Password berhasil diubah" | ☐ |
| SF-003 | Verify modal closes | Modal closes after success | ☐ |
| SF-004 | Verify activity logged | Activity log entry created | ☐ |
| SF-005 | Verify haptic feedback | Vibration on success (mobile) | ☐ |
| SF-006 | Can login dengan password baru | New password works for login | ☐ |

### 6. Role Badge Colors

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| RB-001 | SUPERADMIN role | Purple badge displayed | ☐ |
| RB-002 | PRINCIPAL role | Blue badge displayed | ☐ |
| RB-003 | ADMIN role | Indigo badge displayed | ☐ |
| RB-004 | TEACHER role | Green badge displayed | ☐ |
| RB-005 | PARENT role | Orange badge displayed | ☐ |

### 7. UX & Design Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UX-001 | Modal open animation | Scale 0.95 → 1 (spring) | ☐ |
| UX-002 | Modal close animation | Scale 1 → 0.95 (spring) | ☐ |
| UX-003 | Button press feedback | Scale to 0.97 on tap | ☐ |
| UX-004 | Haptic feedback works | Vibration on actions (mobile) | ☐ |
| UX-005 | Loading state | Spinner in button during submit | ☐ |
| UX-006 | Success feedback | Checkmark icon animation | ☐ |
| UX-007 | Mobile responsive (375px) | Modal fits screen, readable | ☐ |
| UX-008 | Avatar gradient | Role-based gradient colors | ☐ |

### 8. Security Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| SE-001 | Access without auth | Redirect to login | ☐ |
| SE-002 | Current password validation | Backend validates current password | ☐ |
| SE-003 | Password strength enforced | Weak passwords rejected | ☐ |
| SE-004 | CSRF protection | Request fails without CSRF token | ☐ |
| SE-005 | XSS attempt | Input sanitized | ☐ |
| SE-006 | Activity logging | Password change logged with timestamp | ☐ |
| SE-007 | Session invalidation | Consider invalidating other sessions | ☐ |

### 9. Edge Cases

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| EC-001 | Network error during submit | Error toast, modal stays open | ☐ |
| EC-002 | Session expired | Redirect to login | ☐ |
| EC-003 | Rapid modal open/close | No UI glitch, smooth animations | ☐ |
| EC-004 | Long name (50+ chars) | Truncated gracefully with ellipsis | ☐ |
| EC-005 | Long email (50+ chars) | Truncated or wrapped properly | ☐ |

---

## Automated Test Coverage

### Feature Tests (PHPUnit)

```bash
php artisan test tests/Feature/Profile/ProfileTest.php
```

**Expected Tests:**
- [x] `test_authenticated_user_can_view_profile`
- [x] `test_guest_cannot_access_profile`
- [x] `test_user_can_change_password`
- [x] `test_current_password_validation`
- [x] `test_new_password_strength_validation`
- [x] `test_password_confirmation_validation`
- [x] `test_password_change_logged_in_activity_log`
- [x] `test_can_login_with_new_password_after_change`

---

## Test Summary

| Category | Total | Passed | Failed | Pass Rate |
|----------|-------|--------|--------|-----------|
| Functional | 30 | - | - | -% |
| UX/Design | 8 | - | - | -% |
| Security | 7 | - | - | -% |
| Edge Cases | 5 | - | - | -% |
| **TOTAL** | **50** | **-** | **-** | **-%** |

---

## Sign-Off

- [ ] All functional tests passed
- [ ] All security tests passed
- [ ] Activity logging works
- [ ] No critical bugs found
- [ ] Ready for production

**Tester:** ________________  
**Date:** ________________  
**Reviewer:** ________________  
**Date:** ________________

---

*Last Updated: 2025-12-23*




