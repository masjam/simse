# SIMSE — Sistem Informasi Manajemen Sekolah Terpadu
### SD Muhammadiyah Al Mujahidin Wonosari

![Build Status](https://img.shields.io/badge/build-passing-brightgreen)
![Tests](https://img.shields.io/badge/tests-87%20passed%20(787%20assertions)-brightgreen)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red)
![PHP](https://img.shields.io/badge/PHP-8.3-blue)
![Inertia](https://img.shields.io/badge/Inertia.js-v2-purple)
![Vue](https://img.shields.io/badge/Vue.js-3.x-emerald)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v3-cyan)
![Database](https://img.shields.io/badge/MySQL-UUID%20v7-orange)

**SIMSE** adalah aplikasi manajemen sekolah terintegrasi yang dirancang khusus untuk memenuhi seluruh siklus operasional **SD Muhammadiyah Al Mujahidin Wonosari** (Gunungkidul, D.I. Yogyakarta) — sekolah dasar berkarakter *"Sekolah Ramah Bakat, Berakhlak Mulia, & Belia Mendunia"*.

---

## 📑 Daftar Isi
1. [Status Pengembangan & Sprint](#-status-pengembangan--sprint)
2. [Fitur Unggulan per Portal](#-fitur-unggulan-per-portal)
   - [1. Portal Super Admin](#1-portal-super-admin)
   - [2. Portal Guru](#2-portal-guru)
   - [3. Portal Bendahara & Keuangan](#3-portal-bendahara--keuangan)
   - [4. Portal Staf Sekolah](#4-portal-staf-sekolah)
   - [5. Portal Siswa (Mobile-First)](#5-portal-siswa-mobile-first)
   - [6. Portal Orang Tua (Wali Santri)](#6-portal-orang-tua-wali-santri)
   - [7. E-Kantin Digital & Kartu ATM Siswa (Cashless)](#7-e-kantin-digital--kartu-atm-siswa-cashless)
   - [8. Kiosk Presensi Siswa Mandiri (Terkunci MAC Address)](#8-kiosk-presensi-siswa-mandiri-terkunci-mac-address)
   - [9. PPDB Online & Konversi Otomatis Siswa Baru](#9-ppdb-online--konversi-otomatis-siswa-baru)
   - [10. CMS Website Sekolah & Landing Page Publik](#10-cms-website-sekolah--landing-page-publik)
3. [Arsitektur & Keamanan](#-arsitektur--keamanan)
4. [Persyaratan Sistem & Instalasi](#-persyaratan-sistem--instalasi)
5. [Daftar Kredensial Akun Demo](#-daftar-kredensial-akun-demo)
6. [Pengujian & Jaminan Mutu](#-pengujian--jaminan-mutu)

---

## 🚀 Status Pengembangan & Sprint

Pengembangan dilakukan secara bertahap dalam **6 Sprint terstruktur** dengan status **100% Selesai & Teruji**:

| Sprint | Modul / Fokus Pengembangan | Status |
|---|---|:---:|
| **Sprint 1** | **Portal Siswa Mobile-First**: Dashboard, Jadwal, Nilai/e-Raport, LMS (Materi & Tugas), CBT Interaktif dengan Timer Server & Anti-Cheat (Visibility API, fullscreen lock), KTS Digital dengan QR Code dinamis. | ✅ Selesai |
| **Sprint 2** | **Portal Orang Tua**: Login Passwordless via WhatsApp OTP + Tanggal Lahir Anak, Panel 4 Tab (Akademik, Presensi, Keuangan, BK), Switcher Multi-Anak, Pengajuan Prestasi Siswa Mandiri. | ✅ Selesai |
| **Sprint 3** | **WhatsApp Gateway & Pusat Notifikasi**: Pengiriman WA via Queue Job (abstraksi driver Log/Fonnte/Wablas), Notifikasi In-App Realtime (Bell Icon), Saklar Microservices / Toggle Modul Sistem. | ✅ Selesai |
| **Sprint 4** | **E-Kantin Cashless & Kartu Siswa ATM**: Role Pengelola Stand, Pembayaran Dompet Digital Siswa dengan PIN 6 digit, Kartu Siswa RFID ATM, Batas Jajan Harian orang tua, Laporan Penjualan Stand. | ✅ Selesai |
| **Sprint 5** | **Absensi Lanjutan, Presensi Geolokasi, & Portal Staf**: Presensi Guru/Staf radius GPS koordinat sekolah, integrasi presensi ke kalkulasi Payroll, Sirkulasi Buku Perpustakaan fisik & stok, pembersihan mock data. | ✅ Selesai |
| **Sprint 6** | **PPDB, CMS Website, & Penyempurnaan Akhir**: Pendaftaran PPDB 3 langkah, berkas privat (KK, Akta, Ijazah TK), konversi atomik calon siswa diterima ke siswa aktif + akun ortu + kelas 1 + dompet digital, CMS Berita sekolah terhubung ke Landing Page `Welcome.vue`, Gamifikasi LMS (XP & Lencana), Format Cetak e-Raport resmi. | ✅ Selesai |

---

## 🌟 Fitur Unggulan per Portal

### 1. Portal Super Admin
- **Pusat Kendali Data Induk**: Manajemen Akun Pengguna, Guru, Siswa, Staf, Tingkat (1-6), Rombongan Belajar, dan Mata Pelajaran.
- **Jadwal Pelajaran Otomatis**: Algoritma penjadwalan pintar untuk mencegah bentrok guru dan ruang kelas.
- **Ekspor & Impor Data**: Manajemen data massal berbasis format Excel template resmi.
- **Manajemen Microservices (`/admin/modules`)**: Kemampuan menyembunyikan menu dan memblokir route dinamis untuk modul yang dinonaktifkan via middleware `EnsureModuleIsActive`.
- **Pengaturan WhatsApp Gateway (`/sistem/wa-gateway`)**: Konfigurasi provider pesan (Fonnte/Wablas/Log dev), broadcast pengumuman massal, monitoring log pengiriman, dan fitur kirim ulang (retry).
- **Verifikasi Prestasi Siswa**: Verifikasi sertifikat/piagam yang diajukan orang tua sebelum resmi tercantum di buku e-Raport siswa.

### 2. Portal Guru
- **Dashboard Mengajar & Jadwal Harian**: Tinjauan kelas hari ini dan agenda tatap muka.
- **Jurnal Mengajar Harian**: Pencatatan aktivitas materi harian yang otomatis terangkum menjadi **Materi Mingguan** bagi siswa dan orang tua.
- **Presensi Siswa per Jam Pelajaran**: Rekapitulasi kehadiran dengan notifikasi otomatis ke WhatsApp orang tua jika siswa tercatat Alfa/Sakit.
- **LMS (Learning Management System)**: Unggah materi teks, video pembelajaran, dan dokumen referensi ke storage privat. Penugasan tugas daring dan penilaian berbobot.
- **CBT (Computer-Based Test)**: Bank soal pilihan ganda, benar/salah, dan esai. Penilaian otomatis untuk soal obyektif.
- **Bimbingan Konseling (BK)**: Pencatatan poin pelanggaran siswa, penerbitan otomatis Surat Peringatan (SP 1, SP 2, SP 3), penjadwalan sesi konseling, serta cetak Berita Acara Konseling resmi.
- **Penilaian & e-Raport Cetak**: Rekapitulasi nilai capaian kompetensi, predikat, catatan perkembangan, kop resmi Majelis Dikdasmen, dan catatan prestasi terverifikasi.

### 3. Portal Bendahara & Keuangan
- **Dashboard Finansial & KPI Kasir**: Tinjauan total pendapatan, penerimaan kas, piutang, dan saldo dompet digital siswa.
- **Kasir Cepat & Verifikasi Transfer**: Pelayanan pembayaran tunai di kasir dan verifikasi bukti transfer yang diunggah orang tua secara online.
- **Manajemen Tagihan SPP Multi-Periode**:
  - Konfigurasi pos tagihan master berperiode: **Bulanan, Semester, Tahunan, dan Sekali Bayar**.
  - Distribusi tagihan siswa massal per kelas dengan selektor sub-periode otomatis (*Januari–Desember* untuk Bulanan, *Semester Ganjil/Genap* untuk Semester).
  - Monitoring tunggakan siswa dan pengiriman surat tegangan via WhatsApp dalam satu klik.
- **Payroll & Penggajian Terintegrasi**: Perhitungan tunjangan dan honor presensi guru/staf secara otomatis berdasarkan data kehadiran riil (`$attendanceDays * Rp 25.000`).
- **Monitoring E-Kantin**: Laporan omset penjualan, pembagian bagi-hasil stand kantin, dan rekap mutasi dompet siswa.

### 4. Portal Staf Sekolah
- **Presensi Berbasis Geolokasi**: Verifikasi presensi masuk dan pulang berdasarkan titik koordinat GPS dan toleransi radius resmi kampus SD Muhammadiyah Al Mujahidin Wonosari.
- **Sirkulasi Buku Perpustakaan**: Peminjaman dan pengembalian buku, perhitungan denda keterlambatan, serta sinkronisasi stok buku fisik secara otomatis.
- **Slip Gaji Digital**: Akses transparan rincian slip gaji bulanan lengkap dengan komponen tunjangan dan potongan kehadiran.

### 5. Portal Siswa (Mobile-First)
- **Dashboard Siswa Responsif**: Mobile-first design yang menampilkan jadwal pelajaran hari ini, hitung mundur tugas mendekati deadline, ujian aktif, dan saldo dompet digital.
- **KTS Digital (Kartu Tanda Siswa)**: Dilengkapi foto, identitas siswa lengkap, dan **QR Code dinamis** yang terhubung dengan sistem presensi dan profil.
- **LMS Interaktif & Gamifikasi Belajar**:
  - Akses materi per mapel, streaming video, dan pengunduhan lampiran dokumen yang terenkripsi aman.
  - Pengumpulan tugas online (unggah dokumen dan jawaban teks).
  - **Sistem Gamifikasi**: Akumulasi XP poin, level progresif (Level 1 *Penuntut Ilmu Pemula* s.d. Level 5 *Cendekiawan Belia Mendunia*), dan lencana prestasi belajar (*Langkah Pertama, Kolektor Nilai A, Santri Tekun, Bintang Mujahidin*).
- **CBT Anti-Cheat Mandiri**:
  - Timer berbasis waktu server (tidak ter-reset saat halaman direfresh).
  - Anti-kecurangan aktif: Deteksi pergantian tab browser (*Page Visibility API*), pemblokiran klik kanan/inspeksi, kunci mode layar penuh (*Fullscreen Lock*), pencatatan ke log pelanggaran `exam_violations`, dan auto-submit otomatis jika melanggar batas toleransi guru.
- **E-Kantin Cashless Siswa**: Belanja jajanan sehat langsung dari kantin sekolah dengan saldo dompet digital dan otentikasi PIN 6 digit.

### 6. Portal Orang Tua (Wali Santri)
- **Login Passwordless (Bebas Password)**: Masuk cukup dengan nomor WhatsApp yang terdaftar dan tanggal lahir anak, diverifikasi melalui kode OTP WhatsApp (keamanan ekstra dengan rate limiting & lockout anti brute-force).
- **Dukungan Multi-Anak**: Orang tua dengan lebih dari satu anak dapat beralih santri (*switch active child*) secara instan dalam satu akun.
- **Panel 4 Tab Informasi Lengkap**:
  1. **Akademik**: Jadwal kelas, rekap nilai per kompetensi, materi mingguan dari jurnal guru, dan daftar prestasi yang telah disetujui.
  2. **Presensi**: Rekap kehadiran harian (Hadir, Sakit, Izin, Alfa) yang transparan.
  3. **Keuangan**: Tagihan SPP Bulanan/Semester, riwayat pembayaran lunas, formulir upload bukti transfer, serta pengaturan **Batas Jajan Harian** dompet digital anak di kantin.
  4. **Perilaku & BK**: Catatan poin pelanggaran siswa dan status pembinaan konseling.
- **Pengajuan Prestasi Mandiri**: Orang tua dapat mengunggah bukti piagam lomba/kejuaraan anak untuk diverifikasi oleh pihak sekolah.

### 7. E-Kantin Digital & Kartu ATM Siswa (Cashless)
- **Role Pengelola Stand Kantin**: Pengelolaan menu makanan/minuman, foto, stok harian, dan validasi pesanan siswa.
- **Kartu Siswa RFID ATM**:
  - Kartu Tanda Siswa fisik berbasis RFID dapat digunakan sebagai **Kartu Debit / ATM Sekolah**.
  - Pengecekan saldo kartu di stand kantin (`/kantin/rfid-pos`) dan verifikasi PIN rahasia 6-digit milik siswa sebelum saldo dipotong.
  - Seluruh pemotongan saldo dan pencatatan mutasi dompet berjalan dalam transaksi atomik (`DB::transaction`).

### 8. Kiosk Presensi Siswa Mandiri (Terkunci MAC Address)
- **Endpoint Kiosk Mandiri (`/kiosk-presensi`)**: Halaman khusus layar sentuh / tap kartu untuk gerbang masuk sekolah.
- **Keamanan Perangkat Keras (MAC Address Lock)**: Dilindungi middleware `EnsureKioskMacAllowed`. Hanya komputer/laptop dengan MAC Address terdaftar di menu admin (`/sistem/kiosk-devices`) yang diizinkan membuka kiosk presensi.

### 9. PPDB Online & Konversi Otomatis Siswa Baru
- **Alur Pendaftaran 3 Langkah (`/ppdb/daftar`)**:
  - Langkah 1: Biodata Calon Siswa (NIK, NISN, TTL, Asal TK).
  - Langkah 2: Data Orang Tua & No. WhatsApp aktif.
  - Langkah 3: Unggah Berkas Persyaratan (KK, Akta Kelahiran, Ijazah TK) yang disimpan di direktori privat terproteksi.
- **Notifikasi WhatsApp**: Pengiriman nomor registrasi pendaftaran unik otomatis ke WA orang tua.
- **Konversi Atomik Otomatis**: Ketika panitia admin menyetujui pendaftaran (*Status: Diterima*), sistem secara otomatis membuatkan:
  1. Akun `User` siswa (role `Siswa`).
  2. Akun `User` orang tua (role `Orang Tua`).
  3. Record data induk `Student` dan penempatan rombel kelas 1.
  4. Inisialisasi dompet digital `Wallet` (saldo Rp 0).
  5. Notifikasi in-app ucapan selamat kepada akun orang tua.

### 10. CMS Website Sekolah & Landing Page Publik
- **Landing Page Interaktif (`Welcome.vue`)**: Menampilkan profil keunggulan (*Belia Mendunia, Tahfidzul Qur’an, Sekolah Ramah Bakat, Polcil & HW*), fasilitas kampus Wonosari, testimoni, dan integrasi feed Instagram sekolah.
- **Pusat Artikel & Berita (`/sistem/website`)**: CRUD berita, pengumuman, warta prestasi, dan artikel resmi yang langsung tayang secara dinamis di halaman depan.
- **Integrasi PPDB Online**: Akses satu-klik menuju pendaftaran PPDB dari seluruh tautan CTA beranda.

---

## 🔒 Arsitektur & Keamanan

- **Primary Key UUID**: Seluruh tabel menggunakan UUID v7 (`HasUuids`) untuk mencegah eksploitasi enumerasi ID (*Insecure Direct Object Reference*).
- **Proteksi Berkas Privat**: Berkas sensitif (bukti pembayaran transfer, berkas PPDB, lampiran tugas siswa, slip gaji) disimpan di `storage/app/` (disk `local` privat) dan hanya dilayani melalui *Streamed Response* terproteksi route middleware.
- **Otorisasi Data Ketat**: Data portal siswa dan orang tua dilindungi policy ketat sehingga pengguna hanya dapat melihat dan memanipulasi data milik diri sendiri atau anak kandungnya.
- **Integritas Transaksi Finansial**: Setiap mutasi dompet, transaksi kasir, pembayaran kantin, dan konversi PPDB dijalankan di dalam blok `DB::transaction` untuk mencegah inkonsistensi data atau *race conditions*.
- **Anti-Cheat CBT**: Kombinasi *Visibility API*, larangan navigasi keluar tab, fullscreen lock, dan pencatatan pelanggaran otomatis.

---

## 💻 Persyaratan Sistem & Instalasi

### Kebutuhan Perangkat Lunak
- **PHP**: Versi 8.3 atau lebih baru
- **Composer**: Versi 2.x
- **Node.js & NPM**: Versi 20.x atau lebih baru
- **Database Server**: MySQL 8.x / MariaDB 10.x (Direkomendasikan menggunakan **Laragon** pada Windows)
- **Web Browser**: Google Chrome / Microsoft Edge / Mozilla Firefox terbaru

### Langkah Instalasi

1. **Clone Repositori & Masuk ke Direktori**:
   ```bash
   git clone https://github.com/sdmujahidin/simse.git
   cd simse
   ```

2. **Instal Dependensi PHP**:
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend**:
   ```bash
   npm install
   ```

4. **Konfigurasi Berkas Lingkungan (`.env`)**:
   Salin `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi database Laragon:
   ```env
   APP_NAME=SIMSE
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://simse.test

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=simse_db
   DB_USERNAME=root
   DB_PASSWORD=

   # Konfigurasi WhatsApp Gateway (Pilihan: log, fonnte, wablas)
   WA_GATEWAY_PROVIDER=log
   WA_GATEWAY_TOKEN=
   ```

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database & Jalankan Seeder**:
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Kompilasi Aset Frontend**:
   ```bash
   # Mode pengembangan (live reload)
   npm run dev

   # Atau build produksi
   npm run build
   ```

8. **Jalankan Worker Antrean (Queue) untuk WhatsApp & Notifikasi**:
   ```bash
   php artisan queue:work
   ```

---

## 🔑 Daftar Kredensial Akun Demo

Untuk memudahkan eksplorasi dan verifikasi fitur, tersedia akun pengujian dengan kata sandi bawaan `password` atau OTP bypass pada environment lokal:

| Role | Email / Akun | Kata Sandi | Halaman Login |
|---|---|:---:|:---:|
| **Super Admin** | `admin@sdmujahidin.sch.id` | `password` | `/admin/login` |
| **Guru Pengajar** | `guru.demo@sekolah.com` | `password` | `/guru/login` |
| **Bendahara** | `bendahara@sdmujahidin.sch.id` | `password` | `/bendahara/login` |
| **Staf Sekolah** | `staf.demo@sekolah.com` | `password` | `/staf/login` |
| **Pengelola Stand Kantin** | `stand.amanah@sekolah.com` | `password` | `/kantin/login` |
| **Siswa** | `siswa.demo@sekolah.com` | `password` *(PIN Dompet: 123456)* | `/siswa/login` |
| **Orang Tua** | No. WA: `081234567890` • TTL Anak: `2016-05-15` | *Kode OTP WhatsApp* | `/ortu/login` |

---

## 🧪 Pengujian & Jaminan Mutu

Aplikasi ini dilengkapi rangkaian uji otomatis menyeluruh menggunakan **PHPUnit** dan pemformat kode resmi **Laravel Pint**.

- **Menjalankan Seluruh Test Suite**:
  ```bash
  php artisan test
  ```
  *Hasil pengujian terakhir:* **87 tests passed, 0 failures (787 assertions)**.

- **Memformat Kode Sesuai Standar Laravel**:
  ```bash
  vendor/bin/pint --format agent
  ```

- **Memeriksa Kompilasi Frontend**:
  ```bash
  npm run build
  ```

---

<p align="center">
  Dikelola dan dikembangkan dengan ❤️ untuk kemajuan pendidikan anak bangsa<br>
  <strong>SD Muhammadiyah Al Mujahidin Wonosari, Gunungkidul</strong>
</p>
