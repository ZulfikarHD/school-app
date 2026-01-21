# Grades & Report Cards - User Journeys

## Overview

Dokumen ini menjelaskan user journey untuk setiap role dalam module Grades & Report Cards, yaitu: Teacher (Guru Mapel & Wali Kelas), Admin/TU, Principal/Kepala Sekolah, dan Parent/Orang Tua.

---

## 🎯 Journey 1: Teacher Input Nilai UH

### Pengguna: Guru Mata Pelajaran

**Starting Point:** Dashboard Guru

**Steps:**
1. Klik menu "Penilaian" → "Input Nilai" di sidebar kiri
2. Sistem menampilkan halaman daftar penilaian yang sudah diinput
3. Klik tombol "Tambah Penilaian Baru" (hijau, pojok kanan atas)
4. **Step 1 - Pilih Detail Penilaian:**
   - Pilih Kelas dari dropdown (hanya kelas yang diajar)
   - Pilih Mata Pelajaran dari dropdown (hanya mapel yang diajar)
   - Pilih Semester (1/2)
   - Pilih Jenis Penilaian (UH/UTS/UAS/Praktik/Proyek)
   - Isi Nomor UH (jika UH, contoh: 1, 2, 3)
   - Isi Judul Penilaian (contoh: "UH 1: Perkalian Bilangan")
   - Pilih Tanggal Pelaksanaan
5. Klik "Lanjut" → Sistem load daftar siswa
6. **Step 2 - Input Nilai:**
   - Tabel berisi: No, NIS, Nama Siswa, Nilai (0-100), Catatan
   - Isi nilai untuk setiap siswa
   - Catatan opsional untuk keterangan khusus
7. Klik "Simpan Nilai"
8. Sistem validasi dan menyimpan
9. **Selesai:** Redirect ke halaman index dengan pesan sukses

**Alternative Flow:**
- Jika ada nilai tidak valid (>100 atau <0): Tampilkan error di field terkait
- Jika ingin set nilai sama untuk semua: Gunakan "Isi Semua" dengan input bulk

---

## 🎯 Journey 2: Wali Kelas Input Nilai Sikap

### Pengguna: Guru sebagai Wali Kelas

**Starting Point:** Dashboard Guru

**Steps:**
1. Klik menu "Penilaian" → "Nilai Sikap" di sidebar
2. Sistem cek: Apakah guru ini wali kelas?
   - Jika bukan wali kelas → Tampilkan pesan "Hanya wali kelas yang dapat mengakses"
3. Sistem tampilkan halaman nilai sikap untuk kelas yang di-wali
4. Klik "Input Nilai Sikap"
5. **Form Input Nilai Sikap:**
   - Otomatis: Kelas (kelas wali kelas), Tahun Ajaran
   - Pilih Semester (1/2)
   - Per siswa:
     - Dropdown Nilai Spiritual (A/B/C/D)
     - Textarea Deskripsi Spiritual (atau klik "Gunakan Template")
     - Dropdown Nilai Sosial (A/B/C/D)
     - Textarea Deskripsi Sosial (atau klik "Gunakan Template")
     - Textarea Catatan Wali Kelas (untuk rapor)
6. Klik "Simpan Nilai Sikap"
7. **Selesai:** Data tersimpan, akan muncul di rapor

**Template Otomatis:**
- Jika pilih predikat dan klik "Gunakan Template", sistem auto-fill deskripsi standar K13

---

## 🎯 Journey 3: Admin Konfigurasi Bobot Nilai

### Pengguna: Admin/TU

**Starting Point:** Dashboard Admin

**Steps:**
1. Klik menu "Penilaian" → "Bobot Nilai" di sidebar (atau Settings → Grade Weights)
2. Sistem tampilkan konfigurasi bobot aktif untuk tahun ajaran sekarang
3. **Form Konfigurasi:**
   - Tahun Ajaran (dropdown jika ada multiple)
   - Bobot UH: [input]%
   - Bobot UTS: [input]%
   - Bobot UAS: [input]%
   - Bobot Praktik: [input]%
   - Total: [auto-calculate]% (harus = 100%)
