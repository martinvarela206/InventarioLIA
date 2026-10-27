<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\UsuarioTrayectoria;
use App\Models\Cargo;
use App\Models\Carrera;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Rol::all()->keyBy('rol');
        $cargosMap = Cargo::all()->pluck('id', 'nombre');
        $carrerasMap = Carrera::all()->pluck('id', 'nombre');

        $users = [
            // Usuarios Oficiales y Cuentas Reales
            [
                'nombre' => 'Flavia Millan',
                'email' => 'flavia@lia.com',
                'password' => Hash::make('1234'),
                'dni' => '27111222',
                'cargo_actual' => 'Director',
                'carrera' => 'Licenciatura en Ciencias de la Computación',
                'foto_path' => '/assets/flavia.png',
                'activo' => true,
                'roles' => ['registered', 'user_admin', 'app_admin', 'item_admin', 'tec', 'subtec', 'rev'],
            ],
            [
                'nombre' => 'Marcelo Mondre',
                'email' => 'marcelo@lia.com',
                'password' => Hash::make('1234'),
                'dni' => '28333444',
                'cargo_actual' => 'Coordinador de Área',
                'carrera' => 'Licenciatura en Sistemas de Información',
                'foto_path' => '/assets/manuel.png',
                'activo' => true,
                'roles' => ['registered', 'rev', 'subtec', 'tec'],
            ],
            [
                'nombre' => 'Cristina Sosa',
                'email' => 'cristina@lia.com',
                'password' => Hash::make('1234'),
                'dni' => '29555666',
                'cargo_actual' => 'Secretaria',
                'carrera' => 'Licenciatura en Ciencias de la Computación',
                'foto_path' => '/assets/pamela.png',
                'activo' => true,
                'roles' => ['registered', 'user_admin', 'app_admin'],
            ],
            [
                'nombre' => 'Martin Varela',
                'email' => 'martin@lia.com',
                'password' => Hash::make('1234'),
                'dni' => '38777888',
                'cargo_actual' => 'JTP',
                'carrera' => 'Licenciatura en Sistemas de Información',
                'activo' => true,
                'roles' => ['registered', 'rev'],
            ],
            // 3 usuarios aleatorios complementarios
            [
                'nombre' => 'Juan Pérez (Registrado)',
                'email' => 'juan.perez@lia.com',
                'password' => Hash::make('1234'),
                'dni' => '42111333',
                'cargo_actual' => 'Estudiante / Alumno',
                'carrera' => 'Licenciatura en Ciencias de la Computación',
                'foto_path' => '/assets/alumna.png',
                'activo' => true,
                'roles' => ['registered'],
            ],
            [
                'nombre' => 'Carlos Gómez (Ex-Personal)',
                'email' => 'carlos.gomez@ex-lia.com',
                'password' => Hash::make('1234'),
                'dni' => '24999888',
                'cargo_actual' => 'Ex-Técnico de Laboratorio',
                'carrera' => 'Licenciatura en Sistemas de Información',
                'activo' => false,
                'roles' => ['registered'],
                'trayectoria_fin' => '2019-11-30',
                'trayectoria_inicio' => '2015-03-01',
                'trayectoria_cargo' => 'Técnico de Hardware',
            ],
            [
                'nombre' => 'Laura Martínez (Ex-Personal)',
                'email' => 'laura.martinez@ex-lia.com',
                'password' => Hash::make('1234'),
                'dni' => '25444333',
                'cargo_actual' => 'Ex-Pasante de Investigación',
                'carrera' => 'Licenciatura en Ciencias de la Computación',
                'activo' => false,
                'roles' => ['registered'],
                'trayectoria_fin' => '2018-10-15',
                'trayectoria_inicio' => '2016-04-01',
                'trayectoria_cargo' => 'Pasante de Redes',
            ],
            // Cuentas de soporte y compatibilidad operativa
            [
                'nombre' => 'Administrador General',
                'email' => 'admin@lia.unsj.edu.ar',
                'password' => Hash::make('1234'),
                'dni' => '30111222',
                'cargo_actual' => 'Director LIA',
                'activo' => true,
                'roles' => ['registered', 'user_admin', 'app_admin', 'item_admin', 'tec', 'subtec', 'rev'],
            ],
            [
                'nombre' => 'Administrador de Aplicación',
                'email' => 'app_admin@lia.unsj.edu.ar',
                'password' => Hash::make('1234'),
                'dni' => '31222333',
                'cargo_actual' => 'Coordinador Web LIA',
                'activo' => true,
                'roles' => ['registered', 'app_admin'],
            ],
            [
                'nombre' => 'Encargado de Inventario',
                'email' => 'item_admin@lia.unsj.edu.ar',
                'password' => Hash::make('1234'),
                'dni' => '32333444',
                'cargo_actual' => 'JTP Inventario',
                'activo' => true,
                'roles' => ['registered', 'item_admin'],
            ],
            [
                'nombre' => 'Técnico de Laboratorio',
                'email' => 'tecnico@lia.unsj.edu.ar',
                'password' => Hash::make('1234'),
                'dni' => '35555666',
                'cargo_actual' => 'Ayudante de 1ra',
                'activo' => true,
                'roles' => ['registered', 'tec', 'rev'],
            ],
            [
                'nombre' => 'Revisor de Auditoría',
                'email' => 'revisor@lia.unsj.edu.ar',
                'password' => Hash::make('1234'),
                'dni' => '28777888',
                'cargo_actual' => 'Docente Investigador',
                'activo' => true,
                'roles' => ['registered', 'rev'],
            ],
            [
                'nombre' => 'Usuario Registrado',
                'email' => 'registrado@lia.unsj.edu.ar',
                'password' => Hash::make('1234'),
                'dni' => '40999000',
                'cargo_actual' => 'Becario Alumno',
                'activo' => false,
                'roles' => ['registered'],
            ],
        ];

        foreach ($users as $u) {
            $roleNames = $u['roles'];
            unset($u['roles']);

            $trayectoriaInicio = $u['trayectoria_inicio'] ?? null;
            $trayectoriaFin = $u['trayectoria_fin'] ?? null;
            $trayectoriaCargo = $u['trayectoria_cargo'] ?? null;
            unset($u['trayectoria_inicio'], $u['trayectoria_fin'], $u['trayectoria_cargo']);

            $cargoNombre = $u['cargo_actual'] ?? null;
            $carreraNombre = $u['carrera'] ?? null;
            unset($u['cargo_actual'], $u['carrera']);

            if ($cargoNombre) {
                $cargoId = $cargosMap[$cargoNombre] ?? null;
                if (!$cargoId) {
                    $nuevoCargo = Cargo::firstOrCreate(['nombre' => $cargoNombre]);
                    $cargoId = $nuevoCargo->id;
                    $cargosMap[$cargoNombre] = $cargoId;
                }
                $u['cargo_id'] = $cargoId;
            }

            if ($carreraNombre && isset($carrerasMap[$carreraNombre])) {
                $u['carrera_id'] = $carrerasMap[$carreraNombre];
            }

            $user = Usuario::firstOrCreate(['email' => $u['email']], $u);
            
            $roleIds = [];
            foreach ($roleNames as $rName) {
                if (isset($roles[$rName])) {
                    $roleIds[] = $roles[$rName]->id;
                }
            }
            $user->roles()->sync($roleIds);

            if ($trayectoriaFin) {
                UsuarioTrayectoria::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'fecha_fin' => $trayectoriaFin,
                    ],
                    [
                        'cargo' => $trayectoriaCargo ?? 'Cargo Histórico',
                        'fecha_inicio' => $trayectoriaInicio ?? '2016-01-01',
                        'notas' => 'Vínculo laboral extinguido hace más de 6 años. Sujeto a Ley 25.326.',
                    ]
                );
            }
        }
    }
}
