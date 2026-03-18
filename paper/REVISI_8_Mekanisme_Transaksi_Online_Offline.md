# REVISI 8: Mekanisme Transaksi Online dan Offline

## 8.1 Pendahuluan

Aplikasi Toko Trijaya mendukung 2 jenis transaksi:
1. **Transaksi Offline (In-Store)** - Customer datang langsung ke toko
2. **Transaksi Online (WhatsApp)** - Customer pesan via WhatsApp

Kedua jenis transaksi ini memiliki flow yang berbeda namun bermuara pada sistem transaksi yang sama.

## 8.2 Transaksi Offline (In-Store Transaction)

### 8.2.1 Definisi

Transaksi offline adalah transaksi yang dilakukan secara langsung di toko, di mana customer datang ke lokasi fisik Toko Trijaya dan melakukan pembelian produk secara langsung dengan bantuan kasir.

### 8.2.2 Flow Transaksi Offline

```
═══════════════════════════════════════
FASE 1: CUSTOMER DATANG KE TOKO
═══════════════════════════════════════

Customer datang ke toko
  ↓
Customer memilih produk yang ingin dibeli
  ↓
Customer memberikan produk ke kasir

═══════════════════════════════════════
FASE 2: KASIR INPUT TRANSAKSI
═══════════════════════════════════════

Kasir login ke sistem POS
  ↓
Kasir buka menu "Transaksi Baru"
  ↓
Kasir cari produk:
  - Filter by jenis produk (KELING, KANCING, dll)
  - Filter by warna (NKL, BN, dll)
  - Search by nama produk
  ↓
Sistem tampilkan list ProductUnit yang match
  (1 produk bisa punya multiple units: LUSIN, GROSS, dll)
  ↓
Kasir pilih ProductUnit yang sesuai
  ↓
System tampilkan:
  - Nama produk + warna
  - Satuan (unit)
  - Stok tersedia
  - Harga per unit
  ↓
Kasir input quantity (jumlah yang dibeli)
  ↓
System validasi: stock >= quantity?
  ↓ NO → Show error "Stok tidak cukup"
  ↓ YES
System calculate subtotal: price × quantity
  ↓
Item ditambahkan ke cart
  ↓
Kasir ulangi untuk produk lain (jika ada)
  ↓
Kasir klik "Lanjut ke Pembayaran"

═══════════════════════════════════════
FASE 3: PEMBAYARAN
═══════════════════════════════════════

System tampilkan ringkasan:
  - List items + quantity + harga
  - Total amount
  - Discount (jika ada)
  - Grand Total
  ↓
Kasir input (optional):
  - Customer name
  - Customer phone
  - Discount amount
  ↓
Kasir pilih metode pembayaran:
  1. CASH (Tunai)
  2. CARD (Kartu Debit/Kredit)
  3. QRIS (QR Payment)
  ↓
IF Payment = CASH:
  Kasir input jumlah uang diterima (cash_amount)
  System calculate kembalian: cash_amount - grand_total
  Display kembalian
  ↓
Kasir pilih status:
  - PAID (Langsung bayar di toko)
  - UNPAID (Nota belum dibayar - untuk pre-order/kredit)
  ↓
Kasir klik "Simpan Transaksi"

═══════════════════════════════════════
FASE 4: PROSES DI BACKEND
═══════════════════════════════════════

System validate input:
  - User ID valid?
  - Cart items not empty?
  - Payment method valid?
  - Cash amount >= total? (jika payment = cash)
  ↓ Validation Failed → Return error
  ↓ Validation OK
Database Transaction Start
  ↓
Step 1: Handle Customer
  IF customer_id provided:
    → Use existing customer
  ELSE IF customer_phone provided:
    → Find customer by phone
    → IF found: use existing
    → IF not found: create new customer
  ELSE IF customer_name provided:
    → Find customer by name
    → IF found: use existing
    → IF not found: create new customer
  ↓
Step 2: Create Transaction Record
  INSERT INTO transactions:
    - user_id: {kasir ID}
    - customer_id: {customer ID or NULL}
    - transaction_code: TRX-{timestamp}
    - total_amount: {grand total}
    - discount: {discount amount}
    - payment_method: cash/card/qris
    - cash_amount: {jumlah uang diterima, jika cash}
    - change_amount: {kembalian, jika cash}
    - status: paid/unpaid
  
  Get transaction_id: 456
  ↓
Step 3: Create Transaction Details
  FOR EACH item in cart:
    INSERT INTO transaction_details:
      - transaction_id: 456
      - product_id: {product ID}
      - unit_name: {satuan, ex: LUSIN}
      - quantity: {jumlah}
      - price: {harga per unit}
      - subtotal: {price × quantity}
  ↓
Step 4: Update Stock (HANYA jika status = PAID)
  IF status == 'paid':
    FOR EACH item:
      - Find ProductUnit
      - Decrease stock: stock - quantity
      - Create Inventory record (type: keluar)
  ↓
Step 5: Create CashFlow (HANYA jika status = PAID)
  IF status == 'paid' AND total_amount > 0:
    Determine account type:
      - cash → account: 'cash'
      - card/qris → account: 'bank'
    
    INSERT INTO cash_flows:
      - user_id: {kasir ID}
      - flow_type: 'masuk'
      - source_type: 'transaction'
      - account: cash/bank
      - amount: {total_amount}
      - description: "Penjualan Transaksi #TRX-xxx"
      - transaction_id: 456
  ↓
Step 6: Update Daily Report
  IF status == 'paid':
    Find or create today's report for this user
    
    UPDATE reports:
      - total_sales += {total_amount}
      - total_cost += {cost of goods}
      - profit += {total_amount - cost}
      - {payment_method}_amount += {total_amount}
      - transaction_count += 1
  ↓
Database Transaction Commit
  ↓
Step 7: Generate Receipt/Nota
  Redirect to: /transactions/{id}
  Display:
    - Transaction code
    - Items list
    - Payment details
    - Barcode (transaction ID)
  ↓
Step 8: Print Receipt (Optional)
  Kasir klik "Cetak Nota"
  Print ke thermal printer / PDF
  ↓
END

IF ANY ERROR:
  → Database Transaction Rollback
  → Return error message
  → No data saved
```

### 8.2.3 Status Transaksi Offline

| Status | Deskripsi | Stock Updated? | CashFlow Created? |
|--------|-----------|----------------|-------------------|
| **UNPAID** 🔴 | Nota dibuat tapi belum dibayar (pre-order/kredit) | ❌ NO | ❌ NO |
| **PAID** 🟢 | Transaksi selesai dan sudah dibayar | ✅ YES | ✅ YES |
| **SENT** 📦 | Nota dikirim via WhatsApp (khusus untuk tracking) | ✅ YES | ✅ YES |
| **FINISHED** ✅ | Transaksi selesai & barang sudah diambil | ✅ YES | ✅ YES |

**State Transition:**

```
UNPAID → (Bayar) → PAID → (Kirim WA) → SENT → (Selesai) → FINISHED
  ↓                   ↓
(Cancel)          (Refund) → REFUNDED
```

### 8.2.4 Implementasi Kode - Transaction Store

**File: `app/Http/Controllers/TransactionController.php`**

**Method: `store(Request $request)`**

