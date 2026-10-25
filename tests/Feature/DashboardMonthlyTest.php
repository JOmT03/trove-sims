<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->withoutVite());
afterEach(fn () => Carbon::setTestNow());

function dashOwner(): User
{
    return User::create([
        'first_name' => 'Owner', 'last_name' => 'Test', 'mobile_no' => '0917' . random_int(1000000, 9999999),
        'email' => 'owner' . random_int(1, 99999) . '@t.test', 'password' => bcrypt('password'), 'role' => 'Owner',
    ]);
}

function dashSale(int $productId, string $name, int $qty, float $price, string $at): void
{
    $id = DB::table('sales')->insertGetId([
        'sale_number' => 'S' . random_int(1, 999999), 'total' => $qty * $price, 'status' => 'completed',
        'completed_at' => $at, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('sale_items')->insert([
        'sale_id' => $id, 'product_id' => $productId, 'product_name' => $name, 'quantity' => $qty,
        'unit_price' => $price, 'line_total' => $qty * $price, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function dashBatch(int $productId, int $qty, float $price, string $date, bool $voided = false): void
{
    $id = DB::table('batches')->insertGetId([
        'batch_date' => $date, 'status' => 'sent', 'voided_at' => $voided ? now() : null,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('batch_items')->insert([
        'batch_id' => $id, 'product_id' => $productId, 'qty_sent' => $qty, 'qty_returned' => 0,
        'unit_price' => $price, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('monthly sales add up the counter, branch transfers and completed orders, and skip voided batches', function () {
    Carbon::setTestNow('2026-10-25 12:00:00');
    $cake = DB::table('products')->insertGetId(['product_name' => 'Cheese Cake', 'price' => 480, 'created_at' => now(), 'updated_at' => now()]);
    $roll = DB::table('products')->insertGetId(['product_name' => 'Ube Roll', 'price' => 150, 'created_at' => now(), 'updated_at' => now()]);

    dashSale($roll, 'Ube Roll', 4, 150, '2026-10-03 10:00:00');      // 600
    dashSale($roll, 'Ube Roll', 1, 150, '2026-09-30 10:00:00');      // previous month
    dashBatch($cake, 10, 480, '2026-10-10');                         // 4,800
    dashBatch($cake, 99, 480, '2026-10-11', voided: true);           // must not count
    $order = DB::table('orders')->insertGetId(['total_amount' => 2500, 'status' => 'Completed', 'completed_at' => '2026-10-18 15:00:00', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('order_items')->insert(['order_id' => $order, 'product_id' => $cake, 'quantity' => 2, 'price' => 1250, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('expenses')->insert(['expense_date' => '2026-10-05', 'category' => 'ingredients', 'description' => 'Flour', 'amount' => 1200, 'quantity' => 1, 'created_at' => now(), 'updated_at' => now()]);

    $res = $this->actingAs(dashOwner())->get('/dashboard')->assertOk();

    expect($res->viewData('monthSales'))->toEqual(7900)
        ->and($res->viewData('prevSales'))->toEqual(150)
        ->and($res->viewData('soldPcs'))->toBe(16)
        ->and($res->viewData('batchesThisMonth'))->toBe(1);

    $chart = $res->viewData('chart');
    expect($chart['daily'])->toHaveCount(31)
        ->and($chart['daily'][2])->toEqual([600, 0, 0])
        ->and($chart['daily'][9])->toEqual([0, 4800, 0])
        ->and($chart['daily'][17])->toEqual([0, 0, 2500])
        ->and($chart['daily'][25])->toBeNull()                       // Oct 26 is still ahead
        ->and($chart['top'][0])->toEqual(['n' => 'Cheese Cake', 'v' => 12])
        ->and(end($chart['months']))->toMatchArray(['label' => 'Oct', 'revenue' => 7900.0, 'expenses' => 1200.0]);

    expect($res->viewData('latestBatch')->batch_date->toDateString())->toBe('2026-10-10');
});

test('the month picker shows past months and ignores future or malformed ones', function () {
    Carbon::setTestNow('2026-10-25 12:00:00');
    $user = dashOwner();

    $sep = $this->actingAs($user)->get('/dashboard?month=2026-09')->assertOk()->assertSee('September 2026');
    expect($sep->viewData('chart')['daily'])->toHaveCount(30)->and($sep->viewData('isCurrent'))->toBeFalse();

    $this->actingAs($user)->get('/dashboard?month=2027-01')->assertOk()->assertSee('October 2026');
    $this->actingAs($user)->get('/dashboard?month=nope')->assertOk()->assertSee('October 2026');
});
