# User Stories: School Website

Module ini mencakup website publik sekolah sebagai front-facing platform.

---

## US-WEB-001: Homepage School Website

**As a** Visitor (orang tua calon siswa/masyarakat umum)  
**I want** mengakses homepage website sekolah  
**So that** saya dapat melihat informasi umum tentang sekolah

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** visitor akses URL sekolah (misal: www.sdmaju.sch.id)  
   **When** homepage load  
   **Then** sistem tampilkan: Header (logo, menu navigasi), Hero Section (foto sekolah, tagline), Tentang Sekolah (ringkas), Pengumuman Terbaru, Galeri Foto, Footer (kontak, alamat, sosmed)

✅ **Given** visitor scroll homepage  
   **When** visitor scroll ke section "Tentang Kami"  
   **Then** sistem tampilkan: sejarah singkat, visi-misi, akreditasi, jumlah siswa & guru

✅ **Given** homepage load  
   **When** performa di-test  
   **Then** homepage load dalam < 3 detik pada koneksi normal

**Notes:**
- Responsive design (mobile, tablet, desktop)
- Modern UI/UX (clean, professional)
- SEO-friendly (meta tags, sitemap)
- Akses publik (tidak perlu login)

---

## US-WEB-002: Halaman Profil Sekolah

**As a** Visitor  
**I want** melihat profil lengkap sekolah  
**So that** saya dapat mengenal sekolah lebih detail

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** visitor di homepage  
   **When** visitor klik menu "Profil"  
   **Then** sistem tampilkan halaman dengan section: Sejarah, Visi-Misi, Struktur Organisasi, Fasilitas, Akreditasi, Prestasi

✅ **Given** visitor di section "Fasilitas"  
   **When** visitor lihat  
   **Then** sistem tampilkan list fasilitas dengan foto: Ruang Kelas, Perpustakaan, Lab Komputer, Lapangan, Masjid, dll

**Notes:**
- Konten editable oleh admin (CMS)
- Upload foto fasilitas
- Struktur organisasi: chart/diagram

---

## US-WEB-003: Halaman Pengumuman

**As a** Visitor/Orang Tua  
**I want** melihat pengumuman dari sekolah  
**So that** saya dapat update informasi terbaru

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** visitor di homepage  
   **When** visitor klik menu "Pengumuman"  
   **Then** sistem tampilkan list pengumuman (sorted by tanggal, terbaru di atas): judul, tanggal, excerpt

✅ **Given** visitor klik salah satu pengumuman  
   **When** visitor buka detail  
   **Then** sistem tampilkan: judul, tanggal publish, konten lengkap, attachment (jika ada)

✅ **Given** ada pengumuman penting (misal: libur sekolah)  
   **When** pengumuman dipublish  
   **Then** pengumuman tampil di homepage dengan badge "Penting"

**Notes:**
- Kategori pengumuman: Umum, Akademik, Keuangan, PSB
- Support rich text editor untuk konten
- Upload attachment (PDF, gambar)
- Pagination (10 pengumuman per halaman)

---

## US-WEB-004: Halaman Galeri Foto/Video

**As a** Visitor  
**I want** melihat galeri foto & video kegiatan sekolah  
**So that** saya dapat melihat aktivitas dan suasana sekolah

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** visitor di homepage  
   **When** visitor klik menu "Galeri"  
   **Then** sistem tampilkan grid foto dengan thumbnail dan kategori (Kegiatan Belajar, Upacara, Ekskul, Acara, dll)

✅ **Given** visitor klik salah satu foto  
   **When** foto dibuka  
   **Then** sistem tampilkan lightbox dengan foto full-size, caption, dan navigation (prev/next)

✅ **Given** visitor filter galeri berdasarkan kategori "Ekskul"  
   **When** visitor apply filter  
   **Then** sistem hanya tampilkan foto kegiatan ekstrakurikuler

**Notes:**
- Lazy loading untuk performa
- Support video (embed YouTube/Vimeo)
- Album/kategori galeri
- Caption & tanggal untuk setiap foto

---

## US-WEB-005: Halaman Kontak & Lokasi

**As a** Visitor  
**I want** melihat informasi kontak dan lokasi sekolah  
**So that** saya dapat menghubungi atau berkunjung ke sekolah

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** visitor di homepage  
   **When** visitor klik menu "Kontak"  
   **Then** sistem tampilkan: alamat lengkap, nomor telepon, email, jam operasional, Google Maps embed

