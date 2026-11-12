<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_class_id')->constrained()->cascadeOnDelete();
            $table->string('nisn')->unique();
            $table->string('nik')->unique();
            $table->string('qr_code')->nullable();
            $table->text('face_data')->nullable(); // Untuk menyimpan tensor/vektor wajah dari faceAPI
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
