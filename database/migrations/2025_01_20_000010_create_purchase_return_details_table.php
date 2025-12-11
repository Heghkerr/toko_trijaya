<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->decimal('cost_price', 10, 2);
            $table->decimal('subtotal', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_details');
    }
};

