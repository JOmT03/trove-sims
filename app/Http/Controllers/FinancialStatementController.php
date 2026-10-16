<?php

namespace App\Http\Controllers;

use App\Models\FinancialAuditLog;
use App\Models\PeriodClosure;
use App\Services\FinancialStatementService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The Financial Statement is READ-ONLY. It is computed live from the ledger
 * (or verified against the frozen snapshot once a month is closed).
 */
class FinancialStatementController extends Controller
{
    public function index(Request $request, FinancialStatementService $svc)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);

        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay();

        $s = $svc->build($from, $to);

        // Is the selected range exactly one calendar month? Then it can be closed / verified.
        $isWholeMonth = $from->isSameDay($from->copy()->startOfMonth()) && $to->isSameDay($from->copy()->endOfMonth());
        $closure = $isWholeMonth ? PeriodClosure::whereDate('period', $from->toDateString())->first() : null;

        $integrity = null;
        if ($closure) {
            $integrity = [
                'hash_ok'    => $closure->hashIsValid(),
                'figures_ok' => abs($closure->net_income - $s['netIncome']) < 0.005
                             && abs($closure->revenue - $s['revenue']) < 0.005
                             && abs($closure->cogs - $s['cogs']) < 0.005
                             && abs($closure->opex - $s['opex']) < 0.005,
            ];
        }

        $canClose = $isWholeMonth && !$closure && $to->lt(now()->startOfDay()) && auth()->user()->isOwner();

        return view('financial-statement.index', [
            's' => $s, 'from' => $from, 'to' => $to, 'closure' => $closure,
            'integrity' => $integrity, 'isWholeMonth' => $isWholeMonth, 'canClose' => $canClose,
            'closures' => PeriodClosure::orderByDesc('period')->limit(12)->get(),
            'preparedBy' => trim(auth()->user()->first_name . ' ' . auth()->user()->last_name),
        ]);
    }

    /** Owner-only. Freezes a finished month permanently. */
    public function close(Request $request, FinancialStatementService $svc)
    {
        abort_unless(auth()->user()->isOwner(), 403, 'Only the Owner can close a month.');
        $request->validate(['month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/']]);

        $start = Carbon::createFromFormat('Y-m-d', $request->month . '-01')->startOfDay();
        $end   = $start->copy()->endOfMonth();

        if (!$end->lt(now()->startOfDay())) {
            return back()->with('error', 'A month can only be closed after it has ended.');
        }
        if (PeriodClosure::isClosed($start)) {
            return back()->with('error', $start->format('F Y') . ' is already closed.');
        }

        try {
            DB::transaction(function () use ($svc, $start, $end) {
                $s    = $svc->build($start, $end);
                $prev = PeriodClosure::orderByDesc('period')->first();
                $at   = now()->format('Y-m-d H:i:s');
                $name = trim(auth()->user()->first_name . ' ' . auth()->user()->last_name);

                $closure = PeriodClosure::create([
                    'period' => $start->toDateString(),
                    'revenue' => $s['revenue'], 'cogs' => $s['cogs'], 'opex' => $s['opex'], 'net_income' => $s['netIncome'],
                    'closed_by' => auth()->id(), 'closed_by_name' => $name, 'closed_at' => $at,
                    'prev_hash' => $prev?->hash,
                    'hash' => PeriodClosure::computeHash($prev?->hash, $start->toDateString(),
                        $s['revenue'], $s['cogs'], $s['opex'], $s['netIncome'], $at, $name),
                ]);

                FinancialAuditLog::record('period.closed', $closure, [
                    'period' => $start->format('Y-m'), 'revenue' => $s['revenue'], 'cogs' => $s['cogs'],
                    'opex' => $s['opex'], 'net_income' => $s['netIncome'],
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'That month was just closed by someone else.');
        }

        return redirect()->route('financial-statement.index', [
            'from' => $start->toDateString(), 'to' => $end->toDateString(),
        ])->with('success', $start->format('F Y') . ' is now closed and locked.');
    }

    /** Read-only audit trail. */
    public function auditLog(Request $request)
    {
        $q = FinancialAuditLog::query()->orderByDesc('id');
        if ($request->filled('action')) {
            $q->where('action', 'like', $request->action . '%');
        }

        $per  = 50;
        $page = max(1, (int) $request->input('page', 1));
        $rows = $q->skip(($page - 1) * $per)->take($per + 1)->get();

        return view('financial-statement.audit-log', [
            'logs' => $rows->take($per), 'hasMore' => $rows->count() > $per,
            'page' => $page, 'action' => $request->action,
        ]);
    }
}
