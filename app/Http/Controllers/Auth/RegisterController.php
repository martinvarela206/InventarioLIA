<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $carreras = Carrera::activas()->get();
        return view('auth.register', compact('carreras'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios'],
            'dni' => ['nullable', 'string', 'max:15', 'unique:usuarios'],
            'carrera_id' => ['nullable', 'exists:carreras,id'],
            'carrera' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'terminos_consentimiento' => ['accepted'],
        ], [
            'terminos_consentimiento.accepted' => 'Debe aceptar los términos de uso y tratamiento de datos personales conforme a la Ley 25.326.',
        ]);

        $carreraId = $request->input('carrera_id');
        $carreraNombre = null;
        if ($carreraId) {
            $carreraObj = Carrera::find($carreraId);
            $carreraNombre = $carreraObj?->nombre;
        } elseif ($request->filled('carrera')) {
            $carreraObj = Carrera::where('nombre', $request->input('carrera'))->first();
            $carreraId = $carreraObj?->id;
            $carreraNombre = $request->input('carrera');
        }

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'dni' => $request->dni,
            'carrera' => $carreraNombre,
            'carrera_id' => $carreraId,
            'password' => Hash::make($request->password),
            'activo' => false, // Requiere aprobación de user_admin
        ]);

        // Asignar rol inicial 'registered'
        $rolRegistered = Rol::where('rol', 'registered')->first();
        if ($rolRegistered) {
            $user->roles()->attach($rolRegistered->id);
        }

        Auth::login($user);

        return redirect(route('revision.index'))
            ->with('success', 'Registro completado. Tu cuenta se encuentra pendiente de activación por el administrador.');
    }
}
