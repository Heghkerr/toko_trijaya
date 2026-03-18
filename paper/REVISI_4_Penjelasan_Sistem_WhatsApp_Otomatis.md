# REVISI 4: Penjelasan Detail Sistem Respons Otomatis WhatsApp

## 4.1 Pendahuluan Sistem Respons Otomatis WhatsApp

Sistem respons otomatis WhatsApp yang dikembangkan dalam aplikasi ini merupakan sistem berbasis aturan (rule-based system) yang dirancang untuk membantu customer melakukan pemesanan produk secara otomatis melalui WhatsApp tanpa perlu interaksi langsung dengan pemilik toko.

Sistem ini BUKAN menggunakan teknologi Artificial Intelligence (AI) atau Machine Learning, melainkan menggunakan pendekatan rule-based matching di mana setiap perintah customer akan dicocokkan dengan pola (pattern) tertentu dan memberikan respons yang sudah ditentukan sebelumnya.

**Istilah yang Tepat:**
- ❌ TIDAK TEPAT: "Chatbot", "AI Chatbot", "Smart Chatbot"
- ✅ TEPAT: "Sistem Respons Otomatis WhatsApp Berbasis Aturan" atau "WhatsApp Auto-Reply System"

## 4.2 Arsitektur Sistem

Sistem terdiri dari komponen-komponen berikut:

### 4.2.1 Komponen Utama

1. **WhatsappService** (`app/Services/WhatsappService.php`)
   - Bertanggung jawab untuk komunikasi dengan API Fonnte.com
   - Mengirim pesan WhatsApp ke customer
   - Mengirim gambar/media ke customer

2. **ChatbotService** (`app/Services/ChatbotService.php`)
   - Inti dari sistem respons otomatis
   - Memproses pesan masuk dari customer
   - Mencocokkan pesan dengan aturan (rules) yang telah didefinisikan
   - Mengelola state/kondisi percakapan menggunakan cache
   - Menghasilkan respons yang sesuai

3. **WhatsappOrderController** (`app/Http/Controllers/WhatsappOrderController.php`)
   - Mengelola tampilan dashboard pesanan WhatsApp
   - Memproses pesanan menjadi transaksi
   - Menangani pembatalan pesanan

### 4.2.2 Model Data

1. **WhatsappOrder** (`app/Models/WhatsappOrder.php`)
   - Menyimpan data pesanan dari WhatsApp
   - Field: id, name, phone, order_text, status, created_at, updated_at

2. **WhatsappOrderItem** (`app/Models/WhatsappOrderItem.php`)
   - Menyimpan detail item pesanan
   - Field: id, whatsapp_order_id, product_id, color_id, product_unit_id, quantity, stock_pcs

3. **Customer** (`app/Models/Customer.php`)
   - Menyimpan data customer yang melakukan pemesanan

## 4.3 Cara Kerja Sistem Respons Otomatis

### 4.3.1 Alur Utama (Main Flow)

```
Customer → WhatsApp → API Fonnte.com → Webhook → ChatbotService → WhatsappService → API Fonnte.com → WhatsApp → Customer
```

**Penjelasan Alur:**

1. **Customer mengirim pesan** melalui WhatsApp ke nomor bisnis Toko Trijaya
2. **API Fonnte.com menerima** pesan dan meneruskan ke webhook aplikasi
3. **Webhook** memanggil `ChatbotService::processMessage()`
4. **ChatbotService** memproses pesan:
   - Normalisasi pesan (lowercase, trim)
   - Deteksi command/perintah
   - Cocokkan dengan aturan (rules)
   - Generate respons yang sesuai
5. **WhatsappService** mengirim respons ke API Fonnte.com
6. **API Fonnte.com** meneruskan respons ke WhatsApp customer

### 4.3.2 Aturan-Aturan (Rules) yang Diterapkan

Sistem menggunakan pendekatan rule-based matching dengan prioritas sebagai berikut:

#### **Priority 1: Command Reset (Tertinggi)**
- Command: `0`, `MENU`
- Aksi: Clear semua state, kirim menu utama
- Tujuan: Customer bisa reset percakapan kapan saja

#### **Priority 2: Order State Check**
- Cek apakah customer sedang dalam proses pemesanan
- Jika ya, lanjutkan ke step berikutnya
- Jika tidak, lanjut ke rule berikutnya

