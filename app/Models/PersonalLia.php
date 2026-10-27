<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalLia extends Model
{
    use HasFactory;

    protected $table = 'personal_lia';

    protected $fillable = [
        'user_id',
        'nombre',
        'cargo',
        'comentario',
        'email',
        'foto_path',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->user();
    }

    public function getResolvedUserAttribute(): ?Usuario
    {
        if ($this->relationLoaded('user') && $this->user) {
            return $this->user;
        }
        if ($this->user_id) {
            return $this->user;
        }
        if ($this->email) {
            return Usuario::where('email', $this->email)->first();
        }
        return null;
    }

    public function getNombreAttribute(?string $value): string
    {
        return $value ?: ($this->resolved_user?->nombre ?? 'Personal del LIA');
    }

    public function getCargoAttribute(?string $value): string
    {
        return $value ?: ($this->resolved_user?->cargo_actual ?? 'Miembro del LIA');
    }

    public function getEmailAttribute(?string $value): ?string
    {
        return $value ?: $this->resolved_user?->email;
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_path) {
            if (str_starts_with($this->foto_path, 'http://') || str_starts_with($this->foto_path, 'https://') || str_starts_with($this->foto_path, '/assets/')) {
                return $this->foto_path;
            }
            return asset($this->foto_path);
        }
        $user = $this->resolved_user;
        if ($user && $user->foto_path) {
            return $user->foto_url;
        }
        return asset('assets/placeholder-user.png');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('id');
    }
}
