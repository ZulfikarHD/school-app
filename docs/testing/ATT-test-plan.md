# 🧪 ATT - Attendance System Test Plan

**Module Code:** `ATT`  
**Test Type:** Manual QA (Happy Path Focus)  
**Last Updated:** 2026-01-12  
**Status:** ✅ Test Suite Ready

---

## Test Documentation Structure

Comprehensive QA documentation untuk Attendance System telah dibuat dan tersedia di root project, yaitu:

### Main QA Documents (Root Level)

1. **ATTENDANCE-SYSTEM-QA-MANUAL-TESTING.md**
   - 12 detailed test cases dengan step-by-step instructions
   - Expected results untuk setiap step
   - Pass/Fail tracking checkboxes
   - Defect reporting section
   - Estimated time: 60 minutes

2. **ATTENDANCE-QA-QUICK-CHECKLIST.md**
   - 15 quick test scenarios
   - Fast execution checklist format
   - Mobile & browser compatibility testing
   - Estimated time: 45 minutes

3. **ATTENDANCE-QA-TEST-DATA-SETUP.md**
   - Environment preparation guide
   - Test data creation (seeder & manual)
   - Test user credentials
   - Verification checklist
   - Troubleshooting guide

4. **QA-DOCUMENTATION-INDEX.md**
   - Navigation hub untuk semua QA docs
   - Quick start guide
   - Test execution workflow

---

## Test Coverage Summary

### Features Tested (Happy Path)

| Feature ID | Feature Name | Test Case | Status |
|------------|--------------|-----------|--------|
| US-ATT-001 | Daily Morning Attendance Input | TC-ATT-001 | ✅ Ready |
| US-ATT-002 | Subject Attendance Input | TC-ATT-002 | ✅ Ready |
| US-ATT-003 | Parent Submit Leave Request | TC-ATT-003 | ✅ Ready |
| US-ATT-004 | Teacher Approve Leave | TC-ATT-004 | ✅ Ready |
| US-ATT-005 | Teacher Reject Leave | TC-ATT-005 | ✅ Ready |
| US-ATT-006 | Teacher Clock In/Out | TC-ATT-006 | ✅ Ready |
| US-ATT-007 | Student Attendance Report | TC-ATT-007 | ✅ Ready |
| US-ATT-008 | Teacher Attendance Report | TC-ATT-008 | ✅ Ready |
| US-ATT-009 | Principal Approve Teacher Leave | TC-ATT-009 | ✅ Ready |
| US-ATT-010 | Admin Manual Correction | TC-ATT-010 | ✅ Ready |
| US-ATT-011 | Alpha Notification | TC-ATT-011 | ✅ Ready |
| US-ATT-012 | Attendance Reminder | TC-ATT-012 | ✅ Ready |

**Total Test Cases:** 12  
**Coverage:** All P0 (Critical) features  
**Pass Criteria:** ≥ 95% pass rate

---

## Test Execution Quick Reference

### For First-Time/Full Testing (60 min)

```bash
# 1. Setup test data (15-20 min)
php artisan db:seed

# 2. Execute full test suite
# Open: ATTENDANCE-SYSTEM-QA-MANUAL-TESTING.md
# Follow TC-ATT-001 through TC-ATT-012
```

### For Daily/Smoke Testing (45 min)

```bash
# Quick sanity check
# Open: ATTENDANCE-QA-QUICK-CHECKLIST.md
# Execute 15 quick scenarios
```

---

## Test Users (Credentials)

| Role | Email | Password | Purpose |
|------|-------|----------|---------|
| Teacher | teacher@school.test | password | Input attendance, clock in/out |
| Parent | parent@school.test | password | Submit leave requests |
| Admin/TU | admin@school.test | password | Reports, corrections |
| Principal | principal@school.test | password | Approve teacher leaves |

---

## Test Data Requirements

### Minimum Required:
- ✅ 1 active class with 10+ students
- ✅ 2+ subjects assigned to teacher
- ✅ 1+ student linked to parent
- ✅ Teacher as wali kelas
- ✅ Teacher-subject assignments configured

### Setup Commands:
```bash
# Run database seeder
php artisan db:seed

# Or use specific seeders
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=AttendanceTestSeeder
```

Detailed setup guide: `ATTENDANCE-QA-TEST-DATA-SETUP.md`

---

## Critical Test Paths

### Priority P0 - Must Pass Before Release

1. **Daily Attendance Flow**
   - Teacher login → Select class → Mark attendance → Save
   - Edit mode: Same date reloads existing data
   - **Test Case:** TC-ATT-001

2. **Leave Request Flow**
   - Parent submit → Teacher approve → Auto-update attendance
   - **Test Cases:** TC-ATT-003, TC-ATT-004

