<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\InscripcionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/informacion', [PageController::class, 'information'])->name('informacion');
Route::get('/sistema', [PageController::class, 'system'])->name('sistema');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/inscripciones/{inscripcion}', [InscripcionController::class, 'show'])
        ->name('inscripciones.show');

    Route::post('/inscripciones/{inscripcion}/categorias', [InscripcionController::class, 'updateCategorias'])
        ->name('inscripciones.categorias.update');

    Route::post('/inscripciones/{inscripcion}/alumnos', [InscripcionController::class, 'storeAlumno'])
        ->name('inscripciones.alumnos.store');

    Route::delete('/inscripciones/{inscripcion}/alumnos/{alumno}', [InscripcionController::class, 'destroyAlumno'])
        ->name('inscripciones.alumnos.destroy');

    Route::get('/inscripciones/{inscripcion}/alumnos/{alumno}/evaluacion', [InscripcionController::class, 'showEvaluacion'])
        ->name('inscripciones.alumnos.evaluacion.show');

    Route::post('/inscripciones/{inscripcion}/alumnos/{alumno}/evaluacion', [InscripcionController::class, 'storeEvaluacion'])
        ->name('inscripciones.alumnos.evaluacion.store');

    Route::delete('/inscripciones/{inscripcion}/alumnos/{alumno}/evaluacion', [InscripcionController::class, 'destroyEvaluacion'])
        ->name('inscripciones.alumnos.evaluacion.destroy');
});