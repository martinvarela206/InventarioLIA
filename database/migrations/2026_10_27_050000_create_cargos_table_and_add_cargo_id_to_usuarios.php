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
        // 1. Crear tabla de cargos del laboratorio
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 2. Vincular usuario a cargo_id (preservando columna 'cargo_actual' histórica)
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('cargo_id')->nullable()->after('cargo_actual')->constrained('cargos')->nullOnDelete();
        });

        // 3. Poblar cargos iniciales estándar del LIA
        $cargosIniciales = [
            ['nombre' => 'Director', 'descripcion' => 'Dirección general y representación del laboratorio', 'orden' => 1, 'activo' => true],
            ['nombre' => 'Secretaria', 'descripcion' => 'Secretaría administrativa y coordinación general', 'orden' => 2, 'activo' => true],
            ['nombre' => 'Coordinador de Área', 'descripcion' => 'Coordinación académica, técnica o de proyectos', 'orden' => 3, 'activo' => true],
            ['nombre' => 'JTP', 'descripcion' => 'Jefe de Trabajos Prácticos de cátedras y laboratorios', 'orden' => 4, 'activo' => true],
            ['nombre' => 'Ayudante 1ra', 'descripcion' => 'Docente auxiliar graduado de primera categoría', 'orden' => 5, 'activo' => true],
            ['nombre' => 'Ayudante 2da', 'descripcion' => 'Auxiliar docente estudiantil de segunda categoría', 'orden' => 6, 'activo' => true],
            ['nombre' => 'Becario', 'descripcion' => 'Becario/a de investigación, desarrollo o extensión', 'orden' => 7, 'activo' => true],
            ['nombre' => 'Pasante', 'descripcion' => 'Pasante de formación profesional en informática', 'orden' => 8, 'activo' => true],
            ['nombre' => 'Estudiante / Alumno', 'descripcion' => 'Estudiante de grado en carreras afines', 'orden' => 9, 'activo' => true],
            ['nombre' => 'Técnico de Laboratorio', 'descripcion' => 'Soporte técnico, hardware y conectividad', 'orden' => 10, 'activo' => true],
        ];

        $now = now();
        foreach ($cargosIniciales as $c) {
            DB::table('cargos')->insert([
                'nombre' => $c['nombre'],
                'descripcion' => $c['descripcion'],
                'orden' => $c['orden'],
                'activo' => $c['activo'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 4. Mapear usuarios existentes a sus cargo_id correspondientes
        $cargosDb = DB::table('cargos')->pluck('id', 'nombre');
        $usuarios = DB::table('usuarios')->whereNotNull('cargo_actual')->get(['id', 'cargo_actual']);
        foreach ($usuarios as $u) {
            $cargoTrim = trim($u->cargo_actual);
            // Búsqueda directa o parcial
            if (isset($cargosDb[$cargoTrim])) {
                DB::table('usuarios')->where('id', $u->id)->update(['cargo_id' => $cargosDb[$cargoTrim]]);
            } else {
                foreach ($cargosDb as $nombre => $id) {
                    if (stripos($cargoTrim, $nombre) !== false) {
                        DB::table('usuarios')->where('id', $u->id)->update(['cargo_id' => $id]);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['cargo_id']);
            $table->dropColumn('cargo_id');
        });

        Schema::dropIfExists('cargos');
    }
};
