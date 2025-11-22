<?php

namespace App\Http\Controllers;

// ... Hapus import Request yang lama jika ada
use Illuminate\Http\Request; // PASTIKAN ADA IMPORT INI
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Inventory;
use App\Models\CashFlow;
use App\Models\ProductUnit;
use App\Models\Purchase; // TAMBAHKAN IMPORT PURCHASE
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{
    /**
     * Menampilkan daftar retur pembelian.
     */
    public function index()
    {
        $purchaseReturns = PurchaseReturn::with(['supplier', 'user'])
                                        ->latest()
                                        ->paginate(10);

        return view('purchase_returns.index', compact('purchaseReturns'));
    }

    /**
     * Menampilkan form untuk membuat retur baru.
     * MODIFIKASI: Terima Request $request
     */
    public function create(Request $request) // MODIFIKASI
    {
        $purchase = null;
        $purchaseProducts = [];

        // Cek apakah ada purchase_id yang dikirim dari tombol retur
        if ($request->has('purchase_id')) {
            $purchase = Purchase::with('details.product', 'supplier')->find($request->query('purchase_id'));

            if ($purchase) {
                // Siapkan produk dari pembelian itu untuk pre-fill
                foreach ($purchase->details as $detail) {
                    $purchaseProducts[] = [
                        'id' => $detail->product_id,
                        'name' => $detail->product->name,
                        'price_buy' => $detail->price, // Harga saat beli
                        'quantity' => $detail->quantity // Kuantitas yg dibeli (bisa jadi max retur)
                    ];
                }
            }
        }

        // Ambil semua supplier, HANYA jika tidak ada purchase_id
        // Jika ada purchase_id, kita hanya perlu supplier itu
        $suppliers = $purchase ?
                     Supplier::where('id', $purchase->supplier_id)->get() :
                     Supplier::orderBy('name', 'asc')->get();

        // Ambil semua produk, untuk dropdown jika menambah manual
        $products = Product::orderBy('name', 'asc')->get();

        return view('purchase_returns.create', compact('suppliers', 'products', 'purchase', 'purchaseProducts'));
    }

    /**
     * Menyimpan retur pembelian baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'return_date' => 'required|date',
            'notes'       => 'nullable|string',
            'products'    => 'required|array',
            'products.*'  => 'required|integer|exists:products,id',
            'quantities'  => 'required|array',
            'quantities.*'=> 'required|integer|min:1',
            'prices'      => 'required|array',
            'prices.*'    => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 2. Buat Induk Retur
            $returnCode = 'RT-' . time();
            $purchaseReturn = PurchaseReturn::create([
                'return_code'  => $returnCode,
                'supplier_id'  => $validated['supplier_id'],
                'user_id'      => Auth::id(),
                'return_date'  => $validated['return_date'],
                'notes'        => $validated['notes'],
                'total_amount' => 0, // Akan di-update nanti
                'status'       => 'completed', // Langsung 'completed'
            ]);

            $totalAmount = 0;

            // 3. Loop Produk, Kurangi Stok, Catat Detail
            foreach ($validated['products'] as $i => $productId) {
                $quantity = $validated['quantities'][$i];
                $costPrice = $validated['prices'][$i]; // Ini adalah harga modal/beli
                $subtotal = $quantity * $costPrice;
                $totalAmount += $subtotal;

                // 3a. Simpan detail retur
                $purchaseReturn->details()->create([
                    'product_id' => $productId,
                    'quantity'   => $quantity,
                    'cost_price' => $costPrice,
                    'subtotal'   => $subtotal,
                ]);

                // 3b. Kurangi Stok (Logika kebalikan dari PurchaseController)
                // Asumsi 'SATUAN' adalah unit dasar Anda, sama seperti di PurchaseController
                $baseUnit = ProductUnit::where('product_id', $productId)
                                       ->where('name', 'SATUAN')
                                       ->first();

                if ($baseUnit) {
                    // Kurangi stok
                    $baseUnit->decrement('stock', $quantity);

                    // Catat di tabel inventory
                    Inventory::create([
                        'product_unit_id' => $baseUnit->id,
                        'product_id'      => $productId,
                        'quantity'        => $quantity, // Kuantitas positif
                        'type'            => 'keluar', // Tipe 'keluar'
                        'user_id'         => Auth::id(),
                        'description'     => 'Stok keluar dari Retur Pembelian #' . $returnCode,
                    ]);
                } else {
                    // Jika unit 'SATUAN' tidak ditemukan, lempar error
                    throw new \Exception("Unit dasar 'SATUAN' untuk produk ID $productId tidak ditemukan.");
                }
            }

            // 4. Update Total Harga di Induk Retur
            $purchaseReturn->total_amount = $totalAmount;
            $purchaseReturn->save();

            // 5. Catat di CashFlow (Logika kebalikan dari PurchaseController)
            // Asumsi kita menerima PENGEMBALIAN DANA ke 'bank'
            if ($totalAmount > 0) {
                CashFlow::create([
                    'user_id'       => Auth::id(),
                    'flow_type'     => 'masuk', // Uang MASUK
                    'source_type'   => 'purchase_returns', // Sumber dari retur
                    'account'       => 'bank', // Masuk ke bank (sesuai asumsi)
                    'amount'        => $totalAmount,
                    'description'   => 'Pengembalian dana dari retur pembelian #' . $returnCode,
                    'purchase_return_id' => $purchaseReturn->id, // Tautkan ke ID retur
                ]);
            }

            // 6. Selesaikan
            DB::commit();
            return redirect()->route('purchase-returns.index')
                             ->with('success', 'Retur pembelian berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan retur: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail retur.
     */
    public function show($id)
    {
        $purchaseReturn = PurchaseReturn::with('details.product', 'supplier', 'user')
                                        ->findOrFail($id);
        return view('purchase_returns.show', compact('purchaseReturn'));
    }

    /**
     * Menghapus data retur (HANYA JIKA 'pending', jika 'completed' harusnya dibatalkan)
     * Logika destroy Anda di PurchaseController berbahaya karena tidak mengembalikan stok.
     * Saya buatkan logika yang lebih aman:
     * Retur 'completed' TIDAK BISA dihapus, harus dibuatkan 'Jurnal Balik'
     * Tapi untuk sederhana, kita larang hapus 'completed'
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturn::with('details')->findOrFail($id);

            // LOGIKA PENGAMAN:
            // Jika retur sudah 'completed', stok dan kas sudah berubah.
            // Menghapusnya akan merusak data.
            // Seharusnya ada fitur "Batal Retur" yang membalik logikanya.
            if ($purchaseReturn->status === 'completed') {
                return redirect()->route('purchase-returns.index')
                                 ->with('error', 'Retur yang sudah "Completed" tidak bisa dihapus. Harap batalkan retur (fitur Batal) untuk mengembalikan stok.');
            }

            // Jika statusnya 'pending' (jika nanti Anda implementasi itu), aman untuk dihapus
            $purchaseReturn->details()->delete();
            $purchaseReturn->delete();

            DB::commit();
            return redirect()->route('purchase-returns.index')
                             ->with('success', 'Data retur berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('purchase-returns.index')
                             ->with('error', 'Gagal menghapus retur: ' . $e->getMessage());
        }
    }
}
