<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category');
            $table->string('unit');
            $table->decimal('quantity_on_hand', 10, 2)->default(0);
            $table->decimal('quantity_damaged', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();

            // ← FIX: use explicit reference because table is 'inventory'
            //         not 'inventories' (Laravel's default guess)
            $table->unsignedBigInteger('inventory_id');
            $table->foreign('inventory_id')
                  ->references('id')->on('inventory')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->foreign('delivery_id')
                  ->references('id')->on('deliveries')
                  ->onDelete('set null');

            $table->string('type');
            $table->decimal('quantity', 10, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventory');
    }
};