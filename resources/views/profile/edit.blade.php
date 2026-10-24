@extends('layouts.app')

@section('content')
<div class="max-w-[750px] mx-auto py-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Mi Perfil de Usuario</span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">Gestión de datos personales y seguridad de la cuenta institucional</p>
        </div>
        <a href="{{ route('dashboard.index') }}" class="text-xs font-semibold text-gray-600 hover:text-black">← Volver al Dashboard</a>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-2 text-sm font-semibold shadow-sm" role="alert">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Tu perfil y datos de acceso han sido actualizados correctamente.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 lg:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Identidad y Credenciales de Acceso -->
        <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200/80 space-y-4">
            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Identidad y Credenciales Institucionales</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre y Apellido <span class="text-red-500 font-bold">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $user->nombre) }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none" required>
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="username" class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre de Usuario (Identificador)</label>
                    <input type="text" id="username" value="{{ $user->username ?? explode('@', $user->email)[0] }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed" disabled>
                </div>
            </div>

            <!-- Email Institucional con Bloqueo y Regla de Negocio -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase">
                        Correo Electrónico Institucional (Ingreso)
                    </label>
                    @if($user->hasRole('user_admin'))
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                            Permiso user_admin activo
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Modificación restringida
                        </span>
                    @endif
                </div>

                @if($user->hasRole('user_admin'))
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none" required>
                    <p class="text-[11px] text-gray-500 mt-1">Como administrador de usuarios, puedes modificar tu propia dirección de correo.</p>
                @else
                    <input type="email" id="email" value="{{ $user->email }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-gray-100 text-gray-500 font-medium cursor-not-allowed" readonly disabled>
                    <p class="text-[11px] text-amber-700 mt-1 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        El correo institucional no puede ser editado directamente. Para solicitar un cambio de casilla, contacte a un Administrador de Usuarios (<code>user_admin</code>).
                    </p>
                @endif
                @error('email')
                    <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Datos Personales y Académicos -->
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Datos Personales y de Contacto</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="dni" class="block text-xs font-bold text-gray-700 uppercase mb-1">DNI</label>
                    <input type="text" name="dni" id="dni" value="{{ old('dni', $user->dni) }}" placeholder="Documento de identidad" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                    @error('dni')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="whatsapp" class="block text-xs font-bold text-gray-700 uppercase mb-1">Teléfono / WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="+54 9 264 ..." class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="carrera" class="block text-xs font-bold text-gray-700 uppercase mb-1">Carrera / Área Académica</label>
                    <input type="text" name="carrera" id="carrera" value="{{ old('carrera', $user->carrera) }}" placeholder="Ej. LCC, Programador Web" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div>
                    <label for="cargo_actual" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cargo en el Laboratorio</label>
                    <input type="text" name="cargo_actual" id="cargo_actual" value="{{ old('cargo_actual', $user->cargo_actual) }}" placeholder="Ej. Ayudante de 2da, Pasante" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
            </div>

            <div class="mt-4">
                <label for="direccion" class="block text-xs font-bold text-gray-700 uppercase mb-1">Dirección / Domicilio</label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $user->direccion) }}" placeholder="Calle, número, localidad" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
            </div>
        </div>

        <!-- Cambio de Contraseña -->
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Seguridad y Cambio de Contraseña</h3>
            <p class="text-xs text-gray-500 mb-3">Deje estos campos en blanco si no desea modificar su clave actual.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Mínimo 4 caracteres" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" placeholder="Reingrese la misma clave" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-400">Sistema Nexo LIA • Gestión de Cuentas</span>
            <button type="submit" class="px-6 py-2.5 bg-amber-400 hover:bg-amber-500 text-black font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar Cambios de Perfil
            </button>
        </div>
    </form>
</div>
@endsection
