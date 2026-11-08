<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Carbon\Carbon;

class ConsistenciaTemporalMovimientosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ultimo_movimiento_coincide_estrictamente_con_la_cabecera_del_timeline(): void
    {
        $tipo = Tipo::first();
        $ubicacion1 = Ubicacion::first();
        $ubicacion2 = Ubicacion::skip(1)->first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-TEMP-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Workstation Precision',
        ]);

        $m1 = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion1->id,
            'user_id' => $tec->id,
            'fecha' => now()->subDays(10),
            'comentario' => 'Movimiento inicial en sala 1',
        ]);

        $m2 = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion2->id,
            'user_id' => $tec->id,
            'fecha' => now()->subDays(5),
            'comentario' => 'Traslado a sala 2',
        ]);

        $elemento->refresh();

        // 1. Verificar coincidencia entre latestOfMany y timeline
        $this->assertEquals($m2->id, $elemento->ultimoMovimiento->id);
        $this->assertEquals($m2->id, $elemento->movimientos->first()->id);
        $this->assertEquals($ubicacion2->id, $elemento->ultimoMovimiento->ubicacion_id);
    }

    public function test_form_requests_rechazan_fechas_futuras_en_todos_los_flujos(): void
    {
        $admin = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'asset_admin'))->first()
            ?? Usuario::whereHas('roles', fn($q) => $q->where('rol', 'item_admin'))->first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        // 1. Alta de elemento con fecha_adquisicion futura
        $responseEl = $this->actingAs($admin)->post(route('elementos.store'), [
            'nro_lia' => 'TST-FUT-EL',
            'tipo_id' => $tipo->id,
            'modelo' => 'Laptop',
            'cantidad' => 1,
            'fecha_adquisicion' => Carbon::now()->addDays(5)->toDateString(),
        ]);
        $responseEl->assertSessionHasErrors(['fecha_adquisicion']);

        // 2. Registro de préstamo con fecha futura
        $el = Elemento::create([
            'nro_lia' => 'TST-FUT-MOV',
            'tipo_id' => $tipo->id,
            'modelo' => 'Laptop',
        ]);
        Movimiento::create([
            'elemento_id' => $el->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $responseMov = $this->actingAs($tec)->post(route('movimientos.store'), [
            'elemento_id' => $el->id,
            'tipo_movimiento' => 'externo_prestamo',
            'solicitante' => 'Docente',
            'fecha_prestamo' => Carbon::now()->addDays(2)->toDateString(),
        ]);
        $responseMov->assertSessionHasErrors(['fecha_prestamo']);

        // 3. Devolución de préstamo con fecha futura
        $prestamo = Movimiento::create([
            'elemento_id' => $el->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'fecha' => now()->subDay(),
            'solicitante' => 'Docente',
            'fecha_prestamo' => now()->subDay()->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $responseDev = $this->actingAs($tec)->post(route('movimientos.devolucion', $prestamo->id), [
            'ubicacion_id' => $ubicacion->id,
            'fecha_devolucion' => Carbon::now()->addDays(3)->toDateString(),
        ]);
        $responseDev->assertSessionHasErrors(['fecha_devolucion']);
    }
}
