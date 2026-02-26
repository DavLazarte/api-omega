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
            $table->string('telefono_secundario')->nullable()->after('telefono');
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->string('anio')->nullable()->after('nivel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('telefono_secundario');
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn('anio');
        });
    }
};
