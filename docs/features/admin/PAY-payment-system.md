# Feature: PAY - Payment System (Sistem Pembayaran)

> **Code:** PAY | **Priority:** Critical | **Status:** ✅ Complete
> **Sprint:** 1-6 | **Menu:** Admin → Pembayaran, Parent → Pembayaran, Principal → Keuangan

---

## Overview

Payment System merupakan fitur digitalisasi pencatatan pembayaran sekolah yang bertujuan untuk mengelola tagihan siswa (SPP, Uang Gedung, Seragam, Kegiatan), yaitu: tracking status real-time, pencatatan pembayaran manual, generate kwitansi PDF, laporan keuangan, dan rekonsiliasi bank.

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| PAY-01 | Admin | Membuat kategori pembayaran | Dapat mengorganisir jenis pembayaran | ✅ |
| PAY-02 | Admin | Generate tagihan bulanan | Siswa mendapat tagihan otomatis | ✅ |
| PAY-03 | Admin | Mencatat pembayaran manual | Pembayaran tunai tercatat | ✅ |
| PAY-04 | Admin | Verifikasi pembayaran transfer | Pembayaran transfer terkonfirmasi | ✅ |
| PAY-05 | Admin | Generate kwitansi PDF | Siswa mendapat bukti pembayaran | ✅ |
| PAY-06 | Admin | Melihat laporan keuangan | Dapat monitoring pemasukan | ✅ |
| PAY-07 | Admin | Rekonsiliasi bank | Mencocokkan pembayaran dengan mutasi bank | ✅ |
| PAY-08 | Parent | Melihat tagihan anak | Tahu berapa yang harus dibayar | ✅ |
| PAY-09 | Parent | Melihat riwayat pembayaran | Dapat tracking pembayaran | ✅ |
| PAY-10 | Parent | Download kwitansi | Punya bukti pembayaran digital | ✅ |
| PAY-11 | Principal | Melihat laporan keuangan | Monitoring kesehatan finansial sekolah | ✅ |
| PAY-12 | Principal | Melihat siswa menunggak | Dapat follow-up tunggakan | ✅ |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-01 | Pembayaran tunai langsung verified | Auto-verify saat metode = tunai |
| BR-02 | Pembayaran transfer butuh verifikasi | Status = pending sampai admin verify |
| BR-03 | Nominal tidak boleh melebihi sisa tagihan | Validasi di StorePaymentRequest |
| BR-04 | Kategori non-aktif tidak bisa generate tagihan | Check is_active sebelum generate |
| BR-05 | Nomor kwitansi format: KWT/YYYY/MM/XXXXX | Auto-generate di Payment model |
| BR-06 | Tahun ajaran: Juli = tahun/tahun+1, Jan-Juni = tahun-1/tahun | calculateTahunAjaran() di service |
| BR-07 | Due day default tanggal 10 setiap bulan | Configurable per kategori |
| BR-08 | Partial payment diizinkan | Status = 'sebagian' jika belum lunas |

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| Model | `app/Models/PaymentCategory.php` | Kategori pembayaran |
| Model | `app/Models/Bill.php` | Tagihan siswa |
| Model | `app/Models/Payment.php` | Pembayaran siswa |
| Model | `app/Models/BankReconciliation.php` | Rekonsiliasi bank |
| Service | `app/Services/PaymentService.php` | Recording, receipt, verification |
| Service | `app/Services/BillGenerationService.php` | Generate tagihan bulk |
| Service | `app/Services/BankReconciliationService.php` | Bank reconciliation |
| Controller | `app/Http/Controllers/Admin/PaymentCategoryController.php` | CRUD kategori |
| Controller | `app/Http/Controllers/Admin/BillController.php` | Generate tagihan |
| Controller | `app/Http/Controllers/Admin/PaymentController.php` | Recording & reports |
| Controller | `app/Http/Controllers/Admin/BankReconciliationController.php` | Bank reconciliation |
| Controller | `app/Http/Controllers/Parent/PaymentController.php` | Parent portal |
| Controller | `app/Http/Controllers/Principal/FinancialReportController.php` | Principal reports |
| Page | `resources/js/pages/Admin/Payments/**/*.vue` | Admin payment pages |
| Page | `resources/js/pages/Parent/Payments/**/*.vue` | Parent payment pages |
| Page | `resources/js/pages/Principal/Financial/**/*.vue` | Principal report pages |

### Routes Summary

| Group | Count | Prefix |
|-------|-------|--------|
| Payment Categories CRUD | 8 | `admin/payment-categories` |
| Bills Management | 5 | `admin/payments/bills` |
| Payment Recording | 12 | `admin/payments/records` |
| Reports | 3 | `admin/payments/reports` |
| Bank Reconciliation | 8 | `admin/payments/reconciliation` |
| Parent Payments | 3 | `parent/payments` |
| Principal Financial | 3 | `principal/financial` |

> 📡 Full API documentation: [Payment API](../../api/payments.md)

### Database Tables

| Table | Purpose |
|-------|---------|
| `payment_categories` | Jenis pembayaran (SPP, Uang Gedung, dll) |
| `payment_category_class_prices` | Harga per kelas (optional) |
| `bills` | Tagihan per siswa per periode |
| `payments` | Transaksi pembayaran |
| `bank_reconciliations` | Header rekonsiliasi bank |
| `bank_reconciliation_items` | Items dalam rekonsiliasi |
| `payment_reminder_logs` | Log reminder WhatsApp/Email |

> 📌 Lihat migration files untuk schema lengkap.

## Edge Cases & Handling

| Scenario | Expected Behavior | Implementation |
|----------|-------------------|----------------|
| Duplicate bill generation | Skip siswa yang sudah punya tagihan | checkDuplicates() di service |
| Payment exceeds remaining | Validation error dengan sisa tagihan | Custom validation di FormRequest |
| Bill already paid (lunas) | Cannot record payment | canBePaid() check |
| Cancel verified payment | Recalculate bill status | updatePaymentStatus() |
| Student inactive | Tidak di-generate tagihan | Student::active() scope |
| Transfer tanpa bukti | Status pending, admin verify manual | Verification flow |

## Security Considerations

| Concern | Mitigation | Implementation |
|---------|------------|----------------|
| Unauthorized access | Role-based middleware | `auth`, role check di controller |
| Parent access other children | Filter by guardian relationship | Parent controller validation |
| Payment manipulation | Audit trail | ActivityLog on record/verify/cancel |
| Receipt tampering | Server-generated PDF | DomPDF with signature |

## Related Documentation

- **API Documentation:** [Payment API](../../api/payments.md)
- **Test Plan:** [PAY Test Plan](../../testing/PAY-payment-test-plan.md)
- **User Journeys:** [Payment User Journeys](../../guides/payment-user-journeys.md)

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-01-21 | Initial implementation - Sprint 1-4 core features |
| 1.1 | 2026-01-21 | Added bank reconciliation - Sprint 6 |
| 1.2 | 2026-01-21 | Added principal financial reports |

---
*Last Updated: 2026-01-21*
