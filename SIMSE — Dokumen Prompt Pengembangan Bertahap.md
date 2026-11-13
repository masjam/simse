# SIMSE — Dokumen Prompt Pengembangan Bertahap

Nov 13, 2026 · @dd

## Cara memakai dokumen ini

Dokumen ini berisi 1 Master Prompt dan 7 prompt sprint; tempel Master Prompt bersama prompt sprint yang sedang dikerjakan, satu sprint per sesi, dan jangan lanjut sebelum gerbang lulusnya terpenuhi.

Kunci agar hasilnya tidak terfragmentasi ada di dua tempat: Sprint 0 membuat `docs/CONVENTIONS.md` (satu sumber kebenaran arsitektur), dan Sprint 1 membuat design system berbasis token. Setiap sprint sesudahnya wajib membaca keduanya dan dilarang membuat pola baru.

| Sprint | Fokus | Ukuran | Gerbang lulus |
| --- | --- | --- | --- |
| 0 | Stabilisasi: hapus mock, CI, Tailwind, uji akses lintas pengguna, pecah controller besar | M | Test hijau, CI hijau, nol `Placeholder` |
| 1 | Design system: token, komponen bersama, satu PortalShell | L | Halaman style guide lengkap, skrip cek konsistensi lulus |
| 2 | Migrasi semua portal ke design system | L | Nol pemakaian layout lama |
| 3 | PDF resmi, audit log, soft delete, backup | M | Semua dokumen resmi berupa PDF, restore backup teruji |
| 4 | CBT lanjutan: 3 tipe soal, monitor guru | L | Unit test penilai untuk 6 tipe soal |
| 5 | Gamifikasi, PWA, kasbon, perpustakaan, BK dan keuangan siswa | L | Fitur berjalan di HP 360px |
| 6 | Keamanan, performa, Pulse, UAT | M | Checklist UAT per role ditandatangani |
| 7 (opsional) | Paket tema non-reguler | S | Ganti tema hanya lewat satu atribut |

Sebelum tiap sesi, siapkan tiga hal:

- Branch baru dari `main` (`sprint-N-nama`) dan database lokal yang bisa di-reset.
- Hasil sprint sebelumnya sudah di-commit, supaya agen bisa membandingkan.
- Jawaban atas pertanyaan di akhir tiap prompt (istilah "siswa" atau "santri", provider WhatsApp, realtime atau polling).

Sesudah tiap sesi, lakukan tinjau cepat: jalankan `php artisan test`, `vendor/bin/pint --test`, `npm run build`, lalu buka 3 halaman acak di lebar 360px. Minta agen menempel ringkasan perubahan ke `docs/CHANGELOG.md`.

## Master Prompt (tempel di awal setiap sesi)

Prompt ini mengunci arsitektur tunggal; semua prompt sprint di bawahnya diasumsikan sudah didahului blok ini.

```markdown
# PERAN
Anda adalah engineer senior yang melanjutkan SIMSE (Sistem Informasi Manajemen Sekolah) untuk SD Muhammadiyah Al Mujahidin Wonosari. Kerjakan seperti anggota tim, bukan tamu: ikuti pola yang sudah ada, jangan menciptakan pola baru.

# STACK (jangan diganti)
PHP 8.3, Laravel 13, Inertia 2, Vue 3 (Composition API, <script setup>), Tailwind CSS, Spatie Permission, Maatwebsite Excel, Ziggy, qrcode.vue, vue-qrcode-reader. MySQL (Laragon, Windows) untuk dev. PK UUID (HasUuids) di semua tabel. File privat di disk `local`, dilayani lewat route terproteksi.

# SEBELUM MENULIS KODE
1. Baca docs/CONVENTIONS.md, docs/CHANGELOG.md, routes/web.php, dan 2-3 file yang paling mirip dengan tugas Anda.
2. Tulis rencana singkat: daftar file yang akan dibuat/diubah dan asumsi. Jika ada pertanyaan yang mengubah desain, ajukan SEKALI dalam satu pesan, lalu lanjut dengan asumsi terbaik.
3. Kalau tugas bertabrakan dengan CONVENTIONS.md, ikuti CONVENTIONS.md dan laporkan benturannya.

# ARSITEKTUR TUNGGAL (WAJIB)
Backend
- Controller tipis: validasi lewat FormRequest, otorisasi lewat Policy/Gate, logika bisnis di app/Actions/<Domain>/<Kata kerja>.php atau app/Services/<Nama>Service.php. Controller > 300 baris = tanda harus dipecah.
- Satu controller per fitur, di folder per portal: app/Http/Controllers/<Portal>/<Fitur>Controller.php.
- Props Inertia dibentuk di Action/Service atau API Resource, bukan di Vue. Kirim hanya field yang dipakai halaman.
- Eager loading wajib; tidak boleh ada query di dalam loop. Transaksi uang/nilai wajib DB::transaction.
- Nama route: <portal>.<fitur>.<aksi> (contoh siswa.cbt.submit). Semua route punya middleware auth + role + module bila berlaku.
- Pesan validasi dan teks UI dalam Bahasa Indonesia. Istilah baku: "siswa" (bukan campuran siswa/santri).

Frontend
- Semua halaman memakai SATU shell: Layouts/PortalShell.vue. Dilarang membuat layout baru atau menyalin layout lama.
- Navigasi didefinisikan di resources/js/config/navigation.js per role; dilarang menulis menu di dalam komponen halaman.
- Halaman hanya merakit komponen dari Components/ui (primitif) dan Components/domain (komponen bermakna bisnis). Dilarang membuat tombol, kartu, tabel, modal, badge, atau input baru di dalam halaman; jika kurang, tambahkan atau perluas komponen bersama dulu.
- Warna, font, radius, bayangan hanya lewat token semantik Tailwind (bg-surface, text-ink, bg-brand-600, ring-focus, rounded-card). Dilarang hex, rgb(), style="", atau kelas warna mentah seperti bg-blue-600 di halaman.
- Feedback pengguna hanya lewat useToast() dan useConfirm(). Dilarang alert()/confirm().
- Format uang, tanggal, angka hanya lewat composable useFormat (id-ID, Rp, zona Asia/Jakarta).
- Setiap halaman wajib punya: header (PageHeader), keadaan memuat (Skeleton), keadaan kosong (EmptyState), keadaan galat, dan tampilan 360px yang bisa dipakai.
- Satu file Vue maksimal ~300 baris; di atas itu pecah menjadi komponen.

# KEAMANAN
Data siswa/orang tua/pegawai hanya boleh diakses pemiliknya atau role berwenang (Policy + test). Upload: validasi mime dan ukuran, nama acak, disk privat. Jangan log data sensitif (PIN, token, NIK).

# DEFINISI SELESAI (semua harus benar)
- Migration + model ($fillable, casts, relasi) + FormRequest + Policy + controller + route + halaman Vue + seeder demo + feature test (jalur sukses, ditolak otorisasi, validasi gagal).
- vendor/bin/pint --test, php artisan test, npm run build, dan node scripts/check-ui-consistency.mjs semuanya lulus (skrip terakhir ada sejak Sprint 1).
- docs/CHANGELOG.md diperbarui; docs/CONVENTIONS.md diperbarui bila ada keputusan baru.
- Jangan menyatakan selesai bila ada test gagal; jelaskan apa adanya.

# LARANGAN
Jangan menambah dependency tanpa alasan tertulis. Jangan mengubah .env atau konfigurasi Laragon permanen tanpa melapor. Jangan menghapus test agar hijau. Jangan melakukan refactor di luar lingkup sprint kecuali diminta.

# FORMAT LAPORAN AKHIR
(1) Ringkasan 3 kalimat. (2) File dibuat/diubah. (3) Asumsi yang diambil. (4) Yang belum selesai. (5) Cara uji manual per role.
```

