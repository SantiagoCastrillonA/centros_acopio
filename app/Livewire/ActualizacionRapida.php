<?php

namespace App\Livewire;

use App\Models\Centro;
use App\Models\Necesidad;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * La pantalla mas importante del proyecto.
 *
 * El coordinador la usa parado en una bodega, con una mano ocupada, en un
 * celular de gama baja. Cada toque guarda: no hay boton "guardar", no hay
 * modales, no hay menus anidados.
 */
#[Layout('publico.layout-rapido')]
class ActualizacionRapida extends Component
{
    public Centro $centro;

    /** Cuanto suma o resta cada toque. En una bodega se reciben cajas, no unidades. */
    public int $paso = 1;

    /** Ultima necesidad tocada, para la confirmacion visual. */
    public ?int $tocada = null;

    public function mount(Centro $centro): void
    {
        $this->centro = $centro;
    }

    public function cambiarPaso(int $paso): void
    {
        $this->paso = in_array($paso, [1, 10, 50], true) ? $paso : 1;
    }

    public function ajustar(int $necesidadId, int $signo): void
    {
        $necesidad = $this->necesidad($necesidadId);

        // Nunca por debajo de cero: cantidad_cubierta es unsigned en la base.
        $necesidad->update([
            'cantidad_cubierta' => max(0, $necesidad->cantidad_cubierta + ($signo * $this->paso)),
        ]);

        $this->tocada = $necesidadId;
    }

    /**
     * Marca el insumo como completo de un toque. Es lo que mas se repite
     * cuando llega un camion y se cubre una necesidad entera.
     */
    public function completar(int $necesidadId): void
    {
        $necesidad = $this->necesidad($necesidadId);

        $necesidad->update(['cantidad_cubierta' => $necesidad->cantidad_requerida]);

        $this->tocada = $necesidadId;
    }

    private function necesidad(int $necesidadId): Necesidad
    {
        // Se busca dentro del centro y no por id suelto: evita que una peticion
        // manipulada toque las necesidades de otro centro.
        return $this->centro->necesidades()->findOrFail($necesidadId);
    }

    public function render()
    {
        $necesidades = $this->centro
            ->necesidades()
            ->with('item')
            ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderByRaw('GREATEST(CAST(cantidad_requerida AS SIGNED) - CAST(cantidad_cubierta AS SIGNED), 0) DESC')
            ->get();

        return view('livewire.actualizacion-rapida', [
            'pendientes' => $necesidades->reject->cubierta,
            'cubiertas' => $necesidades->filter->cubierta,
            'total' => $necesidades->count(),
        ]);
    }
}
