# API Documentation: Payment System

## Overview

API untuk mengelola pembayaran sekolah, termasuk kategori pembayaran, tagihan (bills), pembayaran (payments), dan rekonsiliasi bank.

Base URL: `/admin/`, `/parent/`, `/principal/`

## Authentication

Semua endpoint memerlukan authentication via Laravel session (Inertia.js).

---

## Admin Payment Categories

### List Payment Categories

**Endpoint:** `GET /admin/payment-categories`
**Route Name:** `admin.payment-categories.index`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | Filter by nama/kode |
| tipe | string | No | Filter by tipe (bulanan/tahunan/insidental) |
| is_active | boolean | No | Filter by status aktif |

**Response:** Inertia page with categories data

---

### Create Payment Category

**Endpoint:** `POST /admin/payment-categories`
**Route Name:** `admin.payment-categories.store`

**Request Body:**

```json
{
  "nama": "SPP Bulanan",
  "kode": "SPP",
  "deskripsi": "Sumbangan Pembinaan Pendidikan",
  "tipe": "bulanan",
  "nominal_default": 300000,
  "is_active": true,
  "is_mandatory": true,
  "due_day": 10,
  "tahun_ajaran": "2025/2026"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| nama | string | Yes | Max 100 chars |
| kode | string | Yes | Max 20 chars, unique |
| deskripsi | string | No | Max 500 chars |
| tipe | enum | Yes | bulanan/tahunan/insidental |
| nominal_default | decimal | Yes | Min 0 |
| is_active | boolean | No | Default true |
| is_mandatory | boolean | No | Default true |
| due_day | integer | No | 1-28 |
| tahun_ajaran | string | No | Format: YYYY/YYYY |

---

### Toggle Category Status

**Endpoint:** `PATCH /admin/payment-categories/{id}/toggle-status`
**Route Name:** `admin.payment-categories.toggle-status`

---

## Admin Bills Management

### List Bills

**Endpoint:** `GET /admin/payments/bills`
**Route Name:** `admin.payments.bills.index`

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| search | string | Search by nomor tagihan/nama siswa |
| status | string | belum_bayar/sebagian/lunas/dibatalkan |
| category_id | integer | Filter by kategori |
| month | integer | Filter by bulan (1-12) |
| year | integer | Filter by tahun |

---

### Preview Bill Generation

**Endpoint:** `POST /admin/payments/bills/preview`
**Route Name:** `admin.payments.bills.preview`

**Request Body:**

```json
{
  "payment_category_id": 1,
  "bulan": 2,
  "tahun": 2026,
  "class_ids": [1, 2, 3]
}
```

**Response:**

```json
{
  "students": [...],
  "summary": {
    "total_students": 180,
    "total_nominal": 54000000,
    "formatted_total": "Rp 54.000.000",
    "duplicate_count": 0
  },
  "duplicates": [...]
}
```

---

### Generate Bills

**Endpoint:** `POST /admin/payments/bills`
**Route Name:** `admin.payments.bills.store`

**Request Body:**

```json
{
  "payment_category_id": 1,
  "bulan": 2,
  "tahun": 2026,
  "class_ids": [1, 2, 3],
  "skip_duplicates": true
}
```

---

## Admin Payment Recording

### List Payments

**Endpoint:** `GET /admin/payments/records`
**Route Name:** `admin.payments.records.index`

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| search | string | Nomor kwitansi/nama siswa |
| status | string | pending/verified/cancelled |
| metode | string | tunai/transfer/qris |
| start_date | date | Filter from date |
| end_date | date | Filter to date |
| date | date | Single date filter |

---

### Record Payment

**Endpoint:** `POST /admin/payments/records`
**Route Name:** `admin.payments.records.store`

**Request Body:**

```json
{
  "bill_id": 1,
  "nominal": 300000,
  "metode_pembayaran": "tunai",
  "tanggal_bayar": "2026-01-21",
  "keterangan": "Pembayaran tunai"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| bill_id | integer | Yes | exists:bills,id |
| nominal | decimal | Yes | min:1, max:sisa_tagihan |
| metode_pembayaran | enum | Yes | tunai/transfer/qris |
| tanggal_bayar | date | Yes | before_or_equal:today |
| keterangan | string | No | max:500 |

---

### Verify Payment

**Endpoint:** `POST /admin/payments/records/{payment}/verify`
**Route Name:** `admin.payments.records.verify`

---

### Cancel Payment

**Endpoint:** `POST /admin/payments/records/{payment}/cancel`
**Route Name:** `admin.payments.records.cancel`

**Request Body:**

```json
{
  "reason": "Kesalahan input data"
}
```

---

### Download Receipt

**Endpoint:** `GET /admin/payments/records/{payment}/receipt`
**Route Name:** `admin.payments.records.receipt`

**Response:** PDF file download

---

### Verification Queue

**Endpoint:** `GET /admin/payments/records/verification`
**Route Name:** `admin.payments.records.verification`

---

## Admin Reports

### Financial Reports

**Endpoint:** `GET /admin/payments/reports`
**Route Name:** `admin.payments.reports.index`

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| month | integer | Current | Filter bulan |
| year | integer | Current | Filter tahun |
| category_id | integer | All | Filter kategori |

**Response Data:**

```json
{
  "summary": {
    "total_income": 45000000,
    "formatted_income": "Rp 45.000.000",
    "transaction_count": 150,
    "total_piutang": 9000000,
    "collectibility": 83.3,
    "by_method": {...}
  },
  "trend": [...],
  "categoryBreakdown": [...],
  "overdueSummary": {...}
}
```

---

### Export Reports

**Endpoint:** `GET /admin/payments/reports/export`
**Route Name:** `admin.payments.reports.export`

**Response:** CSV file download

---

### Delinquent Students

**Endpoint:** `GET /admin/payments/reports/delinquents`
**Route Name:** `admin.payments.reports.delinquents`

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| sort | string | total_tunggakan | Sort field |
| dir | string | desc | Sort direction |

---

## Admin Bank Reconciliation

### List Reconciliations

**Endpoint:** `GET /admin/payments/reconciliation`
**Route Name:** `admin.payments.reconciliation.index`

---

### Upload Bank Statement

**Endpoint:** `POST /admin/payments/reconciliation/upload`
**Route Name:** `admin.payments.reconciliation.upload`

**Request:** Multipart form with Excel/CSV file

---

### Auto Match

**Endpoint:** `POST /admin/payments/reconciliation/{id}/auto-match`
**Route Name:** `admin.payments.reconciliation.auto-match`

---

### Manual Match

**Endpoint:** `POST /admin/payments/reconciliation/{id}/match`
**Route Name:** `admin.payments.reconciliation.match.store`

---

### Verify Reconciliation

**Endpoint:** `POST /admin/payments/reconciliation/{id}/verify`
**Route Name:** `admin.payments.reconciliation.verify`

---

## Parent Payments

### View Bills & Payments

**Endpoint:** `GET /parent/payments`
**Route Name:** `parent.payments.index`

Shows active bills and payment summary for parent's children.

---

### Payment History

**Endpoint:** `GET /parent/payments/history`
**Route Name:** `parent.payments.history`

---

### Download Receipt

**Endpoint:** `GET /parent/payments/{payment}/receipt`
**Route Name:** `parent.payments.receipt`

---

## Principal Financial

### Financial Reports

**Endpoint:** `GET /principal/financial/reports`
**Route Name:** `principal.financial.reports`

Same data as admin reports (read-only).

---

### Delinquent Students

**Endpoint:** `GET /principal/financial/delinquents`
**Route Name:** `principal.financial.delinquents`

---

### Export Reports

**Endpoint:** `GET /principal/financial/reports/export`
**Route Name:** `principal.financial.reports.export`

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| BILL_NOT_PAYABLE | 422 | Tagihan sudah lunas/dibatalkan |
| NOMINAL_EXCEEDS_REMAINING | 422 | Nominal melebihi sisa tagihan |
| PAYMENT_NOT_VERIFIABLE | 422 | Pembayaran tidak dapat diverifikasi |
| PAYMENT_NOT_CANCELLABLE | 422 | Pembayaran tidak dapat dibatalkan |
| CATEGORY_INACTIVE | 422 | Kategori tidak aktif |
| UNAUTHORIZED | 403 | Tidak memiliki akses |

---

*Last Updated: 2026-01-21*
