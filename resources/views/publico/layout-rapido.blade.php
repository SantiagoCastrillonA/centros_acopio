<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo', 'Actualización rápida')</title>
    <style>
        /*
         * Pantalla de bodega: una mano ocupada, sol de frente, gama baja.
         * Todo lo que se toca vive en la mitad inferior, al alcance del pulgar.
         */
        :root {
            --tinta: #14181d;
            --tinta-suave: #4a5560;
            --fondo: #ffffff;
            --superficie: #eef2f6;
            --borde: #c8d2dc;

            /*
             * El acento es indigo y no azul: los otros tres colores de esta
             * pantalla ya significan algo (rojo urgente, ambar necesario,
             * verde cubierto). El indigo no se confunde con ninguno, ni
             * siquiera de reojo y a pleno sol.
             */
            --acento: #4338ca;
            --acento-fuerte: #3730a3;
            --acento-suave: #eef0fd;
            --acento-tenue: #c9cdf6;

            --alta: #9e1409;
            --alta-fondo: #fdeceb;
            --media: #7a4b00;
            --media-fondo: #fdf2e0;
            --baja: #14572a;
            --baja-fondo: #e8f4ec;
            --ok: #14572a;
            --ok-fondo: #dff0e4;
        }

        * { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            margin: 0;
            background: var(--superficie);
            color: var(--tinta);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 17px;
            line-height: 1.4;
            /* Deja aire para la barra fija de abajo. */
            padding-bottom: calc(112px + env(safe-area-inset-bottom));
            overscroll-behavior-y: contain;
        }

        /* Nada de zoom accidental ni resaltado azul al tocar rapido. */
        button {
            font-family: inherit;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            cursor: pointer;
        }

        /*
         * En el celular la lista es una columna. En tableta sobraba media
         * pantalla a los lados, asi que la envoltura crece y las tarjetas
         * se reparten en varias columnas: menos desplazamiento, mas insumos
         * a la vista de un vistazo.
         */
        .envoltura { max-width: 34rem; margin: 0 auto; padding: 0 12px; }

        @media (min-width: 46rem) {
            .envoltura { max-width: 72rem; padding: 0 20px; }
        }

        .rejilla { display: grid; grid-template-columns: 1fr; gap: 12px; margin: 12px 0; }

        @media (min-width: 46rem) {
            .rejilla { grid-template-columns: repeat(2, 1fr); gap: 16px; }
        }
        @media (min-width: 72rem) {
            .rejilla { grid-template-columns: repeat(3, 1fr); }
        }

        /* Cabecera fija */
        .barra-alta {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--acento);
            color: #ffffff;
            padding: calc(12px + env(safe-area-inset-top)) 0 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .25);
        }
        .barra-alta .envoltura { display: flex; align-items: center; gap: 10px; }
        .barra-alta h1 {
            /* Crece con la pantalla: es el unico sitio donde el coordinador
               confirma que esta actualizando el centro correcto. */
            font-size: clamp(1.15rem, .95rem + 1.1vw, 1.75rem);
            font-weight: 800;
            letter-spacing: -.015em;
            margin: 0;
            line-height: 1.2;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .barra-alta h1 .emoji-centro { font-size: .95em; line-height: 1; }
        .barra-alta .salir {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.4rem;
            padding: 8px;
            min-width: 44px;
            text-align: center;
            border-radius: 10px;
            transition: background-color 140ms ease-out;
        }
        .barra-alta .salir:hover,
        .barra-alta .salir:focus-visible { background: rgba(255, 255, 255, .16); }
        .avance-global {
            font-size: clamp(.8rem, .74rem + .25vw, .95rem);
            color: var(--acento-tenue);
            margin: 3px 0 0;
        }

        /* Fila de insumo */
        /*
         * El canto izquierdo no es un adorno de color: es el mismo avance
         * que la barra, leido de reojo. Se llena de abajo hacia arriba con
         * lo que ya se consiguio, y su color dice la prioridad. Asi, al
         * bajar la lista con una mano, se ve cuanto falta sin leer cifras.
         */
        .insumo {
            position: relative;
            overflow: hidden;
            background: var(--fondo);
            border: 1px solid var(--borde);
            border-radius: 12px;
            padding: 12px 12px 12px 18px;
            /* La separacion la pone la rejilla: si la tarjeta trae margen
               propio, en dos columnas los huecos quedan desparejos. */
            display: flex;
            flex-direction: column;
            transition: opacity 140ms linear, background-color 240ms ease-out;
        }
        /* Los controles se pegan abajo para que las tarjetas de una misma
           fila tengan los botones a la misma altura aunque el nombre del
           insumo ocupe dos lineas en una y una sola en la otra. */
        .insumo .controles { margin-top: auto; padding-top: 12px; }
        .insumo::before {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 6px;
            height: 100%;
            background: var(--borde);
            transform: scaleY(var(--avance, 0));
            transform-origin: left bottom;
            transition: transform 320ms cubic-bezier(0.16, 1, 0.3, 1), background-color 240ms ease-out;
        }
        .insumo--alta::before { background: var(--alta); }
        .insumo--media::before { background: var(--media); }
        .insumo--baja::before { background: var(--baja); }
        .insumo--lista { background: var(--ok-fondo); }
        .insumo--lista::before { background: var(--ok); }

        /*
         * Estados de una escritura, que es lo unico que se mueve aqui:
         * el dato viaja al servidor y hay que contarlo, no adornarlo.
         *
         * 1. --guardando: mientras la peticion esta en el aire.
         * 2. .sello: el acuse cuando aterrizo.
         */
        .insumo--guardando { opacity: .62; }
        .insumo--guardando .controles button { pointer-events: none; }

        .sello {
            display: inline-flex;
            align-items: center;
            margin-left: 8px;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--ok-fondo);
            color: var(--ok);
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            /* Nace invisible y sin ocupar sitio: solo existe cuando hay
               algo que acusar. Sale solo, porque es un acuse y no un aviso. */
            opacity: 0;
        }
        .sello--visible {
            animation: acuse 1.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes acuse {
            0% { opacity: 0; transform: translateY(4px) scale(.9); }
            12% { opacity: 1; transform: none; }
            72% { opacity: 1; }
            100% { opacity: 0; }
        }

        .encabezado { display: flex; align-items: flex-start; gap: 10px; }
        .emoji { font-size: 1.7rem; line-height: 1.1; }
        .nombre { font-weight: 700; font-size: 1.05rem; }
        .unidad { color: var(--tinta-suave); font-size: .85rem; }

        /*
         * El nombre lleva a la ficha del insumo en el panel: desde la bodega
         * se corrige ahi la cantidad pedida, la prioridad o la nota, sin
         * tener que buscar el insumo en una tabla de noventa filas.
         *
         * El subrayado esta siempre puesto, no al pasar el cursor: en una
         * tableta no hay cursor que pasar.
         */
        a.nombre {
            color: var(--tinta);
            text-decoration: none;
            border-bottom: 2px solid var(--acento-tenue);
            transition: color 140ms ease-out, border-color 140ms ease-out;
        }
        a.nombre:hover,
        a.nombre:focus-visible { color: var(--acento); border-bottom-color: var(--acento); }
        a.nombre:focus-visible { outline: 3px solid var(--acento); outline-offset: 3px; border-radius: 3px; }

        .marca {
            margin-left: auto;
            border-radius: 999px;
            padding: 3px 9px;
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .marca--alta { background: var(--alta-fondo); color: var(--alta); }
        .marca--media { background: var(--media-fondo); color: var(--media); }
        .marca--baja { background: var(--baja-fondo); color: var(--baja); }
        .marca--lista { background: var(--ok); color: #ffffff; }

        .cuenta {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            margin: 10px 0 6px;
            font-variant-numeric: tabular-nums;
        }
        .cuenta strong { font-size: 1.9rem; letter-spacing: -.02em; }
        .cuenta .de { color: var(--tinta-suave); font-size: .9rem; }

        /* Campo para escribir la cantidad: cuando llega un camion con 400
           unidades, contarlas a toques no tiene sentido. */
        .campo-cantidad {
            width: 5.5ch;
            min-height: 56px;
            padding: 6px 8px;
            font-family: inherit;
            font-size: 1.7rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            text-align: center;
            color: var(--tinta);
            background: var(--fondo);
            border: 2px solid var(--borde);
            border-radius: 10px;
            -moz-appearance: textfield;
        }
        .campo-cantidad::-webkit-outer-spin-button,
        .campo-cantidad::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .campo-cantidad:focus-visible { outline: 3px solid var(--acento); outline-offset: 1px; border-color: var(--acento); }

        .oculto-visualmente {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
        }

        .barra { height: 8px; border-radius: 999px; background: var(--superficie); overflow: hidden; }
        .barra span {
            display: block;
            width: 100%;
            height: 100%;
            background: var(--acento);
            /* Recorre con scaleX y no con width: animar el ancho obliga al
               navegador a recalcular la maquetacion en cada fotograma, y
               esto corre en gama baja. */
            transform: scaleX(var(--avance, 0));
            transform-origin: left center;
            transition: transform 260ms cubic-bezier(0.16, 1, 0.3, 1), background-color 240ms ease-out;
        }
        .insumo--lista .barra span { background: var(--ok); }

        /* Controles: lo unico que se toca, y por eso lo mas grande */
        .controles { display: flex; gap: 10px; margin-top: 12px; }
        .controles button {
            min-height: 60px;
            border-radius: 12px;
            border: 2px solid var(--acento);
            background: var(--fondo);
            color: var(--acento);
            font-size: 1.7rem;
            font-weight: 800;
            flex: 1;
        }
        .controles button.mas { background: var(--acento); color: #ffffff; }
        .controles button.listo {
            flex: 1.4;
            font-size: 1rem;
            border-color: var(--ok);
            color: var(--ok);
        }
        /* El acuse tactil no puede esperar al servidor: ocurre en el dedo,
           antes de que la peticion salga. 90 ms es el limite de lo que se
           siente instantaneo. */
        .controles button {
            transition: transform 90ms ease-out, opacity 140ms linear, background-color 140ms linear;
        }
        .controles button:active { transform: scale(.95); }
        .controles button.mas:active { background: var(--acento-fuerte); border-color: var(--acento-fuerte); }
        .controles button[disabled] { opacity: .35; }

        h2 {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--tinta-suave);
            margin: 18px 0 4px;
        }

        .vacio {
            background: var(--fondo);
            border: 2px dashed var(--borde);
            border-radius: 12px;
            padding: 18px;
            color: var(--tinta-suave);
            text-align: center;
        }

        /* Barra fija de abajo: el paso vive en la zona del pulgar */
        .barra-baja {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10;
            background: var(--fondo);
            border-top: 1px solid var(--borde);
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
            box-shadow: 0 -2px 8px rgba(0, 0, 0, .08);
        }
        .barra-baja p { margin: 0 0 6px; font-size: .74rem; color: var(--tinta-suave); text-transform: uppercase; letter-spacing: .06em; }
        .pasos { display: flex; gap: 8px; }
        .pasos button {
            flex: 1;
            min-height: 52px;
            border-radius: 12px;
            border: 2px solid var(--borde);
            background: var(--fondo);
            color: var(--tinta);
            font-size: 1.05rem;
            font-weight: 800;
        }
        .pasos button {
            transition: transform 90ms ease-out, background-color 160ms ease-out, border-color 160ms ease-out, color 160ms ease-out;
        }
        .pasos button:active { transform: scale(.96); }
        .pasos button[aria-pressed="true"] {
            background: var(--acento);
            border-color: var(--acento);
            color: #ffffff;
        }

        /*
         * Superficies que no dibujamos pero igual son del diseño:
         * seleccion, cursor de texto y barra de desplazamiento.
         */
        ::selection { background: var(--acento); color: #ffffff; }
        .campo-cantidad { caret-color: var(--acento); }
        html { scrollbar-color: var(--borde) transparent; scrollbar-width: thin; }

        /*
         * Quien pidio menos movimiento no recibe ninguno. El acuse sigue
         * existiendo, pero aparece y desaparece sin desplazarse.
         */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
                scroll-behavior: auto !important;
            }
            .sello--visible { animation: acuse-quieto 1.6s steps(1, end) forwards; }
            @keyframes acuse-quieto {
                0%, 80% { opacity: 1; }
                100% { opacity: 0; }
            }
        }
    </style>
</head>
<body>
{{ $slot }}

{{--
    El acuse de guardado vive aqui y no dentro del componente: Livewire 3
    no ejecuta los <script> en linea de un componente.

    Reinicia la animacion quitando la clase, forzando un reflujo y
    volviendola a poner. Sin eso, al segundo toque seguido sobre la misma
    fila no se veria nada: la animacion no se repite sobre un nodo que ya
    existia.
--}}
<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('necesidad-guardada', function (datos) {
            var carga = Array.isArray(datos) ? datos[0] : datos;
            var sello = document.querySelector('[data-sello="' + carga.necesidad + '"]');

            if (! sello) {
                return;
            }

            sello.textContent = 'Guardado';
            sello.classList.remove('sello--visible');
            void sello.offsetWidth;
            sello.classList.add('sello--visible');
        });
    });
</script>
</body>
</html>
