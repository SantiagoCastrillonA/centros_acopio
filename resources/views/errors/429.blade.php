@extends('publico.layout')

@section('titulo', 'Demasiadas solicitudes seguidas')

@section('contenido')
    <header class="principal">
        <h1>Demasiadas solicitudes seguidas</h1>
    </header>

    <div class="vacio">
        <p>La plataforma recibió muchas peticiones desde esta conexión en poco tiempo.</p>
        <p>Espere un minuto y vuelva a cargar la página.</p>
    </div>

    <a class="enlace-detalle" href="{{ route('publico.index') }}">Volver a la portada</a>
@endsection
