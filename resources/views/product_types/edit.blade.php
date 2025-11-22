@extends('layouts.app')

@section('title', 'Edit Jenis Produk')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Edit Jenis Produk</h6>
        <a href="{{ route('product_types.index') }}" class="btn btn-secondary btn-sm">
             <i class="bi bi-arrow-left"></i> Kembali ke Daftar Jenis Produk
        </a>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('product_types.update', $productType->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Nama Jenis</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name"
                       value="{{ old('name', $productType->name) }}"
                       oninput="this.value = this.value.toUpperCase()" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-warning w-100">Update</button>
        </form>
    </div>
</div>
@endsection
