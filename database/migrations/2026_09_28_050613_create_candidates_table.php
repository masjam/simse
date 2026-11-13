<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admission_wave_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('nisn')->unique()->nullable();
            $table->string('prev_school')->nullable();
            $table->string('phone')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('documents')->nullable(); // path folder berkas
            $table->enum('form_status', ['Draft', 'Menunggu Verifikasi', 'Lengkap'])->default('Draft');
            $table->enum('payment_status', ['Belum Bayar', 'Lunas'])->default('Belum Bayar');
            $table->boolean('is_accepted')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
