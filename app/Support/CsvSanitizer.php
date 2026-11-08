<?php

namespace App\Support;

class CsvSanitizer
{
    /**
     * Caracteres que activan la ejecución de fórmulas en hojas de cálculo (CWE-1236 / CSV Injection).
     */
    protected const DANGEROUS_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Sanitiza un campo individual anteponiendo comilla simple si inicia con un prefijo peligroso.
     */
    public static function sanitizeField(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        $firstChar = $value[0];
        if (in_array($firstChar, self::DANGEROUS_PREFIXES, true)) {
            return "'" . $value;
        }

        // Si comienza con espacios y luego con un prefijo de fórmula
        $trimmedSpaces = ltrim($value, " ");
        if ($trimmedSpaces !== '' && in_array($trimmedSpaces[0], self::DANGEROUS_PREFIXES, true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Sanitiza una fila completa de datos CSV.
     *
     * @param array<int|string, mixed> $row
     * @return array<int|string, mixed>
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitizeField'], $row);
    }
}
