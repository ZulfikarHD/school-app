# GRD - Grades & Report Cards Test Plan

## Overview

Dokumen ini berisi test plan komprehensif untuk module Grades & Report Cards, yaitu: manual testing untuk Desktop dan Mobile, edge cases, serta acceptance criteria untuk QA team.

**Feature:** Grades & Report Cards  
**Version:** 1.0  
**Last Updated:** 2026-01-21

---

## Test Environment

| Item | Specification |
|------|---------------|
| **URL** | http://localhost:8000 atau staging URL |
| **Browsers** | Chrome (latest), Firefox (latest), Safari (latest) |
| **Mobile** | Chrome Mobile, Safari iOS |
| **Viewport** | Desktop: 1920x1080, Tablet: 768x1024, Mobile: 375x667 |
| **Test Data** | Seeder data atau create new test data |

### Test Accounts

| Role | Username | Password | Notes |
|------|----------|----------|-------|
| Admin | admin@school.test | password | Full access |
| Teacher | guru@school.test | password | Mengajar Matematika di 1A, 1B |
| Wali Kelas | wali@school.test | password | Wali kelas 1A |
| Principal | kepsek@school.test | password | Approval access |
| Parent | ortu@school.test | password | Parent of siswa di 1A |

---

## Test Case Categories

