<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->nullable()->constrained()->cascadeOnDelete();
            // Optionally we can add staff_id if staff are separate from teachers, but we'll assume everyone who gets payroll is in user table or linked to teacher table.
            $table->string('month');
            $table->string('year');
            $table->decimal('total_salary', 15, 2);
            $table->enum('status', ['Draft', 'Menunggu Persetujuan', 'Dibayar'])->default('Draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
