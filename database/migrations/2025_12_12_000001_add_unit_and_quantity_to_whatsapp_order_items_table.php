<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('whatsapp_order_items', function (Blueprint $table) {
            // Tambahkan kolom product_unit_id untuk relasi ke product_units
            $table->foreignId('product_unit_id')->nullable()->after('color_id')
                ->constrained('product_units')->onDelete('cascade');
            
            // Tambahkan kolom quantity untuk jumlah yang dipesan
            $table->integer('quantity')->default(1)->after('product_unit_id')
                ->comment('Jumlah yang dipesan dalam satuan unit');
            
            // Index untuk performa
            $table->index('product_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn(['product_unit_id', 'quantity']);
        });
    }
};

