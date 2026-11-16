# Panduan Pemulihan Basis Data & Berkas (Disaster Recovery & Restore)
## SIMSE SD Muhammadiyah Al Mujahidin Wonosari

Dokumen ini menjelaskan prosedur pemulihan (*recovery / restore*) sistem SIMSE dari berkas arsip cadangan (`.zip`) yang dihasilkan oleh paket `spatie/laravel-backup`.

---

## 1. Lokasi Berkas Cadangan

Berkas cadangan harian disimpan pada:
- **Penyimpanan Lokal**: `storage/app/SIMSE_AlMujahidin/*.zip`
- **Isi Cadangan**:
  - `db-dumps/mysql-simse.sql`: Salinan lengkap seluruh tabel dan relasi basis data MySQL.
  - `storage/app/private/`: Seluruh berkas lampiran privat (bukti transfer SPP, berkas scan persyaratan PPDB, tugas siswa, materi modul guru).

---

## 2. Prosedur Pemulihan (Restore) Langkah-demi-Langkah

### Skenario: Pemulihan Basis Data ke Database Kosong (Laragon / Windows Server)

#### Langkah 1: Ekstrak Berkas Arsip `.zip`
Pilih arsip tanggal terbaru dari folder `storage/app/SIMSE_AlMujahidin/`, lalu ekstrak berkas `.zip` tersebut:
```powershell
# Menggunakan PowerShell untuk mengekstrak
Expand-Archive -Path "storage/app/SIMSE_AlMujahidin/2026-11-13-01-00-00.zip" -DestinationPath "temp_restore/"
```

#### Langkah 2: Buat Database Kosong / Bersihkan Database Lama
Buka terminal MySQL di Laragon:
```sql
DROP DATABASE IF EXISTS simse;
CREATE DATABASE simse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Langkah 3: Impor Dump SQL ke Database Baru
Jalankan perintah impor melalui command prompt atau PowerShell:
```powershell
# Masuk ke folder temp_restore/db-dumps/
mysql -u root -p simse < temp_restore/db-dumps/mysql-simse.sql
```
*Catatan: Pada instalasi default Laragon di Windows, password root biasanya kosong (`mysql -u root simse < temp_restore/db-dumps/mysql-simse.sql`).*

#### Langkah 4: Pulihkan Berkas Privat
Salin kembali folder berkas privat yang diekstrak ke direktori aplikasi:
```powershell
Copy-Item -Recurse -Force temp_restore/storage/app/private/* storage/app/private/
```

#### Langkah 5: Bersihkan Cache Aplikasi & Validasi
Jalankan perintah optimasi Laravel:
```bash
php artisan optimize:clear
php artisan migrate --force
php artisan test --compact
```
Pastikan seluruh pengujian melewati verifikasi dengan hasil hijau (*all tests passing*).

---

## 3. Konfigurasi Otomatisasi Windows Task Scheduler (Laragon)

Agar perintah `php artisan schedule:run` dieksekusi setiap menit secara otomatis di sistem operasi Windows (Laragon):

1. Tekan tombol **Windows + R**, ketik `taskschd.msc`, lalu tekan **Enter**.
2. Pada panel kanan, klik **Create Basic Task...** (Buat Tugas Dasar).
3. Beri nama: `SIMSE Laravel Scheduler`.
4. Pilih pemicu (**Trigger**): **Daily** (Harian), mulai hari ini.
5. Pada pilihan aksi (**Action**): Pilih **Start a program**.
6. Isi kolom:
   - **Program/script**: `C:\laragon\bin\php\php-8.3.x-Win32-...\php.exe` (sesuaikan dengan path PHP Laragon aktif).
   - **Add arguments**: `artisan schedule:run`
   - **Start in**: `H:\laragon\www\simse` (path root proyek SIMSE).
7. Klik **Finish**.
8. Buka tab **Properties** dari tugas yang baru dibuat, masuk ke tab **Triggers**, klik **Edit**:
   - Centang opsi **Repeat task every**: `1 minute` (Ulangi tugas tiap 1 menit).
   - Untuk durasi (**for a duration of**): `Indefinitely` (Tanpa batas).
9. Klik **OK** dan aktifkan tugas.

Dengan konfigurasi ini, pencadangan otomatis akan berjalan setiap hari pada pukul **01:00 WIB** dan proses pembersihan retensi berjalan pada pukul **02:00 WIB**.

