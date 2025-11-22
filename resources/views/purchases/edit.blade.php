@extends('layouts.app')

@section('title', 'Edit Pembelian')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-pencil-square me-2"></i>Edit Pembelian #{{ $purchase->purchase_code }}
        </h6>
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Pembelian
        </a>
    </div>

    <div class="card-body">
        <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
            @csrf
            @method('PUT')

            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <h6 class="alert-heading fw-bold">Validasi Gagal!</h6>
                <p>Data yang Anda masukkan tidak lengkap atau salah. Silakan perbaiki error di bawah ini:</p>
                <hr>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="supplier_name" class="form-label fw-semibold">Nama Supplier</label>
                    <input type="text" name="supplier_name" id="supplier_name" class="form-control"
                        placeholder="Masukkan nama supplier (baru atau lama)" required
                        value="{{ old('supplier_name', $purchase->supplier->name) }}">
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Nomor Telepon (Opsional)</label>
                    <input type="text" name="phone" id="phone" class="form-control"
                        placeholder="Masukkan no. telp supplier"
                        value="{{ old('phone', $purchase->supplier->phone) }}">
                </div>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status Pembelian</label>
                <select name="status" id="status" class="form-select">
                    <option value="pending" {{ $purchase->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ $purchase->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ $purchase->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="delivery_cost" class="form-label">Biaya Pengiriman</label>
                <input type="number" name="delivery_cost" id="delivery_cost" class="form-control"
                    placeholder="Masukkan biaya pengiriman"
                    value="{{ $purchase->delivery_cost }}">
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Detail Produk</h6>

            <div id="product-list">
                @foreach($purchase->details as $detail)
                <div class="row align-items-end mb-3 product-item">
                    <div class="col-md-5">
                        <label class="form-label">Produk</label>
                        <select name="products[]" class="form-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ $detail->product_id == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->color->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantities[]" class="form-control"
                            value="{{ $detail->quantity }}" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Harga Satuan</label>
                        <input type="number" name="prices[]" class="form-control"
                            value="{{ $detail->price }}" placeholder="Rp" required>
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-product mt-4">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" id="add-product" class="btn btn-sm btn-outline-primary mb-4">
                <i class="bi bi-plus-circle"></i> Tambah Produk
            </button>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('add-product').addEventListener('click', function () {
        const list = document.getElementById('product-list');
        const newItem = list.querySelector('.product-item').cloneNode(true);
        newItem.querySelectorAll('input').forEach(input => input.value = '');
        newItem.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        list.appendChild(newItem);
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-product')) {
            const item = e.target.closest('.product-item');
            if (document.querySelectorAll('.product-item').length > 1) {
                item.remove();
            }
        }
    });
</script>
@endsection
