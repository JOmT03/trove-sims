<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Extra fields for ALL users
            if (!Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('email');
            }
            // Role: 'admin' | 'supplier' | 'user'
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('contact_number');
            }
            // Supplier-specific fields
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'company_address')) {
                $table->string('company_address')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('users', 'company_city')) {
                $table->string('company_city')->nullable()->after('company_address');
            }
            if (!Schema::hasColumn('users', 'company_zip')) {
                $table->string('company_zip')->nullable()->after('company_city');
            }
            if (!Schema::hasColumn('users', 'company_email')) {
                $table->string('company_email')->nullable()->after('company_zip');
            }
            if (!Schema::hasColumn('users', 'company_tel')) {
                $table->string('company_tel')->nullable()->after('company_email');
            }
        });

        // Supplier products table
        if (!Schema::hasTable('supplier_products')) {
            Schema::create('supplier_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade'); // supplier user
                $table->string('name');
                $table->string('category');
                $table->string('unit');
                $table->decimal('price', 10, 2);
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_available')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'contact_number', 'company_name', 'company_address',
                'company_city', 'company_zip', 'company_email', 'company_tel',
            ]);
        });

        Schema::dropIfExists('supplier_products');
    }
};