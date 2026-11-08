<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::unprepared('
                CREATE UNIQUE INDEX IF NOT EXISTS idx_auditoria_detalles_auditoria_elemento 
                ON auditoria_detalles(auditoria_id, elemento_id) 
                WHERE elemento_id IS NOT NULL;
            ');

            DB::unprepared('
                CREATE UNIQUE INDEX IF NOT EXISTS idx_auditoria_detalles_auditoria_puesto 
                ON auditoria_detalles(auditoria_id, puesto) 
                WHERE puesto IS NOT NULL;
            ');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('auditoria_detalles', function (Blueprint $table) {
                $table->unique(['auditoria_id', 'elemento_id'], 'idx_auditoria_detalles_auditoria_elemento');
                $table->unique(['auditoria_id', 'puesto'], 'idx_auditoria_detalles_auditoria_puesto');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::unprepared('DROP INDEX IF EXISTS idx_auditoria_detalles_auditoria_elemento;');
            DB::unprepared('DROP INDEX IF EXISTS idx_auditoria_detalles_auditoria_puesto;');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('auditoria_detalles', function (Blueprint $table) {
                $table->dropUnique('idx_auditoria_detalles_auditoria_elemento');
                $table->dropUnique('idx_auditoria_detalles_auditoria_puesto');
            });
        }
    }
};
