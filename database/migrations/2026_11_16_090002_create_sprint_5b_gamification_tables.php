<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('name', 255);
            $table->text('description');
            $table->string('icon', 50)->default('🏆');
            $table->string('category', 100)->default('Akademik');
            $table->integer('points_required')->default(0);
            $table->integer('threshold_count')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_badges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('awarded_at')->useCurrent();
            $table->timestamps();

            $table->unique(['student_id', 'badge_id']);
        });

        Schema::create('point_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('event_type', 100);
            $table->integer('points');
            $table->string('reference_type', 255)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_events');
        Schema::dropIfExists('student_badges');
        Schema::dropIfExists('badges');
    }
};
