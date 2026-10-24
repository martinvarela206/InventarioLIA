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

    public function test_catalogo_roles_contiene_los_6_roles_oficiales(): void
    {
        $rolesEsperados = ['user_admin', 'app_admin', 'item_admin', 'tec', 'rev', 'registered'];
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
        $responseShow->assertSee('Listado de Inventario Completo');

        // Reportes específicos vinculados a KPI cards del dashboard
        $responseOp = $this->actingAs($admin)->get(route('reportes.show', 'operativos'));
        $responseOp->assertStatus(200);
        $responseOp->assertSee('Activos Operativos en Salas');

        $responsePrest = $this->actingAs($admin)->get(route('reportes.show', 'prestados'));
        $responsePrest->assertStatus(200);
        $responsePrest->assertSee('Equipos en Préstamo Externo');

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
        $responseHabilitado->assertSee('Centro de Control Operativo LIA');
    }

    public function test_landing_page_posee_boton_registro_y_permite_registro_de_nuevos_usuarios(): void
    {
        // 1. Visitante no autenticado en la landing page
        $responseLanding = $this->get('/');
        $responseLanding->assertStatus(200);
        $responseLanding->assertSee(route('register'));
        $responseLanding->assertSee('Registrarse');
        $responseLanding->assertSee('Iniciar Sesión');

        // 2. Acceder al formulario de registro
        $responseForm = $this->get(route('register'));
        $responseForm->assertStatus(200);
        $responseForm->assertSee('Registro de Usuario');

        // 3. Completar el registro
        $responsePost = $this->post(route('register'), [
            'nombre' => 'Nuevo Estudiante Test',
            'email' => 'nuevo_estudiante@exactas.unsj.edu.ar',
            'dni' => '40999888',
            'carrera' => 'Licenciatura en Ciencias de la Computación',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
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
        $responseWelcome->assertSee('Próximamente');
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

        // 1. Acceder al listado de usuarios y verificar presencia del rol app_admin y badge de 6 roles
        $responseList = $this->actingAs($admin)->get(route('users.index'));
        $responseList->assertStatus(200);
        $responseList->assertSee('6 Roles Oficiales');
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
        $responseApp->assertSee('Panel de Gestión de la Aplicación');
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
        $responseIndex->assertSee('Salas, Laboratorios y Depósitos del LIA');

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
}


