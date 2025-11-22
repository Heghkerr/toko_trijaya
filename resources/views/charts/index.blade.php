@extends('layouts.app')

@section('title', 'Grafik Penjualan')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Grafik Penjualan Produk</h6>
        <div class="btn-group">
            <button id="exportChartBtn" class="btn btn-success btn-sm">
                <i class="bi bi-download me-1"></i> Ekspor
            </button>
            <button type="button" id="resetZoomBtn" class="btn btn-outline-secondary btn-sm ms-1">
                <i class="bi bi-zoom-out me-1"></i> Reset Zoom
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('charts.index') }}" class="mb-4">
            {{-- Filter Anda (Bulan, Tahun, Tombol) --}}
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Bulan</label>
                    <select class="form-select form-select-sm" name="month">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Tahun</label>
                    <select class="form-select form-select-sm" name="year">
                        @foreach($years as $yr)
                            <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('charts.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
                <div class="col-md-3 text-end">
                    <small class="text-muted">Gunakan scroll mouse untuk zoom</small>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-8">
                {{-- Container untuk Chart.js --}}
                <div class="chart-container" style="position: relative; height:500px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                {{-- Tabel Ringkasan dan Paginasi --}}
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 font-weight-bold">Ringkasan Penjualan (Halaman {{ $salesData->currentPage() }})</h6>
                    </div>
                    <div class="card-body p-0" style="display: flex; flex-direction: column; height: 500px; max-height: 500px;">
                        <div class="table-responsive" style="flex-grow: 1; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th class="small">Produk</th>
                                        <th class="small text-end">Terjual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- [PERBAIKAN] Membaca $item->product_name --}}
                                    @forelse($salesData as $item)
                                    <tr>
                                        <td class="small">
                                            {{ $item->product_name }} ({{ $item->color_name ?? '-' }}) - <strong>{{ $item->unit_name }}</strong>
                                        </td>
                                        <td class="small text-end">{{ number_format($item->sold) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center small text-muted">Tidak ada data penjualan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light" style="position: sticky; bottom: 0; z-index: 1;">
                                    <tr>
                                        <th class="small">Total Halaman Ini</th>
                                        <th class="small text-end">{{ number_format($chartData['data']->sum()) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="card-footer bg-light p-2 d-flex justify-content-center">
                            {{ $salesData->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('styles')
{{-- Styles Anda sudah benar --}}
<style>
    .chart-container { background-color: #fff; border-radius: 8px; padding: 15px; border: 1px solid #e3e6f0; }
    #salesChart { background-color: white; border-radius: 6px; }
    .table-sm td, .table-sm th { padding: 0.5rem; }
    .form-select-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .card-header { padding: 0.75rem 1.25rem; }
    .pagination { margin-bottom: 0; }
    .page-link { font-size: 0.875rem; padding: 0.25rem 0.5rem; }
</style>
@endpush

@push('scripts')
{{-- Scripts Anda sudah benar --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const combinedLabels = @json($chartData['labels']);
        const chartDataValues = @json($chartData['data']);

        const chartData = {
            labels: combinedLabels,
            datasets: [{
                label: 'Jumlah Terjual',
                data: chartDataValues,
                backgroundColor: [
                    'rgba(78, 115, 223, 0.7)', 'rgba(54, 185, 204, 0.7)',
                    'rgba(28, 200, 138, 0.7)', 'rgba(246, 194, 62, 0.7)',
                    'rgba(231, 74, 59, 0.7)', 'rgba(142, 141, 145, 0.7)',
                    'rgba(133, 135, 150, 0.7)', 'rgba(108, 117, 125, 0.7)',
                    'rgba(253, 126, 20, 0.7)', 'rgba(111, 66, 193, 0.7)'
                ],
                borderColor: [
                    'rgba(78, 115, 223, 1)', 'rgba(54, 185, 204, 1)',
                    'rgba(28, 200, 138, 1)', 'rgba(246, 194, 62, 1)',
                    'rgba(231, 74, 59, 1)', 'rgba(142, 141, 145, 1)',
                    'rgba(133, 135, 150, 1)', 'rgba(108, 117, 125, 1)',
                    'rgba(253, 126, 20, 1)', 'rgba(111, 66, 193, 1)'
                ],
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: [
                    'rgba(78, 115, 223, 1)', 'rgba(54, 185, 204, 1)',
                    'rgba(28, 200, 138, 1)', 'rgba(246, 194, 62, 1)',
                    'rgba(231, 74, 59, 1)', 'rgba(142, 141, 145, 1)',
                    'rgba(133, 135, 150, 1)', 'rgba(108, 117, 125, 1)',
                    'rgba(253, 126, 20, 1)', 'rgba(111, 66, 193, 1)'
                ],
            }]
        };

        const config = {
            type: 'bar',
            data: chartData,
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.raw.toLocaleString()}`;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: (value) => value > 0 ? value.toLocaleString() : '',
                        font: { weight: 'bold', size: 10 }
                    },
                    zoom: {
                        zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'xy' },
                        pan: { enabled: true, mode: 'xy' },
                        limits: { x: {min: 'original', max: 'original'}, y: {min: 'original', max: 'original'} }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold', size: 12 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            precision: 0,
                            callback: function(value) { return value.toLocaleString(); }
                        }
                    }
                },
                animation: { duration: 1000, easing: 'easeOutQuart' },
                interaction: { intersect: false, mode: 'index' }
            }
        };

        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, config);

        document.getElementById('resetZoomBtn').addEventListener('click', function() {
            salesChart.resetZoom();
        });

        document.getElementById('exportChartBtn').addEventListener('click', function() {
            const link = document.createElement('a');
            link.download = `grafik-penjualan-${new Date().toISOString().slice(0,10)}.png`;
            link.href = salesChart.toBase64Image('image/png', 1);
            link.click();
        });
    });
</script>
@endpush
@endsection
