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
     * Resuelve o crea una ubicación normalizando variaciones del relevamiento.
     */
    public static function firstOrCreateNormalizado(string $nombre, ?string $descripcion = null): self
    {
        $raw = trim($nombre);
        $clean = mb_strtolower($raw, 'UTF-8');

        $map = [
            'oficina a' => 'Oficina A',
            'of a' => 'Oficina A',
            'oficina b' => 'Oficina B',
            'of b' => 'Oficina B',
            'laboratorio federico sanchez' => 'Laboratorio Federico Sanchez',
            'lab federico sanchez' => 'Laboratorio Federico Sanchez',
            'lab fs' => 'Laboratorio Federico Sanchez',
            'lab fb' => 'Laboratorio Federico Sanchez',
            'laboratorio paola moyano' => 'Laboratorio Paola Moyano',
            'lab paola moyano' => 'Laboratorio Paola Moyano',
            'lab pm' => 'Laboratorio Paola Moyano',
            'laboratorio redes' => 'Laboratorio Redes',
            'lab redes' => 'Laboratorio Redes',
            'redes' => 'Laboratorio Redes',
            'armario de hw' => 'Armario de HW',
            'armario hw' => 'Armario de HW',
            'armario' => 'Armario de HW',
            'administracion' => 'Administracion',
            'admin' => 'Administracion',
            'laboratorio b' => 'Laboratorio B',
            'lab b' => 'Laboratorio B',
            'laboratorio c' => 'Laboratorio C',
            'lab c' => 'Laboratorio C',
            'rack administracion' => 'Rack Administracion',
            'rack admin' => 'Rack Administracion',
            'rack redes' => 'Rack Redes',
            'oficina de hardware' => 'Oficina de Hardware',
            'of hardware' => 'Oficina de Hardware',
            'lab hardware' => 'Oficina de Hardware',
            'lia' => 'Administracion',
        ];

        $nombreFinal = $map[$clean] ?? $raw;

        return static::firstOrCreate(
            ['nombre' => $nombreFinal],
            ['descripcion' => $descripcion ?? "Ubicación física: {$nombreFinal}"]
        );
    }
}
