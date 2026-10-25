<?php

use App\Models\Expense;
use App\Models\User;
use App\Services\InventoryFifoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->withoutVite());

function restockUser(): User
{
    return User::create([
        'first_name' => 'Owner', 'last_name' => 'Test', 'mobile_no' => '0917' . random_int(1000000, 9999999),
        'email' => 'owner' . random_int(1, 99999) . '@t.test', 'password' => bcrypt('password'), 'role' => 'Owner',
    ]);
}

function restockItem(string $name, string $category, float $qty): int
{
    return DB::table('inventory')->insertGetId([
        'item_name' => $name, 'category' => $category, 'unit' => 'kg', 'quantity_on_hand' => $qty,
        'quantity_damaged' => 0, 'minimum_stock' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('one restock form stocks several items, adds FIFO lots, and records one expense per line', function () {
    $flour = restockItem('Flour', 'Baking Essentials', 1);
    $boxes = restockItem('Cake Boxes', 'Packaging', 10);

    $this->actingAs(restockUser())->post('/inventory/restock', [
        'restock_date' => now()->toDateString(), 'supplier' => 'Gaisano',
        'items' => [
            ['inventory_id' => $flour, 'quantity' => '2.5', 'unit_price' => '60'],
            ['inventory_id' => $boxes, 'quantity' => '50', 'unit_price' => '18'],
        ],
    ])->assertRedirect('/inventory');

    expect((float) DB::table('inventory')->where('id', $flour)->value('quantity_on_hand'))->toBe(3.5)
        ->and((float) DB::table('inventory')->where('id', $boxes)->value('quantity_on_hand'))->toBe(60.0);

    $e = Expense::orderBy('id')->get();
    expect($e)->toHaveCount(2)
        ->and($e[0]->category)->toBe('ingredients')->and((float) $e[0]->amount)->toBe(150.0)->and((float) $e[0]->quantity)->toBe(2.5)
        ->and($e[1]->category)->toBe('packaging')->and((float) $e[1]->amount)->toBe(900.0);

    expect(InventoryFifoService::valuation($flour))->toBe(150.0)
        ->and(DB::table('inventory_logs')->where('inventory_id', $flour)->where('type', 'received')->value('quantity'))->toEqual(2.5);
});

test('voiding a fractional restock takes back exactly that quantity', function () {
    $flour = restockItem('Flour', 'Baking Essentials', 1);
    $user = restockUser();
    $this->actingAs($user)->post('/inventory/restock', [
        'restock_date' => now()->toDateString(),
        'items' => [['inventory_id' => $flour, 'quantity' => '2.5', 'unit_price' => '60']],
    ]);

    $this->actingAs($user)->post('/expenses/' . Expense::first()->id . '/void', ['void_reason' => 'wrong quantity'])->assertRedirect();

    expect((float) DB::table('inventory')->where('id', $flour)->value('quantity_on_hand'))->toBe(1.0)
        ->and(InventoryFifoService::valuation($flour))->toBe(0.0);
});

test('restock needs a price and a quantity on every line, and no future dates', function () {
    $flour = restockItem('Flour', 'Baking Essentials', 1);
    $this->actingAs(restockUser())->post('/inventory/restock', [
        'restock_date' => now()->addDay()->toDateString(),
        'items' => [['inventory_id' => $flour, 'quantity' => '0', 'unit_price' => '']],
    ])->assertSessionHasErrors(['restock_date', 'items.0.quantity', 'items.0.unit_price']);

    expect(Expense::count())->toBe(0)
        ->and((float) DB::table('inventory')->where('id', $flour)->value('quantity_on_hand'))->toBe(1.0);
});
