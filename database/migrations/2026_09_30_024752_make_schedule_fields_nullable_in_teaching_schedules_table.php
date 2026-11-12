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
        Schema::table('teaching_schedules', function (Blueprint $table) {
            $table->tinyInteger('day')->nullable()->change();
            $table->time('time_start')->nullable()->change();
            $table->time('time_end')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teaching_schedules', function (Blueprint $table) {
            $table->tinyInteger('day')->nullable(false)->change();
            $table->time('time_start')->nullable(false)->change();
            $table->time('time_end')->nullable(false)->change();
        });
    }
};
