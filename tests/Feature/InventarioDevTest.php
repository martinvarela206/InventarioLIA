<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Estado;
use App\Models\Ubicacion;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;

class InventarioDevTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\TipoSeeder::class);
        $this->seed(\Database\Seeders\AtributoSeeder::class);
        $this->seed(\Database\Seeders\EstadoSeeder::class);
        $this->seed(\Database\Seeders\UbicacionSeeder::class);
    }

    public function test_catalogo_tipos_posee_las_19_categorias(): void
    {
        $this->assertGreaterThanOrEqual(19, Tipo::count());
        $this->assertNotNull(Tipo::where('nombre', 'servidor')->first());
        $this->assertNotNull(Tipo::where('nombre', 'basura tecnologica')->first());
        $this->assertNotNull(Tipo::where('nombre', 'punto de acceso')->first());
    }

    public function test_catalogo_estados_posee_flag_disponible(): void
    {
        $funcionando = Estado::where('nombre', 'funcionando')->first();
        $this->assertTrue($funcionando->disponible);

        $prestado = Estado::where('nombre', 'prestado')->first();
        $this->assertFalse($prestado->disponible);

        $baja = Estado::where('nombre', 'dado de baja')->first();
        $this->assertFalse($baja->disponible);
    }

    public function test_catalogo_roles_contiene_los_5_roles_oficiales(): void
    {
        $rolesEsperados = ['user_admin', 'item_admin', 'tec', 'rev', 'registered'];
        foreach ($rolesEsperados as $r) {
            $this->assertDatabaseHas('roles', ['rol' => $r]);
        }
    }

    public function test_catalogo_ubicaciones_contiene_los_12_espacios_reales(): void
    {
        $this->assertDatabaseHas('ubicaciones', ['nombre' => 'Laboratorio Federico Sanchez']);
        $this->assertDatabaseHas('ubicaciones', ['nombre' => 'Laboratorio Paola Moyano']);
        $this->assertDatabaseHas('ubicaciones', ['nombre' => 'Rack Redes']);
        $this->assertDatabaseHas('ubicaciones', ['nombre' => 'Armario de HW']);
    }

    public function test_creacion_de_elemento_garantiza_movimiento_inicial(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $rolAdmin = Rol::where('rol', 'user_admin')->first();
        $admin->roles()->attach($rolAdmin);

        $tipo = Tipo::where('nombre', 'computadora')->first();
        $ubicacion = Ubicacion::first();
        $estado = Estado::where('nombre', 'nuevo')->first();

        $response = $this->actingAs($admin)->post(route('elementos.store'), [
            'nro_lia' => 'LIA-TEST-99',
            'nro_unsj' => '02-99999',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'modelo' => 'Inspiron',
            'cantidad' => 1,
            'ubicacion_id' => $ubicacion->id,
            'estado_id' => $estado->id,
        ]);

        $elemento = Elemento::where('nro_lia', 'LIA-TEST-99')->first();
        $this->assertNotNull($elemento);
        $this->assertDatabaseHas('movimientos', [
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'estado_id' => $estado->id,
        ]);
    }

    public function test_flujo_de_prestamo_y_devolucion_rapida(): void
    {
        $tec = Usuario::create([
            'nombre' => 'Tecnico Test',
            'email' => 'tec@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $rolTec = Rol::where('rol', 'tec')->first();
        $tec->roles()->attach($rolTec);

        $tipo = Tipo::first();
        $elemento = Elemento::create([
            'nro_lia' => 'LIA-NB-TEST',
            'tipo_id' => $tipo->id,
            'marca' => 'Lenovo',
        ]);

        $estadoPrestado = Estado::where('nombre', 'prestado')->first();
        $estadoFuncionando = Estado::where('nombre', 'funcionando')->first();
        $ubicacionDestino = Ubicacion::first();

        // 1. Registrar préstamo
        $this->actingAs($tec)->post(route('movimientos.store'), [
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'solicitante' => 'Profesor Carlos Gómez',
            'fecha_prestamo' => Carbon::now()->subDays(5)->toDateString(),
            'estado_id' => $estadoPrestado->id,
        ]);

        $this->assertTrue(Elemento::prestadosActivos()->where('id', $elemento->id)->exists());

        // 2. Registrar devolución
        $prestamo = Movimiento::where('elemento_id', $elemento->id)->latest('fecha')->first();
        $this->actingAs($tec)->post(route('movimientos.devolucion', $prestamo->id), [
            'fecha_devolucion' => Carbon::now()->toDateString(),
            'ubicacion_id' => $ubicacionDestino->id,
            'estado_id' => $estadoFuncionando->id,
            'comentario' => 'Reingresó en óptimo estado',
        ]);

        $this->assertFalse(Elemento::prestadosActivos()->where('id', $elemento->id)->exists());
    }

    public function test_modulo_de_reportes_y_export_csv(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Reportes',
            'email' => 'reports@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $tipo = Tipo::first();
        Elemento::create([
            'nro_lia' => 'LIA-REP-01',
            'tipo_id' => $tipo->id,
            'marca' => 'HP',
        ]);

        // Hub de reportes
        $response = $this->actingAs($admin)->get(route('reportes.index'));
        $response->assertStatus(200);

        // Reporte digital completo
        $responseShow = $this->actingAs($admin)->get(route('reportes.show', 'inventario-completo'));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Listado de Inventario Completo');

        // Reportes específicos vinculados a KPI cards del dashboard
        $responseOp = $this->actingAs($admin)->get(route('reportes.show', 'operativos'));
        $responseOp->assertStatus(200);
        $responseOp->assertSee('Activos Operativos en Salas');

        $responsePrest = $this->actingAs($admin)->get(route('reportes.show', 'prestados'));
        $responsePrest->assertStatus(200);
        $responsePrest->assertSee('Equipos en Préstamo Externo');

        $responseTaller = $this->actingAs($admin)->get(route('reportes.show', 'en-taller'));
        $responseTaller->assertStatus(200);
        $responseTaller->assertSee('Equipos en Verificación Técnica y Taller');

        $responseBajas = $this->actingAs($admin)->get(route('reportes.show', 'bajas-basura'));
        $responseBajas->assertStatus(200);
        $responseBajas->assertSee('Activos Dados de Baja y Basura Tecnológica');

        // Dashboard contiene los enlaces directos a los reportes
        $responseDash = $this->actingAs($admin)->get(route('dashboard.index'));
        $responseDash->assertSee(route('reportes.show', 'inventario-completo'));
        $responseDash->assertSee(route('reportes.show', 'operativos'));
        $responseDash->assertSee(route('reportes.show', 'prestados'));
        $responseDash->assertSee(route('reportes.show', 'en-taller'));
        $responseDash->assertSee(route('reportes.show', 'bajas-basura'));

        // Export CSV
        $responseCsv = $this->actingAs($admin)->get(route('reportes.exportCsv', 'inventario-completo'));
        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_gestion_de_trayectorias_de_usuario(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Users',
            'email' => 'admin_users@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $estudiante = Usuario::create([
            'nombre' => 'Estudiante Becario',
            'email' => 'alumno@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('users.trayectoria.store', $estudiante->id), [
            'cargo' => 'Becario de Investigación LIA',
            'fecha_inicio' => '2026-03-01',
            'notas' => 'Proyecto IA aplicada',
        ]);

        $this->assertDatabaseHas('usuario_trayectorias', [
            'user_id' => $estudiante->id,
            'cargo' => 'Becario de Investigación LIA',
        ]);
    }

    public function test_inmutabilidad_de_movimientos_y_soporte_de_enmiendas(): void
    {
        $tec = Usuario::create([
            'nombre' => 'Tecnico Inmutable',
            'email' => 'tecinmutable@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::first();
        $ubicacion1 = Ubicacion::first();
        $ubicacion2 = Ubicacion::skip(1)->first();
        $estado = Estado::first();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-INMUTABLE-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        $movimientoOriginal = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion1->id,
            'estado_id' => $estado->id,
            'user_id' => $tec->id,
            'fecha' => now(),
            'comentario' => 'Movimiento con error de tipeo en puesto',
        ]);

        // 1. Probar que intentar mutar el registro original lanza excepción
        try {
            $movimientoOriginal->update(['comentario' => 'Intento de modificar historial']);
            $this->fail('Se esperaba una LogicException ante intento de mutar un movimiento existente.');
        } catch (\LogicException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }

        // 2. Registrar enmienda oficial
        $response = $this->actingAs($tec)->post(route('movimientos.correccion', $movimientoOriginal->id), [
            'motivo_correccion' => 'Se corrigió la sala y puesto real del activo',
            'ubicacion_id' => $ubicacion2->id,
            'puesto' => 'Mesa 08',
            'estado_id' => $estado->id,
            'comentario' => 'Enmienda auditada',
        ]);

        $response->assertRedirect(route('elementos.show', $elemento->id));

        $this->assertDatabaseHas('movimientos', [
            'elemento_id' => $elemento->id,
            'correccion_de_id' => $movimientoOriginal->id,
            'ubicacion_id' => $ubicacion2->id,
            'puesto' => 'Mesa 08',
        ]);
    }

    public function test_perfil_usuario_restringe_modificacion_de_email_a_no_administradores(): void
    {
        $usuarioComun = Usuario::create([
            'nombre' => 'Docente Comun',
            'email' => 'docente@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $usuarioComun->roles()->attach(Rol::where('rol', 'registered')->first());

        // El usuario comun intenta cambiar su propio email en /profile
        $this->actingAs($usuarioComun)->put(route('profile.update'), [
            'nombre' => 'Docente Modificado',
            'email' => 'hacked@test.com',
            'carrera' => 'Computacion',
        ]);

        $usuarioComun->refresh();
        $this->assertEquals('Docente Modificado', $usuarioComun->nombre);
        $this->assertEquals('Computacion', $usuarioComun->carrera);
        // El email debe permanecer inalterado
        $this->assertEquals('docente@test.com', $usuarioComun->email);
    }

    public function test_login_y_render_dashboard_ultra_rapido(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Speed',
            'email' => 'admin_speed@test.com',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $t0 = microtime(true);
        $response = $this->actingAs($admin)->get(route('dashboard.index'));
        $elapsed = microtime(true) - $t0;

        $response->assertStatus(200);
        $response->assertSee('Préstamos Externos Activos');
        $this->assertLessThan(2.0, $elapsed, "El dashboard debe cargar en menos de 2 segundos, tardó {$elapsed}s");
    }

    public function test_administrador_puede_gestionar_tipos_y_asociar_atributos(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Tipos',
            'email' => 'admin_tipos@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'item_admin')->first());

        $atributoExistente = \App\Models\Atributo::where('nombre', 'Procesador')->first();
        $this->assertNotNull($atributoExistente);

        // Crear nuevo tipo con atributo existente y dos nuevos al vuelo en simultáneo
        $response = $this->actingAs($admin)->post(route('tipos.store'), [
            'nombre' => 'Instrumento de Medicion',
            'categoria' => 'Laboratorio',
            'descripcion' => 'Aparatos de medicion de precision',
            'atributos' => [$atributoExistente->id],
            'nuevos_atributos_nombre' => ['Tasa de Muestreo', 'Impedancia de Entrada'],
            'nuevos_atributos_unidad' => ['MS/s', 'Ohms'],
            'nuevos_atributos_tipo' => ['texto', 'numero'],
        ]);

        $response->assertRedirect(route('tipos.index'));
        $this->assertDatabaseHas('tipos', ['nombre' => 'instrumento de medicion']);
        $this->assertDatabaseHas('atributos', ['nombre' => 'Tasa de Muestreo']);
        $this->assertDatabaseHas('atributos', ['nombre' => 'Impedancia de Entrada']);

        $tipoCreado = Tipo::where('nombre', 'instrumento de medicion')->first();
        $this->assertCount(3, $tipoCreado->atributos);

        // Verificar que item_admin ve el enlace a Tipos en navbar
        $responseNav = $this->actingAs($admin)->get(route('dashboard.index'));
        $responseNav->assertSee(route('tipos.index'));

        // Verificar que user_admin NO tiene acceso a tipos (403 Forbidden) y no ve el botón en navbar
        $userAdmin = Usuario::create([
            'nombre' => 'User Admin Only',
            'email' => 'user_admin_only@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $forbiddenIndex = $this->actingAs($userAdmin)->get(route('tipos.index'));
        $forbiddenIndex->assertStatus(403);

        $forbiddenPost = $this->actingAs($userAdmin)->post(route('tipos.store'), [
            'nombre' => 'Intento No Autorizado',
        ]);
        $forbiddenPost->assertStatus(403);

        $responseNavUserAdmin = $this->actingAs($userAdmin)->get(route('dashboard.index'));
        $responseNavUserAdmin->assertDontSee(route('tipos.index'));
    }

    public function test_creacion_de_elemento_persiste_atributos_en_tabla_pivot(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Activos',
            'email' => 'admin_activos@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'item_admin')->first());

        $tipo = Tipo::where('nombre', 'computadora')->first();
        $ubicacion = Ubicacion::first();
        $estado = Estado::where('nombre', 'nuevo')->first();
        $attrCpu = \App\Models\Atributo::where('nombre', 'Procesador')->first();
        $attrRam = \App\Models\Atributo::where('nombre', 'RAM')->first();

        $response = $this->actingAs($admin)->post(route('elementos.store'), [
            'nro_lia' => '5001',
            'nro_unsj' => 'UNSJ-5001',
            'tipo_id' => $tipo->id,
            'ubicacion_id' => $ubicacion->id,
            'estado_id' => $estado->id,
            'modelo' => 'Dell Precision T3600',
            'cantidad' => 1,
            'atributos' => [
                $attrCpu->id => 'Intel Xeon E5-1620',
                $attrRam->id => '32GB DDR3',
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $elemento = Elemento::where('nro_lia', '5001')->first();
        $this->assertNotNull($elemento);

        // Verificar almacenamiento en tabla pivot
        $this->assertDatabaseHas('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrCpu->id,
            'valor' => 'Intel Xeon E5-1620',
        ]);
        $this->assertDatabaseHas('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrRam->id,
            'valor' => '32GB DDR3',
        ]);

        // Verificar que el accessor dinámico genera la descripción
        $this->assertStringContainsString('Intel Xeon E5-1620', $elemento->descripcion_texto);
        $this->assertStringContainsString('32GB DDR3', $elemento->descripcion_texto);
    }

    public function test_reporte_historial_reparacion_y_trazabilidad_resuelve_por_nro_lia_y_unsj(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Reportes',
            'email' => 'admin_rep@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'rev')->first());

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();
        $estado = Estado::first();

        $el = Elemento::create([
            'nro_lia' => 7777,
            'nro_unsj' => 'UNSJ-7777',
            'tipo_id' => $tipo->id,
            'ubicacion_id' => $ubicacion->id,
            'estado_id' => $estado->id,
            'modelo' => 'Servidor ProLiant',
        ]);

        // Consulta de trazabilidad histórica pasando nro_lia
        $response = $this->actingAs($admin)->get(route('reportes.show', [
            'tipo' => 'trazabilidad-historica',
            'nro_lia' => 7777,
            'nro_unsj' => 'UNSJ-7777',
        ]));

        $response->assertStatus(200);
        $response->assertSee('UNSJ-7777');
        $response->assertSee('Servidor ProLiant');

        // Consulta de historial de reparación pasando nro_lia
        $responseRep = $this->actingAs($admin)->get(route('reportes.show', [
            'tipo' => 'historial-reparaciones',
            'nro_lia' => 7777,
        ]));

        $responseRep->assertStatus(200);
        $responseRep->assertSee('7777');
    }
}

