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
    {{-- Filtro por GET: funciona con JavaScript apagado. --}}
    <form class="filtros" method="GET" action="{{ route('publico.index') }}">
        <div class="campo">
            <label for="departamento">📍 Departamento</label>
            <select name="departamento" id="departamento">
                <option value="">Todos</option>
                @foreach (array_keys($lugares) as $departamento)
                    <option value="{{ $departamento }}" @selected($filtros['departamento'] === $departamento)>
                        {{ $departamento }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="campo">
            <label for="ciudad">🏙️ Ciudad</label>
            <select name="ciudad" id="ciudad">
                <option value="">Todas</option>
                @foreach ($lugares as $departamento => $ciudades)
                    <optgroup label="{{ $departamento }}">
                        @foreach ($ciudades as $ciudad)
                            <option value="{{ $ciudad }}" @selected($filtros['ciudad'] === $ciudad)>{{ $ciudad }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div class="acciones">
            <button type="submit" class="boton">Filtrar</button>

            {{-- Solo aparece si el navegador tiene geolocalizacion: sin esto
                 seria un boton que no hace nada. --}}
            <button type="button" id="cerca" class="boton boton--secundario" hidden>📍 Cerca de mí</button>

            @if ($filtros['departamento'] || $filtros['ciudad'] || $cerca)
                <a class="boton boton--secundario" href="{{ route('publico.index') }}">Quitar filtros</a>
            @endif
        </div>
    </form>

    @if ($cerca)
        <p class="apunte">Ordenado por cercanía a donde está usted. Los centros sin ubicación cargada quedan al final.</p>
    @endif

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
                    @if ($cerca && $centro->distancia_m !== null)
                        <span class="distancia">
                            @if ($centro->distancia_m < 1000)
                                a {{ round($centro->distancia_m) }} m
                            @else
                                a {{ number_format($centro->distancia_m / 1000, 1, ',', '.') }} km
                            @endif
                        </span>
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

                @if ($centro->como_llegar_url)
                    <a class="boton boton--secundario"
                       href="{{ $centro->como_llegar_url }}"
                       target="_blank"
                       rel="noopener noreferrer">🧭 Cómo llegar</a>
                @endif
            </article>
        @endforeach
    @empty
        <div class="vacio">
            @if ($filtros['departamento'] || $filtros['ciudad'])
                <p>No hay centros abiertos con ese filtro.</p>
                <p><a href="{{ route('publico.index') }}">Vea todos los centros</a> para encontrar el más cercano.</p>
            @else
                <p>Todavía no hay centros publicados.</p>
                <p>Si usted coordina un punto de acopio o un albergue, pida su acceso al panel para publicar lo que necesita.</p>
            @endif
        </div>
    @endforelse
@endsection

@push('scripts')
    {{-- Mejora progresiva: sin JavaScript el filtro por ciudad sigue
         funcionando, que es lo que exige la regla de conexion mala. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var boton = document.getElementById('cerca');

            if (! boton || ! navigator.geolocation) {
                return;
            }

            boton.hidden = false;

            boton.addEventListener('click', function () {
                boton.disabled = true;
                boton.textContent = 'Buscando su ubicación…';

                navigator.geolocation.getCurrentPosition(
                    function (posicion) {
                        var url = new URL(window.location.href);
                        url.searchParams.set('lat', posicion.coords.latitude.toFixed(6));
                        url.searchParams.set('lng', posicion.coords.longitude.toFixed(6));
                        window.location.href = url.toString();
                    },
                    function () {
                        boton.disabled = false;
                        boton.textContent = '📍 No se pudo obtener su ubicación';
                    },
                    { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 }
                );
            });
        });
    </script>
@endpush
