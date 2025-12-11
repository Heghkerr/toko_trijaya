<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund_details', function (Blueprint $table) {
            // Drop old columns
            $table->dropForeign(['product_id']);
            $table->dropColumn(['transaction_detail_id', 'product_id', 'unit_price']);
            
            // Add new columns based on actual usage
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->onDelete('set null');
            $table->decimal('price_per_unit', 14, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('refund_details', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn(['product_unit_id', 'price_per_unit']);
            
            $table->foreignId('transaction_detail_id')->nullable()->constrained('transaction_details')->onDelete('set null');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('unit_price', 14, 2)->default(0);
        });
    }
};

