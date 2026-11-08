<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestriccionTrasladoRevisorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
        Rol::firstOrCreate(['rol' => 'rev']);
    }

    public function test_revisor_no_puede_trasladar_activo_al_abrir_ticket(): void
    {
        $rev = Usuario::factory()->create(['activo' => true]);
        $rev->roles()->attach(Rol::where('rol', 'rev')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $salaOrigen = Ubicacion::firstOrCreate(['nombre' => 'Sala Laboratorio A'], ['activa' => true]);
        $taller = Ubicacion::firstOrCreate(['nombre' => 'Taller Reparaciones'], ['activa' => true]);

        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);
        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $salaOrigen->id,
            'puesto' => 'Mesa 1',
            'user_id' => $rev->id,
            'fecha' => now()->subDay(),
        ]);

        // Revisor intenta enviar a taller
        $response = $this->actingAs($rev)->post(route('tickets.store'), [
            'elemento_id' => $elemento->id,
            'comentario' => 'Pantalla rota reportada por revisor',
            'tipo_traslado' => 'taller',
            'ubicacion_id' => $taller->id,
            'puesto' => 'Banco 5',
        ]);

        $response->assertSessionHasNoErrors();

        // El ticket debe haberse creado pero manteniendo la ubicación original in-situ
        $ticket = Movimiento::where('elemento_id', $elemento->id)->where('tipo_movimiento', 'reparacion')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($salaOrigen->id, $ticket->ubicacion_id);
        $this->assertEquals('Mesa 1', $ticket->puesto);
    }

    public function test_tecnico_si_puede_trasladar_activo_al_abrir_ticket(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $salaOrigen = Ubicacion::firstOrCreate(['nombre' => 'Sala Laboratorio A'], ['activa' => true]);
        $taller = Ubicacion::firstOrCreate(['nombre' => 'Taller Reparaciones'], ['activa' => true]);

        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);
        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $salaOrigen->id,
            'puesto' => 'Mesa 1',
            'user_id' => $tec->id,
            'fecha' => now()->subDay(),
        ]);

        // Técnico traslada a taller
        $response = $this->actingAs($tec)->post(route('tickets.store'), [
            'elemento_id' => $elemento->id,
            'comentario' => 'Mantenimiento preventivo en taller',
            'tipo_traslado' => 'taller',
            'ubicacion_id' => $taller->id,
            'puesto' => 'Banco 5',
        ]);

        $response->assertSessionHasNoErrors();

        $ticket = Movimiento::where('elemento_id', $elemento->id)->where('tipo_movimiento', 'reparacion')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($taller->id, $ticket->ubicacion_id);
        $this->assertEquals('Banco 5', $ticket->puesto);
    }

    public function test_revisor_en_auditoria_no_traslada_activo_al_crear_ticket_inconsistencia(): void
    {
        $rev = Usuario::factory()->create(['activo' => true]);
        $rev->roles()->attach(Rol::where('rol', 'rev')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $salaReal = Ubicacion::firstOrCreate(['nombre' => 'Sala Oficial'], ['activa' => true]);
        $salaAuditada = Ubicacion::firstOrCreate(['nombre' => 'Sala Auditada Distinta'], ['activa' => true]);

        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);
        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $salaReal->id,
            'puesto' => 'Puesto Real',
            'user_id' => $rev->id,
            'fecha' => now()->subDay(),
        ]);

        $auditoria = Auditoria::create([
            'ubicacion_id' => $salaAuditada->id,
            'user_id' => $rev->id,
            'estado' => 'en_progreso',
            'fecha' => now(),
        ]);

        $response = $this->actingAs($rev)->postJson(route('auditorias.crearTicketInconsistencia', $auditoria->id), [
            'elemento_id' => $elemento->id,
            'tipo_discrepancia' => 'rotura',
            'descripcion' => 'Rotura detectada durante relevamiento',
            'puesto' => 'Puesto Arbitrario',
        ]);

        $response->assertStatus(200);

        $ticket = Movimiento::where('elemento_id', $elemento->id)->where('tipo_movimiento', 'reparacion')->first();
        $this->assertNotNull($ticket);
        // La ubicación del ticket debe mantenerse en la sala real, no en la auditada
        $this->assertEquals($salaReal->id, $ticket->ubicacion_id);
        $this->assertEquals('Puesto Real', $ticket->puesto);
    }
}
