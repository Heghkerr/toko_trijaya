<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_return_details';

    // Tidak perlu timestamps (created_at, updated_at) untuk tabel detail
    public $timestamps = false;

    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'quantity',
        'cost_price',
        'subtotal',
    ];

    /**
     * Relasi ke produk
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke header retur
     */
    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
}
