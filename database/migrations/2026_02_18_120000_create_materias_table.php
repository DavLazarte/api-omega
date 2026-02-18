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
        // Solo la creamos si no existe ya
        if (!Schema::hasTable('materias')) {
            Schema::create('materias', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->enum('nivel', ['secundario', 'universitario']);
                $table->integer('duracion_minutos')->default(90);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
