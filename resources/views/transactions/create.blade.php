@extends('layouts.app')

@section('title', 'Tambah Transaksi')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Transaksi Baru</h6>
        <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
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
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif


        <div class="row mb-4">

            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-box-seam me-2"></i>Cari Produk
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- Search Bar --}}
                        <form method="GET" action="{{ route('transactions.create') }}" class="mb-4" id="searchForm">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control"
                                    placeholder="Cari Produk..."
                                    value="{{ request('search') }}">
                                @if(request('type_id'))
                                    <input type="hidden" name="type_id" value="{{ request('type_id') }}">
                                @endif
                                @if(request('color_id'))
                                    <input type="hidden" name="color_id" value="{{ request('color_id') }}">
                                @endif
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Scan QR & Filter --}}
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#transactionQrScannerModal">
                                <i class="bi bi-qr-code-scan me-1"></i> Scan QR
                            </button>

                            {{-- Filter Jenis --}}
                            <div class="dropdown" style="z-index: 1030;">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-funnel me-1"></i> Jenis
                                </button>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Jenis Barang</h6></li>
                                    @foreach($productTypes as $type)
                                        <li>
                                            <a class="dropdown-item {{ request('type_id') == $type->id ? 'active' : '' }}"
                                                data-keep-cart="true"
                                                href="{{ request()->fullUrlWithQuery(['type_id' => $type->id]) }}">
                                                {{ $type->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" data-keep-cart="true"
                                            href="{{ request()->fullUrlWithQuery(['type_id' => null]) }}">
                                            Reset Jenis
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {{-- Filter Warna --}}
                            <div class="dropdown" style="z-index: 1030;">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-palette me-1"></i> Warna
                                </button>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Warna Barang</h6></li>
                                    @foreach($productColors as $color)
                                        <li>
                                            <a class="dropdown-item {{ request('color_id') == $color->id ? 'active' : '' }}"
                                                data-keep-cart="true"
                                                href="{{ request()->fullUrlWithQuery(['color_id' => $color->id]) }}">
                                                {{ $color->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" data-keep-cart="true"
                                            href="{{ request()->fullUrlWithQuery(['color_id' => null]) }}">
                                            Reset Warna
                                        </a>
                                    </li>
                                </ul>
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
                                       data-keep-cart="true"
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
                                       data-keep-cart="true"
                                       href="{{ request()->fullUrlWithQuery(['color_id' => null]) }}">&times;</a>
                                </span>
                            @endif
                        </div>
                        @endif

                        {{-- Tabel Produk (scrollable) --}}
                        <div class="table-responsive" style="max-height: 60vh; border-radius: 8px; overflow: auto;">
                            <table class="table table-hover table-bordered mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="align-middle">Produk (Variasi)</th>
                                        <th class="align-middle text-end" style="width: 130px;">Harga Jual</th>
                                        <th class="align-middle text-center" style="width: 90px;">Stok</th>
                                        <th class="align-middle text-center" style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="productList">

                                    @forelse($productUnits as $unit)
                                    <tr class="product-row align-middle">
                                        {{-- Kolom Nama --}}
                                        <td>
                                            <div class="fw-semibold mb-1">
                                                {{ $unit->product->name }}
                                                <small class="text-muted">({{ $unit->product->color?->name ?? '-' }})</small>
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge bg-primary">{{ $unit->name }}</span>
                                                <small class="text-muted ms-1">({{ $unit->conversion_value }} pcs)</small>
                                            </div>
                                        </td>

                                        {{-- Kolom Harga --}}
                                        <td class="text-end">
                                            <span class="fw-semibold text-success">Rp {{ number_format($unit->price, 0, ',', '.') }}</span>
                                        </td>

                                        {{-- Kolom Stok --}}
                                        <td class="text-center">
                                            @if($unit->stock > 0)
                                                <span class="badge bg-success stock-display">{{ $unit->stock }}</span>
                                            @else
                                                <span class="badge bg-danger stock-display">Habis</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Aksi --}}
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-primary btn-sm add-product"
                                                data-unit-id="{{ $unit->id }}"
                                                data-product-id="{{ $unit->product_id }}"
                                                data-name="{{ $unit->product->name }} ({{ $unit->name }})"
                                                data-unit-name="{{ $unit->name }}"
                                                data-color="{{ $unit->product->color?->name ?? '-' }}"
                                                data-price="{{ $unit->price }}"
                                                data-stock="{{ $unit->stock }}"
                                                data-conversion="{{ $unit->conversion_value }}"
                                                @if($unit->stock <= 0) disabled @endif>
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <i class="bi bi-search me-2"></i>Tidak ada produk yang ditemukan.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-7">
                <form id="transactionForm" method="POST" action="{{ route('transactions.store') }}"  >
                    @csrf
                    <h5>Detail Transaksi</h5>
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select-customer" class="form-label fw-semibold">Customer (Opsional)</label>
                                    <input type="text" id="select-customer" class="form-control"
                                        placeholder="Ketik untuk cari customer..."
                                        value="{{ old('customer_id') }}">
                                    <input type="hidden" id="customer_id_hidden" name="customer_id">
                                </div>
                                <div class="col-md-6">
                                    <label for="select-phone" class="form-label fw-semibold">Nomor Telepon</label>
                                    <input type="text" id="select-phone" class="form-control"
                                        placeholder="Masukkan no. telp customer"
                                        value="{{ old('phone') }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="paymentMethod" class="form-label fw-semibold">Metode Pembayaran</label>
                                    <select class="form-select" name="payment_method" id="paymentMethod" required>
                                        <option value="" selected disabled>-- Pilih Metode --</option>
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold">Status Pembelian</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="unpaid">Pending</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3" id="cashAmountField" style="display: none;">
                                <label class="form-label">Uang Tunai Diterima</label>
                                <input type="number" class="form-control" name="cash_amount" id="cashAmount" min="0" placeholder="Masukkan jumlah uang">

                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label">Diskon (Rp)</label>
                                    <input type="number" class="form-control" name="discount_amount" id="discount_amount" min="0" placeholder="Masukkan jumlah Rp">
                                </div>
                                <div class="col">
                                    <label class="form-label">Diskon (%)</label>
                                    <input type="number" class="form-control" name="discount_percent" id="discount_percent" min="0" max="100" step="0.01" placeholder="Masukkan %">
                                </div>
                            </div>

                            <hr>
                            <div class="mb-3">
                                <h6>Produk Dipilih</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm" id="selectedProducts">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Subtotal</th>
                                                <th id="subtotalAmount">Rp 0</th>
                                                <th></th>
                                            </tr>
                                            <tr>
                                                <th colspan="2">Diskon</th>
                                                <th id="discountAmount">Rp 0</th>
                                                <th></th>
                                            </tr>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th id="totalAmount">Rp 0</th>
                                                <th></th>
                                            </tr>
                                            <tr id="changeRow" style="display: none;">
                                                <th colspan="2">Kembalian</th>
                                                <th id="changeAmount">Rp 0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Checkout</button>
                            </div>
                        </div>
                    </div>
                <input type="hidden" name="products" id="productsInput">
            </form>

            </div>
        </div>
    </div>
</div>

{{-- Modal QR Scanner untuk Transaksi --}}
<div class="modal fade" id="transactionQrScannerModal" tabindex="-1" aria-labelledby="transactionQrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="transactionQrScannerModalLabel">
                    <i class="bi bi-qr-code-scan me-2"></i>Scan QR Code Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="closeTransactionScannerBtn"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <div id="transaction-qr-reader" style="width: 100%; min-height: 240px; border: 2px dashed #dee2e6; border-radius: 12px; background: #f8f9fa; position: relative; overflow: hidden;">
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            <div class="text-center">
                                <i class="bi bi-camera-video" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">Memuat kamera...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="transaction-qr-reader-results"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="stopTransactionScannerBtn">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Script transaksi & scanner ditempatkan di bagian bawah file --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/idb-keyval@6/dist/umd.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Pastikan jQuery ter-load sebelum melanjutkan
(function() {
    let jqueryLoaded = false;
    let attempts = 0;
    const maxAttempts = 10;

    function checkJQuery() {
        if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
            jqueryLoaded = true;
            initApp();
        } else {
            attempts++;
            if (attempts < maxAttempts) {
                setTimeout(checkJQuery, 100);
            } else {
                console.error('jQuery gagal ter-load setelah beberapa kali percobaan');
                alert('Gagal memuat aplikasi. Pastikan koneksi internet aktif atau refresh halaman.');
            }
        }
    }

    function initApp() {
        const { get, set, createStore } = idbKeyval;
        const transactionStore = createStore('toko-trijaya-db', 'pending-transactions');

        $(document).ready(function() {
    // Array untuk menyimpan item di keranjang
    let selectedProducts = [];

    // Variabel untuk kalkulasi
    let subtotal = 0;
    let discountAmount = 0;
    let discountPercent = 0;
    let total = 0;
    let cashAmount = 0;
    let changeAmount = 0;

    // Flag untuk mencegah restore ganda
    let isRestored = false;

    // Fungsi untuk menyimpan state keranjang ke sessionStorage
    function saveCartState() {
        const cartState = {
            selectedProducts: selectedProducts,
            discountAmount: discountAmount,
            discountPercent: discountPercent,
            cashAmount: cashAmount,
            paymentMethod: $('#paymentMethod').val(),
            customerId: $('#customer_id_hidden').val(),
            customerName: $('#select-customer').val(),
            customerPhone: $('#select-phone').val(),
            isNewCustomer: $('#select-customer').attr('data-new-customer') || false
        };
        sessionStorage.setItem('transactionCartState', JSON.stringify(cartState));
        console.log('Cart state saved:', cartState);
    }

    // Fungsi untuk memulihkan state keranjang dari sessionStorage
    function restoreCartState() {
        // Cek apakah transaksi baru saja berhasil (jangan restore jika baru checkout)
        const transactionCompleted = sessionStorage.getItem('transaction_completed');
        if (transactionCompleted === 'true') {
            // Hapus flag dan state, jangan restore
            sessionStorage.removeItem('transaction_completed');
            sessionStorage.removeItem('transactionCartState');
            console.log('Transaksi baru saja berhasil, skip restore state');
            return;
        }

        // Cegah restore ganda
        if (isRestored) {
            console.log('Cart already restored, skipping...');
            return;
        }

        const savedState = sessionStorage.getItem('transactionCartState');
        if (savedState) {
            try {
                const cartState = JSON.parse(savedState);
                console.log('Restoring cart state:', cartState);

                // Restore selected products
                if (cartState.selectedProducts && cartState.selectedProducts.length > 0) {
                    selectedProducts = cartState.selectedProducts;
                    console.log('Restored products:', selectedProducts);
                    isRestored = true; // Set flag setelah restore berhasil
                    window.isRestored = true; // Set flag global juga

                    // Restore form values
                    if (cartState.paymentMethod) {
                        $('#paymentMethod').val(cartState.paymentMethod).trigger('change');
                    }
                    if (cartState.discountAmount) {
                        discountAmount = cartState.discountAmount;
                        $('#discount_amount').val(cartState.discountAmount);
                    }
                    if (cartState.discountPercent) {
                        discountPercent = cartState.discountPercent;
                        $('#discount_percent').val(cartState.discountPercent);
                    }
                    if (cartState.cashAmount) {
                        cashAmount = cartState.cashAmount;
                        $('#cashAmount').val(cartState.cashAmount);
                    }

                    // Restore customer
                    if (cartState.customerId) {
                        $('#customer_id_hidden').val(cartState.customerId);
                        if (window.customerSelect) {
                            window.customerSelect.setValue(cartState.customerId);
                        }
                    } else if (cartState.isNewCustomer && cartState.customerName) {
                        $('#select-customer').attr('data-new-customer', cartState.customerName);
                        if (window.customerSelect) {
                            window.customerSelect.setValue(cartState.customerName);
                        }
                    }

                    if (cartState.customerPhone) {
                        $('#select-phone').val(cartState.customerPhone);
                    }

                    // Update tampilan keranjang - pastikan dipanggil setelah DOM siap
                    setTimeout(function() {
                        updateSelectedProductsTable();
                        // Update stok di tabel kiri berdasarkan keranjang
                        updateStockDisplayFromCart();
                    }, 100);
                }
            } catch (e) {
                console.error('Error restoring cart state:', e);
            }
        } else {
            console.log('No saved cart state found');
        }
    }

    // Fungsi untuk update tampilan stok berdasarkan item di keranjang
    function updateStockDisplayFromCart() {
        selectedProducts.forEach(product => {
            const productRow = $(`#productList .add-product[data-unit-id="${product.id}"]`).closest('tr');
            if (productRow.length) {
                const stockCell = productRow.find('.stock-display');
                const originalStock = product.stock_awal || 0;
                const quantityInCart = product.quantity || 0;
                const remainingStock = originalStock - quantityInCart;

                if (remainingStock <= 0) {
                    stockCell.html('<span class="text-danger fw-bold">Habis</span>');
                    productRow.find('.add-product').prop('disabled', true);
                } else {
                    stockCell.text(remainingStock);
                    stockCell.removeClass('text-danger fw-bold');
                    productRow.find('.add-product').prop('disabled', false);
                }
            }
        });
    }

    // Simpan state sebelum form search di-submit
    $('#searchForm').on('submit', function(e) {
        // Pastikan state tersimpan sebelum submit
        saveCartState();
        // Beri waktu sedikit untuk memastikan sessionStorage tersimpan
        const form = this;
        setTimeout(function() {
            // Form akan submit secara normal setelah state tersimpan
        }, 50);
    });

    // Simpan state juga saat filter di-klik
    $('.dropdown-item').on('click', function() {
        setTimeout(saveCartState, 100); // Delay sedikit untuk memastikan URL sudah berubah
    });

    // Backup: Simpan state saat user akan meninggalkan halaman
    $(window).on('beforeunload', function() {
        saveCartState();
    });

    // Buat restoreCartState bisa diakses dari luar
    window.restoreCartState = restoreCartState;

    // Cek apakah transaksi baru saja berhasil (dari redirect)
    const transactionCompleted = sessionStorage.getItem('transaction_completed');
    if (transactionCompleted === 'true') {
        // Hapus flag dan state, jangan restore
        sessionStorage.removeItem('transaction_completed');
        sessionStorage.removeItem('transactionCartState');
        console.log('Transaksi baru saja berhasil, clear state');
    }

    // Coba restore segera setelah document ready (jika TomSelect belum siap, akan dicoba lagi nanti)
    setTimeout(function() {
        if ($('#productList').length > 0 && selectedProducts.length === 0 && !isRestored) {
            restoreCartState();
        }
    }, 500);

    // Fungsi format Rupiah
    function formatRupiah(angka) {
        if (isNaN(angka)) {
            return 'Rp 0';
        }
        return 'Rp ' + Math.round(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Logika Payment Method & Diskon (Sama, tidak perlu diubah)
    $('#paymentMethod').change(function() {
        if ($(this).val() === 'cash') {
            $('#cashAmountField').show();
            $('#changeRow').show();
            calculateChange();
        } else {
            $('#cashAmountField').hide();
            $('#changeRow').hide();
        }
        saveCartState(); // Simpan state setelah update
    });

    $(document).on('click', '.quick-cash-btn', function() {
        const amount = $(this).data('amount');

        // 1. Set nilai input
        $('#cashAmount').val(amount);

        // 2. Update variabel global
        cashAmount = parseFloat(amount);

        // 3. Hitung kembalian
        calculateChange();
    });

    $('#cashAmount').on('input', function() {
        // Ambil nilai tunai
        cashAmount = parseFloat($(this).val()) || 0;

        // --- VALIDASI BARU: Batasi Kembalian Maksimum ---

        // Tentukan batas kembalian (sesuai contoh Anda, 100rb adalah batas yg logis)
        const maxKembalian = 100000;

        // Cek hanya jika ada total tagihan dan uang tunai dimasukkan
        if (total > 0 && cashAmount > 0) {
            let kembalian = cashAmount - total;

            // Jika kembalian melebihi batas
            if (kembalian > maxKembalian) {

                // Hitung uang tunai maksimum yang seharusnya
                const maxAllowedCash = total + maxKembalian;


                // Setel nilai input ke nilai maksimum yang diizinkan
                $(this).val(Math.round(maxAllowedCash));

                // Perbarui variabel global
                cashAmount = maxAllowedCash;
            }
        }
        calculateChange();
        saveCartState(); // Simpan state setelah update
    });
    $('#discount_amount').on('input', function() {
        discountAmount = parseFloat($(this).val()) || 0;

        // --- VALIDASI BARU DITAMBAHKAN ---
        // Cek apakah diskon melebihi subtotal
        if (discountAmount > subtotal) {
            discountAmount = subtotal; // Batasi nilainya
            $(this).val(Math.round(subtotal)); // Set nilai input ke nilai maksimum


        }
        // --- AKHIR VALIDASI BARU ---

        // Hitung ulang persentase berdasarkan diskon Rp yang sudah divalidasi
        discountPercent = subtotal > 0 ? (discountAmount / subtotal) * 100 : 0;

        // Update input persentase (jangan nonaktifkan input persen)
        $('#discount_percent').val(discountPercent > 0 ? discountPercent.toFixed(2) : '');

        // Hitung ulang total
        calculateTotal();
        calculateChange();
        saveCartState(); // Simpan state setelah update
    });

    $('#discount_percent').on('input', function() {
        discountPercent = parseFloat($(this).val()) || 0;
        if (discountPercent > 100) discountPercent = 100;
        discountAmount = (discountPercent / 100) * subtotal;
        $('#discount_amount').val(Math.round(discountAmount));
        calculateTotal();
        calculateChange();
        saveCartState(); // Simpan state setelah update
    });

    // Fungsi Kalkulasi Total
    function calculateTotal() {
        total = subtotal - discountAmount;
        if (total < 0) total = 0;
        $('#subtotalAmount').text(formatRupiah(subtotal));
        $('#discountAmount').text(formatRupiah(discountAmount));
        $('#totalAmount').text(formatRupiah(total));

        // Panggil updateQuickCashButtons jika ada, jika tidak ada tidak apa-apa
        if (typeof updateQuickCashButtons === 'function') {
            updateQuickCashButtons(total);
        }
    }

    // Fungsi untuk update tombol quick cash (jika ada di UI)
    function updateQuickCashButtons(totalAmount) {
        // Fungsi ini untuk update tombol quick cash berdasarkan total
        // Jika tidak ada tombol quick cash di UI, fungsi ini bisa dikosongkan
        // atau dihapus jika tidak diperlukan

        // Contoh implementasi jika ada tombol quick cash:
        // $('.quick-cash-btn').each(function() {
        //     const btnAmount = $(this).data('amount');
        //     if (btnAmount < totalAmount) {
        //         $(this).prop('disabled', true);
        //     } else {
        //         $(this).prop('disabled', false);
        //     }
        // });

        // Untuk sekarang, fungsi ini dikosongkan karena tidak ada tombol quick cash di UI
        // Tapi tetap didefinisikan untuk menghindari error
    }

    // Fungsi Kalkulasi Kembalian
    function calculateChange() {
        if ($('#paymentMethod').val() === 'cash') {
            changeAmount = cashAmount - total;
            $('#changeAmount').text(formatRupiah(changeAmount < 0 ? 0 : changeAmount));
        }
    }


    // ======================================================
    // Logika 'Tambah Produk'
    // ======================================================
    $(document).on('click', '#productList .add-product', function(e) {
        e.preventDefault();
        e.stopPropagation();

        // Cek apakah jQuery sudah ter-load
        if (typeof $ === 'undefined') {
            console.error('jQuery tidak ter-load! Pastikan koneksi internet aktif untuk pertama kali.');
            alert('Aplikasi sedang memuat. Silakan tunggu sebentar atau periksa koneksi internet Anda.');
            return false;
        }

        const $thisButton = $(this);
        const tr = $thisButton.closest('tr');

        // 1. Ambil data langsung dari tombol
        const unitId = $thisButton.data('unit-id');
        const productId = $thisButton.data('product-id'); // Ambil ID Induk
        const name = $thisButton.data('name');
        const unitName = $thisButton.data('unit-name'); // <-- PERBAIKAN: Ambil nama unit
        const color = $thisButton.data('color');
        const price = parseFloat($thisButton.data('price'));
        const conversion = parseInt($thisButton.data('conversion'));
        const stockSistem = parseInt($thisButton.data('stock'));

        const quantity = 1;

        const stockCell = tr.find('.stock-display');
        let currentStock = 0;

        if (stockCell.text() !== 'Habis') {
             currentStock = parseInt(stockCell.text()) || 0;
        }

        // 3. Cek Stok (quantity sekarang selalu 1)
        if (quantity > currentStock) {
            alert(`Stok tidak mencukupi! \n\nDiminta: ${quantity} \nSisa Stok: ${currentStock}`);
            return;
        }

        const productSubtotal = price * quantity;
        let newStock = currentStock - quantity;

        // 4. Update tampilan stok di tabel kiri
        if (newStock <= 0) {
            stockCell.html('<span class="text-danger fw-bold">Habis</span>');
            $thisButton.prop('disabled', true);
        } else {
            stockCell.text(newStock);
        }

        // 5. Cek keranjang
        const cartItemId = unitId;
        const existingIndex = selectedProducts.findIndex(item => item.id === cartItemId);

        if (existingIndex >= 0) {
            // Jika sudah ada, tambahkan quantity
            selectedProducts[existingIndex].quantity += quantity;
            selectedProducts[existingIndex].subtotal = selectedProducts[existingIndex].price * selectedProducts[existingIndex].quantity;
        } else {
            // Jika belum ada, tambahkan item baru
            selectedProducts.push({
                id: cartItemId,          // Ini ID Unit
                product_id: productId,   // <-- PERBAIKAN 1: Tambahkan ID Induk
                name: name,
                color: color,
                unit_name: unitName,
                price: price,
                quantity: quantity,
                subtotal: productSubtotal,
                stock_awal: stockSistem,
                conversion: conversion
            });
        }
        updateSelectedProductsTable();
        saveCartState(); // Simpan state setelah update
    });

    // ======================================================
    // Update Tabel Keranjang
    // ======================================================
    function updateSelectedProductsTable() {
        const tableBody = $('#selectedProducts tbody');
        tableBody.empty();
        subtotal = 0;

        selectedProducts.forEach((product, index) => {
            tableBody.append(`
                <tr>
                    <td>
                        ${product.name}
                        ${product.color && product.color !== '-' ? `<br><small class="text-muted">Warna: ${product.color}</small>` : ''}
                    </td>
                    <td style="width: 90px;">
                        <input
                            type="number"
                            class="form-control form-control-sm cart-quantity-input"
                            value="${product.quantity}"
                            min="1"
                            data-index="${index}"
                            data-unit-id="${product.id}"
                        >
                    </td>
                    <td>${formatRupiah(product.subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-from-cart" data-index="${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            subtotal += product.subtotal;
        });

        // Kalkulasi diskon, total, dan kembalian
        if (discountPercent > 0) {
            discountAmount = (discountPercent / 100) * subtotal;
            $('#discount_amount').val(Math.round(discountAmount));
        } else if (discountAmount > 0) {
             discountPercent = subtotal > 0 ? (discountAmount / subtotal) * 100 : 0;
            $('#discount_percent').val(discountPercent > 0 ? discountPercent.toFixed(2) : '');
        }
        calculateTotal();
        calculateChange();

        // Update input tersembunyi
        const productsForServer = selectedProducts.map(p => {
            return {
                id: p.id,                  // Ini ID Unit (mis: 193)
                product_id: p.product_id,    // <-- PERBAIKAN 2: Kirim ID Induk ke server
                quantity: p.quantity,
                price: p.price,
                subtotal: p.subtotal,
                conversion: p.conversion,
                unit_name: p.unit_name
            };
        });
        $('#productsInput').val(JSON.stringify(productsForServer));

        saveCartState(); // Simpan state setelah update
    }

    // ======================================================
    // Handler untuk ubah quantity di keranjang
    // ======================================================
    $(document).on('change', '#selectedProducts .cart-quantity-input', function() {
        const $input = $(this);
        const index = parseInt($input.data('index'));
        const newQuantity = parseInt($input.val());
        const unitId = $input.data('unit-id');

        if (isNaN(newQuantity) || newQuantity < 1) {
            alert('Quantity minimal 1');
            $input.val(selectedProducts[index].quantity);
            return;
        }

        const product = selectedProducts[index];
        const oldQuantity = product.quantity;
        const quantityDiff = newQuantity - oldQuantity;

        const productRow = $(`#productList .add-product[data-unit-id="${unitId}"]`).closest('tr');
        const stockCell = productRow.find('.stock-display');
        let currentStockOnLeft = 0;

        if (stockCell.text() !== 'Habis') {
            currentStockOnLeft = parseInt(stockCell.text()) || 0;
        }

        if (quantityDiff > currentStockOnLeft) {
            alert(`Stok tidak mencukupi. Anda hanya bisa menambah ${currentStockOnLeft} lagi.`);
            const maxQuantity = oldQuantity + currentStockOnLeft;
            $input.val(maxQuantity);

            product.quantity = maxQuantity;
            product.subtotal = product.price * product.quantity;

            stockCell.html('<span class="text-danger fw-bold">Habis</span>');
            productRow.find('.add-product').prop('disabled', true);

            updateSelectedProductsTable(); // Re-render
            return;
        }

        product.quantity = newQuantity;
        product.subtotal = product.price * newQuantity;

        const newStockOnLeft = currentStockOnLeft - quantityDiff;

        if (newStockOnLeft <= 0) {
            stockCell.html('<span class="text-danger fw-bold">Habis</span>');
            productRow.find('.add-product').prop('disabled', true);
        } else {
            stockCell.text(newStockOnLeft);
            stockCell.removeClass('text-danger fw-bold');
            productRow.find('.add-product').prop('disabled', false);
        }

        updateSelectedProductsTable();
        saveCartState(); // Simpan state setelah update
    });


    // ======================================================
    // Hapus dari Keranjang
    // ======================================================
    $(document).on('click', '#selectedProducts .remove-from-cart', function() {
        const index = $(this).data('index');
        if (index >= 0 && index < selectedProducts.length) {

            const removedProduct = selectedProducts[index];
            const stockToReturn = removedProduct.quantity;

            const productRow = $(`#productList .add-product[data-unit-id="${removedProduct.id}"]`).closest('tr');

            if (productRow.length) {
                const stockCell = productRow.find('.stock-display');
                let currentStock = 0;

                if (stockCell.text() !== 'Habis') {
                    currentStock = parseInt(stockCell.text()) || 0;
                }

                const newStock = currentStock + stockToReturn;
                stockCell.text(newStock);
                stockCell.removeClass('text-danger fw-bold');

                if (newStock > 0) {
                    productRow.find('.add-product').prop('disabled', false);
                }
            }

            selectedProducts.splice(index, 1);
            updateSelectedProductsTable();
            saveCartState(); // Simpan state setelah update
        }
    });

    // Validasi Submit
    $('#transactionForm').on('submit', function(e) {
        // SELALU hentikan submit standar
        e.preventDefault();

        // --- Validasi Sisi Klien (sudah ada) ---
        if (selectedProducts.length === 0) {
            alert('Silahkan tambahkan minimal 1 produk terlebih dahulu');
            return false;
        }
        if ($('#paymentMethod').val() === 'cash') {
            if (cashAmount < total) {
                alert('Uang tunai tidak mencukupi!');
                return false;
            }
        }

        // Nonaktifkan tombol
        $(this).find('button[type="submit"]').prop('disabled', true).text('Memproses...');

        // --- BACKGROUND SYNC ---
        // 2. Kumpulkan SEMUA data transaksi menjadi satu objek
        const transactionData = serializeTransactionData();

        // 3. Kirim data
        sendTransaction(transactionData);
    });

    // --- BACKGROUND SYNC ---
    // 4. Fungsi untuk mengubah data menjadi JSON
    function serializeTransactionData() {
        const data = {};

        // Ambil data dari variabel global skrip Anda
        data.payment_method = $('#paymentMethod').val();
        data.discount_amount = discountAmount;
        data.subtotal = subtotal;
        data.total_amount = total;
        data.cash_amount = cashAmount;
        data.change_amount = changeAmount;
        data.status = 'unpaid';
        // Ambil customer_id dari hidden input (TomSelect)
        const customerId = $('#customer_id_hidden').val();
        const newCustomerName = $('#select-customer').attr('data-new-customer');

        // Ambil nomor telepon
        const customerPhone = $('#select-phone').val() || '';

        if (customerId && !isNaN(customerId)) {
            // Customer yang sudah ada
            data.customer_id = parseInt(customerId);
            data.customer_phone = customerPhone; // Kirim phone untuk update jika diubah
        } else if (newCustomerName) {
            // Customer baru - kirim nama dan phone untuk dibuat di backend
            data.customer_name = newCustomerName;
            data.customer_phone = customerPhone;
            data.customer_id = null;
        } else {
            data.customer_id = null;
        }
        // Ambil user_id dari meta tag (PENTING!)
        // Pastikan Anda punya <meta name="user-id" content="{{ auth()->id() }}"> di <head>
        data.user_id = parseInt($('meta[name="user-id"]').attr('content')) || null;

        // Ambil keranjang belanja
        data.cart_items = selectedProducts.map(p => {
            return {
                id: p.id,           // Ini ID Unit
                product_id: p.product_id,
                quantity: p.quantity,
                price: p.price,
                subtotal: p.subtotal,
                conversion: p.conversion,
                unit_name: p.unit_name
            };
        });

        return data;
    }

    // --- BACKGROUND SYNC ---
    // 5. Fungsi untuk mengirim data (atau menyimpannya jika offline)
    async function sendTransaction(data) {
        try {
            // Ambil CSRF token
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // 6. COBA kirim ke server (Online) via AJAX
            // Wrap jQuery AJAX dalam Promise untuk async/await yang lebih baik
            const response = await new Promise((resolve, reject) => {
                $.ajax({
                    url: "{{ route('transactions.store') }}",
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    timeout: 30000, // 30 detik timeout
                    success: function(data, textStatus, xhr) {
                        resolve(data);
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        // jQuery AJAX error handler
                        console.error('AJAX Error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            textStatus: textStatus,
                            errorThrown: errorThrown
                        });

                        // Parse response JSON jika ada
                        let responseJSON = null;
                        try {
                            if (xhr.responseText) {
                                responseJSON = JSON.parse(xhr.responseText);
                            }
                        } catch (e) {
                            console.warn('Gagal parse response JSON:', e);
                        }

                        // Buat error object yang lengkap
                        const ajaxError = new Error(errorThrown || xhr.statusText || 'Network error');
                        ajaxError.status = xhr.status || 0;
                        ajaxError.statusText = xhr.statusText || textStatus || 'error';
                        ajaxError.responseJSON = responseJSON || xhr.responseJSON;
                        ajaxError.xhr = xhr;
                        ajaxError.textStatus = textStatus;
                        ajaxError.originalError = errorThrown;

                        // Reject dengan error object
                        reject(ajaxError);
                    }
                });
            });

            // Hapus semua state keranjang setelah transaksi berhasil
            selectedProducts = []; // Kosongkan array
            subtotal = 0;
            discountAmount = 0;
            discountPercent = 0;
            total = 0;
            cashAmount = 0;
            changeAmount = 0;

            // Kembalikan stok di tampilan sebelum mengosongkan keranjang
            selectedProducts.forEach(product => {
                const productRow = $(`#productList .add-product[data-unit-id="${product.id}"]`).closest('tr');
                if (productRow.length) {
                    const stockCell = productRow.find('.stock-display');
                    const originalStock = product.stock_awal || 0;
                    const quantityInCart = product.quantity || 0;
                    const remainingStock = originalStock; // Kembalikan ke stok awal karena transaksi sudah berhasil

                    if (remainingStock <= 0) {
                        stockCell.html('<span class="text-danger fw-bold">Habis</span>');
                        productRow.find('.add-product').prop('disabled', true);
                    } else {
                        stockCell.text(remainingStock);
                        stockCell.removeClass('text-danger fw-bold');
                        productRow.find('.add-product').prop('disabled', false);
                    }
                }
            });

            // Hapus semua state keranjang setelah transaksi berhasil
            selectedProducts = []; // Kosongkan array
            subtotal = 0;
            discountAmount = 0;
            discountPercent = 0;
            total = 0;
            cashAmount = 0;
            changeAmount = 0;

            // Update tampilan keranjang (kosongkan)
            updateSelectedProductsTable();

            // Tandai bahwa transaksi sudah berhasil (untuk mencegah restore)
            sessionStorage.setItem('transaction_completed', 'true');

            // Hapus state dari sessionStorage
            sessionStorage.removeItem('transactionCartState');

            // Reset form
            $('#transactionForm')[0].reset();
            $('#paymentMethod').val('').trigger('change');
            $('#cashAmountField').hide();
            $('#changeRow').hide();

            // Reset customer
            if (window.customerSelect) {
                window.customerSelect.clear();
            }
            $('#select-phone').val('').prop('readOnly', false);
            $('#customer_id_hidden').val('');

            // Reset flag restore
            isRestored = false;
            window.isRestored = false;

            // Redirect ke index setelah transaksi berhasil
            window.location.href = "{{ route('transactions.index') }}?success=true";

        } catch (error) {
            // 8. GAGAL! Cek apakah benar-benar offline atau error server
            console.error('Error saat mengirim transaksi:', error);
            console.error('Error details:', {
                status: error.status,
                statusText: error.statusText,
                message: error.message,
                responseJSON: error.responseJSON,
                navigatorOnLine: navigator.onLine
            });

            // Cek apakah error karena network/offline
            // jQuery textStatus bisa: 'timeout', 'error', 'abort', 'parsererror'
            // Network error biasanya: status = 0, textStatus = 'error' atau 'timeout'
            const textStatus = error.textStatus || error.statusText || '';
            const isNetworkError = (
                // Status 0 biasanya berarti network error
                (!error.status || error.status === 0) &&
                // textStatus 'error' atau 'timeout' biasanya network error
                (textStatus === 'error' || textStatus === 'timeout')
            ) ||
            // Cek juga dari message
            error.message?.includes('NetworkError') ||
            error.message?.includes('Failed to fetch') ||
            error.message?.includes('timeout') ||
            // Cek navigator.onLine
            (navigator.onLine === false);

            // Cek apakah error karena server (400, 422, 500, dll)
            // Server error punya status code 400-599
            // Jika status ada dan >= 400, berarti server merespons (bukan offline)
            const isServerError = error.status && error.status >= 400 && error.status < 600;

            console.log('Error type check:', {
                isNetworkError: isNetworkError,
                isServerError: isServerError,
                errorStatus: error.status
            });

            if (isNetworkError && !isServerError) {
                // Benar-benar offline atau network error - simpan untuk sync nanti
                console.warn('Benar-benar offline/network error, menyimpan untuk sinkronisasi...');
                saveTransactionForLater(data);
                alert('Anda sedang offline atau koneksi terputus. Transaksi akan disimpan dan disinkronkan otomatis saat online.');
                // Refresh halaman, transaksi akan "tertunda"
                window.location.reload();
            } else {
                // Server error atau validation error - tampilkan error yang spesifik
                let errorMessage = 'Gagal menyimpan transaksi.';

                if (error.responseJSON) {
                    if (error.responseJSON.error) {
                        errorMessage = error.responseJSON.error;
                    } else if (error.responseJSON.message) {
                        errorMessage = error.responseJSON.message;
                    } else if (error.responseJSON.errors) {
                        // Format validation errors
                        const errors = error.responseJSON.errors;
                        const errorList = Object.keys(errors).map(key => {
                            return `${key}: ${Array.isArray(errors[key]) ? errors[key].join(', ') : errors[key]}`;
                        }).join('\n');
                        errorMessage = 'Validasi gagal:\n' + errorList;
                    }
                } else if (error.statusText) {
                    errorMessage = error.statusText;
                } else if (error.message) {
                    errorMessage = error.message;
                }

                console.error('Server/validation error:', errorMessage);

                // Aktifkan kembali tombol submit
                $('#transactionForm').find('button[type="submit"]').prop('disabled', false).text('Checkout');

                // Tampilkan error yang lebih spesifik
                alert('Gagal menyimpan transaksi:\n\n' + errorMessage +
                      '\n\nSilakan periksa data yang Anda masukkan dan coba lagi.');

                // Jangan reload, biarkan user memperbaiki data
                return false;
            }
        }
    }

    // --- BACKGROUND SYNC ---
    // 9. Fungsi untuk menyimpan data ke IndexedDB
    async function saveTransactionForLater(data) {
        let queue = await get('pending-transactions', transactionStore) || [];
        queue.push(data);
        await set('pending-transactions', queue, transactionStore);

        // 10. Daftarkan "Tugas Sync" ke Service Worker
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready.then(sw => {
                // Beri 'tag' yang unik
                sw.sync.register('sync-new-transactions');
                console.log('Tugas sinkronisasi "sync-new-transactions" telah didaftarkan.');
            });
        }
    }

    // Fallback jika Background Sync tidak jalan: paksa sync saat online
    async function manualSyncPendingTransactions() {
        if (!navigator.onLine) return;
        try {
            let queue = await get('pending-transactions', transactionStore) || [];
            if (queue.length === 0) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            while (queue.length > 0) {
                const data = queue[0];
                const res = await fetch('/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
                    },
                    body: JSON.stringify(data)
                }).catch(() => null);

                if (!res || !res.ok) {
                    console.warn('Manual sync gagal, coba lagi nanti');
                    break;
                }

                queue.shift();
                await set('pending-transactions', queue, transactionStore);
            }
        } catch (err) {
            console.error('Manual sync error:', err);
        }
    }

    // Trigger manual sync ketika kembali online atau saat halaman dibuka dalam keadaan online
    window.addEventListener('online', manualSyncPendingTransactions);
    manualSyncPendingTransactions();

}); // Penutup $(document).ready

    } // Penutup function initApp

    // Mulai proses pengecekan jQuery segera setelah script dimuat
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkJQuery);
    } else {
        checkJQuery();
    }

// Inisialisasi TomSelect untuk Customer
// Pastikan TomSelect library sudah ter-load dan DOM sudah ready
function initializeCustomerTomSelect() {
    // Cek apakah TomSelect library sudah ter-load
    if (typeof TomSelect === 'undefined') {
        console.error('TomSelect library belum ter-load!');
        // Coba lagi setelah 100ms
        setTimeout(initializeCustomerTomSelect, 100);
        return;
    }

    // Cek apakah elemen input customer ada
    const customerInput = document.getElementById('select-customer');
    if (!customerInput) {
        console.error('Element #select-customer tidak ditemukan!');
        return;
    }

    // Cek apakah sudah diinisialisasi sebelumnya
    if (customerInput.tomselect) {
        console.log('TomSelect sudah diinisialisasi sebelumnya');
        return;
    }

    try {
        // Konversi data PHP ke JavaScript
        const customers = @json($customers ?? []);

        // Validasi data customers
        if (!Array.isArray(customers)) {
            console.error('Data customers bukan array!');
            return;
        }

        // Ambil elemen input telepon (kita akan sering menggunakannya)
        const phoneInput = document.getElementById('select-phone');

        if (!phoneInput) {
            console.error('Element #select-phone tidak ditemukan!');
            return;
        }

        // Buat array options untuk TomSelect
        const customerOptions = customers.map(customer => ({
            value: customer.id,
            text: customer.name + (customer.phone ? ' - ' + customer.phone : ''),
            name: customer.name,
            phone: customer.phone || '' // Pastikan data 'phone' ada di sini
        }));

        console.log('Customer options:', customerOptions.length, 'items');

        // Inisialisasi TomSelect Customer
        window.customerSelect = new TomSelect("#select-customer", {
            create: function(input, callback) {
                const newOption = {
                    value: input.trim().toUpperCase(),
                    text: input.trim().toUpperCase() + ' (Baru)'
                };
                callback(newOption);
            },
            maxItems: 1,
            openOnFocus: false, // Buka dropdown hanya saat mulai mengetik
            placeholder: 'Ketik untuk cari customer...',
            sortField: { field: "text", direction: "asc" },
            options: customerOptions,
            valueField: 'value',
            labelField: 'text',
            searchField: ['name', 'phone'], // Pencarian berdasarkan nama dan nomor telepon
            render: {
                option_create: function(data, escape) {
                    return '<div class="create">Tambah customer baru: <strong>' + escape(data.input) + '</strong>...</div>';
                },
                option: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                no_results: function(data, escape) {
                    return '<div class="no-results">Tidak ada customer ditemukan.</div>';
                }
            },
            onFocus: function() {
                // Jika sudah ada teks (misal hasil restore), buka dropdown
                const currentSearch = this.control_input?.value?.trim() || '';
                if (currentSearch.length > 0 && !this.isOpen) {
                    this.open();
                }
            },
            onType: function(str) {
                // Buka dropdown ketika user mengetik huruf pertama
                if (str.length > 0 && !this.isOpen) {
                    this.open();
                } else if (str.length === 0 && this.isOpen) {
                    this.close();
                }
            },
            onItemAdd: function(value) {
                if (isNaN(value)) {
                    // --- CUSTOMER BARU ---
                    document.getElementById('customer_id_hidden').value = '';
                    document.getElementById('select-customer').setAttribute('data-new-customer', value);

                    // Kosongkan input telepon dan buat bisa diedit
                    if (phoneInput) {
                        phoneInput.value = '';
                        phoneInput.readOnly = false; // <-- PENTING
                    }

                } else {
                    // --- CUSTOMER LAMA (YANG DIPILIH) ---
                    document.getElementById('customer_id_hidden').value = value;
                    document.getElementById('select-customer').removeAttribute('data-new-customer');

                    // --- LOGIKA BARU UNTUK AUTOFILL TELEPON ---
                    // 1. Cari data customer yang dipilih dari 'customerOptions'
                    const selectedCustomer = customerOptions.find(c => c.value == value);

                    if (selectedCustomer && phoneInput) {
                        // 2. Set nilai input telepon
                        phoneInput.value = selectedCustomer.phone;
                        // 3. Buat input 'readOnly' agar tidak salah ketik
                        phoneInput.readOnly = true; // <-- PENTING
                    }
                    // --- AKHIR LOGIKA BARU ---
                }
                // Simpan state setelah customer berubah
                if (typeof saveCartState === 'function') {
                    setTimeout(saveCartState, 100);
                }
            },
            onItemRemove: function() {
                // Clear hidden input
                document.getElementById('customer_id_hidden').value = '';
                document.getElementById('select-customer').removeAttribute('data-new-customer');

                // --- LOGIKA BARU ---
                // Kosongkan input telepon dan buat bisa diedit lagi
                if (phoneInput) {
                    phoneInput.value = '';
                    phoneInput.readOnly = false; // <-- PENTING
                }

                // Simpan state setelah customer dihapus
                if (typeof saveCartState === 'function') {
                    setTimeout(saveCartState, 100);
                }
            }
        });

        // Tambahkan event listener untuk membuka dropdown saat mengetik
        // TomSelect membuat wrapper, jadi kita akses melalui wrapper-nya
        if (window.customerSelect) {
            const tsInput = window.customerSelect.control_input;

            if (tsInput) {
                // Buka dropdown hanya ketika user mulai mengetik (huruf pertama)
                tsInput.addEventListener('input', function() {
                    const inputValue = this.value.trim();
                    if (inputValue.length > 0) {
                        window.customerSelect.open();
                    } else {
                        window.customerSelect.close();
                    }
                });
            }
        }

        // Set initial value jika ada old input
        @if(old('customer_id'))
            const oldCustomerId = {{ old('customer_id') }};
            const oldCustomer = customerOptions.find(c => c.value == oldCustomerId);
            if (oldCustomer && window.customerSelect) {
                window.customerSelect.setValue(oldCustomerId);
                // Juga set telepon & readonly saat halaman di-load jika ada old()
                if (phoneInput) {
                    phoneInput.value = oldCustomer.phone;
                    phoneInput.readOnly = true;
                }
            }
        @endif

        console.log('TomSelect untuk customer berhasil diinisialisasi');
    } catch (error) {
        console.error('Error saat inisialisasi TomSelect customer:', error);
    }
}

// Inisialisasi saat DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Tunggu sedikit untuk memastikan semua script sudah ter-load
    setTimeout(initializeCustomerTomSelect, 100);
});

