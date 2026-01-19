I'll analyze the attendance system requirements and create a comprehensive cross-frontend implementation strategy for each role. Let me start by reading the specification files.

[3 tools called]

Let me read the files with the correct path.

[3 tools called]

Now let me check the current existing pages and routes to understand the current state of implementation.

[2 tools called]

Now I have a comprehensive understanding of the requirements and current state. Let me create a detailed cross-frontend implementation strategy for each role.

---

# 📋 Attendance System - Cross-Frontend Implementation Strategy

## PHASE 1: FEATURE UNDERSTANDING

### Module Overview
The Attendance System handles two types of attendance:
1. **Student Attendance** - Daily morning attendance & per-subject attendance
2. **Teacher Attendance** - Clock in/out presence tracking

Including leave/sick request management with document upload and approval workflow.

---

## PHASE 2: CROSS-FRONTEND IMPACT MAPPING

### Feature Owner & Consumer Matrix

| Feature | Owner (Who Creates) | Consumer (Who Views) | Data Flow |
|---------|---------------------|---------------------|-----------|
| **Daily Student Attendance** | Teacher (input per class) | Teacher, Admin, Principal, Parent | Teacher→Store→All Viewers |
| **Subject Attendance** | Teacher (per subject) | Teacher, Admin, Principal | Teacher→Store→Reports |
| **Student Leave Request** | Parent (submit) | Teacher/Admin (verify) → Parent (result) | Parent→Pending→Verified→Attendance Updated |
| **Student Attendance Report** | System (auto-calculate) | Admin, Principal, Teacher, Parent | Attendance→Calculate→Display |
| **Teacher Clock In/Out** | Teacher (self) | Teacher (widget), Admin, Principal | Teacher→Store→Reports |
| **Teacher Leave Request** | Teacher (submit) | Principal/Admin (verify) | Teacher→Pending→Approved→Attendance Updated |
| **Teacher Attendance Report** | System (auto-calculate) | Admin, Principal | Attendance→Calculate→Payroll |
| **Real-Time Dashboard** | System (aggregate) | Principal, Admin | All Data→Aggregate→Dashboard |
| **Alpha Notifications** | System (auto-trigger) | Parent (receive WhatsApp) | Alpha→Queue→Send |

---

## PHASE 3: MISSING IMPLEMENTATION DETECTION

### ✅ = Implemented | ⚠️ = Partial | ❌ = Missing

### A. ADMIN Frontend

#### Owner Side (Data Management)
| Feature | Status | Notes |
|---------|--------|-------|
| ✅ Student Attendance Correction | Done | `Admin/Attendance/Students/Correction.vue` |
| ✅ View Student Attendance | Done | `Admin/Attendance/Students/Index.vue` |
| ✅ Student Attendance Report | Done | `Admin/Attendance/Students/Report.vue` |
| ✅ Teacher Attendance View | Done | `Admin/Attendance/Teachers/Index.vue` |
| ✅ Teacher Attendance Report | Done | `Admin/Attendance/Teachers/Report.vue` |
| ❌ Leave Request Verification UI | Missing | No page for Admin to verify student leaves |
| ❌ Teacher Leave Verification UI | Missing | No page for Admin to verify teacher leaves |
| ❌ Manual Teacher Attendance Edit | Missing | TU should be able to edit teacher attendance |

#### Consumer Side (Data Display)
| Feature | Status | Notes |
|---------|--------|-------|
| ❌ Real-time Dashboard Widget | Missing | Dashboard should show attendance stats |
| ❌ Pending Leave Badge | Missing | No notification badge for pending leaves |
| ❌ Low Attendance Alert List | Missing | No page showing < 80% attendance students |

---

### B. PRINCIPAL Frontend

#### Consumer Side (Read-Only Monitoring)
| Feature | Status | Notes |
|---------|--------|-------|
| ✅ Teacher Leave Verification | Done | `Principal/TeacherLeave/Index.vue` |
| ✅ Student Viewing | Done | `Principal/Students/Index.vue`, `Show.vue` |
| ❌ Student Attendance Report | Missing | Principal should see attendance reports |
| ❌ Teacher Attendance Report | Missing | Principal should see teacher attendance |
| ❌ Real-time Dashboard | Missing | Dashboard without attendance widgets |
| ❌ Class-wise Attendance Status | Missing | See which classes have/haven't input attendance |
| ❌ Daily Attendance Summary | Missing | Summary cards for today's attendance |

