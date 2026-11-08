<?php

namespace App\Enums;

enum EstadoElemento: string
{
    case OPERATIVO = 'operativo';
    case PRESTADO = 'prestado';
    case EN_REPARACION = 'en_reparacion';
    case EN_LIMBO_REUBICACION = 'en_limbo_reubicacion';
    case BAJA = 'baja';

    /**
     * Nombre legible para el usuario final en interfaz y reportes.
     */
    public function label(): string
    {
        return match ($this) {
            self::OPERATIVO => 'En Sala / Operativo',
            self::PRESTADO => 'Prestado',
            self::EN_REPARACION => 'En Reparación',
            self::EN_LIMBO_REUBICACION => 'En Limbo (Pendiente Reubicación)',
            self::BAJA => 'Basura Tecnológica / De Baja',
        };
    }

    /**
     * Clases Tailwind de estilo visual y color semántico.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::OPERATIVO => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::PRESTADO => 'bg-blue-100 text-blue-800 border-blue-300',
            self::EN_REPARACION => 'bg-amber-100 text-amber-800 border-amber-300',
            self::EN_LIMBO_REUBICACION => 'bg-purple-100 text-purple-800 border-purple-300',
            self::BAJA => 'bg-rose-100 text-rose-800 border-rose-300',
        };
    }

    /**
     * Determina si el elemento se encuentra físicamente disponible para asignaciones o préstamos.
     */
    public function esDisponible(): bool
    {
        return $this === self::OPERATIVO;
    }

    /**
     * Determina si el elemento se encuentra en estado terminal irreversible.
     */
    public function esTerminal(): bool
    {
        return $this === self::BAJA;
    }
}
