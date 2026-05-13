<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packs_clases', function (Blueprint $table) {
            // Referencia al pack "padre" cuando este registro es un abono parcial.
            // Si pack_origen_id es null → este registro ES el pack original.
            // Si pack_origen_id tiene valor → este registro es un abono sobre ese pack.
            $table->foreignId('pack_origen_id')
                  ->nullable()
                  ->after('pack_catalogo_id')
                  ->constrained('packs_clases')
                  ->onDelete('set null')
                  ->comment('ID del pack original. Poblado solo en abonos/cuotas posteriores.');
        });
    }

    public function down(): void
    {
        Schema::table('packs_clases', function (Blueprint $table) {
            $table->dropForeign(['pack_origen_id']);
            $table->dropColumn('pack_origen_id');
        });
    }
};
