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
       Schema::create('packs_clases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->integer('cantidad_clases');
            $table->decimal('monto_pagado', 10, 2);
            $table->enum('metodo_pago', ['transferencia', 'efectivo', 'mercado_pago']);
            $table->enum('estado', ['pendiente_validacion', 'validado', 'rechazado'])->default('pendiente_validacion');
            $table->date('fecha_pago_informado');
            $table->date('fecha_validacion')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->string('comprobante_path')->nullable();
            $table->timestamps();
            
            $table->index('estado');
            $table->index(['alumno_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packs_clases');
    }
};
