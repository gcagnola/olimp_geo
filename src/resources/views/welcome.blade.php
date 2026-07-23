@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7 col-xl-6">

            <div class="campus-card">
                <div class="campus-card-header">
                    Sistema de gestión
                </div>

                <div class="campus-card-body">
                    <div class="text-center mb-4">
                        <h1 class="campus-title">
                            Olimpiada de Geografía
                        </h1>

                        <p class="campus-description">
                            Plataforma para la inscripción de escuelas,
                            responsables, categorías, estudiantes y
                            evaluaciones.
                        </p>
                    </div>

                    <div class="d-grid gap-3">
                        <a
                            href="{{ route('sistema') }}"
                            class="btn btn-primary btn-lg"
                        >
                            Ingresar al sistema
                        </a>

                        <a
                            href="{{ route('informacion') }}"
                            class="btn btn-outline-secondary"
                        >
                            Información
                        </a>
                    </div>
                </div>

                <div class="campus-card-footer">
                    Facultad de Humanidades y Ciencias · UNL
                </div>
            </div>

        </div>
    </div>
@endsection
