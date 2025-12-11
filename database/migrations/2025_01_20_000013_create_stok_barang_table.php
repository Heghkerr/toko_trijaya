<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_barang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->onDelete('set null');
            $table->integer('quantity');
            $table->enum('type', ['masuk', 'keluar'])->default('masuk');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_barang');
    }
};

