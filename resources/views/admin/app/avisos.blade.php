@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('app_admin.index') }}" class="hover:text-figma-brand transition-colors">Gestión App</a>
                <span>/</span>
                <span class="text-figma-text-primary">Avisos de Seguridad</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Concientización y Buenas Prácticas Informáticas</span>
                <span class="text-[10px] bg-figma-bg-deep text-figma-brand font-mono font-bold px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $avisos->count() }} avisos
                </span>
            </h1>
            <p class="text-xs text-figma-text-secondary mt-0.5">
                Administra los slides dinámicos y consejos que se proyectan en el carrusel de la landing page.
            </p>
        </div>
        <div>
            <a href="{{ url('/') }}" target="_blank" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver en Portada</span>
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 text-xs">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Formulario Alta de Aviso -->
        <div class="lg:col-span-1">
            <div class="figma-panel p-5 sticky top-20 space-y-3.5">
                <h2 class="font-bold text-figma-text-primary text-sm">Nuevo Slide de Seguridad</h2>
                <p class="text-xs text-figma-text-tertiary">Añadir una pauta o advertencia para la comunidad.</p>

                <form action="{{ route('app_admin.avisos.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Título del Aviso <span class="text-rose-400">*</span></label>
                        <input type="text" name="titulo" required placeholder="Ej. Bloqueo de Puesto al Ausentarse" class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Descripción / Recomendación <span class="text-rose-400">*</span></label>
                        <textarea name="descripcion" rows="3" required placeholder="Explica en forma concisa la buena práctica..." class="figma-input w-full px-2.5 py-1.5 text-xs"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Severidad <span class="text-rose-400">*</span></label>
                            <select name="severidad" required class="figma-input w-full px-2 py-1.5 text-xs">
                                <option value="tip">Consejo (Verde)</option>
                                <option value="info">Info (Azul)</option>
                                <option value="warning" selected>Advertencia (Ámbar)</option>
                                <option value="danger">Crítico (Rojo)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Ícono</label>
                            <select name="icono" class="figma-input w-full px-2 py-1.5 text-xs">
                                <option value="shield">🛡️ Escudo</option>
                                <option value="lock">🔒 Candado</option>
                                <option value="key">🔑 Clave</option>
                                <option value="usb">💾 USB</option>
                                <option value="alert">⚠️ Alerta</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 items-center">
                        <div>
                            <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Orden</label>
                            <input type="number" name="orden" value="1" min="0" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
                        </div>
                        <div class="pt-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-figma-text-secondary">
                                <input type="checkbox" name="activo" value="1" checked class="rounded text-figma-brand focus:ring-figma-brand">
                                <span>Activo en carrusel</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="figma-btn-primary w-full justify-center text-xs py-2 mt-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Guardar Aviso</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Avisos -->
        <div class="lg:col-span-2 space-y-3">
            @if($avisos->isEmpty())
                <div class="figma-card p-10 text-center text-figma-text-tertiary text-xs italic">
                    No se han registrado avisos de seguridad todavía.
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($avisos as $aviso)
                        @php
                            $badgeColors = [
                                'tip' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                'info' => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
                                'warning' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                'danger' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                            ];
                            $badgeClass = $badgeColors[$aviso->severidad] ?? 'bg-figma-bg-deep text-figma-text-secondary border-figma-border-subtle';
                        @endphp
                        <div class="figma-card p-4 space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div class="space-y-1 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase border {{ $badgeClass }}">
                                            {{ $aviso->severidad }}
                                        </span>
                                        <span class="text-[10px] font-mono text-figma-text-tertiary">#{{ $aviso->orden }}</span>
                                        @if($aviso->activo)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Visible
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-figma-text-tertiary bg-figma-bg-deep px-1.5 py-0.5 rounded">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Oculto
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-bold text-figma-text-primary">{{ $aviso->titulo }}</h3>
                                    <p class="text-xs text-figma-text-secondary leading-relaxed">{{ $aviso->descripcion }}</p>
                                </div>

                                <div class="flex items-center gap-1.5 self-end sm:self-start">
                                    <!-- Toggle Activo -->
                                    <form action="{{ route('app_admin.avisos.toggle', $aviso->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 text-[11px] font-semibold rounded border border-figma-border-subtle bg-figma-bg-deep text-figma-text-secondary hover:text-figma-text-primary transition-colors">
                                            {{ $aviso->activo ? 'Pausar' : 'Activar' }}
                                        </button>
                                    </form>

                                    <!-- Botón Editar Modal Trigger -->
                                    <button onclick="document.getElementById('modal-edit-{{ $aviso->id }}').classList.remove('hidden')" class="px-2 py-1 text-[11px] font-semibold rounded border border-figma-border-subtle bg-figma-bg-deep text-figma-text-secondary hover:text-figma-text-primary transition-colors">
                                        Editar
                                    </button>

                                    <!-- Eliminar -->
                                    <form action="{{ route('app_admin.avisos.destroy', $aviso->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este aviso de seguridad?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-figma-text-tertiary hover:text-rose-400 rounded transition-colors" title="Eliminar">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Edición -->
                        <div id="modal-edit-{{ $aviso->id }}" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                            <div class="figma-panel max-w-lg w-full p-5 space-y-3.5 shadow-2xl">
                                <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
                                    <h3 class="font-bold text-figma-text-primary text-sm">Editar Slide de Seguridad</h3>
                                    <button onclick="document.getElementById('modal-edit-{{ $aviso->id }}').classList.add('hidden')" class="text-figma-text-tertiary hover:text-figma-text-primary text-base leading-none">&times;</button>
                                </div>
                                <form action="{{ route('app_admin.avisos.update', $aviso->id) }}" method="POST" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Título *</label>
                                        <input type="text" name="titulo" value="{{ $aviso->titulo }}" required class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
                                    </div>
                                    <div>
                                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Descripción / Recomendación *</label>
                                        <textarea name="descripcion" rows="3" required class="figma-input w-full px-2.5 py-1.5 text-xs">{{ $aviso->descripcion }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Severidad *</label>
                                            <select name="severidad" required class="figma-input w-full px-2 py-1.5 text-xs">
                                                <option value="tip" {{ $aviso->severidad === 'tip' ? 'selected' : '' }}>Consejo (Verde)</option>
                                                <option value="info" {{ $aviso->severidad === 'info' ? 'selected' : '' }}>Info (Azul)</option>
                                                <option value="warning" {{ $aviso->severidad === 'warning' ? 'selected' : '' }}>Advertencia (Ámbar)</option>
                                                <option value="danger" {{ $aviso->severidad === 'danger' ? 'selected' : '' }}>Crítico (Rojo)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Ícono</label>
                                            <select name="icono" class="figma-input w-full px-2 py-1.5 text-xs">
                                                <option value="shield" {{ $aviso->icono === 'shield' ? 'selected' : '' }}>🛡️ Escudo</option>
                                                <option value="lock" {{ $aviso->icono === 'lock' ? 'selected' : '' }}>🔒 Candado</option>
                                                <option value="key" {{ $aviso->icono === 'key' ? 'selected' : '' }}>🔑 Clave</option>
                                                <option value="usb" {{ $aviso->icono === 'usb' ? 'selected' : '' }}>💾 USB</option>
                                                <option value="alert" {{ $aviso->icono === 'alert' ? 'selected' : '' }}>⚠️ Alerta</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2.5 items-center">
                                        <div>
                                            <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Orden</label>
                                            <input type="number" name="orden" value="{{ $aviso->orden }}" min="0" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
                                        </div>
                                        <div class="pt-4">
                                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-figma-text-secondary">
                                                <input type="checkbox" name="activo" value="1" {{ $aviso->activo ? 'checked' : '' }} class="rounded text-figma-brand">
                                                <span>Activo</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-figma-border-subtle">
                                        <button type="button" onclick="document.getElementById('modal-edit-{{ $aviso->id }}').classList.add('hidden')" class="figma-btn-secondary text-xs">Cancelar</button>
                                        <button type="submit" class="figma-btn-primary text-xs">Guardar Cambios</button>
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
