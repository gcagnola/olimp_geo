@extends('layouts.app')

@section('title', 'Administración - Evaluaciones')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Evaluaciones recibidas</h1>
            <div class="text-muted">Archivos PDF vigentes · Solo consulta</div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver al panel</a>
    </div>

    <nav class="d-flex flex-wrap gap-2 mb-3" aria-label="Secciones de administración">
        <a href="{{ route('admin.olimpiada.escuelas.index', request()->only(['olimpiada', 'region'])) }}" class="btn btn-outline-secondary btn-sm">Inscripciones</a>
        <a href="{{ route('admin.olimpiada.personas.index', request()->only(['olimpiada', 'region'])) }}" class="btn btn-outline-secondary btn-sm">Personas</a>
        <a href="{{ route('admin.olimpiada.evaluaciones.index', request()->only(['olimpiada', 'region'])) }}" class="btn btn-primary btn-sm" aria-current="page">Evaluaciones</a>
    </nav>

    <form method="GET" action="{{ route('admin.olimpiada.evaluaciones.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label">Escuela, alumno o DNI</label>
                <input type="search" class="form-control" id="q" name="q" maxlength="150" value="{{ $busqueda }}" placeholder="Buscar evaluación">
            </div>
            <div class="col-md-3">
                <label for="olimpiada" class="form-label">Edición</label>
                <select class="form-select" id="olimpiada" name="olimpiada">
                    <option value="">Todas las ediciones</option>
                    @foreach($olimpiadas as $opcion)
                        <option value="{{ $opcion->id_olimpiada }}" @selected($idOlimpiada === (int) $opcion->id_olimpiada)>{{ $opcion->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="region" class="form-label">Región</label>
                <select class="form-select" id="region" name="region">
                    <option value="">Todas las regiones</option>
                    @foreach($listaRegiones as $opcion)
                        <option value="{{ $opcion }}" @selected($region === $opcion)>{{ $opcion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
        @if($regionOrigen === 'provincia')
            <div class="form-text mt-2">La región se obtiene de la provincia de la escuela, usando las cinco macroregiones argentinas.</div>
        @endif
    </form>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong>PDF vigentes</strong>
            <span class="text-muted small">{{ number_format($evaluaciones->total(), 0, ',', '.') }} archivos encontrados</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Escuela</th><th>Región</th><th>Alumno</th><th>Categoría</th>
                        <th>Edición / inscripción</th><th>Fecha de carga</th><th>Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evaluaciones as $evaluacion)
                        <tr>
                            <td>
                                <a href="{{ route('admin.olimpiada.escuelas.show', ['escuela' => $evaluacion->id_escuela, 'olimpiada' => $evaluacion->id_olimpiada]) }}">
                                    {{ $evaluacion->escuela }}
                                </a>
                            </td>
                            <td>{{ $evaluacion->region_nombre }}</td>
                            <td>{{ $evaluacion->apellido }}, {{ $evaluacion->nombre }}
                                <div class="text-muted small">DNI: {{ $evaluacion->numero_documento ?? '—' }}</div>
                            </td>
                            <td>{{ $evaluacion->categoria ?: '—' }}</td>
                            <td>{{ $evaluacion->olimpiada ?: ('Edición #' . $evaluacion->id_olimpiada) }}<div class="text-muted small">Inscripción Nº {{ $evaluacion->inscripcion ?? '—' }}</div></td>
                            <td class="text-nowrap">{{ $evaluacion->fecha_carga ? substr((string) $evaluacion->fecha_carga, 0, 16) : '—' }}</td>
                            <td>
                                <a href="{{ route('admin.olimpiada.evaluaciones.pdf', $evaluacion->id) }}"
                                   class="btn btn-outline-primary btn-sm text-nowrap" target="_blank" rel="noopener"
                                   title="{{ $evaluacion->nombre_original ?: 'Evaluación PDF' }}">
                                    Ver PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted p-4">No se encontraron PDF vigentes con los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($evaluaciones->hasPages())
            <div class="card-footer">{{ $evaluaciones->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
