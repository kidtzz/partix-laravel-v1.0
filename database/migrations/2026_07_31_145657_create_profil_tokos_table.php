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
        Schema::create('profil_tokos', function (Blueprint $table) {
            $table->id();
            $table->string('kode_profil')->unique();
            $table->string('nama_toko');
            $table->string('logo_toko')->nullable();
            $table->string('alamat_toko');
            $table->string('nomor_telepon');
            $table->string('footer_invoice');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_tokos');
    }
};
