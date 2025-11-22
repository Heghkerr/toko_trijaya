<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        $customers = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        return view('customer.index', compact('customers'));
    }

    /**
     * Menyimpan customer baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        try {
            Customer::create([
                'name' => Str::upper($validated['name']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
            return redirect()->route('customers.index')->with('success', 'Customer baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Customer $customer)
    {
        return view('customer.edit', compact('customer'));
    }

    /**
     * Memperbarui customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        try {
            $customer->update([
                'name' => Str::upper($validated['name']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
            return redirect()->route('customers.index')->with('success', 'Customer berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Customer $customer)
    {
        // Pengecekan keamanan: Jangan hapus jika masih dipakai
        if ($customer->transactions()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Gagal menghapus! Customer ini masih digunakan oleh data Transaksi.');
        }

        try {
            $customer->delete();
            return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}

