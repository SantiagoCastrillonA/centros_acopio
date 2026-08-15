<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enlace al mapa del centro, pegado por el coordinador.
     *
     * Es aparte de latitud y longitud: un enlace corto de Google Maps no
     * trae coordenadas, y aun asi sirve para que el donante llegue. Cuando
     * el enlace si las trae, se copian a latitud y longitud, que son las
     * que usan el mapa y el orden por cercania.
     */
    public function up(): void
    {
        Schema::table('centros', function (Blueprint $table) {
            $table->string('mapa_url', 500)->nullable()->after('longitud');
        });
    }

    public function down(): void
    {
        Schema::table('centros', function (Blueprint $table) {
            $table->dropColumn('mapa_url');
        });
    }
};
