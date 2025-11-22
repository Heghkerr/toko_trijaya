@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Produk</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Produk
                </a>
                <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="masterDataDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-database-fill-gear"></i> Master Data
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="masterDataDropdown">
                    <li>
                        {{-- Link ke Master Jenis --}}
                        <a class="dropdown-item" href="{{ route('product_types.index') }}">
                            <i class="bi bi-tags-fill me-2"></i> Master Jenis
                        </a>
                    </li>
                    <li>
                        {{-- Link ke Master Warna --}}
                        <a class="dropdown-item" href="{{ route('product_colors.index') }}">
                            <i class="bi bi-palette-fill me-2"></i> Master Warna
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body">

        {{-- GANTI BAGIAN FILTER LAMA ANDA DENGAN INI --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            {{-- Search Bar (SOLUSI 1: Ganti flex-grow-1 jadi w-50 atau w-75) --}}
            <form method="GET" action="{{ route('products.index') }}" class="w-50">
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

                <form method="GET" action="{{ route('products.index') }}">

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


        {{-- [AKHIR PERBAIKAN 2] --}}


        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Jenis Produk</th>
                        <th>Harga Beli</th>
                        <th>Supplier</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" width="50">
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $product->name }} ({{ $product->color->name ?? '-' }})</td>
                            <td>{{ $product->type->name ?? '-' }}</td>
                            <td>Rp{{ number_format($product->price_buy, 0, ',', '.') }}</td>
                            <td>{{ $product->supplier->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada produk ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">

                            <li class="page-item @if($products->onFirstPage()) disabled @endif">
                            <a class="page-link" href="{{ $products->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo; Previous</span>
                            </a>
                            </li>

                            <li class="page-item @if(!$products->hasMorePages()) disabled @endif">
                            <a class="page-link" href="{{ $products->nextPageUrl() }}" aria-label="Next">
                                <span aria-hidden="true">Next &raquo;</span>
                            </a>
                            </li>

                        </ul>
                        </nav>

                        <div class="mt-2 text-muted text-center">
                            Showing {{ $products->firstItem() ?? 0 }}
                            to {{ $products->lastItem() ?? 0 }}
                            of {{ $products->total() }} results
                        </div>
                </div>

            </div>
        </div>
@endsection

