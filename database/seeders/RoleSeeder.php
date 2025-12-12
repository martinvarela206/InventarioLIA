<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Rol::insert([
            ['rol' => 'user_admin'],
            ['rol' => 'coordinador'],
            ['rol' => 'tecnico'],
            ['rol' => 'revisor'],
        ]);
    }
}