## Sprint 0 — Stabilisasi

Tujuannya membersihkan sisa mock dan menyiapkan jaring pengaman (CI, test akses, dokumen konvensi) sebelum ada perubahan besar; tidak ada fitur baru di sprint ini.

```markdown
# SPRINT 0 — STABILISASI

Tujuan: repo bersih, terukur, dan punya pagar pengaman. TIDAK ADA fitur baru.

## Tugas
1. Hapus mock dan placeholder
   - routes/web.php: ganti semua route yang merender Inertia 'Placeholder' dan route learning.cbt / finansial.kantin (mock) menjadi redirect berbasis role lewat satu helper `App\Support\RoleHome::for($user, $modul)` (Siswa -> siswa.*, Orang Tua -> ortu.*, Guru -> guru.*, dst). Route lama tetap ada agar tautan lama tidak patah.
   - Hapus: CBTController (mock), KantinController (mock), Pages/CBT/Index.vue, Pages/Kantin/Index.vue, Pages/Placeholder.vue, Pages/Akademik/Absensi.vue + AkademikController bila tidak dipakai lagi, dan `Pages/Welcome copy.vue`. Cek dengan grep bahwa tidak ada referensi tersisa.
2. Ubah seed demo ke konteks SD
   - Kelas 1A-6B (tingkat 1-6), mapel: Matematika, Bahasa Indonesia, IPAS, Pendidikan Agama Islam, Al-Islam dan Kemuhammadiyahan, Bahasa Arab, Bahasa Inggris, PJOK, Seni Budaya, Tahfidz. Hilangkan istilah SMA/SMK ("Peminatan", "Rekayasa Perangkat Lunak"). Satu perintah `php artisan migrate:fresh --seed` harus menghasilkan data demo utuh untuk semua role.
3. Rapikan Tailwind
   - package.json memuat tailwindcss ^3 bersama @tailwindcss/vite ^4 sementara vite.config.js tidak memakai plugin tersebut dan app.css memakai sintaks v3. Pilih Tailwind v3 (risiko terendah), hapus @tailwindcss/vite, pastikan `npm run build` bersih. Catat keputusan di CONVENTIONS.md.
4. Pecah controller besar tanpa mengubah perilaku
   - StudentPortalController (>1000 baris), TeacherController, BendaharaController, dan yang >400 baris lain -> satu controller per fitur di folder per portal (contoh Siswa\LmsController, Siswa\CbtController, Guru\NilaiController). Pindahkan logika bisnis ke app/Actions atau app/Services. Nama route TIDAK BERUBAH. Test yang sudah ada harus tetap hijau tanpa diedit (itu jaring pengaman).
5. Uji akses lintas pengguna (IDOR)
   - Buat tests/Feature/Security/CrossUserAccessTest.php: untuk setiap route yang menerima ID model (siswa, submission, attempt, bill, payment, wallet, payroll, slip gaji, lampiran materi, bukti transfer, prestasi, struk) buktikan bahwa pengguna A tidak bisa membuka/mengubah milik pengguna B (harapan 403 atau 404).
   - Tambahkan Policy yang belum ada: Payroll, Wallet, WalletTransaction, Bill, Payment, BookLoan. Daftarkan semuanya di AppServiceProvider.
6. CI
   - .github/workflows/ci.yml: PHP 8.3, composer install, salin .env.example, key:generate, jalankan `vendor/bin/pint --test`, `php artisan test`, `npm ci`, `npm run build`. Gunakan SQLite in-memory jika semua migration kompatibel; jika tidak, service MySQL 8.
7. Dokumentasi
   - Buat docs/CONVENTIONS.md (ringkasan Master Prompt: lapisan backend, struktur folder, penamaan route, aturan frontend, definisi selesai, keputusan Tailwind), docs/CHANGELOG.md, dan perbarui README (cara setup Laragon, seed, menjalankan queue worker dan scheduler).
8. Matikan kebiasaan berisiko
   - Tambahkan `Model::preventLazyLoading(! app()->isProduction())` dan `Model::shouldBeStrict()` pada mode non-produksi; perbaiki N+1 yang muncul di test.

## Gerbang lulus
- `php artisan test`, pint, build, dan CI hijau.
- `grep -r "Placeholder" resources routes app` kosong.
- CrossUserAccessTest mencakup minimal 15 route dan lulus.
- Tidak ada controller > 400 baris.

## Tanyakan dulu (satu pesan) jika belum jelas
Istilah resmi UI: "siswa" atau "santri"? Apakah server produksi Windows (Laragon) atau Linux?
```

