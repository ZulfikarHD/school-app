# EPIC 5: Grades & Report Cards System

**Epic ID:** EPIC-005  
**Epic Name:** Grades & Report Cards  
**Created:** 2025-12-13  
**Status:** Draft  
**Priority:** High  
**Target Release:** Phase 2  
**Epic Owner:** Product Team  

---

## 📋 Epic Overview

### Business Goal
Mengimplementasikan sistem penilaian dan rapor digital yang memungkinkan guru untuk menginput nilai siswa, menghitung nilai akhir otomatis, dan menghasilkan rapor digital sesuai dengan format Kurikulum 2013 (K13). Sistem ini juga menyediakan portal untuk orang tua agar dapat melihat dan mengunduh rapor anak mereka secara online.

### Problem Statement
Saat ini, proses penilaian dan pembuatan rapor masih dilakukan secara manual atau menggunakan spreadsheet, yang memakan waktu, rentan kesalahan perhitungan, dan sulit untuk diakses oleh orang tua. Dibutuhkan sistem yang dapat mengotomasi perhitungan nilai, menghasilkan rapor dalam format standar K13, dan memberikan akses real-time kepada orang tua.

### Success Criteria
- Guru dapat menginput nilai untuk semua komponen penilaian (UH, UTS, UAS, Praktik, Sikap)
- Sistem dapat menghitung nilai akhir secara otomatis berdasarkan bobot yang dikonfigurasi
- Rapor digital dapat digenerate dalam format PDF sesuai template K13
- Orang tua dapat mengakses dan mengunduh rapor anak mereka melalui portal
- Pengurangan waktu proses pembuatan rapor minimal 70%
- Akurasi perhitungan nilai 100% (tidak ada kesalahan manual)

### Scope
**In Scope:**
- Input nilai untuk komponen: Ulangan Harian (UH), UTS, UAS, Praktik/Keterampilan, Sikap
- Konfigurasi bobot nilai per mata pelajaran
- Perhitungan otomatis nilai Pengetahuan dan Keterampilan
- Konversi nilai numerik ke predikat (A/B/C/D)
- Input langsung predikat Sikap (Spiritual & Sosial)
- Generate rapor PDF format K13
- Portal orang tua untuk melihat dan download rapor
- Fitur kunci nilai (lock grades) untuk mencegah perubahan setelah rapor diterbitkan
- Export nilai ke Excel untuk backup

**Out of Scope:**
- Sistem penilaian untuk kurikulum selain K13
- Penilaian berbasis proyek atau portofolio digital
- Sistem rapor untuk jenjang selain SD/SMP/SMA
- Integrasi dengan sistem penilaian eksternal (UNBK, dll)

---

## 📚 User Stories Included

### Phase 2 - Core Features

| ID | User Story | Priority | Estimation |
|---|---|---|---|
| US-GRD-001 | Konfigurasi Bobot Nilai | High | 5 SP |
| US-GRD-002 | Input Nilai UH (Ulangan Harian) | High | 8 SP |
| US-GRD-003 | Input Nilai UTS & UAS | High | 5 SP |
| US-GRD-004 | Input Nilai Praktik/Keterampilan | High | 5 SP |
| US-GRD-005 | Input Nilai Sikap | High | 5 SP |
| US-GRD-006 | Lihat Rangkuman Nilai Siswa | High | 8 SP |
| US-GRD-007 | Generate Rapor PDF | High | 13 SP |
| US-GRD-008 | Orang Tua Lihat Rapor | High | 8 SP |
| US-GRD-009 | Kunci Nilai (Lock Grades) | Medium | 5 SP |
| US-GRD-010 | Edit Nilai yang Sudah Diinput | Medium | 5 SP |
| US-GRD-011 | Export Nilai ke Excel | Medium | 5 SP |
| US-GRD-012 | Validasi Perhitungan Nilai | High | 5 SP |

**Total Estimation:** 77 Story Points

### Detailed User Stories

#### US-GRD-001: Konfigurasi Bobot Nilai
**As a** Guru/Wali Kelas  
**I want** mengatur bobot untuk setiap komponen penilaian (UH, UTS, UAS, Praktik)  
**So that** nilai akhir dihitung sesuai dengan proporsi yang ditentukan  

**Priority:** High  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Konfigurasi Bobot Nilai
- When saya mengisi bobot untuk UH (%), UTS (%), UAS (%), Praktik (%)
- Then total bobot harus = 100%
- And sistem menampilkan error jika total ≠ 100%
- And saya dapat set bobot default untuk semua mata pelajaran
- And saya dapat set bobot khusus per mata pelajaran

**Notes:**
- Bobot dapat berbeda untuk setiap mata pelajaran
- Default bobot: UH 40%, UTS 30%, UAS 30%, Praktik 0%
- Validasi real-time saat input bobot

---

#### US-GRD-002: Input Nilai UH (Ulangan Harian)
**As a** Guru Mata Pelajaran  
**I want** menginput nilai Ulangan Harian untuk siswa  
**So that** nilai UH dapat digunakan dalam perhitungan nilai akhir  

**Priority:** High  
**Estimation:** 8 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Input Nilai
- When saya pilih Kelas, Mata Pelajaran, dan "Ulangan Harian"
- Then sistem menampilkan form input dengan kolom: Judul UH, Tanggal, Daftar Siswa
- And untuk setiap siswa, saya dapat input nilai (0-100)
- And sistem menghitung rata-rata UH otomatis jika ada multiple UH
- And nilai yang diinput tersimpan ke database
- And sistem menampilkan notifikasi sukses

**Notes:**
- Dapat input multiple UH (UH 1, UH 2, dst)
- Rata-rata UH dihitung untuk nilai Pengetahuan
- Format nilai: angka 0-100

---

#### US-GRD-003: Input Nilai UTS & UAS
**As a** Guru Mata Pelajaran  
**I want** menginput nilai UTS dan UAS untuk siswa  
**So that** nilai ujian dapat digunakan dalam perhitungan nilai akhir  

**Priority:** High  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Input Nilai
- When saya pilih Kelas, Mata Pelajaran, dan "UTS" atau "UAS"
- Then sistem menampilkan form input dengan kolom: Tanggal, Daftar Siswa
- And untuk setiap siswa, saya dapat input nilai (0-100)
- And nilai yang diinput tersimpan ke database
- And sistem menampilkan notifikasi sukses

**Notes:**
- UTS dan UAS hanya 1x per semester
- Validasi: nilai harus 0-100
- Auto-save setiap perubahan nilai

---

#### US-GRD-004: Input Nilai Praktik/Keterampilan
**As a** Guru Mata Pelajaran  
**I want** menginput nilai Praktik/Keterampilan untuk siswa  
**So that** nilai keterampilan dapat digunakan dalam perhitungan nilai akhir  