```php
public function store(Request $request)
{
    // 1. Parse JSON payload dari frontend
    $data = $request->json()->all();
    
    // 2. Validasi input
    $validator = Validator::make($data, [
        'user_id' => 'required|integer|exists:users,id',
        'customer_id' => 'nullable|integer|exists:customers,id',
        'customer_name' => 'nullable|string|max:255',
        'customer_phone' => 'nullable|string|max:20',
        'payment_method' => 'required|string|in:cash,card,qris',
        'cash_amount' => 'nullable|numeric|min:0',
        'discount_amount' => 'nullable|numeric|min:0',
        'status' => 'required|string|in:unpaid,paid,sent,finished',
        'cart_items' => 'required|array|min:1',
        'cart_items.*.id' => 'required|integer', // ProductUnit ID
        'cart_items.*.product_id' => 'required|integer',
        'cart_items.*.quantity' => 'required|numeric|min:1',
        'cart_items.*.price' => 'required|numeric',
        'cart_items.*.subtotal' => 'required|numeric',
        'cart_items.*.conversion' => 'required|integer',
        'cart_items.*.unit_name' => 'required|string',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    
    $validated = $validator->validated();
    $cartProducts = $validated['cart_items'];
    $userId = $validated['user_id'];
    
    // 3. Start database transaction
    DB::beginTransaction();
    
    try {
        // 4. Calculate amounts
        $subtotal = array_sum(array_column($cartProducts, 'subtotal'));
        $discount = (float) ($validated['discount_amount'] ?? 0);
        $totalAmount = $subtotal - $discount;
        
        $cashAmount = (float) ($validated['cash_amount'] ?? 0);
        $changeAmount = 0;
        if ($validated['payment_method'] == 'cash') {
            $changeAmount = $cashAmount - $totalAmount;
        }
        
        // 5. Handle customer (find or create)
        $customerId = $validated['customer_id'] ?? null;
        $incomingPhone = $validated['customer_phone'] ?? null;
        $incomingName = $validated['customer_name'] ?? null;
        
        if (!$customerId) {
            // Cari existing customer by phone ATAU name
            $existingCustomer = null;
            if ($incomingPhone) {
                $existingCustomer = Customer::where('phone', $incomingPhone)->first();
            }
            if (!$existingCustomer && $incomingName) {
                $existingCustomer = Customer::whereRaw(
                    'upper(name) = ?',
                    [strtoupper(trim($incomingName))]
                )->first();
            }
            
            if ($existingCustomer) {
                $customerId = $existingCustomer->id;
                // Update data jika ada perubahan
                if ($incomingName) {
                    $existingCustomer->name = strtoupper(trim($incomingName));
                }
                if ($incomingPhone) {
                    $existingCustomer->phone = $incomingPhone;
                }
                $existingCustomer->save();
            } elseif ($incomingName) {
                // Create new customer
                $newCustomer = Customer::create([
                    'name' => strtoupper(trim($incomingName)),
                    'phone' => $incomingPhone,
                ]);
                $customerId = $newCustomer->id;
            }
        }
        
        // 6. Create transaction
        $transaction = Transaction::create([
            'user_id' => $userId,
            'customer_id' => $customerId,
            'transaction_code' => 'TRX-' . time(),
            'total_amount' => $totalAmount,
            'discount' => $discount,
            'payment_method' => $validated['payment_method'],
            'cash_amount' => $cashAmount,
            'change_amount' => $changeAmount,
            'status' => $validated['status'],
        ]);
        
        // 7. Create transaction details
        foreach ($cartProducts as $item) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['product_id'],
                'unit_name' => $item['unit_name'],
                'quantity' => (float) $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);
        }
        
        // 8. Update stock & cashflow HANYA jika status = PAID
        if ($validated['status'] == 'paid') {
            foreach ($cartProducts as $item) {
                // Decrease stock
                $productUnit = ProductUnit::find($item['id']);
                $productUnit->decrement('stock', $item['quantity']);
                
                // Create inventory record
                Inventory::create([
                    'user_id' => $userId,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['id'],
                    'quantity' => -$item['quantity'],
                    'type' => 'keluar',
                    'description' => "Penjualan #{$transaction->transaction_code}",
                ]);
            }
            
            // Create cashflow
            $accountType = ($validated['payment_method'] == 'cash') ? 'cash' : 'bank';
            
            CashFlow::create([
                'user_id' => $userId,
                'flow_type' => 'masuk',
                'source_type' => 'transaction',
                'account' => $accountType,
                'amount' => $totalAmount,
                'description' => "Penjualan Transaksi #{$transaction->transaction_code}",
                'transaction_id' => $transaction->id,
            ]);
            
            // Update daily report
            // (Kode untuk update report...)
        }
        
        // 9. Commit database transaction
        DB::commit();
        
        // 10. Return success response
        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
        ]);
        
    } catch (\Exception $e) {
        // Rollback if error
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### 8.2.5 Perbedaan PAID vs UNPAID

**Skenario UNPAID:**
```
Contoh: Customer pre-order produk yang stoknya belum ada

Kasir buat transaksi dengan status UNPAID
  ↓
Sistem TIDAK mengurangi stok
Sistem TIDAK mencatat cashflow
  ↓
Transaksi tersimpan sebagai "hutang" atau "pre-order"
  ↓
Ketika customer bayar:
  Kasir update status → PAID
  Sistem jalankan update stock & cashflow
```

**Skenario PAID:**
```
Customer beli langsung dan bayar di tempat

Kasir buat transaksi dengan status PAID
  ↓
Sistem langsung:
  - Kurangi stok
  - Catat cashflow
  - Update laporan harian
  ↓
Transaksi selesai
```

### 8.2.6 Metode Pembayaran

**1. CASH (Tunai)**
- Customer bayar dengan uang tunai
- Kasir input jumlah uang diterima
- Sistem hitung kembalian otomatis
- Cashflow account: `cash`

**Implementasi:**
```php
if ($paymentMethod == 'cash') {
    $cashAmount = $request->cash_amount;
    $changeAmount = $cashAmount - $totalAmount;
    
    // Validasi: uang diterima harus >= total
    if ($cashAmount < $totalAmount) {
        return error('Jumlah uang tidak cukup');
    }
}
```

**2. CARD (Kartu Debit/Kredit)**
- Customer bayar dengan kartu (EDC machine)
- Tidak ada kembalian
- Cashflow account: `bank`

**Implementasi:**
```php
if ($paymentMethod == 'card') {
    $cashAmount = 0;
    $changeAmount = 0;
    // Langsung approve, tidak perlu input cash_amount
}
```

**3. QRIS (QR Code Payment)**
- Customer scan QR code (GoPay, OVO, Dana, dll)
- Tidak ada kembalian
- Cashflow account: `bank`

**Implementasi:**
```php
if ($paymentMethod == 'qris') {
    $cashAmount = 0;
    $changeAmount = 0;
    // Langsung approve setelah payment berhasil
}
```

## 8.3 Transaksi Online (WhatsApp Transaction)

### 8.3.1 Definisi

Transaksi online adalah transaksi yang dimulai dari pesanan customer via WhatsApp menggunakan sistem respons otomatis. Customer tidak perlu datang ke toko untuk melakukan pemesanan awal.

### 8.3.2 Flow Transaksi Online (End-to-End)

```
═══════════════════════════════════════
FASE 1: PEMESANAN VIA WHATSAPP
═══════════════════════════════════════

Customer kirim pesan ke WhatsApp Toko
  ↓
System kirim menu otomatis (1x per hari)
  ↓
