<?php
declare(strict_types=1);

/*
 * Recibe la encuesta que responde el paciente SIN iniciar sesión.
 *
 * Controles:
 *  - método POST y token de acceso del QR válido,
 *  - token anti-CSRF guardado en la sesión pública del navegador,
 *  - el documento debe existir y estar activo,
 *  - validación estricta de calificación, utilidad y comentario,
 *  - una sola respuesta por documento y por navegador (y un tope por sesión),
 *  - campo trampa (honeypot) contra envíos automáticos.
 *
 * No se guarda ningún dato personal.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../Servicios Comunes/Seguridad/AccesoPublico.php';
require_once __DIR__ . '/../../Modulo Documentacion/Modelo/DocumentoModelo.php';
require_once __DIR__ . '/../Modelo/EncuestaModelo.php';

const MAXIMO_ENCUESTAS_POR_SESION = 15;

/** Responde con JSON y corta la ejecución. */
function responder(bool $exito, string $mensaje, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode(['exito' => $exito, 'mensaje' => $mensaje]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.', 405);
}

// Sesión propia del paciente (separada de la del personal del hospital)
session_name('sigsm_publico');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// 1. Acceso: token del QR y token anti-CSRF
if (!AccesoPublico::tokenValido(is_string($_POST['t'] ?? null) ? $_POST['t'] : null)) {
    responder(false, 'Este enlace no es válido. Escanee nuevamente el código QR del hospital.', 403);
}
$csrfSesion = $_SESSION['csrf_encuesta'] ?? '';
$csrfRecibido = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrfRecibido)) {
    responder(false, 'La página caducó. Recárguela e intente nuevamente.', 403);
}

// 2. Campo trampa: las personas no lo ven ni lo completan
if (trim((string) ($_POST['sitio_web'] ?? '')) !== '') {
    responder(true, 'Gracias por su opinión.');
}

// 3. Documento evaluado
$textoId = trim((string) ($_POST['id_documento'] ?? ''));
$idDocumento = preg_match('/^[1-9]\d{0,9}$/', $textoId) ? (int) $textoId : 0;
$documento = $idDocumento > 0 ? (new DocumentoModelo())->buscarPorId($idDocumento) : false;
if (!$documento || !$documento['activo']) {
    responder(false, 'El documento ya no está disponible.', 404);
}

// 4. Límites por navegador
$respondidas = $_SESSION['encuestas_respondidas'] ?? [];
if (isset($respondidas[$idDocumento])) {
    responder(false, 'Ya registramos su opinión sobre este documento. ¡Gracias!');
}
if (count($respondidas) >= MAXIMO_ENCUESTAS_POR_SESION) {
    responder(false, 'Alcanzó el máximo de encuestas permitidas. ¡Gracias por su colaboración!', 429);
}

// 5. Validación de las respuestas
$textoCalificacion = trim((string) ($_POST['calificacion_acceso'] ?? ''));
if (!preg_match('/^[1-5]$/', $textoCalificacion)) {
    responder(false, 'Califique del 1 al 5 qué tan fácil fue acceder a la información.');
}
$calificacion = (int) $textoCalificacion;

$utilidad = (string) ($_POST['utilidad'] ?? '');
if (!in_array($utilidad, EncuestaModelo::OPCIONES_UTILIDAD, true)) {
    responder(false, 'Indique qué tan útil le resultó la información.');
}

// Comentario opcional: sin caracteres de control, espacios sobrantes ni exceso de largo
$comentario = (string) ($_POST['comentarios'] ?? '');
$comentario = str_replace(["\r\n", "\r"], "\n", $comentario);
$comentario = preg_replace('/[^\P{C}\n]+/u', '', $comentario);
if ($comentario === null) {
    responder(false, 'El comentario contiene caracteres no válidos.');
}
$comentario = trim($comentario);
if (mb_strlen($comentario, 'UTF-8') > EncuestaModelo::LARGO_MAXIMO_COMENTARIO) {
    responder(false, 'El comentario no puede superar los ' . EncuestaModelo::LARGO_MAXIMO_COMENTARIO . ' caracteres.');
}

// 6. Guardar
try {
    (new EncuestaModelo())->guardar($idDocumento, $calificacion, $utilidad, $comentario === '' ? null : $comentario);
} catch (Throwable $error) {
    error_log('[S.I.G.S.M. encuestas] ' . $error->getMessage());
    responder(false, 'No se pudo registrar su opinión. Intente nuevamente más tarde.', 500);
}

$respondidas[$idDocumento] = true;
$_SESSION['encuestas_respondidas'] = $respondidas;

responder(true, '¡Gracias por su opinión!');
