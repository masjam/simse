# Dokumen Pengujian Penerimaan Pengguna (User Acceptance Testing / UAT)
## Sistem Informasi Manajemen Sekolah (SIMSE)
### SD Muhammadiyah Al Mujahidin Wonosari

Dokumen ini berisi panduan skenario pengujian fungsional terstruktur untuk **10 Peran (Role)** pengguna di lingkungan SD Muhammadiyah Al Mujahidin guna memastikan seluruh fitur memenuhi kriteria operasional sebelum go-live.

---

## Ringkasan Matriks Pengujian 10 Peran

| ID | Peran / Pengguna | Fokus Pengujian Utama | Total Kasus Uji |
|---|---|---|---|
| **UAT-ADM** | **Super Admin** | Manajemen Pengguna, RBAC, Modul, Audit Log, Backup, Keamanan 2FA | 8 Kasus |
| **UAT-OPS** | **Operator SIMSE** | Master Data Siswa, Kelas, Guru, Jadwal Bebas Bentrok, Verifikasi Dokumen | 7 Kasus |
| **UAT-GUR** | **Guru Pengajar** | Jurnal Mengajar, Nilai, CBT (Bank Soal/Monitoring), e-Raport, Kasbon | 9 Kasus |
| **UAT-GBK** | **Guru Bimbingan Konseling (BK)** | Pencatatan Konseling, Penanganan Pelanggaran, Permintaan Konseling Siswa | 5 Kasus |
| **UAT-SIS** | **Siswa** | KTS Digital, Jadwal, LMS & Tugas, Ujian CBT Anti-Curang, Gamifikasi, Pustaka | 9 Kasus |
| **UAT-ORT** | **Orang Tua / Wali Siswa** | Login OTP Tanpa Password, Pantau Nilai, Tagihan SPP, Dompet e-Kantin, Unduh Raport | 7 Kasus |
| **UAT-BEN** | **Bendahara Sekolah** | Pos Kasir POS, Invoice SPP, Payroll & Slip Gaji, Persetujuan Kasbon, Laporan Kantin | 8 Kasus |
| **UAT-STF** | **Staf Tata Usaha & Layanan** | Presensi Pegawai, Sirkulasi Perpustakaan, Kasbon Staf, Unduh Slip Gaji | 5 Kasus |
| **UAT-KAN** | **Pengelola Stand Kantin** | Menu Makanan/Minuman, Kasir POS Scan QR/RFID, Rekapitulasi Penjualan Harian | 5 Kasus |
| **UAT-PDB** | **Calon Siswa Baru (PPDB)** | Formulir Pendaftaran Online, Unggah Berkas Persyaratan, Bukti Kelulusan Ber-QR | 4 Kasus |
| **TOTAL** | **10 Peran Pengguna** | **Cakupan Seluruh Siklus Operasional Sekolah** | **67 Kasus Uji** |

---

## 1. Skenario UAT Super Admin (UAT-ADM)

- **UAT-ADM-01**: Masuk portal Super Admin melalui URL `/admin/login`.
  - *Ekspektasi*: Tampil dasbor statistik civitas sekolah dan menu modul terkelola.
- **UAT-ADM-02**: Mengaktifkan Autentikasi Dua Faktor (2FA TOTP RFC 6238) di menu Profil.
  - *Ekspektasi*: QR Code tampil, kode 6 digit terverifikasi, kode pemulihan tersimpan, login berikutnya mewajibkan 2FA challenge.
- **UAT-ADM-03**: Manajemen Pengguna dan Penetapan Role Spatie.
  - *Ekspektasi*: Akun baru dapat dibuat, peran dapat diubah tanpa konflik otorisasi.
- **UAT-ADM-04**: Pengaturan Modul Sekolah (Toggle Fitur LMS, CBT, e-Kantin, Gamifikasi).
  - *Ekspektasi*: Fitur yang dinonaktifkan di sistem setting secara instan menyembunyikan menu terkait pada portal pengguna.
- **UAT-ADM-05**: Audit Trail Terpusat (`/admin/audit`).
  - *Ekspektasi*: Seluruh mutasi data keuangan dan perubahan nilai tercatat rapi beserta IP, User Agent, dan diff perubahan.
- **UAT-ADM-06**: Pengelolaan Cadangan Basis Data (`/admin/backups`).
  - *Ekspektasi*: Pembuatan backup instan, daftar berkas backup, dan panduan restore berjalan sesuai SOP.
