<?php
/*
 * Acceso para cualquier usuario con sesión activa (todos los roles
 * autenticados pueden consultar los documentos).
 * Si no hay sesión, se lo redirige al login.
 */
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';
require_once __DIR__ . '/../../Servicios Comunes/Seguridad/AccesoPublico.php';

$auth = new AuthController();
$auth->protegerRuta();

$paginaActual = 'documentacion';

// Documentos activos cargados por el Administrador (borrado lógico: activo = TRUE)
$documentoModelo = new DocumentoModelo();
$documentos = $documentoModelo->listarActivos();

// URL que se codifica en el QR (repositorio público + token) y aviso si es local
$urlQr = AccesoPublico::urlRepositorio($_SERVER['SCRIPT_NAME']);
$qrEsLocal = AccesoPublico::esUrlLocal($urlQr);
$servidorQr = parse_url($urlQr, PHP_URL_HOST) . (parse_url($urlQr, PHP_URL_PORT) ? ':' . parse_url($urlQr, PHP_URL_PORT) : '');

// Categorías que realmente tienen documentos (para no mostrar chips vacíos)
$categoriasConDocumentos = [];
foreach ($documentos as $documento) {
    $categoriasConDocumentos[$documento['id_categoria']] = $documento['nombre_categoria'];
}
asort($categoriasConDocumentos);

/** Atajo para escapar texto antes de imprimirlo en HTML. */
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
    <title>Panel de Documentación</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/panel-documentacion.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <main class="main-wrapper">
        <div class="main-content">
            <div class="container">

            <section class="hero-section">
                <h1 class="page-title">Panel de Documentación</h1>

                <button class="btn-qr" id="btnMostrarQr" type="button">
                    <span class="material-symbols-outlined">qr_code_2</span>
                    Mostrar QR a los pacientes
                </button>

                <div class="search-wrapper">
                    <div class="search-input-group">
                        <input class="search-input" id="buscadorDocumentos" autocomplete="off"
                               placeholder="Buscar documento explicativo o preparación médica..." type="text">
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
                        $urlControlador = '../Controlador/servir-documento.php?id=' . (int) $documento['id_documento'];
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
                            <p class="doc-fecha">
                                Cargado el <?= esc(date('d/m/Y', strtotime($documento['fecha_carga']))) ?>
                            </p>
                        </div>
                        <div class="doc-acciones">
                            <a class="btn-outline-primary"
                               href="<?= esc($urlControlador . '&accion=ver') ?>" target="_blank" rel="noopener">
                                <span class="material-symbols-outlined icon-sm">visibility</span>
                                Ver PDF
                            </a>
                            <a class="btn-primary-solid"
                               href="<?= esc($urlControlador . '&accion=descargar') ?>">
                                <span class="material-symbols-outlined icon-sm">download</span>
                                Descargar PDF
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Estado vacío: sin documentos cargados -->
            <p class="estado-vacio<?= count($documentos) > 0 ? ' hidden' : '' ?>" id="estadoVacio">
                <span class="material-symbols-outlined">folder_off</span>
                <span id="textoEstadoVacio">
                    <?= count($documentos) > 0 ? 'No se encontraron documentos con esos criterios.' : 'Aún no hay documentos cargados.' ?>
                </span>
            </p>

            </div>
        </div>
    </main>

    <!-- Modal: código QR para los pacientes (solo personal con sesión iniciada) -->
    <div class="modal-overlay hidden" id="modalQr" role="dialog" aria-modal="true" aria-labelledby="tituloModalQr"
         data-url="<?= esc($urlQr) ?>">
        <div class="modal-container">
            <div class="modal-header no-imprimir">
                <h2 class="modal-title" id="tituloModalQr">Código QR para pacientes</h2>
                <button class="btn-close" id="btnCerrarQr" type="button" aria-label="Cerrar">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="qr-titulo-impresion">Hospital de Clínicas</p>
                <div class="qr-lienzo" id="lienzoQr"></div>
                <p class="qr-instruccion">Escanee este código con la cámara de su celular para ver y descargar los documentos de preparación e indicaciones.</p>

                <?php if ($qrEsLocal): ?>
                    <p class="qr-aviso no-imprimir">
                        El QR apunta a <strong><?= esc($servidorQr) ?></strong>, una dirección local que los celulares
                        no pueden abrir. Configure <code>URL_BASE_PUBLICA</code> en
                        <code>Servicios Comunes/Configuracion/configuracion.php</code>.
                    </p>
                <?php else: ?>
                    <p class="qr-servidor no-imprimir">Servidor: <?= esc($servidorQr) ?></p>
                <?php endif; ?>

                <div class="modal-actions no-imprimir">
                    <button class="btn-primary flex-1" id="btnImprimirQr" type="button">Imprimir</button>
                    <button class="btn-secondary flex-1" id="btnFinalizarQr" type="button">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/qrcode.js"></script>
    <script src="assets/js/qr-pacientes.js"></script>
    <script src="assets/js/panel-documentacion.js"></script>
</body>
</html>
