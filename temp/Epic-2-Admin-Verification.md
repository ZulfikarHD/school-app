# Epic 2: Admin Verification System

## Sprint 2 - Week 2

**Goal:** Membangun sistem verifikasi untuk Admin/TU agar dapat memproses pendaftaran siswa baru.

---

## Backend Developer Tasks

### Task 2.1: Admin PSB Controller & Routes (P0)
**Story Points:** 5

**Routes (routes/admin.php atau routes/web.php dengan middleware):**
```php
Route::prefix('admin/psb')->name('admin.psb.')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/', [AdminPsbController::class, 'index'])->name('index');
    
    // Registrations
    Route::get('/registrations', [AdminPsbController::class, 'registrations'])->name('registrations.index');
    Route::get('/registrations/{registration}', [AdminPsbController::class, 'show'])->name('registrations.show');
    Route::post('/registrations/{registration}/approve', [AdminPsbController::class, 'approve'])->name('registrations.approve');
    Route::post('/registrations/{registration}/reject', [AdminPsbController::class, 'reject'])->name('registrations.reject');
    Route::post('/registrations/{registration}/revision', [AdminPsbController::class, 'requestRevision'])->name('registrations.revision');
});
```

**Controller Methods:**
- `index()` - Dashboard overview dengan stats
- `registrations()` - List dengan filter & pagination
- `show()` - Detail view dengan documents
- `approve()` - Approve registration
- `reject()` - Reject dengan reason
- `requestRevision()` - Request document revision

**Acceptance Criteria:**
- [ ] Routes protected dengan auth middleware
- [ ] List mendukung filter by status
- [ ] Pagination berfungsi
- [ ] Approve/reject update status correctly
- [ ] Notification dikirim ke parent setelah action

---

### Task 2.2: Verification Form Requests (P0)
**Story Points:** 3

**Form Requests:**

**ApproveRegistrationRequest:**
```php
public function rules(): array
{
    return [
        'notes' => ['nullable', 'string', 'max:500'],
    ];
}
```

**RejectRegistrationRequest:**
```php
public function rules(): array
{
    return [
        'rejection_reason' => ['required', 'string', 'max:1000'],
    ];
}
```

**RequestRevisionRequest:**
```php
public function rules(): array
{
    return [
        'documents' => ['required', 'array', 'min:1'],
        'documents.*.id' => ['required', 'exists:psb_documents,id'],
        'documents.*.revision_note' => ['required', 'string', 'max:500'],
    ];
}
```

**Acceptance Criteria:**
- [ ] Validation rules lengkap
- [ ] Custom error messages dalam Bahasa Indonesia
- [ ] Authorization check (hanya admin)

---

### Task 2.3: PsbService - Verification Methods (P0)
**Story Points:** 5

Tambahkan methods ke PsbService:

```php
class PsbService
{
    // Approve registration
    public function approveRegistration(PsbRegistration $registration, User $verifier, ?string $notes = null): bool;
    
    // Reject registration
    public function rejectRegistration(PsbRegistration $registration, User $verifier, string $reason): bool;
    
    // Request document revision
    public function requestDocumentRevision(PsbRegistration $registration, array $documents): bool;
    
    // Get registration list dengan filters
    public function getRegistrations(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    // Get registration detail dengan documents
    public function getRegistrationDetail(PsbRegistration $registration): array;
    
    // Get summary statistics
    public function getSummaryStats(): array;
}
```

**getSummaryStats() Return:**
```php
return [
    'total' => 150,
    'pending' => 15,
    'document_review' => 10,
    'approved' => 100,
    'rejected' => 10,
    'waiting_list' => 5,
    're_registration' => 8,
    'completed' => 2,
];
```

**Acceptance Criteria:**
- [ ] Status transition sesuai flow
- [ ] Verified_by dan verified_at di-update
- [ ] Filters: status, search (nama/nomor), date range
- [ ] Stats query efficient dengan single query atau cache

---

### Task 2.4: Notification System (P1)
**Story Points:** 5

**Notifications:**
- `PsbRegistrationApproved` - Email ke parent
- `PsbRegistrationRejected` - Email ke parent dengan reason
- `PsbDocumentRevisionRequested` - Email ke parent

**Notification Channels:**
- Email (primary)
- Database (untuk in-app notification)

**Commands:**
```bash
php artisan make:notification PsbRegistrationApproved
php artisan make:notification PsbRegistrationRejected
php artisan make:notification PsbDocumentRevisionRequested
```

**Acceptance Criteria:**
- [ ] Email template proper dengan branding sekolah
- [ ] Include registration number dan next steps
- [ ] Notification queued untuk performance
- [ ] Database notification untuk tracking

---

## Frontend Developer Tasks

