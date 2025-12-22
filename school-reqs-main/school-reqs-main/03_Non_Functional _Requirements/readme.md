# Non-Functional Requirements (NFR)
## Sistem Manajemen Sekolah SD

Dokumen ini mendefinisikan kebutuhan non-fungsional sistem berdasarkan kondisi teknis, infrastruktur, dan constraint operasional SD.

---

## 1. Performance (Kinerja)

### 1.1 Response Time
- **Waktu loading halaman utama:** ≤ 3 detik pada koneksi internet normal
- **Waktu loading dashboard:** ≤ 5 detik dengan data penuh
- **Operasi CRUD sederhana** (input absensi, pembayaran): ≤ 2 detik
- **Generate laporan PDF** (rapor): ≤ 10 detik
- **Export data ke Excel:** ≤ 8 detik untuk data 1 tahun ajaran

### 1.2 Scalability (Skalabilitas)
- **Kapasitas saat ini:**
  - Mendukung minimal **200 siswa aktif**
  - **20 guru dan staf** concurrent users
  - **100 orang tua** dapat mengakses portal secara bersamaan
- **Proyeksi pertumbuhan:**
  - Sistem harus dapat scale up hingga **300 siswa** dalam 3 tahun tanpa perubahan arsitektur mayor
  - Database dapat menampung **minimal 5 tahun data historis** tanpa penurunan performa signifikan

### 1.3 Concurrency (Konkurensi)
- Sistem harus mampu menangani:
  - **50 concurrent users** tanpa degradasi performa
  - **Multiple input absensi** dari berbagai guru di waktu bersamaan (pagi hari)
  - **Bulk payment recording** saat periode pembayaran SPP puncak

### 1.4 Optimasi untuk Koneksi Terbatas
- **Lazy loading** untuk data besar (tabel siswa, riwayat transaksi)
- **Kompresi gambar** otomatis untuk dokumen upload (max 500KB per file)
- **Caching** data statis (master kelas, mata pelajaran, pengumuman)
- **Offline capability** untuk input absensi (sync saat koneksi kembali) — *opsional fase 1, wajib fase 2*

---

## 2. Reliability & Availability (Keandalan & Ketersediaan)

### 2.1 Uptime
- **Target uptime:** 99% (downtime maksimal ~7 jam/bulan)
- **Maintenance window:** di luar jam operasional sekolah (Sabtu malam / Minggu pagi)
- **Notifikasi maintenance** harus diinformasikan **minimal 48 jam** sebelumnya

### 2.2 Data Integrity & Backup
- **Backup otomatis harian** (incremental) pukul 23:00 WIB
- **Backup penuh mingguan** setiap Minggu pukul 02:00 WIB
- **Retention policy:** backup disimpan minimal **6 bulan**
- **Recovery Time Objective (RTO):** maksimal **4 jam** untuk restore data
- **Recovery Point Objective (RPO):** maksimal kehilangan data **24 jam terakhir**

### 2.3 Error Handling
- Sistem harus menampilkan **pesan error dalam Bahasa Indonesia** yang mudah dipahami
- Tidak boleh menampilkan **technical error stack** ke user biasa
- **Logging error** otomatis untuk troubleshooting developer
- **Fallback mechanism** untuk fitur critical (misal: jika gateway pembayaran down, user tetap bisa catat pembayaran manual)

### 2.4 Fault Tolerance
- Jika modul tertentu error, **tidak boleh crash seluruh sistem**
- Sistem pembayaran harus **isolated** — error di modul nilai tidak boleh ganggu transaksi
- **Session timeout:** 2 jam untuk user aktif, otomatis logout jika idle > 30 menit (admin: 15 menit idle)

---

## 3. Usability (Kemudahan Penggunaan)

### 3.1 User Interface
- **Desain sederhana, clean, dan intuitif** — staf TU dan guru tidak tech-savvy
- **Mobile-first approach:** UI harus optimal untuk smartphone (mayoritas guru dan orang tua pakai HP)
- **Responsive design:** dapat diakses dari desktop, tablet, dan smartphone dengan layout yang menyesuaikan
- **Konsistensi UI:** pattern yang sama untuk operasi serupa (simpan, edit, hapus) di seluruh modul

