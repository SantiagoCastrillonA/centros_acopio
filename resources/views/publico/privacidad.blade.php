@extends('publico.layout')

@section('titulo', 'Política de tratamiento de datos')
@section('descripcion', 'Qué datos guardamos de los voluntarios, para qué, y cómo pedir que los borremos.')

@php
    $responsable = config('acopio.responsable');
    $incompleto = blank($responsable['nombre']) || blank($responsable['correo']);
@endphp

@section('cabecera')
    <div class="franja">
        <div class="contenido">
            <a class="volver" href="{{ route('publico.index') }}">&larr; Todos los centros</a>
            <h1>🔒 Política de tratamiento de datos</h1>
            <p>Ley 1581 de 2012 · Colombia</p>
        </div>
    </div>
@endsection

@section('contenido')
    @if ($incompleto)
        <p class="aviso aviso--alto">
            ⚠️ Esta política está incompleta: falta identificar a la organización responsable.
            No abra el formulario de voluntarios al público hasta completarla.
        </p>
    @endif

    <h3>Quién responde por sus datos</h3>
    @if ($incompleto)
        <div class="vacio">
            <p>Pendiente de completar por la organización que opera esta plataforma.</p>
        </div>
    @else
        <div class="datos">
            <div><strong>{{ $responsable['nombre'] }}</strong></div>
            @if ($responsable['documento'])
                <div>NIT o documento: {{ $responsable['documento'] }}</div>
            @endif
            @if ($responsable['direccion'])
                <div>{{ $responsable['direccion'] }}</div>
            @endif
            <div>Correo: <a href="mailto:{{ $responsable['correo'] }}">{{ $responsable['correo'] }}</a></div>
            @if ($responsable['telefono'])
                <div>Teléfono: {{ $responsable['telefono'] }}</div>
            @endif
        </div>
    @endif

    <h3>Qué guardamos</h3>
    <p>Solo cuando usted se anota a un turno de voluntariado, y solo esto:</p>
    <ul>
        <li>Su <strong>nombre</strong>.</li>
        <li>Su <strong>celular</strong>.</li>
        <li>La <strong>fecha y hora</strong> en que autorizó el uso de estos datos.</li>
        <li>El turno al que se anotó y si asistió.</li>
    </ul>
    <p>
        No pedimos documento de identidad, ni dirección, ni correo. No usamos cookies de publicidad
        ni de seguimiento. Consultar qué necesita un centro no requiere entregar ningún dato.
    </p>

    <h3>Para qué los usamos</h3>
    <p>
        Para coordinar el turno: que el coordinador del centro sepa con cuánta gente cuenta y pueda
        llamarle si el turno cambia de hora, se cancela o se traslada.
    </p>
    <p>
        <strong>No los usamos para nada más.</strong> No los vendemos, no los compartimos con terceros
        y no le enviamos mensajes de publicidad.
    </p>

    <h3>Quién los ve</h3>
    <p>
        Únicamente los coordinadores con acceso al panel de esta plataforma.
        <strong>Su celular nunca aparece en una página pública.</strong> Quien visite el sitio ve
        cuántos cupos quedan en un turno, nunca quién se anotó.
    </p>

    <h3>Cuánto tiempo los guardamos</h3>
    <p>
        El tiempo que dure la operación de emergencia. Cuando los centros dejen de operar, las
        inscripciones se eliminan. También puede pedir que borremos los suyos antes.
    </p>

    <h3>Sus derechos</h3>
    <p>La Ley 1581 de 2012 le da derecho a:</p>
    <ul>
        <li>Conocer qué datos suyos tenemos.</li>
        <li>Pedir que los actualicemos o corrijamos.</li>
        <li>Revocar la autorización y pedir que los borremos.</li>
        <li>Presentar quejas ante la Superintendencia de Industria y Comercio.</li>
    </ul>

    <h3>Cómo ejercerlos</h3>
    @if ($incompleto)
        <div class="vacio">
            <p>Pendiente de completar por la organización que opera esta plataforma.</p>
        </div>
    @else
        <p>
            Escriba a <a href="mailto:{{ $responsable['correo'] }}">{{ $responsable['correo'] }}</a>
            diciendo su nombre y el celular con el que se anotó. No necesita justificar la solicitud
            para pedir que borremos sus datos.
        </p>
    @endif

    <p class="apunte" style="margin-top:24px">
        Si prefiere no dejar sus datos, igual puede ayudar: lleve donaciones directamente a un centro,
        sin anotarse a ningún turno.
    </p>
@endsection
