<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete(); // Kasir / Bendahara
            $table->decimal('amount_paid', 12, 2);
            $table->enum('payment_method', ['Tunai', 'Transfer Bank', 'Dompet Digital']);
            $table->string('receipt_photo')->nullable(); // Bukti transfer
            $table->date('payment_date');
            $table->enum('status', ['Menunggu Verifikasi', 'Ditolak', 'Berhasil'])->default('Menunggu Verifikasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