### 3.2 Navigation & Accessibility
- **Maksimal 3 klik** untuk mencapai fitur utama dari dashboard
- **Breadcrumb** untuk navigasi kompleks (laporan, rekap nilai)
- **Search & filter** untuk data besar (daftar siswa, transaksi)
- **Keyboard shortcuts** untuk power user (admin/TU) — opsional
- **Contrast ratio** minimal 4.5:1 untuk readability (WCAG AA standard)

### 3.3 Localization
- **Bahasa:** 100% Bahasa Indonesia untuk UI, validasi, error messages
- **Timezone:** Asia/Jakarta (WIB) untuk semua timestamp
- **Currency:** Rupiah (Rp) dengan format Indonesia (titik sebagai separator ribuan, koma untuk desimal)
- **Date format:** DD/MM/YYYY atau "Senin, 13 Desember 2025"
- **Number format:** 1.000.000,00 (Indonesia style)

### 3.4 Help & Documentation
- **Tooltip / inline help** untuk field yang kompleks (misal: bobot nilai, jenis pembayaran)
- **User manual** dalam Bahasa Indonesia (PDF + video tutorial singkat)
- **FAQ section** untuk pertanyaan umum orang tua dan guru
- **Onboarding wizard** untuk user pertama kali (guru, orang tua baru)

---

## 4. Security (Keamanan)

### 4.1 Authentication & Authorization
- **Role-Based Access Control (RBAC):** setiap role (Kepala Sekolah, TU, Guru, Orang Tua, Siswa) hanya akses fitur sesuai hak aksesnya
- **Password policy:**
  - Minimal **8 karakter** (kombinasi huruf, angka, simbol)
  - **Hashed** dengan algoritma modern (bcrypt / Argon2)
  - **Password reset** via email/WhatsApp dengan token expiry 1 jam
- **Session management:**
  - Token-based authentication (JWT atau session cookie secure)
  - Auto-logout setelah **idle 30 menit** (user biasa), **15 menit** (admin)
- **Multi-factor authentication (MFA):** opsional untuk admin — *future phase*

### 4.2 Data Privacy & Protection
- **Enkripsi data sensitif:**
  - **Data in transit:** HTTPS/TLS 1.2+ wajib
  - **Data at rest:** enkripsi untuk data pembayaran dan informasi pribadi (KTP, akte, dll)
- **Compliance:**
  - Mengikuti prinsip **UU PDP (Perlindungan Data Pribadi) Indonesia**
  - Data siswa dan orang tua **tidak boleh dibagikan ke pihak ketiga** tanpa consent
- **Data masking:** nomor rekening/NIK hanya tampil sebagian (misal: 1234****5678)
- **Audit log:** semua akses dan perubahan data sensitive dicatat (who, what, when)

### 4.3 Payment Security
- **PCI DSS compliance** jika integrasi payment gateway (fase 2)
- **Virtual Account / QRIS** diutamakan (tidak simpan data kartu)
- **Transaction validation:** konfirmasi pembayaran melalui email/WhatsApp otomatis
- **Reconciliation:** setiap transaksi harus dapat diaudit dan dicocokkan dengan bukti bank

### 4.4 File Upload Security
- **Validasi tipe file:** hanya terima PDF, JPG, PNG untuk dokumen siswa
- **Ukuran maksimal:** 2MB per file (untuk menjaga bandwidth)
- **Antivirus scanning** untuk file upload (jika budget memungkinkan)
- **Sanitasi nama file:** hindari karakter berbahaya (script injection)

---

## 5. Compatibility & Portability (Kompatibilitas)

### 5.1 Browser Support
- **Desktop:**
  - Chrome/Edge (versi 2 tahun terakhir)
  - Firefox (versi 2 tahun terakhir)
  - Safari (versi terbaru untuk macOS)
- **Mobile:**
  - Chrome Mobile (Android)
  - Safari Mobile (iOS)
- **Legacy support:** IE11 dan browser lama **tidak didukung** (akan muncul notifikasi update browser)

### 5.2 Device Compatibility
- **Desktop/Laptop:** Windows 7+, macOS 10.13+, Linux (Ubuntu 18.04+)
- **Smartphone:** Android 8.0+, iOS 12+
- **Tablet:** Kompatibel dengan layout responsive
- **Screen resolution:** optimal untuk 1366x768 (desktop), 360x640 (mobile) ke atas

