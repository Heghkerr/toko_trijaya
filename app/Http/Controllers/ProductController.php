<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductUnit;
use App\Models\ProductType;
use App\Models\ProductCOlor;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $request->validate([
            'type_id' => 'nullable|integer|exists:product_types,id',
            'color_id' => 'nullable|integer|exists:product_colors,id',
        ]);


        $query = Product::with(['type', 'units','color']);


        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('color_id')) {
            $query->where('color_id', $request->color_id);
        }


        $products = $query->orderBy('name', 'asc')
            ->paginate(30)
            ->withQueryString();


        $productTypes = ProductType::orderBy('name', 'asc')->get();
        $productColors = ProductColor::orderBy('name', 'asc')->get();

        $activeType = null;
        if ($request->filled('type_id')) {
            $activeType = $productTypes->firstWhere('id', $request->type_id);
        }
        $activeColor = null;
        if ($request->filled('color_id')) {
            $activeColor = $productColors->firstWhere('id', $request->color_id);
        }

        return view('products.index', compact('products', 'productTypes', 'activeType', 'productColors', 'activeColor'));
    }

    public function create()
    {
        $productUnits = ProductUnit::orderBy('name', 'asc')->pluck('name');
        $productTypes = ProductType::orderBy('name', 'asc')->pluck('name');
        $productColors = ProductColor::orderBy('name', 'asc')->pluck('name');

        $standardUnits = [
            ['name' => 'PCS', 'conversion' => 1],
            ['name' => 'LUSIN', 'conversion' => 12],
            ['name' => 'GROSS', 'conversion' => 144],
            ['name' => 'RATUSAN', 'conversion' => 100],
            ['name' => 'RIBUAN', 'conversion' => 1000],
            ['name' => 'PAK', 'conversion' => null],
        ];

        $customUnits = ProductUnit::distinct()
                        ->pluck('name')
                        ->diff(collect($standardUnits)->pluck('name')) // Ambil yang belum ada di $standardUnits
                        ->map(function($name) {
                            return ['name' => $name, 'conversion' => null]; // Satuan custom selalu isi sendiri
                        });

        // 4. [BARU] Gabungkan keduanya
        $unitOptions = collect($standardUnits)->merge($customUnits)->sortBy('name')->values();

        return view('products.create', compact('productTypes', 'productColors', 'productUnits', 'unitOptions'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type_name'   => 'required|string|max:255',
            'color'       => 'required|string|max:255',
            'price_buy'   => 'required|numeric|min:0',
            'min_stock'   => 'nullable|integer|min:0',
            'max_stock'   => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'units'                   => 'required|array|min:1',
            'units.*.name'            => 'required|string|max:100',
            'units.*.conversion_value'=> 'required|integer|min:1',
            'units.*.price'           => 'required|numeric|min:0|gte:price_buy',
            'units.*.stock'           => 'nullable|integer|min:0',


        ],
        [
        // Pesan error kustom agar lebih mudah dibaca
        'units.*.price.gte' => 'Harga jual satuan tidak boleh lebih rendah dari Harga Beli produk.'
        ]);


        DB::beginTransaction();
        try {


            $productType = ProductType::firstOrCreate(
                ['name' => Str::upper($validated['type_name'])],
                []
            );
            $productColors = ProductColor::firstOrCreate(
                ['name' => Str::upper($validated['color'])],
                []
            );

            $existingProduct = Product::where('name', Str::upper($validated['name']))
                                  ->where('type_id', $productType->id)
                                  ->where('color_id', $productColors->id)
                                  ->exists(); // 'exists()' lebih cepat dari 'first()'
            if ($existingProduct) {
            // Jika sudah ada, lemparkan error.
            // Ini akan otomatis ditangkap oleh blok 'catch' di bawah.
            throw new \Exception('Gagal! Produk dengan Nama, Tipe, dan Warna yang sama persis sudah ada.');
        }
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }


            $product = Product::create([
                'name' => Str::upper($validated['name']),
                'type_id' => $productType->id,
                'color_id' => $productColors->id,
                'description' => $validated['description'] ?? null,
                'price_buy' => $validated['price_buy'],
                'min_stock' => $validated['min_stock'] ?? null,
                'max_stock' => $validated['max_stock'] ?? null,
                'image' => $imagePath,
            ]);

            if (!empty($validated['units'])) {
                foreach ($validated['units'] as $unitData) {
                    $unitStock = isset($unitData['stock']) && $unitData['stock'] !== '' ? (int)$unitData['stock'] : 0;
                    // Simpan unit/variasi
                    $unit = $product->units()->create([
                        'name'                => $unitData['name'],
                        'conversion_value'    => $unitData['conversion_value'],
                        'price'               => $unitData['price'],
                        'stock'               => $unitStock,
                    ]);

                    // Buat log inventory untuk SETIAP unit
                    if ($unitStock > 0) {
                        Inventory::create([
                            'product_id' => $product->id,
                            'product_unit_id' => $unit->id, // <-- (Opsional tapi disarankan)
                            'quantity' => $unitStock,
                            'user_id' => auth()->id(),
                            'type' => 'masuk',
                            'description' => 'Stok awal (' . $unitData['name'] . ')'
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('products.create')->with('success', 'Produk berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            return back()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage())->withInput();
        }
    }


    public function edit(Product $product)
    {
        $product->load('units', 'color', 'type');
        $productUnits = ProductUnit::orderBy('name', 'asc')->pluck('name');
        $productTypes = ProductType::orderBy('name', 'asc')->pluck('name');
        $productColors = ProductColor::orderBy('name', 'asc')->pluck('name');
        return view('products.edit', compact('product', 'productTypes', 'productColors', 'productUnits'));
    }


    public function update(Request $request, Product $product)
    {

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type_name'   => 'required|string|max:255',
            'color'       => 'required|string|max:255',
            'price_buy'   => 'required|numeric|min:0',
            'min_stock'   => 'nullable|integer|min:0',
            'max_stock'   => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'units'          => 'required|array|min:1',
            'units.*.id'     => 'nullable|integer|exists:product_units,id',
            'units.*.name'   => 'required|string|max:100',
            'units.*.conversion_value'=> 'required|integer|min:1',
            'units.*.price'           => 'required|numeric|min:0|gte:price_buy',
            'units.*.stock'  => 'nullable|integer|min:0',
        ],
        [
        // Pesan error kustom agar lebih mudah dibaca
        'units.*.price.gte' => 'Harga jual satuan tidak boleh lebih rendah dari Harga Beli produk.'
        ]);

        DB::beginTransaction();
        try {


            $productType = ProductType::firstOrCreate(
                ['name' => Str::upper($validated['type_name'])],
                []
            );
            $productColors = ProductColor::firstOrCreate(
                ['name' => Str::upper($validated['color'])],
                []
            );


            $imagePath = $product->image;
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $imagePath = $request->file('image')->store('product-images', 'public');
            }


            $product->update([
                'name'        => Str::upper($validated['name']),
                'type_id'     => $productType->id,
                'color_id'    => $productColors->id,
                'description' => $validated['description'] ?? null,
                'price_buy'   => $validated['price_buy'],
                'min_stock'   => $validated['min_stock'] ?? null,
                'max_stock'   => $validated['max_stock'] ?? null,
                'image'       => $imagePath,
            ]);


            $incomingUnitIds = [];


            foreach ($validated['units'] as $unitData) {
                $unitId = $unitData['id'] ?? null;


                $unit = $product->units()->findOrNew($unitId);


                $oldStock = $unit->stock ?? 0;
                $newStock = isset($unitData['stock']) && $unitData['stock'] !== '' ? (int)$unitData['stock'] : 0;

                $unit->name = $unitData['name'];
                $unit->conversion_value = $unitData['conversion_value'];
                $unit->price = $unitData['price'];
                $unit->stock = $newStock;


                if (!$unit->exists) {
                    $unit->product_id = $product->id;
                }

                $unit->save();


                $stockDifference = $newStock - $oldStock;

                if ($stockDifference != 0) {
                    Inventory::create([
                        'product_id' => $product->id,
                        'product_unit_id' => $unit->id, // Tautkan ke unit
                        'user_id' => auth()->id(),
                        'quantity' => $stockDifference, // Simpan selisihnya (bisa positif/negatif)
                        'type' => $stockDifference > 0 ? 'masuk' : 'keluar',
                        'description' => 'Koreksi stok (Update Produk)'
                    ]);
                }

                $incomingUnitIds[] = $unit->id;
            }

            $product->units()->whereNotIn('id', $incomingUnitIds)->delete();

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // --- PENGECEKAN TRANSAKSI ---
        // Asumsi: Anda punya relasi bernama 'transactionDetails' di model Product
        // yang merujuk ke tabel detail transaksi.

        // Ganti 'transactionDetails' jika nama relasi Anda berbeda (misal: 'sales', 'details')
        if ($product->transactionDetails()->exists()) {

            // Jika produk sudah ada di transaksi, JANGAN HAPUS.
            // Kembalikan ke index dengan pesan error.
            return redirect()
                ->route('products.index')
                ->with('error', 'Gagal! Produk tidak dapat dihapus karena sudah tercatat dalam transaksi.');
        }
        // --- AKHIR PENGECEKAN ---


        // Jika aman (tidak ada di transaksi), lanjutkan proses hapus
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }
}
