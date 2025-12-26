<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_base',
        'categoria_id',
        'stock_disponible',
        'stock_minimo',
        'disponible',
        'imagen_url',
        'costo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_base' => 'decimal:2',
            'costo' => 'decimal:2',
            'disponible' => 'boolean',
            'activo' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