3. **Teacher Clock In/Out**
   - Clock in with GPS → Clock out → Duration calculated
   - Late detection (>07:30)
   - **Test Case:** TC-ATT-006

4. **Reports Generation**
   - Student report with filters
   - Teacher report for payroll
   - **Test Cases:** TC-ATT-007, TC-ATT-008

---

## Test Environment Requirements

### Browser Requirements:
- ✅ Chrome (recommended for GPS)
- ✅ Firefox
- ✅ Safari (iOS)
- ✅ Edge

### Mobile Requirements:
- ✅ iOS device (GPS testing)
- ✅ Android device (GPS testing)
- ✅ HTTPS connection (GPS requires secure context)

### System Requirements:
- ✅ PHP 8.2+
- ✅ Laravel 11.x
- ✅ Database with test data
- ✅ Queue worker running (for notifications)
- ✅ Timezone: Asia/Jakarta (WIB)

---

## Test Execution Workflow

```
Setup (First Time)
       ↓
Choose Testing Mode
   ↙        ↘
Full Test  Quick Test
  (60min)   (45min)
   ↓        ↓
Execute Test Cases
   ↓
Document Results
   ↓
Report Defects
   ↓
Sign-Off
```

---

## Defect Reporting Template

```
ID: ATT-BUG-XXX
Test Case: TC-ATT-XXX
Severity: Critical/High/Medium/Low
Status: Open

Description:
[Clear description]

Steps to Reproduce:
1. [Step]
2. [Step]

Expected: [What should happen]
Actual: [What happened]

Environment:
- Browser: [Chrome/Firefox/etc]
- OS: [Windows/macOS/iOS/Android]
- User Role: [Teacher/Parent/etc]
```

---

## Success Criteria

### Release Readiness Checklist

- [ ] All P0 test cases: PASS
- [ ] Pass rate: ≥ 95%
- [ ] No critical defects open
- [ ] All user roles tested
- [ ] Mobile GPS functionality verified
- [ ] Cross-browser compatibility confirmed
- [ ] Performance benchmarks met
- [ ] Security tests passed

---

## Known Issues / Exclusions

### Not Tested (Out of Scope):
- ❌ Negative test cases
- ❌ Edge cases (boundary testing)
- ❌ Performance/load testing
- ❌ Security penetration testing
- ❌ API testing (manual only)

### Optional Features (May be N/A):
- ⚠️ WhatsApp notifications (requires API key)
- ⚠️ Email notifications (requires SMTP)
- ⚠️ Scheduled tasks (requires cron setup)

---

## Testing Schedule Recommendations

### Daily (Active Development):
- **Time:** 45 minutes
- **Document:** ATTENDANCE-QA-QUICK-CHECKLIST.md
- **Focus:** Smoke test critical paths

### Weekly (Sprint End):
- **Time:** 60 minutes
- **Document:** ATTENDANCE-SYSTEM-QA-MANUAL-TESTING.md
- **Focus:** Full regression test

### Before Release:
- **Time:** 90 minutes
- **Document:** Both + exploratory testing
- **Focus:** Complete suite + edge cases

---

## Test Metrics to Track

| Metric | Target | Actual |
|--------|--------|--------|
| Pass Rate | ≥ 95% | ___% |
| Test Execution Time | ≤ 60 min | ___ min |
| Defects Found | Track | ___ |
| Critical Defects | 0 | ___ |
| Test Coverage | 100% P0 features | ___% |

---

## Automated Testing (Future)

### Planned for Phase 2:
- Unit tests untuk Service layer
- Feature tests untuk Controllers
- Browser tests dengan Laravel Dusk
- API tests dengan PHPUnit

### Current Status:
- ✅ Manual QA ready
- 📝 Automated tests: Planned

---

## References

- **Main QA Document:** `/ATTENDANCE-SYSTEM-QA-MANUAL-TESTING.md`
- **Quick Checklist:** `/ATTENDANCE-QA-QUICK-CHECKLIST.md`
- **Setup Guide:** `/ATTENDANCE-QA-TEST-DATA-SETUP.md`
- **QA Index:** `/QA-DOCUMENTATION-INDEX.md`
- **Feature Doc:** [ATT Attendance System](../features/attendance/ATT-attendance-system.md)
- **API Doc:** [Attendance API](../api/attendance.md)
- **Requirements:** `school-reqs-main/02_Functional_Requirements/03_Attendance_System.md`

---

## Contact & Support

**For Testing Issues:**
- Check troubleshooting in setup guide
- Review test data verification checklist

**For Feature Questions:**
- Developer: Zulfikar Hidayatullah
- Refer to requirements document

---

**Document Version:** 1.0  
**Test Suite Status:** ✅ Ready for Execution  
**Last Test Run:** [Pending]  
**Next Review Date:** [TBD]
