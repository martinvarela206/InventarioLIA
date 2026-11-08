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
        $driver = DB::getDriverName();

        // 1. Eliminar índice compuesto defectuoso previo
        Schema::table('elementos', function (Blueprint $table) use ($driver) {
            // En SQLite dropIndex/dropUnique recrea la tabla si es necesario
            try {
                $table->dropUnique('elementos_nro_lia_nro_unsj_unique');
            } catch (\Throwable $e) {
                // Si el índice no existe bajo ese nombre exacto
            }
        });

        // 2. Crear índices de unicidad robustos por identificador
        if ($driver === 'sqlite') {
            DB::unprepared("
                CREATE UNIQUE INDEX IF NOT EXISTS idx_elementos_nro_lia_unique 
                ON elementos(nro_lia) 
                WHERE deleted_at IS NULL AND nro_lia IS NOT NULL;
            ");

            DB::unprepared("
                CREATE UNIQUE INDEX IF NOT EXISTS idx_elementos_nro_unsj_unique 
                ON elementos(nro_unsj) 
                WHERE deleted_at IS NULL AND nro_unsj IS NOT NULL;
            ");

            // Disparador de verificación CHECK en SQLite para asegurar al menos un identificador
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_elementos_check_identificadores_insert
                BEFORE INSERT ON elementos
                FOR EACH ROW
                WHEN NEW.nro_lia IS NULL AND NEW.nro_unsj IS NULL
                BEGIN
                    SELECT RAISE(ABORT, 'El elemento debe poseer al menos un identificador valido (Nro LIA o Nro UNSJ).');
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_elementos_check_identificadores_update
                BEFORE UPDATE ON elementos
                FOR EACH ROW
                WHEN NEW.nro_lia IS NULL AND NEW.nro_unsj IS NULL
                BEGIN
                    SELECT RAISE(ABORT, 'El elemento debe poseer al menos un identificador valido (Nro LIA o Nro UNSJ).');
                END;
            ");
        } elseif ($driver === 'mysql') {
            // En MySQL / MariaDB los índices únicos individuales permiten múltiples NULLs
            // pero garantizan unicidad estricta para cualquier valor presente
            Schema::table('elementos', function (Blueprint $table) {
                $table->unique('nro_lia', 'idx_elementos_nro_lia_unique');
                $table->unique('nro_unsj', 'idx_elementos_nro_unsj_unique');
            });

            DB::unprepared("
                ALTER TABLE elementos 
                ADD CONSTRAINT chk_elementos_identificador 
                CHECK (nro_lia IS NOT NULL OR nro_unsj IS NOT NULL);
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::unprepared("DROP INDEX IF EXISTS idx_elementos_nro_lia_unique;");
            DB::unprepared("DROP INDEX IF EXISTS idx_elementos_nro_unsj_unique;");
            DB::unprepared("DROP TRIGGER IF EXISTS trigger_elementos_check_identificadores_insert;");
            DB::unprepared("DROP TRIGGER IF EXISTS trigger_elementos_check_identificadores_update;");
        } elseif ($driver === 'mysql') {
            DB::unprepared("ALTER TABLE elementos DROP CONSTRAINT IF EXISTS chk_elementos_identificador;");
            Schema::table('elementos', function (Blueprint $table) {
                $table->dropUnique('idx_elementos_nro_lia_unique');
                $table->dropUnique('idx_elementos_nro_unsj_unique');
                $table->unique(['nro_lia', 'nro_unsj'], 'elementos_nro_lia_nro_unsj_unique');
            });
        }
    }
};
