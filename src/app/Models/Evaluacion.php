<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id_inscripcion_persona',
    'estado',
])]
class Evaluacion extends Model
{
    protected $table = 'evaluaciones';

    protected $primaryKey = 'id_evaluacion';

    public function inscripcionPersona()
    {
        return $this->belongsTo(
            InscripcionPersona::class,
            'id_inscripcion_persona',
            'id_inscripcion_persona'
        );
    }

    public function archivos()
    {
        return $this->hasMany(EvaluacionArchivo::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function archivoVigente()
    {
        return $this->hasOne(EvaluacionArchivo::class, 'id_evaluacion', 'id_evaluacion')
            ->where('vigente', true)
            ->latest('id_archivo');
    }
}