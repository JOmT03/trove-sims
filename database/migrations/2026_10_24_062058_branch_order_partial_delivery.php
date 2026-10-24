<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_order_items', function (Blueprint $t) {
            $t->unsignedInteger('qty_closed')->default(0);
        });
        Schema::table('branch_orders', function (Blueprint $t) {
            $t->string('short_reason', 500)->nullable();
        });
        // earlier version marked fulfilled orders as 'sent'
        DB::table('branch_orders')->where('status', 'sent')->update(['status' => 'complete']);
    }

    public function down(): void
    {
        Schema::table('branch_order_items', function (Blueprint $t) { $t->dropColumn('qty_closed'); });
        Schema::table('branch_orders', function (Blueprint $t) { $t->dropColumn('short_reason'); });
    }
};