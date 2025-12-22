# Modul 8: Dashboard & Reports (Dashboard & Laporan)

## 📋 Overview

Modul ini menyediakan dashboard ringkasan dan berbagai laporan untuk setiap role (Kepala Sekolah, TU, Guru, Orang Tua).

**Module Code:** `DASH`  
**Priority:** P1 (High)  
**Dependencies:** Semua modul (aggregate data from all modules)

---

## 🎯 Tujuan

1. Memberikan overview real-time untuk setiap stakeholder
2. Visualisasi data dengan chart/grafik yang mudah dipahami
3. Quick access ke fitur-fitur utama
4. Analytics untuk decision making
5. Export laporan untuk dokumentasi

---

## 📖 User Stories & Functional Requirements

### 1. Dashboard Kepala Sekolah

**US-DASH-001:**
```
Sebagai Kepala Sekolah,
Saya ingin melihat ringkasan operasional sekolah,
Sehingga saya dapat memonitor performa sekolah secara keseluruhan
```

**Dashboard Components:**

**A. Summary Cards (Top Row):**
- Total Siswa Aktif (number + icon)
- Total Guru (number + icon)
- Rata-rata Kehadiran Siswa Bulan Ini (percentage + trend)
- Total Pemasukan Bulan Ini (Rp amount + trend)

**B. Charts:**
- **Grafik Absensi:** Line chart kehadiran siswa & guru per hari (last 30 days)
- **Grafik Keuangan:** Bar chart pemasukan per jenis pembayaran bulan ini
- **Grafik Akademik:** Bar chart rata-rata nilai per kelas semester ini

**C. Quick Stats:**
- Siswa dengan Kehadiran < 80% (count, badge merah)
- Siswa dengan Tunggakan SPP (count, badge merah)
- Guru Terlambat Bulan Ini (count)
- Pendaftar PSB Pending Verifikasi (count)

**D. Recent Activities (Timeline):**
- Last 10 activities/events (e.g., "10 siswa bayar SPP", "Rapor kelas 1A di-generate", dll)

**E. Quick Actions:**
- Lihat Laporan Keuangan Lengkap
- Lihat Laporan Akademik
- Download Laporan Bulanan (PDF)

**Access:** Principal only

---

### 2. Dashboard TU/Admin

**US-DASH-002:**
```
Sebagai Staf TU,
Saya ingin melihat antrian pekerjaan dan akses cepat ke fungsi utama,
Sehingga saya dapat bekerja lebih efisien
```

**Dashboard Components:**

**A. Task Queue (Prioritized):**
- Izin/Sakit Siswa Pending Verifikasi (badge count)
- Pembayaran Pending Konfirmasi (badge count)
- PSB Pending Verifikasi (badge count)
- Guru yang Belum Clock In Hari Ini (list)

**B. Quick Actions (Large Buttons):**
- Input Pembayaran
- Input Absensi (jika perlu koreksi)
- Tambah Siswa Baru
- Generate Tagihan SPP
- Verifikasi PSB

**C. Summary:**
- Total Pemasukan Hari Ini (Rp amount)
- Siswa Hadir Hari Ini (percentage)
- Guru Hadir Hari Ini (count/total)

**D. Calendar & Reminders:**
- Hari ini: tanggal, hari libur/event
- Upcoming events (7 hari ke depan)

**Access:** Admin/TU only

---

### 3. Dashboard Guru

**US-DASH-003:**
```
Sebagai Guru,
Saya ingin melihat jadwal hari ini dan quick access ke fitur yang sering digunakan,
Sehingga saya dapat fokus mengajar
```

**Dashboard Components:**

**A. Jadwal Hari Ini (Prominent):**
- List jadwal mengajar hari ini (kelas, mata pelajaran, jam)
- Next class highlighted
- Status absensi: sudah input atau belum (icon check/warning)

**B. Presensi:**
- Card: Status presensi hari ini (Sudah Clock In/Belum)
- Jam masuk (jika sudah)
- Button: Clock In atau Clock Out (prominent)

**C. Tasks:**
- Nilai yang Belum Diinput (count siswa)
- Izin/Sakit Pending Verifikasi dari Siswa Kelas Saya (count)

**D. Quick Actions:**
- Input Absensi Harian
- Input Nilai
- Lihat Jadwal Minggu Ini

**E. Summary:**
- Total Jam Mengajar Bulan Ini (untuk guru honorer)
- Kehadiran Saya Bulan Ini (percentage)

**Access:** Teacher only

---

### 4. Dashboard Orang Tua

**US-DASH-004:**
```
Sebagai Orang Tua,
Saya ingin melihat ringkasan informasi anak saya,
Sehingga saya selalu update dengan perkembangan anak
```

**Dashboard Components:**

