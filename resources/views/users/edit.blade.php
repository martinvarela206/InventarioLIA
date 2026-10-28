@extends('layouts.app')

@section('content')
<div class="max-w-[760px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('users.index') }}" class="hover:text-figma-brand transition-colors">Gestión de Usuarios</a>
                <span>/</span>
                <span class="text-figma-text-primary">{{ $user->nombre }}</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Editar Usuario: {{ $user->nombre }}</span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('users.trayectoria', $user->id) }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Ver Trayectoria</span>
            </a>
            <a href="{{ route('users.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="figma-panel p-5 sm:p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            <div>
                <label for="nombre" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nombre y Apellido <span class="text-rose-400">*</span></label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $user->nombre) }}" class="figma-input w-full px-3 py-1.5 text-xs font-semibold" required>
            </div>
            <div>
                <label for="email" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Email Institucional <span class="text-rose-400">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="figma-input w-full px-3 py-1.5 text-xs font-mono" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            <div>
                <label for="dni" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">DNI</label>
                <input type="text" name="dni" id="dni" value="{{ old('dni', $user->dni) }}" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
            </div>
            <div>
                <label for="fecha_nacimiento" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->fecha_nacimiento ? $user->fecha_nacimiento->toDateString() : '') }}" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            <div>
                <label for="carrera_id" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Carrera / Área Académica</label>
                <select name="carrera_id" id="carrera_id" class="figma-input w-full px-3 py-1.5 text-xs">
                    <option value="">-- Seleccionar carrera --</option>
                    @foreach($carreras as $c)
                        <option value="{{ $c->id }}" {{ (string)old('carrera_id', $user->carrera_id) === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->nombre }} {{ $c->codigo ? "({$c->codigo})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="cargo_id" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Cargo Institucional Oficial</label>
                <select name="cargo_id" id="cargo_id" class="figma-input w-full px-3 py-1.5 text-xs">
                    <option value="">-- Seleccionar cargo --</option>
                    @foreach($cargos as $cargo)
                        <option value="{{ $cargo->id }}" {{ (string)old('cargo_id', $user->cargo_id) === (string)$cargo->id ? 'selected' : '' }}>
                            {{ $cargo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            <div>
                <label for="whatsapp" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Teléfono / WhatsApp</label>
                <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="+54 9 264 ..." class="figma-input w-full px-3 py-1.5 text-xs font-mono">
            </div>
            <div>
                <label for="direccion" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Dirección / Domicilio</label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $user->direccion) }}" class="figma-input w-full px-3 py-1.5 text-xs">
            </div>
        </div>

        <!-- Estado de Cuenta -->
        <div class="figma-card p-3 border-figma-border-subtle">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $user->activo) ? 'checked' : '' }} class="rounded text-figma-brand focus:ring-figma-brand">
                <span class="text-xs font-bold text-figma-text-primary">Cuenta activa (Habilitar inicio de sesión y acceso)</span>
            </label>
        </div>

        <!-- Roles Asignados -->
        <div class="space-y-2">
            <label class="block text-[11px] font-bold text-figma-text-secondary uppercase">Roles Oficiales Asignados</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($roles as $rol)
                    <label class="flex items-center gap-2 p-2 rounded bg-figma-bg-deep border border-figma-border-subtle hover:border-figma-brand cursor-pointer text-xs transition-colors">
                        <input type="checkbox" name="roles[]" value="{{ $rol->id }}" {{ $user->roles->contains($rol->id) ? 'checked' : '' }} class="rounded text-figma-brand focus:ring-figma-brand">
                        <span class="font-medium text-figma-text-primary">{{ $rol->rol }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pt-3 border-t border-figma-border-subtle flex items-center justify-end gap-2">
            <a href="{{ route('users.index') }}" class="figma-btn-secondary text-xs">
                Cancelar
            </a>
            <button type="submit" class="figma-btn-primary text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>
@endsection
