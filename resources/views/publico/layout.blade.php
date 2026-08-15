<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Centros de acopio y albergues')</title>
    <meta name="description" content="@yield('descripcion', 'Qué se necesita y dónde llevarlo. Centros de acopio y albergues activos.')">

    {{-- PWA: se puede instalar en el celular y abrir sin señal. --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#0b5394">
    <link rel="icon" href="{{ asset('iconos/icono-192.png') }}" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('iconos/icono-apple-180.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Acopio">
    <style>
        /*
         * Sin fuentes externas y sin CSS de terceros: la pagina tiene que
         * abrir en 2G. El color es funcional, no decorativo: cada tono
         * significa un nivel de urgencia.
         */
        :root {
            --tinta: #14181d;
            --tinta-suave: #4a5560;
            --fondo: #ffffff;
            --superficie: #f2f5f8;
            --borde: #ccd5de;
            --borde-fuerte: #14181d;

            --acento: #0b5394;
            --acento-suave: #e6eff8;

            --alta: #9e1409;
            --alta-fondo: #fdeceb;
            --media: #7a4b00;
            --media-fondo: #fdf2e0;
            --baja: #14572a;
            --baja-fondo: #e8f4ec;
            --ok: #14572a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0 0 48px;
            background: var(--superficie);
            color: var(--tinta);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 18px;
            line-height: 1.5;
        }

        .contenido { max-width: 46rem; margin: 0 auto; padding: 0 16px; }

        /* Cabecera */
        .franja {
            background: var(--acento);
            color: #ffffff;
            padding: 22px 0 20px;
            margin-bottom: 22px;
        }
        .franja h1 { font-size: 1.7rem; line-height: 1.2; margin: 0 0 8px; }
        .franja p { margin: 0; color: #dbe8f5; font-size: .98rem; }
        .franja a { color: #ffffff; }

        h2 { font-size: 1.3rem; line-height: 1.3; margin: 0 0 6px; }
        h3 {
            font-size: .82rem;
            margin: 26px 0 10px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--tinta-suave);
        }

        p { margin: 0 0 12px; }
        .apunte { color: var(--tinta-suave); font-size: .93rem; }

        a { color: var(--acento); }
        a:focus-visible, button:focus-visible { outline: 3px solid var(--acento); outline-offset: 2px; }

        .volver {
            display: inline-block;
            padding: 12px 0;
            min-height: 48px;
            font-weight: 600;
            text-decoration: none;
        }

        /* Tarjeta de centro */
        .centro {
            background: var(--fondo);
            border: 1px solid var(--borde);
            border-left: 6px solid var(--acento);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 18px;
        }
        .centro--albergue { border-left-color: var(--baja); }

        .etiqueta {
            display: inline-block;
            border-radius: 999px;
            padding: 3px 11px;
            font-size: .76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            background: var(--acento-suave);
            color: var(--acento);
        }
        .etiqueta--albergue { background: var(--baja-fondo); color: var(--baja); }
        .etiqueta--urgente { background: var(--alta-fondo); color: var(--alta); }

        .datos { margin: 10px 0 14px; font-size: .93rem; color: var(--tinta-suave); }
        .datos div { margin-bottom: 3px; }

        /* Lista de insumos */
        ul.insumos { list-style: none; margin: 0; padding: 0; }

        ul.insumos li {
            padding: 12px 0;
            border-top: 1px solid var(--borde);
        }

        .insumo-linea {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: baseline;
        }
        .insumo-nombre { font-weight: 600; }
        .insumo-nota { display: block; font-weight: 400; font-size: .88rem; color: var(--tinta-suave); }
        .falta { white-space: nowrap; font-variant-numeric: tabular-nums; text-align: right; }
        .falta strong { font-size: 1.2rem; }

        .prioridad-alta .falta strong { color: var(--alta); }
        .prioridad-media .falta strong { color: var(--media); }
        .prioridad-baja .falta strong { color: var(--baja); }
        .cumplido { color: var(--ok); font-weight: 700; }

        /* Barra de avance: cuanto se lleva conseguido */
        .barra {
            margin-top: 8px;
            height: 10px;
            border-radius: 999px;
            background: var(--borde);
            overflow: hidden;
        }
        .barra span { display: block; height: 100%; background: var(--acento); }
        .prioridad-alta .barra span { background: var(--alta); }
        .prioridad-media .barra span { background: var(--media); }
        .prioridad-baja .barra span { background: var(--baja); }
        .barra--completa span { background: var(--ok); }

        /* Botones */
        .boton {
            display: inline-block;
            margin-top: 14px;
            padding: 13px 18px;
            min-height: 48px;
            background: var(--acento);
            color: #ffffff;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
        }
        .boton--secundario {
            background: var(--fondo);
            color: var(--acento);
            border: 2px solid var(--acento);
        }

        .vacio {
            background: var(--fondo);
            border: 2px dashed var(--borde);
            border-radius: 8px;
            padding: 18px;
            color: var(--tinta-suave);
        }

        /* Mapa: solo aparece si el navegador logra cargarlo */
        .mapa {
            height: 320px;
            border: 1px solid var(--borde);
            border-radius: 8px;
            background: var(--fondo);
        }
        .marcador-centro {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #ffffff;
            background: var(--acento);
            box-shadow: 0 0 0 1px var(--tinta);
        }
        .marcador-centro--urgente { background: var(--alta); }
        .leaflet-popup-content { font-family: inherit; font-size: .95rem; }

        /* Aviso de sesion: solo lo ve un coordinador con sesion abierta */
        .barra-sesion {
            background: var(--tinta);
            color: #ffffff;
            font-size: .88rem;
            padding: 8px 0;
        }
        .barra-sesion .contenido {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .barra-sesion a {
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, .5);
            border-radius: 6px;
            padding: 8px 12px;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        /* Filtros de la portada */
        .filtros {
            background: var(--fondo);
            border: 1px solid var(--borde);
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 18px;
        }
        .filtros label { margin: 0 0 6px; font-size: .9rem; }
        .filtros .campo { margin-bottom: 12px; }
        select {
            width: 100%;
            min-height: 52px;
            padding: 12px 14px;
            font-size: 17px;
            font-family: inherit;
            color: var(--tinta);
            background: var(--fondo);
            border: 2px solid var(--borde);
            border-radius: 8px;
        }
        .filtros .acciones { display: flex; gap: 10px; flex-wrap: wrap; }
        .filtros .acciones > * { flex: 1 1 auto; min-width: 44%; text-align: center; margin-top: 0; }

        .distancia {
            display: inline-block;
            background: var(--acento-suave);
            color: var(--acento);
            border-radius: 999px;
            padding: 3px 10px;
            font-size: .8rem;
            font-weight: 700;
        }

        /* Turnos de voluntariado */
        .turno {
            background: var(--fondo);
            border: 1px solid var(--borde);
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .turno--lleno { background: var(--superficie); color: var(--tinta-suave); }
        .turno h4 { margin: 0 0 4px; font-size: 1.05rem; }
        .cupos { font-weight: 700; }
        .cupos--libres { color: var(--ok); }
        .cupos--lleno { color: var(--tinta-suave); }

        /* Formulario: 17 px o mas en los campos, si no iOS hace zoom al tocar */
        label { display: block; font-weight: 600; margin: 16px 0 6px; }
        input[type="text"], input[type="tel"] {
            width: 100%;
            min-height: 52px;
            padding: 12px 14px;
            font-size: 17px;
            font-family: inherit;
            color: var(--tinta);
            background: var(--fondo);
            border: 2px solid var(--borde);
            border-radius: 8px;
        }
        input:focus-visible { outline: 3px solid var(--acento); outline-offset: 1px; }

        .casilla {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin: 18px 0;
            padding: 14px;
            background: var(--acento-suave);
            border-radius: 8px;
            font-weight: 400;
        }
        .casilla input[type="checkbox"] { width: 26px; height: 26px; margin-top: 2px; flex: none; }

        button.boton {
            width: 100%;
            border: none;
            font-size: 1.05rem;
            cursor: pointer;
        }

        .error { color: var(--alta); font-weight: 600; margin-top: 6px; }
        .aviso {
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .aviso--bien { background: var(--baja-fondo); color: var(--baja); }
        .aviso--alto { background: var(--alta-fondo); color: var(--alta); }

        footer.principal {
            margin-top: 36px;
            padding-top: 16px;
            border-top: 1px solid var(--borde);
            font-size: .9rem;
            color: var(--tinta-suave);
        }
    </style>
    @stack('estilos')
</head>
<body>
{{-- Solo aparece con sesion abierta: el donante nunca ve nada del panel. --}}
@auth
    <div class="barra-sesion">
        <div class="contenido">
            <span>Está viendo la página pública</span>
            <a href="{{ url('/admin') }}">⚙️ Ir al panel</a>
        </div>
    </div>
@endauth

@yield('cabecera')

<div class="contenido">
    @yield('contenido')

    <footer class="principal">
        <p>Esta plataforma no recibe dinero. Solo publica qué insumos necesita cada centro y dónde llevarlos.</p>
        <p>Confirme el horario con el centro antes de salir. Los datos los actualiza el coordinador de cada punto.</p>
        <p><a href="{{ route('publico.privacidad') }}">🔒 Política de tratamiento de datos</a></p>
        @guest
            <p><a href="{{ url('/admin/login') }}">⚙️ Soy coordinador de un centro</a></p>
        @endguest
    </footer>
</div>

@stack('scripts')

{{-- El service worker es opcional: si el navegador no lo soporta o falla
     el registro, la pagina funciona igual. Solo se registra sobre HTTPS
     (o localhost), que es lo unico que los navegadores permiten. --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {
                // Sin caché offline, pero la página sigue sirviendo.
            });
        });
    }
</script>
</body>
</html>
