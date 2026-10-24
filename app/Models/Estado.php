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
}
