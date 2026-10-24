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
</head>
<body>

    <aside class="sidebar">
        <div class="brand-header">
            <img alt="Logo Hospital de Clínicas" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAnQG5BbOtUZYm8wlwzLWy649KTL7oBPfAkSEQ-r0noZgmtp7bHtLj9hgyixUMl2BuOcfB1oPzxA_L27PBIY9lQutdCPhsm3AwSu46oj7sLZtY1kGmmi0UUVKcBaO-fFJ-AwA_hVC2u16EU6C7TE0NzhxkI4SxwTad2H7oOa0Dan7U5eX5dykTPba56IHqVIXJWOVttVkvl0rZwLSgvk_FvR6Kgdkp96_mtcNKJDE6xcAcS4U4kagnVguVITg5RKjuSCSMgfoJIqcY">
            <h2>Hospital de Clínicas</h2>
            <p>Módulo de Logística</p>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-links">
                <a class="nav-link active" href="#">
                    <span class="material-symbols-outlined">dashboard</span>
                    Panel de Control
                </a>
                <a class="nav-link" href="#">
                    <span class="material-symbols-outlined">local_shipping</span>
                    Traslados
                </a>
                <a class="nav-link" href="#">
                    <span class="material-symbols-outlined">minor_crash</span>
                    Gestión de Flota
                </a>
                <a class="nav-link" href="#">
                    <span class="material-symbols-outlined">map</span>
                    Planificación de Rutas
                </a>
                <a class="nav-link" href="#">
                    <span class="material-symbols-outlined">assessment</span>
                    Reportes
                </a>
            </div>
            <div class="nav-cta">
                <button class="btn btn-primary btn-full">
                    <span class="material-symbols-outlined text-sm">add</span>
                    Nuevo Traslado
                </button>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <a class="nav-link" href="#">
                <span class="material-symbols-outlined">settings</span>
                Configuración
            </a>
            <a class="nav-link" href="#">
                <span class="material-symbols-outlined">contact_support</span>
                Soporte
            </a>
        </div>
    </aside>

    <div class="main-wrapper">
        
        <header class="topbar">
            <div class="search-container">
                <span class="material-symbols-outlined search-icon">search</span>
                <input class="search-input" placeholder="Buscar traslados, vehículos..." type="text">
            </div>
            
            <div class="topbar-actions">
                <button class="icon-btn">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="badge-dot"></span>
                </button>
                <button class="icon-btn">
                    <span class="material-symbols-outlined">help</span>
                </button>
                
                <div class="divider-vertical"></div>
                
                <button class="profile-btn">
                    <span class="material-symbols-outlined profile-icon">account_circle</span>
                    <div class="profile-info">
                        <p class="profile-name">Perfil de Administrador</p>
                        <p class="profile-role">Depto. Logística</p>
                    </div>
                </button>
            </div>
        </header>

        <main class="dashboard-canvas">
            
            <div class="page-header">
                <div>
                    <h1 class="page-title">Panel de Gestión de Traslados</h1>
                    <p class="page-subtitle">Vista en tiempo real de las operaciones logísticas y estado de la flota.</p>
                </div>
                <div class="update-pill">
                    <span class="material-symbols-outlined icon-blue">schedule</span>
                    <span>Última actualización: Justo ahora</span>
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
                        <p class="kpi-value">17</p>
                        <p class="kpi-trend text-success">
                            <span class="material-symbols-outlined text-sm">trending_up</span> +12% esta semana
                        </p>
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
                        <p class="kpi-value">2<span class="value-sub">/6</span></p>
                        <p class="kpi-trend text-muted">
                            <span class="dot bg-success"></span> 6 actualmente en mantenimiento
                        </p>
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
                        <p class="kpi-value">12</p>
                        <p class="kpi-trend text-muted">
                            En turno en todas las zonas
                        </p>
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
                        <p class="kpi-value">05</p>
                        <p class="kpi-trend text-error">
                            <span class="material-symbols-outlined text-sm">warning</span> Requiere asignación inmediata
                        </p>
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
                                <td class="fw-bold">TR-8492</td>
                                <td>
                                    <div class="td-flex">
                                        <div class="avatar bg-tertiary"><span class="material-symbols-outlined text-sm">bloodtype</span></div>
                                        <div>
                                            <p class="td-title">Muestras de Sangre</p>
                                            <p class="td-subtitle">Alta Prioridad</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">Laboratorio Central</td>
                                <td class="text-muted">Hospital de Clínicas</td>
                                <td class="text-muted">Camioneta 04 (M. Silva)</td>
                                <td>
                                    <span class="badge badge-blue">
                                        <span class="dot bg-blue animate-pulse"></span> En Progreso
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button class="icon-btn-small"><span class="material-symbols-outlined">more_vert</span></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">TR-8493</td>
                                <td>
                                    <div class="td-flex">
                                        <div class="avatar bg-variant"><span class="material-symbols-outlined text-sm">accessible</span></div>
                                        <div>
                                            <p class="td-title">Juan Pérez (Paciente)</p>
                                            <p class="td-subtitle">Traslado de Rutina</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">Pabellón B, Cama 12</td>
                                <td class="text-muted">Depto. Imágenes</td>
                                <td class="text-muted f-italic">Sin asignar</td>
                                <td>
                                    <span class="badge badge-gray">
                                        <span class="dot bg-gray"></span> Pendiente
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button class="icon-btn-small"><span class="material-symbols-outlined">more_vert</span></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">TR-8490</td>
                                <td>
                                    <div class="td-flex">
                                        <div class="avatar bg-tertiary"><span class="material-symbols-outlined text-sm">medication</span></div>
                                        <div>
                                            <p class="td-title">Reabastecimiento de Farmacia</p>
                                            <p class="td-subtitle">Estándar</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">Depósito Central</td>
                                <td class="text-muted">Clínica Norte</td>
                                <td class="text-muted">Camión 02 (A. Gómez)</td>
                                <td>
                                    <span class="badge badge-blue">
                                        <span class="dot bg-blue animate-pulse"></span> En Progreso
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button class="icon-btn-small"><span class="material-symbols-outlined">more_vert</span></button>
                                </td>
                            </tr>
                            <tr class="row-completed">
                                <td class="fw-bold">TR-8488</td>
                                <td>
                                    <div class="td-flex">
                                        <div class="avatar bg-variant"><span class="material-symbols-outlined text-sm">science</span></div>
                                        <div>
                                            <p class="td-title">Equipo: Bobina de RM</p>
                                            <p class="td-subtitle">Frágil</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">Ingeniería Biomédica</td>
                                <td class="text-muted">Radiología</td>
                                <td class="text-muted">Carro 05 (L. Ruiz)</td>
                                <td>
                                    <span class="badge badge-success">
                                        <span class="material-symbols-outlined text-sm">check_circle</span> Completado
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button class="icon-btn-small"><span class="material-symbols-outlined">more_vert</span></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-footer">
                    <p class="text-muted text-sm">Mostrando 1 a 4 de 24 traslados activos</p>
                    <div class="pagination-controls">
                        <button class="btn-page" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="btn-page"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </section>

        </main>
    </div>

</body>
</html>