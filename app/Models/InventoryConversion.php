<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'from_product_unit_id',
        'to_product_unit_id',
        'quantity_from',
        'quantity_to',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'from_product_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'to_product_unit_id');
    }
}
