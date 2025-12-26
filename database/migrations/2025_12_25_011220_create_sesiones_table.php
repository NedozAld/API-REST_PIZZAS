<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('usuarios')
                  ->cascadeOnDelete();
            $table->string('token_jwt', 500)->unique();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_expiracion');
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('estado', 20)->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
