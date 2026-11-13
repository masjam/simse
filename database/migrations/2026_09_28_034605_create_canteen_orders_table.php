<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('canteen_stand_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['Menunggu Konfirmasi', 'Sedang Dimasak', 'Siap Diambil', 'Selesai', 'Dibatalkan']);
            $table->enum('payment_method', ['Tunai', 'Dompet Digital']);
            $table->string('qr_code_pickup')->nullable(); // QR validasi saat siswa mengambil makanan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_orders');
    }
};
