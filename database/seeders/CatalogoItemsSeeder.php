<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class CatalogoItemsSeeder extends Seeder
{
    /**
     * Catalogo cerrado de insumos.
     *
     * La idea es que el coordinador escoja y no escriba: si cada uno teclea
     * "colchoneta" a su manera, los datos dejan de ser agregables y la
     * plataforma pierde su razon de ser. Por eso conviene que la lista sea
     * larga y cubra lo que de verdad se pide en terreno.
     *
     * updateOrCreate sobre el nombre: se puede volver a correr sin duplicar,
     * y agregar insumos nuevos es agregar filas aqui y correrlo de nuevo.
     *
     * Ajustalo con el coordinador antes de publicar.
     */
    public function run(): void
    {
        $items = [
            // [nombre, unidad, categoria]

            // Agua
            ['Agua embotellada', 'botella 600 ml', 'agua'],
            ['Bidon de agua', 'bidon 20 L', 'agua'],
            ['Bolsa de agua', 'bolsa 6 L', 'agua'],
            ['Pastillas potabilizadoras', 'sobre', 'agua'],
            ['Filtro de agua portatil', 'unidad', 'agua'],
            ['Tanque de almacenamiento', 'unidad', 'agua'],

            // Alimento no perecedero
            ['Arroz', 'kg', 'alimento'],
            ['Panela', 'kg', 'alimento'],
            ['Aceite de cocina', 'litro', 'alimento'],
            ['Atun enlatado', 'lata', 'alimento'],
            ['Sardina enlatada', 'lata', 'alimento'],
            ['Frijol o lenteja', 'kg', 'alimento'],
            ['Pasta', 'kg', 'alimento'],
            ['Harina de maiz', 'kg', 'alimento'],
            ['Avena', 'kg', 'alimento'],
            ['Sal', 'kg', 'alimento'],
            ['Azucar', 'kg', 'alimento'],
            ['Cafe', 'kg', 'alimento'],
            ['Chocolate de mesa', 'kg', 'alimento'],
            ['Leche en polvo', 'kg', 'alimento'],
            ['Galletas', 'paquete', 'alimento'],
            ['Sopa instantanea', 'paquete', 'alimento'],
            ['Bienestarina', 'kg', 'alimento'],
            ['Alimento para mascotas', 'kg', 'alimento'],

            // Habitat y descanso
            ['Colchoneta', 'unidad', 'habitat'],
            ['Colchon sencillo', 'unidad', 'habitat'],
            ['Cobija', 'unidad', 'habitat'],
            ['Sabana', 'unidad', 'habitat'],
            ['Almohada', 'unidad', 'habitat'],
            ['Toldillo', 'unidad', 'habitat'],
            ['Hamaca', 'unidad', 'habitat'],
            ['Carpa', 'unidad', 'habitat'],
            ['Plastico o lona impermeable', 'metro', 'habitat'],
            ['Toalla', 'unidad', 'habitat'],
            ['Botas de caucho', 'par', 'habitat'],
            ['Ropa interior nueva', 'unidad', 'habitat'],

            // Higiene
            ['Kit de aseo personal', 'kit', 'higiene'],
            ['Jabon de bano', 'unidad', 'higiene'],
            ['Crema dental', 'unidad', 'higiene'],
            ['Cepillo de dientes', 'unidad', 'higiene'],
            ['Shampoo', 'unidad', 'higiene'],
            ['Desodorante', 'unidad', 'higiene'],
            ['Papel higienico', 'rollo', 'higiene'],
            ['Toallas higienicas', 'paquete', 'higiene'],
            ['Panitos humedos', 'paquete', 'higiene'],
            ['Detergente para ropa', 'kg', 'higiene'],
            ['Jabon para loza', 'unidad', 'higiene'],
            ['Bolsas de basura', 'paquete', 'higiene'],
            ['Panal para adulto', 'paquete', 'higiene'],

            // Bebes y ninos
            ['Panal de bebe', 'paquete', 'bebe'],
            ['Formula infantil', 'tarro', 'bebe'],
            ['Tetero', 'unidad', 'bebe'],
            ['Crema antipanalitis', 'unidad', 'bebe'],
            ['Ropa de bebe', 'unidad', 'bebe'],
            ['Cobija de bebe', 'unidad', 'bebe'],

            // Salud
            ['Botiquin de primeros auxilios', 'kit', 'salud'],
            ['Suero oral', 'sobre', 'salud'],
            ['Acetaminofen', 'caja', 'salud'],
            ['Alcohol antiseptico', 'frasco', 'salud'],
            ['Gasas esteriles', 'paquete', 'salud'],
            ['Vendas', 'unidad', 'salud'],
            ['Curas adhesivas', 'caja', 'salud'],
            ['Guantes de latex', 'caja', 'salud'],
            ['Termometro', 'unidad', 'salud'],
            ['Repelente de insectos', 'unidad', 'salud'],
            ['Gel antibacterial', 'frasco', 'salud'],
            ['Tapabocas', 'unidad', 'salud'],

            // Herramienta y logistica
            ['Guantes de construccion', 'par', 'herramienta'],
            ['Casco de seguridad', 'unidad', 'herramienta'],
            ['Gafas de seguridad', 'unidad', 'herramienta'],
            ['Linterna', 'unidad', 'herramienta'],
            ['Pilas', 'paquete', 'herramienta'],
            ['Radio a pilas', 'unidad', 'herramienta'],
            ['Bateria portatil', 'unidad', 'herramienta'],
            ['Extension electrica', 'unidad', 'herramienta'],
            ['Pala', 'unidad', 'herramienta'],
            ['Carretilla', 'unidad', 'herramienta'],
            ['Machete', 'unidad', 'herramienta'],
            ['Martillo', 'unidad', 'herramienta'],
            ['Clavos', 'kg', 'herramienta'],
            ['Cuerda', 'metro', 'herramienta'],
            ['Escoba', 'unidad', 'herramienta'],
            ['Trapeador', 'unidad', 'herramienta'],
            ['Balde', 'unidad', 'herramienta'],
            ['Estufa portatil de gas', 'unidad', 'herramienta'],
            ['Cilindro de gas', 'unidad', 'herramienta'],
            ['Olla grande', 'unidad', 'herramienta'],
            ['Vajilla desechable', 'paquete', 'herramienta'],
            ['Cinta adhesiva y marcadores', 'kit', 'herramienta'],
        ];

        foreach ($items as [$nombre, $unidad, $categoria]) {
            Item::updateOrCreate(
                ['nombre' => $nombre],
                ['unidad' => $unidad, 'categoria' => $categoria, 'activo' => true],
            );
        }
    }
}
