<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios'],
            'dni' => ['nullable', 'string', 'max:15', 'unique:usuarios'],
            'carrera' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'dni' => $request->dni,
            'carrera' => $request->carrera,
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
