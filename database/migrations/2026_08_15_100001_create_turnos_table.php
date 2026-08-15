<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')->constrained('centros')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            // Lista cerrada, por la misma razon que el catalogo de insumos:
            // si cada coordinador escribe la tarea a su manera, no se puede
            // agregar ni buscar.
            $table->enum('tipo_tarea', [
                'clasificar',
                'cargar',
                'cocinar',
                'atender',
                'aseo',
                'inventario',
                'otro',
            ])->default('clasificar');
            $table->unsignedSmallInteger('cupos')->default(1);
            $table->string('nota')->nullable();
            $table->boolean('abierto')->default(true);
            $table->timestamps();

            $table->index(['centro_id', 'fecha']);
            $table->index(['abierto', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
