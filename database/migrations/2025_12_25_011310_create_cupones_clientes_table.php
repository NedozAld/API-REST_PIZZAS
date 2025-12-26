<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cupones_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cupon_id')
                  ->constrained('cupones')
                  ->cascadeOnDelete();
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->cascadeOnDelete();
            $table->timestamp('fecha_uso')->useCurrent();
            $table->timestamps();

            $table->index(['cupon_id', 'cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupones_clientes');
    }
};
