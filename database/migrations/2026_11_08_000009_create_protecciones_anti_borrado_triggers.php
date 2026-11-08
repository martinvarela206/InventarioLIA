<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // 1. Prohibir hard delete sobre elementos (deben usar SoftDeletes)
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_elementos_before_delete
                BEFORE DELETE ON elementos
                FOR EACH ROW
                BEGIN
                    SELECT RAISE(ABORT, 'Prohibido eliminar activos fisicamente: los elementos patrimoniales solo admiten baja logica (soft delete).');
                END;
            ");

            // 2. Prohibir eliminación de usuarios con movimientos históricos patrimoniales
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_usuarios_before_delete
                BEFORE DELETE ON usuarios
                FOR EACH ROW
                WHEN (
                    EXISTS (SELECT 1 FROM movimientos WHERE user_id = OLD.id)
                )
                BEGIN
                    SELECT RAISE(ABORT, 'Integridad referencial y custodia legal: Prohibido eliminar usuarios con historial patrimonial.');
                END;
            ");

            // 3. Prohibir eliminación de ubicaciones con movimientos o auditorías
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_ubicaciones_before_delete
                BEFORE DELETE ON ubicaciones
                FOR EACH ROW
                WHEN (
                    EXISTS (SELECT 1 FROM movimientos WHERE ubicacion_id = OLD.id) OR
                    EXISTS (SELECT 1 FROM auditorias WHERE ubicacion_id = OLD.id)
                )
                BEGIN
                    SELECT RAISE(ABORT, 'Prohibido eliminar ubicaciones con movimientos historicos o auditorias vinculadas.');
                END;
            ");

            // 4. Prohibir eliminación de roles canónicos
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_roles_before_delete
                BEFORE DELETE ON roles
                FOR EACH ROW
                WHEN (OLD.rol IN ('app_admin', 'user_admin', 'asset_admin', 'item_admin', 'tec', 'subtec', 'rev', 'registered'))
                BEGIN
                    SELECT RAISE(ABORT, 'Prohibido eliminar roles esenciales del sistema.');
                END;
            ");

            // 5. Prohibir eliminación de tipos con elementos asociados
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_tipos_before_delete
                BEFORE DELETE ON tipos
                FOR EACH ROW
                WHEN (EXISTS (SELECT 1 FROM elementos WHERE tipo_id = OLD.id))
                BEGIN
                    SELECT RAISE(ABORT, 'Prohibido eliminar tipos con elementos asociados.');
                END;
            ");
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            // 1. Prohibir hard delete sobre elementos
            DB::unprepared("
                CREATE TRIGGER trigger_elementos_before_delete
                BEFORE DELETE ON elementos
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Prohibido eliminar activos fisicamente: los elementos patrimoniales solo admiten baja logica (soft delete).';
                END;
            ");

            // 2. Prohibir eliminación de usuarios con movimientos históricos patrimoniales
            DB::unprepared("
                CREATE TRIGGER trigger_usuarios_before_delete
                BEFORE DELETE ON usuarios
                FOR EACH ROW
                BEGIN
                    IF (
                        EXISTS (SELECT 1 FROM movimientos WHERE user_id = OLD.id)
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Integridad referencial y custodia legal: Prohibido eliminar usuarios con historial patrimonial.';
                    END IF;
                END;
            ");

            // 3. Prohibir eliminación de ubicaciones con movimientos o auditorías
            DB::unprepared("
                CREATE TRIGGER trigger_ubicaciones_before_delete
                BEFORE DELETE ON ubicaciones
                FOR EACH ROW
                BEGIN
                    IF (
                        EXISTS (SELECT 1 FROM movimientos WHERE ubicacion_id = OLD.id) OR
                        EXISTS (SELECT 1 FROM auditorias WHERE ubicacion_id = OLD.id)
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Prohibido eliminar ubicaciones con movimientos historicos o auditorias vinculadas.';
                    END IF;
                END;
            ");

            // 4. Prohibir eliminación de roles canónicos
            DB::unprepared("
                CREATE TRIGGER trigger_roles_before_delete
                BEFORE DELETE ON roles
                FOR EACH ROW
                BEGIN
                    IF (OLD.rol IN ('app_admin', 'user_admin', 'asset_admin', 'item_admin', 'tec', 'subtec', 'rev', 'registered')) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Prohibido eliminar roles esenciales del sistema.';
                    END IF;
                END;
            ");

            // 5. Prohibir eliminación de tipos con elementos asociados
            DB::unprepared("
                CREATE TRIGGER trigger_tipos_before_delete
                BEFORE DELETE ON tipos
                FOR EACH ROW
                BEGIN
                    IF (EXISTS (SELECT 1 FROM elementos WHERE tipo_id = OLD.id)) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Prohibido eliminar tipos con elementos asociados.';
                    END IF;
                END;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['sqlite', 'mysql', 'mariadb'], true)) {
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_elementos_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_usuarios_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_ubicaciones_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_roles_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_tipos_before_delete;');
        }
    }
};
