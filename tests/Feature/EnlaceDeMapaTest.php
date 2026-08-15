<?php

namespace Tests\Feature;

use App\Models\Centro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnlaceDeMapaTest extends TestCase
{
    use RefreshDatabase;

    private function centro(array $atributos = []): Centro
    {
        return Centro::create(array_merge([
            'nombre' => 'Coliseo Municipal',
            'tipo' => 'acopio',
            'direccion' => 'Carrera 7 # 20-30',
            'ciudad' => 'Pereira',
            'departamento' => 'Risaralda',
            'activo' => true,
        ], $atributos));
    }

    public static function enlacesConCoordenadas(): array
    {
        return [
            'mapa con @' => ['https://www.google.com/maps/@4.5339,-75.6811,17z', 4.5339, -75.6811],
            'lugar con !3d!4d' => ['https://www.google.com/maps/place/Armenia/data=!3d4.5339!4d-75.6811', 4.5339, -75.6811],
            'consulta q=' => ['https://www.google.com/maps?q=4.5339,-75.6811', 4.5339, -75.6811],
            'openstreetmap' => ['https://www.openstreetmap.org/?mlat=4.5339&mlon=-75.6811', 4.5339, -75.6811],
        ];
    }

    #[DataProvider('enlacesConCoordenadas')]
    public function test_saca_las_coordenadas_del_enlace(string $url, float $latitud, float $longitud): void
    {
        $coordenadas = Centro::coordenadasDesdeUrl($url);

        $this->assertNotNull($coordenadas, 'No se extrajeron coordenadas de: '.$url);
        $this->assertEqualsWithDelta($latitud, $coordenadas['latitud'], 0.0001);
        $this->assertEqualsWithDelta($longitud, $coordenadas['longitud'], 0.0001);
    }

    public function test_el_enlace_corto_no_trae_coordenadas_y_no_pasa_nada(): void
    {
        $this->assertNull(Centro::coordenadasDesdeUrl('https://maps.app.goo.gl/aBcDeFgH'));
        $this->assertNull(Centro::coordenadasDesdeUrl(null));
    }

    public function test_solo_acepta_dominios_de_mapas(): void
    {
        $this->assertTrue(Centro::esUrlDeMapaValida('https://maps.app.goo.gl/aBcDeFgH'));
        $this->assertTrue(Centro::esUrlDeMapaValida('https://www.google.com/maps/@4.5,-75.6,17z'));
        $this->assertTrue(Centro::esUrlDeMapaValida('https://www.openstreetmap.org/?mlat=4.5'));
        $this->assertTrue(Centro::esUrlDeMapaValida(null));

        // Ese enlace termina como boton en una pagina publica.
        $this->assertFalse(Centro::esUrlDeMapaValida('https://sitio-cualquiera.com/algo'));
        $this->assertFalse(Centro::esUrlDeMapaValida('javascript:alert(1)'));
        $this->assertFalse(Centro::esUrlDeMapaValida('http://google.com.sitio-falso.co/maps'));
    }

    public function test_el_boton_usa_el_enlace_del_coordinador_cuando_existe(): void
    {
        $centro = $this->centro([
            'mapa_url' => 'https://maps.app.goo.gl/aBcDeFgH',
            'latitud' => 4.5339,
            'longitud' => -75.6811,
        ]);

        $this->get(route('publico.centro', $centro))
            ->assertOk()
            ->assertSee('Cómo llegar')
            ->assertSee('maps.app.goo.gl/aBcDeFgH', false);
    }

    public function test_sin_enlace_arma_uno_con_las_coordenadas(): void
    {
        $centro = $this->centro(['latitud' => 4.5339, 'longitud' => -75.6811]);

        $this->assertStringContainsString('openstreetmap.org', $centro->como_llegar_url);

        $this->get(route('publico.centro', $centro))
            ->assertOk()
            ->assertSee('Cómo llegar');
    }

    public function test_sin_enlace_ni_coordenadas_no_se_muestra_el_boton(): void
    {
        $centro = $this->centro();

        $this->assertNull($centro->como_llegar_url);

        $this->get(route('publico.centro', $centro))
            ->assertOk()
            ->assertDontSee('Cómo llegar');
    }
}
