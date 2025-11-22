<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'conversion_value',
        'price',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'product_unit_id');
    }
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'product_id', 'product_id')
                    // [PERBAIKAN] Ganti whereColumn dengan whereRaw untuk memaksa collation
                    ->whereRaw(
                        'transaction_details.unit_name COLLATE utf8mb4_unicode_ci = product_units.name COLLATE utf8mb4_unicode_ci'
                    );
    }


}
