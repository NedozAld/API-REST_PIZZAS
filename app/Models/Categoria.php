<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
