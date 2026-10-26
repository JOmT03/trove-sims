<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recipes are saved in product_raw_materials.quantity_used (see RecipeController),
 * but the original create migration only made quantity_needed. Add the column on
 * databases that lack it and copy the old values over. No-op where it already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_raw_materials') && ! Schema::hasColumn('product_raw_materials', 'quantity_used')) {
            Schema::table('product_raw_materials', function (Blueprint $table) {
                $table->decimal('quantity_used', 10, 2)->default(0);
            });
            DB::table('product_raw_materials')->update(['quantity_used' => DB::raw('quantity_needed')]);
        }
    }

    public function down(): void
    {
        // kept: existing recipes depend on it
    }
};
