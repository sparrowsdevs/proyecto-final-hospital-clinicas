<?php
/*
 * Resultados de Encuestas (dashboard).
 * Acceso para cualquier usuario con sesión activa (Administrador y Usuario Básico).
 * Las encuestas las responden los pacientes SIN iniciar sesión (son anónimas);
 * aquí se analizan los resultados, en total o por documento.
 */
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/EncuestaModelo.php';

$auth = new AuthController();
$auth->protegerRuta();

$paginaActual = 'encuestas-resultados';

date_default_timezone_set('America/Montevideo');

$modelo = new EncuestaModelo();
$documentos = $modelo->obtenerResumenPorDocumento();

// Filtro por documento (?doc=ID): solo se acepta uno que tenga respuestas
$idFiltro = null;
$textoDoc = is_string($_GET['doc'] ?? null) ? trim($_GET['doc']) : '';
if (preg_match('/^[1-9]\d{0,9}$/', $textoDoc)) {
    foreach ($documentos as $documento) {
        if ((int) $documento['id_documento'] === (int) $textoDoc) {
            $idFiltro = (int) $textoDoc;
            break;
        }
    }
}

$resumen = $modelo->obtenerResumen($idFiltro);
$distCalificacion = $modelo->obtenerDistribucionCalificacion($idFiltro);
$distUtilidad = $modelo->obtenerDistribucionUtilidad($idFiltro);
$comentarios = $modelo->obtenerComentariosRecientes($idFiltro, 10);

$etiquetasUtilidad = [
    'muy_util' => 'Sí, muy útil',
    'si' => 'Sí',
    'regular' => 'Regular',
    'no' => 'No',
    'para_nada' => 'Para nada útil',
];

