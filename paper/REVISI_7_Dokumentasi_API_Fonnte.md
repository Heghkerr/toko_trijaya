
# REVISI 7: Dokumentasi Coding & Integrasi API Fonnte.com

## 7.1 Pengenalan Fonnte.com

**Fonnte.com** adalah layanan WhatsApp Business API Gateway yang menyediakan API untuk mengirim dan menerima pesan WhatsApp secara terprogram (programmatic). Layanan ini memungkinkan aplikasi web/mobile untuk berinteraksi dengan WhatsApp tanpa perlu mengurus infrastruktur WhatsApp Business API yang kompleks.

**Keuntungan menggunakan Fonnte:**
1. ✅ Tidak perlu setup WhatsApp Business API sendiri
2. ✅ Dokumentasi lengkap dalam Bahasa Indonesia
3. ✅ Support berbagai fitur (text, image, document, location, dll)
4. ✅ Dashboard monitoring yang user-friendly
5. ✅ Harga terjangkau untuk UMKM
6. ✅ Webhook support untuk terima pesan masuk

**Website:** https://fonnte.com
**Dokumentasi API:** https://docs.fonnte.com

## 7.2 Konfigurasi Awal

### 7.2.1 Mendapatkan API Token

**Langkah-langkah:**

1. **Registrasi Akun**
   - Buka https://fonnte.com
   - Klik "Daftar" atau "Register"
   - Isi data: Email, Password, Nama
   - Verifikasi email

2. **Hubungkan WhatsApp**
   - Login ke dashboard Fonnte
   - Pilih menu "Device" → "Add Device"
   - Scan QR Code dengan WhatsApp (seperti WhatsApp Web)
   - WhatsApp terhubung dengan Fonnte

3. **Dapatkan API Token**
   - Buka menu "Settings" atau "API"
   - Copy API Token yang diberikan
   - Format: String random panjang (contoh: `abc123xyz789...`)

4. **Simpan Token di Aplikasi**
   - Buka file `.env` di root project
   - Tambahkan: `FONNTE_TOKEN=abc123xyz789...`
   - Save file

### 7.2.2 Konfigurasi di Laravel

**File: `config/services.php`**

```php
return [
    // ... konfigurasi lain
    
    'chatbot' => [
        'owner_phone' => env('OWNER_PHONE', '6281234567890'),
        'fonnte_token' => env('FONNTE_TOKEN'),
    ],
];
```

**File: `.env`**

```env
# WhatsApp Configuration
FONNTE_TOKEN=your_fonnte_token_here
OWNER_PHONE=6281234567890
```

### 7.2.3 Setup Webhook

**Webhook** adalah mekanisme di mana Fonnte akan mengirim HTTP request ke aplikasi ketika ada pesan masuk.

**Langkah Setup:**

1. **Di Dashboard Fonnte:**
   - Buka menu "Webhook Settings"
   - Isi Webhook URL: `https://yourdomain.com/api/whatsapp/webhook`
   - Pilih event: "Message Received"
   - Save

2. **Di Aplikasi Laravel:**

**File: `routes/api.php`**
```php
// Webhook dari Fonnte untuk terima pesan WhatsApp
Route::post('/whatsapp/webhook', [WhatsAppController::class, 'webhook']);
```

**File: `app/Http/Controllers/WhatsAppController.php`**
```php
public function webhook(Request $request)
{
    // Log request untuk debugging
    Log::info('WhatsApp Webhook Received', $request->all());
    
    // Extract data
    $phone = $request->input('sender');
    $message = $request->input('message');
    
    // Process message
    $chatbotService = app(ChatbotService::class);
    $chatbotService->processMessage($phone, $message);
    
    return response()->json(['status' => 'ok']);
}
```

## 7.3 Implementasi WhatsappService

### 7.3.1 Class Structure

