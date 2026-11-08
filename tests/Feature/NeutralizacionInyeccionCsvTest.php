<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Support\CsvSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeutralizacionInyeccionCsvTest extends TestCase
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

        $this->ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Principal'], ['activa' => true]);
        $this->tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['categoria' => 'Informatica']);
    }

    public function test_csv_sanitizer_neutraliza_prefijos_peligrosos(): void
    {
        $this->assertEquals("'=cmd|' /C calc'!A0", CsvSanitizer::sanitizeField("=cmd|' /C calc'!A0"));
        $this->assertEquals("'+12345", CsvSanitizer::sanitizeField('+12345'));
        $this->assertEquals("'-100", CsvSanitizer::sanitizeField('-100'));
        $this->assertEquals("'@SUM(A1:A10)", CsvSanitizer::sanitizeField('@SUM(A1:A10)'));
        $this->assertEquals("'\tTabulado", CsvSanitizer::sanitizeField("\tTabulado"));
        $this->assertEquals("'\rRetorno", CsvSanitizer::sanitizeField("\rRetorno"));

        // Campos inofensivos deben mantenerse inalterados
        $this->assertEquals('Dell Inc.', CsvSanitizer::sanitizeField('Dell Inc.'));
        $this->assertEquals('Latitude 5420', CsvSanitizer::sanitizeField('Latitude 5420'));
        $this->assertEquals(1234, CsvSanitizer::sanitizeField(1234));
        $this->assertNull(CsvSanitizer::sanitizeField(null));
    }

    public function test_export_csv_de_inventario_neutraliza_inyeccion_de_formulas(): void
    {
        $pcMaliciosa = Elemento::create([
            'tipo_id' => $this->tipo->id,
            'ubicacion_id' => $this->ubicacion->id,
            'nro_lia' => 'LIA-EXP-01',
            'marca' => '=2+5*cmd',
            'modelo' => '+ExploitModel',
            'observaciones' => '@HYPERLINK("http://malicious.site")',
        ]);

        Movimiento::create([
            'elemento_id' => $pcMaliciosa->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $this->ubicacion->id,
            'user_id' => $this->revisor->id,
            'solicitante' => '-AdminExploit',
            'fecha' => now(),
        ]);

        $response = $this->actingAs($this->revisor)->get(route('elementos.exportCsv'));
        $response->assertOk();

        // Obtener contenido del CSV stream
        $content = $response->streamedContent();

        // Verificar que los campos con prefijos peligrosos estén neutralizados con comilla simple
        $this->assertStringContainsString(";'=2+5*cmd;", $content);
        $this->assertStringContainsString(";'+ExploitModel;", $content);
        $this->assertStringContainsString("'@HYPERLINK", $content);
        $this->assertStringContainsString(";'-AdminExploit;", $content);
    }
}
