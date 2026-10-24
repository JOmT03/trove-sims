<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_yield_audits')) {
            return;
        }

        Schema::create('production_yield_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 150);
            $table->unsignedBigInteger('recipe_version_id')->nullable();
            $table->string('recipe_name')->nullable();
            $table->unsignedInteger('expected_qty');   // yield the issued materials should give
            $table->unsignedInteger('actual_qty');     // what was really produced
            $table->integer('variance_qty');           // expected - actual (positive = SHORT)
            $table->json('materials')->nullable();     // snapshot of raw materials issued
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_yield_audits');
    }
};
