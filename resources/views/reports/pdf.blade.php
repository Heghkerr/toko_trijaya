<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan {{ ucfirst($report_type) }} {{ $month_name }} {{ $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 14px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 20px; }
        .summary-table { width: 50%; margin-top: 10px; border-collapse: collapse; }
        .summary-table td { padding: 5px; border: none; }
        .summary-table td:first-child { font-weight: bold; width: 60%; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd;
                  font-size: 10px; text-align: right; }
        .logo { text-align: center; margin-bottom: 10px; }
        .logo img { max-width: 120px; max-height: 80px; }
        .payment-summary { margin-top: 15px; }
        .payment-summary table { width: 100%; border-collapse: collapse; }
        .payment-summary th, .payment-summary td { padding: 6px; border: 1px solid #ddd; }
        .payment-summary th { background-color: #f2f2f2; }

        /* [TAMBAHAN] Style untuk Laba */
        .text-success { color: #155724; }
    </style>

</head>
<body>
    <div class="logo" style="text-align:center; margin-bottom:10px;">
        {{-- Pastikan path ke logo Anda benar --}}
        {{-- <img src="file://{{ public_path('file/TRUE_HOME_LOGO.jpg') }}" alt="Logo" style="max-width:120px; max-height:80px;"> --}}
        <h2>Toko Trijaya</h2>
    </div>

    <div class="header">
        <h2>
            @if ($report_type === 'penjualan')
                Laporan Transaksi Bulanan
            @elseif ($report_type === 'pembelian')
                Laporan Pembelian Bulanan
            @else
                Laporan Laba Rugi Bulanan
            @endif
        </h2>
        <p>Periode: {{ $month_name }} {{ $year }}</p>
    </div>

    {{-- ====================== PEMBELIAN ====================== --}}
    @if ($report_type === 'pembelian')


        @php
            $purchaseReturnAmount = $purchase_return_amount ?? 0;
            $netPurchaseSpending = ($total_purchase_amount - $purchaseReturnAmount) + $total_delivery_cost;
        @endphp
        <div class="summary">
            <h4>Ringkasan</h4>
            <table class="summary-table">
                <tr><td>Total Pembelian:</td><td class="text-right">{{ $purchase_count }}</td></tr>
                <tr><td>Total Produk Dibeli:</td><td class="text-right">{{ $products_purchased }}</td></tr>
                <tr><td>Total Nilai Pembelian:</td><td class="text-right">Rp {{ number_format($total_purchase_amount, 0, ',', '.') }}</td></tr>
                <tr><td>Total Retur Pembelian:</td><td class="text-right text-danger">Rp {{ number_format($purchaseReturnAmount, 0, ',', '.') }}</td></tr>
                <tr><td>Total Biaya Pengiriman:</td><td class="text-right">Rp {{ number_format($total_delivery_cost, 0, ',', '.') }}</td></tr>
                <tr><td><b>Pengeluaran Bersih:</b></td><td class="text-right"><b>Rp {{ number_format($netPurchaseSpending, 0, ',', '.') }}</b></td></tr>
            </table>
        </div>

        @if(($purchase_returns ?? collect())->count() > 0)
        <div class="summary" style="margin-top:25px;">
            <h4>Detail Retur Pembelian</h4>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Retur</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Diproses Oleh</th>
                        <th class="text-right">Nilai Retur</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase_returns as $return)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $return->return_code }}</td>
                        <td>{{ optional($return->created_at)->timezone('Asia/Jakarta')->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $return->supplier->name ?? '-' }}</td>
                        <td>{{ $return->user->name ?? '-' }}</td>
                        <td class="text-right text-danger">Rp {{ number_format($return->total_amount ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $return->purchase->notes ?? ($return->notes ?? '-') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif


        {{-- tabel pembelian --}}
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Pembelian</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Kasir</th>
                    <th>Status</th>
                    <th class="text-right">Total Pembelian</th>
                    <th class="text-right">Biaya Pengiriman</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $purchase->purchase_code }}</td>
                    <td>{{ $purchase->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</td>
                    <td>{{ $purchase->supplier->name ?? '-' }}</td>
                    <td>{{ $purchase->user->name ?? '-' }}</td>
                    <td>{{ ucfirst($purchase->status) }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->delivery_cost ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->total_amount + ($purchase->delivery_cost ?? 0), 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada pembelian pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    {{-- ====================== PENJUALAN ====================== --}}
    @elseif ($report_type === 'penjualan')

        {{-- tabel transaksi --}}
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Transaksi</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Metode</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Diskon</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $transaction->transaction_code }}</td>
                    <td>{{ $transaction->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</td>
                    <td>{{ $transaction->user->name ?? '-' }}</td>
                    <td>{{ ucfirst($transaction->payment_method) }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada transaksi pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ringkasan penjualan --}}
        <div class="summary">
            <h4>Ringkasan</h4>
            <table class="summary-table">
                <tr><td>Total Transaksi:</td><td class="text-right">{{ $transaction_count }}</td></tr>
                <tr><td>Total Produk Terjual:</td><td class="text-right">{{ $transactions->sum(fn($t) => $t->details->sum('quantity')) }}</td></tr>
                <tr><td><b>Penjualan Bersih:</b></td><td class="text-right"><b>Rp {{ number_format($total_sales - ($total_refund_amount ?? 0), 0, ',', '.') }}</b></td></tr>
                <tr><td>Total Diskon:</td><td class="text-right">Rp {{ number_format($transactions->sum('discount'), 0, ',', '.') }}</td></tr>
                <tr><td>Total Refund:</td><td class="text-right text-danger">Rp {{ number_format($total_refund_amount ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td>Total Penjualan:</td><td class="text-right">Rp {{ number_format($total_sales, 0, ',', '.') }}</td></tr>
            </table>
        </div>

        {{-- rincian pembayaran --}}
        <div class="payment-summary">
            <h4>Rincian Pembayaran</h4>
            <table>
                <thead>
                    <tr>
                        <th>Metode</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (['cash' => $cash_amount, 'card' => $card_amount, 'qris' => $qris_amount] as $method => $amount)
                        <tr>
                            <td>{{ ucfirst($method) }}</td>
                            <td class="text-right">{{ $transactions->where('payment_method', $method)->count() }}</td>
                            <td class="text-right">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                            <td class="text-right">{{ $total_sales > 0 ? number_format(($amount / $total_sales) * 100, 2) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(($refunds ?? collect())->count() > 0)
        <div class="summary" style="margin-top:25px;">
            <h4>Daftar Refund</h4>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal Refund</th>
                        <th>Kasir Asal</th>
                        <th>Diproses Oleh</th>
                        <th class="text-right">Nilai Refund</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($refunds as $refund)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $refund->originalTransaction->transaction_code ?? '-' }}</td>
                        <td>{{ optional($refund->created_at)->timezone('Asia/Jakarta')->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $refund->originalTransaction->user->name ?? '-' }}</td>
                        <td>{{ $refund->user->name ?? '-' }}</td>
                        <td class="text-right text-danger">Rp {{ number_format($refund->total_refund_amount ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $refund->reason ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    {{-- ====================== LABA RUGI ====================== --}}
    @else

        <div class="summary">
            <h4>Ringkasan Laba Rugi</h4>
            <table class="summary-table">
                <tr><td>Total Penjualan</td><td class="text-right">Rp {{ number_format($total_sales, 0, ',', '.') }}</td></tr>
                <tr><td>Total Modal (Harga Beli)</td><td class="text-right">Rp {{ number_format($total_cost, 0, ',', '.') }}</td></tr>
                <tr><td>Total Refund Penjualan</td><td class="text-right text-danger">Rp {{ number_format($total_refund_amount ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td>Penjualan Bersih</td><td class="text-right">Rp {{ number_format($total_sales - ($total_refund_amount ?? 0), 0, ',', '.') }}</td></tr>
                <tr><td><b>Laba Bersih</b></td><td class="text-right"><b>Rp {{ number_format($profit, 0, ',', '.') }}</b></td></tr>
            </table>
        </div>

        {{-- [PERUBAHAN DIMULAI DI SINI] --}}
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Satuan</th> {{-- [BARU] --}}
                        <th class="text-center">Jumlah</th> {{-- [MODIFIKASI] --}}
                        <th class="text-right">Harga Modal</th> {{-- [MODIFIKASI] --}}
                        <th class="text-right">Harga Jual</th> {{-- [MODIFIKASI] --}}
                        <th class="text-right">Laba</th> {{-- [MODIFIKASI] --}}
                    </tr>
                </thead>
                <tbody>
                    {{-- Controller Anda sudah mengirim $product_reports yang benar --}}
                    @forelse($product_reports as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        {{-- [MODIFIKASI] Menampilkan nama + warna --}}
                        <td>{{ $item['name'] }} ({{ $item['color'] ? $item['color']->name : '-' }})</td>
                        {{-- [BARU] Menampilkan nama unit --}}
                        <td>{{ $item['unit_name'] ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-right">Rp {{ number_format($item['cost'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                        <td class="text-right text-success">Rp {{ number_format($item['profit'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    {{-- [MODIFIKASI] Colspan diubah menjadi 7 --}}
                    <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- [PERUBAHAN SELESAI] --}}

        @if(($refunds ?? collect())->count() > 0)
        <div class="summary" style="margin-top:25px;">
            <h4>Daftar Refund Penjualan</h4>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal Refund</th>
                        <th>Kasir Asal</th>
                        <th>Diproses Oleh</th>
                        <th class="text-right">Nilai Refund</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($refunds as $refund)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $refund->originalTransaction->transaction_code ?? '-' }}</td>
                        <td>{{ optional($refund->created_at)->timezone('Asia/Jakarta')->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $refund->originalTransaction->user->name ?? '-' }}</td>
                        <td>{{ $refund->user->name ?? '-' }}</td>
                        <td class="text-right text-danger">Rp {{ number_format($refund->total_refund_amount ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $refund->reason ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    @endif

    <div class="footer">
        <p>Dicetak pada: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
        <p>Oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
