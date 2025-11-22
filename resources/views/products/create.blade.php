@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="card shadow-lg border-0">
    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="bi bi-box-seam me-2"></i>Tambah Produk Baru</h5>
        <a href="{{ route('products.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Produk
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
        {{-- Akhir Error --}}

        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf


            <div class="border-start border-4 border-primary ps-3 mb-4">
                <h6 class="fw-bold text-primary mb-3">Informasi Utama</h6>
                <div class="row g-3">
                     <div class="col-md-5">
                        <label class="form-label fw-semibold">Nama Produk</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama produk" value="{{ old('name') }}"
                        oninput="this.value = this.value.toUpperCase()" required>
                    </div>


                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Jenis Produk</label>
                        {{-- Beri ID unik 'select-type' --}}
                        <input type="text" id="select-type" name="type_name"
                            placeholder="Ketik untuk cari/tambah jenis..." value="{{ old('type_name') }}"
                            oninput="this.value = this.value.toUpperCase()" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Warna</label>
                        {{-- Beri ID unik 'select-color' --}}
                        <input type="text" id="select-color" name="color"
                            placeholder="Ketik untuk cari/tambah warna..." value="{{ old('color') }}"
                            oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Harga Beli</label>
                        <input type="number" class="form-control" id="price_buy" name="price_buy" min="0" value="{{ old('price_buy') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Stok Minimum </label>
                        <input type="number" class="form-control" id="min_stock" name="min_stock" min="0" value="{{ old('min_stock') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Stok Maksimum</label>
                        <input type="number" class="form-control" id="max_stock" name="max_stock" min="0" value="{{ old('max_stock') }}" required>
                    </div>
                </div>
            </div


            <div class="border-start border-4 border-success ps-3 mb-4">
                <h6 class="fw-bold text-success mb-3">Variasi Satuan, Harga Jual, & Stok</h6>

                <div id="unit-container">
                    @if(old('units'))

                        @foreach(old('units') as $index => $unit)
                        <div class="unit-item bg-light border rounded p-3 mb-3 shadow-sm">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">

                                    <label class="form-label">Nama Satuan</label>

                                    <input type="text"
                                        name="units[{{ $index }}][name]"
                                        class="form-control unit-name-select" {{-- <-- Tambah class 'unit-name-select' --}}
                                        placeholder="cth: Pcs"
                                        value="{{ $unit['name'] }}" {{-- value biarkan untuk old() --}}
                                        oninput="this.value = this.value.toUpperCase()"
                                        required>


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
                                    <label class="form-label">Stok Awal</label>
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

                        <div class="unit-item bg-light border rounded p-3 mb-3 shadow-sm">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">

                                    <label class="form-label">Nama Satuan</label>

                                    <input type="text"
                                        name="units[0][name]"
                                        class="form-control unit-name-select" {{-- <-- Tambah class 'unit-name-select' --}}
                                        placeholder="cth: Pcs"
                                        oninput="this.value = this.value.toUpperCase()"
                                        required>

                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Konversi (Pcs)</label>
                                    {{-- 'value' dihapus --}}
                                    <input type="number" name="units[0][conversion_value]" class="form-control" placeholder="cth: 1" required min="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Jual</label>
                                    {{-- Ini sudah tidak punya 'value', jadi sudah benar --}}
                                    <input type="number" name="units[0][price]" class="form-control" placeholder="Rp..." required min="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Stok Awal</label>
                                    {{-- 'value' dihapus --}}
                                    <input type="number" name="units[0][stock]" class="form-control" placeholder="Jml Pack" required min="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-remove-unit w-100 d-none">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
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
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="text-muted fst-italic">Opsional, format: JPG/PNG, max 2MB.</small>
                </div>
            </div>


            <div class="border-start border-4 border-info ps-3 mb-4">
                <h6 class="fw-bold text-info mb-3">Deskripsi Produk</h6>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Tambahkan detail atau catatan tambahan...">{{ old('description') }}</textarea>
            </div>


            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>




