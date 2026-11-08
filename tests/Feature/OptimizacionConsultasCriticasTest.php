<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Models\UsuarioTrayectoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptimizacionConsultasCriticasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'user_admin']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
        Rol::firstOrCreate(['rol' => 'rev']);
    }

    public function test_elegibles_borrado_huella_filtra_estrictamente_a_nivel_sql(): void
    {
        // 1. Usuario activo con egreso viejo -> NO elegible
        Usuario::factory()->create([
            'activo' => true,
            'fecha_egreso' => now()->subYears(7),
            'huella_borrada' => false,
        ]);

        // 2. Usuario inactivo con huella ya borrada -> NO elegible
        Usuario::factory()->create([
            'activo' => false,
            'fecha_egreso' => now()->subYears(8),
            'huella_borrada' => true,
        ]);

        // 3. Usuario inactivo con egreso menor a 6 años -> NO elegible
        Usuario::factory()->create([
            'activo' => false,
            'fecha_egreso' => now()->subYears(3),
            'huella_borrada' => false,
        ]);

        // 4. Usuario inactivo con egreso mayor a 6 años -> ELEGIBLE
        $elegibleA = Usuario::factory()->create([
            'activo' => false,
            'fecha_egreso' => now()->subYears(7),
            'huella_borrada' => false,
        ]);

        // 5. Usuario inactivo sin fecha_egreso pero con trayectoria cerrada hace 8 años -> ELEGIBLE
        $elegibleB = Usuario::factory()->create([
            'activo' => false,
            'fecha_egreso' => null,
            'huella_borrada' => false,
        ]);
        UsuarioTrayectoria::create([
            'user_id' => $elegibleB->id,
            'cargo' => 'Pasante',
            'fecha_inicio' => now()->subYears(10),
            'fecha_fin' => now()->subYears(8),
        ]);

        // 6. Usuario inactivo sin fecha_egreso pero con trayectoria abierta (sin fecha_fin) -> NO elegible
        $noElegibleC = Usuario::factory()->create([
            'activo' => false,
            'fecha_egreso' => null,
            'huella_borrada' => false,
        ]);
        UsuarioTrayectoria::create([
            'user_id' => $noElegibleC->id,
            'cargo' => 'Investigador',
            'fecha_inicio' => now()->subYears(8),
            'fecha_fin' => null,
        ]);

        $elegibles = Usuario::elegiblesBorradoHuella();

        $this->assertCount(2, $elegibles);
        $this->assertTrue($elegibles->contains('id', $elegibleA->id));
        $this->assertTrue($elegibles->contains('id', $elegibleB->id));
        $this->assertFalse($elegibles->contains('id', $noElegibleC->id));
    }

    public function test_solicitantes_frecuentes_ordena_por_conteo_real_descendente(): void
    {
        $tipo = Tipo::firstOrCreate(['nombre' => 'Monitor'], ['slug' => 'monitor', 'icono' => 'monitor']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Lab 1'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        // 'Zoe' tiene 5 préstamos (alfabéticamente última)
        for ($i = 0; $i < 5; $i++) {
            Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'externo_prestamo',
                'solicitante' => 'Zoe Frecuente',
                'user_id' => $tec->id,
                'fecha' => now()->subDays(10),
            ]);
        }

        // 'Ana' tiene 1 préstamo (alfabéticamente primera)
        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'solicitante' => 'Ana Ocasional',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(10),
        ]);

        // 'Carlos' tiene 3 préstamos
        for ($i = 0; $i < 3; $i++) {
            Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'externo_prestamo',
                'solicitante' => 'Carlos Medio',
                'user_id' => $tec->id,
                'fecha' => now()->subDays(10),
            ]);
        }

        $response = $this->actingAs($tec)->get(route('movimientos.rapido'));
        $response->assertOk();
        $solicitantes = $response->viewData('solicitantesFrecuentes');

        $this->assertNotNull($solicitantes);
        $this->assertEquals('Zoe Frecuente', $solicitantes->first());
        $this->assertEquals('Carlos Medio', $solicitantes->values()->get(1));
        $this->assertEquals('Ana Ocasional', $solicitantes->values()->get(2));
    }

    public function test_rango_fechas_movimientos_filtra_por_limites_sin_wheredate(): void
    {
        $tipo = Tipo::firstOrCreate(['nombre' => 'Router'], ['slug' => 'router', 'icono' => 'router']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Redes'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);
        $rev = Usuario::factory()->create(['activo' => true]);
        $rev->roles()->attach(Rol::where('rol', 'rev')->first());

        // Movimiento a las 23:30 del 2026-05-10
        $mov1 = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno_traslado',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $rev->id,
            'fecha' => '2026-05-10 23:30:00',
        ]);

        // Movimiento a las 00:15 del 2026-05-11
        $mov2 = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno_traslado',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $rev->id,
            'fecha' => '2026-05-11 00:15:00',
        ]);

        // Filtrar hasta 2026-05-10 (debe incluir el movimiento de las 23:30)
        $response = $this->actingAs($rev)->get(route('movimientos.index', [
            'fecha_desde' => '2026-05-10',
            'fecha_hasta' => '2026-05-10',
        ]));

        $response->assertOk();
        $items = $response->viewData('movimientos');
        $this->assertTrue($items->contains('id', $mov1->id));
        $this->assertFalse($items->contains('id', $mov2->id));
    }

    public function test_cache_de_roles_en_instancia_de_usuario(): void
    {
        $user = Usuario::factory()->create(['activo' => true]);
        $rolTec = Rol::where('rol', 'tec')->first();
        $rolRev = Rol::where('rol', 'rev')->first();

        $user->roles()->attach($rolTec->id);

        // Primera llamada cachea roles
        $this->assertTrue($user->hasRole('tec'));
        $this->assertFalse($user->hasRole('rev'));

        // Adjuntar rol directamente en BD
        $user->roles()->attach($rolRev->id);

        // Sin invalidar, la instancia conserva la caché
        $this->assertFalse($user->hasRole('rev'));

        // Invalidar caché
        $user->flushRolesCache();
        $this->assertTrue($user->hasRole('rev'));
    }
}
