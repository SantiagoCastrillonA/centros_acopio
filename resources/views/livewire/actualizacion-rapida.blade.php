@section('titulo', 'Actualizar '.$centro->nombre)

<div>
    <header class="barra-alta">
        <div class="envoltura">
            <div style="flex:1">
                <h1><span class="emoji-centro" aria-hidden="true">📦</span> {{ $centro->nombre }}</h1>
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
        @if ($pendientes->isEmpty())
            <div class="vacio" style="margin-top:16px">
                @if ($total === 0)
                    <p>🗒️ Este centro todavía no tiene insumos publicados.</p>
                    <p>Agréguelos desde el panel y aparecerán aquí.</p>
                @else
                    <p>🎉 Todo lo publicado está cubierto.</p>
                    <p>Si sigue llegando gente con donaciones, publique nuevas necesidades desde el panel.</p>
                @endif
            </div>
        @else
            <div class="rejilla">
            @foreach ($pendientes as $necesidad)
                <article class="insumo insumo--{{ $necesidad->prioridad }}"
                         style="--avance: {{ $necesidad->porcentaje / 100 }}"
                         wire:key="necesidad-{{ $necesidad->id }}"
                         wire:loading.class="insumo--guardando"
                         wire:target="ajustar({{ $necesidad->id }}, 1), ajustar({{ $necesidad->id }}, -1), completar({{ $necesidad->id }}), cantidades.{{ $necesidad->id }}">
                    <div class="encabezado">
                        <span class="emoji" aria-hidden="true">{{ $necesidad->item->emoji }}</span>
                        <span>
                            {{-- Lleva a la ficha del insumo en el panel: desde
                                 la bodega se corrige ahi lo pedido o la
                                 prioridad, sin buscarlo en la tabla. --}}
                            <a class="nombre"
                               href="{{ route('filament.admin.resources.necesidades.edit', $necesidad) }}"
                               title="Abrir {{ $necesidad->item->nombre }} en el panel">{{ $necesidad->item->nombre }}</a><br>
                            <span class="unidad">{{ $necesidad->item->unidad }}</span>
                        </span>
                        <span class="marca marca--{{ $necesidad->prioridad }}">
                            @switch($necesidad->prioridad)
                                @case('alta') 🚨 Urgente @break
                                @case('media') ⚠️ Necesario @break
                                @default 🕗 Cuando se pueda
                            @endswitch
                        </span>

                        <span class="sello" data-sello="{{ $necesidad->id }}" role="status" aria-live="polite"></span>
                    </div>

                    <div class="cuenta">
                        <span>
                            <label class="oculto-visualmente" for="cantidad-{{ $necesidad->id }}">
                                Cantidad recibida de {{ $necesidad->item->nombre }}
                            </label>
                            <input type="number"
                                   id="cantidad-{{ $necesidad->id }}"
                                   class="campo-cantidad"
                                   inputmode="numeric"
                                   min="0"
                                   step="1"
                                   wire:model.live.debounce.600ms="cantidades.{{ $necesidad->id }}">
                            <span class="de">de {{ number_format($necesidad->cantidad_requerida, 0, ',', '.') }}</span>
                        </span>
                        <span class="de">faltan {{ number_format($necesidad->pendiente, 0, ',', '.') }}</span>
                    </div>

                    <div class="barra" role="presentation">
                        <span></span>
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
            @endforeach
            </div>
        @endif

        @if ($cubiertas->isNotEmpty())
            <h2>✅ Ya está cubierto ({{ $cubiertas->count() }})</h2>

            <div class="rejilla">
            @foreach ($cubiertas as $necesidad)
                <article class="insumo insumo--lista"
                         style="--avance: 1"
                         wire:key="necesidad-{{ $necesidad->id }}"
                         wire:loading.class="insumo--guardando"
                         wire:target="ajustar({{ $necesidad->id }}, 1), ajustar({{ $necesidad->id }}, -1), cantidades.{{ $necesidad->id }}">
                    <div class="encabezado">
                        <span class="emoji" aria-hidden="true">{{ $necesidad->item->emoji }}</span>
                        <span>
                            {{-- Lleva a la ficha del insumo en el panel: desde
                                 la bodega se corrige ahi lo pedido o la
                                 prioridad, sin buscarlo en la tabla. --}}
                            <a class="nombre"
                               href="{{ route('filament.admin.resources.necesidades.edit', $necesidad) }}"
                               title="Abrir {{ $necesidad->item->nombre }} en el panel">{{ $necesidad->item->nombre }}</a><br>
                            <span class="unidad">{{ $necesidad->item->unidad }}</span>
                        </span>
                        <span class="marca marca--lista">Completo</span>

                        @if ($tocada === $necesidad->id)
                            <span class="sello" wire:key="sello-{{ $necesidad->id }}-{{ $sello }}" role="status">Guardado</span>
                        @endif
                    </div>

                    <div class="cuenta">
                        <span>
                            <label class="oculto-visualmente" for="cantidad-{{ $necesidad->id }}">
                                Cantidad recibida de {{ $necesidad->item->nombre }}
                            </label>
                            <input type="number"
                                   id="cantidad-{{ $necesidad->id }}"
                                   class="campo-cantidad"
                                   inputmode="numeric"
                                   min="0"
                                   step="1"
                                   wire:model.live.debounce.600ms="cantidades.{{ $necesidad->id }}">
                            <span class="de">de {{ number_format($necesidad->cantidad_requerida, 0, ',', '.') }}</span>
                        </span>
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
            </div>
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
