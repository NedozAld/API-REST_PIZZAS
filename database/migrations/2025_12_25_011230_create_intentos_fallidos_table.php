<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intentos_fallidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();
            $table->string('email', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('razon', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intentos_fallidos');
    }
};