- **UAT-ADM-07**: Pengaturan Gateway WhatsApp (`/system/wa-gateway`).
  - *Ekspektasi*: Konfigurasi token Fonnte/Wablas tersimpan dan uji kirim pesan notifikasi berhasil terkirim ke ponsel.
- **UAT-ADM-08**: Pemulihan Data dari Keranjang Sampah (`/admin/trash`).
  - *Ekspektasi*: Data siswa yang terhapus secara lunak (*soft deleted*) dapat dipulihkan kembali secara utuh.

---

## 2. Skenario UAT Operator SIMSE (UAT-OPS)

- **UAT-OPS-01**: Input & Import Data Peserta Didik (Siswa).
  - *Ekspektasi*: Validasi NISN unik, tanggal lahir, dan penempatan kelas otomatis.
- **UAT-OPS-02**: Plotting Kelas dan Kenaikan Kelas Siswa.
  - *Ekspektasi*: Siswa berpindah rombel kelas tanpa kehilangan data historis nilai tahun ajaran sebelumnya.
- **UAT-OPS-03**: Penjadwalan Pelajaran Bebas Bentrok.
  - *Ekspektasi*: Sistem menolak bila guru atau ruangan yang sama dijadwalkan mengajar pada jam pelajaran yang bersamaan.
- **UAT-OPS-04**: Manajemen Data Master Guru & Tenaga Kependidikan.
  - *Ekspektasi*: Informasi NIP, bidang studi, dan foto profil tersimpan dengan benar.
- **UAT-OPS-05**: Verifikasi Berkas Ijazah & Dokumen Resmi Ber-QR Code.
  - *Ekspektasi*: Dokumen yang dipindai mengarah ke `/verifikasi/{token}` dengan tampilan kop resmi dan status validitas hijau.
- **UAT-OPS-06**: Pengelolaan Kalender Akademik & Hari Libur Sekolah.
  - *Ekspektasi*: Hari libur otomatis mengecualikan perhitungan presensi wajib.
- **UAT-OPS-07**: Cetak Kartu Tanda Siswa (KTS) Massal.
  - *Ekspektasi*: KTS memuat foto, identitas siswa, barcode/QR code, dan siap cetak format kartu fisik.

---

## 3. Skenario UAT Guru Pengajar (UAT-GUR)

- **UAT-GUR-01**: Pengisian Jurnal Mengajar Harian dan Absensi Kelas.
  - *Ekspektasi*: Guru dapat menandai status presensi siswa (Hadir/Sakit/Izin/Alpa) dan mencatat materi ajar.
- **UAT-GUR-02**: Pengunggahan Materi LMS & Penugasan Siswa.
  - *Ekspektasi*: Dokumen modul (PDF) terunggah aman ke disk privat, tenggat waktu penugasan terpasang.
- **UAT-GUR-03**: Penilaian Tugas Siswa & Integrasi Poin Gamifikasi.
  - *Ekspektasi*: Saat guru memberi nilai A (>=90), siswa otomatis menerima +50 Poin Gamifikasi.
- **UAT-GUR-04**: Pembuatan Bank Soal CBT 6 Tipe Soal & Impor Excel.
  - *Ekspektasi*: Mendukung PG, Ganda Kompleks, Benar/Salah, Menjodohkan, Isian Rumpang, dan Esai.
- **UAT-GUR-05**: Live Monitoring Proctor Ujian CBT (`/guru/cbt/monitor`).
  - *Ekspektasi*: Proctor dapat melihat status pengerjaan 175 siswa secara *real-time*, menambah waktu, dan membuka blokir pelanggaran.
- **UAT-GUR-06**: Penilaian Esai Manual & Rilis Nilai CBT.
  - *Ekspektasi*: Guru mengoreksi jawaban esai, sistem menghitung skor akhir proporsional.
- **UAT-GUR-07**: Penginputan Nilai Akhir & Pencetakan e-Raport Kurikulum Merdeka.
  - *Ekspektasi*: Nilai harian, asesmen, dan catatan deskriptif capaian pembelajaran terakumulasi ke lembar rapor resmi ber-QR.
