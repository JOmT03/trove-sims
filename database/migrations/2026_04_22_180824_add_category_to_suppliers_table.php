<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add category if it doesn't already exist
        // (it may already be in the base create_suppliers_table migration)
        if (!Schema::hasColumn('suppliers', 'category')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('category')->nullable()->after('address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'category')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};