<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Eliminar FK a estados_pedido y columna estado_id si existe
            if (Schema::hasColumn('pedidos', 'estado_id')) {
                $table->dropForeign(['estado_id']);
                $table->dropColumn('estado_id');
            }

            // Agregar columna estado enum
            if (!Schema::hasColumn('pedidos', 'estado')) {
                $table->enum('estado', [
                    'PENDIENTE',
                    'TICKET_ENVIADO',
                    'CONFIRMADO',
                    'EN_PREPARACION',
                    'LISTO',
                    'EN_ENTREGA',
                    'ENTREGADO',
                    'CANCELADO'
                ])->default('PENDIENTE')->after('total');
            }

            // Nuevas columnas de seguimiento WhatsApp
            $table->timestamp('fecha_ticket_enviado')->nullable()->after('estado');
            $table->timestamp('fecha_confirmacion_whatsapp')->nullable()->after('fecha_ticket_enviado');
            $table->string('metodo_confirmacion', 50)->nullable()->after('fecha_confirmacion_whatsapp');
            $table->string('whatsapp_message_sid', 100)->nullable()->after('metodo_confirmacion');
            $table->text('motivo_cancelacion')->nullable()->after('whatsapp_message_sid');

            // Índices
            $table->index('estado');
            $table->index('fecha_ticket_enviado');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['fecha_ticket_enviado']);
            $table->dropColumn([
                'fecha_ticket_enviado',
                'fecha_confirmacion_whatsapp',
                'metodo_confirmacion',
                'whatsapp_message_sid',
                'motivo_cancelacion',
                'estado',
            ]);

            // Restaurar estado_id si se necesita (solo estructura)
            $table->foreignId('estado_id')->after('cliente_id')->constrained('estados_pedido');
        });
    }
};
