<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop old columns that don't match the model
            $table->dropColumn(['price', 'type', 'weight']);
            
            // Add new columns based on Product model
            $table->foreignId('type_id')->nullable()->constrained('product_types')->onDelete('set null');
            $table->foreignId('color_id')->nullable()->constrained('product_colors')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->decimal('price_buy', 10, 2)->nullable();
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropForeign(['color_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['type_id', 'color_id', 'supplier_id', 'price_buy', 'min_stock', 'max_stock']);
            
            $table->decimal('price', 10, 2)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('weight', 100)->nullable();
        });
    }
};

