<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Eliminar la FK constraint existente
            $table->dropForeign(['cliente_id']);
            
            // Modificar la columna para que sea nullable
            $table->foreignId('cliente_id')
                  ->nullable()
                  ->change();
            
            // Volver a crear la FK constraint
            $table->foreign('cliente_id')
                  ->references('id')
                  ->on('clientes')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Eliminar la FK
            $table->dropForeign(['cliente_id']);
            
            // Volver a NOT NULL
            $table->foreignId('cliente_id')
                  ->nullable(false)
                  ->change();
            
            // Recrear FK
            $table->foreign('cliente_id')
                  ->references('id')
                  ->on('clientes')
                  ->cascadeOnDelete();
        });
    }
};
