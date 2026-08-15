@extends('publico.layout')

@section('titulo', $centro->nombre.' — qué necesita hoy')
@section('descripcion', 'Insumos que necesita '.$centro->nombre.' en '.$centro->ciudad.'. Dirección, horario y contacto.')

@section('cabecera')
    <div class="franja">
        <div class="contenido">
            <a class="volver" href="{{ route('publico.index') }}">&larr; Todos los centros</a>
            <h1>{{ $centro->nombre }}</h1>
            <p>{{ $centro->ciudad }}, {{ $centro->departamento }}</p>
        </div>
    </div>
@endsection

@section('contenido')
    @if (session('anotado'))
        <p class="aviso aviso--bien">
            🙌 Quedó anotado. El coordinador del centro lo llama si algo cambia. Gracias.
        </p>
    @endif

    <article class="centro {{ $centro->tipo === 'albergue' ? 'centro--albergue' : '' }}">
        <p>
            <span class="etiqueta {{ $centro->tipo === 'albergue' ? 'etiqueta--albergue' : '' }}">
                {{ $centro->tipo === 'albergue' ? 'Albergue' : 'Centro de acopio' }}
            </span>
        </p>

        <div class="datos">
            <div>{{ $centro->direccion }}</div>
            @if ($centro->horario)
                <div>Horario: {{ $centro->horario }}</div>
            @endif
            @if ($centro->contacto_telefono)
                <div>
                    Contacto:
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $centro->contacto_telefono) }}">{{ $centro->contacto_telefono }}</a>
                    @if ($centro->contacto_nombre)
                        ({{ $centro->contacto_nombre }})
                    @endif
                </div>
            @endif
            <div>Actualizado el {{ $actualizado->format('d/m/Y') }} a las {{ $actualizado->format('H:i') }}</div>
        </div>

        @if ($centro->notas)
            <p>{{ $centro->notas }}</p>
        @endif

        @if (filled($centro->latitud) && filled($centro->longitud))
            <a class="boton boton--secundario"
               href="https://www.openstreetmap.org/?mlat={{ $centro->latitud }}&mlon={{ $centro->longitud }}#map=17/{{ $centro->latitud }}/{{ $centro->longitud }}"
               target="_blank"
               rel="noopener noreferrer">Cómo llegar (abre OpenStreetMap)</a>
        @endif
    </article>

    @include('publico.mapa', ['id' => 'mapa-centro', 'titulo' => 'Dónde queda', 'puntos' => $puntos])

    @if ($pendientes->isNotEmpty())
        @foreach ($pendientes->groupBy('prioridad') as $prioridad => $grupo)
            <h3>
                @switch($prioridad)
                    @case('alta') 🚨 Urgente @break
                    @case('media') ⚠️ Necesario @break
                    @default 🕗 Cuando se pueda
                @endswitch
            </h3>

            <ul class="insumos">
                @foreach ($grupo as $necesidad)
                    <li class="prioridad-{{ $necesidad->prioridad }}">
                        <div class="insumo-linea">
                            <span>
                                <span class="insumo-nombre">
                                    <span aria-hidden="true">{{ $necesidad->item->emoji }}</span>
                                    {{ $necesidad->item->nombre }}
                                </span>
                                @if ($necesidad->nota)
                                    <span class="insumo-nota">{{ $necesidad->nota }}</span>
                                @endif
                            </span>
                            <span class="falta">
                                faltan <strong>{{ number_format($necesidad->pendiente, 0, ',', '.') }}</strong>
                                {{ $necesidad->item->unidad }}
                            </span>
                        </div>
                        <div class="barra" role="presentation">
                            <span style="width: {{ $necesidad->porcentaje }}%"></span>
                        </div>
                        <p class="apunte" style="margin: 6px 0 0">
                            Lleva {{ $necesidad->porcentaje }}% de lo que necesita
                            ({{ number_format($necesidad->cantidad_cubierta, 0, ',', '.') }}
                            de {{ number_format($necesidad->cantidad_requerida, 0, ',', '.') }}).
                        </p>
                    </li>
                @endforeach
            </ul>
        @endforeach
    @else
        <div class="vacio">
            <p>Este centro aún no ha publicado necesidades pendientes.</p>
            <p>Llame antes de llevar donaciones: así no se satura un punto que ya está cubierto.</p>
        </div>
    @endif

    @if ($turnos->isNotEmpty())
        <h3>🙌 Turnos para voluntarios</h3>

        @foreach ($turnos as $turno)
            @php($libres = max(0, $turno->cupos - $turno->tomados))

            <article class="turno {{ $libres === 0 ? 'turno--lleno' : '' }}">
                <h4>{{ $turno->emoji }} {{ $turno->tarea }}</h4>
                <div class="datos">
                    <div>📅 {{ $turno->fecha->translatedFormat('l j \d\e F') }} &middot; 🕗 {{ $turno->horario }}</div>
                </div>
                @if ($turno->nota)
                    <p class="apunte">{{ $turno->nota }}</p>
                @endif

                @if ($libres > 0)
                    <p class="cupos cupos--libres">Quedan {{ $libres }} de {{ $turno->cupos }} cupos</p>
                    <a class="boton" href="{{ route('publico.turno', $turno) }}">Anotarme en este turno</a>
                @else
                    <p class="cupos cupos--lleno">Turno completo</p>
                @endif
            </article>
        @endforeach

        <p class="apunte">
            Para anotarse solo se pide nombre y celular, y su celular no aparece en ninguna página pública.
            Lea la <a href="{{ route('publico.privacidad') }}">política de tratamiento de datos</a>.
        </p>
    @endif

    @if ($cubiertas->isNotEmpty())
        <h3>✅ Ya está cubierto</h3>
        <ul class="insumos">
            @foreach ($cubiertas as $necesidad)
                <li>
                    <div class="insumo-linea">
                        <span class="insumo-nombre">
                            <span aria-hidden="true">{{ $necesidad->item->emoji }}</span>
                            {{ $necesidad->item->nombre }}
                        </span>
                        <span class="falta cumplido">Completo</span>
                    </div>
                    <div class="barra barra--completa" role="presentation">
                        <span style="width: 100%"></span>
                    </div>
                </li>
            @endforeach
        </ul>
        <p class="apunte">Estos insumos no hacen falta por ahora.</p>
    @endif
@endsection
