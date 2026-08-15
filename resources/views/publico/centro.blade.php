@extends('publico.layout')

@section('titulo', $centro->nombre.' — qué necesita hoy')
@section('descripcion', 'Insumos que necesita '.$centro->nombre.' en '.$centro->ciudad.'. Dirección, horario y contacto.')

@section('contenido')
    <header class="principal">
        <a class="volver" href="{{ route('publico.index') }}">&larr; Todos los centros</a>
        <h1>{{ $centro->nombre }}</h1>
        <p><span class="etiqueta">{{ $centro->tipo === 'albergue' ? 'Albergue' : 'Centro de acopio' }}</span></p>

        <div class="datos">
            <div>{{ $centro->direccion }}</div>
            <div>{{ $centro->ciudad }}, {{ $centro->departamento }}</div>
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
    </header>

    @if ($pendientes->isNotEmpty())
        @foreach ($pendientes->groupBy('prioridad') as $prioridad => $grupo)
            <h3>
                @switch($prioridad)
                    @case('alta') Urgente @break
                    @case('media') Necesario @break
                    @default Cuando se pueda
                @endswitch
            </h3>

            <ul class="insumos">
                @foreach ($grupo as $necesidad)
                    <li class="prioridad-{{ $necesidad->prioridad }}">
                        <span>
                            <span class="insumo-nombre">{{ $necesidad->item->nombre }}</span>
                            @if ($necesidad->nota)
                                <span class="insumo-nota">{{ $necesidad->nota }}</span>
                            @endif
                        </span>
                        <span class="falta">
                            faltan <strong>{{ number_format($necesidad->pendiente, 0, ',', '.') }}</strong>
                            {{ $necesidad->item->unidad }}
                        </span>
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

    @if ($cubiertas->isNotEmpty())
        <h3>Ya está cubierto</h3>
        <ul class="insumos">
            @foreach ($cubiertas as $necesidad)
                <li>
                    <span class="insumo-nombre">{{ $necesidad->item->nombre }}</span>
                    <span class="falta cumplido">Completo</span>
                </li>
            @endforeach
        </ul>
        <p class="apunte">Estos insumos no hacen falta por ahora.</p>
    @endif
@endsection
