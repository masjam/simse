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
        if (Schema::hasTable('students') && ! Schema::hasColumn('students', 'nis')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('nis', 20)->nullable()->after('nik');
            });
        }

        if (Schema::hasTable('teachers') && ! Schema::hasColumn('teachers', 'position')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->string('position')->nullable()->after('nip');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'nis')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('nis');
            });
        }

        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'position')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }
};
