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

    /**
     * Se incrementa en cada escritura.
     *
     * Livewire reusa el nodo del DOM cuando la clave no cambia, y una
     * animacion CSS no se repite sobre un nodo que ya existia. El sello
     * viaja en la clave del acuse para que cada guardado dibuje uno nuevo.
     */
    public int $sello = 0;

    /**
     * Lo recibido de cada necesidad, por id.
     *
     * Existe para poder escribir la cantidad directo: cuando llega un
     * camion con 400 unidades, contarlas a toques no tiene sentido.
     */
    public array $cantidades = [];

    public function mount(Centro $centro): void
    {
        $this->centro = $centro;
        $this->sincronizarCantidades();
    }

    private function sincronizarCantidades(): void
    {
        $this->cantidades = $this->centro->necesidades()
            ->pluck('cantidad_cubierta', 'id')
            ->map(fn ($cantidad) => (int) $cantidad)
            ->all();
    }

    /**
     * Se dispara al salir del campo, sin boton "guardar", igual que los
     * botones de mas y menos.
     */
    public function updatedCantidades(mixed $valor, string $clave): void
    {
        $necesidad = $this->necesidad((int) $clave);

        // La columna es unsigned: cualquier cosa que escriban se acota antes
        // de llegar a la base, en vez de reventar con un error de MySQL.
        $limpio = max(0, min(4294967295, (int) $valor));

        $necesidad->update(['cantidad_cubierta' => $limpio]);

        // Se devuelve el valor acotado al campo: si escribieron -5, el
        // coordinador tiene que ver que quedo en 0.
        $this->cantidades[$clave] = $limpio;
        $this->acusar((int) $clave);
    }

    /**
     * Avisa al navegador que el dato aterrizo.
     *
     * Va por evento y no por una clase en el HTML porque Livewire reusa el
     * nodo al redibujar, y una animacion CSS no vuelve a correr sobre un
     * nodo que ya existia: al segundo toque seguido no se veria nada.
     */
    private function acusar(int $necesidadId): void
    {
        $this->tocada = $necesidadId;
        $this->sello++;

        $this->dispatch('necesidad-guardada', necesidad: $necesidadId);
    }

    public function cambiarPaso(int $paso): void
    {
        $this->paso = in_array($paso, [1, 10, 50], true) ? $paso : 1;
    }

    public function ajustar(int $necesidadId, int $signo): void
    {
        $necesidad = $this->necesidad($necesidadId);

        // Nunca por debajo de cero: cantidad_cubierta es unsigned en la base.
        $nueva = max(0, $necesidad->cantidad_cubierta + ($signo * $this->paso));

        $necesidad->update(['cantidad_cubierta' => $nueva]);

        $this->cantidades[$necesidadId] = $nueva;
        $this->acusar($necesidadId);
    }

    /**
     * Marca el insumo como completo de un toque. Es lo que mas se repite
     * cuando llega un camion y se cubre una necesidad entera.
     */
    public function completar(int $necesidadId): void
    {
        $necesidad = $this->necesidad($necesidadId);

        $necesidad->update(['cantidad_cubierta' => $necesidad->cantidad_requerida]);

        $this->cantidades[$necesidadId] = $necesidad->cantidad_requerida;
        $this->acusar($necesidadId);
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

        // Una necesidad publicada desde el panel mientras esta pantalla
        // estaba abierta no tendria entrada en el arreglo.
        foreach ($necesidades as $necesidad) {
            $this->cantidades[$necesidad->id] ??= $necesidad->cantidad_cubierta;
        }

        return view('livewire.actualizacion-rapida', [
            'pendientes' => $necesidades->reject->cubierta,
            'cubiertas' => $necesidades->filter->cubierta,
            'total' => $necesidades->count(),
        ]);
    }
}