**Priority:** High  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Input Nilai
- When saya pilih Kelas, Mata Pelajaran, dan "Praktik/Keterampilan"
- Then sistem menampilkan form input dengan kolom: Judul Praktik, Tanggal, Daftar Siswa
- And untuk setiap siswa, saya dapat input nilai (0-100)
- And sistem menghitung rata-rata Praktik otomatis jika ada multiple
- And nilai yang diinput tersimpan ke database
- And sistem menampilkan notifikasi sukses

**Notes:**
- Dapat input multiple praktik (Praktik 1, Praktik 2, dst)
- Rata-rata Praktik dihitung untuk nilai Keterampilan
- Format nilai: angka 0-100

---

#### US-GRD-005: Input Nilai Sikap
**As a** Wali Kelas  
**I want** menginput nilai Sikap (Spiritual & Sosial) untuk siswa  
**So that** nilai sikap dapat ditampilkan di rapor  

**Priority:** High  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Input Nilai Sikap
- When saya pilih Kelas
- Then sistem menampilkan daftar siswa dengan kolom: Sikap Spiritual, Sikap Sosial
- And untuk setiap siswa, saya dapat pilih predikat: SB (Sangat Baik), B (Baik), C (Cukup), K (Kurang)
- And saya dapat input deskripsi untuk setiap sikap
- And nilai yang diinput tersimpan ke database
- And sistem menampilkan notifikasi sukses

**Notes:**
- Sikap diinput oleh Wali Kelas, bukan guru mata pelajaran
- Format: pilihan predikat + text area untuk deskripsi
- Deskripsi optional

---

#### US-GRD-006: Lihat Rangkuman Nilai Siswa
**As a** Guru/Wali Kelas  
**I want** melihat rangkuman nilai siswa per mata pelajaran  
**So that** saya dapat memverifikasi nilai sebelum mencetak rapor  

**Priority:** High  
**Estimation:** 8 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Rangkuman Nilai
- When saya pilih Kelas dan Siswa
- Then sistem menampilkan tabel dengan kolom: Mata Pelajaran, UH, UTS, UAS, Praktik, Nilai Pengetahuan, Nilai Keterampilan, Predikat
- And sistem menampilkan nilai akhir untuk setiap mata pelajaran
- And sistem menampilkan nilai Sikap (Spiritual & Sosial)
- And saya dapat klik "Edit" untuk mengubah nilai
- And saya dapat klik "Generate Rapor" untuk mencetak rapor

**Notes:**
- Tampilkan semua mata pelajaran yang diambil siswa
- Highlight nilai yang belum lengkap (warna merah)
- Formula perhitungan ditampilkan di tooltip

---

#### US-GRD-007: Generate Rapor PDF
**As a** Wali Kelas  
**I want** menghasilkan rapor PDF untuk siswa  
**So that** rapor dapat dicetak atau dikirim ke orang tua  

**Priority:** High  
**Estimation:** 13 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Generate Rapor
- When saya pilih Semester dan Tahun Ajaran
- And saya klik "Generate Rapor untuk Semua Siswa" atau pilih siswa tertentu
- Then sistem menghasilkan file PDF untuk setiap siswa
- And PDF berisi: Biodata Siswa, Nilai per Mata Pelajaran (Pengetahuan, Keterampilan, Predikat, Deskripsi), Nilai Sikap, Absensi, Catatan Wali Kelas
- And format PDF sesuai template K13
- And PDF dapat didownload
- And sistem mengirim notifikasi ke orang tua bahwa rapor sudah tersedia

**Notes:**
- Template rapor mengikuti format resmi K13
- Include logo sekolah di header
- Watermark "Dokumen Resmi" di background
- File naming: Rapor_[NIS]_[Nama]_[Semester]_[TahunAjaran].pdf

---

#### US-GRD-008: Orang Tua Lihat Rapor
**As a** Orang Tua  
**I want** melihat dan mengunduh rapor anak saya  
**So that** saya dapat memantau perkembangan akademik anak  

**Priority:** High  
**Estimation:** 8 Story Points  

**Acceptance Criteria:**
- Given saya login sebagai orang tua
- When saya masuk ke menu "Rapor"
- Then sistem menampilkan list rapor anak saya (per semester/tahun ajaran)
- And untuk setiap rapor, saya dapat klik "Lihat" untuk preview
- And saya dapat klik "Download PDF" untuk mengunduh rapor
- And sistem mencatat log setiap kali orang tua mengunduh rapor

**Notes:**
- Preview rapor di browser sebelum download
- Notifikasi email/WhatsApp saat rapor baru tersedia
- History download rapor tersimpan

---

#### US-GRD-009: Kunci Nilai (Lock Grades)
**As a** Admin/Kepala Sekolah  
**I want** mengunci nilai setelah rapor diterbitkan  
**So that** nilai tidak dapat diubah tanpa approval  

**Priority:** Medium  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Manajemen Nilai
- When saya klik "Kunci Nilai" untuk semester tertentu
- Then sistem mengunci semua nilai untuk semester tersebut
- And guru tidak dapat mengedit nilai yang sudah dikunci
- And jika perlu edit, guru harus request unlock ke admin
- And sistem mencatat log lock/unlock beserta alasan

**Notes:**
- Lock dilakukan per semester
- Email notifikasi ke guru saat nilai dikunci
- Request unlock harus include alasan

---

#### US-GRD-010: Edit Nilai yang Sudah Diinput
**As a** Guru Mata Pelajaran  
**I want** mengedit nilai yang sudah saya input sebelumnya  
**So that** saya dapat memperbaiki kesalahan input  

**Priority:** Medium  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Rangkuman Nilai atau Input Nilai
- When saya klik "Edit" pada nilai tertentu
- Then sistem menampilkan form edit dengan nilai yang sudah ada
- And saya dapat mengubah nilai
- And sistem menyimpan perubahan dengan log audit (siapa, kapan, nilai lama, nilai baru)
- And sistem menampilkan notifikasi sukses

**Notes:**
- Tidak bisa edit jika nilai sudah dikunci
- Audit trail untuk semua perubahan nilai
- Highlight nilai yang sudah diedit

---

#### US-GRD-011: Export Nilai ke Excel
**As a** Wali Kelas  
**I want** export nilai ke Excel  
**So that** saya dapat backup atau analisis lebih lanjut  

**Priority:** Medium  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given saya di halaman Rangkuman Nilai
- When saya klik "Export to Excel"
- Then sistem menghasilkan file Excel dengan sheet:
  - Sheet 1: Nilai per Siswa (pivot: siswa sebagai baris, mapel sebagai kolom)
  - Sheet 2: Nilai per Mata Pelajaran (detail semua komponen)
  - Sheet 3: Rangkuman Kelas (statistik)
