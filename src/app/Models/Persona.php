<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'apellido',
    'nombre',
    'tipo_documento',
    'numero_documento',
    'email',
    'telefono',
])]
class Persona extends Model
{
    protected $table = 'personas';

    protected $primaryKey = 'id_persona';

    public function inscripciones()
    {
        return $this->hasMany(InscripcionPersona::class, 'id_persona', 'id_persona');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido . ', ' . $this->nombre);
    }
}