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
        Schema::create('return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_transaction_id')->constrained('return_transactions')->onDelete('cascade');
            $table->foreignId('barang_direturn_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('qty_direturn');
            $table->foreignId('barang_pengganti_id')->nullable()->constrained('barangs')->onDelete('set null');
            $table->integer('qty_pengganti')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_details');
    }
};
