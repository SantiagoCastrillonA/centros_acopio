@section('titulo', 'Actualizar '.$centro->nombre)

<div>
    <header class="barra-alta">
        <div class="envoltura">
            <div style="flex:1">
                <h1>📦 {{ $centro->nombre }}</h1>
                <p class="avance-global">
                    @if ($total === 0)
                        Sin insumos publicados
                    @else
                        {{ $cubiertas->count() }} de {{ $total }} completos &middot; {{ $pendientes->count() }} por conseguir
                    @endif
                </p>
            </div>
            <a class="salir" href="{{ route('publico.centro', $centro) }}" title="Ver la página pública">👁️</a>
            <a class="salir" href="{{ url('/admin') }}" title="Volver al panel">⚙️</a>
        </div>
    </header>

    <div class="envoltura">
        @forelse ($pendientes as $necesidad)
            <article class="insumo insumo--{{ $necesidad->prioridad }} @if ($tocada === $necesidad->id) insumo--tocada @endif"
                     wire:key="necesidad-{{ $necesidad->id }}">
                <div class="encabezado">
                    <span class="emoji" aria-hidden="true">{{ $necesidad->item->emoji }}</span>
                    <span>
                        <span class="nombre">{{ $necesidad->item->nombre }}</span><br>
                        <span class="unidad">{{ $necesidad->item->unidad }}</span>
                    </span>
                    <span class="marca marca--{{ $necesidad->prioridad }}">
                        @switch($necesidad->prioridad)
                            @case('alta') 🚨 Urgente @break
                            @case('media') ⚠️ Necesario @break
                            @default 🕗 Cuando se pueda
                        @endswitch
                    </span>
                </div>

                <div class="cuenta">
                    <span>
                        <strong>{{ number_format($necesidad->cantidad_cubierta, 0, ',', '.') }}</strong>
                        <span class="de">de {{ number_format($necesidad->cantidad_requerida, 0, ',', '.') }}</span>
                    </span>
                    <span class="de">faltan {{ number_format($necesidad->pendiente, 0, ',', '.') }}</span>
                </div>

                <div class="barra" role="presentation">
                    <span style="width: {{ $necesidad->porcentaje }}%"></span>
                </div>

                <div class="controles">
                    <button type="button"
                            wire:click="ajustar({{ $necesidad->id }}, -1)"
                            @disabled($necesidad->cantidad_cubierta === 0)
                            aria-label="Restar {{ $paso }} a {{ $necesidad->item->nombre }}">−</button>

                    <button type="button"
                            class="mas"
                            wire:click="ajustar({{ $necesidad->id }}, 1)"
                            aria-label="Sumar {{ $paso }} a {{ $necesidad->item->nombre }}">+</button>

                    <button type="button"
                            class="listo"
                            wire:click="completar({{ $necesidad->id }})"
                            aria-label="Marcar {{ $necesidad->item->nombre }} como completo">✅ Ya está</button>
                </div>
            </article>
        @empty
            <div class="vacio" style="margin-top:16px">
                @if ($total === 0)
                    <p>🗒️ Este centro todavía no tiene insumos publicados.</p>
                    <p>Agréguelos desde el panel y aparecerán aquí.</p>
                @else
                    <p>🎉 Todo lo publicado está cubierto.</p>
                    <p>Si sigue llegando gente con donaciones, publique nuevas necesidades desde el panel.</p>
                @endif
            </div>
        @endforelse

        @if ($cubiertas->isNotEmpty())
            <h2>✅ Ya está cubierto ({{ $cubiertas->count() }})</h2>

            @foreach ($cubiertas as $necesidad)
                <article class="insumo insumo--lista @if ($tocada === $necesidad->id) insumo--tocada @endif"
                         wire:key="necesidad-{{ $necesidad->id }}">
                    <div class="encabezado">
                        <span class="emoji" aria-hidden="true">{{ $necesidad->item->emoji }}</span>
                        <span>
                            <span class="nombre">{{ $necesidad->item->nombre }}</span><br>
                            <span class="unidad">{{ number_format($necesidad->cantidad_cubierta, 0, ',', '.') }} {{ $necesidad->item->unidad }}</span>
                        </span>
                        <span class="marca marca--lista">Completo</span>
                    </div>

                    <div class="controles">
                        <button type="button"
                                wire:click="ajustar({{ $necesidad->id }}, -1)"
                                aria-label="Restar {{ $paso }} a {{ $necesidad->item->nombre }}">−</button>

                        <button type="button"
                                class="mas"
                                wire:click="ajustar({{ $necesidad->id }}, 1)"
                                aria-label="Sumar {{ $paso }} a {{ $necesidad->item->nombre }}">+</button>
                    </div>
                </article>
            @endforeach
        @endif
    </div>

    <nav class="barra-baja">
        <div class="envoltura">
            <p>Cada toque suma o resta</p>
            <div class="pasos">
                @foreach ([1, 10, 50] as $opcion)
                    <button type="button"
                            wire:click="cambiarPaso({{ $opcion }})"
                            aria-pressed="{{ $paso === $opcion ? 'true' : 'false' }}">
                        {{ $opcion === 1 ? '1' : '+'.$opcion }}
                    </button>
                @endforeach
            </div>
        </div>
    </nav>
</div>
