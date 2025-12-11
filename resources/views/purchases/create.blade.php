@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
@endpush

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-cart-plus me-2"></i>Tambah Pembelian Baru
        </h6>
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Pembelian
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
        <form action="{{ route('purchases.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="supplier-selector" class="form-label fw-semibold">Nama Supplier</label>
                    <input type="hidden" name="supplier_id" id="supplier_id" value="{{ old('supplier_id', '') }}">
                    <input type="text"
                        id="supplier-selector"
                        class="form-control"
                        placeholder="Ketik untuk cari / tambah supplier..."
                        value="{{ optional($suppliers->firstWhere('id', old('supplier_id')))->name }}"
                        autocomplete="off">

                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Nomor Telepon</label>
                    <input type="text" name="phone" id="select-phone" class="form-control"
                        placeholder="Masukkan no. telp supplier"
                        value="{{ old('phone', optional($suppliers->firstWhere('id', old('supplier_id')))->phone) }}">
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="bi bi-box-seam me-2"></i>Cari Produk
                            </h6>
                        </div>
                        <div class="card-body">
                            {{-- Search Bar --}}
                            {{-- Form pencarian produk (pisahkan dari submit utama) --}}
                            <form method="GET" action="{{ route('purchases.create') }}" class="mb-4" id="searchForm" onsubmit="event.stopPropagation();">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="search" name="search" class="form-control"
                                        placeholder="Cari produk..."
                                        value="{{ request('search') }}">
                                    @if(request('type_id'))
                                        <input type="hidden" name="type_id" value="{{ request('type_id') }}">
                                    @endif
                                    @if(request('color_id'))
                                        <input type="hidden" name="color_id" value="{{ request('color_id') }}">
                                    @endif
                                    <button class="btn btn-primary" type="button" id="searchSubmitBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>

                            {{-- Filter dan Scan QR --}}
                            <div class="d-flex gap-2 flex-wrap mt-3">
                                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#purchaseQrScannerModal">
                                    <i class="bi bi-qr-code-scan me-1"></i> Scan QR
                                </button>

                                <div class="dropdown" style="z-index: 1030;">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-funnel me-1"></i> Jenis
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><h6 class="dropdown-header">Jenis Barang</h6></li>
                                        @foreach($productTypes as $type)
                                            <li>
                                                <a class="dropdown-item {{ request('type_id') == $type->id ? 'active' : '' }}"
                                                    href="{{ request()->fullUrlWithQuery(['type_id' => $type->id]) }}">
                                                    {{ $type->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger"
                                                href="{{ request()->fullUrlWithQuery(['type_id' => null]) }}">
                                                Reset Jenis
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="dropdown" style="z-index: 1030;">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-palette me-1"></i> Warna
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><h6 class="dropdown-header">Warna Barang</h6></li>
                                        @foreach($productColors as $color)
                                            <li>
                                                <a class="dropdown-item {{ request('color_id') == $color->id ? 'active' : '' }}"
                                                    href="{{ request()->fullUrlWithQuery(['color_id' => $color->id]) }}">
                                                    {{ $color->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger"
                                                href="{{ request()->fullUrlWithQuery(['color_id' => null]) }}">
                                                Reset Warna
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Badge Filter Aktif --}}
                    @if(request('type_id') || request('color_id'))
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="text-muted fw-semibold me-1">Filter Aktif:</span>
                        @if(request('type_id'))
                            @php
                                $activeType = $productTypes->firstWhere('id', request('type_id'));
                            @endphp
                            <span class="badge bg-primary d-flex align-items-center">
                                Jenis: {{ $activeType->name ?? 'Tidak diketahui' }}
                                <a class="text-white ms-2" style="text-decoration: none; font-weight: 700;"
                                   href="{{ request()->fullUrlWithQuery(['type_id' => null]) }}">&times;</a>
                            </span>
                        @endif
                        @if(request('color_id'))
                            @php
                                $activeColor = $productColors->firstWhere('id', request('color_id'));
                            @endphp
                            <span class="badge bg-success d-flex align-items-center">
                                Warna: {{ $activeColor->name ?? 'Tidak diketahui' }}
                                <a class="text-white ms-2" style="text-decoration: none; font-weight: 700;"
                                   href="{{ request()->fullUrlWithQuery(['color_id' => null]) }}">&times;</a>
                            </span>
                        @endif
                    </div>
                    @endif

                    {{-- Tabel Produk --}}
                    <div class="table-responsive" style="max-height: 50vh; border-radius: 8px; overflow: auto;">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="align-middle">Produk</th>
                                    <th class="align-middle text-end" style="width: 150px;">Harga Beli</th>
                                    <th class="align-middle text-center" style="width: 70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productSearchList">
                                @if(isset($hasFilter) && $hasFilter)
                                    @forelse($products as $product)
                                    <tr class="product-search-row align-middle">
                                        <td>
                                            <div class="fw-semibold mb-1">
                                                {{ $product->name }}
                                                @if($product->color)
                                                    <small class="text-muted">({{ $product->color->name }})</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold text-success">Rp {{ number_format($product->price_buy ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-primary btn-sm add-product-to-form"
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }} ({{ $product->color?->name ?? '-' }})"
                                                data-price-buy="{{ $product->price_buy ?? 0 }}"
                                                title="Tambah ke form pembelian">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="bi bi-search me-2"></i>Tidak ada produk yang ditemukan.
                                        </td>
                                    </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">
                                            <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="mt-3 mb-1 fw-semibold">Silakan cari atau filter produk</p>
                                            <small class="text-muted">Gunakan search bar, filter jenis, atau filter warna di atas</small>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Detail Produk Pembelian</h6>

                    <div id="product-list">
                        <div class="row align-items-end mb-3 product-item">
                            <div class="col-md-4">
                                <label class="form-label">Produk</label>
                                <input type="hidden" name="products[]" class="product-id" value="">
                                <input type="text" class="form-control product-name" placeholder="Pilih produk dari tabel" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jumlah (PCS)</label>
                                <input type="number" name="quantities[]" class="form-control quantity" placeholder="0" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Harga Beli (PCS)</label>
                                <input type="number" name="prices[]" class="form-control price" placeholder="Rp" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control subtotal" value="Rp 0" readonly>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-product mt-4">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-product" class="btn btn-sm btn-outline-primary mb-4">
                        <i class="bi bi-plus-circle"></i> Tambah Baris
                    </button>
                </div>
            </div>

            <hr>

            <div class="text-end">
                <h5>Total Pembelian: <span id="total-purchase" class="fw-bold text-success">Rp 0</span></h5>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Simpan Pembelian
                </button>
            </div>
        </form>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {


    const productList = document.getElementById('product-list');
    const addProductBtn = document.getElementById('add-product');
    const purchaseForm = document.querySelector('form[action="{{ route('purchases.store') }}"]');
    const supplierHiddenInput = document.getElementById('supplier_id');
    const supplierSelectorInput = document.getElementById('supplier-selector');
    const phoneInput = document.getElementById('select-phone');
    const supplierStoreUrl = "{{ route('suppliers.store') }}";
    const supplierOptions = @json($supplierOptions);
    const initialSupplierId = "{{ old('supplier_id', '') }}";
    let supplierTomSelect = null;
    const pendingNewSuppliers = {};
    const NEW_SUPPLIER_PREFIX = '__new__:';
    let suppressPhoneAutofill = false;
    const purchaseSearchForm = document.getElementById('searchForm');
    const purchaseSearchBtn = document.getElementById('searchSubmitBtn');
    const AUTO_ADD_KEY_ID = 'purchase_auto_add_product_id';
    const AUTO_ADD_KEY_NAME = 'purchase_auto_add_product_name';
    // Ekspos key ke global supaya dipakai di script scanner di bawah
    window.PURCHASE_AUTO_ADD_KEY_ID = AUTO_ADD_KEY_ID;
    window.PURCHASE_AUTO_ADD_KEY_NAME = AUTO_ADD_KEY_NAME;

    function sanitizeNumber(value) {
        if (value === null || value === undefined) return '';
        return value.toString().replace(/\s+/g, '').replace(',', '.');
    }

    function getSupplierOptionData(value) {
        if (!value || !supplierTomSelect) {
            return null;
        }
        return supplierTomSelect.options[value] || null;
    }

    function handleSupplierSelection(value) {
        if (supplierHiddenInput) {
            const isNewValue = value && value.startsWith(NEW_SUPPLIER_PREFIX);
            supplierHiddenInput.value = isNewValue ? '' : (value || '');
        }

        if (!phoneInput) {
            return;
        }

        if (suppressPhoneAutofill) {
            suppressPhoneAutofill = false;
            return;
        }

        if (!value) {
            phoneInput.value = '';
            return;
        }

        const optionData = getSupplierOptionData(value);
        if (optionData && optionData.phone) {
            phoneInput.value = optionData.phone;
        } else if (!phoneInput.value) {
            phoneInput.value = '';
        }
    }

    async function createSupplierOnServer(name) {
        const payload = {
            name: name,
            phone: phoneInput ? phoneInput.value.trim() : ''
        };

        let response;

        try {
            response = await fetch(supplierStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
        } catch (error) {
            throw new Error('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
        }

        let data = null;

        try {
            data = await response.json();
        } catch (parseError) {
            data = null;
        }

        if (!response.ok) {
            let message = 'Gagal menambahkan supplier baru.';

            if (data && data.errors) {
                const errorMessages = [];
                Object.keys(data.errors).forEach(function(key) {
                    const current = data.errors[key];
                    if (Array.isArray(current)) {
                        current.forEach(function(item) {
                            errorMessages.push(item);
                        });
                    } else if (typeof current === 'string') {
                        errorMessages.push(current);
                    }
                });
                if (errorMessages.length > 0) {
                    message = errorMessages.join('\n');
                }
            } else if (data && data.message) {
                message = data.message;
            }

            throw new Error(message);
        }

        if (!data || !data.supplier) {
            throw new Error('Data supplier baru tidak valid.');
        }

        return data.supplier;
    }

    function initializeSupplierTomSelect() {
        if (!supplierSelectorInput) {
            return;
        }

        if (typeof TomSelect === 'undefined') {
            console.warn('TomSelect belum dimuat. Input supplier berjalan tanpa fitur pencarian.');
            return;
        }

        supplierTomSelect = new TomSelect('#supplier-selector', {
            maxItems: 1,
            openOnFocus: false,
            valueField: 'value',
            labelField: 'name',
            searchField: ['name', 'phone'],
            sortField: { field: 'name', direction: 'asc' },
            placeholder: supplierSelectorInput.getAttribute('placeholder') || 'Ketik nama supplier...',
            options: supplierOptions,
            render: {
                option: function(data, escape) {
                    const name = data.name || data.text || '';
                    const phone = data.phone ? '<small class="text-muted d-block">' + escape(data.phone) + '</small>' : '';
                    return '<div><span class="fw-semibold">' + escape(name) + '</span>' + phone + '</div>';
                },
                item: function(data, escape) {
                    const label = data.name || data.text || '';
                    return '<div>' + escape(label) + '</div>';
                },
                option_create: function(data, escape) {
                    return '<div class="create">Tambah supplier baru: <strong>' + escape(data.input) + '</strong></div>';
                }
            },
            create: function(input, callback) {
                const sanitized = input.trim();
                if (!sanitized) {
                    callback();
                    return;
                }

                const tempValue = NEW_SUPPLIER_PREFIX + Date.now();
                pendingNewSuppliers[tempValue] = sanitized;

                callback({
                    value: tempValue,
                    text: sanitized + ' (Baru)',
                    name: sanitized,
                    phone: phoneInput ? phoneInput.value.trim() : '',
                    isNew: true
                });

                supplierTomSelect.setValue(tempValue);
            }
        });

        supplierTomSelect.on('change', function(value) {
            handleSupplierSelection(value);
        });

        if (initialSupplierId) {
            suppressPhoneAutofill = phoneInput && phoneInput.value.trim().length > 0;
            supplierTomSelect.setValue(initialSupplierId);
        }
    }

    if (supplierSelectorInput) {
        initializeSupplierTomSelect();

        if (!supplierTomSelect && initialSupplierId && phoneInput) {
            const fallbackOption = supplierOptions.find(option => option.value == initialSupplierId);
            if (fallbackOption && fallbackOption.phone && !phoneInput.value) {
                phoneInput.value = fallbackOption.phone;
            }
        }
    }

    function updateCalculations() {
        let total = 0;
        const rows = document.querySelectorAll('.product-item');

        rows.forEach(row => {
            const qty = parseFloat(sanitizeNumber(row.querySelector('.quantity')?.value || 0)) || 0;
            const price = parseFloat(sanitizeNumber(row.querySelector('.price')?.value || 0)) || 0;
            const subtotal = qty * price;

            const subtotalField = row.querySelector('.subtotal');
            if (subtotalField) {
                subtotalField.value = 'Rp ' + subtotal.toLocaleString('id-ID');
            }
            total += subtotal;
        });

        const totalField = document.getElementById('total-purchase');
        if (totalField) {
            totalField.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
    }

    // Handle klik tombol tambah produk dari tabel
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-product-to-form')) {
            const btn = e.target.closest('.add-product-to-form');
            const productId = btn.dataset.productId;
            const productName = btn.dataset.productName;
            const priceBuy = parseFloat(btn.dataset.priceBuy) || 0;

            // Cari baris kosong pertama atau tambahkan baris baru
            let emptyRow = null;
            const allRows = productList.querySelectorAll('.product-item');

            allRows.forEach(row => {
                const idInput = row.querySelector('.product-id');
                if (idInput && !idInput.value) {
                    emptyRow = row;
                }
            });

            if (!emptyRow) {
                // Tambahkan baris baru
                const firstItem = productList.querySelector('.product-item');
                emptyRow = firstItem.cloneNode(true);
                emptyRow.querySelector('.product-id').value = '';
                emptyRow.querySelector('.product-name').value = '';
                emptyRow.querySelector('.quantity').value = '';
                emptyRow.querySelector('.price').value = '';
                emptyRow.querySelector('.subtotal').value = 'Rp 0';
                productList.appendChild(emptyRow);
            }

            // Isi data produk
            emptyRow.querySelector('.product-id').value = productId;
            emptyRow.querySelector('.product-name').value = productName;
            if (priceBuy > 0) {
                emptyRow.querySelector('.price').value = priceBuy;
            }
            emptyRow.querySelector('.quantity').focus();

            updateCalculations();
        }
    });

    addProductBtn.addEventListener('click', function() {
        const firstItem = productList.querySelector('.product-item');
        const newItem = firstItem.cloneNode(true);

        // Reset values
        newItem.querySelector('.product-id').value = '';
        newItem.querySelector('.product-name').value = '';
        newItem.querySelector('.quantity').value = '';
        newItem.querySelector('.price').value = '';
        newItem.querySelector('.subtotal').value = 'Rp 0';

        productList.appendChild(newItem);
    });

    productList.addEventListener('click', function(e) {
        if (e.target.closest('.remove-product')) {
            const allItems = document.querySelectorAll('.product-item');
            if (allItems.length > 1) {
                e.target.closest('.product-item').remove();
                updateCalculations();
            } else {
                alert('Setidaknya harus ada satu baris produk.');
            }
        }
    });

    productList.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
            const sanitized = sanitizeNumber(e.target.value);
            if (sanitized !== e.target.value) {
                e.target.value = sanitized;
            }
            updateCalculations();
        }
    });


    updateCalculations();

    // Pastikan form pencarian tidak memicu submit form utama
    if (purchaseSearchForm && purchaseSearchBtn) {
        purchaseSearchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            purchaseSearchForm.submit();
        });
    }

    // Auto-add produk setelah scan QR dan reload search
    function tryAutoAddProductFromStorage(attempt = 0) {
        const productId = sessionStorage.getItem(AUTO_ADD_KEY_ID);
        const productName = sessionStorage.getItem(AUTO_ADD_KEY_NAME);
        let btn = null;

        if (productId) {
            btn = document.querySelector(`.add-product-to-form[data-product-id="${productId}"]`);
        }

        // Fallback: cari tombol berdasarkan nama yang mengandung kata kunci scan
        if (!btn && productName) {
            const buttons = document.querySelectorAll('.add-product-to-form');
            buttons.forEach(b => {
                if (btn) return;
                const name = (b.dataset.productName || '').toLowerCase();
                if (name.includes(productName.toLowerCase().trim())) {
                    btn = b;
                }
            });
        }

        if (btn) {
            btn.click();
            sessionStorage.removeItem(AUTO_ADD_KEY_ID);
            sessionStorage.removeItem(AUTO_ADD_KEY_NAME);
            return;
        }

        // Tambah retry dengan jeda lebih banyak untuk tunggu render tabel
        if (attempt < 15) {
            setTimeout(() => tryAutoAddProductFromStorage(attempt + 1), 250);
        }
    }

    // Coba jalankan setelah render
    tryAutoAddProductFromStorage();

    if (purchaseForm) {
        let isSubmittingPurchase = false;

        async function ensureSupplierExistsBeforeSubmit(event) {
            if (!supplierTomSelect) {
                return true;
            }

            const currentValue = supplierTomSelect.getValue();
            const selectedValue = Array.isArray(currentValue) ? currentValue[0] : currentValue;

            if (!selectedValue || !selectedValue.startsWith(NEW_SUPPLIER_PREFIX)) {
                return true;
            }

            event.preventDefault();

            if (isSubmittingPurchase) {
                return false;
            }
            isSubmittingPurchase = true;

            const supplierName = pendingNewSuppliers[selectedValue];
            if (!supplierName) {
                alert('Nama supplier baru tidak ditemukan. Silakan ulangi.');
                isSubmittingPurchase = false;
                return false;
            }

            try {
                const newSupplier = await createSupplierOnServer(supplierName);
                const newValue = newSupplier.id.toString();

                supplierTomSelect.addOption({
                    value: newValue,
                    text: newSupplier.name,
                    name: newSupplier.name,
                    phone: newSupplier.phone || ''
                });
                supplierTomSelect.setValue(newValue, true);
                handleSupplierSelection(newValue);

                document.querySelectorAll('.quantity, .price').forEach(input => {
                    input.value = sanitizeNumber(input.value);
                });

                purchaseForm.submit();
            } catch (error) {
                console.error(error);
                alert(error && error.message ? error.message : 'Gagal menambahkan supplier baru.');
                isSubmittingPurchase = false;
            }

            return false;
        }

        purchaseForm.addEventListener('submit', function(event) {
            if (isSubmittingPurchase) {
                event.preventDefault();
                return;
            }

            const currentValue = supplierTomSelect ? supplierTomSelect.getValue() : null;
            const selectedValue = Array.isArray(currentValue) ? currentValue[0] : currentValue;

            if (selectedValue && selectedValue.startsWith(NEW_SUPPLIER_PREFIX)) {
                ensureSupplierExistsBeforeSubmit(event);
                return;
            }

            document.querySelectorAll('.quantity, .price').forEach(input => {
                input.value = sanitizeNumber(input.value);
            });
        });
    }

});
</script>

