<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Inventory extends Model
{
    use HasFactory;

    protected $table = 'stok_barang';

    protected $fillable = [
        'product_id',
        'product_unit_id',
        'quantity',
        'type',
        'user_id',
        'description',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
