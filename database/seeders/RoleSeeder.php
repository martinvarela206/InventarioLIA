<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'user_admin',
            'app_admin',
            'item_admin',
            'tec',
            'rev',
            'registered',
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['rol' => $rol]);
        }
    }
}
