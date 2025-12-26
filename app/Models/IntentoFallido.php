<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntentoFallido extends Model
{
    use HasFactory;

    protected $table = 'intentos_fallidos';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'email',
        'ip_address',
        'razon',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
