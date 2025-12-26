<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            if (!Schema::hasColumn('auditoria', 'created_at')) {
                $table->timestamp('created_at')->useCurrent()->after('user_agent');
            }
            if (!Schema::hasColumn('auditoria', 'updated_at')) {
                $table->timestamp('updated_at')->useCurrent()->after('created_at');
            }

            $table->index('usuario_id');
            $table->index('tabla_afectada');
            $table->index('tipo_accion');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropIndex(['usuario_id']);
            $table->dropIndex(['tabla_afectada']);
            $table->dropIndex(['tipo_accion']);
            $table->dropIndex(['created_at']);
            if (Schema::hasColumn('auditoria', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('auditoria', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