Customer pilih "3. PESAN"
  ↓
System kirim format pesanan
  ↓
Customer kirim pesanan dengan format:
  NAMA: DARREN
  
  NAMA PRODUK: K10, KC206
  WARNA PRODUK: NKL, ATG  
  JUMLAH PRODUK: 2 LUSIN, 1 GROSS
  ↓
System parse format
  ↓
System validasi stok untuk setiap item
  ↓
Stock cukup? → NO → Send "stok tidak cukup" → END
  ↓ YES
System kirim konfirmasi pesanan
  ↓
Customer ketik "YA"
  ↓
System simpan ke database:
  1. whatsapp_orders table
     - name, phone, order_text
     - status: PENDING
  2. whatsapp_order_items table
     - product_id, color_id, product_unit_id
     - quantity, stock_pcs
  ↓
System kirim notifikasi ke Owner (WhatsApp)
  ↓
System kirim konfirmasi ke Customer:
  "✅ Pesanan diterima! Order ID: #123, Status: PENDING"

═══════════════════════════════════════
FASE 2: OWNER REVIEW PESANAN (DI DASHBOARD WEB)
═══════════════════════════════════════

Owner buka dashboard web
  ↓
Owner buka menu "Pesanan WhatsApp"
  ↓
System tampilkan list pesanan:
  - Order ID, Nama, Phone, Waktu
  - Detail items (produk, warna, quantity, satuan)
  - Status (PENDING / CONFIRMED / CANCELLED / PROCESSED)
  ↓
Owner review pesanan #123
  ↓
Owner pilih action:
  
  OPTION A: "Proses Pesanan" (Convert to Transaction)
    ↓
    System create Transaction record:
      - Link ke whatsapp_order_id
      - status: UNPAID
      - payment_method: QRIS (default)
      - Copy semua items dari whatsapp_order_items
    ↓
    Update whatsapp_orders.status → CONFIRMED
    ↓
    Redirect ke halaman Transaction Detail
    ↓
    Owner tunggu customer datang/transfer
    
  OPTION B: "Batalkan Pesanan"
    ↓
    Update whatsapp_orders.status → CANCELLED
    ↓
    (Optional) Send notif WhatsApp ke customer:
      "Maaf, pesanan Anda dibatalkan karena [alasan]"

═══════════════════════════════════════
FASE 3: CUSTOMER BAYAR & AMBIL BARANG
═══════════════════════════════════════

Scenario 3A: Customer Datang ke Toko

Customer datang ke toko dengan Order ID
  ↓
Owner/Kasir cari transaksi by Order ID atau customer name
  ↓
Owner/Kasir buka transaksi tersebut
  ↓
Current status: UNPAID
  ↓
Customer bayar (CASH / CARD / QRIS)
  ↓
Kasir klik tombol "Bayar"
  ↓
System update status: UNPAID → PAID
  ↓
System execute:
  - Decrease stock
  - Create cashflow
  - Update daily report
  - Update whatsapp_orders.status → PROCESSED
  ↓
System generate receipt
  ↓
Kasir print receipt
  ↓
Customer ambil barang
  ↓
Transaksi selesai


Scenario 3B: Customer Transfer (COD/Delivery)

Customer transfer via bank/e-wallet
  ↓
Customer kirim bukti transfer via WhatsApp
  ↓
Owner verifikasi pembayaran
  ↓
Owner update status transaksi → PAID (di dashboard web)
  ↓
System execute stock & cashflow
  ↓
Owner siapkan barang
  ↓
Owner kirim nota via WhatsApp (klik "Kirim WA")
  ↓
System update status → SENT
  ↓
Owner kirim barang (kurir/jemput sendiri)
  ↓
Customer terima barang
  ↓
Owner update status → FINISHED
  ↓
Transaksi selesai
```

### 8.3.3 WhatsappOrder → Transaction Conversion

**Kode Konversi:**

**File: `app/Http/Controllers/WhatsappOrderController.php`**

```php
public function process($id)
{
    DB::beginTransaction();
    
    try {
        // 1. Ambil WhatsApp order
        $order = WhatsappOrder::with([
                'orderItems.product',
                'orderItems.color',
                'orderItems.productUnit'
            ])
            ->findOrFail($id);
        
        // 2. Validate status
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->back()
                ->with('error', 'Pesanan sudah diproses/dibatalkan');
        }
        
        // 3. Validate items exist
        if ($order->orderItems->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Pesanan tidak memiliki detail item');
        }
        
        // 4. Find or create customer
        $customerName = strtoupper($order->name) . ' (WhatsApp)';
        $customer = Customer::firstOrCreate(
            [
                'phone' => $order->phone,
                'name' => $customerName
            ]
        );
        
        // 5. Calculate total
        $totalAmount = 0;
        $transactionDetails = [];
        
        foreach ($order->orderItems as $item) {
            // Validate product unit exists
            if (!$item->productUnit) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Item tidak memiliki satuan');
            }
            
            // Validate stock
            if ($item->productUnit->stock < $item->quantity) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "Stok tidak cukup untuk {$item->product->name}");
            }
            
            $subtotal = $item->productUnit->price * $item->quantity;
            $totalAmount += $subtotal;
            
            $transactionDetails[] = [
                'product_id' => $item->product_id,
                'unit_name' => $item->productUnit->name,
                'quantity' => $item->quantity,
                'price' => $item->productUnit->price,
                'subtotal' => $subtotal,
            ];
        }
        
        // 6. Create transaction
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'customer_id' => $customer->id,
            'transaction_code' => 'TRX-' . time(),
            'total_amount' => $totalAmount,
            'discount' => 0,
            'payment_method' => 'qris', // Default QRIS untuk online
            'cash_amount' => 0,
            'change_amount' => 0,
            'status' => 'unpaid', // UNPAID karena belum bayar
            'whatsapp_order_id' => $order->id, // Link ke WhatsApp order
        ]);
        
        // 7. Create transaction details
        foreach ($transactionDetails as $detail) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $detail['product_id'],
                'unit_name' => $detail['unit_name'],
                'quantity' => $detail['quantity'],
                'price' => $detail['price'],
                'subtotal' => $detail['subtotal'],
            ]);
        }
        
        // 8. Update WhatsApp order status → CONFIRMED
        //    (BUKAN processed, karena belum bayar)
        $order->update(['status' => 'confirmed']);
        
        DB::commit();
        
        // 9. Redirect to transaction detail
        return redirect()
            ->route('transactions.show', $transaction->id)
            ->with('success', "Transaksi dibuat dari Order #{$order->id}. Status: CONFIRMED. Klik 'Bayar' setelah customer bayar.");
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error processing WhatsApp order: ' . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Error: ' . $e->getMessage());
    }
}
```

### 8.3.4 Status Lifecycle - Online Transaction

```
┌─────────────┐
│  PENDING    │ ← WhatsApp order baru dibuat
│  (WhatsApp) │
└──────┬──────┘
       │ Owner klik "Proses Pesanan"
       ↓
┌─────────────┐
│ CONFIRMED   │ ← Order di-approve, Transaction created
│ (WhatsApp)  │   Transaction status: UNPAID
└──────┬──────┘
       │ Customer bayar, Owner klik "Bayar"
       ↓
┌─────────────┐
│ PROCESSED   │ ← Transaction status: PAID
│ (WhatsApp)  │   Stock updated, Cashflow created
└──────┬──────┘
       │ Owner kirim nota via WA
       ↓
