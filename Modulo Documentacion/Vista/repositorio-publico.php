<?php

require_once __DIR__ . '/../../Servicios Comunes/Seguridad/AccesoPublico.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

// Cabeceras de seguridad: sin indexación, sin referer (el token va en la URL),
// sin iframes y con scripts/estilos solo propios (más Google Fonts).
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self'; frame-ancestors 'none'");
header('Cache-Control: private, max-age=0, must-revalidate');

$token = $_GET['t'] ?? '';

// Token ausente o incorrecto: mensaje genérico, sin revelar nada del sistema.
if (!is_string($token) || !AccesoPublico::tokenValido($token)) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="es"><head><meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no disponible</title>
    <link rel="stylesheet" href="assets/css/repositorio-publico.css"></head>
    <body class="pagina-publica"><main class="publico-contenedor">
        <p class="estado-vacio">Este enlace no es válido. Por favor, escanee nuevamente el código QR del hospital.</p>
    </main></body></html>
    <?php
    exit;
}

$documentos = (new DocumentoModelo())->listarActivos();

$categoriasConDocumentos = [];
foreach ($documentos as $documento) {
    $categoriasConDocumentos[$documento['id_categoria']] = $documento['nombre_categoria'];
}
asort($categoriasConDocumentos);

function esc(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="noindex, nofollow">
    <title>Documentación para pacientes</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/panel-documentacion.css">
    <link rel="stylesheet" href="assets/css/repositorio-publico.css">
</head>
<body class="pagina-publica">

    <header class="publico-encabezado">
        <span class="material-symbols-outlined">local_hospital</span>
        <div>
            <p class="publico-hospital">Hospital de Clínicas</p>
            <p class="publico-subtitulo">Documentación para pacientes</p>
        </div>
    </header>

    <main class="publico-contenedor">

        <section class="hero-section">
            <div class="search-wrapper">
                <div class="search-input-group">
                    <input class="search-input" id="buscadorDocumentos" autocomplete="off"
                           placeholder="Buscar documento (ej. ayuno, ecografía)..." type="search">
                    <button class="btn-search" type="button" aria-label="Buscar">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </div>
            </div>

            <div class="quick-filters" id="filtrosCategoria">
                <button class="filter-chip active" type="button" data-categoria="todas">Todas</button>
                <?php foreach ($categoriasConDocumentos as $idCategoria => $nombreCategoria): ?>
                    <button class="filter-chip" type="button" data-categoria="<?= (int) $idCategoria ?>">
                        <?= esc($nombreCategoria) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="results-grid" id="listaDocumentos">
            <?php foreach ($documentos as $documento): ?>
                <?php
                    $urlControlador = '../Controlador/servir-documento.php?id='
                        . (int) $documento['id_documento'] . '&t=' . rawurlencode($token);
                ?>
                <div class="doc-card"
                     data-categoria="<?= (int) $documento['id_categoria'] ?>"
                     data-busqueda="<?= esc(mb_strtolower($documento['titulo'] . ' ' . $documento['descripcion'] . ' ' . $documento['nombre_categoria'], 'UTF-8')) ?>">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge"><?= esc($documento['nombre_categoria']) ?></span>
                            <span class="material-symbols-outlined text-muted">description</span>
                        </div>
                        <h3 class="doc-title"><?= esc($documento['titulo']) ?></h3>
                        <p class="doc-desc"><?= esc($documento['descripcion']) ?></p>
                    </div>
                    <div class="doc-acciones">
                        <a class="btn-outline-primary" href="<?= esc($urlControlador . '&accion=ver') ?>"
                           target="_blank" rel="noopener noreferrer">
                            <span class="material-symbols-outlined icon-sm">visibility</span>
                            Ver PDF
                        </a>
                        <a class="btn-primary-solid" href="<?= esc($urlControlador . '&accion=descargar') ?>">
                            <span class="material-symbols-outlined icon-sm">download</span>
                            Descargar PDF
                        </a>
                    </div>
                    <a class="enlace-opinion"
                       href="<?= esc('../../Modulo%20Encuestas/Vista/encuesta.php?id=' . (int) $documento['id_documento'] . '&t=' . rawurlencode($token)) ?>">
                        <span class="material-symbols-outlined icon-sm">rate_review</span>
                        Dejar mi opinión sobre este documento
                    </a>
                </div>
            <?php endforeach; ?>
        </section>

        <p class="estado-vacio<?= count($documentos) > 0 ? ' hidden' : '' ?>" id="estadoVacio">
            <span class="material-symbols-outlined">folder_off</span>
            <span id="textoEstadoVacio">
                <?= count($documentos) > 0 ? 'No se encontraron documentos con esos criterios.' : 'Aún no hay documentos disponibles.' ?>
            </span>
        </p>

        <p class="publico-aviso">
            Esta información es de carácter general y no reemplaza la indicación de su médico tratante.
        </p>
    </main>

    <script src="assets/js/panel-documentacion.js"></script>
</body>
</html>
