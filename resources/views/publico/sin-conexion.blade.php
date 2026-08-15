@extends('publico.layout')

@section('titulo', 'Sin conexión')

@section('cabecera')
    <div class="franja">
        <div class="contenido">
            <h1>📴 Se fue la conexión</h1>
        </div>
    </div>
@endsection

@section('contenido')
    <div class="vacio">
        <p>Esta página no alcanzó a guardarse para verla sin señal.</p>
        <p>Las páginas que ya visitó sí se pueden abrir, aunque los datos sean los de la última vez que tuvo conexión.</p>
    </div>

    <a class="boton" href="{{ route('publico.index') }}">Ver los centros guardados</a>

    <h3>Si necesita llevar donaciones ahora</h3>
    <p>
        Llame directamente al centro antes de salir. Los datos guardados en este teléfono pueden estar
        desactualizados, y un acopio que ayer necesitaba algo hoy puede estar lleno.
    </p>
@endsection
