<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\TransactionDetail;
use App\Models\Inventory;
use App\Models\ProductType;
use App\Models\Supplier;
use App\Models\ProductUnit;


class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_id',
        'color_id',
        'price_buy',
        'description',
        'supplier_id',
        'image',
        'min_stock',
        'max_stock'

    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'product_id');
    }
    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }
    public function type()
    {
        return $this->belongsTo(ProductType::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function color()
    {
        return $this->belongsTo(ProductColor::class);
    }
    public function scopeWithGlobalStock(Builder $query)
    {
        return $query->addSelect([
            'current_global_stock' => ProductUnit::selectRaw('COALESCE(SUM(stock * conversion_value), 0)')
                ->whereColumn('product_id', 'products.id')
        ]);
    }
    public function scopeOnlyUnderstock(Builder $query)
    {
        // Pastikan scopeWithGlobalStock dipanggil, atau kita pakai logic raw where
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(stock * conversion_value), 0) FROM product_units WHERE product_units.product_id = products.id) <= products.min_stock'
        );
    }
    public function getCurrentGlobalStockAttribute()
    {
        // Jika kita sudah load pakai scope, pakai nilainya. Jika belum, hitung manual.
        if (!array_key_exists('current_global_stock', $this->attributes)) {
            return $this->units->sum(function ($unit) {
                return $unit->stock * $unit->conversion_value;
            });
        }
        return $this->attributes['current_global_stock'];
    }



}
