<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Atributo;
use App\Models\ReporteConfigurado;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InventarioDevTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\TipoSeeder::class);
        $this->seed(\Database\Seeders\AtributoSeeder::class);
        $this->seed(\Database\Seeders\UbicacionSeeder::class);
        $this->seed(\Database\Seeders\AppAdminSeeder::class);
    }

    public function test_catalogo_tipos_posee_las_19_categorias(): void
    {
        $this->assertGreaterThanOrEqual(19, Tipo::count());
        $this->assertNotNull(Tipo::where('nombre', 'servidor')->first());
        $this->assertNotNull(Tipo::where('nombre', 'basura tecnologica')->first());
        $this->assertNotNull(Tipo::where('nombre', 'punto de acceso')->first());
    }

    public function test_catalogo_roles_contiene_los_roles_oficiales(): void
    {
        $rolesEsperados = ['user_admin', 'app_admin', 'item_admin', 'tec', 'subtec', 'rev', 'registered'];
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
        $rolAdmin = Rol::where('rol', 'item_admin')->first();
        $admin->roles()->attach($rolAdmin);

        $tipo = Tipo::where('nombre', 'computadora')->first();
        $ubicacion = Ubicacion::first();

        $response = $this->actingAs($admin)->post(route('elementos.store'), [
            'nro_lia' => 'LIA-TEST-99',
            'nro_unsj' => '02-99999',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'modelo' => 'Inspiron',
            'cantidad' => 1,
            'ubicacion_id' => $ubicacion->id,
        ]);

        $elemento = Elemento::where('nro_lia', 'LIA-TEST-99')->first();
        $this->assertNotNull($elemento);
        $this->assertDatabaseHas('movimientos', [
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
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

        $ubicacionDestino = Ubicacion::first();

        // 1. Registrar préstamo
        $this->actingAs($tec)->post(route('movimientos.store'), [
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'solicitante' => 'Profesor Carlos Gómez',
            'fecha_prestamo' => Carbon::now()->subDays(5)->toDateString(),
        ]);

        $this->assertTrue(Elemento::prestadosActivos()->where('id', $elemento->id)->exists());

        // 2. Registrar devolución
        $prestamo = Movimiento::where('elemento_id', $elemento->id)->latest('fecha')->first();
        $this->actingAs($tec)->post(route('movimientos.devolucion', $prestamo->id), [
            'fecha_devolucion' => Carbon::now()->toDateString(),
            'ubicacion_id' => $ubicacionDestino->id,
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
        $admin->roles()->attach(Rol::where('rol', 'rev')->first());

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
        $responseShow->assertSee('Listado completo del Inventario');

        // Reportes específicos vinculados a KPI cards del dashboard
        $responseOp = $this->actingAs($admin)->get(route('reportes.show', 'operativos'));
        $responseOp->assertStatus(200);
        $responseOp->assertSee('Activos Operativos en Salas');

        $responsePrest = $this->actingAs($admin)->get(route('reportes.show', 'prestados'));
        $responsePrest->assertStatus(200);
        $responsePrest->assertSee('Historial de Préstamos');

        $responseBajas = $this->actingAs($admin)->get(route('reportes.show', 'bajas-basura'));
        $responseBajas->assertStatus(200);
        $responseBajas->assertSee('Activos Dados de Baja y Basura Tecnológica');

        // Dashboard contiene los enlaces directos a los reportes
        $responseDash = $this->actingAs($admin)->get(route('dashboard.index'));
        $responseDash->assertSee(route('reportes.show', 'inventario-completo'));
        $responseDash->assertSee(route('reportes.show', 'operativos'));
        $responseDash->assertSee(route('reportes.show', 'prestados'));
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

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-INMUTABLE-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        $movimientoOriginal = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion1->id,
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
        $attrCpu = \App\Models\Atributo::where('nombre', 'Procesador')->first();
        $attrRam = \App\Models\Atributo::where('nombre', 'RAM')->first();

        $response = $this->actingAs($admin)->post(route('elementos.store'), [
            'nro_lia' => '5001',
            'nro_unsj' => 'UNSJ-5001',
            'tipo_id' => $tipo->id,
            'ubicacion_id' => $ubicacion->id,
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

        $el = Elemento::create([
            'nro_lia' => 7777,
            'nro_unsj' => 'UNSJ-7777',
            'tipo_id' => $tipo->id,
            'ubicacion_id' => $ubicacion->id,
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

    public function test_revisor_no_puede_hacer_movimientos_ni_ve_botones_de_movimiento_rapido(): void
    {
        $revisor = Usuario::create([
            'nombre' => 'Auditor Revisor',
            'email' => 'auditor_rev@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $revisor->roles()->attach(Rol::where('rol', 'rev')->first());

        $tipo = Tipo::first();
        $el = Elemento::create([
            'nro_lia' => 'LIA-REV-TEST',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        // 1. En inventario: no ve el botón de Movimiento Rápido
        $responseIndex = $this->actingAs($revisor)->get(route('elementos.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertDontSee(route('movimientos.rapido'));

        // 2. En navbar y dashboard: no ve el botón de Movimiento Rápido
        $responseDash = $this->actingAs($revisor)->get(route('dashboard.index'));
        $responseDash->assertStatus(200);
        $responseDash->assertDontSee(route('movimientos.rapido'));

        // 3. En la ficha de detalle de activo: no ve el botón de Registrar Movimiento
        $responseShow = $this->actingAs($revisor)->get(route('elementos.show', $el->id));
        $responseShow->assertStatus(200);
        $responseShow->assertDontSee(route('movimientos.rapido', ['elemento_id' => $el->id]));

        // 4. Intento de acceso directo por URL a /movimientos/rapido: 403 Forbidden
        $forbiddenGet = $this->actingAs($revisor)->get(route('movimientos.rapido'));
        $forbiddenGet->assertStatus(403);

        // 5. Intento de POST directo a /movimientos: 403 Forbidden
        $forbiddenPost = $this->actingAs($revisor)->post(route('movimientos.store'), [
            'elemento_id' => $el->id,
            'tipo_movimiento' => 'interno',
        ]);
        $forbiddenPost->assertStatus(403);

        // 6. Verificar que un técnico (tec) SÍ tiene acceso a movimientos.rapido
        $tecnico = Usuario::create([
            'nombre' => 'Tecnico Habilitado',
            'email' => 'tec_hab@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $tecnico->roles()->attach(Rol::where('rol', 'tec')->first());

        $allowedGet = $this->actingAs($tecnico)->get(route('movimientos.rapido'));
        $allowedGet->assertStatus(200);
    }

    public function test_usuario_con_rol_registered_es_redirigido_a_bienvenida_y_no_ve_dashboard_ni_modulos(): void
    {
        $usuarioRegistrado = Usuario::create([
            'nombre' => 'Estudiante Pendiente',
            'email' => 'estudiante_pendiente@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $usuarioRegistrado->roles()->attach(Rol::where('rol', 'registered')->first());

        // 1. Intento de acceder al Dashboard: Redirigido a /bienvenida
        $responseDash = $this->actingAs($usuarioRegistrado)->get(route('dashboard.index'));
        $responseDash->assertRedirect(route('bienvenida'));

        // 2. Pantalla de Bienvenida contiene mensaje institucional y estado
        $responseBienvenida = $this->actingAs($usuarioRegistrado)->get(route('bienvenida'));
        $responseBienvenida->assertStatus(200);
        $responseBienvenida->assertSee('¡Bienvenido a Nexo LIA!');
        $responseBienvenida->assertSee('Cuenta Registrada: Esperando Asignación de Permisos');
        $responseBienvenida->assertSee('Estudiante Pendiente');

        // 3. Navbar NO muestra accesos a módulos operativos
        $responseBienvenida->assertDontSee(route('dashboard.index'));
        $responseBienvenida->assertDontSee(route('elementos.index'));
        $responseBienvenida->assertDontSee(route('reportes.index'));
        $responseBienvenida->assertDontSee(route('tipos.index'));

        // 4. Intento de acceder a Inventario o Reportes: Redirigido a /bienvenida
        $responseElementos = $this->actingAs($usuarioRegistrado)->get(route('elementos.index'));
        $responseElementos->assertRedirect(route('bienvenida'));

        $responseReportes = $this->actingAs($usuarioRegistrado)->get(route('reportes.index'));
        $responseReportes->assertRedirect(route('bienvenida'));

        // 5. Una vez que el administrador le asigna un rol operativo (ej: rev), puede acceder al dashboard
        $usuarioRegistrado->roles()->attach(Rol::where('rol', 'rev')->first());
        $usuarioRegistrado->refresh();

        $responseHabilitado = $this->actingAs($usuarioRegistrado)->get(route('dashboard.index'));
        $responseHabilitado->assertStatus(200);
        $responseHabilitado->assertSee('Dashboard');
    }

    public function test_landing_page_posee_boton_registro_y_permite_registro_de_nuevos_usuarios(): void
    {
        // 1. Visitante no autenticado en la landing page
        $responseLanding = $this->get('/');
        $responseLanding->assertStatus(200);
        $responseLanding->assertSee(route('register'));
        $responseLanding->assertSee('Registrarse');
        $responseLanding->assertSee('Iniciar Sesión');

        // 2. Acceder al formulario de registro y verificar cláusula legal Ley 25.326
        $responseForm = $this->get(route('register'));
        $responseForm->assertStatus(200);
        $responseForm->assertSee('Registro de Usuario');
        $responseForm->assertSee('Ley 25.326');
        $responseForm->assertSee('6 años');

        // 3. Intento sin consentimiento legal debe ser rechazado
        $responseRechazo = $this->post(route('register'), [
            'nombre' => 'Estudiante Sin Consentimiento',
            'email' => 'sin_consentimiento@exactas.unsj.edu.ar',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $responseRechazo->assertSessionHasErrors('terminos_consentimiento');

        // 4. Completar el registro con consentimiento explícito
        $responsePost = $this->post(route('register'), [
            'nombre' => 'Nuevo Estudiante Test',
            'email' => 'nuevo_estudiante@exactas.unsj.edu.ar',
            'dni' => '40999888',
            'carrera' => 'Licenciatura en Ciencias de la Computación',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terminos_consentimiento' => '1',
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'nuevo_estudiante@exactas.unsj.edu.ar',
            'nombre' => 'Nuevo Estudiante Test',
            'activo' => false,
        ]);

        $nuevoUsuario = Usuario::where('email', 'nuevo_estudiante@exactas.unsj.edu.ar')->first();
        $this->assertNotNull($nuevoUsuario);
        $this->assertTrue($nuevoUsuario->hasRole('registered'));
    }

    public function test_app_admin_puede_gestionar_carreras_y_preservar_historial_inmutable(): void
    {
        $appAdmin = Usuario::create([
            'nombre' => 'Admin App Test',
            'email' => 'appadmin@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $appAdmin->roles()->attach(Rol::where('rol', 'app_admin')->first());

        // 1. Acceder al módulo de carreras
        $response = $this->actingAs($appAdmin)->get(route('app_admin.carreras'));
        $response->assertStatus(200);
        $response->assertSee('Carreras para Registro de Usuarios');

        // 2. Crear una nueva carrera
        $responseStore = $this->actingAs($appAdmin)->post(route('app_admin.carreras.store'), [
            'nombre' => 'Ingeniería en Inteligencia Artificial',
            'codigo' => 'IIA',
            'activa' => 1,
        ]);
        $responseStore->assertRedirect(route('app_admin.carreras'));
        $this->assertDatabaseHas('carreras', ['nombre' => 'Ingeniería en Inteligencia Artificial', 'activa' => true]);

        $carrera = \App\Models\Carrera::where('nombre', 'Ingeniería en Inteligencia Artificial')->first();

        // 3. Registrar un usuario asociado a dicha carrera
        $alumno = Usuario::create([
            'nombre' => 'Alumno IA',
            'email' => 'alumno_ia@test.com',
            'password' => bcrypt('password'),
            'carrera_id' => $carrera->id,
            'carrera' => $carrera->nombre,
            'activo' => true,
        ]);

        // 4. Desactivar / Archivar la carrera (por ejemplo, deja de dictarse)
        $responseToggle = $this->actingAs($appAdmin)->post(route('app_admin.carreras.toggle', $carrera->id));
        $responseToggle->assertRedirect(route('app_admin.carreras'));
        $carrera->refresh();
        $this->assertFalse($carrera->activa);

        // 5. La carrera NO debe figurar en el formulario de nuevos registros
        $responseRegister = $this->get(route('register'));
        $responseRegister->assertDontSee('Ingeniería en Inteligencia Artificial (IIA)');

        // 6. Pero el alumno PRESERVA de forma inmutable su carrera histórica
        $alumno->refresh();
        $this->assertEquals($carrera->id, $alumno->carrera_id);
        $this->assertEquals('Ingeniería en Inteligencia Artificial', $alumno->carrera_nombre);

        // 7. Si se intenta eliminar físicamente, el sistema la desactiva y protege la asociación
        $responseDestroy = $this->actingAs($appAdmin)->delete(route('app_admin.carreras.destroy', $carrera->id));
        $this->assertDatabaseHas('carreras', ['id' => $carrera->id, 'activa' => false]);
    }

    public function test_app_admin_puede_publicar_actividades_tipo_instagram_en_el_feed(): void
    {
        $appAdmin = Usuario::create([
            'nombre' => 'Admin Feed Test',
            'email' => 'feedadmin@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $appAdmin->roles()->attach(Rol::where('rol', 'app_admin')->first());

        // 1. Publicar una actividad con mensaje y título
        $responsePost = $this->actingAs($appAdmin)->post(route('app_admin.actividades.store'), [
            'titulo' => 'Concurso de Programación Competitiva LIA',
            'contenido' => 'Inscripciones abiertas para estudiantes de computación y sistemas.',
            'imagen_url' => '/assets/notebook-wide.png',
        ]);

        $responsePost->assertRedirect(route('app_admin.actividades'));
        $this->assertDatabaseHas('actividades_lia', [
            'titulo' => 'Concurso de Programación Competitiva LIA',
        ]);

        // 2. Verificar que se renderiza en la portada pública
        $responseWelcome = $this->get('/');
        $responseWelcome->assertStatus(200);
        $responseWelcome->assertSee('Concurso de Programación Competitiva LIA');
        $responseWelcome->assertSee('Lo que Ofrece el LIA');
        $responseWelcome->assertSee('Gestión de Tickets');
        $responseWelcome->assertDontSee('Próximamente');
    }

    public function test_usuario_sin_rol_app_admin_es_bloqueado_de_la_configuracion(): void
    {
        $revisor = Usuario::create([
            'nombre' => 'Revisor Comun',
            'email' => 'revisor_comun@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $revisor->roles()->attach(Rol::where('rol', 'rev')->first());

        $response = $this->actingAs($revisor)->get(route('app_admin.index'));
        $response->assertStatus(403);

        $responseCarreras = $this->actingAs($revisor)->get(route('app_admin.carreras'));
        $responseCarreras->assertStatus(403);
    }

    public function test_gestion_de_usuarios_muestra_y_permite_asignar_rol_app_admin(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Usuarios Test',
            'email' => 'admin_users@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $docente = Usuario::create([
            'nombre' => 'Docente A Designar',
            'email' => 'docente_designar@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $docente->roles()->attach(Rol::where('rol', 'registered')->first());

        // 1. Acceder al listado de usuarios y verificar presencia del rol app_admin
        $responseList = $this->actingAs($admin)->get(route('users.index'));
        $responseList->assertStatus(200);
        $responseList->assertSee('Usuarios');
        $responseList->assertSee('app_admin');

        // 2. Asignar rol app_admin al docente mediante toggleRole
        $responseToggle = $this->actingAs($admin)->postJson(route('users.toggleRole', $docente->id), [
            'role' => 'app_admin',
        ]);
        $responseToggle->assertStatus(200);
        $responseToggle->assertJson(['status' => 'assigned']);

        $this->assertTrue($docente->fresh()->hasRole('app_admin'));
        $this->assertTrue($docente->fresh()->isAppAdmin());

        // 3. Verificar que ahora el docente puede acceder a la gestión de la app y landing
        $responseApp = $this->actingAs($docente->fresh())->get(route('app_admin.index'));
        $responseApp->assertStatus(200);
        $responseApp->assertSee('Gestión de la Aplicación');
    }

    public function test_interseccion_cero_de_permisos_y_reglas_estrictas_de_roles(): void
    {
        // CASO 1: Usuario con user_admin + item_admin pero SIN app_admin
        // NO debe poder acceder a la gestión de la app
        $userAdminItem = Usuario::create([
            'nombre' => 'User e Item Admin',
            'email' => 'user_item_admin@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $userAdminItem->roles()->attach([
            Rol::where('rol', 'user_admin')->first()->id,
            Rol::where('rol', 'item_admin')->first()->id,
            Rol::where('rol', 'registered')->first()->id,
        ]);

        $this->assertFalse($userAdminItem->isAppAdmin());
        $responseApp = $this->actingAs($userAdminItem)->get(route('app_admin.index'));
        $responseApp->assertStatus(403);

        $responseCarreras = $this->actingAs($userAdminItem)->get(route('app_admin.carreras'));
        $responseCarreras->assertStatus(403);

        // Sin embargo sí puede gestionar usuarios y activos
        $this->actingAs($userAdminItem)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($userAdminItem)->get(route('elementos.create'))->assertStatus(200);

        // Pero NO puede hacer movimientos (requiere rol tec)
        $this->actingAs($userAdminItem)->get(route('movimientos.rapido'))->assertStatus(403);

        // CASO 2: Usuario con tec pero SIN rev
        $tecnicoSolo = Usuario::create([
            'nombre' => 'Tecnico Puro',
            'email' => 'tecpuro@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $tecnicoSolo->roles()->attach([
            Rol::where('rol', 'tec')->first()->id,
            Rol::where('rol', 'registered')->first()->id,
        ]);

        // Puede hacer movimientos
        $this->actingAs($tecnicoSolo)->get(route('movimientos.rapido'))->assertStatus(200);

        // NO puede ver reportes (requiere rol rev)
        $this->actingAs($tecnicoSolo)->get(route('reportes.index'))->assertStatus(403);

        // NO puede dar de alta activos (requiere rol item_admin)
        $this->actingAs($tecnicoSolo)->get(route('elementos.create'))->assertStatus(403);

        // CASO 3: Usuario que SOLO tiene registered
        $soloRegistrado = Usuario::create([
            'nombre' => 'Solo Registrado',
            'email' => 'soloregistrado@test.com',
            'password' => bcrypt('password'),
            'activo' => false,
        ]);
        $soloRegistrado->roles()->attach(Rol::where('rol', 'registered')->first()->id);

        $this->assertTrue($soloRegistrado->isOnlyRegistered());
        $this->assertFalse($soloRegistrado->hasActivePermissions());

        // Al intentar ir al dashboard, es redirigido a bienvenida
        $this->actingAs($soloRegistrado)->get(route('dashboard.index'))->assertRedirect(route('bienvenida'));

        // Puede editar sus datos personales en perfil
        $this->actingAs($soloRegistrado)->get(route('profile.edit'))->assertStatus(200);

        // CASO 4: Usuario con rol operativo que visita bienvenida es redirigido a dashboard
        $revisor = Usuario::create([
            'nombre' => 'Revisor Activo',
            'email' => 'rev_activo@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $revisor->roles()->attach([
            Rol::where('rol', 'rev')->first()->id,
            Rol::where('rol', 'registered')->first()->id,
        ]);

        $this->actingAs($revisor)->get(route('bienvenida'))->assertRedirect(route('dashboard.index'));

        // CASO 5: Usuario sin rol registered no puede editar su perfil
        $sinRegistered = Usuario::create([
            'nombre' => 'Sin Registered',
            'email' => 'sinregistered@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $sinRegistered->roles()->attach(Rol::where('rol', 'rev')->first()->id);

        $this->actingAs($sinRegistered)->get(route('profile.edit'))->assertStatus(403);
    }

    public function test_dashboard_kpi_cards_y_reportes_consistentes(): void
    {
        $revisor = Usuario::create([
            'nombre' => 'Revisor Auditor',
            'email' => 'auditor_kpi@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $revisor->roles()->attach(Rol::where('rol', 'rev')->first()->id);

        $responseDashboard = $this->actingAs($revisor)->get(route('dashboard.index'));
        $responseDashboard->assertStatus(200);

        $totalElementos = $responseDashboard->viewData('totalElementos');
        $totalOperativos = $responseDashboard->viewData('totalOperativos');
        $totalPrestados = $responseDashboard->viewData('totalPrestados');
        $totalObsoletos = $responseDashboard->viewData('totalObsoletos');

        // 1. Operativos debe coincidir con Elemento::disponibles()
        $this->assertEquals(Elemento::disponibles()->count(), $totalOperativos);

        // 2. Prestados debe coincidir con Elemento::prestadosActivos()
        $this->assertEquals(Elemento::prestadosActivos()->count(), $totalPrestados);

        // 3. Obsoletos debe coincidir con Elemento::obsoletos()
        $this->assertEquals(Elemento::obsoletos()->count(), $totalObsoletos);

        // 4. La suma de los tres estados debe igualar el 100% de los elementos
        $this->assertEquals($totalElementos, $totalOperativos + $totalPrestados + $totalObsoletos);
    }

    public function test_busqueda_rapida_de_movimientos_encuentra_codigo_lia_y_unsj(): void
    {
        $tecnico = Usuario::create([
            'nombre' => 'Tecnico Test Search',
            'email' => 'tecnico_search@test.com',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $tecnico->roles()->attach(Rol::where('rol', 'tec')->first()->id);

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-SEARCH-99',
            'nro_unsj' => '02-09999',
            'tipo_id' => $tipo->id,
            'marca' => 'Bangho Pro',
            'modelo' => 'Ultra 2026',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tecnico->id,
            'fecha' => now(),
        ]);

        // Búsqueda por Nro UNSJ
        $responseUnsj = $this->actingAs($tecnico)->getJson(route('movimientos.buscar-rapido', ['q' => '02-09999']));
        $responseUnsj->assertStatus(200);
        $dataUnsj = $responseUnsj->json();
        $this->assertNotEmpty($dataUnsj);
        $this->assertEquals($elemento->id, $dataUnsj[0]['id']);

        // Búsqueda por Nro LIA
        $responseLia = $this->actingAs($tecnico)->getJson(route('movimientos.buscar-rapido', ['q' => 'LIA-SEARCH-99']));
        $responseLia->assertStatus(200);
        $dataLia = $responseLia->json();
        $this->assertNotEmpty($dataLia);
        $this->assertEquals($elemento->id, $dataLia[0]['id']);
    }

    public function test_item_admin_puede_gestionar_salas_y_espacios_fisicos(): void
    {
        $itemAdmin = Usuario::create([
            'nombre' => 'Admin de Espacios',
            'email' => 'admin_espacios@test.com',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $itemAdmin->roles()->attach(Rol::where('rol', 'item_admin')->first()->id);

        $tecnico = Usuario::create([
            'nombre' => 'Tecnico Sin Permiso Espacios',
            'email' => 'tecnico_sin_espacios@test.com',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $tecnico->roles()->attach(Rol::where('rol', 'tec')->first()->id);

        // 1. Acceder al índice de salas como item_admin
        $responseIndex = $this->actingAs($itemAdmin)->get(route('ubicaciones.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Salas y Espacios');

        // 2. Crear una nueva sala
        $responseStore = $this->actingAs($itemAdmin)->post(route('ubicaciones.store'), [
            'nombre' => 'Laboratorio Delta IA',
            'descripcion' => 'Espacio para experimentación con modelos locales',
        ]);
        $responseStore->assertRedirect(route('ubicaciones.index'));

        $this->assertDatabaseHas('ubicaciones', [
            'nombre' => 'Laboratorio Delta IA',
        ]);

        $nuevaSala = Ubicacion::where('nombre', 'Laboratorio Delta IA')->first();
        $this->assertNotNull($nuevaSala);

        // 3. Editar la sala
        $responseUpdate = $this->actingAs($itemAdmin)->put(route('ubicaciones.update', $nuevaSala->id), [
            'nombre' => 'Laboratorio Delta IA - Planta Alta',
            'descripcion' => 'Descripción actualizada',
        ]);
        $responseUpdate->assertRedirect(route('ubicaciones.index'));

        $this->assertDatabaseHas('ubicaciones', [
            'id' => $nuevaSala->id,
            'nombre' => 'Laboratorio Delta IA - Planta Alta',
        ]);

        // 4. Eliminar la sala sin activos asociados (debe eliminarse con éxito)
        $responseDelete = $this->actingAs($itemAdmin)->delete(route('ubicaciones.destroy', $nuevaSala->id));
        $responseDelete->assertRedirect(route('ubicaciones.index'));
        $this->assertDatabaseMissing('ubicaciones', [
            'id' => $nuevaSala->id,
        ]);

        // 5. Protección: si una sala tiene activos o movimientos, no se puede eliminar
        $salaConMovimientos = Ubicacion::first();
        $this->assertNotNull($salaConMovimientos);

        $tipo = Tipo::first();
        $elProtegido = Elemento::create([
            'nro_lia' => 'LIA-PROT-01',
            'tipo_id' => $tipo->id,
        ]);
        Movimiento::create([
            'elemento_id' => $elProtegido->id,
            'ubicacion_id' => $salaConMovimientos->id,
            'user_id' => $itemAdmin->id,
            'fecha' => now(),
        ]);

        $responseDeleteProtegida = $this->actingAs($itemAdmin)->delete(route('ubicaciones.destroy', $salaConMovimientos->id));
        $responseDeleteProtegida->assertRedirect(route('ubicaciones.index'));
        $this->assertDatabaseHas('ubicaciones', [
            'id' => $salaConMovimientos->id,
        ]);

        // 6. Restricción: un técnico o usuario sin item_admin recibe 403 Forbidden
        $responseForbidden = $this->actingAs($tecnico)->post(route('ubicaciones.store'), [
            'nombre' => 'Sala Hack',
        ]);
        $responseForbidden->assertStatus(403);
    }

    public function test_inventario_completo_filtros_multicriterio_export_csv_e_imprimir(): void
    {
        $tec = Usuario::create([
            'nombre' => 'Tecnico Test',
            'email' => 'tec.inv@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $rolTec = Rol::where('rol', 'tec')->first();
        $this->assertNotNull($rolTec);
        $tec->roles()->attach($rolTec);

        $tipoAccesorio = Tipo::where('nombre', 'accesorio')->first();
        $this->assertNotNull($tipoAccesorio);

        $elAccesorio = Elemento::create([
            'nro_lia' => 'LIA-ACC-99',
            'tipo_id' => $tipoAccesorio->id,
            'marca' => 'Genius',
            'modelo' => 'Cable HDMI',
            'anio_compra' => 2024,
        ]);

        // 1. Búsqueda por término "accesorio" encuentra el elemento cuyo tipo es accesorio
        $resBusqueda = $this->actingAs($tec)->get(route('elementos.index', ['search' => 'accesorio']));
        $resBusqueda->assertStatus(200);
        $resBusqueda->assertSee('LIA-ACC-99');
        $resBusqueda->assertSee('Cable HDMI');

        // 2. Exportación CSV de la vista filtrada
        $resCsv = $this->actingAs($tec)->get(route('elementos.exportCsv', ['search' => 'accesorio']));
        $resCsv->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $resCsv->headers->get('Content-Type'));

        // 3. Vista de impresión / PDF de la vista filtrada
        $resPrint = $this->actingAs($tec)->get(route('elementos.imprimir', ['search' => 'accesorio']));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('Informe de Inventario Completo');
        $resPrint->assertSee('LIA-ACC-99');
    }

    public function test_nuevos_reportes_fechas_donados_altas_ingreso_garantias_y_columnas_ocultas(): void
    {
        $rev = Usuario::create([
            'nombre' => 'Revisor Reportes',
            'email' => 'rev.rep@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $rolRev = Rol::where('rol', 'rev')->first();
        $rev->roles()->attach($rolRev);

        $tipo = Tipo::first();
        $el = Elemento::create([
            'nro_lia' => 'LIA-REP-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'modelo' => 'Optiplex',
            'fecha_adquisicion' => now()->subMonth()->toDateString(),
            'fecha_vencimiento_garantia' => now()->addMonths(6)->toDateString(),
            'observaciones' => 'Donación de empresa tecnológica',
        ]);

        // 1. Reporte de Donados entre Fechas
        $resDonados = $this->actingAs($rev)->get(route('reportes.show', [
            'tipo' => 'donados-fechas',
            'fecha_desde' => now()->subYear()->toDateString(),
            'fecha_hasta' => now()->toDateString(),
        ]));
        $resDonados->assertStatus(200);
        $resDonados->assertSee('LIA-REP-01');
        $resDonados->assertSee('Reporte de Activos Donados entre Fechas');

        // 2. Reporte de Altas entre Fechas
        $resAltas = $this->actingAs($rev)->get(route('reportes.show', [
            'tipo' => 'altas-fechas',
            'fecha_desde' => now()->subYear()->toDateString(),
            'fecha_hasta' => now()->toDateString(),
        ]));
        $resAltas->assertStatus(200);
        $resAltas->assertSee('LIA-REP-01');

        // 3. Reporte de Estado según Fecha de Ingreso al LIA
        $resIngreso = $this->actingAs($rev)->get(route('reportes.show', [
            'tipo' => 'ingreso-estado-fechas',
            'fecha_desde' => now()->subYear()->toDateString(),
            'fecha_hasta' => now()->toDateString(),
        ]));
        $resIngreso->assertStatus(200);
        $resIngreso->assertSee('Estado de Activos según Fecha de Ingreso al LIA');
        $resIngreso->assertSee('LIA-REP-01');

        // 4. Reporte de Garantías por Rango de Fechas
        $resGarantias = $this->actingAs($rev)->get(route('reportes.show', [
            'tipo' => 'garantias-fechas',
            'fecha_desde' => now()->subMonth()->toDateString(),
            'fecha_hasta' => now()->addYear()->toDateString(),
        ]));
        $resGarantias->assertStatus(200);
        $resGarantias->assertSee('Reporte de Estado de Garantías de Activos');
        $resGarantias->assertSee('LIA-REP-01');

        // 5. Ocultamiento de Columnas en CSV
        $resCsvOcultas = $this->actingAs($rev)->get(route('reportes.exportCsv', [
            'tipo' => 'donados-fechas',
            'columnas_ocultas' => 'Nro UNSJ,Donante / Solicitante',
        ]));
        $resCsvOcultas->assertStatus(200);
    }

    public function test_modulo_tickets_reparaciones_basura_tecnologica_y_reportes(): void
    {
        Storage::fake('public');

        $rolTec = Rol::where('rol', 'tec')->first();
        $rolRev = Rol::where('rol', 'rev')->first();

        $tec = Usuario::create([
            'nombre' => 'Tecnico Taller',
            'email' => 'tec.taller@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $tec->roles()->attach($rolTec);

        $rev = Usuario::create([
            'nombre' => 'Revisor Auditor',
            'email' => 'rev.auditor@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $rev->roles()->attach($rolRev);

        $lab = Ubicacion::first();
        $tipoPC = Tipo::where('nombre', 'computadora')->first();

        // -------------------------------------------------------------
        // 1. REESTRUCTURACIÓN DE BASURA TECNOLÓGICA COMO ESTADO OPERATIVO
        // -------------------------------------------------------------
        $pcBaja = Elemento::create([
            'nro_lia' => 'LIA-BAJA-01',
            'tipo_id' => $tipoPC->id, // Categoría física real: computadora
            'marca' => 'Lenovo',
            'modelo' => 'ThinkCentre',
        ]);
        Movimiento::create([
            'elemento_id' => $pcBaja->id,
            'tipo_movimiento' => 'baja',
            'estado_ticket' => 'baja',
            'ubicacion_id' => $lab->id,
            'puesto' => 'Descarte',
            'user_id' => $tec->id,
            'fecha' => now(),
            'comentario' => 'Baja por obsolescencia institucional',
        ]);

        $this->assertTrue(Elemento::obsoletos()->where('id', $pcBaja->id)->exists());
        $this->assertFalse(Elemento::disponibles()->where('id', $pcBaja->id)->exists());
        $this->assertStringContainsString('Basura Tecnológica', $pcBaja->presenter()->statusBadge());

        // -------------------------------------------------------------
        // 2. FORMULARIO "INICIAR TICKET" Y CREACIÓN CON FOTOS
        // -------------------------------------------------------------
        $pcReparar = Elemento::create([
            'nro_lia' => 'LIA-TICKET-01',
            'nro_unsj' => 'UNSJ-9988',
            'tipo_id' => $tipoPC->id,
            'marca' => 'Dell',
            'modelo' => 'Optiplex 7050',
            'puesto' => 'Mesa 04',
        ]);
        // Movimiento inicial previo en sala
        Movimiento::create([
            'elemento_id' => $pcReparar->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $lab->id,
            'puesto' => 'Mesa 04',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(10),
            'comentario' => 'Asignación regular a laboratorio',
        ]);

        // Formulario accesible para Revisor y Técnico
        $resFormRev = $this->actingAs($rev)->get(route('tickets.create', ['elemento_id' => $pcReparar->id]));
        $resFormRev->assertStatus(200);
        $resFormRev->assertSee('Apertura de Ticket de Reparación Técnica');
        $resFormRev->assertSee('LIA-TICKET-01');
        $resFormRev->assertSee('Revisor Auditor');
        $resFormRev->assertSee('Ubicación Actual Detectada (Origen)');
        $resFormRev->assertSee('Permanece en su sala para inspección in situ');
        $resFormRev->assertSee('Trasladar físicamente a Taller de Hardware / Depósito');

        // Registro de Ticket con imagen adjunta
        $fotoFake = UploadedFile::fake()->image('falla_fuente.jpg', 600, 400);
        $resStore = $this->actingAs($rev)->post(route('tickets.store'), [
            'elemento_id' => $pcReparar->id,
            'comentario' => 'Capacitor de fuente de poder inflado, produce reinicios intermitentes.',
            'fotos' => [$fotoFake],
        ]);
        $resStore->assertRedirect(route('dashboard.index'));

        // Verificar persistencia de ticket, estado y adjunto
        $ticketMov = Movimiento::where('elemento_id', $pcReparar->id)
            ->where('tipo_movimiento', 'reparacion')
            ->first();
        $this->assertNotNull($ticketMov);
        $this->assertEquals('abierto', $ticketMov->estado_ticket);
        $this->assertEquals('Revisor Auditor', $ticketMov->solicitante);
        $this->assertCount(1, $ticketMov->adjuntos);

        $pcReparar->refresh();
        $this->assertStringContainsString('En Reparación', $pcReparar->presenter()->statusBadge());
        $this->assertTrue(Elemento::enReparacion()->where('id', $pcReparar->id)->exists());

        // -------------------------------------------------------------
        // 3. BANDEJA Y GESTIÓN DE TICKETS PARA TÉCNICOS
        // -------------------------------------------------------------
        // Revisor bloqueado de la bandeja de gestión de tickets
        $resRevBandeja = $this->actingAs($rev)->get(route('tickets.index'));
        $resRevBandeja->assertStatus(403);

        // Técnico accede y ve el ticket abierto
        $resTecBandeja = $this->actingAs($tec)->get(route('tickets.index'));
        $resTecBandeja->assertStatus(200);
        $resTecBandeja->assertSee('LIA-TICKET-01');
        $resTecBandeja->assertSee('Capacitor de fuente de poder inflado');

        // Ver detalle del ticket
        $resShowTicket = $this->actingAs($tec)->get(route('tickets.show', $ticketMov->id));
        $resShowTicket->assertStatus(200);
        $resShowTicket->assertSee('Resolución Técnica del Ticket');
        $resShowTicket->assertSee('Mesa 04'); // Ubicación previa heredada

        // Resolución de Ticket por el Técnico
        $resResolver = $this->actingAs($tec)->post(route('tickets.resolver', $ticketMov->id), [
            'accion' => 'resolver',
            'solucion' => 'Se reemplazó fuente de poder por modelo ATX 500W nueva. Pruebas de carga de 2 horas sin fallas.',
            'ubicacion_id' => $lab->id,
            'puesto' => 'Mesa 04',
        ]);
        $resResolver->assertRedirect(route('tickets.index'));

        $ticketMov->refresh();
        $this->assertEquals('resuelto', $ticketMov->estado_ticket);

        $pcReparar->refresh();
        $this->assertStringContainsString('En Sala / Operativo', $pcReparar->presenter()->statusBadge());

        // -------------------------------------------------------------
        // 4. RESOLUCIÓN DE TICKET COMO BAJA / IRREPARABLE
        // -------------------------------------------------------------
        $pcIrreparable = Elemento::create([
            'nro_lia' => 'LIA-IRREP-02',
            'tipo_id' => $tipoPC->id,
            'marca' => 'Asus',
            'modelo' => 'Prime',
        ]);
        $ticket2 = Movimiento::create([
            'elemento_id' => $pcIrreparable->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $lab->id,
            'user_id' => $tec->id,
            'fecha' => now(),
            'solicitante' => 'Tecnico Taller',
            'comentario' => 'Cortocircuito en placa madre con quemadura en chipset principal.',
        ]);

        $resBajaTicket = $this->actingAs($tec)->post(route('tickets.resolver', $ticket2->id), [
            'accion' => 'baja',
            'solucion' => 'Daño irreparable en capas internas de PCB tras cortocircuito. Se declara baja definitiva.',
        ]);
        $resBajaTicket->assertRedirect(route('tickets.index'));

        $ticket2->refresh();
        $this->assertEquals('baja', $ticket2->estado_ticket);
        $pcIrreparable->refresh();
        $this->assertStringContainsString('Basura Tecnológica', $pcIrreparable->presenter()->statusBadge());

        // -------------------------------------------------------------
        // 5. CONSULTA DE HISTORIAL DE TICKETS EN REPORTES
        // -------------------------------------------------------------
        // Listado general de reparaciones
        $resRepGral = $this->actingAs($rev)->get(route('reportes.show', 'historial-reparaciones'));
        $resRepGral->assertStatus(200);
        $resRepGral->assertSee('Historial General de Reparaciones');
        $resRepGral->assertSee('LIA-TICKET-01');
        $resRepGral->assertSee('LIA-IRREP-02');

        // Historial consolidado de un activo específico
        $resRepEspecifico = $this->actingAs($rev)->get(route('reportes.show', [
            'tipo' => 'historial-reparaciones',
            'nro_lia' => 'LIA-TICKET-01',
        ]));
        $resRepEspecifico->assertStatus(200);
        $resRepEspecifico->assertSee('Historial Consolidado de Tickets: LIA-TICKET-01');
        $resRepEspecifico->assertSee('Ubicación Previa a Taller');
        $resRepEspecifico->assertSee('Capacitor de fuente de poder inflado');

        // Export CSV de historial de reparaciones
        $resCsvRep = $this->actingAs($rev)->get(route('reportes.exportCsv', 'historial-reparaciones'));
        $resCsvRep->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $resCsvRep->headers->get('Content-Type'));
    }

    public function test_columnas_dinamicas_atributos_y_visibilidad_por_defecto_en_reportes(): void
    {
        $rev = Usuario::create([
            'nombre' => 'Revisor Columnas',
            'email' => 'rev.col@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $rev->roles()->attach(Rol::where('rol', 'rev')->first());

        $tipoPc = Tipo::where('nombre', 'computadora')->first() ?? Tipo::first();
        $tipoPeriferico = Tipo::where('nombre', 'accesorio')->first() ?? Tipo::firstOrCreateNormalizado('accesorio');

        $attrCpu = Atributo::firstOrCreateNormalizado('Procesador');
        $attrRam = Atributo::firstOrCreateNormalizado('RAM');

        $pc = Elemento::create([
            'nro_lia' => 'LIA-COL-01',
            'tipo_id' => $tipoPc->id,
            'marca' => 'HP',
            'modelo' => 'Z240',
            'descripcion' => [
                'Procesador' => 'Intel Core i7-7700',
                'RAM' => '16GB',
            ],
        ]);

        $mouse = Elemento::create([
            'nro_lia' => 'LIA-COL-02',
            'tipo_id' => $tipoPeriferico->id,
            'marca' => 'Logitech',
            'modelo' => 'M90',
            'descripcion' => null,
        ]);

        $resShow = $this->actingAs($rev)->get(route('reportes.show', 'inventario-completo'));
        $resShow->assertStatus(200);

        // 1. Columnas de atributos dinámicos presentes en el HTML inicial
        $resShow->assertSee('Procesador');
        $resShow->assertSee('RAM');
        $resShow->assertSee('Solicitante');
        $resShow->assertSee('Garantía / Obs');

        // 2. Verificar que los valores del activo con atributos se generan y el que no los tiene muestra '-'
        $this->assertEquals('Intel Core i7-7700', $pc->getValorAtributo('Procesador'));
        $this->assertEquals('16GB', $pc->getValorAtributo('RAM'));
        $this->assertEquals('-', $mouse->getValorAtributo('Procesador'));
        $this->assertEquals('-', $mouse->getValorAtributo('RAM'));

        // 3. Export CSV sin parámetros oculta las columnas por defecto (sincronizado con pantalla)
        $resCsv = $this->actingAs($rev)->get(route('reportes.exportCsv', 'inventario-completo'));
        $resCsv->assertStatus(200);
        $csvContent = $resCsv->streamedContent();
        $this->assertStringContainsString('Nro LIA', $csvContent);
        $this->assertStringContainsString('Marca', $csvContent);
        $this->assertStringNotContainsString('Procesador;', $csvContent);
        $this->assertStringNotContainsString('Solicitante;', $csvContent);

        // 4. Export CSV si el usuario desoculta una columna específica (enviando columnas_ocultas sin Procesador)
        $resCsvCustom = $this->actingAs($rev)->get(route('reportes.exportCsv', [
            'tipo' => 'inventario-completo',
            'columnas_ocultas' => 'Solicitante,Garantía / Obs',
        ]));
        $resCsvCustom->assertStatus(200);
        $customContent = $resCsvCustom->streamedContent();
        $this->assertStringContainsString('Procesador', $customContent);
        $this->assertStringContainsString('Intel Core i7-7700', $customContent);
    }

    public function test_modelo_y_persistencia_de_reportes_configurados(): void
    {
        $this->seed(\Database\Seeders\ReporteConfiguradoSeeder::class);

        // 1. Verificar catálogo sembrado con los 11 reportes oficiales
        $this->assertEquals(11, ReporteConfigurado::count());
        $auditoriaSala = ReporteConfigurado::where('slug', 'auditoria-sala')->first();
        $this->assertNotNull($auditoriaSala);
        $this->assertTrue($auditoriaSala->es_sistema);
        $this->assertTrue($auditoriaSala->requiereUbicacion());
        $this->assertFalse($auditoriaSala->requiereNroLia());
        $this->assertFalse($auditoriaSala->requiereRangoFechas());

        $trazabilidad = ReporteConfigurado::where('slug', 'ubicacion-trazabilidad')->first();
        $this->assertTrue($trazabilidad->requiereNroLia());

        $donados = ReporteConfigurado::where('slug', 'donados-fechas')->first();
        $this->assertTrue($donados->requiereRangoFechas());

        // 2. Creación y generación de slug único para reporte personalizado
        $customSlug = ReporteConfigurado::generarSlug('Auditoría Notebooks Docentes');
        $this->assertEquals('auditoria-notebooks-docentes', $customSlug);

        $customRep = ReporteConfigurado::create([
            'slug' => $customSlug,
            'titulo' => 'Auditoría Notebooks Docentes',
            'subtitulo' => 'Reporte específico de notebooks prestadas a docentes',
            'descripcion' => 'Filtro automático de inventario',
            'icono' => 'laptop',
            'categoria' => 'custom',
            'filtros_fijos' => ['tipo_id' => 1, 'situacion' => 'prestado'],
            'variables_requeridas' => ['fechas' => true],
            'es_sistema' => false,
            'activo' => true,
            'orden' => 99,
        ]);

        $this->assertDatabaseHas('reportes_configurados', [
            'slug' => 'auditoria-notebooks-docentes',
            'es_sistema' => false,
        ]);
        $this->assertEquals(['tipo_id' => 1, 'situacion' => 'prestado'], $customRep->filtros_fijos);
        $this->assertTrue($customRep->requiereRangoFechas());

        // Slug incremental si ya existe
        $slugDuplicado = ReporteConfigurado::generarSlug('Auditoría Notebooks Docentes');
        $this->assertEquals('auditoria-notebooks-docentes-1', $slugDuplicado);
    }

    public function test_app_admin_puede_guardar_reporte_desde_inventario(): void
    {
        $appAdmin = Usuario::create([
            'nombre' => 'App Admin Reportero',
            'email' => 'appadmin_rep@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $appAdmin->roles()->attach(Rol::where('rol', 'app_admin')->first()->id);

        $usuarioComun = Usuario::create([
            'nombre' => 'Revisor Comun',
            'email' => 'revisor_comun@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $usuarioComun->roles()->attach(Rol::where('rol', 'rev')->first()->id);

        // 1. Usuario común no ve el botón y no puede enviar POST
        $resComun = $this->actingAs($usuarioComun)->get(route('elementos.index'));
        $resComun->assertStatus(200);
        $resComun->assertDontSee('Guardar como Reporte');

        $resForbidden = $this->actingAs($usuarioComun)->post(route('app_admin.reportes.guardarDesdeInventario'), [
            'titulo' => 'Reporte No Autorizado',
        ]);
        $resForbidden->assertStatus(403);

        // 2. App Admin ve el botón y el modal en Inventario
        $payload = [
            'titulo' => 'Equipos Dell Operativos',
            'subtitulo' => 'Listado de equipos Dell en funcionamiento',
            'descripcion' => 'Filtro guardado desde Inventario Completo',
            'icono' => 'desktop',
            'categoria' => 'custom',
            'filtros_fijos' => [
                'search' => 'Optiplex',
                'situacion' => 'operativo',
                'anio' => '2024',
            ],
            'variables_requeridas' => [
                'ubicacion_id' => '1',
                'fechas' => '1',
            ],
        ];

        $resAdmin = $this->actingAs($appAdmin)->get(route('elementos.index', $payload['filtros_fijos']));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Guardar como Reporte');
        $resAdmin->assertSee('modal-guardar-reporte');

        // 3. App Admin guarda el reporte desde el inventario
        $postRes = $this->actingAs($appAdmin)->from(route('elementos.index', $payload['filtros_fijos']))->post(route('app_admin.reportes.guardarDesdeInventario'), $payload);
        $postRes->assertRedirect(route('elementos.index', $payload['filtros_fijos']));
        $postRes->assertSessionHas('success');

        // 4. Verificar existencia en la base de datos
        $this->assertDatabaseHas('reportes_configurados', [
            'titulo' => 'Equipos Dell Operativos',
            'slug' => 'equipos-dell-operativos',
            'categoria' => 'custom',
            'es_sistema' => false,
            'activo' => true,
        ]);

        $reporteGuardado = ReporteConfigurado::where('slug', 'equipos-dell-operativos')->first();
        $this->assertNotNull($reporteGuardado);
        $this->assertEquals(['search' => 'Optiplex', 'situacion' => 'operativo', 'anio' => '2024'], $reporteGuardado->filtros_fijos);
        $this->assertTrue($reporteGuardado->requiereUbicacion());
        $this->assertTrue($reporteGuardado->requiereRangoFechas());
        $this->assertFalse($reporteGuardado->requiereNroLia());
    }

    public function test_modulo_gestion_de_reportes_en_app_admin(): void
    {
        $this->seed(\Database\Seeders\ReporteConfiguradoSeeder::class);

        $appAdmin = Usuario::create([
            'nombre' => 'App Admin Gerente',
            'email' => 'gerente_reportes@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $appAdmin->roles()->attach(Rol::where('rol', 'app_admin')->first()->id);

        $usuarioComun = Usuario::create([
            'nombre' => 'Revisor Base',
            'email' => 'revisor_base@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $usuarioComun->roles()->attach(Rol::where('rol', 'rev')->first()->id);

        // 1. Verificación de permisos y acceso al dashboard de App Admin
        $resForbidden = $this->actingAs($usuarioComun)->get(route('app_admin.reportes'));
        $resForbidden->assertStatus(403);

        $resIndexApp = $this->actingAs($appAdmin)->get(route('app_admin.index'));
        $resIndexApp->assertStatus(200);
        $resIndexApp->assertSee('Gestión de Reportes');
        $resIndexApp->assertSee(route('app_admin.reportes'));

        $resIndex = $this->actingAs($appAdmin)->get(route('app_admin.reportes'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Catálogo y Configuración de Reportes');
        $resIndex->assertSee('Listado completo del Inventario');
        $resIndex->assertSee('Nuevo Reporte');

        // 2. Creación de un nuevo reporte desde la gestión
        $resStore = $this->actingAs($appAdmin)->post(route('app_admin.reportes.store'), [
            'titulo' => 'Notebooks en Mantenimiento Preventivo',
            'subtitulo' => 'Equipos portátiles en revisión',
            'descripcion' => 'Seguimiento semestral de notebooks',
            'categoria' => 'soporte',
            'icono' => 'wrench',
            'orden' => 45,
            'variables_requeridas' => [
                'ubicacion_id' => '1',
            ],
            'filtros_fijos' => [
                'situacion' => 'en_reparacion',
            ],
            'activo' => '1',
        ]);

        $resStore->assertRedirect(route('app_admin.reportes'));
        $resStore->assertSessionHas('success');

        $this->assertDatabaseHas('reportes_configurados', [
            'titulo' => 'Notebooks en Mantenimiento Preventivo',
            'categoria' => 'soporte',
            'es_sistema' => false,
            'activo' => true,
        ]);

        $reporteNuevo = ReporteConfigurado::where('titulo', 'Notebooks en Mantenimiento Preventivo')->first();
        $this->assertNotNull($reporteNuevo);
        $this->assertEquals(45, $reporteNuevo->orden);
        $this->assertTrue($reporteNuevo->requiereUbicacion());

        // 3. Edición del reporte
        $resUpdate = $this->actingAs($appAdmin)->put(route('app_admin.reportes.update', $reporteNuevo->id), [
            'titulo' => 'Notebooks en Mantenimiento - Actualizado',
            'subtitulo' => 'Subtítulo modificado',
            'descripcion' => 'Descripción modificada',
            'categoria' => 'patrimonio',
            'icono' => 'shield',
            'orden' => 12,
            'variables_requeridas' => [
                'nro_lia' => '1',
            ],
            'filtros_fijos' => [
                'situacion' => 'operativo',
            ],
            'activo' => '1',
        ]);

        $resUpdate->assertRedirect(route('app_admin.reportes'));
        $this->assertDatabaseHas('reportes_configurados', [
            'id' => $reporteNuevo->id,
            'titulo' => 'Notebooks en Mantenimiento - Actualizado',
            'categoria' => 'patrimonio',
            'orden' => 12,
        ]);
        $this->assertTrue($reporteNuevo->fresh()->requiereNroLia());
        $this->assertFalse($reporteNuevo->fresh()->requiereUbicacion());

        // 4. Toggle de activación/desactivación
        $this->assertTrue($reporteNuevo->fresh()->activo);
        $resToggle = $this->actingAs($appAdmin)->patch(route('app_admin.reportes.toggle', $reporteNuevo->id));
        $resToggle->assertSessionHas('success');
        $this->assertFalse($reporteNuevo->fresh()->activo);

        // 5. Eliminación de reporte personalizado
        $resDestroy = $this->actingAs($appAdmin)->delete(route('app_admin.reportes.destroy', $reporteNuevo->id));
        $resDestroy->assertRedirect(route('app_admin.reportes'));
        $resDestroy->assertSessionHas('success');
        $this->assertDatabaseMissing('reportes_configurados', [
            'id' => $reporteNuevo->id,
        ]);

        // 6. Reporte del sistema NO se elimina físicamente, solo se desactiva
        $reporteSistema = ReporteConfigurado::where('slug', 'inventario-completo')->first();
        $this->assertTrue($reporteSistema->es_sistema);
        $resDestroySistema = $this->actingAs($appAdmin)->delete(route('app_admin.reportes.destroy', $reporteSistema->id));
        $resDestroySistema->assertRedirect(route('app_admin.reportes'));
        $this->assertDatabaseHas('reportes_configurados', [
            'id' => $reporteSistema->id,
            'slug' => 'inventario-completo',
            'activo' => false,
        ]);
    }

    public function test_motor_de_reportes_dinamicos_y_hub_de_reportes_personalizados(): void
    {
        $this->seed(\Database\Seeders\ReporteConfiguradoSeeder::class);

        $appAdmin = Usuario::create([
            'nombre' => 'App Admin Dynamic',
            'email' => 'admin_dynamic@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $appAdmin->roles()->attach([
            Rol::where('rol', 'app_admin')->first()->id,
            Rol::where('rol', 'rev')->first()->id,
        ]);

        $revisor = Usuario::create([
            'nombre' => 'Revisor User',
            'email' => 'revisor_dyn@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $revisor->roles()->attach(Rol::where('rol', 'rev')->first()->id);

        // Crear ubicaciones y tipos
        $salaA = Ubicacion::firstOrCreate(['nombre' => 'Sala A - Microelectrónica']);
        $salaB = Ubicacion::firstOrCreate(['nombre' => 'Sala B - Servidores']);
        $tipoPC = Tipo::firstOrCreate(['nombre' => 'computadora escritorio', 'tipo' => 'computadora escritorio']);

        // Crear elementos de prueba
        $pcSalaA = Elemento::create([
            'nro_lia' => 'LIA-DYN-01',
            'nro_unsj' => 'UNSJ-DYN-01',
            'tipo_id' => $tipoPC->id,
            'marca' => 'Dell',
            'modelo' => 'Optiplex 7050',
            'observaciones' => 'Equipo de laboratorio principal',
        ]);
        Movimiento::create([
            'elemento_id' => $pcSalaA->id,
            'ubicacion_id' => $salaA->id,
            'tipo_movimiento' => 'creacion',
            'user_id' => $appAdmin->id,
            'fecha' => Carbon::now()->subMonths(2),
        ]);

        $pcSalaB = Elemento::create([
            'nro_lia' => 'LIA-DYN-02',
            'nro_unsj' => 'UNSJ-DYN-02',
            'tipo_id' => $tipoPC->id,
            'marca' => 'HP',
            'modelo' => 'ProDesk 400',
            'observaciones' => 'Equipo secundario en sala B',
        ]);
        Movimiento::create([
            'elemento_id' => $pcSalaB->id,
            'ubicacion_id' => $salaB->id,
            'tipo_movimiento' => 'creacion',
            'user_id' => $appAdmin->id,
            'fecha' => Carbon::now()->subMonths(2),
        ]);

        // Crear reporte configurado dinámico con filtro fijo (tipo PC) y variable requerida (ubicacion_id)
        $reporteCustom = ReporteConfigurado::create([
            'slug' => 'computadoras-por-sala-especifica',
            'titulo' => 'Computadoras por Sala Específica',
            'subtitulo' => 'Filtro automático de PCs con selección de laboratorio',
            'descripcion' => 'Muestra únicamente computadoras de escritorio asignadas a la sala seleccionada',
            'icono' => 'cubes',
            'categoria' => 'custom',
            'filtros_fijos' => [
                'tipo_id' => $tipoPC->id,
            ],
            'variables_requeridas' => [
                'ubicacion_id' => true,
            ],
            'es_sistema' => false,
            'activo' => true,
            'orden' => 10,
        ]);

        // 1. Hub de Reportes: Usuario Revisor ve la sección de Reportes Personalizados
        $resHub = $this->actingAs($revisor)->get(route('reportes.index'));
        $resHub->assertStatus(200);
        $resHub->assertSee('Reportes Personalizados y Guardados');
        $resHub->assertSee('Computadoras por Sala Específica');
        $resHub->assertSee($salaA->nombre);
        $resHub->assertDontSee('Configurar Catálogo de Reportes'); // Revisor no ve botón de gestión

        // App Admin ve el botón de configuración de catálogo
        $resHubAdmin = $this->actingAs($appAdmin)->get(route('reportes.index'));
        $resHubAdmin->assertStatus(200);
        $resHubAdmin->assertSee('Configurar Catálogo de Reportes');

        // 2. Ejecución dinámica de reporte con variable de entrada ubicacion_id = Sala A
        $resShowSalaA = $this->actingAs($revisor)->get(route('reportes.show', [
            'tipo' => 'computadoras-por-sala-especifica',
            'ubicacion_id' => $salaA->id,
        ]));
        $resShowSalaA->assertStatus(200);
        $resShowSalaA->assertSee('Computadoras por Sala Específica');
        $resShowSalaA->assertSee('LIA-DYN-01');
        $resShowSalaA->assertSee('Dell');
        $resShowSalaA->assertDontSee('LIA-DYN-02'); // Sala B excluida por la variable de entrada

        // 3. Ejecución dinámica con variable de entrada ubicacion_id = Sala B
        $resShowSalaB = $this->actingAs($revisor)->get(route('reportes.show', [
            'tipo' => 'computadoras-por-sala-especifica',
            'ubicacion_id' => $salaB->id,
        ]));
        $resShowSalaB->assertStatus(200);
        $resShowSalaB->assertSee('LIA-DYN-02');
        $resShowSalaB->assertSee('HP');
        $resShowSalaB->assertDontSee('LIA-DYN-01');

        // 4. Exportación a CSV de reporte dinámico
        $resCsv = $this->actingAs($revisor)->get(route('reportes.exportCsv', [
            'tipo' => 'computadoras-por-sala-especifica',
            'ubicacion_id' => $salaA->id,
        ]));
        $resCsv->assertStatus(200);
        $csvContent = $resCsv->streamedContent();
        $this->assertStringContainsString('LIA-DYN-01', $csvContent);
        $this->assertStringContainsString('Dell', $csvContent);
        $this->assertStringNotContainsString('LIA-DYN-02', $csvContent);
    }

    public function test_exclusion_de_cabeceras_ods_y_depuracion_de_registros_espurios(): void
    {
        // 1. Verificar que no existan activos espurios con nro_lia en palabras clave de encabezados
        $headerWords = ['Disco', 'RAM', 'CPU', 'Procesador', 'Modelo', 'Nro LIA', 'Nro UNSJ'];
        $countLiaHeaders = Elemento::whereIn('nro_lia', $headerWords)->count();
        $this->assertEquals(0, $countLiaHeaders, 'No deben existir activos cuyo Nro LIA sea un nombre de encabezado ODS.');

        // 2. Verificar que no existan activos con especificaciones redundantes de cabecera
        $countSpecsHeaders = Elemento::whereJsonContains('descripcion->Procesador', 'Procesador')
            ->whereJsonContains('descripcion->RAM', 'RAM')
            ->count();
        $this->assertEquals(0, $countSpecsHeaders, 'No deben existir activos con especificaciones técnicas que dupliquen nombres de columnas.');
    }

    public function test_reporte_configurado_de_fuentes_y_resolucion_de_filtros_de_tipo_y_ubicacion(): void
    {
        $revisor = Usuario::where('email', 'martin@lia.com')->first();
        if (!$revisor) {
            $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
            $revisor = Usuario::factory()->create([
                'nombre' => 'Martin Varela',
                'email' => 'martin@lia.com',
                'password' => bcrypt('1234'),
            ]);
            $revisor->roles()->syncWithoutDetaching([$rolRev->id]);
        }

        $tipoFuente = Tipo::firstOrCreate(['nombre' => 'fuente'], ['descripcion' => 'Fuente de alimentación']);
        $salaA = Ubicacion::firstOrCreate(['nombre' => 'Sala A - Laboratorio']);
        $taller = Ubicacion::firstOrCreate(['nombre' => 'Taller de Hardware']);

        // Crear dos fuentes: una en Sala A y otra en Taller de Hardware
        $fuente1 = Elemento::create([
            'nro_lia' => 'LIA-FNT-01',
            'tipo_id' => $tipoFuente->id,
            'marca' => 'EVGA',
            'modelo' => '600W 80 Plus',
            'activo' => true,
        ]);
        Movimiento::create([
            'elemento_id' => $fuente1->id,
            'ubicacion_id' => $salaA->id,
            'user_id' => $revisor->id,
            'tipo_movimiento' => 'alta',
            'fecha' => now(),
        ]);

        $fuente2 = Elemento::create([
            'nro_lia' => 'LIA-FNT-02',
            'tipo_id' => $tipoFuente->id,
            'marca' => 'Corsair',
            'modelo' => 'CX750M',
            'activo' => true,
        ]);
        Movimiento::create([
            'elemento_id' => $fuente2->id,
            'ubicacion_id' => $taller->id,
            'user_id' => $revisor->id,
            'tipo_movimiento' => 'alta',
            'fecha' => now(),
        ]);

        // 1. Reporte con filtro search='fuente' que debe resolverse automáticamente a tipo_id
        $reporteFuentes = ReporteConfigurado::create([
            'slug' => 'reporte-fuentes-test',
            'titulo' => 'Reporte Oficial de Fuentes',
            'filtros_fijos' => ['search' => 'fuente'],
            'variables_requeridas' => ['ubicacion_id' => true],
            'es_sistema' => false,
            'activo' => true,
            'orden' => 99,
        ]);

        // Sin especificar ubicacion_id en request: debe devolver ambas fuentes y no restringir a ubicacion_id = 1
        $resAll = $this->actingAs($revisor)->get(route('reportes.show', 'reporte-fuentes-test'));
        $resAll->assertStatus(200);
        $resAll->assertSee('LIA-FNT-01');
        $resAll->assertSee('LIA-FNT-02');
        $resAll->assertSee('EVGA');
        $resAll->assertSee('Corsair');

        // Especificando ubicacion_id = $taller->id: debe filtrar solo la del taller
        $resTaller = $this->actingAs($revisor)->get(route('reportes.show', [
            'tipo' => 'reporte-fuentes-test',
            'ubicacion_id' => $taller->id,
        ]));
        $resTaller->assertStatus(200);
        $resTaller->assertSee('LIA-FNT-02');
        $resTaller->assertDontSee('LIA-FNT-01');

        // 2. Reporte con filtros_fijos con nombre de tipo textual ('tipo' => 'fuente')
        $reporteTipoSlug = ReporteConfigurado::create([
            'slug' => 'fuentes-por-tipo-nombre',
            'titulo' => 'Fuentes por Nombre de Tipo',
            'filtros_fijos' => ['tipo' => 'fuente'],
            'variables_requeridas' => [],
            'es_sistema' => false,
            'activo' => true,
            'orden' => 100,
        ]);

        $resSlug = $this->actingAs($revisor)->get(route('reportes.show', 'fuentes-por-tipo-nombre'));
        $resSlug->assertStatus(200);
        $resSlug->assertSee('LIA-FNT-01');
        $resSlug->assertSee('LIA-FNT-02');
    }

    public function test_filtrado_multidimensional_por_puesto_de_trabajo_en_inventario_y_reportes(): void
    {
        $admin = Usuario::where('email', 'admin@lia.com')->first();
        if (!$admin) {
            $rolAdmin = Rol::firstOrCreate(['rol' => 'app_admin']);
            $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
            $admin = Usuario::factory()->create([
                'nombre' => 'App Admin User',
                'email' => 'admin@lia.com',
                'password' => bcrypt('1234'),
            ]);
            $admin->roles()->syncWithoutDetaching([$rolAdmin->id, $rolRev->id]);
        }

        $tipoPc = Tipo::firstOrCreate(['nombre' => 'computadora escritorio'], ['descripcion' => 'PC de escritorio']);
        $sala1 = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Sala 1']);

        // Crear activos en puestos específicos
        $pcPuesto3 = Elemento::create([
            'nro_lia' => 'LIA-PST-03',
            'tipo_id' => $tipoPc->id,
            'marca' => 'Lenovo',
            'modelo' => 'ThinkCentre M720',
            'puesto' => 'Puesto 03',
            'activo' => true,
        ]);
        Movimiento::create([
            'elemento_id' => $pcPuesto3->id,
            'ubicacion_id' => $sala1->id,
            'puesto' => 'Puesto 03',
            'user_id' => $admin->id,
            'tipo_movimiento' => 'alta',
            'fecha' => now(),
        ]);

        $pcPuesto7 = Elemento::create([
            'nro_lia' => 'LIA-PST-07',
            'tipo_id' => $tipoPc->id,
            'marca' => 'Dell',
            'modelo' => 'OptiPlex 7080',
            'puesto' => 'Puesto 07',
            'activo' => true,
        ]);
        Movimiento::create([
            'elemento_id' => $pcPuesto7->id,
            'ubicacion_id' => $sala1->id,
            'puesto' => 'Puesto 07',
            'user_id' => $admin->id,
            'tipo_movimiento' => 'alta',
            'fecha' => now(),
        ]);

        // 1. Verificar presencia de selector de puesto y filtrado en elementos.index
        $resIndex = $this->actingAs($admin)->get(route('elementos.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Todos los Puestos');
        $resIndex->assertSee('Puesto 03');
        $resIndex->assertSee('Puesto 07');

        // Filtrar específicamente por Puesto 03
        $resFilterPuesto = $this->actingAs($admin)->get(route('elementos.index', ['puesto' => 'Puesto 03']));
        $resFilterPuesto->assertStatus(200);
        $resFilterPuesto->assertSee('LIA-PST-03');
        $resFilterPuesto->assertSee('Lenovo');
        $resFilterPuesto->assertDontSee('LIA-PST-07');

        // 2. Filtrado por puesto en auditoriaSala
        $resAuditoria = $this->actingAs($admin)->get(route('reportes.auditoria', [
            'ubicacion' => $sala1->id,
            'puesto' => 'Puesto 07',
        ]));
        $resAuditoria->assertStatus(200);
        $resAuditoria->assertSee('LIA-PST-07');
        $resAuditoria->assertDontSee('LIA-PST-03');

        // 3. Reporte configurado con filtro fijo de puesto
        $reportePuestoFijo = ReporteConfigurado::create([
            'slug' => 'auditoria-puesto-3',
            'titulo' => 'Auditoría de Puesto 03',
            'filtros_fijos' => ['puesto' => 'Puesto 03'],
            'variables_requeridas' => [],
            'es_sistema' => false,
            'activo' => true,
            'orden' => 101,
        ]);

        $resRepPuesto = $this->actingAs($admin)->get(route('reportes.show', 'auditoria-puesto-3'));
        $resRepPuesto->assertStatus(200);
        $resRepPuesto->assertSee('LIA-PST-03');
        $resRepPuesto->assertDontSee('LIA-PST-07');
    }

    public function test_columna_compuesta_de_especificaciones_hardware_en_inventario_y_reportes(): void
    {
        $admin = Usuario::where('email', 'admin@lia.com')->first();
        if (!$admin) {
            $rolAdmin = Rol::firstOrCreate(['rol' => 'app_admin']);
            $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
            $admin = Usuario::factory()->create([
                'nombre' => 'App Admin User',
                'email' => 'admin@lia.com',
                'password' => bcrypt('1234'),
            ]);
            $admin->roles()->syncWithoutDetaching([$rolAdmin->id, $rolRev->id]);
        }

        $tipoPc = Tipo::firstOrCreate(['nombre' => 'computadora escritorio'], ['descripcion' => 'PC']);
        $sala1 = Ubicacion::firstOrCreate(['nombre' => 'Sala 1']);

        $attrProc = Atributo::firstOrCreate(['nombre' => 'Procesador']);
        $attrRam = Atributo::firstOrCreate(['nombre' => 'RAM'], ['unidad_medida' => 'GB']);
        $attrDisco = Atributo::firstOrCreate(['nombre' => 'Disco'], ['unidad_medida' => 'GB']);

        // Crear elemento con especificaciones
        $pc = Elemento::create([
            'nro_lia' => 'LIA-SPEC-01',
            'tipo_id' => $tipoPc->id,
            'marca' => 'Dell',
            'modelo' => 'OptiPlex 3080',
            'puesto' => 'Puesto 01',
            'activo' => true,
        ]);
        Movimiento::create([
            'elemento_id' => $pc->id,
            'ubicacion_id' => $sala1->id,
            'puesto' => 'Puesto 01',
            'user_id' => $admin->id,
            'tipo_movimiento' => 'alta',
            'fecha' => now(),
        ]);

        \App\Models\ElementoAtributoValor::create(['elemento_id' => $pc->id, 'atributo_id' => $attrProc->id, 'valor' => 'i5-10400']);
        \App\Models\ElementoAtributoValor::create(['elemento_id' => $pc->id, 'atributo_id' => $attrRam->id, 'valor' => '16']);
        \App\Models\ElementoAtributoValor::create(['elemento_id' => $pc->id, 'atributo_id' => $attrDisco->id, 'valor' => '480 SSD']);

        // 1. Probar método sintetizado directo
        $pc->load('valoresAtributos.atributo');
        $specs = $pc->especificacionesSintetizadas(false);
        $this->assertStringContainsString('i5-10400', $specs);
        $this->assertStringContainsString('16 GB', $specs);
        $this->assertStringContainsString('480 SSD', $specs);

        // 2. Verificar que index.blade.php renderiza la columna y los badges de hardware
        $resIndex = $this->actingAs($admin)->get(route('elementos.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Especificaciones / Hardware');
        $resIndex->assertSee('i5-10400');
        $resIndex->assertSee('16 GB');

        // 3. Verificar exportCsv de elementos
        $resCsv = $this->actingAs($admin)->get(route('elementos.exportCsv'));
        $resCsv->assertStatus(200);
        $csvText = $resCsv->streamedContent();
        $this->assertStringContainsString('Especificaciones / Hardware', $csvText);
        $this->assertStringContainsString('i5-10400', $csvText);

        // 4. Verificar reporte inventario-completo
        $resRep = $this->actingAs($admin)->get(route('reportes.show', 'inventario-completo'));
        $resRep->assertStatus(200);
        $resRep->assertSee('Especificaciones / Hardware');
        $resRep->assertSee('i5-10400');
    }

    public function test_middleware_de_cabeceras_de_seguridad_http_en_rutas_web(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        // Verificar presencia de cabeceras de endurecimiento
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Verificar Content-Security-Policy
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);

        // Verificar que no se divulgue X-Powered-By
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_rate_limiting_contra_fuerza_bruta_en_login(): void
    {
        // Enviar 5 intentos fallidos
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'login' => 'ataque@lia.com',
                'password' => 'password_erroneo',
            ]);
            // Redirige hacia atrás con error de credenciales
            $response->assertSessionHasErrors('login');
        }

        // El 6to intento debe ser bloqueado por RateLimiter (o middleware throttle 429)
        $bloqueado = $this->post('/login', [
            'login' => 'ataque@lia.com',
            'password' => 'password_erroneo',
        ]);

        if ($bloqueado->status() === 429) {
            $this->assertEquals(429, $bloqueado->status());
        } else {
            $bloqueado->assertSessionHasErrors('login');
            $mensaje = session('errors')->first('login');
            $this->assertStringContainsString('Demasiados intentos', $mensaje);
        }
    }

    public function test_estandarizacion_de_desplegables_y_autocompletado_consistente_carreras_cargos_y_solicitantes(): void
    {
        $userAdmin = Usuario::create([
            'nombre' => 'Admin Usuarios Datalist',
            'email' => 'admin_datalist@lia.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());
        $userAdmin->roles()->attach(Rol::where('rol', 'registered')->first());
        $userAdmin->roles()->attach(Rol::where('rol', 'tec')->first());

        // 1. users.edit debe contener datalists para carreras y cargos oficiales
        $resUsersEdit = $this->actingAs($userAdmin)->get(route('users.edit', $userAdmin->id));
        $resUsersEdit->assertStatus(200);
        $resUsersEdit->assertSee('id="lista-carreras"', false);
        $resUsersEdit->assertSee('id="lista-cargos"', false);
        $resUsersEdit->assertSee('Director');
        $resUsersEdit->assertSee('Coordinador de Área');
        $resUsersEdit->assertSee('JTP');

        // 2. profile.edit debe contener datalists estandarizados
        $resProfileEdit = $this->actingAs($userAdmin)->get(route('profile.edit'));
        $resProfileEdit->assertStatus(200);
        $resProfileEdit->assertSee('id="lista-carreras-perfil"', false);
        $resProfileEdit->assertSee('id="lista-cargos-perfil"', false);

        // 3. tickets.create debe tener datalist de solicitantes frecuentes
        $resTicketCreate = $this->actingAs($userAdmin)->get(route('tickets.create'));
        $resTicketCreate->assertStatus(200);
        $resTicketCreate->assertSee('id="lista-solicitantes-ticket"', false);

        // 4. movimientos.rapido debe tener datalist y dropdown estandarizado
        $resMovRapido = $this->actingAs($userAdmin)->get(route('movimientos.rapido'));
        $resMovRapido->assertStatus(200);
        $resMovRapido->assertSee('id="lista-solicitantes-rapido"', false);
        $resMovRapido->assertSee('id="resultados-dropdown"', false);
    }

    public function test_unificacion_y_ordenamiento_de_reportes_oficiales_y_personalizados(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Reportes Unificados',
            'email' => 'admin_unificados@lia.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach(Rol::where('rol', 'app_admin')->first());
        $admin->roles()->attach(Rol::where('rol', 'rev')->first());

        // 1. Acceder al hub unificado de reportes
        $res = $this->actingAs($admin)->get(route('reportes.index'));
        $res->assertStatus(200);
        $res->assertSee('Catálogo unificado de reportes');
        $res->assertSee('Listado completo del Inventario');
        $res->assertSee('Inventario por ubicación');
        // No debe existir división con encabezado "Reportes Oficiales del Sistema"
        $res->assertDontSee('Reportes Oficiales del Sistema');

        // 2. Administrador puede desactivar u ordenar cualquier reporte inicial gestionado en reportes_configurados
        $reporteInicial = \App\Models\ReporteConfigurado::where('slug', 'bajas-basura')->first();
        if ($reporteInicial) {
            $resDelete = $this->actingAs($admin)->delete(route('app_admin.reportes.destroy', $reporteInicial->id));
            $resDelete->assertRedirect(route('app_admin.reportes'));
            $this->assertDatabaseHas('reportes_configurados', ['id' => $reporteInicial->id, 'activo' => false]);

            // Con parámetro force puede removerse definitivamente
            $this->actingAs($admin)->delete(route('app_admin.reportes.destroy', $reporteInicial->id), ['force' => 1]);
            $this->assertDatabaseMissing('reportes_configurados', ['id' => $reporteInicial->id]);
        }
    }

    public function test_matriz_escalonada_de_permisos_tickets_rev_vs_subtec_vs_tec(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\TipoSeeder::class);
        $this->seed(\Database\Seeders\UbicacionSeeder::class);

        $rolRev = \App\Models\Rol::where('rol', 'rev')->firstOrFail();
        $rolSubTec = \App\Models\Rol::where('rol', 'subtec')->firstOrFail();
        $rolTec = \App\Models\Rol::where('rol', 'tec')->firstOrFail();
        $rolReg = \App\Models\Rol::where('rol', 'registered')->firstOrFail();

        $userRev = \App\Models\Usuario::create([
            'email' => 'revisor_test@lia.com',
            'nombre' => 'Revisor Ticket Test',
            'password' => bcrypt('secret123'),
            'activo' => true,
        ]);
        $userRev->roles()->sync([$rolReg->id, $rolRev->id]);

        $userSubTec = \App\Models\Usuario::create([
            'email' => 'subtec_test@lia.com',
            'nombre' => 'SubTec Ticket Test',
            'password' => bcrypt('secret123'),
            'activo' => true,
        ]);
        $userSubTec->roles()->sync([$rolReg->id, $rolSubTec->id]);

        $userTec = \App\Models\Usuario::create([
            'email' => 'tec_test@lia.com',
            'nombre' => 'Tec Ticket Test',
            'password' => bcrypt('secret123'),
            'activo' => true,
        ]);
        $userTec->roles()->sync([$rolReg->id, $rolTec->id]);

        $ubicacion = \App\Models\Ubicacion::first();
        $elemento = \App\Models\Elemento::create([
            'nro_lia' => 'TICK-SUBTEC-01',
            'tipo_id' => 1,
            'marca' => 'Dell',
            'modelo' => 'OptiPlex 7050',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        \App\Models\Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'puesto' => 'Puesto 05',
            'user_id' => $userTec->id,
            'fecha' => now(),
            'comentario' => 'Ingreso regular a sala',
        ]);

        // 1. REVISOR puede iniciar ticket
        $resStore = $this->actingAs($userRev)->post(route('tickets.store'), [
            'elemento_id' => $elemento->id,
            'comentario' => 'Falla en cooler frontal y ruido en fuente de poder.',
            'tipo_traslado' => 'insitu',
        ]);
        $resStore->assertRedirect(route('dashboard.index'));

        $ticket = \App\Models\Movimiento::where('elemento_id', $elemento->id)
            ->where('tipo_movimiento', 'reparacion')
            ->firstOrFail();
        $this->assertEquals('abierto', $ticket->estado_ticket);

        // 2. REVISOR puede comentar en el hilo del ticket
        $resComentarioRev = $this->actingAs($userRev)->post(route('tickets.comentar', $ticket->id), [
            'mensaje' => 'Se verificó que el cooler deja de girar luego de 5 minutos.',
        ]);
        $resComentarioRev->assertRedirect(route('tickets.show', $ticket->id));
        $this->assertDatabaseHas('ticket_comentarios', [
            'ticket_id' => $ticket->id,
            'user_id' => $userRev->id,
            'tipo' => 'comentario',
        ]);

        // 3. REVISOR NUNCA puede solucionar el ticket (debe recibir 403)
        $resRevSolucionar = $this->actingAs($userRev)->post(route('tickets.solucionar', $ticket->id), [
            'solucion' => 'Intento de solución por un Revisor no autorizado.',
        ]);
        $resRevSolucionar->assertStatus(403);

        // 4. REVISOR NUNCA puede cerrar el ticket (debe recibir 403)
        $resRevCerrar = $this->actingAs($userRev)->post(route('tickets.resolver', $ticket->id), [
            'accion' => 'resolver',
            'solucion' => 'Intento de cierre formal por Revisor.',
        ]);
        $resRevCerrar->assertStatus(403);

        // 5. SUBTEC puede solucionar el ticket
        $conteoMovimientosAntes = \App\Models\Movimiento::count();
        $resSubTecSolucionar = $this->actingAs($userSubTec)->post(route('tickets.solucionar', $ticket->id), [
            'solucion' => 'Se desarmó cooler frontal, limpieza con alcohol isopropílico y lubricación con aceite sintético. Pruebas térmicas OK.',
        ]);
        $resSubTecSolucionar->assertRedirect(route('tickets.show', $ticket->id));
        
        $ticket->refresh();
        $this->assertEquals('solucionado', $ticket->estado_ticket);
        $this->assertDatabaseHas('ticket_comentarios', [
            'ticket_id' => $ticket->id,
            'user_id' => $userSubTec->id,
            'tipo' => 'solucion_propuesta',
        ]);

        // REGLA ESTRICTA: El SubTec NUNCA registra movimientos asociados al ticket
        $conteoMovimientosDespues = \App\Models\Movimiento::count();
        $this->assertEquals($conteoMovimientosAntes, $conteoMovimientosDespues, 'El SubTec no debe generar ningún registro en la tabla movimientos.');

        // 6. SUBTEC NUNCA puede cerrar formalmente el ticket (debe recibir 403)
        $resSubTecCerrar = $this->actingAs($userSubTec)->post(route('tickets.resolver', $ticket->id), [
            'accion' => 'resolver',
            'solucion' => 'Intento de cierre formal por SubTec.',
        ]);
        $resSubTecCerrar->assertStatus(403);

        // 7. TECNICO puede cerrar formalmente el ticket y asociar movimiento de inventario
        $resTecCerrar = $this->actingAs($userTec)->post(route('tickets.resolver', $ticket->id), [
            'accion' => 'resolver',
            'solucion' => 'Verificación técnica aprobada. Equipo retornado a servicio activo.',
            'ubicacion_id' => $ubicacion->id,
            'puesto' => 'Puesto 05',
        ]);
        $resTecCerrar->assertRedirect(route('tickets.index'));

        $ticket->refresh();
        $this->assertEquals('resuelto', $ticket->estado_ticket);

        // Se verifica que el TEC sí generó el movimiento formal con ticket_id asociado
        $this->assertDatabaseHas('movimientos', [
            'elemento_id' => $elemento->id,
            'ticket_id' => $ticket->id,
            'tipo_movimiento' => 'interno',
            'estado_ticket' => 'resuelto',
        ]);
    }

    public function test_seeder_cuentas_reales_roles_y_anonimizacion_ley_25326(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);

        // 1. Verificación de cuentas oficiales
        $flavia = \App\Models\Usuario::where('email', 'flavia@lia.com')->firstOrFail();
        $this->assertTrue($flavia->hasRole(['user_admin', 'app_admin', 'item_admin', 'tec', 'subtec', 'rev', 'registered']));

        $marcelo = \App\Models\Usuario::where('email', 'marcelo@lia.com')->firstOrFail();
        $this->assertTrue($marcelo->hasRole('subtec'));
        $this->assertTrue($marcelo->hasRole('tec'));
        $this->assertTrue($marcelo->hasRole('rev'));
        $this->assertFalse($marcelo->hasRole('user_admin'));

        $cristina = \App\Models\Usuario::where('email', 'cristina@lia.com')->firstOrFail();
        $this->assertTrue($cristina->hasRole('user_admin'));
        $this->assertTrue($cristina->hasRole('app_admin'));
        $this->assertFalse($cristina->hasRole('tec'));

        $martin = \App\Models\Usuario::where('email', 'martin@lia.com')->firstOrFail();
        $this->assertTrue($martin->hasRole('rev'));
        $this->assertFalse($martin->hasRole('tec'));

        // 2. Verificación de casos > 6 años
        $carlos = \App\Models\Usuario::where('email', 'carlos.gomez@ex-lia.com')->firstOrFail();
        $this->assertFalse($carlos->activo);
        $this->assertTrue($carlos->isExPersonalMayor6Anios());

        $laura = \App\Models\Usuario::where('email', 'laura.martinez@ex-lia.com')->firstOrFail();
        $this->assertFalse($laura->activo);
        $this->assertTrue($laura->isExPersonalMayor6Anios());

        // 3. Visualización en panel de User Admin
        $resIndex = $this->actingAs($flavia)->get(route('users.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Plazo Legal Cumplido');
        $resIndex->assertSee('Borrar Huella');

        // 4. Ejecución del borrado de huella digital y anonimización (Ley 25.326)
        $resAnon = $this->actingAs($flavia)->post(route('users.anonimizar', $carlos->id));
        $resAnon->assertRedirect(route('users.index'));

        $carlos->refresh();
        $this->assertTrue($carlos->esAnonimizado());
        $this->assertNull($carlos->dni);
        $this->assertNull($carlos->whatsapp);
        $this->assertStringStartsWith('anonimo_' . $carlos->id, $carlos->email);
        $this->assertCount(0, $carlos->roles);

        // Su historial de trayectorias permanece íntegro sin quebrar claves foráneas
        $this->assertGreaterThan(0, $carlos->trayectorias()->count());

        // La vista de usuarios ahora refleja el estado anonimizado
        $resIndexDespues = $this->actingAs($flavia)->get(route('users.index'));
        $resIndexDespues->assertSee('Huella Anonimizada (Ley 25.326)');
    }

    public function test_modulo_de_reportes_de_auditoria_y_trazabilidad_por_usuario(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\TipoSeeder::class);
        $this->seed(\Database\Seeders\UbicacionSeeder::class);

        $flavia = \App\Models\Usuario::where('email', 'flavia@lia.com')->firstOrFail();
        $marcelo = \App\Models\Usuario::where('email', 'marcelo@lia.com')->firstOrFail();
        $martin = \App\Models\Usuario::where('email', 'martin@lia.com')->firstOrFail();
        $juan = \App\Models\Usuario::where('email', 'juan.perez@lia.com')->firstOrFail();

        $ubicacion = \App\Models\Ubicacion::first();
        $elemento = \App\Models\Elemento::create([
            'nro_lia' => 'REP-USER-01',
            'tipo_id' => 1,
            'marca' => 'HP',
            'modelo' => 'EliteDesk 800',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        // Registrar actividad vinculada a Marcelo (un movimiento regular y un ticket)
        \App\Models\Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'puesto' => 'Puesto 03',
            'user_id' => $marcelo->id,
            'fecha' => now(),
            'comentario' => 'Asignación regular por Marcelo',
        ]);

        $ticket = \App\Models\Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $ubicacion->id,
            'puesto' => 'Puesto 03',
            'user_id' => $marcelo->id,
            'fecha' => now(),
            'solicitante' => $marcelo->nombre,
            'comentario' => 'Revisión técnica de memoria RAM',
        ]);

        \App\Models\TicketComentario::create([
            'ticket_id' => $ticket->id,
            'user_id' => $marcelo->id,
            'mensaje' => 'Diagnóstico: Módulo DDR4 con pines limpios, prueba MemTest OK.',
            'tipo' => 'solucion_propuesta',
        ]);

        // 1. Acceso restringido: Usuario sin rol user_admin recibe 403
        $resForbidden = $this->actingAs($martin)->get(route('users.reporte', $marcelo->id));
        $resForbidden->assertStatus(403);

        // 2. Acceso autorizado: User admin (Flavia) visualiza el reporte consolidado
        $resAdmin = $this->actingAs($flavia)->get(route('users.reporte', $marcelo->id));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Informe de Auditoría y Trazabilidad: Marcelo Mondre');
        $resAdmin->assertSee('Apartado de Tickets y Soporte Técnico');
        $resAdmin->assertSee('Apartado de Configuración y Emisión de Reportes');
        $resAdmin->assertSee('Apartado de Movimientos de Inventario y Operatoria Física');
        $resAdmin->assertSee('Imprimir Informe Oficial');

        // Se comprueba presencia de los registros de Marcelo
        $resAdmin->assertSee('REP-USER-01');
        $resAdmin->assertSee('Revisión técnica de memoria RAM');
        $resAdmin->assertSee('Asignación regular por Marcelo');

        // 3. Usuario sin actividad previa (Juan Pérez) muestra apartados limpios con mensajes de ausencia de datos
        $resVacio = $this->actingAs($flavia)->get(route('users.reporte', $juan->id));
        $resVacio->assertStatus(200);
        $resVacio->assertSee('El usuario no posee participaciones registradas en tickets');
        $resVacio->assertSee('El usuario no posee registros históricos de movimientos');
    }

    public function test_areas_multidisciplinarias_y_acumulativas_en_tickets(): void
    {
        $rolTec = Rol::firstOrCreate(['rol' => 'tec']);
        $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
        $rolReg = Rol::firstOrCreate(['rol' => 'registered']);

        $tec = Usuario::create([
            'email' => 'tec_area_test@lia.com',
            'nombre' => 'Tec Area Test',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $tec->roles()->sync([$rolReg->id, $rolTec->id]);

        $rev = Usuario::create([
            'email' => 'rev_area_test@lia.com',
            'nombre' => 'Rev Area Test',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $rev->roles()->sync([$rolReg->id, $rolRev->id]);

        $tipo = \App\Models\Tipo::firstOrCreate(['nombre' => 'Laptop']);
        $ubicacion = \App\Models\Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Redes']);

        $areaHw = \App\Models\Area::firstOrCreate(['codigo' => 'HW'], ['nombre' => 'Hardware', 'color' => '#f97316', 'activo' => true]);
        $areaSw = \App\Models\Area::firstOrCreate(['codigo' => 'SW'], ['nombre' => 'Software', 'color' => '#3b82f6', 'activo' => true]);

        $elemento = \App\Models\Elemento::create([
            'nro_lia' => 'LIA-AREA-TEST',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'modelo' => 'Latitude 5420',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        // 1. Crear ticket con área inicial Hardware
        $resStore = $this->actingAs($rev)->post(route('tickets.store'), [
            'elemento_id' => $elemento->id,
            'area_id' => $areaHw->id,
            'comentario' => 'Falla de pantalla azul persistente y posible capacitor quemado',
            'tipo_traslado' => 'insitu',
        ]);
        $resStore->assertRedirect(route('dashboard.index'));

        $ticket = \App\Models\Movimiento::where('elemento_id', $elemento->id)
            ->where('tipo_movimiento', 'reparacion')
            ->first();
        $this->assertNotNull($ticket);
        $this->assertTrue($ticket->areas->contains('id', $areaHw->id));
        $this->assertCount(1, $ticket->areas);

        // 2. Acumular área Software vía comentario/hilo
        $resComentario = $this->actingAs($tec)->post(route('tickets.comentar', $ticket->id), [
            'mensaje' => 'Se detectó además corrupción en el kernel de Windows, requiere intervención de Software',
            'nueva_area_id' => $areaSw->id,
        ]);
        $resComentario->assertRedirect(route('tickets.show', $ticket->id));

        $ticket->refresh();
        $this->assertCount(2, $ticket->areas);
        $this->assertTrue($ticket->areas->contains('id', $areaHw->id));
        $this->assertTrue($ticket->areas->contains('id', $areaSw->id));

        // 3. Filtrar bandeja de tickets por área
        $resFiltroHw = $this->actingAs($tec)->get(route('tickets.index', ['area_id' => $areaHw->id]));
        $resFiltroHw->assertStatus(200);
        $resFiltroHw->assertSee('LIA-AREA-TEST');
        $resFiltroHw->assertSee('Hardware');
        $resFiltroHw->assertSee('Software');

        // 4. Verificación en la vista de detalle
        $resShow = $this->actingAs($tec)->get(route('tickets.show', $ticket->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Áreas Involucradas (Multidisciplinario)');
        $resShow->assertSee('Hardware');
        $resShow->assertSee('Software');
    }

    public function test_modulo_tickets_colaborativo_tipo_foro_y_adjuntos_en_hilo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $rolTec = Rol::firstOrCreate(['rol' => 'tec']);
        $rolSubTec = Rol::firstOrCreate(['rol' => 'subtec']);
        $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
        $rolReg = Rol::firstOrCreate(['rol' => 'registered']);

        $tec = Usuario::create([
            'email' => 'tec_foro@lia.com',
            'nombre' => 'Tecnico Foro',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $tec->roles()->sync([$rolReg->id, $rolTec->id]);

        $rev = Usuario::create([
            'email' => 'rev_foro@lia.com',
            'nombre' => 'Revisor Foro',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $rev->roles()->sync([$rolReg->id, $rolRev->id]);

        $tipo = \App\Models\Tipo::first();
        $ubicacion = \App\Models\Ubicacion::first();

        $elemento = \App\Models\Elemento::create([
            'nro_lia' => 'LIA-FORO-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Lenovo',
            'modelo' => 'ThinkCentre M720',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        $ticket = \App\Models\Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $ubicacion->id,
            'puesto' => 'P-10',
            'user_id' => $rev->id,
            'fecha' => now(),
            'solicitante' => $rev->nombre,
            'comentario' => 'Equipo no enciende luego de corte de tensión',
        ]);

        // 1. Revisor publica mensaje con foto adjunta en el hilo
        $fotoRev = \Illuminate\Http\UploadedFile::fake()->image('fuente_quemada.jpg');
        $resPostRev = $this->actingAs($rev)->post(route('tickets.comentar', $ticket->id), [
            'mensaje' => 'Adjunto foto del fusible de la fuente de poder chamuscado.',
            'fotos' => [$fotoRev],
        ]);
        $resPostRev->assertRedirect(route('tickets.show', $ticket->id));

        $this->assertDatabaseHas('ticket_comentarios', [
            'ticket_id' => $ticket->id,
            'user_id' => $rev->id,
            'mensaje' => 'Adjunto foto del fusible de la fuente de poder chamuscado.',
        ]);

        $comentarioRev = \App\Models\TicketComentario::where('ticket_id', $ticket->id)
            ->where('user_id', $rev->id)
            ->first();
        $this->assertNotNull($comentarioRev);
        $this->assertCount(1, $comentarioRev->adjuntos);
        $this->assertEquals('fuente_quemada.jpg', $comentarioRev->adjuntos->first()->nombre_original);

        // 2. Técnico responde en el foro con otra foto
        $fotoTec = \Illuminate\Http\UploadedFile::fake()->image('reemplazo_fusible.jpg');
        $resPostTec = $this->actingAs($tec)->post(route('tickets.comentar', $ticket->id), [
            'mensaje' => 'Recibido en banco de pruebas. Se reemplaza fusible y se testean voltajes de 12V y 5V.',
            'fotos' => [$fotoTec],
        ]);
        $resPostTec->assertRedirect(route('tickets.show', $ticket->id));

        // 3. Verificación de TicketMensaje como modelo
        $mensajesForo = \App\Models\TicketMensaje::where('ticket_id', $ticket->id)->orderBy('id')->get();
        $this->assertCount(2, $mensajesForo);

        // 4. Renderizado del foro en la vista tickets.show
        $resShow = $this->actingAs($tec)->get(route('tickets.show', $ticket->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Hilo de Seguimiento y Discusión Técnica');
        $resShow->assertSee('Revisor Foro');
        $resShow->assertSee('Tecnico Foro');
        $resShow->assertSee('Adjunto foto del fusible');
        $resShow->assertSee('Recibido en banco de pruebas');
        $resShow->assertSee('Fotos adjuntas');
    }

    public function test_vinculacion_transaccional_entre_respuestas_de_tickets_y_movimientos_de_inventario(): void
    {
        $rolTec = Rol::firstOrCreate(['rol' => 'tec']);
        $rolReg = Rol::firstOrCreate(['rol' => 'registered']);

        $tec = Usuario::create([
            'email' => 'tec_mov_test@lia.com',
            'nombre' => 'Tecnico Movimientos',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $tec->roles()->sync([$rolReg->id, $rolTec->id]);

        $tipo = \App\Models\Tipo::first();
        $salaOrigen = \App\Models\Ubicacion::firstOrCreate(['nombre' => 'Laboratorio 1']);
        $salaTaller = \App\Models\Ubicacion::firstOrCreate(['nombre' => 'Taller de Hardware']);

        $elemento = \App\Models\Elemento::create([
            'nro_lia' => 'LIA-MOV-LINK-01',
            'tipo_id' => $tipo->id,
            'marca' => 'HP',
            'modelo' => 'ProDesk 600',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        $ticket = \App\Models\Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $salaOrigen->id,
            'puesto' => 'P-01',
            'user_id' => $tec->id,
            'fecha' => now(),
            'solicitante' => 'Docente Pruebas',
            'comentario' => 'Placa madre no entrega señal de video por HDMI',
        ]);

        // 1. Técnico comenta y activa generación de movimiento hacia Taller de Hardware
        $resPost = $this->actingAs($tec)->post(route('tickets.comentar', $ticket->id), [
            'mensaje' => 'Se retira el equipo de Lab 1 y se ingresa a banco de prueba en Taller.',
            'generar_movimiento' => '1',
            'ubicacion_id' => $salaTaller->id,
            'puesto' => 'Banco 03',
        ]);
        $resPost->assertRedirect(route('tickets.show', $ticket->id));

        // 2. Verificar existencia del movimiento enlazado al ticket
        $movimientoGenerado = \App\Models\Movimiento::where('elemento_id', $elemento->id)
            ->where('tipo_movimiento', 'interno')
            ->where('ubicacion_id', $salaTaller->id)
            ->where('ticket_id', $ticket->id)
            ->first();
        $this->assertNotNull($movimientoGenerado);
        $this->assertEquals('Banco 03', $movimientoGenerado->puesto);
        $this->assertEquals($ticket->id, $movimientoGenerado->ticket_id);

        // 3. Verificar que el comentario en el hilo apunta al movimiento_id
        $comentario = \App\Models\TicketComentario::where('ticket_id', $ticket->id)
            ->where('user_id', $tec->id)
            ->first();
        $this->assertNotNull($comentario);
        $this->assertEquals($movimientoGenerado->id, $comentario->movimiento_id);
        $this->assertEquals($comentario->movimiento->id, $movimientoGenerado->id);

        // 4. Verificar renderizado en la vista tickets.show
        $resShow = $this->actingAs($tec)->get(route('tickets.show', $ticket->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('📦 Traslado Físico Vinculado');
        $resShow->assertSee('Taller de Hardware');
        $resShow->assertSee('Banco 03');
        $resShow->assertSee('Movimiento #' . $movimientoGenerado->id);
    }

    public function test_arquitectura_datos_y_persistencia_incremental_de_auditorias(): void
    {
        $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
        $rolReg = Rol::firstOrCreate(['rol' => 'registered']);

        $auditor = Usuario::create([
            'email' => 'auditor_test@lia.com',
            'nombre' => 'Auditor Salas',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $auditor->roles()->sync([$rolReg->id, $rolRev->id]);

        $tipo = \App\Models\Tipo::first();
        $sala = \App\Models\Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Sistemas']);

        $elemento1 = \App\Models\Elemento::create([
            'nro_lia' => 'LIA-AUD-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'modelo' => 'OptiPlex',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        $elemento2 = \App\Models\Elemento::create([
            'nro_lia' => 'LIA-AUD-02',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'modelo' => 'OptiPlex',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        // 1. Iniciar sesión de auditoría en progreso
        $resStore = $this->actingAs($auditor)->postJson(route('auditorias.store'), [
            'ubicacion_id' => $sala->id,
            'observaciones' => 'Auditoría semestral física de puestos',
        ]);
        $resStore->assertStatus(200);
        $resStore->assertJson(['success' => true]);

        $auditoria = \App\Models\Auditoria::where('ubicacion_id', $sala->id)->where('estado', 'en_progreso')->first();
        $this->assertNotNull($auditoria);
        $this->assertEquals('en_progreso', $auditoria->estado);

        // 2. Guardado incremental del primer puesto: OK
        $resDetalle1 = $this->actingAs($auditor)->postJson(route('auditorias.guardarDetalle', $auditoria->id), [
            'puesto' => 'Puesto 01',
            'elemento_id' => $elemento1->id,
            'estado' => 'ok',
        ]);
        $resDetalle1->assertStatus(200);
        $resDetalle1->assertJson([
            'success' => true,
            'totales' => [
                'total_auditados' => 1,
                'total_ok' => 1,
                'total_inconsistencias' => 0,
            ],
        ]);

        // 3. Guardado incremental del segundo puesto: Inconsistencia (Rotura)
        $resDetalle2 = $this->actingAs($auditor)->postJson(route('auditorias.guardarDetalle', $auditoria->id), [
            'puesto' => 'Puesto 02',
            'elemento_id' => $elemento2->id,
            'estado' => 'inconsistencia',
            'tipo_discrepancia' => 'rotura',
            'observaciones' => 'Pantalla rota y cable de poder desprendido',
        ]);
        $resDetalle2->assertStatus(200);
        $resDetalle2->assertJson([
            'success' => true,
            'totales' => [
                'total_auditados' => 2,
                'total_ok' => 1,
                'total_inconsistencias' => 1,
            ],
        ]);

        // 4. Finalizar sesión de auditoría
        $resFinalizar = $this->actingAs($auditor)->postJson(route('auditorias.finalizar', $auditoria->id));
        $resFinalizar->assertStatus(200);
        $resFinalizar->assertJson(['success' => true]);

        $auditoria->refresh();
        $this->assertEquals('finalizada', $auditoria->estado);
        $this->assertEquals(2, $auditoria->total_auditados);
        $this->assertEquals(1, $auditoria->total_ok);
        $this->assertEquals(1, $auditoria->total_inconsistencias);
    }

    public function test_interfaz_interactiva_de_auditoria_grilla_de_puestos_y_apertura_ticket_in_situ(): void
    {
        $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
        $rolReg = Rol::firstOrCreate(['rol' => 'registered']);

        $auditor = Usuario::create([
            'email' => 'auditor_grilla@lia.com',
            'nombre' => 'Auditor Grilla',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);
        $auditor->roles()->sync([$rolReg->id, $rolRev->id]);

        $tipo = \App\Models\Tipo::first();
        $sala = \App\Models\Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Redes y Puestos']);

        $elementoPuesto = \App\Models\Elemento::create([
            'nro_lia' => 'LIA-PUESTO-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'modelo' => 'Precision',
            'origen' => 'compra',
            'fecha_ingreso' => now(),
            'activo' => true,
        ]);

        \App\Models\Movimiento::create([
            'elemento_id' => $elementoPuesto->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $sala->id,
            'puesto' => 'Puesto 01',
            'user_id' => $auditor->id,
            'fecha' => now(),
            'comentario' => 'Asignado a Puesto 01',
        ]);

        // 1. Cargar pantalla interactiva de auditoría
        $resView = $this->actingAs($auditor)->get(route('auditorias.create', ['ubicacion_id' => $sala->id]));
        $resView->assertStatus(200);
        $resView->assertSee('Auditoría Física de Ubicación: Laboratorio Redes y Puestos');
        $resView->assertSee('Grilla de Puestos y Mesones');
        $resView->assertSee('Puesto 01');
        $resView->assertSee('LIA-PUESTO-01');

        $auditoria = \App\Models\Auditoria::where('ubicacion_id', $sala->id)->first();
        $this->assertNotNull($auditoria);

        // 2. Apertura de Ticket In Situ derivado de inconsistencia
        $resTicket = $this->actingAs($auditor)->postJson(route('auditorias.crearTicketInconsistencia', $auditoria->id), [
            'puesto' => 'Puesto 01',
            'elemento_id' => $elementoPuesto->id,
            'tipo_discrepancia' => 'rotura',
            'descripcion' => 'Monitor con líneas verticales y parpadeo continuo en panel IPS',
        ]);
        $resTicket->assertStatus(200);
        $resTicket->assertJson([
            'success' => true,
            'elemento_id' => $elementoPuesto->id,
            'puesto' => 'Puesto 01',
        ]);

        $ticketId = $resTicket->json('ticket_id');
        $this->assertNotNull($ticketId);

        $ticket = \App\Models\Movimiento::find($ticketId);
        $this->assertNotNull($ticket);
        $this->assertEquals('reparacion', $ticket->tipo_movimiento);
        $this->assertEquals('abierto', $ticket->estado_ticket);
        $this->assertStringContainsString('ROTURA', $ticket->comentario);

        // 3. Verificar detalle de auditoría actualizado
        $detalle = \App\Models\AuditoriaDetalle::where('auditoria_id', $auditoria->id)
            ->where('elemento_id', $elementoPuesto->id)
            ->first();
        $this->assertNotNull($detalle);
        $this->assertEquals('inconsistencia', $detalle->estado);
        $this->assertEquals('rotura', $detalle->tipo_discrepancia);
        $this->assertEquals($ticketId, $detalle->ticket_id);
    }

    public function test_archivo_historico_de_auditorias_y_emision_de_informe_oficial(): void
    {
        $auditor = Usuario::create([
            'nombre' => 'Auditor Oficial Test',
            'email' => 'auditor_oficial@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $auditor->roles()->attach(Rol::where('rol', 'rev')->first());

        $otroAuditor = Usuario::create([
            'nombre' => 'Otro Auditor Test',
            'email' => 'otro_auditor@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $otroAuditor->roles()->attach(Rol::where('rol', 'rev')->first());

        $lab1 = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Alpha Audit'], ['descripcion' => 'Sala 1']);
        $lab2 = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Beta Audit'], ['descripcion' => 'Sala 2']);

        // Crear auditoría 1 (en Lab 1 por auditor con inconsistencia)
        $auditoria1 = \App\Models\Auditoria::create([
            'ubicacion_id' => $lab1->id,
            'user_id' => $auditor->id,
            'fecha' => now()->subDays(2),
            'estado' => 'finalizada',
            'total_auditados' => 2,
            'total_ok' => 1,
            'total_inconsistencias' => 1,
            'observaciones' => 'Auditoría semestral con un monitor averiado en puesto 01.',
        ]);

        $tipo = Tipo::firstOrCreate(['nombre' => 'Pantalla Test'], ['categoria' => 'periferico']);
        $elementoOk = Elemento::create(['tipo_id' => $tipo->id, 'modelo' => 'Pantalla Samsung 24', 'nro_serie' => 'SAM-9988']);
        $elementoRotura = Elemento::create(['tipo_id' => $tipo->id, 'modelo' => 'Pantalla LG 27', 'nro_serie' => 'LG-7766']);

        $ticket = Movimiento::create([
            'elemento_id' => $elementoRotura->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $lab1->id,
            'puesto' => 'Puesto 01',
            'user_id' => $auditor->id,
            'fecha' => now()->subDays(2),
            'solicitante' => $auditor->nombre,
            'comentario' => '[AUDITORÍA INCONSISTENCIA - ROTURA] Pantalla rota',
        ]);

        \App\Models\AuditoriaDetalle::create([
            'auditoria_id' => $auditoria1->id,
            'elemento_id' => $elementoOk->id,
            'puesto' => 'Puesto 02',
            'estado' => 'ok',
        ]);

        \App\Models\AuditoriaDetalle::create([
            'auditoria_id' => $auditoria1->id,
            'elemento_id' => $elementoRotura->id,
            'puesto' => 'Puesto 01',
            'estado' => 'inconsistencia',
            'tipo_discrepancia' => 'rotura',
            'ticket_id' => $ticket->id,
            'observaciones' => 'Pantalla con panel roto',
        ]);

        // Crear auditoría 2 (en Lab 2 por otro auditor sin inconsistencias)
        $auditoria2 = \App\Models\Auditoria::create([
            'ubicacion_id' => $lab2->id,
            'user_id' => $otroAuditor->id,
            'fecha' => now()->subDay(),
            'estado' => 'en_progreso',
            'total_auditados' => 5,
            'total_ok' => 5,
            'total_inconsistencias' => 0,
        ]);

        // 1. Consultar bandeja histórica general
        $resIndex = $this->actingAs($auditor)->get(route('auditorias.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Archivo Histórico de Auditorías Físicas');
        $resIndex->assertSee('Laboratorio Alpha Audit');
        $resIndex->assertSee('Laboratorio Beta Audit');
        $resIndex->assertSee('Auditor Oficial Test');
        $resIndex->assertSee('Otro Auditor Test');

        // 2. Filtrar por sala (Lab 1)
        $resFilterLab = $this->actingAs($auditor)->get(route('auditorias.index', ['ubicacion_id' => $lab1->id]));
        $resFilterLab->assertStatus(200);
        $resFilterLab->assertSee(route('auditorias.show', $auditoria1->id));
        $resFilterLab->assertDontSee(route('auditorias.show', $auditoria2->id));

        // 3. Filtrar por auditor actuante
        $resFilterAuditor = $this->actingAs($auditor)->get(route('auditorias.index', ['user_id' => $otroAuditor->id]));
        $resFilterAuditor->assertStatus(200);
        $resFilterAuditor->assertSee(route('auditorias.show', $auditoria2->id));
        $resFilterAuditor->assertDontSee(route('auditorias.show', $auditoria1->id));

        // 4. Filtrar únicamente auditorías con inconsistencias
        $resFilterInconsistencias = $this->actingAs($auditor)->get(route('auditorias.index', ['con_inconsistencias' => '1']));
        $resFilterInconsistencias->assertStatus(200);
        $resFilterInconsistencias->assertSee(route('auditorias.show', $auditoria1->id));
        $resFilterInconsistencias->assertDontSee(route('auditorias.show', $auditoria2->id));

        // 5. Visualizar Informe Oficial de Auditoría
        $resShow = $this->actingAs($auditor)->get(route('auditorias.show', $auditoria1->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Acta e Informe Oficial de Auditoría Física de Ubicación');
        $resShow->assertSee('Laboratorio Alpha Audit');
        $resShow->assertSee('Auditor Oficial Test');
        $resShow->assertSee('SAM-9988');
        $resShow->assertSee('LG-7766');
        $resShow->assertSee('ROTURA');
        $resShow->assertSee("#{$ticket->id}");
        $resShow->assertSee('Certificación y Conformidad de Relevamiento');
        $resShow->assertSee('Auditor Relevador LIA');
        $resShow->assertSee('Imprimir / Exportar a PDF');
    }

    public function test_soporte_bd_y_gestion_crud_de_avisos_de_seguridad_app_admin(): void
    {
        $appAdmin = Usuario::create([
            'nombre' => 'App Admin Avisos Test',
            'email' => 'admin_avisos@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $appAdmin->roles()->attach(Rol::where('rol', 'app_admin')->first());

        $revisor = Usuario::create([
            'nombre' => 'Revisor Avisos Test',
            'email' => 'revisor_avisos@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $revisor->roles()->attach(Rol::where('rol', 'rev')->first());

        // 1. Usuario sin rol app_admin no puede acceder a la gestión de avisos (403)
        $resForbid = $this->actingAs($revisor)->get(route('app_admin.avisos'));
        $resForbid->assertStatus(403);

        // 2. Administrador accede correctamente al panel de avisos
        $resIndex = $this->actingAs($appAdmin)->get(route('app_admin.avisos'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Concientización y Buenas Prácticas Informáticas');

        // 3. Crear un nuevo aviso de seguridad
        $resStore = $this->actingAs($appAdmin)->post(route('app_admin.avisos.store'), [
            'titulo' => 'Cierre de Sesión Obligatorio',
            'descripcion' => 'Nunca dejes tu cuenta de Nexo LIA abierta en computadoras de acceso público.',
            'severidad' => 'warning',
            'icono' => 'lock',
            'orden' => 10,
            'activo' => 1,
        ]);
        $resStore->assertRedirect(route('app_admin.avisos'));

        $this->assertDatabaseHas('avisos_seguridad', [
            'titulo' => 'Cierre de Sesión Obligatorio',
            'severidad' => 'warning',
            'activo' => true,
        ]);

        $aviso = \App\Models\AvisoSeguridad::where('titulo', 'Cierre de Sesión Obligatorio')->first();
        $this->assertNotNull($aviso);

        // 4. Actualizar el aviso
        $resUpdate = $this->actingAs($appAdmin)->put(route('app_admin.avisos.update', $aviso->id), [
            'titulo' => 'Cierre de Sesión Estricto y Bloqueo',
            'descripcion' => 'Bloquea la terminal o presiona Win + L inmediatamente al retirarte.',
            'severidad' => 'danger',
            'icono' => 'shield',
            'orden' => 2,
        ]);
        $resUpdate->assertRedirect(route('app_admin.avisos'));

        $aviso->refresh();
        $this->assertEquals('Cierre de Sesión Estricto y Bloqueo', $aviso->titulo);
        $this->assertEquals('danger', $aviso->severidad);
        $this->assertEquals(2, $aviso->orden);

        // 5. Toggle de activación / desactivación
        $resToggle = $this->actingAs($appAdmin)->post(route('app_admin.avisos.toggle', $aviso->id));
        $aviso->refresh();
        $this->assertFalse($aviso->activo);

        // 6. Eliminar el aviso
        $resDestroy = $this->actingAs($appAdmin)->delete(route('app_admin.avisos.destroy', $aviso->id));
        $resDestroy->assertRedirect(route('app_admin.avisos'));
        $this->assertDatabaseMissing('avisos_seguridad', ['id' => $aviso->id]);
    }

    public function test_panel_carrusel_flotante_concientizacion_en_landing_y_welcome_registered(): void
    {
        // 1. Crear avisos de prueba (uno activo y uno inactivo)
        $avisoActivo = \App\Models\AvisoSeguridad::create([
            'titulo' => 'Consejo Activo de Prueba Ciberseguridad',
            'descripcion' => 'Recomendación vigente para toda la comunidad académica del LIA.',
            'severidad' => 'warning',
            'icono' => 'shield',
            'orden' => 1,
            'activo' => true,
        ]);

        $avisoInactivo = \App\Models\AvisoSeguridad::create([
            'titulo' => 'Aviso Oculto Desactivado Test',
            'descripcion' => 'Este consejo no debe figurar en ningún carrusel público ni de bienvenida.',
            'severidad' => 'info',
            'icono' => 'lock',
            'orden' => 99,
            'activo' => false,
        ]);

        // 2. Comprobar landing page pública (/)
        $resLanding = $this->get('/');
        $resLanding->assertStatus(200);
        $resLanding->assertSee('Cultura de Seguridad LIA');
        $resLanding->assertSee('Consejo Activo de Prueba Ciberseguridad');
        $resLanding->assertSee('Recomendación vigente para toda la comunidad académica del LIA.');
        $resLanding->assertDontSee('Aviso Oculto Desactivado Test');
        // Debe encontrarse entre el Hero y la sección Cómo funciona
        $resLanding->assertSeeInOrder([
            'BIENVENIDO AL SISTEMA LIA',
            'Cultura de Seguridad LIA',
            'COMO FUNCIONA',
        ]);

        // 3. Comprobar pantalla de bienvenida para usuario con rol registered exclusivo
        $userRegistered = Usuario::create([
            'nombre' => 'Alumno Registered Test',
            'email' => 'alumno_reg@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);
        $userRegistered->roles()->attach(Rol::where('rol', 'registered')->first());

        $resBienvenida = $this->actingAs($userRegistered)->get(route('bienvenida'));
        $resBienvenida->assertStatus(200);
        $resBienvenida->assertSee('Cultura de Ciberseguridad LIA');
        $resBienvenida->assertSee('Consejo Activo de Prueba Ciberseguridad');
        $resBienvenida->assertDontSee('Aviso Oculto Desactivado Test');
    }

    public function test_soporte_bd_y_modelo_para_borrado_de_huella_digital_y_usuarios_historicos(): void
    {
        // 1. Crear usuario desvinculado hace 7 años con datos personales sensibles
        $exColaborador = Usuario::create([
            'nombre' => 'Juan Perez Historico',
            'email' => 'juan.perez@lia.com',
            'password' => bcrypt('password123'),
            'dni' => '28999111',
            'fecha_nacimiento' => '1982-03-15',
            'carrera' => 'Licenciatura en Computación',
            'cargo_actual' => 'Ayudante Técnico',
            'whatsapp' => '+5492641122334',
            'direccion' => 'Av. Ignacio de la Roza 590',
            'activo' => false,
            'fecha_egreso' => now()->subYears(7),
        ]);
        $exColaborador->roles()->attach(Rol::where('rol', 'rev')->first());

        // 2. Asociar movimiento de inventario histórico para verificar integridad referencial
        $tipo = Tipo::firstOrCreate(['nombre' => 'Gabinete Test'], ['categoria' => 'equipo']);
        $elemento = Elemento::create(['tipo_id' => $tipo->id, 'modelo' => 'Server Torre HP', 'nro_serie' => 'HP-7711']);
        $lab = Ubicacion::firstOrCreate(['nombre' => 'Sala Servidores Hist'], ['descripcion' => 'Rack']);

        $movimiento = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'alta',
            'ubicacion_id' => $lab->id,
            'user_id' => $exColaborador->id,
            'fecha' => now()->subYears(7),
            'solicitante' => 'Juan Perez Historico',
            'comentario' => 'Alta histórica de equipamiento',
        ]);

        // 3. Verificar detección automática de ex personal > 6 años (Ley 25.326)
        $this->assertTrue($exColaborador->isExPersonalMayor6Anios());
        $this->assertFalse($exColaborador->huella_borrada);
        $this->assertFalse($exColaborador->esAnonimizado());

        // 4. Ejecutar el borrado irreversible de la huella digital
        $resultado = $exColaborador->anonimizarHuellaDigital();
        $this->assertTrue($resultado);

        // 5. Verificar blanqueo de campos sensibles y preservación de ID
        $exColaborador->refresh();
        $this->assertEquals("Usuario Histórico #{$exColaborador->id}", $exColaborador->nombre);
        $this->assertEquals("historico_{$exColaborador->id}@lia.local", $exColaborador->email);
        $this->assertNull($exColaborador->dni);
        $this->assertNull($exColaborador->whatsapp);
        $this->assertNull($exColaborador->direccion);
        $this->assertNull($exColaborador->fecha_nacimiento);
        $this->assertNull($exColaborador->carrera);
        $this->assertNull($exColaborador->carrera_id);
        $this->assertEquals('Ex Colaborador Histórico', $exColaborador->cargo_actual);
        $this->assertTrue($exColaborador->huella_borrada);
        $this->assertNotNull($exColaborador->anonimizado_el);
        $this->assertFalse($exColaborador->activo);
        $this->assertTrue($exColaborador->esAnonimizado());
        $this->assertTrue($exColaborador->esHistorico());
        $this->assertCount(0, $exColaborador->roles);

        // 6. Integridad de trazabilidad en movimientos preservada
        $movimiento->refresh();
        $this->assertEquals($exColaborador->id, $movimiento->user_id);
        $this->assertEquals("Usuario Histórico #{$exColaborador->id}", $movimiento->usuario->nombre);
    }
}