- **UAT-GUR-08**: Pengajuan Kasbon Mandiri Guru (`/guru/kasbon`).
  - *Ekspektasi*: Guru mengajukan pinjaman darurat, memilih tenor 1-12 bulan, dan melihat simulasi potongan gaji.
- **UAT-GUR-09**: Akses & Unduh Rincian Slip Gaji Bulanan.
  - *Ekspektasi*: Rincian honor jam mengajar (JPL), tunjangan, dan potongan kasbon tampil transparan.

---

## 4. Skenario UAT Guru Bimbingan Konseling (UAT-GBK)

- **UAT-GBK-01**: Pencatatan Pelanggaran Tata Tertib Siswa (`/guru/bk`).
  - *Ekspektasi*: Poin pelanggaran terhitung otomatis dan notifikasi terkirim ke orang tua via WhatsApp.
- **UAT-GBK-02**: Penanganan Permintaan Konseling Mandiri dari Siswa.
  - *Ekspektasi*: Guru BK menerima notifikasi permohonan bimbingan dan mengatur jadwal konseling tertutup.
- **UAT-GBK-03**: Pencatatan Catatan Perkembangan & Prestasi Non-Akademik.
  - *Ekspektasi*: Prestasi membanggakan memberikan poin akhlak mulia dan membuka lencana teladan.
- **UAT-GBK-04**: Pemantauan Rekapitulasi Pembinaan Siswa Berkelanjutan.
  - *Ekspektasi*: Tren perilaku siswa dapat dianalisis untuk pendampingan wali kelas.
- **UAT-GBK-05**: Kerahasiaan Rekam Medis / Catatan Konseling Tertutup.
  - *Ekspektasi*: Catatan bimbingan hanya dapat diakses oleh Guru BK dan Super Admin.

---

## 5. Skenario UAT Siswa (UAT-SIS)

- **UAT-SIS-01**: Akses Dasbor Siswa & Tampilan Mode Anak (Level 1-3 vs 4-6).
  - *Ekspektasi*: Siswa kelas bawah menerima antarmuka kartu bergambar ramah anak, kelas atas antarmuka informatif.
- **UAT-SIS-02**: Tampilan Kartu Tanda Siswa (KTS) Digital & QR Code Presensi.
  - *Ekspektasi*: QR code KTS dapat dipindai untuk presensi mandiri dan transaksi kantin.
- **UAT-SIS-03**: Akses Materi Mingguan & Pengumpulan Tugas LMS.
  - *Ekspektasi*: Siswa mengunduh materi dan mengunggah berkas jawaban tugas (PDF/JPG) dengan batas ukuran aman.
- **UAT-SIS-04**: Pengerjaan Ujian CBT Serentak Anti-Curang (Mode Layar Penuh).
  - *Ekspektasi*: Timer hitung mundur sinkron server, autosave berkala lancar tanpa lag, peringatan pelanggaran saat beralih tab/layar.
- **UAT-SIS-05**: Pusat Gamifikasi Pembelajaran (`/siswa/lms`).
  - *Ekspektasi*: Bar kemajuan level, 7 koleksi lencana prestasi, dan papan peringkat sahabat kelas tampil atraktif.
- **UAT-SIS-06**: Peminjaman Buku & Baca E-Book Perpustakaan Digital (`/siswa/perpustakaan`).
  - *Ekspektasi*: Katalog buku terbuka, membaca e-book memberi poin apresiasi literasi.
- **UAT-SIS-07**: Permohonan Bimbingan Konseling Ramah Siswa (`/siswa/konseling`).
  - *Ekspektasi*: Siswa dapat mencurahkan pesan bantuan kepada guru BK dengan privasi terjaga.
- **UAT-SIS-08**: Riwayat Saldo Jajan Sehat e-Kantin.
  - *Ekspektasi*: Siswa melihat saldo dompet digital dan daftar pembelian makanan sehat di sekolah.
- **UAT-SIS-09**: Mode Offline PWA.
  - *Ekspektasi*: Saat koneksi internet terputus, halaman cadangan informatif offline SIMSE tetap dapat dibuka.

---

## 6. Skenario UAT Orang Tua / Wali Siswa (UAT-ORT)

- **UAT-ORT-01**: Masuk Jalur Cepat Tanpa Password Menggunakan OTP WhatsApp (`/ortu/otp`).
  - *Ekspektasi*: Memasukkan nomor WhatsApp & tanggal lahir anak, menerima 6 digit OTP via WhatsApp, dan berhasil masuk tanpa repot mengingat kata sandi.