┌─────────────┐
│   SENT      │ ← Transaction status: SENT
│ (Trans.)    │
└──────┬──────┘
       │ Barang diterima customer
       ↓
┌─────────────┐
│  FINISHED   │ ← Transaction status: FINISHED
│ (Trans.)    │
└─────────────┘
```

## 8.4 Perbandingan Transaksi Online vs Offline

| Aspek | Offline (In-Store) | Online (WhatsApp) |
|-------|-------------------|-------------------|
| **Inisiasi** | Kasir buat transaksi di POS | Customer pesan via WhatsApp |
| **Customer Registration** | Optional (bisa tanpa customer) | Otomatis (auto-create) |
| **Stock Validation** | Real-time saat add to cart | Real-time saat konfirmasi pesanan |
| **Payment** | Langsung di toko | Transfer/COD/Jemput ke toko |
| **Initial Status** | PAID / UNPAID (pilihan kasir) | PENDING (WhatsApp order) |
| **Stock Update** | Saat status = PAID | Saat Transaction status = PAID |
| **Cashflow Record** | Saat status = PAID | Saat Transaction status = PAID |
| **Receipt** | Print langsung / Send WA | Send via WhatsApp |
| **Data Model** | Langsung ke `transactions` | `whatsapp_orders` → `transactions` |
| **Approval** | Tidak perlu (langsung process) | Perlu review owner di dashboard |

## 8.5 Database Schema Integration

### 8.5.1 ERD - Transaction Flow

```
┌─────────────────┐
│  whatsapp_orders│ (Online only)
├─────────────────┤
│ id (PK)         │
│ name            │
│ phone           │
│ order_text      │
│ status          │ ← pending/confirmed/cancelled/processed
│ created_at      │
└────────┬────────┘
         │
         │ 1:N
         ↓
┌──────────────────────┐
│ whatsapp_order_items │
├──────────────────────┤
│ id (PK)              │
│ whatsapp_order_id(FK)│
│ product_id (FK)      │
│ color_id (FK)        │
│ product_unit_id (FK) │
│ quantity             │
│ stock_pcs            │
└──────────────────────┘

         │
         │ Owner klik "Proses"
         ↓
┌─────────────────┐         ┌──────────────┐
│  transactions   │ 1:1     │  customers   │
├─────────────────┤────────→├──────────────┤
│ id (PK)         │         │ id (PK)      │
│ user_id (FK)    │         │ name         │
│ customer_id (FK)│         │ phone        │
│ whatsapp_order_id│        │ address      │
│ transaction_code│         └──────────────┘
│ total_amount    │
│ discount        │
│ payment_method  │
│ cash_amount     │
│ change_amount   │
│ status          │ ← unpaid/paid/sent/finished
└────────┬────────┘
         │
         │ 1:N
         ↓
┌─────────────────────┐         ┌──────────────┐
│ transaction_details │ N:1     │  products    │
├─────────────────────┤────────→├──────────────┤
│ id (PK)             │         │ id (PK)      │
│ transaction_id (FK) │         │ name         │
│ product_id (FK)     │         │ type_id (FK) │
│ unit_name           │         │ color_id(FK) │
│ quantity            │         └──────────────┘
│ price               │
│ subtotal            │
└─────────────────────┘

Owner klik "Bayar" (status: unpaid → paid)
         │
         ↓
┌─────────────────┐         ┌──────────────────┐
│  cash_flows     │         │  product_units   │
├─────────────────┤         ├──────────────────┤
│ id (PK)         │         │ id (PK)          │
│ user_id (FK)    │         │ product_id (FK)  │
│ transaction_id  │         │ name             │
│ flow_type       │         │ stock ← UPDATED  │
│ source_type     │         │ price            │
│ account         │         │ conversion_value │
│ amount          │         └──────────────────┘
│ description     │
└─────────────────┘
         +
┌─────────────────┐
│  inventories    │
├─────────────────┤
│ id (PK)         │
│ user_id (FK)    │
│ product_id (FK) │
│ product_unit_id │
│ quantity        │ ← Negative (keluar)
│ type            │ ← 'keluar'
│ description     │
└─────────────────┘
```

### 8.5.2 Relasi Antar Tabel

**WhatsappOrder → Transaction (One-to-One)**
```php
// Model: WhatsappOrder
public function transaction()
{
    return $this->hasOne(Transaction::class, 'whatsapp_order_id');
}

// Model: Transaction
public function whatsappOrder()
{
    return $this->belongsTo(WhatsappOrder::class);
}
```

**Usage:**
```php
// Get transaction dari WhatsApp order
$order = WhatsappOrder::find(123);
$transaction = $order->transaction;

// Get WhatsApp order dari transaction
$transaction = Transaction::find(456);
$whatsappOrder = $transaction->whatsappOrder;
```

## 8.6 Perbedaan Stock Update Mechanism

### 8.6.1 Offline Transaction

**Waktu Stock Update:** Saat transaksi dibuat dengan status PAID

```php
// File: TransactionController::store()

if ($validated['status'] == 'paid') {
    foreach ($cartProducts as $item) {
        // 1. Update stock di product_units
        $productUnit = ProductUnit::find($item['id']);
        $productUnit->decrement('stock', $item['quantity']);
        
        // 2. Catat di inventories
        Inventory::create([
            'product_unit_id' => $item['id'],
            'quantity' => -$item['quantity'], // Negative = keluar
            'type' => 'keluar',
            'description' => "Penjualan #{$transactionCode}",
        ]);
    }
}
```

**Timeline:**
```
Transaksi dibuat (status: PAID)
  ↓ (< 1 detik)
Stock updated langsung
```

### 8.6.2 Online Transaction

**Waktu Stock Update:** Saat transaction status diupdate ke PAID (bukan saat order dibuat)

```php
// File: TransactionController::updateStatus()

public function updateStatus($id, Request $request)
{
    $transaction = Transaction::findOrFail($id);
    $oldStatus = $transaction->status;
    $newStatus = $request->status;
    
    // Update status
    $transaction->update(['status' => $newStatus]);
    
    // Jika status berubah dari UNPAID → PAID
    if ($oldStatus == 'unpaid' && $newStatus == 'paid') {
        // Execute stock update & cashflow
        foreach ($transaction->details as $detail) {
            // Find product unit
            $productUnit = ProductUnit::where('product_id', $detail->product_id)
                ->where('name', $detail->unit_name)
                ->first();
            
            // Decrease stock
            $productUnit->decrement('stock', $detail->quantity);
            
            // Create inventory record
            Inventory::create([
                'product_unit_id' => $productUnit->id,
                'quantity' => -$detail->quantity,
                'type' => 'keluar',
                'description' => "Penjualan Online #{$transaction->transaction_code}",
            ]);
        }
        
        // Create cashflow
        CashFlow::create([
            'transaction_id' => $transaction->id,
            'flow_type' => 'masuk',
            'account' => ($transaction->payment_method == 'cash') ? 'cash' : 'bank',
            'amount' => $transaction->total_amount,
            'description' => "Penjualan Online #{$transaction->transaction_code}",
        ]);
        
        // Update WhatsApp order status
        if ($transaction->whatsappOrder) {
            $transaction->whatsappOrder->update(['status' => 'processed']);
        }
    }
    
    return redirect()->back()->with('success', 'Status updated');
}
```

**Timeline:**
```
WhatsApp Order dibuat (status: PENDING)
  ↓
