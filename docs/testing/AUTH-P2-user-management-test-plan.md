# Test Plan: AUTH-P2 - User Management (CRUD)

> **Feature:** AUTH-P2 | **Version:** 1.0 | **Last Updated:** 2025-12-23

---

## Overview

Test plan untuk User Management CRUD yang mencakup functional testing, UX testing, security testing, dan performance testing dengan focus pada create, read, update, delete operations, serta quick actions (reset password, toggle status).

---

## Test Environment

| Component | Requirement |
|-----------|-------------|
| **Browser** | Chrome 120+, Safari 17+, Firefox 120+ |
| **Device** | Desktop (1920x1080), Tablet (768x1024), Mobile (375x667) |
| **Network** | Fast 3G, 4G, WiFi |
| **Test User** | SUPERADMIN role dengan full access |

---

## Manual QA Test Checklist

### 1. User List (Index Page)

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UM-001 | Navigate ke `/admin/users` | Page loads dengan user table | ☐ |
| UM-002 | Verify pagination works | Can navigate pages (Prev/Next) | ☐ |
| UM-003 | Test search by name | Results filtered correctly | ☐ |
| UM-004 | Test search by email | Results filtered correctly | ☐ |
| UM-005 | Test search by username | Results filtered correctly | ☐ |
| UM-006 | Filter by role (ADMIN) | Only ADMIN users displayed | ☐ |
| UM-007 | Filter by status (ACTIVE) | Only active users displayed | ☐ |
| UM-008 | Empty search result | "Belum ada user ditemukan" displayed | ☐ |
| UM-009 | Mobile responsive | Table converts to cards | ☐ |

### 2. Create User

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UC-001 | Click "Tambah User" button | Navigates to create form | ☐ |
| UC-002 | Submit empty form | Validation errors displayed | ☐ |
| UC-003 | Create with valid data | User created, redirects to index | ☐ |
| UC-004 | Verify auto-generated password | Password displayed (12 chars) | ☐ |
| UC-005 | Create with duplicate email | Error: "Email sudah terdaftar" | ☐ |
| UC-006 | Create with duplicate username | Error: "Username sudah digunakan" | ☐ |
| UC-007 | Create with invalid email format | Inline validation error | ☐ |
| UC-008 | Success toast notification | Green toast dengan message | ☐ |

### 3. Edit User

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UE-001 | Click edit icon on user row | Navigates to edit form with pre-filled data | ☐ |
| UE-002 | Verify form pre-filled | All fields contain user data | ☐ |
| UE-003 | Edit name only | User updated, redirects to index | ☐ |
| UE-004 | Edit email to existing email | Error: "Email sudah terdaftar" | ☐ |
| UE-005 | Edit role (ADMIN → TEACHER) | Role updated successfully | ☐ |
| UE-006 | Toggle status (ACTIVE → INACTIVE) | Status updated successfully | ☐ |
| UE-007 | Click cancel without saving | Returns to index without changes | ☐ |
| UE-008 | isDirty check | Save button disabled if no changes | ☐ |

### 4. Delete User

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UD-001 | Click delete icon | Confirmation modal displayed | ☐ |
| UD-002 | Click "Batal" in modal | Modal closes, user not deleted | ☐ |
| UD-003 | Click "Hapus" in modal | User deleted, success toast | ☐ |
| UD-004 | Try to delete self | Button disabled, tooltip shown | ☐ |
| UD-005 | Verify user removed from list | User no longer in table | ☐ |

### 5. Reset Password

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UR-001 | Click reset password icon | Confirmation modal displayed | ☐ |
| UR-002 | Click "Reset" in modal | Password reset, email sent | ☐ |
| UR-003 | Success notification | Toast: "Password berhasil direset" | ☐ |
| UR-004 | Verify email sent (log check) | Email in queue/sent | ☐ |

