<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Elemento;
use App\Models\Tipo;

use Illuminate\View\View;

class RevisionController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (!$user || (!$user->hasRole(['rev', 'tec', 'item_admin', 'user_admin']))) {
            return view('revision.welcome');
        }

        $query = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nro_lia', 'like', "%{$search}%")
                  ->orWhere('nro_unsj', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('nro_serie', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhereHas('tipo', function ($tq) use ($search) {
                      $tq->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ultimoMovimiento.ubicacion', function ($uq) use ($search) {
                      $uq->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tipo')) {
            $query->whereHas('tipo', function ($q) use ($request) {
                $q->where('nombre', $request->tipo)->orWhere('id', $request->tipo);
            });
        }

        if ($request->filled('gb')) {
            $gb = $request->input('gb');
            $query->where('descripcion', 'like', "%{$gb}%");
        }

        $elementos = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $tipos = Tipo::all();

        return view('revision.index', compact('elementos', 'tipos'));
    }
}
