@extends('layouts.app')

@section('content')
<div class="max-w-[700px] mx-auto py-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Usuario: {{ $user->nombre }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Gestión de datos de contacto, rol y habilitación de acceso</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('users.trayectoria', $user->id) }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Ver Trayectoria</span>
            </a>
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
            <span class="text-xs font-semibold text-gray-500">
                Los campos marcados con <span class="text-red-500 font-bold text-sm">*</span> son obligatorios
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nombre" class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre y Apellido <span class="text-red-500 font-bold">*</span></label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $user->nombre) }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium" required>
            </div>
            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase mb-1">Email Institucional <span class="text-red-500 font-bold">*</span></label>
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
                <label for="carrera" class="block text-xs font-bold text-gray-700 uppercase mb-1">Carrera / Área Académica</label>
                <input type="text" name="carrera" id="carrera" list="lista-carreras" value="{{ old('carrera', $user->carrera) }}" placeholder="Seleccione o escriba su carrera..." class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <datalist id="lista-carreras">
                    @foreach($carreras as $c)
                        <option value="{{ $c->nombre }}">{{ $c->codigo ? "({$c->codigo})" : '' }}</option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label for="cargo_actual" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cargo Institucional Oficial</label>
                <input type="text" name="cargo_actual" id="cargo_actual" list="lista-cargos" value="{{ old('cargo_actual', $user->cargo_actual) }}" placeholder="Seleccione o escriba su cargo..." class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <datalist id="lista-cargos">
                    @foreach($cargos as $cargo)
                        <option value="{{ $cargo }}"></option>
                    @endforeach
                </datalist>
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

        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('users.index') }}" class="px-4 py-2.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold rounded-xl text-sm shadow-sm transition-all inline-flex items-center gap-1.5">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2.5 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-sm shadow-sm transition-all inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>
@endsection
