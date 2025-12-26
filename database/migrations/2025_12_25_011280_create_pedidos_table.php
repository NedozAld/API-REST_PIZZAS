<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido', 30)->unique();
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->cascadeOnDelete();
            $table->foreignId('estado_id')
                  ->constrained('estados_pedido');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('costo_entrega', 10, 2)->default(0);
            $table->decimal('monto_descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->timestamp('fecha_entrega')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
