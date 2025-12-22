# Scrum Workflow - Sistem Manajemen Sekolah SD
## Project: SD Management System

**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Methodology:** Scrum/Agile  
**Sprint Duration:** 2-3 minggu  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Table of Contents

1. [Product Backlog Overview](#product-backlog-overview)
2. [Epic Organization](#epic-organization)
3. [Sprint Planning](#sprint-planning)
4. [Release Plan](#release-plan)
5. [Definition of Done](#definition-of-done)
6. [Dependencies Matrix](#dependencies-matrix)
7. [Risk Management](#risk-management)

---

## 📊 Product Backlog Overview

### Total Scope
- **Total User Stories:** 122 stories
- **Total Estimation:** 273 points
- **Phase 1 (MVP):** ~215 points (88 stories)
- **Phase 2 (Enhancement):** ~58 points (34 stories)
- **Estimated Timeline:** 6-8 bulan untuk MVP (1 developer full-time)

### Module Breakdown

| Module | Stories | Points (Phase 1) | Priority |
|--------|---------|------------------|----------|
| Authentication & Authorization | 10 | 21 | Critical |
| Student Management | 12 | 26 | Critical |
| Attendance System | 12 | 30 | Critical |
| Payment System | 12 | 26 | Critical |
| Grades & Report Cards | 12 | 25 | Critical |
| New Student Registration (PSB) | 10 | 22 | High |
| Teacher Management | 10 | 23 | High |
| Dashboard & Reports | 12 | 33 | High |
| School Website | 12 | 22 | Medium |
| Notification System | 10 | 20 | Medium |
| Settings & Configuration | 12 | 23 | Medium |

---

## 🎯 Epic Organization

Epics diorganisir berdasarkan **value stream** dan **user journey**, bukan hanya module teknis.

### **Epic 1: Foundation & Access Control**
**Goal:** User dapat login, logout, dan akses sistem sesuai role mereka dengan aman.

**User Stories:**
- US-AUTH-001: Login ke Sistem (3 pts)
- US-AUTH-002: Logout (2 pts)
- US-AUTH-003: Lupa Password (3 pts)
- US-AUTH-004: Ganti Password (2 pts)
- US-AUTH-005: RBAC (5 pts)
- US-AUTH-006: Manajemen User Account (3 pts)
- US-AUTH-007: First Login Force Change (2 pts)
- US-AUTH-009: Audit Log (3 pts)
- US-SET-001: Master Data Kelas (2 pts)
- US-SET-002: Master Data Mapel (2 pts)
- US-SET-003: Tahun Ajaran & Semester (3 pts)
- US-SET-004: Pengaturan Umum Sekolah (3 pts)
- US-SET-005: Jenis Pembayaran (2 pts)
- US-SET-008: Role & Permission (5 pts)

**Total:** 40 points  
**Sprint:** Sprint 1 & 2  
**Dependencies:** None (foundational)  
**Priority:** Critical - Must complete first

---

### **Epic 2: Student Information System**
**Goal:** TU dapat mengelola data siswa dan orang tua dengan lengkap, mudah, dan akurat.

**User Stories:**
- US-STD-001: Tambah Data Siswa (3 pts)
- US-STD-002: Edit Data Siswa (2 pts)
- US-STD-003: Hapus/Nonaktifkan Siswa (2 pts)
- US-STD-004: Lihat Detail Profil (3 pts)
- US-STD-005: Data Orang Tua (3 pts)
- US-STD-006: Upload Foto Siswa (2 pts)
- US-STD-007: Filter & Search (3 pts)
- US-STD-008: Export Excel (2 pts)
- US-STD-009: Import Excel (3 pts)
- US-STD-010: Pindah Kelas/Naik Kelas (3 pts)

**Total:** 26 points  
**Sprint:** Sprint 2 & 3  
**Dependencies:** Epic 1 (Auth, Master Data)  
**Priority:** Critical

---

### **Epic 3: Daily Attendance Management**
**Goal:** Guru dapat input absensi dengan mudah, orang tua dapat mengajukan izin, dan sistem tracking kehadiran siswa & guru secara akurat.

**User Stories:**
- US-ATT-001: Absensi Harian Siswa (3 pts)
- US-ATT-002: Absensi Per Pelajaran (3 pts)
- US-ATT-003: Ajukan Izin (Orang Tua) (3 pts)
- US-ATT-004: Verifikasi Izin (2 pts)
- US-ATT-005: Rekap Absensi Siswa (3 pts)
- US-ATT-006: Portal Orang Tua (2 pts)
- US-ATT-007: Presensi Guru (3 pts)
- US-ATT-008: Approval Izin Guru (3 pts)
- US-ATT-009: Rekap Presensi Guru (3 pts)
- US-ATT-010: Notifikasi Otomatis (3 pts)
- US-ATT-011: Dashboard Real-Time (3 pts)

**Total:** 31 points  
**Sprint:** Sprint 3, 4, & 5  
**Dependencies:** Epic 2 (Student Data)  
**Priority:** Critical

---

### **Epic 4: Financial Management**
**Goal:** TU dapat mencatat pembayaran, orang tua dapat melihat tagihan dan membayar, serta menghasilkan laporan keuangan yang akurat.

**User Stories:**
- US-PAY-001: Catat Pembayaran Manual (3 pts)
- US-PAY-002: Riwayat Pembayaran (3 pts)
- US-PAY-003: Generate Tagihan (3 pts)
- US-PAY-004: Set Nominal SPP (2 pts)
- US-PAY-005: Reminder Pembayaran (3 pts)
- US-PAY-006: Portal Orang Tua (3 pts)
- US-PAY-007: Verifikasi Transfer (2 pts)
- US-PAY-008: Laporan Keuangan (3 pts)
- US-PAY-009: Pembayaran Non-SPP (2 pts)
- US-PAY-011: Cetak Kwitansi (2 pts)
- US-PAY-012: Dashboard Pembayaran (3 pts)

**Total:** 29 points  
**Sprint:** Sprint 4, 5, & 6  
**Dependencies:** Epic 2 (Student Data), Epic 1 (Settings)  
**Priority:** Critical

---

### **Epic 5: Academic Performance Tracking**
**Goal:** Guru dapat input nilai, sistem generate rapor digital, dan orang tua dapat melihat perkembangan akademik anak secara online.

**User Stories:**
- US-GRD-001: Input Nilai (3 pts)
- US-GRD-002: Set Bobot Nilai (2 pts)
- US-GRD-003: Rekap Nilai (3 pts)
- US-GRD-004: Nilai Sikap (2 pts)
- US-GRD-005: Generate Rapor PDF (5 pts)
- US-GRD-006: Portal Orang Tua (3 pts)
- US-GRD-007: Approval Rapor (3 pts)
- US-GRD-008: Catatan Wali Kelas (2 pts)
- US-GRD-009: Export Excel (2 pts)
- US-GRD-011: Dashboard Akademik (3 pts)

**Total:** 28 points  
**Sprint:** Sprint 6, 7, & 8  
**Dependencies:** Epic 2 (Student Data), Epic 1 (Master Data Mapel)  
**Priority:** Critical

---

### **Epic 6: Student Recruitment (PSB)**
**Goal:** Orang tua dapat mendaftar online, TU dapat verifikasi dokumen, dan proses PSB berjalan efisien hingga daftar ulang.

**User Stories:**
- US-PSB-001: Formulir Pendaftaran (3 pts)
- US-PSB-002: Upload Dokumen (3 pts)
- US-PSB-003: Verifikasi Dokumen (3 pts)
- US-PSB-005: Pengumuman Hasil (2 pts)
- US-PSB-006: Pembayaran Formulir (3 pts)
- US-PSB-007: Daftar Ulang (3 pts)
- US-PSB-008: Dashboard PSB (3 pts)
- US-PSB-009: Konfigurasi Periode (2 pts)
- US-PSB-010: Export Data (2 pts)

**Total:** 24 points  
**Sprint:** Sprint 7 & 8  
**Dependencies:** Epic 4 (Payment System), Epic 2 (Student Data)  
**Priority:** High

---

### **Epic 7: Teacher Management & HR**
**Goal:** TU dapat mengelola data guru, jadwal, honor, dan kepala sekolah dapat evaluasi performa guru.

**User Stories:**
- US-TCH-001: CRUD Data Guru (3 pts)
- US-TCH-002: Profil Guru (3 pts)
- US-TCH-003: Jadwal Mengajar (3 pts)
- US-TCH-004: Rekap Jam & Honor (3 pts)
- US-TCH-005: Set Honor (2 pts)
- US-TCH-006: Evaluasi Guru (3 pts)
- US-TCH-008: Nonaktifkan Guru (2 pts)
- US-TCH-009: Export Data (2 pts)
- US-TCH-010: Dashboard Guru (3 pts)

**Total:** 24 points  
**Sprint:** Sprint 8 & 9  
**Dependencies:** Epic 3 (Attendance), Epic 1 (Auth)  
**Priority:** High

---

### **Epic 8: Executive Dashboard & Reporting**
**Goal:** Kepala sekolah, TU, dan Guru dapat monitoring operasional melalui dashboard dan generate laporan untuk decision making.

**User Stories:**
- US-DASH-001: Dashboard Kepala Sekolah (5 pts)
- US-DASH-002: Dashboard TU (3 pts)
- US-DASH-003: Dashboard Guru (3 pts)
- US-DASH-004: Dashboard Orang Tua (3 pts)
- US-DASH-005: Laporan Keuangan (3 pts)
- US-DASH-006: Laporan Akademik (3 pts)
- US-DASH-007: Laporan Absensi (3 pts)
- US-DASH-008: Laporan PSB (2 pts)
- US-DASH-009: Notifikasi & Alert (3 pts)
- US-DASH-010: Export Multi-Format (2 pts)
- US-DASH-011: Custom Date Range (2 pts)
- US-DASH-012: Grafik & Visualisasi (3 pts)

**Total:** 35 points  
**Sprint:** Sprint 9, 10, & 11  
**Dependencies:** All Epics (aggregate data)  
**Priority:** High

---

### **Epic 9: Public Website & Communication**
**Goal:** Sekolah punya website profesional untuk publik, orang tua dapat akses info, dan admin dapat kelola konten dengan mudah.

**User Stories:**
- US-WEB-001: Homepage (3 pts)
- US-WEB-002: Profil Sekolah (2 pts)
- US-WEB-003: Pengumuman (3 pts)
- US-WEB-004: Galeri Foto/Video (3 pts)
- US-WEB-005: Kontak & Lokasi (2 pts)
- US-WEB-006: Halaman PSB (2 pts)
- US-WEB-007: CMS Admin (5 pts)
- US-WEB-008: Kurikulum & Ekskul (2 pts)
- US-WEB-010: SEO & Performance (3 pts)
- US-WEB-011: Portal Login Link (1 pt)
- US-WEB-012: Footer & Social Media (1 pt)

**Total:** 27 points  
**Sprint:** Sprint 10 & 11  
**Dependencies:** Epic 1 (Auth), Epic 6 (PSB)  
**Priority:** Medium

---

### **Epic 10: Notification & Communication System**
**Goal:** User menerima notifikasi penting via WhatsApp, Email, dan In-App untuk berbagai event sistem.

**User Stories:**
- US-NOTIF-001: Notifikasi Absensi (3 pts)
- US-NOTIF-002: Reminder Pembayaran (3 pts)
- US-NOTIF-003: Broadcast Pengumuman (3 pts)
- US-NOTIF-004: Approval/Rejection (3 pts)
- US-NOTIF-005: Rapor Dirilis (2 pts)
- US-NOTIF-006: Email Fallback (3 pts)
- US-NOTIF-007: In-App Notification (3 pts)
- US-NOTIF-009: Log & Tracking (2 pts)

**Total:** 22 points  
**Sprint:** Sprint 11 & 12  
**Dependencies:** Epic 3 (Attendance), Epic 4 (Payment), Epic 5 (Grades)  
**Priority:** Medium-High

---

### **Epic 11: System Configuration & Administration**
**Goal:** Admin dapat konfigurasi sistem, backup data, dan audit aktivitas user.

**User Stories:**
- US-SET-006: Jam Sekolah & Libur (3 pts)
- US-SET-009: Backup & Restore (3 pts)
- US-SET-010: Audit Log (3 pts)
- US-SET-011: Email & SMTP (2 pts)
- US-STD-011: Portal Orang Tua - Lihat Data Siswa (3 pts)

**Total:** 14 points  
**Sprint:** Sprint 12  
**Dependencies:** All Epics  
**Priority:** Medium

---

## 🏃 Sprint Planning

### Sprint Planning Strategy

**Velocity Assumption:** 15-20 points per sprint (2-3 minggu, 1 developer)  
**Total Sprints untuk MVP:** 12 sprints (~6-7 bulan)  
**Buffer:** 1-2 sprint untuk bug fixes, refinement, testing

---

### **Sprint 1: Foundation Setup (Weeks 1-2)**
**Goal:** Setup project, authentication dasar, dan master data minimum viable.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-AUTH-001 | Login ke Sistem | 3 | Must Have |
| US-AUTH-002 | Logout | 2 | Must Have |
| US-AUTH-005 | RBAC | 5 | Must Have |
| US-AUTH-006 | Manajemen User | 3 | Must Have |
| US-SET-001 | Master Data Kelas | 2 | Must Have |
| US-SET-002 | Master Data Mapel | 2 | Must Have |
| US-SET-004 | Pengaturan Umum | 3 | Must Have |

**Total:** 20 points

**Deliverables:**
- ✅ User dapat login/logout
- ✅ Role-based access berfungsi
- ✅ Master data kelas & mata pelajaran tersedia
- ✅ Project structure, database schema, CI/CD setup

---

### **Sprint 2: Core Data Management (Weeks 3-4)**
**Goal:** Student management CRUD lengkap, setup tahun ajaran.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-AUTH-003 | Lupa Password | 3 | Must Have |
| US-AUTH-004 | Ganti Password | 2 | Must Have |
| US-SET-003 | Tahun Ajaran | 3 | Must Have |
| US-SET-005 | Jenis Pembayaran | 2 | Must Have |
| US-STD-001 | Tambah Data Siswa | 3 | Must Have |
| US-STD-002 | Edit Data Siswa | 2 | Must Have |
| US-STD-003 | Nonaktifkan Siswa | 2 | Must Have |
| US-STD-004 | Lihat Profil Siswa | 3 | Must Have |

**Total:** 20 points

**Deliverables:**
- ✅ TU dapat kelola data siswa (CRUD)
- ✅ Data siswa tersimpan dengan lengkap
- ✅ User dapat reset password

---

### **Sprint 3: Student Data Enhancement & Attendance Start (Weeks 5-7)**
**Goal:** Lengkapi fitur student management, mulai attendance system.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-STD-005 | Data Orang Tua | 3 | Must Have |
| US-STD-006 | Upload Foto Siswa | 2 | Should Have |
| US-STD-007 | Filter & Search | 3 | Must Have |
| US-STD-008 | Export Excel | 2 | Should Have |
| US-ATT-001 | Absensi Harian Siswa | 3 | Must Have |
| US-ATT-003 | Ajukan Izin (Ortu) | 3 | Must Have |
| US-ATT-004 | Verifikasi Izin | 2 | Must Have |

**Total:** 18 points

**Deliverables:**
- ✅ Data orang tua lengkap dan terintegrasi
- ✅ Guru dapat input absensi harian
- ✅ Orang tua dapat ajukan izin

---

### **Sprint 4: Attendance & Payment Foundation (Weeks 8-10)**
**Goal:** Complete attendance features, start payment system.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-ATT-002 | Absensi Per Pelajaran | 3 | Must Have |
| US-ATT-005 | Rekap Absensi Siswa | 3 | Must Have |
| US-ATT-007 | Presensi Guru | 3 | Must Have |
| US-PAY-001 | Catat Pembayaran Manual | 3 | Must Have |
| US-PAY-002 | Riwayat Pembayaran | 3 | Must Have |
| US-PAY-004 | Set Nominal SPP | 2 | Must Have |

**Total:** 17 points

**Deliverables:**
- ✅ Sistem absensi lengkap (siswa & guru)
- ✅ TU dapat catat pembayaran
- ✅ Riwayat pembayaran tersimpan

---

### **Sprint 5: Payment System Complete (Weeks 11-13)**
**Goal:** Payment system fully functional dengan generate tagihan dan verifikasi.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-PAY-003 | Generate Tagihan | 3 | Must Have |
| US-PAY-006 | Portal Orang Tua (Pay) | 3 | Must Have |
| US-PAY-007 | Verifikasi Transfer | 2 | Must Have |
| US-PAY-009 | Pembayaran Non-SPP | 2 | Must Have |
| US-PAY-011 | Cetak Kwitansi | 2 | Must Have |
| US-ATT-008 | Approval Izin Guru | 3 | Should Have |
| US-ATT-009 | Rekap Presensi Guru | 3 | Must Have |

**Total:** 18 points

**Deliverables:**
- ✅ Auto-generate tagihan SPP bulanan
- ✅ Orang tua dapat lihat tagihan & upload bukti bayar
- ✅ TU dapat verifikasi dan cetak kwitansi

---

### **Sprint 6: Grades System Foundation (Weeks 14-16)**
**Goal:** Guru dapat input nilai, sistem hitung nilai akhir, mulai generate rapor.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-GRD-001 | Input Nilai | 3 | Must Have |
| US-GRD-002 | Set Bobot Nilai | 2 | Must Have |
| US-GRD-003 | Rekap Nilai | 3 | Must Have |
| US-GRD-004 | Nilai Sikap | 2 | Must Have |
| US-GRD-005 | Generate Rapor PDF | 5 | Must Have |
| US-GRD-008 | Catatan Wali Kelas | 2 | Should Have |

**Total:** 17 points

**Deliverables:**
- ✅ Guru dapat input nilai per komponen
- ✅ Sistem generate rapor PDF
- ✅ Wali kelas dapat tambah catatan

---

### **Sprint 7: Grades Portal & PSB Start (Weeks 17-19)**
**Goal:** Orang tua dapat lihat rapor online, mulai PSB system.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-GRD-006 | Portal Orang Tua (Rapor) | 3 | Must Have |
| US-GRD-007 | Approval Rapor | 3 | Should Have |
| US-GRD-009 | Export Excel | 2 | Should Have |
| US-PSB-001 | Formulir Pendaftaran | 3 | Must Have |
| US-PSB-002 | Upload Dokumen | 3 | Must Have |
| US-PSB-003 | Verifikasi Dokumen | 3 | Must Have |

**Total:** 17 points

**Deliverables:**
- ✅ Orang tua dapat lihat & download rapor
- ✅ Kepala sekolah dapat approve rapor
- ✅ Calon siswa dapat daftar online

---

### **Sprint 8: PSB Complete & Teacher Management (Weeks 20-22)**
**Goal:** PSB workflow lengkap, mulai teacher management.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-PSB-005 | Pengumuman Hasil | 2 | Must Have |
| US-PSB-007 | Daftar Ulang | 3 | Must Have |
| US-PSB-008 | Dashboard PSB | 3 | Should Have |
| US-PSB-009 | Konfigurasi Periode | 2 | Must Have |
| US-TCH-001 | CRUD Data Guru | 3 | Must Have |
| US-TCH-002 | Profil Guru | 3 | Must Have |
| US-TCH-005 | Set Honor | 2 | Must Have |

**Total:** 18 points

**Deliverables:**
- ✅ PSB workflow end-to-end berfungsi
- ✅ TU dapat kelola data guru
- ✅ Setting honor guru

---

### **Sprint 9: Teacher Management & Dashboard Start (Weeks 23-25)**
**Goal:** Complete teacher features, mulai dashboard untuk semua role.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-TCH-003 | Jadwal Mengajar | 3 | Should Have |
| US-TCH-004 | Rekap Jam & Honor | 3 | Must Have |
| US-TCH-006 | Evaluasi Guru | 3 | Should Have |
| US-DASH-001 | Dashboard Kepala Sekolah | 5 | Must Have |
| US-DASH-002 | Dashboard TU | 3 | Must Have |

**Total:** 17 points

**Deliverables:**
- ✅ Sistem hitung honor guru otomatis
- ✅ Dashboard untuk Kepala Sekolah & TU

---

### **Sprint 10: Dashboard & Reports (Weeks 26-28)**
**Goal:** Dashboard lengkap semua role, laporan-laporan critical.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-DASH-003 | Dashboard Guru | 3 | Must Have |
| US-DASH-004 | Dashboard Orang Tua | 3 | Must Have |
| US-DASH-005 | Laporan Keuangan | 3 | Must Have |
| US-DASH-007 | Laporan Absensi | 3 | Must Have |
| US-DASH-009 | Notifikasi & Alert | 3 | Should Have |
| US-DASH-012 | Grafik & Visualisasi | 3 | Should Have |

**Total:** 18 points

**Deliverables:**
- ✅ Semua role punya dashboard masing-masing
- ✅ Laporan keuangan & absensi bisa di-generate

---

### **Sprint 11: Website & Notification (Weeks 29-31)**
**Goal:** Public website live, notification system aktif.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-WEB-001 | Homepage | 3 | Must Have |
| US-WEB-002 | Profil Sekolah | 2 | Must Have |
| US-WEB-003 | Pengumuman | 3 | Must Have |
| US-WEB-007 | CMS Admin | 5 | Must Have |
| US-NOTIF-004 | Approval Notifikasi | 3 | Must Have |
| US-NOTIF-007 | In-App Notification | 3 | Should Have |

**Total:** 19 points

**Deliverables:**
- ✅ Website sekolah live dengan CMS
- ✅ In-app notification berfungsi

---

### **Sprint 12: Final Polish & Configuration (Weeks 32-34)**
**Goal:** Notification complete, system config, testing & polish.

| Story ID | Story | Points | Priority |
|----------|-------|--------|----------|
| US-NOTIF-001 | Notifikasi Absensi | 3 | Should Have |
| US-NOTIF-002 | Reminder Pembayaran | 3 | Should Have |
| US-NOTIF-006 | Email Fallback | 3 | Should Have |
| US-SET-006 | Jam Sekolah & Libur | 3 | Should Have |
| US-SET-009 | Backup & Restore | 3 | Should Have |
| US-SET-011 | Email SMTP | 2 | Should Have |

**Total:** 17 points

**Deliverables:**
- ✅ WhatsApp/Email notification aktif
- ✅ System config lengkap
- ✅ Backup mechanism ready
- ✅ **MVP COMPLETE & READY FOR UAT**

---

## 📅 Release Plan

### Release 1.0 - MVP (End of Sprint 12)
**Target:** Juli 2025 (asumsi start Januari 2025)  
**Scope:** Semua Must Have & Should Have stories dari Phase 1

**Key Features:**
- ✅ Authentication & User Management
- ✅ Student Management (CRUD, Portal Orang Tua)
- ✅ Attendance System (Siswa & Guru)
- ✅ Payment System (Manual recording, Verification)
- ✅ Grades & Report Cards (Digital Rapor)
- ✅ PSB (Online Registration)
- ✅ Teacher Management (Data, Honor)
- ✅ Dashboard & Reports (Semua role)
- ✅ School Website (CMS)
- ✅ Notification System (In-App, WhatsApp/Email)
- ✅ System Configuration & Backup

**Acceptance Criteria untuk Go-Live:**
1. Semua Must Have stories DONE
2. UAT (User Acceptance Testing) passed
3. Performance test passed (< 2 detik load time)
4. Security audit passed
5. Data migration dari sistem lama (jika ada) completed
6. Training untuk TU, Guru, Kepala Sekolah completed
7. Documentation (User Manual, Technical Doc) ready

---

### Release 1.1 - Enhancement (Phase 2)
**Target:** 2-3 bulan setelah MVP (Oktober 2025)  
**Scope:** Could Have stories & feedback dari MVP usage

**Planned Features:**
- 🔄 Payment Gateway Integration (Midtrans/Xendit)
- 🔄 Offline Mode untuk Absensi (PWA)
- 🔄 Multi-Factor Authentication (MFA)
- 🔄 Remedial & Pengayaan Tracking
- 🔄 Survei Kepuasan Guru
- 🔄 Preferensi Notifikasi User
- 🔄 Advanced Analytics & AI Insights
- 🔄 Mobile App (iOS/Android) - optional

---

## ✅ Definition of Done (DoD)

Setiap user story dianggap DONE jika:

### Development
- [ ] Code written dan memenuhi acceptance criteria
- [ ] Code review passed (self-review minimal)
- [ ] Unit tests written dan passed (coverage > 70%)
- [ ] Integration tests passed
- [ ] Linter passed (yarn run lint)
- [ ] No console errors di browser
- [ ] Responsive design (mobile, tablet, desktop) - untuk frontend

### Testing
- [ ] Functional testing passed (manual QA)
- [ ] Edge cases tested
- [ ] Browser compatibility tested (Chrome, Firefox, Safari, Edge)
- [ ] Performance acceptable (< 2 detik load time)

### Documentation
- [ ] Code comments untuk logic kompleks
- [ ] API documentation updated (jika ada perubahan)
- [ ] User guide updated (jika perlu)

### Deployment
- [ ] Deployed ke staging environment
- [ ] Smoke test passed di staging
- [ ] Ready for demo/review

---

## 🔗 Dependencies Matrix

Tabel dependencies antar epic untuk sprint planning:

| Epic | Depends On | Blocker Level |
|------|------------|---------------|
| Epic 1: Foundation | None | - |
| Epic 2: Student Info | Epic 1 | High |
| Epic 3: Attendance | Epic 2, Epic 1 | High |
| Epic 4: Payment | Epic 2, Epic 1 | High |
| Epic 5: Grades | Epic 2, Epic 1 | High |
| Epic 6: PSB | Epic 4, Epic 2 | Medium |
| Epic 7: Teacher Mgmt | Epic 3, Epic 1 | Medium |
| Epic 8: Dashboard | All Epics | Low (can start partially) |
| Epic 9: Website | Epic 1, Epic 6 | Low |
| Epic 10: Notification | Epic 3, Epic 4, Epic 5 | Low |
| Epic 11: Config | All Epics | Low |

**Critical Path:** Epic 1 → Epic 2 → Epic 3/4/5 (parallel) → Epic 8

---

## 🎯 Sprint Ceremonies

### Daily Standup (15 menit)
**Kapan:** Setiap hari kerja, pagi jam 09:00 WIB  
**Format (Solo Developer):**
- Kemarin saya mengerjakan: [Story ID & Progress]
- Hari ini saya akan: [Story ID & Plan]
- Blocker: [Jika ada]
- Notes untuk stakeholder: [Jika ada yang perlu dikomunikasikan]

**Tools:** Update progress di Notion/Trello/Jira atau simple markdown file

---

### Sprint Planning (2-3 jam)
**Kapan:** Hari pertama sprint  
**Agenda:**
1. Review product backlog
2. Pilih stories untuk sprint (based on velocity & priority)
3. Break down stories menjadi tasks
4. Estimate effort (jika belum)
5. Commit sprint goal

**Output:**
- Sprint backlog (list stories yang akan dikerjakan)
- Sprint goal (1-2 kalimat)
- Task breakdown

---

### Sprint Review/Demo (1 jam)
**Kapan:** Hari terakhir sprint  
**Agenda:**
1. Demo working software ke stakeholder (Kepala Sekolah, TU)
2. Collect feedback
3. Update product backlog berdasarkan feedback

**Tips:** Record demo video jika stakeholder tidak bisa hadir live.

---

### Sprint Retrospective (1 jam)
**Kapan:** Setelah Sprint Review  
**Agenda (Solo):**
1. What went well?
2. What didn't go well?
3. What to improve next sprint?
4. Action items

**Output:** 2-3 action items untuk improvement di sprint berikutnya

---

## ⚠️ Risk Management

### Risk Register

| Risk | Probability | Impact | Mitigation Strategy |
|------|-------------|--------|---------------------|
| **Scope Creep** | High | High | - Strict DoD, no new features mid-sprint<br>- Defer to Phase 2 jika tidak critical |
| **Technical Complexity** | Medium | High | - Spike tasks untuk research<br>- Ask for help (forum, AI, mentor) |
| **Stakeholder Availability** | Medium | Medium | - Async communication (WA/email)<br>- Record demo videos |
| **Infrastructure Issues** | Low | High | - Backup & monitoring from day 1<br>- Cloud hosting (reliable provider) |
| **Data Migration** | High | High | - Start early (Sprint 10-11)<br>- Script untuk import data lama<br>- Extensive testing |
| **WhatsApp API Cost** | Low | Medium | - Phase 1: manual broadcast<br>- Phase 2: auto API when budget ready |
| **Burnout** | Medium | High | - Realistic velocity (15-20 points)<br>- Take breaks, jangan lembur berlebihan<br>- Buffer sprint untuk polish |

---

## 📈 Progress Tracking

### Burndown Chart
Track points completed vs planned setiap sprint.

**Tools:** Simple spreadsheet atau tools seperti:
- Trello (dengan Power-Ups)
- Jira (jika ada budget)
- Notion (dengan formula)
- GitHub Projects

---

### Velocity Chart
Track average points completed per sprint untuk improve estimation.

**Target Velocity:** 15-20 points/sprint (2-3 minggu)

| Sprint | Planned | Completed | Variance |
|--------|---------|-----------|----------|
| Sprint 1 | 20 | TBD | - |
| Sprint 2 | 20 | TBD | - |
| ... | ... | ... | ... |

---

## 📞 Stakeholder Communication

### Weekly Progress Report (Sent every Friday)

**To:** Kepala Sekolah, TU  
**Format:**

```
Subject: [SD XYZ] Weekly Progress Report - Sprint X Week Y

Halo Pak/Bu,

=== Progress Minggu Ini ===
✅ Selesai:
- [US-XXX]: [Story Name] - [Brief description]
- [US-YYY]: [Story Name]

🚧 Sedang Dikerjakan:
- [US-ZZZ]: [Story Name] - [Progress %]

📅 Rencana Minggu Depan:
- [US-AAA]: [Story Name]

⚠️ Blocker/Isu (Jika Ada):
- [Deskripsi issue] - [Action plan]

📊 Progress Keseluruhan:
- Sprint X: [Completed Points] / [Total Points] ([%])
- On track untuk release MVP [Target Date]

Demo tersedia di: [Link staging/video]

Regards,
Zulfikar
```

---

### Sprint Review Invitation

**To:** Kepala Sekolah, TU  
**Subject:** Undangan Sprint Review - Demo Fitur Baru

```
Halo Pak/Bu,

Sprint [X] sudah selesai. Saya akan demo fitur-fitur yang sudah jadi:

📅 Tanggal: [Date]
⏰ Waktu: [Time] WIB
📍 Tempat: [Location/Zoom Link]

Fitur yang akan di-demo:
1. [Feature 1]
2. [Feature 2]
3. [Feature 3]

Durasi: ~30 menit (demo + Q&A)

Mohon konfirmasi kehadiran atau alternatif jadwal jika berhalangan.

Jika tidak bisa hadir, saya akan kirimkan video recording.

Terima kasih!

Regards,
Zulfikar
```

---

## 🛠️ Tools & Tech Stack

### Development
- **Frontend:** React + TypeScript (atau Next.js)
- **Backend:** Node.js + Express/NestJS (atau Laravel PHP)
- **Database:** PostgreSQL/MySQL
- **Package Manager:** Yarn

### Project Management
- **Backlog & Sprint:** Notion, Trello, atau GitHub Projects
- **Documentation:** Markdown files di repo
- **Communication:** WhatsApp, Email

### CI/CD & Hosting
- **Version Control:** Git + GitHub/GitLab
- **CI/CD:** GitHub Actions atau GitLab CI
- **Hosting:** VPS (Niagahoster, Dewaweb) atau Cloud (AWS, GCP, DigitalOcean)
- **Database:** Managed DB atau self-hosted

### Monitoring
- **Error Tracking:** Sentry (free tier)
- **Analytics:** Google Analytics (website)
- **Uptime Monitor:** UptimeRobot (free)

---

## 📚 Additional Resources

### References
- [Scrum Guide](https://scrumguides.org/)
- [Agile Manifesto](https://agilemanifesto.org/)
- [Product Backlog Refinement Best Practices](https://www.scrum.org/resources/blog/product-backlog-refinement-explained)

### Internal Docs
- [Functional Requirements](../02_Functional_Requirements/)
- [User Stories with AC](../04_User_Stories_With_Acceptance_Criteria/)
- [System Architecture](../05_System_Architecture_Recommendation/)
- [Technical Considerations](../07_Technical_Considerations/)

---

## 🎉 Success Metrics

### Sprint Level
- [ ] Sprint goal achieved
- [ ] All planned stories DONE (DoD met)
- [ ] Velocity maintained (15-20 points)
- [ ] No critical bugs in production

### Release Level (MVP)
- [ ] 100% Must Have stories completed
- [ ] 80%+ Should Have stories completed
- [ ] UAT passed dengan min 90% stakeholder satisfaction
- [ ] System dapat handle 200+ concurrent users
- [ ] < 5 critical bugs di production (1 bulan pertama)

### Business Level (3 bulan post-launch)
- [ ] 80%+ adoption rate (TU, Guru actively using)
- [ ] 50%+ orang tua actively using portal
- [ ] Time saving: 40%+ reduction dalam administrative tasks
- [ ] Payment collection improved: 30%+ faster tunggakan resolution

---

**Last Updated:** 13 Desember 2025  
**Maintained By:** Zulfikar Hidayatullah  
**Review Cycle:** Every 2 sprints

---

## Changelog

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | 13 Des 2025 | Initial Scrum Workflow created | Zulfikar |

