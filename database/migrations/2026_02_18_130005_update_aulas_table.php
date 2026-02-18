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
        Schema::table('aulas', function (Blueprint $table) {
            // Renombrar si existe capacidad_maxima
            if (Schema::hasColumn('aulas', 'capacidad_maxima')) {
                $table->renameColumn('capacidad_maxima', 'capacidad');
            }

            // Agregar campos faltantes
            if (!Schema::hasColumn('aulas', 'tipo')) {
                $table->enum('tipo', ['presencial', 'virtual'])->default('presencial')->after('capacidad');
            }

            if (!Schema::hasColumn('aulas', 'ubicacion')) {
                $table->string('ubicacion')->nullable()->after('tipo');
            }

            if (!Schema::hasColumn('aulas', 'estado')) {
                $table->enum('estado', ['disponible', 'mantenimiento'])->default('disponible')->after('ubicacion');
            }

            // Asegurar que nombre sea único
            // Nota: change() requiere doctrine/dbal, pero en Laravel 11 ya es nativo
            $table->string('nombre')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
            $table->dropColumn(['tipo', 'ubicacion', 'estado']);
            $table->renameColumn('capacidad', 'capacidad_maxima');
        });
    }
};
