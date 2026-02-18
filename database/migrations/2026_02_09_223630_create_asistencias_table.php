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
       Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clase_dictada_id')->constrained('clases_dictadas')->onDelete('cascade');
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->enum('estado', ['presente', 'ausente', 'justificado'])->default('presente');
            $table->boolean('descuenta_clase')->default(true);
            $table->foreignId('registrado_por')->constrained('docentes')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['alumno_id', 'estado']);
            $table->index('clase_dictada_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
