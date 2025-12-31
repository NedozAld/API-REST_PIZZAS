<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descuentos_volumen', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto_minimo', 10, 2)->comment('Monto mínimo de compra');
            $table->decimal('monto_maximo', 10, 2)->nullable()->comment('Monto máximo de compra (NULL = sin límite)');
            $table->decimal('porcentaje_descuento', 5, 2)->comment('Porcentaje de descuento');
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable()->comment('Descripción de la oferta');
            $table->timestamps();

            // Índice para búsquedas de rango
            $table->index(['monto_minimo', 'monto_maximo', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descuentos_volumen');
    }
};
