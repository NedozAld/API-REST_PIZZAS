<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Mostrar página principal con productos
     */
    public function index(Request $request)
    {
        $categorias = Categoria::where('estado', true)
            ->orderBy('nombre')
            ->get();

        $query = Producto::where('disponible', true)
            ->where('activo', true)
            ->with('categoria');

        // Filtrar por categoría si se especifica
        if ($request->has('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        $productos = $query->orderBy('categoria_id')
            ->orderBy('nombre')
            ->paginate(12);

        return view('home', compact('productos', 'categorias'));
    }

    /**
     * Buscar productos
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $productos = Producto::where('disponible', true)
            ->where('activo', true)
            ->where('nombre', 'ILIKE', "%{$query}%")
            ->with('categoria')
            ->paginate(12);

        $categorias = Categoria::where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('home', compact('productos', 'categorias'));
    }
}
