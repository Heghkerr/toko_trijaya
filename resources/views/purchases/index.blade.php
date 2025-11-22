@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-cart-plus me-2"></i>Daftar Pembelian Barang
        </h6>

        <div class="d-flex align-items-center gap-2">
            {{-- Tombol Lihat Daftar Retur --}}
            <a href="{{ route('purchase-returns.index') }}" class="btn btn-sm btn-warning text-white shadow-sm d-flex align-items-center">
                <i class="bi bi-arrow-return-left me-2"></i>
                Lihat Retur
            </a>

            {{-- Tombol Supplier --}}
            <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-success shadow-sm d-flex align-items-center">
                <i class="bi bi-person-bounding-box me-2"></i>
                Supplier
            </a>

            {{-- Tombol Tambah Pembelian --}}
            <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary shadow-sm d-flex align-items-center">
                <i class="bi bi-plus-circle me-2"></i>
                Tambah Pembelian
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode Pembelian</th>
                        <th>Supplier</th>
                        <th>Total Pembelian</th>
                        <th>Dibuat Oleh</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $purchase->purchase_code }}</td>
                            <td>{{ $purchase->supplier->name ?? '-' }}</td>
                            <td>Rp{{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                            <td>{{ $purchase->user->name ?? '—' }}</td>
                            <td>
                                @if($purchase->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($purchase->status === 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                @elseif($purchase->status === 'cancelled')
                                    <span class="badge bg-danger">Dibatalkan</span> {{-- diganti ke danger --}}
                                @else
                                    <span class="badge bg-secondary">Tidak Diketahui</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                {{-- PERMINTAAN 2: Tombol Buat Retur (jika completed) --}}
                                @if($purchase->status === 'completed')
                                    {{-- Ini akan mengirim ?purchase_id=... ke controller create retur --}}
                                    <a href="{{ route('purchases.refund', $purchase->id) }}" class="btn btn-warning btn-sm" title="Retur Barang">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </a>
                                @endif

                                @if($purchase->status !== 'completed' && $purchase->status !== 'cancelled')
                                    <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $purchases->links() }}
        </div>
    </div>
</div>

@endsection
