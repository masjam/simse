<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['Setoran', 'Penarikan', 'Pembayaran Kantin', 'Pembayaran SPP']);
            $table->decimal('amount', 12, 2);
            $table->string('reference_id')->nullable(); // Referensi struk pembayaran kantin / SPP
            $table->text('description')->nullable();
            $table->enum('status', ['Pending', 'Gagal', 'Sukses'])->default('Sukses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
