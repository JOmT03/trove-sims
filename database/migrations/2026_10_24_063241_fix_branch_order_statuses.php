<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (\App\Models\BranchOrder::where('status', '!=', 'cancelled')->get() as $order) {
            $order->refreshStatus();
        }
    }

    public function down(): void
    {
    }
};