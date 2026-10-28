@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-64px)] flex flex-col justify-between">
    <div class="flex-grow flex items-center justify-center py-10 px-4">
        <div class="w-full max-w-sm">
            <form action="{{ route('login') }}" method="POST" class="figma-panel p-6 sm:p-7 space-y-4 shadow-2xl">
                @csrf
                <div class="flex justify-center mb-2">
                    <span class="w-10 h-10 rounded-xl bg-figma-brand text-black flex items-center justify-center font-extrabold text-sm shadow-sm">
                        LIA
                    </span>
                </div>
                <div class="text-center space-y-0.5">
                    <h2 class="text-lg font-bold text-figma-text-primary">Iniciar Sesión</h2>
                    <p class="text-xs text-figma-text-tertiary">Centro de Control • Nexo LIA</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="login">
                        Usuario o Correo Institucional
                    </label>
                    <input class="figma-input w-full px-3 py-2 text-xs font-medium @error('login') border-rose-500 @enderror @error('nombre') border-rose-500 @enderror" id="login" type="text" name="login" value="{{ old('login', old('nombre')) }}" required autofocus placeholder="usuario@unsj.edu.ar">
                    @error('login')
                        <p class="text-rose-400 text-[11px] mt-0.5">{{ $message }}</p>
                    @enderror
                    @error('nombre')
                        <p class="text-rose-400 text-[11px] mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-figma-text-secondary text-[11px] font-bold uppercase tracking-wider" for="password">
                        Contraseña
                    </label>
                    <input class="figma-input w-full px-3 py-2 text-xs font-medium @error('password') border-rose-500 @enderror" id="password" type="password" name="password" required placeholder="••••••••">
                    @error('password')
                        <p class="text-rose-400 text-[11px] mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button class="figma-btn-primary w-full justify-center py-2.5 text-xs font-semibold" type="submit">
                        Ingresar a Nexo LIA
                    </button>
                </div>

                <div class="text-center pt-2 border-t border-figma-border-subtle">
                    <a class="text-xs font-semibold text-figma-brand hover:underline" href="{{ route('register') }}">
                        ¿No tienes cuenta? Regístrate aquí
                    </a>
                </div>
            </form>
        </div>
    </div>
    @include('common.footer')
</div>
@endsection
