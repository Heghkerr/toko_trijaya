@extends('layouts.app')

@section('title', 'Konversi Stok Barang')

@section('content')

{{-- Tampilkan Error Validasi --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <h5 class="alert-heading">Validasi Gagal!</h5>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('inventories.convert.store') }}" method="POST">
    @csrf
    <div class="row">
        {{-- Card Utama --}}
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-arrow-repeat me-2"></i>Form Konversi Stok
                    </h6>
                </div>
                <div class="card-body">

                    {{-- 1. Pilih Produk --}}
                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-bold">1. Pilih Produk</label>
                        <p class="form-text text-muted mt-0">Hanya produk yang memiliki lebih dari 1 satuan yang tampil di sini.</p>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="" disabled selected>Pilih produk yang akan dikonversi...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->color->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-4">

                    {{-- 2. Detail Konversi --}}
                    <div class="row">
                        {{-- DARI (SUMBER) --}}
                        <div class="col-md-5">
                            <h5 class="fw-bold text-danger">Dari (Sumber)</h5>
                            <div class="mb-3">
                                <label for="from_product_unit_id" class="form-label">Satuan Sumber</label>
                                <select class="form-select" id="from_product_unit_id" name="from_product_unit_id" required disabled>
                                    <option value="">Pilih produk dulu...</option>
                                </select>
                                {{-- [DIUBAH] Info stok ini akan kita buat dinamis dengan class --}}
                                <div class="form-text" id="from_stock_info">Stok Saat Ini: -</div>
                            </div>
                            <div class="mb-3">
                                {{-- [DIUBAH] Input ini sekarang 'readonly' (dihitung otomatis) --}}
                                <label for="quantity_from" class="form-label">Jumlah Dibutuhkan</label>
                                <input type="text" class="form-control" id="quantity_from" readonly>
                                <div class="form-text text-danger d-none" id="quantity_warning"></div>
                            </div>
                        </div>

                        {{-- Panah --}}
                        <div class="col-md-2 d-flex align-items-center justify-content-center fs-1 text-primary">
                            <i class="bi bi-arrow-right-circle-fill"></i>
                        </div>

                        {{-- KE (TUJUAN) --}}
                        <div class="col-md-5">
                            <h5 class="fw-bold text-success">Ke (Tujuan)</h5>
                            <div class="mb-3">
                                <label for="to_product_unit_id" class="form-label">Satuan Tujuan</label>
                                <select class="form-select" id="to_product_unit_id" name="to_product_unit_id" required disabled>
                                    <option value="">Pilih produk dulu...</option>
                                </select>
                                <div class="form-text" id="to_stock_info">Stok Saat Ini: -</div>
                            </div>
                            <div class="mb-3">
                                {{-- [DIUBAH] Input ini sekarang bisa diedit dan punya 'name' --}}
                                <label for="quantity_to" class="form-label">Jumlah Diinginkan</label>
                                <input type="number" step="1" min="1" class="form-control" id="quantity_to" name="quantity_to"
                                    value="{{ old('quantity_to') }}" required inputmode="numeric">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                     {{-- 3. Keterangan --}}
                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Misal: Konversi untuk penjualan eceran">{{ old('description') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- Card Submit --}}
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Aksi</h6>
                </div>
                <div class="card-body">
                    <p>Pastikan data konversi sudah benar sebelum disimpan. Stok akan langsung diperbarui.</p>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="submit_button">
                            <i class="bi bi-save me-1"></i> Simpan Konversi
                        </button>
                        <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary mt-2">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const fromUnitSelect = document.getElementById('from_product_unit_id');
    const toUnitSelect = document.getElementById('to_product_unit_id');
    const fromStockInfo = document.getElementById('from_stock_info');
    const toStockInfo = document.getElementById('to_stock_info');
    const submitButton = document.getElementById('submit_button');

    // [DIUBAH] Variabel input/output dibalik
    const qtyFromInput = document.getElementById('quantity_from'); // Output (Readonly)
    const qtyToInput = document.getElementById('quantity_to');     // Input (Editable)
    const qtyWarning = document.getElementById('quantity_warning');

    const unitsApiUrl = "{{ route('inventories.getUnits') }}";
    let productUnitsData = [];

    // --- Event Listener Utama ---
    productSelect.addEventListener('change', fetchProductUnits);
    fromUnitSelect.addEventListener('change', updateStockInfo);
    toUnitSelect.addEventListener('change', updateStockInfo);

    // [DIUBAH] Listener sekarang ada di 'qtyToInput'
    [qtyToInput, fromUnitSelect, toUnitSelect].forEach(el => {
        el.addEventListener('change', calculateConversion);
        el.addEventListener('keyup', calculateConversion);
        el.addEventListener('input', calculateConversion);
    });

    // --- Fungsi Fetch Units ---
    function fetchProductUnits() {
        // ... (Fungsi ini SAMA seperti sebelumnya) ...
        const productId = productSelect.value;
        if (!productId) {
            resetDropdowns();
            return;
        }

        fromUnitSelect.innerHTML = '<option value="">Loading...</option>';
        toUnitSelect.innerHTML = '<option value="">Loading...</option>';
        fromUnitSelect.disabled = true;
        toUnitSelect.disabled = true;
        submitButton.disabled = true;

        fetch(`${unitsApiUrl}?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                productUnitsData = data;
                populateDropdowns(data);
            })
            .catch(error => {
                console.error('Error fetching units:', error);
                alert('Gagal mengambil data satuan. Coba muat ulang halaman.');
                resetDropdowns();
            });
    }

    // --- Fungsi Populate Dropdowns ---
    function populateDropdowns(units) {
        // ... (Fungsi ini SAMA seperti sebelumnya) ...
        fromUnitSelect.innerHTML = '<option value="" disabled selected>Pilih satuan sumber...</option>';
        toUnitSelect.innerHTML = '<option value="" disabled selected>Pilih satuan tujuan...</option>';

        units.forEach(unit => {
            const optionText = `${unit.name} (Stok: ${unit.stock} | Konversi: ${unit.conversion_value})`;

            const fromOption = document.createElement('option');
            fromOption.value = unit.id;
            fromOption.textContent = optionText;
            fromOption.dataset.stock = unit.stock;
            fromOption.dataset.conv = unit.conversion_value;
            fromUnitSelect.appendChild(fromOption);

            const toOption = document.createElement('option');
            toOption.value = unit.id;
            toOption.textContent = optionText;
            toOption.dataset.stock = unit.stock;
            toOption.dataset.conv = unit.conversion_value;
            toUnitSelect.appendChild(toOption);
        });

        fromUnitSelect.disabled = false;
        toUnitSelect.disabled = false;
        submitButton.disabled = false;

        const oldFromUnit = "{{ old('from_product_unit_id') }}";
        const oldToUnit = "{{ old('to_product_unit_id') }}";

        if (oldFromUnit) {
            fromUnitSelect.value = oldFromUnit;
        }
        if (oldToUnit) {
            toUnitSelect.value = oldToUnit;
        }

        updateStockInfo();
        calculateConversion();
    }

    // --- Fungsi Update Info Stok ---
    function updateStockInfo() {
        // ... (Fungsi ini SAMA seperti sebelumnya, tapi akan dipanggil oleh calculateConversion) ...
        const selectedFrom = fromUnitSelect.options[fromUnitSelect.selectedIndex];
        const selectedTo = toUnitSelect.options[toUnitSelect.selectedIndex];

        if (selectedFrom && selectedFrom.value) {
            // Reset tampilan info stok
            fromStockInfo.className = 'form-text';
            fromStockInfo.textContent = `Stok Saat Ini: ${selectedFrom.dataset.stock || 0}`;
        } else {
            fromStockInfo.textContent = 'Stok Saat Ini: -';
        }

        if (selectedTo && selectedTo.value) {
            toStockInfo.textContent = `Stok Saat Ini: ${selectedTo.dataset.stock || 0}`;
        } else {
            toStockInfo.textContent = 'Stok Saat Ini: -';
        }

        // Panggil kalkulasi ulang untuk update warning stok
        calculateConversion();
    }

    // --- [LOGIKA DIUBAH] Fungsi Kalkulasi Otomatis ---
    function showQuantityWarning(message) {
        if (!qtyWarning) return;
        if (message) {
            qtyWarning.textContent = message;
            qtyWarning.classList.remove('d-none');
            qtyToInput.classList.add('is-invalid');
        } else {
            qtyWarning.textContent = '';
            qtyWarning.classList.add('d-none');
            qtyToInput.classList.remove('is-invalid');
        }
    }

    function calculateConversion() {
        const selectedFrom = fromUnitSelect.options[fromUnitSelect.selectedIndex];
        const selectedTo = toUnitSelect.options[toUnitSelect.selectedIndex];

        showQuantityWarning('');
        qtyFromInput.classList.remove('is-invalid');

        const qtyToRaw = qtyToInput.value.trim();
        const qtyTo = Number(qtyToRaw);
        const convFrom = parseFloat(selectedFrom ? selectedFrom.dataset.conv : 0);
        const convTo = parseFloat(selectedTo ? selectedTo.dataset.conv : 0);
        const stockFrom = parseFloat(selectedFrom ? selectedFrom.dataset.stock : 0);

        const resetOutput = () => {
            qtyFromInput.value = '';
            submitButton.disabled = true;
        };

        if (!qtyToRaw || !qtyTo || !convFrom || !convTo || convFrom === 0) {
            resetOutput();
            return;
        }

        if (!Number.isInteger(qtyTo)) {
            showQuantityWarning('Jumlah diinginkan harus bilangan bulat.');
            qtyFromInput.classList.add('is-invalid');
            resetOutput();
            return;
        }

        // [LOGIKA DIBALIK]
        const baseAmount = qtyTo * convTo;
        const qtyFrom = baseAmount / convFrom;
        const qtyFromRounded = Math.round(qtyFrom);
        const isQtyFromInteger = Math.abs(qtyFrom - qtyFromRounded) < 0.000001;

        if (!isQtyFromInteger) {
            showQuantityWarning('Konversi menghasilkan jumlah sumber pecahan. Sesuaikan jumlah tujuan agar menghasilkan bilangan bulat.');
            qtyFromInput.classList.add('is-invalid');
            resetOutput();
            return;
        }

        // Tampilkan hasil
        qtyFromInput.value = qtyFromRounded;

        // [LOGIKA BARU] Validasi Stok di Frontend untuk UX
        if (qtyFromRounded > stockFrom) {
            qtyFromInput.classList.add('is-invalid'); // Input jadi merah
            fromStockInfo.className = 'text-danger fw-bold'; // Teks info jadi merah
            fromStockInfo.textContent = `Stok Tidak Cukup! (Butuh: ${qtyFromRounded}, Ada: ${stockFrom})`;
            submitButton.disabled = true; // Nonaktifkan tombol
        } else {
            qtyFromInput.classList.remove('is-invalid');
            fromStockInfo.className = 'form-text'; // Kembalikan ke normal
            fromStockInfo.textContent = `Stok Saat Ini: ${stockFrom}`;
            submitButton.disabled = false; // Aktifkan tombol
        }

        // Nonaktifkan tombol jika konversi ke unit yang sama
        if (selectedFrom && selectedTo && selectedFrom.value === selectedTo.value) {
            submitButton.disabled = true;
        }
    }

    // --- Fungsi Reset ---
    function resetDropdowns() {
        // ... (Fungsi ini SAMA seperti sebelumnya) ...
        productUnitsData = [];
        fromUnitSelect.innerHTML = '<option value="">Pilih produk dulu...</option>';
        toUnitSelect.innerHTML = '<option value="">Pilih produk dulu...</option>';
        fromUnitSelect.disabled = true;
        toUnitSelect.disabled = true;
        fromStockInfo.textContent = 'Stok Saat Ini: -';
        toStockInfo.textContent = 'Stok Saat Ini: -';
        qtyFromInput.value = '';
        qtyToInput.value = '';
        submitButton.disabled = true;
    }

    // --- Inisialisasi ---
    if (productSelect.value) {
        fetchProductUnits();
    } else {
        submitButton.disabled = true; // Tombol nonaktif saat awal
    }
});
</script>
@endpush
