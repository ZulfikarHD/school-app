# AT03: Leave Request Management (Parent & Teacher)

**Feature Code:** AT03  
**Owner:** Parent (Submit), Teacher (Verify)  
**Status:** ✅ Complete  
**Priority:** P0 (Critical)  
**Last Updated:** 2025-12-28

---

## Overview

Fitur Leave Request Management merupakan sistem pengajuan dan verifikasi permohonan izin siswa yang bertujuan untuk memfasilitasi komunikasi orang tua-guru terkait ketidakhadiran siswa dengan workflow approval dan auto-sync ke presensi, yaitu: menggantikan surat izin manual, mempercepat proses persetujuan, dan memastikan data presensi selalu akurat.

---

## User Stories

### Parent Stories

| ID | As a | I want to | So that | Priority | Status |
|----|------|-----------|---------|----------|--------|
| AT03-01 | Parent | Mengajukan permohonan izin untuk anak | Guru tahu anak saya tidak hadir | P0 | ✅ |
| AT03-02 | Parent | Memilih jenis izin (Izin/Sakit) | Alasan ketidakhadiran tercatat | P0 | ✅ |
| AT03-03 | Parent | Menentukan rentang tanggal izin | Guru tahu durasi ketidakhadiran | P0 | ✅ |
| AT03-04 | Parent | Upload lampiran (surat dokter) | Ada bukti pendukung permohonan | P0 | ✅ |
| AT03-05 | Parent | Melihat status permohonan (Pending/Approved/Rejected) | Saya tahu progress permohonan | P0 | ✅ |
| AT03-06 | Parent | Melihat alasan penolakan | Saya tahu kenapa ditolak | P0 | ✅ |

### Teacher Stories

| ID | As a | I want to | So that | Priority | Status |
|----|------|-----------|---------|----------|--------|
| AT03-07 | Teacher | Melihat list permohonan izin pending | Saya bisa review dan approve | P0 | ✅ |
| AT03-08 | Teacher | Melihat detail permohonan (tanggal, alasan, lampiran) | Saya bisa buat keputusan informed | P0 | ✅ |
| AT03-09 | Teacher | Approve permohonan izin | Presensi siswa otomatis tercatat | P0 | ✅ |
| AT03-10 | Teacher | Reject permohonan dengan alasan | Parent tahu kenapa ditolak | P0 | ✅ |
| AT03-11 | Teacher | Filter permohonan by status | Saya bisa fokus ke pending requests | P0 | ✅ |

---

## Business Rules

| Rule ID | Rule | Validation | Impact |
|---------|------|------------|--------|
| BR-AT03-01 | Hanya parent bisa submit leave request | `role:PARENT` + own children check | Authorization |
| BR-AT03-02 | Tanggal mulai harus >= hari ini | `tanggal_mulai >= today` | Prevent backdating |
| BR-AT03-03 | Tanggal selesai harus >= tanggal mulai | `tanggal_selesai >= tanggal_mulai` | Logical dates |
| BR-AT03-04 | File attachment max 2MB | Frontend & backend validation | Storage limit |
| BR-AT03-05 | Hanya wali kelas bisa approve | Check `wali_kelas_id` | Authorization |
| BR-AT03-06 | Approved leave auto-create attendance | Sync to `student_attendances` | Data consistency |
| BR-AT03-07 | Rejection reason wajib jika reject | Validate `rejection_reason` not null | Communication |

---

## Technical Implementation

### Components

#### Backend

| Component | Type | Path | Purpose |
|-----------|------|------|---------|
| **ParentLeaveRequestController** | Backend | `app/Http/Controllers/Parent/LeaveRequestController.php` | Parent CRUD |
| **TeacherLeaveRequestController** | Backend | `app/Http/Controllers/Teacher/LeaveRequestController.php` | Teacher verification |
| **AttendanceService** | Backend | `app/Services/AttendanceService.php` | Leave & sync logic |
| **StoreLeaveRequestRequest** | Backend | `app/Http/Requests/StoreLeaveRequestRequest.php` | Submit validation |
| **ApproveLeaveRequestRequest** | Backend | `app/Http/Requests/ApproveLeaveRequestRequest.php` | Approve validation |

