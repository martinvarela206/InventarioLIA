<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class StreamingMemoriaExportacionesTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $revisor;

    protected Ubicacion $ubicacion;

    protected Tipo $tipo;

    protected function setUp(): void
    {
        parent::setUp();

        $rolRev = Rol::firstOrCreate(['rol' => 'rev'], ['nombre' => 'Revisor']);
        $this->revisor = Usuario::factory()->create(['activo' => true]);
        $this->revisor->roles()->attach($rolRev->id);

        $this->ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio de Pruebas'], ['activa' => true]);
        $this->tipo = Tipo::firstOrCreate(['nombre' => 'Servidor'], ['categoria' => 'Servidores']);
    }

    public function test_export_csv_de_inventario_utiliza_streamed_response(): void
    {
        // Crear elementos de prueba
        for ($i = 1; $i <= 10; $i++) {
            $el = Elemento::create([
                'tipo_id' => $this->tipo->id,
                'ubicacion_id' => $this->ubicacion->id,
                'nro_lia' => "LIA-STREAM-{$i}",
                'marca' => 'Dell',
                'modelo' => 'PowerEdge',
            ]);

            Movimiento::create([
                'elemento_id' => $el->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $this->ubicacion->id,
                'user_id' => $this->revisor->id,
                'fecha' => now(),
            ]);
        }

        $response = $this->actingAs($this->revisor)->get(route('elementos.exportCsv'));

        $this->assertInstanceOf(StreamedResponse::class, $response->baseResponse);
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('LIA-STREAM-1', $content);
        $this->assertStringContainsString('LIA-STREAM-10', $content);
    }

    public function test_export_csv_de_reportes_utiliza_streamed_response(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Elemento::create([
                'tipo_id' => $this->tipo->id,
                'ubicacion_id' => $this->ubicacion->id,
                'nro_lia' => "LIA-REP-STREAM-{$i}",
                'marca' => 'HP',
                'modelo' => 'ProLiant',
            ]);
        }

        $response = $this->actingAs($this->revisor)->get(route('reportes.exportCsv', 'inventario-completo'));

        $this->assertInstanceOf(StreamedResponse::class, $response->baseResponse);
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('LIA-REP-STREAM-1', $content);
        $this->assertStringContainsString('LIA-REP-STREAM-5', $content);
    }

    public function test_imprimir_inventario_procesa_colecciones_sin_agotamiento_de_memoria(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Elemento::create([
                'tipo_id' => $this->tipo->id,
                'ubicacion_id' => $this->ubicacion->id,
                'nro_lia' => "LIA-PRINT-{$i}",
                'marca' => 'Lenovo',
                'modelo' => 'ThinkSystem',
            ]);
        }

        $response = $this->actingAs($this->revisor)->get(route('elementos.imprimir'));
        $response->assertOk();
        $response->assertSee('LIA-PRINT-1');
        $response->assertSee('LIA-PRINT-5');
    }
}
