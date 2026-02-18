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
            $table->string('email')->nullable()->unique()->after('nombre');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('saldo_clases');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['email', 'user_id']);
        });
    }
};
