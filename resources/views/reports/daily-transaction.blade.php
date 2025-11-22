@extends('layouts.app')

@section('title', 'Detail Transaksi Harian')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detail Transaksi #{{ $transaction->transaction_code }}</h6>
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Kode Transaksi</label>
                    <input type="text" class="form-control" value="{{ $transaction->transaction_code }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="text" class="form-control"
                           value="{{ $transaction->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <input type="text" class="form-control" value="{{ strtoupper($transaction->payment_method) }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control"
                           value="{{ $transaction->status == 'paid' ? 'Lunas' : 'Belum Lunas' }}" readonly>
                </div>
                @if($transaction->payment_method == 'cash')
                <div class="mb-3">
                    <label class="form-label">Kasir</label>
                    <input type="text" class="form-control"
                           value="{{ $transaction->user->name ?? '-' }}" readonly>
                </div>
                @endif
            </div>
        </div>

        <h5 class="mb-3">Daftar Produk</h5>
        <div class="table-responsive">
            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Harga Satuan</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->details as $detail)
                        @php
                            $conversion = 1;
                            if($detail->product && $detail->product->units && $detail->unit_name) {
                                $unit = $detail->product->units->firstWhere('unit_name', $detail->unit_name);
                                if ($unit) {
                                    $conversion = $unit->conversion_value ?? 1;
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->product->name ?? 'Produk Dihapus' }} ({{ $detail->product->color->name ?? '-' }})</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>{{ $detail->quantity * $conversion }} pcs</td>

                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Subtotal</th>
                        <th>Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Diskon</th>
                        <th>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</th>
                    </tr>
                    @if($transaction->payment_method == 'cash')
                    <tr>
                        <th colspan="4" class="text-end">Tunai Diterima</th>
                        <th class="text-end">Rp {{ number_format($transaction->cash_amount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Kembalian</th>
                        <th class="text-end">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</th>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

