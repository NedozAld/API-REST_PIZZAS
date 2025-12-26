<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Ajustar email y estado (se mantiene string para evitar ALTER enum en PG)
            $table->string('email', 100)->change();
            $table->string('estado', 20)->default('activo')->change();

            // Nuevos campos de seguridad
            $table->unsignedInteger('intentos_fallidos')->default(0)->after('rol_id');
            $table->timestamp('bloqueado_hasta')->nullable()->after('intentos_fallidos');
            $table->timestamp('ultimo_login')->nullable()->after('bloqueado_hasta');

            // Campos de autenticación estándar
            $table->timestamp('email_verified_at')->nullable()->after('estado');
            $table->rememberToken();

            // Indices
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropColumn(['intentos_fallidos', 'bloqueado_hasta', 'ultimo_login', 'email_verified_at', 'remember_token']);
            // Revertir longitudes
            $table->string('estado', 20)->default('activo')->change();
            $table->string('email', 255)->change();
        });
    }
};
