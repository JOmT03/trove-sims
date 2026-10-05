<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1) Remove the delivery_id FK + column from inventory_logs (if present)
        if (Schema::hasTable('inventory_logs') && Schema::hasColumn('inventory_logs', 'delivery_id')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                $table->dropForeign(['delivery_id']);
                $table->dropColumn('delivery_id');
            });
        }

        // 2) Drop legacy delivery tables (child first, then parent)
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
    }

    public function down(): void {
        // Legacy tables are intentionally not recreated.
    }
};