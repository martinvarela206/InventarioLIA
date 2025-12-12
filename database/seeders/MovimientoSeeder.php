<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Estado;
use Carbon\Carbon;

class MovimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elementos = Elemento::all();
        $users = [1, 2]; // Admin, Coordinador (IDs asumidos de UserSeeder)
        
        // Get ubicacion and estado IDs from database
        $ubicacionIds = Ubicacion::pluck('id', 'nombre')->toArray();
        $locations = array_keys($ubicacionIds);
        $estadoIds = Estado::pluck('id', 'nombre')->toArray();

        foreach ($elementos as $elemento) {
            $movementsCount = rand(1, 5);
            $currentDate = Carbon::now()->subMonths(rand(6, 24)); // Fecha de inicio aleatoria

            // 1. Movimiento Inicial
            $initialUser = $users[array_rand($users)];
            Movimiento::create([
                'nro_lia' => $elemento->nro_lia,
                'user_id' => $initialUser,
                'estado_id' => $estadoIds['ingresado'],
                'ubicacion_id' => $ubicacionIds['LIA'],
                'fecha' => $currentDate->copy(),
                'comentario' => 'Ingreso inicial al sistema'
            ]);

            // 2. Movimientos subsiguientes
            for ($i = 1; $i < $movementsCount; $i++) {
                $currentDate->addDays(rand(5, 60)); // Avanzar fecha
                $user = $users[array_rand($users)];
                
                $estadoNombre = array_rand($estadoIds);
                $location = 'LIA';
                $comment = 'Movimiento registrado';

                // Lógica específica por estado
                if ($estadoNombre === 'dado de baja') {
                    $location = $ubicacionIds['Dado de baja'];
                    $comment = 'Dado de baja por resolución N° ' . rand(100, 999) . '/' . date('Y');
                    
                    Movimiento::create([
                        'nro_lia' => $elemento->nro_lia,
                        'user_id' => $user,
                        'estado_id' => $estadoIds[$estadoNombre],
                        'ubicacion_id' => $location,
                        'fecha' => $currentDate->copy(),
                        'comentario' => $comment
                    ]);
                    break; // Detener generación si se da de baja
                } elseif ($estadoNombre === 'prestado') {
                    $location = $ubicacionIds['Prestado'];
                    $destinatarios = [
                        'profesor Juan Pérez',
                        'profesora María González',
                        'oficina de Administración',
                        'departamento de Sistemas',
                        'profesor Carlos Rodríguez',
                        'oficina de Rectorado',
                        'departamento de Investigación',
                    ];
                    $comment = 'Prestado a ' . $destinatarios[array_rand($destinatarios)];
                } elseif ($estadoNombre === 'funcionando' || $estadoNombre === 'guardado') {
                    $validLocNames = array_keys($ubicacionIds);
                    $locationName = $validLocNames[array_rand($validLocNames)];
                    $location = $ubicacionIds[$locationName];
                    $comment = 'Asignado a ' . $locationName;
                } elseif ($estadoNombre === 'ingresado') {
                     $location = $ubicacionIds['LIA'];
                     $comment = 'Reingreso a depósito';
                }

                Movimiento::create([
                    'nro_lia' => $elemento->nro_lia,
                    'user_id' => $user,
                    'estado_id' => $estadoIds[$estadoNombre],
                    'ubicacion_id' => $location,
                    'fecha' => $currentDate->copy(),
                    'comentario' => $comment
                ]);
            }
        }
    }
}
