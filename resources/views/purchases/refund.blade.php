@extends('layouts.app')

@section('title', 'Proses Retur Pembelian')

@section('content')

{{-- Menampilkan error validasi --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Menampilkan error dari controller --}}
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('purchases.refund.store', $purchase->id) }}" method="POST">
    @csrf
    <div class="row">
        {{-- Kolom Kiri - Detail Item --}}
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Pilih Item untuk Diretur
                    </h6>
                </div>
                <div class="card-body">
                    <p>Pilih jumlah barang yang akan dikembalikan ke supplier.</p>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Harga Beli</th>
                                    <th class="text-center">Qty Dibeli</th>
                                    <th class="text-center">Sisa utk Retur</th>
                                    <th class="text-center" style="width: 120px;">Jml Retur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->details as $index => $detail)
                                    @php
                                        // Ambil sisa qty dari controller
                                        $remainingQty = $refundQuantities[$detail->id] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $detail->product->name ?? 'Produk Dihapus' }}
                                            <small class="d-block text-muted">{{ $detail->unit_name }}</small>

                                            {{-- Input hidden untuk ID detail pembelian --}}
                                            <input type="hidden"
                                                   name="refund_items[{{ $index }}][detail_id]"
                                                   value="{{ $detail->id }}">
                                        </td>
                                        <td class="text-center">
                                            Rp {{ number_format($detail->price, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            {{ $detail->quantity }}
                                        </td>
                                        <td class="text-center">
                                            @if($remainingQty > 0)
                                                <span class="badge bg-success">{{ $remainingQty }}</span>
                                            @else
                                                <span class="badge bg-secondary">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Input jumlah yang akan di-retur --}}
                                            <input type="number"
                                                   class="form-control form-control-sm text-center"
                                                   name="refund_items[{{ $index }}][quantity]"
                                                   value="0"
                                                   min="0"
                                                   max="{{ $remainingQty }}"
                                                   {{ $remainingQty <= 0 ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan - Ringkasan --}}
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Detail Pembelian Asli
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-5">No. Pembelian</dt>
                        <dd class="col-7">{{ $purchase->purchase_code }}</dd>

                        <dt class="col-5">Tanggal</dt>
                        <dd class="col-7">{{ $purchase->created_at->format('d M Y, H:i') }}</dd>

                        <dt class="col-5">Supplier</dt>
                        <dd class="col-7">{{ $purchase->supplier->name ?? 'N/A' }}</dd>

                        <dt class="col-5">Total Awal</dt>
                        <dd class="col-7">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</dd>
                    </dl>

                    <hr>

                    {{-- Alasan Retur --}}
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Retur (Opsional)</label>
                        <textarea class="form-control"
                                  id="reason"
                                  name="reason"
                                  rows="3"
                                  placeholder="Contoh: Barang rusak, salah kirim, dll.">{{ old('reason') }}</textarea>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-lg me-1"></i>
                            Proses Retur
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                            Batalkan
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

@endsection
