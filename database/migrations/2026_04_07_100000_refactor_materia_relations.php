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
        // 1. Añadir nueva columna anios y eliminar viejos FKs y columnas
        Schema::table('materias', function (Blueprint $table) {
            $table->json('anios')->nullable()->after('nombre');
            
            // Drop foreign keys si existen
            // Asumimos que existen. El array con nombre de columna Laravel lo resuelve al nombre del index.
            $table->dropForeign(['institucion_id']);
            $table->dropForeign(['nivel_id']);
            
            $table->dropColumn(['institucion_id', 'nivel_id', 'nivel', 'anio']);
        });

        // 2. Crear tabla pivot institucion_materia
        Schema::create('institucion_materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['institucion_id', 'materia_id']);
        });

        // 3. Crear tabla pivot materia_nivel
        Schema::create('materia_nivel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('nivel_id')->constrained('niveles')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['materia_id', 'nivel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_nivel');
        Schema::dropIfExists('institucion_materia');

        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn('anios');
            $table->string('nivel')->nullable();
            $table->string('anio')->nullable();
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->onDelete('set null');
            $table->foreignId('nivel_id')->nullable()->constrained('niveles')->onDelete('set null');
        });
    }
};
