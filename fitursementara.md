# Daftar Fitur SIMSE (Kondisi Saat Ini)

Dokumen ini berisi inventaris seluruh fitur yang sudah tersedia di codebase **SIMSE** berdasarkan implementasi saat ini, dikelompokkan antara fitur yang sudah terhubung penuh dengan database/backend dan fitur yang masih berupa tampilan UI / mock data / placeholder.

---

## 1. Fondasi Sistem, Keamanan & Autentikasi

- **Arsitektur Aplikasi:** Laravel + Inertia.js (Vue 3) + Tailwind CSS + Spatie Laravel Permission (RBAC).
- **Primary Key UUID (36 Karakter):** Seluruh tabel domain, tabel `users`, serta tabel `roles` & `permissions` menggunakan format UUID (`HasUuids`).
- **Penyimpanan File Privat:** Berkas sensitif (seperti lampiran materi LMS dan bukti transfer pembayaran) disimpan di disk `local` (`storage/app/private`) dan dilayani melalui route controller yang terproteksi autentikasi dan role.
- **Landing Page Sekolah (`/`):** Halaman publik (`Welcome.vue`) dilengkapi integrasi feed Instagram sekolah (`InstagramService`).
- **Jalur Login Terpisah per Role (`/{role}/login`):**
  - Mendukung jalur login khusus untuk role: `siswa`, `guru`, `ortu`, `admin`, `staf`, `bendahara`, `kantin`, `ppdb`, dan `bk`, serta `/login` standar.
  - Validasi kecocokan hak akses pada `AuthenticatedSessionController` (menolak login jika akun tidak sesuai portal yang dipilih) dan pengalihan otomatis ke dashboard role masing-masing.
- **Manajemen Akun & Profil (`/profile`, `/register`, Reset Password, Verifikasi Email):** Edit profil, ubah kata sandi, hapus akun, lupa/reset kata sandi, serta verifikasi email.
- **Role Pengguna (`RoleSeeder`):** `Super Admin`, `Admin Tata Usaha`, `Guru Pengajar`, `Guru BK`, `Bendahara`, `Operator`, `Staf Sekolah`, `Siswa`, `Orang Tua`, `Calon Siswa Baru`, dan `Pengelola Stand Kantin`.

---

## 2. Portal Super Admin (`/admin/*`) — *Terhubung Database*

- **Dashboard Admin (`/admin/dashboard`):** Ringkasan statistik total pengguna, jumlah guru, dan jumlah siswa.
- **Manajemen Data Guru & Staf (`/admin/teachers`, `/admin/staffs`):** Daftar akun guru dan pegawai dengan fitur pencarian dan paginasi.
- **Ekspor & Impor Pengguna (`/admin/users/export`, `/admin/users/import`, `/admin/users/template`):**
  - Ekspor data pengguna ke file Excel (`.xlsx`) melalui `UsersExport`.
  - Impor massal pengguna dari file Excel/CSV melalui `UsersImport`.
  - Unduh template CSV terpisah untuk data Pegawai, Siswa, dan Orang Tua.
- **Manajemen Data Siswa (`/admin/students`):**
  - CRUD data siswa beserta biodata orang tua (`nama_ayah`, `nama_ibu`, `pekerjaan_ayah`, `pekerjaan_ibu`, `no_telp_ortu`).
  - Pencatatan riwayat prestasi siswa (`StudentAchievement`) dan data periodik siswa (`StudentPeriodic`).
  - Cetak profil/biodata siswa (`/admin/students/{user}/print`).
- **Master Data Akademik & Kurikulum:**
  - **Tingkat Kelas (`/admin/tingkat`):** CRUD tingkat pendidikan.
  - **Kelas / Rombel (`/admin/student-classes`):** CRUD rombongan belajar beserta relasi tingkat dan wali kelas.
  - **Mata Pelajaran (`/admin/subjects`):** CRUD daftar mata pelajaran.
  - **Penugasan Mengajar (`/admin/teaching-assignments`):** Plotting guru pengajar, mata pelajaran, rombel, dan jumlah jam pelajaran (JPL).
  - **Jadwal Pelajaran (`/admin/schedules`):** Pengelolaan jadwal per kelas/guru serta generator jadwal otomatis (`/admin/schedules/auto-generate`).
- **Manajemen Modul & Pengaturan (`/admin/modules`, `/admin/settings`):** Halaman manajemen status modul dan pengaturan sistem.

---

## 3. Portal Guru & Pendidik (`/guru/*`) — *Terhubung Database*

- **Dashboard Guru (`/guru/dashboard`):** Ringkasan jadwal mengajar hari ini, kelas yang diampu, daftar tugas, dan statistik pengajar.
- **Jadwal Mengajar (`/guru/jadwal`):** Daftar jadwal mengajar guru dari data `TeachingSchedule`.
- **Absensi Siswa (`/guru/absensi`):** Pencatatan kehadiran siswa per kelas, mata pelajaran, dan tanggal (`Attendance`).
- **Penilaian & e-Raport (`/guru/nilai`, `/guru/raport`):**
  - Input dan simpan nilai siswa (`Grade`).
  - Ekspor daftar nilai ke Excel (`NilaiExport`) dan impor nilai dari Excel (`NilaiImport`).
  - Tampilan rekap nilai / e-Raport siswa per kelas.
- **LMS & Tugas Online (`/guru/lms`, `/guru/tugas`):**
  - Upload, unduh aman (dari storage privat), dan hapus materi pembelajaran (`Lesson`).
  - Pembuatan tugas (`Assignment`) dan pemberian nilai atas pengumpulan tugas siswa (`Submission`).
