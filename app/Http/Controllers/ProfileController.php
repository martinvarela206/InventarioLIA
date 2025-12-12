<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed'], // Confirmation field must be password_confirmation
        ]);

        $user->nombre = $validated['nombre'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        /** @var \App\Models\Usuario $user */
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }
}
