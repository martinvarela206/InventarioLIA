@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Usuarios</span>
            </h1>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Usuario / Email</th>
                        <th class="py-3 px-4">Cargo / Carrera</th>
                        <th class="py-3 px-4">Estado Cuenta</th>
                        <th class="py-3 px-4">Roles Asignados</th>
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
                                @if($user->esHistorico())
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                            <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            Usuario Histórico
                                        </span>
                                        <div class="text-[10px] text-purple-700 font-medium">Huella Anonimizada (Ley 25.326)</div>
                                    </div>
                                @elseif($user->activo)
                                    <form action="{{ route('users.toggleActivo', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300" title="Clic para desactivar cuenta">
                                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                            Activo
                                        </button>
                                    </form>
                                @elseif($user->isExPersonalMayor6Anios())
                                    <div class="space-y-1">
                                        <form action="{{ route('users.toggleActivo', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all bg-rose-100 text-rose-800 hover:bg-rose-200 border border-rose-300" title="Clic para activar cuenta">
                                                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                                Inactivo &gt; 6 años
                                            </button>
                                        </form>
                                        <div class="text-[10px] text-rose-700 font-bold">Plazo Legal Cumplido (&gt; 6 años) - Elegible Ley 25.326</div>
                                    </div>
                                @else
                                    <form action="{{ route('users.toggleActivo', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300" title="Clic para activar cuenta">
                                            <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                            Inactivo
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @php
                                        $rolesOficiales = ['user_admin', 'app_admin', 'item_admin', 'tec', 'subtec', 'rev', 'registered'];
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
                                    @if($user->isExPersonalMayor6Anios() && !$user->esHistorico())
                                        <button type="button" 
                                            onclick="abrirModalHuella({{ $user->id }}, '{{ addslashes($user->nombre) }}', '{{ $user->dni ?: 'Sin DNI' }}', '{{ $user->fecha_egreso ? $user->fecha_egreso->format('d/m/Y') : 'Mayor a 6 años' }}')"
                                            class="inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 border border-rose-300 text-rose-700 font-bold px-2.5 py-1.5 rounded-xl text-xs shadow-xs transition-all" 
                                            title="Borrado de Huella Digital y disociación según Ley 25.326">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Borrar Huella</span>
                                        </button>
                                    @endif
                                    <a href="{{ route('users.reporte', $user->id) }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3 py-1.5 rounded-xl text-xs shadow-xs transition-all" title="Ver informe integral de auditoría y trazabilidad (Tickets, Reportes y Movimientos)">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Informe</span>
                                    </a>
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

    function abrirModalHuella(userId, nombre, dni, egreso) {
        const modal = document.getElementById('modal-borrado-huella');
        document.getElementById('huella-user-nombre').textContent = nombre;
        document.getElementById('huella-user-dni').textContent = dni;
        document.getElementById('huella-user-egreso').textContent = egreso;
        document.getElementById('form-borrado-huella').action = `/users/${userId}/anonimizar`;
        modal.classList.remove('hidden');
    }

    function cerrarModalHuella() {
        document.getElementById('modal-borrado-huella').classList.add('hidden');
    }
</script>

<!-- MODAL EXPLICATIVO DE BORRADO DE HUELLA DIGITAL (LEY 25.326) -->
<div id="modal-borrado-huella" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-5 border border-gray-200">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-rose-100 text-rose-700 rounded-2xl shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-gray-900 leading-tight">Borrado de Huella Digital</h3>
                <p class="text-xs text-rose-600 font-bold mt-0.5">Procedimiento Legal Irreversible — Ley 25.326 (Art. 16)</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-2xl p-4 text-xs text-gray-700 space-y-2 border border-gray-100">
            <div><strong>Colaborador:</strong> <span id="huella-user-nombre" class="font-bold text-gray-900"></span></div>
            <div><strong>DNI Registrado:</strong> <span id="huella-user-dni" class="font-mono text-gray-800"></span></div>
            <div><strong>Plazo de Cese Laboral:</strong> <span id="huella-user-egreso" class="text-gray-800"></span> (> 6 años cumplidos)</div>
        </div>

        <div class="text-xs text-gray-600 space-y-2 leading-relaxed">
            <p>
                Al confirmar, se procederá a la <strong>disociación irreversible</strong> de todos los datos personales protegidos (DNI, teléfono, dirección, fecha de nacimiento, carrera y email institucional) asignando la identidad inerte <code class="bg-gray-200 px-1 py-0.5 rounded text-gray-800 font-mono">Usuario Histórico #ID</code>.
            </p>
            <p class="text-[11px] text-gray-500">
                🛡️ <em>Garantía de trazabilidad:</em> El ID y los registros históricos de movimientos, tickets e intervenciones patrimoniales permanecerán inalterados para auditoría.
            </p>
        </div>

        <form id="form-borrado-huella" method="POST" action="">
            @csrf
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="cerrarModalHuella()" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Confirmar Borrado Irreversible</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
