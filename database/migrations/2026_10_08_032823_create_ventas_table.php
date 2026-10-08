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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas');
            $table->enum('tipo', ['sesion', 'directa'])->default('sesion');
            $table->string('nombre_nino')->nullable();
            $table->dateTime('inicio_sesion')->nullable();
            $table->decimal('total', 8, 2)->default(0);
            $table->decimal('monto_pagado', 8, 2)->default(0);
            $table->enum('estado', ['pendiente', 'parcial', 'completado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
