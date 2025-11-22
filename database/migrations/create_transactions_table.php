<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_code', 20)->unique();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount', 15, 2)->default(0.00)->nullable();
            $table->decimal('change_amount', 15, 2)->default(0.00)->nullable();
            $table->enum('payment_method', ['cash', 'card', 'qris']);
            $table->decimal('cash_amount', 15, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
