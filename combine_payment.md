# Combined Payment System - Feature Specification

## Overview

Refactor pembayaran dari **1 Payment : 1 Bill** menjadi **1 PaymentTransaction : Many Bills** untuk mendukung bulk payment yang lebih efisien dan user-friendly.

---

## Current Problem

Saat ini ketika parent membayar 3 tagihan sekaligus dengan 1 transfer:
- Sistem membuat **3 Payment records** terpisah
- **3 nomor kwitansi** berbeda untuk 1 transaksi
- Admin harus verifikasi **3 kali** untuk 1 bukti transfer
- Parent melihat **3 entry** di riwayat untuk 1 pembayaran

## Proposed Solution

### New Data Model

```
PaymentTransaction (Transaksi Pembayaran)
├── id
├── transaction_number (TRX/2026/01/00001)
├── student_id (nullable - jika multi-student)
├── guardian_id (untuk parent submission)
├── total_amount
├── payment_method (tunai/transfer/qris)
├── payment_date
├── payment_time
├── proof_file (bukti_transfer)
├── notes
├── status (pending/verified/cancelled)
├── verified_by
├── verified_at
├── cancelled_by
├── cancelled_at
├── cancellation_reason
├── created_by
├── created_at
└── updated_at

PaymentItem (Detail Pembayaran per Bill)
├── id
├── payment_transaction_id (FK)
├── bill_id (FK)
├── amount (nominal yang dibayar untuk bill ini)
├── created_at
└── updated_at
```

### Relationship

```
PaymentTransaction 1 ──────< * PaymentItem * >────── 1 Bill
       │                         │
       │                         └── amount per bill
       │
       └── total_amount = SUM(PaymentItem.amount)
```

---

## Feature Requirements

### F1: Parent Bulk Payment Submission

**Owner:** Parent (via web/mobile)
**Action:** Submit pembayaran untuk multiple bills dengan 1 bukti transfer

**Requirements:**
- Select multiple bills (sudah ada)
- Upload 1 bukti transfer
- Input tanggal bayar
- Input catatan (optional)
- Preview total sebelum submit
- Create 1 PaymentTransaction + N PaymentItems

### F2: Admin Payment Verification

**Owner:** Admin/TU
**Consumer:** Parent (melihat status)

**Requirements:**
- List pending transactions (bukan individual payments)
- View transaction detail dengan semua bills included
- Verify ALL bills dalam 1 transaction sekaligus
- Partial verify (opsional - verify beberapa bill saja)
- Reject transaction dengan alasan
- Lihat bukti transfer

### F3: Admin Manual Payment Entry

**Owner:** Admin/TU
**Action:** Input pembayaran tunai/transfer yang datang langsung

**Requirements:**
- Select student
- Select multiple bills
- Input payment method
- Input payment date/time
- Upload bukti (optional untuk tunai)
- Auto-verify jika tunai
- Create PaymentTransaction + PaymentItems

### F4: Payment History for Parent

**Consumer:** Parent
**Data Source:** PaymentTransaction

**Requirements:**
- List transactions (bukan individual bill payments)
- Each transaction shows:
  - Transaction number
  - Total amount
  - Payment date
  - Status (pending/verified/cancelled)
  - List of bills included
- Download combined receipt (1 PDF untuk semua bills)
- Filter by status, date range

### F5: Payment Records for Admin

**Consumer:** Admin/TU
**Data Source:** PaymentTransaction

**Requirements:**
- List all transactions
- Filter by:
  - Status
  - Student
  - Payment method
  - Date range
  - Class
- View transaction detail
- Print/export combined receipt
- Void/cancel verified transaction

### F6: Combined Receipt Generation

**Consumer:** Parent, Admin
**Action:** Generate PDF receipt

**Requirements:**
- 1 receipt untuk 1 transaction
- Shows all bills included
- Shows individual amounts
- Shows total
- School header/logo
- Transaction number
- QR code untuk verification (optional)

### F7: Dashboard Statistics Update

**Consumer:** Admin, Principal
**Data Source:** PaymentTransaction aggregates

**Requirements:**
- Total transactions today/week/month
- Total amount received
- Pending verification count
- Charts by payment method

---

## Database Migration Plan

### Step 1: Create New Tables

