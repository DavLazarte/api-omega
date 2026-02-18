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
        Schema::create('solicitudes_turno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->enum('tipo', ['catalogo', 'nuevo'])->default('nuevo');
            $table->enum('franja_horaria', ['mañana', 'siesta', 'tarde', 'noche'])->nullable();
            $table->foreignId('turno_existente_id')->nullable()->constrained('turnos')->onDelete('set null');
            $table->enum('estado', ['pendiente', 'agrupada', 'rechazada', 'expirada'])->default('pendiente');
            $table->timestamps();
            
            $table->index('estado');
            $table->index(['materia_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_turno');
    }
};
