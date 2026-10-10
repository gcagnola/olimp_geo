@extends('layouts.app')

@section('title', 'Administración - Detalle de escuela')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">{{ $datosEscuela->nombre }}</h1>
            <div class="text-muted">CUE: {{ $datosEscuela->cue ?? '—' }} · Anexo: {{ $datosEscuela->anexo ?? '—' }} · {{ $datosEscuela->localidad ?? '' }} {{ $datosEscuela->provincia ?? '' }}</div>
        </div>
        <a href="{{ route('admin.olimpiada.escuelas.index', ['olimpiada' => $idOlimpiada, 'region' => request()->query('region'), 'q' => request()->query('q')]) }}" class="btn btn-outline-secondary btn-sm">Volver a escuelas</a>
    </div>

    @forelse($inscripciones as $inscripcion)
        @php($alumnos = $alumnosPorInscripcion->get($inscripcion->id, collect()))
        <section class="card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <strong>Inscripción Nº {{ $inscripcion->numero ?? $inscripcion->id }} · {{ $inscripcion->nombre_olimpiada ?? ('Edición #' . $inscripcion->id_olimpiada) }}</strong>
                <div><span class="badge text-bg-secondary">{{ $inscripcion->estado ?? 'Sin estado' }}</span>
                    <span class="text-muted small ms-2">{{ $alumnos->count() }} alumnos · {{ $alumnos->whereNotNull('archivo_id')->count() }} con PDF</span></div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th>Categoría</th><th>Apellido y nombre</th><th>Documento</th><th>Email</th><th>PDF evaluación</th>
                    </tr></thead>
                    <tbody>
                    @forelse($alumnos as $alumno)
                        <tr>
                            <td>{{ $alumno->categoria ?? '—' }}</td>
                            <td>{{ $alumno->apellido }}, {{ $alumno->nombre }}</td>
                            <td>{{ $alumno->tipo_documento ?? '' }} {{ $alumno->numero_documento ?? '—' }}</td>
                            <td>{{ $alumno->email ?: '—' }}</td>
                            <td>
                                @if($alumno->archivo_id)
                                    <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="{{ route('admin.olimpiada.evaluaciones.pdf', $alumno->archivo_id) }}">Ver PDF</a>
                                @else
                                    <span class="text-muted">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted p-3">Sin alumnos cargados en esta inscripción.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="alert alert-secondary">Esta escuela no tiene inscripciones para la edición seleccionada.</div>
    @endforelse
</div>
@endsection
