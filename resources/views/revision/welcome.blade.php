@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex flex-col items-center justify-center text-center px-4">
    <div class="figma-panel max-w-lg p-8 sm:p-10 space-y-4 shadow-2xl">
        <div class="w-12 h-12 rounded-2xl bg-figma-brand text-white flex items-center justify-center font-bold text-lg mx-auto shadow-sm">
            LIA
        </div>
        <div class="space-y-1">
            <h1 class="text-xl font-bold text-figma-text-primary">Bienvenido a Nexo LIA</h1>
            <p class="text-xs text-figma-text-secondary leading-relaxed">
                Has ingresado al sistema institucional. Si requieres acceso a funciones operativas o módulos técnicos de inventario, por favor solicita a los administradores la asignación de tus roles correspondientes.
            </p>
        </div>
        <div class="pt-4 border-t border-figma-border-subtle flex justify-center">
            <a href="{{ route('profile.edit') }}" class="figma-btn-primary">
                Ver Mi Perfil
            </a>
        </div>
    </div>
</div>
@endsection