// Restore cart state setelah TomSelect siap dan semua elemen DOM ready
// Gunakan multiple attempts untuk memastikan restore berhasil
function attemptRestore() {
    if (typeof window.restoreCartState === 'function' && !window.isRestored) {
        // Pastikan tabel produk sudah ada di DOM dan TomSelect sudah siap
        if ($('#productList').length > 0 && window.customerSelect) {
            window.restoreCartState();
        } else {
            // Coba lagi setelah 100ms jika tabel belum ready (maks 10 kali)
            if (typeof attemptRestore.attempts === 'undefined') {
                attemptRestore.attempts = 0;
            }
            attemptRestore.attempts++;
            if (attemptRestore.attempts < 10) {
                setTimeout(attemptRestore, 100);
            }
        }
    }
}

// Coba restore setelah delay yang cukup
setTimeout(attemptRestore, 500);

// Expose isRestored flag
window.isRestored = false;

})();

</script>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let transactionQrScanner = null;
    let isTransactionScanning = false;

    const transactionScannerModal = document.getElementById('transactionQrScannerModal');
    const closeTransactionScannerBtn = document.getElementById('closeTransactionScannerBtn');
    const stopTransactionScannerBtn = document.getElementById('stopTransactionScannerBtn');

    if (transactionScannerModal) {
        transactionScannerModal.addEventListener('shown.bs.modal', function () {
            if (!isTransactionScanning) {
                startTransactionQRScanner();
            }
        });

        transactionScannerModal.addEventListener('hidden.bs.modal', function () {
            stopTransactionQRScanner();
        });
    }

    if (closeTransactionScannerBtn) {
        closeTransactionScannerBtn.addEventListener('click', stopTransactionQRScanner);
    }

    if (stopTransactionScannerBtn) {
        stopTransactionScannerBtn.addEventListener('click', stopTransactionQRScanner);
    }

    function startTransactionQRScanner() {
        const qrReaderElement = document.getElementById('transaction-qr-reader');
        const qrResultElement = document.getElementById('transaction-qr-reader-results');

        if (!qrReaderElement || !qrResultElement) {
            return;
        }

        qrResultElement.innerHTML = `
            <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-camera-video-fill me-2" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>Memuat kamera...</strong> Arahkan kamera ke QR code produk untuk memindai.
                </div>
            </div>
        `;

        transactionQrScanner = new Html5Qrcode("transaction-qr-reader");

        transactionQrScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: null, aspectRatio: 1.0 },
            (decodedText) => {
                handleTransactionQRScanned(decodedText);
            },
            () => {}
        ).then(() => {
            isTransactionScanning = true;
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
            isTransactionScanning = false;
        });
    }

    function stopTransactionQRScanner() {
        if (transactionQrScanner && isTransactionScanning) {
            transactionQrScanner.stop().then(() => {
                isTransactionScanning = false;
            }).catch((err) => {
                console.error("Error stopping scanner", err);
                isTransactionScanning = false;
            });
        }
    }

    async function handleTransactionQRScanned(decodedText) {
        const qrResultElement = document.getElementById('transaction-qr-reader-results');

        stopTransactionQRScanner();

        if (!qrResultElement) {
            return;
        }

        let productUnitId = null;
        const productMatch = decodedText.match(/\/product\/(\d+)/i);
        const inventoryMatch = decodedText.match(/\/inventories\/(\d+)/i);

        if (productMatch) {
            productUnitId = productMatch[1];
        } else if (inventoryMatch) {
            productUnitId = inventoryMatch[1];
        } else if (/^\d+$/.test(decodedText.trim())) {
            productUnitId = decodedText.trim();
        }

        if (!productUnitId) {
            qrResultElement.innerHTML = `
                <div class="alert alert-danger mb-3" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.25rem;"></i>
                        <strong>Format QR code tidak dikenali.</strong>
                    </div>
                    <p class="mb-0">Pastikan QR code berasal dari sistem inventaris.</p>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary btn-sm" onclick="startTransactionQRScanner()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi
                    </button>
                </div>
            `;
            return;
        }

        qrResultElement.innerHTML = `
            <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-hourglass-split me-2" style="font-size: 1.25rem;"></i>
                <div><strong>Mencari produk...</strong></div>
            </div>
        `;

        try {
            const apiUrl = `{{ url('/api/product-unit') }}/${productUnitId}`;
            const response = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });

            if (!response.ok) {
                throw new Error('Produk tidak ditemukan');
            }

            const data = await response.json();
            const targetButton = document.querySelector(`.add-product[data-unit-id="${productUnitId}"]`);

            if (targetButton) {
                if (targetButton.hasAttribute('disabled')) {
                    qrResultElement.innerHTML = `
                        <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.25rem;"></i>
                            <div>
                                <strong>Stok habis.</strong> Produk ditemukan tetapi stok pada variasi ini kosong.
                            </div>
                        </div>
                    `;
                    return;
                }

                qrResultElement.innerHTML = `
                    <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2" style="font-size: 1.25rem;"></i>
                        <div>
                            <strong>Produk ditemukan!</strong> Menambahkan ke keranjang...
                        </div>
                    </div>
                `;

                setTimeout(() => {
                    targetButton.click();
                    setTimeout(() => {
                        if (transactionScannerModal) {
                            const modal = bootstrap.Modal.getInstance(transactionScannerModal);
                            if (modal) {
                                modal.hide();
                            }
                        }
                    }, 400);
                }, 400);
            } else {
                qrResultElement.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center mb-2" role="alert">
                        <i class="bi bi-search me-2" style="font-size: 1.25rem;"></i>
                        <div>
                            <strong>Produk ditemukan, tetapi tidak ada di daftar saat ini.</strong>
                        </div>
                    </div>
                    <p class="text-muted mb-2">Kami akan otomatis mencari produk ini di daftar.</p>
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;

                const searchInput = document.querySelector('#searchForm input[name="search"]');

                setTimeout(() => {
                    if (transactionScannerModal) {
                        const modal = bootstrap.Modal.getInstance(transactionScannerModal);
                        if (modal) {
                            modal.hide();
                        }
                    }

                    if (searchInput) {
                        searchInput.value = data.product_name;
                        setTimeout(() => {
                            document.getElementById('searchForm').submit();
                        }, 200);
                    }
                }, 600);
            }
        } catch (error) {
            console.error('Error fetching product:', error);
            qrResultElement.innerHTML = `
                <div class="alert alert-danger mb-3" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.25rem;"></i>
                        <strong>Gagal mengambil data produk.</strong>
                    </div>
                    <p class="mb-0">Pastikan QR code berasal dari sistem atau coba lagi.</p>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary btn-sm" onclick="startTransactionQRScanner()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi
                    </button>
                </div>
            `;
        }
    }

    window.startTransactionQRScanner = startTransactionQRScanner;
</script>
@endpush
