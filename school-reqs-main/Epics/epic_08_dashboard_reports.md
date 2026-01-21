# EPIC 8: Dashboard & Reports (Dashboard & Laporan)

**Project:** SD Management System  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Epic Owner:** Development Team  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📋 Epic Overview

### Goal
Menyediakan dashboard role-specific dan sistem pelaporan komprehensif untuk setiap stakeholder (Kepala Sekolah, TU, Guru, Orang Tua) dengan visualisasi data real-time, analytics untuk decision making, dan export laporan untuk dokumentasi.

### Business Value
- **Real-time Visibility:** Stakeholder dapat monitor kondisi sekolah secara real-time
- **Data-Driven Decision:** Analytics dan chart membantu pengambilan keputusan berdasarkan data
- **Time Saving:** Quick access ke fitur utama mengurangi navigasi time 70%
- **Task Management:** Task queue membantu TU prioritas pekerjaan
- **Transparency:** Semua stakeholder dapat akses informasi yang relevan
- **Documentation:** Export laporan untuk audit, presentasi, dan arsip

### Success Metrics
- 100% user login akses dashboard sebagai landing page
- Dashboard load time < 3 detik (dengan all widgets)
- 90% user pakai quick actions dari dashboard (tidak navigasi manual)
- Laporan di-generate 100% tepat waktu (bulanan)
- Export success rate > 98%
- User satisfaction score > 4/5 untuk dashboard relevance

---

## 📊 Epic Summary

| **Attribute** | **Value** |
|---------------|-----------|
| **Total Points** | 33 points |
| **Target Sprint** | Sprint 9 & 10 |
| **Priority** | P1 - High (aggregate all modules) |
| **Dependencies** | All EPICs (1-7: Auth, Student, Attendance, Payment, Grades, PSB, Teacher) |
| **Target Release** | MVP (Phase 1) |
| **Estimated Duration** | 5 minggu (1 developer) |

---

## 🎯 User Stories Included

### Dashboard Modules (14 points)

#### US-DASH-001: Dashboard Kepala Sekolah
**Priority:** Must Have | **Estimation:** L (5 points)

**As a** Kepala Sekolah  
**I want** melihat dashboard overview seluruh operasional sekolah  
**So that** saya dapat monitoring kondisi sekolah dengan cepat

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah login ke sistem  
   **When** kepala sekolah masuk ke dashboard  
   **Then** sistem tampilkan widget: Total Siswa Aktif, Absensi Hari Ini (siswa & guru), Pemasukan Bulan Ini, Tunggakan SPP, Pending Actions

- ✅ **Given** ada 15 siswa alpha hari ini  
   **When** dashboard load  
   **Then** widget "Absensi Hari Ini" tampilkan angka dengan highlight merah

- ✅ **Given** ada 20 pembayaran menunggu verifikasi  
   **When** dashboard load  
   **Then** widget "Pending Actions" tampilkan notifikasi dengan link ke halaman verifikasi

- ✅ **Given** kepala sekolah ingin lihat detail  
   **When** kepala sekolah klik salah satu widget  
   **Then** sistem redirect ke halaman detail module tersebut

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

**Technical Notes:**
- Cache summary data (refresh every 5 menit)
- Real-time untuk pending actions (WebSocket atau polling)
- Click widget untuk drill-down ke detail page
- Export snapshot dashboard ke PDF

---

#### US-DASH-002: Dashboard TU/Admin
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** TU/Admin  
**I want** melihat dashboard fokus pada administrasi & keuangan  
**So that** saya dapat fokus pada tugas-tugas TU

**Acceptance Criteria:**
- ✅ **Given** TU login ke sistem  
   **When** TU masuk ke dashboard  
   **Then** sistem tampilkan widget: Siswa Baru Hari Ini, Pembayaran Pending Verifikasi, Tunggakan SPP, Dokumen PSB Pending, Presensi Guru Hari Ini

- ✅ **Given** ada 10 pembayaran pending  
   **When** dashboard load  
   **Then** widget "Pembayaran Pending" tampilkan angka 10 dengan link ke halaman verifikasi

- ✅ **Given** TU klik quick action "Catat Pembayaran"  
   **When** TU klik  
   **Then** sistem buka form catat pembayaran baru

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

**Technical Notes:**
- Badge counter update real-time
- Quick action button large & touch-friendly
- Priority sorting untuk task queue (urgent first)

---

#### US-DASH-003: Dashboard Guru
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Guru  
**I want** melihat dashboard fokus pada mengajar & siswa  
**So that** saya dapat fokus pada tugas mengajar

**Acceptance Criteria:**
- ✅ **Given** guru login ke sistem  
   **When** guru masuk ke dashboard  
   **Then** sistem tampilkan widget: Jadwal Hari Ini, Siswa di Kelas Saya, Absensi Pending Input, Nilai Pending Input

- ✅ **Given** guru belum input absensi hari ini  
   **When** dashboard load  
   **Then** widget "Absensi Pending" tampilkan notifikasi "Input Absensi Kelas 3A"

- ✅ **Given** guru klik quick action "Input Absensi"  
   **When** guru klik  
   **Then** sistem buka halaman input absensi untuk kelas yang diajar

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

**Technical Notes:**
- Jadwal hari ini di-highlight waktu mendekati jam mengajar
- Clock in/out button large & prominent (primary action)
- Mobile-first design (guru sering akses via HP)

---

#### US-DASH-004: Dashboard Orang Tua
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Orang Tua  
**I want** melihat dashboard info anak saya  
**So that** saya dapat monitoring perkembangan anak

**Acceptance Criteria:**
- ✅ **Given** orang tua login ke portal  
   **When** orang tua masuk ke dashboard  
   **Then** sistem tampilkan card anak (jika > 1 anak, tampilkan semua), widget per anak: Absensi Bulan Ini, Tagihan Pending, Pengumuman Terbaru

- ✅ **Given** anak A punya tagihan SPP belum bayar  
   **When** dashboard load  
   **Then** card anak A tampilkan notifikasi "Tagihan: 1 bulan belum dibayar"

- ✅ **Given** ada pengumuman baru dari sekolah  
   **When** dashboard load  
   **Then** widget "Pengumuman" tampilkan notifikasi dengan badge "Baru"

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

**Technical Notes:**
- Student switcher jika orang tua punya > 1 anak
- Pengumuman badge "Baru" jika belum dibaca
- Mobile-first design (parent biasanya akses via HP)
- Push notification untuk pengumuman penting

---

### Reports Module (19 points)

#### US-DASH-005: Laporan Keuangan (Bulanan)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah/TU  
**I want** generate laporan keuangan bulanan  
**So that** saya dapat monitoring cash flow dan tunggakan

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah di halaman "Laporan Keuangan"  
   **When** kepala sekolah pilih bulan (misal: Januari 2025)  
   **Then** sistem tampilkan summary: Total Pemasukan, Breakdown per Jenis (SPP, Uang Gedung, dll), Total Tunggakan, Persentase Kolektibilitas

- ✅ **Given** laporan Januari menunjukkan pemasukan Rp 45.000.000  
   **When** laporan di-generate  
   **Then** sistem tampilkan grafik breakdown: SPP (Rp 40jt), Uang Gedung (Rp 3jt), Lain-lain (Rp 2jt)

- ✅ **Given** kepala sekolah ingin export laporan  
   **When** kepala sekolah klik "Export PDF"  
   **Then** sistem generate PDF siap print dengan logo sekolah, summary, grafik, detail transaksi

**Report Content:**
- Summary: Total Pemasukan, Total Piutang, Tingkat Kolektibilitas
- Breakdown per jenis pembayaran (table)
- Breakdown per kelas (table)
- Chart: pemasukan vs target, tren 6 bulan terakhir
- Export: Excel & PDF

**Technical Notes:**
- Async report generation dengan progress indicator
- Cache report untuk 1 hari (regenerate jika ada perubahan data)
- PDF dengan professional layout (logo, header, footer)

---

#### US-DASH-006: Laporan Akademik (Per Semester)
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah  
**I want** generate laporan akademik per semester  
**So that** saya dapat evaluasi kualitas pendidikan

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah di halaman "Laporan Akademik"  
   **When** kepala sekolah pilih semester  
   **Then** sistem tampilkan: Rata-rata Nilai per Mata Pelajaran, Distribusi Predikat (A/B/C/D), Top 10 Siswa, Siswa Perlu Perhatian (nilai < KKM)

