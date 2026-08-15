{{--
    Mapa como mejora progresiva: la seccion nace oculta y solo se muestra
    si Leaflet llego a cargar. Sin senal la pagina sigue completa, que es
    la condicion de la regla 4 del proyecto.

    Espera: $id, $titulo y $puntos (nombre, detalle, lat, lng, url, urgente).
--}}
@if (! empty($puntos))
    @push('estilos')
        <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    @endpush

    <section id="{{ $id }}-seccion" hidden>
        <h3>{{ $titulo }}</h3>
        <div id="{{ $id }}" class="mapa"></div>
        <p class="apunte">
            Mapa de OpenStreetMap. Necesita conexión: si no carga, la información de abajo sigue completa.
        </p>
    </section>

    @push('scripts')
        <script type="application/json" id="{{ $id }}-datos">
            {!! json_encode($puntos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}
        </script>
        <script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>
        {{-- defer no aplica a scripts en linea: sin esperar al DOM, este
             correria antes que leaflet.js y L todavia no existiria. --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof L === 'undefined') {
                    return;
                }

                var seccion = document.getElementById('{{ $id }}-seccion');
                var datos = JSON.parse(document.getElementById('{{ $id }}-datos').textContent);

                if (! datos.length) {
                    return;
                }

                function esc(texto) {
                    var d = document.createElement('div');
                    d.textContent = texto === null || texto === undefined ? '' : texto;
                    return d.innerHTML;
                }

                seccion.hidden = false;

                var mapa = L.map('{{ $id }}', { scrollWheelZoom: false });

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; colaboradores de OpenStreetMap',
                }).addTo(mapa);

                var puntos = [];

                datos.forEach(function (p) {
                    var icono = L.divIcon({
                        className: '',
                        html: '<div class="marcador-centro' + (p.urgente ? ' marcador-centro--urgente' : '') + '"></div>',
                        iconSize: [18, 18],
                        iconAnchor: [9, 9],
                    });

                    L.marker([p.lat, p.lng], { icon: icono, title: p.nombre })
                        .addTo(mapa)
                        .bindPopup(
                            '<strong>' + esc(p.nombre) + '</strong><br>' +
                            esc(p.detalle) + '<br>' +
                            '<a href="' + esc(p.url) + '">Ver qué necesita</a>'
                        );

                    puntos.push([p.lat, p.lng]);
                });

                if (puntos.length === 1) {
                    mapa.setView(puntos[0], 15);
                } else {
                    mapa.fitBounds(puntos, { padding: [30, 30] });
                }
            });
        </script>
    @endpush
@endif
