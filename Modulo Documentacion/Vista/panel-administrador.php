<?php
/*
 * Acceso exclusivo para usuarios con sesión activa y rol Administrador.
 * Si no cumple ambas condiciones, se lo redirige al login.
 */
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';

$auth = new AuthController();
$auth->protegerRuta('Administrador');

$paginaActual = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Panel de Administración - Gestión Documental</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/panel-administrador.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <main class="main-wrapper">

        <div class="page-body">
            <div class="container">
                
                <section class="welcome-section">
                    <div class="welcome-text">
                        <h1>Panel de Administración</h1>
                        <p>Gestión de documentación clínica para gestionar y distribuir documentos informativos y de cuidados </p>
                    </div>
                    <a href="cargar-documento.php" class="btn btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        + Cargar Nuevo Documento
                    </a>
                </section>

                <section class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon bg-blue-light text-blue"><span class="material-symbols-outlined">description</span></div>
                        <div class="stat-info">
                            <p class="stat-label">Total Docs</p>
                            <p class="stat-value text-primary">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-success-light text-success"><span class="material-symbols-outlined">check_circle</span></div>
                        <div class="stat-info">
                            <p class="stat-label">Firmados</p>
                            <p class="stat-value text-primary">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-clinical-light text-clinical"><span class="material-symbols-outlined">pending_actions</span></div>
                        <div class="stat-info">
                            <p class="stat-label">Pendientes</p>
                            <p class="stat-value text-primary">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-error-light text-error"><span class="material-symbols-outlined">warning</span></div>
                        <div class="stat-info">
                            <p class="stat-label">Urgentes</p>
                            <p class="stat-value text-primary">0</p>
                        </div>
                    </div>
                </section>

                <section class="table-section">
                    <div class="table-header">
                        <h3>Documentos Recientes</h3>
                        <div class="filter-group">
                            <button class="filter-btn active">Todos</button>
                            <button class="filter-btn">Pendientes</button>
                            <button class="filter-btn">Completados</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Fecha</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="td-flex">
                                            <div class="status-indicator bg-clinical"></div>
                                            <div>
                                                <p class="doc-title">Manual para cuidados intensivos - Juan Pérez</p>
                                                <p class="doc-id">ID: HC-2026-9981</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-secondary">Medicina General</span></td>
                                    <td class="text-muted text-sm">Oct 24, 2026 14:30</td>
                                    <td class="text-right">
                                        <button class="btn-qr">
                                            <span class="material-symbols-outlined icon-sm">qr_code_2</span> Generar QR
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="td-flex">
                                            <div class="status-indicator bg-success"></div>
                                            <div>
                                                <p class="doc-title">Instrucciones para análisis de sangre - María Sosa</p>
                                                <p class="doc-id">ID: LAB-2026-0042</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-secondary">Análisis Clínicos</span></td>
                                    <td class="text-muted text-sm">Oct 24, 2026 11:15</td>
                                    <td class="text-right">
                                        <button class="btn-qr">
                                            <span class="material-symbols-outlined icon-sm">qr_code_2</span> Generar QR
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="td-flex">
                                            <div class="status-indicator bg-error"></div>
                                            <div>
                                                <p class="doc-title">Instrucciones post-operación - Miguel Duarte</p>
                                                <p class="doc-id">ID: SUR-2026-1102</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-secondary">Cardiología</span></td>
                                    <td class="text-muted text-sm">Oct 26, 2026 09:45</td>
                                    <td class="text-right">
                                        <button class="btn-qr">
                                            <span class="material-symbols-outlined icon-sm">qr_code_2</span> Generar QR
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="td-flex">
                                            <div class="status-indicator bg-clinical"></div>
                                            <div>
                                                <p class="doc-title">Consentimiento Informado - Elena Ortiz</p>
                                                <p class="doc-id">ID: CON-2026-8821</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-secondary">Legales</span></td>
                                    <td class="text-muted text-sm">Oct 22, 2026 16:20</td>
                                    <td class="text-right">
                                        <button class="btn-qr">
                                            <span class="material-symbols-outlined icon-sm">qr_code_2</span> Generar QR
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-footer">
                        <p>Mostrando 4 de 1,284 resultados</p>
                        <div class="pagination">
                            <button class="page-btn"><span class="material-symbols-outlined">chevron_left</span></button>
                            <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                        </div>
                    </div>
                </section>