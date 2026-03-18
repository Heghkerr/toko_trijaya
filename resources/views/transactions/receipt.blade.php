<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota #{{ $transaction->id }}</title>
    <style>
        /* Ini adalah CSS untuk print. Buat sesimpel mungkin. */
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 300px; /* Lebar standar printer thermal 80mm */
            font-size: 14px;
        }
        .header, .footer {
            text-align: center;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
        }
        .content th, .content td {
            padding: 2px 0;
        }
        .total-section table {
            width: 100%;
        }

        .total-section {
            border-top: 1px dashed black;
            margin-top: 5px;
            padding-top: 5px;
        }

        /* ATURAN BARU UNTUK 3 KOLOM */
        .total-section td {
            padding: 1px 0; /* Padding atas-bawah */
            text-align: left; /* Default semua rata kiri */
        }
        .total-section td:nth-child(2) {
            padding-right: 5px; /* Jarak antara 'Rp' dan angka */
        }
        .total-section td:last-child {
            text-align: right; /* Kolom angka dibuat rata kanan */
        }
    </style>
</head>
<body onload="window.print()"> {{-- Opsional: langsung buka dialog print --}}

    <div class="header">
        <h3>Toko Trijaya</h3>
        <p>
            Jl. Imam Bonjol no.336, Denpasar, Bali<br>
            Telp: 0361-483400
        </p>
        <p>Nota: #{{ $transaction->id }}<br>
           Kasir: {{ $transaction->user->name }}<br>
           Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}
        </p>

        {{-- INI BARIS YANG DITAMBAHKAN --}}
        @if(in_array($transaction->status, ['paid', 'finished']))
            <strong>Status: LUNAS</strong>
        @else
            <strong style="font-weight: bold; color: black;">Status: BELUM LUNAS</strong>
        @endif
        {{-- AKHIR BARIS TAMBAHAN --}}
    </div>

    <hr>

    <div class="content">
        <table>
            {{-- Loop semua item --}}
            @foreach($transaction->details as $item)
            <tr>
                <td>
                    {{ $item->product->name }} ({{ $item->product->color->name }})
                    <br>
                    {{ $item->quantity }} ({{ $item->unit_name }}) x {{ number_format($item->price, 0, ',', '.') }}
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    {{ number_format($item->subtotal, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="total-section">
        <table>
            @php
                $subtotal = $transaction->details->sum('subtotal');
            @endphp
            <tr>
                <td>Subtotal</td>
                <td>Rp</td> {{-- Kolom 2 --}}
                <td>{{ number_format($subtotal, 0, ',', '.') }}</td> {{-- Kolom 3 --}}
            </tr>
            <tr>
                <td>Diskon</td>
                <td>Rp</td>
                <td>{{ number_format($transaction->discount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total</strong></td>
                <td><strong>Rp</strong></td>
                <td><strong>{{ number_format($transaction->total_amount, 0, ',', '.') }}</strong></td>
            </tr>

            {{-- Logika Pembayaran --}}
            @if($transaction->payment_method == 'cash')
                <tr>
                    <td>Bayar (Cash)</td>
                    <td>Rp</td>
                    <td>{{ number_format($transaction->cash_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Kembalian</td>
                    <td>Rp</td>
                    <td>{{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
                </tr>
            @else
                {{-- Untuk non-cash, gabungkan 3 kolom --}}
                <tr>
                    <td colspan="3" style="text-align: left;">
                        Pembayaran via {{ $transaction->payment_method }}
                    </td>
                </tr>
            @endif

        </table>
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja!</p>
    </div>

<script>
        window.onload = function() {

            @if (session('print_confirmation'))

                try {
                    if (window.opener && window.opener.location) {
                        window.opener.location.reload();
                    }
                } catch (e) {
                    console.warn("Tidak bisa auto-refresh tab asal:", e);
                }

            @else
                // ALUR 2: Datang dari 'Cetak Nota' (Cetak Ulang)
                // Langsung print tanpa bertanya
                window.print();
            @endif
        }
    </script>


</body>
</html>
