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
            $table->string('nro_lia', 25)->primary();
            $table->string('nro_unsj', 25)->nullable();
            $table->foreignId('tipo_id')->constrained('tipos')->onDelete('restrict');
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(1);
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