@push('scripts')
<script>
// [PERBAIKAN 1: Bungkus semua kode dengan DOMContentLoaded]
// Ini memastikan script baru berjalan SETELAH halaman HTML siap
document.addEventListener('DOMContentLoaded', function() {

    // Konversi data PHP ke JavaScript
    const productTypes = @json($productTypes);
    const productColors = @json($productColors);
    const unitOptionsData = @json($unitOptions);

    // Buat array 'options'
    const typeOptions = productTypes.map(type => ({ value: type, text: type }));
    const colorOptions = productColors.map(color => ({ value: color, text: color }));
    const unitOptions = unitOptionsData.map(unit => ({
        value: unit.name,
        text: unit.name,
        conversion: unit.conversion
    }));

    // Render kustom untuk TomSelect
    const createRender = {
        option_create: function(data, escape) {
            return '<div class="create">Tambah <strong>' + escape(data.input) + '</strong>...</div>';
        }
    };

    // Inisialisasi TomSelect Tipe
    new TomSelect("#select-type", {
        create: true,
        maxItems: 1,
        openOnFocus: false,
        sortField: { field: "text", direction: "asc" },
        options: typeOptions,
        render: createRender
    });

    // Inisialisasi TomSelect Warna
    new TomSelect("#select-color", {
        create: true,
        maxItems: 1,
        openOnFocus: false,
        sortField: { field: "text", direction: "asc" },
        options: colorOptions,
        render: createRender
    });

    // Konfigurasi TomSelect Satuan
    const tomSelectUnitConfig = {
        create: true,
        maxItems: 1,
        openOnFocus: false,
        sortField: { field: "text", direction: "asc" },
        options: unitOptions,
        render: createRender,
        onChange: function(value) {
            if (!value) return;
            const unitItem = this.wrapper.closest('.unit-item');
            if (!unitItem) return;
            const conversionInput = unitItem.querySelector('input[name*="[conversion_value]"]');
            if (!conversionInput) return;

            const selectedData = this.options[value];
            if (selectedData && selectedData.conversion !== null && selectedData.conversion !== undefined) {
                conversionInput.value = selectedData.conversion;
            } else {
                conversionInput.value = '';
                conversionInput.placeholder = 'Isi manual...';
            }
        }
    };

    // Fungsi inisialisasi
    function initializeUnitTomSelect(element) {
        if (element.tomselect) {
            element.tomselect.destroy();
        }
        new TomSelect(element, tomSelectUnitConfig);
    }

    // Inisialisasi TomSelect untuk satuan yang sudah ada
    document.querySelectorAll('.unit-name-select').forEach(initializeUnitTomSelect);

    // Variabel index (sekarang di dalam DOMContentLoaded)
    let unitIndex = {{ old('units') ? count(old('units')) : 1 }};

    // Event listener untuk tombol "Tambah Satuan"
    document.getElementById('add-unit').addEventListener('click', function() {
        const container = document.getElementById('unit-container');
        const newUnit = document.createElement('div');
        newUnit.classList.add('unit-item', 'bg-light', 'border', 'rounded', 'p-3', 'mb-3', 'shadow-sm');

        // [PERBAIKAN 2: Ganti ${newIndex} menjadi ${unitIndex}]
        // Pastikan semua field menggunakan 'unitIndex'
        newUnit.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Nama Satuan</label>
                    <input type="text" name="units[${unitIndex}][name]"
                           class="form-control unit-name-select"
                           placeholder="cth: Pcs"
                           oninput="this.value = this.value.toUpperCase()"
                           required>
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
                    <label class="form-label">Stok Awal</label>
                    <input type="number" name="units[${unitIndex}][stock]" class="form-control" placeholder="Jml Pack" required min="0">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-remove-unit w-100">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newUnit);

        // [PERBAIKAN 3: Inisialisasi TomSelect pada baris baru]
        // Ini agar dropdown baru juga menjadi "pintar"
        const newSelectInput = newUnit.querySelector('.unit-name-select');
        initializeUnitTomSelect(newSelectInput);

        // Tambahkan index SETELAH baris baru dibuat
        unitIndex++;
    });

    // Event listener untuk Hapus (tidak berubah)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-unit')) {
            e.target.closest('.unit-item').remove();
        }
    });

}); // <-- Akhir dari DOMContentLoaded
</script>
@endpush
@endsection

