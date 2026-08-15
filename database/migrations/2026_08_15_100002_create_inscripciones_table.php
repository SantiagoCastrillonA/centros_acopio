<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guarda datos personales (nombre y celular) bajo la Ley 1581 de 2012.
     *
     * Por eso la tabla registra la autorizacion como un hecho con fecha, no
     * como una casilla suelta: si alguien reclama, hay que poder demostrar
     * cuando la dio. Y por eso no se guarda nada mas de lo necesario para
     * que el coordinador pueda llamar al voluntario.
     */
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('celular', 40);
            $table->boolean('autorizacion_datos')->default(false);
            $table->timestamp('autorizacion_en')->nullable();
            $table->enum('estado', ['anotado', 'asistio', 'no_asistio', 'cancelado'])->default('anotado');
            $table->timestamps();

            // Un mismo celular no se anota dos veces al mismo turno.
            $table->unique(['turno_id', 'celular']);
            $table->index(['turno_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
