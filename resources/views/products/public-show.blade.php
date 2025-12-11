<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Produk - {{ $unit->product->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .product-card {
            max-width: 900px;
            margin: 2rem auto;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .product-image {
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
        }
        .product-title {
            color: #2c3e50;
            font-weight: 600;
        }
        .info-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .info-value {
            color: #212529;
            font-size: 1.1rem;
        }
        .price-value {
            color: #28a745;
            font-size: 1.8rem;
            font-weight: bold;
        }
        .stock-value {
            color: #0d6efd;
            font-size: 1.8rem;
            font-weight: bold;
        }
        .no-image {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card product-card shadow-sm">
            <div class="card-body p-4">
                <div class="row">
                    {{-- Kolom Gambar --}}
                    <div class="col-md-5 text-center mb-4 mb-md-0">
                        @if($unit->product->image)
                            <img src="{{ asset('storage/' . $unit->product->image) }}"
                                 alt="{{ $unit->product->name }}"
                                 class="img-fluid product-image mb-3"
                                 style="border: 2px solid #dee2e6;">
                        @else
                            <div class="no-image" style="height: 300px;">
                                <div class="text-center">
                                    <i class="bi bi-image" style="font-size: 4rem;"></i>
                                    <p class="mt-2 mb-0">Tidak ada gambar</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Kolom Info --}}
                    <div class="col-md-7">
                        <h2 class="product-title mb-2">{{ $unit->product->name }}</h2>
                        <p class="text-muted mb-4">
                            <i class="bi bi-tag me-1"></i>{{ $unit->product->type->name ?? 'Tidak ada jenis' }}
                        </p>

                        <hr class="my-4">

                        {{-- Info Detail --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="info-label mb-1">
                                    <i class="bi bi-palette me-1"></i>Warna
                                </div>
                                <div class="info-value">
                                    {{ $unit->product->color->name ?? '-' }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-label mb-1">
                                    <i class="bi bi-box me-1"></i>Satuan
                                </div>
                                <div class="info-value">
                                    {{ $unit->name }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-label mb-1">
                                    <i class="bi bi-arrow-repeat me-1"></i>Konversi
                                </div>
                                <div class="info-value">
                                    1 {{ $unit->name }} = {{ $unit->conversion_value }} pcs
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Stok & Harga --}}
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-label mb-2">
                                    <i class="bi bi-box-seam me-1"></i>Stok Saat Ini
                                </div>
                                <div class="stock-value">
                                    {{ $unit->stock ?? 0 }}
                                </div>
                                <small class="text-muted">{{ $unit->name }}</small>
                            </div>
                            <div class="col-6">
                                <div class="info-label mb-2">
                                    <i class="bi bi-currency-exchange me-1"></i>Harga Jual
                                </div>
                                <div class="price-value">
                                    Rp {{ number_format($unit->price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Info --}}
        <div class="text-center mt-4">
            <p class="text-muted mb-0">
                <small>Informasi produk dari Toko Trijaya</small>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