## Sprint 1 — Design System

Ini sprint yang mencegah fragmentasi: semua tampilan berasal dari token CSS dan satu set komponen, sehingga tema baru nanti cukup berupa satu file token, bukan penulisan ulang halaman.

```markdown
# SPRINT 1 — DESIGN SYSTEM (token + komponen + shell tunggal)

Tujuan: satu bahasa visual dan satu kerangka halaman untuk semua portal. Sprint ini membangun fondasinya; halaman lama dimigrasi di Sprint 2. Jangan mengubah logika backend.

## 1. Arsitektur token (inti anti-fragmentasi)
- Definisikan semua nilai desain sebagai CSS variable di resources/css/tokens.css, dibagi per tema lewat atribut `data-theme` pada <html>:
  --brand-50..900, --accent-50..900, --surface, --surface-raised, --surface-sunken, --ink, --ink-muted, --line, --focus,
  --success/--warning/--danger/--info (masing-masing -bg, -fg, -line),
  --radius-card, --radius-control, --shadow-card, --shadow-pop,
  --font-display, --font-body, --font-numeric, --density (skala jarak: 0.9 | 1 | 1.15).
- Hubungkan ke tailwind.config.js sebagai nama SEMANTIK (bg-surface, text-ink, text-ink-muted, border-line, bg-brand-600, ring-focus, rounded-card, rounded-control, shadow-card, font-display). Warna mentah Tailwind (bg-blue-600 dst) dinonaktifkan di theme.colors agar tak bisa dipakai tanpa sengaja.
- Buat tema bawaan `madinah` (hijau-teal + emas, lihat bagian Template). Tema aktif dikirim lewat shared prop Inertia `theme` (default per role di config/theme.php, bisa diubah Super Admin di Pengaturan) dan diterapkan di app.blade.php sebelum render agar tidak berkedip.
- Kontras: buat scripts/check-contrast.mjs yang membaca tokens.css dan menggagalkan build bila pasangan ink/surface, brand-fg/brand-600, dan tiap status fg/bg < 4.5:1 (teks) atau < 3:1 (komponen UI).

## 2. Komponen primitif: resources/js/Components/ui/
AppButton (primary, secondary, ghost, danger; ukuran sm/md/lg; loading; ikon), AppInput, AppSelect, AppTextarea, AppCheckbox, AppSwitch, FormField (label, hint, error), CurrencyInput (format Rupiah), SearchInput, AppCard, StatCard, PageHeader (judul, deskripsi, aksi utama, breadcrumb), Tabs, Stepper, DataTable (urut, paginasi server-side lewat Inertia, aksi baris, slot kosong/memuat, otomatis berubah jadi daftar kartu di bawah 640px), FilterBar, StatusBadge (peta enum domain -> varian semantik di satu berkas), EmptyState (ilustrasi + ajakan bertindak), Skeleton (blok, baris, tabel), Modal, Drawer, ConfirmDialog, Toast + ToastHost, Avatar, ProgressBar, ProgressRing, Tooltip, Dropdown (bangun ulang di atas yang ada).

## 3. Komponen domain: resources/js/Components/domain/
StudentPicker (cari siswa), ClassSelect, SubjectSelect, AcademicYearBadge, AttendanceStatusToggle (Hadir/Izin/Sakit/Alpa), MoneyText, DateText, ScoreChip (nilai + predikat), KopSurat (kop sekolah dari pengaturan, dipakai cetak/PDF), QrDisplay, PrintSheet (pembungkus halaman cetak A4), NotificationBell (rapikan yang ada).

## 4. Shell tunggal: Layouts/PortalShell.vue
- Satu layout dengan 3 varian: `sidebar` (admin, guru, bendahara, staf, kantin), `bottom-nav` (siswa, ortu; mobile-first), `bare` (kiosk, ujian CBT, cetak).
- Isi menu dari resources/js/config/navigation.js (struktur: role -> grup -> item {label, route, ikon, module}); otomatis menyembunyikan item untuk modul yang dinonaktifkan (data dari ModuleService yang sudah ada) dan menandai item aktif.
- Slot: header, actions, default. Termasuk: lompat ke konten, bell notifikasi, menu profil, pengalih tema (hanya jika diizinkan), ToastHost, ConfirmHost, indikator "Tersimpan/Menyimpan..." untuk halaman autosave.
- Layout lama (AdminLayout, TeacherLayout, dst.) JANGAN dihapus dulu; Sprint 2 yang memigrasi lalu menghapusnya.

## 5. Composable: resources/js/composables/
useToast, useConfirm, useFormat (rupiah, tanggal panjang/pendek id-ID, angka, persen), useDebouncedSearch, usePolling (interval + pause saat tab tersembunyi), useAutosave (debounce + status), useTheme.
HandleInertiaRequests: bagikan `flash.success/error/warning` dan otomatis ditampilkan sebagai toast.

## 6. Style guide hidup
- Halaman /dev/design-system (hanya environment local/testing, 404 di produksi) yang memamerkan semua komponen, semua status, semua tema, mode mobile 360px, keadaan kosong/memuat/galat. Halaman ini adalah "kontrak visual" proyek.

## 7. Penjaga konsistensi (otomatis)
- scripts/check-ui-consistency.mjs: gagal bila di resources/js/Pages ditemukan (a) kelas warna mentah Tailwind, (b) hex/rgb/hsl, (c) atribut style=, (d) <button|input|select|textarea|table> mentah di luar Components/, (e) alert( atau confirm(, (f) import dari Layouts selain PortalShell. Tambahkan ke package.json (`npm run lint:ui`) dan ke CI. Beri allowlist kecil dan tertulis untuk pengecualian.
- Perbarui docs/CONVENTIONS.md: katalog komponen, aturan token, cara menambah komponen/tema.

## Aksesibilitas dan gerak
- Target sentuh >= 44px; fokus terlihat; label untuk setiap ikon-tombol; kontras sesuai skrip. Animasi 150-250 ms hanya untuk transisi bermakna dan hormati `prefers-reduced-motion`.
- Jangan memakai efek kaca/blur berat sebagai gaya dasar (hanya untuk tema yang memintanya, dengan fallback solid).

## Gerbang lulus
- /dev/design-system menampilkan semua komponen di tema madinah dan satu tema uji lain (buat `uji-gelap` sederhana) tanpa perubahan kode komponen.
- `npm run lint:ui`, check-contrast, build, pint, test hijau.
- Tidak ada halaman lama yang berubah perilakunya.

## Tanyakan dulu jika belum jelas
Apakah mode gelap dibutuhkan (jika ya, jadikan tema `madinah-gelap`)? Apakah logo resmi sekolah tersedia dalam SVG?
```