- ✅ **Given** mata pelajaran IPA rata-ratanya 75  
   **When** laporan di-generate  
   **Then** sistem tampilkan bar chart dengan IPA di posisi sesuai rata-rata

- ✅ **Given** kepala sekolah ingin export  
   **When** kepala sekolah klik "Export PDF"  
   **Then** sistem generate PDF dengan grafik, summary, rekomendasi

**Report Content:**
- Summary: Rata-rata nilai kelas, jumlah siswa naik/tidak naik
- Table: per siswa (rata-rata nilai, ranking, predikat)
- Table: per mata pelajaran (rata-rata kelas, tertinggi, terendah)
- Chart: distribusi nilai (A, B, C, D)
- Export: Excel & PDF

**Technical Notes:**
- Integration dengan grades module
- Calculation rata-rata dengan weight per mata pelajaran
- Privacy: ranking optional (dapat di-hide di settings)

---

#### US-DASH-007: Laporan Absensi (Bulanan)
**Priority:** Must Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah/TU  
**I want** generate laporan absensi bulanan (siswa & guru)  
**So that** saya dapat monitoring kedisiplinan

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah di halaman "Laporan Absensi"  
   **When** kepala sekolah pilih bulan dan kategori (Siswa/Guru)  
   **Then** sistem tampilkan rekap: Total Hadir, Sakit, Izin, Alpha, Persentase Kehadiran per Kelas/per Guru

- ✅ **Given** kelas 3A kehadiran 92%, kelas 3B kehadiran 85%  
   **When** laporan di-generate  
   **Then** sistem highlight kelas 3B sebagai "Need Attention"

- ✅ **Given** kepala sekolah ingin lihat detail siswa dengan alpha > 3x  
   **When** kepala sekolah klik "Siswa Bermasalah"  
   **Then** sistem tampilkan list siswa dengan alpha tertinggi dan contact orang tua

**Report Content:**
- Rekap: Total Hadir, Sakit, Izin, Alpha, Persentase Kehadiran per Kelas/per Guru
- Grafik: tren kehadiran per kelas, per bulan
- Table: detail per siswa/guru dengan highlight low attendance
- Export: Excel & PDF
- Filter: per kelas, per periode

**Technical Notes:**
- Integration dengan attendance module
- Threshold untuk "low attendance" configurable (default: < 80%)
- Export include contact info untuk follow-up

---

#### US-DASH-008: Laporan PSB (Recruitment)
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** Kepala Sekolah  
**I want** generate laporan PSB (penerimaan siswa baru)  
**So that** saya dapat evaluasi proses recruitment

**Acceptance Criteria:**
- ✅ **Given** kepala sekolah di halaman "Laporan PSB"  
   **When** kepala sekolah pilih tahun ajaran  
   **Then** sistem tampilkan: Total Pendaftar, Diterima, Ditolak, Daftar Ulang Selesai, Funnel Conversion

- ✅ **Given** total pendaftar 120, diterima 100, daftar ulang selesai 95  
   **When** laporan di-generate  
   **Then** sistem tampilkan funnel chart: 120 → 100 → 95 (conversion rate 79%)

- ✅ **Given** kepala sekolah ingin export  
   **When** kepala sekolah klik "Export"  
   **Then** sistem generate PDF/Excel dengan detail per status

**Report Content:**
- Summary: Total pendaftar, diterima, terdaftar, conversion rate
- Demographics: chart (gender, asal sekolah, lokasi)
- Timeline: pendaftar per hari
- Funnel analysis: pendaftar → verifikasi → diterima → daftar ulang
- Export: Excel & PDF

**Technical Notes:**
- Integration dengan PSB module
- Comparison dengan tahun sebelumnya
- Demographics chart untuk analisis market

---

#### US-DASH-009: Notifikasi & Alert Dashboard
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** User (semua role)  
**I want** menerima notifikasi penting di dashboard  
**So that** saya tidak miss task/informasi penting

**Acceptance Criteria:**
- ✅ **Given** TU belum verifikasi 10 pembayaran  
   **When** TU login  
   **Then** dashboard tampilkan notifikasi "10 pembayaran menunggu verifikasi" dengan badge merah

- ✅ **Given** guru belum input absensi hari ini (jam > 10:00)  
   **When** guru buka dashboard  
   **Then** sistem tampilkan alert "Reminder: Input absensi kelas 3A"

- ✅ **Given** orang tua punya tunggakan SPP 2 bulan  
   **When** orang tua login  
   **Then** dashboard tampilkan warning "Anda memiliki tunggakan 2 bulan. Mohon segera dilunasi"

**Notification Types:**
- **Urgent (Merah):** Tunggakan > 2 bulan, Alpha > 5x, Pending verifikasi > 24 jam
- **Warning (Kuning):** Tunggakan 1 bulan, Alpha 3-5x, Task pending
- **Info (Biru):** Pengumuman baru, Reminder input data

**Technical Notes:**
- Badge counter untuk pending actions
- Notification center dengan history (last 30 days)
- Mark as read functionality
- Push notification (optional, via FCM/OneSignal)

---

#### US-DASH-010: Export Laporan Multi-Format
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** Kepala Sekolah/TU  
**I want** export laporan dalam berbagai format (PDF, Excel, CSV)  
**So that** saya dapat gunakan sesuai kebutuhan

**Acceptance Criteria:**
- ✅ **Given** user di halaman laporan apapun  
   **When** user klik "Export"  
   **Then** sistem tampilkan pilihan format: PDF, Excel (.xlsx), CSV

- ✅ **Given** user pilih PDF  
   **When** user klik "Download PDF"  
   **Then** sistem generate PDF dengan format professional (logo sekolah, header, footer, page number)

- ✅ **Given** user pilih Excel  
   **When** user klik "Download Excel"  
   **Then** sistem generate Excel dengan data detail dan formatting (header bold, auto-width column)

**Export Options:**
- **PDF:** untuk presentasi dan print (with logo, header, footer)
- **Excel:** untuk analisis lebih lanjut (with formatting)
- **CSV:** untuk import ke software lain (plain data)

**Technical Notes:**
- Async export dengan download link via email (untuk large report)
- Export history (last 30 days)
- Filename convention: [ReportType]_[Period]_[Date].pdf

---

#### US-DASH-011: Custom Date Range untuk Laporan
**Priority:** Should Have | **Estimation:** S (2 points)

**As a** Kepala Sekolah/TU  
**I want** memilih custom date range untuk laporan  
**So that** saya dapat generate laporan untuk periode spesifik

**Acceptance Criteria:**
- ✅ **Given** user di halaman laporan keuangan  
   **When** user pilih "Custom Range" dan set 1 Januari - 15 Januari 2025  
   **Then** sistem generate laporan hanya untuk tanggal tersebut

- ✅ **Given** user pilih preset "Minggu Ini" atau "Bulan Ini"  
   **When** user klik preset  
   **Then** date range otomatis terisi sesuai preset

**Date Range Options:**
- Preset: Hari Ini, Minggu Ini, Bulan Ini, 3 Bulan Terakhir, Tahun Ini
- Custom: date picker untuk start & end date
- Validasi: end date tidak boleh < start date
- Save favorite date range (untuk quick access)

**Technical Notes:**
- Date picker with range selection
- Preset shortcuts untuk common periods
- Display data availability (e.g., "Data tersedia: 1 Jan 2024 - Sekarang")

---

#### US-DASH-012: Grafik & Visualisasi Data
**Priority:** Should Have | **Estimation:** M (3 points)

**As a** Kepala Sekolah  
**I want** melihat data dalam bentuk grafik/chart  
**So that** informasi lebih mudah dipahami

**Acceptance Criteria:**
- ✅ **Given** user di dashboard atau laporan  
   **When** data di-load  
   **Then** sistem tampilkan grafik yang sesuai: line chart (tren), bar chart (perbandingan), pie chart (distribusi)

