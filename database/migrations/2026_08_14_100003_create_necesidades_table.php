<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('necesidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_id')->constrained('centros')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('cantidad_requerida')->default(0);
            $table->unsignedInteger('cantidad_cubierta')->default(0);
            $table->enum('prioridad', ['alta', 'media', 'baja'])->default('media');
            $table->string('nota')->nullable();
            $table->timestamps();

            $table->unique(['centro_id', 'item_id']);
            $table->index(['centro_id', 'prioridad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('necesidades');
    }
};
