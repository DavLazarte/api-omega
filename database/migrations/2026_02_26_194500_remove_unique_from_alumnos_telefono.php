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
        Schema::table('alumnos', function (Blueprint $table) {
            // Eliminar el índice único del campo teléfono
            $table->dropUnique('alumnos_telefono_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            // Restaurar el índice único si se revierte la migración
            $table->unique('telefono', 'alumnos_telefono_unique');
        });
    }
};