- And file Excel dapat didownload
- And format Excel sesuai dengan template yang ditentukan

**Notes:**
- Include formula di Excel untuk perhitungan nilai
- Color coding untuk nilai di bawah KKM
- Include chart untuk visualisasi

---

#### US-GRD-012: Validasi Perhitungan Nilai
**As a** Guru/Wali Kelas  
**I want** sistem memvalidasi perhitungan nilai secara otomatis  
**So that** nilai akhir selalu akurat dan sesuai formula  

**Priority:** High  
**Estimation:** 5 Story Points  

**Acceptance Criteria:**
- Given sistem menghitung nilai akhir
- When ada perubahan pada komponen nilai atau bobot
- Then sistem otomatis recalculate nilai Pengetahuan dan Keterampilan
- And sistem menampilkan peringatan jika ada anomali (nilai > 100, nilai negatif, dll)
- And formula perhitungan konsisten dengan aturan K13
- And sistem menampilkan breakdown perhitungan di tooltip

**Notes:**
- Real-time validation saat input
- Formula: Nilai Pengetahuan = (Rata-rata UH × Bobot UH) + (UTS × Bobot UTS) + (UAS × Bobot UAS)
- Formula: Nilai Keterampilan = Rata-rata Praktik
- Predikat: A (86-100), B (71-85), C (56-70), D (≤55)

---

## 🏗️ Technical Architecture

### System Components

#### 1. Backend Services

**Grade Service**
```
Responsibilities:
- Input dan update nilai (UH, UTS, UAS, Praktik, Sikap)
- Perhitungan nilai akhir berdasarkan bobot
- Konversi nilai numerik ke predikat
- Validasi business rules
- Grade locking/unlocking
- Audit trail untuk perubahan nilai

Endpoints:
- POST /api/grades/input
- PUT /api/grades/{id}
- GET /api/grades/student/{studentId}
- GET /api/grades/class/{classId}
- POST /api/grades/lock
- POST /api/grades/unlock
```

**Grade Configuration Service**
```
Responsibilities:
- Manage bobot nilai per mata pelajaran
- Manage template predikat K13
- Default configuration untuk sekolah

Endpoints:
- GET /api/grade-config/weights
- PUT /api/grade-config/weights
- GET /api/grade-config/predicates
```

**Report Card Service**
```
Responsibilities:
- Generate rapor PDF
- Template management
- Merge data (nilai, biodata, absensi, sikap)
- PDF generation menggunakan library

Endpoints:
- POST /api/report-cards/generate
- GET /api/report-cards/{id}/pdf
- GET /api/report-cards/student/{studentId}
- POST /api/report-cards/bulk-generate
```

**Parent Portal Service**
```
Responsibilities:
- API untuk orang tua akses rapor
- Download tracking
- Notification untuk rapor baru

Endpoints:
- GET /api/parent-portal/report-cards
- GET /api/parent-portal/report-cards/{id}/download
- GET /api/parent-portal/report-cards/{id}/view
```

#### 2. Database Schema

**Table: grades**
```sql
CREATE TABLE grades (
  id UUID PRIMARY KEY,
  student_id UUID NOT NULL REFERENCES students(id),
  subject_id UUID NOT NULL REFERENCES subjects(id),
  class_id UUID NOT NULL REFERENCES classes(id),
  semester VARCHAR(10) NOT NULL, -- "Ganjil" or "Genap"
  academic_year VARCHAR(9) NOT NULL, -- "2024/2025"
  
  -- Component Grades (JSON Array for multiple entries)
  uh_scores JSONB, -- [{"title": "UH 1", "date": "2024-09-15", "score": 85}, ...]
  uts_score DECIMAL(5,2),
  uts_date DATE,
  uas_score DECIMAL(5,2),
  uas_date DATE,
  praktik_scores JSONB, -- [{"title": "Praktik 1", "date": "2024-10-01", "score": 90}, ...]
  
  -- Calculated Grades
  pengetahuan_score DECIMAL(5,2),
  pengetahuan_predicate VARCHAR(1), -- A, B, C, D
  keterampilan_score DECIMAL(5,2),
  keterampilan_predicate VARCHAR(1),
  
  -- Status
  is_locked BOOLEAN DEFAULT FALSE,
  locked_at TIMESTAMP,
  locked_by UUID REFERENCES users(id),
  
  -- Audit
  created_by UUID NOT NULL REFERENCES users(id),
  updated_by UUID REFERENCES users(id),
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW(),
  
  UNIQUE(student_id, subject_id, semester, academic_year)
);

CREATE INDEX idx_grades_student ON grades(student_id);
CREATE INDEX idx_grades_class ON grades(class_id);
CREATE INDEX idx_grades_semester ON grades(semester, academic_year);
```

**Table: attitude_grades (Sikap)**
```sql
CREATE TABLE attitude_grades (
  id UUID PRIMARY KEY,
  student_id UUID NOT NULL REFERENCES students(id),
  class_id UUID NOT NULL REFERENCES classes(id),
  semester VARCHAR(10) NOT NULL,
  academic_year VARCHAR(9) NOT NULL,
  
  spiritual_predicate VARCHAR(2), -- SB, B, C, K
  spiritual_description TEXT,
  social_predicate VARCHAR(2),
  social_description TEXT,
  
  created_by UUID NOT NULL REFERENCES users(id),
  updated_by UUID REFERENCES users(id),
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW(),
  
  UNIQUE(student_id, semester, academic_year)
);
```

**Table: grade_weights**
```sql
CREATE TABLE grade_weights (
  id UUID PRIMARY KEY,
  school_id UUID REFERENCES schools(id),
  subject_id UUID REFERENCES subjects(id), -- NULL = default untuk semua
  
  uh_weight DECIMAL(5,2) NOT NULL,
  uts_weight DECIMAL(5,2) NOT NULL,
  uas_weight DECIMAL(5,2) NOT NULL,
  praktik_weight DECIMAL(5,2) DEFAULT 0,
  
  is_default BOOLEAN DEFAULT FALSE,
  
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW(),
  
  CONSTRAINT check_total_weight CHECK (uh_weight + uts_weight + uas_weight + praktik_weight = 100)
);
```

**Table: report_cards**
```sql
CREATE TABLE report_cards (
  id UUID PRIMARY KEY,
  student_id UUID NOT NULL REFERENCES students(id),
  class_id UUID NOT NULL REFERENCES classes(id),
  semester VARCHAR(10) NOT NULL,
  academic_year VARCHAR(9) NOT NULL,
  
  pdf_url TEXT,
  pdf_generated_at TIMESTAMP,
  
  teacher_notes TEXT,
  wali_kelas_id UUID REFERENCES users(id),
  
  status VARCHAR(20) DEFAULT 'draft', -- draft, published
  published_at TIMESTAMP,
  
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW(),
  
  UNIQUE(student_id, semester, academic_year)
);
```

