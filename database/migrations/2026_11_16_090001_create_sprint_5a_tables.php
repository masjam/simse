<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Permintaan Konseling Guru BK oleh Siswa
        if (! Schema::hasTable('counseling_requests')) {
            Schema::create('counseling_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
                $table->string('category')->default('Belajar'); // Belajar, Teman Sebaya, Keluarga, Kebiasaan, Lainnya
                $table->string('preferred_schedule')->nullable(); // Pagi saat istirahat, Siang setelah pulang, dll.
                $table->text('notes');
                $table->string('status')->default('Diajukan'); // Diajukan, Dijadwalkan, Selesai, Dibatalkan
                $table->dateTime('scheduled_at')->nullable();
                $table->text('counselor_notes')->nullable();
                $table->foreignUuid('counselor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Preferensi Notifikasi Orang Tua
        if (! Schema::hasTable('parent_notification_preferences')) {
            Schema::create('parent_notification_preferences', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('channel_wa')->default(true);
                $table->boolean('channel_in_app')->default(true);
                $table->boolean('notify_attendance')->default(true);
                $table->boolean('notify_finance')->default(true);
                $table->boolean('notify_grades')->default(true);
                $table->boolean('notify_discipline')->default(true);
                $table->timestamps();

                $table->unique('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_notification_preferences');
        Schema::dropIfExists('counseling_requests');
    }
};
