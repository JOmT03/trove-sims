<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archive for the Branch Transfers page. Hides finished orders and
 * order-less deliveries from the main list; nothing is deleted and
 * reports are not affected.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['batches', 'branch_orders'] as $table) {
            if (!Schema::hasColumn($table, 'archived_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->timestamp('archived_at')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['batches', 'branch_orders'] as $table) {
            if (Schema::hasColumn($table, 'archived_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('archived_at');
                });
            }
        }
    }
};
