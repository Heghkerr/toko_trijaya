<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Refund;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $appTimezone = config('app.timezone', 'Asia/Jakarta');
        $todayStart = now($appTimezone)->startOfDay();
        $todayEnd = now($appTimezone)->endOfDay();
        $utcStart = $todayStart->clone()->setTimezone('UTC');
        $utcEnd = $todayEnd->clone()->setTimezone('UTC');

        $todayTransactionsCollection = Transaction::whereBetween('created_at', [$utcStart, $utcEnd])->get();
        $todayTransactionsCount = $todayTransactionsCollection->count();
        $todayIncome = $todayTransactionsCollection->sum('total_amount');
        $totalDiscount = $todayTransactionsCollection->sum('discount');
        $grossIncome = $todayTransactionsCollection->sum(function($transaction) {
            return $transaction->total_amount + $transaction->discount;
        });

        $paymentMethods = [
            'cash' => $todayTransactionsCollection->where('payment_method', 'cash')->sum('total_amount'),
            'card' => $todayTransactionsCollection->where('payment_method', 'card')->sum('total_amount'),
            'qris' => $todayTransactionsCollection->where('payment_method', 'qris')->sum('total_amount'),
        ];
        $nonCashIncome = $paymentMethods['card'] + $paymentMethods['qris'];

        $todayRefund = Refund::whereBetween('created_at', [$utcStart, $utcEnd])->sum('total_refund_amount');
        $netIncome = $todayIncome - $todayRefund;

        // Produk terlaris dengan perhitungan diskon
        $bestSellers = Product::withGlobalStock() // Load global stock untuk cek status
            ->with(['transactionDetails' => function($query) use ($utcStart, $utcEnd) {
                $query->whereHas('transaction', function($q) use ($utcStart, $utcEnd) {
                    $q->whereBetween('created_at', [$utcStart, $utcEnd]);
                });
            }])
            ->get()
            ->map(function($product) {
                $totalSold = $product->transactionDetails->sum('quantity');
                $totalRevenue = $product->transactionDetails->sum(function($detail) {
                    return ($detail->price * $detail->quantity) - ($detail->discount ?? 0);
                });

                // Hitung global stock
                $globalStock = $product->current_global_stock;
                
                // Tentukan status stock untuk warna
                $stockStatus = 'normal'; // default
                if ($product->min_stock !== null && $globalStock <= $product->min_stock) {
                    $stockStatus = 'understock'; // Merah
                } elseif ($product->max_stock !== null && $globalStock >= $product->max_stock) {
                    $stockStatus = 'overstock'; // Orange/Warning
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'sold' => $totalSold,
                    'total_revenue' => $totalRevenue,
                    'average_price' => $totalSold > 0 ? $totalRevenue / $totalSold : $product->price,
                    'stock_status' => $stockStatus, // Tambahkan status untuk warna
                    'global_stock' => $globalStock
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(5);

        $lowStockProducts = Product::withGlobalStock() // Hitung stok
                            ->onlyUnderstock()  // Filter yang kurang dari min_stock
                            ->orderBy('current_global_stock', 'asc') // Urutkan dari yang paling sedikit
                            ->take(5)
                            ->get();
        // Produk overstock (melewati batas maksimum)
        $overStockProducts = Product::withGlobalStock()
                            ->whereRaw(
                                '(SELECT COALESCE(SUM(stock * conversion_value), 0) FROM product_units WHERE product_units.product_id = products.id) >= products.max_stock'
                            )
                            ->orderBy('current_global_stock', 'desc')
                            ->take(5)
                            ->get();
        // Kumpulan data untuk dashboard
        $data = [
            'today_transactions' => $todayTransactionsCount,
            'today_income' => $todayIncome,
            'total_discount' => $totalDiscount,
            'non_cash_income' => $nonCashIncome,
            'payment_methods' => $paymentMethods,
            'gross_income' => $grossIncome,
            'today_refund' => $todayRefund,
            'net_income' => $netIncome,
            'best_sellers' => $bestSellers,
            'low_stock_products' => $lowStockProducts,
            'over_stock_products' => $overStockProducts,
        ];

        return view('dashboard.index', $data);
    }
}
