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

        // 1. Crear tabla de catálogos de ámbitos para tickets generales si no existe
        if (! Schema::hasTable('ambitos_tickets')) {
            Schema::create('ambitos_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100)->unique();
                $table->string('descripcion', 255)->nullable();
                $table->integer('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index('activo');
                $table->index('orden');
            });

            // Sembrar ámbitos iniciales institucionales
            DB::table('ambitos_tickets')->insert([
                ['nombre' => 'Laboratorio A', 'descripcion' => 'Instalaciones, puestos y equipamiento general del Laboratorio A', 'orden' => 1, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Laboratorio B', 'descripcion' => 'Instalaciones, puestos y equipamiento general del Laboratorio B', 'orden' => 2, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'LIA General (Infraestructura / Servicios)', 'descripcion' => 'Cuestiones edilicias, climatización, energía o servicios comunes del LIA', 'orden' => 3, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Redes y Conectividad', 'descripcion' => 'Cableado estructurado, racks, switches, Wi-Fi o enlaces del laboratorio', 'orden' => 4, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Mobiliario y Espacios Físicos', 'descripcion' => 'Bancos, sillas, armarios, pizarras o puestos de trabajo', 'orden' => 5, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Otros', 'descripcion' => 'Incidencias generales no categorizadas en los ámbitos anteriores', 'orden' => 6, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. En SQLite, soltar temporalmente los triggers que referencian a movimientos para permitir la reconstrucción de la tabla
        if ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_movimientos_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_movimientos_before_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_usuarios_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_ubicaciones_before_delete;');
        }

        // 3. Modificar tabla movimientos para permitir elemento_id nullable y agregar ámbito_ticket_id
        Schema::table('movimientos', function (Blueprint $table) {
            $table->unsignedBigInteger('elemento_id')->nullable()->change();
            if (! Schema::hasColumn('movimientos', 'ambito_ticket_id')) {
                $table->foreignId('ambito_ticket_id')->nullable()->after('estado_ticket')->constrained('ambitos_tickets')->nullOnDelete();
            }
        });

        // 4. Restaurar y actualizar triggers con soporte de inmutabilidad para ambito_ticket_id
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
                    OLD.fecha_prestamo IS NOT NEW.fecha_prestamo OR
                    OLD.correccion_de_id IS NOT NEW.correccion_de_id OR
                    OLD.ticket_id IS NOT NEW.ticket_id OR
                    OLD.ambito_ticket_id IS NOT NEW.ambito_ticket_id OR
                    OLD.comentario IS NOT NEW.comentario OR
                    (OLD.fecha_devolucion IS NOT NULL AND OLD.fecha_devolucion IS NOT NEW.fecha_devolucion) OR
                    (OLD.solicitante IS NOT NEW.solicitante AND (NEW.solicitante IS NULL OR NEW.solicitante NOT LIKE 'Ex-colaborador anonimizado%'))
                )
                BEGIN
                    SELECT RAISE(ABORT, 'Los movimientos del inventario son inmutables patrimonialmente.');
                END;
            ");

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
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_movimientos_before_update;');
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
                        NOT (OLD.fecha_prestamo <=> NEW.fecha_prestamo) OR
                        NOT (OLD.correccion_de_id <=> NEW.correccion_de_id) OR
                        NOT (OLD.ticket_id <=> NEW.ticket_id) OR
                        NOT (OLD.ambito_ticket_id <=> NEW.ambito_ticket_id) OR
                        NOT (OLD.comentario <=> NEW.comentario) OR
                        (OLD.fecha_devolucion IS NOT NULL AND NOT (OLD.fecha_devolucion <=> NEW.fecha_devolucion)) OR
                        (NOT (OLD.solicitante <=> NEW.solicitante) AND NOT (COALESCE(NEW.solicitante, '') LIKE 'Ex-colaborador anonimizado%'))
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Los movimientos del inventario son inmutables patrimonialmente.';
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

        if ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_movimientos_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_movimientos_before_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_usuarios_before_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trigger_ubicaciones_before_delete;');
        }

        Schema::table('movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('movimientos', 'ambito_ticket_id')) {
                $table->dropForeign(['ambito_ticket_id']);
                $table->dropColumn('ambito_ticket_id');
            }
            $table->unsignedBigInteger('elemento_id')->nullable(false)->change();
        });

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
                    OLD.fecha_prestamo IS NOT NEW.fecha_prestamo OR
                    OLD.correccion_de_id IS NOT NEW.correccion_de_id OR
                    OLD.ticket_id IS NOT NEW.ticket_id OR
                    OLD.comentario IS NOT NEW.comentario OR
                    (OLD.fecha_devolucion IS NOT NULL AND OLD.fecha_devolucion IS NOT NEW.fecha_devolucion) OR
                    (OLD.solicitante IS NOT NEW.solicitante AND (NEW.solicitante IS NULL OR NEW.solicitante NOT LIKE 'Ex-colaborador anonimizado%'))
                )
                BEGIN
                    SELECT RAISE(ABORT, 'Los movimientos del inventario son inmutables patrimonialmente.');
                END;
            ");

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
        }

        Schema::dropIfExists('ambitos_tickets');
    }
};
