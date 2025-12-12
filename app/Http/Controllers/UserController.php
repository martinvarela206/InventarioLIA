<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = Usuario::with('roles')->get();
        $roles = ['user_admin', 'coordinador', 'tecnico', 'revisor'];
        return view('users.index', compact('users', 'roles'));
    }

    public function edit(Usuario $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, Usuario $user)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            // Add other fields validation as necessary
        ]);
        
        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleRole(Request $request, Usuario $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,rol'
        ]);

        $roleName = $request->role;
        $role = Rol::where('rol', $roleName)->firstOrFail();

        if ($user->hasRole($roleName)) {
            $user->roles()->detach($role->id);
            $message = 'Rol removido correctamente.';
            $status = 'removed';
        } else {
            $user->roles()->attach($role->id);
            $message = 'Rol asignado correctamente.';
            $status = 'assigned';
        }

        return response()->json(['message' => $message, 'status' => $status]);
    }
}
