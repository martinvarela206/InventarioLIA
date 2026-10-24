<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Carbon\Carbon;

class MovimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elementos = Elemento::all();
        $adminUser = Usuario::where('email', 'admin@lia.unsj.edu.ar')->first() ?? Usuario::first();
        $tecUser = Usuario::where('email', 'tecnico@lia.unsj.edu.ar')->first() ?? $adminUser;

        $ubicacionFS = Ubicacion::where('nombre', 'Laboratorio Federico Sanchez')->first() ?? Ubicacion::first();
        $ubicacionRedes = Ubicacion::where('nombre', 'Laboratorio Redes')->first() ?? Ubicacion::first();
        $ubicacionArmario = Ubicacion::where('nombre', 'Armario de HW')->first() ?? Ubicacion::first();

        foreach ($elementos as $elemento) {
            // Movimiento base de ingreso
            Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $ubicacionArmario->id,
                'puesto' => null,
                'user_id' => $adminUser->id,
                'fecha' => Carbon::now()->subMonths(6),
                'comentario' => 'Ingreso patrimonial y almacenamiento inicial en Armario de HW',
            ]);

            // Segundo movimiento según tipo
            if ($elemento->marca === 'Dell') {
                Movimiento::create([
                    'elemento_id' => $elemento->id,
                    'tipo_movimiento' => 'interno',
                    'ubicacion_id' => $ubicacionFS->id,
                    'puesto' => $elemento->puesto,
                    'user_id' => $tecUser->id,
                    'fecha' => Carbon::now()->subMonths(3),
                    'comentario' => "Instalación en laboratorio y puesta en funcionamiento en {$elemento->puesto}",
                ]);
            } elseif ($elemento->marca === 'Cisco') {
                Movimiento::create([
                    'elemento_id' => $elemento->id,
                    'tipo_movimiento' => 'interno',
                    'ubicacion_id' => $ubicacionRedes->id,
                    'puesto' => 'Rack Principal',
                    'user_id' => $tecUser->id,
                    'fecha' => Carbon::now()->subMonths(4),
                    'comentario' => 'Conexión a patchera troncal en Laboratorio Redes',
                ]);
            } elseif ($elemento->marca === 'Lenovo') {
                // Préstamo activo
                Movimiento::create([
                    'elemento_id' => $elemento->id,
                    'tipo_movimiento' => 'externo_prestamo',
                    'ubicacion_id' => null,
                    'puesto' => null,
                    'user_id' => $tecUser->id,
                    'fecha' => Carbon::now()->subDays(12),
                    'solicitante' => 'Dra. María González (Cátedra Redes)',
                    'fecha_prestamo' => Carbon::now()->subDays(12),
                    'fecha_devolucion' => null,
                    'comentario' => 'Préstamo para dictado de jornada académica',
                ]);
            }
        }
    }
}
