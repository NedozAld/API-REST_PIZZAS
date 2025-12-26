<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    use HasFactory;

    protected $table = 'sesiones';
    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'token_jwt',
        'fecha_inicio',
        'fecha_expiracion',
        'fecha_cierre',
        'ip_address',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Verificar si la sesión está activa
     */
    public function estaActiva(): bool
    {
        return $this->estado === 'activa' && now()->isBefore($this->fecha_expiracion);
    }
}
