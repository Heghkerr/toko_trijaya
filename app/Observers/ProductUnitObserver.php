<?php

namespace App\Observers;

use App\Models\ProductUnit;
use Illuminate\Support\Facades\Log;

class ProductUnitObserver
{
    public function saved(ProductUnit $unit)
    {
        $product = $unit->product;

        // Refresh relasi supaya hitungan akurat
        $product->load('units');

        // Hitung manual lewat accessor yang sudah kita buat di Model
        $currentStock = $product->current_global_stock;

        // LOGIKA NOTIFIKASI
        if ($currentStock <= $product->min_stock_global) {
            // PANGGIL NOTIFIKASI PWA DISINI
            Log::warning("ALERT PWA: {$product->name} sisa {$currentStock}");
        }
    }
}
