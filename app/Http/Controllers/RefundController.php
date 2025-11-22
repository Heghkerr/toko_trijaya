<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Refund; // Pastikan Model Refund di-use

class RefundController extends Controller
{
    /**
     * Menampilkan daftar semua refund yang telah diproses.
     */
    public function index(Request $request)
    {
        // Ambil semua data refund, diurutkan dari yang terbaru
        // Kita juga ambil relasinya (originalTransaction, user) agar efisien
        $refunds = Refund::with(['originalTransaction.user', 'user', 'details'])
            ->latest() // Urutkan dari yg terbaru
            ->paginate(15); // Paginasi

        // Tampilkan view baru yang akan kita buat
        return view('refunds.index', compact('refunds'));
    }

    /**
     * Menampilkan detail satu refund (Opsional, tapi bagus untuk masa depan)
     *
     * @param  \App\Models\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function show(Refund $refund)
    {
        // Load relasi yang dibutuhkan
        $refund->load(['originalTransaction.user', 'user', 'details.productUnit.product']);

        return view('refunds.show', compact('refund'));
    }
}
