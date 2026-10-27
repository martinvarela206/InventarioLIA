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
        Schema::table('usuarios', function (Blueprint $table) {
            $table->date('fecha_egreso')->nullable()->after('activo');
            $table->boolean('huella_borrada')->default(false)->after('fecha_egreso');
            $table->timestamp('anonimizado_el')->nullable()->after('huella_borrada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['fecha_egreso', 'huella_borrada', 'anonimizado_el']);
        });
    }
};
