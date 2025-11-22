<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashFlowController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'account' => 'nullable|string|in:cash,bank',
            'flow_type' => 'nullable|string|in:masuk,keluar',
            'source_type' => 'nullable|string|in:transaction,purchases,add_funds,refunds',
        ]);


        $query = CashFlow::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Terapkan filter string (akun, tipe, sumber)
        if ($request->filled('account')) {
            $query->where('account', $request->account);
        }
        if ($request->filled('flow_type')) {
            $query->where('flow_type', $request->flow_type);
        }
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }


        $cashflows = $query->paginate(20)->withQueryString();

        $total_cash = CashFlow::where('account', 'cash')
            ->sum(DB::raw("CASE WHEN flow_type = 'masuk' THEN amount ELSE -amount END"));

        $total_bank = CashFlow::where('account', 'bank')
            ->sum(DB::raw("CASE WHEN flow_type = 'masuk' THEN amount ELSE -amount END"));

        $totalsFilteredQuery = CashFlow::query();
        if ($request->filled('start_date')) {
            $totalsFilteredQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $totalsFilteredQuery->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('account')) {
            $totalsFilteredQuery->where('account', $request->account);
        }
        if ($request->filled('source_type')) {
            $totalsFilteredQuery->where('source_type', $request->source_type);
        }

        $total_masuk_filtered = (clone $totalsFilteredQuery)->where('flow_type', 'masuk')->sum('amount');
        $total_keluar_filtered = (clone $totalsFilteredQuery)->where('flow_type', 'keluar')->sum('amount');


        return view('cashflow.index', compact(
            'cashflows',
            'total_cash',
            'total_bank',
            'total_masuk_filtered',
            'total_keluar_filtered'
        ));
    }
}