**Table: report_card_downloads**
```sql
CREATE TABLE report_card_downloads (
  id UUID PRIMARY KEY,
  report_card_id UUID NOT NULL REFERENCES report_cards(id),
  downloaded_by UUID NOT NULL REFERENCES users(id), -- Parent user
  downloaded_at TIMESTAMP DEFAULT NOW(),
  ip_address INET
);
```

**Table: grade_audit_log**
```sql
CREATE TABLE grade_audit_log (
  id UUID PRIMARY KEY,
  grade_id UUID NOT NULL REFERENCES grades(id),
  action VARCHAR(20) NOT NULL, -- 'create', 'update', 'lock', 'unlock'
  field_name VARCHAR(50),
  old_value TEXT,
  new_value TEXT,
  reason TEXT, -- untuk unlock request
  
  performed_by UUID NOT NULL REFERENCES users(id),
  performed_at TIMESTAMP DEFAULT NOW()
);
```

#### 3. Calculation Engine

**Grade Calculator Module**
```typescript
interface GradeComponents {
  uhScores: number[];
  utsScore: number;
  uasScore: number;
  praktikScores: number[];
}

interface GradeWeights {
  uhWeight: number;
  utsWeight: number;
  uasWeight: number;
  praktikWeight: number;
}

class GradeCalculator {
  // Hitung Nilai Pengetahuan
  calculatePengetahuan(components: GradeComponents, weights: GradeWeights): number {
    const avgUH = this.average(components.uhScores);
    
    const nilaiPengetahuan = 
      (avgUH * weights.uhWeight / 100) +
      (components.utsScore * weights.utsWeight / 100) +
      (components.uasScore * weights.uasWeight / 100);
    
    return Math.round(nilaiPengetahuan * 100) / 100; // 2 decimal places
  }
  
  // Hitung Nilai Keterampilan
  calculateKeterampilan(components: GradeComponents): number {
    return this.average(components.praktikScores);
  }
  
  // Konversi ke Predikat
  scoreToPredicate(score: number): 'A' | 'B' | 'C' | 'D' {
    if (score >= 86) return 'A';
    if (score >= 71) return 'B';
    if (score >= 56) return 'C';
    return 'D';
  }
  
  private average(scores: number[]): number {
    if (scores.length === 0) return 0;
    const sum = scores.reduce((a, b) => a + b, 0);
    return sum / scores.length;
  }
}
```

#### 4. PDF Generation

**Library:** puppeteer atau pdfmake  
**Template:** HTML/CSS template untuk rapor K13  

**Rapor Template Structure:**
```
- Header: Logo Sekolah, Nama Sekolah, Alamat
- Biodata Siswa: NIS, Nama, Kelas, Semester, Tahun Ajaran
- Tabel Nilai:
  * Kolom: No, Mata Pelajaran, Pengetahuan (Nilai, Predikat), Keterampilan (Nilai, Predikat)
- Muatan Lokal
- Nilai Sikap: Spiritual & Sosial (Predikat + Deskripsi)
- Absensi: Sakit, Izin, Tanpa Keterangan
- Catatan Wali Kelas
- Tanda Tangan: Orang Tua, Wali Kelas, Kepala Sekolah
- Footer: Tanggal cetak
```

#### 5. Integration Points

- **Student Management:** Get student list, biodata
- **Teacher Management:** Get teacher data untuk wali kelas
- **Class Management:** Get class info, subject list
- **Attendance System:** Get attendance summary untuk rapor
- **Notification Service:** Send email/WhatsApp untuk rapor baru
- **File Storage:** S3/Cloud Storage untuk PDF rapor

---

## 🎨 UI/UX Design Requirements

### 1. Grade Input Page (Guru)

**Layout:**
```
┌─────────────────────────────────────────────────────┐
│ Input Nilai                                    [?]  │
├─────────────────────────────────────────────────────┤
│ [Dropdown: Pilih Kelas ▼] [Dropdown: Mata Pelajaran ▼]  │
│ [Tabs: UH | UTS | UAS | Praktik]               │
├─────────────────────────────────────────────────────┤
│ Ulangan Harian (UH)                                 │
│ Judul: [_____________]  Tanggal: [📅 __________]   │
│                                                     │
│ Daftar Siswa:                                       │
│ ┌───┬─────────────┬────────────┬────────┐          │
│ │No │ NIS         │ Nama Siswa │ Nilai  │          │
│ ├───┼─────────────┼────────────┼────────┤          │
│ │1  │12345        │Ahmad       │[  85  ]│          │
│ │2  │12346        │Budi        │[  90  ]│          │
│ │3  │12347        │Citra       │[  78  ]│          │
│ └───┴─────────────┴────────────┴────────┘          │
│                                                     │
│          [Batal]  [Simpan Draft]  [Simpan]         │
└─────────────────────────────────────────────────────┘
```

**Features:**
- Auto-complete untuk pencarian siswa
- Validasi real-time: nilai harus 0-100
- Auto-save draft setiap 30 detik
- Keyboard navigation (Tab, Enter untuk next field)
- Bulk import dari Excel
- Quick actions: Copy nilai dari UH sebelumnya

**Mobile Responsive:**
- Card layout per siswa
- Swipe untuk edit
- Large input field untuk touch

---

### 2. Grade Summary Page (Guru/Wali Kelas)

**Layout:**
```
┌─────────────────────────────────────────────────────┐
│ Rangkuman Nilai                              [Export Excel] [Generate Rapor]  │
├─────────────────────────────────────────────────────┤
│ [Dropdown: Pilih Kelas ▼] [Dropdown: Pilih Siswa ▼]│
├─────────────────────────────────────────────────────┤
│ Siswa: Ahmad (NIS: 12345)   Semester: Ganjil 2024/2025  │
│                                                     │
│ ┌─────────────────────────────────────────────────┐│
│ │ Mata      │ UH  │UTS│UAS│Prak│ Peng  │ Ket   │  ││
│ │ Pelajaran │     │   │   │    │(Pred) │(Pred) │  ││
│ ├───────────┼─────┼───┼───┼────┼───────┼───────┼──┤│
│ │Matematika │85   │80 │90 │-   │85 (B) │-      │✏️││
│ │B. Indonesia│90  │85 │88 │92  │88 (A) │92 (A) │✏️││
│ │IPA        │78   │75 │80 │85  │78 (B) │85 (B) │✏️││
│ │...        │     │   │   │    │       │       │  ││
│ └─────────────────────────────────────────────────┘│
│                                                     │
│ Nilai Sikap:                                        │
│ • Spiritual: [SB ▼] Deskripsi: [____________]      │
│ • Sosial:    [B  ▼] Deskripsi: [____________]      │
│                                                     │
│ Absensi: Sakit: [2] Izin: [1] Tanpa Keterangan: [0]│
│                                                     │
│ Catatan Wali Kelas: [_____________________]        │
│                                                     │
│                         [Simpan]                    │
└─────────────────────────────────────────────────────┘
```

