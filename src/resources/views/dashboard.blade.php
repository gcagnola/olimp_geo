@extends('layouts.app')

@section('title', 'Panel de control')

@section('header-actions')
    <form method="POST" action="{{ route('logout') }}" class="m-0">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm">
            Cerrar sesión
        </button>
    </form>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">

            <div class="campus-card mb-4">
                <div class="campus-card-header">
                    Panel de control
                </div>

                <div class="campus-card-body">
                    <h1 class="campus-title mb-2">
                        Bienvenido, {{ $user->name }}
                    </h1>

                    <p class="campus-description text-start mx-0">
                        Estás ingresando como
                        <strong>{{ $user->role === 'admin' ? 'administrador' : 'responsable/docente tutor' }}</strong>.
                    </p>

                    @if($stats['olimpiada'])
                        <p class="mb-0">
                            Olimpíada activa:
                            <strong>{{ $stats['olimpiada']->nombre }}</strong>
                        </p>
                    @else
                        <p class="mb-0 text-danger">
                            No hay olimpíada activa configurada.
                        </p>
                    @endif
                </div>
            </div>

            @if($user->isAdmin())
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <div class="campus-card h-100">
                            <div class="campus-card-body text-center">
                                <div class="display-6 fw-bold">{{ $stats['inscripciones'] }}</div>
                                <div class="text-muted">Inscripciones</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="campus-card h-100">
                            <div class="campus-card-body text-center">
                                <div class="display-6 fw-bold">{{ $stats['personas'] }}</div>
                                <div class="text-muted">Personas</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="campus-card h-100">
                            <div class="campus-card-body text-center">
                                <div class="display-6 fw-bold">{{ $stats['alumnos'] }}</div>
                                <div class="text-muted">Alumnos</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="campus-card h-100">
                            <div class="campus-card-body text-center">
                                <div class="display-6 fw-bold">{{ $stats['evaluaciones'] }}</div>
                                <div class="text-muted">Evaluaciones</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="campus-card">
                    <div class="campus-card-header">
                        Administración
                    </div>

                    <div class="campus-card-body">
                        <p class="text-muted mb-4">
                            Desde este panel se gestionarán inscripciones, personas,
                            evaluaciones y reportes.
                        </p>

                        <div class="d-grid gap-2 d-md-flex">
                            <a href="#" class="btn btn-primary">
                                Inscripciones
                            </a>

                            <a href="#" class="btn btn-outline-secondary">
                                Personas
                            </a>

                            <a href="#" class="btn btn-outline-secondary">
                                Evaluaciones
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="campus-card">
                    <div class="campus-card-header">
                        Mis inscripciones
                    </div>

                    <div class="campus-card-body">
                        @if($inscripciones->isEmpty())
                            <p class="mb-0 text-muted">
                                Todavía no tenés inscripciones asociadas.
                                Si esto no es correcto, comunicate con la organización.
                            </p>
                        @else
                            <div class="list-group">
                                @foreach($inscripciones as $inscripcion)
                                    <a
                                        href="{{ route('inscripciones.show', $inscripcion) }}"
                                        class="list-group-item list-group-item-action"
                                    >
                                        <div class="d-flex justify-content-between gap-3">
                                            <div>
                                                <h5 class="mb-1">
                                                    {{ $inscripcion->escuela_data->nombre ?? ('Escuela #' . $inscripcion->id_escuela) }}
                                                </h5>

                                                <p class="mb-1 text-muted">
                                                    Inscripción Nº {{ $inscripcion->numero }}
                                                    · {{ $inscripcion->olimpiada->nombre ?? 'Sin olimpíada' }}
                                                </p>

                                                <small>
                                                    CUE: {{ $inscripcion->escuela_data->cue ?? 'Sin dato' }}
                                                </small>
                                            </div>

                                            <span class="badge campus-badge align-self-start">
                                                Abrir
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection