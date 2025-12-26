<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->string('nombre', 120);
            $table->string('telefono', 20)->nullable();
            $table->foreignId('rol_id')
                  ->nullable()
                  ->constrained('roles')
                  ->nullOnDelete();
            $table->string('estado', 20)->default('activo');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('ultima_conexion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
