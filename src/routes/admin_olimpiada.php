<?php

use App\Http\Controllers\Admin\ConsultaEscuelasController;
use Illuminate\Support\Facades\Route;

// El controlador comprueba además que el usuario tenga rol administrador.
Route::middleware('auth')->prefix('admin')->name('admin.olimpiada.')->group(function (): void {
    Route::get('/escuelas', [ConsultaEscuelasController::class, 'index'])->name('escuelas.index');
    Route::get('/escuelas/{escuela}', [ConsultaEscuelasController::class, 'escuela'])
        ->whereNumber('escuela')->name('escuelas.show');
    Route::get('/evaluaciones/{archivo}/pdf', [ConsultaEscuelasController::class, 'pdf'])
        ->whereNumber('archivo')->name('evaluaciones.pdf');
});
