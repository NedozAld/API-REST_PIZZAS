<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'nombre_usuario',
        'tabla_afectada',
        'tipo_accion',
        'registro_id',
        'datos_anteriores',
        'datos_nuevos',
        'descripcion',
        'fecha_accion',
        'ip_address',
        'user_agent',
        'duracion_operacion_ms',
        'frecuencia_likert',
        'impacto_likert',
        'seguridad_likert',
    ];

    protected $casts = [
        'datos_anteriores' => 'json',
        'datos_nuevos' => 'json',
        'fecha_accion' => 'datetime',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
