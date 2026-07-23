<?php

namespace App\Http\Controllers;

use App\Models\EvaluacionArchivo;
use App\Models\Inscripcion;
use App\Models\InscripcionPersona;
use App\Models\Olimpiada;
use App\Models\Persona;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $olimpiada = Olimpiada::query()
            ->where('activa', true)
            ->first();

        $inscripciones = collect();

        if ($user->isResponsible()) {
            $idsEscuela = DB::table('escuela_user')
                ->where('user_id', $user->id)
                ->where('active', true)
                ->pluck('id_escuela');

            $inscripciones = Inscripcion::query()
                ->whereIn('id_escuela', $idsEscuela)
                ->with('olimpiada')
                ->orderBy('numero')
                ->get()
                ->map(function (Inscripcion $inscripcion) {
                    $inscripcion->escuela_data = $this->schoolData($inscripcion->id_escuela);

                    return $inscripcion;
                });
        }

        $stats = [
            'olimpiada' => $olimpiada,
            'inscripciones' => Inscripcion::count(),
            'personas' => Persona::count(),
            'alumnos' => InscripcionPersona::whereHas('tipo', function ($query) {
                $query->where('codigo', 'alumno');
            })->count(),
            'evaluaciones' => EvaluacionArchivo::where('vigente', true)->count(),
        ];

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
            'inscripciones' => $inscripciones,
        ]);
    }

    private function schoolData(int $idEscuela): object
    {
        if (Schema::hasTable('escuelas')) {
            $escuela = DB::table('escuelas')
                ->where('id_escuela', $idEscuela)
                ->first();

            if ($escuela) {
                return $escuela;
            }
        }

        return (object) [
            'id_escuela' => $idEscuela,
            'nombre' => 'Escuela #' . $idEscuela,
            'cue' => null,
        ];
    }
}