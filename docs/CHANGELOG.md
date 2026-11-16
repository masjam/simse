# Catatan Perubahan (Changelog) SIMSE

Semua perubahan penting pada proyek SIMSE didokumentasikan di berkas ini. Format mengikuti standar [Keep a Changelog](https://keepachangelog.com/).

---

## [Sprint 7] - Paket Tema Visual: 7 Arah Tema, Ornamen Semantik, Pemilih Tema & Peta Konfigurasi Peran

### Ditambahkan
- **7 Berkas CSS Paket Tema Khusus (`resources/css/themes/`)**:
  - `madinah.css`: Tema bawaan hijau-teal teduh dan aksen emas berwibawa (Super Admin, Operator, Guru).
  - `taman-belajar.css`: Ceria dan hangat ramah anak seperti buku cerita, radius besar 24px (Siswa Kelas 1-3).
  - `kertas-tinta.css`: Editorial klasik bernuansa kertas buku catatan dan perpustakaan (Guru e-Raport, Perpustakaan).
  - `ledger-tegas.css`: Presisi akuntansi garis batas tegas dan blok angka mono (Bendahara, Kasir, Payroll).
  - `senja-kaca.css`: Gradien lavender-persik lembut, ramah, dan hangat bagi keluarga (Orang Tua / Wali Murid).
  - `pasar-kantin.css`: Hangat dan menggugah selera selayaknya papan menu kuliner sehat (Stand Kantin, POS RFID).
  - `fokus-ujian.css`: Minimalis tenang tanpa ornamen dan ramah mata anti-lelah untuk ujian CBT.
- **Komponen Ornamen Visual Semantik (`App\Components\ui\ThemeOrnament.vue`)**:
  - SVG inline dengan opasitas rendah (6%) dan `aria-hidden="true"`, menampilkan motif khas masing-masing tema (geometri 8-point star, awan-bintang, cap perpustakaan, kisi kotak buku besar, dsb.) tanpa menyentuh struktur konten halaman.
- **Peta Aset & Ilustrasi Per Tema (`resources/js/config/themeAssets.js`)**:
  - Peta aset terpusat untuk EmptyState dan maskot tema tanpa percabangan if-else di dalam file halaman.
- **Komponen Pengalih Tema (`App\Components\ui\ThemeSwitcher.vue`)**:
  - Tombol elegan dengan preview indikator warna tema aktif dan modal pemilih tema interaktif yang menghormati izin `allowThemeSwitch`.
  - Terintegrasi pada topbar desktop, topbar mobile, dan footer sidebar `PortalShell.vue`.
- **Konfigurasi Tema Peran & Rute (`config/theme.php`)**:
  - Pemetaan bawaan: Admin & Guru (`madinah`), Siswa 1-3 (`taman-belajar`), Siswa 4-6 (`madinah`), Bendahara (`ledger-tegas`), Ortu (`senja-kaca`), Kantin (`pasar-kantin`), CBT (`fokus-ujian`), Rapor/Perpus (`kertas-tinta`).
  - Opsi `theme_allow_user_switch` pada `SystemSetting` memungkinkan Super Admin mengunci tema secara terpusat.
- **Dukungan Halaman Living Style Guide (`resources/js/Pages/Dev/DesignSystem.vue`)**:
  - Pengalih tema instan mencakup seluruh 9 paket tema terdaftar untuk verifikasi tampilan langsung.
- **Pengujian & Validasi Rasio Kontras**:
  - Pembaruan `scripts/check-contrast.mjs`: Menguji otomatis seluruh 9 tema untuk 7 pasang warna inti (WCAG AA >= 4.5:1), 100% lulus.
  - Test Feature `tests/Feature/Sprint7ThemePackageTest.php`: 5 skenario pengujian komprehensif (91 assertions) lulus 100%.

---

## [Sprint 6B & 6C] - Optimasi Kueri Beban 175 Siswa CBT Serentak, Panduan Deployment Debian 12 & Dokumen UAT 10 Peran

### Ditambahkan
- **Indeks Komposit Performa Database (`database/migrations/2026_11_16_090005_add_performance_composite_indexes.php`)**:
  - `exam_attempts`: Indeks komposit `idx_exam_attempts_exam_student` (`exam_id`, `student_id`) untuk lookup autosave instan (< 5ms) dan `idx_exam_attempts_exam_finished` (`exam_id`, `finished_at`) untuk live monitoring proctor.
  - `questions`: Indeks komposit `idx_questions_exam_type` (`exam_id`, `type`).
  - `options`: Indeks komposit `idx_options_question_correct` (`question_id`, `is_correct`) mempercepat evaluasi auto-grading.
  - `exam_violations`: Indeks komposit `idx_exam_violations_attempt_created` (`exam_attempt_id`, `created_at`).
  - `attendances`: Indeks komposit `idx_attendances_user_date_status` (`user_id`, `date`, `status`).
  - `point_events`: Indeks komposit `idx_point_events_student_created` (`student_id`, `created_at`).
  - `bills`: Indeks komposit `idx_bills_student_status` (`student_id`, `status`).
  - `advance_installments`: Indeks komposit `idx_advance_installments_advance_status` (`advance_id`, `status`).
- **Uji Beban Performa CBT 175 Siswa (`tests/Performance/CbtConcurrentExamLoadTest.php`)**:
  - Simulasi 175 peserta didik memulai ujian dan mengirimkan jawaban serentak.
  - Verifikasi latensi pencarian indeks per siswa berada di bawah 15ms dan kueri proctor monitor < 250ms tanpa bottleneck N+1.
- **Panduan Deployment & Hardening Server Debian 12 (`docs/DEPLOYMENT_DEBIAN.md`)**:
  - Spesifikasi sistem server produksi Debian 12 (Bookworm).
  - Konfigurasi Web Server Nginx VirtualHost dengan HTTP/2, SSL Certbot, security headers, Gzip, dan proteksi file privat.
  - Tuning PHP 8.5-FPM Pool (`pm.max_children = 60`) dan konfigurasi OPcache untuk akselerasi eksekusi skrip PHP.
  - Konfigurasi Supervisor worker untuk background queues (`simse-worker.conf`) dan cron scheduler.
  - Skrip deployment otomatis zero-downtime (`deploy.sh`) serta aturan firewall UFW.
- **Dokumen UAT Komprehensif 10 Peran (`docs/UAT.md`)**:
  - 67 kasus uji operasional mencakup Super Admin, Operator, Guru Pengajar, Guru BK, Siswa, Orang Tua, Bendahara, Staf TU, Pengelola Kantin, dan PPDB.
  - Matriks pengujian lengkap dan lembar pengesahan resmi.

---

## [Sprint 6A] - Pengerasan Keamanan: Header Keamanan, Rate Limiting Terpusat, Layanan Unggah Aman, 2FA TOTP RFC 6238 & Session Hygiene

### Ditambahkan
- **Header Keamanan Terpusat (`App\Http\Middleware\SecurityHeadersMiddleware`)**:
  - `X-Frame-Options: SAMEORIGIN` (mitigasi clickjacking).
  - `X-Content-Type-Options: nosniff` (mitigasi MIME sniffing).
  - `Referrer-Policy: strict-origin-when-cross-origin`.
  - `Permissions-Policy: camera=(self), geolocation=(self), microphone=()`.
  - `Content-Security-Policy-Report-Only` di lingkungan non-produksi dan mode protektif di produksi.
  - `Strict-Transport-Security` (HSTS) pada kanal HTTPS dengan `max-age=31536000` dan `includeSubDomains`.
  - Didaftarkan secara global pada pipeline HTTP aplikasi di `bootstrap/app.php`.
- **Rate Limiting Terpusat (`App\Providers\AppServiceProvider`)**:
  - `login`: Pembatasan 5 percobaan masuk per menit per kombinasi alamat pos-el dan alamat IP.
  - `parent_otp`: Pembatasan 3 permintaan kode OTP per 5 menit per nomor HP/IP dengan pesan umpan balik ramah pengguna.
  - `pin_verify`: Pembatasan 3 percobaan PIN dompet siswa & kasir kantin per menit sebelum akun ditangguhkan sementara (HTTP 429).
  - `verification`: Pembatasan 30 verifikasi dokumen/ijazah publik per menit per alamat IP.
  - `file_download`: Pembatasan 60 unduhan berkas privat per menit per pengguna terautentikasi / IP.
- **Layanan Unggah Berkas Aman (`App\Services\Security\SecureUploadService`)**:
  - Deteksi MIME sebenarnya berdasarkan analisis bytes/magic numbers (`getMimeType()`), bukan hanya ekstensi berkas kiriman klien.
  - Whitelist ketat dokumen sekolah: PDF, JPG, PNG, XLSX, DOCX.
  - Pencegahan script injection (pemindaian tanda tangan kode berbahaya `<?php`, `<script`, `eval`, `base64_decode`, dsb.).
  - Penyimpanan wajib pada disk privat `local` dengan penamaan acak terproteksi berbasis UUID (`Str::uuid()`).
- **Autentikasi Dua Faktor TOTP Standar RFC 6238 (`App\Services\Security\TotpService`)**:
  - Pembangkitan secret key Base32 dan pembuatan URI format `otpauth://totp/...` untuk pemindai authenticator (Google Authenticator, Aegis, 1Password, Microsoft Authenticator).
  - Render Data URL SVG QR Code bebas ketergantungan pihak ketiga eksternal menggunakan `chillerlan/php-qrcode`.
  - Verifikasi kode 6-digit TOTP dengan toleransi jendela waktu (window drift $\pm 1$ time-step 30 detik).
  - Pembangkitan 8 kode pemulihan darurat sekali pakai (single-use recovery codes) yang disimpan terenkripsi (`encrypt()`) di database.
  - Migrasi penambahan kolom 2FA pada tabel `users`: `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`.
  - Alur tantangan login 2FA di `AuthenticatedSessionController` dan `TwoFactorAuthenticationController`:
    - Pengguna dengan 2FA aktif dialihkan otomatis ke rute `two-factor.login` saat kata sandi utama lolos.
    - Opsi login fleksibel: kode 6-digit authenticator atau kode pemulihan darurat sekali pakai (otomatis terhapus setelah dipakai).
- **Antarmuka Pengguna Autentikasi Dua Faktor**:
  - Halaman tantangan login 2FA (`resources/js/Pages/Auth/TwoFactorChallenge.vue`) dengan shell bare, badge status, dan peralihan mulus antara kode 6-digit dan kode darurat.
  - Formulir manajemen 2FA di Profil Pengguna (`resources/js/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue`): status aktif/nonaktif, modal pemindaian QR code & kunci manual, penyimpanan kode pemulihan, dan konfirmasi kata sandi sebelum penonaktifan.
- **Kebersihan Sesi (Session Hygiene)**:
  - Invalidasi sesi di perangkat lain secara otomatis (`Auth::logoutOtherDevices()`) pada saat pengguna mengganti kata sandi di `PasswordController`.
  - Regenerasi ID sesi (`$request->session()->regenerate()`) pada saat masuk dan setelah lolos verifikasi tantangan 2FA untuk mencegah *session fixation*.
- **Integrasi Cloudflare Turnstile Bot Protection**:
  - Konfigurasi layanan `services.turnstile` di `config/services.php`.
  - Layanan verifikasi `App\Services\Security\TurnstileService` dan aturan validasi form `App\Rules\TurnstileRule` (dapat diaktifkan/dinonaktifkan lewat `TURNSTILE_ENABLED`).
- **Audit Dependensi**:
  - Penyelesaian kerentanan `npm audit` (resolusi dependensi high/critical ke versi aman).
- **Pengujian & Validasi**:
  - `tests/Feature/Sprint6ASecurityHardeningTest.php`: 9 skenario pengujian komprehensif keamanan (38 assertions) lulus 100%.
  - Suite pengujian lengkap aplikasi: 175 tests lulus 100% (1.233 assertions).

---

## [Sprint 5C] - Fasilitas Kasbon Pegawai & Portal Perpustakaan Siswa

### Ditambahkan
- **Sistem Kasbon & Pinjaman Pegawai (`advances` & `advance_installments`)**:
  - Skema tabel `advances` (nominal pokok, tenor 1-12 bulan, nominal angsuran bulanan, keperluan, status `Diajukan`|`Disetujui`|`Ditolak`|`Lunas`, persetujuan bendahara).
  - Skema tabel `advance_installments` (nomor angsuran, nominal potongan, bulan, tahun, status `Pending`|`Lunas`, keterkaitan ke slip gaji `payrolls`).
  - Layanan bisnis terpadu `App\Services\Payroll\AdvanceService`:
    - `requestAdvance()`: Pengajuan kasbon mandiri disertai validasi plafon dan pencegahan pengajuan ganda saat masih ada tanggungan aktif.
    - `approveAdvance()`: Persetujuan bendahara otomatis menghasilkan jadwal angsuran bulanan terstruktur.
    - `rejectAdvance()`: Penolakan resmi disertai alasan terperinci.
    - `applyPendingInstallmentToPayroll()`: Pemotongan otomatis angsuran kasbon pada saat generate slip gaji bulanan (`payroll_components` bertipe `Potongan`), pembaruan status angsuran menjadi `Lunas`, dan pelunasan kasbon otomatis ketika seluruh angsuran tuntas.
- **Portal Kasbon Mandiri Pegawai & Persetujuan Bendahara**:
  - **Portal Guru (`resources/js/Pages/Teacher/Kasbon.vue` & `Teacher\KasbonController`)**: Riwayat pengajuan, total tanggungan, rincian jadwal potongan gaji, dan modal pengajuan kasbon (`KasbonRequestModal.vue`).
  - **Portal Staf (`resources/js/Pages/Staff/Kasbon.vue` & `Staff\KasbonController`)**: Akses pengajuan pinjaman darurat bagi tenaga kependidikan.
  - **Portal Bendahara (`resources/js/Pages/Bendahara/Kasbon.vue` & `Bendahara\KasbonController`)**: Dasbor persetujuan/penolakan kasbon, metrik perputaran dana disalurkan vs outstanding sisa piutang, dan filter status pengajuan.
- **Portal Perpustakaan & Literasi Siswa**:
  - Halaman Perpustakaan Siswa (`resources/js/Pages/Student/Perpustakaan.vue` & `Student\PerpustakaanController`):
    - Katalog buku fisik sekolah, stok ketersediaan, pencarian judul/penulis, dan filter kategori.
    - Riwayat peminjaman buku fisik (tanggal pinjam, batas waktu pengembalian, deteksi keterlambatan).
    - Akses baca e-book resmi terproteksi (`GET /siswa/perpustakaan/{book}/read` streaming PDF dari disk `local`).
    - Integrasi gamifikasi literasi: Membaca buku memberikan +30 Poin Gamifikasi dan membuka otomatis lencana `book_explorer` ("Penjelajah Pustaka").
- **Navigasi Terintegrasi (`resources/js/config/navigation.js`)**:
  - Penambahan menu `Kasbon Pegawai` pada Guru Pengajar, Staf Sekolah, dan Bendahara.
  - Penambahan menu `Perpustakaan` pada portal Siswa.
- **Pengujian & Validasi**:
  - `tests/Feature/Sprint5CAdvanceAndLibraryTest.php`: 8 skenario pengujian komprehensif, 70 assertions, 100% lulus.

---

## [Sprint 5B] - Gamifikasi Pembelajaran LMS: Lencana Prestasi, Engine Poin, Papan Peringkat & Kontrol Admin

### Ditambahkan
- **Model & Skema Database Gamifikasi (`database/migrations/2026_11_16_090002_create_sprint_5b_gamification_tables.php`)**:
  - `point_events`: Audit log perolehan poin siswa ber-UUID (`student_id`, `event_type`, `points`, `reference_type`, `reference_id`, `notes`).
  - `badges`: Katalog master lencana penghargaan (`code`, `name`, `description`, `icon`, `category`, `points_required`, `threshold_count`, `is_active`).
  - `student_badges`: Relasi pivot perolehan lencana peserta didik (`student_id`, `badge_id`, `awarded_at`).
  - `BadgeSeeder`: Seeder 7 lencana default bernilai islami dan edukatif ("Langkah Pertama", "Kolektor Nilai A", "Pendekar CBT", "Santri Tekun", "Penjelajah Pustaka", "Teladan Akhlak", "Bintang Al-Mujahidin").
- **Layanan Inti Gamifikasi (`App\Services\Gamification\PointService`)**:
  - `award($student, $eventType, $reference, $notes, $customPoints)`: Pemberian poin otomatis terenkapsulasi transaksi database dan audit logging.
  - `checkAndUnlockBadges($student)`: Evaluasi otomatis pencapaian metrik siswa (tugas, nilai kuis, CBT, peminjaman buku, bimbingan konseling positif, ambang batas poin).
  - `getStudentProfile($student)`: Rekapitulasi level siswa (Level 1 Penuntut Ilmu Pemula s.d. Level 5 Cendekiawan Belia Mendunia), cincin persentase kemajuan level, status perolehan lencana, dan riwayat aktivitas poin terakhir.
  - `getLeaderboard($studentClassId, $limit)`: Papan peringkat positif sahabat kelas dengan medali prestasi (🥇, 🥈, 🥉).
  - `isEnabled()`: Pengecekan status operasional gamifikasi via `SystemSetting`.
- **Integrasi Event Pembelajaran Nyata**:
  - Siswa mengumpulkan tugas pembelajaran di LMS (`Student\LmsController::submitAssignment`) -> Memperoleh 50 Poin Prestasi & membuka otomatis lencana "Langkah Pertama" atau "Santri Tekun".
  - Guru memberikan nilai tugas >= 90 (`Teacher\LmsController::gradeSubmission`) -> Memperoleh 50 Poin Prestasi & membuka otomatis lencana "Kolektor Nilai A".
  - Siswa menyelesaikan kuis/ujian CBT (`Student\CbtController::submitExam`) -> Memperoleh 60 Poin Prestasi & membuka otomatis lencana "Pendekar CBT" (+ bonus 40 poin jika skor >= 90).
- **Tampilan Antarmuka Edukatif**:
  - **Portal Siswa (`resources/js/Pages/Student/LMS.vue`)**:
    - Kartu banner Level & Level Progress Bar (`ProgressBar.vue`) tanpa raw colors/inline style.
    - Tab "Materi & Tugas", "Lencana Saya", dan "Peringkat Kelas" berbasis `Tabs.vue`.
  - **Portal Orang Tua (`resources/js/Pages/Parent/Partials/AkademikTab.vue`)**:
    - Kartu apresiasi capaian level dan lencana ananda pada tab prestasi.
  - **Pengaturan Super Admin (`resources/js/Pages/System/Settings.vue` & `SystemSettingController`)**:
    - Toggle on/off modul gamifikasi sekolah (`gamification_enabled = 1 | 0`).
- **Pengujian & Validasi**:
  - `tests/Feature/Sprint5BGamificationTest.php`: 8 skenario pengujian komprehensif, 65 assertions, 100% lulus.

---

## [Sprint 5A] - PWA & Portal Siswa Lengkap, Pengaturan Notifikasi Ortu, & Dokumen PDF Terproteksi

### Ditambahkan
- **Progressive Web App (PWA) Mandiri**:
  - Konfigurasi `VitePWA` (`vite-plugin-pwa`) dengan `registerType: 'autoUpdate'`.
  - Web App Manifest: Nama "SIMSE SD Muhammadiyah Al Mujahidin", short_name "SIMSE SDAM", tema teal Islami `#0f766e`, background `#ffffff`, display `standalone`.
  - Ikon aplikasi PWA resolusi tinggi (`icon-192x192.png`, `icon-512x512.png`).
  - Strategi Cache Workbox: Caching aset statis (`js`, `css`, `html`, `ico`, `png`, `svg`, `woff2`) dengan fallback rute ramah anak `/offline` (`resources/js/Pages/Offline.vue`).
  - **Keamanan Data**: Denylist eksplisit pada service worker untuk semua rute terotentikasi (`/api`, `/siswa`, `/guru`, `/ortu`, `/admin`, `/bendahara`, `/kantin`, `/staf`) guna mencegah kebocoran data siswa pada perangkat bersama.
  - Komponen & Tombol Pasang Aplikasi (*install prompt*) di halaman profil (`resources/js/Pages/Profile/Edit.vue` & `usePwaInstall.js`).
- **Portal Siswa Lengkap (`resources/js/Pages/Student/`)**:
  - **Halaman Keuangan Siswa (`Student/Keuangan.vue` & `KeuanganController`)**:
    - Saldo dompet digital aktif, limit harian, status RFID, mutasi transaksi 50 terakhir.
    - Status tagihan SPP dan iuran sekolah (hanya-baca).
    - **Proteksi Ketat**: PIN dompet digital **tidak pernah** dikirimkan ke props Inertia atau dimuat di UI siswa.
  - **Halaman Prestasi & Pembinaan Siswa (`Student/Prestasi.vue` & `PrestasiController`)**:
    - Rekapitulasi prestasi dan piagam penghargaan yang telah disetujui/diverifikasi.
    - Catatan pembinaan motivasi dan bimbingan positif dari guru BK.
    - **Ramah Anak SD**: Poin pelanggaran, catatan sanksi/SP, dan rincian kasus disiplin disaring total (hanya bisa diakses guru dan orang tua).
    - Fitur "Hubungi Guru BK": Modal formulir permintaan konseling terarah (`counseling_requests`), bukan chat terbuka yang rawan distraksi.
- **Peningkatan Portal Orang Tua (`resources/js/Pages/Parent/`)**:
  - Modal Preferensi Notifikasi (`Parent/Partials/NotificationPreferencesModal.vue` & `NotificationPreferenceController`): Opsi saluran WhatsApp dan In-App untuk presensi, keuangan/SPP, nilai akademik, dan kedisiplinan.
  - Akses Unduh Dokumen PDF Resmi ber-Kop Surat dua logo dan verifikasi QR:
    - E-Raport Resmi Siswa (`ortu.raport.pdf` via `Parent\DocumentController`).
    - Kuitansi / Bukti Bayar Kasir (`ortu.pembayaran.pdf` via `Parent\DocumentController`).
    - Otorisasi ketat: Orang tua dilarang mengakses dokumen siswa lain (HTTP 403).
- **Pengujian & Validasi**:
  - `tests/Feature/Sprint5AStudentPortalAndPwaTest.php`: 11 skenario pengujian komprehensif, 89 assertions, 100% lulus.

---

## [Sprint 4] - CBT Lanjutan: 6 Tipe Soal, Engine Penilaian Fleksibel, Pengawasan Realtime & Analisis Butir Soal

### Ditambahkan
- **Ekspansi 6 Tipe Butir Soal CBT (`app/Services/Exam/` & `resources/js/Pages/Student/Partials/Questions/`)**:
  - `Pilihan Ganda` (Single Choice): Pilihan tunggal A-D dengan dukungan lampiran gambar soal/opsi.
  - `Ganda Kompleks` (Multiple Choice / Multi-Select): Opsi jamak dengan dukungan aturan penilaian standar (exact match) atau penilaian parsial proporsional disertai penalti jawaban keliru (skor minimum 0).
  - `Benar Salah` (True / False): Penilaian pernyataan validitas materi pembelajaran.
  - `Menjodohkan` (Matching): Premis sebelah kiri dipasangkan dengan opsi target kanan; penilaian proporsional per pasangan yang cocok.
  - `Isian Rumpang` (Fill in the Blank): Input teks dengan normalisasi cerdas (case-insensitive, strip tanda baca, abaikan spasi berlebih, dan konversi koma desimal Indonesia `2,5 == 2.5`).
  - `Esai` (Essay / Free Text): Jawaban teks bebas dengan antrean penilaian manual oleh guru dan pembobotan skor custom.
- **Engine Penilaian Terpisah (`App\Services\Exam\ExamGrader`)**:
  - Pola strategi (*Strategy Pattern*) melalui `QuestionGraderInterface` dan implementasi grader per tipe soal: `GradesPilihanGanda`, `GradesGandaKompleks`, `GradesBenarSalah`, `GradesMenjodohkan`, `GradesIsianRumpang`, dan `GradesEsai`.
  - Sinkronisasi otomatis ke model `Grade` (`type = 'Kuis'`) begitu seluruh soal otomatis atau esai dinilai.
- **Ketahanan Ujian Siswa (`resources/js/Pages/Student/CBTExam.vue`)**:
  - Autosave debounced via endpoint API hemat bandwidth `POST /siswa/cbt/attempts/{attempt}/answer`.
  - Antrean offline (*offline queue*) dengan sinkronisasi otomatis saat koneksi pulih (*auto-sync on reconnect*).
  - Sinkronisasi sisa waktu berbasis jam server (`server_now`) guna mencegah manipulasi jam lokal pada perangkat siswa.
  - Mode ramah anak (`childMode`) dengan ukuran tombol/opsi lebih besar dan layout ramah sentuhan.
- **Pengawasan Realtime Guru (`resources/js/Pages/Teacher/CBT/Monitor.vue` & `CbtMonitorController`)**:
  - Polling realtime 5 detik via composable `usePolling` (arsitektur siap migrasi ke Laravel Reverb).
  - Tanpa N+1 queries dengan eager loading relasi siswa dan hitungan pelanggaran.
  - Aksi pengawas: Tambah waktu ujian per individu (+5, +10, +15 menit), reset pelanggaran anti-cheat dengan audit logging resmi, dan paksa kumpul (*force submit*).
- **Ujian Remedial Terarah (`exam_student_targets`)**:
  - Paket ujian remedial yang hanya dapat diakses oleh siswa sasaran tertentu (`StudentExamTarget`), dilengkapi proteksi akses HTTP 403 bagi siswa di luar target.
- **Laporan, Analisis Butir Soal & Ekspor (`resources/js/Pages/Teacher/CBT/Report.vue`)**:
  - Analisis butir soal: Tingkat kesukaran (Mudah, Sedang, Sukar) dan rasio distraktor.
  - Ekspor multi-sheet Excel via Maatwebsite Excel (`ExamStudentsScoresSheet` & `ExamItemAnalysisSheet`).
  - Unduh PDF Resmi Berita Acara & Rekapitulasi CBT dengan 2-logo Kop Surat (Majelis Dikdasmen & SD Muhammadiyah Al Mujahidin) serta QR code verifikasi.
- **Pengujian & Tolok Ukur Beban**:
  - `tests/Unit/ExamGraderTest.php` (7 unit tests, 26 assertions).
  - `tests/Feature/CbtAdvancedWorkflowTest.php` (7 feature tests, 38 assertions).
  - `tests/Performance/CbtLoadBenchmarkTest.php`: Simulasi 175 siswa submit ujian serentak dengan **P95 Latency 8.43 ms** (jauh melampaui standar target < 500 ms).

---

## [Sprint 2D] - Migrasi Admin, Auth, Welcome, PPDB, Profil, System & Pembersihan Layout Tunggal

### Diubah / Dimigrasikan
- **Portal Autentikasi (`resources/js/Pages/Auth`)**:
  - `Auth/Login.vue`: Dimigrasikan ke `PortalShell variant="bare"`, dipecah menjadi dua komponen modular: `Partials/RoleHeroPanel.vue` (branding islami Al-Mujahidin, kutipan nilai, dan pemilih role demo 1-klik) dan `Partials/LoginCard.vue` (form login kredensial, show/hide password via `AppButton`, penanganan error ramah).
  - `Auth/Register.vue`, `Auth/ForgotPassword.vue`, `Auth/ResetPassword.vue`, `Auth/ConfirmPassword.vue`, `Auth/VerifyEmail.vue`: Dimigrasikan penuh ke `PortalShell variant="bare"` dengan token semantik, `AppCard`, `AppInput`, `AppButton`, dan pembersihan seluruh styling bawaan Breeze.
- **Halaman Beranda Publik (`resources/js/Pages/Welcome`)**:
  - `Welcome.vue`: Dimigrasikan dari monolit legacy menjadi 9 komponen modular terpisah di `resources/js/Pages/Welcome/` (< 250 baris tiap komponen): `WelcomeNavbar.vue`, `HeroSection.vue`, `AboutSection.vue`, `ProgramsSection.vue`, `FacilitiesSection.vue`, `NewsSection.vue`, `InstagramSection.vue`, `CtaBannerSection.vue`, dan `WelcomeFooter.vue`. Menggunakan token semantik murni dan tema terpasang.
- **Pendaftaran Siswa Baru (`resources/js/Pages/Ppdb`)**:
  - `Ppdb/Register.vue`: Formulir pendaftaran calon peserta didik baru multi-langkah berbasis `Stepper.vue` (4 langkah: Data Calon Siswa, Data Orang Tua/Wali, Berkas Persyaratan, Ringkasan & Kirim) dengan fitur autosave otomatis ke `localStorage` via `useAutosave`.
  - `Ppdb/Success.vue`: Halaman konfirmasi pendaftaran berhasil dengan kode pendaftaran resmi, instruksi verifikasi, dan tombol cetak bukti formulir.
- **Profil Pengguna (`resources/js/Pages/Profile`)**:
  - `Profile/Edit.vue`: Dimigrasikan ke `PortalShell variant="sidebar"`, dipecah menjadi `Partials/UpdateProfileInformationForm.vue`, `Partials/UpdatePasswordForm.vue`, dan `Partials/DeleteUserForm.vue` menggunakan modal konfirmasi `useConfirm` dan `AppCard`.
- **Portal Admin & Master Data (`resources/js/Pages/Admin`)**:
  - `Admin/Dashboard.vue`: Metrik ringkasan statistik sekolah, status modul sistem, dan riwayat aktivitas terkini.
  - `Admin/Students/Index.vue`: Master data peserta didik dengan `DataTable`, sinkronisasi filter query string (pencarian, tingkat, rombel), shortcut pencarian keyboard Ctrl+K, unduh template Excel, ekspor data, dan impor data via `FileInput`.
  - `Admin/Students/Edit.vue`: Dipecah dari form panjang menjadi 4 tab parsial di `Admin/Students/Partials/`: `PersonalDataTab.vue`, `ParentAddressTab.vue`, `AchievementsTab.vue`, dan `PeriodicDataTab.vue` (< 250 baris per file).
  - `Admin/Teachers/Index.vue`: Master data guru dan tenaga pendidik dengan `DataTable`, shortcut Ctrl+K, ekspor data, dan impor via `FileInput`.
  - `Admin/Staffs/Index.vue`: Master data tenaga kependidikan dan staf operasional dengan `DataTable`, shortcut Ctrl+K, ekspor data, dan impor via `FileInput`.
  - `Admin/StudentClass/Index.vue`, `Admin/Tingkat/Index.vue`, `Admin/Subject/Index.vue`, `Admin/TeachingAssignment/Index.vue`, `Admin/Schedule/Index.vue`, `Admin/Ppdb/Index.vue`, `Admin/Modules.vue`, `Admin/Settings.vue`: Seluruh modul manajemen akademik dan konfigurasi admin dimigrasikan ke `PortalShell`, `DataTable`, dan komponen form semantik.
- **Sistem, Kiosk & Utilitas**:
  - `System/Cms.vue`, `System/KioskDevices.vue`, `System/Settings.vue`, `System/WaGateway.vue`: Seluruh modul konfigurasi teknis dan integrasi perangkat kiosk/WhatsApp dimigrasikan ke Design System.
  - `Kiosk/Presensi.vue`, `Kiosk/Locked.vue`: Halaman mesin anjungan presensi mandiri sekolah berbasis layar sentuh dan mode terkunci darurat.
  - `Notifications/Index.vue`, `Achievements/Verification.vue`, `Dashboard.vue`: Pusat notifikasi terpadu dan verifikasi prestasi siswa.
- **Pembersihan Layout Total & Pagar Konsistensi**:
  - **9 Layout Lama Dihapus**: Seluruh berkas layout monolitik terdahulu (`AdminLayout.vue`, `BendaharaLayout.vue`, `CanteenLayout.vue`, `StaffLayout.vue`, `TeacherLayout.vue`, `AuthenticatedLayout.vue`, `GuestLayout.vue`, `ParentLayout.vue`, `StudentLayout.vue`) resmi dihapus permanen. Proyek kini murni berjalan di atas satu shell: `PortalShell.vue`.
  - `SPRINT2_MIGRATION_ALLOWLIST` di `scripts/check-ui-consistency.mjs` resmi dikosongkan (`new Set([])`).
  - 114/114 file halaman dan parsial dalam `resources/js/Pages/` lulus verifikasi `node scripts/check-ui-consistency.mjs` (0 pelanggaran).
  - Seluruh pengujian: `npm run check:contrast` (WCAG AA), `npm run build` (931 modules Vite), `vendor/bin/pint --test`, dan `php artisan test --compact` (107/107 tests) lulus 100%.

---

## [Sprint 2C] - Migrasi Portal Bendahara, Kantin, & Staf ke Design System

### Diubah / Dimigrasikan
- **Portal Bendahara (`resources/js/Pages/Bendahara`)**:
  - `Bendahara/Dashboard.vue`: Dimigrasikan ke `PortalShell` (varian `sidebar`), 4 kartu statistik kas (`StatCard`), aksi cepat modul keuangan, dan tabel transaksi kas terbaru via `DataTable` berslot status & `MoneyText`.
  - `Bendahara/SPP.vue`: Dimigrasikan dari 488 baris menjadi 290 baris dengan pemisahan modal ke `Partials/BillTypeModal.vue` dan `Partials/GenerateBillModal.vue`. Mendukung penuh periode Bulanan, Semesteran, Tahunan, dan Sekali Bayar. Dilengkapi `DataTable` pos tagihan, `DataTable` tagihan siswa, filter status/periode, dan laporan tunggakan dengan pengingat WA massal.
  - `Bendahara/Kasir.vue`: Dimigrasikan dari 454 baris ke 380 baris dengan pemisahan pratinjau live thermal kuitansi ke `Partials/ReceiptPreview.vue`. Input pencarian siswa selalu fokus, input nominal berfont numerik (`font-numeric`) dengan konversi terbilang otomatis, pemilihan metode bayar tunai/transfer/dompet via `AppRadio`, `FileInput` bukti bayar privat, dan `DataTable` verifikasi transfer & riwayat pembayaran.
  - `Bendahara/Dompet.vue`: Dimigrasikan dari 310 baris ke 270 baris dengan pemisahan modal ke `Partials/WalletTransactionModal.vue`, `Partials/WalletPinModal.vue`, dan `Partials/WalletRfidModal.vue` (menghapus total penggunaan `window.prompt`). Dilengkapi rekap saldo mengendap, mutasi dompet digital, dan manajemen kartu RFID siswa.
  - `Bendahara/Payroll.vue`: Dimigrasikan dari 360 baris ke 285 baris dengan pemisahan modal ke `Partials/GeneratePayrollModal.vue`, `Partials/PayrollComponentModal.vue`, dan `Partials/PayrollSlipViewModal.vue`. Manajemen draf payroll, pencairan gaji pegawai via `useConfirm`, dan slip honorarium ber-Kop Surat.
  - `Bendahara/KantinReport.vue`: Rekapitulasi omzet penjualan per stan e-Kantin, filter rentang tanggal, pembagian hasil, dan riwayat pesanan kantin via `DataTable`.
- **Portal Kantin (`resources/js/Pages/Canteen`)**:
  - `Canteen/Dashboard.vue`: Dashboard operasional stan kantin, toggle buka/tutup stan sementara via `useConfirm`, modal edit profil stan, KPI omzet harian, dan antrean pesanan masuk dengan tombol perubahan status KBM cepat (Masak, Siap Diambil, Selesai).
  - `Canteen/Menus.vue`: Form manajemen menu makanan/minuman dengan pengaturan stok harian, ketersediaan menu, dan daftar menu interaktif dengan konfirmasi hapus via `useConfirm`.
  - `Canteen/ScanQr.vue`: Validasi instan pengambilan pesanan siswa menggunakan pemindai kamera `QrcodeStream` atau input kode manual dengan feedback visual cepat.
  - `Canteen/RfidPos.vue`: Kasir ATM RFID Kantin untuk jam istirahat cepat tanpa konfirmasi berlapis, tap kartu siswa, keranjang belanja dengan kontrol tombol kuantitas besar, verifikasi PIN 6 digit, dan notifikasi transaksi audio-visual.
- **Portal Staf (`resources/js/Pages/Staff`)**:
  - `Staff/Dashboard.vue`: Ringkasan aktivitas harian staf, status presensi masuk/pulang GPS hari ini, log kehadiran bulanan, sirkulasi buku perpustakaan, dan slip gaji terakhir.
  - `Staff/Absensi.vue`: Presensi geolokasi GPS presisi dalam batas radius sekolah (150m), perhitungan jarak Haversine waktu nyata, tombol check-in/check-out satu ketukan, dan riwayat presensi 30 hari via `DataTable`.
  - `Staff/Perpustakaan.vue`: Sirkulasi buku perpustakaan nyata, pengembalian buku dengan pembaruan stok fisik otomatis via `useConfirm`, katalog buku dengan pencarian cepat, dan modal peminjaman buku ke siswa/guru/staf.
  - `Staff/SlipGaji.vue`: Transparansi honorarium staf, banner take-home pay, rincian komponen penerimaan vs potongan, lembar resmi ber-Kop Surat Majelis Dikdasmen SD Muhammadiyah Al Mujahidin Wonosari, dan tombol cetak slip.
- **Pagar Konsistensi & Pengujian**:
  - Seluruh 14 file kelompok 2C dihapus dari `SPRINT2_MIGRATION_ALLOWLIST` di `scripts/check-ui-consistency.mjs`.
  - 64 file halaman aktif lulus verifikasi konsistensi UI (`npm run lint:ui`) dengan 0 pelanggaran.
  - Pengujian rasio kontras WCAG AA, build Vite, Pint formatting, dan 107/107 feature tests semuanya hijau.

---

## [Sprint 2B] - Migrasi Portal Guru ke Design System

### Diubah / Dimigrasikan
- **Portal Guru (`resources/js/Pages/Teacher`)**:
  - `Teacher/Dashboard.vue`: Dimigrasikan penuh ke `PortalShell` (varian `sidebar`), daftar tindakan nyata "Yang Perlu Tindakan Guru" (tugas belum dikoreksi, presensi kelas belum diisi, ujian daring aktif) dengan tombol navigasi langsung, kartu jadwal mengajar hari ini, statistik kelas, dan aktivitas terkini.
  - `Teacher/Absensi.vue`: Presensi satu ketukan per siswa (`Hadir`, `Izin`, `Sakit`, `Alpa`), tombol aksi cepat "Semua Hadir", autosave otomatis via `useAutosave` dengan indikator status tersimpan pada shell, konfirmasi perubahan presensi lewat `useConfirm`.
  - `Teacher/Nilai.vue`: Terintegrasi dengan komponen domain `GradeTable.vue`, navigasi keyboard penuh (Tab, Enter, Panah), penandaan otomatis nilai di bawah KKM (75) menggunakan token status bahaya, simpan otomatis per sel nilai, filter kelas/mapel/semester, dan ekspor data nilai.
  - `Teacher/Raport.vue`: Rekap capaian nilai raport per siswa menggunakan `ScoreChip`, filter semester/kelas, modal cetak raport resmi ber-Kop Surat SD Muhammadiyah Al Mujahidin, dan standarisasi istilah ke "siswa".
  - `Teacher/Jurnal.vue`: Linimasa pencatatan jurnal KBM harian, modal pembuatan/pembaruan jurnal via subkomponen `Partials/JournalModal.vue` (< 140 baris), filter tanggal & kelas, konfirmasi hapus via `useConfirm`.
  - `Teacher/Jadwal.vue`: Tampilan jadwal mengajar mingguan berbasis `Tabs` (Senin s.d. Sabtu), kartu ringkasan jadwal per hari, dan penanda waktu pelajaran.
  - `Teacher/LMS.vue`: Manajemen modul & materi ajar digital, unggah materi dengan `FileInput`, rute berkas terproteksi, modal tambah materi baru, dan manajemen penghapusan dengan `useConfirm`.
  - `Teacher/Tugas.vue`: Manajemen tugas dan pekerjaan rumah, modal pembuatan tugas baru, modal koreksi penyerahan tugas siswa dilengkapi form input nilai dan umpan balik personal.
  - `Teacher/CBT.vue`: Manajemen ujian berbasis komputer, toggle status aktif/arsip ujian, dipecah menjadi subkomponen `Partials/CreateExamModal.vue` dan `Partials/AddQuestionModal.vue` (mendukung pilihan ganda A-D, benar/salah, dan esai dengan `AppRadio`).
  - `Teacher/BK.vue`: Portal Bimbingan Konseling guru, pencatatan pelanggaran disiplin melalui `Partials/ReportViolationModal.vue`, pencatatan sesi konseling melalui `Partials/CounselingModal.vue`, dan modal cetak Berita Acara Pembinaan resmi dengan `KopSurat`.
  - `Teacher/Perpustakaan.vue`: Katalog e-Pustaka dan buku fisik, statistik ketersediaan pustaka, filter kategori buku, pencarian cepat, modal baca e-Book ramah siswa.
  - `Teacher/Payroll.vue`: Slip gaji dan transparansi honorarium pendidik, kartu rincian pendapatan vs potongan dengan `MoneyText`, tabel riwayat penerimaan honor via `DataTable`, modal cetak resmi ber-Kop Surat dan QR otentikasi.
- **Komponen Domain & Primitif Baru**:
  - `resources/js/Components/domain/GradeTable.vue`: Komponen tabel nilai interaktif dengan navigasi keyboard dan penandaan KKM (< 75).
  - `resources/js/Components/ui/AppRadio.vue`: Komponen radio button semantik yang ramah aksesibilitas dan sesuai token desain.
- **Pagar Konsistensi & Pengujian**:
  - Seluruh 12 file Portal Guru dihapus dari `SPRINT2_MIGRATION_ALLOWLIST` di `scripts/check-ui-consistency.mjs`.
  - 41 file halaman aktif lulus verifikasi konsistensi UI (`npm run lint:ui`).
  - Stabilisasi pengujian waktu deterministik pada `Sprint5StaffAndAttendanceTest.php`.

---

## [Sprint 2A] - Migrasi Portal Orang Tua & Siswa ke Design System

### Diubah / Dimigrasikan
- **Portal Orang Tua (`resources/js/Pages/Parent`)**:
  - `Parent/Dashboard.vue`: Dimigrasikan penuh ke `PortalShell` (varian `bottom-nav`), dipecah dari 706 baris menjadi 150 baris dengan 6 komponen modular di `resources/js/Pages/Parent/Partials/`:
    - `ChildSwitcher.vue`: Pengalih anak aktif yang selalu terlihat di mobile & desktop dengan `Avatar` dan `AppSelect`.
    - `HariIniSection.vue`: Bagian teratas "Hari Ini" untuk ananda aktif (status presensi hari ini, kartu tagihan SPP menonjol, status pembinaan SP, dan rata-rata nilai).
    - `AkademikTab.vue`: e-Raport capaian (`DataTable`), rincian nilai harian, jadwal kelas, jurnal materi guru, serta prestasi siswa.
    - `PresensiTab.vue`: Kartu statistik kehadiran (`StatCard`) dan riwayat log kehadiran.
    - `KeuanganTab.vue`: Saldo dompet digital, form pengaturan batas jajan kantin (`useForm`), daftar tagihan SPP, riwayat bayar kasir, dan tombol bayar menonjol.
    - `PerilakuTab.vue`: Poin kedisiplinan, status SP, catatan pelanggaran, dan sesi Bimbingan Konseling (BK).
    - `PrestasiModal.vue`: Modal pengajuan prestasi anak mandiri oleh orang tua dengan `FileInput`.
    - `PayInfoModal.vue`: Modal panduan rekening transfer resmi sekolah (BSI, BPD DIY Syariah).
- **Portal Siswa (`resources/js/Pages/Student`)**:
  - `Student/Dashboard.vue`: Sapaan nama islami, kartu prioritas "Pelajaran Berikutnya" dan "Tugas Terdekat", quick stats, jadwal hari ini, materi jurnal guru, dan ujian CBT aktif. Dilengkapi mode ramah anak (`isKidsMode`) otomatis untuk kelas 1-3.
  - `Student/Jadwal.vue`: Tampilan kartu jadwal per hari (Senin-Sabtu) dengan penanda hari aktif "Hari Ini" dan `EmptyState`.
  - `Student/Nilai.vue`: Rekap capaian nilai e-Raport menggunakan `DataTable` dan `ScoreChip`, rincian nilai tugas, tombol cetak lembar nilai dengan `KopSurat` resmi.
  - `Student/Presensi.vue`: 6 ringkasan kehadiran (`StatCard`) dan riwayat presensi masuk/pulang (`StatusBadge`).
  - `Student/MateriMingguan.vue`: Filter mingguan dan filter mata pelajaran terintegrasi (`AppSelect`), ringkasan jurnal guru per minggu.
  - `Student/LMS.vue`: Gamifikasi progres siswa (Level, Title, XP, lencana/badges), filter mata pelajaran (`AppSelect`), daftar materi dengan lampiran berkas terproteksi, dan daftar tugas.
  - `Student/Partials/AssignmentSubmitModal.vue`: Modal pengerjaan tugas siswa dengan input teks dan `FileInput`.
  - `Student/CBT.vue`: Daftar ujian online aktif/terjadwal, durasi, jumlah soal, kartu tata tertib dan pengawasan anti-kecurangan.
  - `Student/CBTExam.vue`: Dimigrasikan ke shell `PortalShell variant="bare"` (fokus ujian bebas distraksi navigasi), timer hitung mundur font-mono, auto-save debounce jawaban siswa, pengatur ukuran huruf (A-, A, A+), pengawasan anti-cheat (blokir klik kanan, deteksi tab switch, layar penuh, forced submit bila melebihi batas). Dipecah menjadi subkomponen `ExamTimerBar.vue` dan `ExamFinishedCard.vue`.
  - `Student/Kantin.vue`: Tampilan e-Kantin terintegrasi, selektor stan kantin, kartu menu makanan/minuman, modal periksa saldo dompet dengan PIN, dan riwayat pesanan.
  - `Student/Partials/KantinCartDrawer.vue`: Drawer keranjang belanja kantin dengan kontrol kuantitas dan verifikasi PIN dompet siswa.
  - `Student/KTS.vue`: Kartu Tanda Siswa digital resmi dengan QR code (`QrDisplay`), data NISN/NIK, dan tombol cetak fisik.
- **Komponen Primitif UI Baru**:
  - `resources/js/Components/ui/FileInput.vue`: Komponen unggah berkas yang konsisten dengan token desain.
- **Komponen Domain Bisnis Baru**:
  - `resources/js/Components/domain/ExamOptionButton.vue`: Tombol pilihan ganda CBT dengan highlight state dan responsivitas font dinamis.
- **Pagar Konsistensi**:
  - 11 file yang dimigrasikan dihapus dari `SPRINT2_MIGRATION_ALLOWLIST` di `scripts/check-ui-consistency.mjs`.
  - 24 file baru/tervalidasi lolos `npm run lint:ui` dengan 0 pelanggaran.

---

## [Sprint 1] - Design System (Token, Komponen, & Shell Tunggal)

### Ditambahkan
- **Arsitektur Token & Multi-Tema**:
  - `resources/css/tokens.css`: Definisi CSS variables untuk tema `madinah` (hijau-teal + emas khas Al-Mujahidin), `madinah-gelap`, dan tema uji `uji-gelap`.
  - `tailwind.config.js`: Pemetaan token semantik (`surface`, `ink`, `line`, `brand-50..900`, `accent-50..900`, `status-*`, `radius-card`, `radius-control`, `shadow-card`, dll).
  - `config/theme.php`: Konfigurasi tema bawaan per role pengguna.
  - `app/Http/Middleware/HandleInertiaRequests.php`: Injeksi shared props `theme` dan `flash` (success, error, warning).
  - `resources/views/app.blade.php`: Penerapan atribut `data-theme` pada tag `<html>` sebelum render untuk mencegah efek flicker.
  - `scripts/check-contrast.mjs`: Script pengujian otomatis rasio kontras warna WCAG AA (semua rasio > 5.4:1).
- **28 Komponen Primitif UI (`resources/js/Components/ui/`)**:
  - `AppButton`, `AppInput`, `AppSelect`, `AppTextarea`, `AppCheckbox`, `AppSwitch`, `FormField`, `CurrencyInput`, `SearchInput`.
  - `AppCard`, `StatCard`, `PageHeader`, `Tabs`, `Stepper`.
  - `DataTable` (server-side sort & pagination, otomatis responsif menjadi format kartu di bawah 640px).
  - `FilterBar`, `StatusBadge` (pemetaan otomatis enum bisnis ke varian semantik).
  - `EmptyState`, `Skeleton` (block, row, table, circle).
  - `Modal`, `Drawer`, `ConfirmDialog`, `Toast`, `ToastHost`.
  - `Avatar`, `ProgressBar`, `ProgressRing`, `Tooltip`, `Dropdown`.
- **11 Komponen Domain Bisnis (`resources/js/Components/domain/`)**:
  - `StudentPicker`, `ClassSelect`, `SubjectSelect`, `AcademicYearBadge`, `AttendanceStatusToggle`.
  - `MoneyText`, `DateText`, `ScoreChip`, `KopSurat` (Kop resmi Majelis Dikdasmen PCM Wonosari), `QrDisplay`, `PrintSheet` (pembungkus A4), `NotificationBell`.
- **Shell Tunggal (`resources/js/Layouts/PortalShell.vue`)**:
  - Mendukung 3 varian: `sidebar` (admin, guru, bendahara, staf, kantin), `bottom-nav` (siswa, ortu; mobile-first), dan `bare` (kiosk, CBT, cetak).
  - `resources/js/config/navigation.js`: Struktur navigasi terpusat per peran pengguna dengan penyembunyian modul non-aktif otomatis.
  - Aksesibilitas: tautan *skip to content*, lonceng notifikasi, menu profil akun, toggle tema, indikator autosave, dan host dialog/toast.
- **7 Composables Standar (`resources/js/composables/`)**:
  - `useToast`, `useConfirm`, `useFormat` (id-ID, Rp, Asia/Jakarta), `useDebouncedSearch`, `usePolling` (pause saat tab tersembunyi), `useAutosave`, `useTheme`.
- **Living Style Guide (`/dev/design-system`)**:
  - Rute dan controller `App\Http\Controllers\Dev\DesignSystemController` (dilindungi 404 pada production).
  - Halaman `resources/js/Pages/Dev/DesignSystem.vue` (< 300 baris) menampilkan katalog seluruh komponen, state, tema, dan lembar A4.
  - Feature test `tests/Feature/DesignSystemTest.php`.
- **Pagar Konsistensi Otomatis**:
  - `scripts/check-ui-consistency.mjs`: Script linter untuk memastikan tidak ada warna mentah, inline style, tag HTML mentah, alert/confirm, atau layout lama pada halaman baru (dengan allowlist untuk halaman legacy sebelum migrasi di Sprint 2).
  - Perintah npm baru: `npm run lint:ui` dan `npm run check:contrast`.
  - Pipeline GitHub Actions diperbarui dengan validasi kontras dan UI consistency.

---

## [Sprint 0] - Stabilisasi Fondasi

### Ditambahkan
- **Keamanan & Otorisasi**:
  - Kebijakan otorisasi baru: `PayrollPolicy`, `WalletPolicy`, `WalletTransactionPolicy`, `BillPolicy`, `PaymentPolicy`, `BookLoanPolicy`.
  - Pembaruan otorisasi penilaian tugas guru pada `SubmissionPolicy`.
  - Pagar keamanan akses lintas pengguna (IDOR prevention): `tests/Feature/Security/CrossUserAccessTest.php` yang memverifikasi 18 rute sensitif.
- **Strict Mode Eloquent**:
  - Pagar N+1 query dan lazy loading: `Model::preventLazyLoading(! app()->isProduction())` dan `Model::shouldBeStrict(! app()->isProduction())` pada `AppServiceProvider`.
- **Integrasi CI**:
  - Workflow GitHub Actions pada `.github/workflows/ci.yml` mencakup pemeriksaan Pint, PHPUnit (SQLite in-memory), dan build Vite frontend.
- **Dokumentasi Rekayasa**:
  - `docs/CONVENTIONS.md` (arsitektur backend/frontend, definisi selesai, keputusan dependensi).
  - Pembaharuan `README.md` dengan instruksi Laragon, migrasi database, seeding, queue worker, dan schedule worker.

### Diubah
- **Pemekaran & Refaktor Controller Besar**:
  - `StudentPortalController` dipecah menjadi arsitektur domain tipis di `app/Http/Controllers/Student/`.
  - `TeacherController` dipecah ke `app/Http/Controllers/Teacher/`.
  - `BendaharaController` dipecah ke `app/Http/Controllers/Bendahara/`.
  - `CanteenController` dipecah ke `app/Http/Controllers/Canteen/`.
  - Semua controller utama bertindak sebagai facade/delegator tipis (< 170 baris, tidak ada controller > 400 baris) dengan nama rute 100% kompatibel.
- **Konteks Kurikulum SD**:
  - Seeder kelas disesuaikan ke konteks SD Muhammadiyah Al Mujahidin (Kelas 1A-6B, Tingkat 1-6).
  - Seeder mata pelajaran disesuaikan: PAI, Al-Islam & Kemuhammadiyahan, Bahasa Arab, IPAS, Bahasa Inggris, PJOK, Seni Budaya, Tahfidz.
  - Penambahan kolom `nis` pada `students` dan `position` pada `teachers` melalui migrasi baru.
- **Konsolidasi Styling**:
  - Penguncian Tailwind CSS ke v3 dan pencabutan modul `@tailwindcss/vite` v4 yang tidak kompatibel.

### Dihapus
- Seluruh artefak mockup dan placeholder yang tidak digunakan (`Pages/Placeholder.vue`, CBT, Kantin, dll).
