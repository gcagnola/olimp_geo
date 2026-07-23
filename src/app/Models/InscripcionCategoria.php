<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id_inscripcion',
    'categoria',
    'activa',
])]
class InscripcionCategoria extends Model
{
    protected $table = 'inscripcion_categorias';

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }
}