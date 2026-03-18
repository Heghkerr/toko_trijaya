<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CATATAN: Migration ini TIDAK DIPERLUKAN karena:
     * - Kolom 'items' sudah bertipe JSON di tabel whatsapp_orders
     * - Kita cukup mengubah struktur JSON yang disimpan (menambahkan product_id & color_id)
     * - Tidak perlu alter table structure
     * 
     * File ini hanya sebagai dokumentasi.
     * Jangan jalankan migration ini.
     */
    public function up(): void
    {
        // Tidak ada perubahan struktur tabel
        // Hanya perubahan format data JSON di kolom 'items'
        
        // Format lama:
        // {"product_name": "KELING 10", "color_name": "HITAM", "stock_pcs": 100}
        
        // Format baru:
        // {"product_id": 1, "color_id": 2, "stock_pcs": 100}
    }

    public function down(): void
    {
        // Tidak ada yang perlu di-rollback
    }
};

