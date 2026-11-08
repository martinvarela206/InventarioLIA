<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Carrera;
use App\Models\Usuario;
use App\Services\ImageSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(): View
    {
        if (! auth()->user()->hasRole('registered')) {
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
        /** @var Usuario $user */
        $user = auth()->user();

        if (! $user->hasRole('registered')) {
            abort(403, 'Requiere el rol registered para gestionar su perfil personal.');
        }

        $rules = [
            'nombre' => ['required', 'string', 'max:100'],
            'dni' => ['nullable', 'string', 'max:20', Rule::unique('usuarios', 'dni')->ignore($user->id)],
            'carrera_id' => ['nullable', 'exists:carreras,id'],
            'carrera' => ['nullable', 'string', 'max:100'],
            'cargo_id' => ['nullable', 'exists:cargos,id'],
            'cargo_actual' => ['nullable', 'string', 'max:100'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'eliminar_foto' => ['nullable'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
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

        // Gestión de Foto de Perfil
        if ($request->boolean('eliminar_foto')) {
            if ($user->foto_path && ! str_starts_with($user->foto_path, '/assets/')) {
                Storage::disk('public')->delete($user->foto_path);
            }
            $user->foto_path = null;
        } elseif ($request->hasFile('foto')) {
            if ($user->foto_path && ! str_starts_with($user->foto_path, '/assets/')) {
                Storage::disk('public')->delete($user->foto_path);
            }
            $user->foto_path = ImageSanitizer::sanitizeAndStore($request->file('foto'), 'avatares', 'public');
        }

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

            // Revocar tokens API
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // Invalidar otras sesiones de base de datos
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->where('id', '!=', session()->getId())
                    ->delete();
            }
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }
}
