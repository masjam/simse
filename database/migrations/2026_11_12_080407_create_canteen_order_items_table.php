<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('canteen_order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('canteen_menu_id')->constrained()->cascadeOnDelete();
            $table->string('menu_name');
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_order_items');
    }
};
