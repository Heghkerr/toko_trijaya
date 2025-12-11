@extends('layouts.app')

@section('title', 'Stok Opname')

@push('styles')
<style>
.opname-highlight {
    animation: opnamePulse 0.9s ease-in-out 0s 5 alternate;
    box-shadow: 0 0 1.25rem rgba(255, 193, 7, 0.9);
    outline: 3px solid #ffc107;
    background-color: #fff3cd !important;
    position: relative;
}
@keyframes opnamePulse {
    from { background-color: #ffe69c; }
    to   { background-color: #ffd24c; }
}
</style>
<style>
.opname-highlight::after {
    content: 'Ditemukan';
    position: absolute;
    top: 6px;
    right: 10px;
    padding: 2px 6px;
    border-radius: 6px;
    background: #ffc107;
    color: #000;
    font-weight: 700;
    font-size: 0.75rem;
    box-shadow: 0 0 0.5rem rgba(0,0,0,0.15);
}
</style>
@endpush

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-clipboard-check me-2"></i> Stok Opname Barang
        </h6>
        <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Inventaris
        </a>
    </div>

    <div class="card-body">

        {{-- GANTI BAGIAN FILTER LAMA ANDA DENGAN INI --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Tombol Scan QR --}}
            <button type="button" class="btn btn-outline-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#opnameQrScannerModal">
                <i class="bi bi-qr-code-scan me-1"></i> Scan QR
            </button>

            {{-- Search Bar (SOLUSI 1: Ganti flex-grow-1 jadi w-50 atau w-75) --}}
            <form method="GET" action="{{ route('inventories.opname') }}" class="flex-grow-1" id="opnameSearchForm">
                <div class="input-group">
                    <input type="search" name="search" class="form-control form-control-sm" id="opnameSearchInput"
                        placeholder="Cari berdasarkan Nama Produk..."
                        value="{{ request('search') }}">

                    {{-- Penting: Simpan filter aktif saat melakukan search --}}
                    @if(request('type_id'))
                        <input type="hidden" name="type_id" value="{{ request('type_id') }}">
                    @endif
                    @if(request('color_id'))
                        <input type="hidden" name="color_id" value="{{ request('color_id') }}">
                    @endif
                    @if(request('unit_name'))
                        <input type="hidden" name="unit_name" value="{{ request('unit_name') }}">
                    @endif

                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            {{-- Tombol Filter Offcanvas (SOLUSI 2) --}}
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                <i class="bi bi-funnel me-1"></i>
                Filter
                {{-- Beri notifikasi jika ada filter yang aktif --}}
                @if(request('type_id') || request('color_id'))
                    <span class="badge rounded-pill bg-primary ms-1" style="font-size: 0.6em; padding: 0.3em 0.5em;">
                        !
                    </span>
                @endif
            </button>
        </div>

        {{-- Filter Aktif (Tampilan badge tetap sama) --}}
        @if(request('type_id') || request('color_id'))
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="text-muted fw-semibold me-2" style="font-size: 0.9rem;">Filter Aktif:</span>
            @if(isset($activeType))
                <span class="badge bg-primary d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Jenis: {{ $activeType->name }}
                    {{-- [DIUBAH] Link reset ini sekarang juga harus menyimpan query 'search' jika ada --}}
                    <a href="{{ request()->fullUrlWithQuery(['type_id' => null]) }}"
                    class="text-white ms-2" style="text-decoration:none; font-weight:700;">&times;</a>
                </span>
            @endif
            @if(isset($activeColor))
                <span class="badge bg-success d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Warna: {{ $activeColor->name }}
                    {{-- [DIUBAH] Link reset ini sekarang juga harus menyimpan query 'search' jika ada --}}
                    <a href="{{ request()->fullUrlWithQuery(['color_id' => null]) }}"
                    class="text-white ms-2" style="text-decoration:none; font-weight:700;">&times;</a>
                </span>
            @endif
            @if(request('unit_name'))
                <span class="badge bg-warning text-dark d-flex align-items-center" style="font-size: 0.9rem; padding: 0.4em 0.6em;">
                    Satuan: {{ request('unit_name') }}
                    <a href="{{ request()->fullUrlWithQuery(['unit_name' => null]) }}"
                    class="text-dark ms-2" style="text-decoration:none; font-weight:700;">&times;</a>
                </span>
            @endif
        </div>
        @endif
        {{-- BATAS AKHIR BLOK PENGGANTI --}}
        {{-- [BARU] Kode untuk Offcanvas Filter --}}
        <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filter Inventaris</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">

                <form method="GET" action="{{ route('inventories.opname') }}">

                    {{-- Penting: Simpan 'search' query saat melakukan filter --}}
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    {{-- Filter Jenis (diubah jadi <select>) --}}
                    <div class="mb-3">
                        <label for="filter_type_id" class="form-label">Jenis Produk</label>
                        <select class="form-select" name="type_id" id="filter_type_id">
                            <option value="">Semua Jenis</option>
                            @foreach($productTypes as $type)
                                <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Warna (diubah jadi <select>) --}}
                    <div class="mb-3">
                        <label for="filter_color_id" class="form-label">Warna Produk</label>
                        <select class="form-select" name="color_id" id="filter_color_id">
                            <option value="">Semua Warna</option>
                            @foreach($productColors as $color)
                                <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>
                                    {{ $color->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filter_unit_name" class="form-label">Satuan</label>
                        <select class="form-select" name="unit_name" id="filter_unit_name">
                            <option value="">Semua Satuan</option>

                            {{-- Loop dari variabel $unitNames yang baru Anda buat --}}
                            @foreach($unitNames as $unitName)
                                <option value="{{ $unitName }}" {{ request('unit_name') == $unitName ? 'selected' : '' }}>
                                    {{ $unitName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    {{-- Tombol Aksi --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                        </button>

                        {{-- Tombol Reset (Hanya mereset filter, bukan pencarian) --}}
                        <a href="{{ route('inventories.index', ['search' => request('search')]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Validasi Gagal!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inventories.opname.store') }}" method="POST" id="opnameForm">
            @csrf

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Nama Variasi Produk</th>
                            <th class="text-center" style="width: 10%;">Stok Sistem</th>
                            <th class="text-center" style="width: 15%;">Stok Fisik</th>
                            <th class="text-center" style="width: 10%;">Selisih</th>
                            <th style="width: 35%;">Keterangan (Wajib diisi jika ada selisih)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productUnits as $unit)
                        <tr class="opname-row">
                            {{-- 1. Nama Variasi --}}
                            <td>
                                {{ $unit->product->name }}
                                ({{ $unit->product->color->name ?? '-' }})
                                <br>
                                <strong class="text-primary">{{ $unit->name }}</strong>
                            </td>

                            {{-- 2. Stok Sistem --}}
                            <td class="text-center stok-sistem" data-stock-sistem="{{ $unit->stock }}">
                                {{ $unit->stock }}
                            </td>

                            {{-- 3. Input Stok Fisik --}}
                            <td>
                                <input type="number"
                                       name="opname[{{ $unit->id }}][stok_fisik]"
                                       class="form-control form-control-sm text-center stok-fisik-input"
                                       value="{{ $unit->stock }}"
                                       min="0">
                            </td>

                            {{-- 4. Selisih --}}
                            <td class="text-center stok-selisih fw-bold">
                                0
                            </td>

                            {{-- 5. Keterangan (Alasan) per Baris --}}
                            <td>
                                <input type="text"
                                       name="opname[{{ $unit->id }}][alasan]"
                                       class="form-control form-control-sm alasan-input"
                                       placeholder="cth: Rusak, Hilang, Salah input...">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada variasi produk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginasi DIHAPUS --}}

            <hr>

            {{-- [DIUBAH] Textarea alasan global DIHAPUS, diganti tombol simpan --}}
            <div class="text-end">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-save"></i> Simpan Hasil Opname
                </button>
            </div>

        </form>
    </div>
</div>

{{-- Modal QR Scanner untuk Opname --}}
<div class="modal fade" id="opnameQrScannerModal" tabindex="-1" aria-labelledby="opnameQrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="opnameQrScannerModalLabel">
                    <i class="bi bi-qr-code-scan me-2"></i>Scan QR Code Produk
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeOpnameScannerBtn"></button>
            </div>
            <div class="modal-body">
                <div id="opname-qr-reader" style="width: 100%; min-height: 320px; border-radius: 12px; background: #f8f9fa; position: relative; overflow: hidden;">
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                        <div class="text-center">
                            <i class="bi bi-camera-video" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">Memuat kamera...</p>
                        </div>
                    </div>
                </div>
                <div id="opname-qr-reader-results" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="stopOpnameScannerBtn">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const OPNAME_TARGET_KEY = 'opname_target_unit_id';

    function highlightRow(row) {
        if (!row) return;
        row.classList.add('opname-highlight');
        // Hapus highlight setelah cukup lama agar mudah terlihat
        setTimeout(() => row.classList.remove('opname-highlight'), 4500);
    }

    function calculateRowDifference(row) {
        const stokSistemEl = row.querySelector('.stok-sistem');
        const stokFisikEl = row.querySelector('.stok-fisik-input');
        const stokSelisihEl = row.querySelector('.stok-selisih');

        if (!stokSistemEl || !stokFisikEl || !stokSelisihEl) return;

        const sistem = parseInt(stokSistemEl.dataset.stockSistem) || 0;
        const fisik = parseInt(stokFisikEl.value) || 0;
        const selisih = fisik - sistem;

        stokSelisihEl.textContent = selisih > 0 ? `+${selisih}` : selisih;

        stokSelisihEl.classList.remove('text-success', 'text-danger', 'text-muted');
        if (selisih > 0) {
            stokSelisihEl.classList.add('text-success');
        } else if (selisih < 0) {
            stokSelisihEl.classList.add('text-danger');
        } else {
            stokSelisihEl.classList.add('text-muted');
        }
    }

    const allRows = document.querySelectorAll('.opname-row');

    allRows.forEach(row => {
        calculateRowDifference(row);

        const fisikInput = row.querySelector('.stok-fisik-input');
        if (fisikInput) {
            fisikInput.addEventListener('input', function() {
                calculateRowDifference(row);
            });
        }
    });

    // QR Scanner untuk Opname
    let opnameQrScanner = null;
    let isOpnameScanning = false;

    // Initialize scanner when modal is shown
    const opnameQrModal = document.getElementById('opnameQrScannerModal');
    if (opnameQrModal) {
        opnameQrModal.addEventListener('shown.bs.modal', function () {
            if (!isOpnameScanning) {
                startOpnameQRScanner();
            }
        });

        opnameQrModal.addEventListener('hidden.bs.modal', function () {
            stopOpnameQRScanner();
        });
    }

    document.getElementById('closeOpnameScannerBtn')?.addEventListener('click', function () {
        stopOpnameQRScanner();
    });

    document.getElementById('stopOpnameScannerBtn')?.addEventListener('click', function () {
        stopOpnameQRScanner();
    });

    function startOpnameQRScanner() {
        const qrReaderElement = document.getElementById('opname-qr-reader');
        const qrResultElement = document.getElementById('opname-qr-reader-results');

        if (!qrReaderElement || !qrResultElement) return;

        qrResultElement.innerHTML = `
            <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-camera-video-fill me-2" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>Memuat kamera...</strong> Arahkan kamera ke QR code produk untuk memindai.
                </div>
            </div>
        `;

        opnameQrScanner = new Html5Qrcode("opname-qr-reader");

        opnameQrScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: null, aspectRatio: 1.0 },
            (decodedText) => {
                handleOpnameQRScanned(decodedText);
            },
            () => {}
        ).then(() => {
            isOpnameScanning = true;
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
            isOpnameScanning = false;
        });
    }

    function stopOpnameQRScanner() {
        if (opnameQrScanner && isOpnameScanning) {
            opnameQrScanner.stop().then(() => {
                console.log("Opname QR Scanner stopped");
                isOpnameScanning = false;
            }).catch((err) => {
                console.error("Error stopping scanner", err);
                isOpnameScanning = false;
            });
        }
    }

    async function handleOpnameQRScanned(decodedText) {
        const qrResultElement = document.getElementById('opname-qr-reader-results');

        stopOpnameQRScanner();

        // Parse QR code
        let productUnitId = null;

        const productMatch = decodedText.match(/\/product\/(\d+)/);
        const inventoryMatch = decodedText.match(/\/inventories\/(\d+)/);

        if (productMatch) {
            productUnitId = productMatch[1];
        } else if (inventoryMatch) {
            productUnitId = inventoryMatch[1];
        } else if (/^\d+$/.test(decodedText.trim())) {
            productUnitId = decodedText.trim();
        } else {
            qrResultElement.innerHTML = `
                <p class="text-danger">Format QR code tidak dikenali.</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="startOpnameQRScanner()">Coba Lagi</button>
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
            const targetId = data.id || productUnitId;

            // Coba cari baris berdasarkan ProductUnit ID
            const inputField = document.querySelector(`input[name="opname[${targetId}][stok_fisik]"]`);

            if (inputField) {
                const row = inputField.closest('tr');

                qrResultElement.innerHTML = `
                    <div class="alert alert-success d-flex align-items-center mb-2" role="alert">
                        <i class="bi bi-check-circle-fill me-2" style="font-size: 1.25rem;"></i>
                        <div><strong>Produk ditemukan!</strong> Menyorot baris terkait...</div>
                    </div>
                    <p class="text-muted mb-0">Scroll ke produk dan fokus ke input stok fisik.</p>
                `;

                setTimeout(() => {
                    // Simpan target untuk fallback (mis. reload)
                    sessionStorage.setItem(OPNAME_TARGET_KEY, targetId);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('opnameQrScannerModal'));
                    if (modal) {
                        modal.hide();
                    }

                    setTimeout(() => {
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        highlightRow(row);
                        inputField.focus();
                        inputField.select();
                    }, 300);
                }, 400);
            } else {
                // Jika baris belum ada (mungkin karena filter / pagination), pakai pencarian otomatis
                qrResultElement.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center mb-2" role="alert">
                        <i class="bi bi-search me-2" style="font-size: 1.25rem;"></i>
                        <div><strong>Produk ditemukan, memuat daftar...</strong></div>
                    </div>
                    <p class="text-muted mb-0">Daftar akan difilter berdasarkan hasil scan.</p>
                `;

                const searchInput = document.getElementById('opnameSearchInput');
                const searchForm = document.getElementById('opnameSearchForm');

                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('opnameQrScannerModal'));
                    if (modal) modal.hide();

                    if (searchInput && searchForm) {
                        const keyword = data.product_name && data.unit_name
                            ? `${data.product_name} (${data.unit_name})`
                            : (data.name || data.product_name || decodedText || '');
                        searchInput.value = keyword;
                        // Simpan target unit untuk difokuskan setelah reload
                        sessionStorage.setItem(OPNAME_TARGET_KEY, targetId);
                        setTimeout(() => {
                            searchForm.submit();
                        }, 150);
                    }
                }, 300);
            }
        } catch (error) {
            console.error('Error fetching product unit for opname:', error);
            qrResultElement.innerHTML = `
                <div class="alert alert-danger d-flex align-items-center mb-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.25rem;"></i>
                    <div><strong>Gagal mengambil data produk.</strong></div>
                </div>
                <p class="text-muted mb-2">Pastikan QR code valid atau coba lagi.</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="startOpnameQRScanner()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi
                </button>
            `;
        }
    }

    // Setelah halaman dimuat, coba fokuskan baris berdasarkan target yang disimpan (jika ada)
    (function focusTargetAfterReload() {
        const targetId = sessionStorage.getItem(OPNAME_TARGET_KEY);
        if (!targetId) return;

        const inputField = document.querySelector(`input[name="opname[${targetId}][stok_fisik]"]`);
        if (!inputField) return;

        const row = inputField.closest('tr');
        setTimeout(() => {
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            highlightRow(row);
            inputField.focus();
            inputField.select();
            sessionStorage.removeItem(OPNAME_TARGET_KEY);
        }, 300);
    })();
});
</script>
@endpush
@endsection