#### **Priority 3: FAQ Command**
- Command: `FAQ`, `5`
- Aksi: Tampilkan halaman FAQ

#### **Priority 4: Order Format Detection**
- Pattern: Deteksi format `NAMA:` dan `NAMA PRODUK:` / `PESANAN:`
- Aksi: Mulai proses pemesanan

#### **Priority 5: Specific Commands**
- `STOK [nama produk]` → Cek stok produk
- `KATALOG` atau `1` → Tampilkan katalog
- `PESAN` atau `3` → Mulai pemesanan
- `CEK PESANAN` atau `4` → Lihat pesanan
- `BATAL PESANAN [id]` → Batalkan pesanan

#### **Priority 6: Thank You Detection**
- Keywords: `terima kasih`, `makasih`, `thanks`
- Aksi: Balas "sama-sama" dan tampilkan menu

#### **Priority 7: Numeric Menu Shortcut**
- Input: Angka 1-5
- Aksi: Akses menu cepat

#### **Priority 8: Context-Aware Response**
- Jika customer baru lihat katalog produk
- Input nama produk langsung → Auto cek stok

#### **Priority 9: Default Response**
- Jika tidak cocok dengan rule manapun
- Aksi: Tampilkan menu pilihan

### 4.3.3 State Management (Pengelolaan Kondisi)

Sistem menggunakan **Laravel Cache** untuk menyimpan state/kondisi percakapan setiap customer:

**1. Order State** (`order_state_{phone}`)
- Menyimpan progres pemesanan customer
- Struktur:
  ```php
  [
    'step' => 'waiting_order' | 'confirmation',
    'data' => [
      'name' => 'Nama Customer',
      'order' => 'Detail pesanan',
      'phone' => '628xxx',
      'items' => [array of order items]
    ]
  ]
  ```
- Expire: 30 menit (jika tidak ada aktivitas)

**2. Catalog State** (`catalog_state_{phone}`)
- Menyimpan status browsing katalog
- Struktur:
  ```php
  [
    'viewing_types' => true/false,
    'types' => [array of type IDs],
    'count' => jumlah types
  ]
  ```
- Expire: 10 menit

**3. Last Context** (`last_context_{phone}`)
- Menyimpan konteks terakhir untuk respons kontekstual
- Struktur:
  ```php
  [
    'action' => 'viewed_products',
    'type_id' => ID jenis produk,
    'type_name' => 'Nama jenis'
  ]
  ```
- Expire: 10 menit

**4. Menu Sent Flag** (`chatbot_menu_sent_{phone}_{date}`)
- Flag untuk tracking apakah menu sudah dikirim hari ini
- Tujuan: Hindari spam menu
- Expire: Hingga tengah malam

## 4.4 Data yang Digunakan

### 4.4.1 Data Master

Sistem menggunakan data dari tabel-tabel berikut:

1. **products** - Data produk
   - Kolom: id, name, type_id, color_id, created_at, updated_at
   
2. **product_units** - Data satuan dan harga produk
   - Kolom: id, product_id, name, stock, price, conversion_value
   - Contoh: LUSIN (stock: 10, conversion: 12 pcs), GROSS (stock: 5, conversion: 144 pcs)

3. **product_types** - Jenis produk
   - Kolom: id, name
   - Contoh: KELING, KANCING, RING KUNCI, GESPER

4. **product_colors** - Warna produk
   - Kolom: id, name
   - Contoh: NKL (Nikel), BN (Bening), LG (Lengkung)

5. **customers** - Data customer
   - Kolom: id, name, phone, address, created_at, updated_at

### 4.4.2 Data Transaksional

1. **whatsapp_orders** - Pesanan dari WhatsApp
   - Kolom: id, name, phone, order_text, status, items(JSON), created_at, updated_at
   - Status: pending, confirmed, cancelled, processed

2. **whatsapp_order_items** - Detail item pesanan
   - Kolom: id, whatsapp_order_id, product_id, color_id, product_unit_id, quantity, stock_pcs

3. **transactions** - Transaksi yang dibuat dari pesanan
   - Kolom: whatsapp_order_id (foreign key ke whatsapp_orders)

## 4.5 Algoritma Pattern Matching

