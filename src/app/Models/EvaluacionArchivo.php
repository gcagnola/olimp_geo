<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id_evaluacion',
    'user_id',
    'nombre_original',
    'nombre_interno',
    'ruta',
    'mime',
    'tamano',
    'sha256',
    'version',
    'vigente',
    'observacion',
])]
class EvaluacionArchivo extends Model
{
    protected $table = 'evaluacion_archivos';

    protected $primaryKey = 'id_archivo';

    protected function casts(): array
    {
        return [
            'vigente' => 'boolean',
        ];
    }

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}