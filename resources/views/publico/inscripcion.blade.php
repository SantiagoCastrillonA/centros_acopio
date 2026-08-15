@extends('publico.layout')

@section('titulo', 'Anotarse como voluntario — '.$turno->centro->nombre)
@section('descripcion', 'Anótese para ayudar en '.$turno->centro->nombre.' en '.$turno->centro->ciudad.'.')

@section('cabecera')
    <div class="franja">
        <div class="contenido">
            <a class="volver" href="{{ route('publico.centro', $turno->centro) }}">&larr; {{ $turno->centro->nombre }}</a>
            <h1>🙌 Anotarse como voluntario</h1>
            <p>{{ $turno->centro->ciudad }}, {{ $turno->centro->departamento }}</p>
        </div>
    </div>
@endsection

@section('contenido')
    @if (session('cerrado'))
        <p class="aviso aviso--alto">{{ session('cerrado') }}</p>
    @endif

    <article class="turno">
        <h4>{{ $turno->emoji }} {{ $turno->tarea }}</h4>
        <div class="datos">
            <div>📅 {{ $turno->fecha->translatedFormat('l j \d\e F') }}</div>
            <div>🕗 {{ $turno->horario }}</div>
            <div>📍 {{ $turno->centro->direccion }}</div>
        </div>
        @if ($turno->nota)
            <p class="apunte">{{ $turno->nota }}</p>
        @endif
        <p class="cupos {{ $turno->cupos_libres > 0 ? 'cupos--libres' : 'cupos--lleno' }}">
            {{ $turno->cupos_libres }} de {{ $turno->cupos }} cupos disponibles
        </p>
    </article>

    @if (! $turno->admite_inscripciones)
        <div class="vacio">
            <p>Este turno ya no recibe voluntarios.</p>
            <p>Mire los demás turnos del centro: casi siempre hay más de un frente abierto.</p>
        </div>
        <a class="boton" href="{{ route('publico.centro', $turno->centro) }}">Ver los otros turnos</a>
    @else
        <form method="POST" action="{{ route('publico.turno.anotar', $turno) }}">
            @csrf

            <label for="nombre">Su nombre</label>
            <input type="text"
                   id="nombre"
                   name="nombre"
                   value="{{ old('nombre') }}"
                   autocomplete="name"
                   required
                   maxlength="120">
            @error('nombre')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="celular">Su celular</label>
            <input type="tel"
                   id="celular"
                   name="celular"
                   value="{{ old('celular') }}"
                   autocomplete="tel"
                   inputmode="numeric"
                   required
                   maxlength="20"
                   placeholder="3001234567">
            <p class="apunte">Lo usa el coordinador del centro para avisarle si el turno cambia. No aparece en ninguna página pública.</p>
            @error('celular')
                <p class="error">{{ $message }}</p>
            @enderror

            <label class="casilla" for="autorizacion_datos">
                <input type="checkbox"
                       id="autorizacion_datos"
                       name="autorizacion_datos"
                       value="1"
                       {{ old('autorizacion_datos') ? 'checked' : '' }}>
                <span>
                    Autorizo que se guarde mi nombre y mi celular para coordinar este turno de voluntariado,
                    según la <a href="{{ route('publico.privacidad') }}" target="_blank" rel="noopener">política de tratamiento de datos</a>.
                </span>
            </label>
            @error('autorizacion_datos')
                <p class="error">{{ $message }}</p>
            @enderror

            <button type="submit" class="boton">Anotarme en este turno</button>
        </form>

        <p class="apunte" style="margin-top:16px">
            Puede pedir que borremos sus datos en cualquier momento. Cómo hacerlo está en la
            <a href="{{ route('publico.privacidad') }}">política de tratamiento de datos</a>.
        </p>
    @endif
@endsection