**Features:**
- Color coding: 
  - Hijau (A: 86-100)
  - Biru (B: 71-85)
  - Kuning (C: 56-70)
  - Merah (D: ≤55)
  - Abu-abu (belum diisi)
- Click nilai untuk edit inline
- Hover untuk lihat formula perhitungan
- Warning jika ada nilai yang belum lengkap
- Filter: Show only incomplete grades

---

### 3. Generate Report Card Page (Wali Kelas)

**Layout:**
```
┌─────────────────────────────────────────────────────┐
│ Generate Rapor                                 [?]  │
├─────────────────────────────────────────────────────┤
│ [Dropdown: Pilih Kelas ▼]                           │
│ Semester: [Ganjil ▼]  Tahun Ajaran: [2024/2025 ▼] │
├─────────────────────────────────────────────────────┤
│ Status Kelengkapan Nilai:                           │
│ • Nilai Pengetahuan & Keterampilan: ✅ 100% Lengkap │
│ • Nilai Sikap: ⚠️  80% Lengkap (4 siswa belum)     │
│ • Absensi: ✅ 100% Lengkap                          │
│ • Catatan Wali Kelas: ✅ Sudah diisi                │
│                                                     │
│ Daftar Siswa:                                       │
│ ☑️  Pilih Semua                                     │
│ ☑️  12345 - Ahmad           [Lengkap]   [Preview]  │
│ ☑️  12346 - Budi            [Lengkap]   [Preview]  │
│ ☐  12347 - Citra           [⚠️ Sikap]  [Preview]   │
│ ☑️  12348 - Dewi            [Lengkap]   [Preview]  │
│ ...                                                 │
│                                                     │
│       [Generate untuk Siswa Terpilih (3 siswa)]    │
│       [Generate untuk Semua (20 siswa)]            │
│                                                     │
│ Catatan:                                            │
│ • Proses generate memerlukan waktu ~2 menit untuk 20 siswa  │
│ • Rapor akan dikirim notifikasi ke orang tua       │
│ • Setelah generate, nilai akan dikunci otomatis    │
└─────────────────────────────────────────────────────┘
```

**Features:**
- Progress bar saat generate (realtime)
- Preview rapor sebelum generate final
- Batch generation untuk multiple siswa
- Error handling: tampilkan siswa yang gagal
- Option: Auto-notify parents
- Option: Auto-lock grades after generation

---

### 4. Parent Portal - Report Card View

**Layout:**
```
┌─────────────────────────────────────────────────────┐
│ Portal Orang Tua - Rapor                       [👤] │
├─────────────────────────────────────────────────────┤
│ Anak Saya: Ahmad (Kelas 7A)                         │
├─────────────────────────────────────────────────────┤
│ Daftar Rapor:                                       │
│                                                     │
│ ┌─────────────────────────────────────────────┐    │
│ │ 📄 Rapor Semester Ganjil 2024/2025     🆕   │    │
│ │ Tanggal Terbit: 20 Desember 2024           │    │
│ │ Status: Tersedia                            │    │
│ │         [Lihat] [Download PDF]              │    │
│ └─────────────────────────────────────────────┘    │
│                                                     │
│ ┌─────────────────────────────────────────────┐    │
│ │ 📄 Rapor Semester Genap 2023/2024           │    │
│ │ Tanggal Terbit: 25 Juni 2024               │    │
│ │ Status: Tersedia                            │    │
│ │         [Lihat] [Download PDF]              │    │
│ └─────────────────────────────────────────────┘    │
│                                                     │
│ ┌─────────────────────────────────────────────┐    │
│ │ 📄 Rapor Semester Ganjil 2023/2024          │    │
│ │ Tanggal Terbit: 22 Desember 2023           │    │
│ │ Status: Tersedia                            │    │
│ │         [Lihat] [Download PDF]              │    │
│ └─────────────────────────────────────────────┘    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Features:**
- Badge "🆕" untuk rapor baru
- Preview rapor dalam modal/iframe
- Download PDF dengan watermark
- Notifikasi push saat rapor baru tersedia
- History download rapor
- Share rapor via WhatsApp/Email

**Mobile Optimized:**
- Card layout
- Large buttons untuk touch
- Swipe down to refresh
- Download langsung ke device

---

### 5. Grade Configuration Page (Admin/Wali Kelas)

**Layout:**
```
┌─────────────────────────────────────────────────────┐
│ Konfigurasi Bobot Nilai                        [?]  │
├─────────────────────────────────────────────────────┤
│ [Tab: Default] [Tab: Per Mata Pelajaran]           │
├─────────────────────────────────────────────────────┤
│ Bobot Default (untuk semua mata pelajaran):        │
│                                                     │
│ Ulangan Harian (UH):    [40]%                      │
│ UTS:                     [30]%                      │
│ UAS:                     [30]%                      │
│ Praktik/Keterampilan:   [ 0]%                      │
│                         ────                        │
│ Total:                   100% ✅                    │
│                                                     │
│                    [Reset] [Simpan]                 │
├─────────────────────────────────────────────────────┤
│ Bobot Khusus Per Mata Pelajaran:                   │
│                                                     │
│ ┌───────────────────────────────────────────────┐  │
│ │ Matematika                                    │  │
│ │ Menggunakan: ⦿ Bobot Default ○ Bobot Khusus  │  │
│ │                             [Atur Bobot Khusus]│  │
│ └───────────────────────────────────────────────┘  │
│                                                     │
│ ┌───────────────────────────────────────────────┐  │
│ │ Pendidikan Jasmani                            │  │
│ │ Menggunakan: ○ Bobot Default ⦿ Bobot Khusus  │  │
│ │ UH: [20]% UTS: [20]% UAS: [20]% Praktik: [40]%│  │
│ │ Total: 100% ✅                [Simpan]         │  │
│ └───────────────────────────────────────────────┘  │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Features:**
- Real-time validation: total harus 100%
- Visual indicator (✅/❌) untuk total bobot
- Preset templates: "Teori", "Praktik", "Balanced"
- Bulk apply untuk multiple mata pelajaran
- History perubahan bobot

---

## ✅ Definition of Done

