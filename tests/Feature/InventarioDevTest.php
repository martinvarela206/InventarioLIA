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
}
