<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke transaksi asli
     */
    public function originalTransaction()
    {
        return $this->belongsTo(Transaction::class, 'original_transaction_id');
    }

    /**
     * Relasi ke user/kasir yang memproses
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke detail barang yang direfund
     */
    public function details()
    {
        return $this->hasMany(RefundDetail::class);
    }
}
