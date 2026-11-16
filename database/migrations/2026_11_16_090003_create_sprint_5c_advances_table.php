<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->integer('installment_count')->default(1);
            $table->decimal('monthly_amount', 12, 2);
            $table->text('reason');
            $table->enum('status', ['Diajukan', 'Disetujui', 'Ditolak', 'Lunas'])->default('Diajukan');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });

        Schema::create('advance_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('advance_id')->constrained('advances')->cascadeOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 12, 2);
            $table->string('month', 30);
            $table->string('year', 10);
            $table->enum('status', ['Pending', 'Lunas'])->default('Pending');
            $table->foreignUuid('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['advance_id', 'status']);
            $table->index(['month', 'year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_installments');
        Schema::dropIfExists('advances');
    }
};
