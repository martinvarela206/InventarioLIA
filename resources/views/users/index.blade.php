@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Administración</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Gestión de Usuarios y Permisos</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('dashboard.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    <!-- WIDGET INFORMATIVO Y ALERTA DE HUELLA DIGITAL (LEY 25.326) -->
    @include('users.widget-huella-alerta')

    <!-- Tabla de Usuarios (Figma Layers / Canvas Density) -->
    <div class="figma-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-figma-card/80 border-b border-figma-border-subtle text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider">
                        <th class="py-2.5 px-3.5">Usuario / Identificador</th>
                        <th class="py-2.5 px-3.5">Cargo / Carrera</th>
                        <th class="py-2.5 px-3.5">Estado Cuenta</th>
                        <th class="py-2.5 px-3.5">Matriz de Roles</th>
                        <th class="py-2.5 px-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                    @foreach($users as $user)
                        <tr class="hover:bg-figma-hover/50 transition-colors">
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <div class="font-bold text-figma-text-primary text-xs">{{ $user->nombre }}</div>
                                <div class="text-[11px] text-figma-brand font-mono">{{ $user->email }}</div>
                                @if($user->dni)
                                    <div class="text-[10px] text-figma-text-tertiary font-mono">DNI: {{ $user->dni }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3.5">
                                <div class="font-medium text-figma-text-primary text-xs">{{ $user->cargo_actual ?: 'Sin cargo registrado' }}</div>
                                <div class="text-[11px] text-figma-text-tertiary">{{ $user->carrera ?: 'Sin carrera' }}</div>
                            </td>
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                @if($user->esHistorico())
                                    <div class="space-y-0.5">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-500/15 text-purple-400 border border-purple-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            Usuario Histórico
                                        </span>
                                        <div class="text-[10px] text-purple-400/80">Huella Anonimizada</div>
                                    </div>
                                @elseif($user->activo)
                                    <form action="{{ route('users.toggleActivo', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold transition-all bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 border border-emerald-500/30 cursor-pointer" title="Clic para desactivar cuenta">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </button>
                                    </form>
                                @elseif($user->isExPersonalMayor6Anios())
                                    <div class="space-y-0.5">
                                        <form action="{{ route('users.toggleActivo', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold transition-all bg-rose-500/15 text-rose-400 hover:bg-rose-500/25 border border-rose-500/30 cursor-pointer" title="Clic para activar cuenta">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Inactivo &gt; 6 años
                                            </button>
                                        </form>
                                        <div class="text-[10px] text-amber-500">Elegible Ley 25.326</div>
                                    </div>
                                @else
                                    <form action="{{ route('users.toggleActivo', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold transition-all bg-figma-bg-deep text-figma-text-tertiary hover:text-figma-text-primary border border-figma-border-subtle cursor-pointer" title="Clic para activar cuenta">
                                            <span class="w-1.5 h-1.5 rounded-full bg-figma-text-dim"></span>
                                            Inactivo
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3 px-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $rolesOficiales = ['user_admin', 'app_admin', 'item_admin', 'tec', 'subtec', 'rev', 'registered'];
                                    @endphp
                                    @foreach($rolesOficiales as $rName)
                                        @php
                                            $hasRole = $user->hasRole($rName);
                                        @endphp
                                        <button type="button" 
                                            onclick="solicitarConfirmacionRol({{ $user->id }}, '{{ addslashes($user->nombre) }}', '{{ $rName }}', {{ $hasRole ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold transition-all border cursor-pointer {{ $hasRole ? 'bg-figma-brand text-white border-figma-brand shadow-2xs' : 'bg-figma-bg-deep border-figma-border-subtle text-figma-text-dim hover:text-figma-text-secondary hover:border-figma-border-strong' }}"
                                            title="{{ $hasRole ? 'Clic para remover este rol' : 'Clic para asignar este rol' }}">
                                            <span>{{ $rName }}</span>
                                            @if($hasRole)
                                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($user->isExPersonalMayor6Anios() && !$user->esHistorico())
                                        <button type="button" 
                                            onclick="abrirModalHuella({{ $user->id }}, '{{ addslashes($user->nombre) }}', '{{ $user->dni ?: 'Sin DNI' }}', '{{ $user->fecha_egreso ? $user->fecha_egreso->format('d/m/Y') : 'Mayor a 6 años' }}')"
                                            class="inline-flex items-center gap-1 bg-rose-600 hover:bg-rose-500 text-white font-semibold px-2 py-1 rounded text-[11px] shadow-xs transition-colors cursor-pointer" 
                                            title="Borrado de Huella Digital según Ley 25.326">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Huella</span>
                                        </button>
                                    @endif
                                    <a href="{{ route('users.reporte', $user->id) }}" class="figma-btn-secondary text-[11px] py-1 px-2" title="Ver informe integral de auditoría">
                                        <svg class="w-3 h-3 text-figma-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Informe</span>
                                    </a>
                                    <a href="{{ route('users.trayectoria', $user->id) }}" class="figma-btn-secondary text-[11px] py-1 px-2" title="Ver trayectoria">
                                        <span>Trayectoria</span>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="figma-btn-secondary text-[11px] py-1 px-2" title="Editar cuenta">
                                        <svg class="w-3 h-3 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Editar</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-3 border-t border-figma-border-subtle bg-figma-card/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PREVIA PARA CAMBIOS DE ROL (Figma Studio Dialog) -->
<div id="modal-confirmacion-rol" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="figma-panel max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center gap-3">
            <div id="modal-icono" class="w-9 h-9 rounded-lg bg-amber-500/15 text-amber-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-figma-text-primary" id="modal-titulo">Confirmar Cambio Crítico de Rol</h3>
                <p class="text-[11px] text-figma-text-tertiary" id="modal-subtitulo">Esta acción modificará los privilegios del usuario.</p>
            </div>
        </div>

        <p class="text-xs text-figma-text-secondary leading-relaxed" id="modal-mensaje">
            ¿Está seguro de modificar el rol del usuario?
        </p>

        <div class="flex justify-end gap-2 pt-2 border-t border-figma-border-subtle">
            <button type="button" onclick="cerrarModalRol()" class="figma-btn-secondary">
                Cancelar
            </button>
            <button type="button" id="btn-confirmar-rol" class="figma-btn-primary">
                Sí, Confirmar Cambio
            </button>
        </div>
    </div>
</div>

<script>
    let accionPendiente = null;

    function solicitarConfirmacionRol(userId, userName, roleName, actualmenteTiene) {
        accionPendiente = { userId, roleName };
        const modal = document.getElementById('modal-confirmacion-rol');
        const mensaje = document.getElementById('modal-mensaje');
        const btn = document.getElementById('btn-confirmar-rol');

        if (actualmenteTiene) {
            mensaje.innerHTML = `¿Está seguro de que desea <strong>REMOVER</strong> el rol <span class="font-mono bg-figma-bg-deep text-rose-400 px-1.5 py-0.5 rounded border border-rose-500/30 font-bold">${roleName}</span> al usuario <strong>${userName}</strong>?`;
            btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-lg text-xs shadow-xs transition-colors cursor-pointer';
            btn.innerText = 'Sí, Remover Rol';
        } else {
            mensaje.innerHTML = `¿Está seguro de que desea <strong>ASIGNAR</strong> el rol <span class="font-mono bg-figma-bg-deep text-figma-brand px-1.5 py-0.5 rounded border border-figma-brand/30 font-bold">${roleName}</span> al usuario <strong>${userName}</strong>?`;
            btn.className = 'figma-btn-primary cursor-pointer';
            btn.innerText = 'Sí, Asignar Rol';
        }

        modal.classList.remove('hidden');
    }

    function cerrarModalRol() {
        document.getElementById('modal-confirmacion-rol').classList.add('hidden');
        accionPendiente = null;
    }

    document.getElementById('btn-confirmar-rol')?.addEventListener('click', function() {
        if (!accionPendiente) return;

        fetch(`/users/${accionPendiente.userId}/role`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ role: accionPendiente.roleName })
        })
        .then(res => res.json())
        .then(data => {
            cerrarModalRol();
            window.location.reload();
        })
        .catch(err => {
            alert('Error al actualizar rol: ' + err.message);
            cerrarModalRol();
        });
    });

</script>
@endsection
