<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_disponibilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')
                  ->constrained('docentes')
                  ->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->string('nota')->nullable()->comment('Ej: Solo clases de mate');
            $table->timestamps();

            $table->index(['docente_id', 'fecha']);
            $table->index(['fecha', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_disponibilidades');
    }
};
