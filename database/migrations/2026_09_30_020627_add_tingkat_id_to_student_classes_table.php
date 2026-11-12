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
        Schema::table('student_classes', function (Blueprint $table) {
            $table->foreignId('tingkat_id')->nullable()->constrained('tingkats')->cascadeOnDelete();
            // Optional: drop column 'level' if we don't need it anymore, or just keep it
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_classes', function (Blueprint $table) {
            $table->dropForeign(['tingkat_id']);
            $table->dropColumn('tingkat_id');
        });
    }
};
