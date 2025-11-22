<!DOCTYPE html>
<html>
<head>
    <title>Z Report - {{ $date }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 14px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
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
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .table-active { background-color: rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="logo " style="text-align:center; margin-bottom:10px;">
        <img src="file://{{ public_path('file/TRUE_HOME_LOGO.jpg') }}"
            alt="Trijaya Logo"
            style="max-width:120px; max-height:80px;">
        <h2>Toko Trijaya</h2>
    </div>

    <div class="header">
        <h2>Laporan Harian</h2>
        <p>Tanggal: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="30%">Dibuat Oleh</td>
                <td>: {{ $user }}</td>
            </tr>
            <tr>
                <td>Waktu Cetak</td>
                <td>: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <h4>Ringkasan Penjualan</h4>
        <table class="summary-table">
            <tr>
                <td>Total Transaksi</td>
                <td class="text-right">{{ $transaction_count }}</td>
            </tr>
            <tr>
                <td>Total Produk Terjual</td>
                <td class="text-right">{{ number_format($products_sold, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Diskon</td>
                <td class="text-right">Rp {{ number_format($total_discount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total Pendapatan Bersih</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($total_sales, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="payment-summary">
        <h4>Rincian Pembayaran</h4>
        <table>
            <thead>
                <tr>
                    <th>Metode Pembayaran</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cash</td>
                    <td class="text-right">Rp {{ number_format($cash_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $total_sales > 0 ? number_format(($cash_amount/$total_sales)*100, 2) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Card</td>
                    <td class="text-right">Rp {{ number_format($card_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $total_sales > 0 ? number_format(($card_amount/$total_sales)*100, 2) : 0 }}%</td>
                </tr>
                <tr>
                    <td>QRIS</td>
                    <td class="text-right">Rp {{ number_format($qris_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $total_sales > 0 ? number_format(($qris_amount/$total_sales)*100, 2) : 0 }}%</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th class="text-right">Rp {{ number_format($total_sales, 0, ',', '.') }}</th>
                    <th class="text-right">100%</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <p>Dicetak oleh: {{ $user }} pada {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
