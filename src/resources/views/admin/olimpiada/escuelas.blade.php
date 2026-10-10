@extends('layouts.app')

@section('title', 'Administración - Escuelas')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Administración de la Olimpíada</h1>
            <div class="text-muted">Escuelas, inscripciones, alumnos y evaluaciones · Solo consulta</div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver al panel</a>
    </div>

    <form method="GET" action="{{ route('admin.olimpiada.escuelas.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="q">Escuela, CUE, localidad o provincia</label>
                <input class="form-control" name="q" id="q" maxlength="150" value="{{ $busqueda }}" placeholder="Nombre o CUE">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="olimpiada">Edición</label>
                <select class="form-select" name="olimpiada" id="olimpiada">
                    <option value="">Todas las ediciones</option>
                    @foreach($olimpiadas as $olimpiada)
                        <option value="{{ $olimpiada->id_olimpiada }}" @selected($idOlimpiada === (int) $olimpiada->id_olimpiada)>
                            {{ $olimpiada->nombre }} (#{{ $olimpiada->id_olimpiada }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="region">Región</label>
                <select class="form-select" name="region" id="region">
                    <option value="">Todas las regiones</option>
                    @foreach($listaRegiones as $opcion)
                        <option value="{{ $opcion }}" @selected($region === $opcion)>{{ $opcion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary" type="submit">Filtrar</button>
            </div>
        </div>
        @if($regionOrigen === 'provincia')
            <div class="form-text mt-2">Las regiones se agrupan por provincia (NOA, NEA, Cuyo, Pampeana y Patagonia) porque no se encontró una región definida para cada escuela en la base de datos.</div>
        @endif
    </form>

    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Escuelas</div><div class="h4 mb-0">{{ number_format((int) $totales->escuelas, 0, ',', '.') }}</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Inscripciones</div><div class="h4 mb-0">{{ number_format((int) $totales->inscripciones, 0, ',', '.') }}</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Total de alumnos</div><div class="h4 mb-0">{{ number_format((int) $totales->alumnos, 0, ',', '.') }}</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Total de PDF vigentes</div><div class="h4 mb-0">{{ number_format((int) $totales->pdfs, 0, ',', '.') }}</div>
        </div></div></div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Escuelas</strong><span class="text-muted small">{{ $escuelas->total() }} encontradas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr>
                    <th>Escuela</th><th>CUE / anexo</th><th>Localidad / provincia</th><th>Región</th>
                    <th class="text-center">Inscripciones</th><th class="text-center">Alumnos</th><th class="text-center">PDF</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($escuelas as $escuela)
                    <tr>
                        <td><strong>{{ $escuela->nombre }}</strong><div class="text-muted small">ID {{ $escuela->id }}</div></td>
                        <td>{{ $escuela->cue ?: '—' }}@if($escuela->anexo !== null && $escuela->anexo !== '') / {{ $escuela->anexo }}@endif</td>
                        <td>{{ $escuela->localidad ?: '—' }}<div class="text-muted small">{{ $escuela->provincia ?: '' }}</div></td>
                        <td>{{ $escuela->region_nombre }}</td>
                        <td class="text-center">{{ number_format((int) $escuela->cantidad_inscripciones, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format((int) $escuela->cantidad_alumnos, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format((int) $escuela->cantidad_pdfs, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-primary btn-sm text-nowrap" href="{{ route('admin.olimpiada.escuelas.show', ['escuela' => $escuela->id, 'olimpiada' => $idOlimpiada, 'region' => $region, 'q' => $busqueda]) }}">Ver inscripciones</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted p-4">No hay escuelas que coincidan con los filtros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($escuelas->hasPages())<div class="card-footer">{{ $escuelas->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</div>
@endsection
