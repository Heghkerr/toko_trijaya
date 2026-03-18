<?php

namespace App\Http\Controllers;

use App\Models\WhatsappOrder;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsappOrderController extends Controller
{
    /**
     * Tampilkan daftar pesanan WhatsApp.
     */
    public function index()
    {
        $orders = WhatsappOrder::with(['orderItems.product', 'orderItems.color', 'orderItems.productUnit', 'transaction'])
            ->latest()
            ->paginate(20);
        return view('whatsapp.index', compact('orders'));
    }

    /**
     * Proses pesanan WhatsApp dan buat transaksi otomatis
     */
    public function process($id)
    {
        DB::beginTransaction();

        try {
            $order = WhatsappOrder::with(['orderItems.product', 'orderItems.color', 'orderItems.productUnit'])
                ->findOrFail($id);

            // Cek apakah pesanan sudah diproses
            if (!in_array($order->status, ['pending', 'confirmed'])) {
                return redirect()->route('whatsapp.orders.index')
                    ->with('error', 'Pesanan ini sudah diproses atau dibatalkan sebelumnya.');
            }

            // Validasi: pastikan ada order items
            if ($order->orderItems->isEmpty()) {
                return redirect()->route('whatsapp.orders.index')
                    ->with('error', 'Pesanan tidak memiliki detail item. Tidak bisa diproses.');
            }

            // Cari atau buat customer berdasarkan PHONE + NAME (unique combination)
            // Jadi 1 nomor HP bisa punya multiple customers dengan nama berbeda
            $customerName = strtoupper($order->name) . ' (WhatsApp)';
            $customer = Customer::firstOrCreate(
                [
                    'phone' => $order->phone,
                    'name' => $customerName  // Phone + Name sebagai unique identifier
                ]
            );

            Log::info('Customer resolved for WhatsApp order', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'phone' => $order->phone,
                'was_new' => $customer->wasRecentlyCreated
            ]);

            // Calculate total amount
            $totalAmount = 0;
            $transactionDetails = [];

            foreach ($order->orderItems as $item) {
                // Pastikan product unit ada
                if (!$item->productUnit) {
                    DB::rollBack();
                    return redirect()->route('whatsapp.orders.index')
                        ->with('error', 'Pesanan memiliki item tanpa satuan. Tidak bisa diproses.');
                }

                // Cek stok tersedia
                if ($item->productUnit->stock < $item->quantity) {
                    DB::rollBack();
                    return redirect()->route('whatsapp.orders.index')
                        ->with('error', "Stok tidak mencukupi untuk {$item->product->name} ({$item->productUnit->name}). " .
                                       "Tersedia: {$item->productUnit->stock}, Diminta: {$item->quantity}");
                }

                $subtotal = $item->productUnit->price * $item->quantity;
                $totalAmount += $subtotal;

                $transactionDetails[] = [
                    'product_id' => $item->product_id,
                    'unit_name' => $item->productUnit->name,
                    'quantity' => $item->quantity,
                    'price' => $item->productUnit->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'customer_id' => $customer->id,
                'transaction_code' => 'TRX-' . time(),
                'total_amount' => $totalAmount,
                'discount' => 0, // No discount
                'payment_method' => 'qris', // QRIS
                'cash_amount' => 0,
                'change_amount' => 0,
                'status' => 'unpaid', // Unpaid status (belum bayar)
                'whatsapp_order_id' => $order->id, // Link to WhatsApp order
            ]);

            // Create transaction details
            foreach ($transactionDetails as $detail) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product_id'],
                    'unit_name' => $detail['unit_name'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }

            // Update WhatsApp order status to confirmed (bukan processed)
            // Status akan jadi 'processed' setelah transaksi dibayar
            $order->update(['status' => 'confirmed']);

            Log::info('WhatsApp order processed and transaction created', [
                'whatsapp_order_id' => $order->id,
                'transaction_id' => $transaction->id,
                'customer_name' => $customerName,
                'total_amount' => $totalAmount
            ]);

            DB::commit();

            // Redirect to transaction detail (show) instead of index
            return redirect()->route('transactions.show', $transaction->id)
                ->with('success', "Transaksi berhasil dibuat dari Order #{$order->id}! Status Order: CONFIRMED. Klik 'Bayar' setelah customer bayar untuk mengubah status menjadi PROCESSED.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing WhatsApp order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('whatsapp.orders.index')
                ->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan pesanan WhatsApp
     */
    public function cancel($id)
    {
        try {
            $order = WhatsappOrder::findOrFail($id);

            // Cek apakah pesanan bisa dibatalkan (pending atau confirmed)
            if (!in_array($order->status, ['pending', 'confirmed'])) {
                return redirect()->route('whatsapp.orders.index')
                    ->with('error', 'Hanya pesanan dengan status pending atau dikonfirmasi yang bisa dibatalkan.');
            }

            // Update status menjadi cancelled
            $order->update(['status' => 'cancelled']);

            return redirect()->route('whatsapp.orders.index')
                ->with('success', 'Pesanan berhasil dibatalkan.');

        } catch (\Exception $e) {
            Log::error('Error cancelling WhatsApp order: ' . $e->getMessage());
            return redirect()->route('whatsapp.orders.index')
                ->with('error', 'Terjadi kesalahan saat membatalkan pesanan.');
        }
    }
}

