@extends('layouts.app')

@section('content')
<div class="max-w-[800px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Cuenta</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Propiedades del Usuario</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('dashboard.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Dashboard</span>
            </a>
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 flex items-center gap-2 text-xs font-semibold" role="alert">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span>Tus datos de perfil y credenciales han sido actualizados con éxito.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="figma-panel p-5 sm:p-6 space-y-5">
        @csrf
        @method('PUT')

        <!-- Fotografía de Perfil Institucional -->
        <div class="figma-card p-4 flex flex-col sm:flex-row items-center gap-4">
            <div class="relative group shrink-0">
                <img id="avatar-preview" src="{{ $user->foto_url }}" alt="{{ $user->nombre }}" class="w-16 h-16 rounded-full object-cover border border-figma-border-strong bg-figma-bg-deep">
            </div>
            <div class="flex-1 text-center sm:text-left space-y-1">
                <label class="block text-xs font-bold text-figma-text-primary uppercase tracking-wider">
                    Avatar Institucional
                </label>
                <p class="text-[11px] text-figma-text-tertiary">
                    Formatos soportados: JPG, PNG, WEBP (hasta 2 MB). Visible en la barra de herramientas.
                </p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                    <label class="cursor-pointer figma-btn-secondary text-xs">
                        <svg class="w-3.5 h-3.5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Subir Foto</span>
                        <input type="file" name="foto" id="foto" accept="image/*" class="hidden" onchange="previewAvatar(event)">
                    </label>
                    @if($user->foto_path)
                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 rounded-lg text-xs font-semibold text-rose-400 cursor-pointer transition-all">
                            <input type="checkbox" name="eliminar_foto" value="1" class="rounded border-rose-500 text-rose-500 focus:ring-rose-500 w-3 h-3">
                            <span>Quitar foto</span>
                        </label>
                    @endif
                </div>
                @error('foto')
                    <p class="text-rose-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Identidad y Credenciales de Acceso -->
        <div class="figma-card p-4 space-y-3.5">
            <h3 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Identidad y Credenciales</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div>
                    <label for="nombre" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nombre Completo <span class="text-rose-400 font-bold">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $user->nombre) }}" class="figma-input w-full px-3 py-1.5 text-xs font-medium" required>
                    @error('nombre')
                        <p class="text-rose-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="username" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Usuario (Identificador)</label>
                    <input type="text" id="username" value="{{ $user->username ?? explode('@', $user->email)[0] }}" class="figma-input w-full px-3 py-1.5 text-xs font-mono font-bold opacity-60 cursor-not-allowed" disabled>
                </div>
            </div>

            <!-- Email Institucional -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="email" class="block text-[11px] font-bold text-figma-text-secondary uppercase">
                        Correo Electrónico Institucional
                    </label>
                    @if($user->hasRole('user_admin'))
                        <span class="font-mono text-[10px] font-bold text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 px-1.5 py-0.2 rounded">
                            user_admin
                        </span>
                    @else
                        <span class="font-mono text-[10px] font-semibold text-amber-400 bg-amber-500/15 border border-amber-500/30 px-1.5 py-0.2 rounded flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Restringido
                        </span>
                    @endif
                </div>

                @if($user->hasRole('user_admin'))
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="figma-input w-full px-3 py-1.5 text-xs font-medium" required>
                    <p class="text-[10px] text-figma-text-tertiary mt-1">Como administrador de usuarios tienes permiso para modificar tu casilla institucional.</p>
                @else
                    <input type="email" id="email" value="{{ $user->email }}" class="figma-input w-full px-3 py-1.5 text-xs opacity-60 cursor-not-allowed" readonly disabled>
                    <p class="text-[10px] text-figma-text-tertiary mt-1">
                        Para cambiar tu correo de ingreso, solicita la modificación a un administrador de cuentas.
                    </p>
                @endif
                @error('email')
                    <p class="text-rose-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Datos Personales y Académicos -->
        <div class="figma-card p-4 space-y-3">
            <h3 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Datos de Contacto y Rol Académico</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div>
                    <label for="dni" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">DNI</label>
                    <input type="text" name="dni" id="dni" value="{{ old('dni', $user->dni) }}" placeholder="Documento de identidad" class="figma-input w-full px-3 py-1.5 text-xs font-mono font-medium">
                    @error('dni')
                        <p class="text-rose-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="whatsapp" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Teléfono / WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="+54 9 264 ..." class="figma-input w-full px-3 py-1.5 text-xs font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div>
                    <label for="carrera_id" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Carrera / Área Académica</label>
                    <select name="carrera_id" id="carrera_id" class="figma-input w-full px-3 py-1.5 text-xs font-medium cursor-pointer">
                        <option value="">Seleccione su carrera...</option>
                        @foreach($carreras as $c)
                            <option value="{{ $c->id }}" {{ old('carrera_id', $user->carrera_id ?? ($carreras->firstWhere('nombre', $user->carrera)?->id)) == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }} {{ $c->codigo ? "({$c->codigo})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="cargo_id" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Cargo en el Laboratorio</label>
                    <select name="cargo_id" id="cargo_id" class="figma-input w-full px-3 py-1.5 text-xs font-medium cursor-pointer">
                        <option value="">Seleccione su cargo...</option>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->id }}" {{ old('cargo_id', $user->cargo_id ?? ($cargos->firstWhere('nombre', $user->cargo_actual)?->id)) == $cargo->id ? 'selected' : '' }}>
                                {{ $cargo->nombre }}
                            </option>
                        @endforeach
                        @if($user->cargo_actual && !$cargos->contains('nombre', $user->cargo_actual))
                            <option value="" selected disabled>{{ $user->cargo_actual }} (Histórico)</option>
                        @endif
                    </select>
                </div>
            </div>

            <div>
                <label for="direccion" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Dirección / Domicilio</label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $user->direccion) }}" placeholder="Calle, número, localidad" class="figma-input w-full px-3 py-1.5 text-xs font-medium">
            </div>
        </div>

        <!-- Cambio de Contraseña -->
        <div class="figma-card p-4 space-y-3">
            <h3 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Seguridad y Credenciales</h3>
            <p class="text-[11px] text-figma-text-tertiary">Deje estos campos en blanco si no desea modificar su clave actual.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div>
                    <label for="password" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Mínimo 4 caracteres" class="figma-input w-full px-3 py-1.5 text-xs font-medium">
                    @error('password')
                        <p class="text-rose-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" placeholder="Reingrese la clave" class="figma-input w-full px-3 py-1.5 text-xs font-medium">
                </div>
            </div>
        </div>

        <div class="pt-3 border-t border-figma-border-subtle flex items-center justify-between">
            <span class="font-mono text-[10px] text-figma-text-tertiary">v1.6.20 • UNSJ LIA Studio</span>
            <button type="submit" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>

<script>
    function previewAvatar(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
