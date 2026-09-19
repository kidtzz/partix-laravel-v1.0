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
        // Karena Laravel 11/12 kadang bermasalah mengubah auto-increment int ke string
        // via Schema builder, kita eksekusi SQL mentah.
        
        // 1. Untuk tabel barang_returns
        DB::statement('ALTER TABLE barang_returns MODIFY id VARCHAR(255) NOT NULL');

        // 2. Untuk tabel return_suppliers
        DB::statement('ALTER TABLE return_suppliers MODIFY id VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returns_tables', function (Blueprint $table) {
            //
        });
    }
};
