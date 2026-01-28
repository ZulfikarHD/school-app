# PSB Module - Project Overview

## Ringkasan Proyek

PSB (Penerimaan Siswa Baru) merupakan modul pendaftaran siswa baru secara online yang bertujuan untuk mendigitalisasi seluruh proses enrollment dari pendaftaran hingga menjadi siswa aktif.

## Tim Development

| Role | Fokus Area |
|------|------------|
| **Backend Developer** | Database, Models, Controllers, Services, API, Validations, Testing |
| **Frontend Developer** | Vue Pages, Components, UI/UX, Wayfinder Routes, Mobile Responsiveness |

---

## Struktur Epic & Sprint

| Epic | Nama | Sprint | Backend SP | Frontend SP | Total SP |
|------|------|--------|------------|-------------|----------|
| **Epic 1** | Foundation & Public Registration | Sprint 1 | 20 | 18 | 38 |
| **Epic 2** | Admin Verification System | Sprint 2 | 18 | 18 | 36 |
| **Epic 3** | Announcement & Re-registration | Sprint 3 | 21 | 22 | 43 |
| **Epic 4** | Dashboard & Enhancement | Sprint 4 | 23 | 24 | 47 |
| | **TOTAL** | | **82** | **82** | **164** |

**Total: 4 Epics, 4 Sprints**

---

## Story Points Summary

### Per Role
| Role | Total Story Points |
|------|-------------------|
| Backend Developer | 82 SP |
| Frontend Developer | 82 SP |

### Per Priority
| Priority | Description | Count |
|----------|-------------|-------|
| **P0** | Critical - Must have | ~60% tasks |
| **P1** | Important - Should have | ~30% tasks |
| **P2** | Enhancement - Nice to have | ~10% tasks |

---

## Flow Diagram

```
Epic 1 (Foundation) - Sprint 1
    ├── [BE] Database Migrations
    ├── [BE] Models & Relationships
    ├── [BE] PsbService
    ├── [BE] Public Controller & Routes
    ├── [FE] Landing Page
    ├── [FE] Multi-Step Registration Form
    ├── [FE] Success Page
    └── [FE] Status Tracking Page
            ↓
Epic 2 (Admin) - Sprint 2
    ├── [BE] Admin Controller & Routes
    ├── [BE] Form Requests
    ├── [BE] Verification Methods
    ├── [BE] Notification System
    ├── [FE] Registrations List
    ├── [FE] Registration Detail
    ├── [FE] Document Preview Component
    └── [FE] Admin Navigation Update
            ↓
Epic 3 (Completion) - Sprint 3
    ├── [BE] Announcement Controller
    ├── [BE] Parent Re-registration Controller
    ├── [BE] Announcement & Re-reg Methods
    ├── [BE] Principal Controller
    ├── [BE] Payment Verification
    ├── [FE] Announcement Page
    ├── [FE] Parent Re-registration Page
    ├── [FE] Parent Welcome Page
    ├── [FE] Principal Dashboard
    └── [FE] Navigation Updates
            ↓
Epic 4 (Enhancement) - Sprint 4
    ├── [BE] PSB Settings Controller
    ├── [BE] Export to Excel
    ├── [BE] Admin Dashboard
    ├── [BE] Comprehensive Testing
    ├── [BE] Wayfinder Generation
    ├── [FE] Settings Page
    ├── [FE] Admin Dashboard Page
    ├── [FE] Export UI
    ├── [FE] Complete Navigation
    ├── [FE] UI Polish & Mobile Testing
    └── [FE] Frontend Testing
```

---

## Priority Legend

- **P0** - Critical (Feature tidak bisa digunakan tanpa ini)
- **P1** - Important (Feature tidak lengkap tanpa ini)
- **P2** - Enhancement (Bisa di-ship kemudian)

---

## Parallel Work Strategy

Sprint 1-4 dirancang agar Backend dan Frontend bisa bekerja parallel:

### Sprint 1 Strategy
- **Backend** mulai dengan migrations & models
- **Frontend** bisa mulai dengan static mockup/layout
- Sync point: setelah routes ready, Frontend connect ke API

### Sprint 2-4 Strategy
- Backend focus pada controllers, services, testing
- Frontend focus pada pages, components, UX
- Daily sync untuk integration points

---

## File Dokumentasi

| File | Deskripsi |
|------|-----------|
| `PSB-Overview.md` | Dokumen ini - ringkasan project |
| `Epic-1-Foundation.md` | Database, Models, Public Registration |
| `Epic-2-Admin-Verification.md` | Admin Verification System |
| `Epic-3-Announcement-Reregistration.md` | Announcement & Parent Portal |
| `Epic-4-Dashboard-Enhancement.md` | Settings, Export, Dashboard & Polish |

---

## Quick Commands

```bash
# Generate Wayfinder routes
php artisan wayfinder:generate

# Run tests
php artisan test

# Lint PHP
vendor/bin/pint --dirty

# Build frontend
yarn run build

# Lint frontend
yarn run lint
```
