<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Turno;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Inscripcion de voluntarios. Sin cuenta y sin login: cualquier friccion
 * del lado del voluntario mata el uso.
 *
 * Aqui entran datos personales (Ley 1581 de 2012), asi que el formulario
 * pide autorizacion explicita y se guarda cuando se dio.
 */
class InscripcionPublicaController extends Controller
{
    public function create(Turno $turno): View
    {
        $turno->load('centro');

        abort_unless($turno->centro->activo, 404);

        return view('publico.inscripcion', [
            'turno' => $turno,
        ]);
    }

    public function store(Request $request, Turno $turno): RedirectResponse
    {
        $turno->load('centro');

        abort_unless($turno->centro->activo, 404);

        // Se revisa aqui y no solo en la vista: entre que alguien abre el
        // formulario y lo envia, el turno puede haberse llenado.
        if (! $turno->admite_inscripciones) {
            return back()->with('cerrado', $this->motivoDeCierre($turno));
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:120'],
            'celular' => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9 +()-]+$/'],
            'autorizacion_datos' => ['accepted'],
        ], [
            'autorizacion_datos.accepted' => 'Para anotarse hay que autorizar el uso de los datos.',
            'celular.regex' => 'El celular solo puede tener números, espacios y los signos + ( ) -',
        ], [
            'nombre' => 'nombre',
            'celular' => 'celular',
        ]);

        $celular = preg_replace('/\D/', '', $datos['celular']);

        $repetida = Inscripcion::where('turno_id', $turno->id)
            ->where('celular', $celular)
            ->whereIn('estado', ['anotado', 'asistio'])
            ->exists();

        if ($repetida) {
            return back()
                ->withInput()
                ->with('cerrado', 'Ese celular ya está anotado en este turno. No hace falta anotarse otra vez.');
        }

        Inscripcion::create([
            'turno_id' => $turno->id,
            'nombre' => trim($datos['nombre']),
            'celular' => $celular,
            'autorizacion_datos' => true,
            // Fecha de la autorizacion: si alguien reclama, hay que poder
            // demostrar cuando la dio.
            'autorizacion_en' => now(),
            'estado' => 'anotado',
        ]);

        return redirect()
            ->route('publico.centro', $turno->centro)
            ->with('anotado', $turno);
    }

    private function motivoDeCierre(Turno $turno): string
    {
        return match (true) {
            $turno->paso => 'Ese turno ya pasó. Mire los turnos que siguen abiertos.',
            $turno->lleno => 'Ese turno se llenó mientras llenaba el formulario. Mire los otros turnos del centro.',
            default => 'Ese turno ya no recibe voluntarios.',
        };
    }
}
