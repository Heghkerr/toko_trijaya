@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')


{{-- === BAGIAN BARU: ALERT STOK MENIPIS (Hanya muncul jika ada data) === --}}
@if(in_array(auth()->user()->role, ['owner', 'admin']))
    @if(isset($low_stock_products) && count($low_stock_products) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <i class="fas fa-exclamation-triangle me-2"></i> Peringatan Stok Menipis
                            </div>
                            <div class="h5 mb-2 font-weight-bold text-gray-800">
                                Ditemukan {{ count($low_stock_products) }} produk di bawah batas aman!
                            </div>
                            {{-- List Produk Yang Habis (Horizontal) --}}
                            <div>
                                @foreach($low_stock_products as $product)
                                    <span class="badge bg-danger text-white mr-2 mb-1 p-2">
                                        {{ $product->name }} - {{  ($product->color->name ?? '')}}
                                        (Sisa: {{ $product->current_global_stock }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($over_stock_products) && count($over_stock_products) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <i class="fas fa-arrow-up me-2"></i> Info Stok Berlebih (Overstock)
                            </div>
                            <div class="h5 mb-2 font-weight-bold text-gray-800">
                                Ada {{ count($over_stock_products) }} produk menumpuk di gudang.
                            </div>
                            <div>
                                @foreach($over_stock_products as $product)
                                    {{-- Text-dark agar tulisan di background kuning terbaca jelas --}}
                                    <span class="badge bg-warning text-dark mr-2 mb-1 p-2">
                                        {{ $product->name }} - {{  ($product->color->name ?? '')}}
                                        (Total: {{ $product->current_global_stock }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif
{{-- ================================================================= --}}

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Statistik Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Transaksi</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $today_transactions }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Pendapatan</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp {{ number_format($today_income, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Metode Pembayaran</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentChart" height="160"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Ringkasan Keuangan Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-4">
                        <thead class="table-light">
                            <tr>
                                <th>Komponen</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Transaksi Cash</td>
                                <td class="text-end">Rp {{ number_format($payment_methods['cash'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Total Transaksi Non-Cash (Card + QRIS)</td>
                                <td class="text-end">Rp {{ number_format($non_cash_income, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-success">
                                <th>Total Pendapatan Harian</th>
                                <th class="text-end">
                                    Rp {{ number_format($today_income, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>5 Produk Terlaris Hari Ini</h5>
                <div class="badge bg-danger">
                    Total Diskon: Rp {{ number_format($total_discount, 0, ',', '.') }}
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Nama Produk</th>
                                <th width="15%" class="text-end">Harga Normal</th>
                                <th width="15%" class="text-end">Harga Rata-Rata</th>
                                <th width="10%" class="text-end">Terjual</th>
                                <th width="20%" class="text-end">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($best_sellers as $index => $product)
                                <tr>
                                    <td>{{  $index + 1 }}</td>
                                    <td>
                                        <span class="product-name
                                            @if(isset($product['stock_status']))
                                                @if($product['stock_status'] === 'understock') text-danger fw-bold
                                                @elseif($product['stock_status'] === 'overstock') text-warning fw-bold
                                                @endif
                                            @endif">
                                            {{ $product['name'] }}
                                        </span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($product['price'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($product['average_price'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ $product['sold'] }}</td>
                                    <td class="text-end">Rp {{ number_format($product['total_revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-active">
                                <td colspan="5" class="text-end"><strong>Subtotal Produk Terlaris</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($best_sellers->sum('total_revenue'), 0, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Rekonsiliasi Pendapatan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Komponen</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Pendapatan Kotor (Sebelum Diskon)</td>
                                <td class="text-end">Rp {{ number_format($gross_income ?? ($today_income + $total_discount), 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Total Diskon</td>
                                <td class="text-end text-danger">- Rp {{ number_format($total_discount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Total Refund</td>
                                <td class="text-end text-danger">- Rp {{ number_format($today_refund ?? 0, 0, ',', '.') }}</td>
                            </tr>

                            <tr class="table-success">
                                <th>Total Pendapatan Bersih (Setelah Diskon & Refund)</th>
                                <th class="text-end">Rp {{ number_format($net_income ?? ($today_income - ($today_refund ?? 0)), 0, ',', '.') }}</th>
                            </tr>
                        </tbody>
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
                data: [
                    {{ $payment_methods['cash'] }},
                    {{ $payment_methods['card'] }},
                    {{ $payment_methods['qris'] }}
                ],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) label += ': ';
                            label += 'Rp ' + context.raw.toLocaleString('id-ID');
                            return label;
                        }
                    }
                }
            },
            cutout: '70%',
        },
    });
});
</script>
@endpush

@section('styles')
<style>
    .table-light { background-color: #f8f9fa; }
    .table-active { background-color: rgba(0,0,0,0.05); }
    .table-success { background-color: rgba(40,167,69,0.1); }
    .text-success { color: #28a745 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-warning { color: #ffc107 !important; }
    .card-header { border-bottom: 1px solid rgba(0,0,0,0.125); }
    .shadow { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important; }
    .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
    .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
    /* STYLE BARU UNTUK ALERT */
    .border-left-danger { border-left: 0.25rem solid #dc3545 !important; }
    .border-left-warning { border-left: 0.25rem solid #ffc107 !important; }
    .text-xs { font-size: 0.7rem; }
    .text-uppercase { letter-spacing: 0.1em; }
    /* Style untuk product name dengan warna */
    .product-name.text-danger {
        color: #dc3545 !important;
        font-weight: 600;
    }
    .product-name.text-warning {
        color: #f39c12 !important;
        font-weight: 600;
    }

    /* Mobile Responsive Improvements */
    @media (max-width: 768px) {
        .card-header h5,
        .card-header .h5 {
            font-size: 0.95rem;
        }

        .card-header .badge {
            font-size: 0.75rem;
            padding: 0.3rem 0.5rem;
        }

        .table-responsive {
            border: none;
            -webkit-overflow-scrolling: touch;
        }

        .table td,
        .table th {
            font-size: 0.8rem;
            padding: 0.5rem 0.4rem;
        }

        /* Badge dalam alert */
        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
            margin: 0.2rem;
            display: inline-block;
        }

        /* Stat cards */
        .border-left-primary,
        .border-left-success,
        .border-left-danger,
        .border-left-warning {
            border-left-width: 0.2rem !important;
        }

        .card.shadow {
            margin-bottom: 0.75rem;
        }

        .h5.mb-0 {
            font-size: 0.9rem !important;
        }

        /* Chart container */
        .card-body canvas {
            max-height: 180px !important;
        }

        /* Alert heading */
        .alert .h5 {
            font-size: 0.9rem;
        }

        .alert .text-xs {
            font-size: 0.65rem;
        }
    }

    @media (max-width: 480px) {
        .card-header h5 {
            font-size: 0.85rem;
        }

        .table td,
        .table th {
            font-size: 0.7rem;
            padding: 0.4rem 0.3rem;
        }

        .h5 {
            font-size: 0.85rem !important;
        }

        .text-xs {
            font-size: 0.6rem;
        }

        .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.35rem;
        }
    }
</style>
@endsection
@endsection