Owner proses (status: CONFIRMED)
  ↓
Transaction dibuat (status: UNPAID)
  ↓ (Menunggu customer bayar - bisa 1 jam, 1 hari, dst)
Customer bayar
  ↓
Owner update status → PAID
  ↓ (< 1 detik)
Stock updated
```

## 8.7 Notification System

### 8.7.1 Notifikasi ke Owner

**Trigger:** Saat WhatsApp order baru dibuat (customer konfirmasi pesanan)

**Implementasi:**

```php
// File: ChatbotService::handleOrderStep()

// Kirim ke owner
$ownerPhone = config('services.chatbot.owner_phone');

$ownerMessage = "📦 *PESANAN BARU*\n\n";
$ownerMessage .= "👤 Nama: *" . strtoupper($data['name']) . "*\n";
$ownerMessage .= "📱 Nomor: *{$data['phone']}*\n";
$ownerMessage .= "🕒 Waktu: " . now()->format('d/m/Y H:i') . "\n\n";
$ownerMessage .= "━━━━━━━━━━━━━━━━\n";
$ownerMessage .= "📦 *DETAIL PESANAN:*\n\n";

foreach ($items as $item) {
    $ownerMessage .= "• *{$item['product_name']}* ({$item['color_name']})\n";
    $ownerMessage .= "  Jumlah: {$item['quantity']} {$item['unit_name']}\n\n";
}

$this->whatsappService->sendMessage($ownerPhone, $ownerMessage);
```

**Contoh Pesan Owner:**
```
📦 PESANAN BARU

👤 Nama: DARREN
📱 Nomor: 628123456789
🕒 Waktu: 16/12/2025 14:30

━━━━━━━━━━━━━━━━
📦 DETAIL PESANAN:

• K10 (NKL)
  Jumlah: 2 LUSIN

• KC206 (ATG)
  Jumlah: 1 GROSS

━━━━━━━━━━━━━━━━
```

### 8.7.2 Notifikasi ke Customer

**Trigger 1: Saat pesanan diterima**

```
✅ Pesanan Anda telah diterima!

📋 Order ID: #123
👤 Nama: DARREN
📦 Status: PENDING 🟡

Terima kasih atas pesanan Anda.
Kami akan segera memprosesnya. 😊

📍 Alamat Toko:
Jl. Imam Bonjol no.336, Denpasar, Bali
```

**Trigger 2: Saat nota dikirim (setelah bayar)**

```php
// File: TransactionController::sendWhatsapp()

public function sendWhatsapp($id)
{
    $transaction = Transaction::with([
            'details.product',
            'customer',
            'user'
        ])
        ->findOrFail($id);
    
    // Generate message
    $message = "*NOTA TOKO TRIJAYA*\n\n";
    $message .= "━━━━━━━━━━━━━━━━\n";
    $message .= "No: {$transaction->transaction_code}\n";
    $message .= "Tanggal: " . $transaction->created_at->format('d/m/Y H:i') . "\n";
    $message .= "Kasir: {$transaction->user->name}\n\n";
    
    if ($transaction->customer) {
        $message .= "Customer: {$transaction->customer->name}\n\n";
    }
    
    $message .= "━━━━━━━━━━━━━━━━\n";
    $message .= "*DETAIL PEMBELIAN*\n\n";
    
    foreach ($transaction->details as $detail) {
        $message .= "{$detail->product->name}\n";
        $message .= "{$detail->quantity} {$detail->unit_name} × " .
                    number_format($detail->price, 0, ',', '.') . "\n";
        $message .= "Subtotal: Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n\n";
    }
    
    $message .= "━━━━━━━━━━━━━━━━\n";
    $message .= "Total: Rp " . number_format($transaction->total_amount, 0, ',', '.') . "\n";
    
    if ($transaction->discount > 0) {
        $message .= "Diskon: Rp " . number_format($transaction->discount, 0, ',', '.') . "\n";
        $message .= "Grand Total: Rp " . number_format($transaction->total_amount - $transaction->discount, 0, ',', '.') . "\n";
    }
    
    $message .= "\n━━━━━━━━━━━━━━━━\n";
    $message .= "Terima kasih atas kunjungan Anda! 😊\n";
    $message .= "Jl. Imam Bonjol no.336, Denpasar, Bali";
    
    // Send
    $result = $this->whatsappService->sendMessage(
        $transaction->customer->phone,
        $message
    );
    
    if ($result['status']) {
        // Update status → SENT
        $transaction->update(['status' => 'sent']);
        return redirect()->back()->with('success', 'Nota berhasil dikirim via WhatsApp!');
    } else {
        return redirect()->back()->with('error', 'Gagal kirim: ' . $result['message']);
    }
}
```

## 8.8 Payment Method Handling

### 8.8.1 CASH Payment

**Karakteristik:**
- Customer bayar dengan uang tunai
- Perlu input jumlah uang diterima
- System hitung kembalian otomatis
- Uang masuk ke cashflow account `cash`

**UI Flow:**
```
Kasir pilih: Payment Method = CASH
  ↓
Form muncul: Input "Jumlah Uang Diterima"
  ↓
Kasir input: Rp 100.000 (Grand Total: Rp 85.000)
  ↓
System calculate kembalian: 100.000 - 85.000 = Rp 15.000
  ↓
Display: "Kembalian: Rp 15.000"
  ↓
Kasir klik "Simpan"
```

**Data Tersimpan:**
```php
Transaction:
  payment_method: 'cash'
  cash_amount: 100000
  change_amount: 15000
  
CashFlow:
  account: 'cash'
  flow_type: 'masuk'
  amount: 85000 (Grand Total, bukan cash_amount)
```

### 8.8.2 CARD Payment

**Karakteristik:**
- Customer bayar dengan kartu debit/kredit
- Menggunakan EDC machine di toko
- Tidak ada kembalian
- Uang masuk ke cashflow account `bank`

**UI Flow:**
```
Kasir pilih: Payment Method = CARD
  ↓
Form tidak muncul (no cash_amount needed)
  ↓
Kasir gesek kartu customer di EDC
  ↓
EDC approve → Customer tanda tangan
  ↓
Kasir klik "Simpan"
```

**Data Tersimpan:**
```php
Transaction:
  payment_method: 'card'
  cash_amount: 0
  change_amount: 0
  
CashFlow:
  account: 'bank'
  flow_type: 'masuk'
  amount: 85000
```

### 8.8.3 QRIS Payment

**Karakteristik:**
- Customer scan QR code dengan app (GoPay, OVO, Dana, ShopeePay, dll)
- Instant payment verification
- Tidak ada kembalian
- Uang masuk ke cashflow account `bank`

**UI Flow:**
```
Kasir pilih: Payment Method = QRIS
  ↓
System/Kasir tampilkan QR code (static/dynamic)
  ↓
Customer scan dengan app
  ↓
Customer konfirmasi payment di app
  ↓
Kasir tunggu notifikasi payment berhasil
  ↓
Kasir klik "Simpan"
```

**Data Tersimpan:**
```php
Transaction:
  payment_method: 'qris'
  cash_amount: 0
  change_amount: 0
  
CashFlow:
  account: 'bank'
  flow_type: 'masuk'
  amount: 85000
