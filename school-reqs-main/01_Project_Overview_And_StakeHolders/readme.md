# Project Overview & Stakeholders

## 📋 Ringkasan Eksekutif

**Nama Proyek:** Sistem Manajemen Sekolah SD (School Management System)

**Periode Pengembangan:** 5-6 bulan (target sebelum tahun ajaran baru)

**Model Bisnis:** SaaS / Langganan bulanan dengan biaya implementasi awal

**Status:** Planning & Requirements Gathering

---

## 🎯 Latar Belakang & Masalah

### Kondisi Saat Ini
Sekolah Dasar swasta dengan ±180 siswa aktif saat ini menghadapi tantangan besar dalam administrasi operasional:

- **Administrasi Manual:** Semua proses (absensi, pembayaran SPP, rekap nilai, laporan keuangan) masih dilakukan secara manual
- **Keterlambatan Laporan:** Sering terjadi delay dalam pelaporan kepada kepala sekolah dan stakeholders
- **Data Tercecer:** Tidak ada sistem terpusat, data tersebar di berbagai dokumen fisik
- **Beban Kerja Berat:** Staf TU (2 orang) kewalahan menangani semua administrasi manual
- **Komunikasi Lambat:** Informasi ke orang tua sering terlambat atau tidak tersampaikan

### Dampak Masalah
- Efisiensi operasional rendah
- Risiko kehilangan data tinggi
- Kesulitan dalam pengambilan keputusan berbasis data
- Pengalaman orang tua dan guru kurang optimal
- Proses PSB (Penerimaan Siswa Baru) memakan waktu lama

---

## 🏫 Profil Sekolah

| Aspek | Detail |
|-------|--------|
| **Jenjang** | SD (Sekolah Dasar) |
| **Lokasi** | Satu kampus di kota menengah, Jawa |
| **Jumlah Siswa** | ±180 siswa aktif |
| **Struktur Kelas** | 2 kelas per tingkat (Kelas I - VI) = 12 kelas |
| **Tenaga Pendidik** | 12 guru tetap + 3 guru honorer |
| **Staf Administrasi** | 2 staf TU penuh waktu |
| **Kurikulum** | Kurikulum 2013 (K13) |
| **Infrastruktur IT** | Internet (tidak selalu stabil), 1-2 komputer TU, 1 laptop kepala sekolah, guru mayoritas smartphone |

---

## 🎯 Tujuan Proyek

### Tujuan Utama
1. **Digitalisasi proses administrasi** sekolah untuk meningkatkan efisiensi operasional
2. **Centralisasi data** dalam satu sistem terintegrasi
3. **Otomasi pelaporan** keuangan dan akademik
4. **Meningkatkan komunikasi** antara sekolah dengan orang tua
5. **Mempermudah akses informasi** bagi semua stakeholders

### Tujuan Spesifik
- Mengurangi beban kerja TU minimal 40%
- Mempercepat proses pelaporan dari mingguan menjadi real-time
- Meningkatkan kepuasan orang tua melalui transparansi informasi
- Memfasilitasi PSB online untuk menjangkau calon siswa lebih luas
- Memberikan data analytics untuk pengambilan keputusan kepala sekolah

---

## 👥 Stakeholders & Peran

### 1. **Kepala Sekolah** (Primary Stakeholder)
**Kebutuhan:**
- Dashboard eksekutif untuk monitoring real-time
- Laporan keuangan (harian, mingguan, bulanan, semester)
- Rekap absensi siswa dan guru
- Ringkasan akademik dan grafik performa sekolah
- Evaluasi kinerja guru
- Export laporan (Excel & PDF)

**Akses:**
- Full visibility (read-only untuk sebagian besar modul)
- Approval workflow (evaluasi guru, keputusan strategis)

---

### 2. **Staf TU / Administrasi** (Core Users)
**Kebutuhan:**
- **Manajemen Data Siswa:** pendaftaran, update profil, mutasi
- **Manajemen Pembayaran:** pencatatan SPP, uang gedung, seragam, kegiatan
- **Rekonsiliasi Keuangan:** rekap pemasukan, piutang, export Excel
- **PSB Online:** verifikasi dokumen, approval pendaftaran
- **Cetak Dokumen:** kwitansi, surat keterangan, rapor
- **Manajemen Guru & Staf:** data kepegawaian, jadwal, perhitungan honor

**Akses:**
- Full CRUD untuk modul siswa, pembayaran, PSB
- Interface yang sangat user-friendly (tidak tech-savvy)

---

### 3. **Guru** (Daily Users)
**Kebutuhan:**
- **Absensi Siswa:** 
  - Input absensi harian pagi (per kelas)
  - Input absensi per mata pelajaran
  - Verifikasi izin/sakit (lihat dokumen pendukung)
- **Input Nilai:** UH, UTS, UAS, praktik, sikap, proyek
- **Rekap Nilai:** per siswa, per kelas
- **Generate Rapor:** otomatis berdasarkan bobot komponen
- **Presensi Pribadi:** clock in/out dengan PIN + location timestamp
- **Jadwal Mengajar:** lihat jadwal dan rekap jam mengajar