✅ **Given** visitor di section Google Maps  
   **When** visitor lihat map  
   **Then** map interactive (dapat zoom, drag, klik "Directions")

✅ **Given** visitor ingin kirim pesan ke sekolah  
   **When** visitor isi form kontak (nama, email, subjek, pesan)  
   **Then** sistem kirim email ke admin sekolah dan tampilkan notifikasi "Pesan Anda berhasil dikirim"

**Notes:**
- Google Maps API untuk embed
- Contact form dengan validasi
- Spam protection (reCAPTCHA)

---

## US-WEB-006: Halaman PSB (Pendaftaran Siswa Baru)

**As a** Orang Tua calon siswa  
**I want** akses halaman PSB untuk mendaftar  
**So that** saya dapat mendaftarkan anak saya

**Priority:** Must Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** visitor di homepage (saat periode PSB dibuka)  
   **When** visitor klik tombol "Daftar Siswa Baru" atau menu "PSB"  
   **Then** sistem tampilkan halaman PSB: info PSB, persyaratan, timeline, biaya, tombol "Mulai Pendaftaran"

✅ **Given** visitor klik "Mulai Pendaftaran"  
   **When** tombol diklik  
   **Then** sistem redirect ke formulir pendaftaran (US-PSB-001)

✅ **Given** periode PSB sudah ditutup  
   **When** visitor akses halaman PSB  
   **Then** sistem tampilkan "Pendaftaran sudah ditutup. Silakan cek kembali untuk periode berikutnya"

**Notes:**
- Link ke formulir PSB (integrasi dengan module PSB)
- Countdown timer jika PSB akan dibuka/ditutup
- FAQ PSB

---

## US-WEB-007: CMS untuk Kelola Konten Website (Admin)

**As a** Admin/TU  
**I want** mengelola konten website (profil, pengumuman, galeri) tanpa coding  
**So that** website selalu update dengan informasi terbaru

**Priority:** Must Have  
**Estimation:** L (5 points)

**Acceptance Criteria:**

✅ **Given** admin login ke admin panel  
   **When** admin klik menu "Kelola Website"  
   **Then** sistem tampilkan CMS dengan section: Profil, Pengumuman, Galeri, Halaman (About, Contact, dll)

✅ **Given** admin ingin update "Visi-Misi"  
   **When** admin edit konten dengan rich text editor dan klik "Simpan"  
   **Then** konten di website publik otomatis update

✅ **Given** admin ingin publish pengumuman baru  
   **When** admin klik "Tambah Pengumuman", isi judul & konten, upload gambar, dan klik "Publish"  
   **Then** pengumuman langsung tampil di website publik

✅ **Given** admin upload foto ke galeri  
   **When** admin pilih kategori dan upload file  
   **Then** foto tampil di galeri publik setelah di-publish

**Notes:**
- Rich text editor: TinyMCE atau CKEditor
- Upload image dengan auto-resize & compress
- Preview before publish
- Schedule publish (opsional, fase 2)

---

## US-WEB-008: Halaman Kurikulum & Ekstrakurikuler

**As a** Visitor/Orang Tua  
**I want** melihat informasi kurikulum dan ekstrakurikuler  
**So that** saya tahu program pendidikan yang ditawarkan

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** visitor di homepage  
   **When** visitor klik menu "Akademik"  
   **Then** sistem tampilkan sub-menu: Kurikulum, Ekstrakurikuler, Kalender Akademik

✅ **Given** visitor klik "Kurikulum"  
   **When** halaman load  
   **Then** sistem tampilkan: kurikulum yang digunakan (K13), mata pelajaran, metode pembelajaran

✅ **Given** visitor klik "Ekstrakurikuler"  
   **When** halaman load  
   **Then** sistem tampilkan list ekskul dengan foto dan deskripsi: Pramuka, Bola, Menari, Musik, dll

**Notes:**
- Konten editable via CMS
- Foto kegiatan ekskul
- Jadwal ekskul (opsional)

---

## US-WEB-009: Halaman Berita/Artikel

