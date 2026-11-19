<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('usuarios')
            ->where('email', 'marcelo@lia.com')
            ->orWhere('nombre', 'like', '%Marcelo Mondre%')
            ->update(['foto_path' => '/assets/marcelo.png']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('usuarios')
            ->where('email', 'marcelo@lia.com')
            ->orWhere('nombre', 'like', '%Marcelo Mondre%')
            ->update(['foto_path' => '/assets/manuel.png']);
    }
};
