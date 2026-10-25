<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoAdjunto extends Model
{
    use HasFactory;

    protected $table = 'movimiento_adjuntos';

    protected $fillable = [
        'movimiento_id',
        'archivo_path',
        'nombre_original',
        'mime_type',
    ];

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->archivo_path);
    }
}
