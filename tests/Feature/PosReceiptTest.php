<?php

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;

beforeEach(fn () => $this->withoutVite());

test('a completed sale offers a printable 80 mm receipt with change and reprint', function () {
    $owner = User::factory()->create(['first_name' => 'Rexel', 'last_name' => 'Jamlid']);
    $cake = Product::create(['product_name' => 'Ube Cake', 'price' => 490, 'stock_quantity' => 5, 'status' => 'active']);

    $this->actingAs($owner)->post('/pos/sales', [
        'items' => [['product_id' => $cake->id, 'quantity' => 2]],
        'payments' => [['method' => 'cash', 'amount' => 1000, 'reference' => '']],
    ])->assertRedirect('/pos')->assertSessionHas('completedSale');

    $sale = \App\Models\Sale::first();
    $this->actingAs($owner)->get('/pos')->assertOk()->assertSee('Sale complete')->assertSee(route('pos.sales.receipt', $sale), false);

    $this->actingAs($owner)->get(route('pos.sales.receipt', $sale))->assertOk()
        ->assertSee('size: 80mm', false)
        ->assertSeeInOrder(['SALES RECEIPT', $sale->sale_number, 'Rexel Jamlid', 'Ube Cake', '2 x 490.00', '980.00', 'TOTAL', 'Cash', '1,000.00', 'Change', '20.00'])
        ->assertDontSee('REPRINT');

    $this->actingAs($owner)->get(route('pos.sales.receipt', $sale) . '?reprint=1')->assertSee('*** REPRINT ***');
});

test('the receipt follows the paper width setting and staff can print it', function () {
    Setting::set('receipt_width', '58');
    $staff = User::factory()->create(['role' => 'Staff']);
    $cake = Product::create(['product_name' => 'Cheese Cake', 'price' => 500, 'stock_quantity' => 3, 'status' => 'active']);
    $this->actingAs($staff)->post('/pos/sales', [
        'items' => [['product_id' => $cake->id, 'quantity' => 1]],
        'payments' => [['method' => 'gcash', 'amount' => 500, 'reference' => '4031228917']],
    ]);

    $this->actingAs($staff)->get(route('pos.sales.receipt', \App\Models\Sale::first()))->assertOk()
        ->assertSee('size: 58mm', false)->assertSee('GCash')->assertSee('4031228917')->assertDontSee('Change');
});
