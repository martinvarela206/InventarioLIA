<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElementoAtributoValor extends Model
{
    use HasFactory;

    protected $table = 'elemento_atributo_valor';

    protected $fillable = [
        'elemento_id',
        'atributo_id',
        'valor',
    ];

    public function elemento(): BelongsTo
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    public function atributo(): BelongsTo
    {
        return $this->belongsTo(Atributo::class, 'atributo_id');
    }
}
