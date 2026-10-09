<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('recipe_versions')) {
            Schema::create('recipe_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name')->default('Original');
                $table->json('items')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('is_archived')->default(false);
                $table->timestamps();
            });
        }

        // Seed one "Original" active version per product from existing product_materials
        if (Schema::hasTable('product_materials') && Schema::hasTable('products')) {
            foreach (DB::table('products')->pluck('id') as $pid) {
                if (DB::table('recipe_versions')->where('product_id', $pid)->exists()) continue;
                $items = [];
                foreach (DB::table('product_materials')->where('product_id', $pid)->get() as $m) {
                    $items[] = ['inventory_id' => (int) $m->inventory_id, 'quantity_used' => (float) $m->quantity_used];
                }
                DB::table('recipe_versions')->insert([
                    'product_id'  => $pid,
                    'name'        => 'Original',
                    'items'       => json_encode($items),
                    'is_active'   => true,
                    'is_archived' => false,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
    public function down(): void {
        Schema::dropIfExists('recipe_versions');
    }
};