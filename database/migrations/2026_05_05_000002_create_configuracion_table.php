<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->decimal('mora_porcentaje', 5, 2)->default(15);
            $table->integer('mora_dias')->default(10);
            $table->integer('max_deuda_suspension')->default(6);
            $table->integer('porcentaje_primer_cuota')->default(50);
            $table->json('horarios_atencion')->nullable()->comment('Array de {dia, inicio, fin}');
            $table->timestamps();
        });

        // Seed default config row
        DB::table('configuracion')->insert([
            'mora_porcentaje'       => 15,
            'mora_dias'             => 10,
            'max_deuda_suspension'  => 6,
            'porcentaje_primer_cuota' => 50,
            'horarios_atencion'     => json_encode([
                ['dia' => 'lunes',     'inicio' => '08:00', 'fin' => '20:00'],
                ['dia' => 'martes',    'inicio' => '08:00', 'fin' => '20:00'],
                ['dia' => 'miercoles', 'inicio' => '08:00', 'fin' => '20:00'],
                ['dia' => 'jueves',    'inicio' => '08:00', 'fin' => '20:00'],
                ['dia' => 'viernes',   'inicio' => '08:00', 'fin' => '20:00'],
                ['dia' => 'sabado',    'inicio' => '08:00', 'fin' => '13:00'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
