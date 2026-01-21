# User Stories: Notification System

Module ini mencakup sistem notifikasi via WhatsApp, Email, dan In-App untuk berbagai event.

---

## US-NOTIF-001: Notifikasi Absensi (WhatsApp ke Orang Tua)

**As a** Orang Tua  
**I want** menerima notifikasi WhatsApp jika anak saya alpha  
**So that** saya segera tahu jika anak tidak ke sekolah

**Priority:** Should Have (High Impact)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** guru input absensi dan siswa A statusnya "Alpha"  
   **When** guru simpan absensi  
   **Then** sistem otomatis kirim WhatsApp ke orang tua siswa A: "Halo Bapak/Ibu, anak Anda [Nama Siswa] tidak hadir di sekolah hari ini tanpa keterangan. Mohon konfirmasi. Terima kasih."

✅ **Given** siswa B statusnya "Sakit" dengan izin yang sudah disetujui  
   **When** guru simpan absensi  
   **Then** sistem TIDAK kirim notifikasi (karena sudah ada izin)

✅ **Given** notifikasi gagal terkirim (nomor tidak aktif/error)  
   **When** sistem detect error  
   **Then** sistem log error dan fallback ke email (jika ada)

**Notes:**
- Trigger: absensi alpha atau terlambat
- Template pesan dalam Bahasa Indonesia
- Rate limiting: max 10 pesan/menit per nomor (avoid spam)
- Integrasi: WhatsApp Business API (Twilio/Wablas/Fonnte) atau manual via broadcast (fase 1)

---

## US-NOTIF-002: Notifikasi Reminder Pembayaran (WhatsApp/Email)

**As a** Orang Tua  
**I want** menerima reminder pembayaran SPP  
**So that** saya tidak lupa bayar SPP

**Priority:** Should Have (High Impact)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** tanggal 5 setiap bulan (H-5 jatuh tempo)  
   **When** cron job reminder berjalan  
   **Then** sistem kirim WhatsApp ke orang tua yang belum bayar: "Reminder: SPP bulan [Bulan] untuk [Nama Anak] belum dibayar. Jatuh tempo: [Tanggal]. Jumlah: Rp [Nominal]. Terima kasih."

✅ **Given** orang tua sudah bayar SPP sebelum H-5  
   **When** reminder berjalan  
   **Then** orang tua TIDAK menerima reminder

✅ **Given** siswa punya tunggakan 2 bulan  
   **When** reminder berjalan  
   **Then** pesan include total tunggakan: "Anda memiliki tunggakan 2 bulan (Desember, Januari). Total: Rp 500.000"

**Notes:**
- Schedule reminder: H-5, H-3, H (jatuh tempo), H+3, H+7
- Orang tua dapat opt-out reminder (setting preferensi)
- Fallback: email jika WhatsApp gagal

---

## US-NOTIF-003: Notifikasi Pengumuman (Broadcast)

**As a** TU/Admin  
**I want** kirim notifikasi pengumuman penting ke semua orang tua  
**So that** informasi cepat sampai

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** admin publish pengumuman dengan flag "Kirim Notifikasi"  
   **When** admin klik "Publish & Kirim"  
   **Then** sistem kirim WhatsApp/email ke semua orang tua: "[Judul Pengumuman] - [Excerpt]. Baca selengkapnya: [Link]"

✅ **Given** admin ingin kirim ke group tertentu (misal: hanya orang tua kelas 6)  
   **When** admin pilih target "Kelas 6" dan kirim  
   **Then** sistem hanya kirim ke orang tua siswa kelas 6

✅ **Given** ada 200 orang tua  
   **When** sistem kirim broadcast  
   **Then** sistem kirim secara batch (50 pesan/batch dengan delay 10 detik antar batch) untuk avoid API rate limit

**Notes:**
- Target: Semua Orang Tua, Per Kelas, Per Siswa (individual)
- Preview sebelum kirim
- Log: tracking delivery status (terkirim/gagal)

---

## US-NOTIF-004: Notifikasi Approval/Rejection (In-App + WhatsApp)

**As a** User (Orang Tua/Guru)  
**I want** menerima notifikasi saat pengajuan saya di-approve/reject  
**So that** saya tahu status pengajuan

**Priority:** Must Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua ajukan izin untuk anak dan wali kelas approve  
   **When** wali kelas klik "Setujui"  
   **Then** orang tua dapat notifikasi in-app + WhatsApp: "Pengajuan izin untuk [Nama Anak] tanggal [Tanggal] telah disetujui"

