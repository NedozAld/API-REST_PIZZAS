<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionCliente extends Model
{
    use HasFactory;

    protected $table = 'direcciones_cliente';

    protected $fillable = [
        'cliente_id',
        'nombre_direccion',
        'calle',
        'numero',
        'apartamento',
        'ciudad',
        'codigo_postal',
        'provincia',
        'referencia',
        'favorita',
        'activa',
    ];

    protected $casts = [
        'favorita' => 'boolean',
        'activa' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client this address belongs to
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Get formatted full address
     */
    public function getDireccionCompletoAttribute(): string
    {
        $direccion = "{$this->calle} {$this->numero}";
        if ($this->apartamento) {
            $direccion .= " Apt. {$this->apartamento}";
        }
        $direccion .= ", {$this->ciudad}";
        if ($this->provincia) {
            $direccion .= ", {$this->provincia}";
        }
        $direccion .= " {$this->codigo_postal}";
        return $direccion;
    }
}
