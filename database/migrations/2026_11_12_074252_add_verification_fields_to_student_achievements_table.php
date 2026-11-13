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
        Schema::table('student_achievements', function (Blueprint $table): void {
            $table->string('category')->nullable()->after('name');
            $table->string('organizer')->nullable()->after('year');
            $table->date('achievement_date')->nullable()->after('organizer');
            $table->string('certificate_path')->nullable()->after('description');
            $table->string('certificate_original_name')->nullable()->after('certificate_path');
            $table->enum('status', ['Menunggu Verifikasi', 'Disetujui', 'Ditolak'])->default('Disetujui')->after('certificate_original_name');
            $table->foreignUuid('submitted_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignUuid('verified_by')->nullable()->after('submitted_by')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table): void {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'category',
                'organizer',
                'achievement_date',
                'certificate_path',
                'certificate_original_name',
                'status',
                'submitted_by',
                'verified_by',
                'verified_at',
                'verification_notes',
            ]);
        });
    }
};
