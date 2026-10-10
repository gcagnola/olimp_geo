<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * Consulta administrativa, exclusivamente de lectura. No usa inscripciones_excel.
 */
class ConsultaEscuelasController extends Controller
{
    private const ROLES_ADMIN = ['admin', 'administrator', 'administrador'];

    private function autorizar(Request $request): void
    {
        $usuario = $request->user();
        abort_unless($usuario && in_array(mb_strtolower(trim((string) ($usuario->role ?? ''))), self::ROLES_ADMIN, true), 403);
        abort_if(isset($usuario->active) && ! $usuario->active, 403);
    }

    private function olimpiada(Request $request): ?int
    {
        $valor = $request->query('olimpiada');
        if ($valor === null || $valor === '') {
            return null;
        }
        abort_unless(ctype_digit((string) $valor) && (int) $valor > 0, 422);
        return (int) $valor;
    }

    /** Obtiene un nombre de columna existente, sin inventar claves foráneas. */
    private function columna(string $tabla, array $alternativas): string
    {
        foreach ($alternativas as $columna) {
            if (Schema::hasColumn($tabla, $columna)) {
                return $columna;
            }
        }

        abort(500, 'Esquema no reconocido en ' . $tabla . ': ' . implode(', ', $alternativas));
    }

    private function columnaOpcional(string $tabla, array $alternativas): ?string
    {
        foreach ($alternativas as $columna) {
            if (Schema::hasColumn($tabla, $columna)) {
                return $columna;
            }
        }
        return null;
    }

    /**
     * Usa regiones definidas en la base si existen; de otro modo clasifica
     * las provincias en las cinco regiones geográficas habituales.
     * No crea columnas, tablas ni altera los datos de las escuelas.
     *
     * @return array{join: bool, sql: string, origen: string, fk?: string, pk?: string}
     */
    private function expresionRegion(): array
    {
        // Preferir una región consignada explícitamente en escuelas.
        $columna = $this->columnaOpcional('escuelas', [
            'region_geografica', 'region_educativa', 'nombre_region', 'region', 'zona',
        ]);
        if ($columna !== null) {
            return [
                'join' => false,
                'sql' => "COALESCE(NULLIF(BTRIM(CAST(e.\"{$columna}\" AS TEXT)), ''), 'Sin región')",
                'origen' => 'base',
            ];
        }

        // En segundo lugar, relación escuelas -> regiones (si existiera).
        $fk = $this->columnaOpcional('escuelas', ['id_region', 'region_id']);
        if ($fk !== null && Schema::hasTable('regiones')) {
            $pk = $this->columnaOpcional('regiones', ['id_region', 'id']);
            $nombre = $this->columnaOpcional('regiones', ['nombre', 'nombre_region', 'descripcion', 'region']);
            if ($pk !== null && $nombre !== null) {
                return [
                    'join' => true,
                    'fk' => $fk,
                    'pk' => $pk,
                    'sql' => "COALESCE(NULLIF(BTRIM(CAST(r.\"{$nombre}\" AS TEXT)), ''), 'Sin región')",
                    'origen' => 'base',
                ];
            }
        }

        // Fallback sin datos regionales propios: cinco macroregiones de Argentina.
        // Normaliza tildes y mayúsculas en la provincia existente.
        $p = "LOWER(BTRIM(TRANSLATE(COALESCE(CAST(e.provincia AS TEXT), ''), "
           . "'ÁÉÍÓÚÜáéíóúü', 'AEIOUUaeiouu')))";

        return [
            'join' => false,
            'sql' => "CASE\n"
                . " WHEN {$p} IN ('catamarca', 'jujuy', 'salta', 'santiago del estero', 'tucuman') THEN 'NOA'\n"
                . " WHEN {$p} IN ('chaco', 'corrientes', 'formosa', 'misiones') THEN 'NEA'\n"
                . " WHEN {$p} IN ('mendoza', 'san juan', 'san luis') THEN 'Cuyo'\n"
                . " WHEN {$p} IN ('chubut', 'neuquen', 'rio negro', 'santa cruz', 'tierra del fuego', 'tierra del fuego, antartida e islas del atlantico sur') THEN 'Patagonia'\n"
                . " WHEN {$p} IN ('buenos aires', 'provincia de buenos aires', 'capital federal', 'caba', 'ciudad autonoma de buenos aires', 'cordoba', 'entre rios', 'la pampa', 'santa fe') THEN 'Pampeana'\n"
                . " ELSE 'Sin región' END",
            'origen' => 'provincia',
        ];
    }

    private function unirRegiones($consulta, array $regionDatos)
    {
        if ($regionDatos['join']) {
            $consulta->leftJoin('regiones AS r', 'r.' . $regionDatos['pk'], '=', 'e.' . $regionDatos['fk']);
        }
        return $consulta;
    }