- ✅ **Given** data pemasukan 6 bulan terakhir  
   **When** sistem generate chart  
   **Then** tampilkan line chart dengan sumbu X (bulan), sumbu Y (nominal), dan tooltip saat hover

- ✅ **Given** data distribusi nilai (A=30%, B=40%, C=25%, D=5%)  
   **When** sistem generate chart  
   **Then** tampilkan pie chart dengan legend dan persentase

**Chart Types:**
- **Line Chart:** Tren kehadiran, pemasukan (over time)
- **Bar Chart:** Perbandingan per kelas, per mata pelajaran
- **Pie Chart:** Distribusi nilai, status pembayaran
- **Gauge Chart:** Percentage (kehadiran, kolektibilitas)
- **Funnel Chart:** PSB recruitment flow

**Technical Notes:**
- Chart library: Chart.js atau ApexCharts
- Interactive: hover untuk detail, click untuk drill-down
- Responsive: chart resize sesuai screen size
- Export chart as image

---

## 🏗️ Technical Architecture

### Database Schema Requirements

#### Dashboard_Widgets Table (Optional, untuk custom dashboard)
```
- id (PK)
- user_id (FK to users)
- widget_type (summary_card/chart/task_list/recent_activities)
- widget_config (JSON: title, data_source, filters, etc)
- position (integer, untuk ordering)
- size (small/medium/large)
- is_visible (boolean)
- created_at
- updated_at
```

#### Notifications Table
```
- id (PK)
- user_id (FK to users)
- notification_type (urgent/warning/info)
- title (varchar)
- message (text)
- action_url (varchar, link to related page)
- is_read (boolean, default false)
- read_at (timestamp, nullable)
- created_at
- updated_at
```

#### Report_History Table
```
- id (PK)
- user_id (FK to users)
- report_type (financial/academic/attendance/psb)
- report_period (date or date range)
- file_format (pdf/excel/csv)
- file_path (storage path)
- file_size (bytes)
- generated_at (timestamp)
- expires_at (timestamp, for cleanup old files)
- created_at
- updated_at
```

#### System_Activities Table (untuk Recent Activities)
```
- id (PK)
- activity_type (payment/attendance/grade/psb/etc)
- activity_description (text, e.g., "10 siswa bayar SPP")
- related_id (integer, FK to related entity)
- related_type (varchar, polymorphic: payments, attendances, etc)
- user_id (FK to users, who did the action)
- created_at
```

#### Announcements Table (untuk pengumuman di dashboard parent)
```
- id (PK)
- title (varchar)
- content (text)
- target_audience (all/parents/teachers/students)
- priority (high/medium/low)
- is_published (boolean)
- published_at (timestamp)
- expires_at (timestamp, nullable)
- created_by (FK to users)
- created_at
- updated_at
```

---

### API Endpoints

#### Dashboard
- `GET /api/dashboard/principal` - Get principal dashboard data (summary, charts, pending actions)
- `GET /api/dashboard/admin` - Get TU/admin dashboard data (task queue, quick stats)
- `GET /api/dashboard/teacher/:teacher_id` - Get teacher dashboard data (schedule, tasks)
- `GET /api/dashboard/parent/:parent_id` - Get parent dashboard data (student info, announcements)
- `GET /api/dashboard/summary-cards` - Get summary cards data
- `GET /api/dashboard/recent-activities` - Get recent activities (last 10)
- `GET /api/dashboard/pending-tasks` - Get pending tasks per role

#### Reports
- `GET /api/reports/financial` - Get financial report (filters: month, year, type)
- `GET /api/reports/academic` - Get academic report (filters: semester, year, class)
- `GET /api/reports/attendance` - Get attendance report (filters: month, type: student/teacher)
- `GET /api/reports/psb` - Get PSB report (filters: academic_year)
- `POST /api/reports/generate` - Generate report (async, with report_type, period, format)
- `GET /api/reports/history` - Get user's report generation history
- `GET /api/reports/download/:report_id` - Download generated report
- `DELETE /api/reports/:report_id` - Delete old report

#### Charts & Visualizations
- `GET /api/charts/attendance-trend` - Get attendance trend data (last 30 days)
- `GET /api/charts/revenue-breakdown` - Get revenue breakdown (current month)
- `GET /api/charts/grade-distribution` - Get grade distribution (current semester)
- `GET /api/charts/psb-funnel` - Get PSB funnel data (selected academic year)

#### Notifications
- `GET /api/notifications` - Get user notifications (paginated, filters: is_read)
- `PUT /api/notifications/:id/read` - Mark notification as read
- `PUT /api/notifications/read-all` - Mark all notifications as read
- `DELETE /api/notifications/:id` - Delete notification

#### Announcements
- `GET /api/announcements` - Get announcements (filters: target_audience, is_active)
- `POST /api/announcements` - Create announcement (Admin only)
- `PUT /api/announcements/:id` - Update announcement (Admin only)
- `DELETE /api/announcements/:id` - Delete announcement (Admin only)
- `PUT /api/announcements/:id/publish` - Publish announcement

---

### Integration Points

#### INT-DASH-001: All Modules (Data Aggregation)
**Description:** Dashboard aggregate data dari semua module
**Data Flow:**
1. Dashboard load → Query summary dari setiap module
2. Students: total active students
3. Attendance: kehadiran siswa & guru
4. Payment: pemasukan, tunggakan
5. Grades: rata-rata nilai
6. PSB: pending verifications
7. Teacher: total guru, kehadiran

**Technical:**
- Use cache untuk summary data (refresh every 5 menit)
- Async data loading (skeleton loading per widget)
- Database views untuk complex queries

---

#### INT-DASH-002: Notification Module
**Description:** Send notifications untuk events dari berbagai module
**Events:**
- Payment received → Notify parent
- Attendance late/alpha → Notify parent & TU
- Grade posted → Notify student & parent
- PSB status change → Notify applicant
- Task pending > 24h → Notify assigned user

**Technical:**
- Event-driven architecture (event bus)
- Queue-based notification processing
- Template per event type

---

#### INT-DASH-003: Report Generation (All Modules)
**Description:** Generate reports dengan data dari module terkait
**Data Flow:**
1. User request report → System queue job
2. Query data dari module terkait
3. Process & format data
4. Generate file (PDF/Excel/CSV)
5. Store file & send download link
6. Auto-cleanup old files (after 30 days)

**Technical:**
- Async job queue (Redis queue atau Laravel queue)
- PDF library: Puppeteer atau wkhtmltopdf
- Excel library: ExcelJS atau PHPSpreadsheet
- Cloud storage untuk generated files

---

## 🎨 UI/UX Design Requirements

### Principal Dashboard

**Layout (Desktop):**
```
┌─────────────────────────────────────────────────────────┐
│ Dashboard Kepala Sekolah                    [Export PDF]│
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ [📊 Total Siswa]  [👨‍🏫 Total Guru]  [✅ Kehadiran]  [💰 Pemasukan]│
│    245 siswa        32 guru       92%          Rp 45jt  │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Grafik Absensi (Line)      │    Grafik Keuangan (Bar)  │
│ [Chart: 30 days trend]     │    [Chart: Revenue by type]│
│                             │                            │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Quick Stats                 │  Recent Activities        │
│ - Kehadiran < 80%: 5 siswa │  • 10 siswa bayar SPP     │
│ - Tunggakan SPP: 12 siswa  │  • Rapor 1A di-generate   │
│ - Guru Terlambat: 2        │  • PSB: 3 pendaftar baru  │
│ - PSB Pending: 8           │  ...                       │
│                             │                            │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Quick Actions:                                           │
│ [Laporan Keuangan] [Laporan Akademik] [Laporan Bulanan] │
└─────────────────────────────────────────────────────────┘
```

**Mobile:**
- Stack cards vertical
- Summary cards 2-column grid
- Charts full-width dengan scroll horizontal
- Collapsible sections (tap to expand)

---

### TU/Admin Dashboard

