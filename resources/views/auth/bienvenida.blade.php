@extends('layouts.app')

@section('content')
<div class="max-w-[760px] mx-auto py-8 px-4">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Banner superior con identidad institucional LIA -->
        <div style="background:#fbc101;" class="p-8 text-center relative overflow-hidden">
            <div class="inline-flex p-3 bg-white/30 backdrop-blur-xs rounded-2xl mb-3 shadow-2xs">
                @include('common.logo', ['variant' => 'dark', 'class' => 'w-14 h-14'])
            </div>
            <h1 class="text-3xl font-black text-gray-950 tracking-tight">¡Bienvenido a Nexo LIA!</h1>
            <p class="text-sm font-semibold text-gray-800 mt-1">Plataforma de Control y Gestión Patrimonial del Laboratorio de Informática Aplicada</p>
        </div>

        <!-- Contenido principal -->
        <div class="p-8 space-y-6">
            <!-- Mensaje de estado de la cuenta -->
            <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="p-2.5 bg-amber-400 text-black rounded-xl shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Cuenta Registrada: Esperando Asignación de Permisos</h2>
                    <p class="text-xs text-gray-700 mt-1 leading-relaxed">
                        Hola <strong>{{ Auth::user()->nombre }}</strong>, tu cuenta ha sido creada y verificada exitosamente en el sistema. 
                        Actualmente te encuentras con el rol de <span class="bg-amber-200/90 text-amber-950 px-2 py-0.5 rounded font-mono font-bold">registered</span>. 
                        Para acceder a los módulos de inventario, auditoría o gestión operativa, un administrador del laboratorio debe asignarte los permisos correspondientes a tu rol (revisor, técnico o administrador).
                    </p>
                </div>
            </div>

            <!-- Resumen de datos del usuario -->
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Información de tu Usuario</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-500 block">Nombre Completo:</span>
                        <span class="font-bold text-gray-900 text-sm block mt-0.5">{{ Auth::user()->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Correo Electrónico:</span>
                        <span class="font-bold text-gray-900 text-sm block mt-0.5">{{ Auth::user()->email }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">DNI / Documento:</span>
                        <span class="font-medium text-gray-800 block mt-0.5">{{ Auth::user()->dni ?: 'No cargado' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Carrera / Área:</span>
                        <span class="font-medium text-gray-800 block mt-0.5">{{ Auth::user()->carrera ?: 'No especificada' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Fecha de Registro:</span>
                        <span class="font-medium text-gray-800 block mt-0.5">{{ Auth::user()->created_at ? Auth::user()->created_at->format('d/m/Y H:i') : date('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Estado del Acceso:</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Pendiente de Autorización
                        </span>
                    </div>
                </div>
            </div>

            <!-- Acciones permitidas -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('profile.edit') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-100 text-gray-800 font-bold text-xs shadow-2xs transition-all">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Completar / Editar mi Perfil
                </a>

                <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
