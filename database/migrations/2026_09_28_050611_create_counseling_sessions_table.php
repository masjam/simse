<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained()->cascadeOnDelete(); // Guru BK
            $table->enum('type', ['Pribadi', 'Kelompok', 'Karir', 'Akademik']);
            $table->date('date');
            $table->text('problem');
            $table->text('solution')->nullable();
            $table->enum('status', ['Menunggu', 'Berlangsung', 'Selesai'])->default('Selesai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};
