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

            <h6 class="fw-bold mb-3">Detail Produk</h6>

            <div id="product-list">
                <div class="row align-items-end mb-3 product-item">
                    <div class="col-md-4">
                        <label class="form-label">Produk</label>
                        <select name="products[]" class="form-select product-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price-buy="{{ $product->price_buy ?? 0 }}">
                                    {{ $product->name }} ({{ $product->color->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jumlah (PCS) </label>
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
                <i class="bi bi-plus-circle"></i> Tambah Produk
            </button>

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
    let suppressPhoneAutofill = false;

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
            supplierHiddenInput.value = value || '';
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

                if (supplierTomSelect) {
                    supplierTomSelect.lock();
                }

                createSupplierOnServer(sanitized)
                    .then(function(newSupplier) {
                        const newValue = newSupplier.id.toString();
                        const optionData = {
                            value: newValue,
                            text: newSupplier.name,
                            name: newSupplier.name,
                            phone: newSupplier.phone || ''
                        };

                        callback(optionData);
                        supplierTomSelect.setValue(newValue);
                    })
                    .catch(function(error) {
                        console.error(error);
                        alert(error && error.message ? error.message : 'Gagal menambahkan supplier baru.');
                        callback();
                    })
                    .then(function() {
                        if (supplierTomSelect) {
                            supplierTomSelect.unlock();
                        }
                    });
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

    addProductBtn.addEventListener('click', function() {
        const firstItem = productList.querySelector('.product-item');
        const newItem = firstItem.cloneNode(true);

        newItem.querySelector('.product-select').value = '';
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

    productList.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const productItem = e.target.closest('.product-item');
            const priceInput = productItem.querySelector('.price');
            const selectedOption = e.target.options[e.target.selectedIndex];
            const priceBuy = selectedOption.dataset.priceBuy || 0;

            if (priceInput && priceBuy > 0) {
                priceInput.value = priceBuy;
                updateCalculations();
            }
        }
    });

    updateCalculations();

    if (purchaseForm) {
        purchaseForm.addEventListener('submit', function() {
            document.querySelectorAll('.quantity, .price').forEach(input => {
                input.value = sanitizeNumber(input.value);
            });
        });
    }

});
</script>
@endpush