- **UAT-ORT-02**: Fitur *Child Switcher* (Bagi Wali Murid dengan > 1 Anak di Sekolah).
  - *Ekspektasi*: Peralihan anak berlangsung instan tanpa perlu keluar portal.
- **UAT-ORT-03**: Pemantauan Kehadiran Harian & Rekap Mutaba'ah Tahfidz.
  - *Ekspektasi*: Jam masuk dan kepulangan anak terpantau akurat beserta capaian hafalan Al-Qur'an.
- **UAT-ORT-04**: Informasi Tagihan SPP & Konfirmasi Pembayaran.
  - *Ekspektasi*: Nominal tagihan bulanan tampil jelas beserta petunjuk transfer rekening resmi sekolah.
- **UAT-ORT-05**: Pengelolaan Batas Jajan Harian Saldo e-Kantin Anak.
  - *Ekspektasi*: Orang tua dapat mengatur limit nominal maksimal jajan per hari agar anak terbiasa hemat.
- **UAT-ORT-06**: Pengaturan Preferensi Notifikasi WhatsApp (`NotificationPreferencesModal`).
  - *Ekspektasi*: Orang tua dapat memilih notifikasi mana yang ingin diterima (Presensi, SPP, Nilai, CBT, Pelanggaran).
- **UAT-ORT-07**: Tanda Tangan Digital Rapor & Unduh Lembar e-Raport Anak.
  - *Ekspektasi*: Rapor resmi ber-kop ganda (Dikdasmen di kiri & Sekolah di kanan) dapat diunduh dalam format PDF asli.

---

## 7. Skenario UAT Bendahara Sekolah (UAT-BEN)

- **UAT-BEN-01**: Dasbor Keuangan Terpadu & Pembukuan Kas Sekolah.
  - *Ekspektasi*: Grafik arus kas masuk dari SPP dan kasir terpantau *real-time*.
- **UAT-BEN-02**: Point of Sales (POS) Kasir Kas Sekolah (`/bendahara/kasir`).
  - *Ekspektasi*: Transaksi pembayaran SPP manual, cetak struk penerimaan resmi, dan pembaruan status tagihan otomatis menjadi `Lunas`.
- **UAT-BEN-03**: Pembuatan Tagihan SPP Massal Bulanan (`GenerateBillModal`).
  - *Ekspektasi*: Tagihan terbit serentak untuk seluruh siswa terdaftar pada kelas yang dipilih.
- **UAT-BEN-04**: Manajemen Dompet Digital Siswa (Top Up Saldo & Reset PIN).
  - *Ekspektasi*: Penambahan saldo kasir dompet siswa disertai bukti cetak struk kas masuk.
- **UAT-BEN-05**: Generate Payroll & Perhitungan Honor Mengajar Guru.
  - *Ekspektasi*: Sistem menghitung otomatis gaji pokok, honor per jam pelajaran (JPL), tunjangan kehadiran, dan iuran BPJS.
- **UAT-BEN-06**: Otomasi Pemotongan Angsuran Kasbon pada Slip Gaji.
  - *Ekspektasi*: Slip gaji otomatis memuat komponen potongan `Angsuran Kasbon (#X/Y)` dan mengupdate status angsuran menjadi `Lunas`.
- **UAT-BEN-07**: Persetujuan / Penolakan Pengajuan Kasbon Pegawai (`/bendahara/kasbon`).
  - *Ekspektasi*: Bendahara memvalidasi sisa piutang sekolah sebelum menyetujui pengajuan kasbon baru.
- **UAT-BEN-08**: Rekapitulasi & Pencairan Saldo Bagi Hasil Stand Kantin (`/bendahara/kantin-report`).
  - *Ekspektasi*: Rekap penjualan per stand kantin dihitung akurat untuk pencairan berkala.

---

## 8. Skenario UAT Staf Tata Usaha & Layanan (UAT-STF)

- **UAT-STF-01**: Presensi Masuk & Pulang Pegawai Sekolah.
  - *Ekspektasi*: Waktu kehadiran tercatat ke dalam sistem attendance untuk kalkulasi insentif bulanan.
- **UAT-STF-02**: Pengelolaan Sirkulasi Perpustakaan Sekolah (`/staf/perpustakaan`).
  - *Ekspektasi*: Staf memindai barcode buku dan kartu siswa untuk mencatat peminjaman serta pengembalian buku.
