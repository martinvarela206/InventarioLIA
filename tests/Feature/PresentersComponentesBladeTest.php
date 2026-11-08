<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PresentersComponentesBladeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
    }

    public function test_presenter_renderiza_badges_mediante_componentes_blade_con_escape_seguro(): void
    {
        $tipo = Tipo::firstOrCreate(['nombre' => 'PC'], ['slug' => 'pc', 'icono' => 'pc']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Central'], ['activa' => true]);
        $elemento = Elemento::factory()->create([
            'tipo_id' => $tipo->id,
            'fecha_vencimiento_garantia' => now()->addYear(),
        ]);
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        // Status badge sin historial
        $badgeStatus = $elemento->presenter()->statusBadge();
        $this->assertStringContainsString('Sin historial', $badgeStatus);

        // Garantia badge vigente
        $badgeGarantia = $elemento->presenter()->garantiaBadge();
        $this->assertStringContainsString('Vigente', $badgeGarantia);

        // Movimiento ticket con intento de XSS en estado_ticket
        $movimiento = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => '<script>alert(1)</script>',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
            'comentario' => 'Ticket de prueba',
        ]);

        $badgeTipo = $movimiento->presenter()->tipoBadge();
        // Debe escapar el script
        $this->assertStringNotContainsString('<script>', $badgeTipo);
        $this->assertStringContainsString('&lt;script&gt;', $badgeTipo);

        // Badge de estado
        $badgeEstado = $movimiento->presenter()->estadoBadge();
        $this->assertStringContainsString('Interno', $badgeEstado);
    }

    public function test_componentes_blade_se_pueden_invocar_directamente(): void
    {
        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $elemento = Elemento::factory()->create([
            'tipo_id' => $tipo->id,
            'fecha_vencimiento_garantia' => now()->subMonth(),
        ]);

        $htmlStatus = Blade::render('<x-elemento-status-badge :elemento="$elemento" />', ['elemento' => $elemento]);
        $this->assertStringContainsString('Sin historial', $htmlStatus);

        $htmlGarantia = Blade::render('<x-elemento-garantia-badge :elemento="$elemento" />', ['elemento' => $elemento]);
        $this->assertStringContainsString('Vencida hace', $htmlGarantia);
    }
}
