@if(isset($exColaboradoresElegibles) && $exColaboradoresElegibles->isNotEmpty())
    <div id="widget-alerta-huella" class="bg-gradient-to-r from-amber-50 to-rose-50 border border-amber-300/80 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm font-extrabold text-gray-900">
                            Alerta de Privacidad y Prescripción Legal (Ley 25.326)
                        </h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            {{ $exColaboradoresElegibles->count() }} colaboradores > 6 años sin huella borrada
                        </span>
                        <!-- Ícono interactivo de ayuda (?) -->
                        <button type="button" 
                            onclick="abrirModalLegal()" 
                            class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-white border border-gray-300 text-gray-600 hover:text-amber-700 hover:border-amber-400 hover:bg-amber-50 text-xs font-bold transition-all shadow-2xs cursor-pointer" 
                            title="Ver fundamentación legal (Ley 25.326 y prescripción penal a 6 años)">
                            ?
                        </button>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">
                        Detectamos colaboradores con más de 6 años desde su cese laboral sin huella digital borrada. Por exigencia legal y prescripción penal, corresponde disociar sus datos sensibles preservando la trazabilidad de inventario.
                    </p>
                </div>
            </div>
        </div>

        <!-- Lista de colaboradores elegibles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-2 border-t border-amber-200/60">
            @foreach($exColaboradoresElegibles as $exColab)
                <div class="bg-white/90 backdrop-blur-xs rounded-xl p-3 border border-amber-200 shadow-2xs flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-bold text-xs text-gray-900 truncate">{{ $exColab->nombre }}</div>
                        <div class="text-[11px] text-gray-500 font-mono truncate">{{ $exColab->email }}</div>
                        <div class="text-[10px] text-rose-700 font-medium">
                            Cese: {{ $exColab->fecha_egreso ? \Carbon\Carbon::parse($exColab->fecha_egreso)->format('d/m/Y') : 'Mayor a 6 años' }}
                        </div>
                    </div>
                    <button type="button" 
                        onclick="abrirModalHuella({{ $exColab->id }}, '{{ addslashes($exColab->nombre) }}', '{{ $exColab->dni ?: 'Sin DNI' }}', '{{ $exColab->fecha_egreso ? \Carbon\Carbon::parse($exColab->fecha_egreso)->format('d/m/Y') : 'Mayor a 6 años' }}')"
                        class="inline-flex items-center gap-1 bg-rose-600 hover:bg-rose-700 text-white font-extrabold px-2.5 py-1.5 rounded-lg text-xs shadow-xs transition-all shrink-0 cursor-pointer"
                        title="Borrado de Huella Digital">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Borrado de Huella Digital</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- MODAL DE FUNDAMENTACIÓN LEGAL (Ley 25.326 y Prescripción Penal) -->
<div id="modal-fundamentacion-legal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black text-sm">
                    §
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900">Fundamentación Legal y Normativa</h3>
                    <p class="text-xs text-gray-500">Ciclo de vida de datos y prescripción penal</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalLegal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
        </div>

        <div class="space-y-3 text-xs text-gray-700 leading-relaxed max-h-[60vh] overflow-y-auto pr-1">
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl space-y-1">
                <div class="font-bold text-blue-900 text-sm">1. Ley 25.326 de Protección de Datos Personales (Art. 16)</div>
                <p class="text-blue-800">
                    Establece el derecho a la supresión y disociación de los datos personales cuando ha cesado la finalidad legítima para su conservación, impidiendo la permanencia indefinida de registros sensibles de personas ya desvinculadas.
                </p>
            </div>

            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl space-y-1">
                <div class="font-bold text-rose-900 text-sm">2. Prescripción Penal y Laboral a los 6 Años</div>
                <p class="text-rose-800">
                    Transcurridos seis (6) años desde el egreso definitivo de la institución, prescribe legalmente cualquier acción o responsabilidad patrimonial o penal vinculada a la tenencia o gestión de bienes públicos por parte del colaborador, perdiendo causa jurídica la retención de su identidad civil (DNI, WhatsApp, domicilio).
                </p>
            </div>

            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl space-y-1">
                <div class="font-bold text-purple-900 text-sm">3. Necesidad de Disociación y Garantía de Trazabilidad del Inventario</div>
                <p class="text-purple-800">
                    Eliminar el registro físico o el ID de la base de datos destruiría las claves foráneas históricas de movimientos de inventario, actas de auditoría física y tickets técnicos. Por tal motivo, se aplica <strong>disociación irreversible</strong>: se suprime la huella identificatoria pero se preserva el ID numérico bajo el rótulo inerte <em>Usuario Histórico #ID</em>.
                </p>
            </div>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="button" onclick="cerrarModalLegal()" class="px-4 py-2 bg-gray-900 hover:bg-black text-white font-bold rounded-xl text-xs transition-all shadow-xs">
                Entendido
            </button>
        </div>
    </div>
</div>

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
            <div><strong>Plazo de Cese Laboral:</strong> <span id="huella-user-egreso" class="text-gray-800"></span> (&gt; 6 años cumplidos)</div>
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

<script>
    function abrirModalLegal() {
        const modal = document.getElementById('modal-fundamentacion-legal');
        if (modal) modal.classList.remove('hidden');
    }
    function cerrarModalLegal() {
        const modal = document.getElementById('modal-fundamentacion-legal');
        if (modal) modal.classList.add('hidden');
    }
    function abrirModalHuella(userId, nombre, dni, egreso) {
        const modal = document.getElementById('modal-borrado-huella');
        if (!modal) return;
        document.getElementById('huella-user-nombre').textContent = nombre;
        document.getElementById('huella-user-dni').textContent = dni;
        document.getElementById('huella-user-egreso').textContent = egreso;
        document.getElementById('form-borrado-huella').action = `/users/${userId}/anonimizar`;
        modal.classList.remove('hidden');
    }
    function cerrarModalHuella() {
        const modal = document.getElementById('modal-borrado-huella');
        if (modal) modal.classList.add('hidden');
    }
</script>
