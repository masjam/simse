# Konvensi & Standar Rekayasa SIMSE

Dokumen ini menjadi acuan utama pengembangan sistem informasi manajemen SD Muhammadiyah Al Mujahidin Wonosari. Seluruh kontributor wajib mematuhi panduan ini.

---

## 1. Stack Teknologi

- **PHP**: 8.3
- **Framework Backend**: Laravel 13
- **Frontend Core**: Inertia.js 2, Vue 3 (Composition API, `<script setup>`)
- **Styling**: Tailwind CSS v3 (dipilih Tailwind v3 atas stabilitas dan integrasi Vite; plugin `@tailwindcss/vite` v4 dinonaktifkan)
- **Otorisasi & Hak Akses**: Spatie Laravel-Permission
- **Database**: MySQL 8.x (Laragon pada Windows untuk dev), Primary Key UUID (`HasUuids`) di seluruh tabel
- **Penyimpanan Berkas Privat**: Disk `private` / `local` via route terproteksi
- **Format & Lokalisasi**: `id-ID`, Rupiah (`Rp`), Zona waktu `Asia/Jakarta`, istilah baku UI: **"siswa"** (bukan santri)

---

## 2. Arsitektur Backend

### 2.1 Lapisan Kode
1. **Controller**:
   - Berfungsi sebagai pengendali alur request/response (tipis, maksimal < 300 baris).
   - Validasi menggunakan `FormRequest`.
   - Otorisasi menggunakan Policy/Gate via `$this->authorize()` atau `Gate::authorize()`.
   - Logika bisnis dipindahkan ke `app/Actions/<Domain>/<Action>.php` atau `app/Services/<Domain>Service.php`.
   - Satu controller per domain/fitur di folder per portal: `app/Http/Controllers/<Portal>/<Fitur>Controller.php`.
2. **Data & Query**:
   - **Eager loading wajib** (`with()`, `loadMissing()`) guna menghindari masalah N+1 query.
   - Non-produksi mengaktifkan `Model::preventLazyLoading()` dan `Model::shouldBeStrict()`.
   - Operasi finansial (SPP, dompet digital, payroll) dan input nilai siswa **wajib** dibungkus dalam `DB::transaction()`.
   - Props Inertia dirapikan dan diformat di Action/Service/Resource sebelum dikirim ke halaman Vue.
3. **Penamaan Route**:
   - Format: `<portal>.<fitur>.<aksi>` (contoh: `siswa.cbt.start`, `guru.jurnal.update`, `bendahara.spp.generate`).
   - Semua route dilindungi middleware `auth`, role-based verification, dan module toggle bila berlaku.

---

## 3. Arsitektur Frontend & Design System (Sprint 1)

### 3.1 Shell Tunggal
- Seluruh halaman portal menggunakan satu layout bersama: `Layouts/PortalShell.vue`.
- Mendukung 3 varian:
  1. `sidebar`: Standar dashboard untuk Super Admin, Guru, Bendahara, Staf, Pengelola Kantin.
  2. `bottom-nav`: Mobile-first dengan bottom navigation bar khusus Siswa dan Orang Tua.
  3. `bare`: Minimalis tanpa shell untuk kiosk presensi, ujian CBT mandiri, dan lembar cetak dokumen.
- Dilarang membuat layout baru per role; layout lama (`AdminLayout`, `TeacherLayout`, dll) hanya dipertahankan sementara hingga migrasi penuh di Sprint 2.

### 3.2 Navigasi Terpusat
- Navigasi didefinisikan secara terpusat di `resources/js/config/navigation.js`.
- Mendukung pengelompokan grup menu dan penyembunyian dinamis berdasarkan aktivasi modul sistem via `activeModules`.

### 3.3 Sistem Token & Desain Semantik
- Semua token desain didefinisikan sebagai CSS variable di `resources/css/tokens.css` dan dipetakan di `tailwind.config.js`.
- Atribut `data-theme` pada tag `<html>` menentukan tema aktif:
  - `madinah`: Hijau-teal teduh khas Al-Mujahidin + aksen emas.
  - `madinah-gelap`: Mode gelap nyaman untuk malam hari.
  - `uji-gelap`: Tema kontras tinggi untuk verifikasi design system.
- Nilai kontras diverifikasi otomatis oleh `scripts/check-contrast.mjs` (WCAG AA: teks >= 4.5:1, UI >= 3:1).
- **Larangan Keras**: Dilarang menggunakan warna mentah (`bg-blue-600`, `text-slate-900`), nilai hex/rgb/hsl, atau `style=""` pada komponen halaman. Gunakan token semantik:
  - Permukaan: `bg-surface`, `bg-surface-raised`, `bg-surface-sunken`
  - Teks: `text-ink`, `text-ink-muted`
  - Border & Garis: `border-line`, `ring-focus`
  - Brand & Status: `bg-brand-600`, `text-brand-fg`, `bg-status-success-bg`, dll.

---

## 4. Katalog Komponen Desain

