<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('violation_point_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete(); // Guru pelapor / Guru BK
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('status')->default('Tercatat'); // e.g. Tercatat, SP 1, SP 2, SP 3
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_violations');
    }
};
