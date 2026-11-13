<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_waves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g. Gelombang 1
            $table->integer('quota');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('form_fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_waves');
    }
};
