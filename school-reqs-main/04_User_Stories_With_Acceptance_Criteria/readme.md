# User Stories with Acceptance Criteria
## Sistem Manajemen Sekolah SD

Dokumen ini berisi user stories lengkap dengan acceptance criteria untuk setiap fitur dalam sistem.

---

## 📋 Struktur Dokumen

User stories diorganisir berdasarkan module/fitur sistem:

1. **[01_Authentication_Authorization.md](01_Authentication_Authorization.md)** - Login, logout, password reset, role management
2. **[02_Student_Management.md](02_Student_Management.md)** - CRUD siswa, profil, data keluarga
3. **[03_Attendance_System.md](03_Attendance_System.md)** - Absensi siswa & guru
4. **[04_Payment_System.md](04_Payment_System.md)** - SPP, pembayaran lain, reminder
5. **[05_Grades_Report_Cards.md](05_Grades_Report_Cards.md)** - Input nilai, rapor, rekap akademik
6. **[06_New_Student_Registration.md](06_New_Student_Registration.md)** - PSB online, verifikasi dokumen
7. **[07_Teacher_Management.md](07_Teacher_Management.md)** - Data guru, jadwal, honor, evaluasi
8. **[08_Dashboard_Reports.md](08_Dashboard_Reports.md)** - Dashboard & laporan untuk semua role
9. **[09_School_Website.md](09_School_Website.md)** - Website publik sekolah
10. **[10_Notification_System.md](10_Notification_System.md)** - WhatsApp, email, in-app notifications
11. **[11_Settings_Configuration.md](11_Settings_Configuration.md)** - Master data, konfigurasi sistem

---

## 📝 Format User Story

Setiap user story menggunakan format standar:

```markdown
### US-[MODULE]-[NUMBER]: [Judul Story]

**As a** [Role/Persona]  
**I want** [Fitur/Aksi]  
**So that** [Manfaat/Tujuan]

**Priority:** Must Have / Should Have / Could Have / Won't Have (MoSCoW)

**Acceptance Criteria:**

✅ **Given** [Kondisi awal]  
   **When** [Aksi yang dilakukan]  
   **Then** [Hasil yang diharapkan]

✅ **Given** [...]  
   **When** [...]  
   **Then** [...]

**Notes/Dependencies:**
- [Catatan tambahan, dependency, atau constraint]
```

---

## 🎯 Priority Definitions (MoSCoW)

| Priority | Keterangan | Fase |
|----------|------------|------|
| **Must Have** | Critical untuk MVP, sistem tidak berfungsi tanpa ini | Phase 1 |
| **Should Have** | Penting tapi bisa di-workaround sementara | Phase 1 (late) / Phase 2 |
| **Could Have** | Nice to have, meningkatkan UX tapi tidak critical | Phase 2 |
| **Won't Have** | Out of scope untuk saat ini, future consideration | Future |

---

## 👥 User Roles

| Role | Kode | Deskripsi |
|------|------|-----------|
| **Kepala Sekolah** | KS | Mengawasi seluruh operasional, melihat laporan, approval |
| **TU/Admin** | TU | Administrasi, pembayaran, data siswa, laporan keuangan |
| **Guru** | GR | Input absensi, nilai, komunikasi dengan orang tua |
| **Orang Tua** | OT | Melihat info siswa, pembayaran, pengumuman |
| **Siswa** | SW | (Limited) Melihat pengumuman, tugas (fase 2) |
| **System Admin** | SA | Technical admin, konfigurasi sistem |

---

## 📊 Story Estimation (Optional)

Setiap story bisa diberi estimasi complexity:

- **XS** (1 point): Sangat sederhana, < 2 jam
- **S** (2 points): Sederhana, 2-4 jam
- **M** (3 points): Medium complexity, 1 hari
- **L** (5 points): Complex, 2-3 hari
- **XL** (8 points): Very complex, 1 minggu
- **XXL** (13+ points): Epic, perlu di-break down

---

## ✅ Acceptance Criteria Guidelines

Setiap acceptance criteria harus:

1. **Specific** - Jelas dan tidak ambigu
2. **Testable** - Bisa diverifikasi (manual atau automated test)
3. **Measurable** - Ada kriteria sukses yang objektif
4. **Realistic** - Feasible untuk diimplementasikan
5. **User-centric** - Fokus pada value untuk user, bukan technical detail

### Contoh BAIK:

```
✅ Given user ada di halaman login
   When user input username & password yang valid
   Then sistem redirect ke dashboard sesuai role user dalam < 2 detik
```

### Contoh KURANG BAIK:

```
❌ System should authenticate user
```
(Tidak spesifik, tidak testable)

---

## 🔗 Cross-Reference dengan Dokumen Lain

- **Functional Requirements** → Detail spesifikasi teknis fitur
- **Non-Functional Requirements** → Performance, security, usability constraints
- **Technical Architecture** → Implementasi teknis

---

## 📈 Progress Tracking

Setiap story dapat di-track dengan status:

- **📝 Draft** - User story masih draft, belum final
- **✅ Ready** - Sudah final, siap untuk development
- **🚧 In Progress** - Sedang dikerjakan
- **✔️ Done** - Sudah selesai & tested
- **🔄 Review** - Need revision atau clarification

---

## 🚀 Sprint Planning

User stories ini akan digunakan untuk sprint planning. Rekomendasi prioritas per sprint:

### Sprint 1 (2-3 minggu)
- Authentication & Authorization (Must Have)
- Student Management Basic (CRUD)
- Setup master data (kelas, mata pelajaran)

### Sprint 2 (2-3 minggu)
- Attendance System (Must Have)
- Payment System Basic (recording manual)

### Sprint 3 (2-3 minggu)
- Grades & Report Cards (Must Have)
- Dashboard & Basic Reports

### Sprint 4 (2-3 minggu)
- New Student Registration (Must Have)
- Teacher Management Basic

### Sprint 5 (2-3 minggu)
- Notification System (WhatsApp/Email manual)
- School Website (public facing)

### Sprint 6+ (Phase 2)
- Payment Gateway Integration
- Advanced Reports
- Offline Mode
- Additional features

---

## 📞 Contact & Feedback

Jika ada pertanyaan, clarification, atau perubahan requirement:

**Development Team:**  
Developer: Zulfikar Hidayatullah (+62 857-1583-8733)

**Stakeholder:**  
Kepala Sekolah SD + TU

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** Ready for Development Planning
