@extends('publico.layout')

@section('titulo', 'No encontramos esa página')

@section('contenido')
    <header class="principal">
        <h1>No encontramos esa página</h1>
    </header>

    <div class="vacio">
        <p>Puede que el centro ya no esté recibiendo donaciones, o que el enlace esté incompleto.</p>
        <p>Vea la lista de centros que sí están activos ahora mismo.</p>
    </div>

    <a class="enlace-detalle" href="{{ route('publico.index') }}">Ver los centros activos</a>
@endsection
