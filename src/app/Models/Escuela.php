<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escuela extends Model
{
    protected $table = 'escuelas';

    protected $primaryKey = 'id_escuela';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id_escuela',
        'nombre',
        'cue',
        'anexo',
        'localidad',
        'provincia',
        'region',
        'subregion',
        'detalle',
    ];
}
