@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-64px)] flex flex-col justify-between overflow-x-hidden w-full">
    <div class="flex-grow flex items-center justify-center py-8 sm:py-12 px-4 w-full">
        <div class="w-full max-w-md">
            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm rounded-2xl px-5 sm:px-8 pt-6 pb-8 mb-4 border border-slate-200">
                @csrf
                <div class="flex justify-center mb-4">
                    @include('common.logo', ['variant' => 'dark', 'class' => 'w-16 h-16'])
                </div>
                <h2 class="text-2xl font-bold mb-1 text-center text-slate-900">Registro de Usuario</h2>
                <p class="text-xs text-slate-500 text-center mb-6">Acceso institucional a la plataforma Nexo LIA</p>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="nombre">
                        Nombre y Apellido *
                    </label>
                    <input class="w-full border rounded-xl px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 @error('nombre') border-red-500 @enderror" id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" required autofocus placeholder="Ej. Juan Pérez">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="email">
                        Correo Institucional / Personal *
                    </label>
                    <input class="w-full border rounded-xl px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 @error('email') border-red-500 @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="usuario@exactas.unsj.edu.ar">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="dni">
                        DNI (Opcional)
                    </label>
                    <input class="w-full border rounded-xl px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400" id="dni" type="text" name="dni" value="{{ old('dni') }}" placeholder="35123456">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="carrera_id">
                            Carrera Universitaria
                        </label>
                        <select class="w-full border rounded-xl px-3 py-2 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium" id="carrera_id" name="carrera_id">
                            <option value="">Selecciona tu carrera...</option>
                            @foreach($carreras as $c)
                                <option value="{{ $c->id }}" {{ old('carrera_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nombre }} {{ $c->codigo ? "({$c->codigo})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="cargo_id">
                            Cargo en el Laboratorio
                        </label>
                        <select class="w-full border rounded-xl px-3 py-2 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium" id="cargo_id" name="cargo_id">
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
                <div class="mb-4 bg-amber-50/50 p-3.5 rounded-xl border border-amber-200/70 flex items-center gap-3.5">
                    <img id="register-avatar-preview" src="{{ asset('assets/placeholder-user.png') }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-amber-300 bg-white shadow-sm shrink-0">
                    <div class="flex-1">
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-0.5">
                            Fotografía de Perfil (Opcional)
                        </label>
                        <p class="text-[11px] text-gray-500 mb-1.5">Aparecerá en el portal si integras el personal.</p>
                        <label class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 shadow-sm transition-all">
                            <span>Subir foto</span>
                            <input type="file" name="foto" id="foto" accept="image/*" class="hidden" onchange="previewRegisterAvatar(event)">
                        </label>
                        @error('foto')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="password">
                        Contraseña *
                    </label>
                    <input class="w-full border rounded-xl px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 @error('password') border-red-500 @enderror" id="password" type="password" name="password" required placeholder="Mínimo 4 caracteres">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="password_confirmation">
                        Confirmar Contraseña *
                    </label>
                    <input class="w-full border rounded-xl px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400" id="password_confirmation" type="password" name="password_confirmation" required placeholder="Repita la contraseña">
                <div class="mb-5">
                    <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/90 text-left">
                        <label class="flex items-start gap-2.5 cursor-pointer text-xs text-gray-700 select-none" for="terminos_consentimiento">
                            <input type="checkbox" id="terminos_consentimiento" name="terminos_consentimiento" value="1" required {{ old('terminos_consentimiento') ? 'checked' : '' }} class="mt-0.5 rounded text-amber-500 focus:ring-amber-400 border-gray-300">
                            <span class="leading-relaxed">
                                Acepto el <strong class="text-gray-900 font-semibold">Contrato de Uso y Tratamiento de Datos (Ley 25.326)</strong>. Comprendo y consiento que mis datos identificatorios y registros de auditoría operativa se conservarán por un período legal mínimo de <strong class="text-amber-800 font-bold">6 años</strong> para garantizar la trazabilidad institucional patrimonial.
                            </span>
                        </label>
                        @error('terminos_consentimiento')
                            <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <button class="bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow w-full transition-colors text-sm" type="submit">
                        Registrarse
                    </button>
                </div>
                
                <div class="text-center">
                    <a class="text-xs font-bold text-amber-700 hover:underline" href="{{ route('login') }}">
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