#### Frontend

| Component | Type | Path | Purpose |
|-----------|------|------|---------|
| **Create.vue** | Frontend | `resources/js/pages/Parent/LeaveRequest/Create.vue` | Submit form |
| **Index.vue** (Parent) | Frontend | `resources/js/pages/Parent/LeaveRequest/Index.vue` | History view |
| **Index.vue** (Teacher) | Frontend | `resources/js/pages/Teacher/LeaveRequest/Index.vue` | Verification page |
| **LeaveRequestCard.vue** | Frontend | `resources/js/components/features/attendance/LeaveRequestCard.vue` | Request card |
| **LeaveStatusBadge.vue** | Frontend | `resources/js/components/features/attendance/LeaveStatusBadge.vue` | Status badge |

### Routes

#### Parent Routes

| Method | URI | Name | Controller@Method |
|--------|-----|------|-------------------|
| GET | `/parent/leave-requests` | parent.leave-requests.index | ParentLeaveRequestController@index |
| GET | `/parent/leave-requests/create` | parent.leave-requests.create | ParentLeaveRequestController@create |
| POST | `/parent/leave-requests` | parent.leave-requests.store | ParentLeaveRequestController@store |

#### Teacher Routes

| Method | URI | Name | Controller@Method |
|--------|-----|------|-------------------|
| GET | `/teacher/leave-requests` | teacher.leave-requests.index | TeacherLeaveRequestController@index |
| POST | `/teacher/leave-requests/{id}/approve` | teacher.leave-requests.approve | TeacherLeaveRequestController@approve |

### Database Schema

**Table:** `leave_requests`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| student_id | bigint | FK to students |
| jenis | enum('IZIN','SAKIT') | Leave type |
| tanggal_mulai | date | Start date |
| tanggal_selesai | date | End date |
| alasan | text | Reason |
| attachment_path | string | File path (nullable) |
| status | enum | PENDING/APPROVED/REJECTED |
| submitted_by | bigint | FK to users (parent) |
| reviewed_by | bigint | FK to users (teacher, nullable) |
| reviewed_at | timestamp | Review timestamp (nullable) |
| rejection_reason | text | Rejection reason (nullable) |

---

## Edge Cases

| Case | Handling | Status |
|------|----------|--------|
| Parent tidak punya anak | Show empty state dengan message | ✅ |
| File upload > 2MB | Frontend validation + error message | ✅ |
| Unsupported file format | Validate JPG/PNG/PDF only | ✅ |
| Tanggal overlap dengan leave lain | Allow (business decision) | ✅ |
| Teacher bukan wali kelas | Return 403 Forbidden | ✅ |
| Already approved/rejected | Prevent re-approval | ✅ |
| Network error upload file | Show error, allow retry | ✅ |

---

## Auto-Sync Logic

### Approval Flow

```
1. Teacher clicks "Setujui"
2. Confirmation modal shows:
   "Permohonan ini akan disetujui dan otomatis tercatat 
    sebagai SAKIT/IZIN di absensi siswa untuk tanggal X-Y"
3. Teacher confirms
4. Backend calls AttendanceService::approveLeaveRequest()
5. For each date in range (tanggal_mulai to tanggal_selesai):
   - Create StudentAttendance record
   - status = 'S' (Sakit) or 'I' (Izin)
   - keterangan = "Auto-generated from leave request: {alasan}"
   - recorded_by = teacher_id
6. Update leave_request.status = 'APPROVED'
7. Return success response
```

### Sync Example

**Leave Request:**
- Student: Ahmad Fauzi
- Jenis: SAKIT
- Tanggal: 2025-12-28 to 2025-12-29
- Alasan: "Demam tinggi 39°C"

