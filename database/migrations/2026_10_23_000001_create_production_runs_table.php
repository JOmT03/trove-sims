<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Audited production runs: before / expected / actual / variance per ingredient. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->json('items')->nullable();           // [{inventory_id,name,unit,before,expected,after,used,variance}]
            $table->boolean('has_variance')->default(false);
            $table->foreignId('produced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('produced_by_name')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('production_runs'); }
};