✅ **Given** TU reject pembayaran transfer (bukti tidak sesuai)  
   **When** TU klik "Tolak" dengan alasan  
   **Then** orang tua dapat notifikasi: "Pembayaran Anda ditolak. Alasan: [Keterangan]. Mohon upload ulang bukti transfer yang benar"

✅ **Given** user buka aplikasi dan ada notifikasi baru  
   **When** user lihat notification icon  
   **Then** tampil badge merah dengan jumlah notifikasi belum dibaca

**Notes:**
- In-app notification: bell icon di header dengan badge counter
- Kombinasi: in-app (priority) + WhatsApp (backup)
- Mark as read saat notifikasi diklik

---

## US-NOTIF-005: Notifikasi Rapor Dirilis (Orang Tua)

**As a** Orang Tua  
**I want** menerima notifikasi saat rapor anak saya sudah dirilis  
**So that** saya segera bisa mengecek rapor

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** kepala sekolah release rapor kelas 3A  
   **When** kepala sekolah klik "Release"  
   **Then** sistem kirim notifikasi ke semua orang tua kelas 3A: "Rapor semester [X] untuk [Nama Anak] sudah tersedia. Silakan login untuk melihat: [Link Portal]"

✅ **Given** orang tua klik link di notifikasi  
   **When** orang tua buka link  
   **Then** browser redirect ke portal orang tua, halaman rapor

**Notes:**
- Kirim setelah rapor status "Released"
- Link deep-link langsung ke halaman rapor
- One-time notification (tidak repeat)

---

## US-NOTIF-006: Email Notification (Fallback)

**As a** User (semua role)  
**I want** menerima notifikasi via email jika WhatsApp gagal  
**So that** saya tetap dapat informasi penting

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** sistem coba kirim WhatsApp dan gagal (nomor tidak aktif)  
   **When** sistem detect error  
   **Then** sistem fallback kirim email ke alamat email user

✅ **Given** email berhasil terkirim  
   **When** user buka email  
   **Then** email berisi: subjek jelas, konten notifikasi, link action (jika ada), footer sekolah

✅ **Given** user tidak punya email (field kosong)  
   **When** WhatsApp gagal dan tidak ada email  
   **Then** sistem log error dan tandai "Notification Failed" untuk manual follow-up

**Notes:**
- Email service: SMTP atau transactional email (SendGrid, Mailgun)
- Template email: HTML dengan branding sekolah
- Track delivery: sent, opened (opsional)

---

## US-NOTIF-007: In-App Notification Center

**As a** User (semua role)  
**I want** melihat semua notifikasi di satu tempat  
**So that** saya tidak miss informasi penting

**Priority:** Should Have  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** user login ke sistem  
   **When** user lihat header  
   **Then** ada icon bell dengan badge counter (jumlah notifikasi belum dibaca)

✅ **Given** user klik icon bell  
   **When** dropdown notification muncul  
   **Then** sistem tampilkan list notifikasi (terbaru di atas) dengan: icon, pesan, timestamp, status baca/belum

✅ **Given** user klik salah satu notifikasi  
   **When** user klik  
   **Then** sistem mark as read, redirect ke halaman terkait (misal: notifikasi "Pembayaran Pending" → redirect ke halaman verifikasi)

✅ **Given** user klik "Lihat Semua"  
   **When** user klik  
   **Then** sistem redirect ke halaman Notification Center (all notifications dengan filter: Semua/Belum Dibaca/Sudah Dibaca)

**Notes:**
- Real-time update (websocket atau polling)
- Auto-mark as read setelah diklik
- Retention: simpan notifikasi 30 hari

---

## US-NOTIF-008: Preferensi Notifikasi (User Settings)

**As a** User (terutama Orang Tua)  
**I want** mengatur preferensi notifikasi  
**So that** saya hanya menerima notifikasi yang saya inginkan

**Priority:** Could Have (Phase 2)  
**Estimation:** M (3 points)

**Acceptance Criteria:**

✅ **Given** orang tua di halaman "Pengaturan Notifikasi"  
   **When** halaman load  
   **Then** sistem tampilkan checklist: Notifikasi Absensi (WhatsApp/Email), Notifikasi Pembayaran, Notifikasi Pengumuman, Notifikasi Rapor

