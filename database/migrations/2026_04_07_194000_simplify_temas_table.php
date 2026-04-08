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
        Schema::table('temas', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['nivel_id']);
            $table->dropForeign(['institucion_id']);
            
            // Drop columns
            $table->dropColumn(['nivel_id', 'institucion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temas', function (Blueprint $table) {
            $table->foreignId('nivel_id')->nullable()->constrained('niveles')->onDelete('cascade');
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->onDelete('cascade');
        });
    }
};
