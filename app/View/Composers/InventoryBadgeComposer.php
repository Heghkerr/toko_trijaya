<?php

namespace App\View\Composers;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class InventoryBadgeComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Hitung jumlah produk yang overstock atau understock
        $alertCount = $this->getInventoryAlertCount();

        $view->with('inventoryAlertCount', $alertCount);
    }

    /**
     * Hitung jumlah produk yang overstock atau understock
     */
    private function getInventoryAlertCount(): int
    {
        // Query untuk mendapatkan produk yang overstock atau understock
        // Overstock: global_stock >= max_stock (jika max_stock tidak null)
        // Understock: global_stock <= min_stock (jika min_stock tidak null)

        // Gunakan query yang lebih efisien dengan subquery
        // Hitung produk yang understock ATAU overstock (tidak keduanya sekaligus)
        $alertCount = Product::where(function ($query) {
                $query->where(function ($q) {
                    // Understock: global_stock <= min_stock
                    $q->whereNotNull('min_stock')
                      ->whereRaw('(SELECT COALESCE(SUM(stock * conversion_value), 0) FROM product_units WHERE product_units.product_id = products.id) <= products.min_stock');
                })
                ->orWhere(function ($q) {
                    // Overstock: global_stock >= max_stock
                    $q->whereNotNull('max_stock')
                      ->whereRaw('(SELECT COALESCE(SUM(stock * conversion_value), 0) FROM product_units WHERE product_units.product_id = products.id) >= products.max_stock');
                });
            })
            ->distinct()
            ->count();

        return $alertCount;
    }
}

