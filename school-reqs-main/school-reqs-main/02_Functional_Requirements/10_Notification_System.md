# Modul 10: Notification System (Sistem Notifikasi)

## 📋 Overview

Sistem notifikasi terintegrasi untuk komunikasi otomatis via WhatsApp, Email, dan In-App Notification.

**Module Code:** `NOTIF`  
**Priority:** P3 (Low - Support Module)  
**Dependencies:** Semua modul (sebagai service layer)

---

## 🎯 Tujuan

1. Otomasi komunikasi dengan stakeholder
2. Reminder & alert real-time
3. Meningkatkan engagement orang tua
4. Mengurangi beban manual komunikasi TU/Guru
5. Tracking delivery & read status notification

---

## 📖 Notification Channels

### 1. WhatsApp (Priority Channel)

**Use Cases:**
- Reminder pembayaran SPP
- Alert absensi (siswa alpha)
- Pengumuman penting
- Approval notification (izin/sakit disetujui/ditolak)
- PSB status update
- Welcome message untuk siswa baru

**Integration:**
- WhatsApp Business API atau Third-party (Fonnte, Wablas, WA Gateway)
- Template message (pre-approved oleh WhatsApp)
- Support text + image/document (optional)

**Business Logic:**
- Kirim hanya jika nomor HP valid (format Indonesia)
- Retry mechanism: jika gagal, retry 3x dengan delay
- Log delivery status (sent, delivered, read, failed)
- Rate limiting: max X message per menit (avoid spam detection)

---

### 2. Email

**Use Cases:**
- Kwitansi pembayaran (PDF attachment)
- Rapor tersedia (PDF attachment atau link download)
- Laporan bulanan untuk Principal
- Kredensial akun baru (username & password)
- Newsletter/pengumuman panjang

**Integration:**
- SMTP (Gmail, Office365) atau Email Service (SendGrid, MailGun)
- Template HTML (responsive email template)
- Support attachment (PDF max 10MB)

**Business Logic:**
- Validate email format sebelum kirim
- Retry jika gagal
- Track open rate (optional, via tracking pixel)

---

### 3. In-App Notification (Bell Icon)

**Use Cases:**
- Real-time notification untuk user yang sedang login
- Task reminder (e.g., "Anda belum input absensi hari ini")
- Activity feed (e.g., "Nilai Matematika anak Anda telah diinput")

**Implementation:**
- Notification badge count (unread notifications)
- Notification list: title, message, timestamp, read/unread status
- Click notification → redirect ke page terkait
- Mark as read when clicked

---

## ⚙️ Functional Requirements

### FR-NOTIF-001: Notification Template Management
**Priority:** Must Have  
**Description:** Admin dapat mengelola template notifikasi.

**Details:**
- CRUD template untuk setiap jenis notifikasi
- Template support variable/placeholder: `{nama_siswa}`, `{jumlah}`, `{tanggal}`, dll
- Example template:
  ```
  Yth. Bapak/Ibu {nama_ortu},
  Tagihan SPP bulan {bulan} untuk {nama_siswa} sebesar Rp{jumlah} belum dibayar.
  Mohon segera melunasi. Terima kasih.
  ```
- Preview template dengan sample data
- Version control (history perubahan template)

---

### FR-NOTIF-002: Scheduled Notification (Cron Jobs)
**Priority:** Must Have  
**Description:** Sistem auto-send notification berdasarkan schedule.

**Scheduled Jobs:**
- **Daily (06:00):** Reminder input absensi ke guru
- **Daily (15:00):** Alert alpha ke orang tua (jika anak alpha hari ini)
- **Tanggal 5 setiap bulan:** Reminder pembayaran SPP (H-5 before due date)
- **Tanggal 10 setiap bulan:** Reminder due date SPP
- **Tanggal 17 setiap bulan:** Warning tunggakan (H+7 after due date)
- **Weekly (Jumat 16:00):** Summary kehadiran siswa ke Principal
- **Monthly (tanggal 1):** Laporan bulanan ke Principal

**Implementation:**
- Cron jobs atau scheduled tasks (Node-cron, Bull Queue, dll)
- Queue system untuk batch notification (avoid overload)
- Configurable schedule di settings

---

### FR-NOTIF-003: Manual Broadcast Notification
**Priority:** Should Have  
**Description:** TU/Admin dapat broadcast notification manual ke group tertentu.

**Details:**
**Broadcast Options:**
- **Target:**
  - Semua Orang Tua
  - Orang Tua per Kelas
  - Semua Guru
  - Specific Users (multi-select)
- **Channel:** WhatsApp, Email, atau Both
- **Content:**
  - Subject (untuk email)
  - Message (textarea, support variable)
  - Attachment (optional, PDF/image)
- **Schedule:** Send Now atau Schedule untuk tanggal/jam tertentu

