<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estados';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'disponible',
    ];

    protected $casts = [
        'disponible' => 'boolean',
    ];

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'estado_id');
    }

    public function scopeDisponibles($query)
    {
        return $query->where('disponible', true);
    }

    public static function firstOrCreateNormalizado(string $nombre): self
    {
        $raw = trim($nombre);
        $clean = mb_strtolower($raw, 'UTF-8');

        $map = [
            'nuevo' => 'nuevo',
            'bueno' => 'funcionando',
            'funcionando' => 'funcionando',
            'operativo' => 'funcionando',
            'almacenado' => 'almacenado',
            'en reparacion' => 'en reparacion',
            'reparacion' => 'en reparacion',
            'roto' => 'en reparacion',
            'verificar' => 'verificar funcionamiento',
            'verificar funcionamiento' => 'verificar funcionamiento',
            'prestado' => 'prestado',
            'dado de baja' => 'dado de baja',
            'baja' => 'dado de baja',
            'obsoleto' => 'obsoleto',
            'basura tecnologica' => 'basura tecnologica',
            'basura' => 'basura tecnologica',
        ];

        $nombreFinal = $map[$clean] ?? $clean;
        $disponible = in_array($nombreFinal, ['nuevo', 'funcionando', 'almacenado']);

        return static::firstOrCreate(
            ['nombre' => $nombreFinal],
            ['disponible' => $disponible, 'descripcion' => 'Estado operativo: ' . ucfirst($nombreFinal)]
        );
    }
}
