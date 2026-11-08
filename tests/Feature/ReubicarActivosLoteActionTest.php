<?php

namespace Tests\Feature;

use App\Actions\Ubicaciones\ReubicarActivosLoteAction;
use App\Enums\EstadoElemento;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReubicarActivosLoteActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_reubicacion_en_lote_a_destino_comun_saca_activos_de_limbo(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        // Sala archivada / histórica
        $salaVieja = Ubicacion::create([
            'nombre' => 'Sala Desmantelada Histórica',
            'activa' => false,
        ]);

        $salaNueva = Ubicacion::create([
            'nombre' => 'Laboratorio Nuevo de IA',
            'activa' => true,
        ]);

        $el1 = Elemento::create(['nro_lia' => 'TST-REUB-01', 'tipo_id' => $tipo->id, 'modelo' => 'PC1']);
        $el2 = Elemento::create(['nro_lia' => 'TST-REUB-02', 'tipo_id' => $tipo->id, 'modelo' => 'PC2']);

        Movimiento::create(['elemento_id' => $el1->id, 'tipo_movimiento' => 'interno', 'ubicacion_id' => $salaVieja->id, 'user_id' => $tec->id, 'fecha' => now()]);
        Movimiento::create(['elemento_id' => $el2->id, 'tipo_movimiento' => 'interno', 'ubicacion_id' => $salaVieja->id, 'user_id' => $tec->id, 'fecha' => now()]);

        // Ambos activos están en limbo
        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $el1->fresh()->estado);
        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $el2->fresh()->estado);

        $action = app(ReubicarActivosLoteAction::class);
        $res = $action->execute([$el1->id, $el2->id], $salaNueva->id, $tec, 'Traslado masivo de equipos de sala desmantelada');

        $this->assertEquals(2, $res['total_reubicados']);
        $this->assertCount(2, $res['movimientos']);

        // Ambos activos ya NO están en limbo, ahora son operativos en la sala nueva
        $el1->refresh();
        $el2->refresh();
        $this->assertEquals(EstadoElemento::OPERATIVO, $el1->estado);
        $this->assertEquals(EstadoElemento::OPERATIVO, $el2->estado);
        $this->assertEquals($salaNueva->id, $el1->ultimoMovimiento->ubicacion_id);
        $this->assertEquals($salaNueva->id, $el2->ultimoMovimiento->ubicacion_id);
    }

    public function test_reubicacion_discriminada_permite_destinos_y_puestos_diferentes(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $salaA = Ubicacion::create(['nombre' => 'Sala Servidores', 'activa' => true]);
        $salaB = Ubicacion::create(['nombre' => 'Depósito Central', 'activa' => true]);
        $salaInactiva = Ubicacion::create(['nombre' => 'Sala Vieja', 'activa' => false]);

        $el1 = Elemento::create(['nro_lia' => 'TST-DISCR-01', 'tipo_id' => $tipo->id, 'modelo' => 'Switch']);
        $el2 = Elemento::create(['nro_lia' => 'TST-DISCR-02', 'tipo_id' => $tipo->id, 'modelo' => 'Monitor']);

        Movimiento::create(['elemento_id' => $el1->id, 'tipo_movimiento' => 'interno', 'ubicacion_id' => $salaInactiva->id, 'user_id' => $tec->id, 'fecha' => now()]);
        Movimiento::create(['elemento_id' => $el2->id, 'tipo_movimiento' => 'interno', 'ubicacion_id' => $salaInactiva->id, 'user_id' => $tec->id, 'fecha' => now()]);

        $items = [
            ['elemento_id' => $el1->id, 'ubicacion_id' => $salaA->id, 'puesto' => 'Rack 01'],
            ['elemento_id' => $el2->id, 'ubicacion_id' => $salaB->id, 'puesto' => 'Estante 3'],
        ];

        $res = app(ReubicarActivosLoteAction::class)->execute($items, null, $tec);

        $this->assertEquals(2, $res['total_reubicados']);

        $el1->refresh();
        $el2->refresh();
        $this->assertEquals(EstadoElemento::OPERATIVO, $el1->estado);
        $this->assertEquals(EstadoElemento::OPERATIVO, $el2->estado);
        $this->assertEquals($salaA->id, $el1->ultimoMovimiento->ubicacion_id);
        $this->assertEquals('Rack 01', $el1->ultimoMovimiento->puesto);
        $this->assertEquals($salaB->id, $el2->ultimoMovimiento->ubicacion_id);
        $this->assertEquals('Estante 3', $el2->ultimoMovimiento->puesto);
    }

    public function test_rechaza_reubicar_a_sala_inactiva(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();
        $salaInactiva = Ubicacion::create(['nombre' => 'Sala Inactiva Destino', 'activa' => false]);

        $el = Elemento::create(['nro_lia' => 'TST-DEST-INACT', 'tipo_id' => $tipo->id, 'modelo' => 'PC']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('se encuentra archivada e inactiva');

        app(ReubicarActivosLoteAction::class)->execute([$el->id], $salaInactiva->id, $tec);
    }

    public function test_rechaza_reubicar_activo_en_baja(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();
        $salaDestino = Ubicacion::create(['nombre' => 'Sala Valida', 'activa' => true]);

        $el = Elemento::create(['nro_lia' => 'TST-REUB-BAJA', 'tipo_id' => $tipo->id, 'modelo' => 'PC Quemada']);
        Movimiento::create(['elemento_id' => $el->id, 'tipo_movimiento' => 'baja', 'user_id' => $tec->id, 'fecha' => now()]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('se encuentra dado de baja patrimonial y no puede ser reubicado');

        app(ReubicarActivosLoteAction::class)->execute([$el->id], $salaDestino->id, $tec);
    }

    public function test_rechaza_reubicacion_con_items_vacios(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Debe especificar al menos un activo para reubicar');

        app(ReubicarActivosLoteAction::class)->execute([]);
    }
}
