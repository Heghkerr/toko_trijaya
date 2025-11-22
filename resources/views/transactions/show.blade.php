@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detail Transaksi #{{ $transaction->transaction_code }}</h6>
        <div class="d-flex gap-2">

            <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>

            {{-- Ini adalah route 'nota' yang kita diskusikan tadi --}}
            <a href="{{ route('transactions.receipt', $transaction->id) }}" class="btn btn-primary btn-sm" target="_blank">
                <i class="bi bi-printer"></i> Cetak Nota
            </a>

        </div>
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
                    <input type="text" class="form-control" value="{{ strtoupper($transaction->status) }}" readonly>
                </div>


                @if($transaction->payment_method == 'cash')
                @endif
            </div>
            <div class="col-12">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Customer</label>
                            <input type="text" class="form-control" value="{{ $transaction->customer->name ?? 'Umum' }}" readonly>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" value="{{ $transaction->customer->phone ?? '-' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-3">Daftar Produk</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->details as $detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->product->name }} ({{ $detail->product->color->name }})</td>
                            <td>{{ $detail->product->type->name }}</td>
                            <td>{{ number_format($detail->quantity, 0, ',', '.') }} ({{ $detail->unit_name }}) </td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5">Subtotal</th>
                        <th>Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="5">Diskon</th>
                        <th>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="5">Total</th>
                        <th>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</th>
                    </tr>
                    @if($transaction->payment_method == 'cash')
                    <tr>
                        <th colspan="5">Tunai Diterima</th>
                        <th>Rp {{ number_format($transaction->cash_amount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="5">Kembalian</th>
                        <th>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</th>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
