<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Olimpiada de Geografía') — FHUC
    </title>

    @vite([
        'resources/css/app.scss',
        'resources/js/app.js',
    ])
</head>
<body>
<div class="campus-wrapper">

    <header class="inst-bar">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <a
                    href="{{ url('/') }}"
                    class="inst-brand"
                >
                    <img
                        src="{{ asset('imagenes/fhuc_2.png') }}"
                        alt="FHUC - Universidad Nacional del Litoral"
                        class="inst-logo"
                    >
                </a>

                <nav class="d-none d-md-flex gap-3 align-items-center">
                    <a href="{{ route('home') }}" class="text-decoration-none text-dark fw-semibold">Inicio</a>
                    <a href="{{ route('informacion') }}" class="text-decoration-none text-dark fw-semibold">Información</a>
                    <a href="{{ route('sistema') }}" class="text-decoration-none text-dark fw-semibold">Sistema</a>
                </nav>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary">
                        Panel
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Salir
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                        Ingresar
                    </a>
                @endif

                @hasSection('header-actions')
                    <div>
                        @yield('header-actions')
                    </div>
                @endif
            </div>
        </div>
    </header>

    <div class="inst-subbar">
        Olimpiada de Geografía
    </div>

    <main class="campus-main">
        <div class="container py-5">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="campus-footer">
        © {{ date('Y') }} UNL · Facultad de Humanidades y Ciencias
    </footer>

</div>
</body>
</html>
