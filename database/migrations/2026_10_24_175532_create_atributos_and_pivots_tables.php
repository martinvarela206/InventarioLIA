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
        // 1. Catálogo maestro de especificaciones / atributos técnicos
        Schema::create('atributos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('tipo_dato')->default('texto'); // texto, numero, selector, fecha
            $table->string('unidad_medida')->nullable(); // GB, GHz, Pulgadas, Watts, etc.
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // 2. Tabla Pivot que define qué atributos aplican a cada Tipo de Activo
        Schema::create('tipo_atributo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_id')->constrained('tipos')->onDelete('cascade');
            $table->foreignId('atributo_id')->constrained('atributos')->onDelete('cascade');
            $table->boolean('es_requerido')->default(false);
            $table->integer('orden')->default(0);
            $table->unique(['tipo_id', 'atributo_id']);
            $table->timestamps();
        });

        // 3. Tabla Pivot de valores reales almacenados por cada activo
        Schema::create('elemento_atributo_valor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemento_id')->constrained('elementos')->onDelete('cascade');
            $table->foreignId('atributo_id')->constrained('atributos')->onDelete('cascade');
            $table->text('valor');
            $table->unique(['elemento_id', 'atributo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elemento_atributo_valor');
        Schema::dropIfExists('tipo_atributo');
        Schema::dropIfExists('atributos');
    }
};
