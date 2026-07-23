@extends('layouts.app')

@section('title', 'Sistema')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="campus-card">
                <div class="campus-card-header">
                    Ingreso al sistema
                </div>

                <div class="campus-card-body">
                    @auth
                        <p>
                            Ya estás autenticado. Podés acceder a tu panel desde el siguiente botón.
                        </p>

                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                            Ir al panel
                        </a>
                    @else
                        <p>
                            El ingreso está reservado a responsables o docentes tutores
                            previamente registrados por la organización.
                        </p>

                        <p class="text-muted">
                            El usuario corresponde al correo informado en la inscripción.
                        </p>

                        <div class="d-grid gap-3">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                Iniciar sesión
                            </a>

                            <a href="{{ route('informacion') }}" class="btn btn-outline-secondary">
                                Información
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection