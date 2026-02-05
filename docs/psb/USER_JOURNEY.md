# User Journey: PSB (Penerimaan Siswa Baru)

Dokumen ini menjelaskan alur penggunaan fitur PSB untuk berbagai tipe pengguna.

---

## 1. Alur Pendaftaran Calon Siswa (Public)

### Persona
- **Orang Tua** yang ingin mendaftarkan anaknya ke sekolah
- Tidak memerlukan login/akun

### Alur Langkah-demi-Langkah

```
┌─────────────────────────────────────────────────────────────────┐
│                    ALUR PENDAFTARAN PSB                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  [1] Landing Page (/psb)                                        │
│       │                                                         │
│       ├── Lihat informasi periode pendaftaran                   │
│       ├── Lihat persyaratan dokumen                             │
│       ├── Lihat timeline pendaftaran                            │
│       │                                                         │
│       ▼                                                         │
│  [2] Klik "Daftar Sekarang"                                     │
│       │                                                         │
│       ▼                                                         │
│  [3] Form Multi-Step (/psb/register)                            │
│       │                                                         │
│       ├── Step 1: Data Siswa                                    │
│       │   • Nama lengkap                                        │
│       │   • NIK (16 digit)                                      │
│       │   • Tempat & tanggal lahir                              │
│       │   • Jenis kelamin, agama                                │
│       │   • Alamat lengkap                                      │
│       │   • Anak ke-                                            │
│       │   • Asal sekolah (TK/PAUD)                              │
│       │                                                         │
│       ├── Step 2: Data Orang Tua                                │
│       │   • Data Ayah: Nama, NIK, pekerjaan, HP, email          │
│       │   • Data Ibu: Nama, NIK, pekerjaan, HP, email           │
│       │                                                         │
│       ├── Step 3: Upload Dokumen                                │
│       │   • Akte kelahiran (PDF/JPG/PNG, max 5MB)               │
│       │   • Kartu Keluarga (PDF/JPG/PNG, max 5MB)               │
│       │   • KTP Orang Tua (PDF/JPG/PNG, max 5MB)                │
│       │   • Pas foto 3x4 (JPG/PNG, max 2MB)                     │
│       │                                                         │
│       ├── Step 4: Review & Submit                               │
│       │   • Periksa semua data yang diisi                       │
│       │   • Klik "Kirim Pendaftaran"                            │
│       │                                                         │
│       ▼                                                         │
│  [4] Halaman Sukses (/psb/success/{id})                         │
│       │                                                         │
│       ├── Tampilkan nomor pendaftaran (PSB/2026/XXXX)           │
│       ├── Instruksi simpan nomor untuk tracking                 │
│       │                                                         │
│       ▼                                                         │
│  [5] Tracking Status (/psb/tracking)                            │
│       │                                                         │
│       ├── Input nomor pendaftaran                               │
│       ├── Lihat timeline status                                 │
│       └── Lihat status dokumen (jika ada revisi)                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Detail Interaksi

| Langkah | URL | Aksi User | Hasil yang Diharapkan |
|---------|-----|-----------|----------------------|
| 1 | `/psb` | Buka halaman PSB | Landing page dengan info periode |
| 2 | `/psb` | Klik "Daftar Sekarang" | Redirect ke form jika periode buka |
| 3.1 | `/psb/register` | Isi form Step 1 | Validasi realtime, tombol Lanjut aktif |
| 3.2 | `/psb/register` | Isi form Step 2 | Data orang tua tersimpan di form |
| 3.3 | `/psb/register` | Upload 4 dokumen | Preview file tampil |
| 3.4 | `/psb/register` | Review & Submit | Loading indicator, redirect ke sukses |
| 4 | `/psb/success/{id}` | Catat nomor pendaftaran | Nomor format PSB/2026/XXXX |
| 5 | `/psb/tracking` | Input nomor, klik Cek | Timeline status tampil |

---

## 2. Alur Admin Verifikasi PSB

### Persona
- **Admin/TU** dengan role SUPERADMIN atau ADMIN
- Login ke sistem

### Alur Langkah-demi-Langkah

```
┌─────────────────────────────────────────────────────────────────┐
│                 ALUR ADMIN VERIFIKASI PSB                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  [1] Login sebagai ADMIN                                        │
│       │                                                         │
│       ▼                                                         │
│  [2] Dashboard Admin                                            │
│       │                                                         │
│       ├── Klik menu "PSB" di sidebar                            │
│       │                                                         │
│       ▼                                                         │
│  [3] Dashboard PSB (/admin/psb)                                 │
│       │                                                         │
│       ├── Lihat statistik: Total, Pending, Approved, Rejected   │
│       ├── Lihat quick summary per status                        │
│       │                                                         │
│       ▼                                                         │
│  [4] Daftar Pendaftaran (/admin/psb/registrations)              │
│       │                                                         │
│       ├── Filter berdasarkan status                             │
│       ├── Search berdasarkan nama/nomor pendaftaran             │
│       ├── Filter berdasarkan tanggal                            │
│       │                                                         │
│       ▼                                                         │
│  [5] Detail Pendaftaran (/admin/psb/registrations/{id})         │
│       │                                                         │
│       ├── Section: Data Siswa                                   │
│       ├── Section: Data Orang Tua (Ayah & Ibu)                  │
│       ├── Section: Dokumen dengan preview                       │
│       ├── Section: Timeline status                              │
│       │                                                         │
│       ▼                                                         │
│  [6] Aksi Verifikasi                                            │
│       │                                                         │
│       ├── [SETUJUI] → Modal konfirmasi → Notifikasi email       │
│       ├── [TOLAK] → Input alasan → Notifikasi email             │
│       └── [MINTA REVISI] → Pilih dokumen → Input catatan        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Detail Aksi Verifikasi

