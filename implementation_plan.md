# Rencana Implementasi (Implementation Plan) - Sistem Manajemen Sekolah Terpadu
**Tech Stack**: Laravel 11, Vue.js 3 (Composition API), Inertia.js, TailwindCSS, MySQL/PostgreSQL, Redis.

Proyek ini sangat besar (21 Modul). Mengembangkan semuanya sekaligus akan menyebabkan *bottleneck*. Rencana ini membaginya menjadi **6 Fase Iteratif (Agile)**.

---

## Fase 1: Fondasi Sistem, Autentikasi & Master Data (Modul 1, 17, 21)
Fase ini membangun tulang punggung aplikasi.
1. **Setup Infrastruktur**:
   - Inisialisasi Laravel + Vue 3 + Inertia + Tailwind.
   - Konfigurasi Database & Redis (untuk *caching* dan antrean *jobs*).
2. **Sistem Autentikasi & Multi-Role (Modul 1)**:
   - Setup spatie/laravel-permission untuk mengelola 11 Role.
   - Pembuatan sistem login (termasuk proteksi percobaan gagal, aktivasi akun instan, & Captcha).
   - Setup khusus login PIN 6-angka untuk transaksi dompet digital.
3. **Pengaturan Sistem & Manajemen Modul (Modul 21)**:
   - Fitur *Toggle* Modul (Aktif/Nonaktifkan modul seperti CBT, Kantin, dll).
   - Pengaturan Identitas Sekolah (Logo, Kop Surat, dll).
4. **Manajemen Akademik (Modul 17)**:
   - Master data: Tahun Ajaran, Kelas, Jurusan/Program Keahlian.
   - Master data Pengguna: Super Admin, Siswa, Guru, Staf.

## Fase 2: Portal Utama, Akademik & Presensi (Modul 2, 3, 7, 16)
Fase ini memastikan kegiatan operasional harian sekolah berjalan digital.
1. **KTS Digital & Generator QR Code (Modul 2)**:
   - Generator KTS dengan *barcode/QR code* tersimpan di profil Siswa & Guru.
2. **Absensi Digital & Deteksi Wajah (Modul 3)**:
   - Integrasi pustaka scanner QR (*vue-qrcode-reader*).
   - Integrasi Geolocation API untuk presensi Guru berbasis radius lokasi.
   - Integrasi pustaka *FaceAPI.js* untuk deteksi wajah (perekaman titik wajah & pencocokan saat absen).
3. **e-Raport & Manajemen Nilai (Modul 7)**:
   - Sistem input nilai massal (UI *spreadsheet-like*).
   - Fitur Ekspor/Impor nilai Excel (*Maatwebsite/Laravel-Excel*).
   - Generator PDF e-Raport.
4. **Perpustakaan Digital (Modul 16)**:
   - Katalog e-book dan koleksi buku fisik.
   - Integrasi PDF Viewer/ePub reader bawaan Vue.

## Fase 3: Pembelajaran & Ujian (LMS & CBT) (Modul 4, 5, 6)
Fase paling intensif sumber daya dan logika.
1. **Pembelajaran Online / LMS (Modul 6)**:
   - CRUD Materi berdasarkan Bab, pengumpulan tugas, kuis.
   - Gamifikasi: Sistem Poin, Badge Lencana, & Papan Peringkat (menggunakan database relasional dinamis).
2. **Diagnostik & Persiapan Server CBT (Modul 4)**:
   - Dashboard pemantauan RAM/CPU server realtime (menggunakan *Laravel Pulse*).
3. **Ujian Online CBT & Anti-Kecurangan (Modul 5)**:
   - Antarmuka ujian dengan 6 tipe soal (PG, Kompleks, Menjodohkan, Rumpang, dll).
   - **Anti-Cheat Engine**: Membaca *Window Visibility API* untuk mendeteksi *tab switch*, menonaktifkan *right-click*, dan *Full-screen API*.
   - Sinkronisasi timer menggunakan zona waktu server (*Carbon*), sehingga sisa waktu tidak hilang saat *refresh*.
   - WebSocket/Pusher untuk pengawasan realtime Guru terhadap siswa yang sedang ujian.

## Fase 4: Ekosistem Finansial Sekolah (Modul 9, 10, 11, 12)
Fase finansial yang membutuhkan tingkat keamanan transaksi ketat.
1. **Keuangan & SPP (Modul 9)**:
   - Master pos tagihan & distribusi tagihan otomatis.
   - Kasir SPP dengan filter tanggal, pencetakan kwitansi *thermal/dot-matrix*.
2. **Tabungan & Dompet Digital (Modul 10)**:
   - Sistem deposit (Top-up) dan riwayat mutasi.
3. **E-Kantin Digital (Modul 11)**:
   - Portal Stand Kantin: Kelola menu, stok, validasi pesanan via pemindai QR.
   - Portal Pemesanan Siswa: UI Keranjang belanja layaknya aplikasi *food delivery*, bayar pakai Dompet Digital (membutuhkan input PIN).
4. **Penggajian / Payroll (Modul 12)**:
   - Algoritma penggajian (berdasarkan kehadiran dari Modul 3).
   - Sistem kasbon/pinjaman, slip gaji digital dengan otentikasi QR.

## Fase 5: Portal Publik & Ekosistem Spesifik (Modul 8, 13, 14, 15, 20)
Penyelesaian portal interaksi mandiri (Mobile-First & Publik).
1. **Bimbingan Konseling (Modul 8)**:
   - Sistem poin pelanggaran, generator PDF Surat Peringatan (SP) dan Berita Acara.
2. **Portal Mobile-First (Modul 13 & 14)**:
   - **Siswa**: Dashboard *mobile-optimized* untuk jadwal, CBT, dan LMS.
   - **Orang Tua**: Login tanpa password (via OTP WhatsApp & Tanggal Lahir). Panel 4-tab realtime.
3. **Penerimaan Siswa Baru / PPDB (Modul 15)**:
   - Formulir pendaftaran bertahap (wizard), upload dokumen, konversi akun otomatis menjadi "Siswa".
4. **Website Profil Sekolah & CMS (Modul 20)**:
   - Halaman pendaratan (*Landing Page*), Blog, dan Galeri yang ramah SEO.

## Fase 6: Otomatisasi & Penyempurnaan (Modul 18, 19)
1. **WhatsApp Gateway & Broadcast (Modul 18)**:
   - Integrasi API WhatsApp (misal: Fonnte, Wablas, atau Baileys Node.js).
   - Pemicu (*Trigger*) pengiriman pesan: Siswa absen/pulang, OTP Login Ortu, tagihan jatuh tempo.
2. **Pusat Notifikasi (Modul 19)**:
   - Fitur *bell icon* realtime menggunakan Laravel WebSockets / Reverb.
3. **Testing, Optimasi, & UAT (User Acceptance Testing)**:
   - Penyesuaian UI/UX agar terlihat premium (animasi *glassmorphism*, skeleton loading).
   - Optimasi query database (Eager Loading) untuk fitur berat (SIAKAD & CBT).
