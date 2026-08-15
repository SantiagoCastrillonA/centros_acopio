@extends('publico.layout')

@section('titulo', 'Algo falló de nuestro lado')

@section('contenido')
    <header class="principal">
        <h1>Algo falló de nuestro lado</h1>
    </header>

    <div class="vacio">
        <p>No es culpa suya y no se perdió ninguna donación. El problema es de la plataforma.</p>
        <p>Espere un momento y vuelva a cargar la página. Si sigue igual, avise al coordinador del centro por teléfono.</p>
    </div>

    <a class="enlace-detalle" href="{{ route('publico.index') }}">Volver a la portada</a>
@endsection