**Auto-Created Attendance Records:**
```sql
INSERT INTO student_attendances (student_id, tanggal, status, keterangan)
VALUES 
  (15, '2025-12-28', 'S', 'Auto-generated from leave request: Demam tinggi 39°C'),
  (15, '2025-12-29', 'S', 'Auto-generated from leave request: Demam tinggi 39°C');
```

---

## Security Considerations

| Security Aspect | Implementation | Status |
|-----------------|----------------|--------|
| **Authorization** | Parent can only submit for own children | ✅ |
| **File Upload Security** | Validate type, size, store in private storage | ✅ |
| **CSRF Protection** | Laravel CSRF token | ✅ |
| **XSS Prevention** | Sanitize alasan & rejection_reason | ✅ |
| **Path Traversal** | Use Storage::disk() for file handling | ✅ |

---

## User Experience Features

### Parent Form Features
- ✅ Child dropdown (multi-child support)
- ✅ Leave type radio buttons (IZIN/SAKIT)
- ✅ Date range picker dengan duration calculator
- ✅ File upload dengan preview (image/PDF)
- ✅ File size validation (max 2MB)
- ✅ Haptic feedback on submit
- ✅ Success message dengan redirect

### Teacher Verification Features
- ✅ Filter by status (ALL/PENDING/APPROVED/REJECTED)
- ✅ Summary cards dengan counts
- ✅ Attachment lightbox preview
- ✅ Approve confirmation modal
- ✅ Reject modal dengan reason textarea
- ✅ Optimistic UI updates
- ✅ Info card explaining auto-sync

### Design Standards
- ✅ Emerald accent untuk approve actions
- ✅ Red accent untuk reject actions
- ✅ Amber badge untuk PENDING status
- ✅ Rounded-2xl cards
- ✅ iOS-like animations
- ✅ Mobile-responsive grid layout

---

## Performance Considerations

| Aspect | Implementation | Impact |
|--------|----------------|--------|
| **Lazy Load Images** | Attachment thumbnails lazy loaded | Faster page load |
| **Optimistic UI** | Update status before API response | Instant feedback |
| **Batch Sync** | Transaction untuk multiple dates | Atomic operation |
| **Indexed Queries** | Index on student_id, status, tanggal | Fast filtering |

---

## Future Enhancements (P2)

| Enhancement | Description | Priority |
|-------------|-------------|----------|
| **Push Notifications** | Notify parent when approved/rejected | P1 |
| **Email Notifications** | Send email to parent & teacher | P1 |
| **Bulk Approval** | Approve multiple requests at once | P2 |
| **Leave Templates** | Common leave reasons templates | P3 |
| **Calendar View** | Visual calendar untuk leave requests | P2 |

---

## Related Documentation

- **API Documentation:** [Leave Request API](../../api/attendance.md#leave-requests)
- **Test Plan:** [AT03 Test Plan](../../testing/AT03-leave-request-test-plan.md)
- **User Journeys:** [Leave Request User Journeys](../../guides/attendance-user-journeys.md#leave-requests)

---

## Verification Evidence

```bash
# Parent routes verified
$ php artisan route:list --path=parent/leave-requests
✓ parent.leave-requests.index (GET)
✓ parent.leave-requests.create (GET)
✓ parent.leave-requests.store (POST)

# Teacher routes verified
$ php artisan route:list --path=teacher/leave-requests
✓ teacher.leave-requests.index (GET)
✓ teacher.leave-requests.approve (POST)

# Migrations verified
$ php artisan migrate:status | findstr leave
✓ 2025_12_24_091410_create_leave_requests_table [Ran]

# Service methods verified
✓ AttendanceService::submitLeaveRequest() - exists
✓ AttendanceService::approveLeaveRequest() - exists
✓ AttendanceService::rejectLeaveRequest() - exists
✓ AttendanceService::syncLeaveToAttendance() - exists
```

---

**Last Verified:** 2025-12-28 21:45 WIB