**Akses:**
- Akses terbatas pada kelas yang diajar
- Input & edit data akademik
- Read-only untuk data non-akademik
- Mobile-friendly (akses via smartphone)

---

### 4. **Orang Tua** (External Users)
**Kebutuhan:**
- **Info Pembayaran:** status SPP, tunggakan, history pembayaran
- **Pengumuman:** broadcast dari sekolah
- **Perkembangan Anak:** absensi, nilai, rapor online
- **Pengajuan Izin/Sakit:** upload surat dokumen via foto
- **Reminder:** notifikasi pembayaran (WhatsApp, email, in-app)

**Akses:**
- Read-only untuk data anak sendiri
- Submit izin/sakit
- Akses via smartphone (PWA/responsive web)
- Login sederhana (no. HP / email + password)

---

### 5. **Siswa** (Secondary Users - Future Phase)
**Kebutuhan (Tidak Prioritas MVP):**
- Lihat pengumuman
- Lihat tugas/PR
- Lihat jadwal pelajaran

**Akses:**
- Sangat terbatas
- Fase 2 pengembangan

---

## 📦 Fitur Utama

### **Phase 1 - MVP (Target: 5-6 bulan)**

#### 1. Modul Absensi Digital
- **Dua mode absensi:**
  - Absensi harian pagi (semua siswa per kelas)
  - Absensi per mata pelajaran (tracking kehadiran per jam)
- **Manajemen izin/sakit:**
  - Orang tua submit izin + upload dokumen
  - Guru/TU verifikasi status
- **Presensi guru:**
  - Login + PIN dengan timestamp lokasi
  - Rekap kehadiran guru untuk perhitungan honor
- **Laporan:**
  - Rekap absensi harian/mingguan/bulanan
  - Export Excel & PDF

#### 2. Sistem Pembayaran SPP
- **Jenis pembayaran:**
  - SPP bulanan / semester
  - Uang gedung (sekali bayar)
  - Seragam, kegiatan, donasi, dll (multi-kategori)
- **Metode pencatatan:**
  - Manual input (tunai/transfer)
  - Virtual Account (fase future)
- **Reminder otomatis:**
  - WhatsApp (prioritas)
  - Email & notifikasi in-app (sekunder)
- **Laporan:**
  - Rekap pemasukan per kategori
  - Status tunggakan per siswa
  - Rekonsiliasi keuangan bulanan

#### 3. Rekap Nilai & Rapor Digital (K13)
- **Komponen penilaian:**
  - UH (Penilaian Harian)
  - UTS, UAS
  - Praktik/Portofolio
  - Nilai Sikap (Spiritual & Sosial)
  - Proyek/Penugasan
  - Nilai keterampilan (KK/PK)
- **Manajemen rapor:**
  - Generate rapor otomatis per semester
  - Bobot komponen nilai configurable
  - Template rapor standar (PDF siap cetak)
  - Akses online untuk orang tua
- **Laporan:**
  - Rekap nilai per kelas/per siswa
  - Analisa performa akademik

#### 4. Pendaftaran Siswa Baru (PSB Online)
- **Alur pendaftaran:**
  1. Daftar online (form data calon siswa)
  2. Upload dokumen (akte, KK, KTP ortu, rapor, pas foto, surat pindah)
  3. Verifikasi admin
  4. (Opsional) Tes/seleksi
  5. Pengumuman
  6. Daftar ulang & pembayaran formulir
- **Payment gateway (opsional MVP):**
  - Virtual Account untuk biaya formulir
  - Rekonsiliasi otomatis
- **Tracking:**
  - Status pendaftaran per calon siswa
  - Notifikasi ke orang tua (email/WhatsApp)

#### 5. Dashboard Sekolah
- **Dashboard Kepala Sekolah:**
  - Grafik jumlah siswa aktif
  - Rekap absensi (siswa & guru)
  - Ringkasan keuangan (pemasukan vs target)
  - Alert tunggakan SPP
  - Summary akademik (rata-rata nilai per kelas)
- **Dashboard TU:**
  - Antrian verifikasi (izin, PSB, pembayaran)
  - Quick action (input pembayaran, cetak kwitansi)
  - Kalender kegiatan
- **Dashboard Guru:**
  - Jadwal hari ini
  - Siswa yang belum absen
  - Quick action (input absensi, input nilai)

#### 6. Manajemen Guru & Staf
- **Data kepegawaian:**
  - Profil guru (biodata, kontak, foto)
  - Status (tetap/honorer)
  - Mata pelajaran yang diampu
- **Jadwal mengajar:**
  - Input jadwal per guru
  - Rekap jam mengajar
- **Perhitungan honor:**
  - Gaji tetap + honor per jam (kombinasi)
  - Export slip gaji (PDF)
- **Evaluasi guru:**
  - Input evaluasi dari kepala sekolah
  - Survei kepuasan orang tua/siswa (opsional fase 1)

#### 7. Website Sekolah (Public-Facing)
- **Halaman informasi:**
  - Profil sekolah
  - Visi & Misi
  - Fasilitas
  - Kontak
