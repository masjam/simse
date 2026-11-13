<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_class_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teaching_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->unsignedInteger('meeting_number')->default(1);
            $table->string('topic');
            $table->text('summary');
            $table->text('activities')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
