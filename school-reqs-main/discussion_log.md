# Dev
What project or system are you looking to build? Feel free to share:

The problem you're trying to solve
Who the users are
Any specific features or functionality you have in mind
Business constraints (timeline, budget, existing systems)

------
# User
Baik, Pak/Bu. Saya jelaskan dari kondisi sekolah kami.

Pertama, **masalah utama** yang sedang kami hadapi adalah semua administrasi masih manual. Mulai dari absensi, pembayaran SPP, rekap nilai, sampai laporan keuangan. Akibatnya sering terjadi keterlambatan laporan, data tercecer, dan pekerjaan TU jadi sangat berat.

**Pengguna sistem** nanti kemungkinan besar:

1. **Guru** – untuk absensi, input nilai, rekap kegiatan belajar.
2. **Orang tua siswa** – untuk melihat informasi pembayaran, pengumuman, dan perkembangan anak mereka.
3. **TU / Administrasi** – untuk menerima pembayaran, mencatat data siswa, dan membuat laporan.
4. **Kepala sekolah** – untuk pemantauan keseluruhan dan laporan keuangan/akademik.
5. **Siswa** – mungkin hanya bagian tertentu seperti pengumuman atau tugas, tapi tidak prioritas.

Untuk **fitur yang kami bayangkan**, secara garis besar:

* **Absensi digital** (guru bisa input langsung)
* **Sistem pembayaran SPP** (rekap otomatis, reminder ke orang tua)
* **Rekap nilai & rapor digital**
* **Pendaftaran siswa baru online**
* **Dashboard sekolah** (laporan keuangan, jumlah siswa aktif, grafik absensi)
* **Manajemen guru** (jadwal, honor, evaluasi)
* **Website sekolah** sebagai front-facing platform

Mengenai **batasan bisnis**:

* **Anggaran terbatas**, kami bisa bayar bulanan, asal tidak terlalu besar.
* **Waktu pengembangan** idealnya sebelum tahun ajaran baru berikutnya (sekitar 5–6 bulan).
* **Tidak ada sistem existing**, semuanya dari nol.
* Staf kami tidak terlalu tech-savvy, jadi UI harus sederhana dan mudah dipakai.

Jika diperlukan, saya bisa jelaskan masalah lebih rinci satu per satu.

Dari sini, apa tahapan berikutnya yang perlu kami bahas?

------
# Dev
Terima kasih atas penjelasan lengkapnya, Pak/Bu. Saya sudah mendapat gambaran yang cukup jelas. Sekarang saya akan mulai menggali lebih dalam untuk memetakan requirement dengan detail.

Saya akan tanyakan beberapa hal penting untuk memastikan sistem yang akan dibangun sesuai kebutuhan:

## **A. Tentang Sekolah & Skala Operasional**

1. **Jenjang pendidikan** apa sekolah Bapak/Ibu? (SD/SMP/SMA/SMK?)
2. **Berapa jumlah**:
   - Siswa aktif saat ini?
   - Guru dan staf?
   - Kelas per tingkat?
3. Apakah sekolah punya **lebih dari satu lokasi/cabang**, atau hanya satu lokasi?

## **B. Proses Absensi Digital**

4. Untuk absensi guru input siswa, apakah:
   - Per kelas per mata pelajaran? Atau hanya absensi harian di pagi hari?
   - Apakah perlu fitur izin/sakit dengan dokumen pendukung?
5. Absensi guru sendiri bagaimana? Apakah perlu sistem presensi untuk guru (fingerprint/face recognition/manual)?

## **C. Sistem Pembayaran SPP**

6. **Metode pembayaran** yang diterima saat ini apa saja? (tunai, transfer, atau ingin integrasi payment gateway?)
7. Apakah SPP dibayar per bulan, atau ada sistem cicilan/paket semester/tahunan?
8. Apakah ada **jenis pembayaran lain** selain SPP? (uang gedung, seragam, kegiatan, dll)
9. Reminder ke orang tua mau lewat apa? WhatsApp, email, SMS, atau notifikasi app?

## **D. Rekap Nilai & Rapor Digital**

