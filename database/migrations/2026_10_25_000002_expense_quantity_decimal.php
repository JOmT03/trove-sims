<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Restocks can be fractional (2.5 kg, 0.75 liters), so the linked quantity becomes a decimal. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $t) {
            $t->decimal('quantity', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $t) {
            $t->integer('quantity')->nullable()->change();
        });
    }
};
