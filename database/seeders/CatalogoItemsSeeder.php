<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class CatalogoItemsSeeder extends Seeder
{
    /**
     * Catalogo cerrado de insumos.
     *
     * Basado en lo que las organizaciones humanitarias priorizan
     * en la emergencia: kits de habitat, higiene, agua y alimento
     * no perecedero. Ajustalo con el coordinador antes de publicar.
     */
    public function run(): void
    {
        $items = [
            // [nombre, unidad, categoria]
            ['Agua embotellada', 'botella 600 ml', 'agua'],
            ['Bidon de agua', 'bidon 20 L', 'agua'],
            ['Arroz', 'kg', 'alimento'],
            ['Panela', 'kg', 'alimento'],
            ['Aceite de cocina', 'litro', 'alimento'],
            ['Atun enlatado', 'lata', 'alimento'],
            ['Frijol o lenteja', 'kg', 'alimento'],
            ['Pasta', 'kg', 'alimento'],
            ['Colchoneta', 'unidad', 'habitat'],
            ['Cobija', 'unidad', 'habitat'],
            ['Carpa', 'unidad', 'habitat'],
            ['Plastico o lona impermeable', 'metro', 'habitat'],
            ['Kit de aseo personal', 'kit', 'higiene'],
            ['Jabon de bano', 'unidad', 'higiene'],
            ['Papel higienico', 'rollo', 'higiene'],
            ['Toallas higienicas', 'paquete', 'higiene'],
            ['Panal de bebe', 'paquete', 'bebe'],
            ['Formula infantil', 'tarro', 'bebe'],
            ['Botiquin de primeros auxilios', 'kit', 'salud'],
            ['Suero oral', 'sobre', 'salud'],
            ['Tapabocas', 'unidad', 'salud'],
            ['Guantes de construccion', 'par', 'herramienta'],
            ['Casco de seguridad', 'unidad', 'herramienta'],
            ['Gafas de seguridad', 'unidad', 'herramienta'],
            ['Linterna', 'unidad', 'herramienta'],
            ['Pilas', 'paquete', 'herramienta'],
        ];

        foreach ($items as [$nombre, $unidad, $categoria]) {
            Item::updateOrCreate(
                ['nombre' => $nombre],
                ['unidad' => $unidad, 'categoria' => $categoria, 'activo' => true],
            );
        }
    }
}