4. Edit nilai sesuai kebutuhan
5. Klik "Simpan Konfigurasi"
6. Jika total ≠ 100%: Error "Total bobot harus 100%"
7. **Selesai:** Konfigurasi tersimpan, activity log mencatat perubahan

**Default Values (K13):**
- UH: 30%
- UTS: 25%
- UAS: 30%
- Praktik: 15%

---

## 🎯 Journey 4: Admin Generate Rapor Bulk

### Pengguna: Admin/TU

**Starting Point:** Dashboard Admin

**Steps:**
1. Klik menu "Penilaian" → "Rapor" di sidebar
2. Klik "Generate Rapor"
3. **Step 1 - Selection:**
   - Pilih Tahun Ajaran
   - Pilih Semester
   - Centang kelas yang akan di-generate (bisa multi-select)
4. Klik "Validasi Kelengkapan"
5. **Step 2 - Validation:**
   - Sistem cek kelengkapan data untuk setiap kelas:
     - ✅ Siswa dengan nilai lengkap (hijau)
     - ⚠️ Siswa dengan data incomplete (kuning) → Detail: "UAS Matematika belum diinput"
   - Jika ada incomplete: Warning "X siswa belum lengkap"
6. Pilih opsi:
   - "Generate Semua" (termasuk incomplete)
   - "Generate Lengkap Saja" (skip incomplete)
7. Klik "Generate Rapor"
8. **Step 3 - Processing:**
   - Sistem generate PDF per siswa
   - Lock semua nilai untuk semester ini
   - Progress bar menunjukkan status
9. **Step 4 - Complete:**
   - "30 rapor berhasil di-generate"
   - Opsi: "Download ZIP" atau "Lihat Daftar Rapor"
10. Rapor status: DRAFT (belum di-submit ke Principal)

---

## 🎯 Journey 5: Principal Approve Rapor

### Pengguna: Kepala Sekolah

**Starting Point:** Dashboard Principal

**Steps:**
1. Klik menu "Akademik" → "Approval Rapor" di sidebar
2. Sistem tampilkan daftar rapor menunggu approval
   - Grouped by kelas
   - Badge: "X rapor pending" per kelas
3. Pilih kelas untuk direview (contoh: "Kelas 1A - 25 siswa")
4. **Review Mode:**
   - List siswa dengan preview score dan ranking
   - Klik nama siswa untuk preview rapor lengkap
5. **Preview Rapor:**
   - Tampilan HTML rapor seperti versi cetak
   - Data: Biodata, Nilai per mapel, Sikap, Kehadiran, Catatan wali kelas
6. Opsi:
   - **Approve:** Klik "Setujui & Rilis"
     - Rapor status → RELEASED
     - Notifikasi dikirim ke orang tua
   - **Reject:** Klik "Tolak"
     - Isi catatan alasan penolakan (wajib)
     - Rapor status → DRAFT (kembali ke wali kelas)
7. **Bulk Approve:**
   - Centang semua siswa di kelas
   - Klik "Approve Semua"
   - Konfirmasi: "Approve 25 rapor?"
8. **Selesai:** Rapor dirilis, orang tua dapat melihat

---

## 🎯 Journey 6: Parent Lihat Rapor Anak

### Pengguna: Orang Tua

**Starting Point:** Dashboard Orang Tua

**Steps:**
1. Terima notifikasi: "Rapor Semester 1 sudah tersedia untuk [Nama Anak]"
2. Klik notifikasi atau navigasi ke menu "Anak Saya"
3. Klik card anak yang ingin dilihat rapornya
4. Klik tab "Rapor" di halaman detail anak
5. **Daftar Rapor:**
   - Card per semester dengan info:
     - Semester & Tahun Ajaran
     - Status: Tersedia (hijau)
     - Rata-rata: 85.50
     - Ranking: #3 dari 30 siswa
     - Tanggal Rilis
6. Klik "Lihat Rapor"
7. **View Rapor Online:**
   - Header: Logo sekolah, nama sekolah, alamat
   - Biodata siswa
   - Tabel nilai per mata pelajaran (Pengetahuan, Keterampilan, Nilai Akhir, Predikat)
   - Nilai Sikap (Spiritual & Sosial) dengan deskripsi
   - Ringkasan Kehadiran (Hadir, Sakit, Izin, Alpha)
   - Catatan Wali Kelas
   - Rata-rata & Ranking
