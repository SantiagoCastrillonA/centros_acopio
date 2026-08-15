<?php

namespace Tests\Feature;

use App\Models\Centro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_de_sin_conexion_existe(): void
    {
        $this->get(route('publico.sin_conexion'))
            ->assertOk()
            ->assertSee('Se fue la conexión', false);
    }

    public function test_la_vista_publica_declara_el_manifiesto_y_registra_el_service_worker(): void
    {
        $this->get(route('publico.index'))
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee("navigator.serviceWorker.register('/sw.js')", false)
            ->assertSee('theme-color', false);
    }

    public function test_los_archivos_de_la_pwa_estan_en_public(): void
    {
        foreach ([
            'manifest.webmanifest',
            'sw.js',
            'iconos/icono-192.png',
            'iconos/icono-512.png',
            'iconos/icono-apple-180.png',
        ] as $archivo) {
            $this->assertFileExists(public_path($archivo), $archivo.' falta en public/');
        }
    }

    public function test_el_manifiesto_es_json_valido_y_apunta_a_iconos_reales(): void
    {
        $manifiesto = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

        $this->assertIsArray($manifiesto, 'El manifiesto no es JSON válido.');
        $this->assertSame('/', $manifiesto['start_url']);
        $this->assertSame('standalone', $manifiesto['display']);
        $this->assertNotEmpty($manifiesto['icons']);

        foreach ($manifiesto['icons'] as $icono) {
            $this->assertFileExists(public_path(ltrim($icono['src'], '/')));
        }
    }

    /**
     * El panel y la pantalla rapida no pueden quedar cacheados: son datos
     * que cambian y pantallas autenticadas.
     */
    public function test_el_service_worker_excluye_el_panel_y_la_pantalla_rapida(): void
    {
        $codigo = file_get_contents(public_path('sw.js'));

        foreach (['/admin', '/rapido', '/livewire'] as $ruta) {
            $this->assertStringContainsString("'".$ruta."'", $codigo, $ruta.' debería estar excluida del caché.');
        }

        $this->assertStringContainsString("peticion.method !== 'GET'", $codigo);
        $this->assertStringContainsString('/sin-conexion', $codigo);
    }

    public function test_la_portada_sigue_funcionando_sin_javascript(): void
    {
        Centro::create([
            'nombre' => 'Coliseo Municipal',
            'tipo' => 'acopio',
            'direccion' => 'Carrera 7',
            'ciudad' => 'Pereira',
            'departamento' => 'Risaralda',
            'activo' => true,
        ]);

        // El contenido va en el HTML, no lo pinta un script: si el service
        // worker o el JS fallan, la pagina sigue sirviendo.
        $html = $this->get(route('publico.index'))->assertOk()->getContent();
        $sinScripts = preg_replace('#<script[\s\S]*?</script>#i', '', $html);

        $this->assertStringContainsString('Coliseo Municipal', $sinScripts);
        $this->assertStringContainsString('Qué se necesita y dónde llevarlo', $sinScripts);
    }
}
