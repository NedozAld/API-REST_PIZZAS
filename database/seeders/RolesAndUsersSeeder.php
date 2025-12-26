<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $roles = [
            [
                'nombre' => 'ADMINISTRADOR',
                'descripcion' => 'Administrador del sistema con acceso total',
                'activo' => true,
            ],
            [
                'nombre' => 'OPERADOR_COCINA',
                'descripcion' => 'Operador de cocina, prepara pedidos',
                'activo' => true,
            ],
            [
                'nombre' => 'OPERADOR_DELIVERY',
                'descripcion' => 'Operador de delivery, entrega pedidos',
                'activo' => true,
            ],
            [
                'nombre' => 'USUARIO',
                'descripcion' => 'Usuario estándar del sistema',
                'activo' => true,
            ],
            [
                'nombre' => 'AUDITOR',
                'descripcion' => 'Auditor del sistema',
                'activo' => true,
            ],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(
                ['nombre' => $rol['nombre']],
                $rol
            );
        }

        // Crear usuario administrador
        Usuario::firstOrCreate(
            ['email' => 'admin@lapizzeria.ec'],
            [
                'nombre' => 'Administrador',
                'email' => 'admin@lapizzeria.ec',
                'password_hash' => Hash::make('Admin@123456'),
                'telefono' => '+593998765432',
                'rol_id' => Rol::where('nombre', 'ADMINISTRADOR')->first()->id,
                'estado' => 'activo',
            ]
        );

        // Crear usuario de prueba
        Usuario::firstOrCreate(
            ['email' => 'usuario@lapizzeria.ec'],
            [
                'nombre' => 'Usuario Prueba',
                'email' => 'usuario@lapizzeria.ec',
                'password_hash' => Hash::make('Usuario@123456'),
                'telefono' => '+593987654321',
                'rol_id' => Rol::where('nombre', 'USUARIO')->first()->id,
                'estado' => 'activo',
            ]
        );

        // Crear cocinero de prueba
        Usuario::firstOrCreate(
            ['email' => 'cocinero@lapizzeria.ec'],
            [
                'nombre' => 'Carlos Cocinero',
                'email' => 'cocinero@lapizzeria.ec',
                'password_hash' => Hash::make('Cocinero@123456'),
                'telefono' => '+593976543210',
                'rol_id' => Rol::where('nombre', 'OPERADOR_COCINA')->first()->id,
                'estado' => 'activo',
            ]
        );

        // Crear repartidor de prueba
        Usuario::firstOrCreate(
            ['email' => 'repartidor@lapizzeria.ec'],
            [
                'nombre' => 'Juan Repartidor',
                'email' => 'repartidor@lapizzeria.ec',
                'password_hash' => Hash::make('Repartidor@123456'),
                'telefono' => '+593965432109',
                'rol_id' => Rol::where('nombre', 'OPERADOR_DELIVERY')->first()->id,
                'estado' => 'activo',
            ]
        );

        echo "✅ Roles y usuarios de prueba creados exitosamente\n";
    }
}
