<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketComentario extends Model
{
    use HasFactory;

    protected $table = 'ticket_comentarios';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'movimiento_id',
        'mensaje',
        'tipo',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'ticket_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(TicketComentarioAdjunto::class, 'ticket_comentario_id');
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }
}
