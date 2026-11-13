<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('registration_number')->unique();
            $table->string('student_name');
            $table->string('nik', 20);
            $table->string('nisn', 20)->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->string('birth_place');
            $table->date('birth_date');
            $table->text('address');
            $table->string('previous_school')->nullable(); // TK / PAUD asal

            // Data Orang Tua
            $table->string('parent_name');
            $table->string('parent_phone', 30);
            $table->string('parent_email')->nullable();
            $table->string('parent_job')->nullable();

            // Berkas Dokumen Privat (disimpan di disk local 'private' / 'local')
            $table->string('kk_path')->nullable(); // Kartu Keluarga
            $table->string('birth_certificate_path')->nullable(); // Akta Kelahiran
            $table->string('ijazah_path')->nullable(); // Ijazah / SKL TK

            $table->enum('status', ['Menunggu Verifikasi', 'Diterima', 'Ditolak'])->default('Menunggu Verifikasi');
            $table->text('admin_notes')->nullable();

            // Jika sudah diterima dan dikonversi menjadi siswa aktif
            $table->foreignUuid('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('student_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('parent_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_registrations');
    }
};