### 5.3 Progressive Web App (PWA)
- **Installable:** user dapat "Add to Home Screen" di smartphone tanpa perlu native app
- **Offline-capable** (terbatas untuk absensi input) — *future enhancement*
- **Push notification support** untuk reminder SPP — *future phase*

---

## 6. Maintainability & Supportability (Pemeliharaan)

### 6.1 Code Quality
- **Clean code principles:** readable, modular, well-documented
- **Linting:** ESLint/Prettier untuk konsistensi code style
- **Version control:** Git dengan commit message yang jelas
- **Code review:** minimal 1 reviewer untuk setiap pull request

### 6.2 Logging & Monitoring
- **Application logs:** level INFO, WARN, ERROR untuk troubleshooting
- **System monitoring:** CPU, memory, disk usage di server (jika self-hosted)
- **Error tracking:** integrasi Sentry atau sejenisnya untuk capture error real-time
- **Audit trail:** log untuk semua aktivitas critical (login, perubahan data pembayaran, perubahan nilai)

### 6.3 Deployment & Updates
- **Zero-downtime deployment** untuk update sistem (rolling update)
- **Rollback mechanism** jika ada bug critical di versi baru
- **Versioning:** semantic versioning (v1.0.0, v1.1.0, dll)
- **Changelog:** dokumentasi perubahan setiap release

### 6.4 Documentation
- **Technical documentation:**
  - API documentation (jika ada eksternal API)
  - Database schema & ERD
  - Setup & deployment guide
- **User documentation:**
  - User manual per role (TU, Guru, Orang Tua)
  - Video tutorial singkat (5-10 menit)
  - FAQ & troubleshooting guide

---

## 7. Compliance & Regulatory (Kepatuhan Regulasi)

### 7.1 Standar Pendidikan Indonesia
- **Kurikulum 2013 (K13):** sistem penilaian harus sesuai komponen nilai K13
- **Format rapor:** sesuai standar Dinas Pendidikan setempat
- **Integrasi Dapodik:** persiapan struktur data untuk integrasi ke Dapodik Kemendikbud — *future phase*

### 7.2 Data Protection
- Sesuai **UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi**
- **Consent management:** orang tua harus consent untuk penggunaan data anak
- **Right to erasure:** data dapat dihapus jika diminta (dengan batasan hukum)

### 7.3 Financial Record Keeping
- **Audit trail pembayaran:** setiap transaksi harus dapat dilacak dan diverifikasi
- **Export untuk akuntan:** laporan keuangan harus dapat di-export ke format standar akuntansi

---

## 8. Integration & Interoperability (Integrasi)

### 8.1 Third-Party Integration
- **WhatsApp Business API** untuk notifikasi (prioritas utama)
- **Email service** (SMTP atau transactional email provider seperti SendGrid)
- **Payment Gateway:**
  - Midtrans / Xendit / Doku untuk Virtual Account & QRIS
  - Integrasi wajib di fase 2, opsional di fase 1
- **SMS Gateway** (opsional, backup untuk WhatsApp)

### 8.2 Data Import/Export
- **Import data siswa:** dari Excel (template provided)
- **Export laporan:** ke Excel (.xlsx) dan PDF
- **Export database backup:** format SQL dump atau CSV

### 8.3 API (Future Consideration)
- **RESTful API** untuk integrasi dengan sistem eksternal (Dapodik, sistem lain)
- **Webhook** untuk notifikasi pembayaran dari payment gateway
- **Rate limiting:** 100 requests/minute per user untuk mencegah abuse

---

## 9. Training & Onboarding (Pelatihan)

### 9.1 Training Requirements
- **Durasi:** 1-2 sesi pelatihan (masing-masing 2-3 jam)
- **Target peserta:**
  - **Sesi 1:** Kepala Sekolah + TU (fokus dashboard, pembayaran, laporan)
  - **Sesi 2:** Guru (fokus absensi, input nilai, komunikasi orang tua)
- **Materi:**
  - Pengenalan sistem & navigasi
  - Hands-on practice untuk setiap role
  - Troubleshooting umum
  - Q&A session

