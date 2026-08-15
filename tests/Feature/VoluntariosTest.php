<?php

namespace Tests\Feature;

use App\Models\Centro;
use App\Models\Inscripcion;
use App\Models\Turno;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoluntariosTest extends TestCase
{
    use RefreshDatabase;

    private function turno(array $atributos = []): Turno
    {
        $centro = Centro::create([
            'nombre' => 'Coliseo Municipal',
            'tipo' => 'acopio',
            'direccion' => 'Carrera 7 # 20-30',
            'ciudad' => 'Pereira',
            'departamento' => 'Risaralda',
            'activo' => true,
        ]);

        return Turno::create(array_merge([
            'centro_id' => $centro->id,
            'fecha' => now()->addDay()->toDateString(),
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'tipo_tarea' => 'clasificar',
            'cupos' => 2,
            'abierto' => true,
        ], $atributos));
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Ana Restrepo',
            // Distinto del placeholder del formulario: si no, la prueba de
            // fuga de datos se dispara contra el propio ejemplo del campo.
            'celular' => '311 555 4433',
            'autorizacion_datos' => '1',
        ], $cambios);
    }

    public function test_el_formulario_se_abre_sin_cuenta(): void
    {
        $turno = $this->turno();

        $this->get(route('publico.turno', $turno))
            ->assertOk()
            ->assertSee('Anotarse como voluntario')
            ->assertSee('política de tratamiento de datos', false);
    }

    public function test_anotarse_guarda_la_autorizacion_con_su_fecha(): void
    {
        $turno = $this->turno();

        $this->post(route('publico.turno.anotar', $turno), $this->datosValidos())
            ->assertRedirect(route('publico.centro', $turno->centro));

        $inscripcion = Inscripcion::firstOrFail();

        $this->assertSame('Ana Restrepo', $inscripcion->nombre);
        // El celular se guarda solo con digitos: asi "311 555 4433" y
        // "3115554433" no se cuelan como dos personas distintas.
        $this->assertSame('3115554433', $inscripcion->celular);
        $this->assertTrue($inscripcion->autorizacion_datos);
        $this->assertNotNull($inscripcion->autorizacion_en);
        $this->assertSame('anotado', $inscripcion->estado);
    }

    public function test_sin_autorizacion_no_se_guarda_nada(): void
    {
        $turno = $this->turno();

        $this->from(route('publico.turno', $turno))
            ->post(route('publico.turno.anotar', $turno), $this->datosValidos(['autorizacion_datos' => null]))
            ->assertSessionHasErrors('autorizacion_datos');

        $this->assertSame(0, Inscripcion::count());
    }

    public function test_el_celular_nunca_aparece_en_la_vista_publica(): void
    {
        $turno = $this->turno();

        $this->post(route('publico.turno.anotar', $turno), $this->datosValidos());

        $this->get(route('publico.centro', $turno->centro))
            ->assertOk()
            ->assertDontSee('3115554433')
            ->assertDontSee('Ana Restrepo');

        $this->get(route('publico.turno', $turno))
            ->assertOk()
            ->assertDontSee('3115554433')
            ->assertDontSee('Ana Restrepo');
    }

    public function test_el_mismo_celular_no_se_anota_dos_veces(): void
    {
        $turno = $this->turno();

        $this->post(route('publico.turno.anotar', $turno), $this->datosValidos());

        $this->from(route('publico.turno', $turno))
            ->post(route('publico.turno.anotar', $turno), $this->datosValidos(['nombre' => 'Otro nombre']))
            ->assertSessionHas('cerrado');

        $this->assertSame(1, Inscripcion::count());
    }

    public function test_un_turno_lleno_no_recibe_mas_voluntarios(): void
    {
        $turno = $this->turno(['cupos' => 1]);

        $this->post(route('publico.turno.anotar', $turno), $this->datosValidos());

        $this->from(route('publico.turno', $turno))
            ->post(route('publico.turno.anotar', $turno), $this->datosValidos(['celular' => '3009999999']))
            ->assertSessionHas('cerrado');

        $this->assertSame(1, Inscripcion::count());
    }

    public function test_un_turno_que_ya_paso_no_recibe_voluntarios(): void
    {
        $turno = $this->turno(['fecha' => now()->subDays(2)->toDateString()]);

        $this->from(route('publico.turno', $turno))
            ->post(route('publico.turno.anotar', $turno), $this->datosValidos())
            ->assertSessionHas('cerrado');

        $this->assertSame(0, Inscripcion::count());
    }

    public function test_los_turnos_de_un_centro_apagado_no_son_publicos(): void
    {
        $turno = $this->turno();
        $turno->centro->update(['activo' => false]);

        $this->get(route('publico.turno', $turno))->assertNotFound();
        $this->post(route('publico.turno.anotar', $turno), $this->datosValidos())->assertNotFound();
    }

    public function test_la_politica_avisa_cuando_falta_el_responsable(): void
    {
        config(['acopio.responsable.nombre' => null, 'acopio.responsable.correo' => null]);

        $this->get(route('publico.privacidad'))
            ->assertOk()
            ->assertSee('falta identificar a la organización responsable', false);
    }

    public function test_la_politica_muestra_al_responsable_cuando_esta_configurado(): void
    {
        config([
            'acopio.responsable.nombre' => 'Fundación de prueba',
            'acopio.responsable.correo' => 'datos@ejemplo.org',
        ]);

        $this->get(route('publico.privacidad'))
            ->assertOk()
            ->assertSee('Fundación de prueba')
            ->assertSee('datos@ejemplo.org')
            ->assertDontSee('falta identificar a la organización responsable', false);
    }
}
