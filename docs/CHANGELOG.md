# Catatan Perubahan (Changelog) SIMSE

Semua perubahan penting pada proyek SIMSE didokumentasikan di berkas ini. Format mengikuti standar [Keep a Changelog](https://keepachangelog.com/).

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
