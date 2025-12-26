<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word() . ' ' . fake()->word(),
            'descripcion' => $this->faker->sentence(),
            'precio_base' => fake()->randomFloat(2, 20, 100),
            'categoria_id' => 1,
            'stock_disponible' => fake()->numberBetween(0, 100),
            'stock_minimo' => fake()->numberBetween(5, 20),
            'costo' => fake()->randomFloat(2, 5, 50),
            'disponible' => true,
            'activo' => true,
        ];
    }

    public function noDisponible()
    {
        return $this->state(function (array $attributes) {
            return [
                'disponible' => false,
            ];
        });
    }

    public function noActivo()
    {
        return $this->state(function (array $attributes) {
            return [
                'activo' => false,
            ];
        });
    }
}
