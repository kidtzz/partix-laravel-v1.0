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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level')->default('error'); // error, warning, info
            $table->text('message');
            $table->longText('context')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('user')->nullable(); // Can be string or id depending on frontend
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
