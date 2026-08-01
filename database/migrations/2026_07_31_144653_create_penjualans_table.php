<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('kategori_customer');
            $table->integer('subtotal');
            $table->integer('potongan')->default(0);
            $table->integer('total');
            $table->string('metode_pembayaran');
            $table->string('detail_pembayaran')->nullable();
            $table->integer('kembalian')->default(0);
            $table->string('status_transaksi')->default('Selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