1. [Teacher: Input Nilai](#1-teacher-input-nilai)
2. [Teacher: Edit & Delete Nilai](#2-teacher-edit--delete-nilai)
3. [Wali Kelas: Input Nilai Sikap](#3-wali-kelas-input-nilai-sikap)
4. [Admin: Konfigurasi Bobot](#4-admin-konfigurasi-bobot)
5. [Admin: Generate Rapor](#5-admin-generate-rapor)
6. [Principal: Approval Flow](#6-principal-approval-flow)
7. [Parent: View Grades & Rapor](#7-parent-view-grades--rapor)
8. [Edge Cases](#8-edge-cases)
9. [Mobile Testing](#9-mobile-testing)

---

## 1. Teacher: Input Nilai

### TC-GRD-001: Create New Assessment (Happy Path)

**Preconditions:**
- [ ] Login sebagai Teacher
- [ ] Teacher mengajar Matematika di kelas 1A

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik menu "Penilaian" → "Input Nilai" | Halaman index penilaian tampil |
| 2 | Klik tombol "Tambah Penilaian Baru" | Form wizard step 1 tampil |
| 3 | Pilih Kelas: "1A" | Dropdown shows kelas 1A |
| 4 | Pilih Mapel: "Matematika" | Dropdown shows Matematika |
| 5 | Pilih Semester: "1" | Selected |
| 6 | Pilih Jenis: "UH" | Selected |
| 7 | Isi Nomor: "1" | Filled |
| 8 | Isi Judul: "UH 1: Perkalian" | Filled |
| 9 | Pilih Tanggal: today | Selected |
| 10 | Klik "Lanjut" | Step 2: Daftar siswa tampil |
| 11 | Isi nilai siswa 1: 85 | Field accepts 85 |
| 12 | Isi nilai siswa 2: 90 | Field accepts 90 |
| 13 | Klik "Simpan Nilai" | Redirect ke index dengan "Berhasil menyimpan nilai" |

**Postconditions:**
- [ ] Data nilai tersimpan di database
- [ ] Muncul di daftar penilaian

---

### TC-GRD-002: Validation - Score Out of Range

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Di step 2, isi nilai: 105 | Error: "Nilai maksimal 100" |
| 2 | Isi nilai: -5 | Error: "Nilai minimal 0" |
| 3 | Kosongkan nilai | Error: "Nilai wajib diisi" |
| 4 | Isi nilai: abc | Error: "Nilai harus berupa angka" |

---

### TC-GRD-003: Validation - Duplicate Assessment

**Preconditions:**
- [ ] UH 1 Matematika tanggal X sudah ada

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Create assessment dengan judul & tanggal sama | Error: "Penilaian dengan judul dan tanggal yang sama sudah ada" |

---

### TC-GRD-004: Teacher Cannot Access Other Class

**Preconditions:**
- [ ] Teacher TIDAK mengajar di kelas 2A

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Di form create, pilih kelas 2A | Kelas 2A TIDAK muncul di dropdown |

---

## 2. Teacher: Edit & Delete Nilai

### TC-GRD-005: Edit Existing Assessment

**Preconditions:**
- [ ] Ada penilaian UH 1 yang sudah diinput
- [ ] Nilai BELUM di-lock

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Di halaman index, klik icon "Edit" pada UH 1 | Form edit tampil dengan data existing |
| 2 | Ubah nilai siswa 1 dari 85 ke 88 | Field updated |
| 3 | Klik "Simpan" | Redirect ke index dengan "Berhasil mengupdate nilai" |

---

### TC-GRD-006: Cannot Edit Locked Grade

**Preconditions:**
- [ ] Nilai sudah di-lock (rapor sudah generate)

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik "Edit" pada nilai yang locked | Error: "Nilai sudah dikunci dan tidak dapat diedit" |

---

### TC-GRD-007: Delete Assessment

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik icon "Hapus" pada penilaian | Modal konfirmasi muncul |
| 2 | Klik "Ya, Hapus" | Penilaian terhapus dengan "Berhasil menghapus penilaian" |

---

## 3. Wali Kelas: Input Nilai Sikap

### TC-GRD-010: Input Attitude Grade (Happy Path)

**Preconditions:**
- [ ] Login sebagai Wali Kelas 1A

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik menu "Penilaian" → "Nilai Sikap" | Halaman nilai sikap tampil dengan info kelas |
| 2 | Klik "Input Nilai Sikap" | Form input tampil |
| 3 | Pilih Semester: "1" | Selected |
| 4 | Siswa 1: Spiritual = A | Selected |
| 5 | Klik "Gunakan Template" untuk deskripsi | Auto-fill deskripsi K13 |
| 6 | Siswa 1: Sosial = B | Selected |
| 7 | Isi Catatan Wali Kelas | Filled |
| 8 | Klik "Simpan Nilai Sikap" | Redirect dengan "Berhasil menyimpan nilai sikap" |

---

### TC-GRD-011: Non-Wali Kelas Cannot Access

**Preconditions:**
- [ ] Login sebagai Teacher biasa (bukan wali kelas)

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik menu "Nilai Sikap" | Pesan: "Hanya wali kelas yang dapat mengakses" |

---

## 4. Admin: Konfigurasi Bobot

### TC-GRD-020: Update Grade Weight (Happy Path)

**Preconditions:**
- [ ] Login sebagai Admin

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik "Penilaian" → "Bobot Nilai" | Halaman konfigurasi tampil |
| 2 | Ubah UH Weight: 35 | Field updated |
| 3 | Ubah UAS Weight: 25 | Field updated |
| 4 | Total auto-calculate: 100% | Displayed correctly |
| 5 | Klik "Simpan Konfigurasi" | "Konfigurasi bobot nilai berhasil disimpan" |

---

### TC-GRD-021: Validation - Total Not 100%

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Set UH: 50, UTS: 50, UAS: 50, Praktik: 50 | Total: 200% |
| 2 | Klik "Simpan" | Error: "Total bobot harus 100%, saat ini: 200%" |

---

## 5. Admin: Generate Rapor

### TC-GRD-030: Generate Rapor Bulk (Happy Path)

**Preconditions:**
- [ ] Login sebagai Admin
- [ ] Semua nilai kelas 1A sudah lengkap
- [ ] Nilai sikap sudah diinput

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik "Penilaian" → "Rapor" | Halaman index rapor |
| 2 | Klik "Generate Rapor" | Wizard step 1 |
| 3 | Pilih Tahun Ajaran & Semester | Selected |
| 4 | Centang kelas 1A | Checked |
| 5 | Klik "Validasi Kelengkapan" | List siswa dengan status completeness |
| 6 | Semua siswa complete (hijau) | ✅ tampil |
| 7 | Klik "Generate Rapor" | Processing... progress bar |
| 8 | Complete | "X rapor berhasil di-generate" |
| 9 | Klik "Download ZIP" | ZIP file downloaded |

---

### TC-GRD-031: Validation - Incomplete Data

**Preconditions:**
- [ ] Siswa Ahmad BELUM ada UAS Matematika

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik "Validasi Kelengkapan" | Ahmad: ⚠️ "UAS Matematika belum diinput" |
| 2 | Klik "Generate Lengkap Saja" | Ahmad di-skip, lainnya di-generate |

---

### TC-GRD-032: Unlock Report Card

**Preconditions:**
- [ ] Rapor sudah di-generate (status DRAFT)

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Di list rapor, klik "Unlock" | Konfirmasi muncul |
| 2 | Klik "Ya, Unlock" | "Rapor berhasil di-unlock" |
| 3 | Nilai terkait jadi unlocked | Teacher dapat edit |

---

## 6. Principal: Approval Flow

### TC-GRD-040: Approve Report Card

**Preconditions:**
- [ ] Login sebagai Principal
- [ ] Ada rapor dengan status PENDING_APPROVAL

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik "Akademik" → "Approval Rapor" | List pending approval |
| 2 | Klik kelas 1A | List siswa |
| 3 | Klik nama siswa | Preview rapor |
| 4 | Review nilai, sikap, catatan | Data tampil lengkap |
| 5 | Klik "Setujui & Rilis" | "Rapor berhasil di-approve dan dirilis" |
| 6 | Status → RELEASED | Tampil di list |

**Postconditions:**
- [ ] Parent menerima notifikasi
- [ ] Parent dapat melihat rapor

---

### TC-GRD-041: Reject Report Card

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik "Tolak" | Modal muncul |
| 2 | Kosongkan catatan | Error: "Catatan alasan penolakan wajib diisi" |
| 3 | Isi: "Nilai matematika perlu dicek" | Filled |
| 4 | Klik "Tolak" | "Rapor dikembalikan ke wali kelas" |
| 5 | Status → DRAFT | Tampil di list |

---

### TC-GRD-042: Bulk Approve

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Centang semua siswa di kelas 1A | All checked |
| 2 | Klik "Approve Semua" | Konfirmasi: "Approve X rapor?" |
| 3 | Klik "Ya" | "X rapor berhasil di-approve dan dirilis" |

---

## 7. Parent: View Grades & Rapor

### TC-GRD-050: View Child Grades

**Preconditions:**
- [ ] Login sebagai Parent
- [ ] Anak terdaftar di kelas 1A dengan nilai

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik "Anak Saya" | List anak |
| 2 | Klik card anak | Detail anak |
| 3 | Klik tab "Nilai" | Rekap nilai tampil |
| 4 | Filter Semester: 1 | Data filtered |
| 5 | Lihat tabel nilai | Mapel, Nilai UH/UTS/UAS, Nilai Akhir, Predikat |
| 6 | Klik row mapel | Expand detail UH 1, UH 2, dll |

---

### TC-GRD-051: View Released Report Card

**Preconditions:**
- [ ] Rapor sudah RELEASED

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Klik tab "Rapor" | List rapor per semester |
| 2 | Lihat card Semester 1 | Status: Tersedia, Rata-rata, Ranking |
| 3 | Klik "Lihat Rapor" | HTML preview rapor |
| 4 | Scroll dan review | Biodata, Nilai, Sikap, Kehadiran, Catatan |
| 5 | Klik "Download PDF" | PDF downloaded |

---

### TC-GRD-052: Report Card Not Released

**Preconditions:**
- [ ] Rapor status DRAFT atau PENDING

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Akses halaman rapor | "Rapor belum tersedia untuk dilihat" |

---

### TC-GRD-053: Parent Cannot View Other Child

**Preconditions:**
- [ ] Login sebagai Parent A
- [ ] Coba akses rapor anak Parent B

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Akses URL /parent/children/{other_student}/report-cards | 403: "Anda tidak memiliki akses" |

---

## 8. Edge Cases

### TC-GRD-060: No Grades Yet

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Parent akses tab Nilai (belum ada nilai) | Empty state: "Belum ada data nilai untuk semester ini" |

---

### TC-GRD-061: Siswa Pindah Kelas

**Scenario:** Siswa pindah dari 1A ke 1B di tengah semester

**Expected:**
- [ ] Nilai di kelas 1A tetap ada
- [ ] Generate rapor menggunakan kelas terakhir

---

### TC-GRD-062: Bobot Weight Changed After Input

**Scenario:** Nilai sudah diinput, lalu admin ubah bobot

**Expected:**
- [ ] Perhitungan nilai akhir menggunakan bobot terbaru
- [ ] Rapor yang sudah generate tidak berubah (snapshot)

---

### TC-GRD-063: Teacher Mengajar Multiple Classes

**Scenario:** Guru mengajar Matematika di 1A dan 1B

**Expected:**
- [ ] Dropdown kelas menampilkan 1A dan 1B
- [ ] Nilai tersimpan per kelas dengan benar

---

### TC-GRD-064: No Wali Kelas Assigned

**Scenario:** Kelas tidak punya wali kelas

**Expected:**
- [ ] Generate rapor: Warning "Wali kelas belum diset"
- [ ] Nilai sikap tidak bisa diinput

---

### TC-GRD-065: PDF Generation Timeout

**Scenario:** Generate rapor 100+ siswa

**Expected:**
- [ ] Progress bar berjalan
- [ ] Tidak timeout (timeout > 5 menit)
- [ ] Error handling jika gagal

---

## 9. Mobile Testing

### TC-GRD-070: Mobile - Grade Input Table

**Device:** iPhone 12 (375x667)

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Buka halaman input nilai | Table responsive |
| 2 | Scroll horizontal | Smooth scroll, NIS & Nama sticky |
| 3 | Input nilai | Keyboard numeric muncul |
| 4 | Submit | Works correctly |

---

### TC-GRD-071: Mobile - Report Card Preview

**Device:** iPhone 12

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Buka preview rapor | Responsive layout |
| 2 | Pinch zoom | Zoom enabled |
| 3 | Scroll | Smooth scroll |
| 4 | Download PDF | PDF opens in viewer |

---

### TC-GRD-072: Mobile - Parent Grade View

**Device:** iPhone 12

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Buka tab Nilai | Card-based layout |
| 2 | Tap mapel card | Expand detail |
| 3 | Filter semester | Dropdown works |

---

### TC-GRD-073: Mobile - Admin Generate Wizard

**Device:** iPad (768x1024)

**Steps:**
| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Buka wizard generate | Responsive |
| 2 | Select classes | Checkboxes work |
| 3 | View validation | Table scrollable |

---

## Defect Report Template

```markdown
## Bug Report

**Title:** [Brief description]

**Severity:** Critical / High / Medium / Low

**Steps to Reproduce:**
1. ...
2. ...
3. ...

**Expected Result:**
[What should happen]

**Actual Result:**
[What actually happened]

**Screenshot/Video:**
[Attach if applicable]

**Environment:**
- Browser: Chrome 120
- Device: Desktop / iPhone 12
- URL: http://localhost:8000/...
- User: admin@school.test
```

---

## Test Completion Checklist

### Teacher Tests
- [ ] TC-GRD-001: Create new assessment
- [ ] TC-GRD-002: Score validation
- [ ] TC-GRD-003: Duplicate validation
- [ ] TC-GRD-004: Access control
- [ ] TC-GRD-005: Edit assessment
- [ ] TC-GRD-006: Cannot edit locked
- [ ] TC-GRD-007: Delete assessment

### Wali Kelas Tests
- [ ] TC-GRD-010: Input attitude grade
- [ ] TC-GRD-011: Non-wali access denied

### Admin Tests
- [ ] TC-GRD-020: Update grade weight
- [ ] TC-GRD-021: Weight validation
- [ ] TC-GRD-030: Generate rapor bulk
- [ ] TC-GRD-031: Incomplete data handling
- [ ] TC-GRD-032: Unlock report card

### Principal Tests
- [ ] TC-GRD-040: Approve report card
- [ ] TC-GRD-041: Reject report card
- [ ] TC-GRD-042: Bulk approve

### Parent Tests
- [ ] TC-GRD-050: View child grades
- [ ] TC-GRD-051: View released report card
- [ ] TC-GRD-052: Report card not released
- [ ] TC-GRD-053: Access control

### Edge Cases
- [ ] TC-GRD-060 to TC-GRD-065

### Mobile Tests
- [ ] TC-GRD-070: Grade input table
- [ ] TC-GRD-071: Report card preview
- [ ] TC-GRD-072: Parent grade view
- [ ] TC-GRD-073: Admin wizard

---

## Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| QA Lead | | | |
| Developer | | | |
| Product Owner | | | |
