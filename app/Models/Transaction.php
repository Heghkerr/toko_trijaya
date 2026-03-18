<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'total_amount',
        'discount',
        'change_amount',
        'payment_method',
        'cash_amount',
        'status',
        'user_id',
        'customer_id',
        'whatsapp_order_id',
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'original_transaction_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function whatsappOrder()
    {
        return $this->belongsTo(WhatsappOrder::class);
    }
}