    public function index(Request $request)
    {
        $this->autorizar($request);
        $idOlimpiada = $this->olimpiada($request);
        $busqueda = trim((string) $request->query('q', ''));
        $region = trim((string) $request->query('region', ''));
        abort_if(mb_strlen($busqueda) > 150 || mb_strlen($region) > 150, 422);

        $regionDatos = $this->expresionRegion();
        $regionSql = $regionDatos['sql'];

        // Valores de la región REALMENTE presentes en escuelas.
        $listaRegiones = $this->unirRegiones(DB::table('escuelas AS e'), $regionDatos)
            ->selectRaw("{$regionSql} AS region_nombre")
            ->distinct()
            ->orderBy('region_nombre')
            ->pluck('region_nombre')
            ->all();

        if ($region !== '') {
            abort_unless(in_array($region, $listaRegiones, true), 422, 'Región no reconocida.');
        }

        // Contar inscripciones, alumnos y PDFs vigentes por escuela sin
        // inflar las cifras debido a las relaciones 1:N o versiones antiguas.
        $fkInscripcion = $this->columna('inscripcion_personas', ['id_inscripcion', 'inscripcion_id']);
        $fkAlumno = $this->columna('evaluaciones', ['id_inscripcion_persona', 'inscripcion_persona_id']);
        $fkEvaluacion = $this->columna('evaluacion_archivos', ['id_evaluacion', 'evaluacion_id']);
        $vigente = $this->columna('evaluacion_archivos', ['vigente', 'es_vigente']);

        $conteos = DB::table('inscripciones AS i')
            ->leftJoin('inscripcion_personas AS ip', 'ip.' . $fkInscripcion, '=', 'i.id_inscripcion')
            ->leftJoin('evaluaciones AS ev', 'ev.' . $fkAlumno, '=', 'ip.id_inscripcion_persona')
            ->leftJoin('evaluacion_archivos AS ea', function ($join) use ($fkEvaluacion, $vigente) {
                $join->on('ea.' . $fkEvaluacion, '=', 'ev.id_evaluacion')
                    ->where('ea.' . $vigente, '=', true);
            })
            ->when($idOlimpiada !== null, fn ($q) => $q->where('i.id_olimpiada', $idOlimpiada))
            ->select('i.id_escuela')
            ->selectRaw('COUNT(DISTINCT i.id_inscripcion) AS cantidad_inscripciones')
            ->selectRaw('COUNT(DISTINCT ip.id_inscripcion_persona) AS cantidad_alumnos')
            ->selectRaw('COUNT(DISTINCT ea.id_archivo) AS cantidad_pdfs')
            ->groupBy('i.id_escuela');

        $consulta = $this->unirRegiones(DB::table('escuelas AS e'), $regionDatos)
            ->leftJoinSub($conteos, 'c', fn ($join) => $join->on('c.id_escuela', '=', 'e.id_escuela'))
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $patron = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busqueda) . '%';
                $q->where(function ($sub) use ($patron) {
                    $sub->where('e.nombre', 'ILIKE', $patron)
                        ->orWhere('e.localidad', 'ILIKE', $patron)
                        ->orWhere('e.provincia', 'ILIKE', $patron)
                        ->orWhereRaw('CAST(e.cue AS TEXT) ILIKE ?', [$patron]);
                });
            })
            ->when($region !== '', fn ($q) => $q->whereRaw("({$regionSql}) = ?", [$region]));

        // Totales de TODAS las escuelas filtradas, no solo de la página actual.
        $totales = (clone $consulta)
            ->selectRaw('COUNT(*) AS escuelas')
            ->selectRaw('COALESCE(SUM(c.cantidad_inscripciones), 0) AS inscripciones')
            ->selectRaw('COALESCE(SUM(c.cantidad_alumnos), 0) AS alumnos')
            ->selectRaw('COALESCE(SUM(c.cantidad_pdfs), 0) AS pdfs')
            ->first();

        $escuelas = $consulta
            ->select('e.id_escuela AS id', 'e.nombre', 'e.cue', 'e.anexo', 'e.localidad', 'e.provincia')
            ->selectRaw("{$regionSql} AS region_nombre")
            ->selectRaw('COALESCE(c.cantidad_inscripciones, 0) AS cantidad_inscripciones')
            ->selectRaw('COALESCE(c.cantidad_alumnos, 0) AS cantidad_alumnos')
            ->selectRaw('COALESCE(c.cantidad_pdfs, 0) AS cantidad_pdfs')
            ->orderBy('e.nombre')
            ->orderBy('e.id_escuela')
            ->paginate(30)
            ->withQueryString();

        $olimpiadas = DB::table('olimpiadas')->orderByDesc('id_olimpiada')
            ->get(['id_olimpiada', 'nombre']);

        $regionOrigen = $regionDatos['origen'];
        return view('admin.olimpiada.escuelas', compact(
            'escuelas', 'olimpiadas', 'busqueda', 'idOlimpiada',
            'region', 'listaRegiones', 'regionOrigen', 'totales'
        ));
    }

    public function escuela(Request $request, int $escuela)
    {
        $this->autorizar($request);
        $idOlimpiada = $this->olimpiada($request);
        $datosEscuela = DB::table('escuelas')->where('id_escuela', $escuela)->first();
        abort_unless($datosEscuela, 404);

        $inscripciones = DB::table('inscripciones AS i')
            ->leftJoin('olimpiadas AS o', 'o.id_olimpiada', '=', 'i.id_olimpiada')
            ->where('i.id_escuela', $escuela)
            ->when($idOlimpiada, fn ($q) => $q->where('i.id_olimpiada', $idOlimpiada))
            ->orderByDesc('i.id_olimpiada')
            ->orderBy('i.id_inscripcion')
            ->select('i.*', 'i.id_inscripcion AS id', 'o.nombre AS nombre_olimpiada')
            ->get();

        $alumnosPorInscripcion = collect();
        $idsInscripciones = $inscripciones->pluck('id')->all();
        if ($idsInscripciones) {
            $fkInscripcion = $this->columna('inscripcion_personas', ['id_inscripcion', 'inscripcion_id']);
            $fkPersona = $this->columna('inscripcion_personas', ['id_persona', 'persona_id']);
            $alumnos = DB::table('inscripcion_personas AS ip')
                ->join('personas AS p', 'p.id_persona', '=', 'ip.' . $fkPersona)
                ->whereIn('ip.' . $fkInscripcion, $idsInscripciones)
                ->select('ip.*', 'ip.id_inscripcion_persona AS id', 'p.apellido', 'p.nombre', 'p.tipo_documento', 'p.numero_documento', 'p.email')
                ->orderBy('p.apellido')->orderBy('p.nombre')
                ->get();

            $pdfPorAlumno = collect();
            if ($alumnos->isNotEmpty()) {
                $fkAlumno = $this->columna('evaluaciones', ['id_inscripcion_persona', 'inscripcion_persona_id']);
                $fkEvaluacion = $this->columna('evaluacion_archivos', ['id_evaluacion', 'evaluacion_id']);
                $vigente = $this->columnaOpcional('evaluacion_archivos', ['vigente', 'es_vigente']);
                // No se muestran versiones anteriores como vigentes por accidente.
                abort_unless($vigente, 500, 'No se encontró el indicador de vigencia de los archivos de evaluación.');

                $evaluaciones = DB::table('evaluaciones')
                    ->whereIn($fkAlumno, $alumnos->pluck('id')->all())
                    ->get(['id_evaluacion AS id', $fkAlumno]);

                if ($evaluaciones->isNotEmpty()) {
                    $archivos = DB::table('evaluacion_archivos')
                        ->whereIn($fkEvaluacion, $evaluaciones->pluck('id')->all())
                        ->where($vigente, true)
                        ->orderByDesc('id_archivo')
                        ->get(['id_archivo AS id', $fkEvaluacion]);
                    $archivoPorEvaluacion = $archivos->unique($fkEvaluacion)->keyBy($fkEvaluacion);
                    foreach ($evaluaciones as $evaluacion) {
                        $archivo = $archivoPorEvaluacion->get($evaluacion->id);
                        if ($archivo) {
                            $pdfPorAlumno->put($evaluacion->{$fkAlumno}, $archivo->id);
                        }
                    }
                }
            }

            foreach ($alumnos as $alumno) {
                $alumno->archivo_id = $pdfPorAlumno->get($alumno->id);
            }
            $alumnosPorInscripcion = $alumnos->groupBy($fkInscripcion);
        }

        return view('admin.olimpiada.escuela', compact('datosEscuela', 'inscripciones', 'alumnosPorInscripcion', 'idOlimpiada'));
    }

    public function pdf(Request $request, int $archivo)
    {
        $this->autorizar($request);
        $fkEvaluacion = $this->columna('evaluacion_archivos', ['id_evaluacion', 'evaluacion_id']);
        $vigente = $this->columna('evaluacion_archivos', ['vigente', 'es_vigente']);

        $registro = DB::table('evaluacion_archivos AS ea')
            ->join('evaluaciones AS ev', 'ev.id_evaluacion', '=', 'ea.' . $fkEvaluacion)
            ->where('ea.id_archivo', $archivo)
            ->where('ea.' . $vigente, true)
            ->select('ea.ruta', 'ea.nombre_original')
            ->first();
        abort_unless($registro, 404);

        $ruta = (string) $registro->ruta;
        abort_unless(str_starts_with($ruta, 'evaluaciones/') && ! str_contains($ruta, '..') && ! str_contains($ruta, "\\"), 404);
        $disco = Storage::disk('local');
        abort_unless($disco->exists($ruta), 404);

        $nombre = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) ($registro->nombre_original ?: 'evaluacion.pdf')));
        $nombre = $nombre ?: 'evaluacion.pdf';
        return response()->file($disco->path($ruta), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $nombre),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
