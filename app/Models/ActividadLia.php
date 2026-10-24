<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActividadLia extends Model
{
    use HasFactory;

    protected $table = 'actividades_lia';

    protected $fillable = [
        'titulo',
        'contenido',
        'imagen_path',
        'fecha_publicacion',
        'activo',
        'user_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_publicacion' => 'datetime',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('fecha_publicacion', 'desc');
    }
}
