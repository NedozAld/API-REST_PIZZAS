<?php

namespace Database\Factories;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'numero_pedido' => 'PED-' . $this->faker->unique()->numerify('########'),
            'cliente_id' => null,
            'subtotal' => fake()->randomFloat(2, 50, 500),
            'impuesto' => fake()->randomFloat(2, 5, 50),
            'costo_entrega' => fake()->randomFloat(2, 0, 30),
            'monto_descuento' => fake()->randomFloat(2, 0, 50),
            'total' => fake()->randomFloat(2, 50, 600),
            'estado' => 'PENDIENTE',
            'notas' => $this->faker->optional()->sentence(),
            'fecha_confirmacion' => null,
            'metodo_confirmacion' => null,
        ];
    }

    public function confirmado()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'CONFIRMADO',
                'fecha_confirmacion' => now(),
                'metodo_confirmacion' => 'manual',
            ];
        });
    }

    public function enPreparacion()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'EN_PREPARACION',
                'fecha_confirmacion' => now()->subHours(1),
            ];
        });
    }
}
