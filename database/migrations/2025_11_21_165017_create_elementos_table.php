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
        Schema::create('elementos', function (Blueprint $table) {
            $table->id();
            $table->string('nro_lia', 50)->nullable();
            $table->string('nro_unsj', 50)->nullable();
            $table->foreignId('tipo_id')->constrained('tipos')->onDelete('restrict');
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('nro_serie', 100)->nullable();
            $table->smallInteger('anio_compra')->nullable();
            $table->string('puesto', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('descripcion')->nullable(); // JSON formatted or text
            $table->integer('cantidad')->default(1);
            $table->date('fecha_adquisicion')->nullable();
            $table->date('fecha_vencimiento_garantia')->nullable();

            $table->unique(['nro_lia', 'nro_unsj']);
            $table->index('nro_lia');
            $table->index('nro_unsj');
            $table->index('puesto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elementos');
    }
};
