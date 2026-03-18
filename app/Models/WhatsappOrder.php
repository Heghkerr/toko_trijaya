<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappOrder extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_orders';

    protected $fillable = [
        'name',
        'phone',
        'order_text',
        'status',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    /**
     * Relasi ke WhatsappOrderItem (detail items)
     */
    public function orderItems()
    {
        return $this->hasMany(WhatsappOrderItem::class, 'whatsapp_order_id');
    }

    /**
     * Relasi ke Transaction (one-to-one)
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'whatsapp_order_id');
    }
}