## Sprint 2 — Migrasi semua portal

Sprint ini memindahkan setiap halaman ke PortalShell dan komponen bersama, portal dengan pengguna terbanyak lebih dulu (Ortu, Siswa), dan menambahkan perbaikan UX per pengguna.

Saran pembagian sesi: 2A (Ortu + Siswa), 2B (Guru), 2C (Bendahara + Kantin + Staf), 2D (Admin + Auth + Welcome + PPDB). Jalankan prompt di bawah empat kali, tiap kali dengan satu kelompok portal.

```markdown
# SPRINT 2 — MIGRASI PORTAL KE DESIGN SYSTEM
Kelompok yang dikerjakan sesi ini: <ISI: 2A | 2B | 2C | 2D>

Aturan inti: PERILAKU TIDAK BERUBAH. Test yang sudah ada tidak boleh diedit supaya hijau (kecuali selector/teks UI yang memang berganti, dan itu dicatat). Tujuan sesi adalah konsistensi visual dan UX, bukan fitur baru.

## Prosedur per halaman (ulangi untuk setiap halaman)
1. Ganti layout lama dengan PortalShell + varian yang sesuai.
2. Ganti semua elemen mentah dengan komponen Components/ui dan Components/domain. Hapus kelas warna mentah, hex, style=, alert/confirm.
3. Tambahkan 4 keadaan: memuat (Skeleton), kosong (EmptyState dengan ajakan bertindak), galat, dan sukses (toast).
4. Pecah file > 300 baris menjadi komponen di folder Pages/<Portal>/Partials atau Components/domain.
5. Tabel -> DataTable (otomatis jadi kartu di mobile). Form -> FormField + validasi inline.
6. Uji di lebar 360px dan 1280px; tidak ada scroll horizontal pada halaman.
7. Jalankan npm run lint:ui; nol pelanggaran baru.

## Perbaikan UX khusus per kelompok
2A Ortu dan Siswa (bottom-nav, mobile-first)
- Ortu: bagian teratas dashboard = "Hari ini" untuk anak aktif: hadir/tidak hadir, tugas yang jatuh tempo, tagihan jatuh tempo, pengumuman terbaru. Pengalih anak selalu terlihat. Tombol bayar/lihat tagihan menonjol. Pecah Parent/Dashboard.vue (700+ baris) menjadi tab dan komponen.
- Siswa: sapaan nama, kartu "Pelajaran berikutnya" dan "Tugas terdekat". Tambahkan prop `mode` (`anak` untuk tingkat 1-3: tombol lebih besar, teks lebih sedikit, ikon besar; `standar` untuk tingkat 4-6) yang ditentukan dari tingkat kelas, tanpa percabangan kode di tiap halaman (cukup lewat token density dan beberapa slot).
- CBT siswa memakai varian shell `bare` (fokus ujian): tanpa menu, pewaktu terlihat, ukuran huruf soal dapat diatur.
2B Guru
- Presensi satu ketukan per siswa, tombol Semua Hadir, simpan otomatis dengan indikator status (useAutosave).
- Input nilai: navigasi keyboard (Tab/Enter/panah), sorot nilai di bawah KKM, simpan otomatis per sel dengan pembatalan ringan.
- Dashboard guru: "Yang perlu tindakan" (tugas belum dikoreksi, presensi belum diisi, ujian aktif) sebagai daftar tindakan, bukan angka saja.
2C Bendahara, Kantin, Staf
- Kasir: kolom pencarian siswa selalu fokus, hasil dengan keyboard, nominal berfont numerik (font-numeric) dan kontras tinggi; kesalahan input langsung terlihat.
- Kantin (POS RFID dan scan QR): tombol besar, umpan balik bunyi/warna setelah transaksi, tanpa konfirmasi berlapis untuk jalur cepat.
2D Admin, Auth, Welcome, PPDB
- Admin: DataTable dengan filter tersimpan di query string, aksi massal, pencarian cepat (Ctrl+K) untuk lompat ke siswa/guru/kelas.
- Login semua role memakai satu komponen LoginCard dengan variasi judul/ilustrasi per role (bukan 9 salinan). Login.vue (680 baris) dipecah.
- Welcome.vue (1000 baris) dipecah menjadi section-section komponen; optimalkan gambar (lazy, ukuran responsif) dan cache feed Instagram.
- PPDB: formulir bertahap dengan Stepper, simpan draf otomatis, ringkasan sebelum kirim.

## Penutup sesi
- Layout lama yang sudah tidak dipakai dihapus (setelah grep menunjukkan nol referensi). Sesi 2D menghapus semua layout lama; jika masih ada yang memakai, sesi belum selesai.
- Perbarui docs/CHANGELOG.md dan lampirkan daftar halaman yang dimigrasi.

## Gerbang lulus (sesi 2D = gerbang sprint)
- Nol import ke Layouts selain PortalShell; Components Breeze yang tak terpakai dihapus.
- npm run lint:ui, check-contrast, build, pint, php artisan test hijau.
- Daftar periksa manual per role (3 skenario utama tiap role) dijalankan di 360px dan dilampirkan sebagai tangkapan layar atau catatan.
```

## Sprint 3 — PDF resmi, audit log, soft delete, backup

Sprint ini membuat dokumen resmi sekolah konsisten dan dapat diverifikasi, serta menambah jejak audit dan pemulihan data untuk modul nilai dan keuangan.

