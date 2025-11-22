<?php

namespace App\Http\Controllers;

use App\Models\Supplier; // [DIGANTI]
use App\Models\Purchase; // [DITAMBAHKAN] Untuk relasi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierController extends Controller
{

    public function index(Request $request)
    {

        $query = Supplier::query();

        $suppliers = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Menyimpan supplier baru.
     */
    public function store(Request $request)
    {
        // [DIGANTI] Validasi
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name', // [DIGANTI]
            'phone' => 'nullable|string|max:20', // [DITAMBAHKAN]
        ]);

        try {
            // [DIGANTI]
            $supplier = Supplier::create([
                'name' => Str::upper($validated['name']),
                'phone' => $validated['phone'] ?? null, // [DITAMBAHKAN]
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Supplier baru berhasil ditambahkan.',
                    'supplier' => $supplier,
                ], 201);
            }

            return redirect()->route('suppliers.index')->with('success', 'Supplier baru berhasil ditambahkan.'); // [DIGANTI]
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal menyimpan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Supplier $supplier) // [DIGANTI]
    {
        return view('suppliers.edit', compact('supplier')); // [DIGANTI]
    }

    /**
     * Memperbarui supplier.
     */
    public function update(Request $request, Supplier $supplier) // [DIGANTI]
    {
        // [DIGANTI] Validasi
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id, // [DIGANTI]
            'phone' => 'nullable|string|max:20', // [DITAMBAHKAN]
        ]);

        try {
            // [DIGANTI]
            $supplier->update([
                'name' => Str::upper($validated['name']),
                'phone' => $validated['phone'] ?? null, // [DITAMBAHKAN]
            ]);
            return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.'); // [DIGANTI]
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }


    public function destroy(Supplier $supplier) // [DIGANTI]
    {
        // [DIGANTI] Pengecekan keamanan: Jangan hapus jika masih dipakai
        if ($supplier->purchases()->count() > 0) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Gagal menghapus! Supplier ini masih digunakan oleh data Pembelian.'); // [DIGANTI]
        }

        try {
            $supplier->delete(); // [DIGANTI]
            return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.'); // [DIGANTI]
        } catch (\Exception $e) {
            return redirect()->route('suppliers.index')->with('error', 'Gagal menghapus: ' . $e->getMessage()); // [DIGANTI]
        }
    }
}
