<?php

namespace App\Support;

/**
 * Ersetzt nur einfache Platzhalter {{ $name }} und {!! $name !!}.
 * Der übrige Text, auch HTML, bleibt stehen und wird nicht als PHP ausgeführt.
 */
class SafeTemplate
{
    public static function render(string $template, array $data): string
    {
        $replace = function (array $match) use ($data): string {
            $key = $match[1];
            if (!array_key_exists($key, $data) || is_array($data[$key]) || is_object($data[$key])) {
                return $match[0];
            }

            return e((string) $data[$key]);
        };

        $template = preg_replace_callback(
            '/\{\{\s*\$([A-Za-z_][A-Za-z0-9_]*)\s*\}\}/',
            $replace,
            $template
        );

        return preg_replace_callback(
            '/\{!!\s*\$([A-Za-z_][A-Za-z0-9_]*)\s*!!\}/',
            $replace,
            $template
        );
    }
}
