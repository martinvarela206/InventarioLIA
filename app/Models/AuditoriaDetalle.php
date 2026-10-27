<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaDetalle extends Model
{
    use HasFactory;

    protected $table = 'auditoria_detalles';

    protected $fillable = [
        'auditoria_id',
        'puesto',
        'elemento_id',
        'estado',
        'ticket_id',
        'tipo_discrepancia',
        'observaciones',
    ];

    public function auditoria(): BelongsTo
    {
        return $this->belongsTo(Auditoria::class, 'auditoria_id');
    }

    public function elemento(): BelongsTo
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'ticket_id');
    }
}