### 6. Toggle Status

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UT-001 | Click status toggle button | Status immediately changes (optimistic UI) | ☐ |
| UT-002 | Toggle ACTIVE → INACTIVE | Badge color changes to gray | ☐ |
| UT-003 | Toggle INACTIVE → ACTIVE | Badge color changes to green | ☐ |
| UT-004 | Network error during toggle | Status rolls back, error toast | ☐ |

### 7. UX & Design Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UX-001 | Button press feedback | Scale to 0.97 on tap | ☐ |
| UX-002 | Haptic feedback works | Vibration on button tap (mobile) | ☐ |
| UX-003 | Loading states visible | Skeleton loader during data fetch | ☐ |
| UX-004 | Empty state displayed | Icon + message ketika no data | ☐ |
| UX-005 | Mobile responsive (375px) | Cards layout, readable text | ☐ |
| UX-006 | Tablet responsive (768px) | Table layout, proper spacing | ☐ |
| UX-007 | Desktop responsive (1920px) | Full table, optimal spacing | ☐ |
| UX-008 | Spring animations smooth | Page entrance animation (stiffness 300) | ☐ |

### 8. Security Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| SE-001 | Access as TEACHER role | 403 Forbidden error | ☐ |
| SE-002 | Access as PARENT role | 403 Forbidden error | ☐ |
| SE-003 | Access without auth | Redirect to login | ☐ |
| SE-004 | CSRF token validation | Request fails without token | ☐ |
| SE-005 | XSS attempt in name field | Input sanitized, script not executed | ☐ |
| SE-006 | SQL injection attempt | Input sanitized, no DB error | ☐ |

### 9. Performance Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| PE-001 | Initial page load time | < 2 seconds | ☐ |
| PE-002 | Search debounce (300ms) | No API call before 300ms | ☐ |
| PE-003 | Pagination load time | < 1 second | ☐ |
| PE-004 | Scroll performance | 60fps, smooth scrolling | ☐ |
| PE-005 | Animation performance | No jank, smooth spring | ☐ |

---

## Automated Test Coverage

### Feature Tests (PHPUnit)

```bash
php artisan test tests/Feature/Admin/UserManagementTest.php
```

**Expected Tests:**
- [x] `test_admin_can_view_users_list`
- [x] `test_admin_can_create_user`
- [x] `test_admin_can_edit_user`
- [x] `test_admin_can_delete_user`
- [x] `test_admin_can_reset_user_password`
- [x] `test_admin_can_toggle_user_status`
- [x] `test_non_admin_cannot_access_user_management`
- [x] `test_cannot_delete_self`
- [x] `test_duplicate_email_validation`
- [x] `test_duplicate_username_validation`

---

## Bug Report Template

```markdown
**Bug ID:** UM-BUG-XXX
**Severity:** [Critical/High/Medium/Low]
**Title:** [Short description]

**Steps to Reproduce:**
1. Step 1
2. Step 2
3. Step 3

**Expected Result:**
[What should happen]

**Actual Result:**
[What actually happened]

**Environment:**
- Browser: [Chrome 120]
- Device: [Desktop/Mobile]
- User Role: [SUPERADMIN]

**Screenshot/Video:**
[Attach if available]
```

---

## Test Summary

| Category | Total | Passed | Failed | Pass Rate |
|----------|-------|--------|--------|-----------|
| Functional | 32 | - | - | -% |
| UX/Design | 8 | - | - | -% |
| Security | 6 | - | - | -% |
| Performance | 5 | - | - | -% |
| **TOTAL** | **51** | **-** | **-** | **-%** |

---

## Sign-Off

- [ ] All functional tests passed
- [ ] All UX tests passed
- [ ] All security tests passed
- [ ] All performance tests passed
- [ ] No critical bugs found
- [ ] Ready for production

**Tester:** ________________  
**Date:** ________________  
**Reviewer:** ________________  
**Date:** ________________

---

*Last Updated: 2025-12-23*