**A. Student Card (jika multiple anak, bisa switch):**
- Foto anak
- Nama, Kelas, NIS
- Button: Lihat Profil Lengkap

**B. Summary Cards:**
- **Kehadiran Bulan Ini:** Percentage, icon hijau/merah, keterangan (Hadir X hari, Alpha Y hari)
- **Status SPP:** Lunas/Belum Bayar/Menunggak (badge), total tunggakan (jika ada)
- **Rata-rata Nilai Semester Ini:** Number (jika sudah ada nilai), trend

**C. Recent Activities:**
- Last 5 activities (absensi, pembayaran, nilai baru di-input, pengumuman)

**D. Pengumuman:**
- Latest 3 pengumuman dari sekolah (title, date, excerpt)

**E. Quick Actions:**
- Bayar SPP (link ke halaman pembayaran)
- Ajukan Izin/Sakit
- Lihat Rapor
- Hubungi Sekolah (WhatsApp atau phone)

**Access:** Parent only

---

### 5. Reports Module

**FR-DASH-005: Generate Reports**
**Priority:** Must Have  
**Description:** Sistem dapat generate berbagai laporan untuk dokumentasi & analisis.

**Report Types:**

**A. Laporan Keuangan Bulanan:**
- Filter: Bulan, Tahun
- Content:
  - Summary: Total Pemasukan, Total Piutang, Tingkat Kolektibilitas
  - Breakdown per jenis pembayaran (table)
  - Breakdown per kelas (table)
  - Chart: pemasukan vs target, tren 6 bulan terakhir
- Export: Excel & PDF
- Access: Principal, TU

**B. Laporan Absensi Bulanan:**
- Filter: Bulan, Tahun, Kelas
- Content:
  - Summary: Rata-rata kehadiran siswa & guru
  - Table: per siswa (Hadir, Izin, Sakit, Alpha, %)
  - Table: per guru (Hadir, Terlambat, Izin, Alpha, %)
  - Highlight: siswa/guru dengan kehadiran rendah
- Export: Excel & PDF
- Access: Principal, TU, Teacher (kelas sendiri)

**C. Laporan Akademik Per Semester:**
- Filter: Semester, Tahun Ajaran, Kelas
- Content:
  - Summary: Rata-rata nilai kelas, jumlah siswa naik/tidak naik
  - Table: per siswa (rata-rata nilai, ranking, predikat)
  - Table: per mata pelajaran (rata-rata kelas, tertinggi, terendah)
  - Chart: distribusi nilai (A, B, C, D)
- Export: Excel & PDF
- Access: Principal, TU, Teacher (kelas sendiri)

**D. Laporan PSB:**
- Filter: Tahun Ajaran
- Content:
  - Summary: Total pendaftar, diterima, terdaftar, conversion rate
  - Demographics: chart (gender, asal sekolah, lokasi)
  - Timeline: pendaftar per hari
- Export: Excel & PDF
- Access: Principal, TU

**E. Laporan Keseluruhan (End of Year Report):**
- Filter: Tahun Ajaran
- Content: Summary dari semua laporan di atas (keuangan, absensi, akademik, PSB)
- Format: PDF comprehensive (20-30 halaman), dengan cover & executive summary
- Access: Principal only

---

## 🎨 UI/UX Requirements

**Layout:**
- Grid layout (responsive: 3-4 columns desktop, 1-2 columns tablet, 1 column mobile)
- Card-based components dengan shadow & border radius
- Color coding: hijau (positif), merah (warning/negatif), biru (info), kuning (pending)
- Icons untuk visual clarity

**Charts:**
- Interactive (hover untuk detail)
- Responsive (resize untuk mobile)
- Library: Chart.js, Recharts, atau ApexCharts

**Performance:**
- Load data incrementally (summary dulu, chart menyusul)
- Skeleton loading untuk UX
- Cache data yang tidak sering berubah

**Mobile:**
- Stack cards vertically
- Collapsible sections
- Touch-friendly buttons (min 48px height)
- Pull-to-refresh

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Dashboard untuk setiap role (Principal, TU, Teacher, Parent)
- ✅ Summary cards dengan real-time data
- ✅ Charts/visualisasi (absensi, keuangan, akademik)
- ✅ Quick actions per role
- ✅ Generate & export reports (Keuangan, Absensi, Akademik, PSB)
- ✅ Export to Excel & PDF

### Should Have (MVP):
- ✅ Recent activities timeline
- ✅ Task queue untuk TU (pending items dengan badge count)
- ✅ Calendar & reminders
- ✅ Trend indicators (naik/turun)

### Could Have:
- ⬜ Customizable dashboard (drag & drop widgets)
- ⬜ Real-time dashboard (auto-refresh setiap X detik)
- ⬜ Push notifications untuk task queue
- ⬜ Dashboard themes (light/dark mode)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft

