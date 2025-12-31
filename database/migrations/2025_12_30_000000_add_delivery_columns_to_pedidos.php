<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('direccion_entrega', 500)->nullable()->after('notas');
            $table->string('telefono_contacto', 20)->nullable()->after('direccion_entrega');
            $table->string('metodo_pago', 50)->nullable()->after('telefono_contacto');
            $table->string('comprobante_pago', 255)->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['direccion_entrega', 'telefono_contacto', 'metodo_pago', 'comprobante_pago']);
        });
    }
};