**Layout (Desktop):**
```
┌─────────────────────────────────────────────────────────┐
│ Dashboard TU                      Hari ini: Rabu, 13 Des│
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Task Queue (Pending):                                    │
│ [⚠️ Pembayaran Pending: 10]  [📋 PSB Pending: 8]        │
│ [🏥 Izin/Sakit Pending: 5]   [⏰ Guru Belum Clock In: 3]│
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Quick Actions (Large Buttons):                          │
│ [💰 Input Pembayaran]  [✅ Input Absensi]  [👤 Tambah Siswa]│
│ [📄 Generate Tagihan]  [✔️ Verifikasi PSB]               │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Summary Hari Ini:           │  Calendar & Reminders:    │
│ - Pemasukan: Rp 2.500.000   │  13 Desember 2025        │
│ - Siswa Hadir: 92%          │  Upcoming:                │
│ - Guru Hadir: 30/32         │  • 15 Des: Rapat Guru    │
│                             │  • 20 Des: Tutup Semester │
└─────────────────────────────────────────────────────────┘
```

---

### Teacher Dashboard

**Layout (Desktop):**
```
┌─────────────────────────────────────────────────────────┐
│ Dashboard Guru - Pak Budi, S.Pd                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Presensi Hari Ini:                                       │
│ [✅ Clock In: 07:25]           [Clock Out →]            │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Jadwal Hari Ini (Rabu):                                  │
│ ⏰ 08:00-09:30  Kelas 3A - Matematika  [✅ Absensi OK]  │
│ ⏰ 09:30-11:00  Kelas 3B - Matematika  [⚠️ Belum Input] │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Tasks:                      │  Quick Actions:           │
│ - Nilai Pending: 15 siswa   │  [Input Absensi]         │
│ - Izin Pending: 2 siswa     │  [Input Nilai]           │
│                             │  [Lihat Jadwal Minggu]   │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Summary Bulan Ini:                                       │
│ - Jam Mengajar: 72 jam  |  Kehadiran Saya: 95%         │
└─────────────────────────────────────────────────────────┘
```

**Mobile:**
- Clock In/Out button prominent (top, large)
- Next class highlighted
- Swipe untuk lihat jadwal selanjutnya
- Quick action floating button

---

### Parent Dashboard

**Layout (Mobile-First):**
```
┌─────────────────────┐
│ Dashboard           │
│                     │
│ ┌─────────────────┐ │
│ │   [👦 Foto]     │ │
│ │                 │ │
│ │ Ahmad Fauzi     │ │
│ │ Kelas 3A • 2345 │ │
│ │ [Lihat Profil]  │ │
│ └─────────────────┘ │
│                     │
│ Summary:            │
│ ┌─────────────────┐ │
│ │ ✅ Kehadiran    │ │
│ │    92%          │ │
│ │ Hadir 23 hari   │ │
│ └─────────────────┘ │
│                     │
│ ┌─────────────────┐ │
│ │ 💰 Status SPP   │ │
│ │ ⚠️ Belum Bayar  │ │
│ │ Rp 500.000      │ │
│ └─────────────────┘ │
│                     │
│ ┌─────────────────┐ │
│ │ 📊 Rata² Nilai  │ │
│ │    85           │ │
│ │ Semester 1      │ │
│ └─────────────────┘ │
│                     │
│ 📢 Pengumuman:      │
│ • Libur Natal...   │
│ • Rapat Ortu...    │
│                     │
│ Quick Actions:      │
│ [Bayar SPP]        │
│ [Ajukan Izin]      │
│ [Lihat Rapor]      │
│ [Hubungi Sekolah]  │
└─────────────────────┘
```

---

### Report Page (Financial Report Example)

**Layout (Desktop):**
```
┌─────────────────────────────────────────────────────────┐
│ Laporan Keuangan                                         │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Period: [Bulan Ini ▼]  Custom: [📅 Start] - [📅 End]   │
│         [Export: PDF ▼]                                  │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Summary:                                                 │
│ ┌──────────────────┬──────────────────┬────────────────┐│
│ │ Total Pemasukan  │ Total Tunggakan  │ Kolektibilitas ││
│ │  Rp 45.000.000   │  Rp 5.000.000    │      90%       ││
│ └──────────────────┴──────────────────┴────────────────┘│
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Breakdown per Jenis:        │  Breakdown per Kelas:     │
│ [Pie Chart]                 │  [Bar Chart]              │
│ - SPP: 40jt (89%)           │  Kelas 1: 15jt            │
│ - Uang Gedung: 3jt (7%)     │  Kelas 2: 12jt            │
│ - Lain-lain: 2jt (4%)       │  Kelas 3: 18jt            │
│                             │                            │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Tren 6 Bulan Terakhir:                                   │
│ [Line Chart: Jul-Des]                                    │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Detail Transaksi:                                        │
│ [Table: Date, Student, Type, Amount, Status]            │
│ ...                                                      │
└─────────────────────────────────────────────────────────┘
```

---

### Notification Center

**Layout:**
```
┌─────────────────────────────────┐
│ Notifikasi             [🔔 10]  │
│ ───────────────────────────────│
│                                 │
│ [Filter: Semua ▼] [Mark all ✓] │
│                                 │
│ ⚠️ URGENT                       │
│ 10 pembayaran pending verifikasi│
│ [Lihat Detail →]                │
│ 5 menit lalu                    │
│ ───────────────────────────────│
│                                 │
│ ⚠️ WARNING                      │
│ 12 siswa tunggakan > 1 bulan    │
│ [Lihat Detail →]                │
│ 1 jam lalu                      │
│ ───────────────────────────────│
│                                 │
│ ℹ️ INFO                         │
│ Pengumuman: Libur Natal        │
│ [Baca →]                        │
│ Kemarin                         │
│ ───────────────────────────────│
│                                 │
│ [Load More...]                  │
└─────────────────────────────────┘
```

---

## ✅ Definition of Done

### Code Level
- [ ] Unit test coverage minimal 70% untuk dashboard logic & report generation
- [ ] Integration test untuk critical flow (dashboard load, report generation, export)
- [ ] Code review approved
- [ ] No critical linter errors
- [ ] All API endpoints documented (Swagger/Postman)

### Functionality
- [ ] All acceptance criteria met untuk semua 12 user stories
- [ ] Dashboard untuk semua role (Principal, TU, Teacher, Parent) working
- [ ] Summary cards dengan real-time data working
- [ ] Charts/visualisasi (absensi, keuangan, akademik) working dan interactive
- [ ] Quick actions per role working (redirect ke halaman yang benar)
- [ ] Report generation (Keuangan, Absensi, Akademik, PSB) working
- [ ] Export to PDF, Excel, CSV working dengan proper formatting
- [ ] Notification system working dengan badge counter
- [ ] Custom date range untuk laporan working
- [ ] Recent activities timeline working

### UI/UX
- [ ] Responsive di mobile dan desktop (tested di iOS & Android)
- [ ] Loading state untuk all async actions (dashboard load, report generation)
- [ ] Skeleton loading untuk widgets (smooth UX)
- [ ] Error handling dengan user-friendly message dalam Bahasa Indonesia
- [ ] Success feedback (toast/notification) untuk setiap action
- [ ] Charts responsive dan interactive (hover, click)
- [ ] Dashboard load < 3 detik (dengan caching)
- [ ] Quick actions prominent dan easy to access

### Security
- [ ] Dashboard data filtered by role (Principal: all, TU: operational, Teacher: own data, Parent: own children)
- [ ] Report access controlled by RBAC
- [ ] Sensitive data (finance) hanya accessible oleh Principal & TU
- [ ] Export files secured (token-based download link)
- [ ] Notification tidak expose sensitive data

### Performance
- [ ] Dashboard load < 3 detik (dengan all widgets)
- [ ] Report generation < 30 detik untuk monthly report
- [ ] PDF generation < 5 detik
- [ ] Excel export < 10 detik untuk 1000 rows
- [ ] Charts render < 1 detik
- [ ] Cache dashboard summary data (refresh every 5 menit)
- [ ] Lazy loading untuk charts (load on scroll)

### Integration
- [ ] Integration dengan semua module tested (Student, Attendance, Payment, Grades, PSB, Teacher)
- [ ] Data aggregation accurate (summary match dengan detail data)
- [ ] Notification trigger working untuk all events
- [ ] Report data accurate (verified dengan manual calculation)

