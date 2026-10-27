<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Movimiento::class, 'ticket_areas', 'area_id', 'ticket_id')
            ->withPivot('user_id', 'created_at');
    }
}
