@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-64px)] flex flex-col justify-between">
    <div class="flex-grow flex items-center justify-center py-10 px-4">
        <div class="w-full max-w-lg">
            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="figma-panel p-6 sm:p-7 space-y-4 shadow-2xl">
                @csrf
                <div class="flex justify-center mb-1">
                    <span class="w-10 h-10 rounded-xl bg-figma-brand text-black flex items-center justify-center font-extrabold text-sm shadow-sm">
                        LIA
                    </span>
                </div>
                <div class="text-center space-y-0.5">
                    <h2 class="text-lg font-bold text-figma-text-primary">Registro de Usuario</h2>
                    <p class="text-xs text-figma-text-tertiary">Acceso institucional a la plataforma Nexo LIA</p>
                </div>
                
                <div class="space-y-1">
                    <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="nombre">
                        Nombre y Apellido *
                    </label>
                    <input class="figma-input w-full px-3 py-2 text-xs font-medium @error('nombre') border-rose-500 @enderror" id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" required autofocus placeholder="Ej. Juan Pérez">
                    @error('nombre')
                        <p class="text-rose-400 text-[11px] mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="email">
                        Correo Institucional / Personal *
                    </label>
                    <input class="figma-input w-full px-3 py-2 text-xs font-medium @error('email') border-rose-500 @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="usuario@exactas.unsj.edu.ar">
                    @error('email')
                        <p class="text-rose-400 text-[11px] mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="dni">
                        DNI (Opcional)
                    </label>
                    <input class="figma-input w-full px-3 py-2 text-xs font-mono font-medium" id="dni" type="text" name="dni" value="{{ old('dni') }}" placeholder="35123456">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="carrera_id">
                            Carrera Universitaria
                        </label>
                        <select class="figma-input w-full px-3 py-2 text-xs font-medium" id="carrera_id" name="carrera_id">
                            <option value="">Selecciona tu carrera...</option>
                            @foreach($carreras as $c)
                                <option value="{{ $c->id }}" {{ old('carrera_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nombre }} {{ $c->codigo ? "({$c->codigo})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="cargo_id">
                            Cargo en el Laboratorio
                        </label>
                        <select class="figma-input w-full px-3 py-2 text-xs font-medium" id="cargo_id" name="cargo_id">
                            <option value="">Selecciona tu cargo (opcional)...</option>
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo->id }}" {{ old('cargo_id') == $cargo->id ? 'selected' : '' }}>
                                    {{ $cargo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Foto de Perfil Opcional -->
                <div class="figma-card p-3 flex items-center gap-3">
                    <img id="register-avatar-preview" src="{{ asset('assets/placeholder-user.png') }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-figma-border-subtle bg-figma-bg-deep shrink-0">
                    <div class="flex-1">
                        <label class="block text-figma-text-primary text-[11px] font-bold uppercase mb-0.5">
                            Fotografía de Perfil (Opcional)
                        </label>
                        <p class="text-[10px] text-figma-text-tertiary mb-1">Aparecerá en el portal si integras el personal.</p>
                        <label class="cursor-pointer inline-flex items-center gap-1 px-2 py-0.5 figma-btn-secondary text-[11px]">
                            <span>Subir foto</span>
                            <input type="file" name="foto" id="foto" accept="image/*" class="hidden" onchange="previewRegisterAvatar(event)">
                        </label>
                        @error('foto')
                            <p class="text-rose-400 text-[10px] mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="password">
                            Contraseña *
                        </label>
                        <input class="figma-input w-full px-3 py-2 text-xs font-medium @error('password') border-rose-500 @enderror" id="password" type="password" name="password" required placeholder="Mínimo 4 caracteres">
                        @error('password')
                            <p class="text-rose-400 text-[10px] mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="password_confirmation">
                            Confirmar Contraseña *
                        </label>
                        <input class="figma-input w-full px-3 py-2 text-xs font-medium" id="password_confirmation" type="password" name="password_confirmation" required placeholder="Repita la contraseña">
                    </div>
                </div>

                <!-- Términos Consentimiento -->
                <div class="figma-card p-3 text-left">
                    <label class="flex items-start gap-2.5 cursor-pointer text-xs text-figma-text-secondary select-none" for="terminos_consentimiento">
                        <input type="checkbox" id="terminos_consentimiento" name="terminos_consentimiento" value="1" required {{ old('terminos_consentimiento') ? 'checked' : '' }} class="mt-0.5 rounded text-figma-brand focus:ring-figma-brand">
                        <span class="text-[11px] leading-relaxed">
                            Acepto el <strong class="text-figma-text-primary font-semibold">Contrato de Uso y Tratamiento de Datos (Ley 25.326)</strong>. Comprendo y consiento que mis datos identificatorios y registros de auditoría operativa se conservarán por un período legal mínimo de <strong class="text-figma-brand font-bold">6 años</strong> para garantizar la trazabilidad institucional.
                        </span>
                    </label>
                    @error('terminos_consentimiento')
                        <p class="text-rose-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button class="figma-btn-primary w-full justify-center py-2.5 text-xs font-semibold" type="submit">
                        Registrarse en Nexo LIA
                    </button>
                </div>
                
                <div class="text-center pt-2 border-t border-figma-border-subtle">
                    <a class="text-xs font-semibold text-figma-brand hover:underline" href="{{ route('login') }}">
                        ¿Ya tienes cuenta? Inicia sesión aquí
                    </a>
                </div>
            </form>
        </div>
    </div>
    @include('common.footer')
</div>

<script>
    function previewRegisterAvatar(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('register-avatar-preview');
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