```sql
-- payment_transactions table
CREATE TABLE payment_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(50) UNIQUE NOT NULL,
    guardian_id BIGINT UNSIGNED NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('tunai','transfer','qris') NOT NULL,
    payment_date DATE NOT NULL,
    payment_time TIME NULL,
    proof_file VARCHAR(255) NULL,
    notes TEXT NULL,
    status ENUM('pending','verified','cancelled') DEFAULT 'pending',
    verified_by BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    cancelled_by BIGINT UNSIGNED NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (guardian_id) REFERENCES guardians(id),
    FOREIGN KEY (verified_by) REFERENCES users(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- payment_items table
CREATE TABLE payment_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_transaction_id BIGINT UNSIGNED NOT NULL,
    bill_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (bill_id) REFERENCES bills(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    
    UNIQUE KEY unique_transaction_bill (payment_transaction_id, bill_id)
);
```

### Step 2: Data Migration

Migrate existing `payments` table data to new structure:
- Group payments by `bukti_transfer` + `tanggal_bayar` + `created_by`
- Create PaymentTransaction for each group
- Create PaymentItems for each original payment

### Step 3: Update Bill Status Logic

Bill status update triggered by PaymentItem changes:
- Count verified PaymentItems for each bill
- Update `nominal_terbayar` and `status` accordingly

---

## API Endpoints

### Parent Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/parent/payments/submit` | Submit bulk payment |
| GET | `/parent/payments/transactions` | List my transactions |
| GET | `/parent/payments/transactions/{id}` | Transaction detail |
| GET | `/parent/payments/transactions/{id}/receipt` | Download receipt |

### Admin Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/payments/transactions` | List all transactions |
| GET | `/admin/payments/transactions/pending` | List pending verification |
| GET | `/admin/payments/transactions/{id}` | Transaction detail |
| POST | `/admin/payments/transactions/{id}/verify` | Verify transaction |
| POST | `/admin/payments/transactions/{id}/reject` | Reject transaction |
| POST | `/admin/payments/transactions` | Manual payment entry |
| GET | `/admin/payments/transactions/{id}/receipt` | Download/print receipt |

---

## UI/UX Changes

### Parent Portal

1. **Payment Submit Page** - Minimal changes (already supports multi-bill)
2. **Payment History Page** - Change from list of payments to list of transactions
3. **Transaction Detail Modal** - Show all bills in transaction

### Admin Portal

1. **Verification Page** - Change from individual payments to transactions
2. **Payment Records Page** - Change from payments to transactions
3. **Manual Entry Form** - Support multi-bill selection
4. **Receipt Template** - New combined receipt format

---

## Backward Compatibility

### Option A: Full Migration (Recommended)
- Migrate all existing data
- Remove old `payments` table after migration
- Clean break, simpler codebase

### Option B: Parallel Tables
- Keep old `payments` table read-only
- New transactions use new tables
- More complex, but safer rollback

---

## Implementation Priority

| Priority | Feature | Effort | Dependencies |
|----------|---------|--------|--------------|
| P0 | Database migration | High | None |
| P0 | PaymentTransaction model | Medium | Migration |
| P0 | PaymentItem model | Medium | Migration |
| P0 | Parent submit update | Medium | Models |
| P0 | Admin verification update | High | Models |
| P1 | Payment history update | Medium | Models |
| P1 | Combined receipt | Medium | Models |
| P1 | Admin manual entry | Medium | Models |
| P2 | Dashboard stats update | Low | Models |
| P2 | Reports update | Medium | Models |

---

## Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Data loss during migration | High | Backup, dry-run, staged rollout |
| Breaking existing reports | Medium | Update reports before migration |
| Performance on large datasets | Medium | Proper indexing, pagination |
| Receipt number conflicts | Low | New format for transactions |

---

## Success Metrics

- [ ] Parent can submit 1 payment for multiple bills
- [ ] Admin sees 1 transaction to verify (not N)
- [ ] 1 receipt generated for bulk payment
- [ ] Existing payment data migrated correctly
- [ ] Bill status updates correctly
- [ ] Reports show accurate data

---

# Development Strategy Analysis

## PHASE 1: Feature Understanding

### Data Flow Summary

| Data | Created By | Stored In | Consumed By |
|------|------------|-----------|-------------|
| PaymentTransaction | Parent (submit), Admin (manual entry) | `payment_transactions` | Parent (history), Admin (records, verification) |
| PaymentItem | System (auto-created with transaction) | `payment_items` | Parent (detail), Admin (detail), Bill (status update) |
| Combined Receipt | System (generated) | PDF file | Parent (download), Admin (print) |

