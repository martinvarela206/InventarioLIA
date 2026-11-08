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
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_movimientos_before_delete
                BEFORE DELETE ON movimientos
                FOR EACH ROW
                BEGIN
                    SELECT RAISE(ABORT, 'Los movimientos del inventario son inmutables y no pueden eliminarse.');
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trigger_movimientos_before_update
                BEFORE UPDATE ON movimientos
                FOR EACH ROW
                WHEN (
                    OLD.elemento_id IS NOT NEW.elemento_id OR
                    OLD.tipo_movimiento IS NOT NEW.tipo_movimiento OR
                    OLD.user_id IS NOT NEW.user_id OR
                    OLD.fecha IS NOT NEW.fecha OR
                    OLD.ubicacion_id IS NOT NEW.ubicacion_id OR
                    OLD.puesto IS NOT NEW.puesto OR
                    OLD.solicitante IS NOT NEW.solicitante OR
                    OLD.fecha_prestamo IS NOT NEW.fecha_prestamo OR
                    OLD.correccion_de_id IS NOT NEW.correccion_de_id OR
                    OLD.ticket_id IS NOT NEW.ticket_id OR
                    OLD.comentario IS NOT NEW.comentario
                )
                BEGIN
                    SELECT RAISE(ABORT, 'Los movimientos del inventario son inmutables. Solo se permite actualizar fecha_devolucion y estado_ticket.');
                END;
            ");
        } elseif ($driver === 'mysql') {
            DB::unprepared("
                CREATE TRIGGER trigger_movimientos_before_delete
                BEFORE DELETE ON movimientos
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Los movimientos del inventario son inmutables y no pueden eliminarse.';
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER trigger_movimientos_before_update
                BEFORE UPDATE ON movimientos
                FOR EACH ROW
                BEGIN
                    IF (
                        NOT (OLD.elemento_id <=> NEW.elemento_id) OR
                        NOT (OLD.tipo_movimiento <=> NEW.tipo_movimiento) OR
                        NOT (OLD.user_id <=> NEW.user_id) OR
                        NOT (OLD.fecha <=> NEW.fecha) OR
                        NOT (OLD.ubicacion_id <=> NEW.ubicacion_id) OR
                        NOT (OLD.puesto <=> NEW.puesto) OR
                        NOT (OLD.solicitante <=> NEW.solicitante) OR
                        NOT (OLD.fecha_prestamo <=> NEW.fecha_prestamo) OR
                        NOT (OLD.correccion_de_id <=> NEW.correccion_de_id) OR
                        NOT (OLD.ticket_id <=> NEW.ticket_id) OR
                        NOT (OLD.comentario <=> NEW.comentario)
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Los movimientos del inventario son inmutables. Solo se permite actualizar fecha_devolucion y estado_ticket.';
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

        if ($driver === 'sqlite' || $driver === 'mysql') {
            DB::unprepared("DROP TRIGGER IF EXISTS trigger_movimientos_before_delete;");
            DB::unprepared("DROP TRIGGER IF EXISTS trigger_movimientos_before_update;");
        }
    }
};
