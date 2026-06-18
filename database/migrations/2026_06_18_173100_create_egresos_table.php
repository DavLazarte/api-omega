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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->enum('metodo_pago', ['transferencia', 'efectivo', 'mercado_pago'])->default('transferencia');
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->onDelete('set null');
            $table->decimal('horas_pagadas', 8, 2)->nullable();
            $table->string('mes_ejercicio', 7)->nullable(); // YYYY-MM
            $table->string('comprobante_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
