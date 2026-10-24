<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'ubicacion_id');
    }

    /**
     * Resuelve de forma estricta y canónica la ubicación física dentro de los 12 espacios oficiales del LIA.
     * NUNCA crea ubicaciones espurias a partir de números, estados o celdas desfasadas del ODS.
     */
    public static function resolverUbicacionCanonica(string $nombre): self
    {
        $raw = trim($nombre);
        $clean = mb_strtolower($raw, 'UTF-8');

        // Mapeo exhaustivo hacia las 12 salas físicas reales
        if (str_contains($clean, 'federico') || str_contains($clean, 'sanchez') || $clean === 'fs') {
            return static::where('nombre', 'Laboratorio Federico Sanchez')->first() ?? static::first();
        }
        if (str_contains($clean, 'paola') || str_contains($clean, 'moyano') || $clean === 'pm') {
            return static::where('nombre', 'Laboratorio Paola Moyano')->first() ?? static::first();
        }
        if (str_contains($clean, 'rack') && (str_contains($clean, 'red') || str_contains($clean, 'laboratorio'))) {
            return static::where('nombre', 'Rack Redes')->first() ?? static::first();
        }
        if (str_contains($clean, 'rack')) {
            return static::where('nombre', 'Rack Administracion')->first() ?? static::first();
        }
        if (str_contains($clean, 'redes') || str_contains($clean, 'telecom')) {
            return static::where('nombre', 'Laboratorio Redes')->first() ?? static::first();
        }
        if (str_contains($clean, 'oficina de hard') || str_contains($clean, 'lab hardware') || str_contains($clean, 'taller')) {
            return static::where('nombre', 'Oficina de Hardware')->first() ?? static::first();
        }
        if (str_contains($clean, 'armario') || str_contains($clean, 'hw') || str_contains($clean, 'herramienta') || str_contains($clean, 'disco') || is_numeric($clean)) {
            return static::where('nombre', 'Armario de HW')->first() ?? static::first();
        }
        if (str_contains($clean, 'oficina a') || $clean === 'of a') {
            return static::where('nombre', 'Oficina A')->first() ?? static::first();
        }
        if (str_contains($clean, 'oficina b') || $clean === 'of b') {
            return static::where('nombre', 'Oficina B')->first() ?? static::first();
        }
        if ($clean === 'laboratorio b' || $clean === 'lab b' || $clean === 'b') {
            return static::where('nombre', 'Laboratorio B')->first() ?? static::first();
        }
        if ($clean === 'laboratorio c' || $clean === 'lab c' || $clean === 'c') {
            return static::where('nombre', 'Laboratorio C')->first() ?? static::first();
        }

        return static::where('nombre', 'Administracion')->first() ?? static::first();
    }

    /**
     * Alias retrocompatible con seeders.
     */
    public static function firstOrCreateNormalizado(string $nombre, ?string $descripcion = null): self
    {
        return static::resolverUbicacionCanonica($nombre);
    }
}
