<?php

namespace App\Http\Controllers;

use App\Models\MovimientoAdjunto;
use App\Models\TicketComentarioAdjunto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AdjuntoController extends Controller
{
    /**
     * Entrega autenticada y segura de archivos adjuntos del ticket principal.
     */
    public function verAdjuntoTicket(int $id): Response
    {
        $adjunto = MovimientoAdjunto::findOrFail($id);

        $disk = Storage::disk('local')->exists($adjunto->archivo_path) ? 'local' : 'public';

        if (!Storage::disk($disk)->exists($adjunto->archivo_path)) {
            abort(404, 'Adjunto no encontrado.');
        }

        return Storage::disk($disk)->response($adjunto->archivo_path, $adjunto->nombre_original);
    }

    /**
     * Entrega autenticada y segura de archivos adjuntos en el hilo de comentarios.
     */
    public function verAdjuntoComentario(int $id): Response
    {
        $adjunto = TicketComentarioAdjunto::findOrFail($id);

        $disk = Storage::disk('local')->exists($adjunto->archivo_path) ? 'local' : 'public';

        if (!Storage::disk($disk)->exists($adjunto->archivo_path)) {
            abort(404, 'Adjunto no encontrado.');
        }

        return Storage::disk($disk)->response($adjunto->archivo_path, $adjunto->nombre_original);
    }
}
