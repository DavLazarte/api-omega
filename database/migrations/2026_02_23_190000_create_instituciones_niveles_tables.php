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
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        Schema::create('niveles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->foreignId('institucion_id')->nullable()->after('id')->constrained('instituciones')->onDelete('set null');
            $table->foreignId('nivel_id')->nullable()->after('institucion_id')->constrained('niveles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nivel_id');
            $table->dropConstrainedForeignId('institucion_id');
        });
        Schema::dropIfExists('niveles');
        Schema::dropIfExists('instituciones');
    }
};
