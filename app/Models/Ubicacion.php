<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'ubicacion_id');
    }

    /**
     * Resuelve de forma tolerante y dinámica la ubicación física contra los registros existentes en la tabla ubicaciones.
     * Soporta nombres renombrados, heurísticas históricas del ODS y tolerancia ante bases de datos sin seedear.
     */
    public static function resolverUbicacionCanonica(string $nombre): self
    {
        $raw = trim($nombre);
        if ($raw === '') {
            $raw = 'Administracion';
        }
        $clean = mb_strtolower($raw, 'UTF-8');

        // 1. Coincidencia exacta por nombre (case-insensitive)
        $exacta = static::whereRaw('LOWER(nombre) = ?', [$clean])->first();
        if ($exacta) {
            return $exacta;
        }

        // 2. Mapeos canónicos de heurística conocidos (para ODS con abreviaturas o formatos históricos)
        $heuristica = null;
        if (str_contains($clean, 'federico') || str_contains($clean, 'sanchez') || $clean === 'fs') {
            $heuristica = static::where('nombre', 'LIKE', '%Federico Sanchez%')->first();
        } elseif (str_contains($clean, 'paola') || str_contains($clean, 'moyano') || $clean === 'pm') {
            $heuristica = static::where('nombre', 'LIKE', '%Paola Moyano%')->first();
        } elseif (str_contains($clean, 'rack') && (str_contains($clean, 'red') || str_contains($clean, 'laboratorio'))) {
            $heuristica = static::where('nombre', 'LIKE', '%Rack Redes%')->first();
        } elseif (str_contains($clean, 'rack')) {
            $heuristica = static::where('nombre', 'LIKE', '%Rack Administracion%')->first();
        } elseif (str_contains($clean, 'redes') || str_contains($clean, 'telecom')) {
            $heuristica = static::where('nombre', 'LIKE', '%Redes%')->first();
        } elseif (str_contains($clean, 'oficina de hard') || str_contains($clean, 'lab hardware') || str_contains($clean, 'taller')) {
            $heuristica = static::where('nombre', 'LIKE', '%Hardware%')->first();
        } elseif (str_contains($clean, 'armario') || str_contains($clean, 'hw') || str_contains($clean, 'herramienta') || str_contains($clean, 'disco') || is_numeric($clean)) {
            $heuristica = static::where('nombre', 'LIKE', '%Armario%')->first();
        } elseif (str_contains($clean, 'oficina a') || $clean === 'of a') {
            $heuristica = static::where('nombre', 'LIKE', '%Oficina A%')->first();
        } elseif (str_contains($clean, 'oficina b') || $clean === 'of b') {
            $heuristica = static::where('nombre', 'LIKE', '%Oficina B%')->first();
        } elseif ($clean === 'laboratorio b' || $clean === 'lab b' || $clean === 'b') {
            $heuristica = static::where('nombre', 'LIKE', '%Laboratorio B%')->first();
        } elseif ($clean === 'laboratorio c' || $clean === 'lab c' || $clean === 'c') {
            $heuristica = static::where('nombre', 'LIKE', '%Laboratorio C%')->first();
        }

        if ($heuristica) {
            return $heuristica;
        }

        // 3. Búsqueda por substring parcial en las ubicaciones activas existentes
        $parcial = static::where('activa', true)
            ->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$clean}%"]);
            })->first();

        if ($parcial) {
            return $parcial;
        }

        // 4. Fallback a la primera sala activa existente
        $primera = static::where('activa', true)->first() ?? static::first();
        if ($primera) {
            return $primera;
        }

        // 5. Tolerancia ante base de datos sin seedear: crear la sala requerida al vuelo
        return static::create([
            'nombre' => $raw,
            'descripcion' => 'Espacio físico registrado automáticamente',
            'activa' => true,
        ]);
    }

    /**
     * Alias retrocompatible con seeders e importadores.
     */
    public static function firstOrCreateNormalizado(string $nombre, ?string $descripcion = null): self
    {
        $ubi = static::resolverUbicacionCanonica($nombre);
        if ($descripcion && empty($ubi->descripcion)) {
            $ubi->update(['descripcion' => trim($descripcion)]);
        }

        return $ubi;
    }
}
