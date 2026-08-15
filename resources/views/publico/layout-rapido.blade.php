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

            --acento: #0b5394;
            --acento-suave: #e6eff8;

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

        .envoltura { max-width: 34rem; margin: 0 auto; padding: 0 12px; }

        /* Cabecera fija */
        .barra-alta {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--acento);
            color: #ffffff;
            padding: calc(10px + env(safe-area-inset-top)) 0 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .25);
        }
        .barra-alta .envoltura { display: flex; align-items: center; gap: 10px; }
        .barra-alta h1 { font-size: 1.05rem; margin: 0; line-height: 1.25; flex: 1; }
        .barra-alta .salir {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.4rem;
            padding: 8px;
            min-width: 44px;
            text-align: center;
        }
        .avance-global { font-size: .8rem; color: #d6e6f6; margin: 2px 0 0; }

        /* Fila de insumo */
        .insumo {
            background: var(--fondo);
            border: 1px solid var(--borde);
            border-left: 6px solid var(--borde);
            border-radius: 12px;
            padding: 12px;
            margin: 10px 0;
        }
        .insumo--alta { border-left-color: var(--alta); }
        .insumo--media { border-left-color: var(--media); }
        .insumo--baja { border-left-color: var(--baja); }
        .insumo--lista { border-left-color: var(--ok); background: var(--ok-fondo); }

        .insumo--tocada { animation: destello .6s ease-out; }
        @keyframes destello {
            from { background: var(--acento-suave); }
            to { background: var(--fondo); }
        }

        .encabezado { display: flex; align-items: flex-start; gap: 10px; }
        .emoji { font-size: 1.7rem; line-height: 1.1; }
        .nombre { font-weight: 700; font-size: 1.05rem; }
        .unidad { color: var(--tinta-suave); font-size: .85rem; }

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
        .barra span { display: block; height: 100%; background: var(--acento); }
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
        .controles button:active { transform: scale(.97); }
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
        .pasos button[aria-pressed="true"] {
            background: var(--acento);
            border-color: var(--acento);
            color: #ffffff;
        }

        /* Mientras viaja la peticion, el boton se ve ocupado en vez de muerto */
        [wire\:loading] { opacity: .5; }
    </style>
</head>
<body>
{{ $slot }}
</body>
</html>
