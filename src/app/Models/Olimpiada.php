<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nombre',
    'anio',
    'fecha_inicio',
    'fecha_fin',
    'fecha_inicio_inscripcion',
    'fecha_fin_inscripcion',
    'fecha_inicio_carga_evaluaciones',
    'fecha_fin_carga_evaluaciones',
    'estado',
    'activa',
])]
class Olimpiada extends Model
{
    protected $table = 'olimpiadas';

    protected $primaryKey = 'id_olimpiada';

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_inicio_inscripcion' => 'date',
            'fecha_fin_inscripcion' => 'date',
            'fecha_inicio_carga_evaluaciones' => 'date',
            'fecha_fin_carga_evaluaciones' => 'date',
            'activa' => 'boolean',
        ];
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_olimpiada', 'id_olimpiada');
    }
}