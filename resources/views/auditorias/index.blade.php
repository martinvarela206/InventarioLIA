@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-gray-900">Auditorías</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-indigo-100 text-indigo-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
                <span>Archivo Histórico de Auditorías Físicas</span>
            </h1>
        </div>
    </div>

    <!-- Listado de Auditorías -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        @if($auditorias->isEmpty())
            <div class="p-12 text-center text-gray-500 text-xs font-medium">
                No se han registrado auditorías de ubicación todavía.
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($auditorias as $aud)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/50 transition-colors">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-sm text-gray-900">{{ $aud->ubicacion?->nombre ?? 'Sala' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $aud->estado === 'finalizada' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800 animate-pulse' }}">
                                    {{ strtoupper($aud->estado) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                Auditor: <strong class="text-gray-800">{{ $aud->usuario?->nombre }}</strong> • Fecha: {{ $aud->fecha ? $aud->fecha->format('d/m/Y H:i') : '-' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-xs">
                            <span class="text-gray-600">Total: <strong class="text-gray-900">{{ $aud->total_auditados }}</strong></span>
                            <span class="text-emerald-700 font-bold">OK: {{ $aud->total_ok }}</span>
                            <span class="text-rose-700 font-bold">Inconsistencias: {{ $aud->total_inconsistencias }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-3 border-t border-gray-100">
                {{ $auditorias->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
