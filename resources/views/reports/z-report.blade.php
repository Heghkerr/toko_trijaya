@extends('layouts.app')

@section('title', 'Z Report')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            Laporan Z - {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
        </h6>
        <div>
            <a href="{{ route('reports.x') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('reports.z', ['download' => true]) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-download"></i> Unduh PDF
            </a>
        </div>
    </div>
    <div class="card-body">

        <div class="row mb-4">
            <!-- Ringkasan Penjualan -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ringkasan Penjualan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Transaksi
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $transaction_count }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Produk Terjual
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($products_sold, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Diskon
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp {{ number_format($total_discount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Pendapatan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp {{ number_format($total_sales, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Metode Pembayaran -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Metode Pembayaran</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Pembayaran -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Pembayaran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Metode</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Cash</td>
                                <td class="text-end">Rp {{ number_format($cash_amount, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $total_sales > 0 ? number_format(($cash_amount/$total_sales)*100, 2) : 0 }}%</td>
                            </tr>
                            <tr>
                                <td>Card</td>
                                <td class="text-end">Rp {{ number_format($card_amount, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $total_sales > 0 ? number_format(($card_amount/$total_sales)*100, 2) : 0 }}%</td>
                            </tr>
                            <tr>
                                <td>QRIS</td>
                                <td class="text-end">Rp {{ number_format($qris_amount, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $total_sales > 0 ? number_format(($qris_amount/$total_sales)*100, 2) : 0 }}%</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th class="text-end">Rp {{ number_format($total_sales, 0, ',', '.') }}</th>
                                <th class="text-end">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('paymentChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'Card', 'QRIS'],
                datasets: [{
                    data: [{{ $cash_amount }}, {{ $card_amount }}, {{ $qris_amount }}],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
                cutout: '70%',
                maintainAspectRatio: false,
            }
        });
    });
</script>
@endpush
@endsection