| Aksi | Pre-condition | Input | Hasil |
|------|---------------|-------|-------|
| Setujui | Status: pending/document_review | Catatan (opsional) | Status → approved, email terkirim |
| Tolak | Status: pending/document_review | Alasan penolakan (wajib) | Status → rejected, email terkirim |
| Minta Revisi | Status: pending/document_review | Dokumen + catatan revisi | Status → document_review, email terkirim |

---

## 3. Status Flow

```
                    ┌──────────────┐
                    │   PENDING    │
                    │(Baru daftar) │
                    └──────┬───────┘
                           │
           ┌───────────────┼───────────────┐
           │               │               │
           ▼               ▼               ▼
    ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
    │   APPROVED   │ │   REJECTED   │ │ DOC_REVIEW   │
    │  (Diterima)  │ │  (Ditolak)   │ │(Perlu revisi)│
    └──────┬───────┘ └──────────────┘ └──────┬───────┘
           │                                  │
           │                                  │
           ▼                          Upload ulang dokumen
    ┌──────────────┐                          │
    │RE_REGISTRATION│◄────────────────────────┘
    │(Daftar ulang) │
    └──────┬───────┘
           │
           ▼
    ┌──────────────┐
    │  COMPLETED   │
    │   (Selesai)  │
    └──────────────┘
```

---

## 4. Notifikasi yang Dikirim

| Event | Penerima | Channel | Isi Notifikasi |
|-------|----------|---------|----------------|
| Pendaftaran disetujui | Email orang tua | Email | Selamat diterima + langkah daftar ulang |
| Pendaftaran ditolak | Email orang tua | Email | Informasi penolakan + alasan |
| Revisi dokumen | Email orang tua | Email | Daftar dokumen yang perlu direvisi |

---

## 5. Edge Cases

| Skenario | Handling |
|----------|----------|
| Periode pendaftaran tutup | Redirect ke landing dengan pesan error |
| Nomor pendaftaran tidak ditemukan | Tampilkan pesan "tidak ditemukan" |
| File upload melebihi limit | Validasi error dengan pesan ukuran max |
| NIK bukan 16 digit | Validasi error realtime |
| Admin approve yang sudah approved | Exception dengan pesan error |
| Akses success page > 5 menit | Redirect ke tracking page |
