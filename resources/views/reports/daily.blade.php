@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Laporan</h6>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('reports.daily') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm"
                        name="start_date"
                        value="{{ request('start_date') ?? date('Y-m-d') }}" required>
                </div>

                {{-- Tanggal Sampai --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm"
                        name="end_date"
                        value="{{ request('end_date') ?? date('Y-m-d') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Kasir</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua Kasir</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipe Laporan</label>
                    <select name="type" class="form-select">
                        <option value="sales" {{ request('type') == 'sales' ? 'selected' : '' }}>Laporan Penjualan</option>
                        @if(auth()->user()->role == 'owner')
                            <option value="profit" {{ request('type') == 'profit' ? 'selected' : '' }}>Laporan Laba Rugi</option>
                            <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Laporan Pembelian</option>
                        @endif
                    </select>
                </div>

                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.daily') }}" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        @if(request()->filled('start_date'))
        <div class="row mb-4">
            <!-- Ringkasan -->
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            @if(request('type') == 'profit')
                                Ringkasan Laba Rugi
                            @elseif(request('type') == 'purchase')
                                Ringkasan Pembelian
                            @else
                                Ringkasan Pendapatan
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">

                            @if(request('type') == 'purchase')
                                {{-- Laporan Pembelian --}}
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Komponen</th>
                                            <th class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Total Pembelian</td>
                                            <td class="text-end">Rp {{ number_format($total_purchase_amount ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Biaya Pengiriman</td>
                                            <td class="text-end">Rp {{ number_format($total_delivery_cost ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="table-info">
                                            <th>Total Pengeluaran</th>
                                            <th class="text-end">Rp {{ number_format(($total_purchase_amount ?? 0) + ($total_delivery_cost ?? 0), 0, ',', '.') }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            @elseif(request('type') == 'profit')
                                {{-- Laporan Laba Rugi --}}
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Komponen</th>
                                            <th class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Total Penjualan</td>
                                            <td class="text-end">Rp {{ number_format($total_sales, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Modal (Harga Beli)</td>
                                            <td class="text-end">Rp {{ number_format($total_cost ?? 0, 0, ',', '.') }}</td>
                                        </tr>

                                        <tr class="table-success">
                                            <th>Laba Bersih (Setelah Refund)</th>
                                            <th class="text-end">Rp {{ number_format($profit ?? 0, 0, ',', '.') }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                {{-- Laporan Penjualan --}}
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Komponen</th>
                                            <th class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Total Transaksi Cash</td>
                                            <td class="text-end">Rp {{ number_format($cash_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Transaksi Non-Cash (Card + QRIS)</td>
                                            <td class="text-end">Rp {{ number_format($card_amount + $qris_amount, 0, ',', '.') }}</td>
                                        </tr>

                                        <tr class="table-success">
                                            <th>Total Pendapatan Bersih</th>
                                            <th class="text-end">Rp {{ number_format($total_sales, 0, ',', '.') }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            @if(request('type') == 'profit')
                                Statistik Laba
                            @elseif(request('type') == 'purchase')
                                Statistik Pembelian
                            @else
                                Statistik Penjualan
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(request('type') == 'purchase')
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Total Pembelian</td>
                                            <td class="text-end">{{ $purchase_count ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td>Produk Dibeli</td>
                                            <td class="text-end">{{ $products_purchased ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Nilai Pembelian</td>
                                            <td class="text-end">Rp {{ number_format($total_purchase_amount ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Biaya Pengiriman</td>
                                            <td class="text-end">Rp {{ number_format($total_delivery_cost ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="table-info">
                                            <th>Total Pengeluaran</th>
                                            <th class="text-end text-info">Rp {{ number_format(($total_purchase_amount ?? 0) + ($total_delivery_cost ?? 0), 0, ',', '.') }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @elseif(request('type') == 'profit')
                            @php
                                $profit_margin = $total_sales > 0 ? ($profit / $total_sales) * 100 : 0;
                                $avg_profit = $products_sold > 0 ? $profit / $products_sold : 0;
                            @endphp

                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Total Penjualan</td>
                                            <td class="text-end">Rp {{ number_format($total_sales ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Modal</td>
                                            <td class="text-end">Rp {{ number_format($total_cost ?? 0, 0, ',', '.') }}</td>
                                        </tr>

                                        <tr>
                                            <td>Margin Laba</td>
                                            <td class="text-end">{{ number_format($profit_margin, 0, ',', '.') }}%</td>
                                        </tr>
                                        <tr>
                                            <td>Rata-rata Laba / Transaksi</td>
                                            <td class="text-end">Rp {{ number_format($avg_profit, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="table-success">
                                            <th>Laba Bersih</th>
                                            <th class="text-end text-success">Rp {{ number_format($profit ?? 0, 0, ',', '.') }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Total Transaksi</td>
                                            <td class="text-end">{{ $transaction_count }}</td>
                                        </tr>
                                        <tr>
                                            <td>Produk Terjual</td>
                                            <td class="text-end">{{ $products_sold }}</td>
                                        </tr>
                                        <tr>
                                            <td>Cash</td>
                                            <td class="text-end">Rp {{ number_format($cash_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Card</td>
                                            <td class="text-end">Rp {{ number_format($card_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>QRIS</td>
                                            <td class="text-end">Rp {{ number_format($qris_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Refund</td>
                                            <td class="text-end text-danger">Rp {{ number_format($total_refund_amount ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="table-success">
                                            <th>Total Pendapatan</th>
                                            <th class="text-end text-success">Rp {{ number_format($total_sales, 0, ',', '.') }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- Info ringkasan --}}
        <div class="alert alert-info">
            <strong>Periode:</strong> {{ $display_date }} |
            <strong>Kasir:</strong>
            {{ request('user_id') ? ($users->firstWhere('id', request('user_id'))->name ?? '—') : 'Semua' }} |
            @if(request('type') == 'purchase')
                <strong>Total Pembelian:</strong> {{ $purchase_count ?? 0 }} |
                <strong>Total Pengeluaran:</strong> Rp {{ number_format(($total_purchase_amount ?? 0) + ($total_delivery_cost ?? 0), 0, ',', '.') }}
            @else
                <strong>Total Transaksi:</strong> {{ $transaction_count ?? 0 }} |
                <strong>Total Refund:</strong> Rp {{ number_format($total_refund_amount ?? 0, 0, ',', '.') }} |
                <strong>Total Penjualan:</strong> Rp {{ number_format($total_sales ?? 0, 0, ',', '.') }}
            @endif
        </div>

        {{-- Tabel Utama --}}
        <div class="table-responsive">
            @if(request('type') == 'purchase')
                {{-- Mode Pembelian: tampilkan per-pembelian --}}
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Pembelian</th>
                            <th>Waktu</th>
                            <th>Supplier</th>
                            <th>Kasir</th>
                            <th>Status</th>
                            <th>Total Pembelian</th>
                            <th>Biaya Pengiriman</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases ?? [] as $purchase)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="text-primary">
                                    {{ $purchase->purchase_code }}
                                </a>
                            </td>
                            <td>{{ $purchase->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $purchase->supplier->name ?? '—' }}</td>
                            <td>{{ $purchase->user->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $purchase->status == 'completed' ? 'success' : ($purchase->status == 'pending' ? 'warning' : 'secondary') }}">
                                    {{ strtoupper($purchase->status) }}
                                </span>
                            </td>
                            <td class="text-end">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($purchase->delivery_cost ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($purchase->total_amount + ($purchase->delivery_cost ?? 0), 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">Tidak ada data pembelian</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif(request('type') == 'profit')
                {{-- Mode Laba Rugi: tampilkan per-produk --}}
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Satuan</th>
                            <th>Jumlah Terjual</th>
                            <th>Harga Modal</th>
                            <th>Harga Jual</th>
                            <th>Laba</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product_reports as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['name'] }} ({{ $item['color'] ? $item['color']->name : '-' }})</td>
                            <td>{{ $item['unit_name'] ?? 'N/A' }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td >Rp {{ number_format($item['cost'], 0, ',', '.') }}</td>
                            <td >Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                            <td >Rp {{ number_format($item['profit'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                {{-- Mode Penjualan: tampilkan per-transaksi --}}
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th>Metode Bayar</th>
                            <th>Subtotal</th>
                            <th>Diskon</th>
                            <th>Total</th>
                            <th>Refund</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        @php
                            $refundAmount = $transaction->refunds->sum('total_refund_amount');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('reports.daily.transaction', $transaction->id) }}" class="text-primary">
                                    {{ $transaction->transaction_code }}
                                </a>
                            </td>
                            <td>{{ $transaction->created_at->format('H:i:s') }}</td>
                            <td>{{ $transaction->user->name ?? '—' }}</td>
                            <td>{{ strtoupper($transaction->payment_method) }}</td>
                            <td class="text-end">Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                            <td class="text-end {{ $refundAmount > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                Rp {{ number_format($refundAmount, 0, ',', '.') }}
                                @if($refundAmount > 0)
                                    <span class="badge bg-danger ms-2">Refund</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">Tidak ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if(request('type') != 'purchase' && isset($refunds) && $refunds->isNotEmpty())
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">Daftar Refund</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Transaksi</th>
                                <th>Waktu Refund</th>
                                <th>Kasir Transaksi</th>
                                <th>Diproses Oleh</th>
                                <th>Total Refund</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($refunds as $refund)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($refund->originalTransaction)
                                        <a href="{{ route('reports.daily.transaction', $refund->originalTransaction->id) }}" class="text-danger">
                                            {{ $refund->originalTransaction->transaction_code }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ optional($refund->created_at)->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>{{ $refund->originalTransaction->user->name ?? '—' }}</td>
                                <td>{{ $refund->user->name ?? '—' }}</td>
                                <td class="text-end text-danger fw-semibold">Rp {{ number_format($refund->total_refund_amount ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $refund->reason ?? '—' }}</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

{{--
        Tombol Download
        <div class="mt-3">
            <a href="{{ route('reports.daily.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>
        </div> --}}
        @endif
    </div>
</div>
@endsection