---

### C. TEACHER Frontend

#### Owner Side (Data Creation)
| Feature | Status | Notes |
|---------|--------|-------|
| ✅ Daily Attendance Input | Done | `Teacher/Attendance/Create.vue`, `Index.vue` |
| ✅ Subject Attendance Input | Done | `Teacher/SubjectAttendance/Create.vue`, `Index.vue` |
| ✅ Clock In/Out Widget | Done | `Teacher/Attendance/MyAttendance.vue` |
| ✅ Student Leave Verification | Done | `Teacher/LeaveRequest/Index.vue` |
| ✅ Teacher Leave Submit | Done | `Teacher/TeacherLeave/Create.vue`, `Index.vue` |
| ⚠️ Clock Widget on Dashboard | Partial | Need to verify if widget is on dashboard |

#### Consumer Side (Data Display)
| Feature | Status | Notes |
|---------|--------|-------|
| ✅ Student Viewing | Done | `Teacher/Students/Index.vue`, `Show.vue` |
| ❌ Attendance Report for Classes | Missing | Teacher should see report for their classes |
| ❌ Pending Leave Badge | Missing | Need badge notification for pending leaves |

---

### D. PARENT Frontend

#### Owner Side (Data Creation)
| Feature | Status | Notes |
|---------|--------|-------|
| ✅ Submit Leave Request | Done | `Parent/LeaveRequest/Create.vue` |
| ✅ Edit Leave Request | Done | `Parent/LeaveRequest/Edit.vue` |
| ✅ Leave History | Done | `Parent/LeaveRequest/Index.vue` |

#### Consumer Side (Data Display)
| Feature | Status | Notes |
|---------|--------|-------|
| ✅ View Child List | Done | `Parent/Children/Index.vue` |
| ✅ View Child Detail | Done | `Parent/Children/Show.vue` |
| ✅ View Child Attendance | Done | `Parent/Children/Attendance.vue` |
| ❌ Dashboard Attendance Widget | Missing | Dashboard should show child attendance summary |
| ❌ Low Attendance Warning Banner | Missing | Warning if child < 80% attendance |

---

## PHASE 4: GAP ANALYSIS

### ⚠️ CRITICAL GAPS (Data Flow Broken)

| Gap | Impact | Severity |
|-----|--------|----------|
| ❌ **Admin cannot verify student leaves** | Student leaves approved only by Teacher, no backup | HIGH |
| ❌ **Admin cannot verify teacher leaves** | Only Principal can approve, no TU backup | MEDIUM |
| ❌ **Principal has no attendance dashboard** | Cannot monitor real-time attendance | HIGH |
| ❌ **Principal cannot see attendance reports** | Cannot monitor student/teacher attendance trends | HIGH |
| ❌ **Parent dashboard has no attendance widget** | Parent must navigate to Children → Attendance | MEDIUM |
| ❌ **Teacher dashboard clock widget not verified** | Teacher may not see clock-in widget prominently | MEDIUM |

### ⚠️ NAVIGATION GAPS

| Gap | Impact |
|-----|--------|
| ❌ No pending leave badge in navigation | Users don't know there are pending items |
| ❌ No quick access to attendance from dashboard | Extra clicks needed |
| ❌ Admin sidebar menu incomplete for attendance | Navigation unclear |

---

## PHASE 5: IMPLEMENTATION SEQUENCING

### Priority Matrix

| Priority | Feature | Dependencies | Role(s) |
|----------|---------|--------------|---------|
| **P0** | Principal Attendance Dashboard | None | Principal |
| **P0** | Principal Attendance Reports Access | Backend routes | Principal |
| **P0** | Admin Leave Verification UI | Existing backend | Admin |
| **P1** | Parent Dashboard Attendance Widget | Existing data | Parent |
| **P1** | Teacher Pending Leave Badge | Existing data | Teacher |
| **P1** | Navigation Menu Updates | None | All |
| **P2** | Low Attendance Alert Page | Report calculation | Admin, Principal |
| **P2** | Real-time Dashboard Polling | WebSocket/Polling | Principal, Admin |

