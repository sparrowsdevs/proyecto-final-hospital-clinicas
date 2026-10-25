<?php
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';

$auth = new AuthController();
$auth->protegerRuta();

$paginaActual = 'traslado-panel';
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
                        <p class="kpi-value">0</p>
                        <p class="kpi-trend text-muted">Sin traslados registrados</p>
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
                        <p class="kpi-value">0</p>
                        <p class="kpi-trend text-muted">Sin datos de vehículos</p>
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
                        <p class="kpi-value">0</p>
                        <p class="kpi-trend text-muted">Sin traslados asignados</p>
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
                        <p class="kpi-value">0</p>
                        <p class="kpi-trend text-muted">Sin solicitudes pendientes</p>
                    </div>
                </div>

            </div>

            <section class="table-section">
                <div class="table-toolbar">
                    <h2 class="table-title">Traslados Activos</h2>
                    <div class="table-actions">
                        <button class="btn btn-outline-sm">
                            <span class="material-symbols-outlined text-sm">filter_list</span> Filtrar
                        </button>
                        <button class="btn btn-outline-sm">
                            <span class="material-symbols-outlined text-sm">download</span> Exportar
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Paciente / Carga</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Vehículo</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="tabla-vacia" colspan="7">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                    <p>Aún no hay traslados registrados.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-footer">
                    <p class="text-muted text-sm">Mostrando 0 traslados</p>
                    <div class="pagination-controls">
                        <button class="btn-page" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="btn-page" disabled><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </section>

        </main>
    </div>

</body>
</html>