- **CBT & Bank Soal (`/guru/cbt`):**
  - Pembuatan ujian (`Exam`), penambahan soal pilihan ganda beserta opsi jawaban (`Question`, `Option`), dan toggle status aktif ujian.
- **Bimbingan Konseling / BK (`/guru/bk`):**
  - Pencatatan pelanggaran siswa (`StudentViolation`) berdasarkan daftar poin pelanggaran (`ViolationPoint`).
  - Pencatatan sesi bimbingan/konseling (`CounselingSession`).
- **Perpustakaan & Payroll Guru (`/guru/perpustakaan`, `/guru/payroll`):** Katalog buku perpustakaan (`Book`) dan tampilan slip gaji guru (`Payroll`).

---

## 4. Portal Bendahara Sekolah (`/bendahara/*`) — *Terhubung Database*

- **Dashboard Keuangan (`/bendahara/dashboard`):**
  - KPI langsung dari database: total kas masuk, SPP terkumpul, total tunggakan SPP, payroll terbayar, jumlah transfer menunggu verifikasi, payroll berstatus draft, dan total saldo dompet digital siswa.
  - Daftar transaksi pembayaran dan riwayat payroll terbaru.
- **Kasir Pembayaran & Verifikasi Transfer (`/bendahara/kasir`):**
  - Pencarian siswa langsung, pemilihan tagihan belum lunas/sebagian, format Rupiah otomatis, dan konversi nominal ke *terbilang* Bahasa Indonesia.
  - Pembayaran menggunakan metode `Tunai`, `Transfer Bank`, atau `Dompet Digital` dalam transaksi atomik database.
  - Unggah bukti transfer ke folder privat `storage/app/private/receipts` dan preview/unduh aman via route `bendahara.payments.receipt`.
  - Tab verifikasi transfer berstatus pending (`Berhasil` / `Ditolak`), tab riwayat transaksi, dan fitur cetak struk termal.
- **Manajemen SPP & Pos Tagihan (`/bendahara/spp`):**
  - CRUD Master Pos Tagihan (`BillType`: `Bulanan`, `Tahunan`, `Sekali Bayar`).
  - Generate tagihan (`Bill`) massal per kelas atau seluruh kelas secara idempoten.
  - Filter, pemantauan, dan penghapusan tagihan siswa.
  - Laporan tunggakan siswa dan fitur broadcast pengingat tagihan WhatsApp yang tercatat di `WhatsappLog`.
- **Tabungan & Dompet Digital Siswa (`/bendahara/dompet`):**
  - Daftar dompet digital (`Wallet`) dan saldo seluruh siswa.
  - Transaksi `Setoran` dan `Penarikan` dengan validasi saldo agar tidak minus.
  - Pengaturan/reset PIN 6 digit (tersimpan dalam bentuk hash) dan riwayat mutasi `WalletTransaction`.
- **Payroll & Penggajian Pegawai (`/bendahara/payroll`):**
  - Filter periode bulan dan tahun.
  - Generate payroll massal otomatis berdasarkan gaji pokok, tunjangan mengajar (`TeachingSchedule.jpl`), jumlah kehadiran (`Attendance`), dan potongan BPJS.
  - CRUD komponen gaji (`PayrollComponent` bertipe `Penerimaan` atau `Potongan`) dengan perhitungan ulang total gaji otomatis.
  - Alur persetujuan status payroll (`Draft` -> `Menunggu Persetujuan` -> `Dibayar`) dan modal cetak slip gaji digital.

---

## 5. Fitur / Halaman Tahap UI, Mock Data, dan Placeholder

Fitur berikut sudah memiliki route dan/atau halaman Vue, namun backend-nya masih menggunakan data statis (mock) atau menampilkan halaman placeholder untuk pengembangan tahap berikutnya:

- **Portal Staf Sekolah (`/staf/*`):** Halaman UI `Staff/Dashboard`, `Staff/Absensi`, `Staff/Perpustakaan`, dan `Staff/SlipGaji` (render halaman via `StaffController`).
- **Kartu Tanda Siswa Digital (`/kts`):** Halaman UI `Student/KTS`.
- **Modul Akademik Umum (`/akademik/*`):**
  - `/akademik/absensi` (`AkademikController`): Menggunakan data mock siswa untuk simulasi UI absensi.
  - `/akademik/jadwal` dan `/akademik/nilai`: Menggunakan `Placeholder.vue`.
- **Modul CBT & e-Kantin Umum (`/learning/cbt`, `/finansial/kantin`):**
  - `CBTController` (`CBT/Index.vue`) dan `KantinController` (`Kantin/Index.vue`): Menggunakan data mock ujian aktif, stand kantin, menu, dan saldo dompet untuk demonstrasi UI.
- **Placeholder Modul Lanjutan (`Placeholder.vue`):**
  - LMS Siswa (`/learning/lms`) & Perpustakaan Digital Umum (`/learning/perpustakaan`)
  - Portal Tagihan SPP, Dompet Digital, & Payroll Siswa/Umum (`/finansial/spp`, `/finansial/dompet`, `/finansial/payroll`)
  - Layanan BK Umum (`/layanan/bk`) & PPDB Online (`/layanan/ppdb`)
  - Pengaturan Modul (`/sistem/pengaturan`), WhatsApp Gateway (`/sistem/wa-gateway`), dan CMS Website Sekolah (`/sistem/website`)

