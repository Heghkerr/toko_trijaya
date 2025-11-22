<?php

namespace App\Http\Controllers;

use App\Models\ProductColor; // Diubah
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductColorController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductColor::query(); // Diubah
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $productColors = $query->orderBy('name', 'asc')->paginate(30); // Diubah
        return view('product_colors.index', compact('productColors')); // Diubah
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_colors,name', // Diubah
        ]);
        try {
            ProductColor::create([ // Diubah
                'name' => Str::upper($validated['name'])
            ]);
            return redirect()->route('product_colors.index')->with('success', 'Warna produk baru berhasil ditambahkan.'); // Diubah
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(ProductColor $productColor) // Diubah
    {
        return view('product_colors.edit', compact('productColor')); // Diubah
    }

    public function update(Request $request, ProductColor $productColor) // Diubah
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_colors,name,' . $productColor->id, // Diubah
        ]);
        try {
            $productColor->update([ // Diubah
                'name' => Str::upper($validated['name'])
            ]);
            return redirect()->route('product_colors.index')->with('success', 'Warna produk berhasil diperbarui.'); // Diubah
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(ProductColor $productColor) // Diubah
    {
        if ($productColor->products()->count() > 0) { // Diubah
            return redirect()->route('product_colors.index') // Diubah
                ->with('error', 'Gagal menghapus! Warna ini masih digunakan oleh produk lain.'); // Diubah
        }
        try {
            $productColor->delete(); // Diubah
            return redirect()->route('product_colors.index')->with('success', 'Warna produk berhasil dihapus.'); // Diubah
        } catch (\Exception $e) {
            return redirect()->route('product_colors.index')->with('error', 'Gagal menghapus: ' . $e->getMessage()); // Diubah
        }
    }
}
