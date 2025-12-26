<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cambios_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->restrictOnDelete();
            $table->string('tipo_movimiento', 20); // Entrada, Salida, Ajuste
            $table->unsignedInteger('cantidad_anterior');
            $table->unsignedInteger('cantidad_nueva');
            $table->string('motivo', 50)->nullable();
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->timestamps();

            $table->index('producto_id');
            $table->index('fecha_cambio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_stock');
    }
};
