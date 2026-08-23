<?php
/*
 * Acceso exclusivo para usuarios con sesión activa y rol Administrador.
 */
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';

$auth = new AuthController();
$auth->protegerRuta('Administrador');

$paginaActual = 'cargar-documento';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Hospital de Clínicas - Panel de Administración</title>
    
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
                    <button class="btn btn-primary" onclick="openModal()">
                        <span class="material-symbols-outlined icon-sm">add</span>
                        + Cargar Nuevo Documento
                    </button>
                </div>

                <div class="bento-grid mb-lg">
                    <div class="bento-card">
                        <div class="accent-line bg-clinical-blue"></div>
                        <div class="bento-header">
                            <span class="bento-label">Total Documentos</span>
                            <span class="material-symbols-outlined text-clinical-blue opacity-80">description</span>
                        </div>
                        <div class="bento-value">1.250</div>
                    </div>
                    <div class="bento-card">
                        <div class="accent-line bg-success"></div>
                        <div class="bento-header">
                            <span class="bento-label">Escaneos QR</span>
                            <span class="material-symbols-outlined text-success opacity-80">qr_code_scanner</span>
                        </div>
                        <div class="bento-value">8.400</div>
                    </div>
                    <div class="bento-card">
                        <div class="accent-line bg-tertiary"></div>
                        <div class="bento-header">
                            <span class="bento-label">Nuevas Encuestas</span>
                            <span class="material-symbols-outlined text-tertiary opacity-80">poll</span>
                        </div>
                        <div class="bento-value">45</div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="search-box">
                            <span class="material-symbols-outlined search-icon">search</span>
                            <input class="search-input" placeholder="Buscar documentos..." type="text">
                        </div>
                        <div class="toolbar-actions">
                            <button class="btn-icon-square">
                                <span class="material-symbols-outlined">filter_list</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Fecha de Carga</th>
                                    <th>Última Act.</th>
                                    <th class="text-center">Escaneos</th>
                                    <th class="text-center">QR</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-bold text-primary">Preparación Centellograma</td>
                                    <td><span class="badge badge-secondary">Imagenología</span></td>
                                    <td class="text-secondary">12 Oct 2023</td>
                                    <td class="text-secondary">15 Oct 2023</td>
                                    <td class="text-center font-bold">342</td>
                                    <td class="text-center">
                                        <button class="icon-action text-clinical-blue"><span class="material-symbols-outlined">qr_code</span></button>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-group">
                                            <button class="icon-action text-secondary hover-blue"><span class="material-symbols-outlined icon-sm">edit</span></button>
                                            <button class="icon-action text-secondary hover-red"><span class="material-symbols-outlined icon-sm">delete</span></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-bold text-primary">Guía Ingreso Pacientes</td>
                                    <td><span class="badge badge-gray">General</span></td>
                                    <td class="text-secondary">05 Sep 2023</td>
                                    <td class="text-secondary">05 Sep 2023</td>
                                    <td class="text-center font-bold">1,205</td>
                                    <td class="text-center">
                                        <button class="icon-action text-clinical-blue"><span class="material-symbols-outlined">qr_code</span></button>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-group">
                                            <button class="icon-action text-secondary hover-blue"><span class="material-symbols-outlined icon-sm">edit</span></button>
                                            <button class="icon-action text-secondary hover-red"><span class="material-symbols-outlined icon-sm">delete</span></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-bold text-primary">Protocolo COVID-19 Act.</td>
                                    <td><span class="badge badge-error">Urgencia</span></td>
                                    <td class="text-secondary">20 Ago 2023</td>
                                    <td class="text-secondary">01 Nov 2023</td>
                                    <td class="text-center font-bold">890</td>
                                    <td class="text-center">
                                        <button class="icon-action text-clinical-blue"><span class="material-symbols-outlined">qr_code</span></button>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-group">
                                            <button class="icon-action text-secondary hover-blue"><span class="material-symbols-outlined icon-sm">edit</span></button>
                                            <button class="icon-action text-secondary hover-red"><span class="material-symbols-outlined icon-sm">delete</span></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-bold text-primary">Formulario Alta Médica</td>
                                    <td><span class="badge badge-tertiary">Administrativo</span></td>
                                    <td class="text-secondary">10 Jul 2023</td>
                                    <td class="text-secondary">10 Jul 2023</td>
                                    <td class="text-center font-bold">456</td>
                                    <td class="text-center">
                                        <button class="icon-action text-clinical-blue"><span class="material-symbols-outlined">qr_code</span></button>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-group">
                                            <button class="icon-action text-secondary hover-blue"><span class="material-symbols-outlined icon-sm">edit</span></button>
                                            <button class="icon-action text-secondary hover-red"><span class="material-symbols-outlined icon-sm">delete</span></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-footer">
                        <span class="footer-text">Mostrando 1 a 4 de 1.250 documentos</span>
                        <div class="pagination">
                            <button class="page-btn" disabled>Anterior</button>
                            <button class="page-btn active">1</button>
                            <button class="page-btn">2</button>
                            <button class="page-btn">Siguiente</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <div class="modal-overlay hidden" id="uploadModal">
        <div class="modal-window">
            
            <div class="modal-header">
                <h3>Cargar Nuevo Documento</h3>
                <button class="btn-close-circle" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="doc-title">Título del Documento</label>
                    <input class="form-input" id="doc-title" placeholder="Ej: Protocolo de Higiene 2024" type="text">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="doc-category">Categoría</label>
                    <div class="select-wrapper">
                        <select class="form-input select-input" id="doc-category">
                            <option disabled selected value="">Seleccionar categoría...</option>
                            <option value="imagenologia">Imagenología</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="general">Información General</option>
                            <option value="urgencia">Urgencia</option>
                            <option value="administrativo">Administrativo</option>
                        </select>
                        <span class="material-symbols-outlined select-icon">expand_more</span>
                    </div>
                </div>
                
                <div class="form-group mt-sm">
                    <label class="form-label">Archivo PDF</label>
                    <div class="drag-drop-zone">
                        <div class="upload-icon-circle">
                            <span class="material-symbols-outlined text-clinical-blue text-2xl">upload_file</span>
                        </div>
                        <p class="upload-title">Arrastra tu archivo aquí</p>
                        <p class="upload-subtitle">o haz clic para explorar (Max 10MB)</p>
                    </div>
                </div>
                
                <div class="settings-box">
                    <div class="checkbox-wrapper">
                        <input checked class="custom-checkbox" id="gen-qr" type="checkbox">
                    </div>
                    <div class="settings-text">
                        <label class="settings-title cursor-pointer" for="gen-qr">Generar QR Automáticamente</label>
                        <p class="settings-desc">Crea un código QR vinculado permanentemente a la última versión de este documento.</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">Cancelar</button>
                <button class="btn-primary-action">
                    <span class="material-symbols-outlined icon-sm">save</span>
                    Guardar y Generar
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/cargar-documento.js"></script>
</body>
</html>