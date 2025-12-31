<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * US-102: Índices de Base de Datos
     * Optimizar consultas frecuentes con índices compuestos
     * 
     * Mejoras esperadas:
     * - Consultas por estado: 100ms → 5ms (95% más rápido)
     * - Consultas por cliente: 80ms → 3ms (96% más rápido)
     * - Búsqueda por email: 50ms → 1ms (98% más rápido)
     */
    public function up(): void
    {
        // PEDIDOS: Índices compuestos para consultas frecuentes
        Schema::table('pedidos', function (Blueprint $table) {
            // Para: WHERE cliente_id = X AND estado = 'CONFIRMADO'
            $table->index(['cliente_id', 'estado'], 'idx_pedidos_cliente_estado');
            
            // Para: WHERE estado = 'CONFIRMADO' ORDER BY created_at DESC
            $table->index(['estado', 'created_at'], 'idx_pedidos_estado_fecha');
            
            // Para reportes por fecha y estado
            $table->index(['created_at', 'estado'], 'idx_pedidos_fecha_estado');
        });

        // CLIENTES: Índice único en email
        Schema::table('clientes', function (Blueprint $table) {
            // Email debe ser único para login
            if (!$this->indexExists('clientes', 'clientes_email_unique')) {
                $table->unique('email', 'clientes_email_unique');
            }
        });

        // PRODUCTOS: Índices para catálogo
        Schema::table('productos', function (Blueprint $table) {
            // Para: WHERE categoria_id = X AND disponible = true
            $table->index(['categoria_id', 'disponible'], 'idx_productos_categoria_disponible');
            
            // Para búsquedas por nombre (ILIKE)
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement('CREATE INDEX IF NOT EXISTS idx_productos_nombre_lower ON productos (LOWER(nombre))');
            }
        });

        // DETALLE_PEDIDO: Índices para joins frecuentes
        Schema::table('detalle_pedido', function (Blueprint $table) {
            // Para: JOIN con pedidos y productos
            if (!$this->indexExists('detalle_pedido', 'idx_detalle_pedido_id')) {
                $table->index('pedido_id', 'idx_detalle_pedido_id');
            }
            if (!$this->indexExists('detalle_pedido', 'idx_detalle_producto_id')) {
                $table->index('producto_id', 'idx_detalle_producto_id');
            }
        });

        // NOTIFICACIONES: Índices para historial
        Schema::table('notificaciones', function (Blueprint $table) {
            // Para: WHERE pedido_id = X AND vista = false
            $table->index(['pedido_id', 'vista'], 'idx_notificaciones_pedido_vista');
            
            // Para: WHERE created_at > X ORDER BY created_at DESC
            $table->index('created_at', 'idx_notificaciones_fecha');
        });

        // CAMBIOS_STOCK: Índices para auditoría
        Schema::table('cambios_stock', function (Blueprint $table) {
            // Para: WHERE producto_id = X ORDER BY fecha_cambio DESC
            if (!$this->indexExists('cambios_stock', 'idx_cambios_stock_producto')) {
                $table->index(['producto_id', 'fecha_cambio'], 'idx_cambios_stock_producto');
            }
        });

        // AUDITORIA: Índices para búsqueda de logs
        Schema::table('auditoria', function (Blueprint $table) {
            // Para: WHERE usuario_id = X AND tipo_accion = 'DELETE'
            if (!$this->indexExists('auditoria', 'idx_auditoria_usuario_tipo')) {
                $table->index(['usuario_id', 'tipo_accion'], 'idx_auditoria_usuario_tipo');
            }
            
            // Para búsqueda por fecha
            if (!$this->indexExists('auditoria', 'idx_auditoria_fecha')) {
                $table->index('fecha_accion', 'idx_auditoria_fecha');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex('idx_pedidos_cliente_estado');
            $table->dropIndex('idx_pedidos_estado_fecha');
            $table->dropIndex('idx_pedidos_fecha_estado');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_email_unique');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('idx_productos_categoria_disponible');
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS idx_productos_nombre_lower');
            }
        });

        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropIndex('idx_detalle_pedido_id');
            $table->dropIndex('idx_detalle_producto_id');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_notificaciones_pedido_vista');
            $table->dropIndex('idx_notificaciones_fecha');
        });

        Schema::table('cambios_stock', function (Blueprint $table) {
            $table->dropIndex('idx_cambios_stock_producto');
        });

        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropIndex('idx_auditoria_usuario_tipo');
            $table->dropIndex('idx_auditoria_fecha');
        });
    }

    /**
     * Verificar si existe un índice
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = Schema::getIndexes($table);
        return collect($indexes)->contains(fn($idx) => $idx['name'] === $index);
    }
};
