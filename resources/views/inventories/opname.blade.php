@extends('layouts.app')

@section('title', 'Stok Opname')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-clipboard-check me-2"></i> Stok Opname Barang
        </h6>
        <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Inventaris
        </a>
    </div>

    <div class="card-body">

        {{-- GANTI BAGIAN FILTER LAMA ANDA DENGAN INI --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            {{-- Search Bar (SOLUSI 1: Ganti flex-grow-1 jadi w-50 atau w-75) --}}
            <form method="GET" action="{{ route('inventories.opname') }}" class="w-50">
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

                <form method="GET" action="{{ route('inventories.opname') }}">

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

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Validasi Gagal!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inventories.opname.store') }}" method="POST" id="opnameForm">
            @csrf

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Nama Variasi Produk</th>
                            <th class="text-center" style="width: 10%;">Stok Sistem</th>
                            <th class="text-center" style="width: 15%;">Stok Fisik</th>
                            <th class="text-center" style="width: 10%;">Selisih</th>
                            <th style="width: 35%;">Keterangan (Wajib diisi jika ada selisih)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productUnits as $unit)
                        <tr class="opname-row">
                            {{-- 1. Nama Variasi --}}
                            <td>
                                {{ $unit->product->name }}
                                ({{ $unit->product->color->name ?? '-' }})
                                <br>
                                <strong class="text-primary">{{ $unit->name }}</strong>
                            </td>

                            {{-- 2. Stok Sistem --}}
                            <td class="text-center stok-sistem" data-stock-sistem="{{ $unit->stock }}">
                                {{ $unit->stock }}
                            </td>

                            {{-- 3. Input Stok Fisik --}}
                            <td>
                                <input type="number"
                                       name="opname[{{ $unit->id }}][stok_fisik]"
                                       class="form-control form-control-sm text-center stok-fisik-input"
                                       value="{{ $unit->stock }}"
                                       min="0">
                            </td>

                            {{-- 4. Selisih --}}
                            <td class="text-center stok-selisih fw-bold">
                                0
                            </td>

                            {{-- 5. Keterangan (Alasan) per Baris --}}
                            <td>
                                <input type="text"
                                       name="opname[{{ $unit->id }}][alasan]"
                                       class="form-control form-control-sm alasan-input"
                                       placeholder="cth: Rusak, Hilang, Salah input...">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada variasi produk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginasi DIHAPUS --}}

            <hr>

            {{-- [DIUBAH] Textarea alasan global DIHAPUS, diganti tombol simpan --}}
            <div class="text-end">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-save"></i> Simpan Hasil Opname
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function calculateRowDifference(row) {
        const stokSistemEl = row.querySelector('.stok-sistem');
        const stokFisikEl = row.querySelector('.stok-fisik-input');
        const stokSelisihEl = row.querySelector('.stok-selisih');

        if (!stokSistemEl || !stokFisikEl || !stokSelisihEl) return;

        const sistem = parseInt(stokSistemEl.dataset.stockSistem) || 0;
        const fisik = parseInt(stokFisikEl.value) || 0;
        const selisih = fisik - sistem;

        stokSelisihEl.textContent = selisih > 0 ? `+${selisih}` : selisih;

        stokSelisihEl.classList.remove('text-success', 'text-danger', 'text-muted');
        if (selisih > 0) {
            stokSelisihEl.classList.add('text-success');
        } else if (selisih < 0) {
            stokSelisihEl.classList.add('text-danger');
        } else {
            stokSelisihEl.classList.add('text-muted');
        }
    }

    const allRows = document.querySelectorAll('.opname-row');

    allRows.forEach(row => {
        calculateRowDifference(row);

        const fisikInput = row.querySelector('.stok-fisik-input');
        if (fisikInput) {
            fisikInput.addEventListener('input', function() {
                calculateRowDifference(row);
            });
        }
    });
});
</script>
@endpush
@endsection
