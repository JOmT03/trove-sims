<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialStatementController extends Controller
{
    public function index(Request $request)
    {
        // 1. Calculate Gross Revenue from Completed/Paid Orders
        $totalRevenue = Order::whereNotIn('status', ['Cancelled', 'Pending'])
            ->sum('total_amount');

        // 2. Fetch Expenses Grouped by Category
        $expensesByCategory = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $totalExpenses = array_sum($expensesByCategory);

        // 3. COGS vs Operating Expenses Breakdown
        $cogsCategories = ['ingredients', 'packaging', 'raw_materials'];
        $cogsTotal = 0;
        $operatingExpensesTotal = 0;

        foreach ($expensesByCategory as $cat => $amount) {
            if (in_array(strtolower($cat), $cogsCategories)) {
                $cogsTotal += $amount;
            } else {
                $operatingExpensesTotal += $amount;
            }
        }

        // 4. Calculate Gross Profit & Net Profit
        $grossProfit = $totalRevenue - $cogsTotal;
        $netProfit = $grossProfit - $operatingExpensesTotal;

        // 5. Recent Expense Activity Log
        $recentExpenses = Expense::with('inventoryItem', 'creator')
            ->orderBy('expense_date', 'desc')
            ->take(10)
            ->get();

        return view('financial-statement.index', compact(
            'totalRevenue',
            'expensesByCategory',
            'totalExpenses',
            'cogsTotal',
            'operatingExpensesTotal',
            'grossProfit',
            'netProfit',
            'recentExpenses'
        ));
    }
}