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
        // 1. Sincronizar cualquier usuario que tuviera texto pero no IDs foráneos
        if (Schema::hasColumn('usuarios', 'carrera')) {
            $carreras = DB::table('carreras')->pluck('id', 'nombre');
            foreach ($carreras as $nombre => $id) {
                DB::table('usuarios')->whereNull('carrera_id')->where('carrera', $nombre)->update(['carrera_id' => $id]);
            }
        }

        if (Schema::hasColumn('usuarios', 'cargo_actual')) {
            $cargos = DB::table('cargos')->pluck('id', 'nombre');
            foreach ($cargos as $nombre => $id) {
                DB::table('usuarios')->whereNull('cargo_id')->where('cargo_actual', $nombre)->update(['cargo_id' => $id]);
            }
        }

        // 2. Purgar las columnas de texto redundantes 'carrera' y 'cargo_actual' de la tabla usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'carrera')) {
                $table->dropColumn('carrera');
            }
            if (Schema::hasColumn('usuarios', 'cargo_actual')) {
                $table->dropColumn('cargo_actual');
            }
        });

        // 3. Purgar carreras obsoletas de prueba cargadas para pruebas de historial
        DB::table('carreras')->where('codigo', 'APU-HIST')->orWhere('activa', false)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('carrera', 100)->nullable()->after('fecha_nacimiento');
            $table->string('cargo_actual', 50)->nullable()->after('carrera_id');
        });
    }
};
