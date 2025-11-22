<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductTypeController extends Controller
{
    /**
     * Menampilkan daftar jenis produk & form tambah.
     */
    public function index(Request $request)
    {
        $query = ProductType::query();


        $productTypes = $query->orderBy('name', 'asc')->paginate(30);

        return view('product_types.index', compact('productTypes'));
    }

    /**
     * Menyimpan jenis produk baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_types,name',
        ]);

        try {
            ProductType::create([
                'name' => Str::upper($validated['name'])
            ]);
            return redirect()->route('product_types.index')->with('success', 'Jenis produk baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(ProductType $productType)
    {
        return view('product_types.edit', compact('productType'));
    }

    /**
     * Memperbarui jenis produk.
     */
    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate([
            // Pastikan nama unik, tapi abaikan ID saat ini
            'name' => 'required|string|max:255|unique:product_types,name,' . $productType->id,
        ]);

        try {
            $productType->update([
                'name' => Str::upper($validated['name'])
            ]);
            return redirect()->route('product_types.index')->with('success', 'Jenis produk berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus jenis produk.
     */
    public function destroy(ProductType $productType)
    {
        // Pengecekan keamanan: Jangan hapus jika masih dipakai
        if ($productType->products()->count() > 0) {
            return redirect()->route('product_types.index')
                ->with('error', 'Gagal menghapus! Jenis ini masih digunakan oleh produk lain.');
        }

        try {
            $productType->delete();
            return redirect()->route('product_types.index')->with('success', 'Jenis produk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('product_types.index')->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