{{-- Modal QR Scanner untuk Pembelian --}}
<div class="modal fade" id="purchaseQrScannerModal" tabindex="-1" aria-labelledby="purchaseQrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="purchaseQrScannerModalLabel">
                    <i class="bi bi-qr-code-scan me-2"></i>Scan QR Code Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="closePurchaseScannerBtn"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <div id="purchase-qr-reader" style="width: 100%; min-height: 240px; border: 2px dashed #dee2e6; border-radius: 12px; background: #f8f9fa; position: relative; overflow: hidden;">
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            <div class="text-center">
                                <i class="bi bi-camera-video" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">Memuat kamera...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="purchase-qr-reader-results"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="stopPurchaseScannerBtn">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let purchaseQrScanner = null;
    let isPurchaseScanning = false;
    const AUTO_ADD_KEY_ID = window.PURCHASE_AUTO_ADD_KEY_ID || 'purchase_auto_add_product_id';
    const AUTO_ADD_KEY_NAME = window.PURCHASE_AUTO_ADD_KEY_NAME || 'purchase_auto_add_product_name';

    // Initialize scanner when modal is shown
    document.getElementById('purchaseQrScannerModal').addEventListener('shown.bs.modal', function () {
        if (!isPurchaseScanning) {
            startPurchaseQRScanner();
        }
    });

    // Stop scanner when modal is hidden
    document.getElementById('purchaseQrScannerModal').addEventListener('hidden.bs.modal', function () {
        stopPurchaseQRScanner();
    });

    // Stop scanner when close button is clicked
    document.getElementById('closePurchaseScannerBtn').addEventListener('click', function () {
        stopPurchaseQRScanner();
    });

    document.getElementById('stopPurchaseScannerBtn').addEventListener('click', function () {
        stopPurchaseQRScanner();
    });

    function startPurchaseQRScanner() {
        const qrReaderElement = document.getElementById('purchase-qr-reader');
        const qrResultElement = document.getElementById('purchase-qr-reader-results');

        qrResultElement.innerHTML = `
            <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-camera-video-fill me-2" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>Memuat kamera...</strong> Arahkan kamera ke QR code produk untuk memindai.
                </div>
            </div>
        `;

        purchaseQrScanner = new Html5Qrcode("purchase-qr-reader");

        purchaseQrScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: null,
                aspectRatio: 1.0
            },
            (decodedText, decodedResult) => {
                handlePurchaseQRScanned(decodedText);
            },
            (errorMessage) => {
                // Error callback - ignore
            }
        ).then(() => {
            isPurchaseScanning = true;
            qrResultElement.innerHTML = `
                <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                    <i class="bi bi-camera-video-fill me-2" style="font-size: 1.25rem;"></i>
                    <div>
                        <strong>Kamera aktif!</strong> Arahkan kamera ke QR code produk untuk memindai.
                    </div>
                </div>
            `;
        }).catch((err) => {
            console.error("Unable to start scanning", err);
            qrResultElement.innerHTML = `
                <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.25rem;"></i>
                    <div>
                        <strong>Gagal mengakses kamera.</strong> Pastikan Anda memberikan izin akses kamera.
                    </div>
                </div>
            `;
            isPurchaseScanning = false;
        });
    }

    function stopPurchaseQRScanner() {
        if (purchaseQrScanner && isPurchaseScanning) {
            purchaseQrScanner.stop().then(() => {
                console.log("Purchase QR Scanner stopped");
                isPurchaseScanning = false;
            }).catch((err) => {
                console.error("Error stopping scanner", err);
                isPurchaseScanning = false;
            });
        }
    }

    async function handlePurchaseQRScanned(decodedText) {
        const qrResultElement = document.getElementById('purchase-qr-reader-results');

        stopPurchaseQRScanner();

        // Parse QR code - bisa berupa URL seperti /product/{id} atau /inventories/{id}
        let productUnitId = null;

        // Try to extract product unit ID from URL
        const productMatch = decodedText.match(/\/product\/(\d+)/);
        const inventoryMatch = decodedText.match(/\/inventories\/(\d+)/);

        if (productMatch) {
            productUnitId = productMatch[1];
        } else if (inventoryMatch) {
            productUnitId = inventoryMatch[1];
        } else if (/^\d+$/.test(decodedText.trim())) {
            // If it's just a number, assume it's the product unit ID
            productUnitId = decodedText.trim();
        } else {
            qrResultElement.innerHTML = `
                <div class="alert alert-danger mb-3" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.25rem;"></i>
                        <strong>Format QR code tidak dikenali.</strong>
                    </div>
                    <p class="mb-0">Pastikan QR code berisi ID produk yang valid.</p>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary btn-sm" onclick="startPurchaseQRScanner()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi
                    </button>
                </div>
            `;
            return;
        }

        if (productUnitId) {
            qrResultElement.innerHTML = `
                <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                    <i class="bi bi-hourglass-split me-2" style="font-size: 1.25rem;"></i>
                    <div><strong>Mencari produk...</strong></div>
                </div>
            `;

            // Fetch product unit untuk mendapatkan product ID
            try {
                // Gunakan path relatif untuk menghindari masalah subfolder
                const apiUrl = `{{ url('/api/product-unit') }}/${productUnitId}`;

                const response = await fetch(apiUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) {
                    throw new Error('Produk tidak ditemukan');
                }

                const data = await response.json();
                const productId = data.product_id;
                const productName = data.product_name || decodedText || '';

                // Simpan intent auto-add dan tampilkan produk di hasil pencarian
                sessionStorage.setItem(AUTO_ADD_KEY_ID, productId);
                sessionStorage.setItem(AUTO_ADD_KEY_NAME, productName);

                // Isi search lalu submit agar produk muncul di tabel
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput && productName) {
                    searchInput.value = productName;
                    setTimeout(() => {
                        document.getElementById('searchForm').submit();
                    }, 150);
                }

                qrResultElement.innerHTML = `
                    <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2" style="font-size: 1.25rem;"></i>
                        <div>
                            <strong>Produk ditemukan!</strong> Menampilkan di daftar dan menambahkan ke detail pembelian...
                        </div>
                    </div>
                `;

                // Tutup modal sesaat setelah memicu pencarian
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('purchaseQrScannerModal'));
                    if (modal) {
                        modal.hide();
                    }
                }, 300);
            } catch (error) {
                console.error('Error fetching product:', error);

                // Fallback: gunakan teks hasil scan sebagai kata kunci pencarian
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) {
                    const keyword = decodedText || '';
                    sessionStorage.removeItem(AUTO_ADD_KEY_ID); // tidak ada ID pasti
                    sessionStorage.setItem(AUTO_ADD_KEY_NAME, keyword);
                    searchInput.value = keyword;
                    setTimeout(() => {
                        document.getElementById('searchForm').submit();
                    }, 150);
                }

                qrResultElement.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                        <i class="bi bi-hourglass-split me-2" style="font-size: 1.25rem;"></i>
                        <div>
                            <strong>Produk dicari berdasarkan hasil scan.</strong> Setelah daftar muncul, sistem akan menambahkan otomatis jika ada yang cocok.
                        </div>
                    </div>
                `;

                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('purchaseQrScannerModal'));
                    if (modal) modal.hide();
                }, 300);
            }
        }
    }

    // Expose function untuk retry
    window.startPurchaseQRScanner = startPurchaseQRScanner;
</script>
@endpush