---

## PHASE 6: DETAILED RECOMMENDATIONS

### 📁 NEW PAGES NEEDED

#### Admin Frontend
```
resources/js/pages/Admin/
├── Attendance/
│   └── LeaveRequests/
│       └── Index.vue          # P0 - Verify student leave requests
├── TeacherLeave/
│   └── Index.vue              # P1 - Verify teacher leave requests
```

#### Principal Frontend
```
resources/js/pages/Principal/
├── Attendance/
│   ├── Dashboard.vue          # P0 - Real-time attendance dashboard
│   ├── Students/
│   │   ├── Index.vue          # P0 - Student attendance report
│   │   └── Report.vue         # P0 - Detailed report with charts
│   └── Teachers/
│       ├── Index.vue          # P0 - Teacher attendance list
│       └── Report.vue         # P0 - Teacher attendance report
├── LeaveRequests/
│   └── Index.vue              # P1 - View all leave requests (read-only)
```

#### Parent Frontend
```
resources/js/pages/Parent/
└── Dashboard/
    └── (Update ParentDashboard.vue) # Add attendance summary widget
```

---

### 📝 UPDATES TO EXISTING PAGES

#### Admin Frontend

| Page | Update | Priority |
|------|--------|----------|
| `Admin/Attendance/Students/Index.vue` | Add filter by leave status, pending badge | P1 |
| `AdminDashboard.vue` | Add attendance summary cards, pending leaves widget | P1 |

#### Principal Frontend

| Page | Update | Priority |
|------|--------|----------|
| `PrincipalDashboard.vue` | **Major** - Add full attendance dashboard with: 1) Today's attendance summary cards, 2) Class-wise attendance status, 3) Pending teacher leaves badge, 4) Recent alerts section | P0 |

#### Teacher Frontend

| Page | Update | Priority |
|------|--------|----------|
| `TeacherDashboard.vue` | Ensure Clock In/Out widget is prominent, add pending leave badge | P1 |
| `Teacher/LeaveRequest/Index.vue` | Add better empty states, filter tabs | P2 |

#### Parent Frontend

| Page | Update | Priority |
|------|--------|----------|
| `ParentDashboard.vue` | Add child attendance summary widget with warning if < 80% | P1 |
| `Parent/Children/Attendance.vue` | Add calendar view, warning banner | P2 |

---

### 🧭 NAVIGATION/MENU CHANGES

#### Admin Sidebar Menu

```
Attendance (existing)
├── Student Attendance (existing)
├── Student Report (existing)
├── ➕ Leave Verification (NEW - P0)
├── Teacher Attendance (existing)
├── Teacher Report (existing)
└── ➕ Teacher Leave Verification (NEW - P1)
```

#### Principal Sidebar Menu

```
➕ Attendance (NEW SECTION - P0)
├── Dashboard
├── Student Attendance
├── Teacher Attendance
└── Teacher Leaves (existing, move here)

Students (existing)
```

#### Teacher Sidebar Menu

```
Attendance (existing)
├── Daily Attendance
├── Subject Attendance
├── My Attendance
└── Leave Requests (add badge count)

➕ My Leave (existing, ensure visible)
```

#### Parent Sidebar Menu

```
Dashboard (update with attendance widget)
Children
└── (Keep as is, attendance accessible from child detail)
Leave Requests
```

---

### 🧩 COMPONENT LIBRARY ADDITIONS

