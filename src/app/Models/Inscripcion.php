<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id_olimpiada',
    'id_escuela',
    'numero',
    'auditoria_origen',
    'estado',
])]
class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    protected $primaryKey = 'id_inscripcion';

    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    public function categorias()
    {
        return $this->hasMany(InscripcionCategoria::class, 'id_inscripcion', 'id_inscripcion');
    }

    public function personas()
    {
        return $this->hasMany(InscripcionPersona::class, 'id_inscripcion', 'id_inscripcion');
    }

    public function alumnos()
    {
        return $this->personas()
            ->whereHas('tipo', fn ($query) => $query->where('codigo', 'alumno'));
    }

    public function responsables()
    {
        return $this->personas()
            ->whereHas('tipo', fn ($query) => $query->where('codigo', 'responsable'));
    }
}