### Primary User Goals

| User | Goal | Current Pain Point |
|------|------|-------------------|
| Parent | Bayar multiple tagihan dengan 1 transfer | Melihat 3 entry untuk 1 pembayaran |
| Admin | Verifikasi pembayaran dengan cepat | Harus verifikasi 3x untuk 1 transfer |
| Principal | Melihat statistik pembayaran akurat | Data terfragmentasi |

---

## PHASE 2: Cross-Frontend Impact Mapping

| Feature | Owner (Who Creates) | Consumer (Who Views) | Data Flow |
|---------|---------------------|----------------------|-----------|
| F1: Parent Bulk Payment | Parent Portal → Submit Page | Admin Portal → Verification | Parent submits → DB → Admin sees pending |
| F2: Admin Verification | Admin Portal → Verification Page | Parent Portal → History | Admin verifies → DB → Parent sees status |
| F3: Admin Manual Entry | Admin Portal → Manual Entry Form | Parent Portal → History, Admin → Records | Admin creates → DB → Both see record |
| F4: Payment History | - | Parent Portal → History Page | DB → Parent views transactions |
| F5: Payment Records | - | Admin Portal → Records Page | DB → Admin views all transactions |
| F6: Combined Receipt | System (auto-generate) | Parent Portal, Admin Portal | Transaction → PDF → Download/Print |
| F7: Dashboard Stats | - | Admin Dashboard, Principal Dashboard | DB aggregates → Dashboard widgets |

---

## PHASE 3: Missing Implementation Detection

### F1: Parent Bulk Payment Submission

**Owner Side (Data Creation):**
- [x] UI form/interface for creating data - `Submit.vue` exists
- [x] Validation rules - `SubmitPaymentRequest.php` exists
- [ ] Edit/Update capability - ⚠️ **MISSING** - Parent cannot edit pending submission
- [ ] Delete/Archive capability - ⚠️ **MISSING** - Parent cannot cancel pending submission
- [x] Preview before publishing - Shows summary before submit
- [x] Bulk operations - Multi-bill selection exists

**Consumer Side (Data Display):**
- [ ] Where users will SEE this data - ⚠️ **NEEDS UPDATE** - History page shows individual payments
- [x] How users will FIND this data - Navigation exists
- [ ] What users can DO with this data - ⚠️ **LIMITED** - Only view, no cancel option
- [x] Mobile/responsive version - Already responsive
- [x] Empty states - Exists
- [x] Loading states - Exists

### F2: Admin Payment Verification

**Owner Side (Data Creation):**
- [ ] UI form/interface - ⚠️ **NEEDS UPDATE** - Currently per-payment, need per-transaction
- [x] Validation rules - Exists
- [ ] Bulk operations - ⚠️ **MISSING** - Cannot verify multiple transactions at once

**Consumer Side (Data Display):**
- [ ] Where users will SEE this data - ⚠️ **NEEDS UPDATE** - `Verification.vue` shows individual payments
- [x] How users will FIND this data - Navigation exists
- [ ] Transaction detail modal - ⚠️ **MISSING** - Need to show all bills in transaction

### F3: Admin Manual Payment Entry

**Owner Side (Data Creation):**
- [ ] UI form/interface - ⚠️ **NEEDS UPDATE** - `Create.vue` currently single-bill
- [ ] Multi-bill selection - ⚠️ **MISSING**
- [x] Validation rules - Exists but needs update
- [ ] Auto-verify for cash - ⚠️ **CHECK IF EXISTS**

### F4: Payment History for Parent

**Consumer Side (Data Display):**
- [ ] List view - ⚠️ **NEEDS UPDATE** - Change from payments to transactions
- [ ] Transaction detail - ⚠️ **MISSING** - Need modal showing all bills
- [ ] Download receipt - ⚠️ **NEEDS UPDATE** - Combined receipt
- [ ] Filter by status - ⚠️ **CHECK IF EXISTS**

### F5: Payment Records for Admin

**Consumer Side (Data Display):**
- [ ] List view - ⚠️ **NEEDS UPDATE** - Change from payments to transactions
- [ ] Filters - ⚠️ **CHECK** - May need updates for transaction model
- [ ] Transaction detail - ⚠️ **MISSING**
- [ ] Void/cancel action - ⚠️ **CHECK IF EXISTS**

