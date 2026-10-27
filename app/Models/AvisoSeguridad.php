<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvisoSeguridad extends Model
{
    use HasFactory;

    protected $table = 'avisos_seguridad';

    protected $fillable = [
        'titulo',
        'descripcion',
        'severidad',
        'icono',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Scope para filtrar avisos activos ordenados para el carrusel.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true)->orderBy('orden', 'asc')->orderBy('id', 'desc');
    }
}
