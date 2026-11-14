<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Oficial Ticket #{{ $ticket->id }} - Nexo LIA</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
            @bottom-right {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 8pt;
                color: #6b7280;
            }
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.4;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #fbc101;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header-title {
            font-size: 13pt;
            font-weight: 900;
            color: #111827;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 8pt;
            font-weight: bold;
            color: #4b5563;
            margin: 2px 0 0 0;
        }
        .ticket-code {
            text-align: right;
            font-family: monospace;
            font-size: 13pt;
            font-weight: bold;
            color: #111827;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .status-abierto { background-color: #fef3c7; color: #92400e; border: 1px solid #fbc101; }
        .status-solucionado { background-color: #fef3c7; color: #78350f; border: 1px solid #f59e0b; }
        .status-resuelto { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .status-baja { background-color: #ffe4e6; color: #9f1239; border: 1px solid #f43f5e; }

        .section-box {
            margin-bottom: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .section-header {
            background-color: #fffbeb;
            border-bottom: 1px solid #fde68a;
            padding: 6px 10px;
            font-size: 9pt;
            font-weight: bold;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-content {
            padding: 8px 10px;
        }
        .data-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .data-grid td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 9pt;
        }
        .data-label {
            width: 25%;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .data-value {
            width: 75%;
            color: #111827;
            font-weight: 500;
        }
        .comment-item {
            border-left: 3px solid #fbc101;
            padding: 6px 10px;
            margin-bottom: 8px;
            background-color: #f9fafb;
            border-radius: 0 6px 6px 0;
            page-break-inside: avoid;
        }
        .comment-author {
            font-weight: bold;
            font-size: 8.5pt;
            color: #111827;
        }
        .comment-date {
            font-size: 7.5pt;
            color: #6b7280;
            margin-left: 6px;
        }
        .comment-text {
            font-size: 8.5pt;
            color: #374151;
            margin-top: 3px;
        }
        .photos-container {
            width: 100%;
            margin-top: 8px;
        }
        .photo-card {
            display: inline-block;
            width: 48%;
            margin-right: 2%;
            margin-bottom: 10px;
            vertical-align: top;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
            page-break-inside: avoid;
        }
        .photo-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            display: block;
        }
        .photo-caption {
            padding: 4px 6px;
            font-size: 7.5pt;
            color: #4b5563;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .footer-note {
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            font-size: 7.5pt;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Encabezado Membretado Oficial -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="header-title">Nexo LIA • Informe Oficial de Incidencia</h1>
                <p class="header-subtitle">
                    Laboratorio de Informática Aplicada • Dpto. de Informática<br>
                    Facultad de Ciencias Exactas, Físicas y Naturales — Universidad Nacional de San Juan
                </p>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="ticket-code">TCK-#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div>
                    @if($ticket->estado_ticket === 'resuelto')
                        <span class="status-badge status-resuelto">Resuelto</span>
                    @elseif($ticket->estado_ticket === 'baja')
                        <span class="status-badge status-baja">Baja Irreparable</span>
                    @elseif($ticket->estado_ticket === 'solucionado')
                        <span class="status-badge status-solucionado">Solución Propuesta</span>
                    @else
                        <span class="status-badge status-abierto">Abierto / En Taller</span>
                    @endif
                </div>
                <div style="font-size: 7.5pt; color: #6b7280; margin-top: 3px;">
                    Emisión: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. Identificación del Objeto o Ámbito Afectado -->
    <div class="section-box">
        <div class="section-header">
            @if($ticket->esTicketGeneral())
                1. Ámbito de Infraestructura y Espacio Físico
            @else
                1. Identificación Patrimonial del Activo
            @endif
        </div>
        <div class="section-content">
            <table class="data-grid">
                @if($ticket->esTicketGeneral())
                    <tr>
                        <td class="data-label">Ámbito / Entidad:</td>
                        <td class="data-value"><strong>{{ $ticket->ambitoTicket?->nombre ?? 'LIA General' }}</strong></td>
                    </tr>
                    @if($ticket->ambitoTicket?->descripcion)
                        <tr>
                            <td class="data-label">Descripción Ámbito:</td>
                            <td class="data-value">{{ $ticket->ambitoTicket->descripcion }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="data-label">Sector / Referencia:</td>
                        <td class="data-value">{{ $ticket->puesto ?: 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Modalidad:</td>
                        <td class="data-value">Incidencia General de Infraestructura / Espacios Comunes</td>
                    </tr>
                @else
                    <tr>
                        <td class="data-label">Nro LIA Oficial:</td>
                        <td class="data-value"><strong>{{ $ticket->elemento?->nro_lia ?: 'Sin Asignar' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="data-label">Nro UNSJ:</td>
                        <td class="data-value">{{ $ticket->elemento?->nro_unsj ?: 'S/N' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Tipo y Hardware:</td>
                        <td class="data-value">{{ ucfirst($ticket->elemento?->tipo?->nombre ?? 'Hardware') }} • {{ $ticket->elemento?->marca }} {{ $ticket->elemento?->modelo }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Ubicación Registrada:</td>
                        <td class="data-value">{{ $ticket->ubicacion?->nombre ?? 'Depósito' }} @if($ticket->puesto) — Puesto: {{ $ticket->puesto }} @endif</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <!-- 2. Apertura y Falla Reportada -->
    <div class="section-box">
        <div class="section-header">2. Registro de Apertura y Diagnóstico Inicial</div>
        <div class="section-content">
            <table class="data-grid" style="margin-bottom: 8px;">
                <tr>
                    <td class="data-label">Informante / Solicitante:</td>
                    <td class="data-value"><strong>{{ $ticket->solicitante ?: ($ticket->usuario?->nombre ?? 'Sistema') }}</strong></td>
                </tr>
                <tr>
                    <td class="data-label">Registrado Por:</td>
                    <td class="data-value">{{ $ticket->usuario?->nombre }} ({{ $ticket->usuario?->email }})</td>
                </tr>
                <tr>
                    <td class="data-label">Fecha de Apertura:</td>
                    <td class="data-value">{{ $ticket->fecha ? $ticket->fecha->format('d/m/Y H:i:s') : '-' }}</td>
                </tr>
                @if($ticket->areas->isNotEmpty())
                    <tr>
                        <td class="data-label">Áreas Involucradas:</td>
                        <td class="data-value">
                            @foreach($ticket->areas as $ar)
                                <span style="display: inline-block; padding: 2px 6px; background-color: #f3f4f6; border-radius: 4px; font-size: 8pt; margin-right: 4px; font-weight: bold;">
                                    {{ $ar->nombre }}
                                </span>
                            @endforeach
                        </td>
                    </tr>
                @endif
            </table>

            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px;">
                <div style="font-weight: bold; font-size: 8pt; color: #4b5563; text-transform: uppercase; margin-bottom: 4px;">Descripción de la Falla:</div>
                <div style="font-size: 9pt; color: #111827; white-space: pre-wrap;">{{ $ticket->comentario }}</div>
            </div>
        </div>
    </div>

    <!-- 3. Bitácora de Mensajes e Intervenciones Técnicas -->
    @if($ticket->comentarios->isNotEmpty())
        <div class="section-box">
            <div class="section-header">3. Bitácora Cronológica de Intervenciones ({{ $ticket->comentarios->count() }})</div>
            <div class="section-content">
                @foreach($ticket->comentarios as $c)
                    <div class="comment-item">
                        <div>
                            <span class="comment-author">{{ $c->usuario?->nombre ?: 'Usuario' }}</span>
                            <span class="comment-date">{{ $c->created_at ? $c->created_at->format('d/m/Y H:i:s') : '-' }}</span>
                        </div>
                        <div class="comment-text">{{ $c->contenido }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 4. Resolución Técnica y Cierre -->
    @if($ticket->resoluciones->isNotEmpty())
        <div class="section-box">
            <div class="section-header">4. Resolución Técnica y Cierre de Incidencia</div>
            <div class="section-content">
                @foreach($ticket->resoluciones as $res)
                    <table class="data-grid" style="margin-bottom: 6px;">
                        <tr>
                            <td class="data-label">Técnico Responsable:</td>
                            <td class="data-value"><strong>{{ $res->usuario?->nombre }}</strong> ({{ $res->usuario?->email }})</td>
                        </tr>
                        <tr>
                            <td class="data-label">Fecha de Cierre:</td>
                            <td class="data-value">{{ $res->fecha ? $res->fecha->format('d/m/Y H:i:s') : '-' }}</td>
                        </tr>
                        @if($res->ubicacion)
                            <tr>
                                <td class="data-label">Destino Asignado:</td>
                                <td class="data-value">{{ $res->ubicacion->nombre }} @if($res->puesto) — Puesto: {{ $res->puesto }} @endif</td>
                            </tr>
                        @endif
                    </table>

                    <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 8px;">
                        <div style="font-weight: bold; font-size: 8pt; color: #065f46; text-transform: uppercase; margin-bottom: 4px;">Detalle de la Solución Aplicada:</div>
                        <div style="font-size: 9pt; color: #064e3b; white-space: pre-wrap;">{{ $res->comentario }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 5. Evidencia Fotográfica Adjunta -->
    @if(!empty($fotosBase64))
        <div class="section-box">
            <div class="section-header">5. Evidencia Fotográfica Digitalizada ({{ count($fotosBase64) }})</div>
            <div class="section-content">
                <div class="photos-container">
                    @foreach($fotosBase64 as $foto)
                        <div class="photo-card">
                            <img src="{{ $foto['data_uri'] }}" alt="Evidencia fotográfica">
                            <div class="photo-caption">{{ $foto['nombre'] }} • {{ $foto['contexto'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Pie de Página Institucional -->
    <div class="footer-note">
        Este documento constituye un registro técnico oficial emitido por el sistema Nexo LIA de acuerdo a las directivas de trazabilidad y custodia patrimonial de la Universidad Nacional de San Juan.<br>
        Identificador Forense: {{ hash('sha256', $ticket->id . '-' . $ticket->fecha . '-' . config('app.key')) }}
    </div>
</body>
</html>
