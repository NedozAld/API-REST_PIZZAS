<?php

namespace Tests\Feature\Pedidos;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use RefreshDatabase;

    protected $usuario;
    protected $token;
    protected $categoria;
    protected $productos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndUsersSeeder::class);

        $this->usuario = Usuario::where('email', 'admin@pizzeria.com')->first();
        $this->token = $this->usuario->createToken('test-token')->plainTextToken;

        $this->categoria = Categoria::firstOrCreate(
            ['nombre' => 'Pizzas'],
            ['descripcion' => 'Pizzas variadas']
        );

        // Crear productos disponibles
        $this->productos = Producto::factory()->count(3)->create([
            'categoria_id' => $this->categoria->id,
            'disponible' => true,
            'activo' => true,
            'stock_disponible' => 100,
            'precio_base' => 45.00,
        ]);
    }

    /**
     * US-020: Crear pedido exitoso
     */
    public function test_crear_pedido_exitoso()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson('/api/pedidos', [
            'items' => [
                [
                    'producto_id' => $this->productos[0]->id,
                    'cantidad' => 2,
                    'notas' => 'Sin cebolla',
                ],
                [
                    'producto_id' => $this->productos[1]->id,
                    'cantidad' => 1,
                ],
            ],
            'notas' => 'Entregar en la puerta',
            'costo_entrega' => 10.00,
            'monto_descuento' => 5.00,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
            ])
            ->assertJsonStructure([
                'data' => [
                    'pedido' => [
                        'id',
                        'numero_pedido',
                        'subtotal',
                        'impuesto',
                        'total',
                        'estado',
                        'detalles',
                    ]
                ]
            ]);

        // Verificar cálculo de totales
        $pedido = $response['data']['pedido'];
        $this->assertEquals('PENDIENTE', $pedido['estado']);
        $this->assertEquals(135.00, $pedido['subtotal']); // (45*2) + 45
        $this->assertEquals(13.50, $pedido['impuesto']); // 10% del subtotal
        $this->assertEquals(153.50, $pedido['total']); // 135 + 13.5 + 10 - 5

        // Verificar que el stock se redujo
        $this->produtos[0]->refresh();
        $this->assertEquals(98, $this->productos[0]->stock_disponible);
    }

    /**
     * Crear pedido falla sin items
     */
    public function test_crear_pedido_falla_sin_items()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson('/api/pedidos', [
            'items' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /**
     * Crear pedido falla con stock insuficiente
     */
    public function test_crear_pedido_falla_con_stock_insuficiente()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson('/api/pedidos', [
            'items' => [
                [
                    'producto_id' => $this->productos[0]->id,
                    'cantidad' => 200, // Stock disponible es 100
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.cantidad']);
    }

    /**
     * Crear pedido falla con producto no disponible
     */
    public function test_crear_pedido_falla_con_producto_no_disponible()
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'disponible' => false,
            'stock_disponible' => 100,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson('/api/pedidos', [
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.producto_id']);
    }

    /**
     * US-021: Confirmar pedido exitoso
     */
    public function test_confirmar_pedido_exitoso()
    {
        $pedido = Pedido::factory()->create([
            'estado' => 'PENDIENTE',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->patchJson("/api/pedidos/{$pedido->id}/confirmar");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pedido confirmado exitosamente',
            ]);

        $pedido->refresh();
        $this->assertEquals('CONFIRMADO', $pedido->estado);
        $this->assertNotNull($pedido->fecha_confirmacion);
        $this->assertEquals('manual', $pedido->metodo_confirmacion);
    }

    /**
     * Confirmar pedido falla si ya está confirmado
     */
    public function test_confirmar_pedido_falla_si_ya_esta_confirmado()
    {
        $pedido = Pedido::factory()->create([
            'estado' => 'CONFIRMADO',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->patchJson("/api/pedidos/{$pedido->id}/confirmar");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * US-022: Ver estado del pedido
     */
    public function test_ver_estado_pedido_exitoso()
    {
        $pedido = Pedido::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson("/api/pedidos/{$pedido->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'pedido' => [
                        'id',
                        'numero_pedido',
                        'estado',
                        'total',
                        'detalles',
                    ]
                ]
            ]);
    }

    /**
     * Ver pedido falla si no existe
     */
    public function test_ver_pedido_falla_si_no_existe()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson('/api/pedidos/9999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ]);
    }

    /**
     * Listar pedidos exitosamente
     */
    public function test_listar_pedidos_exitoso()
    {
        Pedido::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson('/api/pedidos');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'current_page',
                    'data',
                    'per_page',
                    'total',
                ]
            ]);

        $this->assertCount(5, $response['data']['data']);
    }

    /**
     * Listar pedidos con filtro por estado
     */
    public function test_listar_pedidos_con_filtro_estado()
    {
        Pedido::factory()->count(3)->create(['estado' => 'PENDIENTE']);
        Pedido::factory()->count(2)->create(['estado' => 'CONFIRMADO']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson('/api/pedidos?estado=PENDIENTE');

        $response->assertStatus(200);
        $this->assertCount(3, $response['data']['data']);
    }

    /**
     * Listar pedidos falla sin autenticación
     */
    public function test_listar_pedidos_falla_sin_autenticacion()
    {
        $response = $this->getJson('/api/pedidos');

        $response->assertStatus(401);
    }
}
