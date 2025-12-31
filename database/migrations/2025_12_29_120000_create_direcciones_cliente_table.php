<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('direcciones_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->cascadeOnDelete();
            $table->string('nombre_direccion', 100); // "Casa", "Oficina", etc.
            $table->string('calle', 255);
            $table->string('numero', 20);
            $table->string('apartamento', 20)->nullable();
            $table->string('ciudad', 100);
            $table->string('codigo_postal', 20);
            $table->string('provincia', 100)->nullable();
            $table->text('referencia')->nullable();
            $table->boolean('favorita')->default(false);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones_cliente');
    }
};
