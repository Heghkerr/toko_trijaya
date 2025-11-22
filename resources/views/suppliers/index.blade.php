@extends('layouts.app')

@section('title', 'Master Suppliers')

@section('content')

<div class="d-flex justify-content-end mb-3">
     <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Pembelian
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-tags-fill me-2"></i>Daftar Suppliers</h6>

            </div>
            <div class="card-body">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Supplier</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               oninput="this.value = this.value.toUpperCase()" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">Nomor Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone') }}"
                               placeholder="(Opsional)">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-tags-fill me-2"></i>Daftar suppliers</h6>
            </div>
            <div class="card-body">

                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Supplier</th>
                            <th>Nomor Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $index => $supply)
                        <tr>
                            <td>{{ $suppliers->firstItem() + $index }}</td>
                            <td>{{ $supply->name }}</td>
                            <td>{{ $supply->phone }}</td>
                            <td>
                                <a href="{{ route('suppliers.edit', $supply->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('suppliers.destroy', $supply->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jenis ini?')">
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
                            <td colspan="3" class="text-center">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Ganti semua kode paginasi Anda dengan satu baris ini --}}
                <div class="d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">

                            <li class="page-item @if($suppliers->onFirstPage()) disabled @endif">
                                <a class="page-link" href="{{ $suppliers->previousPageUrl() }}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Previous</span>
                                </a>
                            </li>

                            <li class="page-item @if(!$suppliers->hasMorePages()) disabled @endif">
                                <a class="page-link" href="{{ $suppliers->nextPageUrl() }}" aria-label="Next">
                                    <span aria-hidden="true">Next &raquo;</span>
                                </a>
                            </li>

                        </ul>
                    </nav>

                    <div class="mt-2 text-muted text-center ms-3"> {{-- Saya tambahkan margin 'ms-3' agar rapi --}}
                        {{-- Ganti $productsTypes menjadi $suppliers --}}
                        Showing {{ $suppliers->firstItem() ?? 0 }}
                        to {{ $suppliers->lastItem() ?? 0 }}
                        of {{ $suppliers->total() }} results
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
