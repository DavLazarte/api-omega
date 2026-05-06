<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_catalogos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('nivel', ['universitario_terciario', 'secundario_ingresos']);
            $table->integer('cantidad_clases')->nullable()->comment('NULL = clase suelta');
            $table->decimal('precio', 10, 2);
            $table->boolean('es_clase_suelta')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('nivel');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_catalogos');
    }
};