```

## 8.9 Sequence Diagram

### 8.9.1 Offline Transaction Sequence

```
Customer    Kasir       System      Database      Printer
   │          │            │            │            │
   │  Pilih   │            │            │            │
   │─produk──→│            │            │            │
   │          │   Scan/    │            │            │
   │          │   Search   │            │            │
   │          │───produk──→│            │            │
   │          │            │  Query     │            │
   │          │            │──products─→│            │
   │          │            │←─results───│            │
   │          │←─tampilkan─│            │            │
   │          │   produk   │            │            │
   │          │            │            │            │
   │          │  Input     │            │            │
   │          │─quantity──→│            │            │
   │          │            │  Validate  │            │
   │          │            │───stock───→│            │
   │          │            │←──OK/Error─│            │
   │          │←─add cart──│            │            │
   │          │            │            │            │
   │  Bayar   │            │            │            │
   │─(cash/──→│  Pilih     │            │            │
   │  card/   │─payment───→│            │            │
   │  qris)   │  method    │            │            │
   │          │            │            │            │
   │          │  Klik      │            │            │
   │          │──Simpan───→│            │            │
   │          │            │ DB Trans   │            │
   │          │            │───Begin───→│            │
   │          │            │            │            │
   │          │            │  Create    │            │
   │          │            │─Transaction│            │
   │          │            │←───OK──────│            │
   │          │            │            │            │
   │          │            │  Create    │            │
   │          │            │──Details──→│            │
   │          │            │←───OK──────│            │
   │          │            │            │            │
   │          │            │  Update    │            │
   │          │            │───Stock───→│            │
   │          │            │←───OK──────│            │
   │          │            │            │            │
   │          │            │  Create    │            │
   │          │            │──CashFlow─→│            │
   │          │            │←───OK──────│            │
   │          │            │            │            │
   │          │            │ DB Commit  │            │
   │          │            │───────────→│            │
   │          │            │←──Success──│            │
   │          │            │            │            │
   │          │←─Redirect──│            │            │
   │          │  to receipt│            │            │
   │          │            │            │            │
   │          │  Klik      │            │            │
   │          │───Print───→│  Generate  │   Print    │
   │          │            │───Receipt─→│──Receipt──→│
   │←────────────────────────Nota──────────────────────│
```

### 8.9.2 Online Transaction Sequence

```
Customer   WhatsApp   System     Database   Owner(WA)  Owner(Web)
   │          │         │           │           │          │
   │  Pesan   │         │           │           │          │
   │─"PESAN"─→│         │           │           │          │
   │          │ Webhook  │           │           │          │
   │          │─────────→│           │           │          │
   │          │          │  Process  │           │          │
   │          │          │─ message  │           │          │
   │          │          │           │           │          │
   │          │←─Format──│           │           │          │
   │←─pesanan─│          │           │           │          │
   │          │          │           │           │          │
   │  Kirim   │          │           │           │          │
   │─format──→│          │           │           │          │
   │  pesanan │ Webhook  │           │           │          │
   │          │─────────→│           │           │          │
   │          │          │  Parse &  │           │          │
   │          │          │  Validate │           │          │
   │          │          │─────────→│           │          │
   │          │          │←─ Stock  ─│           │          │
   │          │          │    OK     │           │          │
   │          │          │           │           │          │
   │          │←─Konf───│            │           │          │
   │←─irmasi──│ pesanan  │           │           │          │
   │          │          │           │           │          │
   │  "YA"    │          │           │           │          │
   │─────────→│ Webhook  │           │           │          │
   │          │─────────→│           │           │          │
   │          │          │ DB Trans  │           │          │
   │          │          │───Begin──→│           │          │
   │          │          │           │           │          │
   │          │          │  Create   │           │          │
   │          │          │─WhatsApp─→│           │          │
   │          │          │   Order   │           │          │
   │          │          │←───OK─────│           │          │
   │          │          │           │           │          │
   │          │          │  Create   │           │          │
   │          │          │─  Items  ─│           │          │
   │          │          │←───OK─────│           │          │
   │          │          │           │           │          │
   │          │          │ DB Commit │           │          │
   │          │          │──────────→│           │          │
   │          │          │           │           │          │
   │          │          │ Send      │           │          │
   │          │          │─ notif ───┼──────────→│          │
   │          │          │  to owner │           │          │
   │          │          │           │           │          │
   │          │←─Success─│           │           │          │
   │←─message─│          │           │           │          │
   │          │          │           │           │          │
   │          │          │           │           │  Login   │
   │          │          │           │           │  & buka  │
   │          │          │           │           │─dashboard│
   │          │          │           │           │          │
   │          │          │           │←──────────┼─  View  ─│
   │          │          │           │   Query   │  orders  │
   │          │          │           │  orders   │          │
   │          │          │           │──────────→│          │
   │          │          │           │           │          │
   │          │          │           │           │  Review  │
   │          │          │           │           │  order   │
   │          │          │           │           │  #123    │
   │          │          │           │           │          │
   │          │          │           │           │  Klik    │
   │          │          │           │←──────────┼─"Proses"─│
   │          │          │           │  Create   │          │
   │          │          │           │ Transact. │          │
   │          │          │           │──────────→│          │
   │          │          │           │           │          │
   │          │          │           │ Update    │          │
   │          │          │           │ WA Order  │          │
   │          │          │           │ →CONFIRM  │          │
   │          │          │           │──────────→│          │
   │          │          │           │           │          │
   │          │          │  Status: UNPAID       │          │
   │          │          │  (Tunggu bayar)       │          │
   │          │          │                       │          │
   │  Transfer│          │           │           │          │
   │─ bukti ─→│          │           │           │          │
   │  bayar   │──────────┼───────────┼──────────→│          │
   │          │  (Manual)│           │           │          │
   │          │          │           │           │          │
   │          │          │           │           │  Verify  │
   │          │          │           │           │  payment │
   │          │          │           │           │          │
   │          │          │           │           │  Klik    │
   │          │          │           │←──────────┼─"Bayar"──│
   │          │          │           │  Update   │          │
   │          │          │           │  status   │          │
   │          │          │           │───PAID───→│          │
   │          │          │           │           │          │
   │          │          │           │  Update   │          │
   │          │          │           │───Stock──→│          │
   │          │          │           │           │          │
   │          │          │           │  Create   │          │
   │          │          │           │─CashFlow─→│          │
   │          │          │           │           │          │
   │          │          │           │  Update   │          │
   │          │          │           │ WA Order  │          │
   │          │          │           │→PROCESSED │          │
   │          │          │           │──────────→│          │
   │          │          │           │           │          │
   │          │          │           │           │  Klik    │
   │          │          │           │←──────────┼─"Kirim  ─│
   │          │          │           │ Generate  │   WA"    │
   │          │          │           │  receipt  │          │
   │          │←─────────┼───────────┼─  message │          │
   │          │  Send WA │           │           │          │
   │←─ Nota ──│          │           │           │          │
   │  digital │          │           │           │          │
```

## 8.10 Stock Update Mechanism (Detail)

### 8.10.1 Product Unit Concept

Setiap produk memiliki multiple unit dengan stok dan harga berbeda:

**Contoh: Produk K10 (Keling 10mm)**

```
Product: K10
├─ Unit: LUSIN
│  ├─ stock: 10
│  ├─ price: 25.000
│  └─ conversion: 12 (1 LUSIN = 12 pcs)
├─ Unit: GROSS
│  ├─ stock: 3
│  ├─ price: 300.000
│  └─ conversion: 144 (1 GROSS = 144 pcs)
└─ Unit: SATUAN
   ├─ stock: 50
   ├─ price: 2.500
   └─ conversion: 1 (1 SATUAN = 1 pcs)

