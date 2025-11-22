@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Edit Customer</h6>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
             <i class="bi bi-arrow-left"></i> Kembali ke Daftar Customer
        </a>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Nama Customer</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name"
                       value="{{ old('name', $customer->name) }}"
                       oninput="this.value = this.value.toUpperCase()" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label fw-semibold">Nomor Telepon</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                       id="phone" name="phone"
                       value="{{ old('phone', $customer->phone) }}"
                       placeholder="(Opsional)">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="address" class="form-label fw-semibold">Alamat</label>
                <textarea class="form-control @error('address') is-invalid @enderror"
                          id="address" name="address" rows="3"
                          placeholder="(Opsional)">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-warning w-100">Update</button>
        </form>
    </div>
</div>
@endsection
