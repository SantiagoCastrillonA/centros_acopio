@extends('publico.layout')

@section('titulo', 'La sesión se cerró por inactividad')

@section('contenido')
    <header class="principal">
        <h1>La sesión se cerró por inactividad</h1>
    </header>

    <div class="vacio">
        <p>Pasó mucho tiempo con la página abierta y la sesión caducó.</p>
        <p>Entre otra vez al panel y repita lo último que estaba haciendo.</p>
    </div>

    <a class="enlace-detalle" href="{{ url('/admin/login') }}">Entrar al panel</a>
@endsection
