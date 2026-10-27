<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Carrera;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $carreras = Carrera::activas()->orderBy('nombre')->get();
        $cargos = Cargo::activos()->orderBy('orden')->orderBy('nombre')->get();
        return view('auth.register', compact('carreras', 'cargos'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios'],
            'dni' => ['nullable', 'string', 'max:15', 'unique:usuarios'],
            'carrera_id' => ['nullable', 'exists:carreras,id'],
            'cargo_id' => ['nullable', 'exists:cargos,id'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terminos_consentimiento' => ['accepted'],
        ], [
            'terminos_consentimiento.accepted' => 'Debe aceptar los términos de uso y tratamiento de datos personales conforme a la Ley 25.326.',
        ]);

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'dni' => $request->dni,
            'carrera_id' => $request->carrera_id,
            'cargo_id' => $request->cargo_id,
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
