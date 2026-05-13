<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packs_clases', function (Blueprint $table) {
            // Precio total acordado para el pack (puede diferir del catálogo).
            // Si es null se asume pago total (monto_pagado = precio completo).
            $table->decimal('monto_total', 10, 2)
                  ->nullable()
                  ->after('monto_pagado')
                  ->comment('Precio total acordado. Si es null, monto_pagado cubre el pack completo.');

            // Clases que se acreditan al alumno con ESTE pago específico.
            // Permite habilitar solo algunas clases cuando el pago es parcial.
            $table->integer('clases_habilitadas')
                  ->nullable()
                  ->after('cantidad_clases')
                  ->comment('Clases acreditadas al alumno con este pago. Null = igual a cantidad_clases.');

            // Fecha en que se originó la deuda (primer pago parcial).
            // Se usa para calcular la antigüedad y determinar el estado del alumno.
            $table->date('fecha_deuda_origen')
                  ->nullable()
                  ->after('monto_total')
                  ->comment('Fecha de inicio de la deuda. Se completa solo si monto_pagado < monto_total.');
        });
    }

    public function down(): void
    {
        Schema::table('packs_clases', function (Blueprint $table) {
            $table->dropColumn(['monto_total', 'clases_habilitadas', 'fecha_deuda_origen']);
        });
    }
};
