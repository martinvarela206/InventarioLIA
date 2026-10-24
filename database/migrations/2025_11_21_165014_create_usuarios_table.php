<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->string('dni', 15)->nullable()->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('carrera', 100)->nullable();
            $table->string('cargo_actual', 50)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->boolean('activo')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