### Task 2.5: Admin PSB Registrations List (P0)
**Story Points:** 5

**File:** `resources/js/pages/Admin/Psb/Registrations/Index.vue`

**Requirements:**
- Table dengan columns: No. Registrasi, Nama, Tanggal Daftar, Status, Actions
- Filter by status (tabs atau dropdown)
- Search by nama atau nomor registrasi
- Pagination
- Status badges dengan warna:
  - Pending: Yellow
  - Document Review: Blue
  - Approved: Green
  - Rejected: Red
  - Waiting List: Orange

**Props:**
```typescript
interface Props {
    registrations: {
        data: PsbRegistration[];
        links: PaginationLinks;
        meta: PaginationMeta;
    };
    filters: {
        status: string | null;
        search: string | null;
    };
    stats: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
    };
}
```

**Acceptance Criteria:**
- [ ] Table responsive (card view di mobile)
- [ ] Filter & search berfungsi dengan debounce
- [ ] Pagination berfungsi
- [ ] Click row navigasi ke detail
- [ ] Badge count per status

---

### Task 2.6: Admin PSB Registration Detail (P0)
**Story Points:** 8

**File:** `resources/js/pages/Admin/Psb/Registrations/Show.vue`

**Sections:**
1. **Header:** Nomor registrasi, status badge, created date
2. **Data Siswa:** Semua data calon siswa
3. **Data Orang Tua:** Data ayah & ibu
4. **Dokumen:** Grid thumbnail dengan lightbox preview
5. **Actions:** Approve, Reject, Request Revision buttons
6. **Timeline/History:** Log perubahan status

**Components Needed:**
- `PsbDocumentPreview.vue` - Thumbnail dengan lightbox
- `PsbActionModal.vue` - Modal untuk approve/reject/revision

**Modals:**
- **Approve Modal:** Optional notes, confirm button
- **Reject Modal:** Required rejection reason, confirm button
- **Revision Modal:** Select documents, add notes per document

**Acceptance Criteria:**
- [ ] Semua data tampil dengan format yang baik
- [ ] Document preview lightbox berfungsi
- [ ] Approve modal dengan confirmation
- [ ] Reject modal require reason
- [ ] Revision modal bisa select multiple documents
- [ ] Loading state saat action
- [ ] Redirect ke list setelah action

---

### Task 2.7: Document Preview Component (P0)
**Story Points:** 3

**File:** `resources/js/components/Psb/PsbDocumentPreview.vue`

**Props:**
```typescript
interface Props {
    document: {
        id: number;
        document_type: string;
        file_path: string;
        original_name: string;
        status: 'pending' | 'approved' | 'revision_needed';
        revision_note?: string;
    };
    selectable?: boolean;
}
```

**Features:**
- Thumbnail preview (image atau PDF icon)
- Click untuk fullscreen lightbox
- Status indicator
- Revision note badge jika ada
- Checkbox jika selectable (untuk revision)

**Acceptance Criteria:**
- [ ] Image preview dengan aspect ratio proper
- [ ] PDF menampilkan icon dengan filename
- [ ] Lightbox dengan zoom
- [ ] Revision note tooltip
- [ ] Selectable mode untuk revision request

---

### Task 2.8: Admin Navigation Update (P1)
**Story Points:** 2

**File:** Update `resources/js/components/AppLayout.vue` atau navigation component

**Admin Menu Addition:**
```typescript
{
    label: 'PSB',
    icon: UserPlusIcon,
    children: [
        { label: 'Pendaftaran', href: adminPsbRegistrationsIndex, badge: pendingCount },
    ]
}
```

**Requirements:**
- Badge menampilkan jumlah pending registrations
- Icon menggunakan HeroIcons
- Nested menu style konsisten dengan existing

**Acceptance Criteria:**
- [ ] Menu PSB muncul di admin sidebar
- [ ] Badge count update realtime (via props)
- [ ] Active state saat di halaman PSB
- [ ] Icon konsisten dengan design system

---

## Sprint 2 Summary

| Role | Tasks | Total Story Points |
|------|-------|-------------------|
| Backend | 2.1, 2.2, 2.3, 2.4 | 18 |
| Frontend | 2.5, 2.6, 2.7, 2.8 | 18 |

**Definition of Done:**
- [ ] Admin bisa melihat list pendaftaran
- [ ] Admin bisa filter dan search
- [ ] Admin bisa approve/reject registration
- [ ] Admin bisa request document revision
- [ ] Notification terkirim ke parent
- [ ] Feature tests untuk verification flow
- [ ] Status update reflect di public tracking

**Integration Points:**
- Status update di backend harus reflect di public tracking (Task 1.8)
- Notification email templates harus proper
