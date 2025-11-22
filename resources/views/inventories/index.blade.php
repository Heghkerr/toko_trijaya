@extends('layouts.app')

@section('title', 'Inventory Barang')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-box-seam me-2"></i>Daftar Stok Barang
        </h6>
        <div class="d-flex gap-2">
        <a href="{{ route('inventories.convert') }}" class="btn btn-success">
            <i class="bi bi-arrow-repeat me-1"></i> Konversi
        </a>
        <a href="{{ route('inventories.opname') }}" class="btn btn-warning">
            <i class="bi bi-clipboard-check me-1"></i> Stok Opname
        </a>
    </div>
</div>

    <div class="card-body">

        {{-- GANTI BAGIAN FILTER LAMA ANDA DENGAN INI --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            {{-- Search Bar (SOLUSI 1: Ganti flex-grow-1 jadi w-50 atau w-75) --}}
            <form method="GET" action="{{ route('inventories.index') }}" class="w-50">
                <div class="input-group">
                    <input type="search" name="search" class="form-control form-control-sm"
                        placeholder="Cari berdasarkan Nama Produk..."
                        value="{{ request('search') }}">

                    {{-- Penting: Simpan filter aktif saat melakukan search --}}
                    @if(request('type_id'))
                        <input type="hidden" name="type_id" value="{{ request('type_id') }}">
                    @endif
                    @if(request('color_id'))
                        <input type="hidden" name="color_id" value="{{ request('color_id') }}">
                    @endif
                    @if(request('unit_name'))
                        <input type="hidden" name="unit_name" value="{{ request('unit_name') }}">
                    @endif

                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            {{-- Tombol Filter Offcanvas (SOLUSI 2) --}}
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                <i class="bi bi-funnel me-1"></i>
                Filter
                {{-- Beri notifikasi jika ada filter yang aktif --}}
                @if(request('type_id') || request('color_id'))
                    <span class="badge rounded-pill bg-primary ms-1" style="font-size: 0.6em; padding: 0.3em 0.5em;">
                        !
                    </span>
                @endif
            </button>
        </div>

        {{-- Filter Aktif (Tampilan badge tetap sama) --}}
        @if(request('type_id') || request('color_id'))
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="text-muted fw-semibold me-2" style="font-size: 0.9rem;">Filter Aktif:</span>
            @if(isset($activeType))
                <span class="badge bg-primary d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Jenis: {{ $activeType->name }}
                    {{-- [DIUBAH] Link reset ini sekarang juga harus menyimpan query 'search' jika ada --}}
                    <a href="{{ request()->fullUrlWithQuery(['type_id' => null]) }}"
                    class="text-white ms-2" style="text-decoration:none; font-weight:700;">&times;</a>
                </span>
            @endif
            @if(isset($activeColor))
                <span class="badge bg-success d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Warna: {{ $activeColor->name }}
                    {{-- [DIUBAH] Link reset ini sekarang juga harus menyimpan query 'search' jika ada --}}
                    <a href="{{ request()->fullUrlWithQuery(['color_id' => null]) }}"
                    class="text-white ms-2" style="text-decoration:none; font-weight:700;">&times;</a>
                </span>
            @endif
            @if(request('unit_name'))
                <span class="badge bg-warning text-dark d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Satuan: {{ request('unit_name') }}
                    <a href="{{ request()->fullUrlWithQuery(['unit_name' => null]) }}"
                    class="text-dark ms-2" style="text-decoration:none; font-weight:700;">&times;</a>
                </span>
            @endif
        </div>
        @endif
        {{-- BATAS AKHIR BLOK PENGGANTI --}}
        {{-- [BARU] Kode untuk Offcanvas Filter --}}
        <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filter Inventaris</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">

                <form method="GET" action="{{ route('inventories.index') }}">

                    {{-- Penting: Simpan 'search' query saat melakukan filter --}}
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    {{-- Filter Jenis (diubah jadi <select>) --}}
                    <div class="mb-3">
                        <label for="filter_type_id" class="form-label">Jenis Produk</label>
                        <select class="form-select" name="type_id" id="filter_type_id">
                            <option value="">Semua Jenis</option>
                            @foreach($productTypes as $type)
                                <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Warna (diubah jadi <select>) --}}
                    <div class="mb-3">
                        <label for="filter_color_id" class="form-label">Warna Produk</label>
                        <select class="form-select" name="color_id" id="filter_color_id">
                            <option value="">Semua Warna</option>
                            @foreach($productColors as $color)
                                <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>
                                    {{ $color->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filter_unit_name" class="form-label">Satuan</label>
                        <select class="form-select" name="unit_name" id="filter_unit_name">
                            <option value="">Semua Satuan</option>

                            {{-- Loop dari variabel $unitNames yang baru Anda buat --}}
                            @foreach($unitNames as $unitName)
                                <option value="{{ $unitName }}" {{ request('unit_name') == $unitName ? 'selected' : '' }}>
                                    {{ $unitName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    {{-- Tombol Aksi --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                        </button>

                        {{-- Tombol Reset (Hanya mereset filter, bukan pencarian) --}}
                        <a href="{{ route('inventories.index', ['search' => request('search')]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Jenis Produk</th>
                        <th>Satuan</th>
                        <th>Harga Jual</th>
                        <th>Stok Saat Ini</th>
                        <th>Log</th>
                        <th>QR</th> {{-- [BARU] Kolom untuk QR Code --}}
                    </tr>
                </thead>
                <tbody>
                    {{-- [DIUBAH] Loop $productUnits, bukan $products --}}
                    @forelse($productUnits as $index => $unit)
                    <tr>
                        <td>{{ $productUnits->firstItem() + $index }}</td>
                        {{-- <td>
                            @if($unit->product->image)
                                <img src="{{ asset('storage/' . $unit->product->image) }}" alt="{{ $unit->product->name }}" width="50">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td> --}}
                        <td>
                            <a href="{{ route('inventories.show', $unit) }}">
                                {{ $unit->product->name }} ({{ $unit->product->color->name ?? '-' }})
                            </a>
                        </td>
                        <td>{{ $unit->product->type->name ?? '-' }}</td>
                        <td style="min-width: 100px;">{{ $unit->name }} ({{ $unit->conversion_value }} pcs)</td>
                        <td style="min-width: 120px;">Rp {{ number_format($unit->price, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $unit->stock ?? 0 }}</td>
                        <td class="text-center">
                            {{-- [DIUBAH] Target modal sekarang unik per $unit->id --}}
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#logModal{{ $unit->id }}">
                                <i class="bi bi-clock-history"></i> Lihat Log
                            </button>
                        </td>
                        <td class="text-center">
                             {!! QrCode::size(60)->generate(route('inventories.show', $unit)) !!}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data produk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        @foreach($productUnits as $unit)
            <div class="modal fade" id="logModal{{ $unit->id }}" tabindex="-1" aria-labelledby="logModalLabel{{ $unit->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            {{-- Judul Modal diubah --}}
                            <h5 class="modal-title">Log Stok — {{ $unit->product->name }} ({{ $unit->name }}) - {{$unit->conversion_value}} pcs</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Filter Tanggal (Sudah Benar) --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Dari Tanggal</label>
                                    <input type="date" class="form-control form-control-sm modal-start-date">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Sampai Tanggal</label>
                                    <input type="date" class="form-control form-control-sm modal-end-date">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary btn-sm w-100 modal-filter-btn">Filter</button>
                                </div>
                            </div>

                            {{-- [DIUBAH] Cek $unit->inventories --}}
                            @if($unit->inventories && $unit->inventories->count() > 0)
                                <table class="table table-striped modal-log-table">
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
                                        {{-- [DIUBAH] Loop $unit->inventories --}}
                                        @foreach($unit->inventories->sortByDesc('created_at') as $log)
                                            <tr data-date="{{ $log->created_at->format('Y-m-d') }}">
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
                                                    <span class="badge
                                                        {{ $log->type == 'masuk' ? 'bg-success' :
                                                           ($log->type == 'keluar' ? 'bg-danger' :
                                                           ($log->type == 'koreksi' ? 'bg-info' : 'bg-secondary')) }}">
                                                        {{ ucfirst($log->type) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <p class="text-muted text-center mb-0 no-logs-found" style="display: none;">
                                    Tidak ada log stok untuk rentang tanggal yang dipilih.
                                </p>
                            @else
                                <p class="text-muted text-center mb-0">
                                    Belum ada log stok untuk variasi ini.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-center">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item @if($productUnits->onFirstPage()) disabled @endif">
                        <a class="page-link" href="{{ $productUnits->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo; Previous</span>
                        </a>
                    </li>
                    <li class="page-item @if(!$productUnits->hasMorePages()) disabled @endif">
                        <a class="page-link" href="{{ $productUnits->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">Next &raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="mt-2 text-muted text-center ms-3">
                Showing {{ $productUnits->firstItem() ?? 0 }}
                to {{ $productUnits->lastItem() ?? 0 }}
                of {{ $productUnits->total() }} results
            </div>
        </div>

    </div>
</div>


@endSection

@push('scripts')
{{-- JavaScript Anda (modal filter, dll) tidak perlu diubah. --}}
<style>
.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ... (Semua script filter modal Anda SAMA dan sudah benar) ...
        document.querySelectorAll('.modal-filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                const startDate = modal.querySelector('.modal-start-date').value;
                const endDate = modal.querySelector('.modal-end-date').value;
                const rows = modal.querySelectorAll('.modal-log-table tbody tr');
                const noLogsMessage = modal.querySelector('.no-logs-found');
                let logsFound = 0;

                const start = startDate ? new Date(startDate) : null;
                const end = endDate ? new Date(endDate) : null;

                if (start) start.setHours(0, 0, 0, 0);
                if (end) end.setHours(23, 59, 59, 999);

                rows.forEach(row => {
                    const rowDateStr = row.getAttribute('data-date');
                    if (!rowDateStr) {
                        // Ini untuk baris 'sub-judul' atau 'empty'
                        // Kita biarkan saja
                        if(row.querySelector('td[colspan="5"]')) {
                             row.style.display = 'table-row';
                        }
                        return;
                    }

                    const rowDate = new Date(rowDateStr);
                    rowDate.setHours(12, 0, 0, 0);

                    let show = true;
                    if (start && rowDate < start) {
                        show = false;
                    }
                    if (end && rowDate > end) {
                        show = false;
                    }

                    if (show) {
                        row.style.display = 'table-row';
                        logsFound++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (logsFound === 0) {
                    noLogsMessage.style.display = 'block';
                } else {
                    noLogsMessage.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('.modal.fade').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', function () {
                this.querySelector('.modal-start-date').value = '';
                this.querySelector('.modal-end-date').value = '';

                this.querySelectorAll('.modal-log-table tbody tr').forEach(row => {
                    row.style.display = 'table-row';
                });

                const noLogsMessage = this.querySelector('.no-logs-found');
                if (noLogsMessage) {
                    noLogsMessage.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
