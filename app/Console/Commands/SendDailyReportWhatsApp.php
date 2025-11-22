<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsappService;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\CashFlow;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyReportWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-whatsapp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim laporan harian via WhatsApp setiap jam 5 sore';

    protected $whatsappService;

    /**
     * Create a new command instance.
     */
    public function __construct(WhatsappService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengiriman laporan harian via WhatsApp...');

        // Ambil nomor telepon owner dari environment variable
        $ownerPhone = env('OWNER_PHONE');

        if (!$ownerPhone) {
            $this->error('OWNER_PHONE belum di-set di file .env');
            Log::error('OWNER_PHONE belum di-set di file .env');
            return 1;
        }

        // Ambil data laporan hari ini
        // $yesterday = Carbon::yesterday()->timezone('Asia/Jakarta');
        $today = Carbon::today()->timezone('Asia/Jakarta');
        $startDate = $today->copy()->startOfDay();
        $endDate = $today->copy()->endOfDay();

        // Ambil data dari Report model (laba rugi)
        $reportsData = Report::where('report_type', 'laba_rugi')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Hitung total
        $total_sales = $reportsData->sum('total_sales');
        $total_cost = $reportsData->sum('total_cost');
        $profit = $reportsData->sum('profit');
        $cash_amount = $reportsData->sum('cash_amount');
        $card_amount = $reportsData->sum('card_amount');
        $qris_amount = $reportsData->sum('qris_amount');
        $transaction_count = $reportsData->sum('transaction_count');

        // Ambil data transaksi untuk produk terjual
        $transactions = Transaction::with(['details.product.color', 'details.product.type'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->get();

        $products_sold = $transactions->sum(fn($t) => $t->details->sum('quantity'));

        // Kumpulkan data produk yang laku dengan detail
        $productSales = [];
        foreach ($transactions as $transaction) {
            foreach ($transaction->details as $detail) {
                if (!$detail->product) continue;

                $productName = $detail->product->name;
                if ($detail->product->color) {
                    $productName .= ' (' . $detail->product->color->name . ')';
                }
                $unitName = $detail->unit_name ?? 'pcs';
                $key = $productName . '|' . $unitName;

                if (!isset($productSales[$key])) {
                    $productSales[$key] = [
                        'name' => $productName,
                        'unit' => $unitName,
                        'quantity' => 0,
                        'total_price' => 0,
                    ];
                }

                $productSales[$key]['quantity'] += $detail->quantity;
                $productSales[$key]['total_price'] += $detail->subtotal;
            }
        }

        // Hitung harga rata-rata per unit untuk setiap produk
        foreach ($productSales as $key => $product) {
            if ($product['quantity'] > 0) {
                $productSales[$key]['price_per_unit'] = $product['total_price'] / $product['quantity'];
            } else {
                $productSales[$key]['price_per_unit'] = 0;
            }
        }

        // Ambil data refund
        $refunds = Refund::with(['originalTransaction'])
            ->whereHas('originalTransaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        $total_refund_amount = $refunds->sum('total_refund_amount');

        // Ambil data kas
        $kasMasuk = CashFlow::where(['flow_type' => 'masuk', 'account' => 'cash'])
            ->whereDate('created_at', $today)
            ->sum('amount');
        $kasKeluar = CashFlow::where(['flow_type' => 'keluar', 'account' => 'cash'])
            ->whereDate('created_at', $today)
            ->sum('amount');
        $kasSaatIni = $kasMasuk - $kasKeluar;

        // Generate pesan WhatsApp
        $message = $this->generateDailyReportMessage(
            $today,
            $total_sales,
            $total_cost,
            $profit,
            $cash_amount,
            $card_amount,
            $qris_amount,
            $transaction_count,
            $products_sold,
            $total_refund_amount,
            $kasSaatIni,
            $productSales
        );

        // Kirim via WhatsApp
        $result = $this->whatsappService->sendMessage($ownerPhone, $message);

        if ($result && isset($result['status']) && $result['status'] === true) {
            $this->info("Laporan harian berhasil dikirim ke WhatsApp: {$ownerPhone}");
            Log::info("Laporan harian berhasil dikirim via WhatsApp ke: {$ownerPhone}");
            return 0;
        } else {
            $errorMsg = $result['message'] ?? 'Gagal mengirim pesan';
            $this->error("Gagal mengirim laporan harian: {$errorMsg}");
            Log::error("Gagal kirim laporan harian via WhatsApp: {$errorMsg}", $result ?? []);
            return 1;
        }
    }

    /**
     * Generate format laporan harian untuk WhatsApp
     */
    protected function generateDailyReportMessage(
        $date,
        $total_sales,
        $total_cost,
        $profit,
        $cash_amount,
        $card_amount,
        $qris_amount,
        $transaction_count,
        $products_sold,
        $total_refund_amount,
        $kasSaatIni,
        $productSales = []
    ) {
        $message = "📊 *LAPORAN HARIAN TOKO TRIJAYA*\n\n";

        $message .= "📅 Tanggal: " . $date->format('d/m/Y') . "\n";
        $message .= "🕐 Waktu: " . Carbon::now()->timezone('Asia/Jakarta')->format('H:i') . " WIB\n";
        $message .= "🛒 Jumlah Transaksi: " . number_format($transaction_count, 0, ',', '.') . " transaksi\n\n";

        // Tampilkan detail produk yang laku
        if (!empty($productSales)) {


            foreach ($productSales as $product) {
                $message .= "• " . $product['name'] . "\n";
                $message .= "  " . number_format($product['quantity'], 0, ',', '.') . " " . $product['unit'];
                $message .= " × Rp " . number_format($product['price_per_unit'], 0, ',', '.');
                $message .= " = Rp " . number_format($product['total_price'], 0, ',', '.') . "\n\n";
            }
        }

        $message .= "💵 Cash: Rp " . number_format($cash_amount, 0, ',', '.') . "\n";
        $message .= "💳 Card: Rp " . number_format($card_amount, 0, ',', '.') . "\n";
        $message .= "📱 QRIS: Rp " . number_format($qris_amount, 0, ',', '.') . "\n\n";

        $message .= "💰 Total Penjualan: Rp " . number_format($total_sales, 0, ',', '.') . "\n";
        $message .= "💸 Total HPP: Rp " . number_format($total_cost, 0, ',', '.') . "\n";

        if ($total_refund_amount > 0) {
            $message .= "↩️ Total Refund: Rp " . number_format($total_refund_amount, 0, ',', '.') . "\n";
        }

        $profitColor = $profit >= 0 ? "✅" : "❌";
        $message .= "{$profitColor} Laba/Rugi: Rp " . number_format($profit, 0, ',', '.') . "\n\n";

        $message .= "💰 Saldo Kas: Rp " . number_format($kasSaatIni, 0, ',', '.') . "\n";

        return $message;
    }
}

