<?php

namespace Tests\Feature;

use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Necesidades\Pages\ListNecesidades;
use App\Filament\Widgets\NecesidadesUrgentes;
use App\Filament\Widgets\ResumenGeneral;
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

    /**
     * Filament devuelve 403 fuera de "local" si User no implementa
     * FilamentUser. El entorno de pruebas es "testing", asi que esta prueba
     * reproduce la condicion que se dio en produccion.
     */
    public function test_el_coordinador_entra_al_panel_fuera_de_local(): void
    {
        $this->assertNotSame('local', config('app.env'));

        $this->actingAs(User::factory()->create())
            ->get('/admin/centros')
            ->assertOk();
    }

    /**
     * Los widgets de Filament se cargan de forma diferida, asi que no salen
     * en el HTML inicial del escritorio: hay que probarlos como componentes.
     */
    public function test_el_resumen_cuenta_centros_urgencias_y_cobertura(): void
    {
        $this->centroConNecesidad(requerida: 100, cubierta: 20);

        Livewire::actingAs(User::factory()->create())
            ->test(ResumenGeneral::class)
            ->assertSee('Centros abiertos')
            ->assertSee('Insumos urgentes')
            ->assertSee('Cobertura')
            ->assertSee('20%')
            ->assertSee('Sin actualizar hoy');
    }

    public function test_lo_urgente_lista_lo_pendiente_y_esconde_lo_cubierto(): void
    {
        $pendiente = $this->centroConNecesidad(requerida: 100, cubierta: 20);
        $cubierta = $this->centroConNecesidad(requerida: 50, cubierta: 50);

        Livewire::actingAs(User::factory()->create())
            ->test(NecesidadesUrgentes::class)
            ->assertCanSeeTableRecords([$pendiente])
            ->assertCanNotSeeTableRecords([$cubierta]);
    }

    public function test_la_tabla_de_necesidades_agrupa_por_centro(): void
    {
        $necesidad = $this->centroConNecesidad();

        $componente = Livewire::actingAs(User::factory()->create())
            ->test(ListNecesidades::class)
            ->assertCanSeeTableRecords([$necesidad]);

        $grupo = $componente->instance()->getTable()->getDefaultGroup();

        $this->assertSame('centro.nombre', $grupo?->getId());
        $this->assertTrue($grupo?->isCollapsible());
    }

    public function test_se_puede_ir_y_volver_entre_el_panel_y_la_vista_publica(): void
    {
        $necesidad = $this->centroConNecesidad();
        $usuario = User::factory()->create();

        // Con sesion abierta, la vista publica ofrece la vuelta al panel.
        $this->actingAs($usuario)
            ->get(route('publico.index'))
            ->assertOk()
            ->assertSee('Ir al panel')
            ->assertSee('/admin', false);

        // Y la pantalla rapida tambien.
        $this->actingAs($usuario)
            ->get(route('rapido', $necesidad->centro))
            ->assertOk()
            ->assertSee('Volver al panel');
    }

    public function test_el_donante_no_ve_nada_del_panel(): void
    {
        $this->centroConNecesidad();

        $this->get(route('publico.index'))
            ->assertOk()
            ->assertDontSee('Ir al panel')
            ->assertDontSee('Está viendo la página pública', false)
            // Salvo la puerta de entrada para coordinadores, en el pie.
            ->assertSee('Soy coordinador de un centro');
    }

    public function test_publica_varios_insumos_en_un_centro_de_una_vez(): void
    {
        $necesidad = $this->centroConNecesidad();
        $centro = $necesidad->centro;

        $agua = Item::create(['nombre' => 'Agua embotellada', 'unidad' => 'botella', 'categoria' => 'agua', 'activo' => true]);
        $arroz = Item::create(['nombre' => 'Arroz', 'unidad' => 'kg', 'categoria' => 'alimento', 'activo' => true]);

        Livewire::actingAs(User::factory()->create())
            ->test(ListItems::class)
            // La Colchoneta ya esta publicada en ese centro: no se debe tocar.
            ->callTableBulkAction('asignar', [$agua->id, $arroz->id, $necesidad->item_id], [
                'centro_id' => $centro->id,
                'prioridad' => 'alta',
                'cantidad_requerida' => 100,
            ]);

        $this->assertSame(3, $centro->necesidades()->count());

        $nueva = $centro->necesidades()->where('item_id', $agua->id)->firstOrFail();
        $this->assertSame(100, $nueva->cantidad_requerida);
        $this->assertSame('alta', $nueva->prioridad);

        // La que ya existia conserva lo que el coordinador tenia recibido.
        $this->assertSame(20, $necesidad->fresh()->cantidad_cubierta);
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
