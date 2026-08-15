<?php

namespace Tests\Feature;

use App\Models\Centro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
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

    /**
     * El enlace corto es el que entrega Compartir en el celular, y es por
     * donde pasa un coordinador. Si no se resuelve, el centro queda sin
     * coordenadas y desaparece del mapa de la portada.
     */
    public function test_resuelve_el_enlace_corto_siguiendo_el_redirect(): void
    {
        // Recorte de lo que devuelve Google hoy: la direccion final ya no
        // trae las coordenadas, solo la imagen de vista previa.
        Http::fake([
            'maps.app.goo.gl/*' => Http::response(
                '<meta content="https://maps.google.com/maps/api/staticmap'
                .'?center=4.5156178%2C-75.6826148&amp;zoom=14" property="image">'
            ),
        ]);

        $coordenadas = Centro::coordenadasDesdeEnlace('https://maps.app.goo.gl/aBcDeFgH');

        $this->assertNotNull($coordenadas);
        $this->assertEqualsWithDelta(4.5156178, $coordenadas['latitud'], 0.0001);
        $this->assertEqualsWithDelta(-75.6826148, $coordenadas['longitud'], 0.0001);
    }

    /** El otro sitio del HTML donde aparecen, con la longitud primero. */
    public function test_resuelve_el_enlace_corto_por_el_formato_de_incrustar(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response(
                '!1m3!1d31819.46!2d-75.6826148!3d4.515617799999999!2m3!1f0.0'
            ),
        ]);

        $coordenadas = Centro::coordenadasDesdeEnlace('https://maps.app.goo.gl/aBcDeFgH');

        $this->assertNotNull($coordenadas);
        $this->assertEqualsWithDelta(4.5156178, $coordenadas['latitud'], 0.0001);
        $this->assertEqualsWithDelta(-75.6826148, $coordenadas['longitud'], 0.0001);
    }

    public function test_si_el_acortador_no_responde_el_centro_se_queda_sin_coordenadas(): void
    {
        Http::fake(fn () => throw new ConnectionException('sin red'));

        $this->assertNull(Centro::coordenadasDesdeEnlace('https://maps.app.goo.gl/aBcDeFgH'));
    }

    /** Un enlace fuera de la lista no genera ni una peticion de salida. */
    public function test_no_sale_a_la_red_por_un_dominio_que_no_esta_en_la_lista(): void
    {
        Http::fake();

        $this->assertNull(Centro::coordenadasDesdeEnlace('https://sitio-cualquiera.com/algo'));

        Http::assertNothingSent();
    }

    public function test_el_enlace_largo_no_necesita_salir_a_la_red(): void
    {
        Http::fake();

        $coordenadas = Centro::coordenadasDesdeEnlace('https://www.google.com/maps/@4.5339,-75.6811,17z');

        $this->assertNotNull($coordenadas);
        Http::assertNothingSent();
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
