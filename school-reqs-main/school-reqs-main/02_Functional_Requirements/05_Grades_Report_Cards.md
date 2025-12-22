# Modul 5: Grades & Report Cards (Nilai & Rapor)

## 📋 Overview

Modul ini menangani input nilai siswa (UH, UTS, UAS, sikap, praktik), perhitungan nilai akhir, dan generate rapor digital sesuai Kurikulum 2013 (K13).

**Module Code:** `GRD`  
**Priority:** P1 (High)  
**Dependencies:** Authentication, Student Management, Teacher Management

---

## 🎯 Tujuan

1. Digitalisasi input dan penyimpanan nilai siswa
2. Otomasi perhitungan nilai akhir berdasarkan bobot K13
3. Generate rapor PDF otomatis per semester
4. Transparansi nilai untuk orang tua (akses online)
5. Rekap nilai untuk analisis performa kelas/siswa
6. Arsip digital rapor multi-tahun

---

## 📖 User Stories

### US-GRD-001: Input Nilai Harian (UH)
```
Sebagai Guru Mata Pelajaran,
Saya ingin menginput nilai harian/ulangan harian untuk siswa,
Sehingga nilai terdokumentasi dan masuk perhitungan rapor
```

**Acceptance Criteria:**
- ✅ Pilih kelas, mata pelajaran, semester
- ✅ Input jenis penilaian: UH (ke-1, ke-2, dst), judul UH
- ✅ List siswa dengan input field nilai (0-100)
- ✅ Validasi: nilai harus 0-100
- ✅ Simpan nilai
- ✅ Nilai bisa diedit sampai rapor di-finalize

---

### US-GRD-002: Input Nilai UTS & UAS
```
Sebagai Guru Mata Pelajaran,
Saya ingin menginput nilai UTS dan UAS untuk siswa,
Sehingga nilai ujian tengah dan akhir semester tercatat
```

**Acceptance Criteria:**
- ✅ Similar dengan input UH, tapi kategori: UTS atau UAS
- ✅ UTS: tengah semester, UAS: akhir semester
- ✅ Satu kali input per semester per mata pelajaran

---

### US-GRD-003: Input Nilai Praktik/Portofolio
```
Sebagai Guru Mata Pelajaran,
Saya ingin menginput nilai praktik atau portofolio siswa,
Sehingga komponen penilaian praktik terakomodir
```

**Acceptance Criteria:**
- ✅ Input nilai praktik (0-100) per siswa
- ✅ Bisa multiple input praktik (Praktik 1, 2, dst)
- ✅ Rata-rata praktik dihitung otomatis

---

### US-GRD-004: Input Nilai Sikap (Spiritual & Sosial)
```
Sebagai Guru Kelas,
Saya ingin menginput nilai sikap spiritual dan sosial siswa,
Sehingga nilai sikap sesuai K13 tersedia untuk rapor
```

**Acceptance Criteria:**
- ✅ Pilih kelas, semester
- ✅ Per siswa: input nilai Spiritual dan Sosial (dropdown predikat: A/B/C/D)
- ✅ Input deskripsi sikap (textarea, optional, max 200 karakter)
- ✅ Simpan

---

### US-GRD-005: Lihat Rekap Nilai Siswa
```
Sebagai Guru/TU/Orang Tua,
Saya ingin melihat rekap nilai siswa per mata pelajaran,
Sehingga saya tahu perkembangan nilai siswa
```

**Acceptance Criteria:**
- ✅ Filter: siswa, semester, mata pelajaran
- ✅ Table: Jenis Penilaian, Nilai, Bobot, Keterangan
- ✅ Summary: Nilai Akhir per Mata Pelajaran (calculate berdasarkan bobot)
- ✅ Chart: visualisasi nilai per mata pelajaran (bar/radar chart)

---

### US-GRD-006: Generate Rapor Semester
```
Sebagai TU/Guru Kelas,
Saya ingin generate rapor siswa untuk semester tertentu,
Sehingga rapor PDF siap dicetak atau dibagikan ke orang tua
```

**Acceptance Criteria:**
- ✅ Pilih semester, tahun ajaran, kelas atau siswa
- ✅ Preview rapor (template K13)
- ✅ Finalize rapor (lock nilai, tidak bisa edit lagi)
- ✅ Generate PDF rapor
- ✅ Rapor tersimpan di sistem, orang tua bisa download

---

### US-GRD-007: Orang Tua Lihat Rapor Online
```
Sebagai Orang Tua,
Saya ingin melihat rapor anak saya secara online,
Sehingga saya tahu perkembangan akademik anak tanpa harus ke sekolah
```

