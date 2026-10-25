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
        Schema::create('reportes_configurados', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('titulo', 150);
            $table->string('subtitulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('icono', 50)->default('document');
            $table->string('categoria', 50)->default('general');
            $table->json('filtros_fijos')->nullable();
            $table->json('variables_requeridas')->nullable();
            $table->json('columnas_visibles')->nullable();
            $table->json('columnas_ocultas')->nullable();
            $table->boolean('es_sistema')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->foreignId('creado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_configurados');
    }
};
