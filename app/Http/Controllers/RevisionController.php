<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Elemento;
use App\Models\Tipo;
use App\Models\Estado;

class RevisionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole('revisor')) {
            return view('revision.welcome');
        }

        $query = Elemento::with(['ultimoMovimiento.ubicacion', 'ultimoMovimiento.estado']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nro_lia', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('ultimoMovimiento.ubicacion', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ultimoMovimiento.estado', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tipo')) {
            $query->whereHas('tipo', function ($q) use ($request) {
                $q->where('nombre', $request->tipo);
            });
        }

        if ($request->filled('gb')) {
            $gb = $request->input('gb');
            $query->where('descripcion', 'like', "%{$gb}GB%");
        }

        if ($request->filled('estado')) {
            $query->whereHas('ultimoMovimiento.estado', function ($q) use ($request) {
                $q->where('nombre', $request->estado);
            });
        }

        $elementos = $query->paginate(10)->withQueryString();
        $estados = Estado::all();
        $tipos = Tipo::all();
        return view('revision.index', compact('elementos', 'estados', 'tipos'));
    }
}