### F6: Combined Receipt

- [ ] Receipt template - ⚠️ **NEEDS UPDATE** - New format for multiple bills
- [ ] PDF generation - ⚠️ **NEEDS UPDATE** - Support transaction model
- [ ] QR code verification - ⚠️ **OPTIONAL** - Not critical

### F7: Dashboard Statistics

- [ ] Admin Dashboard widgets - ⚠️ **NEEDS UPDATE** - Aggregate from transactions
- [ ] Principal Dashboard widgets - ⚠️ **NEEDS UPDATE** - Aggregate from transactions

---

## PHASE 4: Gap Analysis

### ⚠️ Critical Gaps

| Gap | Impact | Resolution |
|-----|--------|------------|
| Parent cannot cancel pending submission | Parent stuck if made mistake | Add cancel functionality |
| Admin verifies per-payment not per-transaction | Inefficient workflow | Update verification to transaction-based |
| Receipt shows single payment | Confusing for bulk payments | New combined receipt template |
| History shows individual payments | Parent confusion | Update to transaction-based list |

### ⚠️ Data Flow Gaps

| From | To | Issue |
|------|-----|-------|
| PaymentTransaction | Bill.status | Need new trigger logic for PaymentItem |
| PaymentTransaction | Reports | Reports query old `payments` table |
| PaymentTransaction | Bank Reconciliation | Need to update matching logic |

### ⚠️ Navigation Gaps

| Frontend | Current | Needed |
|----------|---------|--------|
| Parent Sidebar | "Pembayaran" | No change needed |
| Admin Sidebar | "Verifikasi" badge | Update count from transactions |
| Admin Sidebar | "Rekam Pembayaran" | Consider renaming to "Transaksi" |

---

## PHASE 5: Implementation Sequencing

### Dependencies Graph

```
                    ┌─────────────────┐
                    │ Database        │
                    │ Migration       │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
              ▼              ▼              ▼
     ┌────────────┐  ┌────────────┐  ┌────────────┐
     │ Transaction│  │ PaymentItem│  │ Service    │
     │ Model      │  │ Model      │  │ Layer      │
     └──────┬─────┘  └─────┬──────┘  └─────┬──────┘
            │              │               │
            └──────────────┼───────────────┘
                           │
         ┌─────────────────┼─────────────────┐
         │                 │                 │
         ▼                 ▼                 ▼
  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
  │ Parent      │  │ Admin       │  │ Receipt     │
  │ Submit      │  │ Verification│  │ Template    │
  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘
         │                │                │
         ▼                ▼                ▼
  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
  │ Parent      │  │ Admin       │  │ Dashboard   │
  │ History     │  │ Records     │  │ Updates     │
  └─────────────┘  └─────────────┘  └─────────────┘
```

### Priority Matrix

| Priority | Item | Reason | Effort |
|----------|------|--------|--------|
| **P0 - Critical** | | | |
| P0 | Database migration | Foundation for everything | High |
| P0 | PaymentTransaction model | Core data model | Medium |
| P0 | PaymentItem model | Core data model | Medium |
| P0 | PaymentTransactionService | Business logic | High |
| P0 | Parent submit update | Primary use case | Medium |
| P0 | Admin verification update | Primary admin workflow | High |
| **P1 - Important** | | | |
| P1 | Parent history update | User needs to see results | Medium |
| P1 | Combined receipt | User needs proof | Medium |
| P1 | Admin records update | Admin needs full view | Medium |
| P1 | Admin manual entry update | Admin needs to create | Medium |
| P1 | Data migration script | Existing data must work | High |
| **P2 - Enhancement** | | | |
| P2 | Parent cancel pending | Nice to have | Low |
| P2 | Dashboard stats update | Can work with old data initially | Low |
| P2 | Reports update | Can delay | Medium |
| P2 | Bank reconciliation update | Can delay | Medium |

---

## PHASE 6: Detailed Recommendations

### Backend - New Files Needed

