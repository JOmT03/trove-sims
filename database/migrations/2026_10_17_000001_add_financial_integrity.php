<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Revenue is recognised on the date an order is marked Completed.
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('status');
        });
        DB::table('orders')->where('status', 'Completed')
            ->update(['completed_at' => DB::raw('COALESCE(updated_at, created_at)')]);

        // Expenses are never deleted or edited: they are voided (with a reason).
        Schema::table('expenses', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('void_reason')->nullable();
            $table->string('receipt_path')->nullable();
        });

        // Append-only log of every financial action.
        Schema::create('financial_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role', 30)->nullable();
            $table->string('action', 60);
            $table->string('entity_type', 60)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // One row per closed month. Rows are chained by hash so tampering is detectable.
        Schema::create('period_closures', function (Blueprint $table) {
            $table->id();
            $table->date('period')->unique();            // first day of the closed month
            $table->decimal('revenue', 14, 2);
            $table->decimal('cogs', 14, 2);
            $table->decimal('opex', 14, 2);
            $table->decimal('net_income', 14, 2);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('closed_by_name')->nullable();
            $table->timestamp('closed_at');
            $table->string('prev_hash', 64)->nullable();
            $table->string('hash', 64);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_closures');
        Schema::dropIfExists('financial_audit_logs');
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['voided_at', 'void_reason', 'receipt_path']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
