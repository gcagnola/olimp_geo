@extends('layouts.app')

@section('title', 'Ingresar')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="campus-card">
                <div class="campus-card-header">Ingreso de usuario</div>

                <div class="campus-card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="form-control @error('email') is-invalid @enderror"
                            >

                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                class="form-control @error('password') is-invalid @enderror"
                            >

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remember"
                                id="remember"
                            >

                            <label class="form-check-label" for="remember">
                                Recordarme
                            </label>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Ingresar
                            </button>

                            <a href="{{ route('informacion') }}" class="btn btn-outline-secondary">
                                Información de la Olimpiada
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection