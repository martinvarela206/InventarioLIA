<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Eliminar cualquier fila de personal que no tenga user_id asociado
        DB::table('personal_lia')->whereNull('user_id')->delete();

        // 2. Eliminar columnas redundantes
        Schema::table('personal_lia', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'cargo', 'email', 'foto_path']);
        });

        // 3. Modificar clave foránea user_id a cascadeOnDelete y unique si no lo es
        Schema::table('personal_lia', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_lia', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->string('nombre')->nullable();
            $table->string('cargo')->nullable();
            $table->string('email')->nullable();
            $table->string('foto_path')->nullable();
        });
    }
};
