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
        Schema::create('whatsapp_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_order_id')->constrained('whatsapp_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('color_id')->nullable()->constrained('product_colors')->onDelete('set null');
            $table->integer('stock_pcs')->default(0)->comment('Stok tersedia saat pesanan dibuat (dalam PCS)');
            $table->timestamps();

            // Index untuk performa
            $table->index('whatsapp_order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_order_items');
    }
};