### Feature Level DoD
- [ ] Semua user stories telah diimplementasikan dan memenuhi acceptance criteria
- [ ] Unit tests dengan coverage minimal 80%
- [ ] Integration tests untuk semua API endpoints
- [ ] E2E tests untuk critical user flows (input nilai, generate rapor, parent view)
- [ ] Formula perhitungan nilai telah divalidasi dan akurat
- [ ] PDF rapor sesuai dengan template K13 resmi
- [ ] Performance: Generate 1 rapor dalam < 3 detik, bulk 20 rapor dalam < 60 detik
- [ ] Mobile responsive untuk semua halaman
- [ ] Accessibility: WCAG 2.1 AA compliant
- [ ] Security: Authorization checks untuk semua endpoints
- [ ] Dokumentasi API lengkap (Swagger/OpenAPI)
- [ ] User documentation dan tutorial video
- [ ] Linter errors resolved
- [ ] Code review approved oleh minimal 2 developers
- [ ] QA testing passed (functional, regression, UAT)
- [ ] Deployed ke staging dan production
- [ ] Monitoring dan logging configured
- [ ] Data migration script (jika ada) tested dan documented

### Sprint Level DoD
- [ ] Sprint goals tercapai
- [ ] Demo kepada stakeholder completed
- [ ] Sprint retrospective conducted
- [ ] Backlog refinement untuk sprint berikutnya
- [ ] All blockers resolved atau di-escalate

---

## 🔗 Dependencies

### Internal Dependencies

**Must Have Before Start:**
- ✅ EPIC-001: Foundation & Access Control (untuk authentication & authorization)
- ✅ EPIC-002: Student Management (untuk data siswa)
- ✅ EPIC-003: Teacher & Class Management (untuk data guru, kelas, mata pelajaran)

**Should Have:**
- ⚠️ EPIC-004: Attendance System (untuk data absensi di rapor)
- ⚠️ Notification Service (untuk notifikasi ke orang tua)

**Nice to Have:**
- ⏳ Dashboard & Analytics (untuk visualisasi nilai dan statistik)

### External Dependencies

- **PDF Generation Library:** puppeteer atau pdfmake
- **Cloud Storage:** AWS S3 atau Google Cloud Storage (untuk menyimpan PDF rapor)
- **Email Service:** SendGrid atau AWS SES (untuk notifikasi ke orang tua)
- **SMS/WhatsApp API:** Twilio atau WhatsApp Business API (optional)

### Third-Party Integrations
- None (sistem standalone)

---

## 🧪 Testing Strategy

### 1. Unit Testing

**Grade Calculator Tests:**
```typescript
describe('GradeCalculator', () => {
  test('should calculate Pengetahuan correctly', () => {
    const components = {
      uhScores: [80, 85, 90],
      utsScore: 75,
      uasScore: 85,
      praktikScores: []
    };
    const weights = {
      uhWeight: 40,
      utsWeight: 30,
      uasWeight: 30,
      praktikWeight: 0
    };
    
    const result = calculator.calculatePengetahuan(components, weights);
    // Rata-rata UH = 85
    // Nilai = (85 * 0.4) + (75 * 0.3) + (85 * 0.3)
    //       = 34 + 22.5 + 25.5 = 82
    expect(result).toBe(82);
  });
  
  test('should convert score to predicate correctly', () => {
    expect(calculator.scoreToPredicate(90)).toBe('A');
    expect(calculator.scoreToPredicate(80)).toBe('B');
    expect(calculator.scoreToPredicate(65)).toBe('C');
    expect(calculator.scoreToPredicate(50)).toBe('D');
  });
  
  test('should validate total weight = 100%', () => {
    const weights = { uhWeight: 40, utsWeight: 30, uasWeight: 25, praktikWeight: 0 };
    expect(() => validator.validateWeights(weights)).toThrow('Total bobot harus 100%');
  });
});
```

**API Tests:**
- Test CRUD operations untuk grades, attitude_grades, grade_weights
- Test validasi input (nilai 0-100, total bobot 100%)
- Test authorization (guru hanya bisa input untuk kelasnya)
- Test calculation accuracy
- Test grade locking/unlocking
- Test audit trail logging

### 2. Integration Testing

**Grade Input Flow:**
```gherkin
Scenario: Guru menginput nilai UH
  Given saya login sebagai guru mata pelajaran Matematika
  And saya mengajar kelas 7A
  When saya POST /api/grades/input dengan data:
    """
    {
      "classId": "class-7a-id",
      "subjectId": "matematika-id",
      "type": "UH",
      "title": "UH 1 - Aljabar",
      "date": "2024-09-15",
      "scores": [
        {"studentId": "student-1", "score": 85},
        {"studentId": "student-2", "score": 90}
      ]
    }
    """
  Then response status adalah 201
  And nilai tersimpan di database
  And nilai Pengetahuan terupdate otomatis
```

**Report Card Generation Flow:**
```gherkin
Scenario: Wali kelas generate rapor untuk semua siswa
  Given semua nilai sudah lengkap untuk kelas 7A
  When saya POST /api/report-cards/bulk-generate dengan classId
  Then sistem generate PDF untuk setiap siswa
  And PDF tersimpan di cloud storage
  And notifikasi dikirim ke orang tua
  And nilai di-lock otomatis
  And report_cards record dibuat dengan status 'published'
```

### 3. End-to-End Testing

**Test Scenario 1: Complete Grade Input Journey**
1. Login sebagai Guru
2. Navigate ke "Input Nilai"
3. Pilih Kelas dan Mata Pelajaran
4. Input nilai UH untuk semua siswa
5. Input nilai UTS dan UAS
6. Input nilai Praktik
7. Verify nilai Pengetahuan dan Keterampilan terupdate
8. Navigate ke "Rangkuman Nilai"
9. Verify semua nilai tampil correct
10. Logout

**Test Scenario 2: Report Card Generation & Parent View**
1. Login sebagai Wali Kelas
2. Navigate ke "Generate Rapor"
3. Input nilai Sikap untuk semua siswa
4. Input catatan wali kelas
5. Generate rapor untuk semua siswa
6. Wait for generation complete
7. Preview rapor PDF
8. Logout
9. Login sebagai Orang Tua
10. Navigate ke "Rapor"
11. Verify rapor baru muncul dengan badge "🆕"
12. Preview rapor
13. Download PDF
14. Verify download berhasil

**Test Scenario 3: Grade Edit & Lock**
1. Login sebagai Guru
2. Edit nilai yang sudah diinput
3. Verify audit log tercatat
4. Logout
5. Login sebagai Admin
6. Lock grades untuk semester
7. Logout
8. Login sebagai Guru
9. Try to edit nilai (should fail)
10. Request unlock dengan alasan
11. Logout
12. Login sebagai Admin
13. Approve unlock request
14. Logout
15. Login sebagai Guru
16. Edit nilai (should success)

### 4. Performance Testing

