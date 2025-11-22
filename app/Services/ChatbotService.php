<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Proses pesan masuk dari WhatsApp
     */
    public function processMessage($phone, $message)
    {
        $message = strtolower(trim($message));
        $phone = $this->formatPhone($phone);

        // Cek atau buat customer
        $customer = Customer::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'Customer ' . substr($phone, -4)]
        );

        // Deteksi command
        if (strpos($message, 'menu') !== false || $message === 'hi' || $message === 'halo') {
            return $this->sendMenu($phone);
        }

        if (strpos($message, 'stok') !== false || strpos($message, 'stock') !== false) {
            return $this->handleStockQuery($phone, $message);
        }

        if (strpos($message, 'produk') !== false || $message === 'list') {
            return $this->handleProductList($phone, $message);
        }

        if (strpos($message, 'pesan') !== false || strpos($message, 'order') !== false) {
            return $this->handleOrder($phone, $message);
        }

        if (strpos($message, 'status') !== false || strpos($message, 'transaksi') !== false) {
            return $this->handleTransactionStatus($phone, $customer);
        }

        if (strpos($message, 'help') !== false || strpos($message, 'bantuan') !== false) {
            return $this->sendHelp($phone);
        }

        // Default response
        return $this->sendDefaultResponse($phone);
    }

    /**
     * Kirim menu utama
     */
    protected function sendMenu($phone)
    {
        $message = "🤖 *Menu Toko Trijaya*\n\n";
        $message .= "Silakan pilih menu:\n\n";
        $message .= "1️⃣ *STOK* - Cek stok produk\n";
        $message .= "2️⃣ *PRODUK* - Lihat daftar produk\n";
        $message .= "3️⃣ *PESAN* - Buat pesanan\n";
        $message .= "4️⃣ *STATUS* - Cek status transaksi\n";
        $message .= "5️⃣ *HELP* - Bantuan\n\n";
        $message .= "Ketik: *STOK [nama produk]* untuk cek stok\n";
        $message .= "Ketik: *PRODUK* untuk lihat semua produk\n";
        $message .= "Ketik: *PESAN [produk] [jumlah]* untuk pesan\n";
        $message .= "Ketik: *STATUS* untuk cek transaksi terakhir";

        return $this->whatsappService->sendMessage($phone, $message);
    }

    /**
     * Handle query stok
     */
    protected function handleStockQuery($phone, $message)
    {
        // Extract product name
        $productName = str_replace(['stok', 'stock', 'cek'], '', $message);
        $productName = trim($productName);

        if (empty($productName)) {
            $message = "❌ Format salah!\n\n";
            $message .= "Contoh: *STOK BOLPOIN* atau *STOK PENSIL*";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        // Cari produk
        $products = Product::where('name', 'like', '%' . $productName . '%')
            ->with('units')
            ->get();

        if ($products->isEmpty()) {
            $message = "❌ Produk *{$productName}* tidak ditemukan.\n\n";
            $message .= "Ketik *PRODUK* untuk lihat daftar produk.";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        $response = "📦 *STOK PRODUK*\n\n";
        foreach ($products as $product) {
            $response .= "🔹 *{$product->name}*\n";

            $totalStock = 0;
            foreach ($product->units as $unit) {
                $totalStock += $unit->stock * $unit->conversion_value;
                $response .= "   • {$unit->name}: {$unit->stock} ({$unit->conversion_value} pcs)\n";
            }

            $response .= "   Total: *{$totalStock} pcs*\n";
            $response .= "   Harga: Rp " . number_format($product->units->first()->price ?? 0, 0, ',', '.') . "\n\n";
        }

        return $this->whatsappService->sendMessage($phone, $response);
    }

    /**
     * Handle daftar produk
     */
    protected function handleProductList($phone, $message)
    {
        $products = Product::with('units')
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            $message = "❌ Belum ada produk tersedia.";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        $response = "📋 *DAFTAR PRODUK*\n\n";
        foreach ($products as $index => $product) {
            $firstUnit = $product->units->first();
            $price = $firstUnit ? number_format($firstUnit->price, 0, ',', '.') : '0';

            $totalStock = $product->units->sum(function($unit) {
                return $unit->stock * $unit->conversion_value;
            });

            $response .= ($index + 1) . ". *{$product->name}*\n";
            $response .= "   Harga: Rp {$price}\n";
            $response .= "   Stok: {$totalStock} pcs\n\n";
        }

        $response .= "\nKetik *STOK [nama produk]* untuk detail stok";

        return $this->whatsappService->sendMessage($phone, $response);
    }

    /**
     * Handle pesanan (placeholder - bisa dikembangkan)
     */
    protected function handleOrder($phone, $message)
    {
        $message = "📝 *FITUR PESAN*\n\n";
        $message .= "Untuk saat ini, silakan hubungi admin untuk pemesanan.\n\n";
        $message .= "Atau kunjungi toko kami:\n";
        $message .= "📍 Jl. Imam Bonjol no.336, Denpasar, Bali\n";
        $message .= "📞 0361-483400\n\n";
        $message .= "Fitur pesan online akan segera hadir! 🚀";

        return $this->whatsappService->sendMessage($phone, $message);
    }

    /**
     * Handle status transaksi
     */
    protected function handleTransactionStatus($phone, $customer)
    {
        $transaction = Transaction::where('customer_id', $customer->id)
            ->latest()
            ->first();

        if (!$transaction) {
            $message = "❌ Anda belum memiliki transaksi.\n\n";
            $message .= "Kunjungi toko kami untuk berbelanja!";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        $message = "📄 *STATUS TRANSAKSI*\n\n";
        $message .= "Kode: *{$transaction->transaction_code}*\n";
        $message .= "Tanggal: " . $transaction->created_at->format('d/m/Y H:i') . "\n";
        $message .= "Total: Rp " . number_format($transaction->total_amount, 0, ',', '.') . "\n";
        $message .= "Status: *" . strtoupper($transaction->status) . "*\n";
        $message .= "Metode: " . strtoupper($transaction->payment_method) . "\n\n";

        if ($transaction->status === 'unpaid') {
            $message .= "⚠️ Transaksi ini belum dibayar.";
        } else {
            $message .= "✅ Transaksi sudah dibayar.";
        }

        return $this->whatsappService->sendMessage($phone, $message);
    }

    /**
     * Kirim bantuan
     */
    protected function sendHelp($phone)
    {
        $message = "❓ *BANTUAN*\n\n";
        $message .= "Berikut adalah perintah yang tersedia:\n\n";
        $message .= "• *MENU* - Tampilkan menu utama\n";
        $message .= "• *STOK [produk]* - Cek stok produk\n";
        $message .= "• *PRODUK* - Lihat daftar produk\n";
        $message .= "• *STATUS* - Cek status transaksi terakhir\n";
        $message .= "• *HELP* - Tampilkan bantuan ini\n\n";
        $message .= "📞 Hubungi kami:\n";
        $message .= "0361-483400\n\n";
        $message .= "📍 Alamat:\n";
        $message .= "Jl. Imam Bonjol no.336, Denpasar, Bali";

        return $this->whatsappService->sendMessage($phone, $message);
    }

    /**
     * Default response
     */
    protected function sendDefaultResponse($phone)
    {
        $message = "🤖 *Toko Trijaya Chatbot*\n\n";
        $message .= "Maaf, saya tidak mengerti pesan Anda.\n\n";
        $message .= "Ketik *MENU* untuk melihat menu yang tersedia.\n";
        $message .= "Ketik *HELP* untuk bantuan.";

        return $this->whatsappService->sendMessage($phone, $message);
    }

    /**
     * Format nomor telepon
     */
    protected function formatPhone($phone)
    {
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Jika tidak dimulai dengan 62, tambahkan
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}

