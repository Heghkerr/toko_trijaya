@extends('layouts.app')

@section('title', 'Kelola Transaksi')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body py-2">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="alert alert-info">
                        <strong>Kas saat ini:</strong> Rp {{ number_format($kasSaatIni, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between">
                    <div class="btn-group">
                        <a href="{{ route('transactions.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
                        </a>
                         {{-- Tombol Add Fund --}}
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addFundModal">
                                <i class="bi bi-cash-stack me-1"></i> Add Fund
                        </button>

                        {{-- <a href="{{ route('reports.x') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-file-earmark-text me-1"></i> X Report
                        </a>
                        <a href="{{ route('reports.z') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-file-earmark-lock me-1"></i> Z Report
                        </a> --}}
                    </div>
                        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-primary shadow-sm d-flex align-items-center">
                            <i class="bi bi-people-fill me-2"></i>
                            Customer
                        </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addFundModal" tabindex="-1" aria-labelledby="addFundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('transactions.addFund') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addFundModalLabel">Tambah Dana ke Kas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="1000"
                        placeholder="Masukkan jumlah Rp">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan (Opsional)</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Dana</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-list-ul me-2"></i>Daftar Transaksi
        </h6>
         <div class="d-flex align-items-center gap-2">

            {{-- [BARU] Tombol untuk ke halaman Daftar Refund --}}
            <a href="{{ route('refunds.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-return-left me-1"></i>
                Lihat Refund
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                    <li><h6 class="dropdown-header">Nama Kasir</h6></li>
                    @foreach($cashiers as $cashier)
                        <li>
                            <a class="dropdown-item {{ request('cashier') == $cashier->id ? 'active' : '' }}"
                            href="{{ request()->fullUrlWithQuery(['cashier' => $cashier->id]) }}">
                                {{ $cashier->name }}
                            </a>
                        </li>
                    @endforeach

                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Metode Pembayaran</h6></li>
                    <li>
                        <a class="dropdown-item {{ request('payment_method') == 'cash' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['payment_method' => 'cash']) }}">
                            Cash
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('payment_method') == 'card' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['payment_method' => 'card']) }}">
                            Card
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @if(request('cashier') || request('payment_method'))
        <div class="m-3 d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted fw-semibold me-2" style="font-size: 0.9rem;">Filter Aktif:</span>

            @if(request('cashier'))
                @php
                    $kasir = \App\Models\User::find(request('cashier'));
                @endphp
                <span class="badge bg-primary d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Kasir: {{ $kasir ? $kasir->name : 'Tidak Ditemukan' }}
                    <a href="{{ request()->fullUrlWithoutQuery('cashier') }}" class="text-white ms-2" style="text-decoration:none; font-weight:700;">
                        &times;
                    </a>
                </span>
            @endif

            @if(request('payment_method'))
                <span class="badge bg-success d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Metode: {{ strtoupper(request('payment_method')) }}
                    <a href="{{ request()->fullUrlWithoutQuery('payment_method') }}" class="text-white ms-2" style="text-decoration:none; font-weight:700;">
                        &times;
                    </a>
                </span>
            @endif

            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary ms-auto" style="font-size: 0.85rem; padding: 0.25rem 0.5rem;">
                <i class="bi bi-x-circle"></i> Reset Semua
            </a>
        </div>
    @endif


    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="3%" class="text-center">No</th>
                        <th width="12%">Kode</th>
                        <th width="12%">Tanggal</th>
                        <th width="9%">Kasir</th>
                        <th width="12%">Total</th>
                        <th width="9%">Diskon</th>
                        <th width="9%" class="text-center">Metode</th>
                        <th width="9%" class="text-center">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $transaction->transaction_code }}</td>
                            <td>{{ $transaction->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                            <td>{{ $transaction->user->name ?? '—' }}</td>
                            <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge
                                    {{ $transaction->payment_method == 'cash' ? 'bg-success' :
                                       ($transaction->payment_method == 'card' ? 'bg-primary' : 'bg-secondary') }}">
                                    {{ strtoupper($transaction->payment_method) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($transaction->status === 'unpaid')
                                    <span class="badge bg-warning text-dark">UNPAID</span>
                                @elseif($transaction->status === 'paid')
                                    <span class="badge bg-success">PAID</span>
                                @else
                                    <span class="badge bg-danger">REFUNDED</span>
                                @endif
                            </td>
                            <td class="text-center">

                                    @if($transaction->status === 'unpaid')
                                        <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="{{ route('transactions.markPaid', $transaction->id) }}" method="POST" style="display:inline;"
                                            onsubmit="return confirm('Apakah kamu yakin transaksi ini sudah dibayar?')" target="_blank">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-cash-coin me-1"></i> Paid
                                            </button>
                                        </form>
                                         <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('transactions.show', $transaction->id) }}" style="display:inline;" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('transactions.refund', $transaction->id) }}" style="display:inline;" class="btn btn-warning btn-sm">
                                            <i class="bi bi-arrow-return-left"></i> Refund
                                        </a>
                                        <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">

                            <li class="page-item @if($transactions->onFirstPage()) disabled @endif">
                            <a class="page-link" href="{{ $transactions->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo; Previous</span>
                            </a>
                            </li>

                            <li class="page-item @if(!$transactions->hasMorePages()) disabled @endif">
                            <a class="page-link" href="{{ $transactions->nextPageUrl() }}" aria-label="Next">
                                <span aria-hidden="true">Next &raquo;</span>
                            </a>
                            </li>

                        </ul>
                        </nav>

        <div class="mt-2 text-muted text-center">
            Showing {{ $transactions->firstItem() ?? 0 }}
            to {{ $transactions->lastItem() ?? 0 }}
            of {{ $transactions->total() }} results
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }
    .badge {
        font-size: 0.85em;
        font-weight: 500;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Tooltip initialization */
    [data-bs-toggle="tooltip"] {
        cursor: pointer;
    }
</style>
@endsection

@section('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection


