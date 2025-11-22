<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Product; // Tidak perlu jika sudah di-import di bawah
// use App\Models\User; // (Asumsi Anda sudah import User)
// use App\Models\ProductType; // (Asumsi Anda sudah import ProductType)
// use App\Models\ProductUnit; // (Asumsi Anda sudah import ProductUnit)


class Inventory extends Model
{
    use HasFactory;

    // [DIUBAH] Gunakan nama tabel dari screenshot Anda
    protected $table = 'stok_barang';

    protected $fillable = [
        'product_id',
        'product_unit_id', // [DIUBAH] Pastikan ini ada
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

    // [PERBAIKAN]
    // Relasi ini seharusnya ke ProductType melalui Product
    // Tapi akan lebih mudah jika Anda mengambilnya dari relasi product
    // $log->product->type->name
    // public function type()
    // {
    //     return $this->belongsTo(ProductType::class); // Ini mungkin salah
    // }

    // [PERBAIKAN] Nama relasi 'units' (plural) tidak tepat untuk 'belongsTo'
    // Seharusnya 'productUnit' (singular)
    public function productUnit()
    {
        // Relasi ke ProductUnit
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