8. Klik "Download PDF"
9. **Selesai:** File PDF terdownload ke perangkat

**Empty State:**
- Jika belum ada rapor: "Rapor belum tersedia. Silakan tunggu hingga sekolah merilis rapor."

---

## 🎯 Journey 7: Parent Lihat Rekap Nilai

### Pengguna: Orang Tua

**Starting Point:** Dashboard Orang Tua

**Steps:**
1. Klik menu "Anak Saya"
2. Klik card anak yang ingin dilihat nilainya
3. Klik tab "Nilai" di halaman detail anak
4. **Halaman Rekap Nilai:**
   - Filter: Tahun Ajaran, Semester
   - Tabel per mata pelajaran:
     - Nama Mapel
     - Nilai UH (rata-rata)
     - Nilai UTS
     - Nilai UAS
     - Nilai Praktik (rata-rata)
     - **Nilai Akhir** (terbobot)
     - **Predikat** (A/B/C/D)
   - Summary:
     - Rata-rata Keseluruhan: 85.50
     - Ranking: #3 dari 30 siswa
5. Klik row mapel untuk expand detail:
   - UH 1: 80 (15 Jan 2026)
   - UH 2: 85 (20 Jan 2026)
   - UTS: 82 (10 Feb 2026)
   - ...
6. **Selesai:** Orang tua dapat memantau progress nilai anak

---

## 📊 Data Flow Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                         DATA OWNERS                               │
├────────────────────┬────────────────────┬────────────────────────┤
│   Teacher (Guru)   │   Wali Kelas       │     Admin/TU           │
│   ↓ Input Nilai    │   ↓ Input Sikap    │   ↓ Config & Generate  │
│   UH/UTS/UAS/      │   Spiritual/       │   Bobot/Rapor          │
│   Praktik          │   Sosial/Catatan   │                        │
└────────┬───────────┴─────────┬──────────┴──────────┬─────────────┘
         │                     │                      │
         ▼                     ▼                      ▼
┌────────────────────────────────────────────────────────────────────┐
│                         DATABASE                                    │
│  ┌──────────┐  ┌───────────────┐  ┌────────────┐  ┌──────────────┐ │
│  │  grades  │  │attitude_grades│  │report_cards│  │grade_weights │ │
│  └──────────┘  └───────────────┘  └────────────┘  └──────────────┘ │
└────────────────────────────────────────────────────────────────────┘
         │                     │                      │
         ▼                     ▼                      ▼
┌──────────────────────────────────────────────────────────────────┐
│                       DATA CONSUMERS                              │
├────────────────────┬────────────────────┬────────────────────────┤
│     Principal      │       Parent       │        Teacher         │
│   ↓ Approve/View   │   ↓ View/Download  │   ↓ View Rekap         │
│   Dashboard        │   Rapor            │   per Kelas            │
└────────────────────┴────────────────────┴────────────────────────┘
```

---

## ⚠️ Error Handling

| Error | Displayed Message | Recovery |
|-------|-------------------|----------|
| Score > 100 | "Nilai maksimal 100" | Fix input |
| Score < 0 | "Nilai minimal 0" | Fix input |
| Missing teacher-subject | "Anda tidak mengajar mapel ini di kelas ini" | Contact admin |
| Not wali kelas | "Hanya wali kelas yang dapat mengakses" | - |
| Edit locked grade | "Nilai sudah dikunci" | Contact admin untuk unlock |
| Generate incomplete | Warning list missing data | Complete input first |
| Report card not released | "Rapor belum tersedia" | Wait for release |
| PDF not found | "File PDF tidak ditemukan" | Admin regenerate |

---

## 📱 Mobile Considerations

| Screen | Mobile Optimization |
|--------|---------------------|
| Grade input table | Horizontal scroll, sticky student name |
| Attitude grade form | Full-width inputs, collapsible per student |
| Report card preview | Responsive HTML, zoom enabled |
| Parent grade view | Card-based layout, expandable rows |
| PDF download | Opens in device PDF viewer |
