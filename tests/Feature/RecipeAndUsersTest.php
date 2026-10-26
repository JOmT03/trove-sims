<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => $this->withoutVite());

test('a recipe version saves on a fresh database and feeds the can-still-bake estimate', function () {
    $owner = User::factory()->create();
    $flour = DB::table('inventory')->insertGetId(['item_name' => 'Flour', 'category' => 'Baking Essentials', 'unit' => 'kg', 'quantity_on_hand' => 10, 'quantity_damaged' => 0, 'minimum_stock' => 1, 'created_at' => now(), 'updated_at' => now()]);
    $cake = Product::create(['product_name' => 'Banana Bread', 'price' => 180, 'stock_quantity' => 0, 'status' => 'active']);

    $this->actingAs($owner)->post("/products/{$cake->id}/recipes", ['name' => 'Original', 'items' => [['inventory_id' => $flour, 'quantity_used' => 0.25]]])
        ->assertSessionHasNoErrors()->assertSessionHas('success');

    expect((float) $cake->materials()->first()->pivot->quantity_used)->toBe(0.25);
    $this->actingAs($owner)->get('/dashboard')->assertOk()->assertSeeInOrder(['Can Still Bake', 'Banana Bread', '~40']);
});

test('the users page lists the owner first', function () {
    $owner = User::factory()->create(['first_name' => 'Zed', 'role' => 'Owner']);
    User::factory()->create(['first_name' => 'Amy', 'role' => 'Staff']);

    $this->actingAs($owner)->get('/users')->assertOk()->assertSeeInOrder(['Zed', 'Amy']);
});
