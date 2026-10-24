@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-64px)] flex flex-col justify-between">
    <div class="flex-grow flex items-center justify-center py-12">
        <div class="w-full max-w-md">
            <form action="{{ route('register') }}" method="POST" class="bg-white shadow-md rounded-2xl px-8 pt-6 pb-8 mb-4 border border-gray-200">
                @csrf
                <div class="flex justify-center mb-4">
                    @include('common.logo', ['variant' => 'dark', 'class' => 'w-16 h-16'])
                </div>
                <h2 class="text-2xl font-bold mb-2 text-center text-gray-900">Registro de Usuario</h2>
                <p class="text-xs text-gray-500 text-center mb-6">Acceso institucional a la plataforma Nexo LIA</p>
                
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

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="dni">
                            DNI (Opcional)
                        </label>
                        <input class="w-full border rounded-xl px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400" id="dni" type="text" name="dni" value="{{ old('dni') }}" placeholder="35123456">
                    </div>
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
@endsection