**Acceptance Criteria:**
- ✅ Dashboard: list rapor per semester (Semester 1, 2, dst)
- ✅ Click rapor → view online (HTML) atau download PDF
- ✅ Tampilkan nilai per mata pelajaran, rata-rata, ranking (optional)
- ✅ Mobile-friendly

---

### US-GRD-008: Konfigurasi Bobot Nilai
```
Sebagai Admin/TU,
Saya ingin mengatur bobot komponen penilaian per mata pelajaran,
Sehingga perhitungan nilai akhir sesuai kebijakan sekolah
```

**Acceptance Criteria:**
- ✅ CRUD bobot komponen: UH, UTS, UAS, Praktik, Sikap
- ✅ Total bobot = 100%
- ✅ Bobot bisa berbeda per mata pelajaran (opsional)
- ✅ Bobot tersimpan per semester/tahun ajaran

---

## ⚙️ Functional Requirements

### FR-GRD-001: Grade Components Configuration
**Priority:** Must Have  
**Description:** Sistem menyimpan konfigurasi komponen nilai sesuai K13.

**Details:**
**Komponen Penilaian K13 untuk SD:**

1. **Pengetahuan (KI-3):**
   - Ulangan Harian (UH): bisa multiple (UH1, UH2, UH3, dst)
   - Ulangan Tengah Semester (UTS): 1x per semester
   - Ulangan Akhir Semester (UAS): 1x per semester
   - Bobot default: UH 40%, UTS 30%, UAS 30% (configurable)

2. **Keterampilan (KI-4):**
   - Praktik/Kinerja: bisa multiple
   - Portofolio/Proyek: bisa multiple
   - Bobot: rata-rata semua nilai praktik

3. **Sikap (KI-1 & KI-2):**
   - Sikap Spiritual (KI-1): predikat A/B/C/D + deskripsi
   - Sikap Sosial (KI-2): predikat A/B/C/D + deskripsi
   - Tidak pakai angka, langsung predikat

**Bobot Configuration:**
- Default bobot bisa di-set di settings
- Guru/TU bisa override bobot per mata pelajaran (advanced)
- Validasi: sum(bobot) = 100%

**Predikat Mapping (K13):**
- Nilai 90-100: A (Sangat Baik)
- Nilai 80-89: B (Baik)
- Nilai 70-79: C (Cukup)
- Nilai < 70: D (Perlu Bimbingan)

---

### FR-GRD-002: Input Daily Assessment (UH)
**Priority:** Must Have  
**Description:** Guru dapat input nilai ulangan harian siswa.

