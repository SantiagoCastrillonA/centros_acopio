<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Centros de acopio y albergues')</title>
    <meta name="description" content="@yield('descripcion', 'Qué se necesita y dónde llevarlo. Centros de acopio y albergues activos.')">
    <style>
        /* Sin fuentes externas, sin CDN, sin JS: la pagina tiene que abrir en 2G. */
        :root {
            --tinta: #111111;
            --tinta-suave: #444444;
            --fondo: #ffffff;
            --linea: #cccccc;
            --alta: #b00020;
            --media: #8a5300;
            --baja: #33691e;
            --ok: #1b5e20;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0 16px 48px;
            background: var(--fondo);
            color: var(--tinta);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 18px;
            line-height: 1.5;
        }

        .contenido { max-width: 44rem; margin: 0 auto; }

        header.principal {
            border-bottom: 3px solid var(--tinta);
            padding: 20px 0 16px;
            margin-bottom: 24px;
        }

        h1 { font-size: 1.6rem; line-height: 1.25; margin: 0 0 8px; }
        h2 { font-size: 1.25rem; line-height: 1.3; margin: 0 0 4px; }
        h3 { font-size: 1rem; margin: 24px 0 8px; text-transform: uppercase; letter-spacing: .04em; color: var(--tinta-suave); }

        p { margin: 0 0 12px; }
        .apunte { color: var(--tinta-suave); font-size: .95rem; }

        a { color: #0b4fa8; }
        a:focus-visible, button:focus-visible { outline: 3px solid #0b4fa8; outline-offset: 2px; }

        .volver {
            display: inline-block;
            padding: 12px 0;
            min-height: 48px;
            font-weight: 600;
        }

        .centro {
            border: 2px solid var(--linea);
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .etiqueta {
            display: inline-block;
            border: 1px solid var(--tinta-suave);
            border-radius: 3px;
            padding: 1px 7px;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--tinta-suave);
            vertical-align: middle;
        }

        .datos { margin: 8px 0 12px; font-size: .95rem; color: var(--tinta-suave); }
        .datos div { margin-bottom: 2px; }

        ul.insumos { list-style: none; margin: 0; padding: 0; }

        ul.insumos li {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: baseline;
            padding: 10px 0;
            border-top: 1px solid var(--linea);
        }

        .insumo-nombre { font-weight: 600; }
        .insumo-nota { display: block; font-weight: 400; font-size: .9rem; color: var(--tinta-suave); }
        .falta { white-space: nowrap; font-variant-numeric: tabular-nums; text-align: right; }
        .falta strong { font-size: 1.15rem; }

        .prioridad-alta strong { color: var(--alta); }
        .prioridad-media strong { color: var(--media); }
        .prioridad-baja strong { color: var(--baja); }
        .cumplido { color: var(--ok); font-weight: 600; }

        .enlace-detalle {
            display: inline-block;
            margin-top: 14px;
            padding: 12px 16px;
            min-height: 48px;
            border: 2px solid #0b4fa8;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
        }

        .vacio {
            border: 2px dashed var(--linea);
            border-radius: 6px;
            padding: 16px;
            color: var(--tinta-suave);
        }

        footer.principal {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid var(--linea);
            font-size: .9rem;
            color: var(--tinta-suave);
        }
    </style>
</head>
<body>
<div class="contenido">
    @yield('contenido')

    <footer class="principal">
        <p>Esta plataforma no recibe dinero. Solo publica qué insumos necesita cada centro y dónde llevarlos.</p>
        <p>Confirme el horario con el centro antes de salir. Los datos los actualiza el coordinador de cada punto.</p>
    </footer>
</div>
</body>
</html>
