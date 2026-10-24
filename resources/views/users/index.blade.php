@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Administración de Usuarios y Permisos</span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">Asignación de roles oficiales, activación de cuentas y trayectorias</p>
        </div>
        <span class="text-xs bg-amber-100 text-amber-900 font-bold px-3 py-1 rounded-full border border-amber-300">
            6 Roles Oficiales
        </span>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Usuario / Email</th>
                        <th class="py-3 px-4">Cargo / Carrera</th>
                        <th class="py-3 px-4">Estado Cuenta</th>
                        <th class="py-3 px-4">Roles Oficiales Asignados (Clic para Modificar)</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800">
                    @foreach($users as $user)
                        <tr class="hover:bg-amber-50/40 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900">{{ $user->nombre }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $user->email }}</div>
                                @if($user->dni)
                                    <div class="text-[11px] text-gray-400">DNI: {{ $user->dni }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-gray-900">{{ $user->cargo_actual ?: 'Sin cargo registrado' }}</div>
                                <div class="text-xs text-gray-500">{{ $user->carrera ?: 'Sin carrera' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <form action="{{ route('users.toggleActivo', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all {{ $user->activo ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                        <span class="w-2 h-2 rounded-full {{ $user->activo ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                                        {{ $user->activo ? 'Cuenta Activa' : 'Inactiva (Bloqueado)' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @php
                                        $rolesOficiales = ['user_admin', 'app_admin', 'item_admin', 'tec', 'rev', 'registered'];
                                    @endphp
                                    @foreach($rolesOficiales as $rName)
                                        @php
                                            $hasRole = $user->hasRole($rName);
                                        @endphp
                                        <button type="button" 
                                            onclick="solicitarConfirmacionRol({{ $user->id }}, '{{ addslashes($user->nombre) }}', '{{ $rName }}', {{ $hasRole ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all border {{ $hasRole ? 'bg-amber-400 border-amber-500 text-black shadow-2xs' : 'bg-gray-100 border-gray-200 text-gray-400 hover:bg-gray-200' }}"
                                            title="{{ $hasRole ? 'Clic para remover este rol' : 'Clic para asignar este rol' }}">
                                            <span>{{ $rName }}</span>
                                            @if($hasRole)
                                                <svg class="w-3 h-3 text-black stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3 h-3 text-gray-400 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.trayectoria', $user->id) }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3 py-1.5 rounded-xl text-xs shadow-xs transition-all" title="Ver y gestionar periodos de trayectoria">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Trayectoria ({{ $user->trayectorias->count() }})</span>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-amber-50 border border-gray-300 text-gray-800 hover:text-black font-bold px-3 py-1.5 rounded-xl text-xs shadow-xs transition-all" title="Editar perfil y permisos">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
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
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PREVIA PARA CAMBIOS DE ROL -->
<div id="modal-confirmacion-rol" class="hidden fixed inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-gray-200">
        <div class="flex items-center gap-3">
            <div id="modal-icono" class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900" id="modal-titulo">Confirmar Cambio Crítico de Rol</h3>
                <p class="text-xs text-gray-500" id="modal-subtitulo">Esta acción afectará los privilegios del usuario en el sistema.</p>
            </div>
        </div>

        <p class="text-sm text-gray-700" id="modal-mensaje">
            ¿Está seguro de modificar el rol del usuario?
        </p>

        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button type="button" onclick="cerrarModalRol()" class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold rounded-xl text-xs shadow-sm transition-all">
                Cancelar
            </button>
            <button type="button" id="btn-confirmar-rol" class="px-5 py-2 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs shadow-sm transition-all">
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
            mensaje.innerHTML = `¿Está seguro de que desea <strong>REMOVER</strong> el rol <span class="font-mono bg-red-100 text-red-900 px-1 py-0.5 rounded font-bold">${roleName}</span> al usuario <strong>${userName}</strong>?`;
            btn.className = 'px-5 py-2 bg-rose-600 hover:bg-rose-700 font-extrabold text-white rounded-xl text-xs shadow-sm transition-all';
            btn.innerText = 'Sí, Remover Rol';
        } else {
            mensaje.innerHTML = `¿Está seguro de que desea <strong>ASIGNAR</strong> el rol <span class="font-mono bg-amber-100 text-amber-900 px-1 py-0.5 rounded font-bold">${roleName}</span> al usuario <strong>${userName}</strong>?`;
            btn.className = 'px-5 py-2 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs shadow-sm transition-all';
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