```markdown
# SPRINT 3 — DOKUMEN RESMI, AUDIT, PEMULIHAN DATA

## A. PDF server-side (satu mesin, satu kop)
- Pasang barryvdh/laravel-dompdf (catat alasan di CHANGELOG). Satu service `App\Services\Pdf\PdfRenderer` dan satu layout Blade `resources/views/pdf/layout.blade.php` yang memakai data kop surat dari pengaturan sekolah (logo, nama, alamat, NPSN) dan gaya cetak yang sama (font DejaVu Sans atau font lokal yang mendukung teks Indonesia/Arab bila perlu).
- Template Blade per dokumen di resources/views/pdf/: e-Raport, Surat Peringatan SP1-3, Berita Acara Pelanggaran (BAP), slip gaji, sertifikat prestasi, KTS (cetak), bukti pembayaran. Struk termal kasir tetap seperti sekarang.
- Satu `DocumentController` per portal yang relevan hanya memanggil PdfRenderer; otorisasi lewat Policy (guru mapel/wali kelas untuk raport, ortu/siswa pemilik, bendahara untuk pembayaran).
- Verifikasi keaslian: setiap dokumen resmi memuat QR ke route publik `/verifikasi/{token}` (token acak 40 karakter, tabel `document_verifications`: jenis, referensi, hash isi, diterbitkan oleh, waktu). Halaman publik hanya menampilkan jenis dokumen, nama (disamarkan sebagian), tanggal terbit, dan status "sah/dicabut"; tanpa data sensitif. Rate limit route ini.
- Ganti modal cetak browser dengan tombol "Unduh PDF" + "Pratinjau" memakai komponen PrintSheet/KopSurat dari design system untuk pratinjau.
- Test: setiap dokumen menghasilkan PDF (header %PDF), 403 untuk pihak lain, dan token verifikasi valid/dicabut.

## B. Audit log (satu mekanisme)
- Tabel `audit_logs` (UUID, user_id, event, auditable_type, auditable_id string 36, old json, new json, ip, user_agent, created_at) dan trait `App\Models\Concerns\Auditable` (created/updated/deleted/restored). Jangan menambah paket lain.
- Pasang di: Grade, Payment, Bill, Wallet (penyesuaian saldo, reset PIN, tanpa menyimpan PIN), WalletTransaction, Payroll, PayrollComponent, StudentViolation, Attendance (perubahan setelah hari H), Exam, Student, User (perubahan role), SystemSetting. Field sensitif (password, pin, token) dikecualikan.
- Halaman Super Admin `/admin/audit` memakai DataTable: filter pengguna, jenis, rentang tanggal; detail perubahan sebelum/sesudah. Tidak ada tombol hapus.

## C. Soft delete dan pembatalan
- SoftDeletes pada: Student, Lesson, Assignment, Exam, Question, Book, Bill, Post, BillType. Migration dan halaman "Sampah" sederhana untuk Super Admin (pulihkan/hapus permanen, dengan audit).
- Pembayaran dan mutasi dompet TIDAK pernah dihapus: tambahkan status `Batal` + alasan + pembatal + waktu, dan transaksi pembalik yang menjaga saldo benar (DB::transaction, test saldo).

## D. Backup dan pemulihan
- Pasang spatie/laravel-backup: cadangan harian database + storage/app/private, retensi 7 harian/4 mingguan, jadwal di routes/console.php, notifikasi gagal ke Super Admin lewat notifikasi in-app dan WA.
- Halaman Super Admin kecil "Cadangan" (daftar, ukuran, waktu, status). Dokumentasikan prosedur restore di docs/RESTORE.md dan buktikan di test/skrip bahwa cadangan bisa dipulihkan ke database kosong.
- Jika server Windows/Laragon: sertakan petunjuk Task Scheduler untuk `php artisan schedule:run` tiap menit.

## Gerbang lulus
- Tidak ada lagi pencetakan resmi lewat modal browser; seluruh dokumen berasal dari PdfRenderer.
- QR verifikasi di slip gaji dan raport berfungsi di HP.
- Perubahan nilai dan pembayaran tampak di /admin/audit.
- Restore dari cadangan terbukti (catat langkah dan hasilnya).
- test, pint, build, lint:ui hijau.

## Tanyakan dulu jika belum jelas
Bentuk kop surat dan tanda tangan resmi (siapa menandatangani raport/slip/SP) dan apakah tanda tangan digital (gambar) diizinkan oleh sekolah.
```

## Sprint 4 — CBT lanjutan

Skema soal sudah mengenal enam tipe, tetapi UI ujian siswa baru menangani tiga (Pilihan Ganda, Benar/Salah, Esai); sprint ini menuntaskan sisanya dan memberi guru layar pengawasan.

