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
        $adjunto = MovimientoAdjunto::with('movimiento')->findOrFail($id);
        $user = auth()->user();

        // Blindaje Anti-IDOR: validar que el usuario tenga rol operativo o sea el creador del ticket
        $puedeVer = $user && (
            $user->hasActivePermissions() ||
            ($adjunto->movimiento && $adjunto->movimiento->user_id === $user->id)
        );

        abort_unless($puedeVer, 403, 'No autorizado para visualizar o descargar este archivo adjunto.');

        $disk = Storage::disk('local')->exists($adjunto->archivo_path) ? 'local' : 'public';

        if (! Storage::disk($disk)->exists($adjunto->archivo_path)) {
            abort(404, 'Adjunto no encontrado.');
        }

        return Storage::disk($disk)->response($adjunto->archivo_path, $adjunto->nombre_original);
    }

    /**
     * Entrega autenticada y segura de archivos adjuntos en el hilo de comentarios.
     */
    public function verAdjuntoComentario(int $id): Response
    {
        $adjunto = TicketComentarioAdjunto::with(['comentario.ticket'])->findOrFail($id);
        $user = auth()->user();

        // Blindaje Anti-IDOR: validar que el usuario tenga rol operativo, sea autor del comentario o creador del ticket padre
        $puedeVer = $user && (
            $user->hasActivePermissions() ||
            ($adjunto->comentario && (
                $adjunto->comentario->user_id === $user->id ||
                ($adjunto->comentario->ticket && $adjunto->comentario->ticket->user_id === $user->id)
            ))
        );

        abort_unless($puedeVer, 403, 'No autorizado para visualizar o descargar este archivo adjunto.');

        $disk = Storage::disk('local')->exists($adjunto->archivo_path) ? 'local' : 'public';

        if (! Storage::disk($disk)->exists($adjunto->archivo_path)) {
            abort(404, 'Adjunto no encontrado.');
        }

        return Storage::disk($disk)->response($adjunto->archivo_path, $adjunto->nombre_original);
    }
}
