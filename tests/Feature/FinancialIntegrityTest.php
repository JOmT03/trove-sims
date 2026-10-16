<?php

use App\Models\Expense;
use App\Models\FinancialAuditLog;
use App\Models\PeriodClosure;
use App\Models\User;
use App\Services\FinancialStatementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->withoutVite());

function mkUser(string $role): User
{
    return User::create([
        'first_name' => $role, 'last_name' => 'Test', 'mobile_no' => '0917' . random_int(1000000, 9999999),
        'email' => strtolower($role) . random_int(1, 99999) . '@t.test', 'password' => bcrypt('password'), 'role' => $role,
    ]);
}

function mkExpense(array $o = []): Expense
{
    return Expense::create(array_merge([
        'expense_date' => now()->toDateString(), 'category' => 'ingredients',
        'description' => 'Flour', 'amount' => 100, 'created_by' => null,
    ], $o));
}

test('staff cannot reach finance pages; managers can', function () {
    $this->actingAs(mkUser('Staff'))->get('/expenses')->assertForbidden();
    $this->actingAs(mkUser('Staff'))->get('/financial-statement')->assertForbidden();
    $this->actingAs(mkUser('Manager'))->get('/expenses')->assertOk();
    $this->actingAs(mkUser('Manager'))->get('/financial-statement')->assertOk();
    $this->actingAs(mkUser('Manager'))->get('/financial-audit-log')->assertOk();
});

test('expenses cannot be deleted or edited, by route or by model', function () {
    $e = mkExpense();
    expect($this->actingAs(mkUser('Owner'))->delete("/expenses/{$e->id}")->status())->toBeIn([404, 405]);
    expect($this->actingAs(mkUser('Owner'))->put("/expenses/{$e->id}", ['amount' => 1])->status())->toBeIn([404, 405]);

    expect(fn () => $e->delete())->toThrow(LogicException::class);
    expect(fn () => $e->update(['amount' => 1]))->toThrow(LogicException::class);
    expect(Expense::count())->toBe(1);
});

test('recording validates, blocks future dates, and writes an audit entry', function () {
    $m = mkUser('Manager');
    $this->actingAs($m)->post('/expenses', [
        'expense_date' => now()->addDay()->toDateString(), 'category' => 'rent', 'description' => 'x', 'amount' => 5,
    ])->assertSessionHasErrors('expense_date');

    $this->actingAs($m)->post('/expenses', [
        'expense_date' => now()->toDateString(), 'category' => 'rent', 'description' => 'Rent', 'amount' => 5000,
    ])->assertRedirect(route('expenses.index'));

    expect(Expense::count())->toBe(1);
    expect(FinancialAuditLog::where('action', 'expense.recorded')->count())->toBe(1);
});