### 4.5.1 Normalisasi Nama Produk

Sistem melakukan normalisasi nama produk agar customer bisa mengetik dengan berbagai variasi:

```php
Fungsi: normalizeProductName(string $input)

Input: "keling 10", "k 10", "k10", "K-10", "KELING 10"
Output: "K10"

Input: "KANCING 8", "kancing 8"
Output: "KANCING 8"

Proses:
1. Trim whitespace
2. Hapus karakter special (kecuali spasi, dash, underscore)
3. Deteksi pattern "keling" atau "k" diikuti angka
4. Konversi ke format standar "K[angka]"
5. Jika tidak match pattern, uppercase semua
```

### 4.5.2 Parsing Format Pesanan

Sistem mendukung 2 format pesanan:

**Format 1: Format Terstruktur (Recommended)**
```
NAMA: DARREN

NAMA PRODUK: K10, KC206
WARNA PRODUK: NKL, ATG
JUMLAH PRODUK: 2 LUSIN, 1 GROSS
```

**Format 2: Format Bebas (Legacy)**
```
NAMA: DARREN
PESANAN: 2 LUSIN K10 warna NKL, 1 GROSS KC206 warna ATG
```

**Algoritma Parsing:**

```php
Fungsi: parseOrderMessage(string $message)

Langkah:
1. Extract "NAMA:" menggunakan regex /nama\s*:\s*(.+?)(?=\n|$)/i
2. Extract "NAMA PRODUK:" → split by comma
3. Extract "WARNA PRODUK:" → split by comma
4. Extract "JUMLAH PRODUK:" → split by comma
5. Kombinasikan menjadi format: "[jumlah] [satuan] [produk] warna [warna]"
6. Fallback ke format lama jika format baru tidak terdeteksi
```

### 4.5.3 Validasi Stok

Sistem melakukan validasi stok real-time:

```php
Fungsi: verifyStockForOrder(string $orderText)

Langkah:
1. Split order text by comma/semicolon/newline
2. Untuk setiap item:
   a. Extract quantity, unit, product name, color
   b. Normalize product name
   c. Cari product di database (with color filter)
   d. Cari product_unit yang sesuai
   e. Validasi stock >= quantity
3. Return result:
   - ok: true/false
   - message: pesan error (jika ada)
   - items: array of validated items
```

**Contoh Validasi:**

```
Input: "2 LUSIN K10 warna NKL"

Proses:
1. Quantity: 2
2. Unit: LUSIN
3. Product: K10
4. Color: NKL

Query Database:
- Cari product name LIKE '%K10%' AND color name LIKE '%NKL%'
- Cari product_unit WHERE product_id = X AND name LIKE '%LUSIN%'
- Cek: product_unit.stock >= 2

Result:
✅ Stok tersedia: 10 LUSIN
✅ Validasi berhasil
```

## 4.6 Mekanisme Pencegahan Duplikasi

Sistem mengimplementasikan 2 layer pencegahan duplikasi:

### 4.6.1 Lock Mechanism
```php
$lockKey = 'order_lock_' . md5($phone . $order);
$lock = Cache::lock($lockKey, 10); // Lock 10 detik

if (!$lock->get()) {
    // Ada proses lain sedang create order
    // Wait dan cek apakah order sudah dibuat
}
```

### 4.6.2 Duplicate Detection
```php
// Cek order dengan phone + order_text yang sama
// dalam 5 menit terakhir dengan status active
$existingOrder = WhatsappOrder::where('phone', $phone)
    ->where('order_text', $order)
    ->whereIn('status', ['pending', 'confirmed'])
    ->where('created_at', '>=', now()->subMinutes(5))
    ->first();
```

## 4.7 Integrasi dengan API Fonnte.com

### 4.7.1 Endpoint API yang Digunakan

**1. Send Message**
- URL: `https://api.fonnte.com/send`
- Method: POST
- Headers: `Authorization: {FONNTE_TOKEN}`
- Payload:
  ```json
  {
    "target": "628xxxx",
    "message": "Isi pesan",
    "countryCode": "62"
  }
  ```

**2. Send Image**
- URL: `https://api.fonnte.com/send`
- Method: POST
- Payload:
  ```json
  {
    "target": "628xxxx",
    "url": "https://example.com/image.jpg",
    "type": "image",
    "message": "Caption gambar",
    "countryCode": "62"
  }
  ```