10. Sistem penilaian mengikuti **kurikulum apa**? (K13, Merdeka, atau lainnya?)
11. Komponen nilai apa saja yang perlu diinput? (UH, UTS, UAS, praktik, sikap, projek, dll)
12. Apakah rapor perlu bisa **dicetak langsung** dalam format tertentu, atau cukup dilihat online?

## **E. Pendaftaran Siswa Baru (PSB Online)**

13. Proses PSB saat ini seperti apa? Apakah:
    - Calon siswa daftar → verifikasi dokumen → tes → pengumuman → daftar ulang?
14. Apakah perlu **pembayaran formulir pendaftaran** secara online juga?
15. Dokumen apa saja yang harus diupload saat pendaftaran?

## **F. Dashboard & Laporan**

16. Kepala sekolah butuh laporan apa saja secara berkala? (harian/mingguan/bulanan?)
17. Format laporan yang biasa dipakai seperti apa? Excel, PDF, atau cukup tampilan di layar?

## **G. Manajemen Guru**

18. Honor guru dihitung berdasarkan apa? (per jam mengajar, bulanan tetap, atau kombinasi?)
19. Evaluasi guru seperti apa yang diinginkan? Apakah dari siswa, kepala sekolah, atau keduanya?

## **H. Teknis & Infrastruktur**

20. Apakah sekolah sudah punya:
    - Koneksi internet stabil?
    - Komputer/laptop di ruang TU dan guru?
21. Apakah orang tua siswa mayoritas punya smartphone? (untuk akses portal orang tua)
22. Lebih prefer sistem **berbasis web** (akses lewat browser) atau **mobile app**, atau keduanya?

-------
# User :

Baik — saya jawab semua poin dari sudut pandang **pemilik / kepala SD swasta** secara ringkas, jelas, dan kontekstual dengan kondisi sekolah kami.

# A. Tentang Sekolah & Skala Operasional

1. Jenjang: **SD (Sekolah Dasar)**.
2. Jumlah:

   * **Siswa aktif:** ± **180** siswa.
   * **Guru & staf:** **12 guru tetap** + **3 guru honorer**; 2 staf TU penuh waktu.
   * **Kelas per tingkat:** Rata-rata **2 kelas per tingkat** (kelas I s.d. VI).
3. Lokasi: **Satu lokasi (kampus tunggal)** di kota menengah di Jawa.

# B. Proses Absensi Digital

4. Absensi:

   * Kami butuh **dua mode absensi**:

     1. **Absensi harian pagi** (semua siswa hadir/tidak hadir).
     2. **Absensi per kelas per mata pelajaran** (untuk guru-guru agar catatan kehadiran mengikuti jam pelajaran).
   * Fitur izin/sakit: **Ya**, perlu — orang tua bisa mengajukan izin/surat sakit via upload foto dokumen (surat dokter/izin). Perlu status (izin/diverifikasi).
5. Absensi guru: kami **ingin sistem presensi untuk guru** tetapi untuk tahap awal **manual digital (PIN / login + location timestamp)** sudah cukup. Fingerprint/face recognition boleh dipertimbangkan di fase kedua jika anggaran memungkinkan.

# C. Sistem Pembayaran SPP

6. Metode pembayaran saat ini: mayoritas **tunai** dan **transfer bank** antar orang tua ke rekening sekolah. Ke depan kami **ingin opsi integrasi payment gateway** (otomatis recon, virtual account) namun ini bergantung biaya integrasi.
7. Skema SPP: **SPP dibayar bulanan**; beberapa orang tua memilih cicilan per semester — sistem harus mendukung fleksibilitas (bulanan/semester/khusus).
8. Jenis pembayaran lain: **ada** — uang gedung (sekali bayar), seragam, kegiatan (trip/ekskul), donasi, dll. Harus ada kategori/coa untuk tiap jenis.
9. Reminder: prefer **WhatsApp** (karena sudah jadi kanal utama komunikasi orang tua). Sekunder: **email** dan **notifikasi in-app** bila tersedia.

# D. Rekap Nilai & Rapor Digital

