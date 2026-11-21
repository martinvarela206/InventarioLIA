<?php

namespace Tests\Feature;

use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesImpresionPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_reportes_show_y_auditoria_incorporan_orientacion_dinamica_y_reglas_print(): void
    {
        $rev = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'rev'))->first();
        $ubicacion = Ubicacion::where('activa', true)->first();

        // 1. Reporte General (show.blade.php) - Filtramos por sala para no cargar 1100 filas
        $respShow = $this->actingAs($rev)->get(route('reportes.show', ['tipo' => 'inventario-completo', 'ubicacion_id' => $ubicacion->id]));
        $respShow->assertStatus(200);

        // Verifica selector dinámico de orientación
        $respShow->assertSee('btn-orientacion-auto');
        $respShow->assertSee('btn-orientacion-portrait');
        $respShow->assertSee('btn-orientacion-landscape');
        $respShow->assertSee('setOrientacionImpresion');
        $respShow->assertSee('size: auto;');

        // 2. Planilla de Auditoría (auditoria.blade.php)
        $respAuditoria = $this->actingAs($rev)->get(route('reportes.auditoria', ['ubicacion' => $ubicacion->id]));
        $respAuditoria->assertStatus(200);

        $respAuditoria->assertSee('btn-orientacion-auto');
        $respAuditoria->assertSee('btn-orientacion-portrait');
        $respAuditoria->assertSee('btn-orientacion-landscape');
        $respAuditoria->assertSee('firmas-container');
        $respAuditoria->assertSee('size: auto;');
    }
}