✅ **Given** orang tua hanya mau notifikasi absensi via WhatsApp, yang lain via email  
   **When** orang tua save preferensi  
   **Then** sistem hanya kirim notifikasi sesuai preferensi

✅ **Given** orang tua opt-out semua notifikasi  
   **When** ada event notifikasi  
   **Then** sistem tidak kirim apapun (kecuali notifikasi critical: tagihan overdue)

**Notes:**
- Preferensi per jenis notifikasi
- Preferensi channel: WhatsApp, Email, In-App
- Default: semua notifikasi ON

---

## US-NOTIF-009: Log & Tracking Notifikasi (Admin)

**As a** Admin/TU  
**I want** melihat log notifikasi yang terkirim  
**So that** saya dapat monitoring delivery status dan troubleshoot jika ada masalah

**Priority:** Should Have  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Log Notifikasi"  
   **When** admin pilih periode dan jenis notifikasi  
   **Then** sistem tampilkan list log: tanggal, jenis, recipient (user/nomor HP/email), status (sent/failed), error message (jika failed)

✅ **Given** ada notifikasi gagal terkirim  
   **When** admin lihat log  
   **Then** sistem tampilkan error detail dan tombol "Retry" untuk kirim ulang

✅ **Given** admin ingin export log  
   **When** admin klik "Export"  
   **Then** sistem generate Excel dengan data log

**Notes:**
- Filter: per jenis, per status, per periode
- Retry mechanism untuk failed notifications
- Retention: simpan log 6 bulan

---

## US-NOTIF-010: Notifikasi Custom/Manual (Admin)

**As a** TU/Admin  
**I want** kirim notifikasi custom ke user tertentu  
**So that** saya dapat komunikasi langsung jika ada hal penting

**Priority:** Could Have (Phase 2)  
**Estimation:** S (2 points)

**Acceptance Criteria:**

✅ **Given** admin di halaman "Kirim Notifikasi"  
   **When** admin pilih target (individual user atau group), isi pesan, dan pilih channel (WhatsApp/Email/In-App)  
   **Then** sistem kirim notifikasi sesuai setting

✅ **Given** admin ingin kirim ke orang tua siswa A secara individual  
   **When** admin input pesan "Mohon segera datang ke sekolah untuk ambil rapor" dan kirim  
   **Then** orang tua siswa A terima notifikasi

**Notes:**
- Free-text message (tidak template)
- Target: individual atau group
- Use case: reminder khusus, pemberitahuan urgent

---

## Summary: Notification System

| Story ID | Judul | Priority | Estimation | Phase |
|----------|-------|----------|------------|-------|
| US-NOTIF-001 | Notifikasi Absensi (WA) | Should Have | M | 1 |
| US-NOTIF-002 | Reminder Pembayaran | Should Have | M | 1 |
| US-NOTIF-003 | Broadcast Pengumuman | Should Have | M | 1 |
| US-NOTIF-004 | Approval/Rejection | Must Have | M | 1 |
| US-NOTIF-005 | Rapor Dirilis | Should Have | S | 1 |
| US-NOTIF-006 | Email Fallback | Should Have | M | 1 |
| US-NOTIF-007 | In-App Notification | Should Have | M | 1 |
| US-NOTIF-008 | Preferensi Notifikasi | Could Have | M | 2 |
| US-NOTIF-009 | Log & Tracking | Should Have | S | 1 |
| US-NOTIF-010 | Notifikasi Custom | Could Have | S | 2 |

**Total Estimation Phase 1:** 20 points (~3 weeks untuk 1 developer)

---

**Catatan Penting:**

### WhatsApp Integration Options (Fase 1):

1. **Manual/Semi-Auto** (Recommended untuk MVP):
   - Generate list nomor + pesan
   - TU kirim manual via WhatsApp Business broadcast
   - Cost: Gratis (hanya butuh WhatsApp Business)
   - Limitation: manual effort, tidak scalable

2. **WhatsApp Business API** (Recommended untuk Phase 2):
   - Provider: Fonnte, Wablas, Twilio (pilih yang paling cost-effective)
   - Cost: ~Rp 150-300 per pesan
   - Benefit: otomatis, scalable, tracking delivery

Untuk fase 1 MVP, bisa kombinasi: notifikasi critical (absensi, pembayaran) via manual broadcast, in-app notification untuk semua event.

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** ✅ Ready for Development
