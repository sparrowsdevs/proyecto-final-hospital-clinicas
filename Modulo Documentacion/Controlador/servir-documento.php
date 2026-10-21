<?php
/*
 * Controlador que entrega los archivos PDF (ver o descargar).
 * 
 * Los PDF NO se sirven directamente desde /uploads (esa carpeta está
 * bloqueada por .htaccess). Todo acceso pasa por acá, donde se verifica:
 *   - que quien pide tenga una sesión activa (personal del hospital)
 *     O un token válido (paciente que escaneó el QR);
 *   - que el documento exista y esté activo (los suspendidos no se entregan,
 *     salvo al Administrador);
 *   - que el nombre de archivo sea el esperado (evita recorrer directorios).
 *
 * Parámetros GET: id (obligatorio), accion = ver | descargar, t = token (pacientes).
 */

require_once __DIR__ . '/../../Servicios Comunes/Seguridad/AccesoPublico.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

/** Corta la ejecución con un mensaje de texto plano y el código HTTP indicado. */
function denegar(int $codigoHttp, string $mensaje): void
{
    http_response_code($codigoHttp);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit;
}

// 1. Validar parámetros
$idDocumento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($idDocumento === false || $idDocumento === null || $idDocumento < 1) {
    denegar(400, 'Solicitud inválida.');
}
$comoDescarga = (($_GET['accion'] ?? 'ver') === 'descargar');

// 2. Control de acceso: token del QR o sesión del personal
$accesoPorToken = AccesoPublico::tokenValido($_GET['t'] ?? null);
$esAdministrador = false;

if (!$accesoPorToken) {
    require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
    $auth = new AuthController();

    if (!$auth->sesionActiva()) {
        denegar(403, 'Acceso no autorizado.');
    }
    $esAdministrador = $auth->tieneRol('Administrador');
    session_write_close(); // libera el bloqueo de sesión mientras se envía el archivo
}

// 3. Buscar el documento (consulta preparada dentro del modelo)
$documento = (new DocumentoModelo())->buscarPorId($idDocumento);
if (!$documento || (!$documento['activo'] && !$esAdministrador)) {
    denegar(404, 'Documento no disponible.');
}

// 4. Resolver la ruta física de forma segura
$nombreArchivo = basename((string) $documento['archivo_url']);
if (!preg_match('/^doc_[a-f0-9]+\.pdf$/', $nombreArchivo)) {
    denegar(404, 'Documento no disponible.');
}

$carpetaPermitida = realpath(__DIR__ . '/../uploads/documentos');
$rutaArchivo = $carpetaPermitida ? realpath($carpetaPermitida . DIRECTORY_SEPARATOR . $nombreArchivo) : false;

if ($rutaArchivo === false
    || strpos($rutaArchivo, $carpetaPermitida . DIRECTORY_SEPARATOR) !== 0
    || !is_file($rutaArchivo)) {
    denegar(404, 'Archivo no encontrado.');
}

// 5. Enviar el PDF
$nombreVisible = preg_replace('/[^\p{L}\p{N} ._-]/u', '', (string) $documento['titulo']);
$nombreVisible = trim($nombreVisible) !== '' ? trim($nombreVisible) : 'documento';
$nombreAscii = preg_replace('/[^A-Za-z0-9 ._-]/', '_', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombreVisible) ?: 'documento');

header('Content-Type: application/pdf');
header('Content-Disposition: ' . ($comoDescarga ? 'attachment' : 'inline')
    . '; filename="' . $nombreAscii . '.pdf"; filename*=UTF-8\'\'' . rawurlencode($nombreVisible . '.pdf'));
header('Content-Length: ' . filesize($rutaArchivo));
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($rutaArchivo);
exit;