10. Kurikulum: **Kurikulum 2013 (K13)**.
11. Komponen nilai: harus mendukung **UH (penilaian harian), UTS, UAS, praktik/portofolio, nilai sikap (spiritual/sosial), proyek/penugasan**. Juga nilai KK/PK seperti keterampilan.
12. Rapor: **Perlu dapat dicetak** (PDF siap cetak, format standar sekolah/Dinas) *dan* dapat dilihat online oleh orang tua/guru. Fitur generate rapor per semester otomatis dari bobot komponen.

# E. Pendaftaran Siswa Baru (PSB Online)

13. Proses sekarang: manual (datang bawa berkas). Kami ingin alur: **daftar online → upload dokumen → verifikasi admin → (opsional) tes/seleksi → pengumuman → daftar ulang & pembayaran formulir**.
14. Pembayaran formulir: **baiknya online** (payment gateway/VA) agar otomatis tercatat.
15. Dokumen upload: **akte lahir/akta kelahiran**, **Kartu Keluarga**, **fotokopi KTP orang tua**, **rapor/sertifikat (jika pindahan)**, **pas foto**, dan **surat pindah** bila ada.

# F. Dashboard & Laporan

16. Laporan yang dibutuhkan kepala:

    * **Harian:** rekap absensi siswa & guru.
    * **Mingguan:** ringkasan kegiatan & masalah (kedisiplinan, izin).
    * **Bulanan:** laporan keuangan (pemasukan SPP, biaya lain), rekap keterlambatan/absensi menonjol, jumlah siswa aktif.
    * **Semester:** rekap akademik & rapor.
17. Format laporan: **tampilan di dashboard + export ke Excel dan PDF**. TU butuh Excel untuk rekonsiliasi, kepala sekolah butuh PDF untuk presentasi/arsip.

# G. Manajemen Guru

18. Honor guru: model saat ini **kombinasi** — gaji tetap untuk guru kelas + honor per jam untuk ekstra (ekskul) / pengganti. Sistem harus mendukung **perhitungan honor berdasarkan jam mengajar** dengan input jadwal.
19. Evaluasi guru: diinginkan **gabungan** — evaluasi oleh kepala sekolah (observasi), dan **survei singkat orang tua/siswa** (kepuasan). Hasil bisa disimpan dalam profil guru.

# H. Teknis & Infrastruktur

20. Ketersediaan:

    * **Koneksi internet:** ada **internet di sekolah** tetapi **kadang tidak stabil** (pakai ISP lokal, bandwidth terbatas).
    * **Perangkat:** Komputer/laptop tersedia di ruang TU (1–2 unit) dan 1 laptop untuk kepala sekolah; guru mayoritas hanya punya smartphone.
21. Orang tua: **mayoritas punya smartphone** (WhatsApp umum). Hanya sebagian kecil yang pakai email rutin.
22. Preferensi platform: **Sistem berbasis web responsif (akses browser)** sebagai prioritas, dengan kemampuan **PWA / mobile-friendly** agar orang tua dan guru bisa pakai via smartphone tanpa harus install banyak aplikasi native. Mobile app native bisa jadi fase 2 jika dibutuhkan.

---

## Catatan Prioritas & Pembatasan

* **Prioritas fase 1 (MVP)**: Absensi digital, rekap nilai dasar + rapor PDF, SPP digital sederhana (pencatatan manual + opsi transfer; gateway opsional), dashboard admin dasar, PSB sederhana (form + upload).
* **Fase 2**: Integrasi payment gateway, presensi biometrik, modul HR lanjutan, mobile app native, integrasi Dapodik (jika diperlukan di masa depan).
* **Anggaran:** kami **mendukung model langganan (SaaS) atau biaya implementasi + langganan**, namun anggaran terbatas — sistem harus hemat biaya operasional.
* **Pelatihan:** mohon alokasikan **pelatihan singkat untuk TU & guru** (1–2 sesi) dan dokumentasi manual singkat.

Jika ada yang ingin Anda rinci menjadi user story atau saya tandai sebagai keputusan yang perlu dipilih, silakan bilang. Saya siap menjawab dan memberikan contoh user story per peran (guru, TU, orang tua, kepala).
