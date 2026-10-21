<?php
/*
 * Centraliza el acceso del paciente que llega escaneando el código QR:
 *  - valida el token de la URL (sin sesión ni login),
 *  - construye la URL pública que se codifica dentro del QR.
 *
 * El paciente solo puede llegar al repositorio de documentos y al
 * controlador que entrega los PDF; ninguna otra parte del sistema acepta
 * este token.
 */

require_once __DIR__ . '/../Configuracion/configuracion.php';

class AccesoPublico
{
    /**
     * Comprueba que el token recibido coincide con el configurado.
     * hash_equals compara en tiempo constante (evita ataques de temporización).
     */
    public static function tokenValido(?string $tokenRecibido): bool
    {
        if ($tokenRecibido === null || $tokenRecibido === '') {
            return false;
        }
        return hash_equals(TOKEN_ACCESO_PUBLICO, $tokenRecibido);
    }

    /**
     * URL absoluta del repositorio público, con el token incluido.
     * Es el texto que se convierte en código QR.
     */
    public static function urlRepositorio(string $scriptActual): string
    {
        if (URL_BASE_PUBLICA !== '') {
            $base = rtrim(URL_BASE_PUBLICA, '/') . '/Modulo%20Documentacion/Vista';
        } else {
            $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            // Codifica cada tramo de la ruta (la carpeta tiene un espacio)
            $tramos = array_map('rawurlencode', explode('/', dirname($scriptActual)));
            $base = $esquema . '://' . $host . implode('/', $tramos);
        }

        return $base . '/repositorio-publico.php?t=' . rawurlencode(TOKEN_ACCESO_PUBLICO);
    }

    /**
     * Indica si la URL apunta a un servidor local (localhost / 127.0.0.1),
     * en cuyo caso el celular del paciente no podría abrirla.
     */
    public static function esUrlLocal(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        return in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);
    }
}
