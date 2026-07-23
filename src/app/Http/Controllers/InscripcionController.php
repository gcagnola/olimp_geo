<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\InscripcionCategoria;
use App\Models\InscripcionPersona;
use App\Models\Persona;
use App\Models\PersonaTipo;
use App\Models\Evaluacion;
use App\Models\EvaluacionArchivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InscripcionController extends Controller
{
    public function show(Inscripcion $inscripcion): View
    {
        $this->authorizeResponsibleAccess($inscripcion);

        $inscripcion->load([
            'categorias',
            'personas.persona',
            'personas.tipo',
            'personas.evaluacion.archivoVigente',
        ]);

        return view('inscripciones.show', [
            'inscripcion' => $inscripcion,
            'escuela' => $this->schoolData($inscripcion->id_escuela),
            'categoriasActivas' => $inscripcion->categorias()
                ->where('activa', true)
                ->pluck('categoria')
                ->toArray(),
            'alumnos' => $inscripcion->personas()
                ->whereHas('tipo', fn ($query) => $query->where('codigo', 'alumno'))
                ->with(['persona', 'tipo', 'evaluacion.archivoVigente'])
                ->orderBy('categoria')
                ->orderBy('id_inscripcion_persona')
                ->get(),
        ]);
    }

    public function updateCategorias(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $this->authorizeResponsibleAccess($inscripcion);

        $data = $request->validate([
            'categorias' => ['nullable', 'array'],
            'categorias.*' => ['required', Rule::in(['A', 'B'])],
        ]);

        $seleccionadas = collect($data['categorias'] ?? [])
            ->unique()
            ->values();

        $actualesActivas = $inscripcion->categorias()
            ->where('activa', true)
            ->pluck('categoria');

        $aDesactivar = $actualesActivas->diff($seleccionadas);

        if ($aDesactivar->isNotEmpty()) {
            $tipoAlumno = PersonaTipo::where('codigo', 'alumno')->firstOrFail();

            $categoriasConAlumnos = InscripcionPersona::query()
                ->where('id_inscripcion', $inscripcion->id_inscripcion)
                ->where('id_persona_tipo', $tipoAlumno->id_persona_tipo)
                ->whereIn('categoria', $aDesactivar)
                ->exists();

            if ($categoriasConAlumnos) {
                return back()->withErrors([
                    'categorias' => 'No se puede desactivar una categoría que ya tiene alumnos cargados.',
                ]);
            }
        }

        DB::transaction(function () use ($inscripcion, $seleccionadas): void {
            foreach (['A', 'B'] as $categoria) {
                InscripcionCategoria::updateOrCreate(
                    [
                        'id_inscripcion' => $inscripcion->id_inscripcion,
                        'categoria' => $categoria,
                    ],
                    [
                        'activa' => $seleccionadas->contains($categoria),
                    ]
                );
            }
        });

        return back()->with('success', 'Categorías actualizadas correctamente.');
    }

    public function storeAlumno(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $this->authorizeResponsibleAccess($inscripcion);

        $categoriasActivas = $inscripcion->categorias()
            ->where('activa', true)
            ->pluck('categoria')
            ->toArray();

        $data = $request->validate([
            'categoria' => ['required', Rule::in($categoriasActivas)],
            'apellido' => ['required', 'string', 'max:120'],
            'nombre' => ['required', 'string', 'max:120'],
            'tipo_documento' => ['required', Rule::in(['DNI'])],
            'numero_documento' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $tipoAlumno = PersonaTipo::where('codigo', 'alumno')->firstOrFail();

        DB::transaction(function () use ($inscripcion, $data, $tipoAlumno): void {
            $persona = Persona::firstOrCreate(
                [
                    'tipo_documento' => $data['tipo_documento'],
                    'numero_documento' => $data['numero_documento'],
                ],
                [
                    'apellido' => $data['apellido'],
                    'nombre' => $data['nombre'],
                    'email' => $data['email'] ?? null,
                ]
            );

            $persona->fill([
                'apellido' => $data['apellido'],
                'nombre' => $data['nombre'],
                'email' => $data['email'] ?? $persona->email,
            ])->save();

            InscripcionPersona::firstOrCreate(
                [
                    'id_inscripcion' => $inscripcion->id_inscripcion,
                    'id_persona' => $persona->id_persona,
                    'id_persona_tipo' => $tipoAlumno->id_persona_tipo,
                    'categoria' => $data['categoria'],
                ],
                [
                    'email_contacto' => $data['email'] ?? null,
                    'activo' => true,
                ]
            );
        });

        return back()->with('success', 'Alumno cargado correctamente.');
    }

    public function destroyAlumno(Inscripcion $inscripcion, InscripcionPersona $alumno): RedirectResponse
    {
        $this->authorizeResponsibleAccess($inscripcion);

        abort_unless($alumno->id_inscripcion === $inscripcion->id_inscripcion, 404);
        abort_unless($alumno->esAlumno(), 404);

        if ($alumno->evaluacion()->whereHas('archivoVigente')->exists()) {
            return back()->withErrors([
                'alumno' => 'No se puede eliminar el alumno porque ya tiene una evaluación subida.',
            ]);
        }

        $alumno->delete();

        return back()->with('success', 'Alumno eliminado correctamente.');
    }

    private function authorizeResponsibleAccess(Inscripcion $inscripcion): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        $allowed = DB::table('escuela_user')
            ->where('user_id', $user->id)
            ->where('id_escuela', $inscripcion->id_escuela)
            ->where('active', true)
            ->exists();

        abort_unless($allowed, 403);
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

    public function storeEvaluacion(Request $request, Inscripcion $inscripcion, InscripcionPersona $alumno): RedirectResponse
    {
        $this->authorizeResponsibleAccess($inscripcion);

        abort_unless($alumno->id_inscripcion === $inscripcion->id_inscripcion, 404);
        abort_unless($alumno->esAlumno(), 404);

        if ($alumno->evaluacion_bloqueada) {
            return back()->withErrors([
                'evaluacion' => 'La evaluación de este alumno está bloqueada por administración.',
            ]);
        }

        $data = $request->validate([
            'evaluacion' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $data['evaluacion'];

        DB::transaction(function () use ($alumno, $file): void {
            $evaluacion = Evaluacion::firstOrCreate(
                [
                    'id_inscripcion_persona' => $alumno->id_inscripcion_persona,
                ],
                [
                    'estado' => 'presentada',
                ]
            );

            EvaluacionArchivo::where('id_evaluacion', $evaluacion->id_evaluacion)
                ->where('vigente', true)
                ->update(['vigente' => false]);

            $version = EvaluacionArchivo::where('id_evaluacion', $evaluacion->id_evaluacion)
                ->max('version');

            $version = ((int) $version) + 1;

            $nombreInterno = Str::uuid()->toString() . '.pdf';

            $ruta = $file->storeAs(
                'evaluaciones/' . $evaluacion->id_evaluacion,
                $nombreInterno,
                'local'
            );

            EvaluacionArchivo::create([
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'user_id' => auth()->id(),
                'nombre_original' => $file->getClientOriginalName(),
                'nombre_interno' => $nombreInterno,
                'ruta' => $ruta,
                'mime' => $file->getMimeType() ?: 'application/pdf',
                'tamano' => $file->getSize(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
                'version' => $version,
                'vigente' => true,
                'observacion' => null,
            ]);

            $evaluacion->forceFill([
                'estado' => 'presentada',
            ])->save();
        });

        return back()->with('success', 'Evaluación subida correctamente.');
    }

    public function destroyEvaluacion(Inscripcion $inscripcion, InscripcionPersona $alumno): RedirectResponse
    {
        $this->authorizeResponsibleAccess($inscripcion);

        abort_unless($alumno->id_inscripcion === $inscripcion->id_inscripcion, 404);
        abort_unless($alumno->esAlumno(), 404);

        if ($alumno->evaluacion_bloqueada) {
            return back()->withErrors([
                'evaluacion' => 'La evaluación de este alumno está bloqueada por administración.',
            ]);
        }

        $evaluacion = $alumno->evaluacion;

        if (! $evaluacion) {
            return back();
        }

        DB::transaction(function () use ($evaluacion): void {
            $evaluacion->archivos()
                ->where('vigente', true)
                ->update(['vigente' => false]);

            $evaluacion->forceFill([
                'estado' => 'pendiente',
            ])->save();
        });

        return back()->with('success', 'Evaluación eliminada correctamente.');
    }

    public function showEvaluacion(Inscripcion $inscripcion, InscripcionPersona $alumno)
    {
        $this->authorizeResponsibleAccess($inscripcion);

        abort_unless($alumno->id_inscripcion === $inscripcion->id_inscripcion, 404);
        abort_unless($alumno->esAlumno(), 404);

        $archivo = $alumno->evaluacion?->archivoVigente;

        abort_unless($archivo, 404);

        $path = Storage::disk('local')->path($archivo->ruta);

        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $archivo->nombre_original . '"',
        ]);
    }
}