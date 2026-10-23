<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FIFO lots: each restock is a lot with its own cost; consume oldest first. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->decimal('qty_received', 12, 2);
            $table->decimal('qty_remaining', 12, 2);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->date('received_at');
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamps();
            $table->index(['inventory_id', 'received_at']); // FIFO ordering
        });
    }

    public function down(): void { Schema::dropIfExists('inventory_lots'); }
};