/** Atajo para escapar texto antes de imprimirlo en HTML. */
function esc(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Porcentaje entero de $parte sobre $total (0 si no hay datos). */
function porcentaje(int $parte, int $total): int
{
    return $total > 0 ? (int) round($parte * 100 / $total) : 0;
}

/** Número con coma decimal (formato uruguayo). */
function decimal(?float $valor): string
{
    return $valor === null ? '–' : number_format($valor, 1, ',', '.');
}

$total = $resumen['total'];
$porcentajeUtiles = porcentaje($resumen['utiles'], $total);
$maximoCalificacion = max($distCalificacion) ?: 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Resultados de Encuestas - S.I.G.S.M.</title>

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/resultados-encuestas.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <div class="main-wrapper">
        <main class="dashboard-canvas">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Resultados de Encuestas</h1>
                    <p class="page-subtitle">Opinión anónima de los pacientes sobre los documentos del sistema.</p>
                </div>

                <?php if ($documentos !== []): ?>
                <form class="filtro-documento" method="get" action="resultados-encuestas.php">
                    <label for="filtroDoc">Documento</label>
                    <select class="form-control" id="filtroDoc" name="doc" onchange="this.form.submit()">
                        <option value="">Todos los documentos</option>
                        <?php foreach ($documentos as $documento): ?>
                            <option value="<?= (int) $documento['id_documento'] ?>"<?= (int) $documento['id_documento'] === $idFiltro ? ' selected' : '' ?>>
                                <?= esc($documento['titulo']) ?> (<?= (int) $documento['total'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="btn-filtrar" type="submit">Ver</button></noscript>
                </form>
                <?php endif; ?>
            </div>

            <?php if ($total === 0): ?>
            <section class="tarjeta-proximamente">
                <span class="material-symbols-outlined">analytics</span>
                <h2>Aún no hay respuestas</h2>
                <p>Cuando los pacientes completen la encuesta desde el repositorio de documentos, los resultados aparecerán aquí.</p>
            </section>
            <?php else: ?>

            <!-- Tarjetas resumen -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <h3>Respuestas recibidas</h3>
                    <p class="kpi-value"><?= $total ?></p>
                    <p class="kpi-trend"><?= $idFiltro === null ? 'en todos los documentos' : 'para este documento' ?></p>
                </div>
                <div class="kpi-card">
                    <h3>Facilidad de acceso (promedio)</h3>
                    <p class="kpi-value"><?= decimal($resumen['promedio']) ?><span class="kpi-unidad"> / 5</span></p>
                    <p class="kpi-trend">1 = muy difícil · 5 = muy fácil</p>
                </div>
                <div class="kpi-card">
                    <h3>Consideran útil la información</h3>
                    <p class="kpi-value"><?= $porcentajeUtiles ?><span class="kpi-unidad">%</span></p>
                    <p class="kpi-trend">respondieron «Sí, muy útil» o «Sí»</p>
                </div>
            </div>

            <div class="graficos-grid">
                <!-- Calificación -->
                <section class="panel-resultados">
                    <h2 class="panel-titulo">Facilidad de acceso a la información</h2>
                    <ul class="barras">
                        <?php for ($n = 5; $n >= 1; $n--): ?>
                            <li class="barra-fila">
                                <span class="barra-etiqueta"><?= $n ?> <span class="material-symbols-outlined barra-estrella">star</span></span>
                                <span class="barra-pista"><span class="barra-relleno" style="width: <?= porcentaje($distCalificacion[$n], $maximoCalificacion) ?>%"></span></span>
                                <span class="barra-valor"><?= $distCalificacion[$n] ?> <small>(<?= porcentaje($distCalificacion[$n], $total) ?>%)</small></span>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </section>

                <!-- Utilidad -->
                <section class="panel-resultados">
                    <h2 class="panel-titulo">Utilidad de la información</h2>
                    <ul class="barras">
                        <?php foreach ($etiquetasUtilidad as $clave => $etiqueta): ?>
                            <li class="barra-fila">
                                <span class="barra-etiqueta barra-etiqueta-ancha"><?= esc($etiqueta) ?></span>
                                <span class="barra-pista"><span class="barra-relleno relleno-<?= esc($clave) ?>" style="width: <?= porcentaje($distUtilidad[$clave], $total) ?>%"></span></span>
                                <span class="barra-valor"><?= $distUtilidad[$clave] ?> <small>(<?= porcentaje($distUtilidad[$clave], $total) ?>%)</small></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            </div>

            <?php if ($idFiltro === null): ?>
            <!-- Por documento -->
            <section class="panel-resultados">
                <h2 class="panel-titulo">Resultados por documento</h2>
                <div class="tabla-contenedor">
                    <table class="tabla-resultados">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Categoría</th>
                                <th class="numerica">Respuestas</th>
                                <th class="numerica">Facilidad (prom.)</th>
                                <th class="numerica">Útil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $documento): ?>
                                <tr>
                                    <td>
                                        <a class="enlace-doc" href="?doc=<?= (int) $documento['id_documento'] ?>"><?= esc($documento['titulo']) ?></a>
                                        <?php if (!$documento['activo']): ?><span class="etiqueta-suspendido">Suspendido</span><?php endif; ?>
                                    </td>
                                    <td><?= esc($documento['nombre_categoria']) ?></td>
                                    <td class="numerica"><?= (int) $documento['total'] ?></td>
                                    <td class="numerica"><?= decimal((float) $documento['promedio']) ?></td>
                                    <td class="numerica"><?= porcentaje((int) $documento['utiles'], (int) $documento['total']) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Comentarios -->
            <section class="panel-resultados">
                <h2 class="panel-titulo">Comentarios recientes</h2>
                <?php if ($comentarios === []): ?>
                    <p class="sin-comentarios">Todavía no hay comentarios escritos.</p>
                <?php else: ?>
                    <ul class="lista-comentarios">
                        <?php foreach ($comentarios as $comentario): ?>
                            <li class="comentario">
                                <p class="comentario-texto"><?= nl2br(esc($comentario['comentario'])) ?></p>
                                <p class="comentario-meta">
                                    <?= esc($comentario['titulo']) ?> ·
                                    Facilidad <?= (int) $comentario['calificacion_acceso'] ?>/5 ·
                                    <?= esc($etiquetasUtilidad[$comentario['utilidad']] ?? '') ?> ·
                                    <?= esc((new DateTime($comentario['fecha_hora']))->format('d/m/Y H:i')) ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <?php endif; ?>

        </main>
    </div>

</body>
</html>
