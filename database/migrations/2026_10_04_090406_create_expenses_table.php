<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {$table->id();
            $table->date('expense_date');$table->string('category', 50); // ingredients | packaging | other
            $table->string('description');$table->decimal('amount', 10, 2);
            // Connects specifically to your `inventory` table:
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->nullOnDelete();$table->integer('quantity')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};