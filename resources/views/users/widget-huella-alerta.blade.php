@if(isset($exColaboradoresElegibles) && $exColaboradoresElegibles->isNotEmpty())
    <div id="widget-alerta-huella" class="figma-panel p-4 space-y-3.5 border-l-4 border-l-amber-500">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-xs font-bold text-figma-text-primary">
                            Alerta de Privacidad y Prescripción Legal (Ley 25.326)
                        </h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                            {{ $exColaboradoresElegibles->count() }} colaboradores > 6 años sin huella borrada
                        </span>
                        <!-- Botón de fundamentación legal -->
                        <button type="button" 
                            onclick="abrirModalLegal()" 
                            class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-figma-card border border-figma-border-subtle text-figma-text-secondary hover:text-figma-brand hover:border-figma-brand text-[10px] font-mono font-bold transition-all cursor-pointer" 
                            title="Ver fundamentación legal (Ley 25.326 y prescripción penal a 6 años)">
                            ?
                        </button>
                    </div>
                    <p class="text-[11px] text-figma-text-tertiary mt-0.5">
                        Colaboradores desvinculados hace más de 6 años. Por normativa legal corresponde la disociación de datos preservando trazabilidad histórica.
                    </p>
                </div>
            </div>
        </div>

        <!-- Lista de colaboradores elegibles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-2 border-t border-figma-border-subtle">
            @foreach($exColaboradoresElegibles as $exColab)
                <div class="figma-card p-2.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-bold text-xs text-figma-text-primary truncate">{{ $exColab->nombre }}</div>
                        <div class="text-[11px] text-figma-text-tertiary font-mono truncate">{{ $exColab->email }}</div>
                        <div class="text-[10px] text-amber-500 font-medium">
                            Cese: {{ $exColab->fecha_egreso ? \Carbon\Carbon::parse($exColab->fecha_egreso)->format('d/m/Y') : 'Mayor a 6 años' }}
                        </div>
                    </div>
                    <button type="button" 
                        onclick="abrirModalHuella({{ $exColab->id }}, '{{ addslashes($exColab->nombre) }}', '{{ $exColab->dni ?: 'Sin DNI' }}', '{{ $exColab->fecha_egreso ? \Carbon\Carbon::parse($exColab->fecha_egreso)->format('d/m/Y') : 'Mayor a 6 años' }}')"
                        class="inline-flex items-center gap-1 bg-rose-600 hover:bg-rose-500 text-white font-semibold px-2 py-1.5 rounded text-[11px] shadow-xs transition-all shrink-0 cursor-pointer"
                        title="Borrado de Huella Digital">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Borrar Huella</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- MODAL DE FUNDAMENTACIÓN LEGAL (Figma Studio Dialog) -->
<div id="modal-fundamentacion-legal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="figma-panel max-w-lg w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-figma-border-subtle">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-figma-brand/15 text-figma-brand flex items-center justify-center font-mono font-bold text-xs">§</span>
                <div>
                    <h3 class="text-sm font-bold text-figma-text-primary">Fundamentación Legal y Normativa</h3>
                    <p class="text-[11px] text-figma-text-tertiary">Ciclo de vida de datos y prescripción penal</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalLegal()" class="text-figma-text-tertiary hover:text-figma-text-primary text-sm font-bold">✕</button>
        </div>

        <div class="space-y-2.5 text-xs text-figma-text-secondary leading-relaxed max-h-[60vh] overflow-y-auto pr-1">
            <div class="p-3 figma-card space-y-1">
                <div class="font-bold text-figma-brand text-xs">1. Ley 25.326 de Protección de Datos Personales (Art. 16)</div>
                <p class="text-[11px] text-figma-text-tertiary">
                    Establece el derecho a la supresión y disociación de los datos personales cuando ha cesado la finalidad legítima para su conservación, impidiendo la permanencia indefinida de registros sensibles de personas ya desvinculadas.
                </p>
            </div>

            <div class="p-3 figma-card space-y-1">
                <div class="font-bold text-amber-500 text-xs">2. Prescripción Penal y Laboral a los 6 Años</div>
                <p class="text-[11px] text-figma-text-tertiary">
                    Transcurridos seis (6) años desde el egreso definitivo de la institución, prescribe legalmente cualquier acción o responsabilidad patrimonial o penal vinculada a la tenencia o gestión de bienes públicos por parte del colaborador.
                </p>
            </div>

            <div class="p-3 figma-card space-y-1">
                <div class="font-bold text-indigo-400 text-xs">3. Disociación y Garantía de Trazabilidad del Inventario</div>
                <p class="text-[11px] text-figma-text-tertiary">
                    Para no romper las relaciones con auditorías y tickets pasados, se aplica <strong>disociación irreversible</strong>: se suprime la huella identificatoria pero se preserva el ID numérico bajo el rótulo inerte <code class="font-mono bg-figma-bg-deep px-1 rounded text-figma-text-primary">Usuario Histórico #ID</code>.
                </p>
            </div>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="button" onclick="cerrarModalLegal()" class="figma-btn-primary">
                Entendido
            </button>
        </div>
    </div>
</div>

<!-- MODAL EXPLICATIVO DE BORRADO DE HUELLA DIGITAL -->
<div id="modal-borrado-huella" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="figma-panel max-w-lg w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-start gap-3">
            <div class="p-2.5 bg-rose-500/10 border border-rose-500/30 text-rose-500 rounded-lg shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-figma-text-primary">Borrado de Huella Digital</h3>
                <p class="text-[11px] text-rose-400 font-semibold mt-0.5">Procedimiento Legal Irreversible — Ley 25.326 (Art. 16)</p>
            </div>
        </div>

        <div class="figma-card p-3 text-xs space-y-1.5 text-figma-text-secondary">
            <div><strong class="text-figma-text-primary">Colaborador:</strong> <span id="huella-user-nombre" class="font-bold text-figma-text-primary"></span></div>
            <div><strong class="text-figma-text-primary">DNI Registrado:</strong> <span id="huella-user-dni" class="font-mono text-figma-text-secondary"></span></div>
            <div><strong class="text-figma-text-primary">Plazo de Cese:</strong> <span id="huella-user-egreso" class="text-figma-text-secondary"></span> (&gt; 6 años cumplidos)</div>
        </div>

        <div class="text-xs text-figma-text-tertiary space-y-1.5 leading-relaxed">
            <p>
                Al confirmar, se procederá a la <strong>disociación irreversible</strong> de datos personales protegidos asignando la identidad inerte <code class="font-mono bg-figma-bg-deep px-1 rounded text-figma-text-primary">Usuario Histórico #ID</code>.
            </p>
            <p class="text-[11px]">
                🛡️ <em>Trazabilidad garantizada:</em> Los registros históricos de movimientos y tickets técnicos se preservan intactos para auditoría.
            </p>
        </div>

        <form id="form-borrado-huella" method="POST" action="">
            @csrf
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-figma-border-subtle">
                <button type="button" onclick="cerrarModalHuella()" class="figma-btn-secondary">
                    Cancelar
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs py-2 px-3.5 rounded-lg shadow-xs transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