**Details:**
**Input:**
- Pilih Kelas
- Pilih Mata Pelajaran (based on guru's teaching assignment)
- Pilih Semester & Tahun Ajaran
- Jenis Penilaian: Ulangan Harian (UH)
- Judul/Deskripsi UH: e.g., "UH 1: Perkalian", "UH 2: Pecahan"
- Tanggal Penilaian (date picker)
- List siswa dengan input field nilai (0-100)

**Process:**
1. Guru buka halaman "Input Nilai"
2. Pilih kelas, mata pelajaran, semester
3. Click "Tambah Penilaian Baru" → pilih jenis: UH
4. Input judul & tanggal
5. Sistem load siswa di kelas
6. Input nilai per siswa (number input)
7. Validasi: nilai 0-100
8. Quick action: "Copy nilai dari siswa lain" (untuk kasus remedial/multiple attempts)
9. Click "Simpan"
10. Success notification

**Business Logic:**
- Guru hanya bisa input nilai untuk kelas & mata pelajaran yang diajar
- Bisa ada multiple UH per semester (UH1, UH2, ..., UHn)
- Nilai bisa diedit sampai rapor di-finalize
- Jika ada siswa yang tidak ikut UH (sakit/izin), nilai bisa dikosongkan atau input "0" dengan keterangan

---

### FR-GRD-003: Input Mid-Term & Final Exam (UTS/UAS)
**Priority:** Must Have  
**Description:** Guru dapat input nilai UTS dan UAS.

**Details:**
- Similar dengan FR-GRD-002, tapi jenis penilaian: UTS atau UAS
- UTS: 1x input per semester (tengah semester)
- UAS: 1x input per semester (akhir semester)
- Validasi: tidak bisa input UTS/UAS duplikat untuk semester yang sama

---

### FR-GRD-004: Input Skills Assessment (Praktik/Portofolio)
**Priority:** Must Have  
**Description:** Guru dapat input nilai keterampilan (praktik, portofolio, proyek).

**Details:**
**Input:**
- Jenis: Praktik/Kinerja atau Portofolio/Proyek
- Judul: e.g., "Praktik Olahraga: Lari 100m", "Proyek: Membuat Poster"
- Tanggal
- List siswa dengan nilai (0-100)

**Business Logic:**
- Bisa multiple input praktik per semester
- Nilai akhir Keterampilan = rata-rata semua nilai praktik

---

### FR-GRD-005: Input Attitude Assessment (Sikap)
**Priority:** Must Have  
**Description:** Guru kelas dapat input nilai sikap spiritual dan sosial.

**Details:**
**Input:**
- Pilih Kelas (guru kelas)
- Pilih Semester
- Per siswa:
  - Sikap Spiritual (KI-1): dropdown (A/B/C/D)
  - Deskripsi Spiritual: textarea (optional, max 200 char, e.g., "Rajin berdoa sebelum belajar")
  - Sikap Sosial (KI-2): dropdown (A/B/C/D)
  - Deskripsi Sosial: textarea (optional, e.g., "Sopan dan suka menolong teman")

**Process:**
1. Guru kelas buka "Input Nilai Sikap"
2. Pilih kelas & semester
3. List semua siswa di kelas
4. Per siswa: pilih predikat & input deskripsi
5. Quick action: "Terapkan Predikat Sama ke Semua" (untuk efficiency)
6. Simpan

**Business Logic:**
- Hanya guru kelas yang bisa input nilai sikap (tidak per mata pelajaran)
- Sikap bersifat general (tidak per mapel)
- Deskripsi optional tapi recommended (untuk rapor naratif)

---

### FR-GRD-006: Grade Calculation & Final Grade
**Priority:** Must Have  
**Description:** Sistem otomatis menghitung nilai akhir berdasarkan bobot komponen.

**Details:**
**Calculation Formula:**

**Nilai Pengetahuan:**
```
Rata_UH = sum(semua_nilai_UH) / jumlah_UH
Nilai_Pengetahuan = (Rata_UH × bobot_UH) + (UTS × bobot_UTS) + (UAS × bobot_UAS)

Contoh (bobot default):
Rata_UH = (80 + 85 + 90) / 3 = 85
Nilai_Pengetahuan = (85 × 0.4) + (80 × 0.3) + (90 × 0.3)
                  = 34 + 24 + 27
                  = 85
```

**Nilai Keterampilan:**
```
Nilai_Keterampilan = sum(semua_nilai_praktik) / jumlah_praktik

Contoh:
Praktik 1: 85, Praktik 2: 90, Praktik 3: 80
Nilai_Keterampilan = (85 + 90 + 80) / 3 = 85
```

**Nilai Akhir per Mata Pelajaran:**
```
Nilai_Akhir = (Nilai_Pengetahuan + Nilai_Keterampilan) / 2

Contoh:
Nilai_Akhir = (85 + 85) / 2 = 85
Predikat = B (Baik)
```

**Predikat:**
- 90-100: A
- 80-89: B
- 70-79: C
- < 70: D

**Process:**
- Calculation dilakukan real-time saat view rekap nilai
- Nilai disimpan saat generate rapor (snapshot)
- Jika ada perubahan nilai setelah rapor di-generate, rapor harus di-regenerate

**Business Logic:**
- Jika ada komponen yang belum diisi (e.g., UAS belum ada), nilai akhir tidak bisa dihitung (tampilkan "Belum Lengkap")
- Minimal komponen: minimal 1 UH, 1 UTS, 1 UAS, 1 Praktik untuk dapat generate rapor

---

### FR-GRD-007: Grade Summary & Report View
**Priority:** Must Have  
**Description:** User dapat melihat rekap nilai siswa per semester.

**Details:**
**Filters:**
- Siswa (dropdown atau search)
- Semester & Tahun Ajaran
- Mata Pelajaran (dropdown: All/Specific)

**Output:**

**Table: Rekap Nilai per Mata Pelajaran**
| Mata Pelajaran | Nilai Pengetahuan | Nilai Keterampilan | Nilai Akhir | Predikat |
|----------------|-------------------|-------------------|-------------|----------|
| Matematika     | 85                | 85                | 85          | B        |
| Bahasa Indonesia | 90              | 88                | 89          | B        |
| IPA            | 75                | 80                | 77.5        | C        |
| ...            | ...               | ...               | ...         | ...      |

**Summary:**
- Rata-rata Nilai: (sum nilai_akhir) / jumlah_mapel
- Ranking di Kelas: #3 dari 30 siswa (optional, configurable)
- Status: "Layak Naik Kelas" atau "Perlu Bimbingan" (based on rata-rata dan kehadiran)

**Chart:**
- Radar Chart: nilai akhir per mata pelajaran (visual performa)
- Bar Chart: perbandingan nilai pengetahuan vs keterampilan

**Sikap:**
- Section terpisah:
  - Sikap Spiritual: Predikat + Deskripsi
  - Sikap Sosial: Predikat + Deskripsi

**Access Control:**
- Guru: hanya siswa di kelas yang diajar, detail nilai per komponen (UH, UTS, UAS)
- Orang Tua: hanya anak sendiri, tampilkan nilai akhir saja (tidak detail breakdown, kecuali diminta)
- TU & Principal: semua siswa, full detail

---

### FR-GRD-008: Generate Report Card (Rapor)
**Priority:** Must Have  
**Description:** Sistem dapat generate rapor semester dalam format PDF sesuai template K13.

**Details:**
**Process:**
1. TU/Guru Kelas buka "Generate Rapor"
2. Pilih:
   - Semester & Tahun Ajaran
   - Kelas (All/Specific)
   - Siswa (All/Specific) - untuk generate individual atau bulk
3. Click "Preview Rapor"
4. Sistem:
   - Validasi: semua nilai wajib sudah diinput (minimal UH, UTS, UAS, Praktik, Sikap)
   - Calculate nilai akhir per mata pelajaran
   - Load template rapor K13
   - Populate data: biodata siswa, nilai per mapel, sikap, kehadiran, catatan wali kelas
5. Tampilkan preview rapor (HTML atau PDF preview)
6. User review, jika ok → Click "Finalize & Generate"
7. Sistem:
   - Set rapor status: Finalized (lock nilai)
   - Generate PDF rapor per siswa
   - Simpan PDF ke storage
   - Update database: rapor_id, file_path, generated_at
8. Success notification: "{X} rapor berhasil di-generate"
9. Option: Download ZIP (jika bulk), Kirim ke Orang Tua (email/WhatsApp), Print

**Rapor Template (K13):**
- Cover: Logo Sekolah, Nama Siswa, Kelas, Semester, Tahun Ajaran
- Halaman 1: Biodata Siswa (Nama, NIS, NISN, Kelas, Wali Kelas)
- Halaman 2-3: Tabel Nilai:
  - Kolom: No, Mata Pelajaran, Pengetahuan (Nilai & Predikat), Keterampilan (Nilai & Predikat)
  - Baris per mata pelajaran
- Halaman 4: Nilai Sikap (Spiritual & Sosial dengan deskripsi)
- Halaman 5: Ekstrakurikuler (jika ada, optional MVP)
- Halaman 6: Ketidakhadiran (Sakit: X hari, Izin: Y hari, Alpha: Z hari)
- Halaman 7: Catatan Wali Kelas (textarea input manual, e.g., "Perlu meningkatkan fokus di kelas")
- Halaman 8: Tanda Tangan (Wali Kelas, Kepala Sekolah, Orang Tua) dengan tempat tanda tangan + tanggal

**Business Logic:**
- Rapor yang sudah finalized tidak bisa diedit nilai lagi (immutable)
- Jika ada kesalahan, TU bisa "Unlock Rapor" (permission required, log audit trail)
- Setelah unlock, nilai bisa diedit, harus regenerate rapor

---

### FR-GRD-009: Parent View Report Card Online
**Priority:** Must Have  
**Description:** Orang tua dapat melihat rapor anak secara online.

**Details:**
**Dashboard Rapor (Parent):**
- List rapor per semester: "Rapor Semester 1 - 2024/2025", "Rapor Semester 2 - 2024/2025", dst
- Per rapor card:
  - Semester & Tahun Ajaran
  - Status: Draft/Finalized
  - Rata-rata Nilai
  - Ranking (optional)
  - Action: "Lihat Rapor" (view online) atau "Download PDF"

**View Online:**
- Tampilan HTML responsive (mobile-friendly)
- Layout similar dengan PDF rapor, tapi optimize untuk screen
- Scroll per section
- Option download PDF di footer

**Download PDF:**
- Direct download rapor PDF yang sudah di-generate
- Filename: `Rapor_{Nama_Siswa}_{Semester}_{Tahun}.pdf`

**Access Control:**
- Orang tua hanya bisa lihat rapor anak sendiri
- Rapor hanya bisa diakses setelah finalized (tidak bisa lihat draft)

---

### FR-GRD-010: Grade Weight Configuration
**Priority:** Should Have  
**Description:** Admin dapat mengatur bobot komponen nilai per mata pelajaran.

**Details:**
**Default Weight (Global):**
- UH: 40%
- UTS: 30%
- UAS: 30%
- (Praktik tidak pakai bobot, langsung rata-rata)

**Custom Weight per Mata Pelajaran:**
- Contoh: Mata Pelajaran Olahraga → bobot Praktik lebih besar
  - UH: 20%, UTS: 20%, UAS: 20%, Praktik: 40%
- Mata Pelajaran Matematika → standar
  - UH: 40%, UTS: 30%, UAS: 30%

**CRUD Operations:**
- Create: set bobot untuk mata pelajaran tertentu
- Read: view bobot per mata pelajaran
- Update: edit bobot (simpan history)
- Delete: hapus custom bobot (kembali ke default)

**Validation:**
- Sum(bobot) = 100%
- Bobot tidak boleh negatif atau > 100%

**Business Logic:**
- Perubahan bobot hanya berlaku untuk semester berikutnya (tidak retroactive)
- Untuk semester yang sudah ada nilai, bobot tidak bisa diubah (locked)

---

### FR-GRD-011: Bulk Grade Input (Excel Import)
**Priority:** Could Have (Nice to Have)  
**Description:** Guru dapat upload nilai dari Excel untuk input cepat.

**Details:**
- Download template Excel (header: NIS, Nama, Nilai)
- Guru isi nilai di Excel
- Upload file
- Sistem parse & validasi
- Preview data before import
- Click "Import" → nilai tersimpan

**Business Logic:**
- Validasi: NIS harus valid, Nilai 0-100
- Jika ada error, tampilkan error message per row
- Import hanya untuk satu jenis penilaian (e.g., UH 1) per upload

---

### FR-GRD-012: Grade History & Archive
**Priority:** Should Have  
**Description:** Sistem menyimpan history nilai dan rapor multi-tahun.

**Details:**
- Nilai dan rapor tersimpan per semester/tahun ajaran
- Orang tua/TU bisa akses rapor tahun lalu
- List rapor: "Rapor Kelas 1 Semester 1", "Rapor Kelas 1 Semester 2", ..., "Rapor Kelas 6 Semester 2"
- Archive rapor saat siswa lulus/mutasi (tidak delete)

---

## 📏 Business Rules

### BR-GRD-001: Minimum Assessment Requirements
- Minimal 1 UH, 1 UTS, 1 UAS, 1 Praktik untuk bisa generate rapor
- Jika ada komponen yang kosong, rapor tidak bisa di-finalize

### BR-GRD-002: Grade Lock
- Setelah rapor finalized, nilai tidak bisa diedit
- Unlock hanya bisa oleh TU/Super Admin dengan audit trail

### BR-GRD-003: Grade Scale
- Semua nilai input menggunakan skala 0-100
- Predikat A/B/C/D di-map otomatis saat generate rapor
- Sikap langsung input predikat (tidak pakai angka)

### BR-GRD-004: Ranking Calculation
- Ranking berdasarkan rata-rata nilai akhir (descending)
- Jika ada nilai sama, ranking sama (e.g., ranking 1, 1, 3)
- Ranking optional (bisa disable di settings untuk kebijakan sekolah tertentu)

### BR-GRD-005: Remedial (Future Phase)
- Siswa dengan nilai < KKM (Kriteria Ketuntasan Minimal, e.g., 70) harus remedial
- Nilai remedial replace nilai awal (atau ambil yang lebih tinggi)
- Implementasi penuh di Phase 2

---

## ✅ Validation Rules

### VR-GRD-001: Grade Input Form

**Nilai:**
- Required (kecuali siswa tidak hadir, bisa kosong atau 0 dengan keterangan)
- Number, min: 0, max: 100
- Integer atau decimal (max 2 digit, e.g., 85.5)
- Error: "Nilai wajib diisi", "Nilai harus antara 0-100"

**Judul Penilaian:**
- Required
- Min 3 karakter, max 100 karakter
- Error: "Judul penilaian wajib diisi"

**Tanggal Penilaian:**
- Required
- Valid date, tidak boleh future date > 7 hari
- Error: "Tanggal penilaian tidak boleh lebih dari 7 hari ke depan"

---

### VR-GRD-002: Attitude Assessment Form

**Predikat:**
- Required
- Value: A, B, C, D
- Error: "Predikat wajib dipilih"

**Deskripsi:**
- Optional
- Max 200 karakter
- Error: "Deskripsi maksimal 200 karakter"

---

### VR-GRD-003: Weight Configuration Form

**Bobot Komponen:**
- Required
- Number, min: 0, max: 100
- Sum(semua bobot) = 100%
- Error: "Bobot wajib diisi", "Total bobot harus 100%"

---

## 🎨 UI/UX Requirements

### Grade Input Page (Guru)

**Layout:**
- Header: "Input Nilai" dengan breadcrumb (Kelas > Mata Pelajaran > Jenis Penilaian)
- Filter section:
  - Dropdown: Kelas
  - Dropdown: Mata Pelajaran
  - Dropdown: Semester
  - Button: "Tambah Penilaian Baru"
- List existing assessments (card atau table):
  - Per card: Judul (e.g., "UH 1: Perkalian"), Tanggal, Jumlah Siswa Dinilai, Action (View/Edit/Delete)
- Form input nilai (setelah click card atau "Tambah Baru"):
  - Judul & Tanggal
  - Table siswa: No, Foto, Nama, NIS, Input Nilai (number), Keterangan
  - Footer: Button "Simpan" dan "Batal"

**UX:**
- Quick actions:
  - "Tandai Semua {nilai}" (e.g., input 0 untuk semua jika belum dikerjakan)
  - "Copy dari..." (copy nilai dari penilaian lain, untuk remedial)
- Real-time validation: input field merah jika nilai > 100
- Auto-save draft setiap 30 detik (prevent data loss)
- Keyboard navigation: Tab untuk next siswa, Enter untuk save
- Show summary: Rata-rata Kelas, Nilai Tertinggi, Terendah

**Mobile:**
- Card-based input per siswa (scroll vertical)
- Large input field (touch-friendly)
- Swipe left/right untuk navigate antar siswa
- FAB (Floating Action Button) untuk save

---

### Grade Summary Page (Guru/Parent)

**Layout:**
- Header: Nama Siswa, Kelas, Semester (dengan foto siswa)
- Filter: Mata Pelajaran (dropdown: All/Specific)
- **Tab Navigation:**
  - Tab 1: Nilai per Mata Pelajaran (table)
  - Tab 2: Grafik/Chart
  - Tab 3: Sikap
  
- **Tab 1: Nilai**
  - Table dengan columns: Mata Pelajaran, Pengetahuan, Keterampilan, Nilai Akhir, Predikat
  - Summary row: Rata-rata Nilai, Ranking (optional)
  - Expandable row: click untuk lihat detail komponen (UH, UTS, UAS, Praktik)
  
- **Tab 2: Grafik**
  - Radar chart: performa per mata pelajaran
  - Bar chart: comparison pengetahuan vs keterampilan
  
- **Tab 3: Sikap**
  - Card: Sikap Spiritual (Predikat + Deskripsi)
  - Card: Sikap Sosial (Predikat + Deskripsi)

**UX:**
- Color coding predikat: A=hijau, B=biru, C=kuning, D=merah
- Tooltip untuk info tambahan (e.g., hover predikat → show range nilai)
- Responsive chart (resize untuk mobile)
- Print button: print-friendly layout

**Mobile:**
- Tab swipe navigation
- Card-based layout per mata pelajaran (collapsible)
- Chart full-width, scrollable

---

### Generate Report Card Page (TU/Guru Kelas)

**Layout:**
- **Step 1: Selection**
  - Form: Semester, Tahun Ajaran, Kelas, Siswa (All/Specific)
  - Button "Preview Rapor"
  
- **Step 2: Validation & Preview**
  - Validasi otomatis: cek komponen nilai lengkap
  - Jika ada siswa dengan nilai incomplete, tampilkan warning list
  - Preview rapor (iframe PDF atau HTML preview)
  - Per siswa: thumbnail rapor, nama, status (Complete/Incomplete)
  - Button "Kembali" dan "Finalize & Generate"
  
- **Step 3: Generate**
  - Progress bar: "Generating rapor... {X}/{Y}"
  - Success message dengan summary
  - Action: "Download All (ZIP)", "Kirim ke Orang Tua", "Print"

**UX:**
- Validation clear: jika incomplete, tampilkan list siswa & komponen yang kurang
- Preview rapor side-by-side dengan data source (untuk double-check)
- Bulk action: checkbox untuk select siswa yang akan di-generate (jika tidak semua)
- Success animation setelah generate selesai

**Mobile:**
- Step-by-step wizard (1 step per screen)
- Preview rapor full-screen (swipeable untuk multi siswa)
- Download individual (tidak support ZIP di mobile, download satu per satu)

---

### Parent Report Card View

**Layout:**
- Dashboard: "Rapor Anak Saya"
- Grid/List cards: per semester
  - Card content:
    - Icon semester (1/2)
    - Semester & Tahun Ajaran
    - Foto anak (thumbnail)
    - Rata-rata Nilai (large text)
    - Ranking (badge, e.g., "#3 dari 30")
    - Status: "Sudah Tersedia" atau "Belum Tersedia"
    - Action: "Lihat Rapor" (button)
    
- View Rapor Online:
  - Full-screen HTML view (scrollable)
  - Section per halaman rapor
  - Sticky header: Nama Siswa, Semester, Download PDF button
  - Footer: Copyright sekolah

**UX:**
- Card highlight rapor terbaru (border, shadow)
- Status badge: Tersedia (hijau), Draft (kuning), Belum (abu-abu)
- Download PDF: direct download, no popup
- Share button: copy link rapor untuk share (jika public link enabled)
- Empty state: "Rapor semester ini belum tersedia. Akan diupdate setelah finalisasi nilai."

**Mobile:**
- Card stack vertical
- Swipe down to refresh
- Rapor view optimize untuk portrait mode
- Zoom in/out untuk rapor PDF preview

---

### Rapor PDF Template Design

**Style:**
- Professional, formal layout
- Color scheme: sesuai branding sekolah (logo color)
- Font: readable (e.g., Times New Roman, Arial), size 10-12pt
- Border/line: clear separation per section
- Page size: A4 Portrait
- Margin: 2cm all sides
- Header per page: Logo & Nama Sekolah (watermark background optional)
- Footer per page: Page number, Generated date

**Sections:**
1. **Cover (Page 1):**
   - Logo sekolah (center, large)
   - Title: "RAPOR SISWA"
   - Subtitle: "Kurikulum 2013"
   - Info: Semester, Tahun Ajaran
   - Foto siswa (placeholder atau actual photo)
   - Nama siswa (large, bold)
   
2. **Biodata (Page 2):**
   - Table 2-column: Label | Value
   - Data: Nama, NIS, NISN, Tempat/Tanggal Lahir, Kelas, Wali Kelas
   
3. **Nilai Pengetahuan & Keterampilan (Page 3-4):**
   - Table: No, Mata Pelajaran, Pengetahuan (Nilai & Predikat), Keterampilan (Nilai & Predikat)
   - Summary row: Rata-rata Nilai
   - KKM reference (e.g., KKM = 70)
   
4. **Nilai Sikap (Page 5):**
   - Section: Sikap Spiritual (Predikat + Deskripsi naratif)
   - Section: Sikap Sosial (Predikat + Deskripsi naratif)
   
5. **Ekstrakurikuler (Page 6, optional MVP):**
   - Table: Nama Kegiatan, Predikat, Keterangan
   
6. **Ketidakhadiran (Page 7):**
   - Table: Sakit (X hari), Izin (Y hari), Alpha (Z hari)
   
7. **Catatan Wali Kelas (Page 8):**
   - Textarea: catatan naratif dari wali kelas
   - Example: "Ahmad menunjukkan perkembangan yang baik di bidang Matematika. Perlu meningkatkan fokus saat pelajaran IPA."
   
8. **Tanda Tangan (Page 9):**
   - 3-column table:
     - Column 1: Wali Kelas (Nama, NIP, Tanda Tangan, Tanggal)
     - Column 2: Kepala Sekolah (Nama, NIP, Tanda Tangan + Stempel, Tanggal)
     - Column 3: Orang Tua/Wali (Nama, Tanda Tangan, Tanggal)
   - Note: "Rapor ini dicetak otomatis dan sah tanpa tanda tangan basah" (jika digital signature not implemented)

---

## 🔗 Integration Points

### INT-GRD-001: Student Management Module
- Fetch student list per class untuk input nilai
- Fetch student biodata untuk rapor

### INT-GRD-002: Teacher Management Module
- Fetch teaching assignment (guru mengajar kelas & mata pelajaran apa)
- Permission: guru hanya bisa input nilai untuk mapel yang diajar

### INT-GRD-003: Attendance Module
- Fetch attendance summary untuk rapor (Sakit X hari, Izin Y hari, Alpha Z hari)

### INT-GRD-004: Notification Module
- Send notification ke orang tua saat rapor finalized & tersedia
- Reminder ke guru untuk melengkapi nilai sebelum deadline

### INT-GRD-005: Dashboard Module
- Provide grade summary untuk dashboard (rata-rata kelas, ranking, dll)

---

## 🧪 Test Scenarios

### TS-GRD-001: Input Daily Assessment (UH)
1. Login sebagai Guru Matematika
2. Go to "Input Nilai"
3. Pilih Kelas 1A, Mata Pelajaran Matematika, Semester 1
4. Click "Tambah Penilaian Baru" → UH
5. Input judul: "UH 1: Perkalian", tanggal: 10 Des 2025
6. List 30 siswa muncul
7. Input nilai per siswa: 80, 85, 90, ...
8. Click "Simpan"
9. **Expected:** Success notification, nilai tersimpan, muncul di list penilaian

### TS-GRD-002: Validation - Invalid Grade Value
1. Input nilai UH
2. Input nilai siswa: 150 (di luar range 0-100)
3. Tab/click keluar field
4. **Expected:** Field error merah, error message "Nilai harus antara 0-100", tidak bisa save

### TS-GRD-003: View Grade Summary (Parent)
1. Login sebagai Orang Tua (Ahmad Rizki)
2. Go to "Nilai Anak"
3. Pilih Semester 1 - 2024/2025
4. **Expected:**
   - Table nilai per mata pelajaran dengan nilai akhir & predikat
   - Rata-rata nilai: 85
   - Ranking: #3 dari 30
   - Chart performa mata pelajaran

### TS-GRD-004: Generate Report Card
1. Login sebagai TU
2. Go to "Generate Rapor"
3. Pilih Semester 1, Tahun 2024/2025, Kelas 1A
4. Click "Preview"
5. **Expected:** Validasi pass (semua nilai lengkap), preview 30 rapor
6. Click "Finalize & Generate"
7. **Expected:**
   - Progress bar showing "Generating 30 rapor..."
   - Success message "30 rapor berhasil di-generate"
   - PDF tersimpan, orang tua bisa akses

### TS-GRD-005: Incomplete Grade Validation
1. Generate rapor untuk siswa yang belum lengkap nilai UAS
2. **Expected:**
   - Validation failed
   - Warning list: "Siswa berikut belum lengkap nilai: Ahmad (Matematika: UAS belum diinput)"
   - Button "Finalize" disabled

### TS-GRD-006: Parent Download Report Card
1. Login sebagai Orang Tua
2. Go to "Rapor"
3. List rapor muncul: Semester 1 - 2024/2025 (Status: Tersedia)
4. Click "Lihat Rapor"
5. **Expected:** View online (HTML) dengan semua data rapor
6. Click "Download PDF"
7. **Expected:** Download file `Rapor_Ahmad_Rizki_Semester1_2024.pdf`

### TS-GRD-007: Edit Grade After Input
1. Guru input nilai UH 1, save
2. Kembali ke list penilaian
3. Click "Edit" pada UH 1
4. Ubah nilai beberapa siswa
5. Save
6. **Expected:** Nilai terupdate, success notification

### TS-GRD-008: Lock Grade After Finalize
1. Rapor sudah finalized
2. Guru coba edit nilai UH
3. **Expected:**
   - Form read-only atau error message
   - "Nilai tidak dapat diubah karena rapor sudah finalized. Hubungi TU untuk unlock."

### TS-GRD-009: Calculate Final Grade
1. Siswa Ahmad memiliki nilai:
   - UH: 80, 85, 90 (rata-rata: 85)
   - UTS: 80
   - UAS: 90
   - Praktik: 85, 90 (rata-rata: 87.5)
2. Sistem calculate (bobot default: UH 40%, UTS 30%, UAS 30%):
   - Pengetahuan: (85 × 0.4) + (80 × 0.3) + (90 × 0.3) = 85
   - Keterampilan: 87.5
   - Nilai Akhir: (85 + 87.5) / 2 = 86.25
   - Predikat: B
3. **Expected:** Rekap nilai tampilkan nilai akhir 86.25, predikat B

### TS-GRD-010: Bulk Grade Input via Excel
1. Guru download template Excel
2. Fill nilai untuk 30 siswa (column: NIS, Nama, Nilai)
3. Upload file
4. **Expected:** Preview data, validasi pass, import berhasil, 30 nilai tersimpan

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Input nilai UH (multiple per semester)
- ✅ Input nilai UTS & UAS
- ✅ Input nilai Praktik/Keterampilan
- ✅ Input nilai Sikap (Spiritual & Sosial dengan deskripsi)
- ✅ Grade calculation (otomatis hitung nilai akhir berdasarkan bobot)
- ✅ View grade summary per siswa (table, chart, sikap)
- ✅ Generate rapor PDF (template K13)
- ✅ Parent view rapor online & download PDF
- ✅ Finalize rapor (lock nilai)
- ✅ Grade weight configuration (default bobot)
- ✅ Access control (guru hanya mapel yang diajar, parent hanya anak sendiri)

### Should Have (MVP):
- ✅ Edit nilai (sebelum finalize)
- ✅ Unlock rapor (TU/Super Admin dengan audit trail)
- ✅ Ranking calculation (optional, configurable)
- ✅ Grade history & archive (multi-tahun)
- ✅ Bulk rapor generation (semua siswa sekaligus)
- ✅ Notification ke orang tua saat rapor tersedia
- ✅ Catatan wali kelas di rapor (textarea manual)

### Could Have (Nice to Have):
- ⬜ Bulk grade input via Excel
- ⬜ Custom weight per mata pelajaran
- ⬜ Remedial workflow (auto-detect nilai < KKM, remedial input, replace nilai)
- ⬜ Grade analytics (tren nilai per siswa, comparison antar kelas)
- ⬜ Ekstrakurikuler grade (optional di rapor)
- ⬜ E-signature untuk tanda tangan digital di rapor
- ⬜ Public link rapor (shareable link dengan password)

### Won't Have (Phase 2):
- ⬜ Real-time collaboration (multiple guru input nilai simultaneous)
- ⬜ Grade adjustment/curve (scale nilai jika terlalu rendah/tinggi)
- ⬜ AI-powered descriptive feedback generation
- ⬜ Integration dengan Dapodik (sync nilai)
- ⬜ Student self-assessment (siswa input refleksi)
- ⬜ Peer assessment (siswa nilai teman)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft - Ready for Review

