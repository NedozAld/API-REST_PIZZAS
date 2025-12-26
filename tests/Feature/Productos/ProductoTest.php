<?php

namespace Tests\Feature\Productos;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    protected $usuario;
    protected $token;
    protected $categoria;

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
    }

    /**
     * US-012: Ver menú público exitosamente
     */
    public function test_menu_publico_retorna_productos_disponibles()
    {
        // Crear algunos productos disponibles
        Producto::factory()->count(3)->create([
            'categoria_id' => $this->categoria->id,
            'disponible' => true,
            'activo' => true,
        ]);

        // Crear un producto no disponible
        Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'disponible' => false,
            'activo' => true,
        ]);

        $response = $this->getJson('/api/menu');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'nombre',
                        'precio_base',
                        'stock_disponible',
                        'disponible',
                        'activo',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Verificar que solo retorna 3 productos disponibles
        $this->assertEquals(3, count($response['data']));
    }

    /**
     * US-010: Crear producto exitoso
     */
    public function test_crear_producto_exitoso()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson('/api/productos', [
            'nombre' => 'Pizza Margarita',
            'descripcion' => 'Pizza clásica con tomate, queso y albahaca',
            'precio_base' => 45.00,
            'categoria_id' => $this->categoria->id,
            'stock_disponible' => 50,
            'stock_minimo' => 10,
            'costo' => 15.00,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Producto creado exitosamente',
            ])
            ->assertJsonStructure([
                'data' => [
                    'producto' => [
                        'id',
                        'nombre',
                        'precio_base',
                        'stock_disponible',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Pizza Margarita',
        ]);
    }

    /**
     * Crear producto falla sin autenticación
     */
    public function test_crear_producto_falla_sin_autenticacion()
    {
        $response = $this->postJson('/api/productos', [
            'nombre' => 'Pizza Margarita',
            'precio_base' => 45.00,
            'categoria_id' => $this->categoria->id,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Crear producto falla con nombre duplicado
     */
    public function test_crear_producto_falla_con_nombre_duplicado()
    {
        Producto::factory()->create([
            'nombre' => 'Pizza Margarita',
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson('/api/productos', [
            'nombre' => 'Pizza Margarita',
            'precio_base' => 45.00,
            'categoria_id' => $this->categoria->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    /**
     * US-011: Editar precio de producto
     */
    public function test_editar_precio_producto_exitoso()
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'precio_base' => 45.00,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->patchJson("/api/productos/{$producto->id}/precio", [
            'precio_base' => 55.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Precio actualizado exitosamente',
            ]);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'precio_base' => 55.00,
        ]);
    }

    /**
     * Editar precio falla con valor negativo
     */
    public function test_editar_precio_falla_con_valor_negativo()
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->patchJson("/api/productos/{$producto->id}/precio", [
            'precio_base' => -10.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['precio_base']);
    }

    /**
     * Actualizar producto completo
     */
    public function test_actualizar_producto_completo_exitoso()
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->patchJson("/api/productos/{$producto->id}", [
            'nombre' => 'Pizza Pepperoni Deluxe',
            'descripcion' => 'Pizza con pepperoni premium',
            'precio_base' => 65.00,
            'stock_disponible' => 100,
            'disponible' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
            ]);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'Pizza Pepperoni Deluxe',
            'disponible' => false,
        ]);
    }

    /**
     * Actualizar producto falla con categoria inexistente
     */
    public function test_actualizar_producto_falla_con_categoria_inexistente()
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->patchJson("/api/productos/{$producto->id}", [
            'categoria_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['categoria_id']);
    }
}
