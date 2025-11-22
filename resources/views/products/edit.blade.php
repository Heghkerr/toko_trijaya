@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="card shadow-lg border-0">
    <div class="card-header bg-warning text-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="bi bi-pencil-square me-2"></i>Edit Produk</h5>
        <a href="{{ route('products.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <h6 class="alert-heading fw-bold">Validasi Gagal!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
         @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="border-start border-4 border-primary ps-3 mb-4">
                <h6 class="fw-bold text-primary mb-3">Informasi Utama & Harga Pokok</h6>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Nama Produk</label>
                        <input type="text" class="form-control" name="name"
                               value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Jenis Produk</label>
                        <input type="text" id="select-type" name="type_name"
                            value="{{ old('type_name', $product->type->name ?? '') }}"
                            oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Warna</label>
                        <input type="text" id="select-color" name="color"
                            value="{{ old('color', $product->color->name ?? '') }}"
                            oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Harga Beli (per Pcs)</label>
                        <input type="number" class="form-control" name="price_buy" min="0"
                               value="{{ old('price_buy', $product->price_buy) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Stok Minimum</label>
                        <input type="number" class="form-control" name="min_stock" min="0"
                               value="{{ old('min_stock', $product->min_stock) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Stok Maksimum</label>
                        <input type="number" class="form-control" name="max_stock" min="0"
                               value="{{ old('max_stock', $product->max_stock) }}" required>
                    </div>

                </div>
            </div>

            <div class="border-start border-4 border-success ps-3 mb-4">
                <h6 class="fw-bold text-success mb-3">Variasi Satuan, Harga Jual, & Stok</h6>

                <div id="unit-container">
                    @if(old('units'))

                        @foreach(old('units') as $index => $unit)
                        <div class="unit-item bg-light border rounded p-3 mb-3 shadow-sm">

                            @if(isset($unit['id']))
                            <input type="hidden" name="units[{{ $index }}][id]" value="{{ $unit['id'] }}">
                            @endif

                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Nama Satuan</label>
                                    <input type="text" name="units[{{ $index }}][name]" class="form-control" placeholder="cth: Grosir" value="{{ $unit['name'] }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Konversi (Pcs)</label>
                                    <input type="number" name="units[{{ $index }}][conversion_value]" class="form-control" placeholder="cth: 144" value="{{ $unit['conversion_value'] }}" required min="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Jual</label>
                                    <input type="number" name="units[{{ $index }}][price]" class="form-control" placeholder="Rp..." value="{{ $unit['price'] }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="units[{{ $index }}][stock]" class="form-control" placeholder="Jml Pack" value="{{ $unit['stock'] }}" required min="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-remove-unit w-100 {{ $loop->first ? 'd-none' : '' }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else

                        @foreach ($product->units as $index => $unit)
                        <div class="unit-item bg-light border rounded p-3 mb-3 shadow-sm">

                            <input type="hidden" name="units[{{ $index }}][id]" value="{{ $unit->id }}">

                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Nama Satuan</label>
                                    <input type="text" name="units[{{ $index }}][name]" class="form-control" value="{{ $unit->name }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Konversi (Pcs)</label>
                                    <input type="number" name="units[{{ $index }}][conversion_value]" class="form-control" value="{{ $unit->conversion_value }}" required min="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Jual</label>
                                    <input type="number" name="units[{{ $index }}][price]" class="form-control" value="{{ $unit->price }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="units[{{ $index }}][stock]" class="form-control" value="{{ $unit->stock }}" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-remove-unit w-100 {{ $loop->first ? 'd-none' : '' }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-outline-success btn-sm" id="add-unit">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Satuan Lain
                    </button>
                </div>
            </div>

            <div class="border-start border-4 border-warning ps-3 mb-4">
                <h6 class="fw-bold text-warning mb-3">Gambar Produk</h6>
                <div class="col-md-6">
                    @if ($product->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$product->image) }}" class="img-thumbnail" width="150" alt="Gambar Produk">
                    </div>
                    @endif
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="text-muted fst-italic">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                </div>
            </div>
            <div class="border-start border-4 border-info ps-3 mb-4">
                <h6 class="fw-bold text-info mb-3">Deskripsi Produk</h6>
                <textarea class="form-control" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-warning px-4 text-white">
                    <i class="bi bi-save me-1"></i> Update Produk
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>


        // Konversi data PHP ke JavaScript
        const productTypes = @json($productTypes);
        const productColors = @json($productColors);

        // Buat array 'options'
        const typeOptions = productTypes.map(type => ({ value: type, text: type }));
        const colorOptions = productColors.map(color => ({ value: color, text: color }));

        // Ambil nilai yang sudah ada dari input
        const currentType = document.getElementById('select-type').value;
        const currentColor = document.getElementById('select-color').value;

        // ===============================================
        // PENGATURAN TOM SELECT
        // ===============================================

        new TomSelect("#select-type", {
            create: true,
            maxItems: 1,
            openOnFocus: false,
            sortField: { field: "text", direction: "asc" },
            options: typeOptions,
            items: [currentType],
            render: {
                option_create: function(data, escape) {
                    return '<div class="create"><strong>' + escape(data.input) + '</strong></div>';
                }
            }
        });

        new TomSelect("#select-color", {
            create: true,
            maxItems: 1,
            openOnFocus: false,
            sortField: { field: "text", direction: "asc" },
            options: colorOptions,
            items: [currentColor],
            render: {
                option_create: function(data, escape) {
                    return '<div class="create"><strong>' + escape(data.input) + '</strong></div>';
                }
            }
        });

        // ===============================================
        // SKRIP UNTUK "TAMBAH SATUAN"
        // ===============================================
        let unitIndex = {{ old('units') ? count(old('units')) : $product->units->count() }};

        document.getElementById('add-unit').addEventListener('click', function() {
            const container = document.getElementById('unit-container');
            const newUnit = document.createElement('div');
            newUnit.classList.add('unit-item', 'bg-light', 'border', 'rounded', 'p-3', 'mb-3', 'shadow-sm');

            newUnit.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Nama Satuan</label>
                        <input type="text" name="units[${unitIndex}][name]" class="form-control" placeholder="cth: Grosir" oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Konversi (Pcs)</label>
                        <input type="number" name="units[${unitIndex}][conversion_value]" class="form-control" placeholder="cth: 144" required min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" name="units[${unitIndex}][price]" class="form-control" placeholder="Rp..." required min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Stok</label>
                        <input type="number" name="units[${unitIndex}][stock]" class="form-control" placeholder="Jml Pack" required min="0" value="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-remove-unit w-100">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newUnit);
            unitIndex++;
        });

        // Script untuk hapus (Sudah Benar)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-unit')) {
                e.target.closest('.unit-item').remove();
            }
        });


</script>
@endpush
@endsection
