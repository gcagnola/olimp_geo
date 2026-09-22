@extends('layouts.app')

@section('title', 'Inscripción')

@section('header-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
            Panel
        </a>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                Cerrar sesión
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xxl-11">

            <div class="campus-card mb-4">
                <div class="campus-card-header">
                    Inscripción Nº {{ $inscripcion->numero }}
                </div>

                <div class="campus-card-body">
                    <h1 class="campus-title mb-2">
                        {{ $escuela->nombre ?? ('Escuela #' . $inscripcion->id_escuela) }}
                    </h1>

                    <p class="text-muted mb-3">
                        Olimpíada:
                        <strong>{{ $inscripcion->olimpiada->nombre ?? 'Sin olimpíada' }}</strong>
                    </p>

                    <div class="row g-3">
                        <div class="col-6 col-md-2">
                            <div class="text-muted small">CUE</div>
                            <div class="fw-semibold">
                                {{ $escuela->cue ?: 'Sin dato' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <div class="text-muted small">Anexo</div>
                            <div class="fw-semibold">
                                {{ $escuela->anexo ?: 'Sin dato' }}
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="text-muted small">Localidad</div>
                            <div class="fw-semibold">
                                {{ $escuela->localidad ?: 'Sin dato' }}
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="text-muted small">Provincia</div>
                            <div class="fw-semibold">
                                {{ $escuela->provincia ?: 'Sin dato' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <div class="text-muted small">Región</div>
                            <div class="fw-semibold">
                                {{ $escuela->region ?: 'Sin dato' }}
                            </div>
                        </div>

                        @if(!empty($escuela->subregion))
                            <div class="col-12 col-md-3">
                                <div class="text-muted small">Subregión</div>
                                <div class="fw-semibold">
                                    {{ $escuela->subregion }}
                                </div>
                            </div>
                        @endif

                        @if(!empty($escuela->detalle))
                            <div class="col-12">
                                <div class="text-muted small">Detalle</div>
                                <div class="fw-semibold">
                                    {{ $escuela->detalle }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="campus-card mb-4">
                <div class="campus-card-header">
                    Categorías de participación
                </div>

                <div class="campus-card-body">
                    <form method="POST" action="{{ route('inscripciones.categorias.update', $inscripcion) }}">
                        @csrf

                        <p class="text-muted">
                            Marcá las categorías en las que participará la escuela.
                        </p>

                        <div class="d-flex flex-wrap gap-4 mb-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="categorias[]"
                                    value="A"
                                    id="categoria_a"
                                    @checked(in_array('A', $categoriasActivas, true))
                                >
                                <label class="form-check-label" for="categoria_a">
                                    Categoría A
                                </label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="categorias[]"
                                    value="B"
                                    id="categoria_b"
                                    @checked(in_array('B', $categoriasActivas, true))
                                >
                                <label class="form-check-label" for="categoria_b">
                                    Categoría B
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Guardar categorías
                        </button>
                    </form>
                </div>
            </div>

            <div class="campus-card mb-4">
                <div class="campus-card-header">
                    Cargar alumno
                </div>

                <div class="campus-card-body">
                    @if(empty($categoriasActivas))
                        <div class="alert alert-warning mb-0">
                            Primero seleccioná al menos una categoría de participación.
                        </div>
                    @else
                        <form method="POST" action="{{ route('inscripciones.alumnos.store', $inscripcion) }}">
                            @csrf

                            <div class="row gy-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="categoria">Categoría</label>
                                    <select
                                        id="categoria"
                                        name="categoria"
                                        class="form-select @error('categoria') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">Seleccionar</option>
                                        @foreach($categoriasActivas as $categoria)
                                            <option value="{{ $categoria }}" @selected(old('categoria') === $categoria)>
                                                Categoría {{ $categoria }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('categoria')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="apellido">Apellido</label>
                                    <input
                                        id="apellido"
                                        name="apellido"
                                        type="text"
                                        value="{{ old('apellido') }}"
                                        class="form-control @error('apellido') is-invalid @enderror"
                                        required
                                    >
                                    @error('apellido')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="nombre">Nombre</label>
                                    <input
                                        id="nombre"
                                        name="nombre"
                                        type="text"
                                        value="{{ old('nombre') }}"
                                        class="form-control @error('nombre') is-invalid @enderror"
                                        required
                                    >
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="tipo_documento_visible">
                                        Tipo documento
                                    </label>

                                    <input
                                        id="tipo_documento_visible"
                                        type="text"
                                        value="DNI"
                                        class="form-control"
                                        readonly
                                    >

                                    <input
                                        type="hidden"
                                        name="tipo_documento"
                                        value="DNI"
                                    >

                                    @error('tipo_documento')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="numero_documento">Número documento</label>
                                    <input
                                        id="numero_documento"
                                        name="numero_documento"
                                        type="text"
                                        value="{{ old('numero_documento') }}"
                                        class="form-control @error('numero_documento') is-invalid @enderror"
                                        required
                                    >
                                    @error('numero_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="email">Email opcional</label>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        class="form-control @error('email') is-invalid @enderror"
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Agregar alumno
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="campus-card">
                <div class="campus-card-header">
                    Alumnos cargados
                </div>

                <div class="campus-card-body">
                    @if($alumnos->isEmpty())
                        <p class="mb-0 text-muted">
                            Todavía no hay alumnos cargados.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Apellido y nombre</th>
                                    <th>Documento</th>
                                    <th>Email</th>
                                    <th>Evaluación</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($alumnos as $alumno)
                                    <tr>
                                        <td>
                                            {{ $alumno->categoria ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $alumno->persona->apellido }},
                                            {{ $alumno->persona->nombre }}
                                        </td>

                                        <td>
                                            {{ $alumno->persona->tipo_documento }}
                                            {{ $alumno->persona->numero_documento }}
                                        </td>

                                        <td>
                                            {{ $alumno->persona->email ?: '—' }}
                                        </td>

                                        <td>
                                            @if($alumno->evaluacion?->archivoVigente)
                                                <a
                                                    href="{{ route('inscripciones.alumnos.evaluacion.show', [$inscripcion, $alumno]) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="btn btn-outline-secondary btn-sm"
                                                >
                                                    Ver PDF
                                                </a>
                                            @else
                                                <span class="text-muted">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex align-items-center gap-2 flex-nowrap">

                                                @if(! $alumno->evaluacion?->archivoVigente)
                                                    <form
                                                        method="POST"
                                                        action="{{ route('inscripciones.alumnos.destroy', [$inscripcion, $alumno]) }}"
                                                        onsubmit="return confirm('¿Eliminar este alumno?');"
                                                        class="m-0"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(! $alumno->evaluacion_bloqueada)
                                                    <form
                                                        method="POST"
                                                        action="{{ route('inscripciones.alumnos.evaluacion.store', [$inscripcion, $alumno]) }}"
                                                        enctype="multipart/form-data"
                                                        class="d-flex align-items-center gap-2 m-0"
                                                    >
                                                        @csrf

                                                        <input
                                                            type="file"
                                                            name="evaluacion"
                                                            accept="application/pdf"
                                                            class="form-control form-control-sm evaluacion-file"
                                                            required
                                                        >

                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Subir
                                                        </button>
                                                    </form>

                                                    @if($alumno->evaluacion?->archivoVigente)
                                                        <form
                                                            method="POST"
                                                            action="{{ route('inscripciones.alumnos.evaluacion.destroy', [$inscripcion, $alumno]) }}"
                                                            onsubmit="return confirm('¿Eliminar la evaluación vigente?');"
                                                            class="m-0"
                                                        >
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                Eliminar PDF
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">
                                                        Evaluación bloqueada
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection