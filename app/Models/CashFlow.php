<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_type',
        'flow_type',
        'account', // <-- TAMBAHKAN BARIS INI
        'amount',
        'description',
        'transaction_id',
        'purchase_id',
        'date'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'transaction_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

