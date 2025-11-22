<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke header refund
     */
    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    /**
     * Relasi ke unit produk yang direfund
     */
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
