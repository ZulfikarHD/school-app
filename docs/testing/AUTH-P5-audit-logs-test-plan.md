# Test Plan: AUTH-P5 - Audit Log Viewing

> **Feature:** AUTH-P5 | **Version:** 1.0 | **Last Updated:** 2025-12-23

---

## Overview

Test plan untuk Audit Log Viewing yang mencakup filtering, pagination, expandable rows, role-based access, dan performance testing dengan focus pada compliance dan transparency requirements.

---

## Test Environment

| Component | Requirement |
|-----------|-------------|
| **Browser** | Chrome 120+, Safari 17+, Firefox 120+ |
| **Device** | Desktop (1920x1080), Tablet (768x1024), Mobile (375x667) |
| **Test Users** | SUPERADMIN, ADMIN, PRINCIPAL roles |

---

## Manual QA Test Checklist

### 1. Page Access & Display

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| AL-001 | Navigate to `/admin/audit-logs` (Admin) | Page loads dengan audit log table | ☐ |
| AL-002 | Navigate to `/audit-logs` (Principal) | Page loads (read-only) | ☐ |
| AL-003 | Access as TEACHER role | 403 Forbidden error | ☐ |
| AL-004 | Access as PARENT role | 403 Forbidden error | ☐ |
| AL-005 | Verify pagination works | Can navigate pages | ☐ |
| AL-006 | Verify log entries displayed | Logs show: user, action, timestamp | ☐ |
| AL-007 | Mobile responsive | Table → Cards layout | ☐ |

### 2. Date Range Filter

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| DF-001 | Click "Today" preset | Filters to today's logs | ☐ |
| DF-002 | Click "Last 7 days" preset | Filters to last 7 days | ☐ |
| DF-003 | Click "Last 30 days" preset | Filters to last 30 days | ☐ |
| DF-004 | Click "This Month" preset | Filters to current month | ☐ |
| DF-005 | Select custom date range | Filters to specified range | ☐ |
| DF-006 | Invalid range (end < start) | Error or button disabled | ☐ |
| DF-007 | Clear date filter | Shows all logs | ☐ |

### 3. User Filter

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UF-001 | Click user dropdown | Lists all users | ☐ |
| UF-002 | Search user by name | Filters dropdown options | ☐ |
| UF-003 | Select specific user | Filters logs for that user | ☐ |
| UF-004 | Clear user filter | Shows logs for all users | ☐ |

### 4. Action Filter

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| AF-001 | Click action multi-select | Shows action checkboxes | ☐ |
| AF-002 | Select "login" only | Filters to login actions | ☐ |
| AF-003 | Select multiple actions | Filters to selected actions (OR logic) | ☐ |
| AF-004 | Deselect all actions | Shows all actions | ☐ |
| AF-005 | Verify available actions | login, logout, create, update, delete | ☐ |

### 5. Status Filter

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| SF-001 | Select "Success" status | Filters to successful actions | ☐ |
| SF-002 | Select "Failed" status | Filters to failed actions | ☐ |
| SF-003 | Select "All" status | Shows all logs | ☐ |
| SF-004 | Status badge color | Success: green, Failed: red | ☐ |

### 6. Expandable Rows

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| ER-001 | Click expand icon | Row expands dengan smooth animation | ☐ |
| ER-002 | Verify old values displayed | Old values shown (if update action) | ☐ |
| ER-003 | Verify new values displayed | New values shown (if update action) | ☐ |
| ER-004 | Verify JSON formatting | Pretty-printed dengan indentation | ☐ |
| ER-005 | Verify changed fields highlighted | Visual indication of changes | ☐ |
| ER-006 | Verify null values shown | Displayed as "-" (dash) | ☐ |
| ER-007 | Click collapse icon | Row collapses dengan smooth animation | ☐ |
| ER-008 | Multiple rows expanded | Can expand multiple at once | ☐ |

### 7. Additional Details Display

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| AD-001 | Verify IP address shown | IP address displayed correctly | ☐ |
| AD-002 | Verify user agent shown | Browser info displayed | ☐ |
| AD-003 | Verify timestamp format | Human-readable format (WIB) | ☐ |
| AD-004 | Verify description field | Clear action description | ☐ |

### 8. Empty States

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| ES-001 | No logs in system | "Belum ada log aktivitas" + icon | ☐ |
| ES-002 | No results from filter | "Tidak ada log sesuai filter" | ☐ |
| ES-003 | Empty state styling | Centered, clean design | ☐ |