### Documentation
- [ ] API documentation complete dengan example requests/responses
- [ ] Database schema documented dengan relationships
- [ ] Report generation logic documented dengan examples
- [ ] User manual untuk setiap role (cara pakai dashboard, generate laporan) - Bahasa Indonesia
- [ ] Chart library documentation (how to customize)
- [ ] FAQ document ready

---

## 🔗 Dependencies

### External Dependencies
- **Chart Library:** Chart.js atau ApexCharts untuk visualisasi
- **PDF Library:** Puppeteer atau wkhtmltopdf untuk generate PDF report
- **Excel Library:** ExcelJS atau PHPSpreadsheet untuk export Excel
- **Queue System:** Redis atau database queue untuk async jobs
- **Storage:** Cloud storage (AWS S3, GCS) untuk generated reports
- **Push Notification:** FCM atau OneSignal (optional)

### Internal Dependencies (Must Complete First)
- **Epic 1:** Authentication & RBAC (untuk role-based dashboard)
- **Epic 2:** Student Management (untuk dashboard summary siswa)
- **Epic 3:** Attendance System (untuk dashboard & laporan absensi)
- **Epic 4:** Payment System (untuk dashboard & laporan keuangan)
- **Epic 5:** Grades & Report Cards (untuk laporan akademik)
- **Epic 6:** PSB (untuk dashboard & laporan PSB)
- **Epic 7:** Teacher Management (untuk dashboard guru & laporan)

### Blocking For
Epic 8 tidak blocking epic lain (ini adalah last epic, aggregate semua module).

---

## 🧪 Testing Strategy

### Unit Testing
- Dashboard logic: data aggregation, summary calculation
- Report generation: query logic, data formatting
- Export functions: PDF/Excel/CSV generation
- Notification triggers: event detection, message formatting
- Target coverage: 70%

### Integration Testing
- Dashboard load: aggregate data dari multiple modules
- Report generation: query data → format → generate file → save
- Export: dashboard → export PDF/Excel → download file
- Notification: event trigger → create notification → display di dashboard
- Chart rendering: data → chart library → display

### E2E Testing (Critical Paths)
1. **Principal Dashboard Load:**
   - Principal login → Dashboard load → All widgets display → Charts render → Quick actions working
2. **TU Task Queue:**
   - TU login → Dashboard load → Task queue dengan badge counter → Click pending task → Redirect ke page yang benar
3. **Generate Financial Report:**
   - User pilih periode → Generate report → Summary & charts display → Export PDF → Download successful
4. **Parent Dashboard:**
   - Parent login → Dashboard load → Student card display → Summary accurate → Click quick action "Bayar SPP" → Redirect ke payment page
5. **Notification System:**
   - Event trigger (e.g., pembayaran pending) → Notification created → Badge counter update → Display di notification center → Click → Redirect ke detail

### Performance Testing
- Dashboard load time (dengan 1000+ students, 50+ teachers)
- Report generation time (untuk data 1 tahun)
- PDF/Excel export time (untuk large dataset)
- Chart rendering time (dengan 100+ data points)
- Target:
  - Dashboard load < 3 detik
  - Report generation < 30 detik
  - PDF export < 5 detik
  - Excel export < 10 detik

### Security Testing
- Access control: Teacher coba akses principal dashboard (should be blocked)
- Data filtering: Parent hanya dapat lihat data anak sendiri (not other students)
- Report access: User coba download report orang lain (should be blocked)
- Sensitive data: Finance data tidak visible untuk unauthorized role

### UAT (User Acceptance Testing)
- Test dengan Principal untuk dashboard & laporan
- Test dengan TU untuk task queue & quick actions
- Test dengan sample Guru untuk dashboard guru
- Test dengan sample Parent untuk dashboard parent
- Collect feedback dan adjust before production

---

## 📅 Sprint Planning

### Sprint 9 (2 minggu) - 14 points
**Focus:** Dashboard untuk semua role & Notification system

**Stories:**
- US-DASH-001: Dashboard Kepala Sekolah (5 pts) - **Day 1-5**
  - Summary cards dengan data aggregation
  - Charts (absensi, keuangan, akademik)
  - Recent activities timeline
  - Quick actions
- US-DASH-002: Dashboard TU (3 pts) - **Day 6-7**
  - Task queue dengan badge counter
  - Quick actions (large buttons)
  - Summary hari ini
  - Calendar & reminders
- US-DASH-003: Dashboard Guru (3 pts) - **Day 8-9**
  - Jadwal hari ini dengan next class highlight
  - Clock in/out button
  - Tasks (absensi & nilai pending)
  - Quick actions
- US-DASH-004: Dashboard Orang Tua (3 pts) - **Day 10**
  - Student card dengan summary
  - Pengumuman terbaru
  - Quick actions
  - Mobile-first design

**Deliverables:**
- Dashboard working untuk semua role dengan data aggregation
- Charts interactive dan responsive
- Notification system dengan badge counter
- Quick actions working (redirect ke page yang benar)

**Sprint Goal:** "Semua stakeholder dapat monitor kondisi sekolah real-time via dashboard"

---

### Sprint 10 (3 minggu) - 19 points
**Focus:** Report Generation, Export Multi-Format, & Visualization

**Stories:**
- US-DASH-005: Laporan Keuangan (3 pts) - **Day 1-3**
  - Query financial data dengan periode
  - Summary & breakdown
  - Charts (pie, bar, line)
  - Export PDF & Excel
- US-DASH-007: Laporan Absensi (3 pts) - **Day 4-5**
  - Query attendance data (siswa & guru)
  - Rekap per kelas/guru
  - Highlight low attendance
  - Export PDF & Excel
- US-DASH-006: Laporan Akademik (3 pts) - **Day 6-7**
  - Query grades data per semester
  - Summary & distribusi nilai
  - Top performers & need attention
  - Export PDF & Excel
- US-DASH-008: Laporan PSB (2 pts) - **Day 8**
  - Query PSB data per tahun ajaran
  - Funnel chart & demographics
  - Export PDF & Excel
- US-DASH-010: Export Multi-Format (2 pts) - **Day 9**
  - Export PDF dengan professional layout
  - Export Excel dengan formatting
  - Export CSV for data integration
- US-DASH-011: Custom Date Range (2 pts) - **Day 10**
  - Date range picker
  - Preset periods
  - Validation
- US-DASH-009: Notifikasi & Alert (3 pts) - **Day 11-12**
  - Notification center
  - Badge counter
  - Mark as read
  - Push notification (optional)
- US-DASH-012: Grafik & Visualisasi (3 pts) - **Day 13-15**
  - Chart.js/ApexCharts integration
  - Interactive charts (hover, click)
  - Responsive charts
  - Export chart as image

**Deliverables:**
- All report types working dengan accurate data
- Export PDF/Excel/CSV working dengan proper formatting
- Custom date range working untuk flexible reporting
- Notification system complete
- All charts interactive dan responsive

**Sprint Goal:** "Complete end-to-end dashboard & reporting system dengan visualisasi data yang powerful"

---

## 🎯 Acceptance Criteria (Epic Level)

### Functional
- [ ] Dashboard untuk setiap role (Principal, TU, Teacher, Parent) working
- [ ] Summary cards dengan real-time data accurate
- [ ] Charts/visualisasi (absensi, keuangan, akademik) working
- [ ] Quick actions per role working dan redirect ke page yang benar
- [ ] Task queue untuk TU dengan badge counter working
- [ ] Recent activities timeline working
- [ ] Generate reports (Keuangan, Absensi, Akademik, PSB) working dengan accurate data
- [ ] Export to Excel & PDF working dengan proper formatting
- [ ] Export to CSV working untuk data integration
- [ ] Custom date range untuk laporan working dengan validation
- [ ] Notification system working dengan badge counter
- [ ] Notification center dengan mark as read working
- [ ] Charts interactive (hover, click untuk drill-down)
- [ ] Pengumuman system untuk parent dashboard working

### Non-Functional
- [ ] Mobile-responsive (tested di iOS & Android)
- [ ] Dashboard load < 3 detik (dengan caching)
- [ ] Report generation < 30 detik untuk monthly report
- [ ] PDF generation < 5 detik
- [ ] Excel export < 10 detik untuk 1000 rows
- [ ] Charts render < 1 detik
- [ ] User-friendly error messages dalam Bahasa Indonesia
- [ ] Success feedback untuk setiap action
- [ ] Loading indicator untuk generate report & export
- [ ] Skeleton loading untuk dashboard widgets

