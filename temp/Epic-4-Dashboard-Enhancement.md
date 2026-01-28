# Epic 4: Dashboard & Enhancement

## Sprint 4 - Week 4

**Goal:** Membangun fitur settings, export, enhancement, dan melakukan testing & polish.

---

## Backend Developer Tasks

### Task 4.1: PSB Settings Controller & Routes (P1)
**Story Points:** 5

**Routes:**
```php
// Admin Routes for settings
Route::prefix('admin/psb/settings')->name('admin.psb.settings.')->group(function () {
    Route::get('/', [AdminPsbSettingsController::class, 'index'])->name('index');
    Route::post('/', [AdminPsbSettingsController::class, 'store'])->name('store');
    Route::put('/{setting}', [AdminPsbSettingsController::class, 'update'])->name('update');
});
```

**Controller Methods:**
- `index()` - Settings page dengan current settings
- `store()` - Create new PSB period settings
- `update()` - Update existing settings

**Settings Data:**
```php
return [
    'academic_year_id' => 1,
    'registration_open_date' => '2025-01-01',
    'registration_close_date' => '2025-03-31',
    'announcement_date' => '2025-04-15',
    're_registration_deadline_days' => 14,
    'registration_fee' => 2500000,
    'quota_per_class' => 30,
    'waiting_list_enabled' => true,
];
```

**Form Request - StorePsbSettingsRequest:**
```php
public function rules(): array
{
    return [
        'academic_year_id' => ['required', 'exists:academic_years,id'],
        'registration_open_date' => ['required', 'date'],
        'registration_close_date' => ['required', 'date', 'after:registration_open_date'],
        'announcement_date' => ['required', 'date', 'after:registration_close_date'],
        're_registration_deadline_days' => ['required', 'integer', 'min:7', 'max:30'],
        'registration_fee' => ['required', 'numeric', 'min:0'],
        'quota_per_class' => ['required', 'integer', 'min:1'],
        'waiting_list_enabled' => ['boolean'],
    ];
}
```

**Acceptance Criteria:**
- [ ] CRUD settings per academic year
- [ ] Validation dates logical
- [ ] Only one active setting per academic year
- [ ] Settings affect public registration

---

### Task 4.2: Export to Excel (P2)
**Story Points:** 5

**Routes:**
```php
Route::get('/admin/psb/export', [AdminPsbExportController::class, 'export'])->name('admin.psb.export');
```

**Controller Methods:**
- `export()` - Generate Excel file dengan filters

**Export Options:**
- Filter by status
- Filter by date range
- Include/exclude documents info
- Include payment status

**Columns:**
- No. Registrasi
- Nama Siswa
- NIK
- TTL
- Jenis Kelamin
- Agama
- Alamat
- Nama Ayah
- No. HP Ayah
- Nama Ibu
- No. HP Ibu
- Status
- Tanggal Daftar
- Tanggal Verifikasi
- Status Pembayaran

**Implementation:**
```bash
composer require maatwebsite/excel
php artisan make:export PsbRegistrationsExport --model=PsbRegistration
```

**Acceptance Criteria:**
- [ ] Export to .xlsx format
- [ ] Filters applied
- [ ] Proper column headers
- [ ] Download response correct

---

### Task 4.3: Admin PSB Dashboard (P1)
**Story Points:** 3

**Routes:**
```php
Route::get('/admin/psb', [AdminPsbController::class, 'index'])->name('admin.psb.index');
```

**Dashboard Data (similar to Principal but with actions):**
- Summary cards
- Quick links to each section
- Recent registrations list (5 items)
- Pending actions count

**Acceptance Criteria:**
- [ ] Overview stats
- [ ] Quick navigation
- [ ] Recent activity

---

### Task 4.4: Comprehensive Testing (P0)
**Story Points:** 8

**Unit Tests:**
- `PsbServiceTest` - All service methods
- `PsbRegistrationTest` - Model relationships dan casts

**Feature Tests:**
- `PublicPsbRegistrationTest` - Public registration flow
- `AdminPsbVerificationTest` - Verification flow
- `AdminPsbAnnouncementTest` - Announcement flow
- `ParentReRegistrationTest` - Re-registration flow
- `PsbSettingsTest` - Settings CRUD

**Test Scenarios:**
```php
// PublicPsbRegistrationTest
public function test_can_view_psb_landing_page(): void
public function test_can_submit_registration(): void
public function test_cannot_register_when_period_closed(): void
public function test_can_track_registration_status(): void
public function test_registration_requires_all_documents(): void

// AdminPsbVerificationTest
public function test_admin_can_view_registrations_list(): void
public function test_admin_can_filter_by_status(): void
public function test_admin_can_approve_registration(): void
public function test_admin_can_reject_with_reason(): void
public function test_admin_can_request_document_revision(): void

// AdminPsbAnnouncementTest
public function test_admin_can_bulk_announce(): void
public function test_announcement_sends_notification(): void

// ParentReRegistrationTest
public function test_parent_can_access_re_registration(): void
public function test_parent_cannot_access_if_not_announced(): void
public function test_parent_can_upload_payment(): void
public function test_student_created_after_payment_verified(): void
```

**Acceptance Criteria:**
- [ ] All unit tests passing
- [ ] All feature tests passing
- [ ] Coverage minimal 80% untuk PSB code
- [ ] Edge cases covered

---

### Task 4.5: API Documentation & Wayfinder (P1)
**Story Points:** 2

**Generate Wayfinder Routes:**
```bash
php artisan wayfinder:generate
```

**Verify:**
- All PSB routes generated in `resources/js/routes/`
- TypeScript types correct
- Naming consistent

**Acceptance Criteria:**
- [ ] Wayfinder routes generated
- [ ] No TypeScript errors
- [ ] Routes importable in Vue

---

## Frontend Developer Tasks

### Task 4.6: Admin PSB Settings Page (P1)
**Story Points:** 5

**File:** `resources/js/pages/Admin/Psb/Settings/Index.vue`

**Sections:**
1. **Active Period Info:**
   - Current academic year
   - Status (Open/Closed)
   - Countdown to open/close

2. **Settings Form:**
   - Academic year selector
   - Date pickers untuk open/close/announcement
   - Re-registration deadline (days)
   - Registration fee (currency input)
   - Quota per class
   - Waiting list toggle

3. **History:**
   - Previous PSB periods
   - Stats per period

**Acceptance Criteria:**
- [ ] Form dengan proper validation
- [ ] Date pickers user-friendly
- [ ] Currency formatting (Rp)
- [ ] Toggle switch untuk boolean
- [ ] Save confirmation

---

### Task 4.7: Admin PSB Dashboard Page (P1)
**Story Points:** 5

**File:** `resources/js/pages/Admin/Psb/Index.vue`

**Requirements:**
- Summary cards (same as Principal)
- Quick action buttons
- Recent registrations (5 items)
- Pending actions alerts

**Quick Actions:**
- Verifikasi Pending (badge count)
- Pengumuman
- Pengaturan
- Export Data

**Acceptance Criteria:**
- [ ] Dashboard overview
- [ ] Click cards navigate to filtered list
- [ ] Quick actions visible
- [ ] Alerts untuk pending items

---

### Task 4.8: Export UI (P2)
**Story Points:** 3

**Location:** Add to `Admin/Psb/Registrations/Index.vue` atau create modal

**Requirements:**
- Export button di list page
- Filter options modal:
  - Status filter
  - Date range
  - Column selection
- Download progress indicator

**Acceptance Criteria:**
- [ ] Export button visible
- [ ] Filter modal
- [ ] Download works
- [ ] Loading state

---

### Task 4.9: Complete Navigation Updates (P1)
**Story Points:** 3

**Admin Full Menu:**
```typescript
{
    label: 'PSB',
    icon: UserPlusIcon,
    children: [
        { label: 'Dashboard', href: adminPsbIndex },
        { label: 'Pendaftaran', href: adminPsbRegistrationsIndex, badge: pendingCount },
        { label: 'Pengumuman', href: adminPsbAnnouncementsIndex },
        { label: 'Pembayaran', href: adminPsbPaymentsIndex, badge: pendingPayments },
        { label: 'Pengaturan', href: adminPsbSettingsIndex },
    ]
}
```

**Acceptance Criteria:**
- [ ] All menu items linked
- [ ] Badges show counts
- [ ] Nested menu styling
- [ ] Permission-based visibility

---

### Task 4.10: UI Polish & Mobile Testing (P1)
**Story Points:** 5

**Polish Tasks:**
- Motion-v animations pada semua transitions
- Loading skeletons untuk deferred props
- Error states dengan retry
- Empty states dengan illustrations
- Toast notifications consistent

**Mobile Testing:**
- Registration form pada mobile
- Table responsiveness (card view)
- Touch targets adequate (44px minimum)
- Scroll behaviors smooth

**Acceptance Criteria:**
- [ ] Semua page tested di mobile
- [ ] Animations smooth
- [ ] Touch-friendly
- [ ] No horizontal scroll issues
- [ ] Forms usable di mobile

---

### Task 4.11: Frontend Testing (P2)
**Story Points:** 3

**Component Tests (if applicable):**
- PsbTimeline.vue
- PsbMultiStepForm.vue
- PsbDocumentPreview.vue

**E2E Considerations:**
- Registration flow
- Verification flow
- Re-registration flow

**Acceptance Criteria:**
- [ ] Critical components tested
- [ ] Happy path E2E documented

---

## Sprint 4 Summary

| Role | Tasks | Total Story Points |
|------|-------|-------------------|
| Backend | 4.1, 4.2, 4.3, 4.4, 4.5 | 23 |
| Frontend | 4.6, 4.7, 4.8, 4.9, 4.10, 4.11 | 24 |

**Definition of Done:**
- [ ] All features complete
- [ ] All tests passing
- [ ] Mobile tested
- [ ] No lint errors
- [ ] Documentation updated
- [ ] Ready for deployment

---

## Post-Sprint / Backlog (P2 Enhancement)

Items yang bisa di-ship later:

1. **WhatsApp Notifications**
   - Integrate dengan WhatsApp API
   - Send status updates via WA

2. **Bulk Verification**
   - Admin bisa bulk approve/reject
   - Mass action dengan filters

3. **Test Scheduling (Optional)**
   - Schedule interview/test
   - Calendar integration

4. **Demographics Charts**
   - Age distribution
   - Region analysis
   - School origin stats

5. **Advanced Reports**
   - Funnel analysis
   - Conversion rates
   - Year-over-year comparison

6. **Waiting List Management**
   - Auto-promote dari waiting list
   - Priority scoring

---

## Final Notes

### Run Build After Each Sprint
```bash
yarn run build
```

### Lint Check
```bash
yarn run lint
vendor/bin/pint --dirty
```

### Full Test Suite
```bash
php artisan test
```