test('voiding needs a reason, keeps the row, drops it from totals, and reverses restock', function () {
    $m = mkUser('Manager');
    $invId = DB::table('inventory')->insertGetId([
        'item_name' => 'Flour', 'category' => 'Dry', 'unit' => 'kg', 'quantity_on_hand' => 10,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($m)->post('/expenses', [
        'expense_date' => now()->toDateString(), 'category' => 'ingredients', 'description' => 'Flour 5kg',
        'amount' => 250, 'inventory_id' => $invId, 'quantity' => 5,
    ])->assertSessionHasNoErrors();
    expect((float) DB::table('inventory')->where('id', $invId)->value('quantity_on_hand'))->toBe(15.0);

    $e = Expense::first();
    $this->actingAs($m)->post("/expenses/{$e->id}/void", ['void_reason' => ''])->assertSessionHasErrors('void_reason');
    $this->actingAs($m)->post("/expenses/{$e->id}/void", ['void_reason' => 'Wrong amount entered']);

    expect(Expense::count())->toBe(1);
    expect($e->fresh()->isVoided())->toBeTrue();
    expect((float) DB::table('inventory')->where('id', $invId)->value('quantity_on_hand'))->toBe(10.0);

    $s = app(FinancialStatementService::class)->build(now()->startOfMonth(), now()->endOfMonth());
    expect($s['cogs'])->toBe(0.0)->and($s['voidedCount'])->toBe(1);

    // voiding twice does nothing more
    $this->actingAs($m)->post("/expenses/{$e->id}/void", ['void_reason' => 'Trying again'])->assertSessionHas('error');
    expect(FinancialAuditLog::where('action', 'expense.voided')->count())->toBe(1);
});

test('restock fields must be given together', function () {
    $this->actingAs(mkUser('Manager'))->post('/expenses', [
        'expense_date' => now()->toDateString(), 'category' => 'ingredients', 'description' => 'x', 'amount' => 5, 'quantity' => 3,
    ])->assertSessionHasErrors('inventory_id');
});

test('statement math: orders + reconciled branch sales - COGS - OpEx', function () {
    $month = now()->subMonthNoOverflow()->startOfMonth();
    $mid   = $month->copy()->addDays(10);

    DB::table('orders')->insert([
        ['status' => 'Completed', 'total_amount' => 1000, 'completed_at' => $mid, 'created_at' => $mid, 'updated_at' => $mid],
        ['status' => 'Pending',   'total_amount' => 9999, 'completed_at' => null, 'created_at' => $mid, 'updated_at' => $mid],
    ]);
    $b1 = DB::table('batches')->insertGetId(['batch_date' => $mid->toDateString(), 'status' => 'reconciled', 'created_at' => now(), 'updated_at' => now()]);
    $b2 = DB::table('batches')->insertGetId(['batch_date' => $mid->toDateString(), 'status' => 'sent', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('batch_items')->insert([
        ['batch_id' => $b1, 'qty_sent' => 10, 'qty_returned' => 2, 'unit_price' => 50, 'created_at' => now(), 'updated_at' => now()], // 400
        ['batch_id' => $b2, 'qty_sent' => 99, 'qty_returned' => 0, 'unit_price' => 50, 'created_at' => now(), 'updated_at' => now()], // not reconciled
    ]);
    mkExpense(['expense_date' => $mid->toDateString(), 'category' => 'ingredients', 'amount' => 300]);
    mkExpense(['expense_date' => $mid->toDateString(), 'category' => 'rent', 'amount' => 200]);
    $void = mkExpense(['expense_date' => $mid->toDateString(), 'category' => 'rent', 'amount' => 777]);
    $void->update(['voided_at' => now(), 'void_reason' => 'dup']);

    $s = app(FinancialStatementService::class)->build($month, $month->copy()->endOfMonth());
    expect($s['orderRevenue'])->toBe(1000.0)
        ->and($s['branchRevenue'])->toBe(400.0)
        ->and($s['revenue'])->toBe(1400.0)
        ->and($s['cogs'])->toBe(300.0)
        ->and($s['opex'])->toBe(200.0)
        ->and($s['grossProfit'])->toBe(1100.0)
        ->and($s['netIncome'])->toBe(900.0);
});

test('closing a month: owner only, after month end, then everything is locked and verified', function () {
    $prev  = now()->subMonthNoOverflow()->startOfMonth();
    $owner = mkUser('Owner');
    $exp   = mkExpense(['expense_date' => $prev->copy()->addDays(3)->toDateString(), 'amount' => 500, 'category' => 'rent']);

    $this->actingAs(mkUser('Manager'))->post('/financial-statement/close', ['month' => $prev->format('Y-m')])->assertForbidden();
    $this->actingAs($owner)->post('/financial-statement/close', ['month' => now()->format('Y-m')])->assertSessionHas('error'); // not ended

    $this->actingAs($owner)->post('/financial-statement/close', ['month' => $prev->format('Y-m')])->assertSessionHas('success');
    $c = PeriodClosure::first();
    expect($c->hashIsValid())->toBeTrue()->and((float) $c->net_income)->toBe(-500.0);

    // can't close twice, can't add or void inside it
    $this->actingAs($owner)->post('/financial-statement/close', ['month' => $prev->format('Y-m')])->assertSessionHas('error');
    $this->actingAs($owner)->post('/expenses', [
        'expense_date' => $prev->copy()->addDays(5)->toDateString(), 'category' => 'rent', 'description' => 'late', 'amount' => 1,
    ])->assertSessionHas('error');
    expect(Expense::count())->toBe(1);
    $this->actingAs($owner)->post("/expenses/{$exp->id}/void", ['void_reason' => 'Trying to change'])->assertSessionHas('error');
    expect($exp->fresh()->isVoided())->toBeFalse();

    // closure rows cannot be modified or removed
    expect(fn () => $c->update(['net_income' => 1]))->toThrow(LogicException::class);
    expect(fn () => $c->delete())->toThrow(LogicException::class);

    // statement page verifies
    $this->actingAs($owner)->get('/financial-statement?from=' . $prev->toDateString() . '&to=' . $prev->copy()->endOfMonth()->toDateString())
        ->assertOk()->assertSee('Verified');

    // direct database tampering is detected
    DB::table('expenses')->where('id', $exp->id)->update(['amount' => 1]);
    $this->actingAs($owner)->get('/financial-statement?from=' . $prev->toDateString() . '&to=' . $prev->copy()->endOfMonth()->toDateString())
        ->assertOk()->assertSee('MISMATCH');
});

test('audit log is append-only', function () {
    $log = FinancialAuditLog::record('test.action');
    expect(fn () => $log->update(['action' => 'x']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);
});

test('completing an order stamps completed_at and a closed month freezes it', function () {
    $owner = mkUser('Owner');
    $when  = now()->subMonthNoOverflow()->startOfMonth()->addDays(4);
    $id = DB::table('orders')->insertGetId(['status' => 'Ready', 'total_amount' => 800, 'created_at' => $when, 'updated_at' => $when]);

    $this->actingAs($owner)->patch("/orders/{$id}/status", ['status' => 'Completed']);
    expect(DB::table('orders')->where('id', $id)->value('completed_at'))->not->toBeNull();

    // backdate into last month, close it, then try to change it
    DB::table('orders')->where('id', $id)->update(['completed_at' => $when]);
    $this->actingAs($owner)->post('/financial-statement/close', ['month' => $when->format('Y-m')])->assertSessionHas('success');
    $this->actingAs($owner)->patch("/orders/{$id}/status", ['status' => 'Pending'])->assertSessionHas('error');
    expect(DB::table('orders')->where('id', $id)->value('status'))->toBe('Completed');
});