### Integration
- [ ] Integration dengan semua module (Student, Attendance, Payment, Grades, PSB, Teacher) working
- [ ] Data aggregation accurate (summary match dengan detail data)
- [ ] Notification trigger working untuk all events
- [ ] Report data accurate (verified dengan manual calculation)

### Technical
- [ ] API documentation complete (Swagger)
- [ ] Database schema implemented dengan proper indexes
- [ ] Unit test coverage 70%
- [ ] Integration test untuk critical flows
- [ ] Report generation queue working (async processing)
- [ ] Cache strategy implemented untuk dashboard data
- [ ] Generated reports auto-cleanup (after 30 days)

---

## 🚧 Risks & Mitigation

### Risk 1: Dashboard Performance dengan Large Dataset
**Impact:** High - Slow dashboard dapat frustrate users  
**Probability:** Medium  
**Mitigation:**
- Cache summary data (refresh every 5 menit)
- Database views untuk complex aggregation queries
- Lazy loading untuk charts (load on scroll)
- Pagination untuk recent activities
- Database indexing untuk frequently queried columns
- Use Redis untuk real-time counters (badge)
- Monitor query performance (add query logging)

---

### Risk 2: Report Generation Timeout untuk Large Dataset
**Impact:** Medium - User tidak dapat generate report untuk periode panjang  
**Probability:** Medium  
**Mitigation:**
- Async report generation dengan queue
- Progress indicator dengan estimated time
- Send download link via email (untuk large report)
- Limit report period (max 1 tahun per report)
- Database query optimization dengan proper indexes
- Chunked processing untuk large dataset
- Add timeout warning (e.g., "Report untuk data > 1 tahun akan dikirim via email")

---

### Risk 3: PDF/Excel Export Format Issues
**Impact:** Medium - Export result tidak sesuai ekspektasi (layout broken, data incomplete)  
**Probability:** Medium  
**Mitigation:**
- Extensive testing dengan various data scenarios
- PDF template dengan responsive layout
- Excel formatting dengan auto-width column
- Header & footer consistent dengan logo sekolah
- Page break handling untuk multi-page report
- Preview before export (show sample)
- Multiple format options (user can choose yang paling sesuai)

---

### Risk 4: Chart Library Compatibility & Customization
**Impact:** Medium - Chart tidak sesuai design atau tidak responsive  
**Probability:** Low  
**Mitigation:**
- Choose mature & well-documented library (Chart.js atau ApexCharts)
- Test chart rendering di berbagai screen sizes
- Custom theme untuk consistent look & feel
- Fallback untuk browser yang tidak support canvas
- Export chart as image untuk presentation
- Documentation untuk future customization

---

### Risk 5: Real-time Data Accuracy
**Impact:** High - Dashboard menampilkan data yang tidak accurate  
**Probability:** Low  
**Mitigation:**
- Data validation di setiap source module
- Reconciliation job untuk verify data consistency
- Cache invalidation strategy (clear cache when source data change)
- Add "Last updated" timestamp di setiap widget
- Manual refresh button untuk force data reload
- Alert untuk data anomaly (e.g., sudden spike/drop)
- Audit log untuk track data changes

---

### Risk 6: Notification Spam
**Impact:** Medium - User overwhelmed dengan terlalu banyak notification  
**Probability:** High  
**Mitigation:**
- Smart notification grouping (e.g., "10 pembayaran pending" instead of 10 separate notifications)
- Notification priority (urgent/warning/info)
- User preference untuk notification settings (opt-in/opt-out per type)
- Daily digest untuk non-urgent notifications
- Mark all as read functionality
- Auto-expire old notifications (after 30 days)
- Rate limiting untuk similar notifications (prevent duplicate)

---

## 📊 Success Metrics & KPIs

### Sprint 9 (Dashboard)
- [ ] 100% user stories completed (4/4)
- [ ] Zero critical bugs in production
- [ ] Dashboard load time < 3 detik (tested dengan production-like data)
- [ ] All role dashboards working dengan accurate data
- [ ] User acceptance from all stakeholder (Principal, TU, Teacher, Parent)

### Sprint 10 (Reports & Visualization)
- [ ] 100% user stories completed (8/8)
- [ ] All report types working dengan accurate data
- [ ] Export success rate > 98%
- [ ] PDF/Excel formatting approved by users
- [ ] Charts rendering correctly di all screen sizes
- [ ] Notification system working dengan badge counter

### Epic Level (Post-Launch, First 3 Months)
- [ ] Total 33 points delivered
- [ ] 100% user login akses dashboard sebagai landing page
- [ ] Dashboard load time < 3 detik (average)
- [ ] 90% user pakai quick actions dari dashboard (Google Analytics)
- [ ] Laporan di-generate 100% tepat waktu (bulanan)
- [ ] Export success rate > 98%
- [ ] Report generation time < 30 detik (average)
- [ ] User satisfaction score > 4/5 untuk dashboard relevance (survey)
- [ ] Principal generate laporan minimal 1x per minggu
- [ ] TU check task queue minimal 3x per hari
- [ ] Guru check dashboard untuk jadwal 100% hari kerja
- [ ] Parent check dashboard untuk anak minimal 1x per minggu

### Business Metrics (First Year)
- [ ] Decision making time reduced 40% (dengan data-driven insights)
- [ ] Report generation time reduced dari 2 hari ke 30 detik (99% improvement)
- [ ] Task completion rate increased 30% (dengan task queue prioritization)
- [ ] User engagement increased 50% (dashboard sebagai main landing page)
- [ ] Paper usage reduced 80% (dengan digital reports)
- [ ] Cost saving dari manual reporting: Rp 2jt/tahun

---

## 📝 Notes & Assumptions

### Assumptions
1. Dashboard sebagai landing page untuk semua user (bukan list/table)
2. Data refresh interval 5 menit acceptable (tidak perlu real-time penuh)
3. Report generation async acceptable (user willing to wait atau receive via email)
4. 30 days retention untuk generated reports (after that auto-cleanup)
5. Chart library (Chart.js/ApexCharts) sufficient untuk visualization needs
6. PDF export quality acceptable untuk print (tidak perlu high-resolution)
7. Notification push via web (tidak perlu mobile app push untuk MVP)

### Out of Scope (Epic 8 MVP)
- ❌ Customizable dashboard (drag & drop widgets) - Phase 2
- ❌ Real-time dashboard (auto-refresh setiap X detik dengan WebSocket) - Phase 2
- ❌ Push notifications via mobile app (FCM/OneSignal) - Phase 2
- ❌ Dashboard themes (light/dark mode) - Phase 2
- ❌ Advanced analytics (predictive, trend analysis) - Phase 2
- ❌ Data export to external system (API integration) - Phase 2
- ❌ Custom report builder (user define report structure) - Phase 2
- ❌ Multi-language support untuk reports - Phase 2

### Nice to Have (Not Required for MVP)
- Dashboard widget personalization (show/hide widgets)
- Export dashboard snapshot as image
- Scheduled report generation (auto-generate & email every month)
- Comparison report (current vs previous period)
- Drill-down dari dashboard ke detail page dengan filters preserved
- Export chart as image (standalone)
- Announcement scheduling (publish at specific time)

### Technical Decisions
- Chart library: Chart.js (simpler) atau ApexCharts (more features) - TBD during development
- PDF generation: Puppeteer (flexible) atau wkhtmltopdf (faster) - TBD based on performance test
- Queue system: Database queue (simpler) atau Redis queue (faster) - Redis preferred for scalability
- Cache: Redis untuk summary data & badge counters
- Storage: Cloud storage untuk generated reports (auto-cleanup after 30 days)
- Report filename convention: `[ReportType]_[Period]_[Timestamp].pdf`

---

## 🔄 Review & Refinement

### Sprint 9 Review
**Date:** TBD (end of sprint 9)  
**Attendees:** Development Team, Product Owner, All Stakeholder (Principal, TU, Teacher, Parent)

