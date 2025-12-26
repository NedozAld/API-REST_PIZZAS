<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->string('clave', 100)->primary();
            $table->text('valor');
            $table->string('tipo', 20)->default('STRING');
            $table->text('descripcion')->nullable();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
