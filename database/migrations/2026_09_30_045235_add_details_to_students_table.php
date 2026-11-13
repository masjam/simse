<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('kewarganegaraan')->nullable()->default('ID');
            $table->string('no_kk')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_registrasi_akta_lahir')->nullable();
            $table->string('berkebutuhan_khusus')->nullable();
            $table->string('agama')->nullable();
            $table->string('alamat_jalan')->nullable();
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('dusun')->nullable();
            $table->string('desa_kelurahan')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('lintang')->nullable();
            $table->string('bujur')->nullable();
            $table->string('tempat_tinggal')->nullable();
            $table->string('moda_transportasi')->nullable();
            $table->integer('anak_ke')->nullable();

            // Data Ayah
            $table->string('nik_ayah')->nullable();
            $table->string('tahun_lahir_ayah', 4)->nullable();
            $table->string('pendidikan_ayah')->nullable();
            $table->string('berkebutuhan_khusus_ayah')->nullable();
            $table->string('penghasilan_ayah')->nullable();

            // Data Ibu
            $table->enum('status_hidup_ibu', ['Hidup', 'Wafat'])->nullable();
            $table->string('nik_ibu')->nullable();
            $table->string('tahun_lahir_ibu', 4)->nullable();
            $table->string('pendidikan_ibu')->nullable();
            $table->string('berkebutuhan_khusus_ibu')->nullable();
            $table->string('penghasilan_ibu')->nullable();

            // Data Wali
            $table->boolean('mempunyai_wali')->default(false);
            $table->string('nama_wali')->nullable();
            $table->string('nik_wali')->nullable();
            $table->string('tahun_lahir_wali', 4)->nullable();
            $table->string('pendidikan_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('penghasilan_wali')->nullable();

            // Kontak
            $table->string('no_telepon_rumah')->nullable();
            $table->string('no_hp')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'kewarganegaraan', 'no_kk', 'tempat_lahir', 'tanggal_lahir',
                'no_registrasi_akta_lahir', 'berkebutuhan_khusus', 'agama', 'alamat_jalan',
                'rt', 'rw', 'dusun', 'desa_kelurahan', 'kode_pos', 'lintang', 'bujur',
                'tempat_tinggal', 'moda_transportasi', 'anak_ke',
                'nik_ayah', 'tahun_lahir_ayah', 'pendidikan_ayah',
                'berkebutuhan_khusus_ayah', 'penghasilan_ayah',
                'status_hidup_ibu', 'nik_ibu', 'tahun_lahir_ibu', 'pendidikan_ibu',
                'berkebutuhan_khusus_ibu', 'penghasilan_ibu',
                'mempunyai_wali', 'nama_wali', 'nik_wali', 'tahun_lahir_wali', 'pendidikan_wali',
                'pekerjaan_wali', 'penghasilan_wali',
                'no_telepon_rumah', 'no_hp',
            ]);
        });
    }
};
