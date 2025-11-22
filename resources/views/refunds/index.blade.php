@extends('layouts.app')

@section('title', 'Daftar Refund')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i></i>
            Data Refund
        </h6>
        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Transaksi
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal Refund</th>
                        <th>Kode Transaksi Asli</th>
                        <th>Kasir (Refund)</th>
                        <th>Total Refund</th>
                        <th>Alasan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $index => $refund)
                    <tr>
                        <td>{{ $refunds->firstItem() + $index }}</td>
                        <td>{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            {{-- Link ke transaksi ASLI --}}
                            <a href="{{ route('transactions.show', $refund->original_transaction_id) }}">
                                {{ $refund->originalTransaction->transaction_code ?? 'N/A' }}
                            </a>
                        </td>
                        <td>{{ $refund->user->name ?? 'N/A' }}</td>
                        <td class="text-end text-success">
                            Rp {{ number_format($refund->total_refund_amount, 0, ',', '.') }}
                        </td>
                        <td>{{ $refund->reason ?? '-' }}</td>
                        <td class="text-center">
                            {{-- Link ke detail refund --}}
                            <a href="{{ route('refunds.show', $refund->id) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data refund.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Links --}}
        <div class="d-flex justify-content-center">
            {{ $refunds->links() }}
        </div>
    </div>
</div>
@endsection
