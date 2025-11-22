<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * [DIGANTI] dari ProductType
 */
class Supplier extends Model
{
    use HasFactory;

    /**
     * [DIGANTI] Nama tabel
     */
    protected $table = 'suppliers';

    /**
     * [DIGANTI] Kolom yang bisa diisi
     * Menambahkan 'phone'
     */
    protected $fillable = [
        'name',
        'phone',
    ];

    /**
     * [DIGANTI] Relasi
     * Mendapatkan semua pembelian yang terkait dengan supplier ini.
     * (Berguna untuk mengecek sebelum menghapus)
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }
}
