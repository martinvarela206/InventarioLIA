@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-64px)] flex flex-col justify-between">
    <div class="flex-grow flex items-center justify-center py-12">
        <div class="w-full max-w-md">
            <form action="{{ route('register') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                @csrf
                <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Registro de Usuario</h2>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">
                        Usuario
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nombre') border-red-500 @enderror" id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" required autofocus placeholder="Nombre de usuario">
                    @error('nombre')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Contraseña
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" id="password" type="password" name="password" required placeholder="******************">
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirmation">
                        Confirmar Contraseña
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password_confirmation" type="password" name="password_confirmation" required placeholder="******************">
                </div>

                <div class="flex items-center justify-between mb-4">
                    <button class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition-colors duration-200" type="submit">
                        Registrarse
                    </button>
                </div>
                
                <div class="text-center">
                    <a class="inline-block align-baseline font-bold text-sm text-[#dba800] hover:text-[#fbc101] transition-colors duration-200" href="{{ route('login') }}">
                        ¿Ya tienes cuenta? Inicia sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
    @include('common.footer')
</div>
@endsection
