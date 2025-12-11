<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->renameColumn('transaction_id', 'original_transaction_id');
        });
        
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreign('original_transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['original_transaction_id']);
            $table->renameColumn('original_transaction_id', 'transaction_id');
        });
        
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });
    }
};

