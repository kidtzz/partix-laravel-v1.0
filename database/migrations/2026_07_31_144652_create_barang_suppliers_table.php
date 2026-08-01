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
        Schema::create('barang_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->integer('harga_beli');
            $table->integer('diskon_persen')->default(0);
            $table->string('satuan');
            $table->integer('isi_per_box');
            $table->integer('stok_saat_ini');
            $table->integer('minimum_stok');
            $table->string('lokasi_rak')->nullable();
            $table->string('kode_barang_supplier')->nullable();
            $table->boolean('is_utama')->default(false);
            $table->enum('status', ['Aktif', 'Non Aktif'])->default('Aktif');
            $table->date('tanggal_masuk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_suppliers');
    }
};
