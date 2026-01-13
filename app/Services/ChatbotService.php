<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\ProductType;
use App\Models\ProductColor;
use App\Models\WhatsappOrder;
use App\Models\WhatsappOrderItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
        // Trim dan lowercase
        $message = strtolower(trim($message));
        $phone = $this->formatPhone($phone);

        // Log pesan setelah processing
        Log::info('WhatsApp Message After Processing', [
            'phone' => $phone,
            'processed_message' => $message,
            'is_numeric' => is_numeric($message),
            'message_length' => strlen($message),
        ]);

        // ========================================
        // PRIORITAS TERTINGGI: Command "0" atau "MENU"
        // Harus diproses SEBELUM apapun, bahkan sebelum send menu otomatis
        // ========================================
        $isZeroCommand = (
            $message === '0' ||           // String exact match
            $message == '0' ||            // Loose comparison
            $message === 0 ||             // Integer exact match
            $message == 0 ||              // Loose comparison dengan integer
            (is_numeric($message) && intval($message) === 0) || // Numeric check
            trim($message) === '0'        // Extra trim
        );

        $isMenuCommand = (
            $message === 'menu' ||
            strpos($message, 'menu') === 0 ||
            $message === 'MENU' ||
            strtolower($message) === 'menu'
        );

        // Log hasil pengecekan
        Log::info('WhatsApp Command Check', [
            'phone' => $phone,
            'message' => $message,
            'isZeroCommand' => $isZeroCommand,
            'isMenuCommand' => $isMenuCommand,
        ]);

        if ($isZeroCommand || $isMenuCommand) {
            Log::info('WhatsApp Command Matched: Sending Menu', [
                'phone' => $phone,
                'command_type' => $isZeroCommand ? 'zero' : 'menu',
            ]);

            // Clear semua state
            Cache::forget('order_state_' . $phone);
            Cache::forget('catalog_state_' . $phone);
            Cache::forget('last_context_' . $phone);

            // Kirim menu
            return $this->sendMenu($phone);
        }

        // Cek atau buat customer
        $customer = Customer::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'Customer ' . substr($phone, -4)]
        );

        // Cek apakah sudah pernah kirim menu ke nomor ini hari ini
        $menuSentKey = 'chatbot_menu_sent_' . $phone . '_' . date('Y-m-d');
        $menuAlreadySent = Cache::has($menuSentKey);

        // Jika belum pernah kirim menu hari ini, kirim menu dulu (sekali per hari)
        if (!$menuAlreadySent) {
            $this->sendMenu($phone);
            // Set cache sampai akhir hari (tengah malam)
            $endOfDay = now()->endOfDay();
            $secondsUntilMidnight = now()->diffInSeconds($endOfDay, false);
            Cache::put($menuSentKey, true, $secondsUntilMidnight);
        }

        // Cek apakah user sedang dalam proses pemesanan (cek dulu sebelum command lain)
        $orderState = Cache::get('order_state_' . $phone);
        if ($orderState) {
            // Cek apakah user ingin membatalkan
            $cancelKeywords = ['batal', 'cancel', 'batalkan', 'tidak jadi', 'gagal'];
            foreach ($cancelKeywords as $keyword) {
                if ($message === $keyword || strpos($message, $keyword) === 0) {
                Cache::forget('order_state_' . $phone);
                Cache::forget('last_context_' . $phone);
                $response = "❌ *Pesanan dibatalkan.*\n\n";
                $response .= "━━━━━━━━━━━━━━━━\n";
                $response .= "Ketik *3/PESAN* untuk membuat pesanan baru\n";
                $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
                $response .= "Ketik *2/STOK [nama produk]* untuk cek stok\n";
                $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
                $response .= "Ketik *0/MENU* untuk kembali ke menu";
                return $this->whatsappService->sendMessage($phone, $response);
                }
            }
            return $this->handleOrderStep($phone, $message, $orderState);
        }

        // Cek apakah pesan mengandung format pesanan (NAMA: atau PESANAN:)
        // Jika ya, langsung proses sebagai pesanan, bukan command
        $originalMessage = $message; // Simpan original untuk parsing
        if (preg_match('/nama\s*:/i', $message) || preg_match('/pesanan\s*:/i', $message)) {
            // Ini format pesanan, mulai proses pemesanan
            Cache::put('order_state_' . $phone, [
                'step' => 'waiting_order',
                'data' => []
            ], now()->addMinutes(30));
            return $this->handleOrderStep($phone, $originalMessage, [
                'step' => 'waiting_order',
                'data' => []
            ]);
        }

        // Deteksi command dan proses jika sesuai
        if (strpos($message, 'stok') !== false || strpos($message, 'stock') !== false) {
            return $this->handleStockQuery($phone, $message);
        }

        if (strpos($message, 'katalog') !== false || strpos($message, 'catalog') !== false) {
            return $this->handleCatalog($phone, $message);
        }

        // Cek command untuk melihat pesanan
        if (strpos($message, 'cek pesanan') !== false ||
            strpos($message, 'pesanan saya') !== false ||
            strpos($message, 'riwayat pesanan') !== false ||
            strpos($message, 'lihat pesanan') !== false) {
            return $this->handleViewOrders($phone);
        }

        // Cek command untuk membatalkan pesanan
        if (strpos($message, 'batal pesanan') !== false ||
            strpos($message, 'cancel pesanan') !== false) {
            // Extract order ID jika ada
            preg_match('/\d+/', $message, $matches);
            $orderId = $matches[0] ?? null;
            return $this->handleCancelOrder($phone, $orderId);
        }

        // Cek command "pesan" - hanya jika pesan benar-benar command "pesan" saja
        // Bukan jika ada kata "pesanan" di tengah pesan
        if ($message === 'pesan' || $message === 'order') {
            return $this->handleOrder($phone, $message);
        }

        // Cek apakah customer mengucapkan terimakasih
        $thankYouKeywords = ['terimakasih', 'terima kasih', 'makasih', 'thanks', 'thank you', 'terima kasih ya', 'makasih ya'];
        foreach ($thankYouKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $response = "Sama-sama kak! 😊\n\n";
                $response .= "━━━━━━━━━━━━━━━━\n";
                $response .= "Jika ada yang bisa dibantu, silakan ketik:\n\n";
                $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
                $response .= "Ketik *2/STOK [nama produk]* untuk cek stok\n";
                $response .= "Ketik *3/PESAN* untuk buat pesanan\n";
                $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
                $response .= "Ketik *0/MENU* untuk kembali ke menu";

                return $this->whatsappService->sendMessage($phone, $response);
            }
        }

        // Cek apakah pesan hanya berupa angka (menu cepat atau pilihan katalog)
        $catalogState = Cache::get('catalog_state_' . $phone);
        $lastContext = Cache::get('last_context_' . $phone); // Track context terakhir user

        if (is_numeric($message)) {
            // Jika sedang lihat katalog, angka berarti pilih jenis
            if ($catalogState && isset($catalogState['viewing_types']) && $catalogState['viewing_types']) {
                return $this->showProductsByType($phone, $message);
            }

            // Shortcut angka berlaku universal (bisa digunakan kapan saja)
            // Note: "0" sudah dihandle di awal sebagai prioritas tertinggi
            switch ($message) {
                case '1': // KATALOG
                    return $this->handleCatalog($phone, 'katalog');
                case '2': // STOK
                    $msg = "📦 *CEK STOK PRODUK*\n\n";
                    $msg .= "Untuk cek stok, ketik:\n*STOK [nama produk]*\n\n";
                    $msg .= "Contoh:\n";
                    $msg .= "• *STOK KELING 10* atau *STOK K10*\n";
                    $msg .= "• *STOK KANCING 8*\n\n";
                    $msg .= "━━━━━━━━━━━━━━━━\n";
                    $msg .= "Ketik *1* untuk KATALOG\n";
                    $msg .= "Ketik *3* untuk PESAN\n";
                    $msg .= "Ketik *4* untuk CEK PESANAN\n";
                    $msg .= "Ketik *0/MENU* untuk kembali ke menu";
                    return $this->whatsappService->sendMessage($phone, $msg);
                case '3': // PESAN
                    return $this->handleOrder($phone, 'pesan');
                case '4': // CEK PESANAN
                    return $this->handleViewOrders($phone);
                default:
                    // Angka lain: kirim ulang menu agar jelas
                    return $this->sendMenu($phone);
            }
        }

        // Jika user baru saja lihat katalog produk, coba deteksi apakah ini nama produk untuk cek stok
        if ($lastContext && $lastContext['action'] === 'viewed_products') {
            // User baru lihat daftar produk, mungkin ingin cek stok produk tertentu
            // Coba cari produk dengan nama yang diketik
            $normalizedName = $this->normalizeProductName($message);
            $product = Product::where(function($query) use ($normalizedName, $message) {
                    $query->where('name', 'like', '%' . $normalizedName . '%')
                          ->orWhere('name', 'like', '%' . strtoupper($message) . '%');
                })
                ->with(['units', 'color', 'type'])
                ->first();

            if ($product) {
                // Produk ditemukan! Auto cek stok
                return $this->handleStockQuery($phone, 'stok ' . $message);
            }
        }

        // Jika tidak sesuai command, tidak kirim balasan
        return null;
    }

    /**
     * Kirim menu utama
     */
    protected function sendMenu($phone)
    {
        // Reset semua state agar pilihan angka kembali ke menu utama
        Cache::forget('catalog_state_' . $phone);
        Cache::forget('last_context_' . $phone);

        $message = "Halo kak, terimakasih sudah chat ke Toko Trijaya! 😊\n\n";
        $message .= "🤖 *MENU TOKO TRIJAYA*\n\n";
        $message .= "Silakan pilih menu:\n\n";
        $message .= "1️⃣ *KATALOG* - Lihat katalog produk\n";
        $message .= "2️⃣ *STOK* - Cek stok produk\n";
        $message .= "3️⃣ *PESAN* - Buat pesanan\n";
        $message .= "4️⃣ *CEK PESANAN* - Lihat & batalkan pesanan\n\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "💡 *Cara menggunakan:*\n\n";
        $message .= "Ketik *1* atau *KATALOG* untuk lihat katalog\n";
        $message .= "Ketik *2* atau *STOK [nama produk]* untuk cek stok\n";
        $message .= "Ketik *3* atau *PESAN* untuk membuat pesanan\n";
        $message .= "Ketik *4* atau *CEK PESANAN* untuk lihat pesanan\n\n";
        $message .= "📝 *Tips:* Angka 1/2/3/4 bisa digunakan kapan saja untuk navigasi cepat!";

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
            $message .= "Contoh: *STOK KELING* atau *STOK K10* atau *STOK KELING 10*";

            $message .= "Ketik: *MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        // Normalisasi nama produk: "keling 10" atau "k10" → "K10"
        $normalizedProductName = $this->normalizeProductName($productName);

        // Cari produk dengan nama normalisasi atau nama asli
        $products = Product::where(function($query) use ($normalizedProductName, $productName) {
                $query->where('name', 'like', '%' . $normalizedProductName . '%')
                      ->orWhere('name', 'like', '%' . strtoupper($productName) . '%');
            })
            ->with(['units', 'type', 'color'])
            ->get();

        if ($products->isEmpty()) {
            $message = "❌ Produk *{$productName}* tidak ditemukan.\n\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "Ketik *1/KATALOG* untuk lihat katalog\n";
            $message .= "Ketik *3/PESAN* untuk membuat pesanan\n";
            $message .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
            $message .= "Ketik *0/MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        $response = "📦 *STOK PRODUK*\n\n";
        foreach ($products as $product) {
            $response .= "🔹 *{$product->name}*\n";

            // Tampilkan type dan color
            if ($product->type) {
                $response .= "   Tipe: {$product->type->name}\n";
            }
            if ($product->color) {
                $response .= "   Warna: {$product->color->name}\n";
            }

            // Hitung total stok
            $totalStock = 0;
            foreach ($product->units as $unit) {
                $totalStock += $unit->stock * $unit->conversion_value;
            }

            // Ambil unit pertama untuk harga
            $firstUnit = $product->units->first();
            $price = $firstUnit ? number_format($firstUnit->price, 0, ',', '.') : '0';
            $unitName = $firstUnit ? strtoupper($firstUnit->name) : '';

            $response .= "   Stok: *{$totalStock} pcs*\n";
            $response .= "   Harga: Rp {$price} per {$unitName}\n\n";
        }

        // Tambahkan instruksi navigasi setelah cek stok
        $response .= "━━━━━━━━━━━━━━━━\n";
        $response .= "💡 *Apa yang ingin dilakukan?*\n\n";
        $response .= "Ketik *STOK [nama produk lain]* untuk cek stok lainnya\n";
        $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
        $response .= "Ketik *3/PESAN* untuk membuat pesanan\n";
        $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
        $response .= "Ketik *0/MENU* untuk kembali ke menu";

        return $this->whatsappService->sendMessage($phone, $response);
    }

    /**
     * Normalisasi nama produk - convert "keling 10" atau "k10" menjadi "K10"
     *
     * @param string $input
     * @return string
     */
    protected function normalizeProductName($input)
    {
        $input = trim(strtolower($input));

        // Pattern 1: "keling 10", "kancing 8" → "K10", "K8"
        if (preg_match('/^(keling|kancing|k)\s+(\d+)$/i', $input, $matches)) {
            $prefix = strtolower($matches[1]);
            $number = $matches[2];

            // Mapping prefix ke format database
            $prefixMap = [
                'keling' => 'K',
                'kancing' => 'KC',
                'k' => 'K'
            ];

            $prefix = $prefixMap[$prefix] ?? strtoupper($prefix);
            return $prefix . $number;
        }

        // Pattern 2: "k10", "k-10", "k_10" → "K10"
        if (preg_match('/^([a-z]+)[\s\-_]?(\d+)$/i', $input, $matches)) {
            $prefix = strtolower($matches[1]);
            $number = $matches[2];

            // Mapping singkatan
            $shortcuts = [
                'k' => 'K',
                'kc' => 'K',
                'kac' => 'K'
            ];

            $prefix = $shortcuts[$prefix] ?? strtoupper($prefix);
            return $prefix . $number;
        }

        // Jika tidak match pattern, return uppercase
        return strtoupper($input);
    }

    /**
     * Handle pesanan - mulai proses pemesanan
     */
    protected function handleOrder($phone, $message)
    {
        // Mulai proses pemesanan - minta format sekali kirim
        Cache::put('order_state_' . $phone, [
            'step' => 'waiting_order',
            'data' => []
        ], now()->addMinutes(30)); // Expire setelah 30 menit

        $response = "📝 *PEMESANAN PRODUK*\n\n";
        $response .= "Silakan kirim pesanan Anda dengan format:\n\n";
        $response .= "*NAMA: [Nama Lengkap]*\n";
        $response .= "*PESANAN: [JUMLAH] [SATUAN] [PRODUK] warna [WARNA], ...*\n\n";
        $response .= "📦 *Contoh format pesanan:*\n";
        $response .= "NAMA: DARREN\n";
        $response .= "PESANAN: 2 LUSIN KELING 10 warna NKL, 1 GROSAN KANCING 8 warna MERAH\n\n";
        $response .= "📌 *PENTING:*\n";
        $response .= "• Jumlah & satuan harus disebutkan (contoh: 2 LUSIN)\n";
        $response .= "• Warna harus disebutkan (contoh: warna NKL)\n";
        $response .= "• Gunakan koma (,) untuk pisahkan multiple item\n\n";
        $response .= "💡 *Satuan umum:* LUSIN, GROSAN, PAK, PCS, KODI\n\n";
        $response .= "Ketik *BATAL* atau *0* jika tidak jadi pesan.";

        return $this->whatsappService->sendMessage($phone, $response);
    }

    /**
     * Handle step pemesanan (interaktif)
     */
    protected function handleOrderStep($phone, $message, $orderState)
    {
        $step = $orderState['step'];
        $data = $orderState['data'] ?? [];

        if ($step === 'waiting_order') {
            // Cek apakah user ingin membatalkan
            $cancelKeywords = ['batal', 'cancel', 'batalkan', 'tidak jadi', 'gagal'];
            $messageLower = strtolower(trim($message));
            if ($messageLower === '0') {
                Cache::forget('order_state_' . $phone);
                return $this->sendMenu($phone);
            }
            foreach ($cancelKeywords as $keyword) {
                if ($messageLower === $keyword || $messageLower === $keyword) {
                    Cache::forget('order_state_' . $phone);
                    Cache::forget('last_context_' . $phone);
                    $response = "❌ *Pesanan dibatalkan.*\n\n";
                    $response .= "━━━━━━━━━━━━━━━━\n";
                    $response .= "Ketik *3/PESAN* untuk membuat pesanan baru\n";
                    $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
                    $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
                    $response .= "Ketik *0/MENU* untuk kembali ke menu";
                    return $this->whatsappService->sendMessage($phone, $response);
                }
            }

            // Parse format: NAMA: ... PESANAN: ...
            $parsed = $this->parseOrderMessage($message);

            if (!$parsed || empty($parsed['name']) || empty($parsed['order'])) {
                $response = "❌ *Format pesanan tidak sesuai!*\n\n";
                $response .= "Silakan gunakan format:\n";
                $response .= "*NAMA: [Nama Lengkap]*\n";
                $response .= "*PESANAN: [JUMLAH] [SATUAN] [PRODUK] warna [WARNA], ...*\n\n";
                $response .= "📦 *Contoh:*\n";
                $response .= "NAMA: DARREN\n";
                $response .= "PESANAN: 2 LUSIN KELING 10 warna NKL, 1 GROSAN KANCING 8 warna MERAH\n\n";
                $response .= "📌 *PENTING:*\n";
                $response .= "• Jumlah & satuan harus disebutkan\n";
                $response .= "• Warna harus disebutkan\n";
                $response .= "• Pisahkan dengan koma (,) untuk multiple item\n\n";
                $response .= "💡 *Satuan umum:* LUSIN, GROSAN, PAK, PCS, KODI\n\n";
                $response .= "━━━━━━━━━━━━━━━━\n";
                $response .= "Ketik *BATAL* atau *0* untuk membatalkan";

                return $this->whatsappService->sendMessage($phone, $response);
            }

            // Simpan data dan kirim konfirmasi
            $data['name'] = trim($parsed['name']);
            $data['order'] = trim($parsed['order']);
            $data['phone'] = $phone;

            // Cek stok berdasarkan teks pesanan (wajib ada nama produk + warna)
            $stockCheck = $this->verifyStockForOrder($data['order']);
            if (!$stockCheck['ok']) {
                return $this->whatsappService->sendMessage($phone, $stockCheck['message']);
            }

            Cache::put('order_state_' . $phone, [
                'step' => 'confirmation',
                'data' => array_merge($data, ['items' => $stockCheck['items']])
            ], now()->addMinutes(30));

            // Kirim konfirmasi ke customer
            $response = "📋 *KONFIRMASI PESANAN*\n\n";
            $response .= "Mohon periksa data pesanan Anda:\n\n";
            $response .= "👤 NAMA: *{$data['name']}*\n";
            $response .= "📦 PESANAN: *{$data['order']}*\n\n";
            $response .= "━━━━━━━━━━━━━━━━\n";
            $response .= "📋 *Detail & Ketersediaan Stok:*\n\n";
            foreach ($stockCheck['items'] as $item) {
                if (isset($item['unit_name'])) {
                    // Format baru dengan unit
                    $response .= "✓ *{$item['product_name']}* ({$item['color_name']})\n";
                    $response .= "   Pesanan: {$item['quantity']} {$item['unit_name']}\n";
                    $response .= "   Stok: {$item['stock_available']} {$item['unit_name']} tersedia ✅\n\n";
                } else {
                    // Format lama (backward compatibility)
                    $response .= "✓ *{$item['product_name']}* ({$item['color_name']})\n";
                    $response .= "   Stok: {$item['stock_pcs']} pcs tersedia ✅\n\n";
                }
            }
            $response .= "━━━━━━━━━━━━━━━━\n\n";
            $response .= "Apakah data di atas sudah benar?\n\n";
            $response .= "Ketik *YA* atau *BENAR* untuk mengirim pesanan\n";
            $response .= "Ketik *BATAL* atau *0* untuk membatalkan";

            return $this->whatsappService->sendMessage($phone, $response);
        }

        if ($step === 'confirmation') {
            // Cek konfirmasi customer
            $confirmMessage = strtolower(trim($message));
            if ($confirmMessage === '0') {
                Cache::forget('order_state_' . $phone);
                return $this->sendMenu($phone);
            }
            $isConfirmed = in_array($confirmMessage, ['ya', 'benar', 'yes', 'ok', 'setuju']);

            if ($isConfirmed) {
                // ======================================================
                // LOCK MECHANISM - Prevent Race Condition
                // ======================================================
                // Gunakan cache lock untuk memastikan hanya 1 konfirmasi yang diproses
                $lockKey = 'order_lock_' . md5($data['phone'] . $data['order']);
                $lock = Cache::lock($lockKey, 10); // Lock for 10 seconds

                Log::info('Attempting to acquire order creation lock', [
                    'lock_key' => $lockKey,
                    'phone' => $data['phone'],
                    'customer' => $data['name']
                ]);

                // Coba acquire lock
                if (!$lock->get()) {
                    // Jika tidak dapat lock, berarti ada proses lain yang sedang create order
                    Log::info('Order creation blocked by lock - another process is creating order', [
                        'phone' => $data['phone'],
                        'lock_key' => $lockKey
                    ]);

                    // Wait 2 seconds dan check apakah order sudah dibuat
                    sleep(2);

                    $justCreatedOrder = WhatsappOrder::where('phone', $data['phone'])
                        ->where('order_text', $data['order'])
                        ->where('created_at', '>=', now()->subSeconds(10))
                        ->first();

                    if ($justCreatedOrder) {
                        Cache::forget('order_state_' . $phone);

                        $response = "✅ *Pesanan Anda telah diterima!*\n\n";
                        $response .= "📋 *Order ID: #{$justCreatedOrder->id}*\n";
                        $response .= "👤 Nama: {$data['name']}\n";
                        $response .= "📦 Status: *PENDING* 🟡\n\n";
                        $response .= "Terima kasih atas pesanan Anda.\n";
                        $response .= "Kami akan segera memprosesnya. 😊\n\n";
                        $response .= "Ketik *0/MENU* untuk kembali ke menu utama";

                        return $this->whatsappService->sendMessage($phone, $response);
                    }

                    // Jika masih tidak ada, return generic message
                    Cache::forget('order_state_' . $phone);
                    return $this->whatsappService->sendMessage($phone,
                        "Pesanan Anda sedang diproses. Mohon tunggu sebentar. 😊");
                }

                Log::info('Lock acquired successfully - processing order', [
                    'lock_key' => $lockKey,
                    'phone' => $data['phone']
                ]);

                try {
                    // ======================================================
                    // DUPLICATE ORDER PREVENTION
                    // ======================================================
                    // Check apakah ada order yang sama dalam 5 menit terakhir
                    // HANYA untuk order dengan status PENDING atau CONFIRMED
                    // Order dengan status CANCELLED atau PROCESSED tidak dihitung duplicate
                    $existingOrder = WhatsappOrder::where('phone', $data['phone'])
                        ->where('order_text', $data['order'])
                        ->whereIn('status', ['pending', 'confirmed']) // Only check active orders
                        ->where('created_at', '>=', now()->subMinutes(5))
                        ->first();

                    if ($existingOrder) {
                        Log::warning('Duplicate order detected', [
                            'phone' => $data['phone'],
                            'order' => $data['order'],
                            'existing_order_id' => $existingOrder->id,
                            'existing_order_status' => $existingOrder->status,
                            'existing_order_time' => $existingOrder->created_at
                        ]);

                        // Release lock before returning
                        $lock->release();

                        Cache::forget('order_state_' . $phone);

                        $response = "⚠️ *Pesanan sudah diterima sebelumnya!*\n\n";
                        $response .= "Order ID: #{$existingOrder->id}\n";
                        $response .= "Waktu: " . $existingOrder->created_at->format('d/m/Y H:i') . "\n";
                        $response .= "Status: " . strtoupper($existingOrder->status) . " 🟡\n\n";
                        $response .= "Pesanan Anda sedang dalam proses.\n";
                        $response .= "Tidak perlu kirim pesanan lagi. 😊\n\n";
                        $response .= "Ketik *4/CEK PESANAN* untuk cek status\n";
                        $response .= "Ketik *0/MENU* untuk kembali ke menu utama";

                        return $this->whatsappService->sendMessage($phone, $response);
                    }

                // Kirim ke owner
                $ownerPhone = config('services.chatbot.owner_phone', env('OWNER_PHONE'));
                if ($ownerPhone) {
                    Log::info('Sending notification to OWNER', [
                        'owner_phone' => $ownerPhone,
                        'customer' => $data['name'],
                        'order' => substr($data['order'], 0, 50)
                    ]);

                    $ownerMessage = "📦 *PESANAN BARU*\n\n";
                    $ownerMessage .= "NAMA: *{$data['name']}*\n";
                    $ownerMessage .= "PESANAN: *{$data['order']}*\n";
                    $ownerMessage .= "Nomor: *{$data['phone']}*\n\n";
                    $ownerMessage .= "Waktu: " . now()->format('d/m/Y H:i');

                    $ownerResult = $this->whatsappService->sendMessage($ownerPhone, $ownerMessage);

                    Log::info('Owner notification sent - RESULT', [
                        'owner_phone' => $ownerPhone,
                        'result' => $ownerResult
                    ]);
                } else {
                    Log::warning('Owner phone not configured - notification NOT sent');
                }

                // Simpan ke database untuk dashboard WhatsApp dengan status pending
                $whatsappOrder = WhatsappOrder::create([
                    'name'       => $data['name'],
                    'phone'      => $data['phone'],
                    'order_text' => $data['order'],
                    'status'     => 'pending', // Ubah dari 'confirmed' ke 'pending'
                    'items'      => $data['items'] ?? [], // Tetap simpan di JSON untuk backward compatibility
                ]);

                Log::info('WhatsApp Order created successfully', [
                    'order_id' => $whatsappOrder->id,
                    'customer' => $data['name'],
                    'phone' => $data['phone']
                ]);

                // Simpan detail items ke tabel whatsapp_order_items
                if (!empty($data['items'])) {
                    foreach ($data['items'] as $item) {
                        WhatsappOrderItem::create([
                            'whatsapp_order_id' => $whatsappOrder->id,
                            'product_id'        => $item['product_id'] ?? null,
                            'color_id'          => $item['color_id'] ?? null,
                            'product_unit_id'   => $item['product_unit_id'] ?? null, // Unit yang dipesan
                            'quantity'          => $item['quantity'] ?? 1, // Jumlah yang dipesan
                            'stock_pcs'         => $item['stock_pcs'] ?? 0,
                        ]);
                    }
                }

                // Prepare success response
                Log::info('Preparing success message', [
                    'order_id' => $whatsappOrder->id,
                    'customer' => $data['name'],
                    'phone' => $phone
                ]);

                $response = "✅ *Pesanan Anda telah diterima!*\n\n";
                $response .= "📋 *Order ID: #{$whatsappOrder->id}*\n";
                $response .= "👤 Nama: {$data['name']}\n";
                $response .= "📦 Status: *PENDING* 🟡\n\n";
                $response .= "Terima kasih atas pesanan Anda.\n";
                $response .= "Kami akan segera memprosesnya. 😊\n\n";
                $response .= "📍 *Alamat Toko:*\n";
                $response .= "Jl. Imam Bonjol no.336, Denpasar, Bali\n\n";
                $response .= "━━━━━━━━━━━━━━━━\n";
                $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
                $response .= "Ketik *3/PESAN* untuk pesan lagi\n";
                $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
                $response .= "Ketik *0/MENU* untuk kembali ke menu";

                Log::info('Sending success message to customer', [
                    'order_id' => $whatsappOrder->id,
                    'phone' => $phone,
                    'message_length' => strlen($response)
                ]);

                // Send response FIRST before clearing cache
                try {
                    $sendResult = $this->whatsappService->sendMessage($phone, $response);

                    Log::info('Success message sent to customer - RESULT', [
                        'order_id' => $whatsappOrder->id,
                        'phone' => $phone,
                        'send_result' => $sendResult,
                        'result_type' => gettype($sendResult)
                    ]);
                } catch (\Exception $e) {
                    Log::error('FAILED to send success message to customer', [
                        'order_id' => $whatsappOrder->id,
                        'phone' => $phone,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Tetap assign result untuk return
                    $sendResult = false;
                }

                // ONLY clear cache AFTER message sent successfully
                Cache::forget('order_state_' . $phone);
                Cache::forget('last_context_' . $phone);

                return $sendResult;

                } finally {
                    // ALWAYS release lock after processing
                    $lock->release();
                    Log::info('Order creation lock released', ['lock_key' => $lockKey]);
                }
            } else {
                // Batal atau format salah
                $isCancelled = in_array($confirmMessage, ['tidak', 'salah', 'batal', 'cancel', 'no']);

                if ($isCancelled) {
                    Cache::forget('order_state_' . $phone);
                    Cache::forget('last_context_' . $phone);
                    $response = "❌ *Pesanan dibatalkan.*\n\n";
                    $response .= "━━━━━━━━━━━━━━━━\n";
                    $response .= "Ketik *3/PESAN* untuk membuat pesanan baru\n";
                    $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
                    $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
                    $response .= "Ketik *0/MENU* untuk kembali ke menu";
                    return $this->whatsappService->sendMessage($phone, $response);
                } else {
                    // Format tidak jelas, minta konfirmasi ulang
                    $response = "❓ Mohon konfirmasi:\n\n";
                    $response .= "NAMA: *{$data['name']}*\n";
                    $response .= "PESANAN: *{$data['order']}*\n\n";
                    $response .= "Ketik *YA* atau *BENAR* untuk mengirim\n";
                    $response .= "Ketik *TIDAK* atau *SALAH* untuk membatalkan";
                    return $this->whatsappService->sendMessage($phone, $response);
                }
            }
        }

        // Jika step tidak dikenal, reset
        Cache::forget('order_state_' . $phone);
        return null;
    }

    /**
     * Parse pesan pesanan dari format: NAMA: ... PESANAN: ...
     */
    protected function parseOrderMessage($message)
    {
        $message = trim($message);
        $result = [
            'name' => '',
            'order' => ''
        ];

        // Cari NAMA: (case insensitive)
        if (preg_match('/nama\s*:\s*(.+?)(?:\n|pesanan|$)/i', $message, $matches)) {
            $result['name'] = trim($matches[1]);
        }

        // Cari PESANAN: (case insensitive)
        if (preg_match('/pesanan\s*:\s*(.+?)$/i', $message, $matches)) {
            $result['order'] = trim($matches[1]);
        }

        // Jika tidak ditemukan dengan regex, coba split manual
        if (empty($result['name']) || empty($result['order'])) {
            $lines = explode("\n", $message);
            foreach ($lines as $line) {
                $line = trim($line);
                if (stripos($line, 'nama:') === 0) {
                    $result['name'] = trim(substr($line, 5));
                } elseif (stripos($line, 'pesanan:') === 0) {
                    $result['order'] = trim(substr($line, 8));
                }
            }
        }

        return $result;
    }

    /**
     * Cek stok berdasarkan teks pesanan yang memuat quantity, unit, nama produk dan warna.
     */
    protected function verifyStockForOrder(string $orderText): array
    {
        $parts = preg_split('/[,;\n]+/', $orderText);
        $parts = array_filter(array_map('trim', $parts));

        if (empty($parts)) {
            return [
                'ok' => false,
                'message' => "❌ Format pesanan harus menyertakan jumlah, satuan, nama produk dan warna.\n\n" .
                           "Contoh: *2 LUSIN KELING 10 warna NKL*",
                'items' => []
            ];
        }

        $items = [];

        foreach ($parts as $part) {
            // Coba parse dengan format baru (quantity + unit)
            $parsed = $this->extractOrderItemWithUnit($part);

            if ($parsed) {
                // Format baru dengan quantity dan unit
                $result = $this->validateStockWithUnit($parsed);
                if (!$result['ok']) {
                    return $result;
                }
                $items[] = $result['item'];
                continue;
            }

            // Fallback ke format lama (tanpa quantity/unit) untuk backward compatibility
            $parsedOld = $this->extractProductAndColor($part);

            // Jika tidak ada warna, cari produk dan tampilkan satuan & warna yang tersedia
            if (!$parsedOld) {
                // Coba ekstrak nama produk saja (tanpa warna)
                $productName = $this->extractProductNameOnly($part);

                if ($productName) {
                    // Cari produk berdasarkan nama
                    $normalizedName = $this->normalizeProductName($productName);
                    $products = Product::with(['color', 'type', 'units'])
                        ->where(function($query) use ($normalizedName, $productName) {
                            $query->where('name', 'like', '%' . $normalizedName . '%')
                                  ->orWhere('name', 'like', '%' . strtoupper($productName) . '%');
                        })
                        ->get();

                    if ($products->isNotEmpty()) {
                        $firstProduct = $products->first();

                        // Ambil warna yang tersedia
                        $availableColors = $products->pluck('color.name')->filter()->unique()->values();

                        // Ambil unit yang tersedia dari produk pertama
                        $availableUnits = $firstProduct->units->pluck('name')->filter()->unique()->values();

                        if ($availableUnits->isNotEmpty() && $availableColors->isNotEmpty()) {
                            $colorList = $availableColors->map(function($color, $index) {
                                return ($index + 1) . ". *" . $color . "*";
                            })->implode("\n");

                            $unitList = $availableUnits->map(function($unit, $index) {
                                return ($index + 1) . ". *" . $unit . "*";
                            })->implode("\n");

                            return [
                                'ok' => false,
                                'message' => "❓ *Format pesanan belum lengkap untuk:* {$part}\n\n" .
                                           "Produk ditemukan: *{$firstProduct->name}*\n\n" .
                                           "📦 *Satuan yang tersedia:*\n{$unitList}\n\n" .
                                           "🎨 *Warna yang tersedia:*\n{$colorList}\n\n" .
                                           "Silakan kirim ulang dengan format:\n" .
                                           "*[JUMLAH] [SATUAN] [PRODUK] warna [WARNA]*\n\n" .
                                           "Contoh:\n*NAMA: DARREN*\n*PESANAN: 2 {$availableUnits->first()} {$firstProduct->name} warna {$availableColors->first()}*",
                                'items' => [],
                                'needs_unit_and_color' => true
                            ];
                        }
                    }
                }

                return [
                    'ok' => false,
                    'message' => "❌ Tidak bisa membaca pesanan: *{$part}*.\n\n" .
                                "Pastikan format: *[JUMLAH] [SATUAN] [PRODUK] warna [WARNA]*\n\n" .
                                "Contoh: *2 LUSIN KELING 10 warna NKL*",
                    'items' => []
                ];
            }

            [$productName, $colorName] = $parsedOld;

            // Format lama tanpa unit - tampilkan pilihan unit yang tersedia
            $normalizedProductName = $this->normalizeProductName($productName);
            $product = Product::with(['color', 'units'])
                ->where(function($query) use ($normalizedProductName, $productName) {
                    $query->where('name', 'like', '%' . $normalizedProductName . '%')
                          ->orWhere('name', 'like', '%' . strtoupper($productName) . '%');
                })
                ->whereHas('color', function($q) use ($colorName) {
                    $q->where('name', 'like', '%' . $colorName . '%');
                })
                ->first();

            if (!$product) {
                // Coba cari produk tanpa filter warna untuk kasih saran warna yang tersedia
                $normalizedName = $this->normalizeProductName($productName);
                $productsWithoutColor = Product::with(['color'])
                    ->where(function($query) use ($normalizedName, $productName) {
                        $query->where('name', 'like', '%' . $normalizedName . '%')
                              ->orWhere('name', 'like', '%' . strtoupper($productName) . '%');
                    })
                    ->get();

                if ($productsWithoutColor->isNotEmpty()) {
                    $availableColors = $productsWithoutColor->pluck('color.name')->filter()->unique()->values();

                    if ($availableColors->isNotEmpty()) {
                        $colorList = $availableColors->map(function($color, $index) {
                            return ($index + 1) . ". *" . $color . "*";
                        })->implode("\n");

                        return [
                            'ok' => false,
                            'message' => "❌ Produk *{$productName}* dengan warna *{$colorName}* tidak ditemukan.\n\n" .
                                       "🎨 *Warna yang tersedia untuk {$productsWithoutColor->first()->name}:*\n{$colorList}\n\n" .
                                       "Silakan kirim ulang pesanan dengan warna yang tersedia.",
                            'items' => []
                        ];
                    }
                }

                return [
                    'ok' => false,
                    'message' => "❌ Produk *{$productName}* dengan warna *{$colorName}* tidak ditemukan.\n\n" .
                                "Ketik *KATALOG* untuk melihat produk yang tersedia.",
                    'items' => []
                ];
            }

            // Produk ditemukan tapi format lama (tanpa unit & quantity)
            // Tampilkan pilihan unit yang tersedia dan minta customer kirim ulang
            $availableUnits = $product->units->filter(function($unit) {
                return $unit->stock > 0; // Hanya unit yang ada stoknya
            })->pluck('name')->unique()->values();

            $colorDisplay = $product->color ? $product->color->name : $colorName;

            if ($availableUnits->isEmpty()) {
                return [
                    'ok' => false,
                    'message' => "⚠️ Stok *{$product->name}* warna *{$colorDisplay}* kosong untuk semua satuan.",
                    'items' => []
                ];
            }

            $unitList = $availableUnits->map(function($unit, $index) use ($product) {
                $unitData = $product->units->firstWhere('name', $unit);
                return ($index + 1) . ". *{$unit}* (Stok: {$unitData->stock})";
            })->implode("\n");

            return [
                'ok' => false,
                'message' => "❓ *Satuan dan jumlah belum disebutkan untuk:* {$part}\n\n" .
                           "Produk ditemukan: *{$product->name} ({$colorDisplay})*\n\n" .
                           "📦 *Pilihan satuan yang tersedia:*\n{$unitList}\n\n" .
                           "Silakan kirim ulang pesanan dengan format lengkap:\n" .
                           "*[JUMLAH] [SATUAN] [PRODUK] warna [WARNA]*\n\n" .
                           "Contoh:\n*NAMA: DARREN*\n*PESANAN: 2 {$availableUnits->first()} {$product->name} warna {$colorDisplay}*",
                'items' => [],
                'needs_quantity_and_unit' => true
            ];
        }

        return [
            'ok' => true,
            'message' => '',
            'items' => $items
        ];
    }

    /**
     * Ekstrak quantity, unit, nama produk dan warna dari potongan teks pesanan.
     * Format: [quantity] [unit] [product] warna [color]
     * Contoh: "2 LUSIN K10 warna NKL"
     *
     * @return array|null [quantity, unit_name, product_name, color_name]
     */
    protected function extractOrderItemWithUnit(string $text): ?array
    {
        $text = trim($text);

        // Pola 1: "2 LUSIN K10 warna NKL" atau "1 GROSAN KANCING 8 warna MERAH"
        // Format: [angka] [unit] [produk] warna [warna]
        if (preg_match('/^(\d+)\s+([a-z]+)\s+(.*?)\s+warna\s+(.+)$/i', $text, $matches)) {
            return [
                'quantity' => intval($matches[1]),
                'unit_name' => strtoupper(trim($matches[2])),
                'product_name' => trim($matches[3]),
                'color_name' => trim($matches[4])
            ];
        }

        // Pola 2: "2 LUSIN K10 (NKL)" - dengan kurung
        if (preg_match('/^(\d+)\s+([a-z]+)\s+(.*?)\s*\(([^)]+)\)/i', $text, $matches)) {
            return [
                'quantity' => intval($matches[1]),
                'unit_name' => strtoupper(trim($matches[2])),
                'product_name' => trim($matches[3]),
                'color_name' => trim($matches[4])
            ];
        }

        // Pola 3: Tanpa quantity (default 1)
        // "LUSIN K10 warna NKL"
        if (preg_match('/^([a-z]+)\s+(.*?)\s+warna\s+(.+)$/i', $text, $matches)) {
            return [
                'quantity' => 1,
                'unit_name' => strtoupper(trim($matches[1])),
                'product_name' => trim($matches[2]),
                'color_name' => trim($matches[3])
            ];
        }

        return null;
    }

    /**
     * Validasi stok berdasarkan quantity, unit, product, dan color
     */
    protected function validateStockWithUnit(array $parsed): array
    {
        $quantity = $parsed['quantity'];
        $unitName = $parsed['unit_name'];
        $productName = $parsed['product_name'];
        $colorName = $parsed['color_name'];

        // Normalisasi nama produk
        $normalizedProductName = $this->normalizeProductName($productName);

        // Cari product berdasarkan nama dan warna
        $product = Product::with(['color', 'units'])
            ->where(function($query) use ($normalizedProductName, $productName) {
                $query->where('name', 'like', '%' . $normalizedProductName . '%')
                      ->orWhere('name', 'like', '%' . strtoupper($productName) . '%');
            })
            ->whereHas('color', function($q) use ($colorName) {
                $q->where('name', 'like', '%' . $colorName . '%');
            })
            ->first();

        if (!$product) {
            return [
                'ok' => false,
                'message' => "❌ Produk *{$productName}* dengan warna *{$colorName}* tidak ditemukan.\n\n" .
                           "Ketik *KATALOG* untuk melihat produk yang tersedia.",
                'items' => []
            ];
        }

        // Cari unit yang sesuai untuk produk ini
        $productUnit = $product->units()
            ->where('name', 'like', '%' . $unitName . '%')
            ->first();

        if (!$productUnit) {
            // Unit tidak ditemukan, tampilkan unit yang tersedia
            $availableUnits = $product->units->pluck('name')->filter()->unique()->values();

            if ($availableUnits->isEmpty()) {
                return [
                    'ok' => false,
                    'message' => "❌ Produk *{$product->name}* tidak memiliki satuan apapun.",
                    'items' => []
                ];
            }

            $unitList = $availableUnits->map(function($unit, $index) {
                return ($index + 1) . ". *" . $unit . "*";
            })->implode("\n");

            return [
                'ok' => false,
                'message' => "❌ Satuan *{$unitName}* tidak tersedia untuk *{$product->name}*.\n\n" .
                           "📦 *Satuan yang tersedia:*\n{$unitList}\n\n" .
                           "Silakan kirim ulang pesanan dengan satuan yang tersedia.\n\n" .
                           "Contoh: *{$quantity} {$availableUnits->first()} {$product->name} warna {$colorName}*",
                'items' => []
            ];
        }

        // Cek stok
        $stockTersedia = $productUnit->stock;

        if ($stockTersedia < $quantity) {
            return [
                'ok' => false,
                'message' => "⚠️ *Stok tidak mencukupi!*\n\n" .
                           "Produk: *{$product->name} ({$product->color->name})*\n" .
                           "Diminta: *{$quantity} {$productUnit->name}*\n" .
                           "Tersedia: *{$stockTersedia} {$productUnit->name}*\n\n" .
                           "Silakan kurangi jumlah pesanan atau hubungi kami.",
                'items' => []
            ];
        }

        // Stok mencukupi!
        return [
            'ok' => true,
            'item' => [
                'product_id' => $product->id,
                'product_unit_id' => $productUnit->id,
                'color_id' => $product->color ? $product->color->id : null,
                'quantity' => $quantity,
                'product_name' => $product->name,
                'unit_name' => $productUnit->name,
                'color_name' => $product->color ? $product->color->name : $colorName,
                'stock_available' => $stockTersedia,
                'stock_pcs' => $stockTersedia * $productUnit->conversion_value, // Total dalam PCS
            ]
        ];
    }

    /**
     * Ekstrak nama produk dan warna dari potongan teks pesanan (format lama - backward compatibility).
     */
    protected function extractProductAndColor(string $text): ?array
    {
        $text = trim($text);

        // Pola: "Nama Produk warna Merah"
        if (preg_match('/^(.*?)\s+warna\s+(.+)$/i', $text, $matches)) {
            return [trim($matches[1]), trim($matches[2])];
        }

        // Pola: "Nama Produk (Merah)"
        if (preg_match('/^(.*?)\s*\(([^)]+)\)/', $text, $matches)) {
            return [trim($matches[1]), trim($matches[2])];
        }

        return null;
    }

    /**
     * Ekstrak nama produk saja tanpa warna (untuk mendeteksi produk ketika warna tidak disebutkan).
     */
    protected function extractProductNameOnly(string $text): ?string
    {
        $text = trim($text);

        // Buang jumlah pesanan jika ada (contoh: "2 LUSIN", "1 PAK", dll)
        $text = preg_replace('/\d+\s*(lusin|pak|pcs|box|dos|kodi)/i', '', $text);
        $text = trim($text);

        // Buang karakter ":" dan setelahnya
        if (strpos($text, ':') !== false) {
            $text = substr($text, 0, strpos($text, ':'));
            $text = trim($text);
        }

        if (!empty($text)) {
            return $text;
        }

        return null;
    }

    /**
     * Handle katalog produk - tampilkan pilihan jenis produk atau produk berdasarkan jenis
     */
    protected function handleCatalog($phone, $message)
    {
        Log::info('Handle catalog request', ['phone' => $phone, 'message' => $message]);

        // Extract jenis produk dari pesan (jika ada)
        // Format: "katalog [nama jenis]" atau "katalog 1" (angka)
        $messageParts = explode(' ', strtolower(trim($message)));
        $typeInput = null;

        // Cek apakah ada input jenis setelah "katalog"
        if (count($messageParts) > 1) {
            $typeInput = trim(implode(' ', array_slice($messageParts, 1)));
        }

        // Jika tidak ada input jenis, tampilkan daftar jenis produk
        if (empty($typeInput)) {
            return $this->showProductTypes($phone);
        }

        // Jika ada input jenis, tampilkan produk berdasarkan jenis
        return $this->showProductsByType($phone, $typeInput);
    }

    /**
     * Tampilkan daftar jenis produk
     */
    protected function showProductTypes($phone)
    {
        $productTypes = ProductType::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        if ($productTypes->isEmpty()) {
            $message = "❌ Belum ada jenis produk tersedia.";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        // Simpan state bahwa user sedang melihat katalog (expire setelah 10 menit)
        Cache::put('catalog_state_' . $phone, [
            'viewing_types' => true, // Flag bahwa sedang melihat pilihan jenis
            'types' => $productTypes->pluck('id')->toArray(),
            'count' => $productTypes->count()
        ], now()->addMinutes(10));

        $response = "📋 *PILIHAN JENIS PRODUK*\n\n";
        $response .= "Silakan pilih jenis produk:\n\n";

        foreach ($productTypes as $index => $type) {
            $response .= ($index + 1) . ". *{$type->name}* ({$type->products_count} produk)\n";
        }

        $response .= "\n━━━━━━━━━━━━━━━━\n";
        $response .= "💡 *Cara memilih:*\n";
        $response .= "Ketik *nomor* (contoh: *1*) atau *KATALOG [nama jenis]*\n";
        $response .= "Contoh: *1* atau *KATALOG KAITAN*\n\n";

        $response .= "━━━━━━━━━━━━━━━━\n";
        $response .= "Ketik *2/STOK [nama produk]* untuk cek stok\n";
        $response .= "Ketik *3/PESAN* untuk membuat pesanan\n";
        $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
        $response .= "Ketik *0/MENU* untuk kembali ke menu";

        return $this->whatsappService->sendMessage($phone, $response);
    }

    /**
     * Tampilkan produk berdasarkan jenis yang dipilih
     */
    protected function showProductsByType($phone, $typeInput)
    {
        $productType = null;

        // Cek apakah input berupa angka
        if (is_numeric($typeInput)) {
            if ((int)$typeInput === 0) {
                Cache::forget('catalog_state_' . $phone);
                return $this->sendMenu($phone);
            }

            // Cek dulu apakah ada state katalog yang tersimpan
            $catalogState = Cache::get('catalog_state_' . $phone);

            if ($catalogState && isset($catalogState['types'])) {
                // Gunakan data dari state jika ada
                $productTypes = ProductType::whereIn('id', $catalogState['types'])
                    ->withCount('products')
                    ->having('products_count', '>', 0)
                    ->orderBy('name', 'asc')
                    ->get();
            } else {
                // Jika tidak ada state, ambil semua jenis produk
                $productTypes = ProductType::withCount('products')
                    ->having('products_count', '>', 0)
                    ->orderBy('name', 'asc')
                    ->get();
            }

            $index = (int)$typeInput - 1;
            if ($index >= 0 && $index < $productTypes->count()) {
                $productType = $productTypes[$index];
            }
        } else {
            // Cari berdasarkan nama jenis
            $productType = ProductType::where('name', 'like', '%' . strtoupper($typeInput) . '%')
                ->first();
        }

        if (!$productType) {
            $message = "❌ Jenis produk tidak ditemukan.\n\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "Ketik *1/KATALOG* untuk melihat daftar jenis produk\n";
            $message .= "Ketik *3/PESAN* untuk membuat pesanan\n";
            $message .= "Ketik *0/MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        // Update state: user sudah memilih jenis, sekarang melihat daftar produk
        Cache::forget('catalog_state_' . $phone);

        // Simpan context bahwa user baru lihat daftar produk
        // Jadi kalau ketik nama produk langsung, bisa auto cek stok
        Cache::put('last_context_' . $phone, [
            'action' => 'viewed_products',
            'type_id' => $productType->id,
            'type_name' => $productType->name
        ], now()->addMinutes(10));

        // Ambil produk dengan jenis tersebut
        $products = Product::where('type_id', $productType->id)
            ->with(['units', 'color'])
            ->orderBy('name', 'asc')
            ->get();

        if ($products->isEmpty()) {
            $message = "❌ Belum ada produk untuk jenis *{$productType->name}*.\n\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "Ketik *1/KATALOG* untuk kembali ke katalog\n";
            $message .= "Ketik *3/PESAN* untuk membuat pesanan\n";
            $message .= "Ketik *0/MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        $response = "📦 *KATALOG: {$productType->name}*\n\n";
        $response .= "Total: *{$products->count()} produk*\n\n";

        foreach ($products as $index => $product) {
            $firstUnit = $product->units->first();
            $price = $firstUnit ? number_format($firstUnit->price, 0, ',', '.') : '0';
            $unitName = $firstUnit ? strtoupper($firstUnit->name) : '';

            $response .= ($index + 1) . ". *{$product->name}*\n";
            if ($product->color) {
                $response .= "   Warna: {$product->color->name}\n";
            }
            $response .= "   Harga: Rp {$price} per {$unitName}\n\n";
        }

        $response .= "━━━━━━━━━━━━━━━━\n";
        $response .= "💡 *Cara cek stok:*\n";
        $response .= "• Ketik *STOK [nama produk]* (contoh: *STOK K10*)\n";
        $response .= "• Atau langsung ketik *nama produk* (contoh: *K10*)\n\n";

        $response .= "━━━━━━━━━━━━━━━━\n";
        $response .= "Ketik *1/KATALOG* untuk kembali ke katalog\n";
        $response .= "Ketik *3/PESAN* untuk membuat pesanan\n";
        $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan\n";
        $response .= "Ketik *0/MENU* untuk kembali ke menu";

        return $this->whatsappService->sendMessage($phone, $response);
    }

    /**
     * Handle melihat pesanan customer
     */
    protected function handleViewOrders($phone)
    {
        // Ambil pesanan customer berdasarkan nomor telepon
        $orders = WhatsappOrder::where('phone', $phone)
            ->with(['orderItems.product', 'orderItems.color'])
            ->latest()
            ->take(5) // Ambil 5 pesanan terakhir
            ->get();

        if ($orders->isEmpty()) {
            $response = "📝 *PESANAN ANDA*\n\n";
            $response .= "Anda belum memiliki pesanan.\n\n";
            $response .= "━━━━━━━━━━━━━━━━\n";
            $response .= "Ketik *3/PESAN* untuk membuat pesanan baru\n";
            $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
            $response .= "Ketik *0/MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $response);
        }

        $response = "📝 *PESANAN ANDA*\n\n";
        $response .= "Berikut adalah " . $orders->count() . " pesanan terakhir:\n\n";

        foreach ($orders as $index => $order) {
            $statusIcon = $order->status === 'confirmed' ? '✅' :
                         ($order->status === 'cancelled' ? '❌' : '⏳');
            $statusText = $order->status === 'confirmed' ? 'Dikonfirmasi' :
                         ($order->status === 'cancelled' ? 'Dibatalkan' : 'Pending');

            $response .= "━━━━━━━━━━━━━━━━\n";
            $response .= "🔖 *Pesanan #{$order->id}*\n";
            $response .= "📅 Tanggal: " . $order->created_at->format('d/m/Y H:i') . "\n";
            $response .= "👤 Nama: {$order->name}\n";
            $response .= "📦 Pesanan: {$order->order_text}\n";
            $response .= "{$statusIcon} Status: *{$statusText}*\n";

            // Tampilkan detail items jika ada
            if ($order->orderItems->isNotEmpty()) {
                $response .= "\n📋 Detail:\n";
                foreach ($order->orderItems as $item) {
                    $productName = $item->product ? $item->product->name : '-';
                    $colorName = $item->color ? $item->color->name : '-';
                    $response .= "   • {$productName} ({$colorName}) - {$item->stock_pcs} pcs\n";
                }
            }

            // Tombol batal hanya untuk pesanan yang confirmed atau pending
            if (in_array($order->status, ['confirmed', 'pending'])) {
                $response .= "\n💡 Ketik *BATAL PESANAN {$order->id}* untuk membatalkan\n";
            }

            $response .= "\n";
        }

        $response .= "━━━━━━━━━━━━━━━━\n";
        $response .= "Ketik *3/PESAN* untuk pesan lagi\n";
        $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
        $response .= "Ketik *0/MENU* untuk kembali ke menu";

        return $this->whatsappService->sendMessage($phone, $response);
    }

    /**
     * Handle pembatalan pesanan
     */
    protected function handleCancelOrder($phone, $orderId)
    {
        if (!$orderId) {
            $response = "❌ *Format salah!*\n\n";
            $response .= "Untuk membatalkan pesanan, ketik:\n";
            $response .= "*BATAL PESANAN [nomor pesanan]*\n\n";
            $response .= "Contoh: *BATAL PESANAN 123*\n\n";
            $response .= "━━━━━━━━━━━━━━━━\n";
            $response .= "Ketik *4/CEK PESANAN* untuk lihat nomor pesanan\n";
            $response .= "Ketik *0/MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $response);
        }

        // Cari pesanan berdasarkan ID dan nomor telepon customer
        $order = WhatsappOrder::where('id', $orderId)
            ->where('phone', $phone)
            ->first();

        if (!$order) {
            $response = "❌ *Pesanan tidak ditemukan!*\n\n";
            $response .= "Pesanan #{$orderId} tidak ditemukan atau bukan milik Anda.\n\n";
            $response .= "━━━━━━━━━━━━━━━━\n";
            $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan Anda\n";
            $response .= "Ketik *0/MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $response);
        }

        // Cek apakah pesanan sudah dibatalkan
        if ($order->status === 'cancelled') {
            $response = "ℹ️ *Pesanan sudah dibatalkan sebelumnya.*\n\n";
            $response .= "Pesanan #{$orderId} sudah dibatalkan pada " . $order->updated_at->format('d/m/Y H:i') . "\n\n";
            $response .= "━━━━━━━━━━━━━━━━\n";
            $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan lain\n";
            $response .= "Ketik *3/PESAN* untuk pesan lagi\n";
            $response .= "Ketik *0/MENU* untuk kembali ke menu";
            return $this->whatsappService->sendMessage($phone, $response);
        }

        // Update status menjadi cancelled
        $order->update(['status' => 'cancelled']);

        // Kirim notifikasi ke owner
        $ownerPhone = config('services.chatbot.owner_phone', env('OWNER_PHONE'));
        if ($ownerPhone) {
            $ownerMessage = "❌ *PESANAN DIBATALKAN*\n\n";
            $ownerMessage .= "Pesanan #{$order->id}\n";
            $ownerMessage .= "Nama: {$order->name}\n";
            $ownerMessage .= "Pesanan: {$order->order_text}\n";
            $ownerMessage .= "Nomor: {$order->phone}\n";
            $ownerMessage .= "Dibatalkan: " . now()->format('d/m/Y H:i');
            $this->whatsappService->sendMessage($ownerPhone, $ownerMessage);
        }

        $response = "✅ *Pesanan berhasil dibatalkan!*\n\n";
        $response .= "Pesanan #{$orderId} telah dibatalkan.\n\n";
        $response .= "━━━━━━━━━━━━━━━━\n";
        $response .= "Ketik *3/PESAN* untuk pesan lagi\n";
        $response .= "Ketik *4/CEK PESANAN* untuk lihat pesanan lain\n";
        $response .= "Ketik *1/KATALOG* untuk lihat katalog\n";
        $response .= "Ketik *0/MENU* untuk kembali ke menu";

        return $this->whatsappService->sendMessage($phone, $response);
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

