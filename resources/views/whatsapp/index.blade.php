@extends('layouts.app')

@section('title', 'Pesanan WhatsApp')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary">
            <i class="bi bi-whatsapp me-2"></i> Pesanan WhatsApp
        </h6>
    </div>
    <div class="card-body">
        @if($orders->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0">Belum ada pesanan dari WhatsApp.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th width="10%">Tanggal</th>
                            <th width="12%">Nama</th>
                            <th width="12%">Telepon</th>
                            <th width="20%">Pesanan</th>
                            <th width="10%">Status</th>
                            <th width="18%">Stok Barang</th>
                            <th width="18%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ strtoupper($order->name) }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ strtoupper($order->order_text) }}</td>
                            <td>
                                <span class="badge
                                    @if($order->status === 'confirmed') bg-success
                                    @elseif($order->status === 'pending') bg-warning text-dark
                                    @elseif($order->status === 'cancelled') bg-danger
                                    @elseif($order->status === 'processed') bg-info
                                    @elseif($order->status === 'sent') bg-primary
                                    @elseif($order->status === 'done') bg-success
                                    @else bg-secondary
                                    @endif">
                                    @if($order->status === 'confirmed')
                                        ✓ Dikonfirmasi
                                    @elseif($order->status === 'pending')
                                        ⏳ Pending
                                    @elseif($order->status === 'cancelled')
                                        ✕ Dibatalkan
                                    @elseif($order->status === 'processed')
                                        ✓ Diproses
                                    @elseif($order->status === 'sent')
                                        🚚 Dikirim
                                    @elseif($order->status === 'done')
                                        ✅ Selesai
                                    @else
                                        {{ ucfirst($order->status) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($order->orderItems->isNotEmpty())
                                    <ul class="mb-0 ps-3 small">
                                        @foreach($order->orderItems as $item)
                                            <li>
                                                {{ $item->product ? $item->product->name : '-' }}
                                                @if($item->color)
                                                    ({{ $item->color->name }})
                                                @endif
                                                @if($item->productUnit)
                                                    — {{ $item->quantity }} {{ $item->productUnit->name }}
                                                @else
                                                    — {{ $item->stock_pcs }} pcs
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif(is_array($order->items) && !empty($order->items))
                                    {{-- Fallback untuk data lama (JSON items) --}}
                                    <ul class="mb-0 ps-3 small">
                                        @foreach($order->items as $item)
                                            <li>{{ $item['product_name'] ?? '-' }} ({{ $item['color_name'] ?? '-' }}) — {{ $item['stock_pcs'] ?? 0 }} pcs</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($order->status === 'pending')
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('whatsapp.orders.process', $order->id) }}"
                                           class="btn btn-primary"
                                           title="Proses pesanan dan buat transaksi">
                                            <i class="bi bi-cart-check"></i> Proses
                                        </a>
                                        <button type="button"
                                                class="btn btn-danger"
                                                onclick="cancelOrder({{ $order->id }})"
                                                title="Batalkan pesanan">
                                            <i class="bi bi-x-circle"></i> Batal
                                        </button>
                                    </div>
                                @elseif($order->status === 'confirmed')
                                    @if($order->transaction)
                                        {{-- Sudah ada transaction, menunggu pembayaran --}}
                                        <div class="d-flex flex-column">
                                            <span class="text-warning small mb-1">
                                                <i class="bi bi-clock-history"></i> Menunggu Pembayaran
                                            </span>
                                            <a href="{{ route('transactions.show', $order->transaction->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Lihat transaksi">
                                                <i class="bi bi-receipt"></i> Lihat Transaksi
                                            </a>
                                        </div>
                                    @else
                                        {{-- Belum ada transaction, bisa diproses --}}
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('whatsapp.orders.process', $order->id) }}"
                                               class="btn btn-primary"
                                               title="Proses pesanan dan buat transaksi">
                                                <i class="bi bi-cart-check"></i> Proses
                                            </a>
                                            <button type="button"
                                                    class="btn btn-danger"
                                                    onclick="cancelOrder({{ $order->id }})"
                                                    title="Batalkan pesanan">
                                                <i class="bi bi-x-circle"></i> Batal
                                            </button>
                                        </div>
                                    @endif
                                @elseif($order->status === 'processed')
                                    <span class="text-info small">
                                        <i class="bi bi-credit-card-fill"></i> Sudah Dibayar
                                    </span>
                                @elseif($order->status === 'sent')
                                    <span class="text-primary small">
                                        <i class="bi bi-truck"></i> Sedang Dikirim
                                    </span>
                                @elseif($order->status === 'done')
                                    <span class="text-success small">
                                        <i class="bi bi-check-all"></i> Selesai
                                    </span>
                                @elseif($order->status === 'cancelled')
                                    <span class="text-danger small">
                                        <i class="bi bi-x-circle-fill"></i> Dibatalkan
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">

                        <li class="page-item @if($orders->onFirstPage()) disabled @endif">
                        <a class="page-link" href="{{ $orders->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo; Previous</span>
                        </a>
                        </li>

                        <li class="page-item @if(!$orders->hasMorePages()) disabled @endif">
                        <a class="page-link" href="{{ $orders->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">Next &raquo;</span>
                        </a>
                        </li>

                    </ul>
                </nav>

                <div class="mt-2 text-muted text-center">
                    Showing {{ $orders->firstItem() ?? 0 }}
                    to {{ $orders->lastItem() ?? 0 }}
                    of {{ $orders->total() }} results
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function cancelOrder(orderId) {
    if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/whatsapp/orders/${orderId}/cancel`;

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
    @media (max-width: 768px) {
        .table {
            font-size: 0.75rem;
        }

        .table th,
        .table td {
            padding: 0.4rem 0.3rem;
        }

        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }

        .badge {
            font-size: 0.7rem;
        }

        ul.small {
            font-size: 0.7rem;
        }
    }
</style>
@endpush

@endsection

