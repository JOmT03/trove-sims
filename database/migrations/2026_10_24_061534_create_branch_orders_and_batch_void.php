<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_orders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('destination_site_id')->nullable();
            $t->date('needed_by');
            $t->string('status')->default('requested'); // requested, produced, sent, cancelled
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('branch_order_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('branch_order_id');
            $t->unsignedBigInteger('product_id');
            $t->unsignedInteger('qty');
            $t->timestamps();
            $t->foreign('branch_order_id')->references('id')->on('branch_orders')->onDelete('cascade');
        });

        Schema::table('batches', function (Blueprint $t) {
            $t->unsignedBigInteger('branch_order_id')->nullable();
            $t->timestamp('voided_at')->nullable();
            $t->string('void_reason', 500)->nullable();
            $t->unsignedBigInteger('voided_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $t) {
            $t->dropColumn(['branch_order_id', 'voided_at', 'void_reason', 'voided_by']);
        });
        Schema::dropIfExists('branch_order_items');
        Schema::dropIfExists('branch_orders');
    }
};