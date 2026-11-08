<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalLia extends Model
{
    use HasFactory;

    protected $table = 'personal_lia';

    protected $fillable = [
        'user_id',
        'comentario',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->user();
    }

    public function getNombreAttribute(): string
    {
        return $this->user?->nombre ?? 'Personal del LIA';
    }

    public function getCargoAttribute(): string
    {
        return $this->user?->cargo_actual ?: ($this->user?->cargo?->nombre ?? 'Miembro del LIA');
    }

    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    public function getFotoUrlAttribute(): string
    {
        return $this->user?->foto_url ?? asset('assets/placeholder-user.png');
    }

    /**
     * Scope para figuras activas en portada:
     * Requiere que el registro en personal_lia esté activo, y que el usuario esté activo y NO tenga huella borrada.
     */
    public function scopeActivos($query)
    {
        return $query->where('personal_lia.activo', true)
            ->whereHas('user', function ($q) {
                $q->where('activo', true)
                  ->where('huella_borrada', false);
            })
            ->orderBy('orden')
            ->orderBy('id');
    }
}
