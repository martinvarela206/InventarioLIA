<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Rol::all()->keyBy('rol');

        $users = [
            [
                'nombre' => 'Administrador General',
                'email' => 'admin@lia.unsj.edu.ar',
                'password' => Hash::make('1234'),
                'dni' => '30111222',
                'cargo_actual' => 'Director LIA',
                'activo' => true,
                'roles' => ['registered', 'user_admin', 'app_admin', 'item_admin', 'tec', 'rev'],
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

            $user = Usuario::firstOrCreate(['email' => $u['email']], $u);
            
            $roleIds = [];
            foreach ($roleNames as $rName) {
                if (isset($roles[$rName])) {
                    $roleIds[] = $roles[$rName]->id;
                }
            }
            $user->roles()->sync($roleIds);
        }
    }
}
