<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('source_type', 50)->nullable();
            $table->enum('flow_type', ['masuk', 'keluar']);
            $table->string('account', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->onDelete('set null');
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flows');
    }
};

