<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalLia extends Model
{
    use HasFactory;

    protected $table = 'personal_lia';

    protected $fillable = [
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

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'email', 'email');
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_path) {
            if (str_starts_with($this->foto_path, 'http://') || str_starts_with($this->foto_path, 'https://') || str_starts_with($this->foto_path, '/assets/')) {
                return $this->foto_path;
            }
            return asset($this->foto_path);
        }
        if ($this->usuario && $this->usuario->foto_path) {
            return $this->usuario->foto_url;
        }
        return asset('assets/placeholder-user.png');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('id');
    }
}
