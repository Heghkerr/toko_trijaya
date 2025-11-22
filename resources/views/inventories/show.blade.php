{{-- resources/views/inventories/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Detail Barang')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">

        {{-- Judul Halaman --}}
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-box-seam me-2"></i>
            Detail: {{ $unit->product->name }} ({{ $unit->name }})
        </h6>

        {{-- Tombol Kembali --}}
        <a href="{{ route('inventories.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Daftar Inventaris
        </a>
    </div>
    <div class="card-body">
        <div class="row">

            {{-- Kolom Gambar --}}
            <div class="col-md-4 text-center">
                @if($unit->product->image)
                    <img src="{{ asset('storage/' . $unit->product->image) }}"
                         alt="{{ $unit->product->name }}"
                         class="img-fluid rounded mb-3"
                         style="max-height: 300px; border: 1px solid #ddd;">
                @else
                    <div class="border rounded bg-light d-flex align-items-center justify-content-center"
                         style="height: 300px; width: 100%;">
                        <span class="text-muted">Tidak ada gambar</span>
                    </div>
                @endif

                {{-- [BARU] QR Code Section --}}
                <div class="mt-3" id="qr-code-container">
                    <h6 class="text-muted">Scan QR untuk Detail</h6>
                    {!! QrCode::size(150)->generate(route('inventories.show', $unit)) !!}
                    <p class="mt-2 mb-0" style="font-size: 0.8rem;">
                        <strong>{{ $unit->product->name }}</strong>
                        <br>
                        ({{ $unit->name }} - {{ $unit->product->color->name ?? '' }})
                    </p>
                </div>
                <button onclick="printQr()" class="btn btn-sm btn-outline-secondary mt-3">
                    <i class="bi bi-printer me-1"></i> Cetak QR
                </button>
                {{-- [AKHIR BARU] --}}

            </div>

            {{-- Kolom Info --}}
            <div class="col-md-8">
                <h3>{{ $unit->product->name }}</h3>
                <p class="fs-5 text-muted">{{ $unit->product->type->name ?? 'Tidak ada jenis' }}</p>

                <hr>

                {{-- Info Detail --}}
                <div class="row mb-3">
                    <div class="col-6 col-md-4 mb-3">
                        <strong class="text-dark d-block">Warna</strong>
                        {{ $unit->product->color->name ?? '-' }}
                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <strong class="text-dark d-block">Satuan</strong>
                        {{ $unit->name }}
                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <strong class="text-dark d-block">Konversi</strong>
                        1 {{ $unit->name }} = {{ $unit->conversion_value }} pcs
                    </div>
                </div>

                {{-- Info Stok & Harga --}}
                <div class="row">
                    <div class="col-6 col-md-4">
                        <strong class="text-dark d-block">Stok Saat Ini</strong>
                        <span class="fs-4 fw-bold text-primary">{{ $unit->stock ?? 0 }}</span>
                        <span class="ms-1">{{ $unit->name }}</span>
                    </div>
                    <div class="col-6 col-md-4">
                        <strong class="text-dark d-block">Harga Jual</strong>
                        <span class="fs-4 fw-bold text-success">Rp {{ number_format($unit->price, 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>
        </div>

        <hr class="mt-4">

        {{-- Log Stok (Sama seperti di modal index) --}}
        <h5><i class="bi bi-clock-history me-2"></i>Log Stok</h5>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
             @if($unit->inventories && $unit->inventories->count() > 0)
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                            <th>Tipe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unit->inventories->sortByDesc('created_at') as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d-M-Y H:i') }}</td>
                                <td>{{ $log->user->name ?? '—' }}</td>
                                <td>{{ $log->description ?? '-' }}</td>
                                <td class="text-center">
                                    @if($log->quantity > 0)
                                        <span class="text-success">+{{ $log->quantity }}</span>
                                    @elseif($log->quantity < 0)
                                        <span class="text-danger">{{ $log->quantity }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $log->type == 'masuk' ? 'bg-success' : ($log->type == 'keluar' ? 'bg-danger' : ($log->type == 'koreksi' ? 'bg-info' : 'bg-secondary')) }}">
                                        {{ ucfirst($log->type) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted text-center mb-0">
                    Belum ada log stok untuk variasi ini.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection

{{-- [BARU] Script untuk print --}}
@push('scripts')
<style>
    /* CSS untuk menyembunyikan elemen lain saat print */
    @media print {
        body * {
            visibility: hidden;
        }
        #qr-code-container, #qr-code-container * {
            visibility: visible;
        }
        #qr-code-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    }
</style>
<script>
    function printQr() {
        window.print();
    }
</script>
@endpush
