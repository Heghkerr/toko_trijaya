@extends('layouts.app')

@section('title', 'Detail Refund')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            Detail Refund (ID: {{ $refund->id }})
        </h6>
        <a href="{{ route('refunds.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Refund
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Tanggal Refund:</strong>
                <p>{{ $refund->created_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="col-md-6">
                <strong>Kasir (Refund):</strong>
                <p>{{ $refund->user->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <strong>Transaksi Asli:</strong>
                <p>
                    <a href="{{ route('transactions.show', $refund->original_transaction_id) }}">
                        {{ $refund->originalTransaction->transaction_code ?? 'N/A' }}
                    </a>
                    (dilakukan oleh: {{ $refund->originalTransaction->user->name ?? 'N/A' }})
                </p>
            </div>
            <div class="col-md-6">
                <strong>Alasan Refund:</strong>
                <p>{{ $refund->reason ?? '-' }}</p>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Item yang Direfund</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th>Satuan</th>
                        <th class="text-center">Qty Direfund</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($refund->details as $detail)
                        <tr>
                            <td>{{ $detail->productUnit->product->name ?? 'Produk Dihapus' }}</td>
                            <td>{{ $detail->productUnit->name ?? 'Satuan Dihapus' }}</td>
                            <td class="text-center">{{ $detail->quantity }}</td>
                            <td class="text-end">Rp {{ number_format($detail->price_per_unit, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-bold">
                            <strong>Total Uang Dikembalikan:</strong>
                        </td>
                        <td class="text-end fw-bold text-danger">
                            <strong>Rp {{ number_format($refund->total_refund_amount, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