**Use Cases:**
- Pengumuman libur sekolah
- Undangan rapat orang tua
- Informasi kegiatan sekolah

**Process:**
1. TU buka "Broadcast Notification"
2. Pilih target, channel, compose message
3. Preview
4. Send atau Schedule
5. Sistem queue message & send in batch
6. Show delivery report (sent, failed)

---

### FR-NOTIF-004: Notification Log & History
**Priority:** Should Have  
**Description:** Sistem menyimpan log semua notification untuk audit & troubleshooting.

**Log Data:**
- Notification ID
- Type (payment_reminder, attendance_alert, dll)
- Channel (WhatsApp, Email, In-App)
- Recipient (user_id, nomor HP, email)
- Message content
- Status (pending, sent, delivered, read, failed)
- Sent at (timestamp)
- Delivered at (timestamp, jika available from provider)
- Read at (timestamp, jika available)
- Error message (jika failed)

**View:**
- Table log dengan filter (type, channel, status, date range)
- Search by recipient
- Retry failed notification (manual button)

---

### FR-NOTIF-005: Notification Preferences (User Settings)
**Priority:** Could Have  
**Description:** User dapat set preferensi notifikasi.

**Settings:**
- Enable/Disable notification per type:
  - Payment Reminder: On/Off
  - Attendance Alert: On/Off
  - Grade Update: On/Off
  - Announcement: On/Off
- Preferred Channel: WhatsApp, Email, Both
- Quiet Hours: jam berapa tidak mau terima notifikasi (e.g., 21:00-07:00)

**Access:**
- Parent: set preferensi untuk notifikasi anak sendiri
- Teacher: set preferensi untuk notifikasi profesional
- Default: semua On, WhatsApp + Email

---

## 📏 Business Rules

### BR-NOTIF-001: Rate Limiting
- Max 10 WhatsApp per nomor per hari (avoid spam detection)
- Max 50 Email per alamat per hari
- Batch notification: max 100 per batch, delay 1 detik antar batch

### BR-NOTIF-002: Retry Policy
- Jika failed, retry max 3x dengan exponential backoff (1 menit, 5 menit, 15 menit)
- Setelah 3x retry failed, mark as permanently failed & alert admin

### BR-NOTIF-003: Priority
- Critical notification (e.g., emergency alert) langsung send, bypass queue
- Normal notification via queue (FIFO)

### BR-NOTIF-004: Opt-Out
- User dapat opt-out dari notification (kecuali critical notification)
- Opt-out tersimpan di user preferences

---

## 🎨 UI/UX Requirements

### In-App Notification (Bell Icon)

**UI:**
- Bell icon di navbar dengan badge count (unread)
- Click icon → dropdown list notification (max 10 latest)
- Per notification: icon, title, message (excerpt), timestamp (relative: "2 menit yang lalu")
- Unread: background highlight, bold text
- Read: normal text
- Click notification → mark as read & redirect
- "Lihat Semua" button → full notification page

**Full Notification Page:**
- List all notifications (pagination)
- Filter: All, Unread, Read
- Bulk action: Mark All as Read, Delete All
- Per notification: expandable (click untuk lihat full message)

**Mobile:**
- Notification icon di bottom navbar atau top bar
- Swipe notification untuk delete (optional)

---

### Broadcast Notification Page (TU/Admin)

**UI:**
- Form dengan sections:
  - Target Selection (checkboxes atau multi-select dropdown)
  - Channel Selection (radio: WhatsApp, Email, Both)
  - Message Composition (textarea dengan character counter, variable helper)
  - Attachment Upload (optional)
  - Schedule (radio: Send Now, Schedule untuk later dengan date-time picker)
- Preview button → show preview dengan sample data
- Send button (disabled until all required fields filled)

**After Send:**
- Progress bar/spinner: "Sending... {X}/{Y}"
- Success page: "Notification berhasil dikirim ke {X} penerima. {Y} failed. Lihat Log"
- Link to delivery report

---

## 📊 Acceptance Criteria Summary

### MVP Must Have:
- ✅ WhatsApp notification integration (via third-party API)
- ✅ Email notification (SMTP)
- ✅ In-app notification (bell icon dengan badge count)
- ✅ Scheduled notification (cron jobs untuk reminder/alert otomatis)
- ✅ Notification template management
- ✅ Notification log (tracking delivery status)

### Should Have (MVP):
- ✅ Manual broadcast notification (TU send to group)
- ✅ Retry failed notification
- ✅ Notification preferences (user opt-in/opt-out)

### Could Have:
- ⬜ Push notification (untuk mobile app, Phase 2)
- ⬜ SMS notification (fallback jika WA failed)
- ⬜ Read receipt tracking (WhatsApp read status)
- ⬜ Notification analytics (open rate, click rate)
- ⬜ A/B testing untuk notification template

---

**Document Version:** 1.0  
**Last Updated:** 12 Desember 2025  
**Status:** Draft