### 9.2 Support & Handover
- **User manual** yang mudah dipahami (PDF + video)
- **Help desk / support contact:** WhatsApp atau email untuk pertanyaan
- **Response time support:**
  - Critical issue (sistem down): **< 2 jam**
  - Bug / error: **< 24 jam**
  - Feature request / enhancement: **discuss & plan**

---

## 10. Cost & Budget Considerations (Pertimbangan Biaya)

### 10.1 Deployment Model
- **Preferred:** SaaS model (cloud-hosted, subscription-based)
- **Alternative:** Self-hosted di server lokal (jika sekolah punya infrastruktur + IT support)
- **Payment structure:**
  - Setup fee (one-time) untuk implementasi & training
  - Monthly/yearly subscription untuk maintenance, hosting, support

### 10.2 Operational Cost Efficiency
- **Infrastruktur hemat:** cloud serverless atau PaaS (Heroku, Railway, Vercel) untuk menekan biaya hosting
- **Third-party services:** pilih provider dengan harga terjangkau untuk sekolah
  - WhatsApp Business API: sesuaikan dengan budget (fallback: integrasi manual via broadcast)
  - Payment gateway: pilih yang biaya transaksi rendah dan tidak ada biaya bulanan tetap (Xendit/Doku)
- **Scaling cost:** sistem harus dapat mulai dari skala kecil (180 siswa) tanpa biaya berlebih

---

## 11. Testing & Quality Assurance (Pengujian)

### 11.1 Testing Types
- **Unit testing:** coverage minimal 70% untuk business logic critical
- **Integration testing:** terutama untuk modul pembayaran, absensi, nilai
- **User Acceptance Testing (UAT):** melibatkan pihak sekolah (TU, Guru, Kepala Sekolah) sebelum go-live
- **Performance testing:** load testing dengan simulasi 50 concurrent users

### 11.2 Bug Priority & SLA
- **Critical (P0):** sistem down, data loss — fix **< 4 jam**
- **High (P1):** fitur critical tidak berfungsi (tidak bisa input nilai, absensi) — fix **< 24 jam**
- **Medium (P2):** bug yang mengganggu tapi ada workaround — fix **< 1 minggu**
- **Low (P3):** UI/UX improvement, minor bug — prioritas backlog

---

## 12. Phasing & Prioritization (Pentahapan)

### Phase 1 (MVP - Must Have)
- Performance: fast enough untuk 200 users
- Usability: simple, responsive, mobile-friendly
- Security: basic authentication, HTTPS, role-based access
- Compatibility: modern browser + mobile support
- Localization: full Indonesian UI
- Training: 1-2 sesi pelatihan
- Integration: WhatsApp notifikasi (manual/semi-auto), Email

### Phase 2 (Future Enhancements)
- Performance: offline mode untuk absensi
- Security: MFA untuk admin
- Integration: full payment gateway automation, WhatsApp API
- Advanced reporting: BI dashboard, predictive analytics
- PWA: full offline capability + push notification
- Biometric attendance: fingerprint/face recognition

---

## Acceptance Criteria untuk NFR

Sistem akan dianggap **memenuhi NFR** jika:

1. ✅ **Performance:** Halaman utama load < 3 detik pada koneksi 4G/WiFi normal
2. ✅ **Reliability:** Uptime 99% selama 3 bulan pertama operasional
3. ✅ **Usability:** 80% user (TU, Guru) dapat melakukan task utama tanpa bantuan setelah 1x training
4. ✅ **Security:** Tidak ada data breach atau unauthorized access selama periode trial
5. ✅ **Compatibility:** Berfungsi sempurna di Chrome Mobile (Android) dan Safari (iOS) tanpa layout break
6. ✅ **Localization:** 100% UI dalam Bahasa Indonesia, format Rupiah dan WIB konsisten
7. ✅ **Maintainability:** Developer baru dapat onboard & fix bug < 1 minggu
8. ✅ **Support:** Response time support sesuai SLA yang ditetapkan

---

**Catatan:**  
Dokumen ini akan di-review dan disesuaikan setelah diskusi dengan tim teknis dan pihak sekolah. Prioritas NFR dapat berubah berdasarkan constraint anggaran dan timeline implementasi.

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Prepared by:** Development Team  
**Stakeholder:** SD (Kepala Sekolah, TU, Guru)
