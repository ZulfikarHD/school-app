# Epic 3: Announcement & Re-registration

## Sprint 3 - Week 3

**Goal:** Membangun sistem pengumuman untuk Admin dan fitur daftar ulang untuk Parent yang diterima.

---

## Backend Developer Tasks

### Task 3.1: Announcement Controller & Routes (P1)
**Story Points:** 5

**Routes:**
```php
// Admin Routes
Route::prefix('admin/psb/announcements')->name('admin.psb.announcements.')->group(function () {
    Route::get('/', [AdminPsbAnnouncementController::class, 'index'])->name('index');
    Route::post('/bulk-announce', [AdminPsbAnnouncementController::class, 'bulkAnnounce'])->name('bulk-announce');
});
```

**Controller Methods:**
- `index()` - List approved registrations ready for announcement
- `bulkAnnounce()` - Bulk update status to announced & send notifications

**Acceptance Criteria:**
- [ ] List hanya menampilkan status `approved`
- [ ] Bulk selection dengan checkbox
- [ ] Bulk announce update `announced_at`
- [ ] Send announcement notification to all selected

---

### Task 3.2: Parent Re-registration Controller & Routes (P1)
**Story Points:** 5

**Routes:**
```php
// Parent Routes (authenticated parents only)
Route::prefix('parent/psb')->name('parent.psb.')->middleware(['auth', 'role:parent'])->group(function () {
    Route::get('/re-register', [ParentPsbController::class, 'reRegister'])->name('re-register');
    Route::post('/re-register', [ParentPsbController::class, 'submitReRegister'])->name('submit-re-register');
    Route::post('/payment', [ParentPsbController::class, 'uploadPayment'])->name('upload-payment');
    Route::get('/welcome', [ParentPsbController::class, 'welcome'])->name('welcome');
});
```

**Controller Methods:**
- `reRegister()` - Form daftar ulang (jika status announced)
- `submitReRegister()` - Submit data tambahan daftar ulang
- `uploadPayment()` - Upload bukti pembayaran
- `welcome()` - Welcome page setelah selesai

**Business Logic:**
- Hanya parent dengan registration status `announced` yang bisa akses
- Link registration ke parent user account
- Setelah payment verified, create Student record

**Acceptance Criteria:**
- [ ] Access control berdasarkan registration status
- [ ] Re-registration form dengan data tambahan
- [ ] Payment upload ke storage
- [ ] Welcome page setelah completed

---

### Task 3.3: PsbService - Announcement & Re-registration Methods (P1)
**Story Points:** 5

Tambahkan methods ke PsbService:

```php
class PsbService
{
    // Bulk announce registrations
    public function bulkAnnounce(array $registrationIds): int;
    
    // Submit re-registration
    public function submitReRegistration(PsbRegistration $registration, array $data): bool;
    
    // Upload payment proof
    public function uploadPaymentProof(PsbRegistration $registration, UploadedFile $file, array $data): PsbPayment;
    
    // Verify payment
    public function verifyPayment(PsbPayment $payment, User $verifier, bool $approved, ?string $notes = null): bool;
    
    // Create student from completed registration
    public function createStudentFromRegistration(PsbRegistration $registration): Student;
    
    // Get parent's PSB registration
    public function getParentRegistration(User $parent): ?PsbRegistration;
}
```

**createStudentFromRegistration Logic:**
1. Create User account untuk siswa (jika belum ada)
2. Create Student record dengan data dari registration
3. Create Parent-Student relationship
4. Update registration status ke `completed`

**Acceptance Criteria:**
- [ ] Bulk announce dengan single query
- [ ] Payment proof stored dengan proper naming
- [ ] Student creation dengan semua relationships
- [ ] Status transitions valid

---

### Task 3.4: Principal PSB Controller & Routes (P1)
**Story Points:** 3

**Routes:**
```php
// Principal Routes
Route::prefix('principal/psb')->name('principal.psb.')->middleware(['auth', 'role:principal'])->group(function () {
    Route::get('/', [PrincipalPsbController::class, 'dashboard'])->name('dashboard');
});
```

**Controller Methods:**
- `dashboard()` - PSB Dashboard dengan summary stats dan charts data

**Dashboard Data:**
```php
return Inertia::render('Principal/Psb/Dashboard', [
    'summary' => [
        'total' => 150,
        'pending' => 15,
        'approved' => 100,
        'rejected' => 10,
        're_registration' => 8,
        'completed' => 17,
    ],
    'dailyRegistrations' => [...], // untuk chart
    'genderDistribution' => [...],
    'statusDistribution' => [...],
]);
```

**Acceptance Criteria:**
- [ ] Summary stats accurate
- [ ] Chart data formatted untuk frontend
- [ ] Read-only access (tidak bisa modify)

---

### Task 3.5: Payment Verification (P1)
**Story Points:** 3

**Routes:**
```php
// Admin Routes for payment
Route::prefix('admin/psb/payments')->name('admin.psb.payments.')->group(function () {
    Route::get('/', [AdminPsbPaymentController::class, 'index'])->name('index');
    Route::post('/{payment}/verify', [AdminPsbPaymentController::class, 'verify'])->name('verify');
});
```

