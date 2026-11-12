<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('points');
            $table->enum('category', ['Ringan', 'Sedang', 'Berat', 'Sangat Berat']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_points');
    }
};