```markdown
# SPRINT 4 — CBT LANJUTAN

Kondisi saat ini: tabel `questions` punya enum type ['Pilihan Ganda','Ganda Kompleks','Benar Salah','Menjodohkan','Isian Rumpang','Esai']; StudentPortalController hanya menilai otomatis Pilihan Ganda dan Benar Salah; CBTExam.vue hanya merender PG/BS/Esai; belum ada layar monitor guru. Anti-cheat dasar (tab switch, violation) sudah ada. Periksa skema options/questions/exam_attempts yang ada dan perluas seminimal mungkin (jangan membuat tabel paralel jika kolom JSON cukup).

## A. Tipe soal yang belum ada
1. Ganda Kompleks: pilih >= 1 jawaban benar. Penilaian: semua benar = skor penuh; opsi `partial` (skor proporsional, salah pilih mengurangi, minimum 0) dapat diatur per ujian.
2. Menjodohkan: dua kolom (premis -> pasangan). Simpan pasangan benar; tampilan siswa: pilih pasangan lewat dropdown (aksesibel di HP), bukan hanya drag-drop. Skor proporsional per pasangan.
3. Isian Rumpang: satu atau lebih rumpang; tiap rumpang punya daftar jawaban yang diterima. Normalisasi: trim, huruf kecil, spasi ganda, dan opsi abaikan tanda baca; angka dibandingkan secara numerik.
4. Editor guru untuk keenam tipe di halaman Guru/CBT (komponen QuestionEditor per tipe, pratinjau persis tampilan siswa, validasi: minimal satu jawaban benar, dst.). Dukungan gambar pada soal dan opsi (upload ke disk privat, ukuran maksimum, disajikan lewat route terproteksi).
5. Impor soal dari Excel (template + pesan galat per baris) memakai Maatwebsite yang sudah ada.

## B. Mesin penilai terpisah dan teruji
- `App\Services\Exam\ExamGrader` dengan satu kelas strategi per tipe (`GradesPilihanGanda`, dst.) dan antarmuka tunggal. Dipanggil dari satu tempat saat submit. Tulis unit test per tipe, termasuk kasus tepi (kosong, sebagian benar, spasi/kapital, desimal koma).
- Esai: antrean koreksi guru (layar Koreksi Esai: lihat jawaban, beri skor <= maksimum, komentar), total nilai dihitung ulang otomatis dan disinkronkan ke tabel grades seperti tugas.
- Pengacakan urutan soal dan opsi per siswa dengan seed yang disimpan di attempt (stabil saat refresh, dapat dimatikan per ujian).

## C. Ketahanan saat ujian
- Simpan jawaban otomatis (debounce) ke server; antrean lokal bila koneksi putus dan kirim ulang saat kembali online; indikator "Tersimpan/Menunggu jaringan".
- Timer sepenuhnya berbasis waktu server (kirim `server_now` tiap simpan), tidak hilang saat refresh, dan submit otomatis saat habis.
- Aksi guru: tambah waktu individu, izinkan lanjut setelah gangguan (reset pelanggaran), paksa kumpulkan; semua tercatat (audit log bila Sprint 3 selesai).

## D. Layar pengawasan guru (realtime)
- Halaman Guru/CBT/Monitor per ujian: daftar siswa kelas (Belum mulai / Mengerjakan / Selesai), progres soal terjawab, jumlah pelanggaran, terakhir aktif. Pembaruan lewat usePolling tiap 5 detik (endpoint ringan, tanpa N+1). Rancang antarmuka data sehingga dapat diganti ke Laravel Reverb nanti tanpa mengubah halaman; JANGAN memasang Reverb kecuali dijawab "ya" di bawah.
- Ujian remedial: ujian yang ditargetkan ke sebagian siswa.

## E. Laporan hasil
- Rekap per ujian (rata-rata, tertinggi/terendah, distribusi), analisis butir soal (tingkat kesulitan, persentase memilih tiap opsi), ekspor Excel dan PDF (PdfRenderer dari Sprint 3).

## UI
Semua layar memakai komponen design system. Ujian siswa memakai shell `bare`; ukuran huruf soal dapat diatur; kontras tinggi; tombol besar untuk tingkat 1-3 (mode `anak`).

## Gerbang lulus
- Unit test ExamGrader untuk 6 tipe lulus; feature test submit penuh per tipe; test otorisasi (siswa tak bisa lihat kunci jawaban atau attempt orang lain).
- Uji beban ringan: skrip k6 atau PHP untuk 100 siswa mengirim jawaban bersamaan, p95 < 500 ms (catat hasil).
- test, pint, build, lint:ui hijau.

## Tanyakan dulu jika belum jelas
Realtime pakai Reverb atau cukup polling 5 detik? Berapa siswa maksimal ujian bersamaan di lab?
```

## Sprint 5 — Gamifikasi, PWA, kasbon, perpustakaan, portal siswa

Sprint ini menutup fitur rencana awal yang belum ada; bagi menjadi 5A (PWA + portal siswa), 5B (gamifikasi), 5C (kasbon + perpustakaan siswa) bila satu sesi terasa terlalu panjang.

```markdown
# SPRINT 5 — FITUR PELENGKAP
Kelompok sesi ini: <ISI: 5A | 5B | 5C | semua>

## 5A. PWA dan portal siswa yang lengkap
- PWA: manifest (nama, ikon 192/512, warna dari token tema), service worker lewat vite-plugin-pwa (catat sebagai dependency baru): cache aset statis dan halaman offline sederhana; JANGAN cache respons berotentikasi atau data siswa. Tombol "Pasang aplikasi" di profil. Bila perangkat offline, tampilkan halaman offline yang ramah.
- Siswa: halaman Keuangan (saldo dompet, riwayat mutasi, tagihan hanya-baca; PIN tidak pernah ditampilkan), halaman Prestasi dan Pembinaan (hanya catatan positif dan pembinaan; poin pelanggaran dan detail BK TIDAK ditampilkan ke siswa SD, hanya ke ortu dan guru), dan "Hubungi Guru BK" (formulir singkat yang membuat permintaan konseling, bukan chat bebas).
- Orang tua: preferensi notifikasi (WA/in-app per jenis), unduh PDF raport dan bukti bayar.

## 5B. Gamifikasi LMS (ramah anak, tanpa mempermalukan)
- Tabel `point_events` (UUID, student_id, type, points, reference_type/id, unique per kejadian agar idempoten), `badges`, `student_badges`. Satu service `PointService::award($student, $eventType, $reference)` sebagai satu-satunya pintu; semua pemicu memanggilnya (kumpul tugas tepat waktu, nilai >= KKM, hadir berturut-turut, selesai ujian, membaca buku).
- Lencana bertema nilai sekolah (contoh: Rajin Hadir, Penuntas Tugas, Pembaca Cilik, Hafalan Konsisten); gambar SVG ikon dari satu sprite; aturan lencana dalam seeder, bisa diaktifkan per guru/sekolah.
- Papan peringkat: per kelas, opt-in oleh sekolah. Tampilkan 5 teratas dengan nama depan, dan untuk siswa lain hanya kemajuan pribadinya ("naik 2 level minggu ini"). Tanpa peringkat bawah. Ada pengaturan Super Admin untuk mematikan seluruh gamifikasi.
- Kartu progres di dashboard siswa (ProgressRing dari design system) dan ringkasan untuk guru dan ortu.

## 5C. Kasbon pegawai dan perpustakaan siswa
- Kasbon: tabel `advances` (pegawai, jumlah, alasan, status Diajukan/Disetujui/Ditolak/Lunas, jumlah cicilan, mulai periode) dan `advance_installments`. Pegawai mengajukan dari portal masing-masing; Bendahara menyetujui; saat Generate Payroll cicilan jatuh tempo otomatis menjadi PayrollComponent bertipe Potongan. Aturan batas (maksimum % gaji pokok, satu kasbon aktif) di pengaturan. DB::transaction, audit log, test perhitungan payroll.
- Perpustakaan siswa: katalog dan pencarian, detail buku, pembaca e-book/PDF di dalam aplikasi (lanjutkan dari reader guru), permintaan pinjam buku fisik yang disetujui petugas, riwayat pinjam, dan pengingat jatuh tempo lewat notifikasi. Tambah log baca (memicu PointService).

## Gerbang lulus
- Semua fitur memakai komponen design system dan nyaman di 360px; PWA dapat dipasang di Android Chrome.
- Test: idempotensi PointService, perhitungan potongan kasbon pada payroll, otorisasi (siswa tak bisa melihat BK/pelanggaran, pegawai hanya melihat kasbon sendiri).
- test, pint, build, lint:ui hijau.

## Tanyakan dulu jika belum jelas
Apakah sekolah setuju papan peringkat ditampilkan? Batas kasbon (persen gaji, maksimum bulan cicilan)? Apakah ada daftar judul e-book legal yang akan diunggah?
```

