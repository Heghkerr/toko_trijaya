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
    </style>

</head>
<body>
    <div class="logo" style="text-align:center; margin-bottom:10px;">
        <img src="file://{{ public_path('file/TRUE_HOME_LOGO.jpg') }}" alt="Logo" style="max-width:120px; max-height:80px;">
        <h2>Toko Trijaya</h2>
    </div>

    <div class="header">
        <h2>
            @if ($report_type === 'penjualan')
                Laporan Transaksi Bulanan
            @else
                Laporan Laba Rugi Bulanan
            @endif
        </h2>
        <p>Periode: {{ $month_name }} {{ $year }}</p>
    </div>

    {{-- ====================== PENJUALAN ====================== --}}
    @if ($report_type === 'penjualan')

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
                <tr><td>Total Penjualan:</td><td class="text-right">Rp {{ number_format($total_sales, 0, ',', '.') }}</td></tr>
                <tr><td>Total Diskon:</td><td class="text-right">Rp {{ number_format($transactions->sum('discount'), 0, ',', '.') }}</td></tr>
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

    {{-- ====================== LABA RUGI ====================== --}}
    @else

        <div class="summary">
            <h4>Ringkasan Laba Rugi</h4>
            <table class="summary-table">
                <tr><td>Total Penjualan</td><td class="text-right">Rp {{ number_format($total_sales, 0, ',', '.') }}</td></tr>
                <tr><td>Total Pembelian</td><td class="text-right">Rp {{ number_format($total_purchases, 0, ',', '.') }}</td></tr>
                <tr><td>Total Pengeluaran</td><td class="text-right">Rp {{ number_format($total_expenses, 0, ',', '.') }}</td></tr>
                <tr><td><b>Laba Bersih</b></td><td class="text-right"><b>Rp {{ number_format($laba_rugi, 0, ',', '.') }}</b></td></tr>
            </table>
        </div>

    @endif

    <div class="footer">
        <p>Dicetak pada: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
        <p>Oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
