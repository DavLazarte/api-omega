<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packs_clases', function (Blueprint $table) {
            $table->foreignId('pack_catalogo_id')
                  ->nullable()
                  ->after('alumno_id')
                  ->constrained('pack_catalogos')
                  ->onDelete('set null');

            $table->foreignId('cargado_por')
                  ->nullable()
                  ->after('validado_por')
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('User que registró el pago (admin o docente)');
        });
    }

    public function down(): void
    {
        Schema::table('packs_clases', function (Blueprint $table) {
            $table->dropForeign(['pack_catalogo_id']);
            $table->dropForeign(['cargado_por']);
            $table->dropColumn(['pack_catalogo_id', 'cargado_por']);
        });
    }
};
