<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsuarioTrayectoria extends Model
{
    use HasFactory;

    protected $table = 'usuario_trayectorias';

    protected $fillable = [
        'user_id',
        'cargo',
        'fecha_inicio',
        'fecha_fin',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
