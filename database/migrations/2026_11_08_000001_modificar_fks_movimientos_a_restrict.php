<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign(['elemento_id']);
            $table->dropForeign(['user_id']);
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('usuarios')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign(['elemento_id']);
            $table->dropForeign(['user_id']);
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }
};
