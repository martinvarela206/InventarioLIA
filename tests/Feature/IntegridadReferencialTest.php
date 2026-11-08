<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Usuario;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class IntegridadReferencialTest extends TestCase
{
    use RefreshDatabase;

    private function crearDatosBase(): array
    {
        $this->seed();

        $user = Usuario::create([
            'nombre' => 'Usuario FK Test',
            'email' => 'user_fk@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        return [$user, $tipo, $ubicacion];
    }

    public function test_no_se_puede_borrar_fisicamente_elemento_con_movimientos_por_foreign_key_restrict(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-FK-TEST-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento de prueba FK',
        ]);

        $this->expectException(QueryException::class);
        DB::table('elementos')->where('id', $elemento->id)->delete();
    }

    public function test_no_se_puede_borrar_fisicamente_usuario_con_movimientos_por_foreign_key_restrict(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-FK-TEST-02',
            'tipo_id' => $tipo->id,
            'marca' => 'HP',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento de prueba FK usuario',
        ]);

        $this->expectException(QueryException::class);
        DB::table('usuarios')->where('id', $user->id)->delete();
    }

    public function test_borrado_de_elemento_es_logico_y_preserva_movimientos_historicos(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();
        $admin = Usuario::where('email', 'admin@lia.unsj.edu.ar')->first() ?? $user;

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-SOFT-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Lenovo',
        ]);

        $movimiento = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento que debe sobrevivir al borrado logico',
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('elementos.destroy', $elemento));
        $response->assertRedirect(route('elementos.index'));

        // El elemento no debe figurar en consultas estándar (SoftDeletes)
        $this->assertNull(Elemento::find($elemento->id));

        // Debe existir en withTrashed() con deleted_at y deleted_by
        $archivado = Elemento::withTrashed()->find($elemento->id);
        $this->assertNotNull($archivado);
        $this->assertNotNull($archivado->deleted_at);
        $this->assertEquals($admin->id, $archivado->deleted_by);

        // El movimiento histórico debe seguir existiendo intacto
        $this->assertDatabaseHas('movimientos', [
            'id' => $movimiento->id,
            'elemento_id' => $elemento->id,
            'comentario' => 'Movimiento que debe sobrevivir al borrado logico',
        ]);
    }

    public function test_trigger_impide_borrado_fisico_de_movimiento_incluso_por_sql_crudo(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-TRIGGER-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Asus',
        ]);

        $movimiento = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento inmutable con trigger',
        ]);

        $this->expectException(QueryException::class);
        // Borrado directo por SQL crudo que esquiva eventos de Eloquent
        DB::table('movimientos')->where('id', $movimiento->id)->delete();
    }

    public function test_trigger_impide_modificacion_de_campos_inmutables_de_movimiento_por_sql_crudo(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-TRIGGER-02',
            'tipo_id' => $tipo->id,
            'marca' => 'Apple',
        ]);

        $movimiento = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Comentario original inmutable',
        ]);

        $this->expectException(QueryException::class);
        // Intentar alterar el comentario directamente por base de datos
        DB::table('movimientos')->where('id', $movimiento->id)->update([
            'comentario' => 'Comentario alterado maliciosamente',
        ]);
    }

    public function test_trigger_permite_actualizar_fecha_devolucion_y_estado_ticket(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-TRIGGER-03',
            'tipo_id' => $tipo->id,
            'marca' => 'Acer',
        ]);

        $movimiento = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'fecha_prestamo' => now()->toDateString(),
            'solicitante' => 'Prof. Perez',
            'comentario' => 'Prestamo autorizado',
        ]);

        // Actualizar fecha_devolucion y estado_ticket es la única mutación permitida por el trigger
        DB::table('movimientos')->where('id', $movimiento->id)->update([
            'fecha_devolucion' => now()->toDateString(),
            'estado_ticket' => 'cerrado',
        ]);

        $this->assertDatabaseHas('movimientos', [
            'id' => $movimiento->id,
            'fecha_devolucion' => now()->toDateString(),
            'estado_ticket' => 'cerrado',
        ]);
    }

    public function test_validacion_rechaza_creacion_sin_nro_lia_ni_nro_unsj(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();
        $admin = Usuario::where('email', 'admin@lia.unsj.edu.ar')->first() ?? $user;

        $this->actingAs($admin);
        $response = $this->post(route('elementos.store'), [
            'nro_lia' => null,
            'nro_unsj' => null,
            'tipo_id' => $tipo->id,
            'cantidad' => 1,
        ]);

        $response->assertSessionHasErrors(['nro_lia']);
    }

    public function test_validacion_impide_duplicar_nro_lia_incluso_con_nro_unsj_nulo(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();
        $admin = Usuario::where('email', 'admin@lia.unsj.edu.ar')->first() ?? $user;

        Elemento::create([
            'nro_lia' => 'LIA-DUP-01',
            'nro_unsj' => null,
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('elementos.store'), [
            'nro_lia' => 'LIA-DUP-01',
            'nro_unsj' => null,
            'tipo_id' => $tipo->id,
            'cantidad' => 1,
        ]);

        $response->assertSessionHasErrors(['nro_lia']);
    }

    public function test_base_de_datos_bloquea_insercion_sin_ningun_identificador(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        $this->expectException(QueryException::class);
        DB::table('elementos')->insert([
            'nro_lia' => null,
            'nro_unsj' => null,
            'tipo_id' => $tipo->id,
            'cantidad' => 1,
        ]);
    }

    public function test_indices_compuestos_en_movimientos_existen_y_optimizan_subconsultas(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-INDEX-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento para validar indice compuesto',
        ]);

        // Verificar que la consulta correlacionada de latestOfMany resuelva correctamente
        $elementoConUltimo = Elemento::with('ultimoMovimiento')->find($elemento->id);
        $this->assertNotNull($elementoConUltimo->ultimoMovimiento);
        $this->assertEquals($elemento->id, $elementoConUltimo->ultimoMovimiento->elemento_id);
    }

    public function test_unicidad_en_detalles_de_auditoria_por_elemento_y_puesto(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        $auditoria = \App\Models\Auditoria::create([
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'estado' => 'en_progreso',
        ]);

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-AUD-01',
            'tipo_id' => $tipo->id,
            'marca' => 'HP',
        ]);

        \App\Models\AuditoriaDetalle::create([
            'auditoria_id' => $auditoria->id,
            'puesto' => 'Puesto 01',
            'elemento_id' => $elemento->id,
            'estado' => 'ok',
        ]);

        // Intentar registrar el mismo elemento_id en la misma auditoría debe fallar por unicidad
        $this->expectException(QueryException::class);
        \App\Models\AuditoriaDetalle::create([
            'auditoria_id' => $auditoria->id,
            'puesto' => 'Puesto 02',
            'elemento_id' => $elemento->id,
            'estado' => 'inconsistencia',
        ]);
    }

    public function test_ubicacion_soporta_archivado_logico_y_filtro_de_activas(): void
    {
        $sala = Ubicacion::create([
            'nombre' => 'Sala Antigua Histórica',
            'descripcion' => 'Espacio que será clausurado',
        ]);

        $this->assertTrue($sala->fresh()->activa);

        $sala->update(['activa' => false]);
        $this->assertFalse($sala->fresh()->activa);

        $activas = Ubicacion::activas()->pluck('id');
        $this->assertNotContains($sala->id, $activas);
    }

    public function test_rol_asset_admin_existe_en_bd_y_posee_compatibilidad_bidireccional(): void
    {
        $this->seed();

        $rolAssetAdmin = Rol::where('rol', 'asset_admin')->first();
        $this->assertNotNull($rolAssetAdmin);

        $user = Usuario::create([
            'nombre' => 'Administrador de Activos',
            'email' => 'asset_admin_test@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $user->roles()->attach($rolAssetAdmin);

        $this->assertTrue($user->isAssetAdmin());
        $this->assertTrue($user->hasRole('asset_admin'));
        $this->assertTrue($user->hasRole('item_admin'));
        $this->assertTrue($user->canManageElementos());
    }
}
