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
        Schema::table('asistencias', function (Blueprint $table) {
            // Eliminar la foreign key y columna anterior
            $table->dropForeign(['clase_dictada_id']);
            $table->dropIndex(['clase_dictada_id']);
            $table->dropColumn('clase_dictada_id');
            
            // Agregar la nueva conexión con grupos
            $table->foreignId('grupo_id')->after('id')->constrained('grupos')->onDelete('cascade');
            
            // Agregar campo observaciones si no existía (era de clases_dictadas antes)
            $table->text('observaciones')->nullable()->after('descuenta_clase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign(['grupo_id']);
            $table->dropColumn('grupo_id');
            $table->dropColumn('observaciones');
            
            $table->foreignId('clase_dictada_id')->after('id')->constrained('clases_dictadas')->onDelete('cascade');
        });
    }
};
