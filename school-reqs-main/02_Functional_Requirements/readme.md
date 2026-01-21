# Functional Requirements

Dokumen ini berisi persyaratan fungsional lengkap untuk Sistem Manajemen Sekolah SD, diorganisir per modul.

## 📁 Struktur Dokumen

Setiap modul memiliki file terpisah dengan struktur:
- Overview & Tujuan
- User Stories (per role)
- Functional Requirements (detail)
- Business Rules
- Validation Rules
- UI/UX Requirements
- Integration Points
- Acceptance Criteria

## 🗂️ Daftar Modul

### Core Modules (Phase 1 - MVP)

1. **[01_Authentication_Authorization.md](./01_Authentication_Authorization.md)**
   - Login & logout
   - Role-based access control
   - Password management
   - Session management

2. **[02_Student_Management.md](./02_Student_Management.md)**
   - Pendaftaran siswa
   - Profil siswa
   - Mutasi & status siswa
   - Data orang tua/wali

3. **[03_Attendance_System.md](./03_Attendance_System.md)**
   - Absensi harian pagi
   - Absensi per mata pelajaran
   - Manajemen izin/sakit
   - Presensi guru
   - Laporan kehadiran

4. **[04_Payment_System.md](./04_Payment_System.md)**
   - Manajemen SPP
   - Multi-kategori pembayaran
   - Pencatatan transaksi
   - Reminder otomatis
   - Laporan keuangan

5. **[05_Grades_Report_Cards.md](./05_Grades_Report_Cards.md)**
   - Input nilai (UH, UTS, UAS, sikap, praktik)
   - Bobot & kalkulasi nilai
   - Generate rapor K13
   - Rekap nilai per kelas/siswa

6. **[06_New_Student_Registration.md](./06_New_Student_Registration.md)**
   - Form pendaftaran online
   - Upload dokumen
   - Workflow verifikasi
   - Pembayaran formulir
   - Tracking status

7. **[07_Teacher_Management.md](./07_Teacher_Management.md)**
   - Data kepegawaian
   - Jadwal mengajar
   - Perhitungan honor
   - Evaluasi kinerja

8. **[08_Dashboard_Reports.md](./08_Dashboard_Reports.md)**
   - Dashboard per role
   - Laporan operasional
   - Analytics & grafik
   - Export data

9. **[09_School_Website.md](./09_School_Website.md)**
   - Halaman publik
   - Portal PSB
   - Pengumuman
   - Informasi sekolah

10. **[10_Notification_System.md](./10_Notification_System.md)**
    - WhatsApp integration
    - Email notification
    - In-app notification
    - Template management

### Supporting Modules

11. **[11_Settings_Configuration.md](./11_Settings_Configuration.md)**
    - Pengaturan sekolah
    - Tahun ajaran
    - Kelas & mata pelajaran
    - User management

---

## 📊 Priority Matrix

| Priority | Modules | Timeline |
|----------|---------|----------|
| **P0 (Critical)** | Authentication, Student Management, Attendance, Payment | Sprint 1-2 (Week 1-10) |
| **P1 (High)** | Grades & Report Cards, Dashboard | Sprint 3 (Week 11-16) |
| **P2 (Medium)** | PSB Online, Teacher Management, Website | Sprint 4 (Week 17-20) |
| **P3 (Low)** | Notification System, Advanced Settings | Sprint 5 (Week 21-23) |

---

## 🎯 Key Principles

1. **Mobile-First:** Semua modul harus responsive dan touch-friendly
2. **User-Friendly:** Interface sederhana untuk user dengan tech literacy rendah
3. **Offline-Ready:** Minimal functionality saat internet tidak stabil
4. **Bahasa Indonesia:** Semua UI, validation, error messages
5. **Data Integrity:** Validasi ketat, audit trail, backup otomatis

---

## 📝 Notation & Conventions

### User Story Format
```
Sebagai [role],
Saya ingin [action/feature],
Sehingga [benefit/outcome]
```

### Requirement ID Format
```
[MODULE_CODE]-[CATEGORY]-[NUMBER]

Contoh:
- AUTH-FR-001 (Authentication - Functional Requirement - 001)
- ATT-BR-005 (Attendance - Business Rule - 005)
- PAY-VR-010 (Payment - Validation Rule - 010)
```

### Priority Levels
- **Must Have:** Critical untuk MVP
- **Should Have:** Penting tapi bisa ditunda
- **Could Have:** Nice to have
- **Won't Have (Phase 1):** Untuk fase berikutnya

---

**Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft - Ready for Review

