@extends('publico.layout')

@section('titulo', 'Qué se necesita y dónde llevarlo')
@section('descripcion', 'Centros de acopio y albergues activos, con los insumos que más les faltan hoy.')

@section('cabecera')
    <div class="franja">
        <div class="contenido">
            <h1>Qué se necesita y dónde llevarlo</h1>
            <p>
                Cada centro publica los insumos que le faltan. Lleve solo lo que aparece en la lista:
                lo que no se necesita ocupa espacio y trabajo que hacen falta en otra parte.
            </p>
        </div>
    </div>
@endsection

@section('contenido')
    @include('publico.mapa', ['id' => 'mapa-centros', 'titulo' => 'Dónde están los centros', 'puntos' => $puntos])

    @forelse ($centros->groupBy('ciudad') as $ciudad => $grupo)
        <h3>{{ $ciudad }} &middot; {{ $grupo->first()->departamento }}</h3>

        @foreach ($grupo as $centro)
            @php($urgentes = $centro->necesidades->take(6))

            <article class="centro {{ $centro->tipo === 'albergue' ? 'centro--albergue' : '' }}">
                <h2>{{ $centro->nombre }}</h2>

                <p>
                    <span class="etiqueta {{ $centro->tipo === 'albergue' ? 'etiqueta--albergue' : '' }}">
                        {{ $centro->tipo === 'albergue' ? 'Albergue' : 'Centro de acopio' }}
                    </span>
                    @if ($centro->necesidades->isNotEmpty())
                        <span class="etiqueta etiqueta--urgente">🚨 Urgente</span>
                    @endif
                </p>

                <div class="datos">
                    <div>{{ $centro->direccion }}</div>
                    @if ($centro->horario)
                        <div>Horario: {{ $centro->horario }}</div>
                    @endif
                </div>

                @if ($urgentes->isNotEmpty())
                    <ul class="insumos">
                        @foreach ($urgentes as $necesidad)
                            <li class="prioridad-alta">
                                <div class="insumo-linea">
                                    <span class="insumo-nombre">
                                        <span aria-hidden="true">{{ $necesidad->item->emoji }}</span>
                                        {{ $necesidad->item->nombre }}
                                    </span>
                                    <span class="falta">
                                        faltan <strong>{{ number_format($necesidad->pendiente, 0, ',', '.') }}</strong>
                                        {{ $necesidad->item->unidad }}
                                    </span>
                                </div>
                                <div class="barra" role="presentation">
                                    <span style="width: {{ $necesidad->porcentaje }}%"></span>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if ($centro->necesidades->count() > $urgentes->count())
                        <p class="apunte">
                            Y {{ $centro->necesidades->count() - $urgentes->count() }} insumo(s) urgente(s) más.
                        </p>
                    @endif
                @else
                    <p class="apunte">Sin urgencias publicadas hoy. Vea la lista completa antes de llevar algo.</p>
                @endif

                <a class="boton" href="{{ route('publico.centro', $centro) }}">Ver todo lo que necesita</a>
            </article>
        @endforeach
    @empty
        <div class="vacio">
            <p>Todavía no hay centros publicados.</p>
            <p>Si usted coordina un punto de acopio o un albergue, pida su acceso al panel para publicar lo que necesita.</p>
        </div>
    @endforelse
@endsection
