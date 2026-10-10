@extends('layouts.app')

@section('title', 'Administración - Personas')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Personas registradas</h1>
            <div class="text-muted">Administración de la Olimpíada · Solo consulta</div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver al panel</a>
    </div>

    <nav class="d-flex flex-wrap gap-2 mb-3" aria-label="Secciones de administración">
        <a href="{{ route('admin.olimpiada.escuelas.index', request()->only(['olimpiada', 'region'])) }}" class="btn btn-outline-secondary btn-sm">Inscripciones</a>
        <a href="{{ route('admin.olimpiada.personas.index', request()->only(['olimpiada', 'region'])) }}" class="btn btn-primary btn-sm" aria-current="page">Personas</a>
        <a href="{{ route('admin.olimpiada.evaluaciones.index', request()->only(['olimpiada', 'region'])) }}" class="btn btn-outline-secondary btn-sm">Evaluaciones</a>
    </nav>

    <form method="GET" action="{{ route('admin.olimpiada.personas.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="q">Apellido, nombre, DNI, email o escuela</label>
                <input type="search" class="form-control" id="q" name="q" maxlength="150" value="{{ $busqueda }}" placeholder="Buscar persona">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="olimpiada">Edición</label>
                <select class="form-select" id="olimpiada" name="olimpiada">
                    <option value="">Todas las ediciones</option>
                    @foreach($olimpiadas as $opcion)
                        <option value="{{ $opcion->id_olimpiada }}" @selected($idOlimpiada === (int) $opcion->id_olimpiada)>{{ $opcion->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="region">Región</label>
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
            <strong>Personas</strong>
            <span class="text-muted small">{{ number_format($personas->total(), 0, ',', '.') }} personas encontradas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Apellido y nombre</th>
                        <th>Documento</th>
                        <th>Email</th>
                        <th>Escuelas asociadas</th>
                        <th>Categorías</th>
                        <th class="text-center">Participaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personas as $persona)
                        <tr>
                            <td><strong>{{ $persona->apellido }}, {{ $persona->nombre }}</strong></td>
                            <td>{{ $persona->tipo_documento ?: '—' }} {{ $persona->numero_documento ?: '' }}</td>
                            <td>{{ $persona->email ?: '—' }}</td>
                            <td>{{ $persona->escuelas ?: '—' }}</td>
                            <td>{{ $persona->categorias ?: '—' }}</td>
                            <td class="text-center">{{ number_format((int) $persona->participaciones, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted p-4">No se encontraron personas con los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($personas->hasPages())
            <div class="card-footer">{{ $personas->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
