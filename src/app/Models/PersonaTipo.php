<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'codigo',
    'nombre',
    'descripcion',
    'activo',
])]
class PersonaTipo extends Model
{
    protected $table = 'persona_tipos';

    protected $primaryKey = 'id_persona_tipo';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function inscripcionPersonas()
    {
        return $this->hasMany(InscripcionPersona::class, 'id_persona_tipo', 'id_persona_tipo');
    }
}