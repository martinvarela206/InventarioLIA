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
        Schema::table('elementos', function (Blueprint $table) {
            $table->date('fecha_adquisicion')->nullable()->after('cantidad');
            $table->date('fecha_vencimiento_garantia')->nullable()->after('fecha_adquisicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elementos', function (Blueprint $table) {
            $table->dropColumn(['fecha_adquisicion', 'fecha_vencimiento_garantia']);
        });
    }
};