| Component | Used By | Priority | Path |
|-----------|---------|----------|------|
| `AttendanceDashboardWidget.vue` | Principal, Admin dashboards | P0 | `components/dashboard/` |
| `AttendanceSummaryCards.vue` | All dashboards | P0 | `components/features/attendance/` |
| `LeaveRequestCard.vue` | Teacher, Admin leave pages | P1 | `components/features/attendance/` (exists) |
| `ClassAttendanceStatusCard.vue` | Principal dashboard | P0 | `components/features/attendance/` |
| `AttendanceCalendar.vue` | Parent child attendance | P2 | `components/features/attendance/` |
| `LowAttendanceWarningBanner.vue` | Parent child attendance | P1 | `components/features/attendance/` |
| `PendingBadge.vue` | Navigation items | P1 | `components/ui/` |

---

## PHASE 7: EXAMPLE USER JOURNEYS

### Journey 1: Principal Monitoring Attendance (HIGH PRIORITY - Currently Missing)

**Current State:** Principal has no dedicated attendance view
**Target State:** Full dashboard with real-time monitoring

**Owner Journey (N/A - Principal is Consumer)**

**Consumer Journey:**
1. Principal navigates to: `/principal/dashboard`
2. Principal sees: Attendance summary cards (Total hadir, alpha, guru hadir, kelas belum input)
3. Principal sees: List of classes with input status (✅ Sudah / ⚠️ Belum)
4. Principal clicks: Class "3A" → sees list of alpha/sakit/izin students today
5. Principal navigates to: "Rekap Absensi" → detailed report with charts
6. Principal achieves: Monitor attendance in real-time, identify issues

---

### Journey 2: Admin Verifying Student Leave Request (P0 - Currently Missing)

**Current State:** Only Teacher can verify, Admin has no access
**Target State:** Admin (TU) can also verify leaves as backup

**Owner Journey (Parent - Already Implemented):**
1. Parent navigates to: `/parent/leave-requests`
2. Parent clicks: "Buat Pengajuan Baru"
3. Parent fills: Child, type (Izin/Sakit), dates, reason, upload photo
4. System does: Save with status "Pending", notify Teacher & Admin
5. Parent sees: "Pengajuan berhasil dikirim"

**Consumer Journey (Admin - NEW):**
1. Admin navigates to: `/admin/attendance/leave-requests` (NEW PAGE)
2. Admin sees: List of pending leave requests with details
3. Admin clicks: "Lihat Detail" → sees student info, photo, reason
4. Admin clicks: "Setujui" → modal confirmation
5. System does: Update status to Approved, auto-update attendance
6. Admin achieves: Leave verified, attendance updated

---

### Journey 3: Parent Viewing Child Attendance with Warning (P1 - Partial)

**Current State:** Attendance viewable but no dashboard widget, no warning banner
**Target State:** Dashboard shows summary, warning if < 80%

**Consumer Journey:**
1. Parent navigates to: `/parent/dashboard`
2. Parent sees: **NEW** Child attendance widget showing:
   - "Ahmad (Kelas 3A): Hadir 85%, Sakit 2 hari, Alpha 1 hari"
   - Warning banner if alpha > 0
3. Parent clicks: Widget → navigates to detailed attendance
4. Parent sees: Calendar view with color-coded days
5. Parent sees: Warning banner "⚠️ Kehadiran di bawah 80%, risiko tidak naik kelas"
6. Parent achieves: Awareness of child's attendance status

---

## 📊 SUMMARY BY ROLE

### ADMIN (TU) - Requires 3 New Pages

| File | Type | Priority | Description |
|------|------|----------|-------------|
| `Admin/Attendance/LeaveRequests/Index.vue` | NEW | P0 | Verify student leaves |
| `Admin/TeacherLeave/Index.vue` | NEW | P1 | Verify teacher leaves |
| `AdminDashboard.vue` | UPDATE | P1 | Add attendance widgets |

**Backend Routes Needed:**
```php
// Admin leave verification
Route::get('admin/attendance/leave-requests', [AdminLeaveRequestController::class, 'index']);
Route::post('admin/attendance/leave-requests/{id}/approve', [AdminLeaveRequestController::class, 'approve']);
Route::post('admin/attendance/leave-requests/{id}/reject', [AdminLeaveRequestController::class, 'reject']);

// Admin teacher leave verification
Route::get('admin/teacher-leaves', [AdminTeacherLeaveController::class, 'index']);
Route::post('admin/teacher-leaves/{id}/approve', [AdminTeacherLeaveController::class, 'approve']);
Route::post('admin/teacher-leaves/{id}/reject', [AdminTeacherLeaveController::class, 'reject']);
```