Total Stock = (10 × 12) + (3 × 144) + (50 × 1) = 120 + 432 + 50 = 602 pcs
```

### 8.10.2 Stock Deduction Logic

**Skenario: Customer beli 2 LUSIN K10**

```php
// 1. Find product unit
$productUnit = ProductUnit::where('product_id', 15)
    ->where('name', 'LUSIN')
    ->first();

// Current stock: 10 LUSIN

// 2. Validate stock
if ($productUnit->stock < 2) {
    throw new Exception('Stok tidak cukup');
}

// 3. Decrease stock
$productUnit->decrement('stock', 2);

// New stock: 8 LUSIN

// 4. Create inventory record (audit trail)
Inventory::create([
    'user_id' => $userId,
    'product_id' => 15,
    'product_unit_id' => $productUnit->id,
    'quantity' => -2,  // Negative = keluar
    'type' => 'keluar',
    'description' => 'Penjualan #TRX-123456789',
    'created_at' => now(),
]);
```

**Result:**
```
BEFORE:
product_units.stock = 10 LUSIN

AFTER:
product_units.stock = 8 LUSIN

inventories:
  + 1 record: quantity = -2, type = keluar
```

### 8.10.3 Stock Update Timing

**Offline Transaction:**
```
Transaksi dibuat (status: PAID)
    ↓ (immediate, < 1 second)
Stock updated
```

**Online Transaction:**
```
Order dibuat (status: PENDING)
    ↓ (NO stock update)
Owner approve (status: CONFIRMED)
    ↓ (NO stock update)
Transaction created (status: UNPAID)
    ↓ (NO stock update)
Customer bayar
    ↓
Owner update status (UNPAID → PAID)
    ↓ (immediate, < 1 second)
Stock updated
```

**Alasan perbedaan timing:**
- **Offline:** Customer langsung bayar → Stock langsung keluar
- **Online:** Customer bisa batal/tidak jadi → Stock tidak dikurangi dulu sampai benar-benar bayar

## 8.11 CashFlow Recording

### 8.11.1 CashFlow Structure

```php
CashFlow:
  - id: Primary key
  - user_id: Kasir yang handle transaksi
  - transaction_id: Link ke transaction
  - flow_type: 'masuk' | 'keluar'
  - source_type: 'transaction' | 'purchase' | 'refund' | 'other'
  - account: 'cash' | 'bank'
  - amount: Jumlah uang
  - description: Keterangan
  - created_at: Waktu pencatatan
```

### 8.11.2 CashFlow for Transaction

**Saat Transaction Status = PAID:**

```php
// Determine account type
if ($transaction->payment_method == 'cash') {
    $accountType = 'cash';
} elseif (in_array($transaction->payment_method, ['card', 'qris'])) {
    $accountType = 'bank';
}

// Create cashflow
CashFlow::create([
    'user_id' => $transaction->user_id,
    'transaction_id' => $transaction->id,
    'flow_type' => 'masuk', // Uang masuk
    'source_type' => 'transaction', // Dari penjualan
    'account' => $accountType,
    'amount' => $transaction->total_amount, // Grand total (after discount)
    'description' => "Penjualan Transaksi #{$transaction->transaction_code}",
    'created_at' => now(),
]);
```

**Example Data:**
```
Transaction TRX-1234567890:
  - total_amount: 85.000
  - discount: 5.000
  - payment_method: cash
  - status: paid

CashFlow Created:
  - flow_type: masuk
  - source_type: transaction
  - account: cash
  - amount: 85.000 (bukan 90.000, karena sudah dikurangi discount)
  - description: "Penjualan Transaksi #TRX-1234567890"
```

### 8.11.3 Cash Balance Calculation

**Query untuk Saldo Kas:**

```php
// Saldo kas tunai (cash)
$cashBalance = CashFlow::where('account', 'cash')
    ->sum(DB::raw("CASE WHEN flow_type = 'masuk' THEN amount ELSE -amount END"));

// Saldo bank (card + qris)
$bankBalance = CashFlow::where('account', 'bank')
    ->sum(DB::raw("CASE WHEN flow_type = 'masuk' THEN amount ELSE -amount END"));

// Total saldo
$totalBalance = $cashBalance + $bankBalance;
```

**Contoh Perhitungan:**

```
CashFlows (account: cash):
  1. flow_type: masuk, amount: 100.000 (Penjualan)
  2. flow_type: keluar, amount: 50.000 (Pembelian)
  3. flow_type: masuk, amount: 75.000 (Penjualan)

Cash Balance = 100.000 - 50.000 + 75.000 = Rp 125.000
```

## 8.12 Report Generation

### 8.12.1 Daily Report Structure

**Tabel: `reports`**

```php
Report (Laporan Harian per User):
  - id
  - user_id: Kasir/user yang handle transaksi
  - report_type: 'laba_rugi' (Profit & Loss)
  - total_sales: Total penjualan hari ini
  - total_cost: Total modal barang terjual
  - profit: Laba (sales - cost)
  - cash_amount: Total penjualan cash
  - card_amount: Total penjualan card
  - qris_amount: Total penjualan qris
  - transaction_count: Jumlah transaksi
  - created_at: Tanggal laporan (date only, no time)
```

### 8.12.2 Report Update Logic

**Saat Transaction Status = PAID:**

```php
$today = Carbon::today(); // Date only (2025-12-16 00:00:00)

// Find or create today's report for this user
$report = Report::where('user_id', $userId)
    ->where('report_type', 'laba_rugi')
    ->whereDate('created_at', $today)
    ->first();

if (!$report) {
    $report = Report::create([
        'user_id' => $userId,
        'report_type' => 'laba_rugi',
        'total_sales' => 0,
        'total_cost' => 0,
        'profit' => 0,
        'cash_amount' => 0,
        'card_amount' => 0,
        'qris_amount' => 0,
        'transaction_count' => 0,
        'created_at' => $today,
    ]);
}

// Update report
$report->increment('total_sales', $transaction->total_amount);
$report->increment('total_cost', $totalCostOfGoods);
$report->increment('profit', $transaction->total_amount - $totalCostOfGoods);
$report->increment('transaction_count', 1);

// Update payment method specific amount
if ($transaction->payment_method == 'cash') {
    $report->increment('cash_amount', $transaction->total_amount);
} elseif ($transaction->payment_method == 'card') {
    $report->increment('card_amount', $transaction->total_amount);
} elseif ($transaction->payment_method == 'qris') {
    $report->increment('qris_amount', $transaction->total_amount);
}

$report->save();
```

## 8.13 Integration Between Online & Offline

### 8.13.1 Unified Transaction Table

Baik transaksi offline maupun online, akhirnya tersimpan di tabel `transactions` yang sama:

```
Offline Transaction:
  whatsapp_order_id: NULL
  customer_id: Bisa NULL atau filled
  status: PAID (langsung)

Online Transaction:
  whatsapp_order_id: 123 (Link ke WhatsApp order)
  customer_id: Auto-created from WhatsApp
  status: UNPAID → PAID (bertahap)
```

### 8.13.2 Reporting Integration

**Laporan harian menggabungkan kedua jenis transaksi:**

```php
// Total penjualan hari ini (offline + online)
$dailySales = Transaction::whereDate('created_at', today())
    ->where('status', 'paid')
    ->sum('total_amount');

