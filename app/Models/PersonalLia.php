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

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('id');
    }
}
