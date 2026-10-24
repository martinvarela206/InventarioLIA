<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspacioLia extends Model
{
    use HasFactory;

    protected $table = 'espacios_lia';

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_path',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('id');
    }
}
