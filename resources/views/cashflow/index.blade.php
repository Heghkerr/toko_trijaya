@extends('layouts.app')

@section('title', 'Buku Kas (Cash Flow)')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-book me-2"></i>Buku Kas (Cash Flow)
        </h6>
    </div>

    <div class="card-body">

        {{-- 1. Kartu Saldo Total --}}
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card border-left-success shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Saldo Kas Tunai (Laci) Saat Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp {{ number_format($total_cash, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-cash-stack fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-left-info shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Saldo Kas Bank (Rekening) Saat Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp {{ number_format($total_bank, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-credit-card fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Form Filter --}}
        <form action="{{ route('cashflow.index') }}" method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm"
                           value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                           value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="account" class="form-label">Akun Kas</label>
                    <select name="account" id="account" class="form-select form-select-sm">
                        <option value="">Semua Akun</option>
                        <option value="cash" {{ request('account') == 'cash' ? 'selected' : '' }}>Kas Tunai</option>
                        <option value="bank" {{ request('account') == 'bank' ? 'selected' : '' }}>Kas Bank</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="flow_type" class="form-label">Tipe Arus</label>
                    <select name="flow_type" id="flow_type" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        <option value="masuk" {{ request('flow_type') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                        <option value="keluar" {{ request('flow_type') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('cashflow.index') }}" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </div>
        </form>

        {{-- 3. Tabel Data --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Akun Kas</th>
                        <th>Deskripsi</th>
                        <th>Sumber</th>
                        <th>Kasir</th>
                        <th class="text-end">Masuk</th>
                        <th class="text-end">Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashflows as $flow)
                        <tr>
                            <td>{{ $flow->created_at->format('d-M-Y H:i') }}</td>
                            <td>
                                @if($flow->account == 'cash')
                                    <span class="badge bg-success">Kas Tunai</span>
                                @else
                                    <span class="badge bg-info">Kas Bank</span>
                                @endif
                            </td>
                            <td>{{ $flow->description }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($flow->source_type) }}</span>
                            </td>
                            <td>{{ $flow->user->name ?? '-' }}</td>

                            {{-- Kolom Masuk --}}
                            @if($flow->flow_type == 'masuk')
                                <td class="text-end text-success fw-bold">
                                    Rp {{ number_format($flow->amount, 0, ',', '.') }}
                                </td>
                                <td class="text-end">-</td>
                            @else
                            {{-- Kolom Keluar --}}
                                <td class="text-end">-</td>
                                <td class="text-end text-danger fw-bold">
                                    Rp {{ number_format($flow->amount, 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Tidak ada data kas untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Total (Berdasarkan Filter)</th>
                        <th class="text-end text-success">
                            Rp {{ number_format($total_masuk_filtered, 0, ',', '.') }}
                        </th>
                        <th class="text-end text-danger">
                            Rp {{ number_format($total_keluar_filtered, 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 4. Paginasi --}}
        <div class="d-flex justify-content-center">
            {{ $cashflows->links() }}
        </div>

    </div>
</div>
@endsection
