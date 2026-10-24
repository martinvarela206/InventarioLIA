@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-64px)] flex flex-col justify-between">
    <div class="flex-grow flex items-center justify-center py-12">
        <div class="w-full max-w-md">
            <form action="{{ route('login') }}" method="POST" class="bg-white shadow-md rounded-2xl px-8 pt-6 pb-8 mb-4 border border-gray-200">
                @csrf
                <div class="flex justify-center mb-4">
                    @include('common.logo', ['variant' => 'dark', 'class' => 'w-16 h-16'])
                </div>
                <h2 class="text-2xl font-bold mb-1 text-center text-gray-900">Iniciar Sesión</h2>
                <p class="text-xs text-gray-500 text-center mb-6">Accede al Centro de Control de Nexo LIA</p>

                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="login">
                        Usuario o Correo Institucional
                    </label>
                    <input class="w-full border rounded-xl px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium @error('login') border-red-500 @enderror @error('nombre') border-red-500 @enderror" id="login" type="text" name="login" value="{{ old('login', old('nombre')) }}" required autofocus placeholder="admin@lia.unsj.edu.ar">
                    @error('login')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="password">
                        Contraseña
                    </label>
                    <input class="w-full border rounded-xl px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium @error('password') border-red-500 @enderror" id="password" type="password" name="password" required placeholder="••••••••">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <button class="bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow w-full transition-colors text-sm" type="submit">
                        Ingresar a Nexo LIA
                    </button>
                </div>

                <div class="text-center">
                    <a class="text-xs font-bold text-amber-700 hover:underline" href="{{ route('register') }}">
                        ¿No tienes cuenta institucional? Regístrate aquí
                    </a>
                </div>
            </form>
        </div>
    </div>
    @include('common.footer')
</div>
@endsection