### 4.7.2 Webhook Configuration

Aplikasi menerima webhook dari Fonnte.com di endpoint:
```
POST /api/whatsapp/webhook
```

Request dari Fonnte berisi:
- `device`: Device ID pengirim
- `sender`: Nomor HP pengirim (customer)
- `message`: Isi pesan
- `member_name`: Nama kontak (jika tersimpan)

## 4.8 Flow Diagram Sistem

### 4.8.1 Flow Menu Utama

```
START
  ↓
Customer kirim pesan
  ↓
Cek command "0" atau "MENU"? → YES → Clear state → Kirim Menu Utama → END
  ↓ NO
Cek sudah kirim menu hari ini? → NO → Kirim Menu Utama (1x per hari)
  ↓ YES
Cek sedang order? → YES → Handle Order Step → END
  ↓ NO
Deteksi Command:
  - STOK → Handle Stock Query → END
  - KATALOG → Handle Catalog → END
  - PESAN → Start Order Process → END
  - CEK PESANAN → Show Orders → END
  - Angka 1-5 → Menu Shortcut → END
  ↓
Tidak dikenali → Kirim Menu Pilihan → END
```

### 4.8.2 Flow Pemesanan (Order Flow)

```
Customer ketik "PESAN" atau "3"
  ↓
Sistem kirim format pesanan
  ↓
Set state: order_state = 'waiting_order'
  ↓
Customer kirim format pesanan
  ↓
Parse format (NAMA:, NAMA PRODUK:, WARNA:, JUMLAH:)
  ↓
Validasi format lengkap? → NO → Kirim error + contoh format → Kembali
  ↓ YES
Validasi stok untuk setiap item
  ↓
Stok cukup? → NO → Kirim pesan stok tidak cukup → END
  ↓ YES
Set state: order_state = 'confirmation'
  ↓
Kirim konfirmasi pesanan
  ↓
Customer ketik "YA" atau "BENAR"?
  ↓ NO → Ketik "TIDAK"? → YES → Batalkan → END
  ↓ YES
Acquire Lock (prevent double order)
  ↓
Cek duplicate order (5 menit terakhir)? → YES → Kirim pesan "sudah diterima" → END
  ↓ NO
Simpan WhatsappOrder ke database
  ↓
Simpan WhatsappOrderItem (detail items)
  ↓
Kirim notifikasi ke Owner
  ↓
Kirim konfirmasi ke Customer
  ↓
Clear state
  ↓
Release Lock
  ↓
END
```

### 4.8.3 Flow Validasi Stok

```
Input: "2 LUSIN K10 warna NKL"
  ↓
Extract dengan regex pattern
  ↓
Parse result:
  - quantity: 2
  - unit: LUSIN
  - product: K10
  - color: NKL
  ↓
Normalize product name: K10
  ↓
Query database:
  SELECT * FROM products
  WHERE (name LIKE '%K10%')
  AND EXISTS (
    SELECT 1 FROM product_colors
    WHERE id = products.color_id
    AND name LIKE '%NKL%'
  )
  ↓
Product found? → NO → Return error "Produk tidak ditemukan"
  ↓ YES
Query product_unit:
  SELECT * FROM product_units
  WHERE product_id = {product_id}
  AND name LIKE '%LUSIN%'
  ↓
Unit found? → NO → Tampilkan list unit yang tersedia → END
  ↓ YES
Cek stock: product_unit.stock >= quantity?
  ↓ NO → Return "Stok tidak mencukupi"
  ↓ YES
Return validation success dengan detail item
```

## 4.9 Pengujian Sistem

### 4.9.1 Skenario Pengujian

**Test Case 1: Menu Navigation**
- Input: "MENU" atau "0"
- Expected: Sistem kirim menu utama dan clear semua state
- Result: ✅ Pass

**Test Case 2: Stock Query - Produk Ditemukan**
- Input: "STOK K10"
- Expected: Sistem tampilkan stok K10 semua warna yang tersedia
- Result: ✅ Pass

**Test Case 3: Stock Query - Produk Tidak Ditemukan**
- Input: "STOK XYZ123"
- Expected: Sistem kirim pesan "Produk tidak ditemukan" + menu navigasi
- Result: ✅ Pass

