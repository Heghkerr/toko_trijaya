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
                    <li><h6 class="dropdown-header">Status</h6></li>
                    <li>
                        <a class="dropdown-item {{ request('status') == 'unpaid' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['status' => 'unpaid']) }}">
                            <i class="bi bi-clock text-warning"></i> Belum Bayar
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('status') == 'paid' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['status' => 'paid']) }}">
                            <i class="bi bi-check-circle text-success"></i> Sudah Bayar
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('status') == 'sent' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['status' => 'sent']) }}">
                            <i class="bi bi-truck text-info"></i> Dikirim
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('status') == 'finished' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['status' => 'finished']) }}">
                            <i class="bi bi-check-all text-primary"></i> Selesai
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Metode Pembayaran</h6></li>
                    <li>
                        <a class="dropdown-item {{ request('payment_method') == 'cash' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['payment_method' => 'cash']) }}">
                            <i class="bi bi-cash text-success"></i> Cash
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('payment_method') == 'card' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['payment_method' => 'card']) }}">
                            <i class="bi bi-credit-card text-primary"></i> Card
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('payment_method') == 'qris' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['payment_method' => 'qris']) }}">
                            <i class="bi bi-qr-code text-info"></i> QRIS
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
                                @if($transaction->payment_method == 'cash')
                                    <span class="badge bg-success"><i class="bi bi-cash"></i> Cash</span>
                                @elseif($transaction->payment_method == 'card')
                                    <span class="badge bg-primary"><i class="bi bi-credit-card"></i> Card</span>
                                @elseif($transaction->payment_method == 'qris')
                                    <span class="badge bg-info"><i class="bi bi-qr-code"></i> QRIS</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($transaction->payment_method) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($transaction->status === 'unpaid')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock"></i> BELUM BAYAR
                                    </span>
                                @elseif($transaction->status === 'paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i>SUDAH  BAYAR
                                    </span>
                                @elseif($transaction->status === 'sent')
                                    <span class="badge bg-info">
                                        <i class="bi bi-truck"></i> KIRIM
                                    </span>
                                @elseif($transaction->status === 'finished')
                                    <span class="badge bg-primary">
                                        <i class="bi bi-check-all"></i> SELESAI
                                    </span>
                                @else
                                    <span class="badge bg-danger">{{ strtoupper($transaction->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">

                                    {{-- Detail Button (Always) --}}
                                    <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-info btn-sm" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- Action Buttons Based on Status --}}
                                    @if($transaction->status === 'unpaid')
                                        {{-- Button Bayar --}}
                                        <form action="{{ route('transactions.markPaid', $transaction->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm" title="Bayar"
                                                onclick="return confirm('Tandai sudah dibayar?')">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>

                                        {{-- Button Delete --}}
                                        <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus"
                                                onclick="return confirm('Yakin hapus?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    @elseif($transaction->status === 'paid')
                                        {{-- Button Kirim (online) atau badge Selesai (offline) --}}
                                        @php
                                            $isOnline = $transaction->customer && str_contains($transaction->customer->name, '(WhatsApp)');
                                        @endphp

                                        @if($isOnline)
                                            <form action="{{ route('transactions.markSent', $transaction->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-info btn-sm" title="Kirim"
                                                    onclick="return confirm('Tandai sudah dikirim?')">
                                                    <i class="bi bi-truck"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-success"><i class="bi bi-check-all"></i> Done</span>
                                        @endif

                                        {{-- Button Refund --}}
                                        <a href="{{ route('transactions.refund', $transaction->id) }}"
                                           class="btn btn-warning btn-sm" title="Refund">
                                            <i class="bi bi-arrow-return-left"></i>
                                        </a>

                                    @elseif($transaction->status === 'sent')
                                        {{-- Button Selesai --}}
                                        <form action="{{ route('transactions.markFinished', $transaction->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-primary btn-sm" title="Selesai"
                                                onclick="return confirm('Tandai selesai?')">
                                                <i class="bi bi-check-all"></i>
                                            </button>
                                        </form>

                                        {{-- Button Refund --}}
                                        <a href="{{ route('transactions.refund', $transaction->id) }}"
                                           class="btn btn-warning btn-sm" title="Refund">
                                            <i class="bi bi-arrow-return-left"></i>
                                        </a>

                                    @elseif($transaction->status === 'finished')
                                        {{-- Finished - No Action --}}
                                        <span class="text-success"><i class="bi bi-check-all"></i> Done</span>

                                        {{-- Button Refund --}}
                                        <a href="{{ route('transactions.refund', $transaction->id) }}"
                                           class="btn btn-warning btn-sm" title="Refund">
                                            <i class="bi bi-arrow-return-left"></i>
                                        </a>

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

    /* Mobile Responsive Improvements */
    @media (max-width: 768px) {
        .card-body {
            padding: 0.75rem;
        }

        .card-header {
            padding: 0.75rem;
        }

        .card-header h6 {
            font-size: 0.9rem;
        }

        /* Button group improvements */
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .btn-group .btn {
            flex: 1 1 auto;
            min-width: max-content;
        }

        .btn-outline-primary,
        .btn-outline-success,
        .btn-outline-info,
        .btn-outline-secondary {
            padding: 0.4rem 0.6rem;
            font-size: 0.75rem;
        }

        /* Alert improvements */
        .alert {
            padding: 0.6rem;
            font-size: 0.85rem;
        }

        .alert strong {
            font-size: 0.9rem;
        }

        /* Table improvements */
        .table {
            font-size: 0.75rem;
        }

        .table th,
        .table td {
            padding: 0.4rem 0.3rem;
        }

        /* Badge improvements */
        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }

        /* Dropdown menu */
        .dropdown-menu {
            font-size: 0.8rem;
        }

        .dropdown-item {
            padding: 0.4rem 0.8rem;
        }

        /* Modal */
        .modal-body {
            padding: 0.75rem;
        }

        /* Pagination */
        .pagination {
            font-size: 0.8rem;
        }

        /* Filter section */
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 0.5rem;
        }

        .d-flex.align-items-center {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 480px) {
        .card-header h6 {
            font-size: 0.8rem;
        }

        .table {
            font-size: 0.7rem;
        }

        .table th,
        .table td {
            padding: 0.3rem 0.2rem;
        }

        .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }

        /* Stack the action buttons vertically on very small screens */
        .btn-group {
            flex-direction: column;
        }

        .btn-group .btn {
            width: 100%;
        }

        /* Hide some table columns on very small screens */
        .table th:nth-child(3),
        .table td:nth-child(3) {
            font-size: 0.65rem;
        }
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

        // Buka receipt di tab baru jika ada session
        @if(session('open_receipt'))
            window.open('{{ session('open_receipt') }}', '_blank');
        @endif
    });
</script>
@endsection


