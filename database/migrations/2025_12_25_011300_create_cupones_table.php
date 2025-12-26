<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->string('tipo_descuento', 20);
            $table->decimal('valor_descuento', 10, 2);
            $table->decimal('descuento_maximo', 10, 2)->nullable();
            $table->decimal('compra_minima', 10, 2)->default(0);
            $table->unsignedInteger('usos_maximos')->nullable();
            $table->unsignedInteger('usos_actuales')->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
