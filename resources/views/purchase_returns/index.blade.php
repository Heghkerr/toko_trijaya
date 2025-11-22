@extends('layouts.app')

@section('title', 'Daftar Retur Pembelian')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-arrow-return-left me-1"></i> Daftar Retur Pembelian
        </h6>
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Pembelian
        </a>
    </div>

    <div class="card-body">
        {{-- Tombol Aksi dan Alert --}}

            @if (session('success'))
                <div class="alert alert-success py-1 px-2 mb-0">
                    {{ session('success') }}
                </div>
            @elseif (session('error'))
                <div class="alert alert-danger py-1 px-2 mb-0">
                    {{ session('error') }}
                </div>
            @endif

        {{-- Tabel Daftar Retur Pembelian --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal Retur</th>
                        <th>Kode Retur</th>
                        <th>Supplier</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-end">Total Retur</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseReturns as $index => $return)
                    <tr>
                        <td>{{ $purchaseReturns->firstItem() + $index }}</td>
                        <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d/m/Y H:i') }}</td>
                        <td>{{ $return->return_code }}</td>
                        <td>{{ $return->supplier->name ?? 'N/A' }}</td>
                        <td>{{ $return->user->name ?? 'N/A' }}</td>
                        <td class="text-end text-success">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('purchase-returns.show', $return->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data retur pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $purchaseReturns->links() }}
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table td, .table th {
        padding: 0.6rem;
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85em;
    }
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.3rem 0.5rem;
    }
</style>
@endsection