**Review Checklist:**
- [ ] Demo dashboard untuk setiap role
- [ ] Get feedback tentang dashboard layout & widgets relevance
- [ ] Test dashboard performance dengan production-like data
- [ ] Verify data accuracy (summary vs detail)
- [ ] Test quick actions (redirect working?)
- [ ] Test notification system dengan badge counter
- [ ] Mobile responsive testing (iOS & Android)
- [ ] Identify improvement areas untuk Sprint 10

---

### Sprint 10 Review
**Date:** TBD (end of sprint 10)  
**Attendees:** Development Team, Product Owner, All Stakeholder

**Review Checklist:**
- [ ] Demo complete dashboard & reporting system
- [ ] Demo all report types dengan real data
- [ ] Verify report data accuracy dengan manual calculation
- [ ] Test export to PDF/Excel/CSV dengan various scenarios
- [ ] Test custom date range dengan edge cases
- [ ] Get feedback tentang report format & layout
- [ ] Test charts interactivity & responsiveness
- [ ] Performance review (dashboard load, report generation, export)
- [ ] Security review (data access, RBAC)
- [ ] Documentation complete check
- [ ] User acceptance testing (UAT) dengan actual users
- [ ] Go/No-Go decision untuk production launch

---

### Epic Review (Post-Launch, After 3 Months)
**Date:** TBD (setelah 3 bulan usage)  
**Attendees:** All stakeholders

**Retrospective Questions:**
1. **What went well?**
   - Dashboard usage statistics (how often users access?)
   - Most used widgets & quick actions
   - Report generation frequency & types
   - User satisfaction scores
2. **What didn't go well?**
   - Performance issues (dashboard load, report generation)
   - Data accuracy issues (any discrepancies?)
   - Export format issues (layout broken, data incomplete?)
   - Notification spam (too many notifications?)
3. **What can we improve?**
   - Dashboard widgets (add/remove/rearrange?)
   - Additional report types needed?
   - Chart customization needs?
   - Mobile UX improvements?
4. **Metrics review:**
   - Did we achieve success metrics?
   - Dashboard load time?
   - Report generation success rate?
   - User engagement rate?

**Action Items for Phase 2:**
- List features untuk next release (customizable dashboard, real-time updates, advanced analytics)
- Prioritize based on user feedback & impact
- Plan timeline untuk development

---

## ✅ Epic Checklist (Before Production Launch)

### Development
- [ ] All 12 user stories implemented dan tested
- [ ] Code merged to main branch
- [ ] Database migration successful di staging & production
- [ ] API documentation published dan reviewed
- [ ] Dashboard data aggregation logic documented
- [ ] Report generation logic documented dengan examples

### Testing
- [ ] Unit test pass (coverage 70%)
- [ ] Integration test pass untuk all critical flows
- [ ] E2E test pass (5 critical paths)
- [ ] Security test pass (RBAC, data access control)
- [ ] Performance test pass (dashboard < 3s, report < 30s, export < 10s)
- [ ] Dashboard data accuracy verified dengan manual calculation
- [ ] Report data accuracy verified dengan source modules
- [ ] UAT approved by all stakeholder (Principal, TU, Teacher, Parent)

### Deployment
- [ ] Deployed to staging environment
- [ ] Staging tested dengan production-like data (100+ students, 50+ teachers, 1 year data)
- [ ] Production database backup ready
- [ ] Rollback plan documented
- [ ] Deployed to production
- [ ] Monitoring & logging active (error tracking, performance monitoring)
- [ ] Alert configured untuk critical issues (dashboard down, report generation fail)

### Documentation
- [ ] Technical documentation complete (architecture, database, APIs, caching strategy)
- [ ] User manual untuk Principal (cara pakai dashboard, generate laporan) - Bahasa Indonesia
- [ ] User manual untuk TU (cara pakai dashboard, task queue, quick actions) - Bahasa Indonesia
- [ ] User manual untuk Guru (cara pakai dashboard, clock in/out) - Bahasa Indonesia
- [ ] User manual untuk Parent (cara pakai dashboard, baca pengumuman) - Bahasa Indonesia
- [ ] Report generation guide dengan examples
- [ ] API documentation complete (Swagger/Postman)
- [ ] FAQ document ready

### Training & Support
- [ ] Training session untuk Principal (dashboard & laporan overview)
- [ ] Training session untuk TU (task queue & report generation)
- [ ] Training session untuk Guru (dashboard guru & quick actions)
- [ ] Training session untuk Parent (dashboard parent & quick actions)
- [ ] Demo video untuk common tasks (max 3 menit each)
- [ ] Internal troubleshooting guide untuk common issues
- [ ] Support hotline/WhatsApp prepared

### Data Migration (if applicable)
- [ ] Historical data verified untuk dashboard summary
- [ ] Sample reports generated untuk verify data accuracy
- [ ] Notification backlog cleared (start fresh)
- [ ] Announcements migrated (if applicable)

### Communication
- [ ] Announcement ke all users tentang dashboard baru
- [ ] Info session scheduled untuk Q&A (optional)
- [ ] Feedback channel setup (WhatsApp group atau email)
- [ ] User guide distributed via email/WhatsApp

---

## 📞 Contact & Support

**Epic Owner:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733  
**Email:** [Your Email]

**For Technical Issues:**
- WhatsApp: +62 857-1583-8733 (Developer)
- Email: dev-support@sekolah.app
- Response time: < 4 jam (during business hours)

**For Dashboard/Report Questions:**
- Contact TU: [TU Name & Phone]
- Email: support@sekolah.app

**For Emergency (Dashboard Down):**
- Call Developer: +62 857-1583-8733
- Escalate to: [IT Manager/CTO]
- Fallback: Manual process documented

---

**Document Status:** ✅ Ready for Sprint Planning  
**Last Review:** 13 Desember 2025  
**Next Review:** After Sprint 9 completion

---

*Catatan: Dokumen ini adalah living document dan akan diupdate seiring progress sprint. Semua perubahan akan dicatat di section "Change Log" di bawah.*

## 📋 Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 13 Des 2025 | 1.0 | Initial creation of EPIC 8 document | Zulfikar Hidayatullah |

---

## 🎨 Appendix: Wireframes & Mockups

### A. Principal Dashboard (Desktop)
```
┌─────────────────────────────────────────────────────────────┐
│ Dashboard Kepala Sekolah            Terakhir Update: 10:30  │
│                                              [Export PDF]    │
│ ─────────────────────────────────────────────────────────── │
│                                                              │
│ ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│ │ 📊 Siswa │  │ 👨‍🏫 Guru  │  │ ✅ Hadir │  │ 💰 Masuk │    │
│ │   245    │  │    32    │  │   92%    │  │  45jt    │    │
│ │  aktif   │  │  orang   │  │  ↑ 2%   │  │  ↑ 10%  │    │
│ └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│                                                              │
│ ─────────────────────────────────────────────────────────── │
│                                                              │
│ Grafik Absensi 30 Hari    │   Pemasukan per Jenis Pembayaran│
│ ┌──────────────────────┐  │   ┌──────────────────────┐     │
│ │     /\    /\          │  │   │       SPP ████████   │     │
│ │    /  \  /  \/\       │  │   │    Gedung ██         │     │
│ │   /    \/      \      │  │   │  Lainnya ██          │     │
│ │  /              \     │  │   └──────────────────────┘     │
│ └──────────────────────┘  │                                 │
│                            │                                 │
│ ─────────────────────────────────────────────────────────── │
│                                                              │
│ Quick Stats:               │  Recent Activities:             │
│ ⚠️ Kehadiran < 80%: 5      │  • 10 siswa bayar SPP          │
│ ⚠️ Tunggakan SPP: 12       │    5 menit lalu                │
│ ⚠️ Guru Terlambat: 2       │  • Rapor kelas 1A generated    │
│ ⚠️ PSB Pending: 8          │    1 jam lalu                  │
│                            │  • 3 pendaftar PSB baru        │
│ [Lihat Detail →]          │    2 jam lalu                  │
│                            │  ...                            │
│ ─────────────────────────────────────────────────────────── │
│                                                              │
│ Quick Actions:                                               │
│ [📊 Laporan Keuangan] [🎓 Laporan Akademik] [📄 Lap. Bulanan]│
└─────────────────────────────────────────────────────────────┘
```

