<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create if it doesn't already exist
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone');
                $table->text('address');
                $table->string('category')->nullable();
                $table->timestamps();
            });
        } else {
            // Table exists — just make sure the category column is there
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'category')) {
                    $table->string('category')->nullable()->after('address');
                }
                if (!Schema::hasColumn('suppliers', 'email')) {
                    $table->string('email')->unique()->after('name');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};