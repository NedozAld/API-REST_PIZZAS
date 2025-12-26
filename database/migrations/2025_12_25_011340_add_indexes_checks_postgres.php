<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $default = config('database.default');
        $driver = config("database.connections.$default.driver");
        if ($driver === 'pgsql') {
            // CHECK constraints
            DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_precio_base_nonneg CHECK (precio_base >= 0)');
            DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_stock_nonneg CHECK (stock_disponible >= 0 AND stock_minimo >= 0)');

            DB::statement("ALTER TABLE cupones ADD CONSTRAINT chk_tipo_descuento CHECK (tipo_descuento IN ('porcentaje','monto_fijo'))");
            DB::statement('ALTER TABLE cupones ADD CONSTRAINT chk_valor_descuento_nonneg CHECK (valor_descuento >= 0)');
            DB::statement('ALTER TABLE cupones ADD CONSTRAINT chk_usos_actuales_nonneg CHECK (usos_actuales >= 0)');
            DB::statement('ALTER TABLE cupones ADD CONSTRAINT chk_fechas_cupon CHECK (fecha_fin >= fecha_inicio)');

            DB::statement('ALTER TABLE pedidos ADD CONSTRAINT chk_subtotal_nonneg CHECK (subtotal >= 0)');
            DB::statement('ALTER TABLE pedidos ADD CONSTRAINT chk_impuesto_nonneg CHECK (impuesto >= 0)');
            DB::statement('ALTER TABLE pedidos ADD CONSTRAINT chk_costo_envio_nonneg CHECK (costo_entrega >= 0)');
            DB::statement('ALTER TABLE pedidos ADD CONSTRAINT chk_descuento_nonneg CHECK (monto_descuento >= 0)');
            DB::statement('ALTER TABLE pedidos ADD CONSTRAINT chk_total_nonneg CHECK (total >= 0)');

            DB::statement('ALTER TABLE detalle_pedido ADD CONSTRAINT chk_detalle_cantidad_pos CHECK (cantidad > 0)');
            DB::statement('ALTER TABLE detalle_pedido ADD CONSTRAINT chk_detalle_precio_nonneg CHECK (precio_unitario >= 0)');
            DB::statement('ALTER TABLE detalle_pedido ADD CONSTRAINT chk_detalle_subtotal_nonneg CHECK (subtotal >= 0)');

            DB::statement('ALTER TABLE auditoria ADD CONSTRAINT chk_likert_frecuencia CHECK (frecuencia_likert IS NULL OR (frecuencia_likert BETWEEN 1 AND 5))');
            DB::statement('ALTER TABLE auditoria ADD CONSTRAINT chk_likert_impacto CHECK (impacto_likert IS NULL OR (impacto_likert BETWEEN 1 AND 5))');
            DB::statement('ALTER TABLE auditoria ADD CONSTRAINT chk_likert_seguridad CHECK (seguridad_likert IS NULL OR (seguridad_likert BETWEEN 1 AND 5))');

            // Indexes
            DB::statement('CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_sesiones_usuario ON sesiones(usuario_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_intentos_usuario ON intentos_fallidos(usuario_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_productos_categoria ON productos(categoria_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_pedidos_cliente ON pedidos(cliente_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_detalle_producto ON detalle_pedido(producto_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_cupones_clientes ON cupones_clientes(cupon_id, cliente_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_cambios_stock_producto ON cambios_stock(producto_id)');
        }
    }

    public function down(): void
    {
        $default = config('database.default');
        $driver = config("database.connections.$default.driver");
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE productos DROP CONSTRAINT IF EXISTS chk_precio_base_nonneg');
            DB::statement('ALTER TABLE productos DROP CONSTRAINT IF EXISTS chk_stock_nonneg');

            DB::statement('ALTER TABLE cupones DROP CONSTRAINT IF EXISTS chk_tipo_descuento');
            DB::statement('ALTER TABLE cupones DROP CONSTRAINT IF EXISTS chk_valor_descuento_nonneg');
            DB::statement('ALTER TABLE cupones DROP CONSTRAINT IF EXISTS chk_usos_actuales_nonneg');
            DB::statement('ALTER TABLE cupones DROP CONSTRAINT IF EXISTS chk_fechas_cupon');

            DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_subtotal_nonneg');
            DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_impuesto_nonneg');
            DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_costo_envio_nonneg');
            DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_descuento_nonneg');
            DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_total_nonneg');

            DB::statement('ALTER TABLE detalle_pedido DROP CONSTRAINT IF EXISTS chk_detalle_cantidad_pos');
            DB::statement('ALTER TABLE detalle_pedido DROP CONSTRAINT IF EXISTS chk_detalle_precio_nonneg');
            DB::statement('ALTER TABLE detalle_pedido DROP CONSTRAINT IF EXISTS chk_detalle_subtotal_nonneg');

            DB::statement('ALTER TABLE auditoria DROP CONSTRAINT IF EXISTS chk_likert_frecuencia');
            DB::statement('ALTER TABLE auditoria DROP CONSTRAINT IF EXISTS chk_likert_impacto');
            DB::statement('ALTER TABLE auditoria DROP CONSTRAINT IF EXISTS chk_likert_seguridad');

            DB::statement('DROP INDEX IF EXISTS idx_usuarios_email');
            DB::statement('DROP INDEX IF EXISTS idx_sesiones_usuario');
            DB::statement('DROP INDEX IF EXISTS idx_intentos_usuario');
            DB::statement('DROP INDEX IF EXISTS idx_productos_categoria');
            DB::statement('DROP INDEX IF EXISTS idx_pedidos_cliente');
            DB::statement('DROP INDEX IF EXISTS idx_pedidos_estado');
            DB::statement('DROP INDEX IF EXISTS idx_detalle_producto');
            DB::statement('DROP INDEX IF EXISTS idx_cupones_clientes');
            DB::statement('DROP INDEX IF EXISTS idx_cambios_stock_producto');
        }
    }
};
