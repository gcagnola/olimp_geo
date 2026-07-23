<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id_inscripcion',
    'id_persona',
    'id_persona_tipo',
    'categoria',
    'email_contacto',
    'observaciones',
    'activo',
    'evaluacion_bloqueada',
])]
class InscripcionPersona extends Model
{
    protected $table = 'inscripcion_personas';

    protected $primaryKey = 'id_inscripcion_persona';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'evaluacion_bloqueada' => 'boolean',
        ];
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function tipo()
    {
        return $this->belongsTo(PersonaTipo::class, 'id_persona_tipo', 'id_persona_tipo');
    }

    public function evaluacion()
    {
        return $this->hasOne(Evaluacion::class, 'id_inscripcion_persona', 'id_inscripcion_persona');
    }

    public function esAlumno(): bool
    {
        return $this->tipo?->codigo === 'alumno';
    }

    public function esResponsable(): bool
    {
        return $this->tipo?->codigo === 'responsable';
    }
}