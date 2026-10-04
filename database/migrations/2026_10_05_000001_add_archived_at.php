<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['inventory', 'products', 'orders'] as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t, 'archived_at')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->timestamp('archived_at')->nullable();
                });
            }
        }
    }
    public function down(): void {
        foreach (['inventory', 'products', 'orders'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'archived_at')) {
                Schema::table($t, function (Blueprint $table) { $table->dropColumn('archived_at'); });
            }
        }
    }
};