<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\ProductType;
use App\Models\ProductUnit;
use App\Models\ProductColor;
use App\Models\InventoryConversion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // <-- Pastikan ini ada

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // ... (Method index Anda - TIDAK BERUBAH)
        $request->validate([
            'type_id' => 'nullable|integer|exists:product_types,id',
            'color_id'=> 'nullable|integer|exists:product_colors,id',
            'unit_name'=> 'nullable|string|exists:product_units,name'
        ]);
        $query = ProductUnit::with(['product.type', 'product.color', 'inventories.user']);
        if ($request->filled('type_id') || $request->filled('search') || $request->filled('color_id')) {
            $query->whereHas('product', function ($q) use ($request){
                if ($request->filled('type_id')) {
                    $q->where('type_id', $request->type_id);
                }
                if ($request->filled('search')) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                }
                if ($request->filled('color_id')) {
                    $q->where('color_id', $request->color_id);
                }
            });
        }
        if ($request->filled('unit_name')) {
            $query->where('name', $request->unit_name);
        }
        $productUnits = $query->orderBy(
            Product::select('name')->whereColumn('products.id', 'product_units.product_id')
        )
        ->paginate(50)
        ->withQueryString();
        $productTypes = ProductType::orderBy('name', 'asc')->get();
        $productColors = ProductColor::orderBy('name', 'asc')->get();;
        $unitNames = ProductUnit::distinct()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')->pluck('name');

        $activeType = null;
        if ($request->filled('type_id')) {
            $activeType = $productTypes->firstWhere('id', $request->type_id);
        }
        $activeColor = null;
        if ($request->filled('color_id')) {
            $activeColor = $productColors->firstWhere('id', $request->color_id);
        }
        return view('inventories.index', compact('productUnits', 'productTypes', 'activeType', 'productColors', 'activeColor','unitNames'));
    }

    public function show(ProductUnit $productUnit)
    {
        $productUnit->load('product.color', 'product.type', 'inventories.user');
        return view('inventories.show', ['unit' => $productUnit]);
    }

    public function opname(Request $request)
    {
        $productTypes = ProductType::orderBy('name', 'asc')->get();
        $productColors = ProductColor::orderBy('name', 'asc')->get();;
        $unitNames = ProductUnit::distinct()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')->pluck('name');

        $query = ProductUnit::with(['product.type', 'product.color', 'inventories.user']);
        if ($request->filled('type_id') || $request->filled('search') || $request->filled('color_id')) {
            $query->whereHas('product', function ($q) use ($request){
                if ($request->filled('type_id')) {
                    $q->where('type_id', $request->type_id);
                }
                if ($request->filled('search')) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                }
                if ($request->filled('color_id')) {
                    $q->where('color_id', $request->color_id);
                }
            });
        }
        if ($request->filled('unit_name')) {
            $query->where('name', $request->unit_name);
        }

        $activeType = null;
        if ($request->filled('type_id')) {
            $activeType = $productTypes->firstWhere('id', $request->type_id);
        }
        $activeColor = null;
        if ($request->filled('color_id')) {
            $activeColor = $productColors->firstWhere('id', $request->color_id);
        }

        $productUnits = $query->get();
        return view('inventories.opname', compact('productUnits', 'productTypes', 'productColors', 'unitNames'));
    }

    public function storeOpname(Request $request)
    {
        // ... (Method storeOpname Anda - TIDAK BERUBAH) ...
        $validated = $request->validate([
            'opname'                => 'required|array',
            'opname.*.stok_fisik'   => 'required|integer|min:0',
            'opname.*.alasan'       => 'nullable|string|max:255',
        ]);
        DB::beginTransaction();
        try {
            $totalPerubahan = 0;
            foreach ($validated['opname'] as $unitId => $data) {
                $unit = ProductUnit::find($unitId);
                if (!$unit) continue;
                $stokSistem = $unit->stock;
                $stokFisik = (int)$data['stok_fisik'];
                $selisih = $stokFisik - $stokSistem;
                if ($selisih != 0) {
                    $totalPerubahan++;
                    $unit->update(['stock' => $stokFisik]);
                    Inventory::create([
                        'product_id'      => $unit->product_id,
                        'product_unit_id' => $unit->id,
                        'user_id'         => Auth::id(),
                        'quantity'        => $selisih,
                        'type'            => 'koreksi',
                        'description'     => 'Stok Opname: ' . ($data['alasan'] ?? 'Tanpa Keterangan'),
                    ]);
                }
            }
            DB::commit();
            if ($totalPerubahan == 0) {
                 return redirect()->route('inventories.opname')->with('success', 'Stok sudah sesuai. Tidak ada perubahan disimpan.');
            }
            return redirect()->route('inventories.index')->with('success', "Stok opname berhasil disimpan. $totalPerubahan variasi produk telah diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan opname: ' . $e->getMessage())->withInput();
        }
    }

    public function convert()
    {
        // ... (Method convert Anda - TIDAK BERUBAH) ...
        $products = Product::whereHas('units', null, '>', 1)
                            ->with('color', 'type')
                            ->orderBy('name')
                            ->get();
        return view('inventories.convert', compact('products'));
    }

    // -----------------------------------------------------------------
    // [METHOD DIUBAH] Simpan Logika Konversi (Logika Dibalik)
    // -----------------------------------------------------------------
    public function storeConvert(Request $request)
    {
        // [DIUBAH] Validasi sekarang menerima 'quantity_to'
        $validated = $request->validate([
            'product_id'           => 'required|integer|exists:products,id',
            'from_product_unit_id' => 'required|integer|exists:product_units,id',
            'to_product_unit_id'   => 'required|integer|exists:product_units,id',
            'quantity_to'          => 'required|integer|min:1', // <-- HARUS BILANGAN BULAT
            'description'          => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $unitFrom = ProductUnit::find($validated['from_product_unit_id']);
            $unitTo = ProductUnit::find($validated['to_product_unit_id']);
            $quantityTo = (int)$validated['quantity_to']; // <-- Dari request (bilangan bulat)

            // Validasi 1: Pastikan kedua unit berasal dari produk yang sama
            if ($unitFrom->product_id != $validated['product_id'] || $unitTo->product_id != $validated['product_id']) {
                throw ValidationException::withMessages(['product_id' => 'Satuan yang dipilih tidak cocok dengan produk.']);
            }

            // Validasi 2: Tidak bisa konversi ke unit yang sama
            if ($unitFrom->id == $unitTo->id) {
                throw ValidationException::withMessages(['to_product_unit_id' => 'Tidak bisa mengkonversi ke satuan yang sama.']);
            }

            // Validasi 3: Pastikan conversion_value ada dan tidak 0
            if (empty($unitFrom->conversion_value) || $unitFrom->conversion_value == 0 || empty($unitTo->conversion_value)) {
                throw ValidationException::withMessages(['product_id' => 'Produk ini tidak memiliki nilai konversi (atau 0). Harap set di data produk.']);
            }

            // --- [LOGIKA BARU] Kalkulasi 'quantity_from' di server ---
            $numerator = $quantityTo * $unitTo->conversion_value;

            if ($numerator % $unitFrom->conversion_value !== 0) {
                throw ValidationException::withMessages([
                    'quantity_to' => 'Konversi menghasilkan jumlah sumber pecahan. Sesuaikan jumlah tujuan agar menghasilkan bilangan bulat.'
                ]);
            }

            $quantityFrom = intdiv($numerator, $unitFrom->conversion_value);

            // Validasi 4: Cek stok sumber
            if ($unitFrom->stock < $quantityFrom) {
                throw ValidationException::withMessages([
                    // Tampilkan pesan error yang jelas
                    'quantity_to' => "Stok sumber tidak mencukupi. Untuk mendapat {$quantityTo} {$unitTo->name}, Anda butuh {$quantityFrom} {$unitFrom->name}. Stok saat ini: " . $unitFrom->stock
                ]);
            }

            // --- Mulai Logika Konversi ---

            $productName = optional($unitFrom->product)->name
                ?? optional($unitTo->product)->name
                ?? Product::find($validated['product_id'])->name
                ?? 'Produk';

            // 1. Buat log event konversi
            $conversion = InventoryConversion::create([
                'user_id'       => Auth::id(),
                'product_id'    => $validated['product_id'],
                'from_product_unit_id' => $unitFrom->id,
                'to_product_unit_id'   => $unitTo->id,
                'quantity_from' => $quantityFrom, // <-- Hasil kalkulasi server
                'quantity_to'   => $quantityTo,   // <-- Dari request
                'description'   => $validated['description'] ?? 'Konversi stok',
            ]);

            // 2. Kurangi stok sumber
            $unitFrom->decrement('stock', $quantityFrom);
            Inventory::create([
                'product_id'      => $unitFrom->product_id,
                'product_unit_id' => $unitFrom->id,
                'user_id'         => Auth::id(),
                'quantity'        => -$quantityFrom, // <-- Negatif (Hasil kalkulasi server)
                'type'            => 'keluar',
                'description'     => "Konversi #{$conversion->id} ke {$unitTo->name}",
            ]);

            // 3. Tambah stok tujuan
            $unitTo->increment('stock', $quantityTo);
            Inventory::create([
                'product_id'      => $unitTo->product_id,
                'product_unit_id' => $unitTo->id,
                'user_id'         => Auth::id(),
                'quantity'        => $quantityTo, // <-- Positif (Dari request)
                'type'            => 'masuk',
                'description'     => "Konversi #{$conversion->id} dari {$unitFrom->name}",
            ]);

            DB::commit();

            // [DIUBAH] Pesan sukses disesuaikan
            return redirect()->route('inventories.index')->with(
                'success',
                "Berhasil konversi {$productName}: {$quantityFrom} {$unitFrom->name} menjadi {$quantityTo} {$unitTo->name}."
            );

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan konversi: ' . $e->getMessage())->withInput();
        }
    }


    public function getUnitsForProduct(Request $request)
    {
        // ... (Method getUnitsForProduct Anda - TIDAK BERUBAH) ...
        $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $units = ProductUnit::where('product_id', $request->product_id)
                            ->orderBy('name')
                            ->get();
        return response()->json($units);
    }
}
