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

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

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

        {{-- Transfer Antar Akun --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-primary">
                        <i class="bi bi-arrow-left-right me-2"></i>Transfer (Tunai → Bank)
                    </div>
                    <div class="text-muted small d-none d-md-block">Pilih nominal lalu klik Transfer</div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Tunai -> Bank --}}
                    <div class="col-12 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-semibold mb-2">
                                <i class="bi bi-cash-stack me-1"></i>Tunai → Bank
                            </div>
                            <form id="cashflowTransferCashToBankForm" action="{{ route('cashflow.transfer') }}" method="POST" class="d-flex flex-column gap-3">
                                @csrf
                                <input type="hidden" name="direction" value="cash_to_bank">
                                <div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <input class="btn-check" type="radio" name="amount" id="ctb-amount-1000000" value="1000000" required>
                                        <label class="btn btn-outline-success btn-sm px-3" for="ctb-amount-1000000">1 jt</label>

                                        <input class="btn-check" type="radio" name="amount" id="ctb-amount-500000" value="500000" required>
                                        <label class="btn btn-outline-success btn-sm px-3" for="ctb-amount-500000">500 rb</label>

                                        <input class="btn-check" type="radio" name="amount" id="ctb-amount-200000" value="200000" required>
                                        <label class="btn btn-outline-success btn-sm px-3" for="ctb-amount-200000">200 rb</label>

                                        <input class="btn-check" type="radio" name="amount" id="ctb-amount-100000" value="100000" required>
                                        <label class="btn btn-outline-success btn-sm px-3" for="ctb-amount-100000">100 rb</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check2-circle me-1"></i>Transfer
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Bank -> Tunai --}}
                    <div class="col-12 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-semibold mb-2">
                                <i class="bi bi-bank me-1"></i>Bank → Tunai
                            </div>
                            <form id="cashflowTransferBankToCashForm" action="{{ route('cashflow.transfer') }}" method="POST" class="d-flex flex-column gap-3">
                                @csrf
                                <input type="hidden" name="direction" value="bank_to_cash">
                                <div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <input class="btn-check" type="radio" name="amount" id="btc-amount-1000000" value="1000000" required>
                                        <label class="btn btn-outline-primary btn-sm px-3" for="btc-amount-1000000">1 jt</label>

                                        <input class="btn-check" type="radio" name="amount" id="btc-amount-500000" value="500000" required>
                                        <label class="btn btn-outline-primary btn-sm px-3" for="btc-amount-500000">500 rb</label>

                                        <input class="btn-check" type="radio" name="amount" id="btc-amount-200000" value="200000" required>
                                        <label class="btn btn-outline-primary btn-sm px-3" for="btc-amount-200000">200 rb</label>

                                        <input class="btn-check" type="radio" name="amount" id="btc-amount-100000" value="100000" required>
                                        <label class="btn btn-outline-primary btn-sm px-3" for="btc-amount-100000">100 rb</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check2-circle me-1"></i>Transfer
                                </button>
                            </form>
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
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">

                <li class="page-item @if($cashflows->onFirstPage()) disabled @endif">
                <a class="page-link" href="{{ $cashflows->previousPageUrl() }}" aria-label="Previous">
                    <span aria-hidden="true">&laquo; Previous</span>
                </a>
                </li>

                <li class="page-item @if(!$cashflows->hasMorePages()) disabled @endif">
                <a class="page-link" href="{{ $cashflows->nextPageUrl() }}" aria-label="Next">
                    <span aria-hidden="true">Next &raquo;</span>
                </a>
                </li>

            </ul>
            </nav>


        <div class="mt-2 text-muted text-center">
            Showing {{ $cashflows->firstItem() ?? 0 }}
            to {{ $cashflows->lastItem() ?? 0 }}
            of {{ $cashflows->total() }} results
        </div>

    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Input nominal transfer sekarang memakai preset radio, jadi script currency-input tidak diperlukan lagi.
        return;

        // --- Fungsi Utama Formatting ---
        function formatRupiah(number) {
            if (!number) return '';

            // 1. Bersihkan angka dari format (titik, Rp, dll)
            let cleaned = String(number).replace(/[^,\d]/g, '');

            // 2. Pisahkan bagian desimal dan non-desimal (jika ada koma)
            let parts = cleaned.split(',');
            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? ',' + parts[1] : '';

            // 3. Tambahkan titik sebagai pemisah ribuan pada bagian integer
            let rupiah = '';
            let reverse = integerPart.toString().split('').reverse().join('');
            for (let i = 0; i < reverse.length; i++) {
                if (i % 3 === 0) rupiah += reverse.substr(i, 3) + '.';
            }

            // 4. Gabungkan kembali
            let formatted = rupiah.split('', rupiah.length - 1).reverse().join('');

            // 5. Tambahkan bagian desimal dan 'Rp '
            return formatted + decimalPart;
        }

        // --- Event Listener untuk Real-time Input ---
        currencyInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                // Ambil nilai yang diketik
                let rawValue = e.target.value;

                // Format nilai dan masukkan kembali ke input
                e.target.value = formatRupiah(rawValue);
            });
        });

        // --- Event Listener untuk Form Submission (PENTING) ---
        // Hapus format (Rp, titik, koma) sebelum data dikirim ke Controller
        if (form) {
            form.addEventListener('submit', function(e) {
                currencyInputs.forEach(input => {
                    let formattedValue = input.value;

                    // Hapus titik (pemisah ribuan) dan ganti koma dengan titik (untuk PHP/DB)
                    let cleanValue = formattedValue.replace(/\./g, '');
                    cleanValue = cleanValue.replace(/,/g, '.');

                    // Tulis nilai bersih (misal: 1000000.50) kembali ke input sebelum submit
                    input.value = cleanValue;
                });
                // Catatan: Setelah ini, browser akan mengirimkan form dengan nilai yang sudah bersih (angka).
            });
        }

        // Optional: Terapkan format pada nilai yang sudah ada (misalnya dari old() Laravel)
        currencyInputs.forEach(input => {
            if (input.value) {
                input.value = formatRupiah(input.value);
            }
        });
    });
</script>
