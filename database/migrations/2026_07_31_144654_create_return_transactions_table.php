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
        Schema::create('return_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('no_return')->unique();
            $table->string('no_invoice');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('jenis_return');
            $table->integer('selisih_harga');
            $table->string('alasan_return')->nullable();
            $table->string('status')->default('Selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_transactions');
    }
};
