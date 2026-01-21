# Rekonsiliasi Bank

## Deskripsi

Fitur Rekonsiliasi Bank membantu Admin/TU mencocokkan transaksi pembayaran transfer dari sistem dengan mutasi rekening bank. Proses ini penting untuk:

1. **Verifikasi Pembayaran Transfer** - Memastikan pembayaran transfer yang dicatat sudah benar-benar masuk ke rekening sekolah
2. **Audit Trail** - Menyediakan bukti pencocokan antara data sistem dan data bank
3. **Deteksi Anomali** - Menemukan pembayaran yang tidak cocok atau transaksi mencurigakan

## Kapan Menggunakan Rekonsiliasi Bank

Gunakan fitur ini ketika:

- **Akhir Hari/Minggu**: Mencocokkan pembayaran transfer dengan mutasi bank
- **Sebelum Laporan Keuangan**: Memastikan data pembayaran akurat
- **Ada Keluhan Wali Murid**: Memverifikasi apakah transfer sudah diterima

## Alur Kerja (Workflow)

### 1. Upload Statement Bank

1. Login ke sistem sebagai Admin/TU
2. Buka menu **Pembayaran > Rekonsiliasi Bank**
3. Klik tombol **Upload Statement**
4. Pilih file mutasi bank (format .xlsx, .xls, atau .csv)
5. Isi nama bank dan tanggal statement (opsional)
6. Klik **Upload & Proses**

#### Format File yang Didukung

File Excel atau CSV dengan kolom:
- **Tanggal** - Tanggal transaksi
- **Deskripsi/Keterangan** - Informasi transaksi
- **Nominal/Jumlah** - Nilai transaksi
- **Referensi** (opsional) - Nomor referensi bank

Sistem akan otomatis mendeteksi kolom berdasarkan header.

### 2. Auto-Matching

Setelah upload, sistem akan menampilkan daftar transaksi. Klik **Auto Match** untuk:

- Mencari pembayaran yang cocok berdasarkan:
  - Nominal yang sama
  - Tanggal dalam rentang ±1 hari
  - Metode pembayaran transfer

- Hasil auto-match ditampilkan dengan tingkat keyakinan (confidence):
  - **≥95%**: Match sangat yakin (tanggal & nominal sama persis)
  - **70-94%**: Match kemungkinan besar benar
  - **<70%**: Tidak di-match otomatis, perlu manual

### 3. Manual Matching

Untuk transaksi yang tidak ter-match otomatis:

1. Klik tombol **Match** pada transaksi bank
2. Cari pembayaran di daftar dengan:
   - Nama siswa
   - Nomor kwitansi
   - NIS
3. Klik pembayaran yang cocok
4. Klik **Match** untuk konfirmasi

### 4. Verifikasi Rekonsiliasi

Setelah semua transaksi di-match:

1. Review hasil matching
2. Klik **Verifikasi Rekonsiliasi**
3. Sistem akan:
   - Mengubah status rekonsiliasi ke "Terverifikasi"
   - Memverifikasi pembayaran yang masih pending

## Status Rekonsiliasi

| Status | Deskripsi |
|--------|-----------|
| **Draft** | Baru diupload, belum diproses |
| **Processing** | Sedang dalam proses matching |
| **Completed** | Matching selesai, belum diverifikasi |
| **Verified** | Sudah diverifikasi admin |

## Tips Penggunaan

### Best Practices

1. **Upload Harian/Mingguan**: Jangan menumpuk terlalu lama agar matching lebih akurat
2. **Periksa Unmatched**: Transaksi unmatched bisa jadi:
   - Pembayaran belum dicatat di sistem
   - Transfer dari sumber lain (bukan pembayaran SPP)
3. **Simpan Bukti**: Export hasil rekonsiliasi sebagai arsip

### Troubleshooting

| Masalah | Solusi |
|---------|--------|
| File tidak terbaca | Pastikan format .xlsx/.xls/.csv dan ukuran <10MB |
| Kolom tidak terdeteksi | Pastikan header file berisi kata seperti "tanggal", "nominal", "deskripsi" |
| Auto-match tidak menemukan | Cek apakah pembayaran sudah dicatat dengan tanggal yang sama |
| Transaksi duplikat | Hapus rekonsiliasi draft dan upload ulang dengan file yang benar |

## Contoh Kasus

### Kasus 1: Verifikasi Pembayaran Harian

Admin download mutasi rekening BCA hari ini, upload ke sistem:
1. Auto-match menemukan 8 dari 10 transaksi
2. 2 transaksi di-match manual (tanggal berbeda 1 hari)
3. Verifikasi rekonsiliasi
4. Semua pembayaran transfer hari ini terverifikasi

### Kasus 2: Investigasi Keluhan Wali Murid

Wali murid klaim sudah transfer tapi belum tercatat:
1. Upload mutasi bank rentang tanggal sesuai klaim
2. Cari transaksi dengan nominal yang cocok
3. Jika ditemukan: catat pembayaran di sistem, lalu match
4. Jika tidak: minta bukti transfer dari wali murid

## Keamanan & Akses

- Hanya **ADMIN** dan **SUPERADMIN** yang dapat mengakses fitur ini
- Rekonsiliasi yang sudah terverifikasi tidak dapat dihapus
- Semua aktivitas dicatat di Audit Log

## Lihat Juga

- [Pembayaran - User Journey](../../guides/payment-user-journeys.md)
- [Laporan Keuangan](./PAY-payment-system.md)
