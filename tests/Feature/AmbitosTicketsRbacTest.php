<?php

namespace Tests\Feature;

use App\Models\AmbitoTicket;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmbitosTicketsRbacTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $appAdmin;

    protected Usuario $userAdmin;

    protected Usuario $tecnico;

    protected Usuario $revisor;

    protected Usuario $registeredUser;

    protected AmbitoTicket $ambito;

    protected function setUp(): void
    {
        parent::setUp();

        $rolAppAdmin = Rol::firstOrCreate(['rol' => 'app_admin']);
        $rolUserAdmin = Rol::firstOrCreate(['rol' => 'user_admin']);
        $rolTec = Rol::firstOrCreate(['rol' => 'tec']);
        $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
        $rolRegistered = Rol::firstOrCreate(['rol' => 'registered']);

        $this->appAdmin = Usuario::factory()->create(['activo' => true]);
        $this->appAdmin->roles()->sync([$rolAppAdmin->id, $rolRegistered->id]);

        $this->userAdmin = Usuario::factory()->create(['activo' => true]);
        $this->userAdmin->roles()->sync([$rolUserAdmin->id, $rolRegistered->id]);

        $this->tecnico = Usuario::factory()->create(['activo' => true]);
        $this->tecnico->roles()->sync([$rolTec->id, $rolRegistered->id]);

        $this->revisor = Usuario::factory()->create(['activo' => true]);
        $this->revisor->roles()->sync([$rolRev->id, $rolRegistered->id]);

        $this->registeredUser = Usuario::factory()->create(['activo' => true]);
        $this->registeredUser->roles()->sync([$rolRegistered->id]);

        $this->ambito = AmbitoTicket::firstOrCreate(
            ['nombre' => 'Sala de Servidores'],
            ['descripcion' => 'Rack central', 'orden' => 1, 'activo' => true]
        );
    }

    public function test_app_admin_puede_acceder_al_panel_de_ambitos(): void
    {
        $response = $this->actingAs($this->appAdmin)->get(route('app_admin.ambitos'));

        $response->assertStatus(200);
        $response->assertSee('Catálogo de Ámbitos y Espacios para Tickets');
        $response->assertSee('Sala de Servidores');
    }

    public function test_app_admin_puede_crear_nuevo_ambito(): void
    {
        $response = $this->actingAs($this->appAdmin)->post(route('app_admin.ambitos.store'), [
            'nombre' => 'Laboratorio C',
            'descripcion' => 'Nueva sala de computación',
            'orden' => 10,
            'activo' => '1',
        ]);

        $response->assertRedirect(route('app_admin.ambitos'));
        $this->assertDatabaseHas('ambitos_tickets', [
            'nombre' => 'Laboratorio C',
            'orden' => 10,
            'activo' => true,
        ]);
    }

    public function test_app_admin_puede_actualizar_ambito(): void
    {
        $response = $this->actingAs($this->appAdmin)->put(route('app_admin.ambitos.update', $this->ambito), [
            'nombre' => 'Sala de Servidores Central',
            'descripcion' => 'Datacenter principal',
            'orden' => 2,
            'activo' => '1',
        ]);

        $response->assertRedirect(route('app_admin.ambitos'));
        $this->assertDatabaseHas('ambitos_tickets', [
            'id' => $this->ambito->id,
            'nombre' => 'Sala de Servidores Central',
        ]);
    }

    public function test_app_admin_puede_alternar_estado_activo_inactivo(): void
    {
        $this->assertTrue($this->ambito->activo);

        $response = $this->actingAs($this->appAdmin)->post(route('app_admin.ambitos.toggle', $this->ambito));

        $response->assertRedirect(route('app_admin.ambitos'));
        $this->assertFalse($this->ambito->fresh()->activo);
    }

    public function test_app_admin_no_puede_eliminar_ambito_con_tickets_asociados_y_se_desactiva(): void
    {
        // Crear ticket asociado a este ámbito
        Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->appAdmin->id,
            'fecha' => now(),
            'solicitante' => 'Coordinador',
            'comentario' => 'Ticket de prueba en sala de servidores',
        ]);

        $response = $this->actingAs($this->appAdmin)->delete(route('app_admin.ambitos.destroy', $this->ambito));

        $response->assertRedirect(route('app_admin.ambitos'));
        $response->assertSessionHas('warning');

        // Sigue existiendo en base de datos pero desactivado
        $this->assertDatabaseHas('ambitos_tickets', [
            'id' => $this->ambito->id,
            'activo' => false,
        ]);
    }

    public function test_app_admin_puede_eliminar_ambito_vacio_sin_tickets(): void
    {
        $vacio = AmbitoTicket::create([
            'nombre' => 'Espacio Temporal Descartable',
            'orden' => 50,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->appAdmin)->delete(route('app_admin.ambitos.destroy', $vacio));

        $response->assertRedirect(route('app_admin.ambitos'));
        $this->assertDatabaseMissing('ambitos_tickets', [
            'id' => $vacio->id,
        ]);
    }

    public function test_roles_no_autorizados_reciben_403_al_intentar_acceder_o_modificar_ambitos(): void
    {
        $usuariosOperativosNoAppAdmin = [
            $this->userAdmin,
            $this->tecnico,
            $this->revisor,
        ];

        foreach ($usuariosOperativosNoAppAdmin as $usuario) {
            // GET
            $this->actingAs($usuario)
                ->get(route('app_admin.ambitos'))
                ->assertStatus(403);

            // POST
            $this->actingAs($usuario)
                ->post(route('app_admin.ambitos.store'), [
                    'nombre' => 'Intento Ilegal '.$usuario->id,
                ])
                ->assertStatus(403);

            // PUT
            $this->actingAs($usuario)
                ->put(route('app_admin.ambitos.update', $this->ambito), [
                    'nombre' => 'Modificacion Ilegal '.$usuario->id,
                ])
                ->assertStatus(403);

            // TOGGLE
            $this->actingAs($usuario)
                ->post(route('app_admin.ambitos.toggle', $this->ambito))
                ->assertStatus(403);

            // DELETE
            $this->actingAs($usuario)
                ->delete(route('app_admin.ambitos.destroy', $this->ambito))
                ->assertStatus(403);
        }
    }

    public function test_usuario_registrado_sin_rol_operativo_es_redirigido_a_bienvenida(): void
    {
        $this->actingAs($this->registeredUser)
            ->get(route('app_admin.ambitos'))
            ->assertRedirect(route('bienvenida'));
    }

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get(route('app_admin.ambitos'))
            ->assertRedirect(route('login'));
    }
}
