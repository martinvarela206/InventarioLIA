@extends('layouts.app')

@section('content')
<div class="max-w-[700px] mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs text-gray-500 hover:text-black">← Volver al listado</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Editar Usuario: {{ $user->nombre }}</h1>
        </div>
        <a href="{{ route('users.trayectoria', $user->id) }}" class="text-xs font-bold text-amber-800 bg-amber-100 px-3 py-1.5 rounded-lg hover:bg-amber-200">
            Ver Trayectoria
        </a>
    </div>

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nombre" class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre y Apellido *</label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $user->nombre) }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium" required>
            </div>
            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase mb-1">Email Institucional *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="dni" class="block text-xs font-bold text-gray-700 uppercase mb-1">DNI</label>
                <input type="text" name="dni" id="dni" value="{{ old('dni', $user->dni) }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
            </div>
            <div>
                <label for="fecha_nacimiento" class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->fecha_nacimiento ? $user->fecha_nacimiento->toDateString() : '') }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="carrera" class="block text-xs font-bold text-gray-700 uppercase mb-1">Carrera</label>
                <input type="text" name="carrera" id="carrera" value="{{ old('carrera', $user->carrera) }}" placeholder="Ej. Licenciatura en Ciencias de la Computación" class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
            </div>
            <div>
                <label for="cargo_actual" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cargo Actual</label>
                <input type="text" name="cargo_actual" id="cargo_actual" value="{{ old('cargo_actual', $user->cargo_actual) }}" placeholder="Ej. Ayudante de 1ra, Pasante" class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="whatsapp" class="block text-xs font-bold text-gray-700 uppercase mb-1">Teléfono / WhatsApp</label>
                <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="+54 9 264 ..." class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
            </div>
            <div>
                <label for="direccion" class="block text-xs font-bold text-gray-700 uppercase mb-1">Dirección / Domicilio</label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $user->direccion) }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
            </div>
        </div>

        <!-- Estado de Cuenta -->
        <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $user->activo) ? 'checked' : '' }} class="w-4 h-4 text-amber-500 rounded border-gray-300">
                <span class="text-sm font-bold text-gray-800">Cuenta de usuario activa (Habilitar acceso al sistema)</span>
            </label>
        </div>

        <!-- Roles Asignados -->
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Roles Oficiales Asignados</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($roles as $rol)
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 bg-gray-50 cursor-pointer text-xs font-bold hover:bg-gray-100">
                        <input type="checkbox" name="roles[]" value="{{ $rol->id }}" {{ $user->roles->contains($rol->id) ? 'checked' : '' }} class="w-4 h-4 text-amber-500 rounded">
                        <span>{{ $rol->rol }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('users.index') }}" class="px-4 py-2 border rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-amber-400 hover:bg-amber-500 font-bold text-black rounded-xl text-xs shadow-sm transition-all">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
