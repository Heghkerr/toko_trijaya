<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum status dari 'unpaid,paid' menjadi 'unpaid,paid,sent,finished'
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('unpaid', 'paid', 'sent', 'finished') NOT NULL DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke status lama
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid'");
    }
};

