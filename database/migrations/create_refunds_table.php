<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundsTable extends Migration
{
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // kasir yang memproses refund
            $table->decimal('total_refund', 14, 2)->default(0);
            $table->text('reason')->nullable();
            $table->timestamps();

            // index untuk query cepat
            $table->index(['transaction_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