---

### PRINCIPAL - Requires 5+ New Pages

| File | Type | Priority | Description |
|------|------|----------|-------------|
| `Principal/Attendance/Dashboard.vue` | NEW | P0 | Real-time attendance dashboard |
| `Principal/Attendance/Students/Index.vue` | NEW | P0 | Student attendance list |
| `Principal/Attendance/Students/Report.vue` | NEW | P0 | Student attendance report |
| `Principal/Attendance/Teachers/Index.vue` | NEW | P0 | Teacher attendance list |
| `Principal/Attendance/Teachers/Report.vue` | NEW | P0 | Teacher attendance report |
| `PrincipalDashboard.vue` | UPDATE | P0 | Add attendance summary widgets |

**Backend Routes Needed:**
```php
// Principal attendance (read-only)
Route::get('principal/attendance/dashboard', [PrincipalAttendanceController::class, 'dashboard']);
Route::get('principal/attendance/students', [PrincipalAttendanceController::class, 'studentsIndex']);
Route::get('principal/attendance/students/report', [PrincipalAttendanceController::class, 'studentReport']);
Route::get('principal/attendance/teachers', [PrincipalAttendanceController::class, 'teachersIndex']);
Route::get('principal/attendance/teachers/report', [PrincipalAttendanceController::class, 'teacherReport']);
```

---

### TEACHER - Mostly Complete, Minor Updates

| File | Type | Priority | Description |
|------|------|----------|-------------|
| `TeacherDashboard.vue` | UPDATE | P1 | Verify clock widget prominence |
| `Teacher/LeaveRequest/Index.vue` | UPDATE | P1 | Add pending badge, improve UX |
| `AppLayout.vue` | UPDATE | P1 | Add badge count to menu item |

**No new backend routes needed** - Teacher attendance already complete.

---

### PARENT - Minor Updates

| File | Type | Priority | Description |
|------|------|----------|-------------|
| `ParentDashboard.vue` | UPDATE | P1 | Add attendance summary widget |
| `Parent/Children/Attendance.vue` | UPDATE | P2 | Add calendar view, warning banner |

**Backend Updates Needed:**
```php
// Dashboard endpoint to get attendance summary for parent
Route::get('parent/dashboard/attendance-summary', [ParentDashboardController::class, 'attendanceSummary']);
```

---

## 🚀 IMPLEMENTATION ROADMAP

### Sprint A: Principal Attendance Access (P0)
1. Create `PrincipalAttendanceController.php` (read-only, reuse Admin logic)
2. Add routes for Principal attendance access
3. Create Principal attendance pages (5 pages)
4. Update `PrincipalDashboard.vue` with widgets

### Sprint B: Admin Leave Verification (P0)
1. Create `AdminLeaveRequestController.php`
2. Add routes for Admin leave verification
3. Create `Admin/Attendance/LeaveRequests/Index.vue`
4. Update navigation

### Sprint C: Dashboard Enhancements (P1)
1. Create shared `AttendanceSummaryCards.vue` component
2. Update `ParentDashboard.vue` with attendance widget
3. Update `TeacherDashboard.vue` - verify clock widget
4. Add pending badges to all navigation

### Sprint D: UX Polish (P2)
1. Create `AttendanceCalendar.vue` component
2. Add warning banners for low attendance
3. Implement real-time polling for dashboards
4. Mobile optimization

---

## ⚠️ CRITICAL REMINDERS

1. **Principal CANNOT see any attendance data currently** - This is a major gap
2. **Admin cannot verify leaves** - Only Teacher can, no backup for TU
3. **Parent dashboard shows NO attendance** - Must navigate multiple clicks
4. **No pending badges anywhere** - Users don't know items need action
5. **Consider mobile UX** - Parents primarily use phones

---

**Document Version:** 1.0  
**Created:** 19 January 2026  
**Status:** Ready for Sprint Planning
