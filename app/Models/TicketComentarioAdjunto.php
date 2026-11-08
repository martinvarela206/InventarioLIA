<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComentarioAdjunto extends Model
{
    use HasFactory;

    protected $table = 'ticket_comentario_adjuntos';

    protected $fillable = [
        'ticket_comentario_id',
        'archivo_path',
        'nombre_original',
        'mime_type',
    ];

    public function comentario(): BelongsTo
    {
        return $this->belongsTo(TicketComentario::class, 'ticket_comentario_id');
    }

    public function getUrlAttribute(): string
    {
        return route('adjuntos.ticket-comentario', $this->id);
    }
}
