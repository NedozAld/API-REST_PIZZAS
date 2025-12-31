<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescuentoVolumen extends Model
{
    use HasFactory;

    protected $table = 'descuentos_volumen';

    protected $fillable = [
        'monto_minimo',
        'monto_maximo',
        'porcentaje_descuento',
        'activo',
        'descripcion'
    ];

    protected $casts = [
        'monto_minimo' => 'decimal:2',
        'monto_maximo' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'activo' => 'boolean'
    ];

    /**
     * Obtener el descuento aplicable para un monto dado
     */
    public static function obtenerDescuentoPara(float $monto): ?self
    {
        return self::where('activo', true)
            ->where('monto_minimo', '<=', $monto)
            ->where(function($q) use ($monto) {
                $q->whereNull('monto_maximo')
                  ->orWhere('monto_maximo', '>=', $monto);
            })
            ->orderBy('porcentaje_descuento', 'desc')
            ->first();
    }

    /**
     * Scope para descuentos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Verificar si el monto califica para este descuento
     */
    public function aplicaA(float $monto): bool
    {
        if (!$this->activo) {
            return false;
        }

        if ($monto < $this->monto_minimo) {
            return false;
        }

        if ($this->monto_maximo !== null && $monto > $this->monto_maximo) {
            return false;
        }

        return true;
    }

    /**
     * Calcular descuento para un monto
     */
    public function calcularDescuento(float $monto): float
    {
        return ($monto * $this->porcentaje_descuento) / 100;
    }

    /**
     * Obtener información formateada
     */
    public function getInformacionFormateadaAttribute(): string
    {
        $info = "Compra ";
        
        if ($this->monto_maximo) {
            $info .= "entre \$" . number_format($this->monto_minimo, 2) . 
                     " y \$" . number_format($this->monto_maximo, 2);
        } else {
            $info .= "mayor a \$" . number_format($this->monto_minimo, 2);
        }
        
        $info .= " → " . $this->porcentaje_descuento . "% descuento";
        
        return $info;
    }
}
