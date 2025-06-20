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
        // database/migrations/xxxx_xx_xx_create_vehicles_table.php
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('merk');
            $table->string('tipe');
            $table->year('tahun');
            $table->unsignedBigInteger('harga');
            $table->integer('stok');
            $table->string('foto')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
