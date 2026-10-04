<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Branch Transfers â€” Matina commissary dispatches a weekly batch of
 * products to the Jacinto branch; unsold items are returned.
 * Net Sold = qty_sent - qty_returned (computed offline).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('destination_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->date('batch_date');
            $table->string('status', 20)->default('sent'); // sent | reconciled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('qty_sent')->default(0);
            $table->integer('qty_returned')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_items');
        Schema::dropIfExists('batches');
    }
};