<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Assuming HasFactory is needed, as it's in the instruction

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_roles', 'user_id', 'rol_id');
    }

    public function hasRole($role)
    {
        return $this->roles->contains('rol', $role);
    }

    public function isAdmin()
    {
        return $this->hasRole('user_admin');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'user_id');
    }
}
