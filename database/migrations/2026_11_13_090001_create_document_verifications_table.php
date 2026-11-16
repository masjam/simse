<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token', 40)->unique()->index();
            $table->string('document_type'); // e-Raport, Surat Peringatan, Berita Acara Pelanggaran, Slip Gaji, Sertifikat Prestasi, Kartu Tanda Siswa, Bukti Pembayaran
            $table->string('reference_type')->nullable();
            $table->string('reference_id', 36)->nullable();
            $table->string('title')->nullable();
            $table->string('recipient_name');
            $table->string('content_hash', 64);
            $table->json('metadata')->nullable();
            $table->foreignUuid('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at');
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->foreignUuid('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revoked_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
