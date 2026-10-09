<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('product_materials')) {
            Schema::create('product_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
                $table->decimal('quantity_used', 10, 2)->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'inventory_id']);
            });
        }
    }
    public function down(): void {
        // intentionally left in place
    }
};