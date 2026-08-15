<?php

namespace Tests\Feature;

use App\Models\Centro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UbicacionTest extends TestCase
{
    use RefreshDatabase;

    private function centro(string $nombre, string $ciudad, string $departamento, ?float $lat = null, ?float $lng = null): Centro
    {
        return Centro::create([
            'nombre' => $nombre,
            'tipo' => 'acopio',
            'direccion' => 'Calle 1',
            'ciudad' => $ciudad,
            'departamento' => $departamento,
            'latitud' => $lat,
            'longitud' => $lng,
            'activo' => true,
        ]);
    }

    public function test_filtra_por_ciudad(): void
    {
        $this->centro('Coliseo de Pereira', 'Pereira', 'Risaralda');
        $this->centro('Albergue de Armenia', 'Armenia', 'Quindio');

        $this->get(route('publico.index', ['ciudad' => 'Pereira']))
            ->assertOk()
            ->assertSee('Coliseo de Pereira')
            ->assertDontSee('Albergue de Armenia');
    }

    public function test_filtra_por_departamento(): void
    {
        $this->centro('Coliseo de Pereira', 'Pereira', 'Risaralda');
        $this->centro('Punto de Marsella', 'Marsella', 'Risaralda');
        $this->centro('Albergue de Armenia', 'Armenia', 'Quindio');

        $respuesta = $this->get(route('publico.index', ['departamento' => 'Risaralda']))->assertOk();

        $respuesta->assertSee('Coliseo de Pereira');
        $respuesta->assertSee('Punto de Marsella');
        $respuesta->assertDontSee('Albergue de Armenia');
    }

    public function test_sin_resultados_invita_a_quitar_el_filtro(): void
    {
        $this->centro('Coliseo de Pereira', 'Pereira', 'Risaralda');

        $this->get(route('publico.index', ['ciudad' => 'Leticia']))
            ->assertOk()
            ->assertSee('No hay centros abiertos con ese filtro');
    }

    public function test_ordena_por_cercania_cuando_hay_coordenadas(): void
    {
        // Armenia y Pereira estan a unos 45 km. Se consulta desde Armenia.
        $this->centro('Lejano en Pereira', 'Pereira', 'Risaralda', 4.8133, -75.6961);
        $this->centro('Cercano en Armenia', 'Armenia', 'Quindio', 4.5339, -75.6811);

        $contenido = $this->get(route('publico.index', ['lat' => 4.5339, 'lng' => -75.6811]))
            ->assertOk()
            ->assertSee('Ordenado por cercanía')
            ->getContent();

        $this->assertLessThan(
            strpos($contenido, 'Lejano en Pereira'),
            strpos($contenido, 'Cercano en Armenia'),
            'El centro mas cercano debe aparecer primero.',
        );
    }

    public function test_los_centros_sin_coordenadas_quedan_al_final_pero_no_desaparecen(): void
    {
        $this->centro('Con ubicacion', 'Armenia', 'Quindio', 4.5339, -75.6811);
        $this->centro('Sin ubicacion', 'Armenia', 'Quindio');

        $contenido = $this->get(route('publico.index', ['lat' => 4.5339, 'lng' => -75.6811]))
            ->assertOk()
            ->assertSee('Sin ubicacion')
            ->getContent();

        $this->assertLessThan(
            strpos($contenido, 'Sin ubicacion'),
            strpos($contenido, 'Con ubicacion'),
        );
    }

    public function test_muestra_la_distancia_a_cada_centro(): void
    {
        $this->centro('Cercano en Armenia', 'Armenia', 'Quindio', 4.5339, -75.6811);

        $this->get(route('publico.index', ['lat' => 4.5300, 'lng' => -75.6800]))
            ->assertOk()
            ->assertSee('class="distancia"', false);
    }

    public function test_rechaza_coordenadas_fuera_de_rango(): void
    {
        $this->centro('Coliseo de Pereira', 'Pereira', 'Risaralda');

        $this->get(route('publico.index', ['lat' => 999, 'lng' => -75.6]))
            ->assertSessionHasErrors('lat');
    }

    public function test_la_portada_funciona_sin_filtros(): void
    {
        $this->centro('Coliseo de Pereira', 'Pereira', 'Risaralda');

        $this->get(route('publico.index'))
            ->assertOk()
            ->assertSee('Coliseo de Pereira')
            ->assertDontSee('Ordenado por cercanía');
    }
}
