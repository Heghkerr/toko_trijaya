<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <-- Pastikan ini ada

class SalesChartController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // [PERBAIKAN QUERY MENGGUNAKAN NAMA TABEL YANG BENAR]
        $salesData = TransactionDetail::query()
            // Join ke tabel products untuk dapat nama produk
            ->join('products', 'transaction_details.product_id', '=', 'products.id')

            // [PERBAIKAN] Join ke tabel 'product_colors'
            // (Asumsi foreign key di tabel 'products' adalah 'color_id')
            ->leftJoin('product_colors', 'products.color_id', '=', 'product_colors.id')

            ->select(
                'products.name as product_name',
                'product_colors.name as color_name', // [PERBAIKAN]
                'transaction_details.unit_name',
                // Hitung total penjualan per unit
                DB::raw('SUM(transaction_details.quantity) as sold')
            )
            // Filter berdasarkan bulan dan tahun dari transaksi
            ->whereHas('transaction', function($q) use ($month, $year) {
                $q->whereMonth('created_at', $month)
                  ->whereYear('created_at', $year)
                  // Pastikan hanya transaksi lunas (status terbaru: paid -> sent -> finished)
                  // Catatan: 'completed/closed' dipertahankan untuk kompatibilitas data lama.
                  ->whereIn('status', ['paid', 'sent', 'finished', 'completed', 'closed']);
            })
            // [PERBAIKAN] Grup berdasarkan nama produk, nama warna, dan unit
            ->groupBy('products.id', 'products.name', 'product_colors.name', 'transaction_details.unit_name')
            ->orderBy('sold', 'desc') // Urutkan dari terlaris
            ->paginate(20); // Terapkan pagination 20 item per halaman


        // [PERBAIKAN FORMAT CHART DATA]
        $chartData = [
            'labels' => $salesData->map(function($item) {
                // Buat label: "Nama Produk (Warna) - Unit"
                return $item->product_name . ' (' . ($item->color_name ?? '-') . ') - ' . $item->unit_name;
            }),
            'data' => $salesData->pluck('sold'),
        ];

        return view('charts.index', [
            'chartData' => $chartData, // Data untuk Chart.js
            'salesData' => $salesData, // Kirim data paginator ke view (untuk tabel)
            'month' => $month,
            'year' => $year,
            'months' => $this->getMonths(),
            'years' => range(2020, Carbon::now()->year),
        ]);
    }

    private function getMonths()
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }
}
