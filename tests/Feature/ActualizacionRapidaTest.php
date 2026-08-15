<?php

namespace Tests\Feature;

use App\Livewire\ActualizacionRapida;
use App\Models\Centro;
use App\Models\Item;
use App\Models\Necesidad;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActualizacionRapidaTest extends TestCase
{
    use RefreshDatabase;

    private function centroConNecesidad(int $requerida = 100, int $cubierta = 20): Necesidad
    {
        $centro = Centro::create([
            'nombre' => 'Centro de pruebas',
            'tipo' => 'acopio',
            'direccion' => 'Calle 1',
            'ciudad' => 'Pereira',
            'departamento' => 'Risaralda',
            'activo' => true,
        ]);

        // items.nombre es unique: dos centros comparten el mismo insumo.
        $item = Item::firstOrCreate(
            ['nombre' => 'Colchoneta'],
            ['unidad' => 'unidad', 'categoria' => 'habitat', 'activo' => true],
        );

        return Necesidad::create([
            'centro_id' => $centro->id,
            'item_id' => $item->id,
            'cantidad_requerida' => $requerida,
            'cantidad_cubierta' => $cubierta,
            'prioridad' => 'alta',
        ]);
    }

    public function test_la_pantalla_rapida_exige_autenticacion(): void
    {
        $necesidad = $this->centroConNecesidad();

        $this->get(route('rapido', $necesidad->centro))
            ->assertRedirect();
    }

    public function test_el_coordinador_autenticado_ve_sus_insumos(): void
    {
        $necesidad = $this->centroConNecesidad();

        $this->actingAs(User::factory()->create())
            ->get(route('rapido', $necesidad->centro))
            ->assertOk()
            ->assertSee('Colchoneta')
            ->assertSee('🏕️', false);
    }

    public function test_sumar_y_restar_guardan_de_inmediato(): void
    {
        $necesidad = $this->centroConNecesidad(cubierta: 20);

        Livewire::actingAs(User::factory()->create())
            ->test(ActualizacionRapida::class, ['centro' => $necesidad->centro])
            ->call('ajustar', $necesidad->id, 1)
            ->call('cambiarPaso', 10)
            ->call('ajustar', $necesidad->id, 1)
            ->call('ajustar', $necesidad->id, -1);

        // 20 +1 (paso 1) +10 -10 (paso 10)
        $this->assertSame(21, $necesidad->fresh()->cantidad_cubierta);
    }

    public function test_restar_nunca_deja_la_cantidad_en_negativo(): void
    {
        $necesidad = $this->centroConNecesidad(cubierta: 3);

        Livewire::actingAs(User::factory()->create())
            ->test(ActualizacionRapida::class, ['centro' => $necesidad->centro])
            ->call('cambiarPaso', 50)
            ->call('ajustar', $necesidad->id, -1);

        $this->assertSame(0, $necesidad->fresh()->cantidad_cubierta);
    }

    public function test_ya_esta_completa_la_necesidad_de_un_toque(): void
    {
        $necesidad = $this->centroConNecesidad(requerida: 100, cubierta: 20);

        Livewire::actingAs(User::factory()->create())
            ->test(ActualizacionRapida::class, ['centro' => $necesidad->centro])
            ->call('completar', $necesidad->id);

        $this->assertSame(100, $necesidad->fresh()->cantidad_cubierta);
        $this->assertTrue($necesidad->fresh()->cubierta);
    }

    public function test_no_se_puede_tocar_la_necesidad_de_otro_centro(): void
    {
        $propia = $this->centroConNecesidad();
        $ajena = $this->centroConNecesidad();

        $this->expectException(ModelNotFoundException::class);

        try {
            Livewire::actingAs(User::factory()->create())
                ->test(ActualizacionRapida::class, ['centro' => $propia->centro])
                ->call('ajustar', $ajena->id, 1);
        } finally {
            $this->assertSame(20, $ajena->fresh()->cantidad_cubierta);
        }
    }
}
