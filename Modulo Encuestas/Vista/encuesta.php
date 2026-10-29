<?php
/*
 * Encuesta de satisfacción sobre un documento.
 * Pública: la responde el paciente SIN iniciar sesión, llegando desde el
 * repositorio de documentos (mismo token que el código QR).
 */

require_once __DIR__ . '/../../Servicios Comunes/Seguridad/AccesoPublico.php';
require_once __DIR__ . '/../../Modulo Documentacion/Modelo/DocumentoModelo.php';
require_once __DIR__ . '/../Modelo/EncuestaModelo.php';

// Cabeceras de seguridad (el token va en la URL): sin indexación ni referer,
// sin iframes y con scripts/estilos solo propios (más Google Fonts).
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self'; frame-ancestors 'none'");
header('Cache-Control: no-store');

function esc(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Página de error genérica, sin revelar nada del sistema. */
function paginaError(int $codigoHttp, string $mensaje): void
{
    http_response_code($codigoHttp);
    ?>
    <!DOCTYPE html>
    <html lang="es"><head><meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta no disponible</title>
    <link rel="stylesheet" href="assets/css/encuestas.css">
    <link rel="stylesheet" href="assets/css/encuesta-publica.css"></head>
    <body class="encuesta-publica"><main class="publico-contenedor">
        <p class="estado-aviso"><?= esc($mensaje) ?></p>
    </main></body></html>
    <?php
    exit;
}

$token = $_GET['t'] ?? '';
if (!is_string($token) || !AccesoPublico::tokenValido($token)) {
    paginaError(403, 'Este enlace no es válido. Por favor, escanee nuevamente el código QR del hospital.');
}

$textoId = is_string($_GET['id'] ?? null) ? trim($_GET['id']) : '';
$idDocumento = preg_match('/^[1-9]\d{0,9}$/', $textoId) ? (int) $textoId : 0;
$documento = $idDocumento > 0 ? (new DocumentoModelo())->buscarPorId($idDocumento) : false;
if (!$documento || !$documento['activo']) {
    paginaError(404, 'El documento que desea evaluar no está disponible.');
}

// Sesión propia del paciente (separada de la del personal) para el token anti-CSRF
session_name('sigsm_publico');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['csrf_encuesta'])) {
    $_SESSION['csrf_encuesta'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_encuesta'];
$yaRespondida = isset(($_SESSION['encuestas_respondidas'] ?? [])[$idDocumento]);

$urlRepositorio = '../../Modulo%20Documentacion/Vista/repositorio-publico.php?t=' . rawurlencode($token);
$largoMaximo = EncuestaModelo::LARGO_MAXIMO_COMENTARIO;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="noindex, nofollow">
    <title>Encuesta de Satisfacción - Hospital de Clínicas</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/encuestas.css">
    <link rel="stylesheet" href="assets/css/encuesta-publica.css">
</head>
<body class="encuesta-publica">

    <header class="publico-encabezado">
        <span class="material-symbols-outlined">local_hospital</span>
        <div>
            <p class="publico-hospital">Hospital de Clínicas</p>
            <p class="publico-subtitulo">Encuesta de satisfacción</p>
        </div>
    </header>

    <main class="publico-contenedor">

        <div class="page-header text-center-mobile">
            <h1>Encuesta de Satisfacción del Usuario</h1>
            <p>Su opinión es fundamental para mejorar la calidad de nuestros servicios en el Hospital de Clínicas.</p>
            <p class="documento-evaluado">
                <span class="material-symbols-outlined">description</span>
                <span>Documento: <strong><?= esc($documento['titulo']) ?></strong></span>
            </p>
        </div>

        <div class="survey-card" id="tarjetaEncuesta"<?= $yaRespondida ? ' hidden' : '' ?>>
            <div class="accent-bar-left"></div>

            <form id="surveyForm" action="../Controlador/procesar-encuesta.php" method="POST" novalidate>
                <input type="hidden" name="t" value="<?= esc($token) ?>">
                <input type="hidden" name="id_documento" value="<?= (int) $idDocumento ?>">
                <input type="hidden" name="csrf" value="<?= esc($csrf) ?>">
                <!-- Campo trampa: las personas no lo ven -->
                <div class="campo-trampa" aria-hidden="true">
                    <label for="sitio_web">No complete este campo</label>
                    <input id="sitio_web" name="sitio_web" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-section">
                    <label class="question-title">¿Qué tan fácil fue acceder a la información?</label>
                    <p class="question-subtitle">Califícanos del 1 al 5, donde 5 es muy fácil.</p>

                    <div class="star-rating">
                        <input id="star5" name="calificacion_acceso" type="radio" value="5">
                        <label for="star5" title="5 estrellas"><span class="material-symbols-outlined filled">star</span></label>

                        <input id="star4" name="calificacion_acceso" type="radio" value="4">
                        <label for="star4" title="4 estrellas"><span class="material-symbols-outlined filled">star</span></label>

                        <input id="star3" name="calificacion_acceso" type="radio" value="3">
                        <label for="star3" title="3 estrellas"><span class="material-symbols-outlined filled">star</span></label>

                        <input id="star2" name="calificacion_acceso" type="radio" value="2">
                        <label for="star2" title="2 estrellas"><span class="material-symbols-outlined filled">star</span></label>

                        <input id="star1" name="calificacion_acceso" type="radio" value="1">
                        <label for="star1" title="1 estrella"><span class="material-symbols-outlined filled">star</span></label>
                    </div>
                </div>

                <hr class="divider">

                <div class="form-section">
                    <label class="question-title mb-md">¿La información proporcionada fue útil para su preparación/tratamiento?</label>

                    <div class="radio-group">
                        <label class="radio-option">
                            <input name="utilidad" type="radio" value="muy_util">
                            <span class="radio-text">Sí, muy útil</span>
                        </label>
                        <label class="radio-option">
                            <input name="utilidad" type="radio" value="si">
                            <span class="radio-text">Sí</span>
                        </label>
                        <label class="radio-option">
                            <input name="utilidad" type="radio" value="regular">
                            <span class="radio-text">Regular</span>
                        </label>
                        <label class="radio-option">
                            <input name="utilidad" type="radio" value="no">
                            <span class="radio-text">No</span>
                        </label>
                        <label class="radio-option">
                            <input name="utilidad" type="radio" value="para_nada">
                            <span class="radio-text">Para nada útil</span>
                        </label>
                    </div>
                </div>

                <hr class="divider">

                <div class="form-section">
                    <label class="question-title" for="comments">¿Tiene algún comentario o sugerencia adicional?</label>
                    <p class="question-subtitle">Opcional. Nos ayuda a entender mejor su experiencia.</p>

                    <textarea class="form-textarea" id="comments" name="comentarios" rows="5"
                              maxlength="<?= (int) $largoMaximo ?>" placeholder="Escriba sus comentarios aquí..."></textarea>
                    <div class="comentario-pie">
                        <p class="aviso-privacidad">
                            <span class="material-symbols-outlined">lock</span>
                            Por favor, no escriba datos personales ni información médica.
                        </p>
                        <p class="contador-caracteres"><span id="contadorComentario">0</span>/<?= (int) $largoMaximo ?></p>
                    </div>
                </div>

                <p class="mensaje-error" id="mensajeError" role="alert" hidden></p>

                <div class="form-actions">
                    <button class="btn btn-submit" type="submit" id="botonEnviar">
                        Enviar Encuesta
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </div>
            </form>
        </div>

        <section class="survey-card tarjeta-gracias" id="tarjetaGracias"<?= $yaRespondida ? '' : ' hidden' ?> aria-live="polite">
            <div class="accent-bar-left"></div>
            <span class="material-symbols-outlined icono-gracias">check_circle</span>
            <h2 id="tituloGracias"><?= $yaRespondida ? 'Ya registramos su opinión' : '¡Gracias por su opinión!' ?></h2>
            <p>Sus respuestas nos ayudan a mejorar la información que brindamos a los pacientes.</p>
            <a class="btn btn-submit enlace-volver" href="<?= esc($urlRepositorio) ?>">
                <span class="material-symbols-outlined">arrow_back</span>
                Volver a los documentos
            </a>
        </section>

        <footer class="minimal-footer">
            <p>© 2026 Hospital de Clínicas "Dr. Manuel Quintela". Todos los derechos reservados.</p>
            <p class="nota-anonima">La encuesta es anónima: no guardamos su nombre, cédula ni otros datos personales.</p>
        </footer>

    </main>

    <script src="assets/js/encuesta.js"></script>
</body>
</html>