// Breakdown by source
$offlineSales = Transaction::whereDate('created_at', today())
    ->where('status', 'paid')
    ->whereNull('whatsapp_order_id') // Offline
    ->sum('total_amount');

$onlineSales = Transaction::whereDate('created_at', today())
    ->where('status', 'paid')
    ->whereNotNull('whatsapp_order_id') // Online
    ->sum('total_amount');
```

## 8.14 Tabel Perbandingan Komprehensif

| Aspek | Transaksi Offline | Transaksi Online |
|-------|-------------------|------------------|
| **Channel** | POS System (Web App) | WhatsApp |
| **Inisiasi** | Kasir | Customer |
| **Customer Presence** | Harus datang ke toko | Tidak perlu datang (untuk pesan) |
| **Product Selection** | Kasir cari & pilih | Customer ketik nama produk |
| **Stock Check** | Real-time di cart | Real-time saat konfirmasi |
| **Payment Location** | Di toko (langsung) | Transfer/COD/Jemput ke toko |
| **Payment Timing** | Saat transaksi dibuat | Setelah order di-approve owner |
| **Stock Update Timing** | Saat status = PAID | Saat Transaction status = PAID |
| **Initial Status** | PAID / UNPAID (pilihan) | PENDING (WhatsApp) → UNPAID (Transaction) |
| **Customer Data** | Optional | Otomatis dari format pesanan |
| **Approval Process** | Tidak perlu | Perlu review owner |
| **Notification** | Tidak ada | Notif ke owner & customer |
| **Receipt Delivery** | Print / WhatsApp (optional) | WhatsApp (primary) |
| **Data Entry** | Manual (kasir input) | Semi-automated (parse dari chat) |
| **Cancel Mechanism** | Void transaction | Update status via dashboard atau WA |
| **User Role** | Kasir (authenticated user) | System (auto-process) + Owner (review) |

## 8.15 Keuntungan Masing-Masing Jenis

### 8.15.1 Keuntungan Transaksi Offline

1. ✅ **Immediate Verification**
   - Customer langsung lihat barang
   - Tidak ada risk salah kirim

2. ✅ **Faster Process**
   - Langsung bayar dan bawa pulang
   - Tidak perlu tunggu approve owner

3. ✅ **Direct Communication**
   - Customer bisa tanya langsung ke kasir
   - Bisa nego harga (discount)

4. ✅ **Flexible Payment**
   - Cash, Card, QRIS langsung available
   - Kembalian langsung diterima

### 8.15.2 Keuntungan Transaksi Online

1. ✅ **Customer Convenience**
   - Customer tidak perlu datang untuk pesan
   - Bisa pesan kapan saja (24/7)

2. ✅ **Automated Stock Check**
   - Customer langsung tahu stok tersedia atau tidak
   - Tidak perlu tunggu respon manual owner

3. ✅ **Order History**
   - Customer bisa lihat riwayat pesanan
   - Owner bisa track pesanan via dashboard

4. ✅ **Reduced Owner Workload**
   - Sistem auto-handle pertanyaan umum
   - Owner hanya perlu approve/reject

5. ✅ **Digital Trail**
   - Semua percakapan & pesanan tercatat
   - Mudah untuk audit

## 8.16 Error Handling & Recovery

### 8.16.1 Transaction Rollback

**Scenario:** Error saat create transaction

```php
DB::beginTransaction();

try {
    // Create transaction
    $transaction = Transaction::create([...]);
    
    // Create details
    foreach ($items as $item) {
        TransactionDetail::create([...]);
    }
    
    // Update stock
    foreach ($items as $item) {
        $productUnit->decrement('stock', $quantity);
        // ❌ ERROR here: Stock jadi negative
        throw new Exception('Stock cannot be negative');
    }
    
    DB::commit();
    
} catch (\Exception $e) {
    // ✅ Rollback: Semua perubahan dibatalkan
    DB::rollBack();
    
    // Transaction NOT created
    // Details NOT created
    // Stock NOT updated
    
    return error($e->getMessage());
}
```

**Keuntungan Database Transaction:**
- ✅ All-or-nothing: Semua berhasil atau semua dibatalkan
- ✅ Data consistency terjaga
- ✅ Tidak ada partial data (transaksi tanpa detail, dll)

### 8.16.2 Stock Insufficient Handling

**Offline:**
```
Kasir add item ke cart
  ↓
System validate stock
  ↓
Stock < quantity?
  ↓ YES
Show error: "Stok tidak cukup"
  ↓
Item NOT added to cart
  ↓
Kasir pilih quantity lebih kecil atau produk lain
```

**Online:**
```
Customer kirim pesanan
  ↓
System validate each item stock
  ↓
Stock < quantity?
  ↓ YES
Send message:
"⚠️ Stok tidak mencukupi!
Produk: K10 (NKL)
Diminta: 10 LUSIN
Tersedia: 5 LUSIN

Silakan kurangi jumlah pesanan."
  ↓
Customer kirim pesanan baru dengan quantity lebih kecil
```

## 8.17 Receipt/Nota Generation

### 8.17.1 Receipt Content

**Nota Digital (via WhatsApp):**

```
*NOTA TOKO TRIJAYA*

━━━━━━━━━━━━━━━━
No: TRX-1234567890
Tanggal: 16/12/2025 14:30
Kasir: ADMIN

Customer: DARREN (WhatsApp)

━━━━━━━━━━━━━━━━
*DETAIL PEMBELIAN*

K10 (NKL)
2 LUSIN × 25.000
Subtotal: Rp 50.000

KC206 (ATG)
1 GROSS × 300.000
Subtotal: Rp 300.000

━━━━━━━━━━━━━━━━
Total: Rp 350.000
Diskon: Rp 0
Grand Total: Rp 350.000

Metode: QRIS

━━━━━━━━━━━━━━━━
Terima kasih atas kunjungan Anda! 😊
Jl. Imam Bonjol no.336, Denpasar, Bali
```

### 8.17.2 Send Receipt via WhatsApp

**Kode:**

```php
public function sendWhatsapp($id)
{
    $transaction = Transaction::with(['details.product', 'customer'])
        ->findOrFail($id);
    
    // Validate: Customer must have phone
    if (!$transaction->customer || !$transaction->customer->phone) {
        return redirect()->back()
            ->with('error', 'Customer tidak memiliki nomor WhatsApp');
    }
    
    // Generate message (lihat contoh di atas)
    $message = $this->generateReceiptMessage($transaction);
    
    // Send via WhatsappService
    $result = $this->whatsappService->sendMessage(
        $transaction->customer->phone,
        $message
    );
    
    if ($result['status']) {
        // Update status → SENT
        $transaction->update(['status' => 'sent']);
        
        return redirect()->back()
            ->with('success', 'Nota berhasil dikirim via WhatsApp!');
    } else {
        return redirect()->back()
            ->with('error', 'Gagal kirim: ' . $result['message']);
    }
}
```

---

**Catatan untuk Skripsi:**
- Tambahkan sequence diagram untuk kedua jenis transaksi
- Tambahkan screenshot halaman POS (create transaction)
- Tambahkan screenshot dashboard WhatsApp orders
- Tambahkan tabel status lifecycle dengan penjelasan
- Sertakan contoh nota digital yang dikirim via WhatsApp
