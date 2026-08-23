<?php
/*
 * Acceso para cualquier usuario con sesión activa (todos los roles
 * autenticados pueden ver el panel general de documentación).
 * Si no hay sesión, se lo redirige al login.
 */
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';

$auth = new AuthController();
$auth->protegerRuta();

$paginaActual = 'documentacion';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Panel Médico: Documentación Clínica</title>
    
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
                <h1 class="page-title">Panel Médico: Documentación Clínica</h1>
                
                <div class="search-wrapper">
                    <div class="search-input-group">
                        <input class="search-input" placeholder="Buscar documento explicativo o preparación médica..." type="text">
                        <button class="btn-search">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>
                </div>
                
                <div class="quick-filters">
                    <button class="filter-chip active">Radiología</button>
                    <button class="filter-chip">Cirugía</button>
                    <button class="filter-chip">Laboratorio</button>
                    <button class="filter-chip">Cardiología</button>
                </div>
            </section>

            <section class="results-grid">
                
                <div class="doc-card border-blue">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-blue">Radiología</span>
                            <span class="material-symbols-outlined text-muted">description</span>
                        </div>
                        <h3 class="doc-title">Preparación para Ecografía Abdominal</h3>
                        <p class="doc-desc">Instrucciones detalladas sobre ayuno y consumo de líquidos previos al estudio de abdomen superior.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Preparación para Ecografía Abdominal', 'Radiología')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

                <div class="doc-card border-success">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-success">Laboratorio</span>
                            <span class="material-symbols-outlined text-muted">biotech</span>
                        </div>
                        <h3 class="doc-title">Ayuno para Análisis de Sangre</h3>
                        <p class="doc-desc">Protocolo de ayuno de 8 a 12 horas para perfiles lipídicos y glucemia basal. Recomendaciones generales.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Ayuno para Análisis de Sangre', 'Laboratorio')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

                <div class="doc-card border-tertiary">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-tertiary">Cardiología</span>
                            <span class="material-symbols-outlined text-muted">monitor_heart</span>
                        </div>
                        <h3 class="doc-title">Preparación para Prueba de Esfuerzo</h3>
                        <p class="doc-desc">Requisitos de vestimenta y medicación previa para el test de ergometría graduada.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Preparación para Prueba de Esfuerzo', 'Cardiología')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

                <div class="doc-card border-error">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-error">Cirugía</span>
                            <span class="material-symbols-outlined text-muted">medication</span>
                        </div>
                        <h3 class="doc-title">Indicaciones Pre-operatorias Generales</h3>
                        <p class="doc-desc">Guía de higiene y suspensión de anticoagulantes para cirugías programadas de baja complejidad.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Indicaciones Pre-operatorias Generales', 'Cirugía')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

            </section>
        </div>
    </main>

    <div class="modal-overlay hidden" id="qrModal">
        <div class="modal-container" id="modalContainer">
            <div class="modal-header">
                <h2 class="modal-title">Código de Acceso</h2>
                <button class="btn-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                
                <div class="qr-representation">
                    <div class="qr-pattern">
                        <span class="material-symbols-outlined qr-icon">qr_code</span>
                    </div>
                    <div class="qr-corner top-left"></div>
                    <div class="qr-corner top-right"></div>
                    <div class="qr-corner bottom-left"></div>
                    <div class="qr-corner bottom-right"></div>
                </div>

                <p class="modal-doc-name" id="modalDocName">Nombre del Documento</p>
                <p class="modal-doc-category" id="modalDocCategory">Categoría</p>
                
                <div class="modal-actions">
                    <button class="btn-primary flex-1">Imprimir Etiqueta</button>
                    <button class="btn-secondary flex-1" onclick="closeModal()">Finalizar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/panel-documentacion.js"></script>
</body>
</html>