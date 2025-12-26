<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50);
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->boolean('vista')->default(false);
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->index('tipo');
            $table->index('pedido_id');
            $table->index('vista');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
