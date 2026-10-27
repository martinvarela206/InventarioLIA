@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Avisos de Seguridad</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span class="p-2 bg-amber-100 text-amber-800 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <span>Concientización y Buenas Prácticas Informáticas</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $avisos->count() }} avisos
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Administra los slides dinámicos y consejos de ciberseguridad que se proyectan en el carrusel flotante de la landing page y en la pantalla de bienvenida.
            </p>
        </div>
        <div>
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Ver Carrusel en Landing Page</span>
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario Alta de Aviso -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-20">
                <h2 class="font-bold text-gray-900 text-base mb-1">Nuevo Slide de Seguridad</h2>
                <p class="text-xs text-gray-500 mb-4">Añadir una pauta o advertencia de seguridad para la comunidad.</p>

                <form action="{{ route('app_admin.avisos.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Título del Aviso *</label>
                        <input type="text" name="titulo" required placeholder="Ej. Bloqueo de Puesto al Ausentarse" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Descripción / Recomendación *</label>
                        <textarea name="descripcion" rows="3" required placeholder="Explica en forma clara y concisa la buena práctica o recomendación de seguridad..." class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Severidad *</label>
                            <select name="severidad" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                                <option value="tip">Consejo (Verde)</option>
                                <option value="info">Información (Azul)</option>
                                <option value="warning" selected>Advertencia (Ámbar)</option>
                                <option value="danger">Crítico (Rojo)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Ícono</label>
                            <select name="icono" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                                <option value="shield">🛡️ Escudo</option>
                                <option value="lock">🔒 Candado / Bloqueo</option>
                                <option value="key">🔑 Clave / Credencial</option>
                                <option value="usb">💾 Dispositivo USB</option>
                                <option value="alert">⚠️ Alerta</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Orden</label>
                            <input type="number" name="orden" value="1" min="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                                <input type="checkbox" name="activo" value="1" checked class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                <span>Activo en carrusel</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Guardar Aviso de Seguridad</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Avisos -->
        <div class="lg:col-span-2 space-y-4">
            @if($avisos->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500 text-sm">
                    No se han registrado avisos de seguridad todavía. Agrega el primero usando el formulario.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($avisos as $aviso)
                        @php
                            $badgeColors = [
                                'tip' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'info' => 'bg-sky-100 text-sky-800 border-sky-200',
                                'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'danger' => 'bg-rose-100 text-rose-800 border-rose-200',
                            ];
                            $badgeClass = $badgeColors[$aviso->severidad] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                        @endphp
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 hover:shadow transition-shadow">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $badgeClass }}">
                                            {{ $aviso->severidad }}
                                        </span>
                                        <span class="text-xs font-mono text-gray-400">Orden: #{{ $aviso->orden }}</span>
                                        @if($aviso->activo)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Visible
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Oculto
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-black text-gray-900">{{ $aviso->titulo }}</h3>
                                    <p class="text-xs text-gray-600 leading-relaxed">{{ $aviso->descripcion }}</p>
                                </div>

                                <div class="flex items-center gap-2 self-end sm:self-start">
                                    <!-- Toggle Activo -->
                                    <form action="{{ route('app_admin.avisos.toggle', $aviso->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-lg border transition-colors {{ $aviso->activo ? 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200' }}">
                                            {{ $aviso->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    <!-- Botón Editar Modal Trigger -->
                                    <button onclick="document.getElementById('modal-edit-{{ $aviso->id }}').classList.remove('hidden')" class="px-2.5 py-1.5 text-xs font-bold rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-colors">
                                        Editar
                                    </button>

                                    <!-- Eliminar -->
                                    <form action="{{ route('app_admin.avisos.destroy', $aviso->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este aviso de seguridad?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Edición -->
                        <div id="modal-edit-{{ $aviso->id }}" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-gray-200">
                                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                                    <h3 class="font-black text-gray-900 text-base">Editar Slide de Seguridad</h3>
                                    <button onclick="document.getElementById('modal-edit-{{ $aviso->id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                                </div>
                                <form action="{{ route('app_admin.avisos.update', $aviso->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Título *</label>
                                        <input type="text" name="titulo" value="{{ $aviso->titulo }}" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Descripción / Recomendación *</label>
                                        <textarea name="descripcion" rows="3" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 font-medium">{{ $aviso->descripcion }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Severidad *</label>
                                            <select name="severidad" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs text-gray-800 font-medium">
                                                <option value="tip" {{ $aviso->severidad === 'tip' ? 'selected' : '' }}>Consejo (Verde)</option>
                                                <option value="info" {{ $aviso->severidad === 'info' ? 'selected' : '' }}>Información (Azul)</option>
                                                <option value="warning" {{ $aviso->severidad === 'warning' ? 'selected' : '' }}>Advertencia (Ámbar)</option>
                                                <option value="danger" {{ $aviso->severidad === 'danger' ? 'selected' : '' }}>Crítico (Rojo)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Ícono</label>
                                            <select name="icono" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs text-gray-800 font-medium">
                                                <option value="shield" {{ $aviso->icono === 'shield' ? 'selected' : '' }}>🛡️ Escudo</option>
                                                <option value="lock" {{ $aviso->icono === 'lock' ? 'selected' : '' }}>🔒 Candado</option>
                                                <option value="key" {{ $aviso->icono === 'key' ? 'selected' : '' }}>🔑 Clave</option>
                                                <option value="usb" {{ $aviso->icono === 'usb' ? 'selected' : '' }}>💾 USB</option>
                                                <option value="alert" {{ $aviso->icono === 'alert' ? 'selected' : '' }}>⚠️ Alerta</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Orden</label>
                                            <input type="number" name="orden" value="{{ $aviso->orden }}" min="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs text-gray-800 font-medium">
                                        </div>
                                        <div class="flex items-center pt-5">
                                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                                                <input type="checkbox" name="activo" value="1" {{ $aviso->activo ? 'checked' : '' }} class="rounded border-gray-300 text-amber-600">
                                                <span>Activo</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                                        <button type="button" onclick="document.getElementById('modal-edit-{{ $aviso->id }}').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50">Cancelar</button>
                                        <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
