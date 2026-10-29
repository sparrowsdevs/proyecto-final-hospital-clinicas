<?php
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/TrasladoModelo.php';

$auth = new AuthController();
$auth->protegerRuta();

$paginaActual = 'traslado-panel';

date_default_timezone_set('America/Montevideo');

// Datos reales del panel
$trasladoModelo = new TrasladoModelo();
$traslados = $trasladoModelo->listarTraslados();
$resumen = $trasladoModelo->obtenerResumen($traslados, $trasladoModelo->contarVehiculos());
$estados = $trasladoModelo->obtenerEstados();
$vehiculos = $trasladoModelo->obtenerVehiculos();
$usuariosActivos = $trasladoModelo->obtenerUsuariosActivos();

/** Atajo para escapar texto antes de imprimirlo en HTML. */
function esc(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Clases CSS de la insignia (badge) y del punto según el estado. */
function claseEstado(string $estado): array
{
    return match ($estado) {
        TrasladoModelo::ESTADO_VEHICULO_ASIGNADO => ['badge-blue', 'bg-blue', false],
        TrasladoModelo::ESTADO_EN_TRANSITO => ['badge-blue', 'bg-blue', true],
        TrasladoModelo::ESTADO_COMPLETADO => ['badge-success', 'bg-success', false],
        TrasladoModelo::ESTADO_CANCELADO => ['badge-error', 'bg-error', false],
        default => ['badge-gray', 'bg-gray', false],
    };
}

/** "Apellido, Nombre" o guion si no hay persona asignada. */
function nombrePersona(?string $nombre, ?string $apellido): string
{
    return $nombre === null ? '' : trim($apellido . ', ' . $nombre);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Panel de Gestión de Traslados - S.I.G.S.M.</title>
    
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/panel-gestion.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <div class="main-wrapper">

        <main class="dashboard-canvas">
            
            <div class="page-header">
                <div>
                    <h1 class="page-title">Panel de Gestión de Traslados</h1>
                    <p class="page-subtitle">Registro y gestión de los traslados no urgentes.</p>
                </div>
            </div>

            <div class="kpi-grid">
                
                <div class="kpi-card hover-primary">
                    <span class="material-symbols-outlined bg-icon">swap_horiz</span>
                    <div class="kpi-header">
                        <h3>Total de Traslados</h3>
                        <div class="kpi-icon-container bg-blue-light">
                            <span class="material-symbols-outlined">swap_horiz</span>
                        </div>
                    </div>
                    <div class="kpi-body">
                        <p class="kpi-value"><?= (int) $resumen['total'] ?></p>
                        <p class="kpi-trend text-muted"><?= $resumen['total'] === 0 ? 'Sin traslados registrados' : ($resumen['cancelados'] > 0 ? (int) $resumen['cancelados'] . ' cancelado(s) no incluidos' : 'Sin cancelados') ?></p>
                    </div>
                </div>

                <div class="kpi-card hover-primary">
                    <span class="material-symbols-outlined bg-icon">directions_car</span>
                    <div class="kpi-header">
                        <h3>Vehículos Disponibles</h3>
                        <div class="kpi-icon-container bg-tertiary">
                            <span class="material-symbols-outlined">directions_car</span>
                        </div>
                    </div>
                    <div class="kpi-body">
                        <p class="kpi-value"><?= (int) $resumen['vehiculos_disponibles'] ?></p>
                        <p class="kpi-trend text-muted"><?= $resumen['vehiculos_total'] === 0 ? 'Sin vehículos registrados' : 'de ' . (int) $resumen['vehiculos_total'] . ' en la flota' ?></p>
                    </div>
                </div>

                <div class="kpi-card hover-primary">
                    <span class="material-symbols-outlined bg-icon">person</span>
                    <div class="kpi-header">
                        <h3>Choferes Activos</h3>
                        <div class="kpi-icon-container bg-secondary">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                    </div>
                    <div class="kpi-body">
                        <p class="kpi-value"><?= (int) $resumen['choferes_activos'] ?></p>
                        <p class="kpi-trend text-muted"><?= $resumen['choferes_activos'] === 0 ? 'Sin traslados asignados' : 'con traslados asignados o en curso' ?></p>
                    </div>
                </div>

                <div class="kpi-card hover-error">
                    <span class="material-symbols-outlined bg-icon text-error">pending_actions</span>
                    <div class="kpi-header">
                        <h3>Solicitudes Pendientes</h3>
                        <div class="kpi-icon-container bg-error-light">
                            <span class="material-symbols-outlined">pending_actions</span>
                        </div>
                    </div>
                    <div class="kpi-body">
                        <p class="kpi-value"><?= (int) $resumen['pendientes'] ?></p>
                        <p class="kpi-trend text-muted"><?= $resumen['pendientes'] === 0 ? 'Sin solicitudes pendientes' : 'esperando asignación de vehículo' ?></p>
                    </div>
                </div>

            </div>

            <section class="table-section">
                <div class="table-toolbar">
                    <h2 class="table-title">Traslados</h2>
                    <div class="table-actions">
                        <button class="btn btn-outline-sm" id="btnFiltrar" type="button" aria-expanded="false" aria-controls="barraFiltros">
                            <span class="material-symbols-outlined text-sm">filter_list</span> Filtrar
                        </button>
                        <button class="btn btn-outline-sm" id="btnExportar" type="button">
                            <span class="material-symbols-outlined text-sm">download</span> Exportar
                        </button>
                    </div>
                </div>

                <div class="barra-filtros" id="barraFiltros" hidden>
                    <input class="form-control" id="filtroBusqueda" type="search" maxlength="60"
                           placeholder="Buscar por paciente, cédula, origen, destino o vehículo..." aria-label="Buscar traslados">
                    <select class="form-control" id="filtroEstado" aria-label="Filtrar por estado">
                        <option value="activos">Activos (sin cancelados)</option>
                        <option value="todos">Todos los estados</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= esc($estado) ?>"><?= esc($estado) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Paciente / Carga</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Fecha prevista</th>
                                <th>Vehículo</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTraslados">
                            <?php foreach ($traslados as $traslado):
                                $estado = (string) $traslado['estado'];
                                [$claseBadge, $clasePunto, $pulso] = claseEstado($estado);
                                $finalizado = in_array($estado, [TrasladoModelo::ESTADO_COMPLETADO, TrasladoModelo::ESTADO_CANCELADO], true);
                                $codigo = sprintf('TR-%05d', (int) $traslado['id_traslado']);
                                $paciente = trim($traslado['paciente_nombre'] . ' ' . $traslado['paciente_apellido']);
                                $chofer = nombrePersona($traslado['chofer_nombre'], $traslado['chofer_apellido']);
                                $enfermero = nombrePersona($traslado['enfermero_nombre'], $traslado['enfermero_apellido']);
                                $horaPrevista = new DateTime($traslado['hora_prevista']);
                                $busqueda = mb_strtolower(implode(' ', [
                                    $codigo, $paciente, $traslado['ci_paciente'], $traslado['elemento'],
                                    $traslado['origen'], $traslado['destino'], $traslado['matricula'] ?? '', $estado,
                                ]), 'UTF-8');
                            ?>
                            <tr class="fila-traslado<?= $finalizado ? ' row-completed' : '' ?>"
                                data-id="<?= (int) $traslado['id_traslado'] ?>"
                                data-codigo="<?= esc($codigo) ?>"
                                data-estado="<?= esc($estado) ?>"
                                data-busqueda="<?= esc($busqueda) ?>"
                                data-paciente="<?= esc($paciente) ?>"
                                data-ci="<?= esc($traslado['ci_paciente']) ?>"
                                data-elemento="<?= esc($traslado['elemento']) ?>"
                                data-id-elemento="<?= (int) $traslado['id_tipo_elemento'] ?>"
                                data-origen="<?= esc($traslado['origen']) ?>"
                                data-destino="<?= esc($traslado['destino']) ?>"
                                data-hora="<?= esc($horaPrevista->format('Y-m-d\TH:i')) ?>"
                                data-hora-texto="<?= esc($horaPrevista->format('d/m/Y H:i')) ?>"
                                data-vehiculo="<?= (int) $traslado['id_vehiculo'] ?>"
                                data-matricula="<?= esc($traslado['matricula'] ?? '') ?>"
                                data-chofer="<?= (int) $traslado['id_chofer'] ?>"
                                data-chofer-texto="<?= esc($chofer) ?>"
                                data-enfermero="<?= (int) $traslado['id_enfermero'] ?>"
                                data-transiciones="<?= esc(implode('|', $trasladoModelo->obtenerTransiciones($estado))) ?>">
                                <td class="td-title"><?= esc($codigo) ?></td>
                                <td>
                                    <div class="td-flex">
                                        <div>
                                            <p class="td-title"><?= esc($paciente) ?></p>
                                            <p class="td-subtitle">CI <?= esc($traslado['ci_paciente']) ?> · <?= esc($traslado['elemento']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($traslado['origen']) ?></td>
                                <td><?= esc($traslado['destino']) ?></td>
                                <td><?= esc($horaPrevista->format('d/m/Y H:i')) ?></td>
                                <td>
                                    <?php if ($traslado['matricula'] !== null): ?>
                                        <p class="td-title"><?= esc($traslado['matricula']) ?></p>
                                        <p class="td-subtitle"><?= esc($traslado['tipo_vehiculo']) ?></p>
                                    <?php else: ?>
                                        <span class="text-muted">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $claseBadge ?>">
                                        <span class="dot <?= $clasePunto ?><?= $pulso ? ' animate-pulse' : '' ?>"></span>
                                        <?= esc($estado) ?>
                                    </span>
                                </td>
                                <td class="text-right acciones-fila">
                                    <?php if ($estado === TrasladoModelo::ESTADO_CANCELADO): ?>
                                        <button class="icon-btn-small" type="button" data-accion="reactivar" title="Reactivar traslado" aria-label="Reactivar <?= esc($codigo) ?>">
                                            <span class="material-symbols-outlined">restore</span>
                                        </button>
                                    <?php elseif (!$finalizado): ?>
                                        <button class="icon-btn-small" type="button" data-accion="editar" title="Editar traslado" aria-label="Editar <?= esc($codigo) ?>">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button class="icon-btn-small icon-btn-peligro" type="button" data-accion="cancelar" title="Cancelar traslado" aria-label="Cancelar <?= esc($codigo) ?>">
                                            <span class="material-symbols-outlined">cancel</span>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="fila-vacia" <?= $traslados === [] ? '' : 'hidden' ?>>
                                <td class="tabla-vacia" colspan="8">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                    <p id="textoVacio"><?= $traslados === [] ? 'Aún no hay traslados registrados.' : 'Ningún traslado coincide con el filtro.' ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <p class="text-muted text-sm" id="textoConteo">Mostrando 0 traslados</p>
                    <div class="pagination-controls">
                        <button class="btn-page" id="btnAnterior" type="button" aria-label="Página anterior" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="btn-page" id="btnSiguiente" type="button" aria-label="Página siguiente" disabled><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Mensajes -->
    <div class="aviso-flotante" id="aviso" role="status" aria-live="polite" hidden></div>

    <!-- Modal: editar traslado -->
    <div class="modal-fondo" id="modalEditar" hidden>
        <div class="modal-caja" role="dialog" aria-modal="true" aria-labelledby="tituloEditar">
            <div class="modal-cabecera">
                <h2 id="tituloEditar">Editar traslado</h2>
                <button class="icon-btn-small" type="button" data-cerrar aria-label="Cerrar"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="formEditar" novalidate>
                <input type="hidden" id="editId" name="id_traslado">
                <p class="modal-resumen" id="editResumen"></p>

                <div class="form-group">
                    <label class="form-label" for="editHora">Fecha y hora prevista</label>
                    <input class="form-control" type="datetime-local" id="editHora" name="hora_prevista" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editEstado">Estado</label>
                    <select class="form-control" id="editEstado" name="estado"></select>
                    <p class="ayuda-campo" id="ayudaEstado"></p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editVehiculo">Vehículo</label>
                    <select class="form-control" id="editVehiculo" name="id_vehiculo">
                        <option value="">Sin asignar</option>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <option value="<?= (int) $vehiculo['id_vehiculo'] ?>"
                                    data-compatibles="<?= esc(implode(',', $vehiculo['elementos_compatibles'])) ?>">
                                <?= esc($vehiculo['matricula']) ?> (<?= esc($vehiculo['tipo']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="ayuda-campo" id="ayudaVehiculo"></p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editChofer">Chofer</label>
                    <select class="form-control" id="editChofer" name="id_chofer">
                        <option value="">Sin asignar</option>
                        <?php foreach ($usuariosActivos as $usuario): ?>
                            <option value="<?= (int) $usuario['id_usuario'] ?>"><?= esc($usuario['apellido'] . ', ' . $usuario['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editEnfermero">Personal acompañante</label>
                    <select class="form-control" id="editEnfermero" name="id_enfermero">
                        <option value="">Sin acompañante</option>
                        <?php foreach ($usuariosActivos as $usuario): ?>
                            <option value="<?= (int) $usuario['id_usuario'] ?>"><?= esc($usuario['apellido'] . ', ' . $usuario['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <p class="mensaje-error" id="editError" role="alert" hidden></p>
                <div class="modal-pie">
                    <button class="btn btn-outline-sm" type="button" data-cerrar>Cancelar</button>
                    <button class="btn btn-primary" type="submit" id="btnGuardar">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: confirmar cancelación / reactivación -->
    <div class="modal-fondo" id="modalConfirmar" hidden>
        <div class="modal-caja modal-chica" role="alertdialog" aria-modal="true" aria-labelledby="tituloConfirmar">
            <div class="modal-cabecera">
                <h2 id="tituloConfirmar">Confirmar</h2>
                <button class="icon-btn-small" type="button" data-cerrar aria-label="Cerrar"><span class="material-symbols-outlined">close</span></button>
            </div>
            <p id="textoConfirmar"></p>
            <p class="mensaje-error" id="confirmarError" role="alert" hidden></p>
            <div class="modal-pie">
                <button class="btn btn-outline-sm" type="button" data-cerrar>Volver</button>
                <button class="btn btn-primary" type="button" id="btnConfirmar">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/panel-gestion.js"></script>
</body>
</html>