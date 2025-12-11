<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\ProductType;
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
        $message = strtolower(trim($message));
        $phone = $this->formatPhone($phone);

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
                $response = "❌ *Pesanan dibatalkan.*\n\n";
                $response .= "Ketik: *PESAN* jika ingin membuat pesanan baru.\n";
                $response .= "Ketik: *KATALOG* untuk lihat katalog\n";
                $response .= "Ketik: *STOK [nama produk]* untuk cek stok\n";
                $response .= "Ketik: *MENU* untuk kembali ke menu";
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

        // Cek command "pesan" - hanya jika pesan benar-benar command "pesan" saja
        // Bukan jika ada kata "pesanan" di tengah pesan
        if ($message === 'pesan' || $message === 'order') {
            return $this->handleOrder($phone, $message);
        }

        if (strpos($message, 'menu') !== false) {
            Cache::forget('catalog_state_' . $phone);
            return $this->sendMenu($phone);
        }

        // Cek apakah customer mengucapkan terimakasih
        $thankYouKeywords = ['terimakasih', 'terima kasih', 'makasih', 'thanks', 'thank you', 'terima kasih ya', 'makasih ya'];
        foreach ($thankYouKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $response = "Sama-sama kak! 😊\n\n";
                $response .= "Jika ada yang bisa dibantu, silakan ketik:\n";
                $response .= "• *KATALOG* - Lihat katalog\n";
                $response .= "• *STOK [nama produk]* - Cek stok\n";
                $response .= "• *PESAN* - Buat pesanan";
                $response .= "• *MENU*- Kembali ke menu";

                return $this->whatsappService->sendMessage($phone, $response);
            }
        }

        // Cek apakah pesan hanya berupa angka (menu cepat
        // atau pilihan katalog)
        $catalogState = Cache::get('catalog_state_' . $phone);
        if (is_numeric($message)) {
            // Jika sedang lihat katalog, angka berarti pilih jenis (termasuk 1/2/3)
            if ($catalogState) {
                return $this->showProductsByType($phone, $message);
            }

            // Shortcut angka untuk menu utama
            switch ($message) {
                case '0': // Kembali ke menu
                    return $this->sendMenu($phone);
                case '1': // KATALOG
                    return $this->handleCatalog($phone, 'katalog');
                case '2': // STOK
                    $msg = "Untuk cek stok, ketik:\n*STOK [nama produk]*\n\nContoh:\n*STOK KELING 10* atau *STOK K10*\n\nUntuk kembali ke menu, KETIK *MENU*";
                    return $this->whatsappService->sendMessage($phone, $msg);
                case '3': // PESAN
                    return $this->handleOrder($phone, 'pesan');
                default:
                    // Angka lain: kirim ulang menu agar jelas
                    return $this->sendMenu($phone);
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
        // Reset state katalog agar pilihan angka kembali ke menu utama
        Cache::forget('catalog_state_' . $phone);

        $message = "Halo kak, terimakasih sudah chat ke Toko Trijaya! 😊\n\n";
        $message .= "🤖 *Menu Toko Trijaya*\n\n";
        $message .= "Silakan pilih menu:\n\n";
        $message .= "1️⃣ *KATALOG* - Lihat katalog produk\n";
        $message .= "2️⃣ *STOK* - Cek stok produk\n";
        $message .= "3️⃣ *PESAN* - Buat pesanan\n\n";
        $message .= "Ketik: *1/KATALOG* untuk lihat katalog\n";
        $message .= "Ketik: *2/STOK [nama produk]* untuk cek stok\n";
        $message .= "Ketik: *3/PESAN* untuk membuat pesanan\n";

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
            $message .= "Ketik *KATALOG* untuk lihat katalog produk.";
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
        $response .= "*PESANAN: [Detail Pesanan]*\n\n";
        $response .= "Contoh:\n";
        $response .= "NAMA: DARREN\n";
        $response .= "PESANAN: KELING ukuran 4: 2 LUSIN, KELING ukuran 8: 1 PAK\n\n";
        $response .= "Ketik *BATAL* jika tidak jadi pesan.";

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
                    $response = "❌ *Pesanan dibatalkan.*\n\n";
                    $response .= "Ketik *PESAN* jika ingin membuat pesanan baru.";
                    return $this->whatsappService->sendMessage($phone, $response);
                }
            }

            // Parse format: NAMA: ... PESANAN: ...
            $parsed = $this->parseOrderMessage($message);

            if (!$parsed || empty($parsed['name']) || empty($parsed['order'])) {
                $response = "❌ Format pesanan tidak sesuai!\n\n";
                $response .= "Silakan gunakan format:\n";
                $response .= "*NAMA: [Nama Lengkap]*\n";
                $response .= "*PESANAN: [Detail Pesanan]*\n\n";
                $response .= "Contoh:\n";
                $response .= "NAMA: DARREN\n";
                $response .= "PESANAN: KELING ukuran 4: 2 LUSIN, KELING ukuran 8: 1 PAK\n\n";
                $response .= "Ketik *BATAL* untuk membatalkan pesanan.";

                return $this->whatsappService->sendMessage($phone, $response);
            }

            // Simpan data dan kirim konfirmasi
            $data['name'] = trim($parsed['name']);
            $data['order'] = trim($parsed['order']);
            $data['phone'] = $phone;

            Cache::put('order_state_' . $phone, [
                'step' => 'confirmation',
                'data' => $data
            ], now()->addMinutes(30));

            // Kirim konfirmasi ke customer
            $response = "📋 *KONFIRMASI PESANAN*\n\n";
            $response .= "Mohon periksa data pesanan Anda:\n\n";
            $response .= "NAMA: *{$data['name']}*\n";
            $response .= "PESANAN: *{$data['order']}*\n\n";
            $response .= "Apakah data di atas sudah benar?\n";
            $response .= "Ketik *YA* atau *BENAR* untuk mengirim pesanan\n";
            $response .= "Ketik *BATAL* untuk membatalkan";

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
                // Kirim ke owner
                $ownerPhone = config('services.chatbot.owner_phone', env('OWNER_PHONE'));
                if ($ownerPhone) {
                    $ownerMessage = "📦 *PESANAN BARU*\n\n";
                    $ownerMessage .= "NAMA: *{$data['name']}*\n";
                    $ownerMessage .= "PESANAN: *{$data['order']}*\n";
                    $ownerMessage .= "Nomor: *{$data['phone']}*\n\n";
                    $ownerMessage .= "Waktu: " . now()->format('d/m/Y H:i');

                    $this->whatsappService->sendMessage($ownerPhone, $ownerMessage);
                }

                // Hapus state pemesanan
                Cache::forget('order_state_' . $phone);

                $response = "✅ *Pesanan Anda telah dikirim!*\n\n";
                $response .= "Terima kasih atas pesanan Anda. Kami akan segera menghubungi Anda untuk konfirmasi.\n\n";
                $response .= "📍 Alamat:\n";
                $response .= "Jl. Imam Bonjol no.336, Denpasar, Bali";

                return $this->whatsappService->sendMessage($phone, $response);
            } else {
                // Batal atau format salah
                $isCancelled = in_array($confirmMessage, ['tidak', 'salah', 'batal', 'cancel', 'no']);

                if ($isCancelled) {
                    Cache::forget('order_state_' . $phone);
                    $response = "❌ Pesanan dibatalkan.\n\n";
                    $response .= "Ketik *PESAN* untuk membuat pesanan baru.";
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
            'types' => $productTypes->pluck('id')->toArray(),
            'count' => $productTypes->count()
        ], now()->addMinutes(10));

        $response = "📋 *PILIHAN JENIS PRODUK*\n\n";
        $response .= "Silakan pilih jenis produk:\n\n";

        foreach ($productTypes as $index => $type) {
            $response .= ($index + 1) . ". *{$type->name}* ({$type->products_count} produk)\n";
        }

        $response .= "\nKetik *nomor* (contoh: *1*) atau *KATALOG [nomor/nama jenis]*\n";
        $response .= "Contoh: *1* atau *KATALOG KAITAN*\n\n";

        $response .= "Ketik  *KATALOG* untuk kembali ke katalog\n";
        $response .= "Ketik  *STOK [nama produk]* untuk cek stok\n";
        $response .= "Ketik  *PESAN* untuk membuat pesanan\n";
        $response .= "Ketik *MENU* untuk kembali ke menu";


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
            $message .= "Ketik *KATALOG* untuk melihat daftar jenis produk.";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        // Hapus state katalog setelah user memilih
        Cache::forget('catalog_state_' . $phone);

        // Ambil produk dengan jenis tersebut
        $products = Product::where('type_id', $productType->id)
            ->with(['units', 'color'])
            ->orderBy('name', 'asc')
            ->get();

        if ($products->isEmpty()) {
            $message = "❌ Belum ada produk untuk jenis *{$productType->name}*.";
            return $this->whatsappService->sendMessage($phone, $message);
        }

        $response = "📦 *KATALOG: {$productType->name}*\n\n";
        $response .= "Total: *{$products->count()} produk*\n\n";

        foreach ($products as $index => $product) {
            $firstUnit = $product->units->first();
            $price = $firstUnit ? number_format($firstUnit->price, 0, ',', '.') : '0';
            $unitName = $firstUnit ? strtoupper($firstUnit->name) : '';

            $response .= ($index + 1) . ". Nama: *{$product->name}*\n";
            if ($product->color) {
                $response .= "   Warna: {$product->color->name}\n";
            }
            $response .= "   Harga: Rp {$price} per {$unitName}\n\n";
        }

        $response .= "\nKetik *STOK [nama produk]* untuk detail stok\n";

        $response .= "Ketik  *1*/*KATALOG* untuk kembali ke katalog\n";
        $response .= "Ketik  *2*/*STOK [nama produk]* untuk cek stok\n";
        $response .= "Ketik  *3*/*PESAN* untuk membuat pesanan\n";

        $response .= "Ketik *MENU* untuk kembali ke menu";

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

