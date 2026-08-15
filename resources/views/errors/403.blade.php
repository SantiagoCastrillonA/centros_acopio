@extends('publico.layout')

@section('titulo', 'Esta página necesita permiso')

@section('contenido')
    <header class="principal">
        <h1>Esta página necesita permiso</h1>
    </header>

    <div class="vacio">
        <p>Solo los coordinadores con acceso al panel pueden abrir esta página.</p>
        <p>Lo que necesita cada centro sí es público y no pide cuenta.</p>
    </div>

    <a class="enlace-detalle" href="{{ route('publico.index') }}">Ver los centros activos</a>
@endsection
