<?php
/*
 * Acceso para cualquier usuario con sesión activa (Administrador y Usuario Básico).
 * Si no hay sesión, se lo redirige al login.
 */
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';

$auth = new AuthController();
$auth->protegerRuta();

$paginaActual = 'traslado-formulario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Formulario de Alta de Traslado - S.I.G.S.M.</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/form-traslado.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <main class="main-wrapper">

        <div class="page-container">
            
            <div class="page-header">
                <div>
                    <div class="breadcrumb">
                        <a href="panel-de-gestion.php">Traslados</a>
                        <span class="material-symbols-outlined" style="font-size: 16px;">chevron_right</span>
                        <span class="active">Nuevo Traslado</span>
                    </div>
                    <h1 class="page-title">Formulario de Alta de Traslado</h1>
                    <p class="page-subtitle">Complete los detalles a continuación para registrar una nueva solicitud de traslado en el sistema logístico.</p>
                </div>
                <div class="header-actions">
                    <a class="btn btn-outline" href="panel-de-gestion.php">Cancelar</a>
                    <button class="btn btn-primary hide-mobile" type="submit" form="transfer-form">
                        <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                        Registrar Traslado
                    </button>
                </div>
            </div>

            <form id="transfer-form" class="form-grid">
                
                <div class="col-main">
                    
                    <section class="card">
                        <div class="card-header">
                            <div class="card-icon"><span class="material-symbols-outlined">person_add</span></div>
                            <h2 class="card-title">1. Datos del Traslado</h2>
                        </div>
                        <div class="input-grid">
                            <div class="form-group span-2">
                                <label class="form-label">Tipo de Carga / Paciente <span class="text-error">*</span></label>
                                <select class="form-control">
                                    <option disabled selected value="">Seleccione el tipo...</option>
                                    <option value="paciente_ambulatorio">Paciente Ambulatorio</option>
                                    <option value="paciente_camilla">Paciente en Camilla</option>
                                    <option value="insumos_medicos">Insumos Médicos</option>
                                </select>
                            </div>
                            <div class="form-group span-2">
                                <label class="form-label">Identificación (Nombre del Paciente o Nro. Referencia) <span class="text-error">*</span></label>
                                <input class="form-control" placeholder="Ej: Juan Pérez / REF-90210" type="text">
                            </div>
                            <div class="form-group span-2">
                                <label class="form-label">Notas u Observaciones (Opcional)</label>
                                <textarea class="form-control" placeholder="Requerimientos especiales..." rows="3"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-header">
                            <div class="card-icon"><span class="material-symbols-outlined">route</span></div>
                            <h2 class="card-title">2. Ruta del Traslado</h2>
                        </div>
                        
                        <div class="route-container">
                            <div class="route-line"></div>
                            
                            <div class="route-step">
                                <div class="step-icon origin"><span class="material-symbols-outlined">trip_origin</span></div>
                                <div class="route-content form-group">
                                    <label class="form-label">Origen <span class="text-error">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control">
                                            <option disabled selected value="">Seleccione ubicación de origen...</option>
                                            <optgroup label="Hospital de Clínicas">
                                                <option value="hc_emergencia">Emergencia</option>
                                                <option value="hc_farmacia">Farmacia Central</option>
                                            </optgroup>
                                        </select>
                                        <button class="btn-icon-outline" type="button"><span class="material-symbols-outlined">add_location</span></button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="route-step">
                                <div class="step-icon dest"><span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">location_on</span></div>
                                <div class="route-content form-group">
                                    <label class="form-label">Destino <span class="text-error">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control">
                                            <option disabled selected value="">Seleccione ubicación de destino...</option>
                                            <optgroup label="Externos">
                                                <option value="ext_pasteur">Hospital Pasteur</option>
                                                <option value="ext_maciel">Hospital Maciel</option>
                                            </optgroup>
                                        </select>
                                        <button class="btn-icon-outline" type="button"><span class="material-symbols-outlined">add_location</span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-side">
                    <section class="card">
                        <div class="card-header">
                            <div class="card-icon"><span class="material-symbols-outlined">assignment_ind</span></div>
                            <h2 class="card-title">3. Asignación</h2>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Vehículo (ID) <span class="text-error">*</span></label>
                            <select class="form-control">
                                <option disabled selected value="">Seleccione vehículo...</option>
                                <option value="AMB-01">AMB-01 (Ambulancia CTI)</option>
                                <option value="FUR-01">FUR-01 (Furgón Insumos)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Conductor Asignado <span class="text-error">*</span></label>
                            <select class="form-control">
                                <option disabled selected value="">Seleccione conductor...</option>
                                <option value="drv_1">Carlos Rodríguez</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Personal Médico Acompañante</label>
                            <input class="form-control" placeholder="Buscar por nombre o ID..." type="text">
                            <p style="font-size: 12px; color: var(--on-surface-variant); margin-top: 4px;">Opcional: indique quién acompaña el traslado.</p>
                        </div>
                    </section>

                    <section class="summary-card">
                        <h3>Resumen</h3>
                        <ul class="summary-list">
                            <li><span>Estado Inicial:</span> <span class="val">Pendiente</span></li>
                            <li><span>Fecha de Creación:</span> <span class="val">Hoy, 14:30</span></li>
                            <li><span>Registrado por:</span> <span class="val">Admin</span></li>
                        </ul>
                        <button class="btn btn-action" type="submit">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">send</span>
                            Registrar Traslado
                        </button>
                    </section>
                </div>
            </form>
        </div>
    </main>

</body>
</html>