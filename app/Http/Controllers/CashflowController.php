<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CashFlowController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'account' => 'nullable|string|in:cash,bank',
            'flow_type' => 'nullable|string|in:masuk,keluar',
            'source_type' => 'nullable|string|in:transaction,purchases,add_funds,refunds,transfer',
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

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'direction' => 'required|string|in:cash_to_bank,bank_to_cash',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];
        $direction = $validated['direction'];

        $fromAccount = $direction === 'bank_to_cash' ? 'bank' : 'cash';
        $toAccount = $direction === 'bank_to_cash' ? 'cash' : 'bank';

        // Hitung saldo akun sumber saat ini
        $availableSource = (float) CashFlow::where('account', $fromAccount)
            ->sum(DB::raw("CASE WHEN flow_type = 'masuk' THEN amount ELSE -amount END"));

        if ($amount > $availableSource) {
            $sourceLabel = $fromAccount === 'cash' ? 'kas tunai' : 'kas bank';
            return redirect()->back()->withInput()->with('error', 'Saldo ' . $sourceLabel . ' tidak mencukupi untuk transfer.');
        }

        $transferCode = 'TRF-' . now()->format('YmdHis');
        $desc = trim($validated['description'] ?? '');
        if ($desc === '') {
            $desc = $direction === 'bank_to_cash'
                ? "Transfer Kas Bank ke Tunai ({$transferCode})"
                : "Transfer Kas Tunai ke Bank ({$transferCode})";
        } else {
            $desc = $desc . " ({$transferCode})";
        }

        DB::beginTransaction();
        try {
            // Keluar dari akun sumber
            CashFlow::create([
                'user_id' => Auth::id(),
                'source_type' => 'transfer',
                'flow_type' => 'keluar',
                'account' => $fromAccount,
                'amount' => $amount,
                'description' => $desc,
            ]);

            // Masuk ke akun tujuan
            CashFlow::create([
                'user_id' => Auth::id(),
                'source_type' => 'transfer',
                'flow_type' => 'masuk',
                'account' => $toAccount,
                'amount' => $amount,
                'description' => $desc,
            ]);

            DB::commit();
            return redirect()->route('cashflow.index')->with('success', 'Transfer berhasil dicatat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal melakukan transfer: ' . $e->getMessage());
        }
    }
}
