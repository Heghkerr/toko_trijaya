@extends('layouts.app')

@section('title', 'Unduh Laporan')

@section('content')
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-download me-2"></i>Unduh Laporan Bulanan
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.download') }}" method="POST">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Bulan</label>
                    <select class="form-select" name="month" required>
                        @foreach([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember'
                        ] as $num => $name)
                            <option value="{{ $num }}" {{ $num == date('n') ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Tahun</label>
                    <select class="form-select" name="year" required>
                        @for($year = date('Y'); $year >= 2020; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipe Laporan</label>
                    <select name="report_type" class="form-select">
                        <option value="penjualan" {{ request('report_type') == 'penjualan' ? 'selected' : '' }}>Laporan Penjualan</option>
                        <option value="laba_rugi" {{ request('report_type') == 'laba_rugi' ? 'selected' : '' }}>Laporan Laba Rugi</option>
                        <option value="pembelian" {{ request('report_type') == 'pembelian' ? 'selected' : '' }}>Laporan Pembelian</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Unduh Laporan
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-4">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Laporan bulanan akan mencakup semua transaksi pada bulan dan tahun yang dipilih dalam format PDF.
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .form-select {
        font-size: 0.875rem;
    }
    .alert {
        font-size: 0.875rem;
    }
</style>
@endsection
