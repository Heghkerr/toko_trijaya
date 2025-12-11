<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name', 255);
            $table->decimal('conversion_value', 10, 2)->default(1);
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};

