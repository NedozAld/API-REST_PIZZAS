<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_base', 10, 2);
            $table->foreignId('categoria_id')
                  ->constrained('categorias')
                  ->restrictOnDelete();
            $table->unsignedInteger('stock_disponible')->default(0);
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->boolean('disponible')->default(true);
            $table->string('imagen_url', 500)->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
    }
};