**Controller Methods:**
- `index()` - List pending payments
- `verify()` - Approve/reject payment

**Acceptance Criteria:**
- [ ] List payments dengan filter status
- [ ] Payment proof preview
- [ ] Approve creates Student record
- [ ] Reject dengan reason

---

## Frontend Developer Tasks

### Task 3.6: Admin Announcement Page (P1)
**Story Points:** 5

**File:** `resources/js/pages/Admin/Psb/Announcements/Index.vue`

**Requirements:**
- Table approved registrations
- Checkbox untuk bulk selection
- "Select All" checkbox
- "Umumkan Terpilih" button
- Confirmation modal sebelum announce
- Filter by academic year

**Features:**
- Selected count indicator
- Preview announcement message
- Send notification option

**Acceptance Criteria:**
- [ ] Checkbox selection berfungsi
- [ ] Bulk action dengan confirmation
- [ ] Loading state saat processing
- [ ] Success/error feedback
- [ ] Update list setelah announce

---

### Task 3.7: Parent Re-registration Page (P1)
**Story Points:** 5

**File:** `resources/js/pages/Parent/Psb/ReRegister.vue`

**Requirements:**
- Show registration summary
- Additional data form (jika diperlukan)
- Payment information display (biaya, rekening tujuan)
- Upload bukti pembayaran
- Status tracking di halaman yang sama

**Sections:**
1. **Status Banner:** "Selamat! Anda diterima di [Sekolah]"
2. **Registration Summary:** Data yang sudah diisi
3. **Payment Section:**
   - Informasi biaya pendaftaran
   - Nomor rekening
   - Upload form untuk bukti transfer
4. **Status Tracking:** Timeline progress daftar ulang

**Acceptance Criteria:**
- [ ] Congratulations banner
- [ ] Payment info jelas
- [ ] Upload dengan preview
- [ ] Progress tracking
- [ ] Redirect ke welcome setelah verified

---

### Task 3.8: Parent Welcome Page (P1)
**Story Points:** 2

**File:** `resources/js/pages/Parent/Psb/Welcome.vue`

**Requirements:**
- Welcome message dengan nama siswa
- Informasi selanjutnya (orientasi, jadwal, dll)
- Link ke parent dashboard
- Celebration animation

**Acceptance Criteria:**
- [ ] Welcome message personalized
- [ ] Next steps information
- [ ] Navigation ke parent portal
- [ ] Confetti atau celebration UI

---

### Task 3.9: Principal PSB Dashboard (P1)
**Story Points:** 8

**File:** `resources/js/pages/Principal/Psb/Dashboard.vue`

**Sections:**
1. **Summary Cards:**
   - Total Pendaftar
   - Menunggu Verifikasi
   - Disetujui
   - Ditolak
   - Daftar Ulang
   - Selesai

2. **Charts:**
   - Registrations per day (line chart)
   - Status distribution (donut chart)
   - Gender distribution (pie chart)

3. **Quick Links:**
   - View all registrations (link ke admin)
   - Download report

**Components:**
- Reuse existing chart components atau install `chart.js`/`apexcharts`

**Props:**
```typescript
interface Props {
    summary: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
        re_registration: number;
        completed: number;
    };
    dailyRegistrations: { date: string; count: number }[];
    genderDistribution: { gender: string; count: number }[];
    statusDistribution: { status: string; count: number }[];
}
```

**Acceptance Criteria:**
- [ ] Summary cards dengan click-to-filter
- [ ] Charts render dengan data
- [ ] Responsive layout
- [ ] Loading states untuk data
- [ ] Empty state jika no data

---

### Task 3.10: Navigation Updates - Principal & Parent (P1)
**Story Points:** 2

**Principal Navigation:**
```typescript
{
    label: 'PSB',
    icon: UserPlusIcon,
    href: principalPsbDashboard
}
```

**Parent Navigation (conditional):**
```typescript
// Show only if has announced registration
{
    label: 'Daftar Ulang',
    icon: ClipboardDocumentCheckIcon,
    href: parentPsbReRegister,
    show: hasAnnouncedRegistration
}
```

**Acceptance Criteria:**
- [ ] Principal sees PSB menu
- [ ] Parent sees Daftar Ulang only if applicable
- [ ] Active states correct

---

## Sprint 3 Summary

| Role | Tasks | Total Story Points |
|------|-------|-------------------|
| Backend | 3.1, 3.2, 3.3, 3.4, 3.5 | 21 |
| Frontend | 3.6, 3.7, 3.8, 3.9, 3.10 | 22 |

**Definition of Done:**
- [ ] Admin bisa bulk announce
- [ ] Parent bisa daftar ulang
- [ ] Parent bisa upload bukti bayar
- [ ] Admin bisa verify payment
- [ ] Student record created setelah complete
- [ ] Principal bisa view dashboard
- [ ] Feature tests untuk complete flow

**Integration Points:**
- Announcement triggers email ke parent
- Payment verification triggers student creation
- Principal dashboard reads from same data source