### 9. Pagination

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| PG-001 | Verify items per page (50) | Shows 50 logs max per page | ☐ |
| PG-002 | Click next page | Navigates to page 2 | ☐ |
| PG-003 | Click previous page | Navigates to page 1 | ☐ |
| PG-004 | Verify page numbers | Correct page numbers displayed | ☐ |
| PG-005 | Verify total count | Total logs count displayed | ☐ |

### 10. Role-Based Access

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| RA-001 | SUPERADMIN access | Full access to all logs | ☐ |
| RA-002 | ADMIN access | Full access to all logs | ☐ |
| RA-003 | PRINCIPAL access | Read-only access | ☐ |
| RA-004 | TEACHER access | 403 Forbidden | ☐ |
| RA-005 | PARENT access | 403 Forbidden | ☐ |

### 11. UX & Design Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| UX-001 | Expand animation | Smooth spring (stiffness 300) | ☐ |
| UX-002 | Button press feedback | Scale to 0.97 on tap | ☐ |
| UX-003 | Haptic feedback works | Vibration on expand (mobile) | ☐ |
| UX-004 | Loading states visible | Skeleton loader during fetch | ☐ |
| UX-005 | Filter UI responsive | Collapsible on mobile | ☐ |
| UX-006 | Status badges pulse (failed) | Red badge pulses for failed | ☐ |
| UX-007 | Mobile cards readable | All info visible in card | ☐ |
| UX-008 | Touch targets (mobile) | Min 44x44px buttons | ☐ |

### 12. Performance Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| PE-001 | Initial page load | < 2 seconds | ☐ |
| PE-002 | Filter application | < 1 second | ☐ |
| PE-003 | Pagination navigation | < 1 second | ☐ |
| PE-004 | Expand row (JSON load) | < 500ms | ☐ |
| PE-005 | Multiple filters applied | < 1.5 seconds | ☐ |
| PE-006 | User dropdown search debounce | 300ms delay works | ☐ |
| PE-007 | Smooth scrolling | 60fps maintained | ☐ |

### 13. Security Testing

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| SE-001 | Role-based access enforced | Unauthorized roles get 403 | ☐ |
| SE-002 | CSRF protection | Request fails without token | ☐ |
| SE-003 | XSS prevention | Malicious JSON sanitized | ☐ |
| SE-004 | SQL injection prevention | Filter inputs sanitized | ☐ |
| SE-005 | Sensitive data masking | Password fields masked in JSON | ☐ |
| SE-006 | Activity logged | Viewing audit log is logged | ☐ |

### 14. Edge Cases

| Test ID | Test Case | Expected Result | Pass/Fail |
|---------|-----------|-----------------|-----------|
| EC-001 | Very long JSON values | Wrapped properly, no overflow | ☐ |
| EC-002 | Large result set (1000+ logs) | Pagination handles correctly | ☐ |
| EC-003 | Network error during filter | Error toast, data stays | ☐ |
| EC-004 | Invalid date in URL params | Falls back to default (all dates) | ☐ |
| EC-005 | Rapid filter changes | No race condition, shows latest | ☐ |

---

## Automated Test Coverage

### Feature Tests (PHPUnit)

```bash
php artisan test tests/Feature/Admin/AuditLogTest.php
```

**Expected Tests:**
- [x] `test_admin_can_view_audit_logs`
- [x] `test_principal_can_view_audit_logs_readonly`
- [x] `test_teacher_cannot_access_audit_logs`
- [x] `test_filter_by_date_range`
- [x] `test_filter_by_user`
- [x] `test_filter_by_action`
- [x] `test_filter_by_status`
- [x] `test_pagination_works`
- [x] `test_old_new_values_json_structure`

---

## Test Summary

| Category | Total | Passed | Failed | Pass Rate |
|----------|-------|--------|--------|-----------|
| Functional | 50 | - | - | -% |
| UX/Design | 8 | - | - | -% |
| Performance | 7 | - | - | -% |
| Security | 6 | - | - | -% |
| Edge Cases | 5 | - | - | -% |
| **TOTAL** | **76** | **-** | **-** | **-%** |

---

## Sign-Off

- [ ] All functional tests passed
- [ ] All filters working correctly
- [ ] Role-based access enforced
- [ ] Performance benchmarks met
- [ ] No critical bugs found
- [ ] Ready for production

**Tester:** ________________  
**Date:** ________________  
**Reviewer:** ________________  
**Date:** ________________

---

*Last Updated: 2025-12-23*


