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
        Schema::create('solicitudes_materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->text('contenidos')->nullable();
            $table->json('disponibilidad')->nullable(); // Ej: ["Lunes Mañana", "Martes Tarde"]
            $table->boolean('urgente')->default(false);
            $table->enum('estado', ['pendiente', 'agrupado', 'cancelado'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->onDelete('set null');
            $table->foreignId('aula_id')->nullable()->constrained('aulas')->onDelete('set null');
            $table->string('nombre')->nullable(); // Ej: "Física - Turno Tarde"
            $table->text('contenidos_clase')->nullable();
            $table->date('fecha'); // Fecha de la clase
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('tipo', ['recurrente', 'intensivo'])->default('recurrente');
            $table->enum('estado', ['activo', 'finalizado', 'cancelado'])->default('activo');
            $table->timestamps();
        });

        // Pivot table to link Alumnos to Grupos
        Schema::create('alumno_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno_grupo');
        Schema::dropIfExists('grupos');
        Schema::dropIfExists('solicitudes_materias');
    }
};
