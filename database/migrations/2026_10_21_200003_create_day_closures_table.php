<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** End-of-Day: one locked snapshot per business day. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_closures', function (Blueprint $table) {
            $table->id();
            $table->date('business_date')->unique();
            $table->decimal('gross_sales', 14, 2)->default(0);
            $table->decimal('cash_total', 14, 2)->default(0);
            $table->decimal('gcash_total', 14, 2)->default(0);
            $table->decimal('maya_total', 14, 2)->default(0);
            $table->decimal('bank_total', 14, 2)->default(0);
            $table->decimal('expenses_total', 14, 2)->default(0);
            $table->decimal('net', 14, 2)->default(0);
            $table->unsignedInteger('tx_count')->default(0);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('closed_by_name')->nullable();
            $table->timestamp('closed_at');
        });
    }

    public function down(): void { Schema::dropIfExists('day_closures'); }
};