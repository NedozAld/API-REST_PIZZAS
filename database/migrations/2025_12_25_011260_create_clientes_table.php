<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('email', 100)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('password');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('email');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