**Test Case 4: Order - Format Benar**
- Input:
  ```
  NAMA: DARREN
  
  NAMA PRODUK: K10
  WARNA PRODUK: NKL
  JUMLAH PRODUK: 2 LUSIN
  ```
- Expected: Sistem parse, validasi stok, kirim konfirmasi
- Result: ✅ Pass

**Test Case 5: Order - Format Salah (Tidak Lengkap)**
- Input:
  ```
  NAMA: DARREN
  NAMA PRODUK: K10
  ```
  (Tidak ada warna & jumlah)
- Expected: Sistem deteksi produk, tampilkan warna & satuan yang tersedia + contoh format
- Result: ✅ Pass

**Test Case 6: Order - Stok Tidak Cukup**
- Input: "5 LUSIN K10 warna NKL" (stok hanya 3 LUSIN)
- Expected: Sistem kirim pesan "Stok tidak mencukupi" dengan detail
- Result: ✅ Pass

**Test Case 7: Order - Konfirmasi Pesanan**
- State: Sedang dalam konfirmasi pesanan
- Input: "YA"
- Expected: Order tersimpan ke database, notifikasi ke owner, konfirmasi ke customer
- Result: ✅ Pass

**Test Case 8: Duplicate Order Prevention**
- Scenario: Customer konfirmasi 2x dalam 1 menit
- Expected: Order hanya tersimpan 1x, respons "sudah diterima sebelumnya"
- Result: ✅ Pass

**Test Case 9: Catalog Browsing**
- Input 1: "KATALOG" → Sistem tampilkan list jenis produk
- Input 2: "1" → Sistem tampilkan produk jenis pertama
- Result: ✅ Pass

**Test Case 10: Context-Aware Stock Check**
- Input 1: "KATALOG" → pilih "1" (misal KELING)
- Input 2: "K10" (langsung ketik nama produk)
- Expected: Sistem auto cek stok K10 (tanpa ketik "STOK")
- Result: ✅ Pass

### 4.9.2 Pengujian Integrasi API

**Test Case: Fonnte API Connection**
- Method: POST to `https://api.fonnte.com/send`
- Headers: Authorization token
- Expected: Response 200 OK dengan status success
- Result: ✅ Pass (verified dengan nomor test)

**Test Case: Webhook Receiving**
- Method: POST from Fonnte to `/api/whatsapp/webhook`
- Expected: Aplikasi terima dan proses pesan
- Result: ✅ Pass

## 4.10 Keunggulan Sistem

1. **Real-time Stock Check**
   - Customer bisa cek stok langsung tanpa tunggu owner
   - Data stok selalu update sesuai database

2. **Automatic Order Validation**
   - Sistem validasi format pesanan otomatis
   - Cek stok otomatis sebelum order dibuat
   - Prevent duplicate order

3. **Context-Aware**
   - Sistem ingat konteks percakapan terakhir
   - Bisa auto-complete action berdasarkan konteks

4. **User-Friendly**
   - Multiple format input (angka, text, kombinasi)
   - Error message yang jelas dengan contoh
   - FAQ untuk panduan

5. **Owner Notification**
   - Owner langsung dapat notifikasi pesanan baru
   - Detail lengkap ada di dashboard web

## 4.11 Keterbatasan Sistem

1. **Rule-Based, Bukan AI**
   - Hanya bisa mengenali pattern yang sudah didefinisikan
   - Tidak bisa memahami variasi bahasa alami yang kompleks
   - Jika customer typo atau format berbeda, sistem tidak bisa proses

2. **Dependency pada Fonnte.com**
   - Jika Fonnte down, sistem tidak bisa kirim/terima pesan
   - Biaya berlangganan Fonnte diperlukan

3. **State Management Temporary**
   - State disimpan di cache (Redis/File)
   - Jika cache clear, state hilang dan customer harus mulai dari awal

4. **No Natural Language Processing**
   - Tidak bisa jawab pertanyaan bebas
   - Hanya bisa response untuk command tertentu

---

**Catatan untuk Revisi:**
- Tambahkan screenshot flow diagram (bisa dibuat dengan draw.io atau Lucidchart)
- Tambahkan screenshot contoh percakapan di WhatsApp
- Tambahkan tabel mapping command → action → response
