<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Inventory;
use App\Models\CashFlow;
use App\Models\Report;
use App\Models\ProductUnit;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Supplier;
use App\Services\WhatsappService;

class PurchaseController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
    public function index()
    {

        $purchases = Purchase::with(['supplier', 'user'])
                                ->latest()
                                ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'type_id' => 'nullable|integer|exists:product_types,id',
            'color_id'=> 'nullable|integer|exists:product_colors,id',
        ]);

        $query = Product::with(['type', 'color']);

        // Hanya tampilkan produk jika ada search atau filter
        $hasFilter = $request->filled('search') || ($request->has('type_id') && $request->type_id) || ($request->filled('color_id'));

        if ($hasFilter) {
            if ($request->has('type_id') && $request->type_id) {
                $query->where('type_id', $request->type_id);
            }
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            if ($request->filled('color_id')) {
                $query->where('color_id', $request->color_id);
            }

            $products = $query->orderBy('name', 'asc')->get();
        } else {
            $products = collect();
        }

        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $supplierOptions = $suppliers->map(function ($supplier) {
            return [
                'value' => (string) $supplier->id,
                'name'  => $supplier->name,
                'text'  => $supplier->name,
                'phone' => $supplier->phone,
            ];
        })->values();

        $productTypes = \App\Models\ProductType::orderBy('name', 'asc')->get();
        $productColors = \App\Models\ProductColor::orderBy('name', 'asc')->get();

        return view('purchases.create', [
            'products'        => $products,
            'suppliers'       => $suppliers,
            'supplierOptions' => $supplierOptions,
            'productTypes'    => $productTypes,
            'productColors'   => $productColors,
            'hasFilter'       => $hasFilter,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi - Accept supplier_name OR supplier_id
        $validated = $request->validate([
            'supplier_id'   => 'nullable|integer|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'products'      => 'required|array',
            'products.*'    => 'required|integer|exists:products,id',
            'quantities'    => 'required|array',
            'quantities.*'  => 'required|integer|min:1',
            'prices'        => 'required|array',
            'prices.*'      => 'required|numeric|min:0',
        ]);

        // Ambil supplier id & nama dengan aman
        $supplierId = $validated['supplier_id'] ?? null;
        // Nama bisa datang dari hidden supplier_name atau input text TomSelect (supplier_name_text)
        $supplierName = trim($validated['supplier_name'] ?? '');
        if ($supplierName === '') {
            $supplierName = trim($request->input('supplier_name_text', ''));
        }

        // Validasi custom: minimal salah satu (supplier_id atau supplier_name) harus diisi
        if (empty($supplierId) && $supplierName === '') {
            return redirect()->back()->withInput()->with('error', 'Nama supplier harus diisi!');
        }

        // Pastikan validated supplier_name terisi agar dipakai create/find
        if ($supplierName !== '') {
            $validated['supplier_name'] = $supplierName;
        }

        DB::beginTransaction();
        try {
            // 2. Supplier - Create or Find berdasarkan name jika supplier_id tidak ada
            if (!empty($supplierId)) {
                $supplier = Supplier::findOrFail($supplierId);

                // Update phone jika diisi
                if ($request->filled('phone')) {
                    $supplier->phone = $request->phone;
                    $supplier->save();
                }
            } else {
                // Create/Find supplier berdasarkan name
                $supplier = Supplier::firstOrCreate(
                    ['name' => $supplierName],
                    ['phone' => $request->phone ?? '']
                );

                // Update phone jika supplier sudah ada dan phone diisi
                if (!$supplier->wasRecentlyCreated && $request->filled('phone')) {
                    $supplier->phone = $request->phone;
                    $supplier->save();
                }
            }

            // 3. Buat Induk Pembelian
            $purchaseCode = 'PB-' . time();
            $purchase = Purchase::create([
                'purchase_code' => $purchaseCode,
                'supplier_id'   => $supplier->id,
                'total_amount'  => 0, // Akan di-update nanti
                'status'        => 'pending', // [FIX] DIPAKSA 'pending'
                'user_id'       => auth()->id(),
            ]);

            // 4. Loop produk & hitung total
            $totalAmount = 0;
            foreach ($validated['products'] as $i => $productId) {

                $quantity_pcs  = $validated['quantities'][$i];
                $price_buy_pcs = $validated['prices'][$i];
                $subtotal      = $quantity_pcs * $price_buy_pcs;
                $totalAmount  += $subtotal;

                // Update harga beli dan supplier
                $product = Product::find($productId);
                if ($product) {
                    $product->price_buy = $price_buy_pcs;
                    $product->supplier_id = $supplier->id; // Update supplier product
                    $product->save();
                }

                // [PENTING] Kita TIDAK perlu 'firstOrCreate' unit di sini.
                // Kita biarkan 'update' yang menanganinya nanti.
                // Ini membuat 'create' lebih cepat.

                // Buat detail pembelian (sederhana)
                $purchase->details()->create([
                    'product_id'      => $productId,
                    'quantity'        => $quantity_pcs,
                    'price'           => $price_buy_pcs,
                    'subtotal'        => $subtotal,
                ]);

            }

            // 5. Update Total Harga
            $purchase->total_amount = $totalAmount;
            $purchase->save();

            // 6. Kirim pesan ke supplier
            $this->sendPurchaseToSupplier($purchase);

            // 7. Selesaikan
            DB::commit();
            // Redirect ke 'edit' agar user bisa langsung isi Delivery Cost
            return redirect()->route('purchases.index', $purchase->id)
                            ->with('success', 'Pembelian "Pending" berhasil dibuat. Silakan tambahkan Biaya Kirim dan selesaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with('details.product')->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {

        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();

        return view('purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }


    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);


        // 1. Validasi
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'status'        => 'required|string',
            'delivery_cost' => 'required|numeric|min:0', // <-- Alasan Anda
            'products'      => 'required|array',
            'products.*'    => 'required|integer|exists:products,id',
            'quantities'    => 'required|array',
            'quantities.*'  => 'required|integer|min:1',
            'prices'        => 'required|array',
            'prices.*'      => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $purchase->status; // Pasti 'pending'
            $newStatus = $validated['status'];

            // 2. Logika Supplier
            $supplier = Supplier::firstOrCreate(
                ['name' => $request->supplier_name],
                ['phone' => $request->phone]
            );
            if (!$supplier->wasRecentlyCreated && $request->filled('phone')) {
                $supplier->phone = $request->phone;
                $supplier->save();
            }

            // 3. Hapus Detail Lama
            $purchase->details()->delete();

            // 4. Buat Detail BARU, Hitung Total, DAN LANGSUNG UPDATE STOK
            $total = 0;
            foreach ($validated['products'] as $i => $productId) {
                if (isset($validated['quantities'][$i]) && isset($validated['prices'][$i])) {

                    $quantity_pcs = $validated['quantities'][$i];
                    $price_buy_pcs = $validated['prices'][$i];
                    $subtotal = $quantity_pcs * $price_buy_pcs;
                    $total += $subtotal;

                    // Update harga beli
                    $product = Product::find($productId);
                    if ($product) {
                        $product->price_buy = $price_buy_pcs;
                        $product->save();
                    }

                    // Cari atau Buat unit "SATUAN"
                    $baseUnit = ProductUnit::firstOrCreate(
                        [ 'product_id' => $productId, 'name' => 'SATUAN' ],
                        [ 'conversion_value' => 1, 'price' => $price_buy_pcs * 2, 'stock' => 0 ]
                    );

                    // Buat detail pembelian baru
                    $purchase->details()->create([
                        'product_id'      => $productId,
                        'quantity'        => $quantity_pcs,
                        'price'           => $price_buy_pcs,
                        'subtotal'        => $subtotal
                    ]);

                    // [LOGIKA STOK HANYA ADA DI SINI]
                    if ($newStatus == 'completed') {
                        if ($baseUnit) {
                            $baseUnit->increment('stock', $quantity_pcs);

                            Inventory::create([
                                'product_unit_id' => $baseUnit->id,
                                'product_id'      => $productId,
                                'quantity'        => $quantity_pcs,
                                'type'            => 'masuk',
                                'user_id'         => auth()->id(),
                                'description'     => 'Stok bertambah dari pembelian #' . $purchase->purchase_code,
                            ]);
                        }
                    }
                }
            }

            // 5. Update Pembelian Induk
            $purchase->update([
                'supplier_id'   => $supplier->id,
                'total_amount'  => $total,
                'status'        => $newStatus,
                'delivery_cost' => $validated['delivery_cost'] ?? 0,
            ]);

            // 6. LOGIKA KAS (Hanya jika 'completed')
            if ($newStatus == 'completed') {

                $deliveryCost = (float) ($validated['delivery_cost'] ?? 0);
                $totalCostOfGoods = (float) $total;

                if ($deliveryCost > 0) {
                    CashFlow::create([
                        'user_id' => auth()->id(),
                        'flow_type'   => 'keluar',
                        'source_type' => 'purchases',
                        'account'     => 'cash',
                        'amount'      => $deliveryCost,
                        'description' => 'Biaya kirim (Ongkir) pembelian #' . $purchase->purchase_code,
                        'purchase_id' => $purchase->id,
                    ]);
                }
                if ($totalCostOfGoods > 0) {
                    CashFlow::create([
                        'user_id' => auth()->id(),
                        'flow_type'   => 'keluar',
                        'source_type' => 'purchases',
                        'account'     => 'bank',
                        'amount'      => $totalCostOfGoods,
                        'description' => 'Pembayaran barang pembelian #' . $purchase->purchase_code,
                        'purchase_id' => $purchase->id,
                    ]);
                }
            } else {
                // Pastikan tidak ada arus kas tersisa untuk pembelian pending
                CashFlow::where('purchase_id', $purchase->id)->delete();
            }


            // 8. Selesai
            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui pembelian: ' . $e->getMessage());
        }
    }

    public function refund($id)
    {
        $purchase = Purchase::with(['details.product'])->findOrFail($id);
        $refundQuantities = [];

        foreach ($purchase->details as $detail) {

            $productUnit = ProductUnit::where('product_id', $detail->product_id)
                                      ->where('conversion_value', 1)
                                      ->first();

            // -----------------------------------------------------------
            // AKHIR PERUBAHAN
            // -----------------------------------------------------------

            if (!$productUnit) {
                // Jika unit dasar (PCS) tidak ditemukan untuk produk ini,
                // ini adalah error data. Lewati item ini.
                $refundQuantities[$detail->id] = 0;
                continue;
            }

            // 2. Hitung total yg PERNAH diretur untuk item ini dari pembelian ini
           $previouslyReturnedQty = PurchaseReturnDetail::where('product_id', $detail->product_id)
            ->whereHas('purchaseReturn', function($query) use ($purchase) {
                $query->where('purchase_id', $purchase->id);
            })
            ->sum('quantity');

            // 3. Sisa yang BISA diretur
            $remainingQty = $detail->quantity - $previouslyReturnedQty;
            $refundQuantities[$detail->id] = $remainingQty;
        }

        return view('purchases.refund', compact('purchase', 'refundQuantities'));
    }


    public function refundStore(Request $request, $id)
    {
        $validated = $request->validate([
            'refund_items' => 'required|array',
            'refund_items.*.detail_id' => 'required|exists:purchase_details,id',
            'refund_items.*.quantity' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $originalPurchase = Purchase::with('details.product', 'supplier')->findOrFail($id);
            $totalReturnAmount = 0;
            $purchaseReturnDetailsData = [];

            foreach ($validated['refund_items'] as $item) {
                $returnQty = (float)$item['quantity'];
                if ($returnQty <= 0) {
                    continue;
                }

                $detail = $originalPurchase->details->find($item['detail_id']);
                if (!$detail) {
                    throw new \Exception("Detail item (ID: {$item['detail_id']}) tidak valid.");
                }

                // Cari unit dasar (PCS) berdasarkan conversion_value = 1
                $productUnit = ProductUnit::where('product_id', $detail->product_id)
                                        ->where('conversion_value', 1)
                                        ->first();

                // =============================================================

                if (!$productUnit) {
                    // Sekarang errornya akan lebih jelas jika unit PCS tidak ada
                    throw new \Exception("Unit dasar (PCS) tidak ditemukan untuk produk '{$detail->product->name}'.");
                }

                // Validasi jumlah sisa (sudah diperbaiki)
                $previouslyReturnedQty = PurchaseReturnDetail::where('product_id', $detail->product_id)
                    ->whereHas('purchaseReturn', function($query) use ($originalPurchase) {
                        $query->where('purchase_id', $originalPurchase->id); // FIX
                    })
                    ->sum('quantity');

                $remainingQty = $detail->quantity - $previouslyReturnedQty;

                if ($returnQty > $remainingQty) {
                    throw new \Exception("Jumlah retur '{$productUnit->product->name}' melebihi sisa item. (Sisa: {$remainingQty})");
                }

                $returnSubtotal = $detail->price * $returnQty;
                $totalReturnAmount += $returnSubtotal;

                $purchaseReturnDetailsData[] = [
                    'product_id'      => $detail->product_id, // FIX
                    'quantity'        => $returnQty,
                    'cost_price'      => $detail->price,
                    'subtotal'        => $returnSubtotal,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                // Kurangi Stok (Tabel 'product_units')
                $productUnit->decrement('stock', $returnQty);

                // Catat Log Inventory
                Inventory::create([
                    'user_id'         => auth()->id(),
                    'product_id'      => $productUnit->product_id,
                    'product_unit_id' => $productUnit->id, // Ini OK, karena inventory butuh unit
                    'quantity'        => -$returnQty,
                    'type'            => 'keluar',
                    'description'     => "Retur Pembelian #{$originalPurchase->purchase_code}",
                ]);
            }

            if ($totalReturnAmount <= 0) {
                throw new \Exception("Tidak ada item yang dipilih untuk diretur.");
            }

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id'          => $originalPurchase->id, // FIX
                'supplier_id'          => $originalPurchase->supplier_id,
                'user_id'              => auth()->id(),
                'return_code'          => 'PR-' . now()->format('YmdHis'),
                'total_amount'         => $totalReturnAmount,
                'status'               => 'completed',
                'return_date'          => now(),
                'reason'               => $request->reason,
            ]);

            $purchaseReturn->details()->createMany($purchaseReturnDetailsData);

            CashFlow::create([
                'user_id'       => auth()->id(),
                'flow_type'     => 'masuk',
                'source_type'   => 'purchase_return',
                'account'       => 'bank',
                'amount'        => $totalReturnAmount,
                'description'   => "Retur Pembelian #{$originalPurchase->purchase_code}",
            ]);

            $report = Report::where('report_type', 'laba_rugi')
                ->whereDate('created_at', $originalPurchase->created_at->toDateString())
                ->first();

            if ($report) {
                $report->decrement('total_cost', $totalReturnAmount);
                $report->increment('profit', $totalReturnAmount);
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Retur pembelian berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage() . ' (Baris: ' . $e->getLine() . ')');
        }
    }

    public function destroy($id)
    {
        $purchase = Purchase::with('details')->findOrFail($id);

        $purchase->details()->delete();
        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Data pembelian berhasil dihapus.');
    }

    /**
     * Kirim pesan ke supplier via WhatsApp
     */
    protected function sendPurchaseToSupplier($purchase)
    {
        try {
            // Load relasi yang diperlukan
            $purchase->load('supplier', 'details.product.color');

            // Cek apakah supplier ada dan punya nomor telepon
            if (!$purchase->supplier || !$purchase->supplier->phone) {
                return; // Tidak ada supplier atau tidak punya nomor telepon
            }

            // Generate pesan untuk supplier
            $message = $this->generatePurchaseMessage($purchase);

            // Kirim ke WhatsApp
            $result = $this->whatsappService->sendMessage(
                $purchase->supplier->phone,
                $message
            );

            // Log hasil pengiriman
            if ($result && (isset($result['status']) && $result['status'] === true)) {
                \Log::info("Pesan pembelian berhasil dikirim ke supplier: {$purchase->supplier->phone}");
            } else {
                \Log::warning("Gagal kirim pesan pembelian ke supplier: {$purchase->supplier->phone}", $result ?? []);
            }

        } catch (\Exception $e) {
            // Jangan throw error, hanya log saja agar tidak mengganggu proses pembelian
            \Log::error("Error kirim pesan ke supplier: " . $e->getMessage());
        }
    }

    /**
     * Generate pesan untuk supplier (hanya nama produk dan jumlah, tanpa harga)
     */
    protected function generatePurchaseMessage($purchase)
    {
        $message = "🏪 *Toko Trijaya*\n\n";
        $message .= "PESANAN {$purchase->purchase_code}\n";
        $message .= "Tanggal: " . $purchase->created_at->format('d/m/Y H:i') . "\n\n";
        $message .= "Berikut adalah daftar pesan barang:\n\n";
        foreach ($purchase->details as $item) {
            $productName = $item->product->name ?? 'Produk tidak ditemukan';
            $colorName = $item->product->color->name ?? '';

            $message .= "• {$productName}";
            if ($colorName) {
                $message .= " ({$colorName})";
            }
            $message .= "\n";
            $message .= "  Jumlah: {$item->quantity} pcs\n";
        }

        return $message;
    }

}
