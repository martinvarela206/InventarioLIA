<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

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
            'asset_admin',
            'item_admin',
            'tec',
            'subtec',
            'rev',
            'registered',
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['rol' => $rol]);
        }
    }
}
