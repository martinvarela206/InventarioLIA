<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Enums\EstadoElemento;

class EstadoElementoDominioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_enum_estado_elemento_posee_los_cinco_estados_canonicos(): void
    {
        $estados = array_map(fn($case) => $case->value, EstadoElemento::cases());
        $this->assertContains('operativo', $estados);
        $this->assertContains('prestado', $estados);
        $this->assertContains('en_reparacion', $estados);
        $this->assertContains('en_limbo_reubicacion', $estados);
        $this->assertContains('baja', $estados);
    }

    public function test_elemento_sin_movimientos_o_con_movimiento_interno_es_operativo(): void
    {
        $tipo = Tipo::where('nombre', '!=', 'basura tecnologica')->first();
        $ubicacion = Ubicacion::where('activa', true)->first();
        $user = Usuario::first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-OP-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'ThinkPad',
        ]);

        $this->assertEquals(EstadoElemento::OPERATIVO, $elemento->estado);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
        ]);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::OPERATIVO, $elemento->estado);
        $this->assertTrue(Elemento::operativos()->where('id', $elemento->id)->exists());
        $this->assertTrue(Elemento::disponibles()->where('id', $elemento->id)->exists());
    }

    public function test_elemento_prestado_activo_resuelve_estado_prestado(): void
    {
        $tipo = Tipo::first();
        $user = Usuario::first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-PR-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'ProBook',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $user->id,
            'fecha' => now(),
            'solicitante' => 'Prof. Perez',
            'fecha_prestamo' => now()->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::PRESTADO, $elemento->estado);
        $this->assertTrue(Elemento::prestadosActivos()->where('id', $elemento->id)->exists());
        $this->assertFalse(Elemento::disponibles()->where('id', $elemento->id)->exists());
    }

    public function test_elemento_en_reparacion_abierto_y_solucionado_resuelve_en_reparacion(): void
    {
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();
        $user = Usuario::first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-REP-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'OptiPlex',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
        ]);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::EN_REPARACION, $elemento->estado);
        $this->assertTrue(Elemento::enReparacion()->where('id', $elemento->id)->exists());

        // Al pasar a solucionado (técnico diagnosticó o ejecutó pero no cerró), sigue en reparación
        $ticket->estado_ticket = 'solucionado';
        $ticket->save();

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::EN_REPARACION, $elemento->estado);
        $this->assertTrue(Elemento::enReparacion()->where('id', $elemento->id)->exists());
        $this->assertFalse(Elemento::disponibles()->where('id', $elemento->id)->exists());
    }

    public function test_elemento_en_sala_inactiva_resuelve_estado_limbo_reubicacion(): void
    {
        $tipo = Tipo::first();
        $user = Usuario::first();

        $salaInactiva = Ubicacion::create([
            'nombre' => 'Laboratorio Historico Desmantelado',
            'activa' => false,
        ]);

        $elemento = Elemento::create([
            'nro_lia' => 'TST-LIMBO-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Servidor Legacy',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $salaInactiva->id,
            'user_id' => $user->id,
            'fecha' => now(),
        ]);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $elemento->estado);
        $this->assertTrue(Elemento::enLimbo()->where('id', $elemento->id)->exists());
        $this->assertFalse(Elemento::disponibles()->where('id', $elemento->id)->exists());
    }

    public function test_elemento_en_baja_o_basura_tecnologica_resuelve_estado_baja(): void
    {
        $tipo = Tipo::first();
        $user = Usuario::first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-BAJA-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Monitor Quemado',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'baja',
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Baja patrimonial definitiva',
        ]);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::BAJA, $elemento->estado);
        $this->assertTrue(Elemento::obsoletos()->where('id', $elemento->id)->exists());
        $this->assertFalse(Elemento::disponibles()->where('id', $elemento->id)->exists());
    }

    public function test_presenter_renderiza_badge_conforme_al_estado_canonico(): void
    {
        $tipo = Tipo::first();
        $elemento = Elemento::create([
            'nro_lia' => 'TST-BADGE-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Notebook HP',
        ]);

        $badge = $elemento->presenter()->statusBadge();
        $this->assertStringContainsString('Sin historial', $badge);

        $ubicacion = Ubicacion::where('activa', true)->first();
        $user = Usuario::first();

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
        ]);

        $elemento->refresh();
        $badgeOperativo = $elemento->presenter()->statusBadge();
        $this->assertStringContainsString('En Sala / Operativo', $badgeOperativo);
        $this->assertStringContainsString('bg-emerald-100', $badgeOperativo);
    }
}
