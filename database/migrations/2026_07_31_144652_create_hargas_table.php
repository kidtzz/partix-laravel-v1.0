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
        Schema::create('hargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('harga_regular');
            $table->integer('harga_langganan');
            $table->integer('harga_teman');
            $table->date('tanggal_berlaku')->nullable();
            $table->enum('status_harga', ['Aktif', 'Non Aktif'])->default('Aktif');
            $table->string('keterangan_perubahan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hargas');
    }
};
