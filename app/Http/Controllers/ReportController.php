<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\CashFlow;
use App\Models\Report;
use App\Models\User;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Refund;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{

    public function dailyReport(Request $request)

    {

        $request->validate([

            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date'   => 'nullable|date_format:Y-m-d',
            'type' => 'nullable|string|in:sales,profit,purchase',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);
        $startDate = Carbon::parse($request->start_date ?? now())
            ->timezone('Asia/Jakarta')->startOfDay();

        $endDate = Carbon::parse($request->end_date ?? now())
            ->timezone('Asia/Jakarta')->endOfDay();


        $users = User::orderBy('name')->get();
        $type = $request->type ?? 'sales';

        // Initialize variables
        $purchases = collect();
        $total_purchase_amount = 0;
        $purchase_count = 0;
        $products_purchased = 0;
        $total_delivery_cost = 0;

        if ($type === 'purchase') {
            // Query untuk laporan pembelian
            $purchaseQuery = Purchase::with(['details.product', 'supplier', 'user'])
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($request->filled('user_id')) {
                $purchaseQuery->where('user_id', $request->user_id);
            }

            $purchases = $purchaseQuery->get();
            $total_purchase_amount = $purchases->sum('total_amount');
            $purchase_count = $purchases->count();
            $products_purchased = $purchases->sum(fn($p) => $p->details->sum('quantity'));
            $total_delivery_cost = $purchases->sum('delivery_cost');
        } else {
            // Query untuk laporan penjualan/laba rugi
            $reportQuery = Report::query()
                ->where('report_type', 'laba_rugi')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($request->filled('user_id')) {
                $reportQuery->where('user_id', $request->user_id);
            }
            $reportsData = $reportQuery->get();
            $total_sales = $reportsData->sum('total_sales');
            $total_cost = $reportsData->sum('total_cost');
            $profit = $reportsData->sum('profit');
            $cash_amount = $reportsData->sum('cash_amount');
            $card_amount = $reportsData->sum('card_amount');
            $qris_amount = $reportsData->sum('qris_amount');
            $transaction_count = $reportsData->sum('transaction_count');

            $transactionQuery = Transaction::with(['details.product.units', 'user', 'refunds'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['paid', 'completed']);

            if ($request->filled('user_id')) {
                $transactionQuery->where('user_id', $request->user_id);
            }

            $transactions = $transactionQuery->get();
            $products_sold = $transactions->sum(fn($t) => $t->details->sum('quantity'));

            $refundQuery = Refund::with(['originalTransaction.user', 'user'])
                ->whereHas('originalTransaction', function ($query) use ($startDate, $endDate, $request) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    if ($request->filled('user_id')) {
                        $query->where('user_id', $request->user_id);
                    }
                });
            $refunds = $refundQuery->get();
            $total_refund_amount = $refunds->sum('total_refund_amount');
        }

        $product_reports = [];
        if ($type === 'profit' && isset($transactions)) {
            foreach ($transactions as $t) {
                foreach ($t->details as $d) {
                    if (!$d->product) continue;
                    $pid = $d->product->id;
                    $price_buy = $d->product->price_buy ?? 0; // Harga beli per Pcs (base unit)
                    $unit = $d->product->units->firstWhere('name', $d->unit_name);
                    $unit_name_key = $d->unit_name ?? 'N/A';
                    $conversion = $unit ? ($unit->conversion_value ?? 1) : 1; // misal: 12
                    $quantity_sold_in_unit = $d->quantity;
                    $quantity_in_base = $quantity_sold_in_unit * $conversion;
                    $key = $pid . '_' . $unit_name_key;

                    if (!isset($product_reports[$key])) {

                        $product_reports[$key] = [
                            'name' => $d->product->name,
                            'color' => $d->product->color,
                            'unit_name' => $unit_name_key, // [BARU] Kirim nama unit ke view
                            'quantity' => 0, // Akumulasi kuantitas per unit (misal: 2 Lusin + 1 Lusin)
                            'cost' => 0,     // Akumulasi total modal
                            'revenue' => 0,  // Akumulasi total pendapatan
                            'profit' => 0,   // Akumulasi total laba
                        ];
                    }

                    $product_reports[$key]['quantity'] += $quantity_sold_in_unit;
                    $product_reports[$key]['cost'] += $price_buy * $quantity_in_base;
                    $product_reports[$key]['revenue'] += $d->subtotal;
                    $product_reports[$key]['profit'] = $product_reports[$key]['revenue'] - $product_reports[$key]['cost'];
                }
            }
        }
        $viewData = [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'display_date' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'users' => $users,
            'type' => $type,
        ];

        if ($type === 'purchase') {
            $viewData += [
                'purchases' => $purchases,
                'total_purchase_amount' => $total_purchase_amount,
                'purchase_count' => $purchase_count,
                'products_purchased' => $products_purchased,
                'total_delivery_cost' => $total_delivery_cost,
            ];
        } else {
            $viewData += [
                'transactions' => $transactions ?? collect(),
                'total_sales' => $total_sales ?? 0,
                'transaction_count' => $transaction_count ?? 0,
                'cash_amount' => $cash_amount ?? 0,
                'card_amount' => $card_amount ?? 0,
                'qris_amount' => $qris_amount ?? 0,
                'products_sold' => $products_sold ?? 0,
                'product_reports' => $product_reports,
                'total_cost' => $total_cost ?? 0,
                'profit' => $profit ?? 0,
                'refunds' => $refunds ?? collect(),
                'total_refund_amount' => $total_refund_amount ?? 0,
            ];
        }

        return view('reports.daily', $viewData);

    }


    public function showDailyTransaction($id)
    {
        $transaction = Transaction::with(['details.product'])->findOrFail($id);
        $paymentMethods = [
            'cash' => $transaction->payment_method == 'cash' ? $transaction->total_amount : 0,
            'card' => $transaction->payment_method == 'card' ? $transaction->total_amount : 0,
            'qris' => $transaction->payment_method == 'qris' ? $transaction->total_amount : 0,
        ];
        return view('reports.daily-transaction', [
            'transaction' => $transaction,
            'paymentMethods' => $paymentMethods,
        ]);
    }


    public function downloadReport(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020',
            'report_type' => 'required|string|in:penjualan,laba_rugi,pembelian',
        ]);

        $type = $request->report_type;
        $month = $request->month;
        $year = $request->year;
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $month_name = $monthNames[$month];

        $reportQuery = Report::query()
            ->where('report_type', 'laba_rugi')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        $reportsData = $reportQuery->get();
        $total_sales = $reportsData->sum('total_sales');
        $total_cost = $reportsData->sum('total_cost');
        $profit = $reportsData->sum('profit');
        $cash_amount = $reportsData->sum('cash_amount');
        $card_amount = $reportsData->sum('card_amount');
        $qris_amount = $reportsData->sum('qris_amount');
        $transaction_count = $reportsData->sum('transaction_count');

        if ($type === 'pembelian') {
            // Query untuk laporan pembelian
            $purchases = Purchase::with(['details.product', 'supplier', 'user'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();
            $purchaseReturns = PurchaseReturn::with(['supplier', 'user', 'purchase'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();

            $total_purchase_amount = $purchases->sum('total_amount');
            $purchase_count = $purchases->count();
            $products_purchased = $purchases->sum(fn($p) => $p->details->sum('quantity'));
            $total_delivery_cost = $purchases->sum('delivery_cost');
            $purchase_return_amount = $purchaseReturns->sum('total_amount');
            $purchase_return_count = $purchaseReturns->count();

            $data = [
                'month' => $month,
                'year' => $year,
                'month_name' => $month_name,
                'report_type' => $type,
                'purchases' => $purchases,
                'total_purchase_amount' => $total_purchase_amount,
                'purchase_count' => $purchase_count,
                'products_purchased' => $products_purchased,
                'total_delivery_cost' => $total_delivery_cost,
                'purchase_returns' => $purchaseReturns,
                'purchase_return_amount' => $purchase_return_amount,
                'purchase_return_count' => $purchase_return_count,
            ];

            $filename = "Laporan-Pembelian-{$month_name}-{$year}.pdf";
        } else {
            // Query untuk laporan penjualan/laba rugi
            $transactions = Transaction::with(['details.product.units', 'user'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('status', ['paid', 'completed'])
                ->get();
            $refunds = Refund::with(['originalTransaction.user', 'user'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();
            $total_refund_amount = $refunds->sum('total_refund_amount');
            $refund_count = $refunds->count();

            $data = [
                'month' => $month, 'year' => $year, 'month_name' => $month_name,
                'report_type' => $type,
                'transactions' => $transactions,
                'transaction_count' => $transaction_count,
                'total_sales' => $total_sales,
                'cash_amount' => $cash_amount,
                'card_amount' => $card_amount,
                'qris_amount' => $qris_amount,
                'refunds' => $refunds,
                'total_refund_amount' => $total_refund_amount,
                'refund_count' => $refund_count,
            ];

            if ($type === 'laba_rugi') {
            $product_reports = [];

            // [MODIFIKASI LOGIKA LABA RUGI DIMULAI DI SINI - UNTUK PDF]
            foreach ($transactions as $t) {
                foreach ($t->details as $d) {
                    if (!$d->product) continue;

                    $pid = $d->product->id;
                    $price_buy = $d->product->price_buy ?? 0;

                    $unit = $d->product->units->firstWhere('name', $d->unit_name);

                    $unit_name_key = $d->unit_name ?? 'N/A';
                    $conversion = $unit ? ($unit->conversion_value ?? 1) : 1;

                    $quantity_sold_in_unit = $d->quantity;
                    $quantity_in_base = $quantity_sold_in_unit * $conversion;

                    // [PERUBAHAN UTAMA] Key unik
                    $key = $pid . '_' . $unit_name_key;

                    if (!isset($product_reports[$key])) {
                        $product_reports[$key] = [
                            'name' => $d->product->name,
                            'color' => $d->product->color, // <-- Tambahkan ini jika perlu di PDF
                            'unit_name' => $unit_name_key, // [BARU]
                            'quantity' => 0,
                            'cost' => 0,
                            'revenue' => 0,
                            'profit' => 0,
                        ];
                    }

                    $product_reports[$key]['quantity'] += $quantity_sold_in_unit;
                    $product_reports[$key]['cost'] += $price_buy * $quantity_in_base;
                    $product_reports[$key]['revenue'] += $d->subtotal;
                    $product_reports[$key]['profit'] = $product_reports[$key]['revenue'] - $product_reports[$key]['cost'];
                }
            }
            // [MODIFIKASI LOGIKA LABA RUGI SELESAI - UNTUK PDF]

                $data += [
                    'product_reports' => $product_reports,
                    'total_cost' => $total_cost,
                    'profit' => $profit,
                ];
                $filename = "Laporan-Laba-Rugi-{$month_name}-{$year}.pdf";
            } else {
                $filename = "Laporan-Penjualan-{$month_name}-{$year}.pdf";
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', $data);
        return $pdf->download($filename);
    }


    public function showDownloadForm()
    {
        return view('reports.download');
    }

    public function xReport()
    {
        $today = Carbon::today();
        $transactions = Transaction::whereDate('created_at', $today)->get();
        $kasAwal = \App\Models\Cashflow::where('source_type', 'add_funds')
            ->whereDate('created_at', $today)
            ->sum('amount');
        $data = [
            'total_sales' => $transactions->sum('total_amount'),
            'cash_amount' => $transactions->where('payment_method', 'cash')->sum('total_amount'),
            'card_amount' => $transactions->where('payment_method', 'card')->sum('total_amount'),
            'qris_amount' => $transactions->where('payment_method', 'qris')->sum('total_amount'),
            'transaction_count' => $transactions->count(),
            'products_sold' => TransactionDetail::whereIn('transaction_id', $transactions->pluck('id'))->sum('quantity'),
            'total_discount' => $transactions->sum('discount'),
            'kas_awal' => $kasAwal,
            'date' => $today->format('d/m/Y'),
            'user' => auth()->user()->name,
        ];

        $report = new Report();
        $report->report_type = 'x';
        $report->total_sales = $data['total_sales'];
        $report->cash_amount = $data['cash_amount'];
        $report->card_amount = $data['card_amount'];
        $report->qris_amount = $data['qris_amount'];
        $report->transaction_count = $data['transaction_count'];
        $report->user_id = auth()->id();
        $report->save();

        return view('reports.x-report', $data);
    }


    public function zReport()
    {
        $today = Carbon::today()->timezone('Asia/Jakarta');
        $transactions = Transaction::whereDate('created_at', $today)->get();
        $data = [
            'total_sales' => $transactions->sum('total_amount'),
            'cash_amount' => $transactions->where('payment_method', 'cash')->sum('total_amount'),
            'card_amount' => $transactions->where('payment_method', 'card')->sum('total_amount'),
            'qris_amount' => $transactions->where('payment_method', 'qris')->sum('total_amount'),
            'transaction_count' => $transactions->count(),
            'products_sold' => TransactionDetail::whereIn('transaction_id', $transactions->pluck('id'))->sum('quantity'),
            'total_discount' => $transactions->sum('discount'),
            'date' => $today->format('d/m/Y'),
            'user' => auth()->user()->name,
        ];

        $report = new Report();
        $report->report_type = 'z';
        $report->total_sales = $data['total_sales'];
        $report->cash_amount = $data['cash_amount'];
        $report->card_amount = $data['card_amount'];
        $report->qris_amount = $data['qris_amount'];
        $report->transaction_count = $data['transaction_count'];
        $report->user_id = auth()->id();
        $report->save();

        if (request()->has('download')) {
            return $this->downloadZReport($data);
        }

        return view('reports.z-report', $data);
    }

    private function downloadZReport($data)
    {
        $pdf = PDF::loadView('reports.z-pdf', $data);
        $filename = 'z-report-' . Carbon::today()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}