| File | Purpose | Priority |
|------|---------|----------|
| `database/migrations/xxxx_create_payment_transactions_table.php` | New table | P0 |
| `database/migrations/xxxx_create_payment_items_table.php` | New table | P0 |
| `app/Models/PaymentTransaction.php` | Eloquent model | P0 |
| `app/Models/PaymentItem.php` | Eloquent model | P0 |
| `app/Services/PaymentTransactionService.php` | Business logic | P0 |
| `app/Http/Requests/Parent/SubmitTransactionRequest.php` | Validation | P0 |
| `app/Http/Requests/Admin/StoreTransactionRequest.php` | Validation | P1 |
| `database/migrations/xxxx_migrate_payments_to_transactions.php` | Data migration | P1 |

### Backend - Files to Update

| File | Changes | Priority |
|------|---------|----------|
| `app/Http/Controllers/Parent/PaymentController.php` | Update submit, add transaction methods | P0 |
| `app/Http/Controllers/Admin/PaymentController.php` | Update verification, records | P0 |
| `app/Models/Bill.php` | Update status logic for PaymentItem | P0 |
| `routes/parent.php` | Add transaction routes | P0 |
| `routes/admin.php` | Add transaction routes | P0 |
| `resources/views/receipts/payment.blade.php` | Combined receipt template | P1 |

### Frontend - New Files Needed

| File | Purpose | Priority |
|------|---------|----------|
| `resources/js/pages/Parent/Payments/TransactionDetail.vue` | Transaction detail modal | P1 |
| `resources/js/pages/Admin/Payments/Transactions/Index.vue` | Transaction list | P1 |
| `resources/js/pages/Admin/Payments/Transactions/Show.vue` | Transaction detail | P1 |

### Frontend - Files to Update

| File | Changes | Priority |
|------|---------|----------|
| `resources/js/pages/Parent/Payments/Submit.vue` | Update to create transaction | P0 |
| `resources/js/pages/Parent/Payments/Index.vue` | Update pending section for transactions | P0 |
| `resources/js/pages/Parent/Payments/History.vue` | Change to transaction-based list | P1 |
| `resources/js/pages/Admin/Payments/Payments/Verification.vue` | Update to transaction-based | P0 |
| `resources/js/pages/Admin/Payments/Payments/Index.vue` | Update to transaction-based | P1 |
| `resources/js/pages/Admin/Payments/Payments/Create.vue` | Support multi-bill | P1 |
| `resources/js/pages/Admin/Payments/Payments/Show.vue` | Show transaction detail | P1 |
| `resources/js/pages/Dashboard/AdminDashboard.vue` | Update stats queries | P2 |
| `resources/js/pages/Dashboard/PrincipalDashboard.vue` | Update stats queries | P2 |

### Navigation/Menu Changes

| Frontend | Menu Item | Parent | Action | Priority |
|----------|-----------|--------|--------|----------|
| Admin | "Transaksi" | Pembayaran | Rename from "Rekam Pembayaran" | P2 |
| Admin | Badge count | Verifikasi | Update to count transactions | P0 |

---

## PHASE 7: Example User Journeys

### Journey 1: Parent Submits Bulk Payment (P0)

**Owner Journey (Parent):**
1. User navigates to: `/parent/payments`
2. User sees: List of unpaid bills with checkboxes
3. User selects: 3 bills (SPP Jan, Feb, Mar)
4. User clicks: "Bayar Sekarang" button
5. System navigates to: `/parent/payments/submit?bills=1,2,3`
6. User sees: Summary of selected bills, bank info, upload form
7. User uploads: Transfer proof image
8. User fills: Payment date, optional notes
9. User clicks: "Submit Pembayaran"
10. System creates: 1 PaymentTransaction + 3 PaymentItems
11. User sees: Success toast, redirected to payments page
12. User sees: "Menunggu Verifikasi" section with 1 transaction

**Consumer Journey (Admin):**
1. Admin navigates to: `/admin/payments/verification`
2. Admin sees: List of pending transactions (not individual payments)
3. Admin sees: "1 transaksi" badge, not "3 pembayaran"
4. Admin clicks: Transaction row
5. Admin sees: Modal with all 3 bills, total amount, proof image
6. Admin clicks: "Verifikasi" button
7. System updates: Transaction status = verified, all 3 bills = lunas
8. Parent sees: Status changed in their history

### Journey 2: Admin Manual Entry for Walk-in Payment (P1)