- **Portal PSB:**
  - Link ke form pendaftaran online
  - Informasi jadwal & syarat pendaftaran
- **Pengumuman publik:**
  - Berita sekolah
  - Agenda kegiatan

---

### **Phase 2 - Advanced Features (Future)**

- Integrasi Payment Gateway (VA, QRIS)
- Presensi biometrik (fingerprint/face recognition)
- Mobile app native (iOS & Android)
- Modul HR lanjutan (cuti, penggajian kompleks)
- Integrasi Dapodik
- E-Learning sederhana (assignment online, materi digital)
- Notifikasi push real-time
- Dashboard analytics lanjutan (predictive)

---

## 🔧 Batasan & Constraints

### Teknis
- **Infrastruktur:**
  - Internet tidak selalu stabil → sistem harus bisa handle offline mode/minimal bandwidth
  - Device terbatas → prioritas web responsive, bukan native app
  - Guru mayoritas pakai smartphone → UI mobile-first

### Bisnis
- **Anggaran terbatas:**
  - Model langganan bulanan (SaaS)
  - Biaya implementasi harus reasonable
  - Maintenance & support termasuk dalam langganan
- **Timeline:**
  - MVP harus live dalam 5-6 bulan
  - Pelatihan singkat untuk TU & guru (1-2 sesi)
  - Dokumentasi/manual user sederhana

### User Experience
- **User tech literacy rendah:**
  - Interface harus sangat sederhana & intuitif
  - Onboarding wizard
  - Video tutorial singkat
  - Support responsif (WhatsApp/phone)
- **Bahasa & Lokalisasi:**
  - Bahasa Indonesia (UI, validasi, error)
  - Timezone: Asia/Jakarta (WIB)
  - Currency: Rupiah (Rp)

---

## 📊 Kriteria Sukses

### Metrics MVP (6 bulan setelah launch)
- ✅ 100% guru menggunakan absensi digital
- ✅ 80% orang tua sudah login & lihat info pembayaran minimal 1x
- ✅ Rekap laporan bulanan kepala sekolah tersedia on-time
- ✅ Waktu proses PSB berkurang 50%
- ✅ Zero data loss (backup otomatis berjalan)
- ✅ Support ticket resolution < 24 jam

### Long-term Success (1-2 tahun)
- Ekspansi ke sekolah lain (franchise model)
- Integrasi penuh dengan payment gateway (SPP cashless 80%)
- Mobile app rating ≥ 4.5/5
- Retention rate langganan > 90%

---

## 📞 Tim & Kontak

### Pihak Sekolah (Client)
- **Kepala Sekolah / Pemilik:** [Nama]
- **Koordinator Proyek:** [Nama TU/Wakasek]
- **Kontak:** [Email/Phone]

### Pihak Developer
- **Developer / PM:** Zulfikar Hidayatullah
- **Kontak:** +62 857-1583-8733

---

## 📅 Timeline Rencana

| Fase | Durasi | Deliverable |
|------|--------|-------------|
| **Requirements & Design** | Minggu 1-4 | User stories, wireframe, database schema, tech stack finalized |
| **Development Sprint 1** | Minggu 5-10 | Modul Absensi + Dashboard dasar |
| **Development Sprint 2** | Minggu 11-16 | Modul Pembayaran SPP + Rekap nilai |
| **Development Sprint 3** | Minggu 17-20 | PSB Online + Rapor digital + Website |
| **Testing & Refinement** | Minggu 21-23 | UAT, bug fixing, performance optimization |
| **Training & Deployment** | Minggu 24 | Pelatihan user, data migration, go-live |

---

## 🛠️ Tech Stack (Direncanakan)

### Frontend
- Framework: **React.js / Next.js**
- Styling: **Tailwind CSS**
- State Management: **Zustand / React Context**
- Package Manager: **Yarn**

### Backend
- Runtime: **Node.js**
- Framework: **Express / NestJS**
- Database: **PostgreSQL**
- ORM: **Prisma / TypeORM**
- Authentication: **JWT**

### Infrastructure
- Hosting: **VPS / Cloud (DigitalOcean / AWS Lightsail)**
- Storage: **S3-compatible (dokumen upload)**
- Monitoring: **Sentry, Uptime monitoring**
- Backup: **Automated daily backup**

### Integration (Phase 2)
- Payment Gateway: **Midtrans / Xendit / Doku**
- WhatsApp API: **WhatsApp Business API / Third-party service**
- Email: **SMTP / SendGrid**

---

## 📝 Catatan Penting

1. **Prioritas Mobile Experience:**
   - "Apa yang terbaik untuk user experience, terutama di mobile?"
   - Responsive design mandatory
   - Touch-friendly UI
   - Fast loading (< 3 detik)

2. **Service Pattern:**
   - Pragmatis, tidak over-engineered
   - Kode maintainable dan scalable
   - Dokumentasi lengkap

3. **Case Sensitivity:**
   - Perhatikan case sensitivity di semua input/query
   - Validasi konsisten

4. **Lint & Quality:**
   - `yarn run lint` sebelum commit
   - Fix linter errors yang berdampak fungsional

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Next Review:** Setelah approval stakeholder

