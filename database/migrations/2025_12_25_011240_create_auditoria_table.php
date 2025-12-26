<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();
            $table->string('nombre_usuario', 100);
            $table->string('tabla_afectada', 100);
            $table->string('tipo_accion', 30);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_accion')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->unsignedInteger('duracion_operacion_ms')->nullable();
            $table->unsignedTinyInteger('frecuencia_likert')->nullable();
            $table->unsignedTinyInteger('impacto_likert')->nullable();
            $table->unsignedTinyInteger('seguridad_likert')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
