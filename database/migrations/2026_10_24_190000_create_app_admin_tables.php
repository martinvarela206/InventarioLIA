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
        // 1. Tabla de Carreras Universitarias
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('codigo', 50)->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        // 2. Vincular usuario a carrera_id (preservando columna 'carrera' histórica de texto)
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('carrera_id')->nullable()->after('carrera')->constrained('carreras')->nullOnDelete();
        });

        // 3. Espacios del LIA (fotos de laboratorios e instalaciones)
        Schema::create('espacios_lia', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('imagen_path')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 4. Personal del LIA (figuras relevantes, roles, directores, seguridad)
        Schema::create('personal_lia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cargo'); // Ej: Directora, Coordinador Técnico, Encargado de Seguridad ante Emergencias
            $table->text('comentario')->nullable();
            $table->string('email')->nullable();
            $table->string('foto_path')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 5. Actividades del LIA (publicaciones tipo feed / Instagram con fotos o anuncios)
        Schema::create('actividades_lia', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('contenido');
            $table->string('imagen_path')->nullable();
            $table->timestamp('fecha_publicacion')->useCurrent();
            $table->boolean('activo')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn('carrera_id');
        });

        Schema::dropIfExists('actividades_lia');
        Schema::dropIfExists('personal_lia');
        Schema::dropIfExists('espacios_lia');
        Schema::dropIfExists('carreras');
    }
};
