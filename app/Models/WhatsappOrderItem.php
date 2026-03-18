<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappOrderItem extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_order_items';

    protected $fillable = [
        'whatsapp_order_id',
        'product_id',
        'color_id',
        'product_unit_id',
        'quantity',
        'stock_pcs',
    ];

    /**
     * Relasi ke WhatsappOrder (parent)
     */
    public function order()
    {
        return $this->belongsTo(WhatsappOrder::class, 'whatsapp_order_id');
    }

    /**
     * Relasi ke Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke ProductColor
     */
    public function color()
    {
        return $this->belongsTo(ProductColor::class, 'color_id');
    }

    /**
     * Relasi ke ProductUnit
     */
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}

