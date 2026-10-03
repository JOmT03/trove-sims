<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trove â€” full database schema (single migration).
 * Tables are created in foreign-key dependency order so everything
 * migrates cleanly in one pass.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. SITES (Matina commissary, Jacinto branch, etc.)
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 100);
            $table->string('street', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();
        });

        // 2. USERS (Owner / Manager / Staff)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('mobile_no', 20)->nullable();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('role', 50)->default('Staff');
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        // Laravel password reset + sessions (standard)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 3. SUPPLIERS (optional â€” links to a supplier User account)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        // 4. PRODUCTS (finished goods: cakes, pastries, coffee)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 150);
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->timestamps();
        });

        // 5. INVENTORY (raw materials / ingredients)
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category');
            $table->string('unit');
            $table->decimal('quantity_on_hand', 10, 2)->default(0);
            $table->decimal('quantity_damaged', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. ORDERS (sales / customer orders)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('customer_name', 100)->nullable();
            $table->string('order_type', 50)->default('Dine-in');
            $table->text('design_description')->nullable();
            $table->date('needed_by_date')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('deposit_amount', 12, 2)->default(0.00);
            $table->string('status', 50)->default('Pending');
            $table->timestamps();
        });

        // 7. ORDER ITEMS
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 8. PRODUCT MATERIALS (recipe: which ingredients a product uses)
        Schema::create('product_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->decimal('quantity_used', 8, 2);
            $table->timestamps();
            $table->unique(['product_id', 'inventory_id']);
        });

        // 9. DELIVERIES (commissary -> branch)
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('delivery_number')->unique();
            $table->date('delivered_at')->nullable();
            $table->string('received_by')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. DELIVERY ITEMS
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->string('item_name');
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity_ordered', 10, 2)->default(0);
            $table->decimal('quantity_delivered', 10, 2)->default(0);
            $table->decimal('quantity_damaged', 10, 2)->default(0);
            $table->string('condition')->default('good');
            $table->text('damage_notes')->nullable();
            $table->timestamps();
        });

        // 11. INVENTORY LOGS (audit trail of stock movements)
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries')->nullOnDelete();
            $table->string('type');          // received | used | damaged | adjustment
            $table->decimal('quantity', 10, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('product_materials');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sites');
    }
};