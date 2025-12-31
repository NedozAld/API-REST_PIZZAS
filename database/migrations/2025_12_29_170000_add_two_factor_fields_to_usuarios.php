<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * US-090: 2FA (Two-Factor Authentication)
     * Agregar campos para autenticación de dos factores
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Campo para indicar si 2FA está habilitado
            $table->boolean('dos_fa_habilitado')->default(false)->after('contrasena');
            
            // Campo para almacenar el secret key de Google Authenticator
            $table->text('dos_fa_secret')->nullable()->after('dos_fa_habilitado');
            
            // Campo para backup codes (códigos de recuperación)
            $table->json('dos_fa_backup_codes')->nullable()->after('dos_fa_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['dos_fa_habilitado', 'dos_fa_secret', 'dos_fa_backup_codes']);
        });
    }
};
