@extends('layouts.app')

@section('title', 'Información')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="campus-card">
                <div class="campus-card-header">Información de la Olimpiada</div>
                <div class="campus-card-body">
                    <p>Bienvenidos a la Olimpiada de Geografía de la Facultad de Humanidades y Ciencias de la UNL. Esta plataforma permite la inscripción de escuelas, responsables, categorías, estudiantes y evaluaciones.</p>
                    <p>Para comenzar, podés ingresar al sistema desde la sección correspondiente o contactarte con el equipo organizador si necesitás asistencia.</p>
                    <p>El sistema está diseñado para funcionar bajo el prefijo <strong>/olimp_geo</strong> y respetar la estética institucional de FHUC.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