## Sprint 6 — Keamanan, performa, UAT

Sprint ini menyiapkan SIMSE untuk dipakai sungguhan: pengerasan keamanan, ukuran performa, pemantauan, dan uji penerimaan bersama pengguna.

```markdown
# SPRINT 6 — PENGERASAN, PERFORMA, UAT

## A. Keamanan
- Header keamanan lewat satu middleware: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy (kamera hanya untuk halaman scan), dan Content-Security-Policy dalam mode report-only dulu (catat pelanggaran, perbaiki, baru enforce). HSTS hanya di produksi.
- Rate limit terpusat (RateLimiter::for) untuk: login semua portal, permintaan OTP orang tua, verifikasi PIN dompet/kantin (kunci sementara setelah N salah + notifikasi), route /verifikasi, unduhan file. Pesan galat seragam agar tidak membocorkan keberadaan akun.
- Captcha: Cloudflare Turnstile (gratis) di login, permintaan OTP, dan formulir PPDB; dapat dimatikan lewat konfigurasi untuk dev/test.
- Untuk Super Admin dan Bendahara: autentikasi dua faktor TOTP (opsional per akun, wajib dapat diaktifkan Super Admin). Jangan membuat sistem sendiri jika paket kecil yang terawat tersedia; catat pilihan.
- Sesi: lifetime dan regenerasi saat login, invalidasi sesi lain saat ganti kata sandi, daftar perangkat aktif di profil.
- Audit upload: semua upload melewati satu `SecureUpload` service (mime sebenarnya, ukuran, nama acak, disk privat, ekstensi putih); tidak ada file pengguna di public disk.
- Tinjauan mass-assignment dan route: tidak ada model dengan $guarded kosong; semua route tulis punya Policy dan test; jalankan `composer audit` dan `npm audit`, perbaiki atau catat pengecualian.
- Pastikan APP_DEBUG=false di produksi, log tanpa data sensitif, dan .env.example lengkap.

## B. Performa
- Tambahkan indeks untuk kolom yang sering difilter (student_class_id, academic_year_id, tanggal presensi, status tagihan, user_id notifikasi) lewat migration terpisah dan bukti EXPLAIN pada 5 query terberat.
- Pertahankan `preventLazyLoading` aktif di test; tambahkan test yang gagal bila halaman berat (dashboard tiap role, Kasir, Nilai, Monitor CBT) melakukan > N query (gunakan DB::getQueryLog dan batas wajar).
- Cache ringkasan dashboard (Cache::remember dengan kunci berversi; driver database tidak mendukung tag, jadi hindari tag). Redis hanya bila dijawab "ya" di bawah.
- Antrean: panduan Supervisor/NSSM untuk `queue:work` dan `schedule:run`, dengan `queue:failed` terpantau.
- Laravel Pulse: pasang, batasi hanya Super Admin, tambahkan kartu antrean, query lambat, dan penggunaan server. Tambahkan juga halaman kesehatan `/up` yang sudah ada ke pemantauan sederhana.
- Frontend: analisis ukuran bundle (`vite build --report` atau plugin visualizer), pecah per portal dengan dynamic import, gambar dioptimalkan.

## C. Uji penerimaan pengguna (UAT)
- Buat docs/UAT.md: untuk tiap role (Admin TU, Guru, Guru BK, Wali Kelas, Bendahara, Staf, Kantin, Siswa kelas rendah, Siswa kelas tinggi, Orang Tua) minimal 8 skenario nyata bersifat "lakukan-lalu-periksa" dengan akun demo, hasil yang diharapkan, dan kolom lulus/gagal/catatan.
- Seeder `UatSeeder` yang mengisi data realistis satu semester; seeder produksi terpisah hanya berisi role, modul, pengaturan, dan satu Super Admin.
- docs/DEPLOY.md: langkah server (PHP 8.3, ekstensi, web server, HTTPS, permission storage, `storage:link` tidak dipakai untuk file privat), .env produksi, queue worker, scheduler, backup, rotasi log, cara rollback.
- docs/USER-GUIDE per role (maks. 2 halaman, bergambar) untuk pelatihan guru dan TU.

## Gerbang lulus
- Tidak ada temuan `composer audit`/`npm audit` tingkat tinggi tersisa tanpa catatan.
- CSP dalam mode enforce tanpa pelanggaran pada alur utama.
- Uji beban: dashboard dan Kasir p95 < 800 ms, simpan jawaban CBT p95 < 500 ms pada data satu semester (catat).
- UAT.md terisi oleh pengguna sekolah (bukan oleh agen) dan semua skenario kritis lulus.

## Tanyakan dulu jika belum jelas
Apakah tersedia Redis di server? Apakah Super Admin dan Bendahara bersedia memakai 2FA? Siapa yang menjalankan UAT di sekolah?
```

## Template tema tidak reguler (Sprint 7, opsional)

Enam arah visual di bawah berbeda jauh dari tampilan dashboard biasa, tetapi semuanya hanya berupa paket token plus sedikit ornamen, sehingga tidak ada halaman yang perlu ditulis ulang dan arsitektur tetap satu.