**File: `app/Services/WhatsappService.php`**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected $token;
    
    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');
    }
    
    // Method untuk kirim pesan
    public function sendMessage($target, $message) { ... }
    
    // Method untuk kirim gambar
    public function sendImage($target, $imageUrl, $caption = '') { ... }
}
```

### 7.3.2 Method: sendMessage()

**Fungsi:** Mengirim pesan teks ke nomor WhatsApp

**Parameter:**
- `$target` (string): Nomor HP tujuan (format: 08xxx atau 628xxx)
- `$message` (string): Isi pesan yang akan dikirim

**Return:**
- Array dengan key: `status` (bool), `message` (string), `data` (array)

**Implementasi Lengkap:**

```php
public function sendMessage($target, $message)
{
    // 1. Validasi: Cek apakah token sudah di-set
    if (!$this->token) {
        Log::error('FONNTE_TOKEN belum di-set di .env');
        return [
            'status' => false,
            'message' => 'Token Fonnte not configured.'
        ];
    }
    
    try {
        // 2. Kirim HTTP POST request ke API Fonnte
        $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])
            ->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ]);
        
        // 3. Parse response dari Fonnte
        $responseData = $response->json();
        
        // 4. Log untuk debugging
        Log::info('Fonnte message sent to ' . $target, $responseData);
        
        // 5. Handle response berdasarkan status
        if ($response->successful()) {
            // Cek format response Fonnte
            if (isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'status' => true,
                    'message' => 'Pesan berhasil dikirim',
                    'data' => $responseData
                ];
            } elseif (isset($responseData['status']) && $responseData['status'] === false) {
                // Error dari Fonnte
                $reason = $responseData['reason'] ?? null;
                $errorMsg = $responseData['message'] ?? 'Gagal mengirim pesan';
                
                // Handle error khusus untuk invalid token
                if ($reason === 'invalid token' || 
                    str_contains(strtolower($errorMsg), 'invalid token')) {
                    $errorMsg = 'Token Fonnte tidak valid. Silakan periksa FONNTE_TOKEN di file .env';
                }
                
                return [
                    'status' => false,
                    'message' => $errorMsg,
                    'reason' => $reason,
                    'data' => $responseData
                ];
            } else {
                // Response tidak standar tapi HTTP 200
                return [
                    'status' => true,
                    'message' => 'Pesan dikirim (response tidak standar)',
                    'data' => $responseData
                ];
            }
        } else {
            // HTTP Error (4xx, 5xx)
            $errorMsg = $responseData['message'] ?? 'HTTP Error: ' . $response->status();
            
            return [
                'status' => false,
                'message' => $errorMsg,
                'data' => $responseData,
                'http_status' => $response->status()
            ];
        }
        
    } catch (\Exception $e) {
        // 6. Handle exception (network error, timeout, dll)
        Log::error('Fonnte send error: ' . $e->getMessage());
        return [
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}
```

**Penjelasan Detail:**

1. **Validasi Token (Line 3-8)**
   - Cek apakah `FONNTE_TOKEN` sudah diset di .env
   - Jika belum, return error dan log
   - Mencegah request yang sia-sia ke API

2. **HTTP Request ke Fonnte (Line 11-18)**
   - Menggunakan Laravel HTTP Client (`Http::` facade)
   - Set header `Authorization` dengan token
   - POST ke endpoint `https://api.fonnte.com/send`
   - Payload:
     - `target`: Nomor HP tujuan
     - `message`: Isi pesan
     - `countryCode`: '62' (Indonesia)

3. **Parse Response (Line 20-22)**
   - Convert JSON response ke array PHP
   - Fonnte mengembalikan JSON dengan struktur:
     ```json
     {
       "status": "success" | false,
       "detail": "success! message in queue",
       "id": "message_id_xyz"
     }
     ```

4. **Logging (Line 24)**
   - Catat semua response untuk audit trail
   - Berguna untuk debugging jika ada masalah
   - Log disimpan di `storage/logs/laravel.log`

5. **Response Handling (Line 26-55)**
   - **Success (status = 'success'):** Return `status: true`
   - **Error (status = false):** Extract error message dan reason
   - **Invalid Token:** Deteksi dan berikan pesan khusus
   - **HTTP Error:** Handle 4xx/5xx dengan pesan yang jelas

6. **Exception Handling (Line 57-61)**
   - Catch semua exception (network error, timeout, dll)
   - Log error untuk investigasi
   - Return error message yang user-friendly

### 7.3.3 Method: sendImage()

**Fungsi:** Mengirim gambar/foto ke nomor WhatsApp

**Parameter:**
- `$target` (string): Nomor HP tujuan
- `$imageUrl` (string): URL gambar yang bisa diakses publik
- `$caption` (string, optional): Caption/keterangan gambar

**Return:**
- Array dengan key: `status` (bool), `message` (string), `data` (array)

**Implementasi:**

```php
public function sendImage($target, $imageUrl, $caption = '')
{
    if (!$this->token) {
        Log::error('FONNTE_TOKEN belum di-set di .env');
        return ['status' => false, 'message' => 'Token Fonnte not configured.'];
    }
    
    try {
        // Payload untuk kirim gambar
        $payload = [
            'target' => $target,
            'url' => $imageUrl,              // URL gambar
            'type' => 'image',               // Tipe: image
            'message' => $caption ?: '📸 Katalog Produk',
            'countryCode' => '62',
        ];
        
        if ($caption) {
            $payload['caption'] = $caption;
        }
        
        Log::info('Sending image via Fonnte', [
            'target' => $target,
            'url' => $imageUrl,
            'caption' => $caption
        ]);
        
        // POST ke API Fonnte
        $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])
            ->post('https://api.fonnte.com/send', $payload);
        
        $responseData = $response->json();
        Log::info('Fonnte image sent to ' . $target, [
            'response' => $responseData,
            'status_code' => $response->status()
        ]);
        
        // Handle response
        if ($response->successful()) {
            $isSuccess = (isset($responseData['status']) && 
                         ($responseData['status'] === true || $responseData['status'] === 'success'))
                || (isset($responseData['detail']) && 
                    strpos(strtolower($responseData['detail']), 'success') !== false);
            
            if ($isSuccess) {
                return [
                    'status' => true,
                    'message' => 'Gambar berhasil dikirim',
                    'data' => $responseData
                ];
            } else {
                $errorMsg = $responseData['message'] ?? 'Gagal mengirim gambar';
                return [
                    'status' => false,
                    'message' => $errorMsg,
                    'data' => $responseData
                ];
            }
        } else {
            $errorMsg = $responseData['message'] ?? 'HTTP Error: ' . $response->status();
            return [
                'status' => false,
                'message' => $errorMsg,
                'data' => $responseData,
                'http_status' => $response->status()
            ];
        }
        
    } catch (\Exception $e) {
        Log::error('Fonnte send image error: ' . $e->getMessage());
        return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
```

**Perbedaan dengan sendMessage():**
- Parameter tambahan: `url` (URL gambar)
- Parameter tambahan: `type` = 'image'
- Parameter `caption` untuk keterangan gambar
- URL gambar harus accessible secara public (https://)

**Contoh Penggunaan:**
```php
$whatsappService = app(WhatsappService::class);

$result = $whatsappService->sendImage(
    '628123456789',
    'https://yourdomain.com/images/product/K10.jpg',
    'K10 - Keling 10mm - Warna NKL'
);

if ($result['status']) {
    echo "Gambar terkirim!";
} else {
    echo "Error: " . $result['message'];
}
```

## 7.4 Format Request & Response

### 7.4.1 Send Message - Request

**HTTP Method:** POST

**URL:** `https://api.fonnte.com/send`

**Headers:**
```http
Authorization: {your_fonnte_token}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "target": "628123456789",
  "message": "Halo, ini pesan dari aplikasi!",
  "countryCode": "62"
}
```

**Penjelasan Parameter:**
- `target`: Nomor HP tujuan
  - Format bisa: `08xxx` atau `628xxx` atau `+628xxx`
  - Fonnte akan auto-format ke `628xxx`
- `message`: Isi pesan
  - Mendukung WhatsApp formatting:
    - `*bold*` → **bold**
    - `_italic_` → *italic*
    - `~strikethrough~` → ~~strikethrough~~
    - ```monospace``` → `monospace`
  - Maksimal: 4096 karakter (batasan WhatsApp)
- `countryCode`: Kode negara (default: 62 untuk Indonesia)

### 7.4.2 Send Message - Response

**Success Response:**
```json
{
  "status": "success",
  "detail": "success! message in queue",
  "id": "msg_abc123xyz"
}
```

**Error Response (Invalid Token):**
```json
{
  "status": false,
  "reason": "invalid token",
  "message": "Token tidak valid"
}
```

**Error Response (Device Disconnected):**
```json
{
  "status": false,
  "reason": "device disconnected",
  "message": "Device WhatsApp terputus, silakan scan ulang QR Code"
}
```

**Error Response (Quota Exceeded):**
```json
{
  "status": false,
  "reason": "quota exceeded",
  "message": "Kuota pesan habis, silakan top up"
}
```

### 7.4.3 Send Image - Request

**Body (JSON):**
```json
{
  "target": "628123456789",
  "url": "https://yourdomain.com/images/product.jpg",
  "type": "image",
  "message": "Ini caption gambar",
  "caption": "Ini caption gambar",
  "countryCode": "62"
}
```

**Penjelasan Parameter Tambahan:**
- `url`: URL gambar yang bisa diakses publik
  - Harus HTTPS (tidak boleh HTTP)
  - Format: JPG, PNG, GIF
  - Ukuran: Maksimal 5MB (recommended: < 1MB)
- `type`: `"image"`
- `message` / `caption`: Keterangan gambar (salah satu saja)

**Contoh URL Gambar:**
```
✅ VALID:
- https://yourdomain.com/storage/products/keling-10.jpg
- https://cdn.yoursite.com/images/product123.png

❌ INVALID:
- http://localhost/images/product.jpg (localhost tidak bisa diakses dari luar)
- /storage/products/product.jpg (relative path)
- C:\xampp\htdocs\images\product.jpg (local file path)
```

## 7.5 Webhook - Receiving Messages

### 7.5.1 Webhook Request Format

Ketika customer mengirim pesan, Fonnte akan POST ke webhook URL aplikasi dengan format:

**HTTP Method:** POST

**URL:** `https://yourdomain.com/api/whatsapp/webhook`

**Headers:**
```http
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "device": "628xxxxxxxxxx",
  "sender": "628123456789",
  "message": "Halo, saya mau pesan K10",
  "member_name": "Customer Name",
  "timestamp": "2025-12-16 10:30:45",
  "group": false
}
```

**Penjelasan Field:**
- `device`: Nomor WhatsApp bisnis (nomor toko)
- `sender`: Nomor HP pengirim (customer)
- `message`: Isi pesan dari customer
- `member_name`: Nama kontak (jika tersimpan di HP)
- `timestamp`: Waktu pesan diterima
- `group`: `true` jika dari group, `false` jika personal

### 7.5.2 Webhook Handler Implementation

**File: `app/Http/Controllers/WhatsAppController.php`**

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatbotService;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $chatbotService;
    
    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }
    
    /**
     * Webhook handler untuk terima pesan dari Fonnte
     */
    public function webhook(Request $request)
    {
        // 1. Log seluruh request untuk debugging
        Log::info('WhatsApp Webhook Received', [
            'full_request' => $request->all(),
            'sender' => $request->input('sender'),
            'message' => $request->input('message'),
            'timestamp' => $request->input('timestamp'),
        ]);
        
        // 2. Extract data penting
        $sender = $request->input('sender');    // Nomor customer
        $message = $request->input('message');  // Isi pesan
        $isGroup = $request->input('group', false); // Group atau personal
        
        // 3. Validasi: Hanya proses pesan personal (bukan group)
        if ($isGroup) {
            Log::info('Message from group - ignored', [
                'sender' => $sender
            ]);
            return response()->json([
                'status' => 'ignored',
                'reason' => 'group message'
            ]);
        }
        
        // 4. Validasi: Pesan tidak boleh kosong
        if (empty($message)) {
            Log::warning('Empty message received', [
                'sender' => $sender
            ]);
            return response()->json([
                'status' => 'ignored',
                'reason' => 'empty message'
            ]);
        }
        
        // 5. Process message dengan ChatbotService
        try {
            $this->chatbotService->processMessage($sender, $message);
            
            Log::info('Message processed successfully', [
                'sender' => $sender,
                'message' => substr($message, 0, 50) // Log 50 char pertama saja
            ]);
            
            return response()->json([
                'status' => 'ok',
                'message' => 'Message processed'
            ]);
            
        } catch (\Exception $e) {
            // 6. Handle error saat processing
            Log::error('Error processing webhook message', [
                'sender' => $sender,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

**Flow Webhook:**
```
Fonnte → POST /api/whatsapp/webhook
  ↓
WhatsAppController::webhook()
  ↓
Log request
  ↓
Validate: is group? → YES → Return "ignored"
  ↓ NO
Validate: message empty? → YES → Return "ignored"
  ↓ NO
ChatbotService::processMessage(sender, message)
  ↓
Process dengan rules
  ↓
Generate response
  ↓
WhatsappService::sendMessage()
  ↓
Return 200 OK to Fonnte
```

## 7.6 Error Handling & Recovery

### 7.6.1 Common Errors

**1. Invalid Token**

**Error:**
```json
{
  "status": false,
  "reason": "invalid token"
}
```

**Penyebab:**
- Token salah atau expired
- Token belum diaktifkan di dashboard Fonnte

**Solusi:**
```php
// Di WhatsappService.php, sudah di-handle:
if ($reason === 'invalid token') {
    $errorMsg = 'Token Fonnte tidak valid. Periksa FONNTE_TOKEN di .env';
}
```

**Action:**
1. Login ke dashboard Fonnte
2. Cek API token masih aktif
3. Generate ulang token jika perlu
4. Update `.env` file
5. `php artisan config:clear`

**2. Device Disconnected**

**Error:**
```json
{
  "status": false,
  "reason": "device disconnected"
}
```

**Penyebab:**
- WhatsApp logout dari Fonnte
- QR Code expired
- HP mati/tidak ada internet

**Solusi:**
1. Buka dashboard Fonnte
2. Scan ulang QR Code dengan WhatsApp
3. Pastikan HP online dan ada internet

**3. Quota Exceeded**

**Error:**
```json
{
  "status": false,
  "reason": "quota exceeded"
}
```

**Penyebab:**
- Kuota pesan Fonnte habis

**Solusi:**
1. Top up saldo di dashboard Fonnte
2. Upgrade paket jika perlu

**4. Network Error / Timeout**

**Error:**
```php
Exception: "Connection timeout"
```

**Penyebab:**
- Internet down
- API Fonnte sedang down
- Firewall block request

**Solusi:**
```php
// Already handled with try-catch
try {
    $response = Http::timeout(10) // Set timeout 10 detik
        ->withHeaders([...])
        ->post(...);
} catch (\Exception $e) {
    Log::error('Network error: ' . $e->getMessage());
    // Bisa implement retry mechanism
}
```

### 7.6.2 Retry Mechanism (Optional Enhancement)

**Contoh implementasi retry:**

```php
public function sendMessageWithRetry($target, $message, $maxRetries = 3)
{
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        $attempt++;
        
        $result = $this->sendMessage($target, $message);
        
        if ($result['status'] === true) {
            // Success!
            return $result;
        }
        
        // Failed, check if retriable
        $reason = $result['reason'] ?? null;
        
        // Jangan retry jika invalid token atau quota exceeded
        if (in_array($reason, ['invalid token', 'quota exceeded'])) {
            return $result;
        }
        
        // Retry untuk network error
        Log::warning("Retry attempt {$attempt}/{$maxRetries}", [
            'target' => $target,
            'error' => $result['message']
        ]);
        
        // Wait before retry (exponential backoff)
        sleep(pow(2, $attempt)); // 2s, 4s, 8s
    }
    
    // All retries failed
    return $result;
}
```

## 7.7 Rate Limiting & Best Practices

### 7.7.1 Fonnte Rate Limits

**Limits (berdasarkan paket):**
- Free Trial: 50 pesan/hari
- Starter: 500 pesan/hari
- Professional: 5000 pesan/hari
- Enterprise: Unlimited (fair use)

**Implementasi Rate Limiting di Aplikasi:**

```php
// Bisa implement dengan Laravel Rate Limiter
use Illuminate\Support\Facades\RateLimiter;

public function sendMessage($target, $message)
{
    // Check rate limit
    $key = 'whatsapp-send:' . $target;
    
    if (RateLimiter::tooManyAttempts($key, 10)) {
        // Max 10 pesan per menit per nomor
        Log::warning('Rate limit exceeded', ['target' => $target]);
        return [
            'status' => false,
            'message' => 'Terlalu banyak pesan. Tunggu sebentar.'
        ];
    }
    
    // Hit rate limiter
    RateLimiter::hit($key, 60); // Decay 60 detik
    
    // ... actual send logic
}
```

### 7.7.2 Best Practices

**1. Always Log**
```php
// Log request
Log::info('Sending WhatsApp message', [
    'target' => $target,
    'message_length' => strlen($message)
]);

// Log response
Log::info('WhatsApp message sent', $responseData);
```

**2. Use Environment Variables**
```php
// JANGAN hardcode token
❌ $token = 'abc123xyz789'; 

// GUNAKAN .env
✅ $token = env('FONNTE_TOKEN');
```

**3. Validate Input**
```php
// Validate phone number
if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    return ['status' => false, 'message' => 'Invalid phone number'];
}

// Validate message not empty
if (empty(trim($message))) {
    return ['status' => false, 'message' => 'Message cannot be empty'];
}
```

**4. Handle Exceptions**
```php
try {
    $response = Http::post(...);
} catch (\Illuminate\Http\Client\ConnectionException $e) {
    // Network error
    Log::error('Connection error: ' . $e->getMessage());
} catch (\Exception $e) {
    // Other errors
    Log::error('General error: ' . $e->getMessage());
}
```

**5. Set Timeout**
```php
$response = Http::timeout(10) // 10 seconds timeout
    ->withHeaders([...])
    ->post(...);
```

## 7.8 Testing API Fonnte

### 7.8.1 Manual Testing dengan Postman/cURL

**Test 1: Send Message**

**cURL Command:**
```bash
curl -X POST "https://api.fonnte.com/send" \
  -H "Authorization: your_fonnte_token" \
  -H "Content-Type: application/json" \
  -d '{
    "target": "628123456789",
    "message": "Test message from API",
    "countryCode": "62"
  }'
```

**Expected Response:**
```json
{
  "status": "success",
  "detail": "success! message in queue",
  "id": "msg_xyz123"
}
```

**Test 2: Send Image**

**cURL Command:**
```bash
curl -X POST "https://api.fonnte.com/send" \
  -H "Authorization: your_fonnte_token" \
  -H "Content-Type: application/json" \
  -d '{
    "target": "628123456789",
    "url": "https://example.com/image.jpg",
    "type": "image",
    "message": "Test image caption",
    "countryCode": "62"
  }'
```

### 7.8.2 Testing via Laravel Tinker

**Test di Terminal:**

```bash
php artisan tinker
```

**Di Tinker Shell:**

```php
// Import service
$wa = app(\App\Services\WhatsappService::class);

// Test send message
$result = $wa->sendMessage('628123456789', 'Halo, ini test dari Tinker!');
print_r($result);

// Test send image
$result = $wa->sendImage(
    '628123456789',
    'https://via.placeholder.com/500',
    'Test gambar'
);
print_r($result);
```

**Expected Output:**
```php
Array
(
    [status] => 1
    [message] => Pesan berhasil dikirim
    [data] => Array
        (
            [status] => success
            [detail] => success! message in queue
            [id] => msg_abc123
        )
)
```

### 7.8.3 Automated Testing

**File: `tests/Unit/WhatsappServiceTest.php`**

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Http;

class WhatsappServiceTest extends TestCase
{
    public function test_send_message_success()
    {
        // Mock HTTP response
        Http::fake([
            'api.fonnte.com/*' => Http::response([
                'status' => 'success',
                'detail' => 'success! message in queue',
                'id' => 'msg_test_123'
            ], 200)
        ]);
        
        $service = new WhatsappService();
        $result = $service->sendMessage('628123456789', 'Test message');
        
        // Assert
        $this->assertTrue($result['status']);
        $this->assertEquals('Pesan berhasil dikirim', $result['message']);
    }
    
    public function test_send_message_invalid_token()
    {
        // Mock HTTP response with error
        Http::fake([
            'api.fonnte.com/*' => Http::response([
                'status' => false,
                'reason' => 'invalid token',
                'message' => 'Token tidak valid'
            ], 401)
        ]);
        
        $service = new WhatsappService();
        $result = $service->sendMessage('628123456789', 'Test');
        
        // Assert
        $this->assertFalse($result['status']);
        $this->assertStringContainsString('Token Fonnte tidak valid', $result['message']);
    }
}
```

**Run Test:**
```bash
php artisan test --filter WhatsappServiceTest
```

## 7.9 Monitoring & Logging

### 7.9.1 Log Structure

**Log Location:** `storage/logs/laravel.log`

**Log Format:**

```
[2025-12-16 10:30:45] local.INFO: WhatsApp Webhook Received
{
  "sender": "628123456789",
  "message": "STOK K10",
  "timestamp": "2025-12-16 10:30:45"
}

[2025-12-16 10:30:46] local.INFO: WhatsApp Message After Processing
{
  "phone": "628123456789",
  "processed_message": "stok k10",
  "is_numeric": false,
  "message_length": 8
}

[2025-12-16 10:30:47] local.INFO: Fonnte message sent to 628123456789
{
  "status": "success",
  "detail": "success! message in queue",
  "id": "msg_abc123"
}
```

### 7.9.2 Monitoring Dashboard

**Fonnte Dashboard Metrics:**
1. Jumlah pesan terkirim hari ini
2. Jumlah pesan gagal
3. Status device (connected/disconnected)
4. Quota remaining

**Laravel Application Metrics:**
1. Total orders created (dari WhatsApp)
2. Success rate (%)
3. Average response time
4. Common errors

**Query untuk Metrics:**

```php
// Total orders hari ini
$todayOrders = WhatsappOrder::whereDate('created_at', today())->count();

// Success rate
$totalOrders = WhatsappOrder::count();
$successOrders = WhatsappOrder::whereIn('status', ['confirmed', 'processed'])->count();
$successRate = ($successOrders / $totalOrders) * 100;

// Average response time (need to log timestamps)
// Bisa implement dengan menambah field 'response_time' di logs
```

## 7.10 Security Considerations

### 7.10.1 Token Security

**Cara Aman Menyimpan Token:**

```php
// ✅ AMAN: Di .env file (tidak di-commit ke Git)
FONNTE_TOKEN=abc123xyz

// ❌ TIDAK AMAN: Hardcode di code
$token = 'abc123xyz'; // JANGAN!
```

**Pastikan `.env` ada di `.gitignore`:**
```
# File: .gitignore
.env
.env.backup
.env.production
```

### 7.10.2 Webhook Security

**Validasi Request dari Fonnte:**

```php
public function webhook(Request $request)
{
    // Optional: Validate request IP
    $allowedIPs = [
        '103.xx.xx.xx', // Fonnte server IP
        // Add more IPs from Fonnte documentation
    ];
    
    if (!in_array($request->ip(), $allowedIPs)) {
        Log::warning('Webhook from unauthorized IP', [
            'ip' => $request->ip()
        ]);
        return response()->json(['status' => 'unauthorized'], 401);
    }
    
    // ... process webhook
}
```

### 7.10.3 Input Sanitization

**Sanitize input dari customer:**

```php
// Di ChatbotService::processMessage()
public function processMessage($phone, $message)
{
    // 1. Trim whitespace
    $message = trim($message);
    
    // 2. Remove control characters
    $message = preg_replace('/[\x00-\x1F\x7F]/u', '', $message);
    
    // 3. Limit length (prevent DoS)
    $message = substr($message, 0, 1000);
    
    // 4. Lowercase untuk command matching
    $message = strtolower($message);
    
    // ... process
}
```

## 7.11 Cost Calculation

### 7.11.1 Fonnte Pricing (Referensi per Desember 2025)

**Paket Berlangganan:**
| Paket | Harga/Bulan | Pesan/Hari | Fitur |
|-------|-------------|------------|-------|
| Free Trial | Rp 0 | 50 | Basic features |
| Starter | Rp 100.000 | 500 | Text, Image, Button |
| Professional | Rp 300.000 | 5000 | All features + Priority support |
| Enterprise | Custom | Unlimited | Dedicated server |

**Perhitungan Biaya:**
```
Asumsi:
- Average 100 orders/hari via WhatsApp
- Setiap order = 3 pesan (menu + confirmation + success)
- Total pesan/hari = 100 × 3 = 300 pesan
- Tambahan stock queries & catalog = +100 pesan
- Total = 400 pesan/hari

Paket yang cocok: STARTER (500 pesan/hari)
Biaya: Rp 100.000/bulan = Rp 1.200.000/tahun
```

## 7.12 Alternatives to Fonnte

### 7.12.1 Perbandingan WhatsApp Gateway

| Provider | Harga/Bulan | Ease of Use | Features | Support |
|----------|-------------|-------------|----------|---------|
| **Fonnte** | Rp 100k - 300k | ⭐⭐⭐⭐⭐ Sangat Mudah | ⭐⭐⭐⭐ Lengkap | ⭐⭐⭐⭐ Baik (ID) |
| **Wablas** | Rp 150k - 500k | ⭐⭐⭐⭐ Mudah | ⭐⭐⭐⭐⭐ Sangat Lengkap | ⭐⭐⭐⭐ Baik (ID) |
| **WooWA** | Rp 200k - 600k | ⭐⭐⭐ Sedang | ⭐⭐⭐⭐ Lengkap | ⭐⭐⭐ Cukup |
| **Official WA Business API** | $1000+/bulan | ⭐⭐ Sulit | ⭐⭐⭐⭐⭐ Complete | ⭐⭐⭐⭐⭐ Excellent |

**Alasan Memilih Fonnte:**
1. ✅ Harga terjangkau untuk UMKM
2. ✅ Setup mudah (scan QR code saja)
3. ✅ Dokumentasi Bahasa Indonesia
4. ✅ Support cepat via WhatsApp
5. ✅ No coding untuk setup basic

---

**Catatan untuk Skripsi:**
- Tambahkan screenshot dashboard Fonnte
- Tambahkan screenshot contoh request/response di Postman
- Tambahkan tabel biaya untuk 6 bulan / 1 tahun
- Sertakan dokumentasi lengkap error codes dari Fonnte
