<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductColor;
use App\Models\CashFlow;
use App\Models\Inventory;
use App\Models\ProductUnit; // Pastikan ini di-use
use App\Models\User;
use App\Models\Refund;
use App\Models\RefundDetail;
use App\Models\Report;
use App\Services\WhatsappService;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
    public function index(Request $request)
    {
        $transactions = Transaction::with(['details.product', 'user'])
            ->when($request->payment_method, function($query, $method) {
                return $query->where('payment_method', $method);
            })
            ->when($request->cashier, function($query, $cashierId) {
                return $query->where('user_id', $cashierId);
            })
            ->when($request->date_range, function($query) use ($request) {
                $dates = explode(' - ', $request->date_range);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $cashiers = User::orderBy('name', 'asc')->get();
        $today = now()->toDateString();

        $kasMasuk = CashFlow::where(['flow_type' => 'masuk', 'account'   => 'cash'])
            ->whereDate('created_at', $today)->sum('amount');
        $kasKeluar = CashFlow::where(['flow_type' => 'keluar', 'account'   => 'cash'])
            ->whereDate('created_at', $today)->sum('amount');
        $kasSaatIni = $kasMasuk - $kasKeluar;

        return view('transactions.index', compact('transactions', 'cashiers','kasSaatIni'));
    }


    public function create(Request $request)
    {
        $request->validate([
            'type_id' => 'nullable|integer|exists:product_types,id',
            'color_id'=> 'nullable|integer|exists:product_colors,id',
            'unit_id' => 'nullable|integer|exists:product_units,id',
        ]);

        $query = ProductUnit::with(['product.type', 'product.color']);

        // Hanya tampilkan produk jika ada search atau filter
        $hasFilter = $request->filled('search') || ($request->has('type_id') && $request->type_id) || ($request->filled('color_id'));

        if ($hasFilter) {
            if ($request->has('type_id') && $request->type_id) {
                $query->whereHas('product', function ($q) use ($request){
                    $q->where('type_id', $request->type_id);
                });
            }
            if ($request->filled('search')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            }
            if ($request->filled('color_id')) {
                $query->whereHas('product', function ($q) use ($request){
                    $q->where('color_id', $request->color_id);
                });
            }

            $productUnits = $query->get()->sortBy(function($unit) {
                return $unit->product->name . ' - ' . $unit->name;
            });
        } else {
            // Jika tidak ada filter, return empty collection
            $productUnits = collect();
        }

        $productTypes = ProductType::orderBy('name', 'asc')->get();
        $productColors = ProductColor::orderBy('name', 'asc')->get();
        $customers = \App\Models\Customer::orderBy('name', 'asc')->get();

        return view('transactions.create', compact('productUnits', 'productTypes', 'productColors', 'customers', 'hasFilter'));
    }



    public function store(Request $request)
    {
        // 1. Request sekarang adalah JSON, bukan Form Data
        $data = $request->json()->all();

        // 2. Validasi data JSON
        $validator = Validator::make($data, [
            'user_id'         => 'required|integer|exists:users,id', // Diambil dari <meta>
            'customer_id'     => 'nullable|integer|exists:customers,id',
            'customer_name'   => 'nullable|string|max:255', // Untuk customer baru
            'customer_phone'  => 'nullable|string|max:20', // Nomor telepon customer
            'payment_method'  => 'required|string|in:cash,card,qris',
            'cash_amount'     => $request->payment_method === 'cash' ? 'required|numeric|min:0' : 'nullable',
            'discount_amount' => 'nullable|numeric|min:0',
            'status'          => 'required|string|in:unpaid,paid',
            'cart_items'      => 'required|array|min:1', // Menggantikan 'products'
            'cart_items.*.id' => 'required|integer', // Ini adalah ProductUnit ID
            'cart_items.*.product_id' => 'required|integer',
            'cart_items.*.quantity' => 'required|numeric|min:1',
            'cart_items.*.price' => 'required|numeric',
            'cart_items.*.subtotal' => 'required|numeric',
            'cart_items.*.conversion' => 'required|integer',
            'cart_items.*.unit_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Kirim respons JSON, bukan redirect
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $cartProducts = $validated['cart_items'];

        // 3. Ambil user_id DARI PAYLOAD, bukan dari auth()
        $userId = $validated['user_id'];

        DB::beginTransaction();

        try {
            $subtotal = 0;
            foreach ($cartProducts as $item) {
                $subtotal += $item['subtotal'];
            }
            $discount = (float) ($validated['discount_amount'] ?? 0);
            $totalAmount = $subtotal - $discount;

            $cashAmount = (float) ($validated['cash_amount'] ?? 0);
            $changeAmount = 0;
            if ($validated['payment_method'] == 'cash') {

                $changeAmount = $cashAmount - $totalAmount;
            }

            // Handle customer (reuse by phone to avoid duplicates)
            $customerId = $validated['customer_id'] ?? null;
            $incomingPhone = $validated['customer_phone'] ?? null;
            $incomingName  = $validated['customer_name'] ?? null;

            if (!$customerId) {
                // Coba cari customer existing berdasarkan phone (prioritas) atau nama
                $existingCustomer = null;
                if ($incomingPhone) {
                    $existingCustomer = \App\Models\Customer::where('phone', $incomingPhone)->first();
                }
                if (!$existingCustomer && $incomingName) {
                    $existingCustomer = \App\Models\Customer::whereRaw('upper(name) = ?', [strtoupper(trim($incomingName))])->first();
                }

                if ($existingCustomer) {
                    $customerId = $existingCustomer->id;
                    // Update nama/phone jika ada data baru
                    if ($incomingName) {
                        $existingCustomer->name = strtoupper(trim($incomingName));
                    }
                    if ($incomingPhone) {
                        $existingCustomer->phone = $incomingPhone;
                    }
                    $existingCustomer->save();
                } elseif ($incomingName) {
                    // Buat customer baru hanya jika benar-benar belum ada
                    $newCustomer = \App\Models\Customer::create([
                        'name' => strtoupper(trim($incomingName)),
                        'phone' => $incomingPhone,
                    ]);
                    $customerId = $newCustomer->id;
                }
            } elseif ($customerId && $incomingPhone) {
                // Update phone customer yang sudah ada jika diisi
                $customer = \App\Models\Customer::find($customerId);
                if ($customer) {
                    $customer->phone = $incomingPhone;
                    if ($incomingName) {
                        $customer->name = strtoupper(trim($incomingName));
                    }
                    $customer->save();
                }
            }

            $transaction = Transaction::create([
                'user_id'         => $userId, // <-- 4. GUNAKAN $userId DARI PAYLOAD
                'customer_id'     => $customerId,
                'transaction_code'=> 'TRX-' . time(),
                'total_amount'    => $totalAmount,
                'discount'        => $discount,
                'payment_method'  => $validated['payment_method'],
                'cash_amount'     => $cashAmount,
                'change_amount'   => $changeAmount,
                'status'          => $validated['status'],
            ]);

            $totalCostOfGoods = 0;
            foreach ($cartProducts as $item) {
                $quantity = (float)$item['quantity'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $item['product_id'],
                    'unit_name'      => $item['unit_name'],
                    'quantity'       => $quantity,
                    'price'          => $item['price'],
                    'subtotal'       => $item['subtotal'],
                ]);

                // if ($validated['status'] == 'paid') {
                //     $productUnit = ProductUnit::find($item['id']);
                //     if (!$productUnit) {
                //         throw new \Exception("Unit produk {$item['unit_name']} (ID: {$item['id']}) tidak ditemukan.");
                //     }
                //     if ($productUnit->stock < $quantity) {
                //         throw new \Exception("Stok {$productUnit->name} tidak mencukupi!");
                //     }
                //     $productUnit->decrement('stock', $quantity);

                //     $productParent = Product::find($item['product_id']);
                //     if (!$productParent) {
                //         throw new \Exception("Produk induk (ID: {$item['product_id']}) tidak ditemukan.");
                //     }

                //     $baseUnitQuantity = (int) round( $quantity * (int)$item['conversion'] );

                //     Inventory::create([
                //         'user_id'         => $userId, // <-- 5. GUNAKAN $userId DARI PAYLOAD
                //         'product_id'      => $item['product_id'],
                //         'product_unit_id' => $item['id'],
                //         'quantity'        => -$quantity,
                //         'type'            => 'keluar',
                //         'description'     => "Penjualan #{$transaction->transaction_code}",
                //     ]);

                //     $totalCostOfGoods += ($productParent->price_buy ?? 0) * $baseUnitQuantity;
                // }
            }

            // if ($validated['status'] == 'paid' && $totalAmount > 0) {
            //     $accountType = null;
            //     if ($validated['payment_method'] == 'cash') $accountType = 'cash';
            //     else if ($validated['payment_method'] == 'card' || $validated['payment_method'] == 'qris') $accountType = 'bank';

            //     if ($accountType) {
            //         CashFlow::create([
            //             'user_id'        => $userId, // <-- 6. GUNAKAN $userId DARI PAYLOAD
            //             'flow_type'      => 'masuk',
            //             'source_type'    => 'transaction',
            //             'account'        => $accountType,
            //             'amount'         => $totalAmount,
            //             'description'    => "Penjualan Transaksi #{$transaction->transaction_code}",
            //             'transaction_id' => $transaction->id,
            //         ]);
            //     }

            //     $today = Carbon::today();
            //     // 7. GUNAKAN $userId DARI PAYLOAD
            //     $report = Report::where('user_id', $userId)
            //         ->where('report_type', 'laba_rugi')
            //         ->whereDate('created_at', $today->toDateString())
            //         ->first();

            //     if (!$report) {
            //         $report = Report::create([
            //             'user_id' => $userId, // <-- 8. GUNAKAN $userId DARI PAYLOAD
            //             'report_type' => 'laba_rugi',
            //             'total_sales' => 0, 'total_cost' => 0, 'profit' => 0,
            //             'cash_amount' => 0, 'card_amount' => 0, 'qris_amount' => 0,
            //             'transaction_count' => 0,
            //             'created_at' => $today,
            //             'updated_at' => $today
            //         ]);
            //     }

            //     $report->increment('total_sales', $totalAmount);
            //     $report->increment('total_cost', $totalCostOfGoods);
            //     $report->increment('profit', $totalAmount - $totalCostOfGoods);
            //     $report->increment('transaction_count');

            //     if ($validated['payment_method'] == 'cash') $report->increment('cash_amount', $totalAmount);
            //     else if ($validated['payment_method'] == 'card') $report->increment('card_amount', $totalAmount);
            //     else if ($validated['payment_method'] == 'qris') $report->increment('qris_amount', $totalAmount);
            // }

            DB::commit();

            // 9. Kirim respons JSON dengan transaction ID untuk redirect ke receipt
            return response()->json([
                'message' => 'Transaksi berhasil disimpan!',
                'transaction_id' => $transaction->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // 10. Kirim respons error JSON, BUKAN redirect
            return response()->json(['error' => 'Gagal: ' . $e->getMessage() . ' di baris ' . $e->getLine()], 500);
        }
    }


    public function show($id)
    {
        $transaction = Transaction::with([
            'details.product.color',
            'details.product.type'
        ])->findOrFail($id);

        $paymentMethods = Transaction::selectRaw('payment_method, sum(total_amount) as total')
            ->where('id', $id)
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        return view('transactions.show', compact('transaction', 'paymentMethods'));
    }

    public function refund($id)
    {
        $transaction = Transaction::with(['details.product'])->findOrFail($id);

        // [PENTING] Kita hitung sisa item yang BISA direfund
        // dengan cara cek jumlah beli awal DIKURANGI
        // jumlah yang pernah direfund sebelumnya.
        $refundQuantities = [];
        foreach ($transaction->details as $detail) {
            // 1. Cari unit produknya (sama seperti di logic 'destroy' Anda)
            $productUnit = ProductUnit::where('product_id', $detail->product_id)
                                      ->where('name', $detail->unit_name)
                                      ->first();

            if (!$productUnit) {
                $refundQuantities[$detail->id] = 0; // Tidak bisa refund jika unit tidak ada
                continue;
            }

            // 2. Hitung total yg PERNAH direfund untuk item ini
            $previouslyRefundedQty = RefundDetail::where('product_unit_id', $productUnit->id)
                ->whereHas('refund', function($query) use ($transaction) {
                    $query->where('original_transaction_id', $transaction->id);
                })
                ->sum('quantity');

            // 3. Sisa yang BISA direfund
            $remainingQty = $detail->quantity - $previouslyRefundedQty;
            $refundQuantities[$detail->id] = $remainingQty;
        }

        return view('transactions.refund', compact('transaction', 'refundQuantities'));
    }

    public function refundStore(Request $request, $id)
    {
        $validated = $request->validate([
            'refund_items' => 'required|array',
            'refund_items.*.detail_id' => 'required|exists:transaction_details,id',
            'refund_items.*.quantity' => 'required|numeric|min:0', // Boleh 0, nanti di-skip
            'reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $originalTransaction = Transaction::with('details.product')->findOrFail($id);
            $totalRefundAmount = 0;
            $totalCostToReturn = 0;
            $refundDetailsData = []; // Untuk menampung data yg akan di-create

            // 1. Loop dan validasi item yang di-refund
            foreach ($validated['refund_items'] as $item) {
                $refundQty = (float)$item['quantity'];
                if ($refundQty <= 0) {
                    continue; // Lewati jika kuantitas 0
                }

                $detail = $originalTransaction->details->find($item['detail_id']);
                if (!$detail) {
                    throw new \Exception("Detail item (ID: {$item['detail_id']}) tidak valid.");
                }

                // Cari ProductUnit
                $productUnit = ProductUnit::where('product_id', $detail->product_id)
                                          ->where('name', $detail->unit_name)
                                          ->first();
                if (!$productUnit) {
                    throw new \Exception("Unit produk '{$detail->unit_name}' tidak ditemukan.");
                }

                // Validasi jumlah sisa
                $previouslyRefundedQty = RefundDetail::where('product_unit_id', $productUnit->id)
                    ->whereHas('refund', function($query) use ($originalTransaction) {
                        $query->where('original_transaction_id', $originalTransaction->id);
                    })
                    ->sum('quantity');

                $remainingQty = $detail->quantity - $previouslyRefundedQty;

                if ($refundQty > $remainingQty) {
                    throw new \Exception("Jumlah refund '{$productUnit->product->name} ({$productUnit->name})' melebihi sisa item. (Sisa: {$remainingQty})");
                }

                // --- Kalkulasi ---
                $productParent = $detail->product;
                $baseUnitQuantity = $refundQty * $productUnit->conversion_value; // Qty PCS
                $refundSubtotal = $detail->price * $refundQty; // Uang yg dibalikin

                $totalRefundAmount += $refundSubtotal;
                $totalCostToReturn += ($productParent->price_buy ?? 0) * $baseUnitQuantity;

                // --- Siapkan Data untuk Tabel ---

                // A. Data untuk 'refund_details'
                $refundDetailsData[] = [
                    'product_unit_id' => $productUnit->id,
                    'quantity'        => $refundQty,
                    'price_per_unit'  => $detail->price,
                    'subtotal'        => $refundSubtotal,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                // B. Kembalikan Stok (Tabel 'product_units')
                $productUnit->increment('stock', $refundQty);

                // C. Catat Log Inventory (Tabel 'inventories' / 'stok_barang')
                Inventory::create([
                    'user_id'         => auth()->id(),
                    'product_id'      => $productParent->id,
                    'product_unit_id' => $productUnit->id,
                    'quantity'        => $refundQty, // POSITIF karena stok MASUK
                    'type'            => 'masuk',
                    'description'     => "Refund dari Transaksi #{$originalTransaction->transaction_code}",
                ]);
            }

            // 2. Cek apakah ada yg direfund
            if ($totalRefundAmount <= 0) {
                throw new \Exception("Tidak ada item yang dipilih untuk direfund.");
            }

            // 3. Buat data 'refunds' (Header)
            $refund = Refund::create([
                'original_transaction_id' => $originalTransaction->id,
                'user_id'                 => auth()->id(),
                'total_refund_amount'     => $totalRefundAmount,
                'reason'                  => $request->reason,
            ]);

            // 4. Masukkan semua detail refund
            $refund->details()->createMany($refundDetailsData);

            // 5. Catat Arus Kas (KELUAR)
            // Asumsi refund selalu via cash, atau Anda bisa tanya
            CashFlow::create([
                'user_id'        => auth()->id(),
                'flow_type'      => 'keluar',
                'source_type'    => 'refund', // Sumber baru
                'account'        => 'cash', // Asumsi refund dari laci kas
                'amount'         => $totalRefundAmount,
                'description'    => "Refund Transaksi #{$originalTransaction->transaction_code}",
                'transaction_id' => $originalTransaction->id,
            ]);

            // 6. Update Laporan Harian (Mengurangi profit/sales)
            // Cari laporan di TANGGAL TRANSAKSI ASLI
            $report = Report::where('user_id', $originalTransaction->user_id)
                ->where('report_type', 'laba_rugi')
                ->whereDate('created_at', $originalTransaction->created_at->toDateString())
                ->first();

            if ($report) {
                $report->decrement('total_sales', $totalRefundAmount);
                $report->decrement('total_cost', $totalCostToReturn);
                $report->decrement('profit', $totalRefundAmount - $totalCostToReturn);

                // Kurangi juga total metode pembayarannya
                if ($originalTransaction->payment_method == 'cash') {
                    $report->decrement('cash_amount', $totalRefundAmount);
                } else if ($originalTransaction->payment_method == 'card') {
                    $report->decrement('card_amount', $totalRefundAmount);
                } else if ($originalTransaction->payment_method == 'qris') {
                    $report->decrement('qris_amount', $totalRefundAmount);
                }
            }

            // Selesai!
            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Refund berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Tampilkan error yg spesifik
            return back()->with('error', 'Gagal: ' . $e->getMessage() . ' (Baris: ' . $e->getLine() . ')');
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::with(['details.product'])->findOrFail($id);
            $wasPaid = ($transaction->status == 'paid');
            $totalCostToReturn = 0;

            foreach ($transaction->details as $detail) {
                if ($wasPaid && $detail->quantity > 0) {

                    $productParent = $detail->product;
                    if (!$productParent) continue;

                    $productUnit = ProductUnit::where('product_id', $productParent->id)
                                              ->where('name', $detail->unit_name)
                                              ->first();

                    if ($productUnit) {
                        $quantityToReturn = (float)$detail->quantity;

                        // Kembalikan stok ke UNIT
                        $productUnit->increment('stock', $quantityToReturn);

                        // Hitung HPP yg akan dikembalikan
                        $baseUnitQuantity = $quantityToReturn * $productUnit->conversion_value;
                        $totalCostToReturn += ($productParent->price_buy ?? 0) * $baseUnitQuantity;

                        Inventory::create([
                            'user_id' => auth()->id(),
                            'product_id'  => $productParent->id,
                            'product_unit_id' => $productUnit->id, // Tambahkan ID Unit
                            'quantity'    => $quantityToReturn, // <-- PERBAIKAN: Simpan 3 (LUSIN)
                            'type'        => 'masuk',
                            'description' => 'Stok kembali dari HAPUS Transaksi #' . $transaction->transaction_code,
                        ]);
                    }
                }
                $detail->delete();
            }

            if ($wasPaid) {
                CashFlow::where('transaction_id', $transaction->id)->delete();

                $report = Report::where('user_id', $transaction->user_id)
                    ->where('report_type', 'laba_rugi')
                    ->whereDate('created_at', $transaction->created_at->toDateString())
                    ->first();

                if ($report) {
                    $report->decrement('total_sales', $transaction->total_amount);
                    $report->decrement('total_cost', $totalCostToReturn);
                    $report->decrement('profit', $transaction->total_amount - $totalCostToReturn);
                    $report->decrement('transaction_count');

                    if ($transaction->payment_method == 'cash') {
                        $report->decrement('cash_amount', $transaction->total_amount);
                    } else if ($transaction->payment_method == 'card') {
                        $report->decrement('card_amount', $transaction->total_amount);
                    } else if ($transaction->payment_method == 'qris') {
                        $report->decrement('qris_amount', $transaction->total_amount);
                    }
                }
            }

            $transaction->delete();
            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil dihapus! Stok dan kas telah disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage() . ' di baris ' . $e->getLine());
        }
    }

    public function addFund(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        CashFlow::create([
            'user_id' => auth()->id(),
            'source_type' => 'add_funds',
            'flow_type' => 'masuk',
            'account' => 'cash',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Penambahan kas harian',
        ]);

        return back()->with('success', 'Kas harian berhasil ditambahkan!');
    }

    public function markPaid($id)
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::with('details.product')->findOrFail($id);

            if ($transaction->status === 'paid') {
                return redirect()->back()->with('info', 'Transaksi ini sudah dibayar.');
            }

            // 2. LOOP PERTAMA: Cek stok
            foreach ($transaction->details as $detail) {

                $productParent = $detail->product;
                if (!$productParent) throw new \Exception("Produk induk (ID: {$detail->product_id}) tidak ditemukan.");

                $productUnit = ProductUnit::where('product_id', $productParent->id)
                                          ->where('name', $detail->unit_name)
                                          ->first();

                if (!$productUnit) {
                    throw new \Exception("Data unit produk '{$detail->unit_name}' tidak ditemukan.");
                }

                $quantitySold = (float)$detail->quantity;

                if ($productUnit->stock < $quantitySold) {
                    throw new \Exception("Stok {$productUnit->name} tidak mencukupi (tersisa {$productUnit->stock})!");
                }
            }

            // 3. LOOP KEDUA: Kurangi stok, buat inventory, hitung HPP
            $totalCostOfGoods = 0;
            foreach ($transaction->details as $detail) {

                $productParent = $detail->product;
                $productUnit = ProductUnit::where('product_id', $productParent->id)
                                          ->where('name', $detail->unit_name)
                                          ->first();
                $quantitySold = (float)$detail->quantity;

                // A. Kurangi stok dari tabel product_units
                $productUnit->decrement('stock', $quantitySold);

                // B. Hitung jumlah satuan dasar (pcs) - UNTUK HPP
                $baseUnitQuantity = $quantitySold * $productUnit->conversion_value;

                // C. Buat catatan Inventory
                Inventory::create([
                    'product_id'  => $productParent->id,
                    'product_unit_id' => $productUnit->id, // Tambahkan ID Unit
                    'quantity'    => -$quantitySold, // <-- PERBAIKAN: Simpan -3 (LUSIN)
                    'type'        => 'keluar',
                    'user_id'     => $transaction->user_id,
                    'description' => 'Penjualan ' . $transaction->transaction_code,
                ]);

                // D. Hitung HPP (COGS) - (Tetap pakai $baseUnitQuantity)
                $totalCostOfGoods += ($productParent->price_buy ?? 0) * $baseUnitQuantity;
            }

            // 4. Proses Akuntansi (CashFlow & Report)
            $accountType = null;
            if ($transaction->payment_method === 'cash') $accountType = 'cash';
            else if ($transaction->payment_method === 'card' || $transaction->payment_method === 'qris') $accountType = 'bank';

            if ($accountType) {
                CashFlow::create([
                    'user_id' => $transaction->user_id,
                    'source_type' => 'transaction',
                    'flow_type' => 'masuk',
                    'account' => $accountType,
                    'amount' => $transaction->total_amount,
                    'description' => 'Penjualan (dari Unpaid) #' . $transaction->transaction_code,
                    'transaction_id' => $transaction->id,
                ]);

                $report = Report::where('user_id', $transaction->user_id)
                    ->where('report_type', 'laba_rugi')
                    ->whereDate('created_at', $transaction->created_at->toDateString())
                    ->first();

                if (!$report) {
                    $report = Report::create([
                        'user_id' => $transaction->user_id,
                        'report_type' => 'laba_rugi',
                        'total_sales' => 0, 'total_cost' => 0, 'profit' => 0,
                        'cash_amount' => 0, 'card_amount' => 0, 'qris_amount' => 0,
                        'transaction_count' => 0,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->created_at
                    ]);
                }

                $report->increment('total_sales', $transaction->total_amount);
                $report->increment('total_cost', $totalCostOfGoods);
                $report->increment('profit', $transaction->total_amount - $totalCostOfGoods);
                $report->increment('transaction_count');

                if ($transaction->payment_method == 'cash') {
                    $report->increment('cash_amount', $transaction->total_amount);
                } else if ($transaction->payment_method == 'card') {
                    $report->increment('card_amount', $transaction->total_amount);
                } else if ($transaction->payment_method == 'qris') {
                    $report->increment('qris_amount', $transaction->total_amount);
                }
            }

            // 5. Update status transaksi
            $transaction->status = 'paid';
            $transaction->save();

            // 6. Kirim nota ke WhatsApp customer jika ada
            $this->sendReceiptToWhatsApp($transaction);

            DB::commit();

            // return redirect()->route('transactions.index')->with('success', 'Transaksi telah dibayar dan stok diperbarui!');
            return redirect()->route('transactions.receipt', $transaction->id)
                         ->with('print_confirmation');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage() . ' di file ' . $e->getFile() . ' baris ' . $e->getLine());
        }
    }
    public function receipt(Transaction $transaction)
    {
        // Load relasi yang diperlukan (item-itemnya)
        $transaction->load('details.productUnit.product');

        // Kembalikan view 'receipt', BUKAN 'show'
        return view('transactions.receipt', compact('transaction'));
    }

    /**
     * Generate dan kirim nota ke WhatsApp customer
     */
    protected function sendReceiptToWhatsApp($transaction)
    {
        try {
            // Load relasi yang diperlukan
            $transaction->load('customer', 'details.product.color', 'user');

            // Cek apakah customer ada dan punya nomor telepon
            if (!$transaction->customer || !$transaction->customer->phone) {
                return; // Tidak ada customer atau tidak punya nomor telepon
            }

            // Generate nota dalam format text untuk WhatsApp
            $nota = $this->generateWhatsAppReceipt($transaction);

            // Kirim ke WhatsApp
            $result = $this->whatsappService->sendMessage(
                $transaction->customer->phone,
                $nota
            );

            // Log hasil pengiriman
            if ($result && (isset($result['status']) && $result['status'] === true)) {
                \Log::info("Nota berhasil dikirim ke WhatsApp: {$transaction->customer->phone}");
            } else {
                \Log::warning("Gagal kirim nota ke WhatsApp: {$transaction->customer->phone}", $result ?? []);
            }

        } catch (\Exception $e) {
            // Jangan throw error, hanya log saja agar tidak mengganggu proses transaksi
            \Log::error("Error kirim nota WhatsApp: " . $e->getMessage());
        }
    }

    /**
     * Generate nota dalam format text untuk WhatsApp
     */
    protected function generateWhatsAppReceipt($transaction)
    {
        $nota = "🏪 *Toko Trijaya*\n\n";
        $nota .= "NOTA {$transaction->transaction_code}\n";
        $nota .= "Tanggal: " . $transaction->created_at->format('d/m/Y H:i') . "\n";
        $nota .= "Kasir: {$transaction->user->name}\n";


        if ($transaction->customer) {
            $nota .= "Customer: {$transaction->customer->name}\n";
        }


        foreach ($transaction->details as $item) {
            $nota .= "• {$item->product->name}";
            if ($item->product->color) {
                $nota .= " ({$item->product->color->name})";
            }
            $nota .= "\n";
            $nota .= "  " . number_format($item->quantity, 0, ',', '.') . " {$item->unit_name} × Rp " . number_format($item->price, 0, ',', '.') . "\n";
            $nota .= "  = Rp " . number_format($item->subtotal, 0, ',', '.') . "\n\n";
        }

        if ($transaction->discount > 0) {
            $nota .= "Diskon: Rp " . number_format($transaction->discount, 0, ',', '.') . "\n";
        }

        $nota .= "*TOTAL: Rp " . number_format($transaction->total_amount, 0, ',', '.') . "*\n\n";

        if ($transaction->payment_method === 'cash') {
            $nota .= "(Cash): Rp " . number_format($transaction->cash_amount, 0, ',', '.') . "\n";
            if ($transaction->change_amount > 0) {
                $nota .= "Kembalian: Rp " . number_format($transaction->change_amount, 0, ',', '.') . "\n";
            }
        } else {
            $paymentMethod = strtoupper($transaction->payment_method);
            $nota .= "Pembayaran via: {$paymentMethod}\n";
        }

        $nota .= "Status: *" . strtoupper($transaction->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS') . "*";

        return $nota;
    }
}
