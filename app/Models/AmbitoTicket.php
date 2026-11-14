<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmbitoTicket extends Model
{
    use HasFactory;

    protected $table = 'ambitos_tickets';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Movimientos / tickets técnicos asociados a este ámbito.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'ambito_ticket_id');
    }

    /**
     * Scope para filtrar únicamente los ámbitos activos para selección pública.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true)
            ->orderBy('orden', 'asc')
            ->orderBy('nombre', 'asc');
    }
}
