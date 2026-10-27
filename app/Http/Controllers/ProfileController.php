<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use App\Models\Carrera;
use App\Models\Cargo;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(): View
    {
        if (!auth()->user()->hasRole('registered')) {
            abort(403, 'Requiere el rol registered para gestionar su perfil personal.');
        }

        $carreras = Carrera::activas()->orderBy('nombre')->get();
        $cargos = Cargo::activos()->orderBy('orden')->orderBy('nombre')->get();

        return view('profile.edit', [
            'user' => auth()->user(),
            'carreras' => $carreras,
            'cargos' => $cargos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\Usuario $user */
        $user = auth()->user();

        if (!$user->hasRole('registered')) {
            abort(403, 'Requiere el rol registered para gestionar su perfil personal.');
        }

        $rules = [
            'nombre' => ['required', 'string', 'max:100'],
            'dni' => ['nullable', 'string', 'max:20', Rule::unique('usuarios', 'dni')->ignore($user->id)],
            'carrera_id' => ['nullable', 'exists:carreras,id'],
            'carrera' => ['nullable', 'string', 'max:100'],
            'cargo_id' => ['nullable', 'exists:cargos,id'],
            'cargo_actual' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
        ];

        // Solo el rol user_admin tiene permiso de modificar el correo de ingreso
        if ($user->hasRole('user_admin') && $request->filled('email')) {
            $rules['email'] = ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($user->id)];
        }

        $validated = $request->validate($rules);

        $user->nombre = $validated['nombre'];
        $user->dni = $validated['dni'] ?? null;
        $user->whatsapp = $validated['whatsapp'] ?? null;
        $user->direccion = $validated['direccion'] ?? null;

        if ($request->filled('carrera_id')) {
            $user->carrera_id = $request->carrera_id;
        } elseif ($request->filled('carrera')) {
            $carrera = Carrera::firstOrCreate(['nombre' => $request->carrera]);
            $user->carrera_id = $carrera->id;
        } else {
            $user->carrera_id = null;
        }

        if ($request->filled('cargo_id')) {
            $user->cargo_id = $request->cargo_id;
        } elseif ($request->filled('cargo_actual')) {
            $cargo = Cargo::firstOrCreate(['nombre' => $request->cargo_actual]);
            $user->cargo_id = $cargo->id;
        } else {
            $user->cargo_id = null;
        }

        if ($user->hasRole('user_admin') && isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }
}