### B. TU Dashboard (Desktop)
```
┌─────────────────────────────────────────────────────────┐
│ Dashboard TU                   Rabu, 13 Desember 2025   │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Task Queue - Pending:                                    │
│ ┌────────────────┐ ┌────────────────┐ ┌──────────────┐ │
│ │ ⚠️ Pembayaran  │ │ 📋 PSB         │ │ 🏥 Izin/Sakit │ │
│ │    Pending     │ │    Pending     │ │    Pending    │ │
│ │      10        │ │       8        │ │       5       │ │
│ │ [Verifikasi →] │ │ [Verifikasi →] │ │ [Verifikasi →]│ │
│ └────────────────┘ └────────────────┘ └──────────────┘ │
│                                                          │
│ ⏰ Guru Belum Clock In Hari Ini (3 orang)               │
│ - Pak Ahmad, Bu Siti, Pak Budi         [Lihat Detail →]│
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Quick Actions:                                           │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐     │
│ │ 💰 Input     │ │ ✅ Input     │ │ 👤 Tambah    │     │
│ │  Pembayaran  │ │  Absensi     │ │  Siswa Baru  │     │
│ └──────────────┘ └──────────────┘ └──────────────┘     │
│ ┌──────────────┐ ┌──────────────┐                      │
│ │ 📄 Generate  │ │ ✔️ Verifikasi │                      │
│ │  Tagihan SPP │ │  PSB         │                      │
│ └──────────────┘ └──────────────┘                      │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Summary Hari Ini:          │  Calendar & Reminders:     │
│ • Pemasukan: Rp 2.500.000  │  📅 13 Desember 2025      │
│ • Siswa Hadir: 92%         │                            │
│ • Guru Hadir: 30/32        │  Upcoming:                 │
│                            │  • 15 Des: Rapat Guru     │
│                            │  • 20 Des: Tutup Semester │
└─────────────────────────────────────────────────────────┘
```

### C. Teacher Dashboard (Mobile)
```
┌─────────────────────┐
│ Dashboard Guru      │
│ Pak Budi, S.Pd      │
│ ─────────────────── │
│                     │
│ Presensi Hari Ini:  │
│ ┌─────────────────┐ │
│ │ ✅ Clock In     │ │
│ │    07:25        │ │
│ │                 │ │
│ │ [CLOCK OUT →]   │ │
│ └─────────────────┘ │
│                     │
│ ─────────────────── │
│                     │
│ Jadwal Hari Ini:    │
│ 📚 Rabu, 13 Des     │
│                     │
│ ⏰ 08:00-09:30      │
│ Kelas 3A           │
│ Matematika          │
│ ✅ Absensi OK       │
│                     │
│ ⏰ 09:30-11:00      │
│ Kelas 3B           │
│ Matematika          │
│ ⚠️ Belum Input     │
│ [Input Absensi →]  │
│                     │
│ ─────────────────── │
│                     │
│ Tasks:              │
│ • Nilai Pending:    │
│   15 siswa          │
│ • Izin Pending:     │
│   2 siswa           │
│                     │
│ ─────────────────── │
│                     │
│ Quick Actions:      │
│ [Input Absensi]    │
│ [Input Nilai]      │
│ [Lihat Jadwal]     │
│                     │
│ ─────────────────── │
│                     │
│ Summary Bulan Ini:  │
│ • Jam Mengajar: 72  │
│ • Kehadiran: 95%    │
└─────────────────────┘
```

### D. Parent Dashboard (Mobile)
```
┌─────────────────────┐
│ Dashboard           │
│ ─────────────────── │
│                     │
│ ┌─────────────────┐ │
│ │  [👦 Foto Anak] │ │
│ │                 │ │
│ │  Ahmad Fauzi    │ │
│ │  Kelas 3A • 2345│ │
│ │                 │ │
│ │ [Lihat Profil]  │ │
│ └─────────────────┘ │
│                     │
│ ─────────────────── │
│                     │
│ Summary:            │
│                     │
│ ┌─────────────────┐ │
│ │ ✅ Kehadiran    │ │
│ │    Bulan Ini    │ │
│ │       92%       │ │
│ │                 │ │
│ │ Hadir: 23 hari  │ │
│ │ Alpha: 2 hari   │ │
│ └─────────────────┘ │
│                     │
│ ┌─────────────────┐ │
│ │ 💰 Status SPP   │ │
│ │                 │ │
│ │ ⚠️ Belum Bayar  │ │
│ │  Rp 500.000     │ │
│ │                 │ │
│ │ [Bayar Sekarang]│ │
│ └─────────────────┘ │
│                     │
│ ┌─────────────────┐ │
│ │ 📊 Rata² Nilai  │ │
│ │  Semester Ini   │ │
│ │       85        │ │
│ │    (Baik)       │ │
│ └─────────────────┘ │
│                     │
│ ─────────────────── │
│                     │
│ 📢 Pengumuman:      │
│ • Libur Natal...   │
│ • Rapat Ortu...    │
│ [Lihat Semua →]    │
│                     │
│ ─────────────────── │
│                     │
│ Recent Activities:  │
│ • Hadir hari ini   │
│ • Bayar SPP Des    │
│ • Nilai IPA: 85    │
│                     │
│ ─────────────────── │
│                     │
│ Quick Actions:      │
│ [💰 Bayar SPP]     │
│ [🏥 Ajukan Izin]   │
│ [📊 Lihat Rapor]   │
│ [📞 Hubungi Sekolah]│
└─────────────────────┘
```

### E. Financial Report (Desktop)
```
┌─────────────────────────────────────────────────────────┐
│ Laporan Keuangan                                         │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Period: [Bulan Ini ▼]  atau  Custom: [📅 Start - End] │
│                                                          │
│ [Export: PDF ▼]  [Export: Excel]  [Export: CSV]        │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Summary - Desember 2025:                                 │
│                                                          │
│ ┌──────────────────┬──────────────────┬────────────────┐│
│ │ Total Pemasukan  │ Total Tunggakan  │ Kolektibilitas ││
│ │                  │                  │                ││
│ │  Rp 45.000.000   │  Rp 5.000.000    │      90%       ││
│ │                  │                  │    [████████░] ││
│ └──────────────────┴──────────────────┴────────────────┘│
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Breakdown per Jenis:       │  Breakdown per Kelas:      │
│ ┌──────────────────┐       │  ┌──────────────────┐     │
│ │    [Pie Chart]   │       │  │   [Bar Chart]    │     │
│ │                  │       │  │                  │     │
│ │  SPP: 40jt (89%) │       │  │  Kelas 1: 15jt   │     │
│ │  Gedung: 3jt (7%)│       │  │  Kelas 2: 12jt   │     │
│ │  Lain: 2jt (4%)  │       │  │  Kelas 3: 18jt   │     │
│ └──────────────────┘       │  └──────────────────┘     │
│                             │                            │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Tren 6 Bulan Terakhir:                                   │
│ ┌────────────────────────────────────────────────────┐  │
│ │        [Line Chart: Jul - Des 2025]               │  │
│ │   50jt ┤                                  ⬤        │  │
│ │   40jt ┤        ⬤---⬤---⬤---⬤---⬤---⬤            │  │
│ │   30jt ┤   ⬤                                       │  │
│ │   20jt ┤                                           │  │
│ │        └──Jul─Aug─Sep─Oct─Nov─Dec                 │  │
│ └────────────────────────────────────────────────────┘  │
│                                                          │
│ ─────────────────────────────────────────────────────── │
│                                                          │
│ Detail Transaksi:                                        │
│ [Table with pagination]                                  │
│ Tanggal  │ Siswa        │ Jenis    │ Jumlah      │ Ket │
│ ──────────────────────────────────────────────────────  │
│ 01 Des   │ Ahmad Fauzi  │ SPP      │ Rp 500.000  │ ✅  │
│ 01 Des   │ Siti Aisyah  │ SPP      │ Rp 500.000  │ ✅  │
│ 02 Des   │ Budi Santoso │ Gedung   │ Rp 2.000.000│ ✅  │
│ ...                                                      │
│                                                          │
│ [< Prev]  Page 1 of 10  [Next >]                        │
└─────────────────────────────────────────────────────────┘
```

---

**End of EPIC 8 Document**
