<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detalle_pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_pedido', 'notas')) {
                $table->text('notas')->nullable()->after('subtotal');
            }
            // Índices ya existen por FK; se mantienen
        });
    }

    public function down(): void
    {
        Schema::table('detalle_pedido', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_pedido', 'notas')) {
                $table->dropColumn('notas');
            }
        });
    }
};
