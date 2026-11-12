<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. SPP Bulanan, Uang Gedung
            $table->decimal('amount', 12, 2);
            $table->enum('period', ['Bulanan', 'Tahunan', 'Sekali Bayar']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_types');
    }
};