**As a** Visitor  
**I want** membaca berita & artikel tentang kegiatan sekolah  
**So that** saya dapat update perkembangan sekolah

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin publish berita/artikel  
   **When** admin input: judul, konten, foto, kategori, dan publish  
   **Then** artikel tampil di halaman "Berita"

✅ **Given** visitor akses halaman "Berita"  
   **When** halaman load  
   **Then** sistem tampilkan list artikel (card dengan thumbnail, judul, excerpt, tanggal)

✅ **Given** visitor klik salah satu artikel  
   **When** artikel dibuka  
   **Then** sistem tampilkan: judul, tanggal, penulis, foto, konten lengkap, related articles

**Notes:**
- Berbeda dengan Pengumuman (Berita lebih editorial, Pengumuman lebih formal)
- SEO-friendly URL (slug)
- Social share buttons (opsional)

---

## US-WEB-010: SEO & Performance Optimization

**As a** Admin/Developer  
**I want** website sekolah SEO-friendly dan fast loading  
**So that** website mudah ditemukan di Google dan user experience baik

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** website di-test dengan Google PageSpeed Insights  
   **When** test selesai  
   **Then** score minimal 80/100 (mobile & desktop)

✅ **Given** website di-test dengan SEO tools (Lighthouse, SEO analyzer)  
   **When** test selesai  
   **Then** website punya: meta title, meta description, heading structure (H1-H6), alt text untuk gambar, sitemap.xml, robots.txt

✅ **Given** visitor search "SD [Nama Sekolah]" di Google  
   **When** search result load  
   **Then** website sekolah muncul di halaman pertama

**Notes:**
- Image optimization (WebP, lazy loading)
- Minify CSS/JS
- Caching strategy
- Mobile-friendly (responsive)
- HTTPS (SSL certificate)

---

## US-WEB-011: Portal Login (Link ke Sistem Internal)

**As a** Orang Tua/Guru/TU  
**I want** akses portal login dari website  
**So that** saya dapat login ke sistem internal

**Priority:** Must Have  
**Estimation:** XS (1 point)

**Acceptance Criteria:**

✅ **Given** user (orang tua/guru/TU) di website  
   **When** user klik tombol "Login" di header  
   **Then** sistem redirect ke halaman login sistem internal

✅ **Given** user sudah login  
   **When** user akses website lagi  
   **Then** tombol "Login" berubah jadi "Dashboard" (link ke dashboard sesuai role)

**Notes:**
- Button "Login" di header/navigation
- Single Sign-On (SSO) antara website & sistem internal (opsional)

---

## US-WEB-012: Footer & Social Media Integration

**As a** Visitor  
**I want** melihat footer dengan kontak & link social media  
**So that** saya dapat follow social media sekolah

**Priority:** Must Have  
**Estimation:** XS (1 point)

**Acceptance Criteria:**

✅ **Given** visitor di homepage atau halaman manapun  
   **When** visitor scroll ke bawah  
   **Then** sistem tampilkan footer dengan: logo sekolah, alamat, nomor telepon, email, link social media (Facebook, Instagram, YouTube), copyright

✅ **Given** visitor klik icon Facebook  
   **When** visitor klik  
   **Then** browser buka tab baru ke halaman Facebook sekolah

**Notes:**
- Social media links configurable via admin
- Copyright: "© 2025 SD [Nama Sekolah]. All Rights Reserved."

---

## Summary: School Website

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-WEB-001 | Homepage | Must Have | M | 1 |
| US-WEB-002 | Profil Sekolah | Must Have | S | 1 |
| US-WEB-003 | Pengumuman | Must Have | M | 1 |
| US-WEB-004 | Galeri Foto/Video | Should Have | M | 1 |
| US-WEB-005 | Kontak & Lokasi | Must Have | S | 1 |
| US-WEB-006 | Halaman PSB | Must Have | S | 1 |
| US-WEB-007 | CMS Admin | Must Have | L | 1 |
| US-WEB-008 | Kurikulum & Ekskul | Should Have | S | 1 |
| US-WEB-009 | Berita/Artikel | Could Have | M | 2 |
| US-WEB-010 | SEO & Performance | Should Have | M | 1 |
| US-WEB-011 | Portal Login Link | Must Have | XS | 1 |
| US-WEB-012 | Footer & Social Media | Must Have | XS | 1 |

**Total Estimation Phase 1:** 22 points (~3-4 weeks untuk 1 developer)

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
