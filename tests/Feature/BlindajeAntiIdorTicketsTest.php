<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlindajeAntiIdorTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
        Rol::firstOrCreate(['rol' => 'subtec']);
        Rol::firstOrCreate(['rol' => 'rev']);
    }

    public function test_show_devuelve_404_si_movimiento_no_es_ticket(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala A'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);

        $trasladoInterno = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
            'comentario' => 'Movimiento de traslado ordinario',
        ]);

        $response = $this->actingAs($tec)->get(route('tickets.show', $trasladoInterno->id));
        $response->assertStatus(404);
    }

    public function test_comentar_devuelve_404_si_movimiento_no_es_ticket(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala A'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'solicitante' => 'Profesor Prueba',
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $response = $this->actingAs($tec)->post(route('tickets.comentar', $prestamo->id), [
            'mensaje' => 'Ataque IDOR comentando en prestamo',
        ]);

        $response->assertStatus(404);
    }

    public function test_agregar_area_devuelve_404_si_movimiento_no_es_ticket(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala A'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);
        $area = Area::firstOrCreate(['nombre' => 'Hardware'], ['activo' => true]);

        $traslado = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $response = $this->actingAs($tec)->post(route('tickets.agregarArea', $traslado->id), [
            'area_id' => $area->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_solucionar_devuelve_404_si_movimiento_no_es_ticket(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala A'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);

        $traslado = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $response = $this->actingAs($tec)->post(route('tickets.solucionar', $traslado->id), [
            'solucion' => 'Solucion falsa en movimiento interno',
        ]);

        $response->assertStatus(404);
    }

    public function test_resolver_devuelve_404_si_movimiento_no_es_ticket(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala A'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'solicitante' => 'Profesor Prueba',
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $response = $this->actingAs($tec)->post(route('tickets.resolver', $prestamo->id), [
            'accion' => 'resolver',
            'solucion' => 'Cierre forzado en prestamo',
        ]);

        $response->assertStatus(404);
    }

    public function test_ticket_legitimo_permite_acceso_y_seguimiento(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala A'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
            'estado_ticket' => 'abierto',
            'comentario' => 'Ticket legítimo para reparación de pantalla',
        ]);

        $response = $this->actingAs($tec)->get(route('tickets.show', $ticket->id));
        $response->assertStatus(200);
        $response->assertSee('Ticket legítimo para reparación de pantalla');
    }
}