Aturan pagarnya: tema boleh mengubah warna, font, radius, bayangan, kepadatan, ornamen, ilustrasi, dan nada teks mikro. Tema tidak boleh mengubah posisi navigasi, letak tombol aksi utama, ukuran target sentuh (>= 44px), urutan field formulir, atau kontras minimum. Itulah yang menjaga tampilan menarik sekaligus mudah dipakai.

| Tema | Suasana | Cocok untuk | Font (judul / isi) | Bentuk dan ornamen khas |
| --- | --- | --- | --- | --- |
| Madinah | Tenang, berwibawa, bernuansa Islami | Landing, Admin, Guru (tema bawaan) | Fraunces / Figtree | Radius sedang; pola geometri Islami tipis (opasitas 6%) hanya di header dan halaman login; garis emas tipis sebagai pemisah |
| Taman Belajar | Ceria, hangat, seperti buku cerita | Siswa tingkat 1-3 (mode `anak`) | Fredoka / Figtree | Radius besar (24px), kartu seperti stiker dengan bayangan datar, maskot dan ilustrasi per mata pelajaran, lencana besar |
| Kertas dan Tinta | Tenang, editorial, seperti buku catatan | Guru, e-Raport, Perpustakaan, laporan | Newsreader / Figtree | Latar krem, tabel dengan garis rambut tanpa kotak, judul serif, angka rata-kanan; terasa seperti lembar cetak |
| Ledger Tegas | Tegas, cepat dibaca, serius | Bendahara, Kasir, Payroll | Bricolage Grotesque / IBM Plex Mono (angka) | Garis tebal 2px, blok warna datar, tanpa bayangan lembut, nominal besar berfont mono, status sebagai label persegi |
| Senja Kaca | Lembut, modern, ramah | Portal Orang Tua | Plus Jakarta Sans / Figtree | Latar gradien lembut persik ke lavender, kartu semi-transparan hanya untuk 1-2 kartu utama (fallback solid), angka ringkasan sangat besar |
| Pasar Kantin | Hangat, ramai, seperti papan menu | Kantin, POS RFID, e-Kantin siswa | Baloo 2 / Figtree | Tombol besar seperti papan harga, label harga berbentuk tag, ikon makanan, umpan balik warna penuh setelah transaksi |

Catatan tambahan: layar ujian CBT memakai varian bawaan `fokus` (turunan Kertas dan Tinta: tanpa ornamen, huruf soal 18-20px, opsi sepia atau gelap opsional) agar mata siswa tidak lelah.

Palet usulan beserta kontras yang saya hitung (rumus WCAG 2.x; minimum 4,5:1 untuk teks):

| Tema | Latar | Teks | Teks sekunder | Warna merek | Aksen | Teks/latar | Putih/merek | Merek/latar |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Madinah | #FBF9F4 | #12302B | #4A635E | #0F6B5C | #C9A24B | 13,5 | 6,4 | 6,1 |
| Taman Belajar | #FFFBEB | #1E293B | #475569 | #1D6FD8 | #F5B301 | 14,1 | 4,9 | 4,7 |
| Kertas dan Tinta | #F6F1E7 | #1B1A17 | #57534A | #B3361B | #2F5D50 | 15,5 | 6,1 | 5,4 |
| Ledger Tegas | #FFFFFF | #0A0A0A | #525252 | #1B3FD1 | #FFD400 | 19,8 | 7,8 | 7,8 |
| Senja Kaca | #FCF7F9 | #2B2140 | #5E5473 | #6D4AD6 | #F08A6C | 14,2 | 5,8 | 5,5 |
| Pasar Kantin | #FFF6E8 | #3A1F10 | #6B4A38 | #C2410C | #F2B134 | 14,2 | 5,2 | 4,8 |

Warna aksen (emas, kuning, persik) tidak boleh dipakai sebagai warna teks di atas latar terang; pakai hanya sebagai blok, garis, atau ornamen dengan teks gelap di atasnya. Angka di atas hanya memeriksa pasangan inti; skrip `check-contrast` dari Sprint 1 tetap harus memeriksa warna status (sukses, peringatan, bahaya, info) per tema.

```markdown
# SPRINT 7 — PAKET TEMA (opsional, setelah Sprint 2 selesai)

Tujuan: menambah tema tanpa menyentuh halaman. Seluruh perubahan hanya di resources/css/themes/*.css, config/theme.php, komponen ThemeSwitcher, dan aset ornamen/ilustrasi.

1. Buat satu berkas CSS per tema di resources/css/themes/ (madinah, taman-belajar, kertas-tinta, ledger-tegas, senja-kaca, pasar-kantin, fokus-ujian) yang hanya mengisi token dari tokens.css (nilai pada tabel palet dokumen ini; sesuaikan bila skrip kontras gagal). Dilarang menambah token baru tanpa memperbarui tokens.css dan CONVENTIONS.md.
2. Ornamen lewat token dan satu komponen `ThemeOrnament` (SVG inline, dimuat per tema, opasitas rendah, aria-hidden). Ilustrasi EmptyState dan maskot diganti per tema lewat satu peta aset, bukan percabangan di halaman.
3. Font: pasang lewat @fontsource (self-host, mendukung PWA offline) dan hanya muat yang dipakai tema aktif; font-display: swap. Catat ukuran tambahan bundle.
4. Peta tema bawaan per role di config/theme.php: Admin, Guru -> madinah; Guru (e-Raport, Perpustakaan) -> kertas-tinta; Bendahara -> ledger-tegas; Ortu -> senja-kaca; Siswa tingkat 1-3 -> taman-belajar; Siswa tingkat 4-6 -> madinah; Kantin -> pasar-kantin; ujian CBT -> fokus-ujian. Super Admin dapat mengubah peta dan mematikan pengalih tema bagi pengguna.
5. /dev/design-system menampilkan semua komponen di semua tema; tambahkan uji otomatis check-contrast untuk setiap tema dan uji bahwa setiap tema mendefinisikan seluruh token wajib.
6. Uji di 360px dan 1280px; pastikan target sentuh, urutan form, dan posisi navigasi identik di semua tema.

Gerbang lulus: mengganti tema hanya dengan mengubah atribut data-theme; tidak ada diff di Pages/ atau Components/; check-contrast, lint:ui, build, test hijau.
```
