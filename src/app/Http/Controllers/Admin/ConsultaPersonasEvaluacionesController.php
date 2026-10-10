<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Consultas de lectura para la administración de la Olimpíada.
 * Las evaluaciones se abren a través de la ruta de PDF protegida existente.
 */
class ConsultaPersonasEvaluacionesController extends Controller
{
    private const ROLES_ADMIN = ['admin', 'administrator', 'administrador'];

    private function autorizar(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && in_array(mb_strtolower(trim((string) ($user->role ?? ''))), self::ROLES_ADMIN, true),
            403
        );
        abort_if(isset($user->active) && ! $user->active, 403);
    }

    private function filtros(Request $request): array
    {
        $valor = $request->query('olimpiada');
        $olimpiada = null;
        if ($valor !== null && $valor !== '') {
            abort_unless(is_scalar($valor) && ctype_digit((string) $valor) && (int) $valor > 0, 422);
            $olimpiada = (int) $valor;
        }

        $q = $request->query('q', '');
        $region = $request->query('region', '');
        abort_unless(is_string($q) && is_string($region), 422);
        $q = trim($q);
        $region = trim($region);
        abort_if(mb_strlen($q) > 150 || mb_strlen($region) > 150, 422);

        return [$olimpiada, $q, $region];
    }

    private function columna(string $tabla, array $opciones): string
    {
        foreach ($opciones as $opcion) {
            if (Schema::hasColumn($tabla, $opcion)) {
                return $opcion;
            }
        }
        abort(500, 'No se encontró la columna esperada en ' . $tabla);
    }

    private function opcional(string $tabla, array $opciones): ?string
    {
        foreach ($opciones as $opcion) {
            if (Schema::hasColumn($tabla, $opcion)) {
                return $opcion;
            }
        }
        return null;
    }

    /** Mantiene exactamente las reglas de región del listado de escuelas v2. */
    private function regionDatos(): array
    {
        $columna = $this->opcional('escuelas', [
            'region_geografica', 'region_educativa', 'nombre_region', 'region', 'zona',
        ]);
        if ($columna !== null) {
            return [
                'join' => false,
                'sql' => "COALESCE(NULLIF(BTRIM(CAST(e.\"{$columna}\" AS TEXT)), ''), 'Sin región')",
                'origen' => 'base',
            ];
        }

        $fk = $this->opcional('escuelas', ['id_region', 'region_id']);
        if ($fk !== null && Schema::hasTable('regiones')) {
            $pk = $this->opcional('regiones', ['id_region', 'id']);
            $nombre = $this->opcional('regiones', ['nombre', 'nombre_region', 'descripcion', 'region']);
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

        $p = "LOWER(BTRIM(TRANSLATE(COALESCE(CAST(e.provincia AS TEXT), ''), "
            . "'ÁÉÍÓÚÜáéíóúü', 'AEIOUUaeiouu')))";
        return [
            'join' => false,
            'origen' => 'provincia',
            'sql' => "CASE\n"
                . " WHEN {$p} IN ('catamarca', 'jujuy', 'salta', 'santiago del estero', 'tucuman') THEN 'NOA'\n"
                . " WHEN {$p} IN ('chaco', 'corrientes', 'formosa', 'misiones') THEN 'NEA'\n"
                . " WHEN {$p} IN ('mendoza', 'san juan', 'san luis') THEN 'Cuyo'\n"
                . " WHEN {$p} IN ('chubut', 'neuquen', 'rio negro', 'santa cruz', 'tierra del fuego', 'tierra del fuego, antartida e islas del atlantico sur') THEN 'Patagonia'\n"
                . " WHEN {$p} IN ('buenos aires', 'provincia de buenos aires', 'capital federal', 'caba', 'ciudad autonoma de buenos aires', 'cordoba', 'entre rios', 'la pampa', 'santa fe') THEN 'Pampeana'\n"
                . " ELSE 'Sin región' END",
        ];
    }

    private function unirRegion($consulta, array $region)
    {
        if ($region['join']) {
            $consulta->leftJoin('regiones AS r', 'r.' . $region['pk'], '=', 'e.' . $region['fk']);
        }
        return $consulta;
    }

    private function opcionesRegiones(array $region): array
    {
        return $this->unirRegion(DB::table('escuelas AS e'), $region)
            ->selectRaw("{$region['sql']} AS region_nombre")
            ->distinct()
            ->orderBy('region_nombre')
            ->pluck('region_nombre')
            ->all();
    }

    private function olimpiadas()
    {
        return DB::table('olimpiadas')
            ->orderByDesc('id_olimpiada')
            ->get(['id_olimpiada', 'nombre']);
    }

    private function patron(string $q): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    }

    public function personas(Request $request): View
    {
        $this->autorizar($request);
        [$idOlimpiada, $busqueda, $region] = $this->filtros($request);
        $regionDatos = $this->regionDatos();
        $listaRegiones = $this->opcionesRegiones($regionDatos);
        abort_if($region !== '' && ! in_array($region, $listaRegiones, true), 422);

        $fkInscripcion = $this->columna('inscripcion_personas', ['id_inscripcion', 'inscripcion_id']);
        $fkPersona = $this->columna('inscripcion_personas', ['id_persona', 'persona_id']);

        $consulta = DB::table('personas AS p')
            ->leftJoin('inscripcion_personas AS ip', 'ip.' . $fkPersona, '=', 'p.id_persona')
            ->leftJoin('inscripciones AS i', 'i.id_inscripcion', '=', 'ip.' . $fkInscripcion)
            ->leftJoin('escuelas AS e', 'e.id_escuela', '=', 'i.id_escuela');
        $consulta = $this->unirRegion($consulta, $regionDatos)
            ->when($idOlimpiada !== null, fn ($builder) => $builder->where('i.id_olimpiada', $idOlimpiada))
            ->when($region !== '', fn ($builder) => $builder->whereRaw("({$regionDatos['sql']}) = ?", [$region]))
            ->when($busqueda !== '', function ($builder) use ($busqueda) {
                $patron = $this->patron($busqueda);
                $builder->where(function ($sub) use ($patron) {
                    $sub->where('p.apellido', 'ILIKE', $patron)
                        ->orWhere('p.nombre', 'ILIKE', $patron)
                        ->orWhereRaw('CAST(p.numero_documento AS TEXT) ILIKE ?', [$patron])
                        ->orWhere('p.email', 'ILIKE', $patron)
                        ->orWhere('e.nombre', 'ILIKE', $patron);
                });
            });

        // Agrupar evita que una persona que participa en varias ediciones se repita.
        $personas = $consulta
            ->select('p.id_persona AS id', 'p.apellido', 'p.nombre', 'p.tipo_documento', 'p.numero_documento', 'p.email')
            ->selectRaw('COUNT(DISTINCT ip.id_inscripcion_persona) AS participaciones')
            ->selectRaw("COALESCE(STRING_AGG(DISTINCT CAST(e.nombre AS TEXT), ', ' ORDER BY CAST(e.nombre AS TEXT)), '') AS escuelas")
            ->selectRaw("COALESCE(STRING_AGG(DISTINCT CAST(ip.categoria AS TEXT), ', ' ORDER BY CAST(ip.categoria AS TEXT)), '') AS categorias")
            ->groupBy('p.id_persona', 'p.apellido', 'p.nombre', 'p.tipo_documento', 'p.numero_documento', 'p.email')
            ->orderBy('p.apellido')->orderBy('p.nombre')->orderBy('p.id_persona')
            ->paginate(30)->withQueryString();

        $olimpiadas = $this->olimpiadas();
        $regionOrigen = $regionDatos['origen'];
        return view('admin.olimpiada.personas', compact(
            'personas', 'idOlimpiada', 'busqueda', 'region', 'listaRegiones', 'olimpiadas', 'regionOrigen'
        ));
    }

    public function evaluaciones(Request $request): View
    {
        $this->autorizar($request);
        [$idOlimpiada, $busqueda, $region] = $this->filtros($request);
        $regionDatos = $this->regionDatos();
        $listaRegiones = $this->opcionesRegiones($regionDatos);
        abort_if($region !== '' && ! in_array($region, $listaRegiones, true), 422);

        $fkInscripcion = $this->columna('inscripcion_personas', ['id_inscripcion', 'inscripcion_id']);
        $fkPersona = $this->columna('inscripcion_personas', ['id_persona', 'persona_id']);
        $fkAlumno = $this->columna('evaluaciones', ['id_inscripcion_persona', 'inscripcion_persona_id']);
        $fkEvaluacion = $this->columna('evaluacion_archivos', ['id_evaluacion', 'evaluacion_id']);
        $vigente = $this->columna('evaluacion_archivos', ['vigente', 'es_vigente']);
        $fechaColumna = $this->opcional('evaluacion_archivos', ['created_at', 'fecha_carga', 'fecha_alta']);

        $consulta = DB::table('evaluacion_archivos AS ea')
            ->join('evaluaciones AS ev', 'ev.id_evaluacion', '=', 'ea.' . $fkEvaluacion)
            ->join('inscripcion_personas AS ip', 'ip.id_inscripcion_persona', '=', 'ev.' . $fkAlumno)
            ->join('personas AS p', 'p.id_persona', '=', 'ip.' . $fkPersona)
            ->join('inscripciones AS i', 'i.id_inscripcion', '=', 'ip.' . $fkInscripcion)
            ->join('escuelas AS e', 'e.id_escuela', '=', 'i.id_escuela')
            ->leftJoin('olimpiadas AS o', 'o.id_olimpiada', '=', 'i.id_olimpiada')
            ->where('ea.' . $vigente, true);
        $consulta = $this->unirRegion($consulta, $regionDatos)
            ->when($idOlimpiada !== null, fn ($builder) => $builder->where('i.id_olimpiada', $idOlimpiada))
            ->when($region !== '', fn ($builder) => $builder->whereRaw("({$regionDatos['sql']}) = ?", [$region]))
            ->when($busqueda !== '', function ($builder) use ($busqueda) {
                $patron = $this->patron($busqueda);
                $builder->where(function ($sub) use ($patron) {
                    $sub->where('e.nombre', 'ILIKE', $patron)
                        ->orWhere('p.apellido', 'ILIKE', $patron)
                        ->orWhere('p.nombre', 'ILIKE', $patron)
                        ->orWhereRaw('CAST(p.numero_documento AS TEXT) ILIKE ?', [$patron]);
                });
            });

        $evaluaciones = $consulta
            ->select('ea.id_archivo AS id', 'ea.nombre_original', 'e.id_escuela', 'e.nombre AS escuela',
                'p.apellido', 'p.nombre', 'p.numero_documento', 'ip.categoria', 'i.numero AS inscripcion',
                'i.id_olimpiada', 'o.nombre AS olimpiada')
            ->selectRaw("{$regionDatos['sql']} AS region_nombre")
            ->selectRaw($fechaColumna !== null ? "ea.\"{$fechaColumna}\" AS fecha_carga" : 'NULL AS fecha_carga')
            ->orderByDesc('ea.id_archivo')
            ->paginate(30)->withQueryString();

        $olimpiadas = $this->olimpiadas();
        $regionOrigen = $regionDatos['origen'];
        return view('admin.olimpiada.evaluaciones', compact(
            'evaluaciones', 'idOlimpiada', 'busqueda', 'region', 'listaRegiones', 'olimpiadas', 'regionOrigen'
        ));
    }
}
