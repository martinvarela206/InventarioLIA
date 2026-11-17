@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('tickets.index') }}" class="hover:text-amber-600 transition-colors">Tickets</a>
                <span>/</span>
                <a href="{{ route('tickets.show', $ticket->id) }}" class="hover:text-amber-600 transition-colors">Ticket #{{ $ticket->id }}</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Responder</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-amber-100 text-amber-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </span>
                <span>Responder a Ticket #{{ $ticket->id }}</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('tickets.show', $ticket->id) }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-xs">
                ← Volver al Ticket
            </a>
        </div>
    </div>

    <!-- Ficha Resumen del Ticket al que se responde -->
    <div class="bg-gray-50/80 border border-gray-200 rounded-2xl p-5 space-y-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <span class="text-xs font-black text-gray-700 bg-gray-200/80 px-2 py-0.5 rounded font-mono">#{{ $ticket->id }}</span>
            <span class="text-xs text-gray-500">
                Apertura por <strong class="text-gray-800">{{ $ticket->solicitante ?: ($ticket->usuario?->nombre ?? 'Sistema') }}</strong> • {{ $ticket->fecha ? $ticket->fecha->format('d/m/Y H:i') : '' }}
            </span>
        </div>
        <h2 class="text-base font-black text-gray-900 leading-snug">
            {{ $ticket->titulo_display }}
        </h2>
        @if($ticket->comentario)
            <div class="text-xs text-gray-600 pt-1 line-clamp-2">
                {{ Str::limit(strip_tags($ticket->comentario), 180) }}
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm">
            <p class="font-bold mb-1">Por favor corrija los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario de Respuesta -->
    <form action="{{ route('tickets.comentar', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-5">
        @csrf

        <!-- Mensaje con Markdown -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                    Respuesta / Diagnóstico / Aporte Técnico <span class="text-rose-500 font-bold">*</span>
                </label>
                <span class="text-[11px] text-amber-800 font-semibold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">
                    Soporta Markdown (**negrita**, *cursiva*, listas, `código`)
                </span>
            </div>
            <textarea name="mensaje" rows="6" required 
                      placeholder="Describa su respuesta, avances, pruebas de diagnóstico realizadas, hallazgos o solución técnica..."
                      class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400 transition-all">{{ old('mensaje') }}</textarea>
        </div>

        <!-- Selector de Propuesta de Solución -->
        <div class="p-4 bg-amber-50/70 border border-amber-300 rounded-xl">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="es_solucion" value="1" {{ old('es_solucion') ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 rounded text-amber-600 focus:ring-amber-500">
                <div>
                    <span class="text-sm font-black text-amber-950 block">
                        Marcar esta respuesta como propuesta de solución técnica
                    </span>
                    <span class="text-xs text-amber-800 leading-relaxed block mt-0.5">
                        Indica que el problema ha sido subsanado técnicamente. El ticket pasará a estado de <strong class="font-bold">Solucionado</strong> a la espera de que el Administrador de Activos (Asset Admin) realice el cierre formal.
                    </span>
                </div>
            </label>
        </div>

        <!-- Adjuntar Fotos -->
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                Adjuntar Fotografías o Evidencias (Opcional)
            </label>
            <input type="file" name="fotos[]" multiple accept="image/*"
                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-amber-100 hover:file:text-amber-900 cursor-pointer border border-gray-200 rounded-xl p-1">
            <span class="text-[11px] text-gray-400 mt-1 block">Puede seleccionar múltiples imágenes (PNG, JPG o WebP hasta 10 MB).</span>
        </div>

        <!-- Opciones Técnicas opcionales si es técnico -->
        @if(Auth::user()->hasRole('tec') && isset($areas) && $areas->isNotEmpty())
            <div class="pt-3 border-t border-gray-100 flex items-center gap-3">
                <span class="text-xs font-bold text-gray-600 whitespace-nowrap">+ Área técnica involucrada:</span>
                <select name="nueva_area_id" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs text-gray-800">
                    <option value="">Sin nueva área adicional</option>
                    @foreach($areas as $ar)
                        @if(!$ticket->areas->contains('id', $ar->id))
                            <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Botones de Acción -->
        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('tickets.show', $ticket->id) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-bold hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-black px-6 py-2.5 rounded-xl text-sm transition-all shadow-xs">
                <span>Publicar Respuesta</span>
                <span>→</span>
            </button>
        </div>
    </form>
</div>
@endsection
