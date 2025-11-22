@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-receipt me-2"></i> Detail Pembelian #{{ $purchase->purchase_code }}
        </h6>
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Pembelian
        </a>
    </div>

    <div class="card-body">
        {{-- Informasi Utama Pembelian --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
                <p><strong>Status:</strong>
                    @if($purchase->status === 'pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($purchase->status === 'completed')
                        <span class="badge bg-success">Selesai</span>
                    @else
                        <span class="badge bg-secondary">Dibatalkan</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6">
                <p><strong>Dibuat oleh:</strong> {{ $purchase->user->name ?? '—' }}</p>
                <p><strong>Tanggal:</strong> {{ $purchase->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Delivery Cost: </strong> Rp. {{ number_format ($purchase->delivery_cost, 0, ',', '.') }}</p>
        </div>

        {{-- Tabel Detail Produk --}}
        <h6 class="fw-bold mb-3">Daftar Produk</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->details as $detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->product->name ?? '-' }} ({{ $detail->product->color->name ?? '-' }})</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp{{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($detail->subtotal ?? ($detail->quantity * $detail->price), 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total Pembelian:</th>
                        <th>Rp{{ number_format($purchase->total_amount, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
