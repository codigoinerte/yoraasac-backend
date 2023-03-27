<?php

namespace Database\Factories;

use App\Models\productos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class productosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = productos::class;

    public function definition()
    {
        return [
            'codigo' => $this->faker->unique()->word(),
            'nombre' => $this->faker->sentence(),
            'orden' => $this->faker->numberBetween(1, 100),
            'estado' => $this->faker->numberBetween(0, 1),
            'stock_alerta' => $this->faker->randomNumber(2),
            'precio_venta' => $this->faker->randomFloat(2, 0, 1000),
            'descuento' => $this->faker->numberBetween(20, 80),
            'idtipo_igv' => $this->faker->randomNumber(1),
            'destacado' => $this->faker->numberBetween(0, 1),
            'estados_id' => $this->faker->randomNumber(1),
            'unspsc_id' => $this->faker->randomNumber(1),
            'marcas_id' => $this->faker->randomNumber(1),
            'unidad_id' => $this->faker->randomNumber(1),
            'moneda_id' => $this->faker->randomNumber(1),
            'igv_id' => $this->faker->randomNumber(1),
        ];
    }
}
