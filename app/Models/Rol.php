<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';
    public $timestamps = false;
    protected $fillable = ['rol'];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_roles', 'rol_id', 'user_id');
    }
}
