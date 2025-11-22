@extends('layouts.app')

@section('title', 'Detail Retur Pembelian')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            Detail Retur Pembelian ({{ $purchaseReturn->return_code }})
        </h6>
        <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Retur
        </a>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Supplier:</strong>
                <p>{{ $purchaseReturn->supplier->name ?? '-' }}</p>

                <strong>Tanggal Retur:</strong>
                <p>{{ \Carbon\Carbon::parse($purchaseReturn->return_date)->format('d M Y') }}</p>

                <strong>Dibuat Oleh:</strong>
                <p>{{ $purchaseReturn->user->name ?? '-' }}</p>

                <strong>Catatan:</strong>
                <p>{{ $purchaseReturn->notes ?? '-' }}</p>
            </div>

            <div class="col-md-6 text-end">
                <h5>Total Retur</h5>
                <h2 class="text-primary">Rp {{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}</h2>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Barang yang Diretur</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th class="text-center">Harga Modal</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseReturn->details as $detail)
                        <tr>
                            <td>{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                            <td class="text-center">Rp {{ number_format($detail->cost_price, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $detail->quantity }} pcs</td>
                            <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">TOTAL</td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
