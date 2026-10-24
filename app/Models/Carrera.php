<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carreras';

    protected $fillable = [
        'nombre',
        'codigo',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'carrera_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true)->orderBy('nombre');
    }
}
