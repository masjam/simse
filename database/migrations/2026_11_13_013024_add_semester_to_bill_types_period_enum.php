<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bill_types MODIFY COLUMN period ENUM('Bulanan', 'Semester', 'Tahunan', 'Sekali Bayar') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bill_types MODIFY COLUMN period ENUM('Bulanan', 'Tahunan', 'Sekali Bayar') NOT NULL");
        }
    }
};
