# Modul 9: School Website (Website Sekolah)

## 📋 Overview

Website public-facing untuk menampilkan informasi sekolah, pengumuman, dan portal PSB.

**Module Code:** `WEB`  
**Priority:** P2 (Medium)  
**Dependencies:** PSB Module, Authentication

---

## 🎯 Tujuan

1. Memberikan informasi sekolah ke publik
2. Meningkatkan brand awareness & kredibilitas
3. Portal pendaftaran PSB online
4. Komunikasi pengumuman ke orang tua/publik
5. SEO-friendly untuk menjangkau calon siswa

---

## 📖 Pages & Features

### 1. Homepage
**Content:**
- Hero Section: Banner dengan foto sekolah, tagline, CTA "Daftar Sekaligus"
- About Section: Visi, Misi, Nilai-nilai Sekolah (excerpt)
- Quick Stats: Jumlah Siswa, Jumlah Guru, Tahun Berdiri, Akreditasi
- Latest News/Pengumuman (3 terbaru)
- Gallery: foto kegiatan sekolah (grid/carousel)
- Contact Info & Maps (Google Maps embed)
- Footer: Copyright, Social Media Links, Contact

---

### 2. Profil Sekolah
**Sub-menu:**
- Sejarah Sekolah
- Visi & Misi
- Struktur Organisasi (org chart)
- Fasilitas (list + foto: ruang kelas, lab, perpustakaan, lapangan, dll)
- Prestasi (list prestasi siswa/sekolah dengan tahun)

---

### 3. Pengumuman & Berita
**Features:**
- List pengumuman/berita (pagination)
- Per item: title, date, excerpt, thumbnail
- Detail page: title, date, content (WYSIWYG), images
- Category/tag filter (optional)
- Search pengumuman
- Share button (WhatsApp, Facebook)

**Admin:**
- TU/Admin dapat create, update, delete pengumuman via CMS

---

### 4. Portal PSB
**Features:**
- Informasi PSB: jadwal, syarat, biaya, FAQ
- Button CTA: "Daftar Sekarang" → redirect ke PSB registration form
- Link "Cek Status Pendaftaran" → tracking page

---

### 5. Kontak
**Content:**
- Alamat lengkap sekolah
- No. Telp, Email, WhatsApp (klik langsung call/email/WA)
- Google Maps embed (lokasi sekolah)
- Contact form (nama, email, pesan) → kirim ke email sekolah atau save di database

---

### 6. Login Portal
**Features:**
- Link "Login" di navbar → redirect ke app login page
- Differentiate: Login untuk Guru/TU/Admin vs Login untuk Orang Tua (bisa same login page dengan auto-detect role)

---

## 🎨 UI/UX Requirements

**Design:**
- Modern, clean, professional
- Responsive (mobile-first)
- Fast loading (optimize images, lazy load)
- Accessibility (AA standard)
- Color scheme: sesuai branding sekolah (logo colors)

**Navigation:**
- Top navbar: sticky on scroll
- Menu: Home, Profil, Pengumuman, PSB, Kontak, Login
- Mobile: hamburger menu

**SEO:**
- Meta tags (title, description, keywords)
- Open Graph tags (untuk share di social media)
- Sitemap.xml
- Robots.txt
- Structured data (schema.org)

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ Homepage (hero, about, stats, news, contact)
- ✅ Profil Sekolah (visi/misi, fasilitas)
- ✅ Pengumuman & Berita (list, detail, CRUD via admin)
- ✅ Portal PSB (info + link ke form pendaftaran)
- ✅ Kontak (alamat, maps, contact form)
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ SEO basic (meta tags, sitemap)

### Should Have (MVP):
- ✅ Gallery (foto kegiatan sekolah)
- ✅ Search pengumuman
- ✅ Share button untuk pengumuman

### Could Have:
- ⬜ Blog/artikel tentang pendidikan
- ⬜ Teacher profiles (foto & biodata guru)
- ⬜ Live chat/chatbot
- ⬜ Multi-language (Bahasa & English)

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft

