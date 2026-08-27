<?php
/**
 * cargar-documento.php
 *
 * Acceso exclusivo para usuarios con sesión activa y rol Administrador.
 * CRUD real de documentos, conectado a la base de datos.
 */
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

$auth = new AuthController();
$auth->protegerRuta('Administrador');

$paginaActual = 'cargar-documento';

$documentoModelo = new DocumentoModelo();
$documentos = $documentoModelo->listarTodos();
$categorias = $documentoModelo->obtenerCategorias();

$totalActivos = count(array_filter($documentos, fn($d) => (bool) $d['activo']));
$totalSuspendidos = count($documentos) - $totalActivos;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Hospital de Clínicas - Gestión de Documentos</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/cargar-documento.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <main class="main-wrapper">
        <div class="main-content">
        <div class="container">
                <div class="page-header">
                    <div class="header-text">
                        <h1>Documentos</h1>
                        <p>Gestione la documentación, protocolos y material informativo.</p>
                    </div>
                    <button class="btn btn-primary" type="button" onclick="abrirModalCreacion()">
                        <span class="material-symbols-outlined icon-sm">add</span>
                        + Cargar Nuevo Documento
                    </button>
                </div>

                <div class="bento-grid mb-lg">
                    <div class="bento-card">
                        <div class="accent-line bg-clinical-blue"></div>
                        <div class="bento-header">
                            <span class="bento-label">Documentos Activos</span>
                            <span class="material-symbols-outlined text-clinical-blue opacity-80">description</span>
                        </div>
                        <div class="bento-value"><?= (int) $totalActivos ?></div>
                    </div>
                    <div class="bento-card">
                        <div class="accent-line bg-error"></div>
                        <div class="bento-header">
                            <span class="bento-label">Documentos Suspendidos</span>
                            <span class="material-symbols-outlined text-error opacity-80">block</span>
                        </div>
                        <div class="bento-value"><?= (int) $totalSuspendidos ?></div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="search-box">
                            <span class="material-symbols-outlined search-icon">search</span>
                            <input class="search-input" id="buscadorDocumentos" placeholder="Buscar documentos..." type="text">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="tablaDocumentos">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Fecha de Carga</th>
                                    <th>Última Act.</th>
                                    <th>Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($documentos)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: var(--outline);">
                                        No hay documentos cargados todavía.
                                    </td>
                                </tr>
                                <?php endif; ?>

                                <?php foreach ($documentos as $doc): ?>
                                <tr>
                                    <td class="font-bold text-primary"><?= htmlspecialchars($doc['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($doc['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="text-secondary"><?= htmlspecialchars($doc['fecha_carga'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($doc['fecha_modificacion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if ($doc['activo']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-error">Suspendido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-group">
                                            <a
                                                class="icon-action text-secondary hover-blue"
                                                title="Ver documento"
                                                href="../<?= htmlspecialchars($doc['archivo_url'], ENT_QUOTES, 'UTF-8') ?>"
                                                target="_blank"
                                                rel="noopener">
                                                <span class="material-symbols-outlined icon-sm">visibility</span>
                                            </a>

                                            <button
                                                class="icon-action text-secondary hover-blue"
                                                title="Editar documento"
                                                onclick="abrirModalEdicion(
                                                    <?= (int) $doc['id_documento'] ?>,
                                                    '<?= htmlspecialchars(addslashes($doc['titulo']), ENT_QUOTES, 'UTF-8') ?>',
                                                    '<?= htmlspecialchars(addslashes($doc['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>',
                                                    <?= (int) $doc['id_categoria'] ?>
                                                )">
                                                <span class="material-symbols-outlined icon-sm">edit</span>
                                            </button>

                                            <?php if ($doc['activo']): ?>
                                                <button
                                                    class="icon-action text-secondary hover-red"
                                                    title="Suspender documento"
                                                    onclick="confirmarCambioEstado(<?= (int) $doc['id_documento'] ?>, 'suspender')">
                                                    <span class="material-symbols-outlined icon-sm">block</span>
                                                </button>
                                            <?php else: ?>
                                                <button
                                                    class="icon-action text-secondary hover-blue"
                                                    title="Reactivar documento"
                                                    onclick="confirmarCambioEstado(<?= (int) $doc['id_documento'] ?>, 'reactivar')">
                                                    <span class="material-symbols-outlined icon-sm">check_circle</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal de creación de documento -->
    <div class="modal-overlay hidden" id="uploadModal">
        <div class="modal-window">

            <div class="modal-header">
                <h3>Cargar Nuevo Documento</h3>
                <button class="btn-close-circle" type="button" onclick="cerrarModalCreacion()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="formCreacionDocumento" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mensaje-error" id="mensajeErrorCreacion"></div>

                    <div class="form-group">
                        <label class="form-label" for="crearTitulo">Título del Documento</label>
                        <input class="form-input" id="crearTitulo" name="titulo" placeholder="Ej: Protocolo de Higiene 2024" type="text" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="crearDescripcion">Descripción</label>
                        <textarea class="form-input" id="crearDescripcion" name="descripcion" rows="3" placeholder="Breve descripción del documento (opcional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="crearCategoria">Categoría</label>
                        <div class="select-wrapper">
                            <select class="form-input select-input" id="crearCategoria" name="id_categoria" required>
                                <option disabled selected value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= (int) $cat['id_categoria'] ?>">
                                        <?= htmlspecialchars($cat['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="material-symbols-outlined select-icon">expand_more</span>
                        </div>
                    </div>

                    <div class="form-group mt-sm">
                        <label class="form-label" for="crearArchivo">Archivo PDF</label>
                        <input class="form-input" id="crearArchivo" name="archivo" type="file" accept="application/pdf" required>
                        <span class="form-hint">Solo archivos PDF, tamaño máximo 10MB.</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn-secondary" type="button" onclick="cerrarModalCreacion()">Cancelar</button>
                    <button class="btn-primary-action" type="submit" id="btnGuardarCreacion">
                        <span class="material-symbols-outlined icon-sm">save</span>
                        Guardar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de edición de documento -->
    <div class="modal-overlay hidden" id="editModal">
        <div class="modal-window">

            <div class="modal-header">
                <h3>Editar Documento</h3>
                <button class="btn-close-circle" type="button" onclick="cerrarModalEdicion()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="formEdicionDocumento" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mensaje-error" id="mensajeErrorEdicion"></div>

                    <input type="hidden" id="editIdDocumento" name="id_documento">

                    <div class="form-group">
                        <label class="form-label" for="editTitulo">Título del Documento</label>
                        <input class="form-input" id="editTitulo" name="titulo" type="text" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editDescripcion">Descripción</label>
                        <textarea class="form-input" id="editDescripcion" name="descripcion" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editCategoria">Categoría</label>
                        <div class="select-wrapper">
                            <select class="form-input select-input" id="editCategoria" name="id_categoria" required>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= (int) $cat['id_categoria'] ?>">
                                        <?= htmlspecialchars($cat['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="material-symbols-outlined select-icon">expand_more</span>
                        </div>
                    </div>

                    <div class="form-group mt-sm">
                        <label class="form-label" for="editArchivo">Reemplazar archivo PDF</label>
                        <input class="form-input" id="editArchivo" name="archivo" type="file" accept="application/pdf">
                        <span class="form-hint">Dejar vacío para conservar el archivo actual.</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn-secondary" type="button" onclick="cerrarModalEdicion()">Cancelar</button>
                    <button class="btn-primary-action" type="submit" id="btnGuardarEdicion">
                        <span class="material-symbols-outlined icon-sm">save</span>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/cargar-documento.js"></script>
</body>
</html>
