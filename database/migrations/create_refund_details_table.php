<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('refund_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('refund_id')->constrained('refunds')->onDelete('cascade');
            $table->foreignId('transaction_detail_id')->nullable()->constrained('transaction_details')->onDelete('set null');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0); // harga satuan saat transaksi
            $table->decimal('subtotal', 14, 2)->default(0); // quantity * unit_price
            $table->timestamps();

            // index untuk performa
            $table->index(['refund_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('refund_details');
    }
}
