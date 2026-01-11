# ✅ Attendance System - Quick Test Checklist
**Happy Path Only** | **Estimated Total Time: 45 minutes**

---

## 🚀 Pre-Test Setup (5 min)
- [ ] Database seeded with test data
- [ ] Test users available (Teacher, Parent, Admin, Principal)
- [ ] Browser with GPS permission enabled
- [ ] Test class with 10+ students
- [ ] Network connection stable

---

## 📋 Quick Test Cases

### 1️⃣ Daily Attendance Input (5 min)
**Login:** Teacher → `/teacher/attendance/daily`

- [ ] Select class from dropdown
- [ ] Student list loads with all "Hadir" default
- [ ] Change 2-3 students to I/S/A status
- [ ] Add keterangan for non-Hadir
- [ ] Summary counter updates in real-time
- [ ] Click "Simpan Presensi"
- [ ] Success notification appears
- [ ] Redirect to attendance list
- [ ] **Edit Mode:** Reload same class/date → loads existing data

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 2️⃣ Subject Attendance (4 min)
**Login:** Teacher → `/teacher/attendance/subject`

- [ ] Select class and subject
- [ ] Student list loads
- [ ] Mark 2-3 students as absent
- [ ] Click "Simpan"
- [ ] Success notification
- [ ] View in history

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 3️⃣ Parent Leave Request (4 min)
**Login:** Parent → `/parent/leave-requests/create`

- [ ] Select child from dropdown
- [ ] Choose type: Izin/Sakit
- [ ] Set date range (tomorrow)
- [ ] Enter reason (min 10 chars)
- [ ] Upload file (JPG/PNG/PDF < 2MB)
- [ ] Preview shows
- [ ] Click "Kirim"
- [ ] Success notification
- [ ] Status shows "PENDING"

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 4️⃣ Teacher Approve Leave (4 min)
**Login:** Teacher → `/teacher/leave-requests`

- [ ] Pending tab shows leave request
- [ ] Click to view details
- [ ] Attachment viewable
- [ ] Click "✓ Setujui"
- [ ] Success notification
- [ ] Status → "APPROVED"
- [ ] Request moves to Approved tab

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 5️⃣ Auto-Update Attendance After Approval (2 min)
**Login:** Teacher → `/teacher/attendance/daily`

- [ ] Select class of approved leave
- [ ] Change date to leave date
- [ ] Student auto-marked with status I or S
- [ ] Keterangan shows "Auto-generated from leave request..."

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 6️⃣ Teacher Reject Leave (3 min)
**Login:** Teacher → `/teacher/leave-requests`

- [ ] Create new leave request (as parent first)
- [ ] Click "✗ Tolak"
- [ ] Enter rejection reason (required)
- [ ] Submit
- [ ] Status → "REJECTED"
- [ ] Reason displayed

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 7️⃣ Teacher Clock In (5 min)
**Login:** Teacher → Dashboard

- [ ] Clock widget shows "Belum Clock In"
- [ ] Click "Clock In" button
- [ ] Browser asks GPS permission → Allow
- [ ] GPS loading indicator
- [ ] Success notification
- [ ] Widget shows clock in time
- [ ] Status: "Tepat Waktu" or "Terlambat"
- [ ] Clock Out button now visible

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 8️⃣ Teacher Clock Out (3 min)
**Login:** Teacher → Dashboard

- [ ] Click "Clock Out" button
- [ ] GPS permission (if needed)
- [ ] Success notification
- [ ] Widget shows clock in + clock out times
- [ ] Duration calculated: "X jam X menit"
- [ ] Both buttons disabled

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 9️⃣ View Teacher Attendance History (2 min)
**Login:** Teacher → `/teacher/my-attendance`

- [ ] Page loads with attendance table
- [ ] Today's record shows:
  - Clock In time
  - Clock Out time
  - Duration
  - Status (Hadir/Terlambat)
- [ ] Monthly summary shows stats

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 🔟 Student Attendance Report (4 min)
**Login:** Admin → `/admin/attendance/students/report`