### 4.1 Komponen Primitif (`resources/js/Components/ui/`)
- `AppButton`: Tombol serbaguna (primary, secondary, ghost, danger, accent; sm/md/lg; loading; ikon; touch target >= 44px).
- `AppInput`, `AppSelect`, `AppTextarea`: Kontrol formulir dengan state normal, invalid/error, disabled, dan prefix/suffix ikon.
- `AppCheckbox`, `AppSwitch`: Kontrol pemilihan boolean dan multi-select.
- `FormField`: Pembungkus kolom formulir lengkap dengan label, tanda bintang wajib (*), teks petunjuk (hint), dan pesan galat validasi.
- `CurrencyInput`: Input nilai uang berformat otomatis Rupiah Indonesia (memancarkan nilai murni numerik).
- `SearchInput`: Kolom pencarian dengan tombol bersihkan instan.
- `AppCard`, `StatCard`: Kartu kontainer dan kartu statistik metrik KPI dengan indikator tren.
- `PageHeader`: Judul halaman, deskripsi, breadcrumb navigasi, dan slot tombol aksi utama.
- `Tabs`, `Stepper`: Navigasi tab (line/pills) dan indikator tahapan langkah formulir bertahap.
- `DataTable`: Tabel data responsif server-side; otomatis beralih menjadi format kartu di bawah resolusi 640px.
- `FilterBar`: Baris filter komprehensif dengan tombol reset filter.
- `StatusBadge`: Pemetaan otomatis enum status bisnis ke varian semantik status (success/warning/danger/info).
- `EmptyState`: Ilustrasi dan ajakan bertindak (CTA) untuk keadaan tanpa data.
- `Skeleton`: Animasi pemuatan untuk elemen blok, baris teks, lingkaran avatar, dan tabel data.
- `Modal`, `Drawer`: Kotak dialog dan laci samping dengan perangkap fokus dan dismiss keyboard Esc.
- `ConfirmDialog`: Dialog konfirmasi aksi berbasis Promise (`useConfirm()`).
- `Toast`, `ToastHost`: Notifikasi melayang otomatis terhubung dengan session flash Laravel.
- `Avatar`: Penampil avatar foto atau inisial nama terformat.
- `ProgressBar`, `ProgressRing`: Indikator kemajuan horizontal dan lingkaran persentase.
- `Tooltip`: Petunjuk teks melayang berbasis hover/focus.
- `Dropdown`: Menu sematan melayang dengan penutup otomatis saat klik luar.

### 4.2 Komponen Domain Bisnis (`resources/js/Components/domain/`)
- `StudentPicker`: Komponen pencarian dan pemilihan siswa interaktif.
- `ClassSelect`, `SubjectSelect`: Dropdown khusus rombel kelas dan mata pelajaran.
- `AcademicYearBadge`: Lencana semester dan tahun pelajaran aktif.
- `AttendanceStatusToggle`: Tombol toggle cepat kehadiran siswa (Hadir / Izin / Sakit / Alpa).
- `MoneyText`: Teks berformat resmi Rupiah (`Rp X.XXX.XXX`).
- `DateText`: Teks berformat tanggal lokal Indonesia (`Asia/Jakarta`).
- `ScoreChip`: Lencana angka capaian nilai beserta predikat otomatis (A, B, C, D).
- `KopSurat`: Format resmi kop surat Majelis Dikdasmen SD Muhammadiyah Al Mujahidin Wonosari.
- `QrDisplay`: Render tajam QR Code berbasis SVG menggunakan `qrcode.vue`.
- `PrintSheet`: Pembungkus lembar kerja proporsional A4 untuk pratinjau dan pencetakan fisik/PDF.
- `NotificationBell`: Lonceng notifikasi realtime dengan polling otomatis.

### 4.3 Composables (`resources/js/composables/`)
- `useToast`: Pengirim notifikasi toast (`toast.success()`, `toast.error()`, `toast.warning()`, `toast.info()`).
- `useConfirm`: Pemanggilan dialog konfirmasi modal (`const ok = await confirm({ ... })`).
- `useFormat`: Utilitas pemformatan angka, persentase, tanggal Indonesia, dan mata uang Rupiah.
- `useDebouncedSearch`: Pengatur debounce input pencarian data.
- `usePolling`: Eksekutor polling berkala yang otomatis berhenti saat tab browser tidak aktif (Page Visibility API).
- `useAutosave`: Penyimpan formulir otomatis ber-debounce dengan indikator status ('idle' | 'saving' | 'saved' | 'error').
- `useTheme`: Pengelola tema aktif (`madinah`, `madinah-gelap`, `uji-gelap`).

---

## 5. Cara Menambah Komponen atau Tema Baru

1. **Menambah Tema Baru**:
   - Daftarkan variabel warna dan token pada `resources/css/tokens.css` di bawah selektor `[data-theme="nama-tema"]`.
   - Daftarkan tema pada array konfigurasi `config/theme.php`.
   - Jalankan `npm run check:contrast` untuk memastikan nilai kontras memenuhi standar WCAG AA.
2. **Menambah Komponen Baru**:
   - Jika primitif (tanpa aturan domain): letakkan di `resources/js/Components/ui/`.
   - Jika terikat entitas bisnis sekolah: letakkan di `resources/js/Components/domain/`.
   - Gunakan hanya token semantik Tailwind dan batas file maksimal ~300 baris.
   - Tambahkan demonstrasi komponen baru ke `resources/js/Pages/Dev/DesignSystem.vue`.

---

## 6. Pagar Pengaman Kualitas Otomatis

Setiap perubahan wajib melewati validasi:
```bash
# 1. Pengecekan kontras warna WCAG AA
npm run check:contrast

# 2. Pengecekan konsistensi UI, token semantik, dan layout
npm run lint:ui

# 3. Build bundle Vite
npm run build

# 4. Standar formatting PHP
vendor/bin/pint --test

# 5. Seluruh feature dan unit test
php artisan test
```

---

## 7. Definisi Selesai (Definition of Done)

Sebuah tugas/sprint dinyatakan selesai hanya jika:
1. Komponen, view, migration, model, request, policy, controller, route, dan seeder berfungsi utuh.
2. Seluruh script pemeriksa kualitas (`check-contrast`, `lint:ui`, `build`, `pint`, `test`) lulus 100% tanpa galat.
3. Dokumentasi `docs/CONVENTIONS.md` dan `docs/CHANGELOG.md` telah disinkronkan.
