<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'total_cost',
        'profit',
        'total_sales',
        'cash_amount',
        'card_amount',
        'qris_amount',
        'transaction_count',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