**Owner Journey (Admin):**
1. Admin navigates to: `/admin/payments/create`
2. Admin searches: Student by name/NIS
3. Admin sees: List of unpaid bills for student
4. Admin selects: Multiple bills to pay
5. Admin fills: Payment method (Tunai), date, amount
6. Admin clicks: "Simpan"
7. System creates: 1 PaymentTransaction (auto-verified) + N PaymentItems
8. Admin sees: Success, option to print receipt

### Journey 3: Parent Views Payment History (P1)

**Consumer Journey (Parent):**
1. User navigates to: `/parent/payments` → "Riwayat" tab
2. User sees: List of transactions (not individual payments)
3. Each row shows: Date, total amount, status, bill count
4. User clicks: Transaction row
5. User sees: Modal with transaction details, all bills included
6. User clicks: "Download Kwitansi"
7. User receives: 1 PDF with all bills listed

---

## File Structure Recommendation

```
app/
├── Models/
│   ├── PaymentTransaction.php      # NEW
│   ├── PaymentItem.php             # NEW
│   ├── Payment.php                 # DEPRECATE after migration
│   └── Bill.php                    # UPDATE status logic
├── Services/
│   ├── PaymentTransactionService.php  # NEW
│   └── PaymentService.php          # UPDATE or DEPRECATE
├── Http/
│   ├── Controllers/
│   │   ├── Parent/
│   │   │   └── PaymentController.php      # UPDATE
│   │   └── Admin/
│   │       └── PaymentController.php      # UPDATE
│   │       └── TransactionController.php  # NEW (optional, bisa di PaymentController)
│   └── Requests/
│       ├── Parent/
│       │   └── SubmitTransactionRequest.php  # NEW or UPDATE existing
│       └── Admin/
│           └── StoreTransactionRequest.php   # NEW

database/
├── migrations/
│   ├── xxxx_create_payment_transactions_table.php  # NEW
│   ├── xxxx_create_payment_items_table.php         # NEW
│   └── xxxx_migrate_payments_to_transactions.php   # NEW (data migration)

resources/
├── js/pages/
│   ├── Parent/Payments/
│   │   ├── Index.vue           # UPDATE - pending transactions section
│   │   ├── History.vue         # UPDATE - transaction-based list
│   │   └── Submit.vue          # UPDATE - create transaction
│   └── Admin/Payments/
│       ├── Payments/
│       │   ├── Index.vue       # UPDATE - transaction list
│       │   ├── Create.vue      # UPDATE - multi-bill support
│       │   ├── Show.vue        # UPDATE - transaction detail
│       │   └── Verification.vue # UPDATE - transaction verification
│       └── Transactions/       # OPTIONAL - separate folder
│           └── ...
├── views/receipts/
│   └── transaction.blade.php   # NEW - combined receipt template

routes/
├── parent.php                  # UPDATE - transaction routes
└── admin.php                   # UPDATE - transaction routes
```

---

## Sprint Recommendation

### Sprint 1: Foundation (Week 1)
- [ ] Database migrations (new tables)
- [ ] PaymentTransaction model
- [ ] PaymentItem model
- [ ] PaymentTransactionService (basic CRUD)
- [ ] Update Bill status logic

### Sprint 2: Parent Flow (Week 2)
- [ ] Update Parent submit to create transaction
- [ ] Update Parent Index pending section
- [ ] Update validation rules
- [ ] Test parent submission flow

### Sprint 3: Admin Flow (Week 3)
- [ ] Update Admin verification page
- [ ] Update Admin records page
- [ ] Update Admin manual entry (multi-bill)
- [ ] Combined receipt template

### Sprint 4: Migration & Polish (Week 4)
- [ ] Data migration script
- [ ] Run migration on staging
- [ ] Update dashboard stats
- [ ] Update reports
- [ ] Testing & bug fixes

---

## Testing Checklist

### Unit Tests
- [ ] PaymentTransaction model methods
- [ ] PaymentItem model methods
- [ ] PaymentTransactionService methods
- [ ] Bill status update from PaymentItem

### Feature Tests
- [ ] Parent can submit bulk payment
- [ ] Admin can verify transaction
- [ ] Admin can reject transaction
- [ ] Admin can create manual transaction
- [ ] Receipt generation works
- [ ] Data migration preserves all data

### Integration Tests
- [ ] Full parent payment flow
- [ ] Full admin verification flow
- [ ] Bill status updates correctly
- [ ] Dashboard stats accurate