**Metrics to Test:**
- API response time: < 500ms untuk single request
- Grade calculation: < 100ms untuk 1 siswa, < 2s untuk 30 siswa
- PDF generation: < 3s untuk 1 rapor, < 60s untuk 20 rapor
- Page load: < 2s untuk Grade Input page dengan 30 siswa
- Concurrent users: Support 50 guru input nilai simultaneously

**Load Testing Scenarios:**
- 20 guru input nilai simultaneously (600 requests/minute)
- Bulk generate rapor untuk 500 siswa (stress test)
- 100 orang tua download rapor simultaneously

### 5. Validation Testing

**Test Scenarios:**
| Scenario | Input | Expected Result |
|----------|-------|-----------------|
| Input nilai > 100 | 105 | Error: "Nilai harus 0-100" |
| Input nilai negatif | -10 | Error: "Nilai harus 0-100" |
| Total bobot ≠ 100% | UH:40, UTS:30, UAS:25 | Error: "Total bobot harus 100%" |
| Input nilai untuk siswa bukan di kelas guru | studentId dari kelas lain | Error: "Unauthorized" |
| Edit nilai yang sudah dikunci | PUT /api/grades/{id} saat locked | Error: "Nilai sudah dikunci" |
| Generate rapor dengan nilai tidak lengkap | Beberapa mata pelajaran kosong | Warning: "Nilai belum lengkap untuk 3 siswa" |
| Input predikat Sikap invalid | "E" | Error: "Predikat harus SB/B/C/K" |

### 6. Security Testing

**Test Cases:**
- [ ] Authorization: Guru tidak bisa input nilai untuk kelas yang tidak diajar
- [ ] Authorization: Guru tidak bisa lihat nilai kelas lain
- [ ] Authorization: Orang tua hanya bisa lihat rapor anaknya sendiri
- [ ] SQL Injection: Test input fields dengan SQL injection payloads
- [ ] XSS: Test input fields dengan XSS payloads
- [ ] CSRF: Test POST/PUT requests tanpa CSRF token
- [ ] File Upload Security: Test upload PDF dengan malicious content
- [ ] Rate Limiting: Test bulk API calls (prevent abuse)

---

## 📅 Sprint Planning

### Sprint 1: Foundation & Configuration (2 weeks) - 26 SP

**Goals:**
- Setup database schema dan migrations
- Implement grade configuration service
- Basic grade input API

**Stories:**
- US-GRD-001: Konfigurasi Bobot Nilai (5 SP)
- Database schema implementation (8 SP)
- Grade Calculator module (8 SP)
- API endpoints untuk grade configuration (5 SP)

**Deliverables:**
- Database tables created
- Grade configuration UI completed
- Grade calculator tested dan validated

---

### Sprint 2: Grade Input (2 weeks) - 23 SP

**Goals:**
- Implement grade input untuk semua komponen
- Validation dan error handling

**Stories:**
- US-GRD-002: Input Nilai UH (8 SP)
- US-GRD-003: Input Nilai UTS & UAS (5 SP)
- US-GRD-004: Input Nilai Praktik (5 SP)
- US-GRD-012: Validasi Perhitungan Nilai (5 SP)

**Deliverables:**
- Grade input UI untuk UH, UTS, UAS, Praktik
- Auto-calculation untuk nilai Pengetahuan & Keterampilan
- Validation completed

---

### Sprint 3: Grade Management (2 weeks) - 23 SP

**Goals:**
- Grade summary dan management features
- Sikap input dan edit capabilities

**Stories:**
- US-GRD-005: Input Nilai Sikap (5 SP)
- US-GRD-006: Lihat Rangkuman Nilai Siswa (8 SP)
- US-GRD-009: Kunci Nilai (5 SP)
- US-GRD-010: Edit Nilai (5 SP)

**Deliverables:**
- Grade summary page completed
- Sikap input UI completed
- Grade locking mechanism implemented

---

### Sprint 4: Report Card Generation (2 weeks) - 18 SP

**Goals:**
- PDF rapor generation
- Template K13 implementation

**Stories:**
- US-GRD-007: Generate Rapor PDF (13 SP)
- US-GRD-011: Export Nilai ke Excel (5 SP)

**Deliverables:**
- PDF generation working
- Rapor template K13 implemented
- Bulk generation untuk multiple siswa
- Excel export functionality

---

### Sprint 5: Parent Portal & Polish (2 weeks) - 8 SP

**Goals:**
- Parent portal untuk view/download rapor
- Testing dan bug fixes

**Stories:**
- US-GRD-008: Orang Tua Lihat Rapor (8 SP)

**Deliverables:**
- Parent portal completed
- Mobile responsive untuk semua pages
- E2E testing completed
- Bug fixes dan performance optimization

---

**Total Duration:** 10 weeks (5 sprints)  
**Total Story Points:** 98 SP

---

## ⚠️ Risks & Mitigation

### High Priority Risks

| Risk | Impact | Probability | Mitigation Strategy |
|------|--------|-------------|---------------------|
| **PDF Generation Performance Issue** | High | Medium | - Use efficient library (puppeteer dengan headless Chrome)<br>- Implement queue system untuk bulk generation<br>- Cache template assets<br>- Optimize HTML/CSS template |
| **Formula Perhitungan Tidak Sesuai K13** | High | Low | - Validasi formula dengan tim kurikulum<br>- Unit tests comprehensive untuk semua scenarios<br>- User acceptance testing dengan guru |
| **Data Loss saat Input Nilai** | High | Low | - Implement auto-save draft setiap 30 detik<br>- Transaction management untuk batch updates<br>- Backup database daily<br>- Audit log untuk semua perubahan |

### Medium Priority Risks

| Risk | Impact | Probability | Mitigation Strategy |
|------|--------|-------------|---------------------|
| **Integrasi dengan Attendance System Delayed** | Medium | Medium | - Implement manual input absensi di rapor sebagai fallback<br>- Design loose coupling (dapat berfungsi tanpa attendance system) |
| **Cloud Storage Unavailable** | Medium | Low | - Implement local storage fallback<br>- Retry mechanism dengan exponential backoff<br>- Error handling yang informatif |
| **Large File Size untuk PDF** | Medium | Medium | - Optimize images dan assets<br>- Compress PDF output<br>- Limit PDF size < 1MB per rapor |

### Low Priority Risks

| Risk | Impact | Probability | Mitigation Strategy |
|------|--------|-------------|---------------------|
| **User Confusion dengan UI** | Low | Medium | - User testing sebelum release<br>- Tutorial video<br>- Tooltips dan help text<br>- Onboarding wizard |
| **Browser Compatibility Issues** | Low | Low | - Test di major browsers (Chrome, Firefox, Safari, Edge)<br>- Polyfills untuk browser lama<br>- Graceful degradation |

---

## 📊 Success Metrics & KPIs

### Business Metrics

| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| **Time to Generate Report Card** | 70% reduction | Compare average time: Before (manual) vs After (system) |
| **User Adoption Rate** | 90% within 1 semester | % guru yang aktif menggunakan sistem |
| **Parent Portal Usage** | 80% of parents | % orang tua yang login dan view rapor |
| **Error Rate** | < 1% | % rapor yang perlu koreksi setelah generation |

### Technical Metrics

| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| **API Response Time (P95)** | < 500ms | APM monitoring (New Relic, Datadog) |
| **PDF Generation Time** | < 3s per rapor | Logging dan analytics |
| **System Uptime** | 99.5% | Monitoring tools |
| **Test Coverage** | > 80% | Jest/Pytest coverage reports |
| **Bug Density** | < 5 bugs/1000 LOC | Issue tracking system |

### User Satisfaction Metrics

| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| **User Satisfaction Score (CSAT)** | > 4.5/5 | Survey setelah 1 semester penggunaan |
| **Net Promoter Score (NPS)** | > 50 | Survey ke guru dan orang tua |
| **Support Tickets** | < 10/week | Helpdesk tracking system |

### Operational Metrics

| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| **Data Accuracy** | 100% | Audit nilai manual vs sistem |
| **Failed PDF Generations** | < 0.5% | Error logs dan monitoring |
| **Concurrent Users** | Support 50+ | Load testing results |

---

## 📝 Notes & Assumptions

### Assumptions
1. Sekolah menggunakan Kurikulum 2013 (K13) untuk penilaian
2. Guru familiar dengan komponen penilaian K13 (UH, UTS, UAS, Praktik, Sikap)
3. Infrastruktur internet stabil untuk upload/download PDF
4. Orang tua memiliki akses ke portal (email/smartphone)
5. Data siswa, guru, kelas sudah lengkap dari EPIC-002 dan EPIC-003
6. Cloud storage (S3/GCS) tersedia untuk menyimpan PDF rapor
7. Bobot nilai dapat berbeda per mata pelajaran (misal: Penjaskes lebih besar bobot Praktik)

### Technical Decisions
1. **PDF Library:** Puppeteer (untuk flexibility dan kualitas output)
2. **Storage:** AWS S3 (scalability dan cost-effective)
3. **Calculation:** Server-side (untuk consistency dan security)
4. **Real-time Updates:** WebSocket untuk progress bar saat bulk generation
5. **Mobile Strategy:** Responsive web (tidak native app untuk fase ini)

### Business Rules
1. Nilai dapat diedit sebelum di-lock
2. Lock dilakukan setelah rapor digenerate dan di-publish
3. Unlock memerlukan approval dari Admin/Kepala Sekolah
4. Rapor hanya visible untuk orang tua setelah status = 'published'
5. Audit log untuk semua perubahan nilai (siapa, kapan, nilai lama, nilai baru)

### Future Enhancements (Out of Scope untuk MVP)
- Analytics dashboard untuk guru (trend nilai, ranking, perbandingan antar kelas)
- Grafik visualisasi perkembangan nilai siswa over time
- Export rapor ke format lain (Word, HTML)
- Digital signature untuk rapor PDF
- Sistem remedial otomatis untuk nilai di bawah KKM
- Parent-teacher messaging untuk diskusi nilai
- Multi-language support (English version)
- Integration dengan LMS (Learning Management System)

---

## 🔍 Review & Refinement

### Pre-Development Checklist
- [ ] Epic reviewed dengan Product Owner
- [ ] User stories prioritized dan estimated
- [ ] Technical architecture reviewed dengan Tech Lead
- [ ] UI/UX mockups approved oleh Designer
- [ ] Dependencies confirmed dengan tim lain
- [ ] Database schema reviewed dengan DBA
- [ ] Security requirements validated
- [ ] Performance benchmarks defined
- [ ] Test strategy approved oleh QA Lead

### During Development Reviews
- [ ] Sprint Demo setiap 2 minggu
- [ ] Daily standups untuk blocker resolution
- [ ] Code review untuk semua PRs
- [ ] Weekly stakeholder sync
- [ ] Mid-sprint check-in dengan Product Owner

### Post-Development
- [ ] UAT dengan guru dan wali kelas (minimal 5 users)
- [ ] Load testing dan performance validation
- [ ] Security audit
- [ ] Documentation review
- [ ] Training materials prepared
- [ ] Go/No-Go meeting dengan stakeholders
- [ ] Rollout plan finalized

---

## ✅ Epic Checklist

### Phase 1: Planning & Design
- [ ] Epic overview approved
- [ ] User stories defined dan estimated
- [ ] Technical architecture designed
- [ ] UI/UX mockups created
- [ ] Database schema designed
- [ ] API contracts defined
- [ ] Dependencies mapped
- [ ] Risks identified dan mitigation planned

### Phase 2: Development
- [ ] Sprint 1: Foundation & Configuration completed
- [ ] Sprint 2: Grade Input completed
- [ ] Sprint 3: Grade Management completed
- [ ] Sprint 4: Report Card Generation completed
- [ ] Sprint 5: Parent Portal completed
- [ ] All unit tests passing
- [ ] All integration tests passing
- [ ] Code coverage > 80%

### Phase 3: Testing
- [ ] E2E tests completed
- [ ] Performance tests passed
- [ ] Security tests passed
- [ ] UAT completed dengan users
- [ ] Bug fixes completed
- [ ] Regression testing passed

### Phase 4: Deployment
- [ ] Deployed to staging
- [ ] Smoke tests passed di staging
- [ ] Data migration executed (if any)
- [ ] Production deployment
- [ ] Monitoring configured
- [ ] Rollback plan ready

### Phase 5: Post-Launch
- [ ] User training conducted
- [ ] Documentation published
- [ ] Support team briefed
- [ ] Metrics tracking configured
- [ ] Post-launch review meeting
- [ ] Feedback collection dari users
- [ ] Iteration plan untuk improvements

---

## 📋 Change Log

| Date | Version | Author | Changes |
|------|---------|--------|---------|
| 2025-12-13 | 1.0 | Product Team | Initial epic creation |

---

**Last Updated:** 2025-12-13  
**Next Review Date:** 2025-12-20  
**Status:** Ready for Development

---

## 📞 Contacts & Escalation

**Epic Owner:** Product Team  
**Technical Lead:** [TBD]  
**QA Lead:** [TBD]  
**Stakeholders:**
- Kepala Sekolah
- Wakil Kurikulum
- Wali Kelas
- Guru Mata Pelajaran
- Orang Tua (end users)

**Escalation Path:**
1. Daily Standups → Scrum Master
2. Blockers → Tech Lead
3. Scope Changes → Product Owner
4. Major Risks → Epic Owner
5. Critical Issues → Steering Committee
