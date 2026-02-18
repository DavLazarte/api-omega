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
       Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('aula_id')->constrained('aulas')->onDelete('cascade');
            $table->tinyInteger('dia_semana'); // 1-7 (lunes-domingo)
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('cupo_maximo')->default(3);
            $table->enum('estado', ['propuesto', 'confirmado', 'cancelado'])->default('propuesto');
            $table->enum('tipo', ['recurrente', 'unico'])->default('recurrente');
            $table->timestamps();
            
            $table->index(['docente_id', 'estado']);
            $table->index(['aula_id', 'dia_semana']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