- [ ] Filter section visible
- [ ] Select class: "Kelas 7A"
- [ ] Set date range: Last 30 days
- [ ] Click "Lihat Laporan"
- [ ] Report table loads with data
- [ ] Summary cards show statistics
- [ ] Students with <80% highlighted (if any)

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 1️⃣1️⃣ Teacher Attendance Report (4 min)
**Login:** Admin → `/admin/attendance/teachers/report`

- [ ] Select date range: Current month
- [ ] Click "Lihat Laporan"
- [ ] Table shows teacher attendance:
  - Clock In/Out times
  - Duration
  - Status (Hadir/Terlambat)
- [ ] Summary shows total hours
- [ ] Late entries highlighted

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 1️⃣2️⃣ Principal Approve Teacher Leave (4 min)
**Login:** Principal → `/principal/teacher-leaves`

1. **Setup** (as Teacher first):
   - [ ] Submit teacher leave request
   - [ ] Status: PENDING

2. **Test** (as Principal):
   - [ ] Pending tab shows request
   - [ ] View details
   - [ ] Click "✓ Setujui"
   - [ ] Success notification
   - [ ] Status → "APPROVED"

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 1️⃣3️⃣ Admin Manual Correction (3 min)
**Login:** Admin → `/admin/attendance/students`

- [ ] Find attendance record
- [ ] Click "Edit"
- [ ] Change status (e.g., A → I)
- [ ] Update keterangan
- [ ] Enter correction reason
- [ ] Click "Simpan"
- [ ] Success notification
- [ ] Record updated

**✅ PASS** | **❌ FAIL** | **Notes:** _______________

---

### 1️⃣4️⃣ Alpha Notification (Optional - If WhatsApp Configured)
**Login:** Teacher → Mark student as Alpha

- [ ] Submit attendance with Alpha status
- [ ] Check queue: `php artisan queue:work`
- [ ] Parent receives WhatsApp notification
- [ ] Message format correct
- [ ] Notification logged in DB

**✅ PASS** | **❌ FAIL** | **⬜ N/A** | **Notes:** _______________

---

### 1️⃣5️⃣ Attendance Reminder (Optional - Scheduled)
**Manual Trigger:** `php artisan attendance:send-reminders`

- [ ] Command runs successfully
- [ ] Identifies classes without attendance
- [ ] Queues reminders for wali kelas
- [ ] Teachers receive WhatsApp (if configured)
- [ ] Message format correct

**✅ PASS** | **❌ FAIL** | **⬜ N/A** | **Notes:** _______________

---

## 📊 Quick Summary

**Date:** _____________  
**Tester:** _____________  
**Environment:** Production / Staging

| Category | Passed | Failed | N/A | Notes |
|----------|--------|--------|-----|-------|
| Attendance Input (1-2) | /2 | /2 | | |
| Leave Management (3-6) | /4 | /4 | | |
| Teacher Clock (7-9) | /3 | /3 | | |
| Reports (10-11) | /2 | /2 | | |
| Approvals (12) | /1 | /1 | | |
| Admin Tools (13) | /1 | /1 | | |
| Notifications (14-15) | /2 | /2 | | |

**TOTAL PASSED:** _____ / 15  
**PASS RATE:** _____%

---

## 🐛 Issues Found

1. ________________________________________________
2. ________________________________________________
3. ________________________________________________

---

## ✍️ Sign-Off

**Tested By:** _________________ **Date:** _________ **✅ Approved / ❌ Rejected**

---

## 📱 Mobile Testing Checklist (Quick)

- [ ] Clock In/Out on mobile (iOS/Android)
- [ ] GPS permission works on mobile
- [ ] Attendance form responsive on mobile
- [ ] Touch targets adequate (min 44px)
- [ ] Forms scrollable and usable
- [ ] Haptic feedback works (if device supports)

---

## 🌐 Browser Testing

- [ ] Chrome ✅
- [ ] Firefox ✅
- [ ] Safari ✅
- [ ] Edge ✅

---

**Quick Reference:** For detailed test steps, see `ATTENDANCE-SYSTEM-QA-MANUAL-TESTING.md`
