<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('recipient_name')->nullable();
            $table->string('phone_number');
            $table->text('message');
            $table->enum('status', ['Pending', 'Terkirim', 'Gagal'])->default('Pending');
            $table->text('response_log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