- **UAT-STF-03**: Pemantauan Keterlambatan Pengembalian Buku & Denda.
  - *Ekspektasi*: Sistem mendeteksi peminjaman melewati batas waktu dan memunculkan badge peringatan.
- **UAT-STF-04**: Pengajuan Kasbon Tenaga Kependidikan (`/staf/kasbon`).
  - *Ekspektasi*: Staf tata usaha dapat mengajukan pinjaman darurat sesuai plafon yang berlaku.
- **UAT-STF-05**: Pengunduhan Slip Gaji Digital Tenaga Kependidikan (`/staf/slip-gaji`).
  - *Ekspektasi*: Rincian hak penerimaan dan potongan gaji dapat diunduh mandiri dalam format PDF resmi.

---

## 9. Skenario UAT Pengelola Stand Kantin (UAT-KAN)

- **UAT-KAN-01**: Manajemen Menu Makanan & Minuman Sehat Sekolah.
  - *Ekspektasi*: Menambah menu, mengatur harga jual, stok harian, dan label gizi makanan sehat.
- **UAT-KAN-02**: Kasir Kantin Transaksi Cepat via Scan QR KTS Siswa (`/kantin/scan-qr`).
  - *Ekspektasi*: Kamera memindai QR code siswa, menampilkan nama & sisa saldo dompet seketika.
- **UAT-KAN-03**: Kasir Kantin Transaksi via Kartu RFID (`/kantin/rfid-pos`).
  - *Ekspektasi*: Tempel kartu pada reader RFID, saldo terpotong instan tanpa antrean panjang.
- **UAT-KAN-04**: Validasi Batas Maksimal Jajan Harian & Kecukupan Saldo.
  - *Ekspektasi*: Transaksi ditolak jika saldo tidak mencukupi atau telah melewati limit harian yang ditetapkan orang tua.
- **UAT-KAN-05**: Rekapitulasi Transaksi & Omzet Harian Stand Kantin.
  - *Ekspektasi*: Pengelola melihat total omzet penjualan bersih hari ini siap dicairkan ke bendahara.

---

## 10. Skenario UAT Calon Siswa Baru / PPDB (UAT-PDB)

- **UAT-PDB-01**: Pendaftaran Akun & Pengisian Formulir PPDB Online (`/ppdb/register`).
  - *Ekspektasi*: Calon wali murid mengisi identitas calon siswa, asal TK/RA, data orang tua, dan pilihan program kelas.
- **UAT-PDB-02**: Pengunggahan Berkas Persyaratan (Akta Kelahiran & Kartu Keluarga).
  - *Ekspektasi*: Berkas tervalidasi tipe MIME dan tersimpan dengan enkripsi pada disk privat sekolah.
- **UAT-PDB-03**: Pemantauan Status Seleksi & Kelulusan Berkas.
  - *Ekspektasi*: Halaman status memberikan indikator jelas tahapan verifikasi berkas oleh panitia PPDB.
- **UAT-PDB-04**: Pengunduhan Bukti Tanda Pendaftaran & Kelulusan Resmi Ber-QR Code (`/ppdb/success`).
  - *Ekspektasi*: Lembar bukti registrasi memuat nomor pendaftaran, foto, dan QR code keabsahan verifikasi.

---

## Lembar Pengesahan UAT

Pengujian penerimaan sistem ini dinyatakan valid dan disetujui untuk diimplementasikan ke server produksi:

| Peran Tim Penguji | Nama Pejabat / Perwakilan | Tanda Tangan | Tanggal |
|---|---|---|---|
| **Kepala Sekolah** | SD Muhammadiyah Al Mujahidin | `[ .............................. ]` | .... / .... / 2026 |
| **Ketua Tim Pengembang SIMSE** | Lead Software Engineer | `[ .............................. ]` | .... / .... / 2026 |
| **Koordinator Tata Usaha & IT** | Koordinator IT Sekolah | `[ .............................. ]` | .... / .... / 2026 |
| **Bendahara Sekolah** | Bendahara Pengeluaran | `[ .............................. ]` | .... / .... / 2026 |
| **Perwakilan Komite / Orang Tua** | Perwakilan Wali Murid | `[ .............................. ]` | .... / .... / 2026 |

